// Admin Panel Sidebar Toggle
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
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
    modals.forEach(function(modal) {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        // Fix z-index when modals are shown
        modal.addEventListener('show.bs.modal', function() {
            // Move to body if not already there
            if (this.parentElement !== document.body) {
                document.body.appendChild(this);
            }
        });

        modal.addEventListener('shown.bs.modal', function() {
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
        modal.addEventListener('hidden.bs.modal', function() {
            // Remove backdrop z-index override
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.style.zIndex = '';
            }
        });
    });
});

