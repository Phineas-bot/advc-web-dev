<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../includes/functions.php';
require_once __DIR__ . '/../../../includes/auth.php';
require_role(['admin', 'super_admin']);

$pageTitle = 'Admins - Employees Module';
require_once __DIR__ . '/../../../includes/header.php';
?>
<section class="container">
  <h1 class="h4">Employee Module - Admins</h1>
  <p class="text-muted">This is a placeholder admin area for the employees module.</p>
  <p>If you expect additional admin features here, we can scaffold them now.</p>
</section>
<?php require_once __DIR__ . '/../../../includes/footer.php'; ?>
