<?php
/**
 * Admin Setting Controller
 * Cấu hình thông tin nhà hàng, phí giao hàng và ngưỡng giá trị đơn hàng tối thiểu
 */

class AdminSettingController {
    private RestaurantSetting $settingModel;

    public function __construct() {
        $this->settingModel = new RestaurantSetting();
    }

    public function index(): void {
        AuthMiddleware::requireRole('admin');
        $settings = $this->settingModel->getSettings();

        Response::view('admin.settings.index', [
            'pageTitle' => 'Cài đặt Nhà hàng - Quản trị viên',
            'settings'  => $settings,
            'csrfToken' => Session::csrfToken()
        ], 'admin');
    }

    public function update(): void {
        AuthMiddleware::requireRole('admin');
        CsrfMiddleware::handle();

        $restaurantName = trim($_POST['restaurant_name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $openingHours = trim($_POST['opening_hours'] ?? '');
        $deliveryFee = (float)($_POST['delivery_fee'] ?? 0);
        $minimumOrderAmount = (float)($_POST['minimum_order_amount'] ?? 0);

        $validator = Validator::make($_POST)
            ->required('restaurant_name', 'Tên nhà hàng')
            ->required('address', 'Địa chỉ')
            ->required('phone', 'Số điện thoại liên hệ')
            ->required('opening_hours', 'Giờ mở cửa')
            ->nonNegativeInteger('delivery_fee', 'Phí giao hàng')
            ->nonNegativeInteger('minimum_order_amount', 'Đơn hàng tối thiểu');

        if ($validator->fails()) {
            Session::flash('error', $validator->firstError());
            Response::redirect('admin/settings');
            return;
        }

        try {
            $this->settingModel->updateSettings([
                'restaurant_name'      => $restaurantName,
                'address'              => $address,
                'phone'                => $phone,
                'opening_hours'        => $openingHours,
                'delivery_fee'         => $deliveryFee,
                'minimum_order_amount' => $minimumOrderAmount,
            ]);
            Session::flash('success', "Đã cập nhật thông tin nhà hàng thành công.");
        } catch (Throwable $e) {
            Session::flash('error', "Lỗi lưu cấu hình: " . $e->getMessage());
        }

        Response::redirect('admin/settings');
    }
}
