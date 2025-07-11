<?php
// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    header('Location: ?page=login');
    exit;
}

// Vérifier si l'utilisateur est un vendeur
if ($_SESSION['user']['role'] !== 'vendor') {
    header('Location: ?page=home');
    exit;
}

// Récupérer les informations du vendeur
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$vendor = $stmt->fetch();

// Récupérer les produits du vendeur
$stmt = $pdo->prepare("SELECT * FROM products WHERE vendor_id = ?");
$stmt->execute([$_SESSION['user']['id']]);
$products = $stmt->fetchAll();

// Récupérer les commandes du vendeur
$stmt = $pdo->prepare("SELECT o.*, p.name, p.price 
                       FROM orders o 
                       JOIN products p ON o.product_id = p.id 
                       WHERE p.vendor_id = ?
                       ORDER BY o.created_at DESC");
$stmt->execute([$_SESSION['user']['id']]);
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil du vendeur - eKOM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/custom.css">
    <link rel="stylesheet" href="../assets/css/animations.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h1 class="my-4">Mon Profil de Vendeur</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card profile-card">
                    <div class="card-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-user-tie" style="font-size: 4rem; color: #6c757d"></i>
                        </div>
                        <h3 class="card-title mb-3"><?= htmlspecialchars($vendor['name']) ?></h3>
                        <p class="card-text mb-3"><?= htmlspecialchars($vendor['email']) ?></p>
                        <p class="card-text mb-3"><?= htmlspecialchars($vendor['address']) ?></p>
                        <p class="card-text"><?= htmlspecialchars($vendor['phone']) ?></p>
                        <div class="mt-4">
                            <a href="?page=edit_profile" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i> Modifier mes informations
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Mes produits</h3>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produit</th>
                                        <th>Description</th>
                                        <th>Prix</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td><?= htmlspecialchars($product['description']) ?></td>
                                            <td><?= number_format($product['price'], 2) ?> €</td>
                                            <td><?= htmlspecialchars($product['stock']) ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="?page=edit_product&id=<?= $product['id'] ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?page=delete_product&id=<?= $product['id'] ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-3">
                            <a href="?page=add_product" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i> Ajouter un produit
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Mes commandes</h3>
                        
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Commande</th>
                                        <th>Produit</th>
                                        <th>Quantité</th>
                                        <th>Prix</th>
                                        <th>Date</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?= htmlspecialchars($order['id']) ?></td>
                                            <td><?= htmlspecialchars($order['name']) ?></td>
                                            <td><?= htmlspecialchars($order['quantity']) ?></td>
                                            <td><?= number_format($order['price'], 2) ?> €</td>
                                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                                            <td>
                                                <span class="badge bg-<?= $order['status'] === 'completed' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                    <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation de la page au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const profileCard = document.querySelector('.profile-card');
            if (profileCard) {
                profileCard.classList.add('slideIn');
                setTimeout(() => profileCard.classList.remove('slideIn'), 1000);
            }
        });

        // Animation des boutons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                this.classList.add('pulse');
                setTimeout(() => this.classList.remove('pulse'), 1000);
            });
        });

        // Animation des icônes de la table
        const icons = document.querySelectorAll('i');
        icons.forEach(icon => {
            icon.addEventListener('mouseenter', function() {
                this.classList.add('pulse');
            });
            icon.addEventListener('mouseleave', function() {
                this.classList.remove('pulse');
            });
        });
    </script>
</body>
</html>
