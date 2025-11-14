<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f5f5;
            text-align: center;
            padding: 50px;
        }
        .error-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        h1 {
            color: #e74c3c;
            margin-bottom: 20px;
        }
        .home-link {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404 - Page Not Found</h1>
        <p>The page you are looking for does not exist or has been moved.</p>
        <p><strong>Attempted page:</strong> <?php echo htmlspecialchars($_GET['page'] ?? 'unknown'); ?></p>
        <a href="?page=home" class="home-link">Return to Home Page</a>
    </div>
</body>
</html><?php
class Router {
    private $routes = [];
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
        $this->setupRoutes();
    }
    
    private function setupRoutes() {
        $this->routes = [
            'home' => ['controller' => 'HomeController', 'method' => 'index', 'auth' => false],
            'category' => ['controller' => 'CategoryController', 'method' => 'show', 'auth' => false],
            'listing' => ['controller' => 'ListingController', 'method' => 'show', 'auth' => false],
            'search' => ['controller' => 'SearchController', 'method' => 'index', 'auth' => false],
            'submit' => ['controller' => 'ListingController', 'method' => 'create', 'auth' => 'user'],
            'login' => ['controller' => 'UserController', 'method' => 'login', 'auth' => false],
            'register' => ['controller' => 'UserController', 'method' => 'register', 'auth' => false],
            'profile' => ['controller' => 'UserController', 'method' => 'profile', 'auth' => 'user'],
            'contact' => ['controller' => 'ContactController', 'method' => 'index', 'auth' => false],
            'about' => ['controller' => 'HomeController', 'method' => 'about', 'auth' => false],
            'logout' => ['controller' => 'UserController', 'method' => 'logout', 'auth' => false],
            'staff' => ['controller' => 'UserController', 'method' => 'staffLogin', 'auth' => false],
            'forgot-password' => ['controller' => 'AuthController', 'method' => 'forgotPassword', 'auth' => false],
            'reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword', 'auth' => false]
        ];
    }
    
    public function handleRequest() {
        $page = $_GET['page'] ?? 'home';
        
        // ✅ ROUTE WHITELISTING: Only allow predefined routes
        if (!isset($this->routes[$page])) {
            http_response_code(404);
            $this->show404Error();
            return;
        }
        
        $route = $this->routes[$page];
        
        // Check authentication
        if ($route['auth'] === 'user' && !SessionManager::isLoggedIn()) {
            SessionManager::setMessage('error', 'Please login to access this page');
            redirect('?page=login');
            return;
        }
        
        if ($route['auth'] === 'admin' && !SessionManager::isAdmin()) {
            SessionManager::setMessage('error', 'Admin access required');
            redirect('?page=login');
            return;
        }
        
        $controllerName = $route['controller'];
        $method = $route['method'];
        
        // Load controller file
        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';
        if (!file_exists($controllerFile)) {
            $this->show404Error();
            return;
        }
        
        require_once $controllerFile;
        
        // Create controller instance and call method
        $controller = new $controllerName($this->db);
        $controller->$method();
    }
    
    private function show404Error() {
        // Check if 404 template exists, otherwise show basic error
        $errorPage = __DIR__ . '/../views/errors/404.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo '<h1>404 - Page Not Found</h1>';
            echo '<p>The page you requested does not exist.</p>';
            echo '<a href="?page=home">Return to Home Page</a>';
        }
        exit;
    }
}
?>