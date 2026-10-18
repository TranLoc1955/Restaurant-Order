<?php
/**
 * Cart Controller
 * Xử lý xem giỏ hàng và các API AJAX thêm, sửa, xóa món
 */

class CartController {
    private CartService $cartService;
    private RestaurantSetting $settingModel;

    public function __construct() {
        $this->cartService = new CartService();
        $this->settingModel = new RestaurantSetting();
    }

    /**
     * Xem giỏ hàng
     */
    public function index(): void {
        $items = $this->cartService->getItems();
        $subtotal = $this->cartService->getSubtotal();
        $settings = $this->settingModel->getSettings();
        $shippingFee = (float)$settings['delivery_fee'];
        $minAmount = (float)$settings['minimum_order_amount'];
        $total = $subtotal > 0 ? $subtotal + $shippingFee : 0;

        Response::view('customer.cart', [
            'pageTitle'   => 'Giỏ hàng của bạn - Nhà Hàng Trần Lộc',
            'items'       => $items,
            'subtotal'    => $subtotal,
            'shippingFee' => $shippingFee,
            'minAmount'   => $minAmount,
            'total'       => $total,
            'settings'    => $settings,
        ], 'main');
    }

    /**
     * API Thêm món vào giỏ
     */
    public function add(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);

        try {
            $result = $this->cartService->add($productId, $quantity);
            Response::json($result, 200);
        } catch (Throwable $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API Cập nhật số lượng
     */
    public function update(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $productId = (int)($input['product_id'] ?? 0);
        $quantity = (int)($input['quantity'] ?? 1);

        try {
            $result = $this->cartService->update($productId, $quantity);
            Response::json($result, 200);
        } catch (Throwable $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API Xóa món
     */
    public function remove(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $productId = (int)($input['product_id'] ?? 0);

        try {
            $result = $this->cartService->remove($productId);
            Response::json($result, 200);
        } catch (Throwable $e) {
            Response::json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * API Lấy số lượng món trong giỏ (dành cho badge header)
     */
    public function count(): void {
        Response::json([
            'count'    => $this->cartService->getTotalCount(),
            'subtotal' => $this->cartService->getSubtotal()
        ], 200);
    }
}
