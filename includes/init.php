<?php
/**
 * Fichier d'initialisation de l'application
 * 
 * Ce fichier est inclus au début de chaque page et initialise
 * les composants essentiels de l'application.
 */

// Inclure le fichier de configuration
require_once __DIR__ . '/config.php';

// Vérifier les extensions PHP requises
$missingExtensions = [];
foreach (REQUIRED_EXTENSIONS as $extension) {
    if (!extension_loaded($extension)) {
        $missingExtensions[] = $extension;
    }
}

if (!empty($missingExtensions)) {
    die(sprintf(
        'Erreur : Les extensions PHP suivantes sont requises mais manquantes : %s',
        implode(', ', $missingExtensions)
    ));
}

// Démarrer la session avec les paramètres de sécurité
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();
    
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path' => COOKIE_PATH,
        'domain' => COOKIE_DOMAIN ?: $_SERVER['HTTP_HOST'],
        'secure' => SESSION_SECURE,
        'httponly' => SESSION_HTTP_ONLY,
        'samesite' => SESSION_SAME_SITE
    ]);
    
    session_name(SESSION_NAME);
    session_start();
}

// Définir le fuseau horaire
date_default_timezone_set('Africa/Niamey');

// Configuration des erreurs en fonction de l'environnement
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Inclure la classe Logger
require_once __DIR__ . '/classes/Logger.php';

// Inclure les classes
spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Initialiser les objets globaux
$db = Database::getInstance();
$user = new User();
$product = new Product();
$cart = new Cart();
$order = new Order();

// Fonctions utilitaires
function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige vers une URL
 * 
 * @param string $path Chemin relatif ou absolu
 */
function redirect($path = '/') {
    // Si le chemin est vide, on redirige vers la racine
    if (empty($path)) {
        $path = '/';
    }
    
    // Si le chemin est déjà une URL complète, on l'utilise directement
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header('Location: ' . $path);
        exit();
    }
    
    // Suppression des éventuels doublons de slash
    $path = '/' . ltrim($path, '/');
    
    // Construction de l'URL complète
    $url = rtrim(BASE_URL, '/') . $path;
    
    // Nettoyage des éventuels doubles slashes
    $url = preg_replace('/([^:])\/\//', '$1/', $url);
    
    header('Location: ' . $url);
    exit();
}

function is_ajax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response($data = null, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Vérifier le mode maintenance
if (MAINTENANCE_MODE && !(isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']))) {
    header('HTTP/1.1 503 Service Unavailable');
    include __DIR__ . '/../views/errors/maintenance.php';
    exit();
}

// Gestion des erreurs
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // Ce code d'erreur n'est pas inclus dans error_reporting
        return;
    }
    
    $error = [
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'type' => $errno
    ];
    
    // Enregistrer l'erreur dans un fichier de log
    error_log(print_r($error, true));
    
    if (ENVIRONMENT === 'development') {
        // Afficher l'erreur en développement
        echo '<pre>';
        print_r($error);
        echo '</pre>';
    } else {
        // Rediriger vers une page d'erreur en production
        if (!headers_sent()) {
            header('Location: ' . BASE_URL . 'error/500');
        }
    }
    
    return true;
});

// Gestion des exceptions
set_exception_handler(function($exception) {
    $error = [
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ];
    
    // Enregistrer l'erreur dans un fichier de log
    error_log(print_r($error, true));
    
    if (ENVIRONMENT === 'development') {
        // Afficher l'erreur en développement
        echo '<pre>';
        print_r($error);
        echo '</pre>';
    } else {
        // Rediriger vers une page d'erreur en production
        if (!headers_sent()) {
            header('Location: ' . BASE_URL . 'error/500');
        }
    }
});

// Fonction pour obtenir une valeur de la configuration
function config($key, $default = null) {
    static $config = null;
    
    if ($config === null) {
        $config = [];
        // Charger les paramètres de la base de données
        $db = Database::getInstance();
        $db->query("SELECT setting_key, setting_value FROM settings");
        $settings = $db->fetchAll();
        
        foreach ($settings as $setting) {
            $config[$setting['setting_key']] = $setting['setting_value'];
        }
    }
    
    return $config[$key] ?? $default;
}

// Fonction pour formater les prix
function format_price($amount, $currency = null) {
    if ($currency === null) {
        $currency = config('currency', 'XOF');
    }
    
    $formatted = number_format($amount, 0, ',', ' ');
    
    switch (strtoupper($currency)) {
        case 'EUR':
            return $formatted . ' €';
        case 'USD':
            return '$' . $formatted;
        case 'XOF':
            return $formatted . ' FCFA';
        default:
            return $formatted . ' ' . $currency;
    }
}

// Fonction pour générer un slug à partir d'une chaîne
function slugify($text) {
    // Remplacer les caractères spéciaux et les espaces par des tirets
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    
    // Translittération
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    
    // Supprimer les caractères non alphanumériques
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Supprimer les tirets en début et fin de chaîne
    $text = trim($text, '-');
    
    // Mettre en minuscules
    $text = strtolower($text);
    
    // Si le texte est vide après traitement, retourner un slug aléatoire
    if (empty($text)) {
        return 'n-a' . uniqid();
    }
    
    return $text;
}

// Fonction pour générer un token CSRF
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Vérifier le token CSRF
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        if (is_ajax()) {
            json_response(['error' => 'Token CSRF invalide'], 403);
        } else {
            die('Erreur de sécurité : Token CSRF invalide');
        }
    }
    return true;
}

// Initialiser le token CSRF si nécessaire
if (empty($_SESSION['csrf_token'])) {
    generate_csrf_token();
}

// Fonction pour vérifier si l'utilisateur est connecté et éventuellement rediriger
function require_login($redirect = '/login') {
    global $user;
    if (!$user->isLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        redirect($redirect);
    }
}

// Fonction pour vérifier si l'utilisateur est administrateur
function require_admin($redirect = '/') {
    global $user;
    if (!$user->isAdmin()) {
        redirect($redirect);
    }
}

// Fonction pour obtenir l'URL d'un fichier de ressource (CSS, JS, images)
function asset($path) {
    return BASE_URL . 'assets/' . ltrim($path, '/');
}

// Fonction pour inclure un fichier de vue
function view($name, $data = []) {
    extract($data);
    $file = __DIR__ . '/../views/' . $name . '.php';
    
    if (!file_exists($file)) {
        throw new Exception("La vue $name n'existe pas");
    }
    
    ob_start();
    include $file;
    return ob_get_clean();
}

// Fonction pour inclure une mise en page avec du contenu
function layout($name, $data = [])
{
    $file = VIEWS_PATH . 'layouts/' . $name . '.php';
    extract($data);
    require $file;
}

/**
 * Vérifie si un utilisateur est connecté
 * 
 * @return bool True si un utilisateur est connecté, false sinon
 */
function is_logged_in()
{
    // S'assurer que la session est démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'ID utilisateur est défini dans la session
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur connecté est un administrateur
 * 
 * @return bool True si l'utilisateur est administrateur, false sinon
 */
function is_admin()
{
    // S'assurer que la session est démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'utilisateur est connecté et est administrateur
    return is_logged_in() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

?>
