<?php
require_once __DIR__ . '/../controller/UserController.php';
require_once __DIR__ . '/../controller/StoreController.php';
require_once __DIR__ . '/../controller/ProductController.php';
require_once __DIR__ . '/../controller/AdminController.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../model/Admin.php';
require_once __DIR__ . '/../model/Store.php';
require_once __DIR__ . '/../model/Product.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// New instance of the database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Router class to manage routes
class Router
{
    private $routes = [];
    private $notFound;

    // Add a route
    public function addRoute($method, $path, $callback, $authRequired = false)
    {
        $this->routes[] = [
            'method' => $method, 
            'path' => $path, 
            'callback' => $callback, 
            'authRequired' => $authRequired
        ];
    }

    // POST route
    public function post($path, $callback, $authRequired = false)
    {
        $this->addRoute('POST', $path, $callback, $authRequired);
    }

    // GET route
    public function get($path, $callback, $authRequired = false)
    {
        $this->addRoute('GET', $path, $callback, $authRequired);
    }

    public function setNotFound($callback)
    {
        $this->notFound = $callback;  
    }

    // Dispatch the request to the correct route handler
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                if ($route['authRequired']) {
                    AuthMiddleware::checkAuth();
                }
                call_user_func($route['callback']);
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


//---------- AUTHENTICATION ----------//
$router->post('/api/auth/register', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $response = UserController::register($data, $conn);
    echo json_encode($response);
});

$router->get('/api/auth/verify_email', function() use ($conn) {
    if (isset($_GET['token'])) {
        $verification_token = $_GET['token'];
        $response = UserController::verifyEmail($verification_token, $conn);
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Verification token missing.']);
    }
});

$router->post('/api/auth/login', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $response = UserController::login($data, $conn);
    echo json_encode($response);
});

$router->post('/api/auth/logout', function() use ($conn) {
    AuthMiddleware::checkAuth();
    $data = json_decode(file_get_contents("php://input"), true);
    $response = UserController::logout($data, $conn);
    echo json_encode($response);
});

//--------- ADMIN AUTHENTICATION ---------//
$router->post('/api/auth/admin/register', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $response = AdminController::register($data, $conn);
    echo json_encode($response);
});

$router->post('/api/auth/admin/login', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $response = AdminController::login($data, $conn);
    echo json_encode($response);
});


//---------- STORE ----------//
$router->post('/api/auth/store/create', function () use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(StoreController::create($data, $conn));
}, true);

$router->get('/api/auth/store/list', function () use ($conn) {
    echo json_encode(StoreController::list($conn));
}, true);


//---------- PRODUCT ----------//
$router->post('/api/auth/product/create', function () use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(ProductController::create($data, $conn));
}, true);

$router->get('/api/auth/product/list', function () use ($conn) {
    echo json_encode(ProductController::list($conn));
}, true);

$router->get('/api/auth/product', function() use ($conn) {
    echo json_encode(ProductController::getAllProducts($conn));
});

$router->get('/api/auth/product/category/{product_category}', function() use ($conn) {
    $category = $_GET['product_category'] ?? '';
    echo json_encode(ProductController::getProductsByCategory($conn, $category));
});

$router->setNotFound(function() {
    echo json_encode(['error' => 'Route not found']);
});

$router->dispatch();
?>
