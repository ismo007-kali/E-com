<?php
/**
 * Fichier de configuration principal
 * 
 * Ce fichier contient toutes les constantes et configurations de l'application
 */

// Détection de l'environnement
define('ENVIRONMENT', 'development'); // 'development' ou 'production'

// Configuration des erreurs en fonction de l'environnement
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecom_mode_et_tendance');
define('DB_CHARSET', 'utf8mb4');

// URL de base du site. À ajuster si le nom du dossier ou le domaine change.
define('BASE_URL', 'http://localhost/E-com');

// Chemin de base pour les liens (basé sur le répertoire du projet)
$base_dir = str_replace('\\', '/', dirname(dirname(__FILE__)));
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$base_path = trim(str_replace($doc_root, '', $base_dir), '/');
define('BASE_PATH', '/' . $base_path . '/');

// Répertoires de l'application
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('APP_PATH', ROOT_PATH . 'app' . DIRECTORY_SEPARATOR);
define('INCLUDES_PATH', __DIR__ . DIRECTORY_SEPARATOR);
define('CLASSES_PATH', INCLUDES_PATH . 'classes' . DIRECTORY_SEPARATOR);
define('VIEWS_PATH', ROOT_PATH . 'views' . DIRECTORY_SEPARATOR);
define('UPLOADS_PATH', ROOT_PATH . 'assets' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR);

// URLs des ressources
define('ASSETS_URL', BASE_URL . '/assets/');
define('UPLOADS_URL', BASE_URL . '/assets/uploads/');

// Paramètres du site
define('SITE_NAME', 'MODE ET TENDANCE');
define('SITE_EMAIL', 'contact@modetetendance.com');
define('ADMIN_EMAIL', 'admin@modetetendance.com');
define('DEFAULT_LANGUAGE', 'fr');

// Configuration des sessions
define('SESSION_NAME', 'ecom_session');
define('SESSION_LIFETIME', 86400 * 30); // 30 jours
define('SESSION_PATH', '/');
define('SESSION_DOMAIN', ''); // Mettre votre domaine si nécessaire
define('SESSION_SECURE', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
define('SESSION_HTTP_ONLY', true);
define('SESSION_SAME_SITE', 'Lax');

// Paramètres de sécurité
define('HASH_KEY', bin2hex(random_bytes(32)));
define('CSRF_TOKEN_NAME', 'csrf_token');
define('CSRF_TOKEN_LIFETIME', 7200); // 2 heures

// Configuration des cookies
define('COOKIE_PREFIX', 'ecom_');
define('COOKIE_EXPIRE', time() + (86400 * 30)); // 30 jours
define('COOKIE_PATH', '/');
define('COOKIE_DOMAIN', $_SERVER['HTTP_HOST']);

// Configuration des mots de passe
define('PASSWORD_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_OPTIONS', ['cost' => 12]);

// Configuration de la protection contre les attaques par force brute
define('MAX_LOGIN_ATTEMPTS', 5); // Nombre maximum de tentatives avant blocage
define('LOGIN_ATTEMPT_TIMEOUT', 900); // Durée du blocage en secondes (15 minutes)
define('LOGIN_ATTEMPT_WINDOW', 3600); // Fenêtre temporelle pour les tentatives (1 heure)
define('LOGIN_ATTEMPT_CLEANUP_DAYS', 30); // Nettoyage des tentatives après X jours

// Configuration des logs
define('LOG_PATH', ROOT_PATH . 'logs' . DIRECTORY_SEPARATOR);
if (!file_exists(LOG_PATH)) {
    mkdir(LOG_PATH, 0755, true);
}

// Configuration des emails
define('MAIL_HOST', 'smtp.example.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'noreply@example.com');
define('MAIL_PASSWORD', 'votre_mot_de_passe');
define('MAIL_FROM_EMAIL', 'noreply@example.com');
define('MAIL_FROM_NAME', SITE_NAME);

// Configuration des téléchargements
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 Mo
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// Configuration des images
define('IMAGE_MAX_WIDTH', 2000);
define('IMAGE_MAX_HEIGHT', 2000);
define('IMAGE_QUALITY', 85);

// Configuration du cache
define('CACHE_ENABLED', ENVIRONMENT === 'production');
define('CACHE_PATH', ROOT_PATH . 'cache' . DIRECTORY_SEPARATOR);
define('CACHE_LIFETIME', 3600); // 1 heure

// Configuration du débogage
define('DEBUG_MODE', ENVIRONMENT === 'development');
define('LOG_ERRORS', true);

// Configuration du fuseau horaire
date_default_timezone_set('Africa/Niamey');

// Configuration des devises
define('DEFAULT_CURRENCY', 'XOF');
define('CURRENCY_SYMBOL', 'FCFA');

// Configuration des taxes et frais
define('DEFAULT_TAX_RATE', 18); // 18%
define('SHIPPING_COST', 2000); // 2000 FCFA
define('FREE_SHIPPING_THRESHOLD', 50000); // 50,000 FCFA

// Configuration de la pagination
define('ITEMS_PER_PAGE', 12);

// Configuration du mode maintenance
define('MAINTENANCE_MODE', false);

// Configuration des métadonnées par défaut
define('META_TITLE', SITE_NAME . ' - Votre boutique de mode en ligne');
define('META_DESCRIPTION', 'Découvrez notre collection de vêtements et accessoires tendance à des prix abordables. Livraison rapide et sécurisée.');
define('META_KEYWORDS', 'mode, vêtements, accessoires, tendance, boutique en ligne, mode africaine');

// Configuration des réseaux sociaux
define('SOCIAL_FACEBOOK', '#');
define('SOCIAL_INSTAGRAM', '#');
define('SOCIAL_TWITTER', '#');
define('SOCIAL_PINTEREST', '#');

// Configuration des API
define('GOOGLE_MAPS_API_KEY', '');
define('RECAPTCHA_SITE_KEY', '');
define('RECAPTCHA_SECRET_KEY', '');

// Configuration des extensions requises
define('REQUIRED_EXTENSIONS', [
    'pdo_mysql',
    'mbstring',
    'json',
    'session',
    'gd',
    'fileinfo',
    'openssl',
    'intl',
    'curl'
]);

// Démarrer la session avec les paramètres sécurisés
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

/**
 * Établit une connexion à la base de données
 * 
 * @return PDO Instance PDO pour interagir avec la base de données
 * @throws PDOException Si la connexion échoue
 */
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données : " . $e->getMessage());
            
            if (ENVIRONMENT === 'development') {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            } else {
                die("Une erreur est survenue. Veuillez réessayer plus tard.");
            }
        }
    }
    
    return $pdo;
}

// Initialisation de la connexion à la base de données
$pdo = getDbConnection();
