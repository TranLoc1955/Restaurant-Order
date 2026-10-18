<div class="d-flex justify-between align-center mb-4">
    <div>
        <h2 style="margin:0;">Quản Lý Tài Khoản Người Dùng</h2>
        <p class="text-muted" style="margin:0;">Xem danh sách thành viên, thêm, chỉnh sửa, phân quyền và xóa tài khoản</p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openAddUserModal()">
        + Thêm tài khoản mới
    </button>
</div>

<!-- Bộ lọc vai trò và tìm kiếm -->
<div class="card mb-4" style="padding:1.25rem;">
    <form action="<?= Response::baseUrl('admin/users') ?>" method="GET" style="display:flex; flex-wrap:wrap; gap:1rem; align-items:center;">
        <div style="flex:1; min-width:240px;">
            <input type="text" name="search" class="form-control" placeholder="Tìm theo họ tên, email, số điện thoại..." value="<?= htmlspecialchars($search ?? '') ?>">
        </div>
        <div style="min-width:180px;">
            <select name="role" class="form-control" onchange="this.form.submit()">
                <option value="">Tất cả vai trò</option>
                <option value="customer" <?= (($currentRole ?? '') === 'customer') ? 'selected' : '' ?>>Khách hàng (customer)</option>
                <option value="staff" <?= (($currentRole ?? '') === 'staff') ? 'selected' : '' ?>>Nhân viên (staff)</option>
                <option value="admin" <?= (($currentRole ?? '') === 'admin') ? 'selected' : '' ?>>Quản trị viên (admin)</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
        <?php if (!empty($search) || !empty($currentRole)): ?>
            <a href="<?= Response::baseUrl('admin/users') ?>" class="btn btn-outline">Xóa lọc</a>
        <?php endif; ?>
    </form>
</div>

