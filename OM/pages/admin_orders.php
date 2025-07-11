<?php
// Fonctions utilitaires pour les statuts
function getStatusColor($status) {
    $colors = [
        'pending' => 'warning',
        'processing' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success'
    ];
    return $colors[$status] ?? 'secondary';
}

function getStatusLabel($status) {
    $labels = [
        'pending' => 'En attente',
        'processing' => 'En traitement',
        'shipped' => 'Expédié',
        'delivered' => 'Livré'
    ];
    return $labels[$status] ?? 'Statut inconnu';
}

// Vérifier les permissions
check_permission('admin');

// Récupérer les commandes avec leurs détails
$stmt = $pdo->query("SELECT o.*, u.email, od.product_id, p.name as product_name, od.quantity
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       JOIN order_details od ON o.id = od.order_id
                       JOIN products p ON od.product_id = p.id
                       ORDER BY o.created_at DESC");
$orders = $stmt->fetchAll();

// Grouper les produits par commande
$orders_grouped = [];
foreach ($orders as $order) {
    $orders_grouped[$order['id']]['order'] = $order;
    $orders_grouped[$order['id']]['products'][] = [
        'id' => $order['product_id'],
        'name' => $order['product_name'],
        'quantity' => $order['quantity']
    ];
}

// Gérer la mise à jour du statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        flash("Statut mis à jour avec succès", 'success');
    } catch(PDOException $e) {
        flash("Erreur lors de la mise à jour du statut", 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des commandes - eKOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css">
</head>
<body>
    <?php include 'header.php'; ?>

<main class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="display-4 mb-4">Gestion des commandes</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Produits</th>
                                    <th>Quantité</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td class="align-middle">
                                            <strong>#<?= htmlspecialchars($order['order']['id']) ?></strong>
                                        </td>
                                        <td class="align-middle">
                                            <a href="mailto:<?= htmlspecialchars($order['order']['email']) ?>" class="text-decoration-none">
                                                <?= htmlspecialchars($order['order']['email']) ?>
                                            </a>
                                        </td>
                                        <td class="align-middle">
                                            <?php foreach ($order['products'] as $product): ?>
                                                <div class="d-flex align-items-center">
                                                    <i class="fas fa-box me-2 text-muted"></i>
                                                    <span class="me-1"><?= htmlspecialchars($product['name']) ?></span>
                                                    <span class="badge bg-primary">x<?= $product['quantity'] ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </td>
                                        <td class="align-middle text-end">
                                            <span class="badge bg-info"><?= array_sum(array_column($order['products'], 'quantity')) ?></span>
                                        </td>
                                        <td class="align-middle text-end text-primary">
                                            <?= number_format($order['order']['total'], 2) ?> €
                                        </td>
                                        <td class="align-middle">
                                            <small class="text-muted">
                                                <?= date('d/m/Y H:i', strtotime($order['order']['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td class="align-middle">
                                            <span class="badge bg-<?= getStatusColor($order['order']['status']) ?>">
                                                <?= getStatusLabel($order['order']['status']) ?>
                                            </span>
                                        </td>
                                        <td class="align-middle">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['order']['id']) ?>">
                                                <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                                    <option value="pending" <?= $order['order']['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                                                    <option value="processing" <?= $order['order']['status'] === 'processing' ? 'selected' : '' ?>>En traitement</option>
                                                    <option value="shipped" <?= $order['order']['status'] === 'shipped' ? 'selected' : '' ?>>Expédié</option>
                                                    <option value="delivered" <?= $order['order']['status'] === 'delivered' ? 'selected' : '' ?>>Livré</option>
                                                </select>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>Dernière mise à jour: <?= date('d/m/Y H:i') ?></small>
                        </div>
                        <div>
                            <a href="?page=home" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i> Retour à l'accueil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
// Animation des lignes du tableau
const rows = document.querySelectorAll('tr');
rows.forEach(row => {
    row.addEventListener('mouseenter', function() {
        this.classList.add('shadow-sm');
    });
    row.addEventListener('mouseleave', function() {
        this.classList.remove('shadow-sm');
    });
});

// Animation des sélecteurs de statut
const statusSelects = document.querySelectorAll('select[name="status"]');
statusSelects.forEach(select => {
    select.addEventListener('change', function() {
        this.classList.add('pulse');
        setTimeout(() => this.classList.remove('pulse'), 1000);
    });
});

// Animation des badges de statut
const statusBadges = document.querySelectorAll('.badge');
statusBadges.forEach(badge => {
    badge.addEventListener('mouseenter', function() {
        this.classList.add('pulse');
    });
    badge.addEventListener('mouseleave', function() {
        this.classList.remove('pulse');
    });
});
</script>
</body>
</html>
