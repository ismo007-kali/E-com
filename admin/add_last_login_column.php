<?php
// Inclure le fichier de configuration
require_once __DIR__ . '/../includes/config.php';

// Connexion à la base de données
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Vérifier la connexion
if ($conn->connect_error) {
    die("Échec de la connexion : " . $conn->connect_error);
}

// Vérifier si la colonne existe déjà
$checkColumn = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");

if ($checkColumn->num_rows == 0) {
    // Ajouter la colonne last_login
    $sql = "ALTER TABLE users ADD COLUMN last_login DATETIME DEFAULT NULL AFTER is_active";
    
    if ($conn->query($sql) === TRUE) {
        echo "La colonne 'last_login' a été ajoutée avec succès à la table 'users'.";
    } else {
        echo "Erreur lors de l'ajout de la colonne : " . $conn->error;
    }
} else {
    echo "La colonne 'last_login' existe déjà dans la table 'users'.";
}

// Fermer la connexion
$conn->close();
?>
