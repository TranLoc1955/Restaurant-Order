<div class="card">
    <div class="card-header">
        <h3 style="margin: 0;">Khu vực nghiệp vụ nhân viên</h3>
        <span class="badge badge-staff">Nhân Viên</span>
    </div>
    <div class="card-body">
        <p>Xin chào, <strong><?= htmlspecialchars($user['full_name'] ?? 'Nhân viên') ?></strong>!</p>
        <div style="padding: 1.5rem; background-color: var(--surface-hover); border-radius: var(--radius-md); border-left: 4px solid var(--info-color);">
            <h4 style="margin-bottom: 0.5rem;">Xác minh phân quyền Staff thành công:</h4>
            <p>Nhân viên có quyền truy cập vào danh sách đơn hàng để duyệt đơn, chuyển trạng thái chế biến và giao hàng.</p>
            <p><strong>Lưu ý bảo mật:</strong> Nhân viên không có quyền truy cập các URL quản trị Admin như quản lý người dùng, doanh thu hay xóa Admin.</p>
        </div>
    </div>
</div>
