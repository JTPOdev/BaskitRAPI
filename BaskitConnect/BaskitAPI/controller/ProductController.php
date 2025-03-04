<?php 
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../model/Store.php';

class ProductController
{
    public static function create($data, $conn)
    {
        header('Content-Type: application/json');
        $name = $data['product_name'];
        $price = $data['product_price'];
        $category = strtoupper($data['product_category']);
        $storeId = $data['store_id'];

        $requiredFields = ['product_name', 'product_price', 'product_category', 'store_id'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                http_response_code(400);
                return ['message' => "Missing required field: $field"];
            }
        }
    
    
        $validCategories = ['FRUITS', 'VEGETABLES', 'MEAT', 'FISH', 'FROZEN', 'SPICES'];
        $validOrigins = ['DAGUPAN', 'CALASIAO'];
    
        if (!in_array($category, $validCategories)) {
            http_response_code(400);
            return ['message' => "Invalid category: $category. Valid categories are: " . implode(', ', $validCategories)];
        }
    
        $store = Store::getStoreById($conn, $storeId);
        if (!$store) {
            http_response_code(404);
            return ['message' => 'Invalid store_id. Store does not exist.'];
        }

        $productImgPath = self::uploadFile($_FILES['product_image'] ?? null, 'product_images');

        if (isset($productImgPath['error'])) {
            header('HTTP/1.1 400 Bad Request');
            return ['message' => 'File upload failed'];
        }

        $success = Product::createProduct(
            $conn,
            $name,
            $price,
            $category,
            $storeId,
            $productImgPath['success']
        );

        if ($success) {
            header('HTTP/1.1 201 Created');
            return ['message' => 'Product created Successfully.'];
        }
    
        http_response_code(500);
        return ['message' => 'Failed to create product'];
    }

    public static function list($conn)
    {
        header('Content-Type: application/json');
        
        $products = Product::getProducts($conn);
        if ($products) {
            http_response_code(200);
            error_log('Products retrieved: ' . json_encode($products));
            return $products;
        }
        
        http_response_code(404);
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

    public static function delete($productId, $conn)
    {
        if (!isset($productId) || empty($productId)) {
            http_response_code(400);
            return ['message' => 'Product ID is required'];
        }

        $product = Product::getProductById($productId, $conn);
        if (!$product || isset($product['message'])) {
            http_response_code(404);
            return ['message' => 'Product not found'];
        }

        if (Product::deleteProduct($conn, $productId)) {
            http_response_code(200);
            return ['message' => 'Product deleted successfully'];
        }

        http_response_code(500);
        return ['message' => 'Failed to delete product'];
    }

    // --------- UPLOAD FILES -------- //
    private static function uploadFile($file, $folder)
    {
        if (!$file || empty($file['tmp_name'])) {
            return ['error' => 'No file uploaded'];
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $fileType = mime_content_type($file['tmp_name']);
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($fileType, $allowedTypes)) {
            return ['error' => 'Invalid file type. Allowed: JPG, PNG, PDF'];
        }

        $uploadDir = __DIR__ . "/../uploads/$folder/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid() . "_" . basename($file['name']);
        $targetPath = $uploadDir . $filename;

        return move_uploaded_file($file['tmp_name'], $targetPath) ?
            ['success' => "/uploads/$folder/" . $filename] :
            ['error' => 'Failed to upload file'];
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
