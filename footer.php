<?php
/**
 * Footer Component
 * Job/Internship Management System
 */
?>
<footer class="mt-auto py-3 bg-white text-muted border-top">
    <div class="container text-center">
        <div class="row align-items-center">
            <div class="col-md-6 text-md-start mb-2 mb-md-0">
                <small>&copy; <?= date('Y') ?> <strong>Job/Internship Management System</strong>. All rights reserved.</small>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">Built with PHP, MySQL &amp; Bootstrap 5</small>
            </div>
        </div>
    </div>
</footer>

<!-- Bootstrap 5.3 Bundle JS with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- Confirmation script helper for in-table quick confirmation (if needed) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmButtons = document.querySelectorAll('[data-confirm]');
        confirmButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
</body>
</html>
