<?php
require_once 'includes/config.php';

// Vérifier si l'ID du produit est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID de produit invalide";
    header("Location: products.php");
    exit();
}

$product_id = (int)$_GET['id'];

try {
    // Vérifier si le produit existe
    $stmt = $pdo->prepare("SELECT id, name FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception("Produit introuvable");
    }
    
    // Démarrer une transaction
    $pdo->beginTransaction();
    
    // 1. Supprimer les images du produit du serveur
    $stmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($images as $image_path) {
        if (file_exists('../' . $image_path)) {
            unlink('../' . $image_path);
        }
        // Supprimer le répertoire parent s'il est vide
        $dir = dirname('../' . $image_path);
        if (is_dir($dir) && count(scandir($dir)) == 2) { // 2 pour . et ..
            rmdir($dir);
        }
    }
    
    // 2. Supprimer les entrées liées dans les tables de la base de données
    // Supprimer les images du produit
    $pdo->prepare("DELETE FROM product_images WHERE product_id = ?")->execute([$product_id]);
    
    // Supprimer les avis du produit
    $pdo->prepare("DELETE FROM product_reviews WHERE product_id = ?")->execute([$product_id]);
    
    // Supprimer les entrées dans la table de liaison des commandes
    $pdo->prepare("DELETE FROM order_items WHERE product_id = ?")->execute([$product_id]);
    
    // 3. Supprimer le produit lui-même
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    
    // Valider la transaction
    $pdo->commit();
    
    $_SESSION['success_message'] = "Le produit a été supprimé avec succès";
    
} catch (Exception $e) {
    // En cas d'erreur, annuler la transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    $_SESSION['error_message'] = "Une erreur est survenue lors de la suppression du produit: " . $e->getMessage();
}

// Rediriger vers la liste des produits
header("Location: products.php");
exit();
