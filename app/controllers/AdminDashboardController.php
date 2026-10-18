<?php
/**
 * Admin Dashboard Controller
 * Thống kê tổng quan doanh thu hợp lệ, tiến độ đơn hàng và món bán chạy
 */

class AdminDashboardController {
    private Order $orderModel;

    public function __construct() {
        $this->orderModel = new Order();
    }

    public function index(): void {
        $user = AuthMiddleware::requireRole('admin');
        $stats = $this->orderModel->getStatistics();

        Response::view('admin.dashboard', [
            'pageTitle' => 'Tổng quan Dashboard - Quản trị viên',
            'user'      => $user,
            'stats'     => $stats,
        ], 'admin');
    }
}
