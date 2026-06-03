<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/fpdf.php';

require_login();

$type = $_GET['type'] ?? 'employees';
$user = current_user();

if ($type === 'employees' && !is_admin()) {
    http_response_code(403);
    exit('You cannot export the employee list.');
}

$canExportDepartment = is_super_admin() || is_admin();
$userDepartmentId = (int) ($user['department_id'] ?? 0);

if ($type === 'department_records' && !$canExportDepartment && $userDepartmentId <= 0) {
    http_response_code(403);
    exit('You cannot export department records.');
}

$pdf = new FPDF();
$pdf->AddPage('P');
$pdf->SetFont('Helvetica', 'B', 18);
$pdf->Cell(0, 16, 'CIMEN Limited', 0, 1);
$pdf->SetFont('Helvetica', '', 12);
$pdf->Cell(0, 10, 'Human Resource Management System', 0, 1);
$pdf->Cell(0, 10, 'Report generated on: ' . date('Y-m-d H:i:s'), 0, 1);
$pdf->Ln(4);

if ($type === 'department_records') {
    $departmentId = (int) ($_GET['department_id'] ?? 0);
    if (!is_super_admin() && (int) ($user['department_id'] ?? 0) !== $departmentId) {
        http_response_code(403);
        exit('You can only export your own department records.');
    }
    $department = db()->prepare('SELECT * FROM departments WHERE id = :id LIMIT 1');
    $department->execute(['id' => $departmentId]);
    $department = $department->fetch();

    if (!$department) {
        exit('Department not found.');
    }

    $recordsStmt = db()->prepare('SELECT dr.*, u.full_name AS creator_name FROM department_records dr LEFT JOIN users u ON u.id = dr.created_by WHERE dr.department_id = :department_id ORDER BY dr.id DESC');
    $recordsStmt->execute(['department_id' => $departmentId]);
    $records = $recordsStmt->fetchAll();

    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Department Records: ' . $department['department_name'], 0, 1);
    $pdf->SetFont('Helvetica', 'B', 9);
    foreach (range(1, 10) as $field) {
        $pdf->Cell(50, 10, 'Field ' . $field, 1, 0);
    }
    $pdf->Cell(40, 10, 'Created By', 1, 1);
    $pdf->SetFont('Helvetica', '', 8);

    foreach ($records as $record) {
        foreach (range(1, 10) as $field) {
            $pdf->Cell(50, 10, (string) ($record['field' . $field] ?? ''), 1, 0);
        }
        $pdf->Cell(40, 10, (string) ($record['creator_name'] ?? 'System'), 1, 1);
    }
} elseif ($type === 'search') {
    $query = trim((string) ($_GET['q'] ?? ''));
    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Search Results', 0, 1);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell(0, 10, 'Search term: ' . $query, 0, 1);

    $stmt = db()->prepare('SELECT u.full_name, u.username, u.email, d.department_name, u.role FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.full_name LIKE :q OR u.username LIKE :q OR u.email LIKE :q OR d.department_name LIKE :q ORDER BY u.full_name ASC');
    $stmt->execute(['q' => '%' . $query . '%']);
    $rows = $stmt->fetchAll();

    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(50, 10, 'Name', 1, 0);
    $pdf->Cell(35, 10, 'Username', 1, 0);
    $pdf->Cell(55, 10, 'Email', 1, 0);
    $pdf->Cell(40, 10, 'Department', 1, 0);
    $pdf->Cell(25, 10, 'Role', 1, 1);
    $pdf->SetFont('Helvetica', '', 8);

    foreach ($rows as $row) {
        $pdf->Cell(50, 10, $row['full_name'], 1, 0);
        $pdf->Cell(35, 10, $row['username'], 1, 0);
        $pdf->Cell(55, 10, $row['email'], 1, 0);
        $pdf->Cell(40, 10, $row['department_name'] ?? '', 1, 0);
        $pdf->Cell(25, 10, $row['role'], 1, 1);
    }
} else {
    $sql = 'SELECT u.full_name, u.username, u.email, u.phone, d.department_name, u.role FROM users u LEFT JOIN departments d ON d.id = u.department_id WHERE u.role = "employee"';
    $params = [];
    if (!is_super_admin() && is_admin()) {
        $sql .= ' AND u.department_id = :department_id';
        $params['department_id'] = $userDepartmentId;
    }
    $sql .= ' ORDER BY u.full_name ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $pdf->SetFont('Helvetica', 'B', 14);
    $pdf->Cell(0, 12, 'Employee List', 0, 1);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell(50, 10, 'Name', 1, 0);
    $pdf->Cell(30, 10, 'Username', 1, 0);
    $pdf->Cell(55, 10, 'Email', 1, 0);
    $pdf->Cell(35, 10, 'Phone', 1, 0);
    $pdf->Cell(50, 10, 'Department', 1, 0);
    $pdf->Cell(20, 10, 'Role', 1, 1);
    $pdf->SetFont('Helvetica', '', 8);

    foreach ($rows as $row) {
        $pdf->Cell(50, 10, $row['full_name'], 1, 0);
        $pdf->Cell(30, 10, $row['username'], 1, 0);
        $pdf->Cell(55, 10, $row['email'], 1, 0);
        $pdf->Cell(35, 10, $row['phone'], 1, 0);
        $pdf->Cell(50, 10, $row['department_name'] ?? '', 1, 0);
        $pdf->Cell(20, 10, $row['role'], 1, 1);
    }
}

$pdf->Output('I', 'cimen_report.pdf');
