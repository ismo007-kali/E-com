<?php
class Order {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function createOrder($user_id, $cart, $shipping_address, $billing_address, $payment_method) {
        try {
            // Démarrer une transaction
            $this->db->query("START TRANSACTION");
            
            // Générer un numéro de commande unique
            $order_number = 'ORD-' . strtoupper(uniqid());
            
            // Calculer les montants
            $subtotal = $cart['subtotal'];
            $shipping = $cart['shipping'];
            $tax = $cart['tax'];
            $discount = $cart['discount'] ?? 0;
            $total = $cart['total'];
            
            // Créer la commande
            $sql = "INSERT INTO orders (
                        order_number, user_id, status, subtotal, tax_amount, 
                        shipping_amount, total_amount, payment_method, payment_status,
                        shipping_address, billing_address
                    ) VALUES (
                        :order_number, :user_id, 'en_attente', :subtotal, :tax_amount,
                        :shipping_amount, :total_amount, :payment_method, 'en_attente',
                        :shipping_address, :billing_address
                    )";
            
            $params = [
                ':order_number' => $order_number,
                ':user_id' => $user_id,
                ':subtotal' => $subtotal,
                ':tax_amount' => $tax,
                ':shipping_amount' => $shipping,
                ':total_amount' => $total,
                ':payment_method' => $payment_method,
                ':shipping_address' => json_encode($shipping_address),
                ':billing_address' => json_encode($billing_address)
            ];
            
            $this->db->query($sql, $params);
            $order_id = $this->db->lastInsertId();
            
            // Ajouter les articles de la commande
            foreach ($cart['items'] as $item) {
                $this->addOrderItem($order_id, $item);
                
                // Mettre à jour le stock
                $this->updateProductStock($item['product_id'], $item['quantity']);
            }
            
            // Mettre à jour le compteur d'utilisation du coupon si applicable
            if (isset($cart['coupon'])) {
                $this->updateCouponUsage($cart['coupon']['code']);
            }
            
            // Valider la transaction
            $this->db->query("COMMIT");
            
            return [
                'success' => true,
                'order_id' => $order_id,
                'order_number' => $order_number
            ];
            
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $this->db->query("ROLLBACK");
            
