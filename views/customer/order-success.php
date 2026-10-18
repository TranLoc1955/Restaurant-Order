<div class="container container-md" style="padding: 3.5rem 1.5rem 5rem;">
    <div class="card text-center" style="padding: 3rem 2rem; margin-bottom: 2rem;">
        <div style="width: 72px; height: 72px; border-radius: 50%; background-color: var(--success-light); color: var(--success-color); display: flex; align-items: center; justify-content: center; font-size: 2.25rem; margin: 0 auto 1.5rem;">
            ✓
        </div>

        <h1 style="color: var(--success-color); font-size: 2rem; margin-bottom: 0.5rem;">Đặt Hàng Thành Công!</h1>
        <p class="text-muted" style="max-width: 520px; margin: 0 auto 1.5rem;">
            Cảm ơn bạn đã đặt món tại <strong><?= htmlspecialchars($settings['restaurant_name']) ?></strong>. Đầu bếp đã nhận đơn và sẽ chuẩn bị món ăn tươi nóng ngay bây giờ.
        </p>

        <!-- Khung Mã đơn hàng nổi bật -->
        <div style="background-color: var(--surface-hover); border: 2px dashed var(--primary-color); border-radius: var(--radius-lg); padding: 1.25rem; max-width: 420px; margin: 0 auto 2rem;">
            <div style="font-size: 0.8125rem; text-transform: uppercase; color: var(--text-muted); font-weight: 700; margin-bottom: 0.25rem;">
                Mã đơn hàng của bạn
            </div>
            <div style="font-size: 1.625rem; font-weight: 800; color: var(--primary-color); letter-spacing: 0.05em;" id="order-code-display">
                <?= htmlspecialchars($order['order_code']) ?>
            </div>
        </div>

        <!-- Thông tin giao hàng tóm tắt -->
        <div style="text-align: left; background-color: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; max-width: 580px; margin: 0 auto 2rem;">
            <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                Thông Tin Giao Hàng & Thanh Toán (COD)
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.875rem;">
                <div><strong>Người nhận:</strong> <?= htmlspecialchars($order['recipient_name']) ?></div>
                <div><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['recipient_phone']) ?></div>
                <div style="grid-column: 1 / -1;"><strong>Địa chỉ giao:</strong> <?= htmlspecialchars($order['delivery_address']) ?></div>
                <?php if (!empty($order['note'])): ?>
                    <div style="grid-column: 1 / -1;"><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></div>
                <?php endif; ?>
                <div><strong>Phương thức:</strong> Tiền mặt khi nhận hàng (COD)</div>
                <div><strong>Tổng tiền thanh toán:</strong> <span class="font-bold text-primary"><?= number_format((float)$order['total_amount'], 0, ',', '.') ?>đ</span></div>
            </div>
        </div>

        <!-- Danh sách món đã đặt -->
        <div style="text-align: left; max-width: 580px; margin: 0 auto 2rem;">
            <h4 style="margin-bottom: 0.75rem;">Món ăn đã đặt:</h4>
            <div style="border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.875rem;">
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr style="border-bottom: 1px solid var(--border-color);">
                                <td style="padding: 0.75rem 1rem;">
                                    <strong><?= $item['quantity'] ?>x</strong> <?= htmlspecialchars($item['product_name_snapshot']) ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700;">
                                    <?= number_format((float)$item['line_total'], 0, ',', '.') ?>đ
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Nút hành động -->
        <div class="d-flex justify-center gap-1">
            <a href="<?= Response::baseUrl('orders/' . $order['order_code']) ?>" class="btn btn-primary" id="btn-view-order-detail">
                🔍 Theo dõi tiến trình đơn hàng
            </a>
            <a href="<?= Response::baseUrl('menu') ?>" class="btn btn-outline" id="btn-continue-shopping">
                🍽️ Tiếp tục đặt món
            </a>
        </div>
    </div>
</div>
