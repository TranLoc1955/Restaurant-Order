<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Xin chào, <?= htmlspecialchars($user['full_name'] ?? 'Admin') ?>!</h3>
        <span class="badge badge-admin">Quản Trị Viên</span>
    </div>
    <div class="card-body">
        <p>Bạn đã đăng nhập thành công với quyền <strong>Quản trị viên (Admin)</strong>.</p>
        <div style="padding: 1.5rem; background-color: var(--surface-hover); border-radius: var(--radius-md); border-left: 4px solid var(--primary-color);">
            <h4 style="margin-bottom: 0.5rem;">Thông tin phiên làm việc:</h4>
            <ul style="padding-left: 1.5rem;">
                <li><strong>Mã định danh (ID):</strong> <?= htmlspecialchars($user['id'] ?? '') ?></li>
                <li><strong>Email:</strong> <?= htmlspecialchars($user['email'] ?? '') ?></li>
                <li><strong>Số điện thoại:</strong> <?= htmlspecialchars($user['phone'] ?? '') ?></li>
                <li><strong>Vai trò trong hệ thống:</strong> <?= htmlspecialchars($user['role'] ?? '') ?></li>
            </ul>
        </div>
        <p class="text-muted mt-3">
            Hệ thống phân quyền RBAC và quản lý phiên Session đã hoạt động chính xác. Ở Giai đoạn 6, màn hình này sẽ được gắn đầy đủ biểu đồ thống kê doanh thu hợp lệ, danh sách món bán chạy và quản lý đơn hàng.
        </p>
    </div>
</div>
