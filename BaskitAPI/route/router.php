<?php
require_once __DIR__ . '/../controller/UserController.php';
require_once __DIR__ . '/../model/User.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

// Create a new instance of the database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create a simple Router class to manage routes
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
            'authRequired' => $authRequired // Whether this route requires authentication
        ];
    }

    // Define a POST route
    public function post($path, $callback, $authRequired = false)
    {
        $this->addRoute('POST', $path, $callback, $authRequired);
    }

    // Define a GET route
    public function get($path, $callback, $authRequired = false)
    {
        $this->addRoute('GET', $path, $callback, $authRequired);
    }

    // Set route handler for 404
    public function setNotFound($callback)
    {
        $this->notFound = $callback;  
    }

    // Dispatch the request to the correct route handler
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // Handle URI without query params

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $uri) {
                // If authentication is required, check if the user is authenticated
                if ($route['authRequired']) {
                    AuthMiddleware::checkAuth(); // Check authorization before processing
                }
                call_user_func($route['callback']);
                return;
            }
        }

        // If no matching route was found, invoke the 404 handler
        if (isset($this->notFound)) {
            header("HTTP/1.1 404 Not Found");
            call_user_func($this->notFound);
        }
    }
}

// Instantiate the router
$router = new Router();

// Define the registration route (no authentication required)
$router->post('/api/auth/register', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $response = UserController::register($data, $conn);
    echo json_encode($response);
});

// Define the email verification route (no authentication required)
$router->get('/api/auth/verify_email', function() use ($conn) {
    if (isset($_GET['token'])) {
        $verification_token = $_GET['token'];
        $response = UserController::verifyEmail($verification_token, $conn);
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Verification token missing.']);
    }
});

// Define the login route (no authentication required)
$router->post('/api/auth/login', function() use ($conn) {
    $data = json_decode(file_get_contents("php://input"), true);
    $response = UserController::login($data, $conn);
    echo json_encode($response);
});

$router->post('/api/auth/logout', function() use ($conn) {
    // Check if the user is authenticated
    AuthMiddleware::checkAuth();

    $data = json_decode(file_get_contents("php://input"), true);
    $response = UserController::logout($data, $conn);
    echo json_encode($response);
});

// Route for API not found
$router->setNotFound(function() {
    echo json_encode(['error' => 'Route not found']);
});

// Dispatch the route
$router->dispatch();
?>
