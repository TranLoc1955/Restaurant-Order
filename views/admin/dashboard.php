<!-- 4 THẺ THỐNG KÊ TỔNG QUAN -->
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:1.5rem; margin-bottom:2rem;">
    <!-- Doanh thu hợp lệ (Chỉ DELIVERED) -->
    <div class="card" style="padding:1.5rem; border-left:4px solid var(--success-color);">
        <div style="font-size:0.8125rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.35rem;">
            Doanh Thu Hợp Lệ (DELIVERED)
        </div>
        <div style="font-size:1.85rem; font-weight:800; color:var(--success-color);">
            <?= number_format($stats['valid_revenue'], 0, ',', '.') ?>đ
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">
            Chỉ tính các đơn đã giao thành công
        </div>
    </div>

    <!-- Tổng số đơn -->
    <div class="card" style="padding:1.5rem; border-left:4px solid var(--primary-color);">
        <div style="font-size:0.8125rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.35rem;">
            Tổng Số Đơn Hàng
        </div>
        <div style="font-size:1.85rem; font-weight:800; color:var(--text-color);">
            <?= $stats['total_orders'] ?> đơn
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">
            Toàn bộ các đơn trong hệ thống
        </div>
    </div>

    <!-- Đơn chờ duyệt (PENDING) -->
    <div class="card" style="padding:1.5rem; border-left:4px solid var(--warning-color);">
        <div style="font-size:0.8125rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.35rem;">
            Đơn Đang Chờ Duyệt
        </div>
        <div style="font-size:1.85rem; font-weight:800; color:var(--warning-color);">
            <?= $stats['pending_orders'] ?> đơn
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">
            Cần nhân viên xác nhận
        </div>
    </div>

    <!-- Đơn hoàn tất -->
    <div class="card" style="padding:1.5rem; border-left:4px solid #7c3aed;">
        <div style="font-size:0.8125rem; font-weight:700; text-transform:uppercase; color:var(--text-muted); margin-bottom:0.35rem;">
            Đơn Đã Giao Thành Công
        </div>
        <div style="font-size:1.85rem; font-weight:800; color:#7c3aed;">
            <?= $stats['delivered_orders'] ?> đơn
        </div>
        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:0.35rem;">
            Tỷ lệ thành công: <?= $stats['total_orders'] > 0 ? round(($stats['delivered_orders'] / $stats['total_orders']) * 100, 1) : 0 ?>%
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1fr; gap:2rem; align-items:flex-start;">
    <!-- TOP MÓN ĂN BÁN CHẠY NHẤT -->
    <div class="card" style="padding:1.5rem;">
        <h3 style="margin-bottom:1.25rem; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">
            Top Món Ăn Bán Chạy Nhất
        </h3>
        <?php if (empty($stats['top_dishes'])): ?>
            <p class="text-muted text-center" style="padding:2rem;">Chưa có dữ liệu bán chạy.</p>
        <?php else: ?>
            <table style="width:100%; border-collapse:collapse; font-size:0.875rem;">
                <thead>
                    <tr style="border-bottom:2px solid var(--border-color); text-align:left;">
                        <th style="padding:0.5rem 0;">Món ăn</th>
                        <th style="text-align:center;">Số lượng bán</th>
                        <th style="text-align:right;">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($stats['top_dishes'] as $top): ?>
                        <tr style="border-bottom:1px solid var(--border-color);">
                            <td style="padding:0.75rem 0;">
                                <strong><?= htmlspecialchars($top['name']) ?></strong>
                            </td>
                            <td style="text-align:center; font-weight:700; color:var(--primary-color);">
                                <?= $top['total_qty'] ?> suất
                            </td>
                            <td style="text-align:right; font-weight:700;">
                                <?= number_format((float)$top['total_sales'], 0, ',', '.') ?>đ
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- ĐƠN HÀNG GẦN ĐÂY -->
    <div class="card" style="padding:1.5rem;">
        <div class="d-flex justify-between align-center mb-3" style="border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">
            <h3 style="margin:0;">Đơn Hàng Gần Đây</h3>
            <a href="<?= Response::baseUrl('staff/orders') ?>" class="text-primary font-bold" style="font-size:0.8125rem;">Xem tất cả &rarr;</a>
        </div>
        <div style="display:flex; flex-direction:column; gap:0.75rem;">
            <?php foreach ($stats['recent_orders'] as $rc): ?>
                <div class="d-flex justify-between align-center" style="padding:0.75rem; background:var(--surface-hover); border-radius:var(--radius-md);">
                    <div>
                        <div class="font-bold text-primary"><?= htmlspecialchars($rc['order_code']) ?></div>
                        <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($rc['recipient_name']) ?> &bull; <?= date('d/m/Y H:i', strtotime($rc['created_at'])) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <span class="badge badge-<?= strtolower($rc['order_status']) ?>"><?= $rc['order_status'] ?></span>
                        <div style="font-weight:700; font-size:0.875rem; margin-top:2px;"><?= number_format((float)$rc['total_amount'], 0, ',', '.') ?>đ</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
