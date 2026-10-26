<div class="container" style="padding: 2.5rem 1.5rem 4rem;">
    <!-- Breadcrumb -->
    <nav style="font-size:0.875rem; color:var(--text-muted); margin-bottom:1.5rem;">
        <a href="<?= Response::baseUrl('cart') ?>">&larr; Quay lại giỏ hàng</a>
    </nav>

    <h1 style="margin-bottom: 0.5rem;">Xác Nhận Đặt Hàng</h1>
    <p class="text-muted" style="margin-bottom: 2rem;">Vui lòng nhập thông tin người nhận và áp dụng mã ưu đãi để thưởng thức trọn vẹn ẩm thực</p>

    <form action="<?= Response::baseUrl('checkout') ?>" method="POST" id="checkout-form" data-loading novalidate>
        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
        <!-- Hidden inputs gửi voucher và điểm đã áp dụng -->
        <input type="hidden" name="voucher_code" id="input-voucher-code" value="">
        <input type="hidden" name="points_used" id="input-points-used" value="0">

        <div style="display:grid; grid-template-columns: 1.35fr 1.05fr; gap: 2.5rem; align-items:flex-start;">
            <!-- Cột thông tin nhận hàng & Phương thức thanh toán -->
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
                        rows="2" 
                        class="form-control" 
                        placeholder="Ví dụ: Làm ít cay, xin thêm nước chấm, gọi trước khi đến 10 phút..."
                    ></textarea>
                </div>

                <h3 style="margin: 2rem 0 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    2. Phương Thức Thanh Toán
                </h3>

                <div style="display:flex; flex-direction:column; gap:0.875rem;">
                    <!-- Lựa chọn 1: Tiền mặt COD -->
                    <label class="payment-option-card" id="card-pay-cod" style="padding: 1.125rem 1.25rem; background-color: var(--surface-hover); border: 2px solid var(--primary-color); border-radius: var(--radius-md); display:flex; align-items:flex-start; gap:0.875rem; cursor:pointer; transition: all 0.2s ease;">
                        <input type="radio" name="payment_method" value="COD" checked id="pay-cod" style="margin-top:0.25rem;" onchange="togglePaymentMethod('COD')">
                        <div style="flex:1;">
                            <div style="font-weight: 700; font-size:1rem; color:var(--text-color); display:flex; align-items:center; gap:0.5rem;">
                                <span>💵</span> Thanh toán tiền mặt khi nhận hàng (COD)
                            </div>
                            <div style="font-size:0.8125rem; color:var(--text-muted); margin-top:0.25rem; line-height:1.4;">
                                Bạn thanh toán trực tiếp cho shipper khi nhận được món ăn nóng hổi tại địa chỉ giao hàng.
                            </div>
                        </div>
                    </label>

                    <!-- Lựa chọn 2: Chuyển khoản ngân hàng (VietQR) -->
                    <label class="payment-option-card" id="card-pay-bank" style="padding: 1.125rem 1.25rem; background-color: #ffffff; border: 2px solid var(--border-color); border-radius: var(--radius-md); display:flex; align-items:flex-start; gap:0.875rem; cursor:pointer; transition: all 0.2s ease;">
                        <input type="radio" name="payment_method" value="BANK_TRANSFER" id="pay-bank" style="margin-top:0.25rem;" onchange="togglePaymentMethod('BANK_TRANSFER')">
                        <div style="flex:1;">
                            <div style="font-weight: 700; font-size:1rem; color:var(--text-color); display:flex; align-items:center; gap:0.5rem;">
                                <span>💳</span> Chuyển khoản qua tài khoản ngân hàng (VietQR)
                                <span style="font-size:0.7rem; font-weight:700; background:#e0e7ff; color:#3730a3; padding:2px 8px; border-radius:12px; margin-left:auto;">Khuyên dùng</span>
                            </div>
                            <div style="font-size:0.8125rem; color:var(--text-muted); margin-top:0.25rem; line-height:1.4;">
                                Quét mã VietQR tiện lợi qua mọi ứng dụng ngân hàng (MB, Vietcombank, Techcombank, MoMo...). Mã QR tự động điền sẵn số tiền và mã đơn.
                            </div>

                            <!-- Chi tiết thông tin ngân hàng hiển thị khi chọn -->
                            <div id="bank-info-preview" style="display:none; margin-top:1rem; padding:1rem; background:#f8fafc; border:1px solid #cbd5e1; border-radius:var(--radius-sm); font-size:0.8125rem;">
                                <div style="display:grid; grid-template-columns:auto 1fr; gap:0.4rem 0.8rem; align-items:center;">
                                    <span style="color:#64748b;">🏦 Ngân hàng:</span>
                                    <strong style="color:#0f172a;"><?= htmlspecialchars($banking['bank_name'] ?? 'VietComBank') ?></strong>
                                    <span style="color:#64748b;">💳 Số tài khoản:</span>
                                    <strong style="color:var(--primary-color); font-size:0.95rem; font-family:monospace;"><?= htmlspecialchars($banking['account_no'] ?? '') ?></strong>
                                    <span style="color:#64748b;">👤 Chủ tài khoản:</span>
                                    <strong style="color:#0f172a;"><?= htmlspecialchars($banking['account_name'] ?? '') ?></strong>
                                </div>
                                <div style="margin-top:0.75rem; font-size:0.75rem; color:#0369a1; background:#e0f2fe; padding:6px 10px; border-radius:6px; display:flex; align-items:center; gap:0.4rem;">
                                    <span>⚡</span> Sau khi bấm đặt hàng, màn hình sẽ hiển thị mã QR kèm nội dung chuyển khoản tự động.
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Cột tóm tắt đơn hàng & Ưu đãi Voucher / Tích điểm -->
            <div style="display:flex; flex-direction:column; gap:1.5rem;">
                
                <!-- Card Món ăn & Ưu đãi -->
                <div class="card" style="padding: 1.75rem;">
                    <h3 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                        Món Đã Chọn (<?= count($items) ?> món)
                    </h3>

                    <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1.5rem; max-height:200px; overflow-y:auto; padding-right:4px;">
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

                    <!-- 3. KHỐI MÃ GIẢM GIÁ (VOUCHER) -->
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:var(--radius-md); padding:1rem; margin-bottom:1.25rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.75rem;">
                            <span style="font-weight:700; font-size:0.9rem; color:#1e293b; display:flex; align-items:center; gap:0.35rem;">
                                <span>🎟️</span> Mã Giảm Giá / Voucher
                            </span>
                        </div>

                        <!-- Ô nhập mã voucher -->
                        <div style="display:flex; gap:0.5rem; margin-bottom:0.75rem;">
                            <input 
                                type="text" 
                                id="voucher-code-input" 
                                class="form-control" 
                                placeholder="Nhập mã ưu đãi (VD: TRIANKHACHCU)"
                                style="text-transform:uppercase; font-weight:700; font-size:0.85rem;"
                            >
                            <button 
                                type="button" 
                                id="btn-apply-voucher" 
                                class="btn btn-outline" 
                                style="white-space:nowrap; padding:0.5rem 1rem; font-weight:700;"
                                onclick="applyVoucherCode()"
                            >
                                Áp dụng
                            </button>
                        </div>

                        <!-- Trạng thái kết quả áp dụng voucher -->
                        <div id="voucher-feedback-box" style="display:none; font-size:0.8125rem; padding:0.6rem 0.75rem; border-radius:6px; margin-bottom:0.75rem;"></div>

                        <!-- Gợi ý danh sách Voucher có sẵn -->
                        <?php if (!empty($availableVouchers)): ?>
                            <div style="font-size:0.75rem; color:#64748b; margin-bottom:0.4rem; font-weight:600;">
                                MÃ ƯU ĐÃI DÀNH CHO BẠN:
                            </div>
                            <div style="display:flex; flex-direction:column; gap:0.5rem; max-height:220px; overflow-y:auto;">
                                <?php foreach ($availableVouchers as $voc): ?>
                                    <?php 
                                        $isReturningSpecial = ($voc['customer_condition'] === 'RETURNING_ONLY');
                                        $isEligible = $voc['is_eligible'];
                                        $isSubtotalMet = $voc['is_subtotal_met'];
                                    ?>
                                    <div 
                                        class="voucher-item-badge" 
                                        onclick="selectVoucher('<?= htmlspecialchars($voc['code']) ?>', <?= $isEligible ? 'true' : 'false' ?>, '<?= htmlspecialchars($voc['ineligible_reason'] ?? '') ?>')"
                                        style="
                                            border: 1px dashed <?= $isReturningSpecial ? '#f59e0b' : '#cbd5e1' ?>; 
                                            background: <?= $isReturningSpecial ? '#fffbeb' : '#ffffff' ?>;
                                            border-radius: 8px; 
                                            padding: 0.65rem 0.85rem; 
                                            cursor: <?= $isEligible ? 'pointer' : 'not-allowed' ?>;
                                            opacity: <?= $isEligible ? '1' : '0.65' ?>;
                                            transition: all 0.2s ease;
                                        "
                                        title="<?= !$isEligible ? htmlspecialchars($voc['ineligible_reason']) : 'Bấm để dùng mã' ?>"
                                    >
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2px;">
                                            <strong style="color:var(--primary-color); font-size:0.85rem; font-family:monospace; background:#fee2e2; padding:1px 6px; border-radius:4px;">
                                                <?= htmlspecialchars($voc['code']) ?>
                                            </strong>
                                            <?php if ($isReturningSpecial): ?>
                                                <span style="font-size:0.7rem; font-weight:700; color:#b45309; background:#fef3c7; padding:1px 6px; border-radius:10px;">
                                                    ⭐️ Tri Ân Khách Cũ
                                                </span>
                                            <?php elseif ($voc['customer_condition'] === 'NEW_ONLY'): ?>
                                                <span style="font-size:0.7rem; font-weight:700; color:#0369a1; background:#e0f2fe; padding:1px 6px; border-radius:10px;">
                                                    ✨ Khách Mới
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-weight:600; font-size:0.8rem; color:#1e293b;"><?= htmlspecialchars($voc['title']) ?></div>
                                        <div style="font-size:0.75rem; color:#64748b; line-height:1.3; margin-top:2px;">
                                            <?= htmlspecialchars($voc['description']) ?>
                                        </div>
                                        <?php if (!$isEligible): ?>
                                            <div style="font-size:0.72rem; color:#dc2626; margin-top:3px; font-weight:600;">
                                                🔒 <?= htmlspecialchars($voc['ineligible_reason']) ?>
                                            </div>
                                        <?php elseif (!$isSubtotalMet): ?>
                                            <div style="font-size:0.72rem; color:#d97706; margin-top:3px;">
                                                ⚠️ Cần thêm <?= number_format($voc['subtotal_deficit'], 0, ',', '.') ?>đ để áp dụng
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 4. KHỐI TÍCH ĐIỂM & ĐỔI THƯỞNG -->
                    <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:var(--radius-md); padding:1rem; margin-bottom:1.25rem;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
                            <span style="font-weight:700; font-size:0.9rem; color:#166534; display:flex; align-items:center; gap:0.35rem;">
                                <span>🎁</span> Điểm Tích Lũy Thưởng
                            </span>
                            <span style="font-size:0.8125rem; font-weight:800; color:#15803d; background:#dcfce7; padding:2px 8px; border-radius:12px;">
                                Có: <?= number_format($userPoints, 0, ',', '.') ?> điểm
                            </span>
                        </div>

                        <?php if ($userPoints > 0): ?>
                            <div style="font-size:0.8125rem; color:#374151; margin-bottom:0.75rem;">
                                Quy đổi: <strong>1 điểm = 1.000đ</strong> giảm giá trực tiếp. (Tương đương tối đa <?= number_format($userPoints * 1000, 0, ',', '.') ?>đ).
                            </div>

                            <label style="display:flex; align-items:center; gap:0.5rem; cursor:pointer; font-size:0.875rem; font-weight:600; color:#166534; margin-bottom:0.6rem;">
                                <input type="checkbox" id="checkbox-use-points" onchange="toggleUsePoints(this.checked)" style="width:16px; height:16px; accent-color:#16a34a;">
                                Dùng điểm tích lũy cho đơn hàng này
                            </label>

                            <div id="points-input-group" style="display:none; align-items:center; gap:0.5rem; margin-top:0.5rem;">
                                <input 
                                    type="number" 
                                    id="points-input" 
                                    class="form-control" 
                                    min="0" 
                                    max="<?= $userPoints ?>" 
                                    value="0" 
                                    step="1"
                                    style="width:120px; font-weight:700;"
                                    oninput="onPointsChange(this.value)"
                                >
                                <button type="button" class="btn btn-sm btn-outline" onclick="useMaxPoints()" style="padding:4px 10px; font-size:0.75rem;">
                                    Dùng tối đa
                                </button>
                                <button type="button" class="btn btn-sm btn-outline" onclick="resetPoints()" style="padding:4px 10px; font-size:0.75rem; color:#64748b; border-color:#cbd5e1;">
                                    Hủy
                                </button>
                            </div>
                        <?php else: ?>
                            <div style="font-size:0.8125rem; color:#4b5563; line-height:1.4;">
                                Bạn hiện có 0 điểm tích lũy. Đừng lo! Đơn hàng này sẽ tự động tích điểm cho bạn (<strong>10.000đ = 1 điểm</strong>) sau khi giao thành công.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- 5. CHI TIẾT TÍNH TIỀN (LIVE BREAKDOWN) -->
                    <div style="border-top:1px solid var(--border-color); padding-top:1rem; margin-bottom:1.5rem;">
                        <div class="d-flex justify-between mb-2" style="font-size:0.875rem;">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="font-bold"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                        </div>

                        <!-- Dòng giảm giá Voucher (nếu có) -->
                        <div class="d-flex justify-between mb-2" id="row-voucher-discount" style="display:none; font-size:0.875rem; color:#dc2626;">
                            <span style="display:flex; align-items:center; gap:0.4rem;">
                                <span>🎟️ Voucher (<strong id="lbl-voucher-code"></strong>):</span>
                                <button type="button" onclick="removeVoucher()" style="background:none; border:none; color:#dc2626; cursor:pointer; font-size:0.75rem; text-decoration:underline;">[Gỡ]</button>
                            </span>
                            <span class="font-bold" id="lbl-voucher-discount">-0đ</span>
                        </div>

                        <!-- Dòng giảm giá Tích điểm (nếu có) -->
                        <div class="d-flex justify-between mb-2" id="row-points-discount" style="display:none; font-size:0.875rem; color:#16a34a;">
                            <span>🎁 Dùng điểm (<strong id="lbl-points-count">0</strong> điểm):</span>
                            <span class="font-bold" id="lbl-points-discount">-0đ</span>
                        </div>

                        <div class="d-flex justify-between mb-3" style="font-size:0.875rem;">
                            <span class="text-muted">Phí giao hàng:</span>
                            <span class="font-bold"><?= number_format($shippingFee, 0, ',', '.') ?>đ</span>
                        </div>

                        <div class="d-flex justify-between align-center" style="border-top:1px dashed var(--border-color); padding-top:0.75rem;">
                            <span style="font-size:1.05rem; font-weight:700;">Tổng thanh toán:</span>
                            <span style="font-size:1.5rem; font-weight:800; color:var(--primary-color);" id="lbl-total-amount">
                                <?= number_format($total, 0, ',', '.') ?>đ
                            </span>
                        </div>

                        <!-- Thông báo tích điểm dự kiến -->
                        <div style="margin-top:0.75rem; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:6px; padding:0.5rem 0.75rem; font-size:0.8rem; color:#065f46; display:flex; align-items:center; gap:0.4rem;">
                            <span>🎁</span>
                            <span>Đơn hàng này sẽ tích lũy thêm: <strong id="lbl-earned-points"><?= (int)floor($total / 10000.0) ?></strong> điểm sau khi giao thành công!</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-submit-order">
                        ✓ Xác nhận đặt hàng (COD)
                    </button>

                    <p class="text-muted text-center mt-2" style="font-size:0.75rem;" id="checkout-disclaimer">
                        Nhấn "Xác nhận đặt hàng" đồng nghĩa với việc bạn đồng ý thanh toán số tiền trên khi nhận món ăn từ shipper.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
