<div class="d-flex justify-between align-center mb-4">
    <div>
        <h2 style="margin:0;">Quản Lý Danh Mục Món Ăn</h2>
        <p class="text-muted" style="margin:0;">Phân loại thực đơn nhà hàng (Khai vị, Món chính, Đồ uống, Tráng miệng...)</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openAddCategoryModal()">
        + Thêm danh mục mới
    </button>
</div>

<div class="cart-table-wrapper">
    <table class="cart-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên danh mục</th>
                <th>Mô tả</th>
                <th>Số lượng món</th>
                <th>Trạng thái</th>
                <th style="text-align:right;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td class="text-muted">#<?= $cat['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($cat['name']) ?></strong>
                    </td>
                    <td style="font-size:0.875rem; color:var(--text-muted); max-width:300px;">
                        <?= htmlspecialchars($cat['description'] ?: 'Không có mô tả') ?>
                    </td>
                    <td>
                        <span class="font-bold"><?= (int)$cat['product_count'] ?></span> món
                    </td>
                    <td>
                        <?php if ($cat['status'] === 'active'): ?>
                            <span class="badge badge-delivered">Hoạt động</span>
                        <?php else: ?>
                            <span class="badge badge-cancelled">Đang ẩn</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <div class="d-flex justify-end gap-1" style="justify-content:flex-end;">
                            <!-- Đổi trạng thái ẩn/hiện -->
                            <form action="<?= Response::baseUrl('admin/categories/' . $cat['id'] . '/toggle') ?>" method="POST" style="display:inline;">
                                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <button type="submit" class="btn btn-sm btn-outline">
                                    <?= ($cat['status'] === 'active') ? 'Ẩn' : 'Hiện' ?>
                                </button>
                            </form>

                            <!-- Sửa danh mục -->
                            <button type="button" class="btn btn-sm btn-outline" onclick='openEditCategoryModal(<?= json_encode($cat) ?>)'>
                                Sửa
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Thêm / Sửa Danh Mục -->
<div id="category-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width:480px; width:100%; padding:2rem;">
        <h3 id="modal-cat-title" style="margin-bottom:1.5rem;">Thêm Danh Mục Mới</h3>

        <form id="cat-modal-form" action="<?= Response::baseUrl('admin/categories') ?>" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

            <div class="form-group">
                <label class="form-label">Tên danh mục <span class="required">*</span></label>
                <input type="text" name="name" id="cat-name" class="form-control" required placeholder="Ví dụ: Món ăn vặt">
            </div>

            <div class="form-group">
                <label class="form-label">Mô tả danh mục</label>
                <textarea name="description" id="cat-desc" rows="3" class="form-control" placeholder="Mô tả nhóm món..."></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Trạng thái</label>
                <select name="status" id="cat-status" class="form-control">
                    <option value="active">Hoạt động (Hiển thị ngoài Menu)</option>
                    <option value="hidden">Ẩn danh mục</option>
                </select>
            </div>

            <div class="d-flex justify-between gap-1 mt-4">
                <button type="button" class="btn btn-outline" onclick="closeCategoryModal()">Hủy</button>
                <button type="submit" class="btn btn-primary">Lưu danh mục</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddCategoryModal() {
        document.getElementById('modal-cat-title').textContent = 'Thêm Danh Mục Mới';
        document.getElementById('cat-modal-form').action = window.APP_BASE_URL + '/admin/categories';
        document.getElementById('cat-name').value = '';
        document.getElementById('cat-desc').value = '';
        document.getElementById('cat-status').value = 'active';
        document.getElementById('category-modal').style.display = 'flex';
    }

    function openEditCategoryModal(cat) {
        document.getElementById('modal-cat-title').textContent = 'Sửa Danh Mục: ' + cat.name;
        document.getElementById('cat-modal-form').action = window.APP_BASE_URL + '/admin/categories/' + cat.id + '/update';
        document.getElementById('cat-name').value = cat.name;
        document.getElementById('cat-desc').value = cat.description || '';
        document.getElementById('cat-status').value = cat.status;
        document.getElementById('category-modal').style.display = 'flex';
    }

    function closeCategoryModal() {
        document.getElementById('category-modal').style.display = 'none';
    }
</script>
