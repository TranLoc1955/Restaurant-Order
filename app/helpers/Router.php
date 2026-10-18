<?php
/**
 * Router Engine
 * Bộ định tuyến HTTP hỗ trợ GET/POST/API, route parameters {id}, middleware và fallback
 */

class Router {
    private static array $routes = [];

    /**
     * Đăng ký route GET
     */
    public static function get(string $path, array|callable $handler, array $middleware = []): void {
        self::addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Đăng ký route POST
     */
    public static function post(string $path, array|callable $handler, array $middleware = []): void {
        self::addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Thêm route vào bảng định tuyến
     */
    private static function addRoute(string $method, string $path, array|callable $handler, array $middleware): void {
        $path = '/' . trim($path, '/');
        self::$routes[] = [
            'method'     => $method,
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => $middleware,
        ];
    }

    /**
     * Phân giải URI hiện tại từ request
     */
    public static function resolveUri(): string {
        // 1. Kiểm tra tham số fallback ?route=
        if (!empty($_GET['route'])) {
            return '/' . trim($_GET['route'], '/');
        }

        // 2. Kiểm tra PATH_INFO
        if (!empty($_SERVER['PATH_INFO'])) {
            return '/' . trim($_SERVER['PATH_INFO'], '/');
        }

        // 3. Phân giải từ REQUEST_URI loại bỏ Query String và Script Directory
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $uri = explode('?', $uri)[0];

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = dirname($scriptName);

        // Chuẩn hóa dấu gạch chéo
        $baseDir = str_replace('\\', '/', $baseDir);
        if ($baseDir !== '/' && str_starts_with($uri, $baseDir)) {
            $uri = substr($uri, strlen($baseDir));
        }

        // Loại bỏ 'index.php' nếu còn sót
        if (str_starts_with($uri, '/index.php')) {
            $uri = substr($uri, 10);
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Thực thi xử lý request theo routes đã khai báo
     */
    public static function dispatch(): void {
        $requestMethod = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $currentUri = self::resolveUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            // Chuyển route path thành regex để hỗ trợ parameters như {id}, {code}
            $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $route['path']);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $currentUri, $matches)) {
                // Trích xuất parameters có tên
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Chạy Middlewares nếu có
                foreach ($route['middleware'] as $mw) {
                    if (is_callable($mw)) {
                        call_user_func($mw);
                    }
                }

                // Chạy Handler
                $handler = $route['handler'];
                if (is_callable($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }

                if (is_array($handler) && count($handler) === 2) {
                    [$class, $method] = $handler;
                    if (!class_exists($class)) {
                        Response::abort(500, "Không tìm thấy controller: {$class}");
                    }
                    $controller = new $class();
                    if (!method_exists($controller, $method)) {
                        Response::abort(500, "Không tìm thấy phương thức: {$method} trong controller {$class}");
                    }
                    call_user_func_array([$controller, $method], $params);
                    return;
                }
            }
        }

        // Nếu không có route nào khớp
        if (str_starts_with($currentUri, '/api/')) {
            Response::json([
                'success' => false,
                'message' => 'API Endpoint không tồn tại.'
            ], 404);
        }

        Response::abort(404, 'Trang bạn đang tìm kiếm không tồn tại.');
    }
}
