<?php
/**
 * Script d'exécution des sauvegardes planifiées
 * 
 * Ce script est appelé par le planificateur de tâches Windows pour effectuer
 * des sauvegardes automatiques du site.
 */

// Définir le fuseau horaire
date_default_timezone_set('Africa/Niamey');

// Chemin vers le fichier de verrouillage
$lockFile = __DIR__ . '/backup.lock';

// Vérifier si une sauvegarde est déjà en cours
if (file_exists($lockFile)) {
    // Si le fichier de verrouillage a plus d'une heure, le supprimer
    if (time() - filemtime($lockFile) > 3600) {
        unlink($lockFile);
    } else {
        exit(0); // Une sauvegarde est déjà en cours
    }
}

// Créer le fichier de verrouillage
file_put_contents($lockFile, date('Y-m-d H:i:s') . " - Backup started\n", LOCK_EX);

try {
    // Inclure les fichiers nécessaires
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/classes/Database.php';
    require_once __DIR__ . '/../includes/classes/Logger.php';
    require_once __DIR__ . '/../includes/classes/BackupManager.php';
    
    // Initialiser le gestionnaire de sauvegarde
    $backupManager = BackupManager::getInstance();
    
    // Configurer la sauvegarde
    $backupManager
        ->setConfig('compress', true)
        ->setConfig('encrypt', true);
    
    // Exécuter la sauvegarde
    $result = $backupManager->createBackup();
    
    // Enregistrer le résultat
    file_put_contents($lockFile, date('Y-m-d H:i:s') . " - Backup completed\n", FILE_APPEND);
    
    if ($result['success']) {
        file_put_contents($lockFile, "Backup successful\n", FILE_APPEND);
        exit(0); // Succès
    } else {
        file_put_contents($lockFile, "Backup failed: " . implode("\n", $result['errors']) . "\n", FILE_APPEND);
        exit(1); // Échec
    }
    
} catch (Exception $e) {
    // Enregistrer l'erreur
    if (isset($lockFile) && file_exists($lockFile)) {
        file_put_contents($lockFile, date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    exit(1); // Échec
} finally {
    // Supprimer le fichier de verrouillage
    if (file_exists($lockFile)) {
        unlink($lockFile);
    }
}
