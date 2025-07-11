<?php
// Récupérer les produits
$stmt = $pdo->query("SELECT id, name, price, description, image_url FROM products ORDER BY created_at DESC");
$products = $stmt->fetchAll();
?>

<?php include 'header.php'; ?>

<main class="container-fluid mt-5">
    <div class="row">
        <div class="col-md-12">
            <h1 class="display-4 mb-4">Nos Produits</h1>
        </div>
    </div>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="height: 200px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($product['description']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h4 mb-0 text-primary"><?php echo number_format($product['price'], 2); ?> €</span>
                            <button class="btn btn-primary btn-sm" onclick="addToCart(<?php echo $product['id']; ?>)">
                                <i class="fas fa-shopping-cart me-2"></i> Ajouter au panier
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</main>

<script>
// Animation des cartes au survol
const cards = document.querySelectorAll('.card');
cards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.classList.add('shadow-lg');
    });
    card.addEventListener('mouseleave', function() {
        this.classList.remove('shadow-lg');
    });
});

// Animation des boutons
const buttons = document.querySelectorAll('.btn');
buttons.forEach(button => {
    button.addEventListener('click', function() {
        this.classList.add('pulse');
        setTimeout(() => this.classList.remove('pulse'), 1000);
    });
});

// Fonction pour l'ajout au panier
async function addToCart(productId) {
    try {
        const response = await fetch('?page=cart&action=add&id=' + productId);
        const data = await response.json();
        
        if (data.success) {
            const button = document.querySelector(`[onclick="addToCart(${productId})"]`);
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="fas fa-check-circle text-success me-2"></i> Ajouté';
            }
            alert('Produit ajouté au panier !');
        } else {
            alert('Erreur lors de l\'ajout au panier');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de l\'ajout au panier');
    }
}
</script>
