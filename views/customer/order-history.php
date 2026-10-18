<div class="container" style="padding: 2.5rem 1.5rem 4rem;">
    <h1 style="margin-bottom: 0.5rem;">Lịch Sử Đơn Hàng</h1>
    <p class="text-muted" style="margin-bottom: 2rem;">Theo dõi trạng thái đơn món ăn và lịch sử đã đặt của bạn</p>

    <?php if (empty($orders)): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">📦</div>
            <h3>Bạn chưa có đơn hàng nào</h3>
            <p class="text-muted">Khi bạn đặt món, toàn bộ thông tin và tiến độ chế biến sẽ xuất hiện ở đây.</p>
            <div class="mt-2">
                <a href="<?= Response::baseUrl('menu') ?>" class="btn btn-primary">Khám phá thực đơn ngay</a>
            </div>
        </div>
    <?php else: ?>
        <div class="cart-table-wrapper">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Ngày đặt</th>
                        <th>Người nhận</th>
                        <th>Số lượng</th>
                        <th>Tổng tiền (COD)</th>
                        <th>Trạng thái đơn</th>
                        <th style="text-align:right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $statusNames = [
                        'PENDING'   => 'Chờ xác nhận',
                        'CONFIRMED' => 'Đã xác nhận',
                        'PREPARING' => 'Đang chuẩn bị',
                        'READY'     => 'Đã nấu xong',
                        'DELIVERED' => 'Đã giao thành công',
                        'REJECTED'  => 'Bị từ chối',
                        'CANCELLED' => 'Đã hủy',
                    ];
                    ?>
                    <?php foreach ($orders as $ord): ?>
                        <tr>
                            <td>
                                <a href="<?= Response::baseUrl('orders/' . $ord['order_code']) ?>" class="font-bold text-primary">
                                    <?= htmlspecialchars($ord['order_code']) ?>
                                </a>
                            </td>
                            <td class="text-muted" style="font-size:0.875rem;">
                                <?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?>
                            </td>
                            <td>
                                <div><strong><?= htmlspecialchars($ord['recipient_name']) ?></strong></div>
                                <div style="font-size:0.75rem; color:var(--text-muted);"><?= htmlspecialchars($ord['recipient_phone']) ?></div>
                            </td>
                            <td><?= (int)$ord['total_items'] ?> món</td>
                            <td class="font-bold text-primary">
                                <?= number_format((float)$ord['total_amount'], 0, ',', '.') ?>đ
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower($ord['order_status']) ?>">
                                    <?= $statusNames[$ord['order_status']] ?? $ord['order_status'] ?>
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="<?= Response::baseUrl('orders/' . $ord['order_code']) ?>" class="btn btn-sm btn-outline">
                                    Xem chi tiết &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
