<?php
/**
 * Voucher Model
 * Quản lý mã giảm giá, kiểm tra điều kiện áp dụng (khách mới / khách cũ),
 * tính toán mức giảm và lưu trữ lịch sử sử dụng
 */

class Voucher {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Tìm voucher theo mã code (không phân biệt hoa thường)
     */
    public function findByCode(string $code): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM vouchers 
            WHERE UPPER(code) = UPPER(?) 
            LIMIT 1
        ");
        $stmt->execute([trim($code)]);
        $voucher = $stmt->fetch();
        return $voucher ?: null;
    }

    /**
     * Tìm voucher theo ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("SELECT * FROM vouchers WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $voucher = $stmt->fetch();
        return $voucher ?: null;
    }

    /**
     * Lấy toàn bộ danh sách voucher (dành cho Admin quản lý)
     */
    public function getAll(): array {
        $stmt = $this->db->query("
            SELECT v.*,
                   (SELECT COUNT(*) FROM voucher_usages vu WHERE vu.voucher_id = v.id) as actual_used_count
            FROM vouchers v
            ORDER BY v.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Đếm số đơn hàng đã hoàn tất (DELIVERED) của một khách hàng
     */
    public function countCustomerDeliveredOrders(int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM orders 
            WHERE user_id = ? AND order_status = 'DELIVERED'
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Đếm số lần một khách hàng đã sử dụng voucher này
     */
    public function getUserUsageCount(int $voucherId, int $userId): int {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM voucher_usages 
            WHERE voucher_id = ? AND user_id = ?
        ");
        $stmt->execute([$voucherId, $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Lấy danh sách voucher hiển thị cho khách hàng tại trang checkout
     */
    public function getAvailableForCustomer(int $userId, float $subtotal = 0.0): array {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare("
            SELECT * FROM vouchers 
            WHERE status = 'active'
              AND start_date <= ?
              AND (end_date IS NULL OR end_date >= ?)
              AND (usage_limit IS NULL OR used_count < usage_limit)
            ORDER BY min_order_amount ASC, id DESC
        ");
        $stmt->execute([$now, $now]);
        $vouchers = $stmt->fetchAll();

        $deliveredOrdersCount = $this->countCustomerDeliveredOrders($userId);

        $result = [];
        foreach ($vouchers as $v) {
            $userUsage = $this->getUserUsageCount((int)$v['id'], $userId);
            $limitPerUser = (int)$v['usage_limit_per_user'];
            $cond = $v['customer_condition'];

            $isEligible = true;
            $ineligibleReason = null;

            // Kiểm tra giới hạn lượt dùng của người dùng
            if ($userUsage >= $limitPerUser) {
                $isEligible = false;
                $ineligibleReason = "Bạn đã dùng hết số lượt cho phép ({$limitPerUser} lần)";
            }
            // Kiểm tra điều kiện khách hàng
            elseif ($cond === 'RETURNING_ONLY' && $deliveredOrdersCount === 0) {
                $isEligible = false;
                $ineligibleReason = "Chỉ dành cho khách hàng thân thiết (đã từng nhận đơn thành công)";
            } elseif ($cond === 'NEW_ONLY' && $deliveredOrdersCount > 0) {
                $isEligible = false;
                $ineligibleReason = "Chỉ dành cho khách hàng mới đặt món lần đầu";
            }

            // Tính toán giảm giá ước tính
            $minAmount = (float)$v['min_order_amount'];
            $isSubtotalMet = ($subtotal >= $minAmount);
            $deficit = max(0.0, $minAmount - $subtotal);

            $estimatedDiscount = 0.0;
            if ($isSubtotalMet) {
                if ($v['discount_type'] === 'FIXED') {
                    $estimatedDiscount = min((float)$v['discount_value'], $subtotal);
                } else {
                    $pct = (float)$v['discount_value'];
                    $disc = ($subtotal * $pct) / 100.0;
                    if ($v['max_discount_amount'] !== null && (float)$v['max_discount_amount'] > 0) {
                        $disc = min($disc, (float)$v['max_discount_amount']);
                    }
                    $estimatedDiscount = min($disc, $subtotal);
                }
            }

            $v['user_used_count'] = $userUsage;
            $v['is_eligible'] = $isEligible;
            $v['ineligible_reason'] = $ineligibleReason;
            $v['is_subtotal_met'] = $isSubtotalMet;
            $v['subtotal_deficit'] = $deficit;
            $v['estimated_discount'] = $estimatedDiscount;

            $result[] = $v;
        }

        return $result;
    }

    /**
     * Kiểm tra tính hợp lệ và tính số tiền giảm giá của một mã voucher
     */
    public function validateVoucher(string $code, int $userId, float $subtotal): array {
        $code = trim($code);
        if (empty($code)) {
            return [
                'valid' => false,
                'message' => 'Vui lòng nhập mã giảm giá.',
                'discount' => 0.0,
                'voucher' => null
            ];
        }

        $voucher = $this->findByCode($code);
        if (!$voucher) {
            return [
                'valid' => false,
                'message' => "Mã giảm giá '{$code}' không tồn tại trong hệ thống.",
                'discount' => 0.0,
                'voucher' => null
            ];
        }

        if ($voucher['status'] !== 'active') {
            return [
                'valid' => false,
                'message' => "Mã giảm giá '{$voucher['code']}' hiện đang tạm ngừng áp dụng.",
                'discount' => 0.0,
                'voucher' => $voucher
            ];
        }

        $now = date('Y-m-d H:i:s');
        if (!empty($voucher['start_date']) && $voucher['start_date'] > $now) {
            return [
                'valid' => false,
                'message' => "Mã giảm giá '{$voucher['code']}' chưa đến thời gian áp dụng.",
                'discount' => 0.0,
                'voucher' => $voucher
            ];
        }

        if (!empty($voucher['end_date']) && $voucher['end_date'] < $now) {
            return [
                'valid' => false,
                'message' => "Mã giảm giá '{$voucher['code']}' đã hết hạn sử dụng.",
                'discount' => 0.0,
                'voucher' => $voucher
            ];
        }

        // Kiểm tra tổng lượt dùng của voucher
        if ($voucher['usage_limit'] !== null && (int)$voucher['used_count'] >= (int)$voucher['usage_limit']) {
            return [
                'valid' => false,
                'message' => "Mã giảm giá '{$voucher['code']}' đã hết lượt sử dụng trong đợt khuyến mãi.",
                'discount' => 0.0,
                'voucher' => $voucher
            ];
        }

        // Kiểm tra số lần dùng của từng khách hàng
        $userUsage = $this->getUserUsageCount((int)$voucher['id'], $userId);
        if ($userUsage >= (int)$voucher['usage_limit_per_user']) {
            return [
                'valid' => false,
                'message' => "Bạn đã sử dụng hết số lần cho phép của mã '{$voucher['code']}' ({$voucher['usage_limit_per_user']} lần).",
                'discount' => 0.0,
                'voucher' => $voucher
            ];
        }

        // Kiểm tra điều kiện khách hàng (Khách cũ / Khách mới)
        $deliveredOrdersCount = $this->countCustomerDeliveredOrders($userId);
        if ($voucher['customer_condition'] === 'RETURNING_ONLY' && $deliveredOrdersCount === 0) {
            return [
                'valid' => false,
                'message' => "Mã '{$voucher['code']}' là ưu đãi TRI ÂN chỉ dành riêng cho khách hàng từng đặt món thành công tại Nhà Hàng.",
                'discount' => 0.0,
                'voucher' => $voucher
            ];
        }

        if ($voucher['customer_condition'] === 'NEW_ONLY' && $deliveredOrdersCount > 0) {
            return [
                'valid' => false,
                'message' => "Mã '{$voucher['code']}' chỉ áp dụng cho đơn hàng đầu tiên của khách hàng mới.",
                'discount' => 0.0,
                'voucher' => $voucher
            ];
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        $minOrderAmount = (float)$voucher['min_order_amount'];
        if ($subtotal < $minOrderAmount) {
            return [
                'valid' => false,
                'message' => "Mã '{$voucher['code']}' yêu cầu đơn hàng tối thiểu từ " . number_format($minOrderAmount, 0, ',', '.') . "đ. Bạn còn thiếu " . number_format($minOrderAmount - $subtotal, 0, ',', '.') . "đ.",
                'discount' => 0.0,
                'voucher' => $voucher
            ];
        }

        // Tính toán mức giảm
        $discount = 0.0;
        if ($voucher['discount_type'] === 'FIXED') {
            $discount = (float)$voucher['discount_value'];
        } else {
            $pct = (float)$voucher['discount_value'];
            $discount = ($subtotal * $pct) / 100.0;
            if ($voucher['max_discount_amount'] !== null && (float)$voucher['max_discount_amount'] > 0) {
                $discount = min($discount, (float)$voucher['max_discount_amount']);
            }
        }

        // Không giảm vượt quá giá trị đơn hàng
        $discount = min($discount, $subtotal);

        return [
            'valid' => true,
            'message' => "Áp dụng thành công mã '{$voucher['code']}' - Giảm " . number_format($discount, 0, ',', '.') . "đ!",
            'discount' => $discount,
            'voucher' => $voucher
        ];
    }

    /**
     * Ghi nhận lượt sử dụng voucher khi tạo đơn thành công
     */
    public function recordUsage(int $voucherId, int $orderId, int $userId, float $discountAmount, ?PDO $pdo = null): void {
        $db = $pdo ?: $this->db;

        // Lưu vào voucher_usages
        $stmt = $db->prepare("
            INSERT INTO voucher_usages (voucher_id, order_id, user_id, discount_amount)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$voucherId, $orderId, $userId, $discountAmount]);

        // Cập nhật số lượt dùng trong vouchers
        $upd = $db->prepare("UPDATE vouchers SET used_count = used_count + 1 WHERE id = ?");
        $upd->execute([$voucherId]);
    }

    /**
     * Hoàn lại lượt sử dụng voucher khi đơn hàng bị HỦY hoặc TỪ CHỐI
     */
    public function revertUsage(int $orderId, ?PDO $pdo = null): void {
        $db = $pdo ?: $this->db;

        // Tìm usage của order này
        $stmt = $db->prepare("SELECT voucher_id FROM voucher_usages WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $usages = $stmt->fetchAll();

        foreach ($usages as $u) {
            $voucherId = (int)$u['voucher_id'];
            $upd = $db->prepare("UPDATE vouchers SET used_count = GREATEST(0, CAST(used_count AS SIGNED) - 1) WHERE id = ?");
            $upd->execute([$voucherId]);
        }

        $del = $db->prepare("DELETE FROM voucher_usages WHERE order_id = ?");
        $del->execute([$orderId]);
    }

    /**
     * Tạo voucher mới (Admin)
     */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO vouchers (
                code, title, description, discount_type, discount_value,
                max_discount_amount, min_order_amount, usage_limit,
                usage_limit_per_user, customer_condition, start_date, end_date, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            strtoupper(trim($data['code'])),
            trim($data['title']),
            trim($data['description'] ?? ''),
            $data['discount_type'] ?? 'FIXED',
            (float)$data['discount_value'],
            !empty($data['max_discount_amount']) ? (float)$data['max_discount_amount'] : null,
            (float)($data['min_order_amount'] ?? 0),
            !empty($data['usage_limit']) ? (int)$data['usage_limit'] : null,
            (int)($data['usage_limit_per_user'] ?? 1),
            $data['customer_condition'] ?? 'ALL',
            !empty($data['start_date']) ? $data['start_date'] : date('Y-m-d H:i:s'),
            !empty($data['end_date']) ? $data['end_date'] : null,
            $data['status'] ?? 'active'
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Chuyển đổi trạng thái voucher (active <-> inactive)
     */
    public function toggleStatus(int $id): bool {
        $v = $this->findById($id);
        if (!$v) return false;

        $newStatus = ($v['status'] === 'active') ? 'inactive' : 'active';
        $stmt = $this->db->prepare("UPDATE vouchers SET status = ? WHERE id = ?");
        return $stmt->execute([$newStatus, $id]);
    }

    /**
     * Xóa voucher (nếu chưa có đơn nào sử dụng, hoặc chuyển sang inactive)
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM voucher_usages WHERE voucher_id = ?");
        $stmt->execute([$id]);
        $usedCount = (int)$stmt->fetchColumn();

        if ($usedCount > 0) {
            // Đã có lịch sử đơn hàng sử dụng -> chuyển sang inactive để bảo toàn dữ liệu lịch sử
            $upd = $this->db->prepare("UPDATE vouchers SET status = 'inactive' WHERE id = ?");
            return $upd->execute([$id]);
        }

        $del = $this->db->prepare("DELETE FROM vouchers WHERE id = ?");
        return $del->execute([$id]);
    }
}
