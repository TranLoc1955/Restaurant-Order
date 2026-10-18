<?php
/**
 * User Model
 * Thao tác cơ sở dữ liệu bảng `users` bằng PDO Prepared Statements
 */

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Tìm người dùng theo Email
     */
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Tìm người dùng theo ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT id, full_name, email, phone, role, status, created_at FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Đăng ký tài khoản khách hàng công khai (Bảo vệ tuyệt đối: Luôn ép role là 'customer')
     */
    public function registerCustomer(array $data): int {
        $data['role'] = 'customer';
        $data['status'] = 'active';
        return $this->create($data);
    }

    /**
     * Tạo tài khoản mới trong hệ thống
     */
    public function create(array $data): int {
        $sql = "INSERT INTO users (full_name, email, phone, password_hash, role, status) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $role = $data['role'] ?? 'customer';
        // Ràng buộc bảo mật: Chỉ cho phép 3 role hợp lệ
        if (!in_array($role, ['customer', 'staff', 'admin'], true)) {
            $role = 'customer';
        }

        $status = $data['status'] ?? 'active';
        if (!in_array($status, ['active', 'locked'], true)) {
            $status = 'active';
        }

        $passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            trim($data['full_name']),
            trim(strtolower($data['email'])),
            trim($data['phone'] ?? ''),
            $passwordHash,
            $role,
            $status
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật trạng thái tài khoản (active hoặc locked)
     */
    public function updateStatus(int $id, string $status): bool {
        if (!in_array($status, ['active', 'locked'], true)) {
            return false;
        }

        // Quy tắc bảo mật: Không được khóa Admin duy nhất/cuối cùng
        $targetUser = $this->findById($id);
        if ($targetUser && $targetUser['role'] === 'admin' && $status === 'locked') {
            if ($this->countActiveAdmins() <= 1) {
                throw new Exception("Không thể khóa tài khoản Admin duy nhất còn hoạt động trong hệ thống.");
            }
        }

        $stmt = $this->db->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Đếm số lượng Admin đang hoạt động
     */
    public function countActiveAdmins(): int {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users WHERE role = 'admin' AND status = 'active'");
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy danh sách người dùng có phân trang và lọc theo role
     */
    public function getAll(?string $role = null, ?string $search = null): array {
        $sql = "SELECT id, full_name, email, phone, role, status, created_at FROM users WHERE 1=1";
        $params = [];

        if (!empty($role)) {
            $sql .= " AND role = ?";
            $params[] = $role;
        }

        if (!empty($search)) {
            $sql .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Kiểm tra xem Email đã có người dùng khác sử dụng chưa
     */
    public function isEmailTakenByOther(string $email, int $excludeUserId): bool {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1");
        $stmt->execute([trim(strtolower($email)), $excludeUserId]);
        return (bool)$stmt->fetch();
    }

    /**
     * Cập nhật thông tin người dùng (Họ tên, email, SĐT, role, status, và mật khẩu mới nếu có)
     */
    public function update(int $id, array $data): bool {
        $targetUser = $this->findById($id);
        if (!$targetUser) {
            throw new Exception("Người dùng không tồn tại.");
        }

        // Kiểm tra bảo mật: Nếu là admin duy nhất thì không thể chuyển role sang role khác hoặc khóa
        if ($targetUser['role'] === 'admin') {
            if (($data['role'] ?? 'admin') !== 'admin' || ($data['status'] ?? 'active') === 'locked') {
                if ($this->countActiveAdmins() <= 1) {
                    throw new Exception("Không thể hạ quyền hoặc khóa tài khoản Admin duy nhất còn hoạt động trong hệ thống.");
                }
            }
        }

        $fields = [
            'full_name = ?',
            'email = ?',
            'phone = ?',
            'role = ?',
            'status = ?'
        ];
        $params = [
            trim($data['full_name']),
            trim(strtolower($data['email'])),
            trim($data['phone'] ?? ''),
            $data['role'] ?? $targetUser['role'],
            $data['status'] ?? $targetUser['status']
        ];

        // Nếu có nhập mật khẩu mới
        if (!empty($data['password'])) {
            $fields[] = 'password_hash = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Xóa người dùng an toàn:
     * - Nếu người dùng đã có đơn hàng / lịch sử xử lý đơn -> Khóa tài khoản để bảo toàn toàn vẹn dữ liệu
     * - Nếu người dùng chưa từng có đơn hàng nào -> Xóa vĩnh viễn khỏi database
     */
    public function deleteOrLock(int $id): array {
        $targetUser = $this->findById($id);
        if (!$targetUser) {
            throw new Exception("Không tìm thấy người dùng cần xóa.");
        }

        // Không được xóa tài khoản Admin duy nhất
        if ($targetUser['role'] === 'admin' && $this->countActiveAdmins() <= 1) {
            throw new Exception("Không thể xóa tài khoản Admin duy nhất còn lại trong hệ thống.");
        }

        // Kiểm tra xem người dùng có đơn hàng nào không
        $stmtOrders = $this->db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
        $stmtOrders->execute([$id]);
        $orderCount = (int)$stmtOrders->fetchColumn();

        // Kiểm tra xem người dùng có từng thao tác đơn hàng không (nhân viên)
        $stmtHistory = $this->db->prepare("SELECT COUNT(*) FROM order_status_history WHERE changed_by = ?");
        $stmtHistory->execute([$id]);
        $historyCount = (int)$stmtHistory->fetchColumn();

        if ($orderCount > 0 || $historyCount > 0) {
            // Có dữ liệu liên quan -> Khóa tài khoản
            $this->updateStatus($id, 'locked');
            return [
                'type' => 'locked',
                'message' => "Tài khoản '{$targetUser['full_name']}' đã có lịch sử đơn hàng hoặc thao tác nghiệp vụ, nên hệ thống đã tự động KHÓA tài khoản thay vì xóa để bảo toàn dữ liệu lịch sử."
            ];
        } else {
            // Chưa từng có dữ liệu liên quan -> Xóa cứng an toàn
            $deleteStmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
            $deleteStmt->execute([$id]);
            return [
                'type' => 'deleted',
                'message' => "Đã xóa vĩnh viễn tài khoản '{$targetUser['full_name']}' khỏi hệ thống thành công."
            ];
        }
    }
}
