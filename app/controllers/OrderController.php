<?php
/**
 * Order Controller
 * Xử lý đặt hàng COD, trang cảm ơn, xem lịch sử đơn và hủy đơn hàng
 */

class OrderController {
    private Order $orderModel;
    private CartService $cartService;
    private RestaurantSetting $settingModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->cartService = new CartService();
        $this->settingModel = new RestaurantSetting();
    }

    /**
     * Hiển thị trang thanh toán và đặt hàng
     */
    public function showCheckout(): void {
        $user = AuthMiddleware::requireLogin();
        $items = $this->cartService->getItems();

        if (empty($items)) {
            Session::flash('warning', 'Giỏ hàng của bạn đang trống. Vui lòng chọn món trước khi đặt hàng.');
            Response::redirect('menu');
            return;
        }

        $subtotal = $this->cartService->getSubtotal();
        $settings = $this->settingModel->getSettings();
        $shippingFee = (float)$settings['delivery_fee'];
        $minAmount = (float)$settings['minimum_order_amount'];

        if ($subtotal < $minAmount) {
            Session::flash('error', "Giá trị đơn hàng tối thiểu là " . number_format($minAmount, 0, ',', '.') . "đ. Bạn cần thêm món để tiếp tục.");
            Response::redirect('cart');
            return;
        }

        $total = $subtotal + $shippingFee;

        Response::view('customer.checkout', [
            'pageTitle'   => 'Thanh toán & Đặt hàng - Nhà Hàng Trần Lộc',
            'user'        => $user,
            'items'       => $items,
            'subtotal'    => $subtotal,
            'shippingFee' => $shippingFee,
            'total'       => $total,
            'settings'    => $settings,
            'csrfToken'   => Session::csrfToken()
        ], 'main');
    }

    /**
     * Xử lý gửi đơn hàng COD
     */
    public function processCheckout(): void {
        $user = AuthMiddleware::requireLogin();
        CsrfMiddleware::handle();

        $recipientName = trim($_POST['recipient_name'] ?? '');
        $recipientPhone = trim($_POST['recipient_phone'] ?? '');
        $deliveryAddress = trim($_POST['delivery_address'] ?? '');
        $note = trim($_POST['note'] ?? '');

        $validator = Validator::make($_POST)
            ->required('recipient_name', 'Họ tên người nhận')
            ->minLength('recipient_name', 2, 'Họ tên người nhận')
            ->required('recipient_phone', 'Số điện thoại nhận hàng')
            ->phone('recipient_phone', 'Số điện thoại nhận hàng')
            ->required('delivery_address', 'Địa chỉ giao hàng')
            ->minLength('delivery_address', 5, 'Địa chỉ giao hàng');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('checkout');
            return;
        }

        $items = $this->cartService->getItems();
        if (empty($items)) {
            Session::flash('error', 'Giỏ hàng đang trống.');
            Response::redirect('cart');
            return;
        }

        try {
            $result = $this->orderModel->createOrder([
                'recipient_name'   => $recipientName,
                'recipient_phone'  => $recipientPhone,
                'delivery_address' => $deliveryAddress,
                'note'             => $note,
            ], $items, (int)$user['id']);

            // Xóa sạch giỏ hàng sau khi tạo đơn thành công
            $this->cartService->clear();

            Session::flash('success', "Đặt hàng thành công! Mã đơn của bạn là {$result['order_code']}.");
            Response::redirect('order-success/' . $result['order_code']);
        } catch (Throwable $e) {
            Session::flash('error', "Không thể tạo đơn hàng: " . $e->getMessage());
            Response::redirect('checkout');
        }
    }

    /**
     * Trang xác nhận đặt hàng thành công
     */
    public function success(string $orderCode): void {
        $user = AuthMiddleware::requireLogin();
        $order = $this->orderModel->findByCode($orderCode);

        if (!$order) {
            Response::abort(404, 'Không tìm thấy thông tin đơn hàng.');
        }

        // Kiểm tra quyền sở hữu đơn hàng
        if ((int)$order['user_id'] !== (int)$user['id'] && $user['role'] === 'customer') {
            Response::abort(403, 'Bạn không có quyền xem đơn hàng này.');
        }

        $items = $this->orderModel->getOrderItems((int)$order['id']);
        $settings = $this->settingModel->getSettings();

        Response::view('customer.order-success', [
            'pageTitle' => 'Đặt hàng thành công - ' . $order['order_code'],
            'order'     => $order,
            'items'     => $items,
            'settings'  => $settings,
        ], 'main');
    }

    /**
     * Lịch sử đơn hàng của khách hàng
     */
    public function history(): void {
        $user = AuthMiddleware::requireLogin();
        $orders = $this->orderModel->getOrdersByUserId((int)$user['id']);

        Response::view('customer.order-history', [
            'pageTitle' => 'Lịch sử đơn hàng của bạn',
            'orders'    => $orders,
            'user'      => $user,
        ], 'main');
    }

    /**
     * Xem chi tiết một đơn hàng cụ thể
     */
    public function detail(string $orderCode): void {
        $user = AuthMiddleware::requireLogin();
        $order = $this->orderModel->findByCode($orderCode);

        if (!$order) {
            Response::abort(404, 'Không tìm thấy thông tin đơn hàng.');
        }

        // Kiểm tra quyền sở hữu đơn hàng (Khách chỉ được xem đơn của mình, Staff/Admin được xem tất cả)
        if ($user['role'] === 'customer' && (int)$order['user_id'] !== (int)$user['id']) {
            Response::abort(403, 'Bạn không có quyền truy cập vào đơn hàng của người khác.');
        }

        $items = $this->orderModel->getOrderItems((int)$order['id']);
        $history = $this->orderModel->getOrderStatusHistory((int)$order['id']);
        $settings = $this->settingModel->getSettings();

        Response::view('customer.order-detail', [
            'pageTitle' => 'Chi tiết đơn hàng ' . $order['order_code'],
            'order'     => $order,
            'items'     => $items,
            'history'   => $history,
            'settings'  => $settings,
            'csrfToken' => Session::csrfToken()
        ], 'main');
    }

    /**
     * Khách hàng tự hủy đơn hàng khi đơn còn ở PENDING
     */
    public function cancel(string $orderCode): void {
        $user = AuthMiddleware::requireLogin();
        CsrfMiddleware::handle();

        $reason = trim($_POST['cancel_reason'] ?? 'Khách hàng đổi ý');

        try {
            $this->orderModel->cancelByCustomer($orderCode, (int)$user['id'], $reason);
            Session::flash('success', "Đã hủy đơn hàng {$orderCode} thành công.");
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('orders/' . $orderCode);
    }
}
