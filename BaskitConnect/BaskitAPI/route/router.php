<?php
require_once __DIR__ . '/../controller/UserController.php';
require_once __DIR__ . '/../controller/StoreController.php';
require_once __DIR__ . '/../controller/ProductController.php';
require_once __DIR__ . '/../controller/CartController.php';
require_once __DIR__ . '/../controller/AdminController.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Admin.php';
require_once __DIR__ . '/../model/Store.php';
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../model/Cart.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

$conn = Database::getConnection();

class Router
{
    private $routes = [];
    private $notFound;

    public function addRoute($method, $path, $callback, $authRequired = false, $isAdminRoute = false)
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'callback' => $callback,
            'authRequired' => $authRequired,
            'isAdminRoute' => $isAdminRoute
        ];
    }

    public function post($path, $callback, $authRequired = false, $isAdminRoute = false)
    {
        $this->addRoute('POST', $path, $callback, $authRequired, $isAdminRoute);
    }

    public function get($path, $callback, $authRequired = false, $isAdminRoute = false)
    {
        $this->addRoute('GET', $path, $callback, $authRequired, $isAdminRoute);
    }

    public function put($path, $callback, $authRequired = false, $isAdminRoute = false)
    {
        $this->addRoute('PUT', $path, $callback, $authRequired, $isAdminRoute);
    }

    public function delete($path, $callback, $authRequired = false, $isAdminRoute = false)
    {
        $this->addRoute('DELETE', $path, $callback, $authRequired, $isAdminRoute);
    }

    public function setNotFound($callback)
    {
        $this->notFound = $callback;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path from URI
        $basePath = '/BaskitConnect/BaskitAPI/public';
        $uri = str_replace($basePath, '', $uri);
        
        // Log for debugging
        error_log("Method: " . $method);
        error_log("URI after base path removal: " . $uri);

        foreach ($this->routes as $route) {
            $pattern = $route['path'];
            // Convert route parameters to regex pattern
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $pattern);
            $pattern = "#^" . $pattern . "$#";
            
            error_log("Checking pattern: " . $pattern . " against URI: " . $uri);

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                
                if ($route['authRequired']) {
                    AuthMiddleware::checkAuth($route['isAdminRoute']);
                }

                call_user_func_array($route['callback'], $matches);
                return;
            }
        }

        header("HTTP/1.1 404 Not Found");
        echo json_encode(['message' => 'Route not found', 'requested_path' => $uri]);
    }
}

$router = new Router();


// ---------- USER AUTHENTICATION ---------- //

// USER REGISTER AND TAGABILI
$router->post('/user/register', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(UserController::register($data, $conn));
});

// USER VERIFY BUT RATHER USE EMAIL
$router->get('/user/verify_email', function() use ($conn) {
    $verification_token = $_GET['token'] ?? null;
    echo json_encode($verification_token
        ? UserController::verifyEmail($verification_token, $conn)
        : ['error' => 'Verification token missing.']
    );
});

// USER LOGIN
$router->post('/user/login', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(UserController::login($data, $conn));
});

// USER LOGOUT
$router->post('/user/logout', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(UserController::logout($data, $conn));
});

// USER RESET PASSWORD
$router->post('/user/resetPassword', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(UserController::resetPassword($data, $conn));
},true, false);

$router->get('/user/details', function() use ($conn) {
    header('Content-Type: application/json');
    $userId = AuthMiddleware::checkAuth();
    echo json_encode(User::getUserDetails($conn, $userId));
}, true, false);



 

// ---------- ADMIN AUTHENTICATION ---------- //

// ADMIN UPDATE CREDENTIALS BASICALLY NEW USERNAME, NEW PASSWORD
$router->post('/admin/changeCredentials', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(AdminController::changeCredentials($data, $conn));
}, false, true);

// ADMIN LOGIN
$router->post('/admin/login', function() use ($conn) {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        http_response_code(400);
        echo json_encode(['message' => 'Invalid request data']);
        return;
    }
    
    echo json_encode(AdminController::login($data, $conn));
});

// ADMIN LOGOUT
$router->post('/admin/logout', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(AdminController::logout($data, $conn));
});


// ---------- STORE ---------- //

