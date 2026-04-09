// Admin Panel Sidebar Toggle
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const isClickInsideSidebar = sidebar.contains(event.target);
            const isClickOnToggle = sidebarToggle.contains(event.target);

            if (!isClickInsideSidebar && !isClickOnToggle && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
            }
        });
    }

    // ============================================
    // GLOBAL MODAL Z-INDEX FIX
    // Fix for Bootstrap modals appearing behind backdrop
    // ============================================

    // Move all modals to body level to avoid stacking context issues
    const modals = document.querySelectorAll('.modal');
    modals.forEach(function (modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        // Fix z-index when modals are shown
        modal.addEventListener('show.bs.modal', function () {
            // Move to body if not already there
            if (this.parentElement !== document.body) {
                document.body.appendChild(this);
            }
        });

        modal.addEventListener('shown.bs.modal', function () {
            // Force z-index values
            this.style.zIndex = '9999';
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.style.zIndex = '9998';
            }
            const modalDialog = this.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.zIndex = '10000';
            }
        });

        // Clean up when modal is hidden
        modal.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
                backdrop.style.zIndex = '';
            });

            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        });
    });

    document.addEventListener('hidden.bs.modal', function () {
        setTimeout(() => {
            if (!document.querySelector('.modal.show')) {
                document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
        }, 100);
    });
    // ============================================
    // GLOBAL BACKDROP SAFETY CLEANUP
    // Prevent rare stuck black overlay that blocks clicks
    // ============================================
    function cleanupOrphanBackdrops() {
        const hasOpenModal = !!document.querySelector('.modal.show');
        const hasOpenOffcanvas = !!document.querySelector('.offcanvas.show');

        // If nothing is open, any remaining backdrop is orphaned and should be removed.
        if (!hasOpenModal && !hasOpenOffcanvas) {
            document.querySelectorAll('.modal-backdrop, .offcanvas-backdrop').forEach(function (backdrop) {
                backdrop.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }
    }

    // Run cleanup on lifecycle events where stale backdrops are commonly observed.
    cleanupOrphanBackdrops();
    window.addEventListener('pageshow', cleanupOrphanBackdrops);
    window.addEventListener('load', cleanupOrphanBackdrops);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') cleanupOrphanBackdrops();
    });
    document.addEventListener('hidden.bs.modal', cleanupOrphanBackdrops);
    document.addEventListener('hidden.bs.offcanvas', cleanupOrphanBackdrops);
});
