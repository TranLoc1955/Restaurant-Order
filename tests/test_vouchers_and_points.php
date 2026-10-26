<?php
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

spl_autoload_register(function (string $className) {
    $directories = [
        APP_PATH . '/helpers/',
        APP_PATH . '/middleware/',
        APP_PATH . '/models/',
        APP_PATH . '/controllers/',
        APP_PATH . '/services/',
    ];

    foreach ($directories as $dir) {
        $file = $dir . $className . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

echo "=== BẮT ĐẦU KIỂM TRA CHỨC NĂNG VOUCHER & TÍCH ĐIỂM ===\n\n";

$db = Database::getConnection();
$userModel = new User();
$voucherModel = new Voucher();
$orderModel = new Order();

// 1. Kiểm tra tài khoản test
$user = $userModel->findByEmail('customer@gmail.com');
$userId = (int)$user['id'];
echo "[1] Khách hàng ID: {$userId} ({$user['full_name']})\n";

// A. Test trạng thái Khách hàng MỚI (chưa có đơn DELIVERED)
echo "\n--- TEST A: KHÁCH HÀNG MỚI (Chưa từng có đơn DELIVERED) ---\n";
$deliveredCount = $voucherModel->countCustomerDeliveredOrders($userId);
echo "    -> Số đơn DELIVERED: {$deliveredCount}\n";

$valNew = $voucherModel->validateVoucher('BANMOI20K', $userId, 100000.0);
echo "    -> Mã BANMOI20K (NEW_ONLY): " . ($valNew['valid'] ? 'HỢP LỆ (Chuẩn)' : 'KHÔNG HỢP LỆ') . " | {$valNew['message']}\n";
assert($valNew['valid'] === true, "Khách mới phải dùng được BANMOI20K");

$valOld = $voucherModel->validateVoucher('TRIANKHACHCU', $userId, 150000.0);
echo "    -> Mã TRIANKHACHCU (RETURNING_ONLY): " . (!$valOld['valid'] ? 'CHẶN THÀNH CÔNG (Chuẩn)' : 'LỖI') . " | {$valOld['message']}\n";
assert($valOld['valid'] === false, "Khách mới không được dùng TRIANKHACHCU");

// B. Tạo 1 đơn DELIVERED trong quá khứ để trở thành KHÁCH HÀNG THÂN THIẾT
echo "\n--- TEST B: NÂNG CẤP THÀNH KHÁCH HÀNG THÂN THIẾT (Có đơn DELIVERED) ---\n";
$db->query("
    INSERT INTO orders (
        order_code, user_id, recipient_name, recipient_phone, delivery_address,
        subtotal, shipping_fee, total_amount, payment_method, payment_status, order_status
    ) VALUES (
        'ORD-TEST-HISTORY', {$userId}, 'Khách Cũ', '0901234567', 'Địa chỉ cũ',
        200000, 15000, 215000, 'COD', 'paid', 'DELIVERED'
    )
");

$deliveredOrders = $voucherModel->countCustomerDeliveredOrders($userId);
echo "    -> Số đơn DELIVERED hiện tại: {$deliveredOrders}\n";
assert($deliveredOrders >= 1, "User phải có ít nhất 1 đơn DELIVERED");

// Bây giờ kiểm tra lại mã TRIANKHACHCU
$valReturning = $voucherModel->validateVoucher('TRIANKHACHCU', $userId, 150000.0);
echo "    -> Mã TRIANKHACHCU (RETURNING_ONLY): " . ($valReturning['valid'] ? 'HỢP LỆ (Chuẩn)' : 'KHÔNG HỢP LỆ') . " | {$valReturning['message']}\n";
assert($valReturning['valid'] === true, "Khách cũ phải dùng được TRIANKHACHCU");

$valNewNow = $voucherModel->validateVoucher('BANMOI20K', $userId, 100000.0);
echo "    -> Mã BANMOI20K (NEW_ONLY): " . (!$valNewNow['valid'] ? 'CHẶN THÀNH CÔNG (Chuẩn)' : 'LỖI') . " | {$valNewNow['message']}\n";
assert($valNewNow['valid'] === false, "Khách cũ không được dùng voucher NEW_ONLY nữa");

// Lấy điểm ban đầu
$initialPoints = $userModel->getRewardPoints($userId);
if ($initialPoints < 50) {
    $userModel->addPoints($userId, 50, 'BONUS', 'Cấp điểm test');
    $initialPoints = $userModel->getRewardPoints($userId);
}
echo "    -> Điểm tích lũy hiện tại: {$initialPoints} điểm\n";

// 4. Lấy một món ăn in_stock để tạo đơn test
$pStmt = $db->query("SELECT id, name, price FROM products WHERE availability_status = 'in_stock' ORDER BY price DESC LIMIT 1");
$prod = $pStmt->fetch();
if (!$prod) {
    echo "Không có món ăn trong kho để test.\n";
    exit(1);
}
echo "\n[4] Chọn món test: {$prod['name']} (Giá: " . number_format($prod['price']) . "đ)\n";

$cartItems = [
    [
        'product_id' => $prod['id'],
        'quantity'   => 2,
    ]
];

$pointsToUse = 20; // Dùng 20 điểm = 20.000đ
echo "    -> Đặt đơn với Voucher TRIANKHACHCU (-30.000đ) và dùng {$pointsToUse} điểm (-20.000đ)...\n";

$orderResult = $orderModel->createOrder([
    'recipient_name'   => 'Test Khách Hàng',
    'recipient_phone'  => '0901234567',
    'delivery_address' => '123 Đường Test, Quận 1',
    'note'             => 'Đơn test tự động voucher & điểm',
    'payment_method'   => 'BANK_TRANSFER',
    'voucher_code'     => 'TRIANKHACHCU',
    'points_used'      => $pointsToUse,
], $cartItems, $userId);

$orderId = (int)$orderResult['id'];
$orderCode = $orderResult['order_code'];
echo "    -> Đã tạo đơn thành công: ID {$orderId}, Mã: {$orderCode}\n";
echo "    -> Tạm tính: " . number_format($orderResult['subtotal']) . "đ\n";
echo "    -> Giảm voucher: -" . number_format($orderResult['discount_amount']) . "đ\n";
echo "    -> Giảm tích điểm: -" . number_format($orderResult['points_discount']) . "đ\n";
echo "    -> Phí ship: " . number_format($orderResult['shipping_fee']) . "đ\n";
echo "    -> Tổng thanh toán: " . number_format($orderResult['total_amount']) . "đ\n";
echo "    -> Điểm thưởng dự kiến: +{$orderResult['points_earned']} điểm\n";

// Kiểm tra số điểm người dùng sau khi trừ
$pointsAfterOrder = $userModel->getRewardPoints($userId);
echo "    -> Số điểm còn lại của user: {$pointsAfterOrder} điểm (Đã trừ {$pointsToUse} điểm)\n";
assert($pointsAfterOrder === ($initialPoints - $pointsToUse), "Điểm phải bị trừ đúng sau khi đặt đơn");

// 5. Kiểm tra bảng voucher_usages
$usageStmt = $db->prepare("SELECT * FROM voucher_usages WHERE order_id = ?");
$usageStmt->execute([$orderId]);
$usage = $usageStmt->fetch();
echo "\n[5] Kiểm tra bản ghi voucher_usages:\n";
echo "    -> Voucher ID: {$usage['voucher_id']}, Giảm: " . number_format($usage['discount_amount']) . "đ, Ngày dùng: {$usage['used_at']}\n";
assert(!empty($usage), "Phải có bản ghi trong voucher_usages");

// 6. Test chuyển trạng thái sang DELIVERED -> Điểm thưởng points_earned phải được cộng cho khách
echo "\n[6] Mô phỏng chuyển trạng thái sang DELIVERED:\n";
// Order flow: PENDING -> CONFIRMED -> PREPARING -> READY -> DELIVERED
$orderModel->updateStatus($orderId, 'CONFIRMED', 1, 'Xác nhận đơn');
$orderModel->updateStatus($orderId, 'PREPARING', 1, 'Đầu bếp đang nấu');
$orderModel->updateStatus($orderId, 'READY', 1, 'Món đã sẵn sàng');
$orderModel->updateStatus($orderId, 'DELIVERED', 1, 'Đã giao thành công');

$pointsAfterDelivered = $userModel->getRewardPoints($userId);
$expectedPoints = $pointsAfterOrder + (int)$orderResult['points_earned'];
echo "    -> Số điểm sau khi DELIVERED: {$pointsAfterDelivered} điểm (Kỳ vọng: {$expectedPoints} điểm)\n";
assert($pointsAfterDelivered === $expectedPoints, "Điểm thưởng phải được cộng khi đơn DELIVERED");

// 7. Test Hủy đơn và Hoàn điểm thưởng
echo "\n[7] Test hoàn điểm khi đơn bị hủy (CANCELLED):\n";
$orderCancelResult = $orderModel->createOrder([
    'recipient_name'   => 'Test Hủy Đơn',
    'recipient_phone'  => '0901234567',
    'delivery_address' => '456 Đường Hủy, Quận 3',
    'payment_method'   => 'COD',
    'points_used'      => 10,
], $cartItems, $userId);

$cancelOrderId = (int)$orderCancelResult['id'];
$pointsBeforeCancel = $userModel->getRewardPoints($userId);
echo "    -> Tạo đơn ID {$cancelOrderId} dùng 10 điểm. Điểm trước hủy: {$pointsBeforeCancel} điểm\n";

$orderModel->updateStatus($cancelOrderId, 'CANCELLED', $userId, 'Khách hàng đổi ý');
$pointsAfterCancel = $userModel->getRewardPoints($userId);
echo "    -> Sau khi CANCELLED, điểm của user: {$pointsAfterCancel} điểm (Được hoàn lại 10 điểm)\n";
assert($pointsAfterCancel === ($pointsBeforeCancel + 10), "Phải hoàn trả 10 điểm khi đơn bị hủy");

// 8. Test Chatbot AI phản hồi câu hỏi về Voucher và Tích điểm
echo "\n[8] Test phản hồi của Chatbot AI khi khách hỏi về voucher và tích điểm:\n";
$ai = new AIService();
$res = $ai->ask("Quán có mã giảm giá hay tích điểm cho khách quen không?", "test-session-123", $userId);
echo "    -> Câu trả lời của Chatbot:\n" . substr($res['reply'], 0, 300) . "...\n";
assert(str_contains($res['reply'], 'TRIANKHACHCU') || str_contains($res['reply'], 'Tích Điểm'), "Chatbot phải trả lời về chương trình voucher và tích điểm");

echo "\n=======================================================\n";
echo "✓ TẤT CẢ CÁC BƯỚC KIỂM THỬ ĐÃ THÀNH CÔNG VƯỢT TRỘI 100%!\n";
echo "=======================================================\n";
