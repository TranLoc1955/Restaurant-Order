<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Quản trị - Nhà Hàng Trần Lộc') ?></title>
    
    <!-- Script khởi động cấu hình giao diện & sidebar chống chớp màn hình (Zero Flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('admin_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);

            const savedSidebar = localStorage.getItem('admin_sidebar_collapsed');
            if (savedSidebar === '1') {
                document.documentElement.classList.add('sidebar-collapsed-preload');
            }
        })();
        window.APP_BASE_URL = <?= json_encode(Response::baseUrl()) ?>;
    </script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Styles -->
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/components.css') ?>">
    
    <style>
        /* 1. KHUNG BỐ CỤC CHÍNH */
        .admin-layout {
            display: flex;
            min-height: 100vh;
            background-color: var(--background-color);
            transition: background-color var(--transition-base), color var(--transition-base);
        }

        /* 2. THANH BÊN (SIDEBAR) */
        .admin-sidebar {
            width: 260px;
            background-color: #1e293b;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1), background-color var(--transition-base);
            overflow-x: hidden;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 50;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Trạng thái thu gọn của Sidebar */
        .admin-layout.sidebar-collapsed .admin-sidebar,
        html.sidebar-collapsed-preload .admin-sidebar {
            width: 72px;
        }

        .admin-sidebar-header {
            padding: 1.25rem 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 68px;
            gap: 0.5rem;
        }

        .sidebar-brand-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-brand-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
        }

        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            transition: opacity 0.2s ease;
        }

        .admin-layout.sidebar-collapsed .sidebar-brand-text,
        html.sidebar-collapsed-preload .sidebar-brand-text {
            display: none;
            opacity: 0;
        }

        /* Nút thu gọn dạng mũi tên bên trong Sidebar */
        .sidebar-collapse-toggle-btn {
            background: transparent;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 0.4rem 0.55rem;
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all var(--transition-fast);
            flex-shrink: 0;
        }

        .sidebar-collapse-toggle-btn:hover {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.12);
        }

        .admin-layout.sidebar-collapsed .sidebar-collapse-toggle-btn {
            margin: 0 auto;
        }

        .admin-sidebar-nav {
            padding: 1.25rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
            overflow-y: auto;
        }

        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 0.85rem;
            color: #cbd5e1;
            border-radius: var(--radius-md);
            font-weight: 500;
            text-decoration: none !important;
            transition: all var(--transition-fast);
            white-space: nowrap;
        }

        .admin-nav-link:hover {
            background-color: rgba(255, 255, 255, 0.08);
            color: #ffffff;
        }

        .admin-nav-link.active {
            background-color: var(--primary-color);
            color: #ffffff;
            font-weight: 600;
        }

        .admin-nav-icon {
            font-size: 1.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            flex-shrink: 0;
        }

        .admin-layout.sidebar-collapsed .admin-nav-text,
        html.sidebar-collapsed-preload .admin-nav-text {
            display: none;
        }

        .admin-layout.sidebar-collapsed .admin-nav-link,
        html.sidebar-collapsed-preload .admin-nav-link {
            justify-content: center;
            padding: 0.75rem 0;
            title: attr(data-title);
        }

        .admin-sidebar-footer {
            padding: 1rem 0.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .admin-layout.sidebar-collapsed .sidebar-footer-text,
        html.sidebar-collapsed-preload .sidebar-footer-text {
            display: none;
        }

        /* 3. KHU VỰC NỘI DUNG CHÍNH (MAIN) */
        .admin-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--background-color);
            min-width: 0;
            transition: background-color var(--transition-base);
        }

        /* Header thanh điều hướng phía trên */
        .admin-header {
            background-color: var(--surface-color);
            border-bottom: 1px solid var(--border-color);
            padding: 0.85rem 1.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            position: sticky;
            top: 0;
            z-index: 40;
            transition: background-color var(--transition-base), border-color var(--transition-base);
        }

        .admin-header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-header-toggle-sidebar {
            background: transparent;
            border: 1px solid var(--border-color);
            color: var(--text-color);
            width: 38px;
            height: 38px;
            border-radius: var(--radius-md);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: all var(--transition-fast);
        }

        .btn-header-toggle-sidebar:hover {
            background-color: var(--surface-hover);
            border-color: var(--text-muted);
        }

        .admin-content {
            padding: 2rem;
            flex: 1;
        }

        /* 4. BỘ NÚT CHUYỂN ĐỔI SÁNG / TỐI BẰNG RADIO BUTTON */
        .theme-switch-container {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: var(--surface-hover);
            padding: 0.25rem 0.35rem;
            border-radius: var(--radius-full);
            border: 1px solid var(--border-color);
        }

        .theme-switch-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-muted);
            padding-left: 0.4rem;
            user-select: none;
        }

        .theme-radio-group {
            display: inline-flex;
            align-items: center;
            gap: 2px;
        }

        .theme-radio-item {
            position: relative;
            cursor: pointer;
            margin: 0;
            display: inline-flex;
        }

        .theme-radio-item input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
            margin: 0;
            pointer-events: none;
        }

        .theme-radio-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: var(--radius-full);
            color: var(--text-muted);
            transition: all var(--transition-fast);
            user-select: none;
        }

        .theme-radio-item input[type="radio"]:checked + .theme-radio-btn {
            background-color: var(--surface-color);
            color: var(--text-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }

        /* 5. GIAO DIỆN CHẾ ĐỘ TỐI (DARK MODE) */
        [data-theme="dark"] {
            --background-color: #0b1120;
            --surface-color: #1e293b;
            --surface-hover: #334155;
            --border-color: #334155;
            --text-color: #f8fafc;
            --text-muted: #94a3b8;
            --text-light: #64748b;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.5);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.5);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.6);
        }

        [data-theme="dark"] .admin-sidebar {
            background-color: #090d16;
            border-right-color: #1e293b;
        }

        [data-theme="dark"] .card {
            background-color: #1e293b;
            border-color: #334155;
            color: #f1f5f9;
        }

        [data-theme="dark"] .cart-table-wrapper {
            background-color: #1e293b;
            border-color: #334155;
        }

        [data-theme="dark"] .cart-table th {
            background-color: #0f172a;
            color: #94a3b8;
            border-bottom-color: #334155;
        }

        [data-theme="dark"] .cart-table td {
            border-bottom-color: #334155;
            color: #f1f5f9;
        }

        [data-theme="dark"] .cart-table tr:hover td {
            background-color: rgba(255, 255, 255, 0.03);
        }

        [data-theme="dark"] .cart-table-wrapper > div:first-child {
            background-color: #1e293b !important;
            border-bottom-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] .form-control {
            background-color: #0f172a !important;
            border-color: #475569 !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] .form-control:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.25) !important;
        }

        [data-theme="dark"] select.form-control option {
            background-color: #0f172a;
            color: #f8fafc;
        }

        [data-theme="dark"] .form-hint {
            color: #94a3b8 !important;
        }

        [data-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        [data-theme="dark"] .font-bold.text-dark {
            color: #f8fafc !important;
        }

        [data-theme="dark"] .btn-outline {
            border-color: #475569;
            color: #cbd5e1;
        }

        [data-theme="dark"] .btn-outline:hover {
            background-color: #334155;
            color: #ffffff;
            border-color: #64748b;
        }

        [data-theme="dark"] .theme-radio-item input[type="radio"]:checked + .theme-radio-btn {
            background-color: #334155;
            color: #f8fafc;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
        }

        /* Modal trong chế độ Dark Mode */
        [data-theme="dark"] #product-modal .card,
        [data-theme="dark"] #category-modal .card,
        [data-theme="dark"] #user-modal .card,
        [data-theme="dark"] #detail-modal .card,
        [data-theme="dark"] #reject-modal .card {
            background-color: #1e293b;
            color: #f8fafc;
            border-color: #475569;
        }

        [data-theme="dark"] #product-modal [style*="background:#f8fafc"],
        [data-theme="dark"] #product-modal [style*="background: #f8fafc"] {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        [data-theme="dark"] #product-modal [style*="background:#ffffff"],
        [data-theme="dark"] #product-modal [style*="background: #ffffff"] {
            background-color: #1e293b !important;
            border-color: #475569 !important;
        }
    </style>