// CREATE STORE REQUEST
$router->post('/store/create', function() use ($conn) {
    header('Content-Type: application/json');
    $authUserId = AuthMiddleware::checkAuth();
    echo json_encode(StoreController::create($authUserId, $_POST, $conn));
}, true, false);

// GET ALL STORE REQUEST
$router->get('/store/request/all', function() use ($conn) {
    header('Content-Type: application/json');
    echo json_encode(Store::getAllStoreRequests($conn));
});

// GET STORE REQUESTS BY ID
$router->get('/store/request/{id}', function($id) use ($conn) {
    header('Content-Type: application/json');
    echo json_encode(Store::getStoreRequestById($conn, $id));
});

// APPROVE A STORE REQUEST
$router->post('/store/approve/{id}', function($id) use ($conn) {
    header('Content-Type: application/json');
    echo json_encode(StoreController::approve($id, $conn));
});

// DECLINE A STORE REQUEST
$router->post('/store/decline/{id}', function($id) use ($conn) {
    header('Content-Type: application/json');
    echo json_encode(StoreController::decline($id, $conn));
});

// GET ALL STORES. APPROVED STORES
$router->get('/store/all', function() use ($conn) {
    $adminId = AuthMiddleware::checkAuth();
    echo json_encode(StoreController::list($conn));
});

// GET ALL STORES BY ORIGIN
$router->get('/store/all/{origin}', function($origin) use ($conn) {
    header('Content-Type: application/json');
    echo json_encode(StoreController::listByOrigin($origin, $conn));
});

// ADD STORE IMAGE
$router->post('/store/image/{id}', function($id) use ($conn) {
    header('Content-Type: application/json');
    echo json_encode(StoreController::uploadStoreImage($id, $conn));
});

// DELETE STORE
$router->delete('/store/delete/{id}', function($storeId) use ($conn) {
    header('Content-Type: application/json');
    echo json_encode(Store::deleteStore($conn, $storeId));
});

// ---------- PRODUCT ---------- //
$router->post('/product/create', function() use ($conn) {
    header('Content-Type: application/json'); 
    echo json_encode(ProductController::create($_POST, $conn));
}, true, false);
    
$router->get('/product/list', function() use ($conn) {
    echo json_encode(ProductController::list($conn));
}, true, false);

$router->get('/product/specific/{id}', function($id) use ($conn) {
    echo json_encode(ProductController::getSpecificProductByid($id, $conn));
},true, false);

$router->delete('/product/delete/{id}', function($id) use ($conn) {
    header('Content-Type: application/json');

    if (!isset($id) || empty($id)) {
        http_response_code(400);
        echo json_encode(['message' => 'Product ID is required']);
        return;
    }
    echo json_encode(ProductController::delete($id, $conn));
},true, false);


// ---------- PRODUCT CATEGORIES ---------- //
$categories = ['fruit', 'vegetable', 'meat', 'fish', 'frozen', 'spice'];
foreach ($categories as $category) {
    $router->get("/product/category/{$category}/{storeId}", function($storeId) use ($conn, $category) {
        $method = 'getProductsByCategory' . ucfirst($category);
        echo json_encode(ProductController::$method($conn, $storeId));
    });
}

foreach ($categories as $category) {
    $router->get("/product/category/{$category}", function() use ($conn, $category) {
        $method = 'getAllProductsByCategory' . ucfirst($category);
        echo json_encode(ProductController::$method($conn));
    });
}

// ---------- CART ---------- //
$router->post('/cart/add', function() use ($conn) {
    $authUserId = AuthMiddleware::checkAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::addToCart($authUserId, $data, $conn));
}, false, true);

$router->get('/cart/view/{user_id}', function() use ($conn) {
    $authUserId = AuthMiddleware::checkAuth();
    echo json_encode(CartController::viewCart($authUserId,$conn));
}, false, true);

$router->put('/cart/update', function() use ($conn) {
    $authUserId = AuthMiddleware::checkAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::updateCart($authUserId, $data, $conn));
},false, true);

$router->delete('/cart/remove', function() use ($conn) {
    $authUserId = AuthMiddleware::checkAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::removeFromCart($authUserId, $data, $conn));
},false, true);


// ---------- 404 NOT FOUND ---------- //
$router->setNotFound(function() {
    echo json_encode(['message' => 'Route not found']);
});

$router->dispatch();
?>
