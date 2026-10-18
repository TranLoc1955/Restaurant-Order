<?php
/**
 * Auth Middleware
 * Kiểm tra xác thực người dùng, trạng thái khóa tài khoản và phân quyền vai trò (RBAC)
 */

class AuthMiddleware {
    /**
     * Yêu cầu người dùng phải đăng nhập
     */
    public static function requireLogin(bool $isApi = false): ?array {
        if (!Session::isLoggedIn()) {
            if ($isApi) {
                Response::json([
                    'success' => false,
                    'message' => 'Yêu cầu đăng nhập để thực hiện thao tác này.'
                ], 401);
            }

            Session::flash('error', 'Vui lòng đăng nhập để tiếp tục.');
            Response::redirect('login');
            return null;
        }

        $user = Session::user();

        // Kiểm tra xem tài khoản có bị khóa không (truy vấn DB thời gian thực để chống trường hợp bị khóa sau khi login)
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, status, role FROM users WHERE id = ?");
        $stmt->execute([$user['id']]);
        $dbUser = $stmt->fetch();

        if (!$dbUser || $dbUser['status'] === 'locked') {
            Session::destroy();
            if ($isApi) {
                Response::json([
                    'success' => false,
                    'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ nhà hàng.'
                ], 403);
            }

            Session::flash('error', 'Tài khoản của bạn đã bị khóa hoặc không còn tồn tại.');
            Response::redirect('login');
            return null;
        }

        // Cập nhật lại role mới nhất nếu có thay đổi
        if ($dbUser['role'] !== $user['role']) {
            $user['role'] = $dbUser['role'];
            Session::set('user', $user);
        }

        return $user;
    }

    /**
     * Yêu cầu vai trò cụ thể (ví dụ: 'admin', hoặc ['staff', 'admin'])
     */
    public static function requireRole(string|array $roles, bool $isApi = false): array {
        $user = self::requireLogin($isApi);
        $allowedRoles = is_array($roles) ? $roles : [$roles];

        if (!in_array($user['role'], $allowedRoles, true)) {
            if ($isApi) {
                Response::json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập chức năng này (Yêu cầu quyền: ' . implode('/', $allowedRoles) . ').'
                ], 403);
            }

            Response::abort(403, 'Bạn không có quyền truy cập vào trang này.');
        }

        return $user;
    }

    /**
     * Chặn người đã đăng nhập truy cập trang login/register
     */
    public static function guestOnly(): void {
        if (Session::isLoggedIn()) {
            $role = Session::userRole();
            if ($role === 'admin') {
                Response::redirect('admin/dashboard');
            } elseif ($role === 'staff') {
                Response::redirect('staff/orders');
            } else {
                Response::redirect('');
            }
        }
    }
}
