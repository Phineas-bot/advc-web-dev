<?php
declare(strict_types=1);

class FPDF
{
    private float $pageWidth = 595.28;
    private float $pageHeight = 841.89;
    private float $margin = 40.0;
    private float $cursorX = 40.0;
    private float $cursorY = 801.89;
    private float $fontSize = 12.0;
    private array $pages = [];
    private array $currentPage = [];

    public function AddPage(string $orientation = 'P'): void
    {
        if (!empty($this->currentPage)) {
            $this->pages[] = $this->currentPage;
        }

        if (strtoupper($orientation) === 'L') {
            $this->pageWidth = 841.89;
            $this->pageHeight = 595.28;
        } else {
            $this->pageWidth = 595.28;
            $this->pageHeight = 841.89;
        }

        $this->cursorX = $this->margin;
        $this->cursorY = $this->pageHeight - $this->margin;
        $this->currentPage = [];
    }

    public function SetFont(string $family, string $style = '', float $size = 12): void
    {
        $this->fontSize = $size;
    }

    public function SetTextColor(int $r, int $g = 0, int $b = 0): void
    {
        // Simple implementation uses a single text color.
    }

    public function SetFillColor(int $r, int $g = 0, int $b = 0): void
    {
        // No-op for the minimal writer.
    }

    public function SetDrawColor(int $r, int $g = 0, int $b = 0): void
    {
        // No-op for the minimal writer.
    }

    public function Cell(float $width, float $height = 0, string $text = '', int $border = 0, int $ln = 0, string $align = 'L', bool $fill = false, string $link = ''): void
    {
        $text = $this->truncateText($text, max(1, (int) round($width / ($this->fontSize * 0.52))));
        $x = $this->cursorX;
        $y = $this->cursorY;
        $this->currentPage[] = [
            'x' => $x,
            'y' => $y,
            'size' => $this->fontSize,
            'text' => $text,
        ];
        $this->cursorX += $width;

        if ($ln > 0) {
            $this->Ln($height > 0 ? $height : $this->fontSize + 4);
        }
    }

    public function Ln(float $height = 0): void
    {
        $height = $height > 0 ? $height : $this->fontSize + 4;
        $this->cursorY -= $height;
        $this->cursorX = $this->margin;
    }

    public function Image(string $file, float $x, float $y, float $width = 0, float $height = 0): void
    {
        $label = '[Logo]';
        $this->currentPage[] = [
            'x' => $x,
            'y' => $this->pageHeight - $y,
            'size' => 12,
            'text' => $label,
        ];
    }

    public function Output(string $dest = 'I', string $name = 'doc.pdf'): string
    {
        if (!empty($this->currentPage)) {
            $this->pages[] = $this->currentPage;
            $this->currentPage = [];
        }

        $pdf = $this->buildPdf();

        if ($dest === 'S') {
            return $pdf;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $name . '"');
        echo $pdf;
        return '';
    }

    private function buildPdf(): string
    {
        $objects = [];
        $pageObjectIds = [];
        $contentObjectIds = [];

        foreach ($this->pages as $index => $page) {
            $pageObjectIds[] = 4 + ($index * 2);
            $contentObjectIds[] = 5 + ($index * 2);
        }

        $catalog = '1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj';
        $kids = implode(' ', array_map(static fn(int $id) => $id . ' 0 R', $pageObjectIds));
        $pages = '2 0 obj << /Type /Pages /Kids [' . $kids . '] /Count ' . count($this->pages) . ' >> endobj';
        $objects[1] = $catalog;
        $objects[2] = $pages;
        $objects[3] = '3 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj';

        foreach ($this->pages as $index => $page) {
            $pageWidth = $this->pageWidth;
            $pageHeight = $this->pageHeight;
            $content = $this->buildContentStream($pageHeight, $page);
            $contentObjectId = $contentObjectIds[$index];
            $pageObjectId = $pageObjectIds[$index];

            $objects[$contentObjectId] = sprintf('%d 0 obj << /Length %d >> stream\n%sendstream endobj', $contentObjectId, strlen($content), $content);
            $objects[$pageObjectId] = sprintf('%d 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 %.2f %.2f] /Contents %d 0 R /Resources << /Font << /F1 3 0 R >> >> >> endobj', $pageObjectId, $pageWidth, $pageHeight, $contentObjectId);
        }

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $objectNumber => $object) {
            $offsets[$objectNumber] = strlen($pdf);
            $pdf .= $object . "\n";
        }

        $xrefPosition = strlen($pdf);
        $maxObjectNumber = max(array_keys($objects));
        $pdf .= 'xref\n0 ' . ($maxObjectNumber + 1) . "\n";
        $pdf .= sprintf("%010d 65535 f \n", 0);
        for ($objectNumber = 1; $objectNumber <= $maxObjectNumber; $objectNumber++) {
            $offset = $offsets[$objectNumber] ?? 0;
            if ($offset === 0) {
                continue;
            }
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }
        $pdf .= 'trailer << /Size ' . ($maxObjectNumber + 1) . ' /Root 1 0 R >>\nstartxref\n' . $xrefPosition . "\n%%EOF";

        return $pdf;
    }

    private function buildContentStream(float $pageHeight, array $page): string
    {
        $stream = '';
        foreach ($page as $line) {
            $x = max($this->margin, (float) $line['x']);
            $y = max($this->margin, (float) $line['y']);
            $pdfY = $pageHeight - $y;
            $text = $this->escapePdfText((string) $line['text']);
            $size = (float) $line['size'];
            $stream .= sprintf('BT /F1 %.2f Tf 1 0 0 1 %.2f %.2f Tm (%s) Tj ET\n', $size, $x, $pdfY, $text);
        }

        return $stream;
    }

    private function escapePdfText(string $text): string
    {
        $text = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
        return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
    }

    private function truncateText(string $text, int $maxChars): string
    {
        if (mb_strlen($text) <= $maxChars) {
            return $text;
        }

        return mb_substr($text, 0, max(0, $maxChars - 1)) . '…';
    }
}
