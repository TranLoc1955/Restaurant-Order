<?php
/**
 * Category Model
 * Thao tác cơ sở dữ liệu bảng `categories`
 */

class Category {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Lấy tất cả danh mục đang hoạt động (dành cho Menu & Khách hàng)
     */
    public function getActive(): array {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id AND p.availability_status != 'hidden'
            WHERE c.status = 'active'
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Lấy tất cả danh mục (dành cho Admin quản lý)
     */
    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT c.*, COUNT(p.id) as product_count 
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.id ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh mục theo ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM categories WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Thêm danh mục mới
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO categories (name, description, status) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            trim($data['name']),
            trim($data['description'] ?? ''),
            $data['status'] ?? 'active'
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật danh mục
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE categories 
            SET name = ?, description = ?, status = ? 
            WHERE id = ?
        ");
        return $stmt->execute([
            trim($data['name']),
            trim($data['description'] ?? ''),
            $data['status'] ?? 'active',
            $id
        ]);
    }

    /**
     * Đổi trạng thái danh mục (active <-> hidden)
     */
    public function toggleStatus(int $id): bool {
        $category = $this->findById($id);
        if (!$category) return false;

        $newStatus = ($category['status'] === 'active') ? 'hidden' : 'active';
        $stmt = $this->db->prepare("UPDATE categories SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }
}
