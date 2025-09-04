<?php
// Vérifier les droits d'administration
require_once '../includes/init.php';
requireAdmin();

// Vérifier si un fichier a été spécifié
$file = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($file)) {
    $_SESSION['error'] = "Aucun fichier spécifié pour la suppression.";
    header('Location: backup.php');
    exit;
}

// Nettoyer le chemin du fichier pour éviter les attaques par répertoire
$file = str_replace(['..', '//'], '', $file);
$filePath = realpath(ROOT_PATH . $file);

// Vérifier que le fichier est bien dans le dossier des sauvegardes
if (strpos($filePath, realpath(ROOT_PATH . 'backups')) !== 0) {
    $_SESSION['error'] = "Accès non autorisé à ce fichier.";
    header('Location: backup.php');
    exit;
}

// Vérifier que le fichier existe
if (!file_exists($filePath)) {
    $_SESSION['error'] = "Le fichier spécifié n'existe pas.";
    header('Location: backup.php');
    exit;
}

// Supprimer le fichier
if (unlink($filePath)) {
    // Supprimer également les fichiers associés (.gz, .enc, etc.)
    $baseName = pathinfo($filePath, PATHINFO_FILENAME);
    $dir = dirname($filePath);
    
    foreach (glob($dir . '/' . $baseName . '.*') as $relatedFile) {
        if ($relatedFile !== $filePath) {
            @unlink($relatedFile);
        }
    }
    
    $_SESSION['message'] = "La sauvegarde a été supprimée avec succès.";
} else {
    $_SESSION['error'] = "Impossible de supprimer le fichier de sauvegarde.";
}

// Rediriger vers la page de gestion des sauvegardes
header('Location: backup.php');
exit;