// Biến dữ liệu giỏ hàng ban đầu
const RAW_SUBTOTAL = <?= (float)$subtotal ?>;
const SHIPPING_FEE = <?= (float)$shippingFee ?>;
const USER_MAX_POINTS = <?= (int)$userPoints ?>;

// Trạng thái hiện tại của voucher và điểm
let currentVoucher = null;
let currentVoucherDiscount = 0.0;
let currentPointsUsed = 0;
let currentPointsDiscount = 0.0;

function recalculateBill() {
    // 1. Áp voucher trước
    let foodTotalAfterVoucher = Math.max(0, RAW_SUBTOTAL - currentVoucherDiscount);

    // 2. Điểm tích lũy chỉ giảm tối đa trên foodTotalAfterVoucher
    let maxAllowedPoints = Math.floor(foodTotalAfterVoucher / 1000);
    if (currentPointsUsed > maxAllowedPoints) {
        currentPointsUsed = maxAllowedPoints;
        const ptsInput = document.getElementById('points-input');
        if (ptsInput) ptsInput.value = currentPointsUsed;
    }
    currentPointsDiscount = currentPointsUsed * 1000;

    // 3. Tổng thanh toán
    let finalFoodTotal = Math.max(0, foodTotalAfterVoucher - currentPointsDiscount);
    let finalTotal = finalFoodTotal + SHIPPING_FEE;

    // 4. Điểm tích lũy dự kiến (10k = 1 điểm)
    let pointsEarned = Math.floor(finalTotal / 10000);

    // Cập nhật DOM
    const rowVoucher = document.getElementById('row-voucher-discount');
    const lblVoucherCode = document.getElementById('lbl-voucher-code');
    const lblVoucherDisc = document.getElementById('lbl-voucher-discount');
    const inputVoucherCode = document.getElementById('input-voucher-code');

    if (currentVoucher && currentVoucherDiscount > 0) {
        rowVoucher.style.display = 'flex';
        lblVoucherCode.textContent = currentVoucher.code;
        lblVoucherDisc.textContent = '-' + currentVoucherDiscount.toLocaleString('vi-VN') + 'đ';
        inputVoucherCode.value = currentVoucher.code;
    } else {
        rowVoucher.style.display = 'none';
        inputVoucherCode.value = '';
    }

    const rowPoints = document.getElementById('row-points-discount');
    const lblPtsCount = document.getElementById('lbl-points-count');
    const lblPtsDisc = document.getElementById('lbl-points-discount');
    const inputPointsUsed = document.getElementById('input-points-used');

    if (currentPointsUsed > 0) {
        rowPoints.style.display = 'flex';
        lblPtsCount.textContent = currentPointsUsed.toLocaleString('vi-VN');
        lblPtsDisc.textContent = '-' + currentPointsDiscount.toLocaleString('vi-VN') + 'đ';
        inputPointsUsed.value = currentPointsUsed;
    } else {
        rowPoints.style.display = 'none';
        inputPointsUsed.value = '0';
    }

    document.getElementById('lbl-total-amount').textContent = Math.round(finalTotal).toLocaleString('vi-VN') + 'đ';
    document.getElementById('lbl-earned-points').textContent = pointsEarned.toLocaleString('vi-VN');
}

