<?php
declare(strict_types=1);
?>
</main>
<footer class="footer mt-5 app-footer">
    <div class="container py-4">
        <div class="row gy-3 align-items-center">
            <div class="col-md-6">
                <h5 class="mb-2">CIMEN Limited</h5>
                <p class="mb-0 text-muted">Cement and Construction Company HR Management Platform.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <p class="mb-1 text-muted">Email: hr@cimenlimited.com | Phone: +233 000 000 000</p>
                <small class="text-muted">&copy; <?= date('Y') ?> CIMEN Limited. All rights reserved.</small>
            </div>
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= e(base_url('assets/js/app.js')) ?>"></script>
<?php if ($flash = flash('success')): ?>
<script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'success',title:'Success',text:<?= json_encode($flash['message']) ?>,confirmButtonColor:'#155e9b'}));</script>
<?php endif; ?>
<?php if ($flash = flash('error')): ?>
<script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'error',title:'Error',text:<?= json_encode($flash['message']) ?>,confirmButtonColor:'#155e9b'}));</script>
<?php endif; ?>
<?php if ($flash = flash('info')): ?>
<script>window.addEventListener('DOMContentLoaded',()=>Swal.fire({icon:'info',title:'Notice',text:<?= json_encode($flash['message']) ?>,confirmButtonColor:'#155e9b'}));</script>
<?php endif; ?>
</body>
</html>
