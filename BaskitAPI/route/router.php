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

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route['path']);
            $pattern = "#^$pattern$#";

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                $params = array_slice($matches, 1);

                if ($route['authRequired']) {
                    AuthMiddleware::checkAuth($route['isAdminRoute']);
                }

                call_user_func_array($route['callback'], $params);
                return;
            }
        }

        if (isset($this->notFound)) {
            header("HTTP/1.1 404 Not Found");
            call_user_func($this->notFound);
        }
    }
}

$router = new Router();


// ---------- AUTHENTICATION ---------- //
$router->post('/api/auth/register', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(UserController::register($data, $conn));
});

$router->get('/api/auth/verify_email', function() use ($conn) {
    $verification_token = $_GET['token'] ?? null;
    echo json_encode($verification_token
        ? UserController::verifyEmail($verification_token, $conn)
        : ['error' => 'Verification token missing.']
    );
});

$router->post('/api/auth/login', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(UserController::login($data, $conn));
});

$router->post('/api/auth/logout', function() use ($conn) {
    AuthMiddleware::checkAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(UserController::logout($data, $conn));
},true,false);


// ---------- ADMIN AUTH ---------- //
$router->post('/api/auth/admin/register', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(AdminController::register($data, $conn));
}, false, true);

$router->post('/api/auth/admin/login', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(AdminController::login($data, $conn));
});


// ---------- STORE ---------- //
$router->post('/api/auth/store/create', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(StoreController::create($data, $conn));
}, true, true);

$router->get('/api/auth/store/list', function() use ($conn) {
    echo json_encode(StoreController::list($conn));
}, false);


// ---------- PRODUCT ---------- //
$router->post('/api/auth/product/create', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(ProductController::create($data, $conn));
}, true, true);
    
$router->get('/api/auth/product/list', function() use ($conn) {
    echo json_encode(ProductController::list($conn));
}, true);

$router->get('/api/auth/product/specific/{id}', function($id) use ($conn) {
    echo json_encode(ProductController::getSpecificProductByid($id, $conn));
}, false);


// ---------- PRODUCT CATEGORIES ---------- //
$categories = ['fruit', 'vegetable', 'meat', 'fish', 'frozen', 'spice'];
foreach ($categories as $category) {
    $router->get("/api/auth/product/category/{$category}", function() use ($conn, $category) {
        $method = 'getProductsByCategory' . ucfirst($category);
        echo json_encode(ProductController::$method($conn));
    });
}


// ---------- CART ---------- //
$router->post('/api/auth/cart/add', function() use ($conn) {
    $authUserId = AuthMiddleware::checkAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::addToCart($authUserId, $data, $conn));
}, false, true);

$router->get('/api/auth/cart/view/{user_id}', function() use ($conn) {
    $authUserId = AuthMiddleware::checkAuth();
    echo json_encode(CartController::viewCart($authUserId,$conn));
}, false, true);

$router->put('/api/auth/cart/update', function() use ($conn) {
    $authUserId = AuthMiddleware::checkAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::updateCart($authUserId, $data, $conn));
},false, true);

$router->delete('/api/auth/cart/remove', function() use ($conn) {
    $authUserId = AuthMiddleware::checkAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::removeFromCart($authUserId, $data, $conn));
},false, true);


// ---------- 404 NOT FOUND ---------- //
$router->setNotFound(function() {
    echo json_encode(['error' => 'Route not found']);
});

$router->dispatch();
?>
