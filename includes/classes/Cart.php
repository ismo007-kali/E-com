<?php
class Cart {
    private $db;
    private $cart_name = 'shopping_cart';

    public function __construct() {
        $this->db = Database::getInstance();
        
        // Initialize cart if not exists
        if (!isset($_SESSION[$this->cart_name])) {
            $_SESSION[$this->cart_name] = [
                'items' => [],
                'subtotal' => 0,
                'shipping' => 0,
                'tax' => 0,
                'total' => 0,
                'coupon' => null,
                'discount' => 0,
                'items_count' => 0
            ];
        }
    }

    public function addItem($product_id, $quantity = 1, $options = []) {
        // Get product details
        $product = $this->getProduct($product_id);
        
        if (!$product) {
            return ['success' => false, 'message' => 'Produit non trouvé'];
        }

        if ($product['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Quantité non disponible en stock'];
        }

        $cart_item_id = $this->generateCartItemId($product_id, $options);
        $cart = &$_SESSION[$this->cart_name];

        if (isset($cart['items'][$cart_item_id])) {
            // Update quantity if item already in cart
            $new_quantity = $cart['items'][$cart_item_id]['quantity'] + $quantity;
            if ($new_quantity > $product['quantity']) {
                return ['success' => false, 'message' => 'Quantité demandée non disponible en stock'];
            }
            $cart['items'][$cart_item_id]['quantity'] = $new_quantity;
        } else {
            // Add new item to cart
            $cart['items'][$cart_item_id] = [
                'product_id' => $product_id,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'options' => $options,
                'image' => $this->getProductImage($product_id)
            ];
        }

        $this->updateCartTotals();
        return ['success' => true, 'cart' => $this->getCart()];
    }

    public function updateItem($cart_item_id, $quantity) {
        if (!isset($_SESSION[$this->cart_name]['items'][$cart_item_id])) {
            return ['success' => false, 'message' => 'Article non trouvé dans le panier'];
        }

        $product_id = $_SESSION[$this->cart_name]['items'][$cart_item_id]['product_id'];
        $product = $this->getProduct($product_id);

        if ($quantity <= 0) {
            return $this->removeItem($cart_item_id);
        }

        if ($product['quantity'] < $quantity) {
            return ['success' => false, 'message' => 'Quantité non disponible en stock'];
        }

        $_SESSION[$this->cart_name]['items'][$cart_item_id]['quantity'] = $quantity;
        $this->updateCartTotals();
        
        return ['success' => true, 'cart' => $this->getCart()];
    }

    public function removeItem($cart_item_id) {
        if (isset($_SESSION[$this->cart_name]['items'][$cart_item_id])) {
            unset($_SESSION[$this->cart_name]['items'][$cart_item_id]);
            $this->updateCartTotals();
            return ['success' => true, 'cart' => $this->getCart()];
        }
        return ['success' => false, 'message' => 'Article non trouvé dans le panier'];
    }

    public function clear() {
        $_SESSION[$this->cart_name] = [
            'items' => [],
            'subtotal' => 0,
            'shipping' => 0,
            'tax' => 0,
            'total' => 0,
            'coupon' => null,
            'discount' => 0,
            'items_count' => 0
        ];
        return true;
    }

    public function getCart() {
        return $_SESSION[$this->cart_name];
    }

    public function getItemsCount() {
        return $_SESSION[$this->cart_name]['items_count'];
    }

    public function getSubtotal() {
        return $_SESSION[$this->cart_name]['subtotal'];
    }

    public function getTotal() {
        return $_SESSION[$this->cart_name]['total'];
    }

