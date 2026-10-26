<div class="card mb-4" style="padding: 1.5rem;">
    <!-- Thanh công cụ tìm kiếm và lọc đơn hàng -->
    <form action="<?= Response::baseUrl('staff/orders') ?>" method="GET" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:center;">
        <div style="flex:1; min-width:220px;">
            <input 
                type="text" 
                name="search" 
                class="form-control" 
                placeholder="Tìm theo mã đơn (ORD-...), tên người nhận, SĐT..."
                value="<?= htmlspecialchars($search ?? '') ?>"
            >
        </div>

        <div style="min-width: 170px;">
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <option value="PENDING" <?= ($currentStatus === 'PENDING') ? 'selected' : '' ?>>Chờ xác nhận (PENDING)</option>
                <option value="CONFIRMED" <?= ($currentStatus === 'CONFIRMED') ? 'selected' : '' ?>>Đã xác nhận (CONFIRMED)</option>
                <option value="PREPARING" <?= ($currentStatus === 'PREPARING') ? 'selected' : '' ?>>Đang chuẩn bị (PREPARING)</option>
                <option value="READY" <?= ($currentStatus === 'READY') ? 'selected' : '' ?>>Đã nấu xong (READY)</option>
                <option value="DELIVERED" <?= ($currentStatus === 'DELIVERED') ? 'selected' : '' ?>>Đã giao (DELIVERED)</option>
                <option value="REJECTED" <?= ($currentStatus === 'REJECTED') ? 'selected' : '' ?>>Bị từ chối (REJECTED)</option>
                <option value="CANCELLED" <?= ($currentStatus === 'CANCELLED') ? 'selected' : '' ?>>Khách hủy (CANCELLED)</option>
            </select>
        </div>

        <div style="min-width: 150px;">
            <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($currentDate ?? '') ?>" onchange="this.form.submit()">
        </div>

        <button type="submit" class="btn btn-primary">
            🔍 Lọc đơn
        </button>

        <?php if (!empty($search) || !empty($currentStatus) || !empty($currentDate)): ?>
            <a href="<?= Response::baseUrl('staff/orders') ?>" class="btn btn-outline">
                ✕ Xóa lọc
            </a>
        <?php endif; ?>
    </form>
</div>

