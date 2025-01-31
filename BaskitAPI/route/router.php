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

    // PUT route
    public function put($path, $callback, $authRequired = false)
    {
        $this->addRoute('PUT', $path, $callback, $authRequired);
    }

    // DELETE route
    public function delete($path, $callback, $authRequired = false)
    {
        $this->addRoute('DELETE', $path, $callback, $authRequired);
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
            $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route['path']);
            $pattern = "#^$pattern$#";

            if ($route['method'] === $method && preg_match($pattern, $uri, $matches)) {
                $params = array_slice($matches, 1); // Extract dynamic params

                if ($route['authRequired']) {
                    AuthMiddleware::checkAuth();
                }

                // Call the callback function, passing extracted params
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

$router->get('/api/auth/product/specific/{id}', function ($id) use ($conn) {
    echo json_encode(ProductController::getSpecificProductByid($id, $conn));
}, true);

//---------- CETEGORY ----------//
$router->get('/api/auth/product/category/fruit', function() use ($conn) {
    echo json_encode(ProductController::getProductsByCategoryFruit($conn));
});

$router->get('/api/auth/product/category/vegetable', function() use ($conn) {
    echo json_encode(ProductController::getProductsByCategoryVegetable($conn));
});

$router->get('/api/auth/product/category/meat', function() use ($conn) {
    echo json_encode(ProductController::getProductsByCategoryMeat($conn));
});

$router->get('/api/auth/product/category/fish', function() use ($conn) {
    echo json_encode(ProductController::getProductsByCategoryFish($conn));
});

$router->get('/api/auth/product/category/frozen', function() use ($conn) {
    echo json_encode(ProductController::getProductsByCategoryFrozen($conn));
});

$router->get('/api/auth/product/category/spice', function() use ($conn) {
    echo json_encode(ProductController::getProductsByCategorySpice($conn));
});

//---------- CART ----------//
$router->post('/api/auth/cart/add', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::addToCart($data, $conn));
}, true);

$router->get('/api/auth/cart/view/{user_id}', function($user_id) use ($conn) {
    echo json_encode(CartController::viewCart($user_id, $conn));
}, true);

$router->put('/api/auth/cart/update', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::updateCart($data, $conn));
}, true);

$router->delete('/api/auth/cart/remove', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    echo json_encode(CartController::removeFromCart($data, $conn));
}, true);

$router->setNotFound(function() {
    echo json_encode(['error' => 'Route not found']);
});

$router->dispatch();
?>
