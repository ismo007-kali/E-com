<?php
// Vérifier les droits d'administration
require_once '../includes/init.php';
// Utiliser la fonction correcte définie dans includes/init.php
require_admin('/admin/login.php');

// Vérifier si un fichier a été spécifié
$file = isset($_GET['file']) ? $_GET['file'] : '';

if (empty($file)) {
    $_SESSION['error'] = "Aucun fichier spécifié pour la suppression.";
    header('Location: backup.php');
    exit;
}

// Nettoyer le chemin du fichier pour éviter les attaques par répertoire
$file = str_replace(['..', '\\', '//'], '', $file);

// Chemin du dossier des sauvegardes
$backupsDir = realpath(ROOT_PATH . 'backups');

// Si le dossier des backups est introuvable, refuser l'opération
if ($backupsDir === false) {
    $_SESSION['error'] = "Le dossier des sauvegardes est introuvable.";
    header('Location: backup.php');
    exit;
}

// Construire le chemin complet du fichier demandé
$filePath = realpath($backupsDir . DIRECTORY_SEPARATOR . ltrim($file, DIRECTORY_SEPARATOR));

// Vérifier que le chemin est valide et bien dans le dossier des sauvegardes
if ($filePath === false || strpos($filePath, $backupsDir) !== 0) {
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
