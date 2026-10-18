<?php if (Session::isLoggedIn()): ?>
    <?php $currentUser = Session::user(); ?>
    <?php if ($currentUser['role'] === 'admin'): ?>
        <!-- BANNER DÀNH RIÊNG CHO QUẢN TRỊ VIÊN -->
        <div style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color:#ffffff; padding: 1.5rem 0; border-bottom: 3px solid var(--primary-color);">
            <div class="container d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:#94a3b8; font-weight:700;">
                        Khu vực tác vụ Quản trị viên
                    </div>
                    <h3 style="color:#ffffff; margin:0.25rem 0 0; font-size:1.35rem;">
                        👑 Bảng Điều Khiển Quản Trị: <?= htmlspecialchars($currentUser['full_name']) ?>
                    </h3>
                </div>
                <div class="d-flex align-center gap-1" style="flex-wrap:wrap;">
                    <a href="<?= Response::baseUrl('admin/dashboard') ?>" class="btn btn-primary">
                        📊 Dashboard Doanh Thu
                    </a>
                    <a href="<?= Response::baseUrl('staff/orders') ?>" class="btn btn-outline" style="color:#ffffff; border-color:rgba(255,255,255,0.3);">
                        📦 Xử Lý Đơn Hàng
                    </a>
                    <a href="<?= Response::baseUrl('admin/products') ?>" class="btn btn-outline" style="color:#ffffff; border-color:rgba(255,255,255,0.3);">
                        🍽️ Quản Lý Món Ăn
                    </a>
                    <a href="<?= Response::baseUrl('admin/users') ?>" class="btn btn-outline" style="color:#ffffff; border-color:rgba(255,255,255,0.3);">
                        👥 Quản Lý Người Dùng
                    </a>
                </div>
            </div>
        </div>
    <?php elseif ($currentUser['role'] === 'staff'): ?>
        <!-- BANNER DÀNH RIÊNG CHO NHÂN VIÊN -->
        <div style="background: linear-gradient(135deg, #1e3a8a 0%, #172554 100%); color:#ffffff; padding: 1.5rem 0; border-bottom: 3px solid #3b82f6;">
            <div class="container d-flex justify-between align-center" style="flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; color:#93c5fd; font-weight:700;">
                        Khu vực tác vụ Nhân viên nhà hàng
                    </div>
                    <h3 style="color:#ffffff; margin:0.25rem 0 0; font-size:1.35rem;">
                        🧑‍🍳 Xin chào Nhân viên: <?= htmlspecialchars($currentUser['full_name']) ?>
                    </h3>
                </div>
                <div>
                    <a href="<?= Response::baseUrl('staff/orders') ?>" class="btn btn-primary" style="background:#2563eb; border-color:#2563eb;">
                        📦 Mở Bảng Xử Lý Đơn Hàng Ngay &rarr;
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container hero-grid">
        <div class="hero-content">
            <div style="display:inline-flex; align-items:center; gap:0.5rem; background:#ffedd5; color:#ea580c; padding:0.35rem 0.85rem; border-radius:var(--radius-full); font-size:0.8125rem; font-weight:700; margin-bottom:1rem;">
                <span>✨</span> Đặt món nhanh & Tư vấn qua Trợ lý AI
            </div>
            <h1>Tinh Hoa Ẩm Thực <span>Trần Lộc</span></h1>
            <p>
                Trải nghiệm hương vị món ăn Việt Nam truyền thống, hải sản tươi ngon và các món lẩu đậm đà được chế biến bởi đầu bếp giàu kinh nghiệm. Giao hàng tận nơi, thanh toán COD an toàn.
            </p>
            <div class="d-flex align-center gap-1">
                <a href="<?= Response::baseUrl('menu') ?>" class="btn btn-primary btn-lg" id="btn-hero-order">
                    🍽️ Xem thực đơn & Đặt món
                </a>
                <button type="button" class="btn btn-outline btn-lg" onclick="Chatbot.toggle()" id="btn-hero-chatbot">
                    💬 Hỏi Trợ lý AI
                </button>
            </div>

            <div class="hero-badges">
                <div class="hero-badge-item">
                    <span class="hero-badge-icon">✓</span>
                    <span>Nguyên liệu tươi mới mỗi ngày</span>
                </div>
                <div class="hero-badge-item">
                    <span class="hero-badge-icon">✓</span>
                    <span>Giao hàng COD tận nơi</span>
                </div>
                <div class="hero-badge-item">
                    <span class="hero-badge-icon">✓</span>
                    <span>Tư vấn món theo khẩu vị với AI</span>
                </div>
            </div>
        </div>

        <div>
            <div class="hero-banner-card">
                <span class="badge" style="background:#ffffff; color:#ea580c; margin-bottom:1rem;">ĐẶC SẢN NHÀ HÀNG</span>
                <h3 style="font-size:1.75rem; color:#ffffff; margin-bottom:0.75rem;">Lẩu Thái Hải Sản & Bò Lúc Lắc</h3>
                <p style="font-size:0.95rem; color:rgba(255,255,255,0.9); margin-bottom:1.5rem; line-height:1.5;">
                    Thưởng thức những món ăn được đặt nhiều nhất với nước dùng hầm xương đậm đà và thăn bò mềm mọng.
                </p>
                <div style="display:flex; align-items:center; justify-content:space-between; border-top:1px solid rgba(255,255,255,0.2); padding-top:1rem;">
                    <div>
                        <div style="font-size:0.75rem; opacity:0.8;">Phí giao hàng chỉ từ</div>
                        <div style="font-size:1.25rem; font-weight:800;"><?= number_format((float)$settings['delivery_fee'], 0, ',', '.') ?>đ</div>
                    </div>
                    <a href="<?= Response::baseUrl('menu') ?>" class="btn btn-sm" style="background:#ffffff; color:#ea580c; font-weight:700;">
                        Đặt ngay &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- DANH MỤC MÓN ĂN -->
