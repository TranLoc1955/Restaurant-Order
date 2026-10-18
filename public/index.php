<?php
/**
 * Front Controller
 * Điểm đón tất cả HTTP request của ứng dụng
 */

declare(strict_types=1);

// Bật hiển thị lỗi trong môi trường phát triển để dễ gỡ lỗi
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Định nghĩa đường dẫn gốc
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

// Nạp cấu hình từ .env nếu tồn tại (thuần PHP, không cần thư viện bên ngoài)
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim(trim($val), "\"'");
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }
    }
}

// Autoloader tự động nạp các lớp Controller, Model, Service, Middleware, Helper
spl_autoload_register(function (string $className) {
    $directories = [
        APP_PATH . '/helpers/',
        APP_PATH . '/middleware/',
        APP_PATH . '/models/',
        APP_PATH . '/controllers/',
        APP_PATH . '/services/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Khởi động Session an toàn
Session::start();

// Nạp các tệp định tuyến
require_once ROOT_PATH . '/routes/web.php';
require_once ROOT_PATH . '/routes/api.php';

// Điều hướng và xử lý request
try {
    Router::dispatch();
} catch (Throwable $e) {
    error_log("Unhandled Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    Response::abort(500, "Đã xảy ra lỗi máy chủ nội bộ. Vui lòng thử lại sau.");
}
