<?php
// =============================================
// ALPHA CONEXÃO - Roteador simples
// =============================================

class Router {
    private array $routes = [];

    public function add(string $method, string $path, callable $handler): void {
        $this->routes[] = compact('method', 'path', 'handler');
    }

    public function get(string $path, callable $handler): void  { $this->add('GET', $path, $handler); }
    public function post(string $path, callable $handler): void { $this->add('POST', $path, $handler); }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $base   = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        $path   = '/' . ltrim(substr($uri, strlen($base)), '/');

        foreach ($this->routes as $route) {
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $route['path']);
            if ($route['method'] === $method && preg_match('#^' . $pattern . '$#', $path, $matches)) {
                array_shift($matches);
                call_user_func_array($route['handler'], $matches);
                return;
            }
        }

        // 404
        http_response_code(404);
        include VIEWS_PATH . '/errors/404.php';
    }
}
