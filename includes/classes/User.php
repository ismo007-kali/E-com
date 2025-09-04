<?php
class User {
    private $db;
    private $user_data = [];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function register($data) {
        $sql = "INSERT INTO users (first_name, last_name, email, password, phone, address, city, postal_code, country) 
                VALUES (:first_name, :last_name, :email, :password, :phone, :address, :city, :postal_code, :country)";
        
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $params = [
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':email' => $data['email'],
            ':password' => $hashed_password,
            ':phone' => $data['phone'] ?? null,
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':postal_code' => $data['postal_code'] ?? null,
            ':country' => $data['country'] ?? null
        ];

        if ($this->db->query($sql, $params)) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email AND is_active = 1 LIMIT 1";
        $this->db->query($sql, [':email' => $email]);
        $user = $this->db->fetch();

        if ($user && password_verify($password, $user['password'])) {
            unset($user['password']);
            $this->user_data = $user;
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['is_admin'] = (bool)$user['is_admin'];
            
            return true;
        }
        return false;
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function isAdmin() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    public function logout() {
        session_unset();
        session_destroy();
        return true;
    }

    public function getUser($id = null) {
        if ($id === null && $this->isLoggedIn()) {
            $id = $_SESSION['user_id'];
        }

        if ($id) {
            $sql = "SELECT id, first_name, last_name, email, phone, address, city, postal_code, country, created_at 
                    FROM users WHERE id = :id";
            $this->db->query($sql, [':id' => $id]);
            return $this->db->fetch();
        }
        return null;
    }

    public function updateProfile($data) {
        if (!$this->isLoggedIn()) return false;

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
            ':id' => $_SESSION['user_id'],
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':phone' => $data['phone'] ?? null,
            ':address' => $data['address'] ?? null,
            ':city' => $data['city'] ?? null,
            ':postal_code' => $data['postal_code'] ?? null,
            ':country' => $data['country'] ?? null
        ];

        return $this->db->query($sql, $params);
    }

    public function changePassword($current_password, $new_password) {
        if (!$this->isLoggedIn()) return false;

        $user = $this->getUser($_SESSION['user_id']);
        if (!$user) return false;

        // Verify current password
        $sql = "SELECT password FROM users WHERE id = :id";
        $this->db->query($sql, [':id' => $_SESSION['user_id']]);
        $user = $this->db->fetch();

        if (password_verify($current_password, $user['password'])) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = :password WHERE id = :id";
            return $this->db->query($sql, [
                ':password' => $new_hashed_password,
                ':id' => $_SESSION['user_id']
            ]);
        }
        return false;
    }
}
?>
