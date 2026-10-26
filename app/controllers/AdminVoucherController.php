<?php
/**
 * Admin Voucher Controller
 * Quản trị viên quản lý danh sách mã giảm giá, tạo voucher mới và bật/tắt trạng thái
 */

class AdminVoucherController {
    private Voucher $voucherModel;

    public function __construct() {
        $this->voucherModel = new Voucher();
    }

    /**
     * Danh sách tất cả mã giảm giá
     */
    public function index(): void {
        AuthMiddleware::requireRole('admin');

        $vouchers = $this->voucherModel->getAll();

        Response::view('admin.vouchers.index', [
            'pageTitle' => 'Quản lý Mã Giảm Giá & Voucher - Quản trị viên',
            'vouchers'  => $vouchers,
            'csrfToken' => Session::csrfToken()
        ], 'admin');
    }

    /**
     * Tạo mã giảm giá mới
     */
    public function store(): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $code = strtoupper(trim($_POST['code'] ?? ''));
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $discountType = in_array($_POST['discount_type'] ?? '', ['FIXED', 'PERCENT'], true) ? $_POST['discount_type'] : 'FIXED';
        $discountValue = (float)($_POST['discount_value'] ?? 0);
        $maxDiscount = !empty($_POST['max_discount_amount']) ? (float)$_POST['max_discount_amount'] : null;
        $minOrder = (float)($_POST['min_order_amount'] ?? 0);
        $usageLimit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
        $limitPerUser = max(1, (int)($_POST['usage_limit_per_user'] ?? 1));
        $condition = in_array($_POST['customer_condition'] ?? '', ['ALL', 'RETURNING_ONLY', 'NEW_ONLY'], true) ? $_POST['customer_condition'] : 'ALL';
        $endDate = !empty($_POST['end_date']) ? $_POST['end_date'] . ' 23:59:59' : null;

        if (empty($code) || empty($title) || $discountValue <= 0) {
            Session::flash('error', 'Vui lòng nhập đầy đủ mã code, tên voucher và giá trị giảm hợp lệ (> 0).');
            Response::redirect('admin/vouchers');
            return;
        }

        // Kiểm tra xem code đã tồn tại chưa
        if ($this->voucherModel->findByCode($code)) {
            Session::flash('error', "Mã voucher '{$code}' đã tồn tại trong hệ thống. Vui lòng chọn mã khác.");
            Response::redirect('admin/vouchers');
            return;
        }

        try {
            $this->voucherModel->create([
                'code'                 => $code,
                'title'                => $title,
                'description'          => $description,
                'discount_type'        => $discountType,
                'discount_value'       => $discountValue,
                'max_discount_amount'  => $maxDiscount,
                'min_order_amount'     => $minOrder,
                'usage_limit'          => $usageLimit,
                'usage_limit_per_user' => $limitPerUser,
                'customer_condition'   => $condition,
                'end_date'             => $endDate,
                'status'               => 'active'
            ]);

            Session::flash('success', "Đã tạo thành công mã giảm giá '{$code}'.");
        } catch (Throwable $e) {
            Session::flash('error', "Không thể tạo voucher: " . $e->getMessage());
        }

        Response::redirect('admin/vouchers');
    }

    /**
     * Bật / Tắt trạng thái hoạt động của Voucher
     */
    public function toggleStatus(int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        try {
            $this->voucherModel->toggleStatus($id);
            Session::flash('success', "Đã cập nhật trạng thái hoạt động của mã giảm giá.");
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('admin/vouchers');
    }

    /**
     * Xóa hoặc hủy kích hoạt Voucher
     */
    public function delete(int $id): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        try {
            $this->voucherModel->delete($id);
            Session::flash('success', "Đã xử lý xóa / ngừng kích hoạt mã voucher thành công.");
        } catch (Throwable $e) {
            Session::flash('error', $e->getMessage());
        }

        Response::redirect('admin/vouchers');
    }
}
