<?php
/**
 * Response Helper
 * Quản lý View rendering với layout, JSON response và HTTP redirects
 */

class Response {
    private static ?string $baseUrl = null;

    /**
     * Tự động xác định Base URL của ứng dụng
     */
    public static function baseUrl(string $path = ''): string {
        if (self::$baseUrl === null) {
            $config = require dirname(__DIR__, 2) . '/config/app.php';
            if (!empty($config['base_url'])) {
                self::$baseUrl = rtrim($config['base_url'], '/');
            } else {
                $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? 'localhost';
                $hostName = strtolower(explode(':', $host)[0]);
                $isLocal = in_array($hostName, ['localhost', '127.0.0.1', '::1', '0.0.0.0']);

                $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                    || (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on')
                    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
                    || !$isLocal;

                $protocol = $isHttps ? 'https://' : 'http://';
                $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
                $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
                self::$baseUrl = rtrim($protocol . $host . $baseDir, '/');
            }
        }

        $path = ltrim($path, '/');
        return $path === '' ? self::$baseUrl : self::$baseUrl . '/' . $path;
    }

    /**
     * Render view lồng vào layout
     */
    public static function view(string $viewName, array $data = [], ?string $layout = 'main'): void {
        extract($data);
        $baseUrl = self::baseUrl();

        $viewFile = dirname(__DIR__, 2) . '/views/' . str_replace('.', '/', $viewName) . '.php';
        if (!file_exists($viewFile)) {
            http_response_code(500);
            die("Lỗi: Không tìm thấy tệp giao diện: {$viewFile}");
        }

        if ($layout === null) {
            require $viewFile;
            return;
        }

        $layoutFile = dirname(__DIR__, 2) . '/views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            http_response_code(500);
            die("Lỗi: Không tìm thấy layout: {$layoutFile}");
        }

        // Bắt đầu capture nội dung view con
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Đảm bảo trình duyệt luôn nhận diện chính xác bảng mã UTF-8
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
        }

        require $layoutFile;
    }

    /**
     * Trả về JSON Response
     */
    public static function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Chuyển hướng URL an toàn
     */
    public static function redirect(string $path): void {
        if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
            $path = self::baseUrl($path);
        }
        header("Location: {$path}");
        exit;
    }

    /**
     * Trả về trang lỗi HTTP tiêu chuẩn
     */
    public static function abort(int $code = 404, string $message = ''): void {
        http_response_code($code);
        $errorFile = dirname(__DIR__, 2) . "/views/errors/{$code}.php";
        if (file_exists($errorFile)) {
            $baseUrl = self::baseUrl();
            require $errorFile;
        } else {
            echo "<h1>Lỗi {$code}</h1><p>" . htmlspecialchars($message) . "</p>";
        }
        exit;
    }
}
