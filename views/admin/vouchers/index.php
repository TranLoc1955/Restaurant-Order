<div class="d-flex justify-between align-center mb-4">
    <div>
        <h2 style="margin:0;">Quản Lý Mã Giảm Giá & Voucher</h2>
        <p class="text-muted" style="margin:0;">Tạo chương trình ưu đãi, voucher tri ân khách hàng cũ và mã chào đón bạn mới</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openCreateVoucherModal()">
        + Tạo Voucher Mới
    </button>
</div>

<!-- Thẻ thống kê nhanh -->
<div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:1.25rem; margin-bottom:1.75rem;">
    <?php
        $totalVouchers = count($vouchers);
        $activeCount = 0;
        $totalUsages = 0;
        $returningOnlyCount = 0;

        foreach ($vouchers as $v) {
            if ($v['status'] === 'active') $activeCount++;
            $totalUsages += (int)$v['used_count'];
            if ($v['customer_condition'] === 'RETURNING_ONLY') $returningOnlyCount++;
        }
    ?>
    <div class="card" style="padding:1.25rem; border-left:4px solid var(--primary-color);">
        <div style="font-size:0.8rem; color:#64748b; font-weight:600;">TỔNG SỐ VOUCHER</div>
        <div style="font-size:1.75rem; font-weight:800; color:#0f172a; margin-top:4px;"><?= $totalVouchers ?></div>
    </div>
    <div class="card" style="padding:1.25rem; border-left:4px solid #16a34a;">
        <div style="font-size:0.8rem; color:#64748b; font-weight:600;">ĐANG HOẠT ĐỘNG</div>
        <div style="font-size:1.75rem; font-weight:800; color:#16a34a; margin-top:4px;"><?= $activeCount ?></div>
    </div>
    <div class="card" style="padding:1.25rem; border-left:4px solid #3b82f6;">
        <div style="font-size:0.8rem; color:#64748b; font-weight:600;">TỔNG LƯỢT ĐÃ DÙNG</div>
        <div style="font-size:1.75rem; font-weight:800; color:#2563eb; margin-top:4px;"><?= $totalUsages ?></div>
    </div>
    <div class="card" style="padding:1.25rem; border-left:4px solid #f59e0b;">
        <div style="font-size:0.8rem; color:#64748b; font-weight:600;">VOUCHER TRI ÂN KHÁCH CŨ</div>
        <div style="font-size:1.75rem; font-weight:800; color:#d97706; margin-top:4px;"><?= $returningOnlyCount ?></div>
    </div>
</div>

