<?php
/**
 * Classe de protection contre les attaques par force brute
 * 
 * Cette classe permet de limiter le nombre de tentatives de connexion échouées
 * pour une adresse IP ou un nom d'utilisateur donné.
 */
class BruteForceProtection
{
    /**
     * Instance de la classe (singleton)
     * @var BruteForceProtection
     */
    private static $instance = null;
    
    /**
     * Connexion PDO à la base de données
     * @var PDO
     */
    private $pdo;
    
    /**
     * Nom de la table pour stocker les tentatives
     * @var string
     */
    private $tableName = 'login_attempts';
    
    /**
     * Constructeur privé (singleton)
     */
    private function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->createTableIfNotExists();
    }
    
    /**
     * Récupère l'instance unique de la classe
     * 
     * @return BruteForceProtection
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Crée la table si elle n'existe pas
     */
    private function createTableIfNotExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->tableName}` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ip_address` varchar(45) NOT NULL,
            `username` varchar(255) DEFAULT NULL,
            `attempts` int(11) NOT NULL DEFAULT '0',
            `last_attempt` datetime NOT NULL,
            `blocked_until` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `ip_username` (`ip_address`, `username`),
            KEY `ip_address` (`ip_address`),
            KEY `username` (`username`),
            KEY `blocked_until` (`blocked_until`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        $this->pdo->exec($sql);
    }
    
    /**
     * Enregistre une tentative de connexion échouée
     * 
     * @param string $username Nom d'utilisateur (optionnel)
     * @return bool True si le compte est bloqué, false sinon
     */
    public function logFailedAttempt($username = null)
    {
        $ipAddress = $this->getClientIp();
        $now = date('Y-m-d H:i:s');
        
        // Vérifier si une entrée existe déjà pour cette IP/utilisateur
        $stmt = $this->pdo->prepare(
            "SELECT * FROM `{$this->tableName}` 
            WHERE (`ip_address` = ? AND `username` " . ($username ? '= ?' : 'IS NULL') . ") 
            OR (`ip_address` = ? AND `username` IS NULL)"
        );
        
        $params = [$ipAddress];
        if ($username) {
            $params[] = $username;
        }
        $params[] = $ipAddress;
        
        $stmt->execute($params);
        $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($attempt) {
            // Mettre à jour l'entrée existante
            $attempts = $attempt['attempts'] + 1;
            $blockedUntil = null;
            $isBlocked = false;
            
            // Vérifier si le compte est déjà bloqué
            if ($attempt['blocked_until'] && strtotime($attempt['blocked_until']) > time()) {
                $isBlocked = true;
                $blockedUntil = $attempt['blocked_until'];
            } 
            // Sinon, vérifier si le nombre maximal de tentatives est atteint
            elseif ($attempts >= MAX_LOGIN_ATTEMPTS) {
                $blockedUntil = date('Y-m-d H:i:s', time() + LOGIN_ATTEMPT_TIMEOUT);
                $isBlocked = true;
                
                // Journaliser le blocage
                Logger::warning(sprintf(
                    'Trop de tentatives de connexion pour l\'IP %s%s',
                    $ipAddress,
                    $username ? " (utilisateur: $username)" : ''
                ));
            }
            
            $stmt = $this->pdo->prepare(
                "UPDATE `{$this->tableName}` 
                SET `attempts` = ?, `last_attempt` = ?, `blocked_until` = ? 
                WHERE `id` = ?"
            );
            $stmt->execute([$attempts, $now, $blockedUntil, $attempt['id']]);
            
            return $isBlocked;
        } else {
            // Créer une nouvelle entrée
            $stmt = $this->pdo->prepare(
                "INSERT INTO `{$this->tableName}` 
                (`ip_address`, `username`, `attempts`, `last_attempt`) 
                VALUES (?, ?, 1, ?)"
            );
            $stmt->execute([$ipAddress, $username, $now]);
            
            return false;
        }
    }
    
    /**
     * Réinitialise le compteur de tentatives pour une IP/utilisateur
     * 
     * @param string $username Nom d'utilisateur (optionnel)
     * @return bool Succès de l'opération
     */
    public function resetAttempts($username = null)
    {
        $ipAddress = $this->getClientIp();
        
        $stmt = $this->pdo->prepare(
            "DELETE FROM `{$this->tableName}` 
            WHERE `ip_address` = ? AND `username` " . ($username ? '= ?' : 'IS NULL')
        );
        
        $params = [$ipAddress];
        if ($username) {
            $params[] = $username;
        }
        
        return $stmt->execute($params);
    }
    
    /**
     * Vérifie si une IP/utilisateur est bloqué
     * 
     * @param string $username Nom d'utilisateur (optionnel)
     * @return array|bool Tableau avec les informations de blocage ou false si non bloqué
     */
    public function isBlocked($username = null)
    {
        $ipAddress = $this->getClientIp();
        $now = date('Y-m-d H:i:s');
        
        $stmt = $this->pdo->prepare(
            "SELECT *, TIMESTAMPDIFF(SECOND, ?, `blocked_until`) AS `remaining_seconds` 
            FROM `{$this->tableName}` 
            WHERE (`ip_address` = ? AND `username` " . ($username ? '= ?' : 'IS NULL') . ") 
            AND `blocked_until` > ? 
            ORDER BY `blocked_until` DESC 
            LIMIT 1"
        );
        
        $params = [$now, $ipAddress];
        if ($username) {
            $params[] = $username;
        }
        $params[] = $now;
        
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ?: false;
    }
    
    /**
     * Nettoie les anciennes tentatives de connexion
     * 
     * @param int $daysToKeep Nombre de jours à conserver dans l'historique
     * @return int Nombre d'entrées supprimées
     */
    public function cleanupOldAttempts($daysToKeep = 30)
    {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-$daysToKeep days"));
        
        $stmt = $this->pdo->prepare(
            "DELETE FROM `{$this->tableName}` 
            WHERE `last_attempt` < ? AND `blocked_until` IS NULL"
        );
        
        $stmt->execute([$cutoffDate]);
        
        return $stmt->rowCount();
    }
    
    /**
     * Récupère l'adresse IP du client
     * 
     * @return string Adresse IP du client
     */
    private function getClientIp()
    {
        $ipAddress = '';
        
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            // Les adresses IP sont généralement sous la forme: client, proxy1, proxy2, ...
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ipAddress = trim($ipList[0]);
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        
        // Nettoyer l'adresse IP
        $ipAddress = filter_var($ipAddress, FILTER_VALIDATE_IP) ? $ipAddress : '0.0.0.0';
        
        return $ipAddress;
    }
    
    /**
     * Empêcher le clonage de l'instance (singleton)
     */
    private function __clone() {}
    
    /**
     * Empêcher la désérialisation de l'instance (singleton)
     */
    private function __wakeup() {}
}