</head>
<body>
    <div class="admin-layout" id="admin-layout">
        <!-- 1. THANH BÊN (SIDEBAR) -->
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="admin-sidebar-header">
                <div class="sidebar-brand-wrapper">
                    <span class="sidebar-brand-icon">🍲</span>
                    <div class="sidebar-brand-text">
                        <h3 style="color: #ffffff; font-size: 1.1rem; margin: 0; line-height: 1.2;">Trần Lộc</h3>
                        <span style="font-size: 0.72rem; color: #94a3b8;">Hệ Thống Quản Trị</span>
                    </div>
                </div>
                <!-- Nút bấm thu gọn sidebar dạng icon mũi tên -->
                <button type="button" class="sidebar-collapse-toggle-btn" onclick="toggleAdminSidebar()" title="Đóng / Mở menu thanh bên (Ctrl+B)">
                    <span id="sidebar-arrow-icon">⇤</span>
                </button>
            </div>

            <nav class="admin-sidebar-nav">
                <?php 
                $currentRole = Session::userRole(); 
                $uri = $_SERVER['REQUEST_URI'] ?? '';
                ?>
                <?php if ($currentRole === 'admin'): ?>
                    <a href="<?= Response::baseUrl('admin/dashboard') ?>" class="admin-nav-link <?= str_contains($uri, 'dashboard') ? 'active' : '' ?>" title="Tổng quan Dashboard">
                        <span class="admin-nav-icon">📊</span>
                        <span class="admin-nav-text">Tổng quan Dashboard</span>
                    </a>
                    <a href="<?= Response::baseUrl('staff/orders') ?>" class="admin-nav-link <?= str_contains($uri, 'orders') ? 'active' : '' ?>" title="Quản lý Đơn hàng">
                        <span class="admin-nav-icon">📦</span>
                        <span class="admin-nav-text">Quản lý Đơn hàng</span>
                    </a>
                    <a href="<?= Response::baseUrl('admin/products') ?>" class="admin-nav-link <?= str_contains($uri, 'products') ? 'active' : '' ?>" title="Quản lý Món ăn">
                        <span class="admin-nav-icon">🍽️</span>
                        <span class="admin-nav-text">Quản lý Món ăn</span>
                    </a>
                    <a href="<?= Response::baseUrl('admin/categories') ?>" class="admin-nav-link <?= str_contains($uri, 'categories') ? 'active' : '' ?>" title="Danh mục món">
                        <span class="admin-nav-icon">📁</span>
                        <span class="admin-nav-text">Danh mục món</span>
                    </a>
                    <a href="<?= Response::baseUrl('admin/users') ?>" class="admin-nav-link <?= str_contains($uri, 'users') ? 'active' : '' ?>" title="Quản lý Người dùng">
                        <span class="admin-nav-icon">👥</span>
                        <span class="admin-nav-text">Quản lý Người dùng</span>
                    </a>
                    <a href="<?= Response::baseUrl('admin/settings') ?>" class="admin-nav-link <?= str_contains($uri, 'settings') ? 'active' : '' ?>" title="Cài đặt Nhà hàng">
                        <span class="admin-nav-icon">⚙️</span>
                        <span class="admin-nav-text">Cài đặt Nhà hàng</span>
                    </a>
                <?php elseif ($currentRole === 'staff'): ?>
                    <a href="<?= Response::baseUrl('staff/orders') ?>" class="admin-nav-link <?= str_contains($uri, 'orders') ? 'active' : '' ?>" title="Danh sách Đơn hàng">
                        <span class="admin-nav-icon">📦</span>
                        <span class="admin-nav-text">Danh sách Đơn hàng</span>
                    </a>
                <?php endif; ?>
            </nav>

            <div class="admin-sidebar-footer">
                <a href="<?= Response::baseUrl('logout') ?>" class="btn btn-outline w-100" style="color: #cbd5e1; border-color: rgba(255,255,255,0.2); justify-content: center; display: flex; align-items: center; gap: 0.5rem;" title="Đăng xuất">
                    <span class="admin-nav-icon">🚪</span>
                    <span class="sidebar-footer-text">Đăng xuất</span>
                </a>
            </div>
        </aside>

        <!-- 2. NỘI DUNG CHÍNH (MAIN AREA) -->
        <div class="admin-main">
            <header class="admin-header">
                <div class="admin-header-left">
                    <!-- Nút đóng mở menu ở Header -->
                    <button type="button" class="btn-header-toggle-sidebar" id="btn-header-toggle-sidebar" onclick="toggleAdminSidebar()" title="Đóng / Mở thanh bên (Ctrl+B)">
                        <span id="sidebar-toggle-icon">☰</span>
                    </button>
                    <h2 style="font-size: 1.25rem; margin: 0; color: var(--text-color);"><?= htmlspecialchars($pageTitle ?? 'Bảng điều khiển') ?></h2>
                </div>

                <div class="d-flex align-center gap-2" style="flex-wrap: wrap;">
                    <!-- BỘ CHUYỂN ĐỔI CHẾ ĐỘ SÁNG / TỐI QUA RADIO BUTTON -->
                    <div class="theme-switch-container" title="Chuyển đổi phong cách giao diện Sáng / Tối">
                        <span class="theme-switch-label">Chế độ:</span>
                        <div class="theme-radio-group">
                            <label class="theme-radio-item" title="Chế độ Sáng (Light)">
                                <input type="radio" name="admin_theme_mode" value="light" id="theme-radio-light" onchange="setAdminTheme('light')">
                                <span class="theme-radio-btn">☀️ Sáng</span>
                            </label>
                            <label class="theme-radio-item" title="Chế độ Tối (Dark)">
                                <input type="radio" name="admin_theme_mode" value="dark" id="theme-radio-dark" onchange="setAdminTheme('dark')">
                                <span class="theme-radio-btn">🌙 Tối</span>
                            </label>
                        </div>
                    </div>

                    <?php $currentUser = Session::user(); ?>
                    <div style="font-size: 0.875rem;">
                        <span class="font-bold" style="color: var(--text-color);"><?= htmlspecialchars($currentUser['full_name'] ?? '') ?></span>
                        <span class="badge badge-<?= $currentUser['role'] ?? 'staff' ?>" style="margin-left: 4px;"><?= strtoupper($currentUser['role'] ?? '') ?></span>
                    </div>

                    <a href="<?= Response::baseUrl('') ?>" class="btn btn-sm btn-outline" target="_blank" title="Xem trang chủ khách hàng">
                        🌐 Xem Website
                    </a>

                    <a href="<?= Response::baseUrl('logout') ?>" class="btn btn-sm btn-danger" title="Đăng xuất tài khoản">
                        Thoát
                    </a>
                </div>
            </header>

            <div class="admin-content">
                <?= $content ?>
            </div>
        </div>
    </div>

    <!-- JavaScript Core -->
    <script src="<?= Response::baseUrl('assets/js/app.js') ?>"></script>
    
    <?php
    $successFlash = Session::getFlash('success');
    $errorFlash = Session::getFlash('error');
    ?>

    <script>
        // 1. Hàm đóng mở thanh bên Sidebar
        function toggleAdminSidebar() {
            const layout = document.getElementById('admin-layout');
            const isCollapsed = layout.classList.toggle('sidebar-collapsed');
            localStorage.setItem('admin_sidebar_collapsed', isCollapsed ? '1' : '0');
            
            // Cập nhật biểu tượng
            const arrowIcon = document.getElementById('sidebar-arrow-icon');
            if (arrowIcon) arrowIcon.textContent = isCollapsed ? '⇥' : '⇤';

            const toggleIcon = document.getElementById('sidebar-toggle-icon');
            if (toggleIcon) toggleIcon.textContent = isCollapsed ? '❯' : '☰';
        }

        // 2. Hàm chuyển đổi phong cách Sáng / Tối qua Radio Button
        function setAdminTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('admin_theme', 'dark');
                const darkRadio = document.getElementById('theme-radio-dark');
                if (darkRadio) darkRadio.checked = true;
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('admin_theme', 'light');
                const lightRadio = document.getElementById('theme-radio-light');
                if (lightRadio) lightRadio.checked = true;
            }
        }

        // 3. Khởi tạo và đồng bộ trạng thái khi tải trang
        document.addEventListener('DOMContentLoaded', () => {
            // Đồng bộ Radio Button theo theme đã lưu
            const currentTheme = localStorage.getItem('admin_theme') || 'light';
            const activeRadio = document.getElementById('theme-radio-' + currentTheme);
            if (activeRadio) {
                activeRadio.checked = true;
            }

            // Đồng bộ Sidebar theo trạng thái đã lưu
            const isSidebarCollapsed = localStorage.getItem('admin_sidebar_collapsed') === '1';
            const layout = document.getElementById('admin-layout');
            if (isSidebarCollapsed && layout) {
                layout.classList.add('sidebar-collapsed');
                const arrowIcon = document.getElementById('sidebar-arrow-icon');
                if (arrowIcon) arrowIcon.textContent = '⇥';
                const toggleIcon = document.getElementById('sidebar-toggle-icon');
                if (toggleIcon) toggleIcon.textContent = '❯';
            }
            document.documentElement.classList.remove('sidebar-collapsed-preload');

            // Hiển thị thông báo Toast nếu có
            <?php if (!empty($successFlash)): ?>
                Toast.show('success', <?= json_encode($successFlash) ?>);
            <?php endif; ?>
            <?php if (!empty($errorFlash)): ?>
                Toast.show('error', <?= json_encode($errorFlash) ?>);
            <?php endif; ?>
        });

        // 4. Hỗ trợ phím tắt Ctrl + B để đóng/mở nhanh sidebar
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'b') {
                e.preventDefault();
                toggleAdminSidebar();
            }
        });
    </script>
</body>
</html>