<section style="padding: 3.5rem 0 2rem;">
    <div class="container">
        <div class="d-flex justify-between align-center mb-3">
            <div>
                <h2>Danh Mục Món Ăn</h2>
                <p class="text-muted" style="margin:0;">Lựa chọn món ăn theo từng nhóm ẩm thực phong phú</p>
            </div>
            <a href="<?= Response::baseUrl('menu') ?>" class="font-bold text-primary">Xem tất cả &rarr;</a>
        </div>

        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem;">
            <?php 
            $catIcons = [
                1 => '🥗', // Khai vị
                2 => '🍚', // Cơm & Mì
                3 => '🥩', // Bò & Gà
                4 => '🍲', // Lẩu & Hải sản
                5 => '🍹', // Đồ uống
                6 => '🍨', // Tráng miệng
            ];
            ?>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= Response::baseUrl('menu?category=' . $cat['id']) ?>" class="card text-center" style="padding:1.25rem 1rem; text-decoration:none !important; border-radius:var(--radius-lg);">
                    <div style="font-size:2.5rem; margin-bottom:0.5rem;"><?= $catIcons[$cat['id']] ?? '🍽️' ?></div>
                    <h4 style="font-size:0.95rem; margin-bottom:0.25rem; color:var(--text-color);"><?= htmlspecialchars($cat['name']) ?></h4>
                    <span style="font-size:0.75rem; color:var(--text-muted);"><?= (int)$cat['product_count'] ?> món ăn</span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- MÓN ĂN BÁN CHẠY NHẤT -->
<section style="padding: 2.5rem 0 3.5rem; background-color: var(--surface-hover);">
    <div class="container">
        <div class="d-flex justify-between align-center mb-3">
            <div>
                <h2>Món Ăn Bán Chạy Nhất</h2>
                <p class="text-muted" style="margin:0;">Các món ăn được đông đảo thực khách yêu thích và đặt nhiều nhất</p>
            </div>
            <a href="<?= Response::baseUrl('menu') ?>" class="font-bold text-primary">Xem toàn bộ menu &rarr;</a>
        </div>

        <div class="food-grid">
            <?php foreach ($bestSellers as $prod): ?>
                <div class="food-card">
                    <div class="food-card-thumb">
                        <div class="food-card-badge">Bán Chạy</div>
                        <img src="<?= Product::getImageUrl($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <span style="display:none; font-size:4rem;">🍲</span>
                    </div>
                    <div class="food-card-body">
                        <div class="food-card-category"><?= htmlspecialchars($prod['category_name']) ?></div>
                        <h3 class="food-card-title">
                            <a href="<?= Response::baseUrl('products/' . $prod['id']) ?>">
                                <?= htmlspecialchars($prod['name']) ?>
                            </a>
                        </h3>
                        <p class="food-card-desc"><?= htmlspecialchars($prod['description']) ?></p>
                        <div class="food-card-footer">
                            <div class="food-card-price">
                                <?= number_format((float)$prod['price'], 0, ',', '.') ?>đ
                            </div>
                            <?php if ($prod['availability_status'] === 'in_stock'): ?>
                                <button type="button" class="btn btn-sm btn-primary btn-add-to-cart" data-id="<?= $prod['id'] ?>" data-name="<?= htmlspecialchars($prod['name']) ?>">
                                    + Thêm
                                </button>
                            <?php else: ?>
                                <span class="badge badge-rejected" style="font-size:0.7rem;">Tạm hết</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- MÓN ĂN NỔI BẬT -->
<section style="padding: 3.5rem 0;">
    <div class="container">
        <div class="d-flex justify-between align-center mb-3">
            <div>
                <h2>Gợi Ý Món Ăn Nổi Bật</h2>
                <p class="text-muted" style="margin:0;">Thực đơn phong phú từ khai vị, món chính đến tráng miệng</p>
            </div>
            <a href="<?= Response::baseUrl('menu') ?>" class="font-bold text-primary">Khám phá menu &rarr;</a>
        </div>

        <div class="food-grid">
            <?php foreach ($featuredProducts as $prod): ?>
                <div class="food-card">
                    <div class="food-card-thumb">
                        <img src="<?= Product::getImageUrl($prod['image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                        <span style="display:none; font-size:4rem;">🍲</span>
                    </div>
                    <div class="food-card-body">
                        <div class="food-card-category"><?= htmlspecialchars($prod['category_name']) ?></div>
                        <h3 class="food-card-title">
                            <a href="<?= Response::baseUrl('products/' . $prod['id']) ?>">
                                <?= htmlspecialchars($prod['name']) ?>
                            </a>
                        </h3>
                        <p class="food-card-desc"><?= htmlspecialchars($prod['description']) ?></p>
                        <div class="food-card-footer">
                            <div class="food-card-price">
                                <?= number_format((float)$prod['price'], 0, ',', '.') ?>đ
                            </div>
                            <?php if ($prod['availability_status'] === 'in_stock'): ?>
                                <button type="button" class="btn btn-sm btn-primary btn-add-to-cart" data-id="<?= $prod['id'] ?>" data-name="<?= htmlspecialchars($prod['name']) ?>">
                                    + Thêm
                                </button>
                            <?php else: ?>
                                <span class="badge badge-rejected" style="font-size:0.7rem;">Tạm hết</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
