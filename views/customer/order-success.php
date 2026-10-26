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

        <?php if (($order['payment_method'] ?? 'COD') === 'BANK_TRANSFER'): ?>
            <!-- KHUNG HƯỚNG DẪN CHUYỂN KHOẢN QUA TÀI KHOẢN (VIETQR) -->
            <div style="text-align: left; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 2px solid #3b82f6; border-radius: var(--radius-lg); padding: 1.75rem; max-width: 620px; margin: 0 auto 2rem; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 1.25rem; border-bottom: 1px solid #bfdbfe; padding-bottom: 0.75rem;">
                    <div>
                        <h3 style="font-size: 1.25rem; color: #1e3a8a; margin: 0; display:flex; align-items:center; gap:0.5rem;">
                            <span>💳</span> Hướng Dẫn Chuyển Khoản Ngân Hàng
                        </h3>
                        <div style="font-size: 0.8125rem; color: #475569; margin-top: 0.25rem;">
                            Quét mã VietQR hoặc chuyển khoản theo thông tin bên dưới:
                        </div>
                    </div>
                    <?php if (($order['payment_status'] ?? 'unpaid') === 'paid'): ?>
                        <span style="font-size:0.75rem; font-weight:700; background:#dcfce7; color:#166534; padding:4px 10px; border-radius:20px; display:flex; align-items:center; gap:4px;">
                            ✓ Đã thanh toán
                        </span>
                    <?php else: ?>
                        <span style="font-size:0.75rem; font-weight:700; background:#fef3c7; color:#92400e; padding:4px 10px; border-radius:20px; display:flex; align-items:center; gap:4px;">
                            ⏳ Chờ chuyển khoản
                        </span>
                    <?php endif; ?>
                </div>

                <div style="display: grid; grid-template-columns: 200px 1fr; gap: 1.5rem; align-items: center;">
                    <!-- Mã VietQR -->
                    <div style="text-align: center; background: #ffffff; padding: 0.75rem; border-radius: var(--radius-md); border: 1px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
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
                            alt="Mã QR Chuyển khoản VietQR" 
                            style="width: 100%; height: auto; border-radius: 6px; display: block;"
                            loading="lazy"
                        >
                        <div style="font-size: 0.6875rem; color: #64748b; margin-top: 0.5rem; font-weight: 600;">
                            Quét bằng app ngân hàng / MoMo
                        </div>
                    </div>

                    <!-- Bảng thông tin chuyển khoản -->
                    <div style="font-size: 0.875rem; display: flex; flex-direction: column; gap: 0.625rem;">
                        <div>
                            <span style="color:#64748b; font-size:0.75rem; display:block;">NGÂN HÀNG</span>
                            <strong style="color:#0f172a; font-size:0.95rem;"><?= htmlspecialchars($banking['bank_name'] ?? 'VietComBank') ?></strong>
                        </div>

                        <div>
                            <span style="color:#64748b; font-size:0.75rem; display:block;">SỐ TÀI KHOẢN</span>
                            <div style="display:flex; align-items:center; gap:0.5rem; margin-top:1px;">
                                <strong style="color:var(--primary-color); font-size:1.15rem; font-family:monospace; letter-spacing:0.05em;" id="copy-acc-num">
                                    <?= htmlspecialchars($banking['account_no'] ?? '') ?>
                                </strong>
                                <button type="button" class="btn btn-sm btn-outline" onclick="copyText('<?= htmlspecialchars($banking['account_no'] ?? '') ?>', this)" style="padding:2px 8px; font-size:0.75rem;">
                                    Sao chép
                                </button>
                            </div>
                        </div>

                        <div>
                            <span style="color:#64748b; font-size:0.75rem; display:block;">CHỦ TÀI KHOẢN</span>
                            <strong style="color:#0f172a;"><?= htmlspecialchars($banking['account_name'] ?? '') ?></strong>
                        </div>

                        <div>
                            <span style="color:#64748b; font-size:0.75rem; display:block;">SỐ TIỀN CHUYỂN</span>
                            <div style="display:flex; align-items:center; gap:0.5rem; margin-top:1px;">
                                <strong style="color:#dc2626; font-size:1.15rem; font-weight:800;">
                                    <?= number_format((float)$order['total_amount'], 0, ',', '.') ?>đ
                                </strong>
                                <button type="button" class="btn btn-sm btn-outline" onclick="copyText('<?= (int)$order['total_amount'] ?>', this)" style="padding:2px 8px; font-size:0.75rem;">
                                    Sao chép
                                </button>
                            </div>
                        </div>

                        <div>
                            <span style="color:#64748b; font-size:0.75rem; display:block;">NỘI DUNG CHUYỂN KHOẢN (BẮT BUỘC)</span>
                            <div style="display:flex; align-items:center; gap:0.5rem; margin-top:1px;">
                                <strong style="color:#1e40af; font-size:1.05rem; font-family:monospace; background:#dbeafe; padding:2px 8px; border-radius:4px;" id="copy-order-code">
                                    <?= htmlspecialchars($order['order_code']) ?>
                                </strong>
                                <button type="button" class="btn btn-sm btn-outline" onclick="copyText('<?= htmlspecialchars($order['order_code']) ?>', this)" style="padding:2px 8px; font-size:0.75rem;">
                                    Sao chép
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 1.25rem; font-size: 0.8125rem; color: #1e40af; background: #dbeafe; padding: 10px 14px; border-radius: var(--radius-sm); line-height: 1.5;">
                    💡 <strong>Lưu ý:</strong> Quý khách vui lòng điền chính xác mã đơn <strong><?= htmlspecialchars($order['order_code']) ?></strong> vào nội dung chuyển tiền. Nhân viên nhà hàng sẽ kiểm tra tài khoản và xác nhận đơn ngay khi nhận được thanh toán.
                </div>
            </div>
        <?php endif; ?>

        <!-- Thông tin giao hàng tóm tắt -->
        <div style="text-align: left; background-color: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; max-width: 620px; margin: 0 auto 2rem;">
            <h4 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">
                Thông Tin Giao Hàng & Đơn Hàng
            </h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.875rem;">
                <div><strong>Người nhận:</strong> <?= htmlspecialchars($order['recipient_name']) ?></div>
                <div><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['recipient_phone']) ?></div>
                <div style="grid-column: 1 / -1;"><strong>Địa chỉ giao:</strong> <?= htmlspecialchars($order['delivery_address']) ?></div>
                <?php if (!empty($order['note'])): ?>
                    <div style="grid-column: 1 / -1;"><strong>Ghi chú:</strong> <?= htmlspecialchars($order['note']) ?></div>
                <?php endif; ?>
                <div>
                    <strong>Phương thức:</strong> 
                    <?php if (($order['payment_method'] ?? 'COD') === 'BANK_TRANSFER'): ?>
                        <span style="color:#2563eb; font-weight:700;">💳 Chuyển khoản ngân hàng (VietQR)</span>
                    <?php else: ?>
                        <span>💵 Tiền mặt khi nhận hàng (COD)</span>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Trạng thái thanh toán:</strong> 
                    <?php if (($order['payment_status'] ?? 'unpaid') === 'paid'): ?>
                        <span class="badge badge-delivered" style="font-size:0.75rem;">Đã thanh toán</span>
                    <?php else: ?>
                        <span class="badge badge-pending" style="font-size:0.75rem;">Chưa thanh toán</span>
                    <?php endif; ?>
                </div>
                <div style="grid-column: 1 / -1; margin-top:0.5rem; padding-top:0.75rem; border-top:1px dashed var(--border-color);">
                    <div style="display:flex; justify-content:space-between; margin-bottom:4px; color:#64748b;">
                        <span>Tạm tính tiền món:</span>
                        <span><?= number_format((float)$order['subtotal'], 0, ',', '.') ?>đ</span>
                    </div>
                    <?php if (!empty($order['voucher_code']) && (float)$order['discount_amount'] > 0): ?>
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px; color:#dc2626; font-weight:600;">
                            <span>🎟️ Mã ưu đãi (<?= htmlspecialchars($order['voucher_code']) ?>):</span>
                            <span>-<?= number_format((float)$order['discount_amount'], 0, ',', '.') ?>đ</span>
                        </div>
                    <?php endif; ?>
                    <?php if ((int)($order['points_used'] ?? 0) > 0): ?>
                        <div style="display:flex; justify-content:space-between; margin-bottom:4px; color:#16a34a; font-weight:600;">
                            <span>🎁 Điểm tích lũy (<?= number_format((int)$order['points_used']) ?> điểm):</span>
                            <span>-<?= number_format((float)$order['points_discount'], 0, ',', '.') ?>đ</span>
                        </div>
                    <?php endif; ?>
                    <div style="display:flex; justify-content:space-between; margin-bottom:6px; color:#64748b;">
                        <span>Phí giao hàng:</span>
                        <span><?= number_format((float)$order['shipping_fee'], 0, ',', '.') ?>đ</span>
                    </div>
                    <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid #e2e8f0; padding-top:6px;">
                        <strong style="font-size:1.05rem;">Tổng thanh toán:</strong> 
                        <span class="font-bold text-primary" style="font-size:1.35rem;"><?= number_format((float)$order['total_amount'], 0, ',', '.') ?>đ</span>
                    </div>
                    <?php if ((int)($order['points_earned'] ?? 0) > 0): ?>
                        <div style="margin-top:0.5rem; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:6px; padding:0.4rem 0.6rem; font-size:0.8rem; color:#065f46; display:flex; align-items:center; gap:0.4rem;">
                            <span>🎁</span>
                            <span>Bạn sẽ nhận được <strong>+<?= number_format((int)$order['points_earned']) ?> điểm</strong> sau khi đơn hàng giao thành công!</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Danh sách món đã đặt -->
        <div style="text-align: left; max-width: 620px; margin: 0 auto 2rem;">
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

<script>
function copyText(text, btn) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        const originalText = btn.innerText;
        btn.innerText = '✓ Đã chép';
        btn.style.color = '#16a34a';
        btn.style.borderColor = '#16a34a';
        setTimeout(() => {
            btn.innerText = originalText;
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
        btn.innerText = '✓ Đã chép';
        setTimeout(() => { btn.innerText = 'Sao chép'; }, 2000);
    });
}
</script>
