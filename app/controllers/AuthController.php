<?php
/**
 * Auth Controller
 * Điều hướng Đăng ký, Đăng nhập, Đăng xuất và phân quyền chuyển hướng
 */

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Hiển thị trang đăng nhập
     */
    public function showLogin(): void {
        AuthMiddleware::guestOnly();
        Response::view('auth.login', [
            'pageTitle' => 'Đăng nhập - Nhà Hàng Trần Lộc',
            'csrfToken' => Session::csrfToken()
        ], 'auth');
    }

    /**
     * Xử lý đăng nhập Form
     */
    public function login(): void {
        AuthMiddleware::guestOnly();
        CsrfMiddleware::handle();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $validator = Validator::make($_POST)
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('password', 'Mật khẩu');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::view('auth.login', [
                'pageTitle' => 'Đăng nhập - Nhà Hàng Trần Lộc',
                'csrfToken' => Session::csrfToken(),
                'old'       => ['email' => $email],
                'errors'    => $validator->errors()
            ], 'auth');
            return;
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Session::flash('error', 'Email hoặc mật khẩu không chính xác.');
            Response::view('auth.login', [
                'pageTitle' => 'Đăng nhập - Nhà Hàng Trần Lộc',
                'csrfToken' => Session::csrfToken(),
                'old'       => ['email' => $email],
                'errors'    => ['login' => 'Email hoặc mật khẩu không chính xác.']
            ], 'auth');
            return;
        }

        // Kiểm tra tài khoản có bị khóa không
        if ($user['status'] === 'locked') {
            Session::flash('error', 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ nhà hàng để được hỗ trợ.');
            Response::view('auth.login', [
                'pageTitle' => 'Đăng nhập - Nhà Hàng Trần Lộc',
                'csrfToken' => Session::csrfToken(),
                'old'       => ['email' => $email],
                'errors'    => ['login' => 'Tài khoản đã bị khóa.']
            ], 'auth');
            return;
        }

        // Đăng nhập thành công -> Đổi session ID chống Session Fixation
        Session::regenerate();
        Session::set('user', [
            'id'        => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'phone'     => $user['phone'],
            'role'      => $user['role'],
        ]);

        Session::flash('success', "Đăng nhập thành công! Chào mừng {$user['full_name']}.");

        // Chuyển hướng theo vai trò
        if ($user['role'] === 'admin') {
            Response::redirect('admin/dashboard');
        } elseif ($user['role'] === 'staff') {
            Response::redirect('staff/orders');
        } else {
            $redirect = Session::get('redirect_after_login', '');
            Session::remove('redirect_after_login');
            Response::redirect($redirect);
        }
    }

    /**
     * Hiển thị trang đăng ký
     */
    public function showRegister(): void {
        AuthMiddleware::guestOnly();
        Response::view('auth.register', [
            'pageTitle' => 'Đăng ký tài khoản - Nhà Hàng Trần Lộc',
            'csrfToken' => Session::csrfToken()
        ], 'auth');
    }

    /**
     * Xử lý đăng ký Form
     */
    public function register(): void {
        AuthMiddleware::guestOnly();
        CsrfMiddleware::handle();

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $validator = Validator::make($_POST)
            ->required('full_name', 'Họ và tên')
            ->minLength('full_name', 2, 'Họ và tên')
            ->maxLength('full_name', 100, 'Họ và tên')
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('phone', 'Số điện thoại')
            ->phone('phone', 'Số điện thoại')
            ->required('password', 'Mật khẩu')
            ->minLength('password', 6, 'Mật khẩu')
            ->matches('password', 'confirm_password', 'Mật khẩu xác nhận không khớp.');

        // Kiểm tra email trùng
        if ($validator->passes() && $this->userModel->findByEmail($email)) {
            $validator->addError('email', 'Email này đã được sử dụng. Vui lòng chọn email khác.');
        }

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::view('auth.register', [
                'pageTitle' => 'Đăng ký tài khoản - Nhà Hàng Trần Lộc',
                'csrfToken' => Session::csrfToken(),
                'old'       => [
                    'full_name' => $fullName,
                    'email'     => $email,
                    'phone'     => $phone,
                ],
                'errors'    => $validator->errors()
            ], 'auth');
            return;
        }

        // Tạo tài khoản (Bảo mật: Ép buộc role là customer qua registerCustomer)
        $newUserId = $this->userModel->registerCustomer([
            'full_name' => $fullName,
            'email'     => $email,
            'phone'     => $phone,
            'password'  => $password,
        ]);

        // Đăng nhập tự động sau khi đăng ký
        Session::regenerate();
        Session::set('user', [
            'id'        => $newUserId,
            'full_name' => $fullName,
            'email'     => $email,
            'phone'     => $phone,
            'role'      => 'customer',
        ]);

        Session::flash('success', "Đăng ký tài khoản thành công! Chào mừng {$fullName}.");
        Response::redirect('');
    }

    /**
     * Xử lý đăng xuất
     */
    public function logout(): void {
        Session::destroy();
        Session::start();
        Session::flash('success', 'Bạn đã đăng xuất thành công.');
        Response::redirect('login');
    }

    /**
     * API Đăng nhập (phục vụ Postman và kiểm thử tự động)
     */
    public function apiLogin(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';

        $validator = Validator::make($input)
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('password', 'Mật khẩu');

        if ($validator->fails()) {
            Response::json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.'
            ], 401);
        }

        if ($user['status'] === 'locked') {
            Response::json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ nhà hàng.'
            ], 403);
        }

        Session::regenerate();
        $userData = [
            'id'        => (int)$user['id'],
            'full_name' => $user['full_name'],
            'email'     => $user['email'],
            'phone'     => $user['phone'],
            'role'      => $user['role'],
        ];
        Session::set('user', $userData);

        Response::json([
            'success' => true,
            'message' => 'Đăng nhập thành công.',
            'user'    => $userData
        ], 200);
    }

    /**
     * API Đăng ký
     */
    public function apiRegister(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $validator = Validator::make($input)
            ->required('full_name', 'Họ và tên')
            ->minLength('full_name', 2, 'Họ và tên')
            ->required('email', 'Email')
            ->email('email', 'Email')
            ->required('phone', 'Số điện thoại')
            ->phone('phone', 'Số điện thoại')
            ->required('password', 'Mật khẩu')
            ->minLength('password', 6, 'Mật khẩu');

        if ($validator->passes() && $this->userModel->findByEmail($input['email'])) {
            $validator->addError('email', 'Email này đã tồn tại trong hệ thống.');
        }

        if ($validator->fails()) {
            Response::json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $userId = $this->userModel->registerCustomer([
            'full_name' => $input['full_name'],
            'email'     => $input['email'],
            'phone'     => $input['phone'],
            'password'  => $input['password'],
        ]);

        Response::json([
            'success' => true,
            'message' => 'Đăng ký tài khoản thành công.',
            'user_id' => $userId
        ], 201);
    }

    /**
     * API Lấy thông tin user hiện tại
     */
    public function apiMe(): void {
        $user = AuthMiddleware::requireLogin(true);
        Response::json([
            'success' => true,
            'user'    => $user
        ], 200);
    }

    /**
     * API Đăng xuất
     */
    public function apiLogout(): void {
        Session::destroy();
        Response::json([
            'success' => true,
            'message' => 'Đăng xuất thành công.'
        ], 200);
    }
}
