<?php 
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../model/Store.php';

class ProductController
{
    public static function create($data, $conn)
    {
        $requiredFields = ['product_name', 'product_price', 'product_description', 'product_category', 'product_origin', 'product_rating', 'store_id'];
    
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return ['error' => "Missing required field: $field"];
            }
        }
    
        $name = $data['product_name'];
        $price = $data['product_price'];
        $description = $data['product_description'];
        $category = $data['product_category'];
        $origin = $data['product_origin'];
        $rating = $data['product_rating'];
        $storeId = $data['store_id'];
    
        $validCategories = ['STORE', 'VEGETABLES', 'MEAT', 'FISH', 'FROZEN', 'SPICES', 'FRUITS'];
        $validOrigins = ['DAGUPAN', 'CALASIAO'];
    
        if (!in_array($category, $validCategories)) {
            return ['error' => "Invalid category: $category"];
        }
    
        if (!in_array($origin, $validOrigins)) {
            return ['error' => "Invalid origin: $origin"];
        }
    
        $store = Store::getStoreById($conn, $storeId);
        if (!$store) {
            return ['error' => 'Invalid store_id. Store does not exist.'];
        }
    
        if (Product::createProduct($conn, $name, $price, $description, $category, $origin, $rating, $storeId)) {
            return ['success' => 'Product created successfully'];
        }
    
        return ['error' => 'Failed to create product'];
    }

    public static function list($conn)
    {
        return Product::getProducts($conn);
    }

    public static function getAllProducts($conn) {
        return Product::fetchAll($conn);
    }

    public static function getProductsByCategory($conn, $category) {
        return Product::fetchByCategory($conn, $category);
    }

}
?>