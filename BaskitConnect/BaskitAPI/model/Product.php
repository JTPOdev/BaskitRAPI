<?php

class Product
{
    public static function createProduct($conn, $name, $price, $category, $storeId, $productImage)
    {
        $storeQuery = "SELECT store_name, owner_name, store_address, store_phone_number, store_origin FROM stores WHERE id = ?";   
        $stmtStore = $conn->prepare($storeQuery);
        $stmtStore->bind_param("i", $storeId);
        $stmtStore->execute();
        $storeResult = $stmtStore->get_result();
        $store = $storeResult->fetch_assoc();
    
        if (!$store) {
            return false;
        }
    
        $storeName = $store['store_name'];
        $ownerName = $store['owner_name'];
        $storePhone = $store['store_phone_number'];
        $storeOrigin = $store['store_origin'];
        $storeAddress = $store['store_address'];
  
        $sql = "INSERT INTO products (product_name, product_price, product_category, product_origin, store_id, store_name, owner_name, store_address, store_phone_number, product_image)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssisssss", $name, $price, $category, $storeOrigin, $storeId, $storeName, $ownerName, $storeAddress, $storePhone, $productImage);
        return $stmt->execute();
    }

    public static function getProducts($conn)
    {
        $sql = "SELECT p.*, s.store_name 
                FROM products p 
                JOIN stores s ON p.store_id = s.id 
                ORDER BY p.product_category, p.product_name";
                
        $result = $conn->query($sql);
        if (!$result) {
            return false;
        }
        
        $products = $result->fetch_all(MYSQLI_ASSOC);
        error_log("Retrieved products: " . json_encode($products));
        return $products;
    }
    
    public static function getProductById($id, $conn) {
        $sql = "SELECT p.*, s.store_name, s.owner_name, s.store_address, s.store_phone_number 
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
            return ['message' => 'Product not found'];
        }
    }

    public static function deleteProduct($conn, $productId)
{
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $productId);
    
    if ($stmt->execute()) {
        return true;
    } else {
        return false;
    }
}
    
    //--------- FETCH ALL PRODUCTS FROM STORE BY CATEGORY---------// 
    public static function fetchByCategoryFruit($conn, $storeId)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FRUITS' AND store_id = ?" ;
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategoryVegetable($conn, $storeId)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'VEGETABLES' AND store_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategoryMeat($conn, $storeId)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'MEAT' AND store_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategoryFish($conn, $storeId)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FISH' AND store_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategoryFrozen($conn, $storeId)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FROZEN' AND store_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchByCategorySpice($conn, $storeId)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'SPICES' AND store_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $storeId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    //---------- FETCH ALL PRODUCTS BY CATEGORY --------//
    public static function fetchAllFruitProducts($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FRUITS'" ;
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchAllVegetableProducts($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'VEGETABLES'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchAllMeatProducts($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'MEAT'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchAllFishProducts($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FISH'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchAllFrozenProducts($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'FROZEN'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchAllSpiceProducts($conn)
    {
        $sql = "SELECT * FROM products WHERE product_category = 'SPICES'";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