<!-- Bảng người dùng -->
<div class="cart-table-wrapper">
    <table class="cart-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Họ và tên</th>
                <th>Email</th>
                <th>Số điện thoại</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th style="text-align:right;">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $currentAdminId = Session::userId();
            foreach ($users as $u): 
                $isSelf = ((int)$u['id'] === (int)$currentAdminId);
            ?>
                <tr>
                    <td class="text-muted">#<?= $u['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($u['full_name']) ?></strong>
                        <?php if ($isSelf): ?>
                            <span class="badge badge-info" style="font-size:0.65rem; margin-left:4px;">Bạn</span>
                        <?php endif; ?>
                        <div style="font-size:0.75rem; color:var(--text-muted);">Tạo lúc: <?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></div>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><?= htmlspecialchars($u['phone'] ?: 'Chưa cập nhật') ?></td>
                    <td>
                        <span class="badge badge-<?= $u['role'] ?>">
                            <?= strtoupper($u['role']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($u['status'] === 'active'): ?>
                            <span class="badge badge-delivered">Hoạt động</span>
                        <?php else: ?>
                            <span class="badge badge-rejected">Đang bị khóa</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:right;">
                        <div class="d-flex justify-end gap-1" style="justify-content:flex-end; align-items:center;">
                            <!-- Nút Sửa người dùng -->
                            <button type="button" class="btn btn-sm btn-outline" onclick='openEditUserModal(<?= json_encode($u) ?>)' title="Chỉnh sửa thông tin">
                                ✏️ Sửa
                            </button>

                            <?php if (!$isSelf): ?>
                                <!-- Nút Khóa / Mở khóa tài khoản -->
                                <form action="<?= Response::baseUrl('admin/users/' . $u['id'] . '/toggle-lock') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn <?= ($u['status'] === 'active') ? 'KHÓA' : 'MỞ KHÓA' ?> tài khoản này?')">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <?php if ($u['status'] === 'active'): ?>
                                        <button type="submit" class="btn btn-sm btn-outline btn-warning" title="Khóa tài khoản">
                                            🔒 Khóa
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-outline btn-success" title="Mở khóa tài khoản">
                                            🔓 Mở
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <!-- Nút Xóa người dùng -->
                                <form action="<?= Response::baseUrl('admin/users/' . $u['id'] . '/delete') ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng \'<?= htmlspecialchars(addslashes($u['full_name'])) ?>\'? Nếu tài khoản này đã có lịch sử đơn hàng, hệ thống sẽ tự động chuyển sang Khóa để bảo toàn dữ liệu.')">
                                    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline btn-danger" title="Xóa tài khoản này">
                                        🗑️ Xóa
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal Thêm / Chỉnh Sửa Tài Khoản Người Dùng -->
<div id="user-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; padding:1rem;">
    <div class="card" style="max-width:520px; width:100%; padding:2rem; max-height:92vh; overflow-y:auto; box-shadow:var(--shadow-xl);">
        <h3 id="modal-user-title" style="margin-bottom:1.5rem; border-bottom:1px solid var(--border-color); padding-bottom:0.75rem;">
            Thêm Tài Khoản Mới
        </h3>

        <form id="user-modal-form" action="<?= Response::baseUrl('admin/users') ?>" method="POST">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <input type="hidden" name="user_id" id="user-id" value="">

            <div class="form-group">
                <label class="form-label">Họ và tên người dùng <span class="required">*</span></label>
                <input type="text" name="full_name" id="user-name" class="form-control" required placeholder="Ví dụ: Nguyễn Văn A">
            </div>

            <div class="form-group">
                <label class="form-label">Email đăng nhập <span class="required">*</span></label>
                <input type="email" name="email" id="user-email" class="form-control" required placeholder="nguyenvana@gmail.com">
            </div>

            <div class="form-group">
                <label class="form-label">Số điện thoại <span class="required">*</span></label>
                <input type="tel" name="phone" id="user-phone" class="form-control" required placeholder="0778591167">
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Vai trò (Role) <span class="required">*</span></label>
                    <select name="role" id="user-role" class="form-control" required>
                        <option value="customer">Khách hàng (customer)</option>
                        <option value="staff">Nhân viên (staff)</option>
                        <option value="admin">Quản trị viên (admin)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Trạng thái tài khoản <span class="required">*</span></label>
                    <select name="status" id="user-status" class="form-control" required>
                        <option value="active">Hoạt động (active)</option>
                        <option value="locked">Bị khóa (locked)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" id="user-password-label">Mật khẩu <span class="required">*</span></label>
                <input type="password" name="password" id="user-password" class="form-control" placeholder="Tối thiểu 6 ký tự">
                <div class="form-hint" id="user-password-hint">Mật khẩu phải có ít nhất 6 ký tự.</div>
            </div>

            <div class="d-flex justify-between gap-1 mt-4">
                <button type="button" class="btn btn-outline" onclick="closeUserModal()">Hủy</button>
                <button type="submit" class="btn btn-primary" id="btn-save-user">💾 Lưu tài khoản</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddUserModal() {
        document.getElementById('modal-user-title').textContent = 'Thêm Tài Khoản Mới';
        document.getElementById('user-modal-form').action = window.APP_BASE_URL + '/admin/users';
        document.getElementById('user-id').value = '';
        document.getElementById('user-name').value = '';
        document.getElementById('user-email').value = '';
        document.getElementById('user-phone').value = '';
        document.getElementById('user-role').value = 'staff';
        document.getElementById('user-status').value = 'active';
        
        // Mật khẩu bắt buộc khi thêm mới
        const pwdInput = document.getElementById('user-password');
        pwdInput.value = '';
        pwdInput.required = true;
        document.getElementById('user-password-label').innerHTML = 'Mật khẩu khởi tạo <span class="required">*</span>';
        document.getElementById('user-password-hint').textContent = 'Tối thiểu 6 ký tự để nhân viên đăng nhập.';
        
        document.getElementById('user-modal').style.display = 'flex';
    }

    function openEditUserModal(u) {
        document.getElementById('modal-user-title').textContent = 'Chỉnh Sửa Người Dùng: ' + u.full_name;
        document.getElementById('user-modal-form').action = window.APP_BASE_URL + '/admin/users/' + u.id + '/update';
        document.getElementById('user-id').value = u.id;
        document.getElementById('user-name').value = u.full_name;
        document.getElementById('user-email').value = u.email;
        document.getElementById('user-phone').value = u.phone || '';
        document.getElementById('user-role').value = u.role;
        document.getElementById('user-status').value = u.status;
        
        // Mật khẩu là tùy chọn khi chỉnh sửa
        const pwdInput = document.getElementById('user-password');
        pwdInput.value = '';
        pwdInput.required = false;
        document.getElementById('user-password-label').innerHTML = 'Đổi mật khẩu mới (Tùy chọn)';
        document.getElementById('user-password-hint').textContent = 'Để trống nếu không muốn thay đổi mật khẩu hiện tại của người dùng.';

        document.getElementById('user-modal').style.display = 'flex';
    }

    function closeUserModal() {
        document.getElementById('user-modal').style.display = 'none';
    }

    // Đóng modal khi click ra bên ngoài backdrop hoặc bấm phím Escape
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('user-modal');
        if (e.target === modal) {
            closeUserModal();
        }
    });

    window.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeUserModal();
        }
    });
</script>
