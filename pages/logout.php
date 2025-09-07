<?php
require_once __DIR__ . '/../includes/init.php';

// Vérifier si l'utilisateur est connecté avant de le déconnecter
if ($user->isLoggedIn()) {
    // Enregistrer l'action de déconnexion dans les logs
    $logger = new Logger();
    $logger->log("Déconnexion", "L'utilisateur ID: {$_SESSION['user_id']} s'est déconnecté");
    
    // Détruire la session
    $user->logout();
    
    // Message de succès
    $_SESSION['success_message'] = 'Vous avez été déconnecté avec succès.';
} else {
    // Si l'utilisateur n'était pas connecté, rediriger avec un message
    $_SESSION['info_message'] = 'Vous n\'étiez pas connecté.';
}

// Rediriger vers la page d'accueil
header('Location: index.php');
exit();
?>