            return [
                'success' => false,
                'message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()
            ];
        }
    }
    
    private function addOrderItem($order_id, $item) {
        $sql = "INSERT INTO order_items (
                    order_id, product_id, product_name, 
                    product_price, quantity, total_price
                ) VALUES (
                    :order_id, :product_id, :product_name, 
                    :product_price, :quantity, :total_price
                )";
        
        $params = [
            ':order_id' => $order_id,
            ':product_id' => $item['product_id'],
            ':product_name' => $item['name'],
            ':product_price' => $item['price'],
            ':quantity' => $item['quantity'],
            ':total_price' => $item['price'] * $item['quantity']
        ];
        
        return $this->db->query($sql, $params);
    }
    
    private function updateProductStock($product_id, $quantity) {
        $sql = "UPDATE products SET quantity = quantity - :quantity 
                WHERE id = :product_id AND quantity >= :quantity";
        
        $params = [
            ':product_id' => $product_id,
            ':quantity' => $quantity
        ];
        
        $this->db->query($sql, $params);
        
        if ($this->db->rowCount() === 0) {
            throw new Exception("Stock insuffisant pour le produit ID: $product_id");
        }
    }
    
    private function updateCouponUsage($coupon_code) {
        $sql = "UPDATE discounts SET usage_count = usage_count + 1 
                WHERE code = :code AND (usage_limit IS NULL OR usage_count < usage_limit)";
        
        $this->db->query($sql, [':code' => $coupon_code]);
    }
    
    public function getOrderById($order_id, $user_id = null) {
        $sql = "SELECT o.*, 
                u.first_name, u.last_name, u.email, u.phone
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.id = :order_id";
        
        $params = [':order_id' => $order_id];
        
        // Si un user_id est fourni, vérifier que la commande lui appartient
        if ($user_id !== null) {
            $sql .= " AND o.user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        
        $this->db->query($sql, $params);
        $order = $this->db->fetch();
        
        if ($order) {
            $order['shipping_address'] = json_decode($order['shipping_address'], true);
            $order['billing_address'] = json_decode($order['billing_address'], true);
            $order['items'] = $this->getOrderItems($order_id);
        }
        
        return $order;
    }
    
    public function getOrderByNumber($order_number, $user_id = null) {
        $sql = "SELECT o.*, 
                u.first_name, u.last_name, u.email, u.phone
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                WHERE o.order_number = :order_number";
        
        $params = [':order_number' => $order_number];
        
        // Si un user_id est fourni, vérifier que la commande lui appartient
        if ($user_id !== null) {
            $sql .= " AND o.user_id = :user_id";
            $params[':user_id'] = $user_id;
        }
        
        $this->db->query($sql, $params);
        $order = $this->db->fetch();
        
        if ($order) {
            $order['shipping_address'] = json_decode($order['shipping_address'], true);
            $order['billing_address'] = json_decode($order['billing_address'], true);
            $order['items'] = $this->getOrderItems($order['id']);
        }
        
        return $order;
    }
    
    public function getOrderItems($order_id) {
        $sql = "SELECT oi.*, 
                (SELECT image_url FROM product_images WHERE product_id = oi.product_id AND is_primary = 1 LIMIT 1) as image
                FROM order_items oi
                WHERE oi.order_id = :order_id";
        
        $this->db->query($sql, [':order_id' => $order_id]);
        return $this->db->fetchAll();
    }
    
    public function getUserOrders($user_id, $limit = 10, $offset = 0) {
        $sql = "SELECT o.id, o.order_number, o.status, o.total_amount, o.created_at, 
                COUNT(oi.id) as items_count
                FROM orders o
                LEFT JOIN order_items oi ON o.id = oi.order_id
                WHERE o.user_id = :user_id
                GROUP BY o.id
                ORDER BY o.created_at DESC
                LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql, [
            ':user_id' => $user_id,
            ':limit' => (int)$limit,
            ':offset' => (int)$offset
        ]);
        
        return $this->db->fetchAll();
    }
    
    public function countUserOrders($user_id) {
        $sql = "SELECT COUNT(*) as total FROM orders WHERE user_id = :user_id";
        $this->db->query($sql, [':user_id' => $user_id]);
        $result = $this->db->fetch();
        return $result ? (int)$result['total'] : 0;
    }
    
    public function updateOrderStatus($order_id, $status) {
        $valid_statuses = ['en_attente', 'traitement', 'expedie', 'livre', 'annule', 'rembourse'];
        
        if (!in_array($status, $valid_statuses)) {
            return [
                'success' => false,
                'message' => 'Statut de commande invalide'
            ];
        }
        
        $sql = "UPDATE orders SET status = :status WHERE id = :order_id";
        $params = [
            ':status' => $status,
            ':order_id' => $order_id
        ];
        
        $this->db->query($sql, $params);
        
        return [
            'success' => $this->db->rowCount() > 0,
            'message' => 'Statut de commande mis à jour avec succès'
        ];
    }
    
    public function updatePaymentStatus($order_id, $status) {
        $valid_statuses = ['en_attente', 'paye', 'echec', 'rembourse', 'annule'];
        
        if (!in_array($status, $valid_statuses)) {
            return [
                'success' => false,
                'message' => 'Statut de paiement invalide'
            ];
        }
        
        $sql = "UPDATE orders SET payment_status = :status WHERE id = :order_id";
        $params = [
            ':status' => $status,
            ':order_id' => $order_id
        ];
        
        $this->db->query($sql, $params);
        
        return [
            'success' => $this->db->rowCount() > 0,
            'message' => 'Statut de paiement mis à jour avec succès'
        ];
    }
    
    public function getRecentOrders($limit = 5) {
        $sql = "SELECT o.id, o.order_number, o.status, o.total_amount, o.created_at,
                u.first_name, u.last_name, u.email
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                ORDER BY o.created_at DESC
                LIMIT :limit";
        
        $this->db->query($sql, [':limit' => (int)$limit]);
        return $this->db->fetchAll();
    }
    
    public function getSalesStats($start_date = null, $end_date = null) {
        $sql = "SELECT 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_sales,
                    AVG(total_amount) as avg_order_value,
                    COUNT(DISTINCT user_id) as total_customers
                FROM orders
                WHERE 1=1";
        
        $params = [];
        
        if ($start_date) {
            $sql .= " AND created_at >= :start_date";
            $params[':start_date'] = $start_date;
        }
        
        if ($end_date) {
            $sql .= " AND created_at <= :end_date";
            $params[':end_date'] = $end_date . ' 23:59:59';
        }
        
        $this->db->query($sql, $params);
        return $this->db->fetch();
    }
}
?>
