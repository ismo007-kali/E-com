<?php
// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    header('Location: ?page=login');
    exit;
}

// Vérifier si l'ID de commande est présent
if (!isset($_GET['id'])) {
    header('Location: ?page=home');
    exit;
}

// Récupérer les détails de la commande
$orderId = $_GET['id'];
$stmt = $pdo->prepare("SELECT orders.*, users.email 
                       FROM orders 
                       JOIN users ON orders.user_id = users.id 
                       WHERE orders.id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

// Vérifier si la commande appartient à l'utilisateur
if (!$order || $order['user_id'] !== $_SESSION['user']['id']) {
    header('Location: ?page=home');
    exit;
}

// Récupérer les détails des produits
$stmt = $pdo->prepare("SELECT products.name, order_details.quantity, products.price 
                       FROM order_details 
                       JOIN products ON order_details.product_id = products.id 
                       WHERE order_details.order_id = ?");
$stmt->execute([$orderId]);
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de commande - eKOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
</head>
<body>
    <?php include 'header.php'; ?>

<main class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="display-4 mb-4">Confirmation de commande</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 4rem"></i>
                        </div>
                        <h2 class="mb-3">Merci pour votre commande !</h2>
                        <p class="lead">Votre commande numéro <span class="text-primary"><?= htmlspecialchars($order['id']) ?></span> a été passée avec succès.</p>
                    </div>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h3 class="card-title mb-4">Détails de la commande</h3>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <tbody>
                                                <tr>
                                                    <th>Date</th>
                                                    <td class="text-end"><?= htmlspecialchars($order['created_at']) ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Total</th>
                                                    <td class="text-end text-primary h4 mb-0"><?= number_format($order['total'], 2) ?> €</td>
                                                </tr>
                                                <tr>
                                                    <th>Statut</th>
                                                    <td class="text-end">
                                                        <span class="badge bg-success"><?= htmlspecialchars($order['status']) ?></span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h3 class="card-title mb-4">Adresse de livraison</h3>
                                    <address class="mb-4">
                                        <strong>Adresse:</strong><br>
                                        <?= htmlspecialchars($order['shipping_address']) ?><br>
                                        <?= htmlspecialchars($order['shipping_postal_code']) ?> <?= htmlspecialchars($order['shipping_city']) ?><br>
                                        <?= htmlspecialchars($order['shipping_country']) ?><br>
                                        <strong>Email:</strong><br>
                                        <?= htmlspecialchars($order['email']) ?>
                                    </address>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h3 class="mb-3">Produits commandés</h3>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Prix unitaire</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td class="text-end"><?= $product['quantity'] ?></td>
                                            <td class="text-end text-primary"><?= number_format($product['price'], 2) ?> €</td>
                                            <td class="text-end text-success"><?= number_format($product['price'] * $product['quantity'], 2) ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-center mt-5">
                        <a href="?page=home" class="btn btn-primary btn-lg">
                            <i class="fas fa-home me-2"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Animation des cartes
const cards = document.querySelectorAll('.card');
cards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.classList.add('shadow-lg');
    });
    card.addEventListener('mouseleave', function() {
        this.classList.remove('shadow-lg');
    });
});

// Animation du bouton retour
const homeButton = document.querySelector('a[href="?page=home"]');
if (homeButton) {
    homeButton.addEventListener('click', function() {
        this.classList.add('pulse');
        setTimeout(() => this.classList.remove('pulse'), 1000);
    });
}

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
</script>
</body>
</html>
