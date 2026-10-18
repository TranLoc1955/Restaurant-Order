<div class="container" style="padding: 2.5rem 1.5rem 4rem;">
    <!-- Breadcrumb -->
    <nav style="font-size:0.875rem; color:var(--text-muted); margin-bottom:1.5rem;">
        <a href="<?= Response::baseUrl('') ?>">Trang chủ</a> &rsaquo; 
        <a href="<?= Response::baseUrl('menu') ?>">Thực đơn</a> &rsaquo; 
        <a href="<?= Response::baseUrl('menu?category=' . $product['category_id']) ?>"><?= htmlspecialchars($product['category_name']) ?></a> &rsaquo; 
        <span style="color:var(--text-color); font-weight:600;"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Khối chi tiết món ăn -->
    <div class="card" style="margin-bottom: 3.5rem; overflow:hidden;">
        <div style="display:grid; grid-template-columns: 1fr 1.25fr; gap: 2.5rem; padding: 2.5rem;">
            <!-- Cột hình ảnh / Thumbnail -->
            <div style="background-color:#fff7ed; border-radius:var(--radius-lg); height:360px; overflow:hidden; display:flex; align-items:center; justify-content:center; border:1px solid var(--border-color); position:relative;">
                <?php if ($product['availability_status'] === 'out_of_stock'): ?>
                    <span class="badge badge-rejected" style="position:absolute; top:15px; left:15px; font-size:0.8rem; z-index:2;">Tạm hết hàng</span>
                <?php else: ?>
                    <span class="badge badge-delivered" style="position:absolute; top:15px; left:15px; font-size:0.8rem; z-index:2;">Đang phục vụ</span>
                <?php endif; ?>
                <img src="<?= Product::getImageUrl($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" style="width:100%; height:100%; object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span style="display:none; font-size:7rem;">🍲</span>
            </div>

            <!-- Cột thông tin món -->
            <div style="display:flex; flex-direction:column; justify-content:center;">
                <span class="text-primary font-bold" style="font-size:0.875rem; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:0.5rem;">
                    <?= htmlspecialchars($product['category_name']) ?>
                </span>
                <h1 style="font-size:2rem; margin-bottom:0.5rem;"><?= htmlspecialchars($product['name']) ?></h1>
                
                <?php if (Session::isLoggedIn() && Session::userRole() === 'admin'): ?>
                    <div style="margin-bottom: 1rem;">
                        <a href="<?= Response::baseUrl('admin/products/' . $product['id'] . '/edit') ?>" class="btn btn-sm btn-outline" style="font-size:0.8rem; border-color: #0f172a; color: #0f172a; font-weight: 700;">
                            ✏️ Chỉnh sửa thông tin món này trong Quản trị
                        </a>
                    </div>
                <?php endif; ?>
                
                <div style="font-size:1.85rem; font-weight:800; color:var(--primary-color); margin-bottom:1.5rem;">
                    <?= number_format((float)$product['price'], 0, ',', '.') ?>đ
                </div>

                <div style="color:var(--text-muted); line-height:1.7; margin-bottom:2rem; font-size:0.95rem;">
                    <?= nl2br(htmlspecialchars($product['description'] ?: 'Món ăn đặc sắc với nguyên liệu tươi mới, gia vị đậm đà chuẩn phong vị ẩm thực nhà hàng.')) ?>
                </div>

                <!-- Thao tác chọn số lượng & Thêm giỏ hàng -->
                <?php if ($product['availability_status'] === 'in_stock'): ?>
                    <div style="display:flex; align-items:center; gap:1.25rem; flex-wrap:wrap; margin-bottom:1.5rem;">
                        <div class="qty-control" style="height:44px;">
                            <button type="button" class="qty-btn" onclick="decreaseDetailQty()" style="width:40px; height:44px;">&minus;</button>
                            <input type="number" id="product-qty" class="qty-input" value="1" min="1" max="99" style="width:50px; height:44px;">
                            <button type="button" class="qty-btn" onclick="increaseDetailQty()" style="width:40px; height:44px;">&#43;</button>
                        </div>

                        <button 
                            type="button" 
                            class="btn btn-primary btn-lg btn-add-to-cart" 
                            data-id="<?= $product['id'] ?>"
                            data-name="<?= htmlspecialchars($product['name']) ?>"
                            id="btn-detail-add-cart"
                        >
                            🛒 Thêm vào giỏ hàng
                        </button>

                        <button 
                            type="button" 
                            class="btn btn-outline btn-lg" 
                            onclick="Chatbot.askPrompt('Món <?= htmlspecialchars(addslashes($product['name'])) ?> có gì đặc biệt?')"
                            title="Hỏi AI về nguyên liệu và hương vị món này"
                        >
                            💬 Hỏi AI về món này
                        </button>
                    </div>
                <?php else: ?>
                    <div style="padding:1rem 1.25rem; background-color:#fff1f2; border-radius:var(--radius-md); border-left:4px solid var(--danger-color); color:#9f1239; margin-bottom:1.5rem;">
                        <strong>Món ăn tạm hết hàng:</strong> Nhà hàng đang chuẩn bị thêm nguyên liệu tươi cho món này. Quý khách vui lòng chọn món khác trong thực đơn nhé!
                    </div>
                <?php endif; ?>

                <div style="border-top:1px solid var(--border-color); padding-top:1.25rem; display:flex; gap:2rem; font-size:0.875rem; color:var(--text-muted);">
                    <div>✓ Giao hàng tận nơi (COD)</div>
                    <div>✓ Đóng hộp cẩn thận, giữ ấm</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Món liên quan cùng danh mục -->
    <?php if (!empty($relatedProducts)): ?>
        <div>
            <h3 style="margin-bottom: 1.5rem;">Món Ăn Cùng Danh Mục</h3>
            <div class="food-grid">
                <?php foreach ($relatedProducts as $rel): ?>
                    <div class="food-card">
                        <div class="food-card-thumb">
                            <img src="<?= Product::getImageUrl($rel['image']) ?>" alt="<?= htmlspecialchars($rel['name']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <span style="display:none; font-size:3rem;">🍲</span>
                        </div>
                        <div class="food-card-body">
                            <h4 class="food-card-title">
                                <a href="<?= Response::baseUrl('products/' . $rel['id']) ?>">
                                    <?= htmlspecialchars($rel['name']) ?>
                                </a>
                            </h4>
                            <div class="food-card-footer">
                                <div class="food-card-price">
                                    <?= number_format((float)$rel['price'], 0, ',', '.') ?>đ
                                </div>
                                <?php if ($rel['availability_status'] === 'in_stock'): ?>
                                    <button type="button" class="btn btn-sm btn-primary btn-add-to-cart" data-id="<?= $rel['id'] ?>" data-name="<?= htmlspecialchars($rel['name']) ?>">
                                        + Thêm
                                    </button>
                                <?php else: ?>
                                    <span class="badge badge-rejected" style="font-size:0.7rem;">Hết</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    function decreaseDetailQty() {
        const input = document.getElementById('product-qty');
        let val = parseInt(input.value) || 1;
        if (val > 1) {
            input.value = val - 1;
        }
    }
    function increaseDetailQty() {
        const input = document.getElementById('product-qty');
        let val = parseInt(input.value) || 1;
        if (val < 99) {
            input.value = val + 1;
        }
    }
</script>
