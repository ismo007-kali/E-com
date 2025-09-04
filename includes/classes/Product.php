<?php
class Product {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getProducts($limit = 12, $offset = 0, $category_id = null, $search = null) {
        $sql = "SELECT p.*, c.name as category_name, 
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1";
        
        $params = [];
        
        if ($category_id) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        
        if ($search) {
            $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql, $params + [
            ':limit' => (int)$limit,
            ':offset' => (int)$offset
        ]);
        
        return $this->db->fetchAll();
    }

    public function getProductById($id) {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.id = :id AND p.is_active = 1";
        
        $this->db->query($sql, [':id' => $id]);
        $product = $this->db->fetch();
        
        if ($product) {
            $product['images'] = $this->getProductImages($id);
        }
        
        return $product;
    }

    public function getProductImages($product_id) {
        $sql = "SELECT * FROM product_images 
                WHERE product_id = :product_id 
                ORDER BY is_primary DESC, sort_order ASC";
        
        $this->db->query($sql, [':product_id' => $product_id]);
        return $this->db->fetchAll();
    }

    public function getFeaturedProducts($limit = 4) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p 
                WHERE p.is_featured = 1 AND p.is_active = 1 
                ORDER BY p.created_at DESC 
                LIMIT :limit";
        
        $this->db->query($sql, [':limit' => (int)$limit]);
        return $this->db->fetchAll();
    }

    public function getRelatedProducts($product_id, $category_id, $limit = 4) {
        $sql = "SELECT p.*, 
                (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as primary_image
                FROM products p 
                WHERE p.category_id = :category_id 
                AND p.id != :product_id 
                AND p.is_active = 1
                ORDER BY RAND() 
                LIMIT :limit";
        
        $this->db->query($sql, [
            ':category_id' => $category_id,
            ':product_id' => $product_id,
            ':limit' => (int)$limit
        ]);
        
        return $this->db->fetchAll();
    }

    public function getCategories($parent_id = null) {
        $sql = "SELECT * FROM categories WHERE is_active = 1";
        $params = [];
        
        if ($parent_id === null) {
            $sql .= " AND parent_id IS NULL";
        } else {
            $sql .= " AND parent_id = :parent_id";
            $params[':parent_id'] = $parent_id;
        }
        
        $sql .= " ORDER BY name ASC";
        
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    public function getCategoryBySlug($slug) {
        $sql = "SELECT * FROM categories WHERE slug = :slug AND is_active = 1";
        $this->db->query($sql, [':slug' => $slug]);
        return $this->db->fetch();
    }

    public function getProductsByCategory($category_id, $limit = 12, $offset = 0) {
        return $this->getProducts($limit, $offset, $category_id);
    }

    public function searchProducts($query, $limit = 12, $offset = 0) {
        return $this->getProducts($limit, $offset, null, $query);
    }

    public function countProducts($category_id = null, $search = null) {
        $sql = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
        $params = [];
        
        if ($category_id) {
            $sql .= " AND category_id = :category_id";
            $params[':category_id'] = $category_id;
        }
        
        if ($search) {
            $sql .= " AND (name LIKE :search OR description LIKE :search)";
            $params[':search'] = "%$search%";
        }
        
        $this->db->query($sql, $params);
        $result = $this->db->fetch();
        return $result ? (int)$result['total'] : 0;
    }
}
?>
