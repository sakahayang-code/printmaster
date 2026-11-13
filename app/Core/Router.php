<?php
namespace App\Core;

class Router {
    private static array $routes = [];

    public static function get(string $path, $handler, array $middlewares = []): void {
        self::addRoute('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, $handler, array $middlewares = []): void {
        self::addRoute('POST', $path, $handler, $middlewares);
    }

    private static function addRoute(string $method, string $path, $handler, array $middlewares = []): void {
        $path = '/' . trim($path, '/');
        self::$routes[] = [
            'method'      => $method,
            'path'        => $path,
            'handler'     => $handler,
            'middlewares' => $middlewares
        ];
    }

    public static function dispatch(): void {
        $requestMethod = Request::method();
        $requestUri = Request::uri();

        $matchMethod = ($requestMethod === 'HEAD') ? 'GET' : $requestMethod;
        foreach (self::$routes as $route) {
            if ($route['method'] !== $matchMethod) {
                continue;
            }

            // Convert /orders/{id} to regex #^/orders/(?P<id>[^/]+)$#
            $pattern = preg_replace('#\{([a-zA-Z0-9_]+)\}#', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $requestUri, $matches)) {
                $params = [];
                foreach ($matches as $key => $value) {
                    if (is_string($key)) {
                        $params[$key] = $value;
                    }
                }

                // Run middlewares
                foreach ($route['middlewares'] as $middleware) {
                    if (is_string($middleware)) {
                        $mwInstance = new $middleware();
                        $mwInstance->handle();
                    } elseif (is_callable($middleware)) {
                        $middleware();
                    }
                }

                // Call handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$controllerClass, $method] = $handler;
                    $controller = new $controllerClass();
                    call_user_func_array([$controller, $method], $params);
                    return;
                }
            }
        }

        // Not Found
        http_response_code(404);
        if (Request::isAjax()) {
            Response::json(['error' => 'Endpoint tidak ditemukan (404)'], 404);
        } else {
            View::render('errors/404', ['title' => 'Halaman Tidak Ditemukan']);
        }
    }
}
