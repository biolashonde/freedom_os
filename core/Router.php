<?php
declare(strict_types=1);

class Router
{
    private array $routes = [];

    public function get(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->routes[] = ['GET', $path, $handler, $middleware];
    }

    public function post(string $path, array|callable $handler, array $middleware = []): void
    {
        $this->routes[] = ['POST', $path, $handler, $middleware];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $basePath = function_exists('app_base_path') ? app_base_path() : '';
        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath)) ?: '/';
        }

        foreach ($this->routes as [$routeMethod, $path, $handler, $middleware]) {
            $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $path) . '$#';
            if ($method !== $routeMethod || !preg_match($pattern, $uri, $matches)) {
                continue;
            }

            foreach ($middleware as $class) {
                (new $class())->handle();
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            if (is_callable($handler)) {
                $handler(...array_values($params));
                return;
            }

            [$class, $action] = $handler;
            (new $class())->$action(...array_values($params));
            return;
        }

        http_response_code(404);
        view('errors/404', ['title' => 'Page not found']);
    }
}
