<?php
// Vérifier les permissions
check_permission('admin');

// Récupérer les données pour les diagrammes

// Statistiques des commandes par statut
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques des produits par catégorie
$stmt = $pdo->query("SELECT vendor_id, COUNT(*) as count FROM products GROUP BY vendor_id");
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistiques des visites par jour
$stmt = $pdo->query("SELECT DATE(created_at) as date, COUNT(*) as count 
                       FROM visits 
                       WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                       GROUP BY DATE(created_at)");
$visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparer les données pour les diagrammes
$orderData = [
    'labels' => array_column($orders, 'status'),
    'values' => array_column($orders, 'count')
];

$productData = [
    'labels' => array_column($products, 'vendor_id'),
    'values' => array_column($products, 'count')
];

$visitorData = [
    'labels' => array_column($visitors, 'date'),
    'values' => array_column($visitors, 'count')
];

// Retourner les données au format JSON
header('Content-Type: application/json');
echo json_encode([
    'orders' => $orderData,
    'products' => $productData,
    'visitors' => $visitorData
]);