<!-- Bảng danh sách voucher -->
<div class="cart-table-wrapper">
    <table class="cart-table">
        <thead>
            <tr>
                <th>Mã Code</th>
                <th>Tiêu đề & Mô tả</th>
                <th>Mức giảm giá</th>
                <th>Điều kiện khách</th>
                <th>Đơn tối thiểu</th>
                <th>Lượt dùng / Giới hạn</th>
                <th>Hạn dùng</th>
                <th>Trạng thái</th>
                <th style="text-align:right;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($vouchers)): ?>
                <tr>
                    <td colspan="9" style="text-align:center; padding:3rem;" class="text-muted">
                        Chưa có mã giảm giá nào. Hãy bấm "+ Tạo Voucher Mới" để bắt đầu!
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($vouchers as $v): ?>
                    <tr>
                        <td>
                            <strong style="font-family:monospace; font-size:0.95rem; background:#fee2e2; color:var(--primary-color); padding:3px 8px; border-radius:4px; letter-spacing:0.05em;">
                                <?= htmlspecialchars($v['code']) ?>
                            </strong>
                        </td>
                        <td>
                            <div style="font-weight:700; color:#0f172a;"><?= htmlspecialchars($v['title']) ?></div>
                            <?php if (!empty($v['description'])): ?>
                                <div style="font-size:0.775rem; color:#64748b; max-width:260px; margin-top:2px;">
                                    <?= htmlspecialchars($v['description']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($v['discount_type'] === 'FIXED'): ?>
                                <strong style="color:#0f172a;"><?= number_format((float)$v['discount_value'], 0, ',', '.') ?>đ</strong>
                            <?php else: ?>
                                <strong style="color:#0f172a;"><?= (float)$v['discount_value'] ?>%</strong>
                                <?php if (!empty($v['max_discount_amount'])): ?>
                                    <div style="font-size:0.75rem; color:#64748b;">(Tối đa: <?= number_format((float)$v['max_discount_amount'], 0, ',', '.') ?>đ)</div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($v['customer_condition'] === 'RETURNING_ONLY'): ?>
                                <span style="font-size:0.75rem; font-weight:700; background:#fef3c7; color:#b45309; padding:2px 8px; border-radius:12px; display:inline-flex; align-items:center; gap:2px;">
                                    <span>⭐️</span> Khách cũ từng mua
                                </span>
                            <?php elseif ($v['customer_condition'] === 'NEW_ONLY'): ?>
                                <span style="font-size:0.75rem; font-weight:700; background:#e0f2fe; color:#0369a1; padding:2px 8px; border-radius:12px; display:inline-flex; align-items:center; gap:2px;">
                                    <span>✨</span> Khách mới
                                </span>
                            <?php else: ?>
                                <span style="font-size:0.75rem; font-weight:600; background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:12px;">
                                    Tất cả khách hàng
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span><?= number_format((float)$v['min_order_amount'], 0, ',', '.') ?>đ</span>
                        </td>
                        <td>
                            <strong><?= (int)$v['used_count'] ?></strong>
                            <span class="text-muted">/ <?= $v['usage_limit'] ? (int)$v['usage_limit'] : '∞' ?></span>
                            <div style="font-size:0.72rem; color:#64748b;">(<?= (int)$v['usage_limit_per_user'] ?> lần/khách)</div>
                        </td>
                        <td>
                            <?php if (!empty($v['end_date'])): ?>
                                <div style="font-size:0.8rem;"><?= date('d/m/Y', strtotime($v['end_date'])) ?></div>
                                <?php if (strtotime($v['end_date']) < time()): ?>
                                    <span style="font-size:0.7rem; color:#dc2626; font-weight:700;">Hết hạn</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:0.8rem;">Vô thời hạn</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($v['status'] === 'active'): ?>
                                <span class="badge badge-delivered" style="font-size:0.75rem;">Đang bật</span>
                            <?php else: ?>
                                <span class="badge badge-rejected" style="font-size:0.75rem;">Đang tắt</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <div class="d-flex justify-end gap-1" style="justify-content:flex-end;">
                                <!-- Nút bật / tắt -->
                                <form action="<?= Response::baseUrl('admin/vouchers/' . $v['id'] . '/toggle') ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline" title="<?= $v['status'] === 'active' ? 'Tạm tắt voucher' : 'Bật kích hoạt' ?>">
                                        <?= $v['status'] === 'active' ? '⏸️ Tắt' : '▶️ Bật' ?>
                                    </button>
                                </form>

                                <!-- Nút xóa -->
                                <form action="<?= Response::baseUrl('admin/vouchers/' . $v['id'] . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa/ngừng mã voucher <?= htmlspecialchars($v['code']) ?>?');">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa voucher">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL TẠO VOUCHER MỚI -->
<div id="create-voucher-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:1.5rem;">
    <div class="card" style="max-width:540px; width:100%; max-height:90vh; overflow-y:auto; padding:2rem; box-shadow:0 20px 25px -5px rgba(0,0,0,0.2);">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin:0;">Tạo Mã Giảm Giá Mới</h3>
            <button type="button" onclick="closeCreateVoucherModal()" style="background:none; border:none; font-size:1.5rem; cursor:pointer; color:#64748b;">&times;</button>
        </div>

        <form action="<?= Response::baseUrl('admin/vouchers') ?>" method="POST" data-loading>
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group mb-3">
                <label class="form-label">Mã Code Voucher <span class="required">*</span></label>
                <input type="text" name="code" class="form-control" placeholder="VD: TRIAN30K, BANMOI, FREESHIP" style="text-transform:uppercase; font-weight:700;" required>
                <div class="form-hint">Nhập không dấu, viết liền. Tự động chuyển thành chữ hoa.</div>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Tiêu đề khuyến mãi <span class="required">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="VD: Tri ân khách hàng thân thiết" required>
            </div>

            <div class="form-group mb-3">
                <label class="form-label">Mô tả chi tiết</label>
                <textarea name="description" rows="2" class="form-control" placeholder="Mô tả quyền lợi hiển thị cho khách..."></textarea>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;" class="mb-3">
                <div class="form-group">
                    <label class="form-label">Loại giảm giá</label>
                    <select name="discount_type" class="form-control" id="modal-discount-type" onchange="toggleDiscountFields(this.value)">
                        <option value="FIXED">Số tiền cố định (VNĐ)</option>
                        <option value="PERCENT">Phần trăm (%)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Mức giảm <span class="required">*</span></label>
                    <input type="number" name="discount_value" id="modal-discount-value" class="form-control" placeholder="VD: 30000 hoặc 10" min="1" step="any" required>
                </div>
            </div>

            <div class="form-group mb-3" id="group-max-discount" style="display:none;">
                <label class="form-label">Giảm tối đa (VNĐ) khi chọn % (Tùy chọn)</label>
                <input type="number" name="max_discount_amount" class="form-control" placeholder="VD: 50000" min="0" step="1000">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;" class="mb-3">
                <div class="form-group">
                    <label class="form-label">Đơn tối thiểu (VNĐ)</label>
                    <input type="number" name="min_order_amount" class="form-control" placeholder="0" min="0" value="0" step="1000">
                </div>
                <div class="form-group">
                    <label class="form-label">Đối tượng khách hàng</label>
                    <select name="customer_condition" class="form-control">
                        <option value="ALL">Mọi khách hàng</option>
                        <option value="RETURNING_ONLY">⭐️ Chỉ khách hàng cũ từng mua</option>
                        <option value="NEW_ONLY">✨ Chỉ khách mới đặt lần đầu</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;" class="mb-3">
                <div class="form-group">
                    <label class="form-label">Tổng lượt dùng tối đa</label>
                    <input type="number" name="usage_limit" class="form-control" placeholder="Để trống nếu không giới hạn" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Số lần/1 người dùng</label>
                    <input type="number" name="usage_limit_per_user" class="form-control" value="1" min="1">
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label">Ngày hết hạn (Tùy chọn)</label>
                <input type="date" name="end_date" class="form-control">
                <div class="form-hint">Để trống nếu mã có hiệu lực vĩnh viễn.</div>
            </div>

            <div class="d-flex justify-end gap-1">
                <button type="button" class="btn btn-outline" onclick="closeCreateVoucherModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu Voucher</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateVoucherModal() {
    document.getElementById('create-voucher-modal').style.display = 'flex';
}
function closeCreateVoucherModal() {
    document.getElementById('create-voucher-modal').style.display = 'none';
}
function toggleDiscountFields(type) {
    const maxGroup = document.getElementById('group-max-discount');
    if (type === 'PERCENT') {
        maxGroup.style.display = 'block';
    } else {
        maxGroup.style.display = 'none';
    }
}
</script>
