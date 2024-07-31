<?php
namespace ModernPhpExample;

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
