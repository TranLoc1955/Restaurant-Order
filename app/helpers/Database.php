<?php
/**
 * Database Helper
 * Singleton PDO Wrapper cho toàn bộ ứng dụng
 */

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Lấy kết nối PDO duy nhất (Singleton)
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $configPath = dirname(__DIR__, 2) . '/config/database.php';
            if (!file_exists($configPath)) {
                throw new Exception("Không tìm thấy file cấu hình database tại: {$configPath}");
            }
            $config = require $configPath;

            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options']
                );
            } catch (PDOException $e) {
                // Ghi log bảo mật, không xuất password/thông tin nhạy cảm ra màn hình
                error_log("Database Connection Error: " . $e->getMessage());
                throw new Exception("Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra dịch vụ MySQL trên XAMPP.");
            }
        }

        return self::$instance;
    }
}
