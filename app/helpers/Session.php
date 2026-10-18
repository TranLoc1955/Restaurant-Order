<?php
/**
 * Session Helper
 * Quản lý Session an toàn, chống Session Fixation, Flash Messages và CSRF Token
 */

class Session {
    /**
     * Khởi tạo Session an toàn nếu chưa bắt đầu
     */
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Cấu hình cookie an toàn
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Lax');

            session_start();
        }
    }

    /**
     * Đổi Session ID sau khi xác thực để chống Session Fixation
     */
    public static function regenerate(): void {
        self::start();
        session_regenerate_id(true);
    }

    /**
     * Lưu giá trị vào session
     */
    public static function set(string $key, mixed $value): void {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Lấy giá trị từ session
     */
    public static function get(string $key, mixed $default = null): mixed {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Kiểm tra khóa có tồn tại trong session không
     */
    public static function has(string $key): bool {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Xóa một khóa khỏi session
     */
    public static function remove(string $key): void {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Hủy toàn bộ session khi đăng xuất
     */
    public static function destroy(): void {
        self::start();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Đặt thông báo Flash (chỉ tồn tại trong 1 chu kỳ request kế tiếp)
     */
    public static function flash(string $type, string $message): void {
        self::start();
        if (!isset($_SESSION['_flash'])) {
            $_SESSION['_flash'] = [];
        }
        $_SESSION['_flash'][$type] = $message;
    }

    /**
     * Lấy và xóa thông báo Flash
     */
    public static function getFlash(string $type): ?string {
        self::start();
        if (isset($_SESSION['_flash'][$type])) {
            $message = $_SESSION['_flash'][$type];
            unset($_SESSION['_flash'][$type]);
            return $message;
        }
        return null;
    }

    /**
     * Lấy toàn bộ Flash messages và dọn dẹp
     */
    public static function getAllFlashes(): array {
        self::start();
        $flashes = $_SESSION['_flash'] ?? [];
        $_SESSION['_flash'] = [];
        return $flashes;
    }

    /**
     * Tạo hoặc lấy CSRF token cho form
     */
    public static function csrfToken(): string {
        self::start();
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    /**
     * Xác thực CSRF token
     */
    public static function validateCsrf(?string $token): bool {
        self::start();
        if (empty($token) || empty($_SESSION['_csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    /**
     * Lấy thông tin user hiện tại đang đăng nhập
     */
    public static function user(): ?array {
        return self::get('user');
    }

    /**
     * Kiểm tra người dùng đã đăng nhập chưa
     */
    public static function isLoggedIn(): bool {
        return self::has('user') && !empty(self::get('user')['id']);
    }

    /**
     * Lấy ID của user hiện tại
     */
    public static function userId(): ?int {
        $user = self::user();
        return !empty($user['id']) ? (int)$user['id'] : null;
    }

    /**
     * Lấy role của user hiện tại
     */
    public static function userRole(): ?string {
        $user = self::user();
        return $user['role'] ?? null;
    }
}
