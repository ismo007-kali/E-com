<?php
// Vérifier si l'utilisateur est connecté et est un administrateur
if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?><?= SITE_NAME ?> Admin</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="../images/logo/favicon.ico" type="image/x-icon">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Boxicons -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #FF8C00;
            --secondary-color: #DC143C;
            --sidebar-width: 250px;
            --header-height: 60px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            overflow-x: hidden;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            transition: all 0.3s;
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-header img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        
        .sidebar-menu {
            padding: 20px 0;
            max-height: calc(100vh - 180px);
            overflow-y: auto;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #e0e0e0;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu a:hover, 
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-left-color: var(--primary-color);
        }
        
        .sidebar-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s;
        }
        
        /* Header */
        .header {
            background: #fff;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: -20px -20px 20px -20px;
        }
        
        .header-left h3 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        
        .header-right {
            display: flex;
            align-items: center;
        }
        
        .user-dropdown {
            position: relative;
            cursor: pointer;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 18px;
            margin-left: 15px;
        }
        
        .dropdown-menu {
            position: absolute;
            right: 0;
            left: auto;
            min-width: 200px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 5px;
            padding: 0;
            overflow: hidden;
        }
        
        .dropdown-header {
            padding: 10px 15px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        
        .dropdown-item {
            padding: 10px 15px;
            color: #333;
            transition: all 0.2s;
        }
        
        .dropdown-item:hover {
            background: #f8f9fa;
            color: var(--primary-color);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            
            .sidebar.active {
                margin-left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.active {
                margin-left: 250px;
            }
        }
        
        /* Custom styles for forms */
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 140, 0, 0.25);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            padding: 0.5rem 1.5rem;
        }
        
        .btn-primary:hover, .btn-primary:focus {
            background: linear-gradient(135deg, #e67e00, #c1121f);
            border: none;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .card:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            color: #333;
        }
        
        /* Tables */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: #6c757d;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: #f8f9fa;
        }
        
        .table td {
            vertical-align: middle;
            padding: 15px;
        }
        
        /* Badges */
        .badge {
            padding: 6px 10px;
            font-weight: 500;
            border-radius: 4px;
            font-size: 12px;
        }
        
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .badge-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        
        /* Stats Cards */
        .stats-card {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stats-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
            color: #fff;
        }
        
        .stats-card .icon.orders { background: #4e73df; }
        .stats-card .icon.products { background: #1cc88a; }
        .stats-card .icon.customers { background: #36b9cc; }
        .stats-card .icon.revenue { background: #f6c23e; }
        
        .stats-card h3 {
            font-size: 24px;
            font-weight: 700;
            margin: 0 0 5px 0;
        }
        
        .stats-card p {
            color: #6c757d;
            margin: 0;
            font-size: 14px;
        }
        
        /* Alerts */
        .alert {
            border: none;
            border-radius: 8px;
            padding: 12px 20px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
    </style>
    
    <?php if (isset($custom_css)): ?>
        <style><?= $custom_css ?></style>
    <?php endif; ?>
    
    <?php if (isset($page_styles)): ?>
        <?php foreach ($page_styles as $style): ?>
            <link href="<?= $style ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../images/logo/logo-white.png" alt="Logo" class="img-fluid">
            <h5 class="mt-2">Tableau de bord</h5>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
                <i class='bx bxs-dashboard'></i>
                <span>Tableau de bord</span>
            </a>
            <a href="products.php" class="<?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
                <i class='bx bxs-shopping-bag'></i>
                <span>Produits</span>
            </a>
            <a href="categories.php" class="<?= basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : '' ?>">
                <i class='bx bxs-category'></i>
                <span>Catégories</span>
            </a>
            <a href="orders.php" class="<?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
                <i class='bx bxs-cart'></i>
                <span>Commandes</span>
                <?php
                $pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'en_attente'")->fetchColumn();
                if ($pending_orders > 0): ?>
                    <span class="badge bg-danger ms-auto"><?= $pending_orders ?></span>
                <?php endif; ?>
            </a>
            <a href="customers.php" class="<?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>">
                <i class='bx bxs-user'></i>
                <span>Clients</span>
            </a>
            <a href="reviews.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : '' ?>">
                <i class='bx bxs-star'></i>
                <span>Avis</span>
                <?php
                $pending_reviews = $pdo->query("SELECT COUNT(*) FROM product_reviews WHERE is_approved = 0")->fetchColumn();
                if ($pending_reviews > 0): ?>
                    <span class="badge bg-warning text-dark ms-auto"><?= $pending_reviews ?></span>
                <?php endif; ?>
            </a>
            <a href="discounts.php" class="<?= basename($_SERVER['PHP_SELF']) == 'discounts.php' ? 'active' : '' ?>">
                <i class='bx bxs-discount'></i>
                <span>Promotions</span>
            </a>
            <a href="settings.php" class="<?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
                <i class='bx bxs-cog'></i>
                <span>Paramètres</span>
            </a>
            <a href="../index.php" target="_blank">
                <i class='bx bx-link-external'></i>
                <span>Voir le site</span>
            </a>
            <a href="logout.php" class="text-danger">
                <i class='bx bx-log-out'></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebar-toggle">
                    <i class='bx bx-menu'></i>
                </button>
                <h3><?= $page_title ?? 'Tableau de bord' ?></h3>
            </div>
            
            <div class="header-right">
                <div class="dropdown">
                    <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class='bx bxs-bell bx-sm'></i>
                        <?php
                        $notif_count = $pending_orders + $pending_reviews;
                        if ($notif_count > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $notif_count > 9 ? '9+' : $notif_count ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><h6 class="dropdown-header">Notifications</h6></li>
                        <?php if ($pending_orders > 0): ?>
                            <li><a class="dropdown-item" href="orders.php?status=en_attente">
                                <i class='bx bxs-cart text-warning me-2'></i>
                                <?= $pending_orders ?> commande(s) en attente
                            </a></li>
                        <?php endif; ?>
                        <?php if ($pending_reviews > 0): ?>
                            <li><a class="dropdown-item" href="reviews.php?status=pending">
                                <i class='bx bxs-star text-warning me-2'></i>
                                <?= $pending_reviews ?> avis en attente de modération
                            </a></li>
                        <?php endif; ?>
                        <?php if ($pending_orders == 0 && $pending_reviews == 0): ?>
                            <li><a class="dropdown-item text-muted" href="#">
                                Aucune nouvelle notification
                            </a></li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-primary" href="#">Voir toutes les notifications</a></li>
                    </ul>
                </div>
                
                <div class="dropdown user-dropdown ms-3">
                    <div class="user-avatar" data-bs-toggle="dropdown">
                        <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <h6><?= htmlspecialchars($_SESSION['user_name']) ?></h6>
                            <span>Administrateur</span>
                        </li>
                        <li><a class="dropdown-item" href="profile.php"><i class='bx bx-user me-2'></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class='bx bx-cog me-2'></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class='bx bx-log-out me-2'></i>Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </div>
