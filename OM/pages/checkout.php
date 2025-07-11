<?php
// Vérifier si l'utilisateur est connecté
if (!is_logged_in()) {
    header('Location: ?page=login');
    exit;
}

// Récupérer le panier
$cart = $_SESSION['cart'] ?? [];

// Calculer le total
$total = 0;
foreach ($cart as $productId => $quantity) {
    $stmt = $pdo->prepare("SELECT price FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    if ($product) {
        $total += $product['price'] * $quantity;
    }
}

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $address = $_POST['address'];
    $city = $_POST['city'];
    $postal_code = $_POST['postal_code'];
    $country = $_POST['country'];
    
    try {
        // Démarrer une transaction
        $pdo->beginTransaction();
        
        // Créer la commande
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total, status, shipping_address, shipping_city, shipping_postal_code, shipping_country) VALUES (?, ?, 'pending', ?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user']['id'], $total, $address, $city, $postal_code, $country]);
        $orderId = $pdo->lastInsertId();
        
        // Créer les détails de la commande
        foreach ($cart as $productId => $quantity) {
            $stmt = $pdo->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $productId, $quantity, $product['price']]);
            
            // Mettre à jour le stock
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$quantity, $productId]);
        }
        
        // Valider la transaction
        $pdo->commit();
        
        // Vider le panier
        $_SESSION['cart'] = [];
        
        // Redirection vers la page de confirmation
        header('Location: ?page=order_confirmation&id=' . $orderId);
        exit;
        
    } catch(PDOException $error) {
        // Annuler la transaction en cas d'erreur
        $pdo->rollBack();
        flash("Une erreur est survenue lors de la commande: " . $error->getMessage(), 'error');
    }
}

// Affichage de la page HTML
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - eKOM</title>
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
            <h1 class="display-4 mb-4">Paiement</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Informations de livraison</h4>
                    
                    <?php if (empty($cart)): ?>
                        <div class="alert alert-info text-center py-5">
                            <i class="fas fa-shopping-cart fa-4x mb-3 text-muted"></i>
                            <h4 class="mb-3">Votre panier est vide</h4>
                            <a href="?page=products" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i> Retour aux produits
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Adresse</label>
                                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                                    <div class="invalid-feedback">Veuillez entrer une adresse de livraison.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="city" name="city" required>
                                    <div class="invalid-feedback">Veuillez entrer une ville.</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="postal_code" class="form-label">Code postal</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" required>
                                    <div class="invalid-feedback">Veuillez entrer un code postal.</div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="country" class="form-label">Pays</label>
                                    <input type="text" class="form-control" id="country" name="country" required>
                                    <div class="invalid-feedback">Veuillez entrer un pays.</div>
                                </div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-credit-card me-2"></i> Passer commande
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-4">Résumé de la commande</h4>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Prix</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart as $productId => $quantity): ?>
                                    <?php
                                    $stmt = $pdo->prepare("SELECT name, price FROM products WHERE id = ?");
                                    $stmt->execute([$productId]);
                                    $product = $stmt->fetch();
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= $quantity ?></td>
                                        <td><?= number_format($product['price'], 2) ?> €</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total</th>
                                    <th class="text-primary h4 mb-0"><?= number_format($total, 2) ?> €</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Validation du formulaire
(function () {
    'use strict'
    
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()

// Animation des champs de formulaire
const formFields = document.querySelectorAll('.form-control');
formFields.forEach(field => {
    field.addEventListener('focus', function() {
        this.classList.add('pulse');
    });
    
    field.addEventListener('blur', function() {
        this.classList.remove('pulse');
    });
});

// Animation du bouton de soumission
const submitButton = document.querySelector('button[type="submit"]');
if (submitButton) {
    submitButton.addEventListener('click', function() {
        this.classList.add('pulse');
        setTimeout(() => this.classList.remove('pulse'), 1000);
    });
}

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
</script>
</body>
</html>
