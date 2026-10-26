<?php
/**
 * Staff Order Controller
 * Xử lý danh sách đơn hàng cho nhân viên, xác nhận, từ chối và cập nhật tiến trình chế biến/giao hàng
 */

class StaffOrderController {
    private Order $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    /**
     * Màn hình danh sách đơn hàng cho nhân viên
     */
    public function index(): void {
        $user = AuthMiddleware::requireRole(['staff', 'admin']);

        $status = $_GET['status'] ?? null;
        $date = $_GET['date'] ?? null;
        $search = trim($_GET['search'] ?? '');

        $orders = $this->orderModel->getAllOrders($status, $date, $search);

        Response::view('staff.orders', [
            'pageTitle'     => 'Xử lý đơn hàng - Nhân viên',
            'user'          => $user,
            'orders'        => $orders,
            'currentStatus' => $status,
            'currentDate'   => $date,
            'search'        => $search,
            'csrfToken'     => Session::csrfToken()
        ], 'admin');
    }

    /**
     * API xem chi tiết đơn hàng (dành cho Drawer / Modal xem nhanh)
     */
    public function detail(string|int $id): void {
        AuthMiddleware::requireRole(['staff', 'admin'], true);

        $order = $this->orderModel->findById((int)$id);
        if (!$order) {
            Response::json(['success' => false, 'message' => 'Không tìm thấy đơn hàng.'], 404);
        }

        $items = $this->orderModel->getOrderItems((int)$id);
        $history = $this->orderModel->getOrderStatusHistory((int)$id);

        Response::json([
            'success' => true,
            'order'   => $order,
            'items'   => $items,
            'history' => $history
        ], 200);
    }

    /**
     * Cập nhật trạng thái đơn hàng (Xác nhận, Nấu, Sẵn sàng, Đã giao, Từ chối)
     */
    public function updateStatus(): void {
        $user = AuthMiddleware::requireRole(['staff', 'admin']);
        CsrfMiddleware::handle();

        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = trim($_POST['new_status'] ?? '');
        $rejectionReason = trim($_POST['rejection_reason'] ?? '');
        $note = trim($_POST['note'] ?? '');

        try {
            $this->orderModel->updateStatus($orderId, $newStatus, (int)$user['id'], $note, $rejectionReason);
            Session::flash('success', "Đã cập nhật trạng thái đơn sang '{$newStatus}' thành công.");
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('staff/orders');
    }

    /**
     * Cập nhật trạng thái thanh toán (ví dụ: Xác nhận đã nhận tiền chuyển khoản)
     */
    public function updatePaymentStatus(): void {
        $user = AuthMiddleware::requireRole(['staff', 'admin']);
        CsrfMiddleware::handle();

        $orderId = (int)($_POST['order_id'] ?? 0);
        $paymentStatus = trim($_POST['payment_status'] ?? 'paid');
        $note = trim($_POST['note'] ?? '');

        try {
            $this->orderModel->updatePaymentStatus($orderId, $paymentStatus, (int)$user['id'], $note);
            $msg = ($paymentStatus === 'paid') 
                ? "Đã xác nhận thanh toán thành công cho đơn hàng." 
                : "Đã cập nhật trạng thái thanh toán.";
            Session::flash('success', $msg);
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('staff/orders');
    }
}
