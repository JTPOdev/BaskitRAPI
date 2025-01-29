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

        // Fetch all products
        public static function fetchAll($conn)
        {
            $sql = "SELECT * FROM products";
            $result = $conn->query($sql);
            return $result->fetch_all(MYSQLI_ASSOC);
        }
    
        // Fetch products by category
        public static function fetchByCategory($conn, $category)
        {
            $sql = "SELECT * FROM products WHERE product_category = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $category);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
}
?>
