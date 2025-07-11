<?php
// Vérifier si l'utilisateur est connecté et s'il est un vendeur
if (!is_logged_in() || $_SESSION['user']['role'] !== 'vendor') {
    header('Location: ?page=home');
    exit;
}

// Gérer la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    
    try {
        // Insérer le produit
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, vendor_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $stock, $_SESSION['user']['id']]);
        
        // Redirection vers la page de gestion des produits
        header('Location: ?page=vendor_products');
        exit;
    } catch(PDOException $e) {
        flash("Erreur lors de l'ajout du produit: " . $e->getMessage(), 'error');
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un produit - eKOM</title>
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
                <h1 class="my-4">Ajouter un produit</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nom du produit</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                                <div class="invalid-feedback">Veuillez entrer un nom pour le produit.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                                <div class="invalid-feedback">Veuillez entrer une description pour le produit.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Prix</label>
                                <div class="input-group">
                                    <span class="input-group-text">€</span>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback">Veuillez entrer un prix valide.</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="stock" class="form-label">Stock</label>
                                <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                                <div class="invalid-feedback">Veuillez entrer une quantité en stock valide.</div>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i> Ajouter le produit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
