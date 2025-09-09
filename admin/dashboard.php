<?php
require_once __DIR__ . '/../includes/init.php';

if (!is_logged_in() || !is_admin()) {
    redirect('login.php');
}

$page_title = 'Tableau de bord';
require_once __DIR__ . '/includes/header.php';

// Récupérer les statistiques
$stats = [
    'total_orders' => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn(),
    'total_customers' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn(),
    'total_revenue' => $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'livre'")->fetchColumn(),
];

// Récupérer les commandes récentes
$recent_orders = $pdo->query(
    "SELECT o.*, CONCAT(u.first_name, ' ', u.last_name) as customer_name 
     FROM orders o 
     LEFT JOIN users u ON o.user_id = u.id 
     ORDER BY o.created_at DESC 
     LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - <?= SITE_NAME ?> Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.1/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
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
        
        /* Recent Orders */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
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
        
        .card-header .btn {
            padding: 5px 15px;
            font-size: 14px;
        }
        
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
        }
        
        .table td {
            vertical-align: middle;
            padding: 15px;
        }
        
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="../images/logo/logo-white.png" alt="Logo" class="img-fluid">
            <h5 class="mt-2">Tableau de bord</h5>
        </div>
        
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class='bx bxs-dashboard'></i>
                <span>Tableau de bord</span>
            </a>
            <a href="products.php">
                <i class='bx bxs-shopping-bag'></i>
                <span>Produits</span>
            </a>
            <a href="categories.php">
                <i class='bx bxs-category'></i>
                <span>Catégories</span>
            </a>
            <a href="orders.php">
                <i class='bx bxs-cart'></i>
                <span>Commandes</span>
                <span class="badge bg-danger ms-auto"><?= $stats['total_orders'] ?></span>
            </a>
            <a href="customers.php">
                <i class='bx bxs-user'></i>
                <span>Clients</span>
            </a>
            <a href="reviews.php">
                <i class='bx bxs-star'></i>
                <span>Avis</span>
            </a>
            <a href="discounts.php">
                <i class='bx bxs-discount'></i>
                <span>Promotions</span>
            </a>
            <a href="settings.php">
                <i class='bx bxs-cog'></i>
                <span>Paramètres</span>
            </a>
            <a href="../index.php" target="_blank">
                <i class='bx bx-link-external'></i>
                <span>Voir le site</span>
            </a>
            <a href="logout.php">
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
                <h3>Tableau de bord</h3>
            </div>
            
            <div class="header-right">
                <div class="dropdown">
                    <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class='bx bxs-bell bx-sm'></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            3
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
            </div>
        </div>

<div class="row">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="icon products"><i class="fas fa-box"></i></div>
            <h3><?= number_format($stats['total_products']) ?></h3>
            <p>Produits</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="icon orders"><i class="fas fa-shopping-cart"></i></div>
            <h3><?= number_format($stats['total_orders']) ?></h3>
            <p>Commandes</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="icon customers"><i class="fas fa-users"></i></div>
            <h3><?= number_format($stats['total_customers']) ?></h3>
            <p>Clients</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="icon revenue"><i class="fas fa-euro-sign"></i></div>
            <h3><?= number_format($stats['total_revenue'], 2, ',', ' ') ?> F CFA</h3>
            <p>Chiffre d'affaires</p>
        </div>
    </div>
</div>
                
<!-- Dernières commandes -->
<div class="card">
    <div class="card-header">
        <h5>Dernières commandes</h5>
        <a href="orders.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_orders)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucune commande récente</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= htmlspecialchars($order['customer_name']) ?></td>
                                <td><?= date('d/m/Y', strtotime($order['created_at'])) ?></td>
                                <td><?= number_format($order['total_amount'], 2, ',', ' ') ?> F CFA</td>
                                <td>
                                    <?php
                                    $statusClasses = [
                                        'en_attente' => 'badge-warning',
                                        'en_cours' => 'badge-info',
                                        'expediee' => 'badge-primary',
                                        'livre' => 'badge-success',
                                        'annulee' => 'badge-danger'
                                    ];
                                    $statusText = [
                                        'en_attente' => 'En attente',
                                        'en_cours' => 'En cours',
                                        'expediee' => 'Expédiée',
                                        'livre' => 'Livrée',
                                        'annulee' => 'Annulée'
                                    ];
                                    $status = $order['status'] ?? 'en_attente';
                                    ?>
                                    <span class="badge <?= $statusClasses[$status] ?? 'badge-secondary' ?>">
                                        <?= $statusText[$status] ?? ucfirst($status) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_details.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Voir les détails">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