    public function applyCoupon($coupon_code) {
        // Vérifier si le coupon est valide
        $coupon = $this->validateCoupon($coupon_code);
        
        if (!$coupon) {
            return ['success' => false, 'message' => 'Coupon invalide ou expiré'];
        }

        $cart = &$_SESSION[$this->cart_name];
        $subtotal = $this->calculateSubtotal();
        
        // Vérifier le montant minimum de commande
        if ($subtotal < $coupon['min_order_amount']) {
            return [
                'success' => false, 
                'message' => sprintf('Ce coupon nécessite un montant minimum de commande de %s', 
                    number_format($coupon['min_order_amount'], 2, ',', ' ')
                )
            ];
        }

        // Calculer la réduction
        if ($coupon['discount_type'] === 'percentage') {
            $discount = ($subtotal * $coupon['discount_value']) / 100;
            
            // Appliquer le montant maximum de réduction si défini
            if ($coupon['max_discount_amount'] > 0 && $discount > $coupon['max_discount_amount']) {
                $discount = $coupon['max_discount_amount'];
            }
        } else {
            $discount = $coupon['discount_value'];
        }

        $cart['coupon'] = [
            'code' => $coupon['code'],
            'discount' => $discount,
            'type' => $coupon['discount_type'],
            'value' => $coupon['discount_value']
        ];

        $cart['discount'] = $discount;
        $this->updateCartTotals();

        return [
            'success' => true, 
            'message' => 'Coupon appliqué avec succès',
            'discount' => $discount,
            'cart' => $this->getCart()
        ];
    }

    public function removeCoupon() {
        if (isset($_SESSION[$this->cart_name]['coupon'])) {
            $_SESSION[$this->cart_name]['coupon'] = null;
            $_SESSION[$this->cart_name]['discount'] = 0;
            $this->updateCartTotals();
            return ['success' => true, 'cart' => $this->getCart()];
        }
        return ['success' => false, 'message' => 'Aucun coupon appliqué'];
    }

    private function updateCartTotals() {
        $cart = &$_SESSION[$this->cart_name];
        $cart['subtotal'] = $this->calculateSubtotal();
        $cart['shipping'] = $this->calculateShipping($cart['subtotal']);
        $cart['tax'] = $this->calculateTax($cart['subtotal']);
        
        // Appliquer la réduction du coupon si elle existe
        $discount = $cart['discount'] ?? 0;
        
        // Calculer le total
        $cart['total'] = max(0, $cart['subtotal'] + $cart['shipping'] + $cart['tax'] - $discount);
        $cart['items_count'] = array_sum(array_column($cart['items'], 'quantity'));
    }

    private function calculateSubtotal() {
        $subtotal = 0;
        foreach ($_SESSION[$this->cart_name]['items'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        return $subtotal;
    }

    private function calculateShipping($subtotal) {
        // Récupérer les frais de port depuis la base de données
        $shipping_cost = $this->getSetting('shipping_cost', 0);
        $free_shipping_threshold = $this->getSetting('free_shipping_threshold', 0);
        
        if ($free_shipping_threshold > 0 && $subtotal >= $free_shipping_threshold) {
            return 0; // Livraison gratuite
        }
        
        return $shipping_cost;
    }

    private function calculateTax($subtotal) {
        $tax_rate = $this->getSetting('tax_rate', 0);
        return ($subtotal * $tax_rate) / 100;
    }

    private function getProduct($product_id) {
        $sql = "SELECT id, name, price, quantity FROM products WHERE id = :id AND is_active = 1";
        $this->db->query($sql, [':id' => $product_id]);
        return $this->db->fetch();
    }

    private function getProductImage($product_id) {
        $sql = "SELECT image_url FROM product_images WHERE product_id = :product_id AND is_primary = 1 LIMIT 1";
        $this->db->query($sql, [':product_id' => $product_id]);
        $result = $this->db->fetch();
        return $result ? $result['image_url'] : 'default-product.jpg';
    }

    private function validateCoupon($coupon_code) {
        $current_date = date('Y-m-d H:i:s');
        
        $sql = "SELECT * FROM discounts 
                WHERE code = :code 
                AND is_active = 1 
                AND (start_date IS NULL OR start_date <= :current_date)
                AND (end_date IS NULL OR end_date >= :current_date)
                AND (usage_limit IS NULL OR usage_count < usage_limit)";
        
        $this->db->query($sql, [
            ':code' => $coupon_code,
            ':current_date' => $current_date
        ]);
        
        return $this->db->fetch();
    }

    private function getSetting($key, $default = null) {
        $sql = "SELECT setting_value FROM settings WHERE setting_key = :key";
        $this->db->query($sql, [':key' => $key]);
        $result = $this->db->fetch();
        return $result ? $result['setting_value'] : $default;
    }

    private function generateCartItemId($product_id, $options) {
        // Générer un identifiant unique pour l'article du panier en fonction de l'ID du produit et des options
        $options_str = !empty($options) ? json_encode($options) : '';
        return md5($product_id . $options_str);
    }
}
?>