<!-- Bảng danh sách đơn hàng cho nhân viên xử lý -->
<div class="cart-table-wrapper">
    <div style="padding: 1rem 1.5rem; background:var(--surface-color); color:var(--text-color); border-bottom:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center;">
        <strong>Danh Sách Đơn Hàng Cần Xử Lý</strong>
        <span class="text-muted" style="font-size:0.875rem;">Tìm thấy <strong><?= count($orders) ?></strong> đơn</span>
    </div>

    <?php if (empty($orders)): ?>
        <div style="padding: 3rem; text-align:center; color:var(--text-muted);">
            Không có đơn hàng nào khớp với điều kiện lọc.
        </div>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Thời gian</th>
                    <th>Khách hàng & SĐT</th>
                    <th>Địa chỉ giao hàng</th>
                    <th>Tổng tiền & Thanh toán</th>
                    <th>Trạng thái đơn</th>
                    <th style="text-align:right;">Thao tác xử lý</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $statusLabels = [
                    'PENDING'   => 'Chờ xác nhận',
                    'CONFIRMED' => 'Đã duyệt',
                    'PREPARING' => 'Đang nấu',
                    'READY'     => 'Đã xong',
                    'DELIVERED' => 'Đã giao',
                    'REJECTED'  => 'Từ chối',
                    'CANCELLED' => 'Đã hủy',
                ];
                ?>
                <?php foreach ($orders as $ord): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--primary-color); font-size:0.95rem;">
                                <?= htmlspecialchars($ord['order_code']) ?>
                            </strong>
                            <div style="font-size:0.75rem; color:var(--text-muted);"><?= (int)$ord['items_count'] ?> món ăn</div>
                        </td>
                        <td style="font-size:0.8125rem; color:var(--text-muted);">
                            <?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?>
                        </td>
                        <td>
                            <div><strong><?= htmlspecialchars($ord['recipient_name']) ?></strong></div>
                            <a href="tel:<?= htmlspecialchars($ord['recipient_phone']) ?>" style="font-size:0.8125rem;">
                                📞 <?= htmlspecialchars($ord['recipient_phone']) ?>
                            </a>
                        </td>
                        <td style="font-size:0.8125rem; max-width:200px; line-height:1.4;">
                            <?= htmlspecialchars($ord['delivery_address']) ?>
                            <?php if (!empty($ord['note'])): ?>
                                <div style="color:var(--primary-color); font-style:italic; margin-top:2px;">
                                    "<?= htmlspecialchars($ord['note']) ?>"
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="font-bold text-primary">
                                <?= number_format((float)$ord['total_amount'], 0, ',', '.') ?>đ
                            </div>
                            <div style="font-size:0.75rem; margin-top:3px;">
                                <?php if (($ord['payment_method'] ?? 'COD') === 'BANK_TRANSFER'): ?>
                                    <span style="display:inline-block; padding:1px 6px; border-radius:4px; background:#e0e7ff; color:#3730a3; font-weight:600;">
                                        💳 Chuyển khoản
                                    </span>
                                    <div style="margin-top:2px;">
                                        <?php if (($ord['payment_status'] ?? 'unpaid') === 'paid'): ?>
                                            <span style="color:#16a34a; font-weight:700;">✓ Đã nhận tiền</span>
                                        <?php else: ?>
                                            <span style="color:#d97706; font-weight:700;">⏳ Chưa nhận tiền</span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="display:inline-block; padding:1px 6px; border-radius:4px; background:#f1f5f9; color:#475569; font-weight:600;">
                                        💵 Tiền mặt COD
                                    </span>
                                    <div style="margin-top:2px;">
                                        <?php if (($ord['payment_status'] ?? 'unpaid') === 'paid'): ?>
                                            <span style="color:#16a34a; font-weight:700;">✓ Đã thu</span>
                                        <?php else: ?>
                                            <span style="color:#64748b;">(Thu khi giao)</span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?= strtolower($ord['order_status']) ?>">
                                <?= $statusLabels[$ord['order_status']] ?? $ord['order_status'] ?>
                            </span>
                            <?php if ($ord['order_status'] === 'REJECTED' && !empty($ord['rejection_reason'])): ?>
                                <div style="font-size:0.72rem; color:var(--danger-color); margin-top:3px; max-width:140px;">
                                    <?= htmlspecialchars($ord['rejection_reason']) ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:right;">
                            <div class="d-flex justify-end gap-1" style="justify-content: flex-end; flex-wrap:wrap;">
                                <!-- Nút xác nhận nhận tiền chuyển khoản nếu khách chọn BANK_TRANSFER và chưa paid -->
                                <?php if (($ord['payment_method'] ?? 'COD') === 'BANK_TRANSFER' && ($ord['payment_status'] ?? 'unpaid') === 'unpaid'): ?>
                                    <form action="<?= Response::baseUrl('staff/orders/payment-status') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Xác nhận đã nhận được tiền chuyển khoản ngân hàng cho đơn <?= htmlspecialchars($ord['order_code']) ?>?');">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <input type="hidden" name="payment_status" value="paid">
                                        <button type="submit" class="btn btn-sm btn-outline" style="color:#16a34a; border-color:#16a34a; font-weight:600;" title="Xác nhận khách đã chuyển khoản thành công">
                                            ✓ Đã nhận CK
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Các nút chuyển trạng thái theo đúng State Machine -->
                                <?php if ($ord['order_status'] === 'PENDING'): ?>
                                    <!-- Xác nhận đơn -->
                                    <form action="<?= Response::baseUrl('staff/orders/status') ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <input type="hidden" name="new_status" value="CONFIRMED">
                                        <button type="submit" class="btn btn-sm btn-primary" title="Xác nhận tiếp nhận đơn này">
                                            ✓ Duyệt đơn
                                        </button>
                                    </form>

                                    <!-- Từ chối đơn (mở modal nhập lý do) -->
                                    <button type="button" class="btn btn-sm btn-outline btn-danger" onclick="openRejectModal(<?= $ord['id'] ?>, '<?= htmlspecialchars($ord['order_code']) ?>')">
                                        ✕ Từ chối
                                    </button>

                                <?php elseif ($ord['order_status'] === 'CONFIRMED'): ?>
                                    <form action="<?= Response::baseUrl('staff/orders/status') ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <input type="hidden" name="new_status" value="PREPARING">
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Chuyển xuống bếp nấu">
                                            🍳 Bắt đầu nấu
                                        </button>
                                    </form>

                                <?php elseif ($ord['order_status'] === 'PREPARING'): ?>
                                    <form action="<?= Response::baseUrl('staff/orders/status') ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <input type="hidden" name="new_status" value="READY">
                                        <button type="submit" class="btn btn-sm btn-primary" style="background:#7c3aed; border-color:#7c3aed;" title="Món đã nấu xong, chờ shipper">
                                            📦 Sẵn sàng giao
                                        </button>
                                    </form>

                                <?php elseif ($ord['order_status'] === 'READY'): ?>
                                    <form action="<?= Response::baseUrl('staff/orders/status') ?>" method="POST" style="display:inline;">
                                        <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                        <input type="hidden" name="order_id" value="<?= $ord['id'] ?>">
                                        <input type="hidden" name="new_status" value="DELIVERED">
                                        <button type="submit" class="btn btn-sm btn-secondary" title="Xác nhận giao thành công">
                                            🛵 Đã giao thành công
                                        </button>
                                    </form>

                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.75rem;">Kết thúc</span>
                                <?php endif; ?>

                                <!-- Nút xem chi tiết modal -->
                                <button type="button" class="btn btn-sm btn-outline" onclick="viewOrderDetail(<?= $ord['id'] ?>)" title="Xem danh sách món và audit log">
                                    Chi tiết
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Modal Từ Chối Đơn Hàng -->
<div id="reject-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width:460px; width:100%; padding:2rem;">
        <h3 style="margin-bottom:0.5rem; color:var(--danger-color);">Từ Chối Đơn Hàng</h3>
        <p class="text-muted" style="font-size:0.875rem; margin-bottom:1.25rem;">
            Vui lòng nhập lý do từ chối đơn hàng <strong id="reject-order-code"></strong> để thông báo cho khách hàng:
        </p>

        <form action="<?= Response::baseUrl('staff/orders/status') ?>" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="order_id" id="reject-order-id" value="">
            <input type="hidden" name="new_status" value="REJECTED">

            <div class="form-group">
                <label class="form-label">Lý do từ chối đơn <span class="required">*</span></label>
                <textarea 
                    name="rejection_reason" 
                    id="reject-reason-input" 
                    rows="3" 
                    class="form-control" 
                    placeholder="Ví dụ: Nhà hàng tạm hết nguyên liệu tươi trong ngày, Quá tải khung giờ cao điểm..." 
                    required
                ></textarea>
            </div>

            <div class="d-flex justify-between gap-1 mt-3">
                <button type="button" class="btn btn-outline" onclick="closeRejectModal()">
                    Hủy bỏ
                </button>
                <button type="submit" class="btn btn-danger">
                    Xác nhận từ chối đơn
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Xem Chi Tiết Đơn Hàng (AJAX Drawer) -->
<div id="detail-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width:620px; width:100%; padding:2rem; max-height:85vh; overflow-y:auto;">
        <div class="d-flex justify-between align-center mb-3">
            <h3 style="margin:0;" id="modal-order-title">Chi Tiết Đơn Hàng</h3>
            <button type="button" class="btn btn-sm btn-outline" onclick="closeDetailModal()">&times;</button>
        </div>

        <div id="modal-order-content">
            <div style="text-align:center; padding:2rem;">Đang tải thông tin...</div>
        </div>
    </div>
