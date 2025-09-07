<?php
require_once '../includes/init.php';

// Vérifier si l'utilisateur est connecté
if (!$user->isLoggedIn()) {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $data = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'address' => trim($_POST['address'] ?? ''),
        'city' => trim($_POST['city'] ?? ''),
        'postal_code' => trim($_POST['postal_code'] ?? ''),
        'country' => trim($_POST['country'] ?? '')
    ];
    
    // Validation des données
    $errors = [];
    
    if (empty($data['first_name'])) {
        $errors['first_name'] = 'Le prénom est requis';
    }
    
    if (empty($data['last_name'])) {
        $errors['last_name'] = 'Le nom est requis';
    }
    
    // Si pas d'erreurs, mettre à jour le profil
    if (empty($errors)) {
        $user = new User();
        $userData = $user->getUser();
        
        // Préparer la requête SQL
        $sql = "UPDATE users SET 
                first_name = :first_name,
                last_name = :last_name,
                phone = :phone,
                address = :address,
                city = :city,
                postal_code = :postal_code,
                country = :country
                WHERE id = :id";
                
        $params = [
            ':id' => $userData['id'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':phone' => !empty($data['phone']) ? $data['phone'] : null,
            ':address' => !empty($data['address']) ? $data['address'] : null,
            ':city' => !empty($data['city']) ? $data['city'] : null,
            ':postal_code' => !empty($data['postal_code']) ? $data['postal_code'] : null,
            ':country' => !empty($data['country']) ? $data['country'] : null
        ];
        
        $db = Database::getInstance();
        if ($db->query($sql, $params)) {
            // Journaliser la modification du profil
            $logger = new Logger();
            $logger->log("Mise à jour du profil", "L'utilisateur ID: {$_SESSION['user_id']} a mis à jour son profil");
            
            // Message de succès
            $_SESSION['success_message'] = 'Votre profil a été mis à jour avec succès.';
            
            // Rediriger vers la page du profil
            header('Location: index.php');
            exit();
        } else {
            $errors['general'] = 'Une erreur est survenue lors de la mise à jour du profil. Veuillez réessayer.';
        }
    }
    
    // S'il y a des erreurs, les stocker en session et rediriger
    if (!empty($errors)) {
        $_SESSION['profile_errors'] = $errors;
        $_SESSION['profile_data'] = $data;
        header('Location: index.php');
        exit();
    }
} else {
    // Si la méthode n'est pas POST, rediriger vers la page du profil
    header('Location: index.php');
    exit();
}
