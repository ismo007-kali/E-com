<?php
// Configuration de base
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce');
define('DB_USER', 'root');
define('DB_PASS', '');

// Connexion à la base de données
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonction utilitaire pour les redirections
function redirect($page) {
    header("Location: ?page=" . $page);
    exit();
}

// Fonction utilitaire pour les flash messages
function flash($message, $type = 'info') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

// Fonction pour vérifier la connexion
function is_logged_in() {
    return isset($_SESSION['user']);
}

// Fonction pour vérifier les permissions
function check_permission($required_role) {
    if (!is_logged_in() || $_SESSION['user']['role'] !== $required_role) {
        redirect('login');
    }
}

// Fonction pour sécuriser les entrées
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