</div>

<script>
    function openRejectModal(id, code) {
        document.getElementById('reject-order-id').value = id;
        document.getElementById('reject-order-code').textContent = code;
        document.getElementById('reject-reason-input').value = '';
        document.getElementById('reject-modal').style.display = 'flex';
    }
    function closeRejectModal() {
        document.getElementById('reject-modal').style.display = 'none';
    }

    function viewOrderDetail(id) {
        document.getElementById('detail-modal').style.display = 'flex';
        document.getElementById('modal-order-content').innerHTML = '<div style="text-align:center; padding:2rem;">Đang tải...</div>';

        fetch(window.APP_BASE_URL + '/api/staff/orders/' + id)
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    document.getElementById('modal-order-content').innerHTML = '<div class="text-danger">' + data.message + '</div>';
                    return;
                }
                const order = data.order;
                const items = data.items;
                const history = data.history;

                let itemsHtml = '';
                items.forEach(it => {
                    itemsHtml += `
                        <tr style="border-bottom:1px solid #eee;">
                            <td style="padding:8px 0;"><strong>${it.quantity}x</strong> ${it.product_name_snapshot}</td>
                            <td style="padding:8px 0; text-align:right;">${parseInt(it.line_total).toLocaleString('vi-VN')}đ</td>
                        </tr>
                    `;
                });

                let historyHtml = '';
                history.forEach(h => {
                    historyHtml += `
                        <div style="font-size:0.8rem; margin-bottom:6px;">
                            <span class="text-muted">${h.created_at}</span> - 
                            <strong>${h.new_status}</strong>: ${h.note || ''} (Bởi: ${h.changer_name})
                        </div>
                    `;
                });

                document.getElementById('modal-order-content').innerHTML = `
                    <div style="margin-bottom:1rem; font-size:0.875rem;">
                        <div><strong>Khách hàng:</strong> ${order.recipient_name} (SĐT: ${order.recipient_phone})</div>
                        <div><strong>Địa chỉ:</strong> ${order.delivery_address}</div>
                        ${order.note ? `<div><strong>Ghi chú:</strong> <em>${order.note}</em></div>` : ''}
                        <div style="margin-top:0.4rem; padding-top:0.4rem; border-top:1px dashed #e2e8f0; display:flex; gap:1.5rem;">
                            <div>
                                <strong>Hình thức:</strong> 
                                ${order.payment_method === 'BANK_TRANSFER' ? '<span style="color:#2563eb; font-weight:700;">💳 Chuyển khoản ngân hàng</span>' : '<span>💵 Tiền mặt (COD)</span>'}
                            </div>
                            <div>
                                <strong>Thanh toán:</strong> 
                                ${order.payment_status === 'paid' ? '<span style="color:#16a34a; font-weight:700;">✓ Đã thanh toán</span>' : '<span style="color:#d97706; font-weight:700;">⏳ Chưa thanh toán</span>'}
                            </div>
                        </div>
                    </div>
                    <table style="width:100%; border-collapse:collapse; margin-bottom:1rem; font-size:0.875rem;">
                        <thead>
                            <tr style="border-bottom:2px solid #ddd; text-align:left;">
                                <th>Món ăn</th>
                                <th style="text-align:right;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody>${itemsHtml}</tbody>
                    </table>
                    <div style="border-top:1px dashed #ddd; padding-top:0.75rem; margin-bottom:1.25rem; font-size:0.875rem;">
                        <div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#64748b;">
                            <span>Tạm tính tiền món:</span>
                            <span>${parseInt(order.subtotal).toLocaleString('vi-VN')}đ</span>
                        </div>
                        ${order.voucher_code && parseFloat(order.discount_amount) > 0 ? `
                            <div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#dc2626; font-weight:600;">
                                <span>🎟️ Voucher (${order.voucher_code}):</span>
                                <span>-${parseInt(order.discount_amount).toLocaleString('vi-VN')}đ</span>
                            </div>
                        ` : ''}
                        ${parseInt(order.points_used) > 0 ? `
                            <div style="display:flex; justify-content:space-between; margin-bottom:3px; color:#16a34a; font-weight:600;">
                                <span>🎁 Điểm thưởng (${order.points_used} điểm):</span>
                                <span>-${parseInt(order.points_discount).toLocaleString('vi-VN')}đ</span>
                            </div>
                        ` : ''}
                        <div style="display:flex; justify-content:space-between; margin-bottom:6px; color:#64748b;">
                            <span>Phí giao hàng:</span>
                            <span>${parseInt(order.shipping_fee).toLocaleString('vi-VN')}đ</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; font-weight:800; font-size:1.15rem; color:var(--primary-color); border-top:1px solid #e2e8f0; padding-top:6px;">
                            <span>Tổng thu khách:</span>
                            <span>${parseInt(order.total_amount).toLocaleString('vi-VN')}đ</span>
                        </div>
                    </div>
                    <h5 style="margin-bottom:0.5rem;">Lịch Sử Chuyển Trạng Thái:</h5>
                    <div style="background:#f8fafc; padding:10px; border-radius:6px;">${historyHtml}</div>
                `;
            })
            .catch(err => {
                console.error(err);
                document.getElementById('modal-order-content').innerHTML = '<div class="text-danger">Lỗi khi tải thông tin.</div>';
            });
    }

    function closeDetailModal() {
        document.getElementById('detail-modal').style.display = 'none';
    }
</script>
