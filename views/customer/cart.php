<div class="container" style="padding: 2.5rem 1.5rem 4rem;">
    <h1 style="margin-bottom: 0.5rem;">Giỏ Hàng Của Bạn</h1>
    <p class="text-muted" style="margin-bottom: 2rem;">Kiểm tra danh sách món ăn đã chọn và tiến hành đặt hàng</p>

    <?php if (empty($items)): ?>
        <div class="card text-center" style="padding: 4.5rem 2rem;">
            <div style="font-size: 5rem; margin-bottom: 1rem;">🛒</div>
            <h2>Giỏ hàng của bạn đang trống</h2>
            <p class="text-muted" style="max-width: 440px; margin: 0.5rem auto 1.5rem;">
                Bạn chưa thêm món ăn nào vào giỏ. Hãy dạo quanh thực đơn của Nhà Hàng Trần Lộc để chọn những món ngon cho bữa ăn nhé!
            </p>
            <div>
                <a href="<?= Response::baseUrl('menu') ?>" class="btn btn-primary btn-lg" id="btn-empty-cart-to-menu">
                    🍽️ Xem thực đơn ngay
                </a>
            </div>
        </div>
    <?php else: ?>
        <div style="display:grid; grid-template-columns: 1.8fr 1fr; gap: 2rem; align-items:flex-start;">
            <!-- Cột bảng món ăn -->
            <div class="cart-table-wrapper">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Món ăn</th>
                            <th>Đơn giá</th>
                            <th style="text-align:center;">Số lượng</th>
                            <th>Thành tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr id="cart-row-<?= $item['product_id'] ?>">
                                <td>
                                    <div class="d-flex align-center gap-1">
                                        <div style="width:48px; height:48px; border-radius:var(--radius-md); background:#fff7ed; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0; border:1px solid var(--border-color);">
                                            <img src="<?= Product::getImageUrl($item['image'] ?? '') ?>" alt="<?= htmlspecialchars($item['name']) ?>" style="width:100%; height:100%; object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <span style="display:none; font-size:1.5rem;">🍲</span>
                                        </div>
                                        <div>
                                            <a href="<?= Response::baseUrl('products/' . $item['product_id']) ?>" class="font-bold text-dark" style="color:var(--text-color);">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                            <?php if (!$item['is_available']): ?>
                                                <div style="font-size:0.75rem; color:var(--danger-color); font-weight:bold;">
                                                    ⚠️ Món hiện tạm hết hàng
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="font-medium">
                                    <?= number_format($item['price'], 0, ',', '.') ?>đ
                                </td>
                                <td style="text-align:center;">
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" onclick="updateCartItem(<?= $item['product_id'] ?>, <?= $item['quantity'] - 1 ?>)">&minus;</button>
                                        <input type="text" class="qty-input" value="<?= $item['quantity'] ?>" readonly>
                                        <button type="button" class="qty-btn" onclick="updateCartItem(<?= $item['product_id'] ?>, <?= $item['quantity'] + 1 ?>)">&#43;</button>
                                    </div>
                                </td>
                                <td class="font-bold text-primary">
                                    <?= number_format($item['line_total'], 0, ',', '.') ?>đ
                                </td>
                                <td style="text-align:right;">
                                    <button 
                                        type="button" 
                                        onclick="removeCartItem(<?= $item['product_id'] ?>)" 
                                        style="background:none; border:none; color:var(--danger-color); cursor:pointer; font-size:1.1rem; padding:0.25rem 0.5rem;"
                                        title="Xóa món này"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cột tóm tắt đơn hàng -->
            <div class="card" style="padding: 1.75rem;">
                <h3 style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">
                    Tóm Tắt Đơn Hàng
                </h3>

                <div class="d-flex justify-between mb-2">
                    <span class="text-muted">Tạm tính:</span>
                    <span class="font-bold"><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                </div>

                <div class="d-flex justify-between mb-3">
                    <span class="text-muted">Phí giao hàng (COD):</span>
                    <span class="font-bold"><?= number_format($shippingFee, 0, ',', '.') ?>đ</span>
                </div>

                <?php if ($subtotal < $minAmount): ?>
                    <div style="padding: 0.75rem; background-color: #fef2f2; border-radius: var(--radius-sm); border-left: 3px solid var(--danger-color); font-size: 0.8125rem; color: #991b1b; margin-bottom: 1.25rem;">
                        ⚠️ Đơn hàng tối thiểu là <strong><?= number_format($minAmount, 0, ',', '.') ?>đ</strong>. Quý khách vui lòng chọn thêm ít nhất <strong><?= number_format($minAmount - $subtotal, 0, ',', '.') ?>đ</strong> để tiếp tục đặt hàng.
                    </div>
                <?php endif; ?>

                <div class="d-flex justify-between align-center mb-4" style="border-top: 1px dashed var(--border-color); padding-top: 1rem;">
                    <span style="font-size: 1.1rem; font-weight: 700;">Tổng cộng:</span>
                    <span style="font-size: 1.5rem; font-weight: 800; color: var(--primary-color);">
                        <?= number_format($total, 0, ',', '.') ?>đ
                    </span>
                </div>

                <?php if ($subtotal >= $minAmount): ?>
                    <a href="<?= Response::baseUrl('checkout') ?>" class="btn btn-primary btn-block btn-lg" id="btn-proceed-checkout">
                        🛵 Tiến hành đặt hàng (COD)
                    </a>
                <?php else: ?>
                    <button type="button" class="btn btn-primary btn-block btn-lg" disabled title="Chưa đạt mức tối thiểu">
                        Chưa đạt đơn tối thiểu
                    </button>
                <?php endif; ?>

                <div class="mt-2 text-center">
                    <a href="<?= Response::baseUrl('menu') ?>" class="text-muted" style="font-size: 0.875rem;">
                        &larr; Tiếp tục chọn thêm món
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
