<?php
/**
 * CSRF Middleware
 * Bảo vệ chống tấn công Cross-Site Request Forgery (CSRF)
 */

class CsrfMiddleware {
    /**
     * Xác thực CSRF token cho các request thay đổi trạng thái
     */
    public static function handle(bool $isApi = false): void {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        if (in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $token = $_POST['_csrf_token'] 
                  ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
                  ?? null;

            if (!Session::validateCsrf($token)) {
                if ($isApi) {
                    Response::json([
                        'success' => false,
                        'message' => 'CSRF Token không hợp lệ hoặc đã hết hạn. Vui lòng tải lại trang.'
                    ], 419);
                }

                Session::flash('error', 'Phiên làm việc đã hết hạn. Vui lòng thử lại.');
                $referer = $_SERVER['HTTP_REFERER'] ?? Response::baseUrl('');
                header("Location: {$referer}");
                exit;
            }
        }
    }
}
