<?php
/**
 * Product Model
 * Thao tác cơ sở dữ liệu bảng `products`
 */

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Trả về URL ảnh hiển thị của món ăn
     * Hỗ trợ ảnh tải lên uploads/products, ảnh assets, link URL trực tuyến, hoặc fallback
     */
    public static function getImageUrl(?string $image): string {
        if (empty($image) || $image === 'default-food.jpg') {
            return Response::baseUrl('assets/images/products/default-food.svg');
        }

        // Nếu là full link http/https
        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        $rootPath = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);

        // Nếu nằm trong uploads/products/
        $uploadPath = $rootPath . '/public/uploads/products/' . $image;
        if (file_exists($uploadPath)) {
            return Response::baseUrl('uploads/products/' . $image);
        }

        // Nếu nằm trong assets/images/products/
        $assetPath = $rootPath . '/public/assets/images/products/' . $image;
        if (file_exists($assetPath)) {
            return Response::baseUrl('assets/images/products/' . $image);
        }

        return Response::baseUrl('assets/images/products/default-food.svg');
    }

    /**
     * Lấy chi tiết món theo ID kèm tên danh mục
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, c.status as category_status
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Lấy danh sách món ăn hiển thị ngoài trang chủ (Món nổi bật)
     */
    public function getFeatured(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id AND c.status = 'active'
            WHERE p.availability_status != 'hidden'
            ORDER BY p.id ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách món bán chạy nhất dựa trên order_items
     */
    public function getBestSellers(int $limit = 6): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.name as category_name, COALESCE(SUM(oi.quantity), 0) as total_sold
            FROM products p
            JOIN categories c ON p.category_id = c.id AND c.status = 'active'
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.order_status = 'DELIVERED'
            WHERE p.availability_status != 'hidden'
            GROUP BY p.id
            ORDER BY total_sold DESC, p.id ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Lấy danh sách món cho trang Menu khách hàng (có lọc, tìm kiếm, sắp xếp và phân trang)
     */
    public function getAvailable(array $filters = [], string $sort = 'default', int $page = 1, int $perPage = 12): array {
        $sql = "
            SELECT p.*, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id AND c.status = 'active'
            WHERE p.availability_status != 'hidden'
        ";
        $params = [];

        // Lọc theo danh mục
        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        // Tìm kiếm theo tên hoặc mô tả
        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . trim($filters['search']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Lọc theo tình trạng còn hàng
        if (!empty($filters['availability'])) {
            $sql .= " AND p.availability_status = ?";
            $params[] = $filters['availability'];
        }

        // Sắp xếp
        switch ($sort) {
            case 'price_asc':
                $sql .= " ORDER BY p.price ASC";
                break;
            case 'price_desc':
                $sql .= " ORDER BY p.price DESC";
                break;
            case 'name_asc':
                $sql .= " ORDER BY p.name ASC";
                break;
            default:
                $sql .= " ORDER BY p.availability_status = 'in_stock' DESC, p.id ASC";
                break;
        }

        // Phân trang
        $offset = ($page - 1) * $perPage;
        $sql .= " LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $paramIndex = 1;
        foreach ($params as $param) {
            $stmt->bindValue($paramIndex++, $param);
        }
        $stmt->bindValue($paramIndex++, $perPage, PDO::PARAM_INT);
        $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Đếm tổng số món thỏa mãn điều kiện lọc
     */
    public function countAvailable(array $filters = []): int {
        $sql = "
            SELECT COUNT(*) 
            FROM products p
            JOIN categories c ON p.category_id = c.id AND c.status = 'active'
            WHERE p.availability_status != 'hidden'
        ";
        $params = [];

        if (!empty($filters['category_id'])) {
            $sql .= " AND p.category_id = ?";
            $params[] = (int)$filters['category_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . trim($filters['search']) . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy toàn bộ món cho giao diện Admin quản lý
     */
    public function getAllForAdmin(?string $search = null, ?int $categoryId = null, ?string $availability = null): array {
        $sql = "
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($categoryId)) {
            $sql .= " AND p.category_id = ?";
            $params[] = $categoryId;
        }

        if (!empty($availability)) {
            $sql .= " AND p.availability_status = ?";
            $params[] = $availability;
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Thêm món mới
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO products (category_id, name, description, price, image, availability_status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['category_id'],
            trim($data['name']),
            trim($data['description'] ?? ''),
            (float)$data['price'],
            trim($data['image'] ?? 'default-food.jpg'),
            $data['availability_status'] ?? 'in_stock'
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật món ăn
     */
    public function update(int $id, array $data): bool {
        $stmt = $this->db->prepare("
            UPDATE products 
            SET category_id = ?, name = ?, description = ?, price = ?, image = ?, availability_status = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            (int)$data['category_id'],
            trim($data['name']),
            trim($data['description'] ?? ''),
            (float)$data['price'],
            trim($data['image'] ?? 'default-food.jpg'),
            $data['availability_status'] ?? 'in_stock',
            $id
        ]);
    }

    /**
     * Cập nhật trạng thái còn hàng (in_stock, out_of_stock, hidden)
     */
    public function updateAvailability(int $id, string $status): bool {
        if (!in_array($status, ['in_stock', 'out_of_stock', 'hidden'], true)) {
            return false;
        }
        $stmt = $this->db->prepare("UPDATE products SET availability_status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Xóa mềm hoặc xóa cứng: nếu món đã từng được đặt, ẩn món; nếu chưa từng đặt thì xóa
     */
    public function deleteOrHide(int $id): bool {
        // Kiểm tra xem món có trong đơn hàng nào chưa
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM order_items WHERE product_id = ?");
        $stmt->execute([$id]);
        $count = (int)$stmt->fetchColumn();

        if ($count > 0) {
            // Đã có trong đơn hàng -> Ẩn để bảo toàn lịch sử
            return $this->updateAvailability($id, 'hidden');
        } else {
            // Chưa có trong đơn hàng -> Xóa an toàn
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            return $stmt->execute([$id]);
        }
    }
}
