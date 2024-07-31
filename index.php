<?php
declare(strict_types=1);

namespace ModernPhpExample;

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'ModernPhpExample\\';
    $base_dir = __DIR__ . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Enable error reporting for development
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Initialize router and database
$router = new Router();
$db = new Database();
$apiController = new ApiController($db);

// Define routes
$router->addRoute('GET', '/api/users', [$apiController, 'getUsers']);
$router->addRoute('POST', '/api/users', [$apiController, 'addUser']);
$router->addRoute('GET', '/', function() {
    include __DIR__ . '/views/home.php';
});

// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uri);
