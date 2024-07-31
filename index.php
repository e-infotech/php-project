<?php
declare(strict_types=1);

namespace ModernPhpExample;

use PDO;
use PDOException;

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

// Router
class Router {
    private array $routes = [];

    public function addRoute(string $method, string $path, callable $handler): void {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $method, string $uri): void {
        if (isset($this->routes[$method][$uri])) {
            $handler = $this->routes[$method][$uri];
            $handler();
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}

// Database
class Database {
    private PDO $pdo;

    public function __construct() {
        try {
            $this->pdo = new PDO('sqlite:' . __DIR__ . '/database.sqlite');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initDatabase();
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    private function initDatabase(): void {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE
        )");
    }

    public function getUsers(): array {
        $stmt = $this->pdo->query("SELECT * FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addUser(string $name, string $email): int {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
        $stmt->execute([$name, $email]);
        return (int) $this->pdo->lastInsertId();
    }
}

// API Controller
class ApiController {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function getUsers(): void {
        $users = $this->db->getUsers();
        $this->jsonResponse($users);
    }

    public function addUser(): void {
        $data = json_decode(file_get_contents('php://input'), true);
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        
        if (empty($name) || empty($email)) {
            $this->jsonResponse(['error' => 'Name and email are required'], 400);
            return;
        }

        $id = $this->db->addUser($name, $email);
        $this->jsonResponse(['id' => $id, 'name' => $name, 'email' => $email], 201);
    }

    private function jsonResponse(array $data, int $statusCode = 200): void {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
    }
}

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
