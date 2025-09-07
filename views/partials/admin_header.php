<header class="bg-white shadow">
    <div class="flex justify-between items-center p-4">
        <div class="flex items-center">
            <button id="sidebar-toggle" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
            <h1 class="ml-4 text-xl font-semibold text-gray-800"><?= $pageTitle ?? 'Tableau de bord' ?></h1>
        </div>
        
        <div class="flex items-center space-x-4">
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                    <div class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center">
                        <i class="fas fa-user text-gray-600"></i>
                    </div>
                    <span class="hidden md:inline text-gray-700"><?= $_SESSION['user_name'] ?? 'Admin' ?></span>
                    <i class="fas fa-chevron-down text-xs text-gray-500"></i>
                </button>
                
                <div x-show="open" 
                     @click.away="open = false"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50" 
                     style="display: none;">
                    <a href="<?= BASE_URL ?>/admin/profile.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user-circle w-5 mr-2"></i> Mon profil
                    </a>
                    <a href="<?= BASE_URL ?>/admin/settings.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog w-5 mr-2"></i> Paramètres
                    </a>
                    <div class="border-t border-gray-100 my-1"></div>
                    <a href="<?= BASE_URL ?>/admin/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                        <i class="fas fa-sign-out-alt w-5 mr-2"></i> Déconnexion
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
// Toggle sidebar on mobile
const sidebar = document.querySelector('.fixed.left-0');
document.getElementById('sidebar-toggle').addEventListener('click', function() {
    sidebar.classList.toggle('-translate-x-full');
    document.querySelector('.ml-64').classList.toggle('ml-0');
});

// Close sidebar when clicking outside on mobile
if (window.innerWidth < 768) {
    document.addEventListener('click', function(event) {
        const isClickInside = document.querySelector('.fixed.left-0').contains(event.target) || 
                            document.getElementById('sidebar-toggle').contains(event.target);
        
        if (!isClickInside) {
            sidebar.classList.add('-translate-x-full');
            document.querySelector('.ml-64').classList.add('ml-0');
        }
    });
}

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth >= 768) {
        sidebar.classList.remove('-translate-x-full');
        document.querySelector('.ml-64').classList.remove('ml-0');
    } else {
        sidebar.classList.add('-translate-x-full');
        document.querySelector('.ml-64').classList.add('ml-0');
    }
});
</script>
