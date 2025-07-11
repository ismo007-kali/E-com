<?php
// Vérifier les permissions
check_permission('admin');

// Gérer l'ajout de produit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    try {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock]);
        flash("Produit ajouté avec succès !", 'success');
        redirect('admin_products');
    } catch(PDOException $e) {
        flash("Erreur lors de l'ajout du produit", 'error');
        redirect('admin_products');
    }
}

// Récupérer tous les produits
$stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Gestion des produits - eKOM</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <h1>Gestion des produits</h1>
        
        <div class="admin-actions">
            <button onclick="document.getElementById('addProductForm').style.display = 'block'">Ajouter un produit</button>
        </div>
        
        <div id="addProductForm" style="display: none; margin-top: 20px; padding: 20px; background: #f5f5f5; border-radius: 8px;">
            <h2>Ajouter un produit</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div>
                    <label for="name">Nom:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div>
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
                
                <div>
                    <label for="price">Prix:</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                
                <div>
                    <label for="stock">Stock:</label>
                    <input type="number" id="stock" name="stock" min="0" required>
                </div>
                
                <button type="submit">Ajouter</button>
                <button type="button" onclick="document.getElementById('addProductForm').style.display = 'none'">Annuler</button>
            </form>
        </div>
        
        <div class="products-list">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <h3><?= escape($product['name']) ?></h3>
                    <p><?= escape($product['description']) ?></p>
                    <p>Prix: <?= number_format($product['price'], 2) ?> €</p>
                    <p>Stock: <?= $product['stock'] ?></p>
                    <div class="product-actions">
                        <a href="?page=edit_product&id=<?= $product['id'] ?>">Modifier</a>
                        <a href="?page=delete_product&id=<?= $product['id'] ?>" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">Supprimer</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
