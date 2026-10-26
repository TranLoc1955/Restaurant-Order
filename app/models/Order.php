<?php
/**
 * Order Model
 * Quản lý đơn hàng, chi tiết món, nhật ký trạng thái và thống kê doanh thu hợp lệ
 */

class Order {
    private PDO $db;

    // Định nghĩa ma trận chuyển trạng thái hợp lệ
    public const VALID_TRANSITIONS = [
        'PENDING'   => ['CONFIRMED', 'REJECTED', 'CANCELLED'],
        'CONFIRMED' => ['PREPARING'],
        'PREPARING' => ['READY'],
        'READY'     => ['DELIVERED'],
        'DELIVERED' => [], // Trạng thái kết thúc
        'REJECTED'  => [], // Trạng thái kết thúc
        'CANCELLED' => [], // Trạng thái kết thúc
    ];

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Tạo đơn hàng mới trong Transaction an toàn
     */
    public function createOrder(array $customerData, array $cartItems, int $userId): array {
        if (empty($cartItems)) {
            throw new Exception("Giỏ hàng của bạn đang rỗng.");
        }

        $this->db->beginTransaction();

        try {
            // 1. Tính toán lại subtotal từ database để bảo vệ tính toàn vẹn giá
            $subtotal = 0.00;
            $verifiedItems = [];

            $productStmt = $this->db->prepare("
                SELECT id, name, price, availability_status 
                FROM products 
                WHERE id = ? FOR UPDATE
            ");

            foreach ($cartItems as $item) {
                $productId = (int)$item['product_id'];
                $quantity = (int)$item['quantity'];

                if ($quantity <= 0) {
                    throw new Exception("Số lượng món ăn không hợp lệ.");
                }

                $productStmt->execute([$productId]);
                $product = $productStmt->fetch();

                if (!$product) {
                    throw new Exception("Món ăn không tồn tại trong hệ thống.");
                }

                if ($product['availability_status'] !== 'in_stock') {
                    throw new Exception("Món '{$product['name']}' hiện đã hết hàng hoặc tạm ngừng phục vụ.");
                }

                $unitPrice = (float)$product['price'];
                $lineTotal = $unitPrice * $quantity;
                $subtotal += $lineTotal;

                $verifiedItems[] = [
                    'product_id'            => $product['id'],
                    'product_name_snapshot' => $product['name'],
                    'unit_price'            => $unitPrice,
                    'quantity'              => $quantity,
                    'line_total'            => $lineTotal,
                ];
            }

            // 2. Lấy cấu hình phí giao hàng và đơn hàng tối thiểu
            $settingStmt = $this->db->query("SELECT delivery_fee, minimum_order_amount FROM restaurant_settings WHERE id = 1 LIMIT 1");
            $setting = $settingStmt->fetch();
            $shippingFee = $setting ? (float)$setting['delivery_fee'] : 15000.00;
            $minAmount = $setting ? (float)$setting['minimum_order_amount'] : 30000.00;

            if ($subtotal < $minAmount) {
                throw new Exception("Giá trị đơn hàng tối thiểu để đặt là " . number_format($minAmount, 0, ',', '.') . "đ.");
            }

            // 3. Xử lý Mã giảm giá (Voucher)
            $voucherCode = !empty($customerData['voucher_code']) ? strtoupper(trim($customerData['voucher_code'])) : null;
            $discountAmount = 0.00;
            $voucherId = null;

            if ($voucherCode) {
                $voucherModel = new Voucher();
                $validation = $voucherModel->validateVoucher($voucherCode, $userId, $subtotal);
                if (!$validation['valid']) {
                    throw new Exception($validation['message']);
                }
                $discountAmount = (float)$validation['discount'];
                $voucherId = (int)$validation['voucher']['id'];
                $voucherCode = $validation['voucher']['code'];
            }

            // 4. Xử lý Dùng Điểm Thưởng (1 điểm = 1.000đ)
            $pointsRequested = isset($customerData['points_used']) ? (int)$customerData['points_used'] : 0;
            $pointsUsed = 0;
            $pointsDiscount = 0.00;

            if ($pointsRequested > 0) {
                $userModel = new User();
                $availablePoints = $userModel->getRewardPoints($userId);

                if ($pointsRequested > $availablePoints) {
                    throw new Exception("Bạn hiện có {$availablePoints} điểm tích lũy, không đủ {$pointsRequested} điểm yêu cầu.");
                }

                // Điểm chỉ được giảm tối đa trên số tiền còn lại sau voucher
                $remainingSubtotal = max(0.00, $subtotal - $discountAmount);
                $maxPointsAllowed = (int)floor($remainingSubtotal / 1000.0);
                $pointsUsed = min($pointsRequested, $maxPointsAllowed);
                $pointsDiscount = $pointsUsed * 1000.00;
            }

            // 5. Tính tổng thanh toán cuối cùng
            $finalFoodTotal = max(0.00, $subtotal - $discountAmount - $pointsDiscount);
            $totalAmount = $finalFoodTotal + $shippingFee;

            // Tính số điểm thưởng sẽ nhận được khi đơn giao thành công (10.000đ = 1 điểm)
            $pointsEarned = (int)floor($totalAmount / 10000.0);

            // 6. Sinh mã đơn hàng duy nhất (Ví dụ: ORD-20261017-A8B9C1)
            $orderCode = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));

            // 7. Lưu bảng orders
            $paymentMethod = ($customerData['payment_method'] ?? 'COD') === 'BANK_TRANSFER' ? 'BANK_TRANSFER' : 'COD';

            $orderStmt = $this->db->prepare("
                INSERT INTO orders (
                    order_code, user_id, recipient_name, recipient_phone, delivery_address,
                    note, subtotal, shipping_fee, total_amount, payment_method, payment_status,
                    voucher_code, discount_amount, points_used, points_discount, points_earned,
                    order_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'unpaid', ?, ?, ?, ?, ?, 'PENDING')
            ");

            $orderStmt->execute([
                $orderCode,
                $userId,
                trim($customerData['recipient_name']),
                trim($customerData['recipient_phone']),
                trim($customerData['delivery_address']),
                trim($customerData['note'] ?? ''),
                $subtotal,
                $shippingFee,
                $totalAmount,
                $paymentMethod,
                $voucherCode,
                $discountAmount,
                $pointsUsed,
                $pointsDiscount,
                $pointsEarned,
            ]);

            $orderId = (int)$this->db->lastInsertId();

            // 8. Ghi nhận lượt dùng Voucher
            if ($voucherId && $discountAmount > 0) {
                (new Voucher())->recordUsage($voucherId, $orderId, $userId, $discountAmount, $this->db);
            }

            // 9. Trừ điểm thưởng của khách hàng ngay khi đặt đơn
            if ($pointsUsed > 0) {
                (new User())->deductPoints(
                    $userId,
                    $pointsUsed,
                    "Dùng {$pointsUsed} điểm giảm " . number_format($pointsDiscount, 0, ',', '.') . "đ cho đơn {$orderCode}",
                    $orderId,
                    $this->db
                );
            }

            // 10. Lưu bảng order_items
            $itemStmt = $this->db->prepare("
                INSERT INTO order_items (
                    order_id, product_id, product_name_snapshot, unit_price, quantity, line_total
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");

            foreach ($verifiedItems as $vItem) {
                $itemStmt->execute([
                    $orderId,
                    $vItem['product_id'],
                    $vItem['product_name_snapshot'],
                    $vItem['unit_price'],
                    $vItem['quantity'],
                    $vItem['line_total'],
                ]);
            }

            // 11. Ghi nhật ký trạng thái đầu tiên
            $methodNote = ($paymentMethod === 'BANK_TRANSFER')
                ? 'Khách hàng tạo đơn mới (Thanh toán: Chuyển khoản ngân hàng VietQR)'
                : 'Khách hàng tạo đơn mới (Thanh toán: Tiền mặt khi nhận hàng COD)';

            if ($voucherCode) {
                $methodNote .= " | Voucher: {$voucherCode} (-" . number_format($discountAmount, 0, ',', '.') . "đ)";
            }
            if ($pointsUsed > 0) {
                $methodNote .= " | Tích điểm: -{$pointsUsed} điểm (-" . number_format($pointsDiscount, 0, ',', '.') . "đ)";
            }

            $historyStmt = $this->db->prepare("
                INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note)
                VALUES (?, NULL, 'PENDING', ?, ?)
            ");
            $historyStmt->execute([$orderId, $userId, $methodNote]);

            $this->db->commit();

            return [
                'id'              => $orderId,
                'order_code'      => $orderCode,
                'total_amount'    => $totalAmount,
                'subtotal'        => $subtotal,
                'shipping_fee'    => $shippingFee,
                'discount_amount' => $discountAmount,
                'points_used'     => $pointsUsed,
                'points_discount' => $pointsDiscount,
                'points_earned'   => $pointsEarned,
            ];
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Tìm đơn hàng theo mã (order_code)
     */
    public function findByCode(string $orderCode): ?array {
        $stmt = $this->db->prepare("
            SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.order_code = ? LIMIT 1
        ");
        $stmt->execute([trim($orderCode)]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    /**
     * Tìm đơn hàng theo ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare("
            SELECT o.*, u.full_name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch();
        return $order ?: null;
    }

    /**
     * Lấy danh sách món ăn trong đơn kèm ảnh sản phẩm hiện tại
     */
    public function getOrderItems(int $orderId): array {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
            ORDER BY oi.id ASC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy nhật ký thay đổi trạng thái của đơn
     */
    public function getOrderStatusHistory(int $orderId): array {
        $stmt = $this->db->prepare("
            SELECT h.*, u.full_name as changer_name, u.role as changer_role
            FROM order_status_history h
            JOIN users u ON h.changed_by = u.id
            WHERE h.order_id = ?
            ORDER BY h.created_at ASC
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy lịch sử đơn hàng của một khách hàng
     */
    public function getOrdersByUserId(int $userId): array {
        $stmt = $this->db->prepare("
            SELECT o.*, COUNT(oi.id) as total_items
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.user_id = ?
            GROUP BY o.id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Lấy toàn bộ đơn hàng (dành cho Staff và Admin xử lý)
     */
    public function getAllOrders(?string $status = null, ?string $date = null, ?string $search = null): array {
        $sql = "
            SELECT o.*, u.full_name as customer_name, u.phone as customer_phone,
                   COUNT(oi.id) as items_count
            FROM orders o
            JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($status)) {
            $sql .= " AND o.order_status = ?";
            $params[] = $status;
        }

        if (!empty($date)) {
            $sql .= " AND DATE(o.created_at) = ?";
            $params[] = $date;
        }

        if (!empty($search)) {
            $sql .= " AND (o.order_code LIKE ? OR o.recipient_name LIKE ? OR o.recipient_phone LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " GROUP BY o.id ORDER BY o.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cập nhật trạng thái đơn hàng (Có kiểm tra chuyển trạng thái hợp lệ và Transaction)
     */
    public function updateStatus(int $orderId, string $newStatus, int $changedByUserId, ?string $note = null, ?string $rejectionReason = null): bool {
        $order = $this->findById($orderId);
        if (!$order) {
            throw new Exception("Đơn hàng không tồn tại.");
        }

        $currentStatus = $order['order_status'];

        // Kiểm tra tính hợp lệ của chuyển trạng thái
        $allowedNextStatuses = self::VALID_TRANSITIONS[$currentStatus] ?? [];
        if (!in_array($newStatus, $allowedNextStatuses, true)) {
            throw new Exception("Không thể chuyển trạng thái từ '{$currentStatus}' sang '{$newStatus}'.");
        }

        // Nếu từ chối, bắt buộc phải có lý do
        if ($newStatus === 'REJECTED' && empty(trim($rejectionReason ?? ''))) {
            throw new Exception("Vui lòng nhập lý do từ chối đơn hàng.");
        }

        $this->db->beginTransaction();

        try {
            // Khi đơn chuyển sang DELIVERED -> Đánh dấu payment_status = 'paid'
            $paymentStatus = ($newStatus === 'DELIVERED') ? 'paid' : $order['payment_status'];

            $updateStmt = $this->db->prepare("
                UPDATE orders 
                SET order_status = ?, payment_status = ?, rejection_reason = ?
                WHERE id = ?
            ");
            $updateStmt->execute([
                $newStatus,
                $paymentStatus,
                $rejectionReason ? trim($rejectionReason) : $order['rejection_reason'],
                $orderId
            ]);

            // Thêm lịch sử trạng thái
            $historyStmt = $this->db->prepare("
                INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note)
                VALUES (?, ?, ?, ?, ?)
            ");
            $historyNote = $note ?: ($newStatus === 'REJECTED' ? $rejectionReason : "Chuyển trạng thái sang {$newStatus}");
            $historyStmt->execute([
                $orderId,
                $currentStatus,
                $newStatus,
                $changedByUserId,
                $historyNote
            ]);

            // Nếu đơn chuyển sang DELIVERED -> Tích điểm thưởng cho khách hàng
            if ($newStatus === 'DELIVERED') {
                $pointsEarned = (int)($order['points_earned'] ?? 0);
                if ($pointsEarned > 0) {
                    (new User())->addPoints(
                        (int)$order['user_id'],
                        $pointsEarned,
                        'EARN',
                        "Tích {$pointsEarned} điểm từ đơn hàng {$order['order_code']} giao thành công",
                        $orderId,
                        $this->db
                    );
                }
            }

            // Nếu đơn bị CANCELLED hoặc REJECTED -> Hoàn lại điểm đã dùng và lượt dùng voucher
            if (in_array($newStatus, ['CANCELLED', 'REJECTED'], true)) {
                $pointsUsed = (int)($order['points_used'] ?? 0);
                if ($pointsUsed > 0) {
                    $actionText = ($newStatus === 'CANCELLED') ? 'hủy' : 'bị từ chối';
                    (new User())->addPoints(
                        (int)$order['user_id'],
                        $pointsUsed,
                        'REFUND',
                        "Hoàn lại {$pointsUsed} điểm do đơn hàng {$order['order_code']} {$actionText}",
                        $orderId,
                        $this->db
                    );
                }

                if (!empty($order['voucher_code'])) {
                    (new Voucher())->revertUsage($orderId, $this->db);
                }
            }

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Cập nhật trạng thái thanh toán (unpaid -> paid) bởi Staff hoặc Admin
     */
    public function updatePaymentStatus(int $orderId, string $newPaymentStatus, int $changedByUserId, ?string $note = null): bool {
        if (!in_array($newPaymentStatus, ['unpaid', 'paid'], true)) {
            throw new Exception("Trạng thái thanh toán không hợp lệ.");
        }

        $order = $this->findById($orderId);
        if (!$order) {
            throw new Exception("Đơn hàng không tồn tại.");
        }

        $this->db->beginTransaction();

        try {
            $updateStmt = $this->db->prepare("UPDATE orders SET payment_status = ? WHERE id = ?");
            $updateStmt->execute([$newPaymentStatus, $orderId]);

            $logNote = $note ?: ($newPaymentStatus === 'paid' 
                ? 'Nhân viên đã xác nhận nhận tiền chuyển khoản thành công' 
                : 'Đã đổi trạng thái thanh toán về Chưa thanh toán');

            $historyStmt = $this->db->prepare("
                INSERT INTO order_status_history (order_id, old_status, new_status, changed_by, note)
                VALUES (?, ?, ?, ?, ?)
            ");
            $historyStmt->execute([
                $orderId,
                $order['order_status'],
                $order['order_status'],
                $changedByUserId,
                $logNote
            ]);

            $this->db->commit();
            return true;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Khách hàng tự hủy đơn (chỉ cho phép khi đơn còn PENDING)
     */
    public function cancelByCustomer(string $orderCode, int $userId, string $reason = ''): bool {
        $order = $this->findByCode($orderCode);
        if (!$order) {
            throw new Exception("Không tìm thấy đơn hàng.");
        }

        if ((int)$order['user_id'] !== $userId) {
            throw new Exception("Bạn không có quyền thao tác trên đơn hàng của người khác.");
        }

        if ($order['order_status'] !== 'PENDING') {
            throw new Exception("Đơn hàng đã được nhà hàng tiếp nhận và xử lý, không thể tự hủy.");
        }

        $note = $reason ? "Khách hủy: " . trim($reason) : "Khách hàng tự hủy đơn khi đang chờ duyệt";
        return $this->updateStatus((int)$order['id'], 'CANCELLED', $userId, $note);
    }

    /**
     * Thống kê báo cáo cho Dashboard Quản trị viên
     */
    public function getStatistics(): array {
        // Đếm số đơn theo từng trạng thái
        $statusStmt = $this->db->query("
            SELECT order_status, COUNT(*) as count, SUM(total_amount) as total
            FROM orders 
            GROUP BY order_status
        ");
        $statusRows = $statusStmt->fetchAll();

        $stats = [
            'total_orders'     => 0,
            'pending_orders'   => 0,
            'confirmed_orders' => 0,
            'preparing_orders' => 0,
            'ready_orders'     => 0,
            'delivered_orders' => 0,
            'rejected_orders'  => 0,
            'cancelled_orders' => 0,
            'valid_revenue'    => 0.00, // Chỉ cộng đơn DELIVERED
        ];

        foreach ($statusRows as $row) {
            $st = $row['order_status'];
            $cnt = (int)$row['count'];
            $stats['total_orders'] += $cnt;

            if ($st === 'PENDING')   $stats['pending_orders']   = $cnt;
            if ($st === 'CONFIRMED') $stats['confirmed_orders'] = $cnt;
            if ($st === 'PREPARING') $stats['preparing_orders'] = $cnt;
            if ($st === 'READY')     $stats['ready_orders']     = $cnt;
            if ($st === 'DELIVERED') {
                $stats['delivered_orders'] = $cnt;
                $stats['valid_revenue']    = (float)$row['total'];
            }
            if ($st === 'REJECTED')  $stats['rejected_orders']  = $cnt;
            if ($st === 'CANCELLED') $stats['cancelled_orders'] = $cnt;
        }

        // Lấy top 5 món bán chạy nhất từ các đơn DELIVERED
        $topStmt = $this->db->query("
            SELECT oi.product_name_snapshot as name, SUM(oi.quantity) as total_qty, SUM(oi.line_total) as total_sales
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            WHERE o.order_status = 'DELIVERED'
            GROUP BY oi.product_name_snapshot
            ORDER BY total_qty DESC
            LIMIT 5
        ");
        $stats['top_dishes'] = $topStmt->fetchAll();

        // 5 đơn hàng mới nhất
        $recentStmt = $this->db->query("
            SELECT o.id, o.order_code, o.recipient_name, o.total_amount, o.order_status, o.created_at
            FROM orders o
            ORDER BY o.created_at DESC
            LIMIT 6
        ");
        $stats['recent_orders'] = $recentStmt->fetchAll();

        return $stats;
    }
}
