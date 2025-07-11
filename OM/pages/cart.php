<?php
// Gérer l'ajout au panier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add':
            $product_id = $_POST['product_id'];
            $quantity = $_POST['quantity'];
            
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            if (!isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = 0;
            }
            
            $_SESSION['cart'][$product_id] += $quantity;
            break;
            
        case 'update':
            $product_id = $_POST['product_id'];
            $quantity = $_POST['quantity'];
            
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id] = $quantity;
            }
            break;
            
        case 'remove':
            $product_id = $_POST['product_id'];
            
            if (isset($_SESSION['cart'][$product_id])) {
                unset($_SESSION['cart'][$product_id]);
            }
            break;
    }
    
    header('Location: ?page=cart');
    exit;
}

// Récupérer les produits du panier
$cart_items = [];
$total = 0;

if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if ($product) {
            $cart_items[] = [
                'id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
            $total += $product['price'] * $quantity;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - eKOM</title>
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
            <h1 class="display-4 mb-4">Mon Panier</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?php if (empty($cart_items)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h3 class="mb-4">Votre panier est vide</h3>
                        <a href="?page=products" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-2"></i> Retour aux produits
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Produit</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart_items as $item): ?>
                                        <tr>
                                            <td class="align-middle">
                                                <strong><?= htmlspecialchars($item['name']) ?></strong>
                                            </td>
                                            <td class="align-middle">
                                                <span class="text-primary"><?= number_format($item['price'], 2) ?> €</span>
                                            </td>
                                            <td class="align-middle">
                                                <form action="?page=cart&action=update" method="POST" class="d-inline">
                                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" 
                                                               min="1" max="100" class="form-control" 
                                                               style="width: 80px;">
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-sync"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="align-middle">
                                                <span class="text-success"><?= number_format($item['price'] * $item['quantity'], 2) ?> €</span>
                                            </td>
                                            <td class="align-middle">
                                                <form action="?page=cart&action=remove" method="POST" class="d-inline">
                                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer bg-white border-top-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="?page=products" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-arrow-left me-2"></i> Continuer les achats
                                    </a>
                                </div>
                                <div class="col-md-6 text-end">
                                    <h3 class="mb-3">Total: <span class="text-primary"><?= number_format($total, 2) ?> €</span></h3>
                                    <a href="?page=checkout" class="btn btn-success w-100">
                                        <i class="fas fa-credit-card me-2"></i> Passer commande
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Animation des boutons
const buttons = document.querySelectorAll('.btn');
buttons.forEach(button => {
    button.addEventListener('click', function() {
        this.classList.add('pulse');
        setTimeout(() => this.classList.remove('pulse'), 1000);
    });
});

// Animation des cases de quantité
const quantityInputs = document.querySelectorAll('input[name="quantity"]');
quantityInputs.forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.classList.add('pulse');
    });
    input.addEventListener('blur', function() {
        this.parentElement.classList.remove('pulse');
    });
});

// Validation des quantités
quantityInputs.forEach(input => {
    input.addEventListener('input', function() {
        const value = parseInt(this.value);
        if (isNaN(value) || value < 1 || value > 100) {
            this.value = 1;
        }
    });
});

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
