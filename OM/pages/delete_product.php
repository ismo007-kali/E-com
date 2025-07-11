<?php
// Vérifier si l'utilisateur est connecté et s'il est un vendeur
if (!is_logged_in() || $_SESSION['user']['role'] !== 'vendor') {
    header('Location: ?page=home');
    exit;
}

// Vérifier si l'ID du produit est présent
if (!isset($_GET['id'])) {
    header('Location: ?page=vendor_products');
    exit;
}

// Récupérer les informations du produit
$product_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND vendor_id = ?");
$stmt->execute([$product_id, $_SESSION['user']['id']]);
$product = $stmt->fetch();

// Vérifier si le produit appartient au vendeur
if (!$product) {
    header('Location: ?page=vendor_products');
    exit;
}

// Gérer la suppression du produit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Supprimer le produit
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?");
        $stmt->execute([$product_id, $_SESSION['user']['id']]);
        
        // Redirection vers la page de gestion des produits
        header('Location: ?page=vendor_products');
        exit;
    } catch(PDOException $e) {
        flash("Erreur lors de la suppression du produit: " . $e->getMessage(), 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer le produit - eKOM</title>
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
                <h1 class="my-4">Supprimer le produit</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 offset-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <i class="fas fa-exclamation-triangle text-warning" style="font-size: 4rem"></i>
                            <h2 class="mt-3">Êtes-vous sûr ?</h2>
                            <p class="lead">Vous êtes sur le point de supprimer le produit "<?= htmlspecialchars($product['name']) ?>".</p>
                            <p>Cette action est irréversible.</p>
                        </div>

                        <form method="POST" class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i> Oui, supprimer le produit
                            </button>
                            <a href="?page=vendor_products" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i> Non, annuler
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation des boutons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                this.classList.add('pulse');
                setTimeout(() => this.classList.remove('pulse'), 1000);
            });
        });

        // Animation de la page au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.card');
            if (card) {
                card.classList.add('slideIn');
                setTimeout(() => card.classList.remove('slideIn'), 1000);
            }
        });
    </script>
</body>
</html>
