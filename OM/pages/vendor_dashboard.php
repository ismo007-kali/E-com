<?php
// Vérifier les permissions
check_permission('vendor');

// Récupérer les statistiques du vendeur
$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_products FROM products WHERE vendor_id = ?");
$stmt->execute([$user_id]);
$total_products = $stmt->fetch()['total_products'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_orders, SUM(quantity) as total_sold FROM orders WHERE vendor_id = ?");
$stmt->execute([$user_id]);
$total_orders = $stmt->fetch();

$stmt = $pdo->prepare("SELECT SUM(total) as total_revenue FROM orders WHERE vendor_id = ? AND status = 'completed'");
$stmt->execute([$user_id]);
$total_revenue = $stmt->fetch()['total_revenue'] ?? 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Vendeur - eKOM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Dashboard Vendeur</h1>
        
        <div class="dashboard-stats">
            <div class="stat-card">
                <h3>Mes produits</h3>
                <p><?= $total_products ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Commandes</h3>
                <p><?= $total_orders['total_orders'] ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Produits vendus</h3>
                <p><?= $total_orders['total_sold'] ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Chiffre d'affaires</h3>
                <p><?= number_format($total_revenue, 2) ?> €</p>
            </div>
        </div>
        
        <div class="dashboard-actions">
            <a href="?page=vendor_products" class="action-card">
                <h3>Mes produits</h3>
                <p>Gérer mes produits</p>
            </a>
            
            <a href="?page=vendor_orders" class="action-card">
                <h3>Mes commandes</h3>
                <p>Surveiller mes commandes</p>
            </a>
            
            <a href="?page=vendor_profile" class="action-card">
                <h3>Mon profil</h3>
                <p>Modifier mes informations</p>
            </a>
        </div>
    </main>
</body>
</html>
