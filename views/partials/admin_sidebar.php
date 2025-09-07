<div class="fixed left-0 top-0 h-full w-64 bg-gray-800 text-white shadow-lg">
    <div class="p-4 border-b border-gray-700">
        <h1 class="text-xl font-bold"><?= SITE_NAME ?></h1>
        <p class="text-sm text-gray-400">Tableau de bord</p>
    </div>
    
    <nav class="mt-4">
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-tachometer-alt w-6"></i>
            <span>Tableau de bord</span>
        </a>
        
        <div class="px-4 pt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestion</div>
        
        <a href="<?= BASE_URL ?>/admin/products.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-box w-6"></i>
            <span>Produits</span>
        </a>
        
        <a href="<?= BASE_URL ?>/admin/categories.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-tags w-6"></i>
            <span>Catégories</span>
        </a>
        
        <a href="<?= BASE_URL ?>/admin/orders.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-shopping-cart w-6"></i>
            <span>Commandes</span>
        </a>
        
        <div class="px-4 pt-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administration</div>
        
        <a href="<?= BASE_URL ?>/admin/users.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-users w-6"></i>
            <span>Utilisateurs</span>
        </a>
        
        <a href="<?= BASE_URL ?>/admin/settings.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-cog w-6"></i>
            <span>Paramètres</span>
        </a>
        
        <a href="<?= BASE_URL ?>/admin/backup.php" class="flex items-center px-4 py-3 text-gray-300 hover:bg-gray-700 hover:text-white">
            <i class="fas fa-database w-6"></i>
            <span>Sauvegardes</span>
        </a>
        
        <div class="absolute bottom-0 w-full p-4 border-t border-gray-700">
            <a href="<?= BASE_URL ?>/admin/logout.php" class="flex items-center text-gray-300 hover:text-white">
                <i class="fas fa-sign-out-alt w-6"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </nav>
</div>
