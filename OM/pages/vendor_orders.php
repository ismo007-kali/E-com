<?php
// Vérifier les permissions
check_permission('vendor');

// Récupérer les commandes du vendeur
$user_id = $_SESSION['user']['id'];
$stmt = $pdo->prepare("SELECT o.*, u.email, p.name as product_name 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       JOIN products p ON o.product_id = p.id 
                       WHERE p.vendor_id = ?
                       ORDER BY o.created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Gérer la mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND vendor_id = ?");
        $stmt->execute([$new_status, $order_id, $user_id]);
        flash("Statut mis à jour avec succès", 'success');
    } catch(PDOException $e) {
        flash("Erreur lors de la mise à jour du statut", 'error');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mes commandes - eKOM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Mes commandes</h1>
        
        <div class="orders-list">
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <h3>Commande n°<?= $order['id'] ?></h3>
                    <p>Client: <?= escape($order['email']) ?></p>
                    <p>Produit: <?= escape($order['product_name']) ?></p>
                    <p>Quantité: <?= $order['quantity'] ?></p>
                    <p>Total: <?= number_format($order['total'], 2) ?> €</p>
                    <p>Date: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                    
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                        <select name="status" onchange="this.form.submit()">
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>En traitement</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Expédié</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Livré</option>
                        </select>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
