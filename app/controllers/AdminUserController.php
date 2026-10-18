<?php
/**
 * Admin User Controller
 * Quản lý tài khoản người dùng: Danh sách, tạo nhân viên mới, khóa/mở khóa tài khoản
 */

class AdminUserController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index(): void {
        AuthMiddleware::requireRole('admin');

        $role = $_GET['role'] ?? null;
        $search = trim($_GET['search'] ?? '');

        $users = $this->userModel->getAll($role, $search);

        Response::view('admin.users.index', [
            'pageTitle'   => 'Quản lý Tài khoản - Quản trị viên',
            'users'       => $users,
            'currentRole' => $role,
            'search'      => $search,
            'csrfToken'   => Session::csrfToken()
        ], 'admin');
    }

    /**
     * Admin tạo tài khoản người dùng mới (Nhân viên, Khách hàng hoặc Quản trị viên)
     */
    public function store(): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'staff';
        $status = $_POST['status'] ?? 'active';

        $validator = Validator::make($_POST)
            ->required('full_name', 'Họ và tên người dùng')
            ->minLength('full_name', 2, 'Họ và tên người dùng')
            ->required('email', 'Email người dùng')
            ->email('email', 'Email người dùng')
            ->required('phone', 'Số điện thoại')
            ->phone('phone', 'Số điện thoại')
            ->required('password', 'Mật khẩu')
            ->minLength('password', 6, 'Mật khẩu');

        if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
            $role = 'staff';
        }
        if (!in_array($status, ['active', 'locked'], true)) {
            $status = 'active';
        }

        if ($this->userModel->findByEmail($email)) {
            $validator->addError('email', 'Email này đã tồn tại trong hệ thống.');
        }

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('admin/users');
            return;
        }

        try {
            $this->userModel->create([
                'full_name' => $fullName,
                'email'     => $email,
                'phone'     => $phone,
                'password'  => $password,
                'role'      => $role,
                'status'    => $status,
            ]);
            Session::flash('success', "Đã tạo tài khoản cho '{$fullName}' ({$role}) thành công.");
        } catch (Throwable $e) {
            Session::flash('error', "Lỗi tạo tài khoản: " . $e->getMessage());
        }

        Response::redirect('admin/users');
    }

    /**
     * Alias để tương thích ngược với route create-staff
     */
    public function createStaff(): void {
        $this->store();
    }

    /**
     * Cập nhật thông tin tài khoản người dùng
     */
    public function update(string|int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $userId = (int)$id;
        $targetUser = $this->userModel->findById($userId);

        if (!$targetUser) {
            Session::flash('error', 'Người dùng không tồn tại.');
            Response::redirect('admin/users');
            return;
        }

        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? $targetUser['role'];
        $status = $_POST['status'] ?? $targetUser['status'];
        $password = trim($_POST['password'] ?? '');

        $validator = Validator::make($_POST)
            ->required('full_name', 'Họ và tên người dùng')
            ->minLength('full_name', 2, 'Họ và tên người dùng')
            ->required('email', 'Email người dùng')
            ->email('email', 'Email người dùng')
            ->required('phone', 'Số điện thoại')
            ->phone('phone', 'Số điện thoại');

        if (!empty($password) && strlen($password) < 6) {
            $validator->addError('password', 'Mật khẩu mới phải có tối thiểu 6 ký tự.');
        }

        if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
            $role = $targetUser['role'];
        }
        if (!in_array($status, ['active', 'locked'], true)) {
            $status = $targetUser['status'];
        }

        // Kiểm tra email trùng lặp với người dùng khác
        if ($this->userModel->isEmailTakenByOther($email, $userId)) {
            $validator->addError('email', 'Email này đã được người dùng khác sử dụng.');
        }

        // Ràng buộc bảo mật: Không thể tự hạ quyền hoặc tự khóa chính mình
        $currentLoggedInId = Session::userId();
        if ($userId === $currentLoggedInId) {
            if ($role !== 'admin') {
                $validator->addError('role', 'Bạn không thể tự hạ quyền Admin của chính mình.');
            }
            if ($status === 'locked') {
                $validator->addError('status', 'Bạn không thể tự khóa tài khoản của chính mình.');
            }
        }

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('admin/users');
            return;
        }

        try {
            $updateData = [
                'full_name' => $fullName,
                'email'     => $email,
                'phone'     => $phone,
                'role'      => $role,
                'status'    => $status,
            ];
            if (!empty($password)) {
                $updateData['password'] = $password;
            }

            $this->userModel->update($userId, $updateData);
            Session::flash('success', "Đã cập nhật thông tin tài khoản '{$fullName}' thành công.");
        } catch (Throwable $e) {
            Session::flash('error', "Lỗi cập nhật: " . $e->getMessage());
        }

        Response::redirect('admin/users');
    }

    /**
     * Xóa người dùng an toàn (Hoặc tự động Khóa nếu có dữ liệu đơn hàng liên quan)
     */
    public function delete(string|int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $userId = (int)$id;

        // Ràng buộc bảo mật: Không được tự xóa tài khoản của chính mình
        if ($userId === Session::userId()) {
            Session::flash('error', 'Bạn không thể tự xóa tài khoản Quản trị viên của chính mình.');
            Response::redirect('admin/users');
            return;
        }

        try {
            $result = $this->userModel->deleteOrLock($userId);
            if ($result['type'] === 'deleted') {
                Session::flash('success', $result['message']);
            } else {
                Session::flash('warning', $result['message']);
            }
        } catch (Throwable $e) {
            Session::flash('error', "Lỗi xóa người dùng: " . $e->getMessage());
        }

        Response::redirect('admin/users');
    }

    /**
     * Khóa hoặc mở khóa tài khoản người dùng
     */
    public function toggleLock(string|int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $userId = (int)$id;
        $targetUser = $this->userModel->findById($userId);

        if (!$targetUser) {
            Session::flash('error', 'Người dùng không tồn tại.');
            Response::redirect('admin/users');
            return;
        }

        // Không thể tự khóa chính mình
        if ($userId === Session::userId()) {
            Session::flash('error', 'Bạn không thể tự khóa tài khoản của chính mình.');
            Response::redirect('admin/users');
            return;
        }

        $newStatus = ($targetUser['status'] === 'active') ? 'locked' : 'active';

        try {
            $this->userModel->updateStatus($userId, $newStatus);
            $actionText = ($newStatus === 'locked') ? 'Khóa' : 'Mở khóa';
            Session::flash('success', "Đã {$actionText} tài khoản '{$targetUser['full_name']}' thành công.");
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('admin/users');
    }
}
