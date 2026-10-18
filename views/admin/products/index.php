<div class="d-flex justify-between align-center mb-4">
    <div>
        <h2 style="margin:0;">Quản Lý Thực Đơn Món Ăn</h2>
        <p class="text-muted" style="margin:0;">Thêm mới, sửa giá, ẩn hiện và quản lý tình trạng còn hàng</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openAddProductModal()">
        + Thêm món ăn mới
    </button>
</div>

<!-- Bộ lọc tìm kiếm món -->
<div class="card mb-4" style="padding:1.25rem;">
    <form action="<?= Response::baseUrl('admin/products') ?>" method="GET" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:center;">
        <div style="flex:1; min-width:220px;">
            <input type="text" name="search" class="form-control" placeholder="Tìm theo tên món ăn..." value="<?= htmlspecialchars($search ?? '') ?>">
        </div>
        <div style="min-width:180px;">
            <select name="category" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả danh mục</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ((int)($currentCategory ?? 0) === (int)$c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="min-width:160px;">
            <select name="availability" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả tình trạng</option>
                <option value="in_stock" <?= (($currentStatus ?? '') === 'in_stock') ? 'selected' : '' ?>>Còn hàng (in_stock)</option>
                <option value="out_of_stock" <?= (($currentStatus ?? '') === 'out_of_stock') ? 'selected' : '' ?>>Tạm hết (out_of_stock)</option>
                <option value="hidden" <?= (($currentStatus ?? '') === 'hidden') ? 'selected' : '' ?>>Đang ẩn (hidden)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Lọc</button>
        <?php if (!empty($search) || !empty($currentCategory) || !empty($currentStatus)): ?>
            <a href="<?= Response::baseUrl('admin/products') ?>" class="btn btn-outline">Xóa lọc</a>
        <?php endif; ?>
    </form>
</div>

<!-- Bảng dữ liệu món ăn -->
<div class="cart-table-wrapper">
    <table class="cart-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Hình ảnh</th>
                <th>Tên món ăn</th>
                <th>Danh mục</th>
                <th>Giá niêm yết</th>
                <th>Trạng thái</th>
                <th style="text-align:right;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td class="text-muted">#<?= $p['id'] ?></td>
                    <td>
                        <div style="width:48px; height:48px; background:#fff7ed; border-radius:8px; overflow:hidden; display:flex; align-items:center; justify-content:center; border:1px solid var(--border-color); flex-shrink:0;">
                            <img src="<?= Product::getImageUrl($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" style="width:100%; height:100%; object-fit:cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                            <span style="display:none; font-size:1.5rem;">🍲</span>
                        </div>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($p['name']) ?></strong>
                        <div style="font-size:0.75rem; color:var(--text-muted); max-width:240px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?= htmlspecialchars($p['description']) ?>
                        </div>
                    </td>
                    <td><?= htmlspecialchars($p['category_name'] ?? 'Không rõ') ?></td>
                    <td class="font-bold text-primary">
                        <?= number_format((float)$p['price'], 0, ',', '.') ?>đ
                    </td>
                    <td>
                        <?php if ($p['availability_status'] === 'in_stock'): ?>
                            <span class="badge badge-delivered">Còn hàng</span>
                        <?php elseif ($p['availability_status'] === 'out_of_stock'): ?>
                            <span class="badge badge-rejected">Tạm hết</span>
                        <?php else: ?>
                            <span class="badge badge-cancelled">Đang ẩn</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <div class="d-flex justify-end gap-1" style="justify-content:flex-end;">
                            <!-- Đổi nhanh trạng thái còn hàng -->
                            <form action="<?= Response::baseUrl('admin/products/' . $p['id'] . '/availability') ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <?php if ($p['availability_status'] === 'in_stock'): ?>
                                    <input type="hidden" name="status" value="out_of_stock">
                                    <button type="submit" class="btn btn-sm btn-outline" title="Báo hết hàng">Báo hết</button>
                                <?php else: ?>
                                    <input type="hidden" name="status" value="in_stock">
                                    <button type="submit" class="btn btn-sm btn-outline" style="color:var(--success-color); border-color:var(--success-color);" title="Mở bán lại">Mở bán</button>
                                <?php endif; ?>
                            </form>

                            <!-- Sửa món -->
                            <button type="button" class="btn btn-sm btn-outline" onclick='openEditProductModal(<?= json_encode($p) ?>)'>
                                Sửa
                            </button>

                            <!-- Xóa / Ẩn món -->
                            <form action="<?= Response::baseUrl('admin/products/' . $p['id'] . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa/ẩn món ăn này?')">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-outline btn-danger" title="Xóa món hoặc ẩn">
                                    Xóa
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Thêm / Sửa món ăn -->
<div id="product-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width:580px; width:100%; padding:2rem; max-height:92vh; overflow-y:auto; box-shadow:var(--shadow-xl);">
        <h3 id="modal-product-title" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">
            Thêm Món Ăn Mới
        </h3>

        <form id="product-modal-form" action="<?= Response::baseUrl('admin/products') ?>" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="current_image" id="prod-current-image" value="">

            <div class="form-group">
                <label class="form-label">Tên món ăn <span class="required">*</span></label>
                <input type="text" name="name" id="prod-name" class="form-control" required placeholder="Ví dụ: Bò lúc lắc khoai tây">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Danh mục món ăn <span class="required">*</span></label>
                    <select name="category_id" id="prod-category" class="form-control" required>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Giá niêm yết (VNĐ) <span class="required">*</span></label>
                    <input type="number" name="price" id="prod-price" class="form-control" required min="1000" step="1000" placeholder="Ví dụ: 75000">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Trạng thái bán</label>
                <select name="availability_status" id="prod-status" class="form-control">
                    <option value="in_stock">Còn hàng (in_stock)</option>
                    <option value="out_of_stock">Tạm hết hàng (out_of_stock)</option>
                    <option value="hidden">Ẩn món (hidden)</option>
                </select>
            </div>

            <!-- PHẦN THÊM VÀ THAY THẾ ẢNH MÓN ĂN -->
            <div class="form-group" style="background:#f8fafc; border:1px solid var(--border-color); border-radius:var(--radius-md); padding:1.25rem;">
                <label class="form-label" style="margin-bottom:0.75rem; display:flex; align-items:center; gap:0.5rem;">
                    <span>🖼️</span> <strong>Hình ảnh món ăn</strong>
                </label>

                <!-- Khung xem trước ảnh hiện tại hoặc ảnh mới chọn -->
                <div style="display:flex; align-items:center; gap:1rem; margin-bottom:1rem; padding:0.75rem; background:#ffffff; border-radius:var(--radius-sm); border:1px dashed #cbd5e1;">
                    <div style="width:76px; height:76px; border-radius:8px; background:#fff7ed; overflow:hidden; display:flex; align-items:center; justify-content:center; border:1px solid #e2e8f0; flex-shrink:0;">
                        <img id="image-preview" src="" alt="Xem trước ảnh" style="width:100%; height:100%; object-fit:cover; display:none;">
                        <span id="image-preview-placeholder" style="font-size:2.25rem;">🍲</span>
                    </div>
                    <div style="flex:1;">
                        <div id="image-preview-label" style="font-weight:600; font-size:0.875rem; color:var(--text-color);">Chưa chọn ảnh</div>
                        <div style="font-size:0.75rem; color:var(--text-muted); margin-top:3px;">
                            Định dạng hỗ trợ: JPG, PNG, WEBP, GIF (Tối đa 5MB)
                        </div>
                    </div>
                </div>

                <!-- 1. Chọn tệp ảnh tải lên từ máy tính -->
                <div style="margin-bottom:0.75rem;">
                    <label for="prod-image-file" style="font-size:0.8125rem; font-weight:600; color:var(--text-color); display:block; margin-bottom:4px;">
                        📁 Tải ảnh mới từ máy tính:
                    </label>
                    <input 
                        type="file" 
                        name="image" 
                        id="prod-image-file" 
                        class="form-control" 
                        accept="image/png, image/jpeg, image/webp, image/gif" 
                        onchange="previewSelectedImage(this)"
                    >
                </div>

                <!-- 2. Hoặc dán link ảnh online -->
                <div>
                    <label for="prod-image-url" style="font-size:0.8125rem; font-weight:600; color:var(--text-color); display:block; margin-bottom:4px;">
                        🌐 Hoặc dán URL ảnh trực tuyến (nếu không tải tệp):
                    </label>
                    <input 
                        type="url" 
                        name="image_url" 
                        id="prod-image-url" 
                        class="form-control" 
                        placeholder="https://images.unsplash.com/... hoặc link ảnh" 
                        oninput="previewUrlImage(this.value)"
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả món ăn</label>
                <textarea name="description" id="prod-desc" rows="3" class="form-control" placeholder="Mô tả nguyên liệu, hương vị..."></textarea>
            </div>

            <div class="d-flex justify-between gap-1 mt-4">
                <button type="button" class="btn btn-outline" onclick="closeProductModal()">Hủy</button>
                <button type="submit" class="btn btn-primary" id="btn-save-product">💾 Lưu món ăn</button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewSelectedImage(input) {
        const file = input.files[0];
        const preview = document.getElementById('image-preview');
        const placeholder = document.getElementById('image-preview-placeholder');
        const label = document.getElementById('image-preview-label');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                placeholder.style.display = 'none';
                label.innerHTML = '<span style="color:var(--success-color);">✓ Tệp đã chọn:</span> ' + file.name;
            };
            reader.readAsDataURL(file);
            document.getElementById('prod-image-url').value = '';
        }
    }

    function previewUrlImage(url) {
        const preview = document.getElementById('image-preview');
        const placeholder = document.getElementById('image-preview-placeholder');
        const label = document.getElementById('image-preview-label');

        if (url && (url.startsWith('http://') || url.startsWith('https://'))) {
            preview.src = url;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            label.innerHTML = '<span style="color:var(--primary-color);">✓ Đang dùng link online</span>';
        }
    }

    function openAddProductModal() {
        document.getElementById('modal-product-title').textContent = 'Thêm Món Ăn Mới';
        document.getElementById('product-modal-form').action = window.APP_BASE_URL + '/admin/products';
        document.getElementById('prod-name').value = '';
        document.getElementById('prod-price').value = '';
        document.getElementById('prod-desc').value = '';
        document.getElementById('prod-status').value = 'in_stock';
        document.getElementById('prod-current-image').value = '';
        document.getElementById('prod-image-file').value = '';
        document.getElementById('prod-image-url').value = '';
        
        // Reset preview
        document.getElementById('image-preview').style.display = 'none';
        document.getElementById('image-preview-placeholder').style.display = 'block';
        document.getElementById('image-preview-label').textContent = 'Chưa chọn ảnh (sẽ dùng ảnh mặc định)';
        
        document.getElementById('product-modal').style.display = 'flex';
    }

    function openEditProductModal(p) {
        document.getElementById('modal-product-title').textContent = 'Chỉnh Sửa Món: ' + p.name;
        document.getElementById('product-modal-form').action = window.APP_BASE_URL + '/admin/products/' + p.id + '/update';
        document.getElementById('prod-name').value = p.name;
        document.getElementById('prod-category').value = p.category_id;
        document.getElementById('prod-price').value = parseInt(p.price);
        document.getElementById('prod-desc').value = p.description || '';
        document.getElementById('prod-status').value = p.availability_status;
        document.getElementById('prod-current-image').value = p.image || '';
        document.getElementById('prod-image-file').value = '';
        document.getElementById('prod-image-url').value = '';

        // Hiển thị ảnh hiện tại của món
        const preview = document.getElementById('image-preview');
        const placeholder = document.getElementById('image-preview-placeholder');
        const label = document.getElementById('image-preview-label');

        if (p.image && p.image !== 'default-food.jpg') {
            let imgSrc = p.image;
            if (!p.image.startsWith('http://') && !p.image.startsWith('https://')) {
                imgSrc = window.APP_BASE_URL + '/uploads/products/' + p.image;
            }
            preview.src = imgSrc;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            label.innerHTML = '<strong>Ảnh hiện tại:</strong> ' + p.image + '<div style="font-size:0.75rem; color:#64748b;">(Chọn ảnh mới bên dưới nếu muốn thay thế)</div>';
        } else {
            preview.style.display = 'none';
            placeholder.style.display = 'block';
            label.textContent = 'Món đang dùng ảnh mặc định (Chưa có ảnh riêng)';
        }

        document.getElementById('product-modal').style.display = 'flex';
    }

    function closeProductModal() {
        document.getElementById('product-modal').style.display = 'none';
    }
</script>
