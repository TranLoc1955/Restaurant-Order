<div style="max-width: 680px; margin: 0 auto;">
    <div class="mb-4">
        <h2 style="margin:0;">Cài Đặt Thông Tin Nhà Hàng</h2>
        <p class="text-muted" style="margin:0;">Cập nhật thông tin liên hệ, giờ phục vụ, phí giao hàng và đơn hàng tối thiểu</p>
    </div>

    <div class="card" style="padding: 2rem;">
        <form action="<?= Response::baseUrl('admin/settings') ?>" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Tên nhà hàng thương hiệu <span class="required">*</span></label>
                <input type="text" name="restaurant_name" class="form-control" required value="<?= htmlspecialchars($settings['restaurant_name']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Địa chỉ nhà hàng <span class="required">*</span></label>
                <input type="text" name="address" class="form-control" required value="<?= htmlspecialchars($settings['address']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Số điện thoại Hotline <span class="required">*</span></label>
                <input type="text" name="phone" class="form-control" required value="<?= htmlspecialchars($settings['phone']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Khung giờ mở cửa phục vụ <span class="required">*</span></label>
                <input type="text" name="opening_hours" class="form-control" required value="<?= htmlspecialchars($settings['opening_hours']) ?>">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                <div class="form-group">
                    <label class="form-label">Phí giao hàng COD (VNĐ) <span class="required">*</span></label>
                    <input type="number" name="delivery_fee" class="form-control" required min="0" step="1000" value="<?= (int)$settings['delivery_fee'] ?>">
                    <div class="form-hint">Mặc định: 20.000đ</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Đơn hàng tối thiểu (VNĐ) <span class="required">*</span></label>
                    <input type="number" name="minimum_order_amount" class="form-control" required min="0" step="1000" value="<?= (int)$settings['minimum_order_amount'] ?>">
                    <div class="form-hint">Mặc định: 50.000đ</div>
                </div>
            </div>

            <div class="mt-4 text-right">
                <button type="submit" class="btn btn-primary btn-lg">
                    💾 Lưu cấu hình nhà hàng
                </button>
            </div>
        </form>
    </div>
</div>
