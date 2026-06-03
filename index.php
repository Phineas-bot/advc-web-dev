<?php
declare(strict_types=1);

$pageTitle = 'CIMEN Limited HRMS | Home';
require_once __DIR__ . '/includes/header.php';
$departments = fetch_departments();
?>
<section class="container">
    <div class="hero-panel p-4 p-lg-5 mb-4 mb-lg-5">
        <div class="row align-items-center g-4 hero-content">
            <div class="col-lg-7">
                <span class="hero-badge mb-3"><i class="fa-solid fa-building"></i> Corporate Human Resource Management</span>
                <h1 class="display-5 fw-bold mb-3">Manage people, departments, and records with control that matches a real enterprise.</h1>
                <p class="lead mb-4 text-white-75">CIMEN HRMS centralizes authentication, department workflows, employee records, and reporting for a secure, role-based HR operation.</p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= e(base_url('login.php')) ?>" class="btn btn-light btn-lg px-4">Sign In</a>
                    <a href="<?= e(base_url('register.php')) ?>" class="btn btn-outline-light btn-lg px-4">Employee Registration</a>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="section-card p-3 p-lg-4 bg-white text-dark">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold">Departments</div>
                            <h2 class="h5 mb-0">Operational units at a glance</h2>
                        </div>
                        <span class="badge text-bg-primary rounded-pill"><?= count($departments) ?> Units</span>
                    </div>
                    <div class="row row-cols-2 g-3">
                        <?php foreach (array_slice($departments, 0, 6) as $department): ?>
                            <div class="col">
                                <div class="mini-stat p-3 h-100">
                                    <div class="fw-semibold"><?= e($department['department_name']) ?></div>
                                    <div class="small text-secondary">Click from the admin menu after sign-in.</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4 mb-lg-5">
        <div class="col-md-4">
            <div class="metric-card p-4">
                <div class="text-uppercase small text-secondary fw-semibold">Security First</div>
                <div class="metric-value mt-2">PDO</div>
                <p class="mb-0 mt-2">Prepared statements, CSRF protection, hashed passwords, and role-aware access rules.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4">
                <div class="text-uppercase small text-secondary fw-semibold">Operational Depth</div>
                <div class="metric-value mt-2">10</div>
                <p class="mb-0 mt-2">Seeded departments with structured records and PDF-ready reporting output.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card p-4">
                <div class="text-uppercase small text-secondary fw-semibold">Role Coverage</div>
                <div class="metric-value mt-2">3</div>
                <p class="mb-0 mt-2">Super Admin, Admin, and Employee with distinct dashboards and permissions.</p>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="section-card p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <div class="text-uppercase small text-secondary fw-semibold">Why CIMEN HRMS</div>
                        <h2 class="h4 mb-0">Designed for an enterprise operations team</h2>
                    </div>
                    <i class="fa-solid fa-chart-line fa-2x text-primary"></i>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light h-100">
                            <h3 class="h6">Role-based workflows</h3>
                            <p class="mb-0 small text-secondary">Administrators can manage people and records without exposing super admin tools to employees.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light h-100">
                            <h3 class="h6">Printable reports</h3>
                            <p class="mb-0 small text-secondary">Department and employee exports are generated in professional PDF layout using FPDF.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light h-100">
                            <h3 class="h6">Mobile ready</h3>
                            <p class="mb-0 small text-secondary">The UI stays responsive on phones, tablets, and desktops without losing clarity.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-light h-100">
                            <h3 class="h6">Security controls</h3>
                            <p class="mb-0 small text-secondary">Session hardening, duplicate checks, input validation, and auth guards are built in.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="section-card p-4 h-100">
                <div class="text-uppercase small text-secondary fw-semibold mb-2">Corporate Notice</div>
                <h2 class="h4 mb-3">CIMEN Limited Human Resource Management</h2>
                <p class="mb-3">This platform is structured to support real HR operations for a cement and construction company: secure user onboarding, department-level records, and administrative oversight.</p>
                <div class="d-grid gap-3">
                    <a class="btn btn-cimen btn-lg" href="<?= e(base_url('dashboard.php')) ?>">Open Dashboard</a>
                    <a class="btn btn-outline-cimen btn-lg" href="<?= e(base_url('register.php')) ?>">Create Employee Account</a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
