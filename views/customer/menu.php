<div class="container" style="padding: 2.5rem 1.5rem 4rem;">
    <!-- THANH TÁC VỤ QUẢN TRỊ DÀNH CHO ADMIN VÀ STAFF -->
    <?php if (Session::isLoggedIn() && Session::userRole() === 'admin'): ?>
        <div style="background: linear-gradient(135deg, #1e293b, #0f172a); color: #ffffff; padding: 1rem 1.5rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-left: 4px solid var(--primary-color);">
            <div>
                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Quyền Quản Trị Viên (Admin)</div>
                <div style="font-weight: 700; font-size: 1.1rem; color: #ffffff;">⚙️ Công Cụ Quản Lý Thực Đơn Trực Tiếp</div>
            </div>
            <div class="d-flex align-center gap-1" style="flex-wrap: wrap;">
                <a href="<?= Response::baseUrl('admin/products/create') ?>" class="btn btn-sm btn-primary">
                    + Thêm Món Ăn Mới
                </a>
                <a href="<?= Response::baseUrl('admin/products') ?>" class="btn btn-sm btn-outline" style="color:#ffffff; border-color: rgba(255,255,255,0.3);">
                    📋 Quản Trị Món Ăn
                </a>
                <a href="<?= Response::baseUrl('admin/categories') ?>" class="btn btn-sm btn-outline" style="color:#ffffff; border-color: rgba(255,255,255,0.3);">
                    📁 Quản Lý Danh Mục
                </a>
            </div>
        </div>
    <?php elseif (Session::isLoggedIn() && Session::userRole() === 'staff'): ?>
        <div style="background: linear-gradient(135deg, #1e3a8a, #172554); color: #ffffff; padding: 0.85rem 1.25rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-left: 4px solid #3b82f6;">
            <div>
                <div style="font-size: 0.75rem; color: #93c5fd; text-transform: uppercase; font-weight: 700;">Quyền Nhân Viên (Staff)</div>
                <div style="font-weight: 600; font-size: 0.95rem; color: #ffffff;">🧑‍🍳 Đang xem thực đơn nhà hàng. Bạn có thể kiểm tra danh mục và giá món phục vụ khách.</div>
            </div>
            <a href="<?= Response::baseUrl('staff/orders') ?>" class="btn btn-sm btn-primary" style="background:#2563eb; border-color:#2563eb;">
                📦 Quay lại Xử Lý Đơn Hàng &rarr;
            </a>
        </div>
    <?php endif; ?>

    <!-- Tiêu đề trang & Thanh tìm kiếm -->
    <div style="margin-bottom: 2rem;">
        <h1>Thực Đơn Món Ăn</h1>
        <p class="text-muted">Khám phá các món ăn truyền thống, đồ uống giải khát và món lẩu hấp dẫn</p>

        <!-- Form tìm kiếm & Sắp xếp -->
        <form action="<?= Response::baseUrl('menu') ?>" method="GET" style="display:flex; flex-wrap:wrap; gap:1rem; margin-top:1.5rem; align-items:center;">
            <?php if (!empty($currentCategory)): ?>
                <input type="hidden" name="category" value="<?= (int)$currentCategory ?>">
            <?php endif; ?>

            <!-- Ô tìm kiếm -->
            <div style="flex:1; min-width:240px; position:relative;">
                <input 
                    type="text" 
                    name="search" 
                    id="menu-search-input" 
                    class="form-control" 
                    placeholder="Tìm kiếm theo tên món ăn hoặc nguyên liệu..." 
                    value="<?= htmlspecialchars($search ?? '') ?>"
                >
            </div>

            <!-- Bộ sắp xếp giá -->
            <div style="min-width: 180px;">
                <select name="sort" class="form-control" onchange="this.form.submit()">
                    <option value="default" <?= ($sort === 'default') ? 'selected' : '' ?>>Sắp xếp: Mặc định</option>
                    <option value="price_asc" <?= ($sort === 'price_asc') ? 'selected' : '' ?>>Giá: Thấp đến Cao</option>
                    <option value="price_desc" <?= ($sort === 'price_desc') ? 'selected' : '' ?>>Giá: Cao đến Thấp</option>
                    <option value="name_asc" <?= ($sort === 'name_asc') ? 'selected' : '' ?>>Tên: A đến Z</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary" id="btn-search-menu">
                🔍 Tìm kiếm
            </button>

            <?php if (!empty($search) || !empty($currentCategory)): ?>
                <a href="<?= Response::baseUrl('menu') ?>" class="btn btn-outline" title="Xóa bộ lọc">
                    ✕ Xóa lọc
                </a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Thanh danh mục dạng Pills -->
    <div class="category-bar">
        <a href="<?= Response::baseUrl('menu' . (!empty($search) ? '?search=' . urlencode($search) : '')) ?>" 
           class="category-pill <?= empty($currentCategory) ? 'active' : '' ?>">
            🍽️ Tất cả món
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="<?= Response::baseUrl('menu?category=' . $cat['id'] . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>" 
               class="category-pill <?= ((int)$currentCategory === (int)$cat['id']) ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?> (<?= $cat['product_count'] ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Thông báo kết quả lọc -->
    <div class="d-flex justify-between align-center mb-3 mt-2">
        <span class="text-muted" style="font-size:0.875rem;">
            Hiển thị <strong><?= count($products) ?></strong> / <strong><?= $totalProducts ?></strong> món ăn
            <?= !empty($selectedCategory) ? ' trong danh mục <strong>' . htmlspecialchars($selectedCategory['name']) . '</strong>' : '' ?>
            <?= !empty($search) ? ' khớp với từ khóa "<em>' . htmlspecialchars($search) . '</em>"' : '' ?>
        </span>
    </div>

    <!-- Lưới món ăn -->
    <?php if (empty($products)): ?>
        <div class="card text-center" style="padding: 4rem 2rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
            <h3>Không tìm thấy món ăn phù hợp</h3>
            <p class="text-muted">Bạn hãy thử thay đổi từ khóa tìm kiếm hoặc chọn danh mục khác nhé.</p>
            <div class="mt-2">
                <a href="<?= Response::baseUrl('menu') ?>" class="btn btn-primary">Xem toàn bộ thực đơn</a>
            </div>
        </div>
    <?php else: ?>
        <div class="food-grid">
            <?php foreach ($products as $prod): ?>
                <div class="food-card" id="food-card-<?= $prod['id'] ?>">
                    <div class="food-card-thumb">
                        <?php if ($prod['availability_status'] === 'out_of_stock'): ?>
                            <div class="food-card-badge" style="color:var(--danger-color);">Tạm hết hàng</div>
                        <?php endif; ?>
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
                            <div class="d-flex align-center gap-1">
                                <?php if (Session::isLoggedIn() && Session::userRole() === 'admin'): ?>
                                    <a href="<?= Response::baseUrl('admin/products/' . $prod['id'] . '/edit') ?>" 
                                       class="btn btn-sm btn-outline" 
                                       style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" 
                                       title="Chỉnh sửa món này">
                                        ✏️ Sửa món
                                    </a>
                                <?php endif; ?>

                                <?php if ($prod['availability_status'] === 'in_stock'): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-primary btn-add-to-cart" 
                                            data-id="<?= $prod['id'] ?>" 
                                            data-name="<?= htmlspecialchars($prod['name']) ?>"
                                            id="btn-add-cart-<?= $prod['id'] ?>"
                                    >
                                        + Thêm giỏ
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-outline" disabled title="Món tạm hết">
                                        Hết hàng
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Phân trang -->
        <?php if ($totalPages > 1): ?>
            <div class="d-flex justify-center gap-1 mt-4">
                <?php if ($currentPage > 1): ?>
                    <a href="<?= Response::baseUrl('menu?' . http_build_query(array_merge($_GET, ['page' => $currentPage - 1]))) ?>" class="btn btn-sm btn-outline">
                        &larr; Trang trước
                    </a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="<?= Response::baseUrl('menu?' . http_build_query(array_merge($_GET, ['page' => $p]))) ?>" 
                       class="btn btn-sm <?= ($p === $currentPage) ? 'btn-primary' : 'btn-outline' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="<?= Response::baseUrl('menu?' . http_build_query(array_merge($_GET, ['page' => $currentPage + 1]))) ?>" class="btn btn-sm btn-outline">
                        Trang sau &rarr;
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
