<?php
/**
 * Classe de journalisation des erreurs et des événements
 */
class Logger
{
    /**
     * Niveaux de log
     */
    const LEVEL_DEBUG = 'DEBUG';
    const LEVEL_INFO = 'INFO';
    const LEVEL_WARNING = 'WARNING';
    const LEVEL_ERROR = 'ERROR';
    const LEVEL_CRITICAL = 'CRITICAL';

    /**
     * Chemin du fichier de log
     * @var string
     */
    private static $logFile;

    /**
     * Initialise le système de journalisation
     */
    public static function initialize()
    {
        // Créer le dossier de logs s'il n'existe pas
        if (!file_exists(LOG_PATH)) {
            mkdir(LOG_PATH, 0755, true);
        }
        
        // Définir le fichier de log
        self::$logFile = LOG_PATH . 'app_' . date('Y-m-d') . '.log';
        
        // Définir le gestionnaire d'erreurs personnalisé
        set_error_handler([__CLASS__, 'errorHandler']);
        set_exception_handler([__CLASS__, 'exceptionHandler']);
        register_shutdown_function([__CLASS__, 'shutdownHandler']);
    }

    /**
     * Gestionnaire d'erreurs personnalisé
     */
    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        $level = self::mapErrorLevel($errno);
        $message = sprintf(
            '[%s] %s: %s in %s on line %d',
            $level,
            $errno,
            $errstr,
            $errfile,
            $errline
        );

        self::log($message, $level);
        
        // Ne pas exécuter le gestionnaire interne de PHP
        return true;
    }

    /**
     * Gestionnaire d'exceptions personnalisé
     */
    public static function exceptionHandler($exception)
    {
        $message = sprintf(
            '[%s] Uncaught Exception: %s in %s on line %d\nStack trace:\n%s',
            self::LEVEL_ERROR,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        self::log($message, self::LEVEL_ERROR);
        
        // Afficher une erreur générique en production
        if (ENVIRONMENT === 'production') {
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Une erreur est survenue. Notre équipe technique a été notifiée.';
        } else {
            // Afficher les détails en développement
            echo '<pre>' . $message . '</pre>';
        }
        
        exit(1);
    }

    /**
     * Gestionnaire d'arrêt pour les erreurs fatales
     */
    public static function shutdownHandler()
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            $message = sprintf(
                '[%s] %s: %s in %s on line %d',
                self::LEVEL_CRITICAL,
                $error['type'],
                $error['message'],
                $error['file'],
                $error['line']
            );
            
            self::log($message, self::LEVEL_CRITICAL);
            
            if (ENVIRONMENT === 'production') {
                // Envoyer une alerte par email en production
                self::sendErrorEmail($message);
            }
        }
    }

    /**
     * Écrit un message dans le fichier de log
     * 
     * @param string $message Le message à logger
     * @param string $level Le niveau de log (parmi les constantes de classe)
     * @return bool True si l'écriture a réussi, false sinon
     */
    public static function log($message, $level = self::LEVEL_INFO)
    {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[$timestamp] [$level] $message" . PHP_EOL;
        
        // Vérifier si le fichier dépasse la taille maximale (10 Mo)
        if (file_exists(self::$logFile) && filesize(self::$logFile) > 10 * 1024 * 1024) {
            // Renommer le fichier avec un timestamp
            $backupFile = LOG_PATH . 'app_' . date('Y-m-d_His') . '.log';
            rename(self::$logFile, $backupFile);
        }
        
        // Écrire dans le fichier de log
        return file_put_contents(
            self::$logFile, 
            $formattedMessage, 
            FILE_APPEND | LOCK_EX
        ) !== false;
    }

    /**
     * Envoie une alerte par email pour les erreurs critiques
     * 
     * @param string $errorMessage Le message d'erreur à envoyer
     */
    private static function sendErrorEmail($errorMessage)
    {
        if (!defined('ADMIN_EMAIL') || empty(ADMIN_EMAIL)) {
            return false;
        }

        $subject = 'ERREUR CRITIQUE sur ' . SITE_NAME;
        $headers = [
            'From' => 'noreply@' . $_SERVER['HTTP_HOST'],
            'X-Mailer' => 'PHP/' . phpversion(),
            'Content-Type' => 'text/plain; charset=UTF-8'
        ];

        $message = "Une erreur critique est survenue sur le site " . SITE_NAME . "\n\n";
        $message .= "Date : " . date('Y-m-d H:i:s') . "\n";
        $message .= "URL : " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "\n\n";
        $message .= "Détails de l'erreur :\n";
        $message .= $errorMessage . "\n\n";
        $message .= "_SERVER :\n" . print_r($_SERVER, true) . "\n";
        $message .= "_POST :\n" . print_r($_POST, true) . "\n";
        $message .= "_GET :\n" . print_r($_GET, true) . "\n";
        $message .= "_SESSION :\n" . (isset($_SESSION) ? print_r($_SESSION, true) : 'Non disponible') . "\n";

        return mail(
            ADMIN_EMAIL,
            '=?' . 'UTF-8' . '?B?' . base64_encode($subject) . '?=',
            $message,
            self::buildHeaders($headers)
        );
    }

    /**
     * Construit les en-têtes email
     */
    private static function buildHeaders($headers)
    {
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "$key: $value\r\n";
        }
        return $headerString;
    }

    /**
     * Convertit un niveau d'erreur PHP en niveau de log
     */
    private static function mapErrorLevel($errno)
    {
        switch ($errno) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                return self::LEVEL_ERROR;
            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                return self::LEVEL_WARNING;
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                return self::LEVEL_INFO;
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                return self::LEVEL_DEBUG;
            default:
                return self::LEVEL_INFO;
        }
    }

    /**
     * Enregistre un message de débogage
     */
    public static function debug($message)
    {
        if (ENVIRONMENT === 'development') {
            self::log($message, self::LEVEL_DEBUG);
        }
    }

    /**
     * Enregistre un message d'information
     */
    public static function info($message)
    {
        self::log($message, self::LEVEL_INFO);
    }

    /**
     * Enregistre un avertissement
     */
    public static function warning($message)
    {
        self::log($message, self::LEVEL_WARNING);
    }

    /**
     * Enregistre une erreur
     */
    public static function error($message)
    {
        self::log($message, self::LEVEL_ERROR);
    }

    /**
     * Enregistre une erreur critique
     */
    public static function critical($message)
    {
        self::log($message, self::LEVEL_CRITICAL);
        
        if (ENVIRONMENT === 'production') {
            self::sendErrorEmail($message);
        }
    }
}

// Initialiser le système de journalisation
Logger::initialize();
