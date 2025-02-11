<?php 
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../model/Store.php';

class ProductController
{
    public static function create($data, $conn)
    {

        $name = $data['product_name'];
        $price = $data['product_price'];
        $category = $data['product_category'];
        $storeId = $data['store_id'];

        $requiredFields = ['product_name', 'product_price', 'product_category', 'store_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                header('HTTP/1.1 400 Bad Request');
                return ['message' => "Missing required field: $field"];
            }
        }
    
    
        $validCategories = ['STORE', 'FRUITS', 'VEGETABLES', 'MEAT', 'FISH', 'FROZEN', 'SPICES'];
        $validOrigins = ['DAGUPAN', 'CALASIAO'];
    
        if (!in_array($category, $validCategories)) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => "Invalid category: $category"];
        }
    
        $store = Store::getStoreById($conn, $storeId);
        if (!$store) {
            header('HTTP/1.1 404 Not Found');
            return ['message' => 'Invalid store_id. Store does not exist.'];
        }
    
        if (Product::createProduct($conn, $name, $price, $category, $storeId)) {
            header('HTTP/1.1 201 Created');
            return ['message' => 'Product created successfully'];
        }
    
        header('HTTP/1.1 500 Internal Server Error');
        return ['message' => 'Failed to create product'];
    }

    public static function list($conn)
    {
        $products = Product::getProducts($conn);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found'];
    }

    public static function getSpecificProductByid($id, $conn)
    {
        $product = Product::getProductById($id, $conn);
        if ($product) {
            header('HTTP/1.1 200 OK');
            return $product;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'Product not found'];
    }

    //--------- GET ALL PRODUCTS FROM STORE BY CATEGORY---------// 
    public static function getProductsByCategoryFruit($conn, $storeId)
    {
        $products = Product::fetchByCategoryFruit($conn, $storeId);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in FRUITS category'];
    }

    public static function getProductsByCategoryVegetable($conn, $storeId)
    {
        $products = Product::fetchByCategoryVegetable($conn, $storeId);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in VEGETABLES category'];
    }

    public static function getProductsByCategoryMeat($conn, $storeId)
    {
        $products = Product::fetchByCategoryMeat($conn, $storeId);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in MEAT category'];
    }

    public static function getProductsByCategoryFish($conn, $storeId)
    {
        $products = Product::fetchByCategoryFish($conn, $storeId);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in FISH category'];
    }

    public static function getProductsByCategoryFrozen($conn, $storeId)
    {
        $products = Product::fetchByCategoryFrozen($conn, $storeId);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in FROZEN category'];
    }

    public static function getProductsByCategorySpice($conn, $storeId)
    {
        $products = Product::fetchByCategorySpice($conn, $storeId);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in SPICES category'];
    }


    //---------- GET ALL PRODUCTS BY CATEGORY --------//
    public static function getAllProductsByCategoryFruit($conn)
    {
        $products = Product::fetchAllFruitProducts($conn);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in FRUITS category'];
    }

    public static function getAllProductsByCategoryVegetable($conn)
    {
        $products = Product::fetchAllVegetableProducts($conn);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in VEGETABLES category'];
    }

    public static function getAllProductsByCategoryMeat($conn)
    {
        $products = Product::fetchAllMeatProducts($conn);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in MEAT category'];
    }

    public static function getAllProductsByCategoryFish($conn)
    {
        $products = Product::fetchAllFishProducts($conn);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in FISH category'];
    }

    public static function getAllProductsByCategoryFrozen($conn)
    {
        $products = Product::fetchAllFrozenProducts($conn);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in FROZEN category'];
    }

    public static function getAllProductsByCategorySpice($conn)
    {
        $products = Product::fetchAllSpiceProducts($conn);
        if ($products) {
            header('HTTP/1.1 200 OK');
            return $products;
        }
        header('HTTP/1.1 404 Not Found');
        return ['message' => 'No products found in SPICES category'];
    }
}
?>
