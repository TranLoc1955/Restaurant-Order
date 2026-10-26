<div class="container" style="padding: 2.5rem 1.5rem 4rem;">
    <!-- Breadcrumb -->
    <nav style="font-size:0.875rem; color:var(--text-muted); margin-bottom:1.5rem;">
        <a href="<?= Response::baseUrl('orders') ?>">&larr; Quay lại danh sách đơn hàng</a>
    </nav>

    <?php 
    $statusNames = [
        'PENDING'   => 'Chờ xác nhận',
        'CONFIRMED' => 'Đã xác nhận',
        'PREPARING' => 'Đang chuẩn bị',
        'READY'     => 'Đã nấu xong, chờ giao',
        'DELIVERED' => 'Đã giao thành công',
        'REJECTED'  => 'Bị từ chối',
        'CANCELLED' => 'Đã hủy',
    ];
    ?>

    <!-- Tiêu đề & Trạng thái hiện tại -->
    <div class="card mb-4" style="padding: 1.75rem 2rem;">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
            <div>
                <span class="text-muted" style="font-size:0.875rem;">Mã đơn hàng:</span>
                <h1 style="font-size: 1.75rem; margin: 0.25rem 0 0; color:var(--primary-color);">
                    <?= htmlspecialchars($order['order_code']) ?>
                </h1>
                <div style="font-size: 0.8125rem; color: var(--text-muted); margin-top: 0.25rem;">
                    Đặt lúc: <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:1rem;">
                <span class="badge badge-<?= strtolower($order['order_status']) ?>" style="font-size: 0.95rem; padding: 0.5rem 1rem;">
                    <?= $statusNames[$order['order_status']] ?? $order['order_status'] ?>
                </span>

                <!-- Khách hàng chỉ có quyền hủy khi đơn còn ở PENDING -->
                <?php if ($order['order_status'] === 'PENDING'): ?>
                    <button type="button" class="btn btn-outline btn-danger btn-sm" onclick="document.getElementById('cancel-modal').style.display='flex'">
                        Hủy đơn hàng
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cảnh báo nếu đơn bị từ chối -->
        <?php if ($order['order_status'] === 'REJECTED' && !empty($order['rejection_reason'])): ?>
            <div style="margin-top: 1.5rem; padding: 1rem 1.25rem; background-color: #fef2f2; border-left: 4px solid var(--danger-color); border-radius: var(--radius-sm); color: #991b1b;">
                <strong>Lý do nhà hàng từ chối đơn:</strong> <?= htmlspecialchars($order['rejection_reason']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div style="display:grid; grid-template-columns: 1.6fr 1fr; gap: 2rem; align-items:flex-start;">
        <!-- Cột danh sách món ăn & Lịch sử audit -->
        <div>
            <!-- Chi tiết món ăn -->
            <div class="cart-table-wrapper mb-4">
                <div style="padding: 1rem 1.25rem; background:#ffffff; border-bottom:1px solid var(--border-color); font-weight:700;">
                    Danh Sách Món Ăn Trong Đơn
                </div>
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Tên món</th>
                            <th>Đơn giá</th>
                            <th style="text-align:center;">SL</th>
                            <th style="text-align:right;">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-center gap-1">
                                        <div style="font-size:1.5rem;">🍲</div>
                                        <div><strong><?= htmlspecialchars($it['product_name_snapshot']) ?></strong></div>
                                    </div>
                                </td>
                                <td><?= number_format((float)$it['unit_price'], 0, ',', '.') ?>đ</td>
                                <td style="text-align:center;">x<?= $it['quantity'] ?></td>
                                <td style="text-align:right; font-weight:700; color:var(--primary-color);">
                                    <?= number_format((float)$it['line_total'], 0, ',', '.') ?>đ
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Nhật ký thay đổi trạng thái (Audit Log) -->
            <div class="card" style="padding: 1.5rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    Tiến Trình Xử Lý Đơn Hàng (Audit Log)
                </h4>
                <div style="display:flex; flex-direction:column; gap: 1rem;">
                    <?php foreach ($history as $h): ?>
                        <div style="display:flex; gap: 1rem; font-size: 0.875rem;">
                            <div style="width: 140px; color: var(--text-muted); flex-shrink: 0;">
                                <?= date('d/m/Y H:i:s', strtotime($h['created_at'])) ?>
                            </div>
                            <div style="flex:1;">
                                <span class="badge badge-<?= strtolower($h['new_status']) ?>">
                                    <?= $statusNames[$h['new_status']] ?? $h['new_status'] ?>
                                </span>
                                <?php if (!empty($h['note'])): ?>
                                    <div style="margin-top: 0.25rem; color: var(--text-color);">
                                        <?= htmlspecialchars($h['note']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Cột tóm tắt giao hàng và tiền -->
        <div style="display:flex; flex-direction:column; gap:1.5rem;">
            <!-- Địa chỉ nhận hàng -->
            <div class="card" style="padding: 1.5rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    Thông Tin Người Nhận
                </h4>
                <div style="display:flex; flex-direction:column; gap:0.5rem; font-size:0.875rem;">
                    <div><strong>Họ và tên:</strong> <?= htmlspecialchars($order['recipient_name']) ?></div>
                    <div><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['recipient_phone']) ?></div>
                    <div><strong>Địa chỉ giao:</strong> <?= htmlspecialchars($order['delivery_address']) ?></div>
                    <?php if (!empty($order['note'])): ?>
                        <div><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></div>
                    <?php endif; ?>
                    <div style="margin-top:0.5rem; padding-top:0.5rem; border-top:1px dashed var(--border-color);">
                        <strong>Phương thức:</strong> 
                        <?= (($order['payment_method'] ?? 'COD') === 'BANK_TRANSFER') ? '💳 Chuyển khoản VietQR' : '💵 Tiền mặt (COD)' ?>
                    </div>
                </div>
            </div>

            <!-- Chi phí đơn hàng -->
            <div class="card" style="padding: 1.5rem;">
                <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                    Chi Tiết Thanh Toán
                </h4>
                <div class="d-flex justify-between mb-2" style="font-size:0.875rem;">
                    <span class="text-muted">Tạm tính:</span>
                    <span><?= number_format((float)$order['subtotal'], 0, ',', '.') ?>đ</span>
                </div>
                <?php if (!empty($order['voucher_code']) && (float)$order['discount_amount'] > 0): ?>
                    <div class="d-flex justify-between mb-2" style="font-size:0.875rem; color:#dc2626; font-weight:600;">
                        <span>🎟️ Mã ưu đãi (<?= htmlspecialchars($order['voucher_code']) ?>):</span>
                        <span>-<?= number_format((float)$order['discount_amount'], 0, ',', '.') ?>đ</span>
                    </div>
                <?php endif; ?>
                <?php if ((int)($order['points_used'] ?? 0) > 0): ?>
                    <div class="d-flex justify-between mb-2" style="font-size:0.875rem; color:#16a34a; font-weight:600;">
                        <span>🎁 Điểm tích lũy (<?= number_format((int)$order['points_used']) ?> điểm):</span>
                        <span>-<?= number_format((float)$order['points_discount'], 0, ',', '.') ?>đ</span>
                    </div>
                <?php endif; ?>
                <div class="d-flex justify-between mb-3" style="font-size:0.875rem;">
                    <span class="text-muted">Phí giao hàng:</span>
                    <span><?= number_format((float)$order['shipping_fee'], 0, ',', '.') ?>đ</span>
                </div>
                <div class="d-flex justify-between align-center" style="border-top:1px dashed var(--border-color); padding-top:0.75rem;">
                    <span style="font-weight:700;">Tổng thanh toán:</span>
                    <span style="font-size:1.35rem; font-weight:800; color:var(--primary-color);">
                        <?= number_format((float)$order['total_amount'], 0, ',', '.') ?>đ
                    </span>
                </div>
                <?php if ((int)($order['points_earned'] ?? 0) > 0): ?>
                    <div style="margin-top:0.75rem; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:6px; padding:0.4rem 0.6rem; font-size:0.8rem; color:#065f46; display:flex; align-items:center; gap:0.4rem;">
                        <span>🎁</span>
                        <span>Tích lũy: <strong>+<?= number_format((int)$order['points_earned']) ?> điểm</strong> <?= ($order['order_status'] === 'DELIVERED') ? '(Đã cộng vào ví)' : '(Nhận sau khi giao thành công)' ?></span>
                    </div>
                <?php endif; ?>
                <div style="margin-top:0.75rem; font-size:0.8125rem; display:flex; justify-content:space-between; align-items:center;">
                    <span class="text-muted">Hình thức:</span>
                    <?php if (($order['payment_method'] ?? 'COD') === 'BANK_TRANSFER'): ?>
                        <strong style="color:#2563eb;">💳 Chuyển khoản VietQR</strong>
                    <?php else: ?>
                        <strong>💵 Tiền mặt (COD)</strong>
                    <?php endif; ?>
                </div>
                <div style="margin-top:0.5rem; font-size:0.8125rem; display:flex; justify-content:space-between; align-items:center;">
                    <span class="text-muted">Trạng thái tiền:</span>
                    <?php if (($order['payment_status'] ?? 'unpaid') === 'paid'): ?>
                        <span class="badge badge-delivered" style="font-size:0.75rem;">✓ Đã thanh toán</span>
                    <?php else: ?>
                        <span class="badge badge-pending" style="font-size:0.75rem;">⏳ Chưa thanh toán</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Khung quét mã chuyển khoản nếu chọn BANK_TRANSFER -->
            <?php if (($order['payment_method'] ?? 'COD') === 'BANK_TRANSFER'): ?>
                <div class="card" style="padding: 1.5rem; border: 2px solid #3b82f6; background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%);">
                    <h4 style="color:#1e3a8a; margin-bottom: 0.75rem; display:flex; align-items:center; gap:0.5rem;">
                        <span>💳</span> Thông Tin Chuyển Khoản
                    </h4>

                    <?php if (($order['payment_status'] ?? 'unpaid') === 'paid'): ?>
                        <div style="padding: 1rem; background: #dcfce7; border-radius: var(--radius-sm); color: #166534; font-size: 0.875rem; text-align: center; font-weight: 600;">
                            ✓ Đơn hàng đã được xác nhận thanh toán thành công!
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; margin-bottom: 1rem;">
                            <?php 
                            $qrAmount = (int)$order['total_amount'];
                            $qrAddInfo = urlencode($order['order_code']);
                            $qrBankId = urlencode($banking['bank_id'] ?? 'VCB');
                            $qrAccountNo = urlencode($banking['account_no'] ?? '');
                            $qrAccountName = urlencode($banking['account_name'] ?? '');
                            $qrUrl = "https://img.vietqr.io/image/{$qrBankId}-{$qrAccountNo}-compact2.png?amount={$qrAmount}&addInfo={$qrAddInfo}&accountName={$qrAccountName}";
                            ?>
                            <img 
                                src="<?= $qrUrl ?>" 
                                alt="Mã VietQR" 
                                style="width: 170px; height: auto; border-radius: 8px; border: 1px solid #cbd5e1; background:#fff; padding:6px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);"
                            >
                            <div style="font-size: 0.7rem; color: #64748b; margin-top: 4px;">
                                Quét mã bằng App Ngân hàng bất kỳ
                            </div>
                        </div>

                        <div style="font-size: 0.8125rem; display:flex; flex-direction:column; gap:0.5rem;">
                            <div>
                                <span class="text-muted" style="display:block; font-size:0.7rem;">NGÂN HÀNG</span>
                                <strong><?= htmlspecialchars($banking['bank_name'] ?? 'VietComBank') ?></strong>
                            </div>
                            <div>
                                <span class="text-muted" style="display:block; font-size:0.7rem;">SỐ TÀI KHOẢN</span>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <strong style="color:var(--primary-color); font-family:monospace; font-size:1rem;"><?= htmlspecialchars($banking['account_no'] ?? '') ?></strong>
                                    <button type="button" class="btn btn-sm btn-outline" onclick="copyDetailText('<?= htmlspecialchars($banking['account_no'] ?? '') ?>', this)" style="padding:1px 6px; font-size:0.7rem;">Chép</button>
                                </div>
                            </div>
                            <div>
                                <span class="text-muted" style="display:block; font-size:0.7rem;">CHỦ TÀI KHOẢN</span>
                                <strong><?= htmlspecialchars($banking['account_name'] ?? '') ?></strong>
                            </div>
                            <div>
                                <span class="text-muted" style="display:block; font-size:0.7rem;">NỘI DUNG CHUYỂN TIỀN</span>
                                <div style="display:flex; justify-content:space-between; align-items:center;">
                                    <strong style="color:#1e40af; font-family:monospace; background:#dbeafe; padding:2px 6px; border-radius:4px; font-size:0.85rem;"><?= htmlspecialchars($order['order_code']) ?></strong>
                                    <button type="button" class="btn btn-sm btn-outline" onclick="copyDetailText('<?= htmlspecialchars($order['order_code']) ?>', this)" style="padding:1px 6px; font-size:0.7rem;">Chép</button>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal xác nhận hủy đơn -->
<?php if ($order['order_status'] === 'PENDING'): ?>
    <div id="cancel-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
        <div class="card" style="max-width:440px; width:100%; padding:2rem;">
            <h3 style="margin-bottom: 0.75rem; color:var(--danger-color);">Xác Nhận Hủy Đơn Hàng?</h3>
            <p class="text-muted" style="font-size:0.875rem; margin-bottom:1.25rem;">
                Bạn có chắc chắn muốn hủy đơn hàng <strong><?= htmlspecialchars($order['order_code']) ?></strong> không? Thao tác này không thể hoàn tác.
            </p>
            <form action="<?= Response::baseUrl('orders/' . $order['order_code'] . '/cancel') ?>" method="POST">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <div class="form-group">
                    <label class="form-label">Lý do hủy đơn (Tùy chọn):</label>
                    <textarea name="cancel_reason" rows="2" class="form-control" placeholder="Ví dụ: Đổi ý, đặt nhầm món..."></textarea>
                </div>
                <div class="d-flex justify-between gap-1 mt-3">
                    <button type="button" class="btn btn-outline" onclick="document.getElementById('cancel-modal').style.display='none'">
                        Đóng
                    </button>
                    <button type="submit" class="btn btn-danger">
                        Xác nhận hủy đơn
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<script>
function copyDetailText(text, btn) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const old = btn.innerText;
        btn.innerText = '✓';
        btn.style.color = '#16a34a';
        btn.style.borderColor = '#16a34a';
        setTimeout(() => {
            btn.innerText = old;
            btn.style.color = '';
            btn.style.borderColor = '';
        }, 2000);
    }).catch(() => {
        const input = document.createElement('input');
        input.value = text;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        document.body.removeChild(input);
        btn.innerText = '✓';
        setTimeout(() => { btn.innerText = 'Chép'; }, 2000);
    });
}
</script>
