<?php

class Product
{
    public static function createProduct($conn, $name, $price, $description, $category, $origin, $rating, $storeId)
    {
        $storeQuery = "SELECT store_name, store_phone_number FROM stores WHERE id = ?";
        $stmtStore = $conn->prepare($storeQuery);
        $stmtStore->bind_param("i", $storeId);
        $stmtStore->execute();
        $storeResult = $stmtStore->get_result();
        $store = $storeResult->fetch_assoc();
    
        if (!$store) {
            return false;
        }
    
        $storeName = $store['store_name'];
        $storePhone = $store['store_phone_number'];
    
        $sql = "INSERT INTO products (product_name, product_price, product_description, product_category, product_origin, product_rating, store_id, store_name, store_phone_number)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdsssisss", $name, $price, $description, $category, $origin, $rating, $storeId, $storeName, $storePhone);
        return $stmt->execute();
    }

    public static function getProducts($conn)
    {
        $sql = "SELECT p.*, s.store_name, s.store_phone_number 
                FROM products p 
                JOIN stores s ON p.store_id = s.id";
        $result = $conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public static function getProductById($id, $conn) {
        $sql = "SELECT p.*, s.store_name, s.store_phone_number 
                FROM products p 
                JOIN stores s ON p.store_id = s.id
                WHERE p.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $product = $result->fetch_assoc();
        
        if ($product) {
            return $product;
        } else {
            return ['error' => 'Product not found'];
        }
    }
    
    //--------- CATEGORY ---------// (FETCHING ALL PRODUCTS FROM EACH CATEGORY IN ALL STORES )
    public static function fetchByCategoryFruit($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FRUITS'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategoryVegetable($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'VEGETABLES'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategoryMeat($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'MEAT'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategoryFish($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FISH'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategoryFrozen($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FROZEN'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategorySpice($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'SPICES'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
