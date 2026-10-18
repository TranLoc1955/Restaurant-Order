<div class="container" style="padding: 2.5rem 1.5rem 4rem;">
    <!-- Breadcrumb -->
    <nav style="font-size:0.875rem; color:var(--text-muted); margin-bottom:1.5rem;">
        <a href="<?= Response::baseUrl('cart') ?>">&larr; Quay lại giỏ hàng</a>
    </nav>

    <h1 style="margin-bottom: 0.5rem;">Xác Nhận Đặt Hàng</h1>
    <p class="text-muted" style="margin-bottom: 2rem;">Vui lòng nhập thông tin người nhận để nhà hàng chế biến và giao hàng nhanh nhất</p>

    <form action="<?= Response::baseUrl('checkout') ?>" method="POST" data-loading novalidate>
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

        <div style="display:grid; grid-template-columns: 1.4fr 1fr; gap: 2.5rem; align-items:flex-start;">
            <!-- Cột thông tin nhận hàng -->
            <div class="card" style="padding: 2rem;">
                <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    1. Thông Tin Nhận Hàng
                </h3>

                <!-- Họ tên người nhận -->
                <div class="form-group">
                    <label for="checkout-name" class="form-label">Họ và tên người nhận <span class="required">*</span></label>
                    <input 
                        type="text" 
                        name="recipient_name" 
                        id="checkout-name" 
                        class="form-control" 
                        placeholder="Ví dụ: Trần Minh Tuấn"
                        value="<?= htmlspecialchars($user['full_name'] ?? '') ?>"
                        required
                    >
                </div>

                <!-- Số điện thoại nhận hàng -->
                <div class="form-group">
                    <label for="checkout-phone" class="form-label">Số điện thoại liên hệ nhận hàng <span class="required">*</span></label>
                    <input 
                        type="tel" 
                        name="recipient_phone" 
                        id="checkout-phone" 
                        class="form-control" 
                        placeholder="0912345678"
                        value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                        required
                    >
                    <div class="form-hint">Shipper sẽ gọi số này khi giao món. Nhập 10 số bắt đầu bằng 03, 05, 07, 08, 09.</div>
                </div>

                <!-- Địa chỉ giao hàng -->
                <div class="form-group">
                    <label for="checkout-address" class="form-label">Địa chỉ nhận hàng chi tiết <span class="required">*</span></label>
                    <input 
                        type="text" 
                        name="delivery_address" 
                        id="checkout-address" 
                        class="form-control" 
                        placeholder="Số nhà, tên đường, phường, quận..."
                        required
                    >
                </div>

                <!-- Ghi chú cho nhà hàng / shipper -->
                <div class="form-group">
                    <label for="checkout-note" class="form-label">Ghi chú cho nhà hàng / đầu bếp (Tùy chọn)</label>
                    <textarea 
                        name="note" 
                        id="checkout-note" 
                        rows="3" 
                        class="form-control" 
                        placeholder="Ví dụ: Làm ít cay, xin thêm nước chấm, gọi trước khi đến 10 phút..."
                    ></textarea>
                </div>

                <h3 style="margin: 2rem 0 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    2. Phương Thức Thanh Toán
                </h3>

                <div style="padding: 1rem 1.25rem; background-color: var(--surface-hover); border: 1.5px solid var(--primary-color); border-radius: var(--radius-md); display:flex; align-items:center; gap:0.75rem;">
                    <input type="radio" name="payment_method" value="COD" checked id="pay-cod">
                    <label for="pay-cod" style="font-weight: 700; cursor:pointer;">
                        💵 Thanh toán tiền mặt khi nhận hàng (COD)
                    </label>
                </div>
            </div>

            <!-- Cột tóm tắt đơn hàng -->
            <div class="card" style="padding: 2rem;">
                <h3 style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    Món Đã Chọn (<?= count($items) ?> món)
                </h3>

                <div style="display:flex; flex-direction:column; gap:0.875rem; margin-bottom:1.5rem; max-height:260px; overflow-y:auto;">
                    <?php foreach ($items as $item): ?>
                        <div class="d-flex justify-between align-center" style="font-size:0.875rem;">
                            <div>
                                <span class="font-bold"><?= $item['quantity'] ?>x</span> 
                                <span><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                            <span class="font-bold"><?= number_format($item['line_total'], 0, ',', '.') ?>đ</span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="border-top:1px solid var(--border-color); padding-top:1rem; margin-bottom:1.5rem;">
                    <div class="d-flex justify-between mb-2">
                        <span class="text-muted">Tạm tính:</span>
                        <span class="font-bold"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                    </div>
                    <div class="d-flex justify-between mb-3">
                        <span class="text-muted">Phí giao hàng:</span>
                        <span class="font-bold"><?= number_format($shippingFee, 0, ',', '.') ?>đ</span>
                    </div>
                    <div class="d-flex justify-between align-center" style="border-top:1px dashed var(--border-color); padding-top:0.75rem;">
                        <span style="font-size:1.1rem; font-weight:700;">Tổng thanh toán:</span>
                        <span style="font-size:1.5rem; font-weight:800; color:var(--primary-color);">
                            <?= number_format($total, 0, ',', '.') ?>đ
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-submit-order">
                    ✓ Xác nhận đặt hàng (COD)
                </button>

                <p class="text-muted text-center mt-2" style="font-size:0.75rem;">
                    Nhấn "Xác nhận đặt hàng" đồng nghĩa với việc bạn đồng ý thanh toán số tiền trên khi nhận món ăn từ shipper.
                </p>
            </div>
        </div>
    </form>
</div>
