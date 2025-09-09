        <!-- End of Main Content -->
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Dark mode JS (admin) -->
    <script src="<?= BASE_URL ?>/js/js/dark-mode.js"></script>
    <script>
        // Toggle Sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('active');
                    document.querySelector('.main-content').classList.toggle('active');
                });
            }
            
            // Activer les tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Activer les popovers
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
        
        // Confirmation de suppression
        function confirmDelete(event) {
            if (!confirm('Êtes-vous sûr de vouloir supprimer cet élément ? Cette action est irréversible.')) {
                event.preventDefault();
                return false;
            }
            return true;
        }
        
        // Désactiver les formulaires au chargement si nécessaire
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form[data-need-confirmation]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('Êtes-vous sûr de vouloir effectuer cette action ?')) {
                        e.preventDefault();
                    }
                });
            });
        });
        
        // Raccorder le bouton de thème du header admin au ThemeManager
        document.addEventListener('DOMContentLoaded', function() {
            const adminToggle = document.getElementById('theme-toggle');
            if (adminToggle) {
                // Mettre à jour l'icône au chargement
                const updateIcon = () => {
                    const icon = adminToggle.querySelector('i');
                    if (!icon) return;
                    const isDark = document.body.classList.contains('dark-mode');
                    icon.classList.toggle('fa-moon', !isDark);
                    icon.classList.toggle('fa-sun', isDark);
                };
                
                adminToggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (window.themeManager) {
                        window.themeManager.toggleTheme();
                    } else {
                        // Fallback simple
                        document.body.classList.toggle('dark-mode');
                    }
                    updateIcon();
                });
                
                // Synchroniser sur changement global
                window.addEventListener('themeChanged', updateIcon);
                // Initial
                updateIcon();
            }
        });
    </script>
    
    <?php if (isset($page_scripts)): ?>
        <?php foreach ($page_scripts as $script): ?>
            <script src="<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (isset($custom_js)): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                <?= $custom_js ?>
            });
        </script>
    <?php endif; ?>
</body>
</html>