// Bấm chọn nhanh một voucher trong danh sách gợi ý
function selectVoucher(code, isEligible, ineligibleReason) {
    if (!isEligible) {
        alert(ineligibleReason || 'Bạn không đủ điều kiện áp dụng mã này.');
        return;
    }
    document.getElementById('voucher-code-input').value = code;
    applyVoucherCode();
}

// Gọi API kiểm tra mã voucher
function applyVoucherCode() {
    const input = document.getElementById('voucher-code-input');
    const code = (input.value || '').trim();
    const btn = document.getElementById('btn-apply-voucher');
    const fb = document.getElementById('voucher-feedback-box');

    if (!code) {
        fb.style.display = 'block';
        fb.style.backgroundColor = '#fee2e2';
        fb.style.color = '#991b1b';
        fb.textContent = 'Vui lòng nhập mã giảm giá.';
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Đang kiểm tra...';

    fetch(window.APP_BASE_URL + '/api/voucher/validate', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            code: code,
            subtotal: RAW_SUBTOTAL
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.textContent = 'Áp dụng';
        fb.style.display = 'block';

        if (data.success) {
            currentVoucher = data.voucher;
            currentVoucherDiscount = parseFloat(data.discount);
            fb.style.backgroundColor = '#dcfce7';
            fb.style.color = '#166534';
            fb.textContent = '✓ ' + data.message;
            recalculateBill();
        } else {
            currentVoucher = null;
            currentVoucherDiscount = 0.0;
            fb.style.backgroundColor = '#fee2e2';
            fb.style.color = '#991b1b';
            fb.textContent = '✕ ' + data.message;
            recalculateBill();
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.textContent = 'Áp dụng';
        fb.style.display = 'block';
        fb.style.backgroundColor = '#fee2e2';
        fb.style.color = '#991b1b';
        fb.textContent = 'Lỗi kết nối khi kiểm tra mã voucher.';
    });
}

function removeVoucher() {
    currentVoucher = null;
    currentVoucherDiscount = 0.0;
    document.getElementById('voucher-code-input').value = '';
    const fb = document.getElementById('voucher-feedback-box');
    fb.style.display = 'none';
    recalculateBill();
}

// Xử lý dùng điểm tích lũy
function toggleUsePoints(checked) {
    const group = document.getElementById('points-input-group');
    if (!group) return;

    if (checked) {
        group.style.display = 'flex';
        useMaxPoints();
    } else {
        group.style.display = 'none';
        resetPoints();
    }
}

function useMaxPoints() {
    let foodTotalAfterVoucher = Math.max(0, RAW_SUBTOTAL - currentVoucherDiscount);
    let maxAllowedPoints = Math.floor(foodTotalAfterVoucher / 1000);
    let pts = Math.min(USER_MAX_POINTS, maxAllowedPoints);

    document.getElementById('points-input').value = pts;
    currentPointsUsed = pts;
    recalculateBill();
}

function resetPoints() {
    const ptsInput = document.getElementById('points-input');
    if (ptsInput) ptsInput.value = 0;
    const chk = document.getElementById('checkbox-use-points');
    if (chk) chk.checked = false;
    const group = document.getElementById('points-input-group');
    if (group) group.style.display = 'none';

    currentPointsUsed = 0;
    recalculateBill();
}

function onPointsChange(val) {
    let pts = parseInt(val) || 0;
    if (pts < 0) pts = 0;
    if (pts > USER_MAX_POINTS) pts = USER_MAX_POINTS;

    let foodTotalAfterVoucher = Math.max(0, RAW_SUBTOTAL - currentVoucherDiscount);
    let maxAllowedPoints = Math.floor(foodTotalAfterVoucher / 1000);
    if (pts > maxAllowedPoints) pts = maxAllowedPoints;

    document.getElementById('points-input').value = pts;
    currentPointsUsed = pts;
    recalculateBill();
}

// Chuyển đổi phương thức thanh toán COD <-> Chuyển khoản VietQR
function togglePaymentMethod(method) {
    const cardCod = document.getElementById('card-pay-cod');
    const cardBank = document.getElementById('card-pay-bank');
    const bankPreview = document.getElementById('bank-info-preview');
    const btnSubmit = document.getElementById('btn-submit-order');
    const disclaimer = document.getElementById('checkout-disclaimer');

    if (method === 'BANK_TRANSFER') {
        cardBank.style.borderColor = 'var(--primary-color)';
        cardBank.style.backgroundColor = 'var(--surface-hover)';
        cardCod.style.borderColor = 'var(--border-color)';
        cardCod.style.backgroundColor = '#ffffff';
        bankPreview.style.display = 'block';
        btnSubmit.innerHTML = '💳 Tiến hành đặt hàng & Chuyển khoản';
        disclaimer.innerHTML = 'Sau khi xác nhận, bạn sẽ nhận được mã QR chuyển khoản ngân hàng tự động điền sẵn tiền và mã đơn.';
    } else {
        cardCod.style.borderColor = 'var(--primary-color)';
        cardCod.style.backgroundColor = 'var(--surface-hover)';
        cardBank.style.borderColor = 'var(--border-color)';
        cardBank.style.backgroundColor = '#ffffff';
        bankPreview.style.display = 'none';
        btnSubmit.innerHTML = '✓ Xác nhận đặt hàng (COD)';
        disclaimer.innerHTML = 'Nhấn "Xác nhận đặt hàng" đồng nghĩa với việc bạn đồng ý thanh toán số tiền trên khi nhận món ăn từ shipper.';
    }
}
</script>
