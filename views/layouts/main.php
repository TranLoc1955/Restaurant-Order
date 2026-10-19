<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nhà Hàng Trần Lộc - Đặt đồ ăn trực tuyến chất lượng cao, giao nhanh COD và tư vấn món ăn thông minh với Trợ lý AI.">
    <title><?= htmlspecialchars($pageTitle ?? 'Nhà Hàng Trần Lộc - Đặt đồ ăn trực tuyến & Trợ lý AI') ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/base.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/components.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/customer.css') ?>">
    <link rel="stylesheet" href="<?= Response::baseUrl('assets/css/chatbot.css') ?>">

    <script>
        window.APP_BASE_URL = <?= json_encode(Response::baseUrl()) ?>;
    </script>
</head>
<body>
    <!-- ROLE WORKSPACE TOPBAR (Dành riêng cho Admin và Staff khi duyệt website) -->
    <?php if (Session::isLoggedIn() && in_array(Session::userRole(), ['admin', 'staff'])): ?>
        <?php $currentUser = Session::user(); ?>
        <div style="background-color: <?= ($currentUser['role'] === 'admin') ? '#0f172a' : '#1e3a8a' ?>; color: #ffffff; padding: 0.5rem 1.5rem; font-size: 0.8125rem; border-bottom: 1px solid rgba(255,255,255,0.15);">
            <div class="container d-flex justify-between align-center" style="flex-wrap: wrap; gap: 0.5rem;">
                <div class="d-flex align-center gap-1">
                    <span style="font-size: 1.1rem;"><?= ($currentUser['role'] === 'admin') ? '👑' : '🧑‍🍳' ?></span>
                    <strong><?= ($currentUser['role'] === 'admin') ? 'BẠN ĐANG ĐĂNG NHẬP VỚI QUYỀN QUẢN TRỊ VIÊN (ADMIN)' : 'BẠN ĐANG ĐĂNG NHẬP VỚI QUYỀN NHÂN VIÊN (STAFF)' ?>:</strong>
                    <span><?= htmlspecialchars($currentUser['full_name']) ?></span>
                </div>
                <div class="d-flex align-center gap-1">
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <a href="<?= Response::baseUrl('admin/dashboard') ?>" class="btn btn-sm btn-primary" style="padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                            📊 Bảng điều khiển (Dashboard)
                        </a>
                        <a href="<?= Response::baseUrl('staff/orders') ?>" class="btn btn-sm btn-outline" style="color: #ffffff; border-color: rgba(255,255,255,0.4); padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                            📦 Đơn hàng
                        </a>
                        <a href="<?= Response::baseUrl('admin/products') ?>" class="btn btn-sm btn-outline" style="color: #ffffff; border-color: rgba(255,255,255,0.4); padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                            🍽️ Món ăn
                        </a>
                        <a href="<?= Response::baseUrl('admin/users') ?>" class="btn btn-sm btn-outline" style="color: #ffffff; border-color: rgba(255,255,255,0.4); padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                            👥 Người dùng
                        </a>
                        <a href="<?= Response::baseUrl('admin/settings') ?>" class="btn btn-sm btn-outline" style="color: #ffffff; border-color: rgba(255,255,255,0.4); padding: 0.25rem 0.65rem; font-size: 0.75rem;">
                            ⚙️ Cài đặt
                        </a>
                    <?php elseif ($currentUser['role'] === 'staff'): ?>
                        <a href="<?= Response::baseUrl('staff/orders') ?>" class="btn btn-sm btn-primary" style="background: #2563eb; border-color: #2563eb; padding: 0.25rem 0.75rem; font-size: 0.75rem;">
                            📦 Vào Bảng Xử Lý Đơn Hàng Ngay &rarr;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 1. HEADER & NAVIGATION -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="<?= Response::baseUrl('') ?>" class="brand-logo" id="nav-brand-logo">
                <span class="brand-icon">🍲</span>
                <span class="brand-text">Trần Lộc</span>
            </a>

            <!-- Menu thông minh phân theo vai trò -->
            <nav class="site-nav">
                <?php $userRole = Session::userRole(); ?>
                
                <?php if ($userRole === 'admin'): ?>
                    <!-- Menu dành cho Admin -->
                    <a href="<?= Response::baseUrl('admin/dashboard') ?>" class="nav-link font-bold text-primary">📊 Dashboard</a>
                    <a href="<?= Response::baseUrl('staff/orders') ?>" class="nav-link">📦 Quản lý Đơn hàng</a>
                    <a href="<?= Response::baseUrl('admin/products') ?>" class="nav-link">🍽️ Quản lý Món ăn</a>
                    <a href="<?= Response::baseUrl('admin/categories') ?>" class="nav-link">📁 Danh mục</a>
                    <a href="<?= Response::baseUrl('admin/users') ?>" class="nav-link">👥 Người dùng</a>
                    <a href="<?= Response::baseUrl('admin/settings') ?>" class="nav-link">⚙️ Cài đặt</a>
                    <a href="<?= Response::baseUrl('menu') ?>" class="nav-link">🌐 Xem Menu Khách</a>

                <?php elseif ($userRole === 'staff'): ?>
                    <!-- Menu dành cho Nhân viên -->
                    <a href="<?= Response::baseUrl('staff/orders') ?>" class="nav-link font-bold text-primary">📦 Xử lý Đơn Hàng</a>
                    <a href="<?= Response::baseUrl('menu') ?>" class="nav-link">🍽️ Xem Thực đơn</a>
                    <a href="<?= Response::baseUrl('') ?>" class="nav-link">🏠 Trang chủ</a>

                <?php else: ?>
                    <!-- Menu dành cho Khách hàng & Khách vãng lai -->
                    <a href="<?= Response::baseUrl('') ?>" class="nav-link <?= empty($_SERVER['PATH_INFO']) || $_SERVER['PATH_INFO'] === '/' ? 'active' : '' ?>">Trang chủ</a>
                    <a href="<?= Response::baseUrl('menu') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'menu') ? 'active' : '' ?>">Thực đơn</a>
                    <?php if (Session::isLoggedIn()): ?>
                        <a href="<?= Response::baseUrl('orders') ?>" class="nav-link <?= str_contains($_SERVER['REQUEST_URI'] ?? '', 'orders') ? 'active' : '' ?>">Đơn của tôi</a>
                    <?php endif; ?>
                    <a href="#about" class="nav-link">Về nhà hàng</a>
                <?php endif; ?>
            </nav>

            <div class="header-actions">
                <!-- Nút Giỏ hàng (chỉ hiển thị cho Khách hàng) -->
                <?php if ($userRole !== 'admin' && $userRole !== 'staff'): ?>
                    <a href="<?= Response::baseUrl('cart') ?>" class="cart-btn" id="btn-header-cart" title="Xem giỏ hàng">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span class="cart-badge" id="cart-count-badge" style="display: none;">0</span>
                    </a>
                <?php endif; ?>

                <!-- Tài khoản người dùng -->
                <?php if (Session::isLoggedIn()): ?>
                    <?php $user = Session::user(); ?>
                    <div class="d-flex align-center gap-1">
                        <span class="user-menu-btn">
                            <span><?= ($user['role'] === 'admin') ? '👑' : (($user['role'] === 'staff') ? '🧑‍🍳' : '👤') ?></span>
                            <span><?= htmlspecialchars($user['full_name']) ?></span>
                            <span class="badge badge-<?= $user['role'] ?>" style="font-size:0.65rem; margin-left:4px;"><?= strtoupper($user['role']) ?></span>
                        </span>
                        <a href="<?= Response::baseUrl('logout') ?>" class="btn btn-sm btn-danger" title="Đăng xuất">Thoát</a>
                    </div>
                <?php else: ?>
                    <div class="d-flex align-center gap-1">
                        <a href="<?= Response::baseUrl('login') ?>" class="btn btn-sm btn-outline" id="btn-nav-login">Đăng nhập</a>
                        <a href="<?= Response::baseUrl('register') ?>" class="btn btn-sm btn-primary" id="btn-nav-register">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- 2. NỘI DUNG CHÍNH (CONTENT) -->
    <main>
        <?= $content ?>
    </main>

    <!-- 3. FOOTER NHÀ HÀNG -->
    <footer class="site-footer" id="about">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <h3 class="footer-title" style="display:flex; align-items:center; gap:0.5rem;">
                        <span>🍲</span> Nhà Hàng Trần Lộc
                    </h3>
                    <p style="font-size: 0.875rem; color: #94a3b8; line-height: 1.6;">
                        Chuyên phục vụ các món ăn truyền thống Việt Nam, hải sản tươi sống và lẩu đậm đà. Đặt món trực tuyến tiện lợi, giao tận nơi nhanh chóng với thanh toán tiền mặt (COD).
                    </p>
                </div>
                <div>
                    <h4 class="footer-title">Khám Phá</h4>
                    <ul class="footer-links">
                        <li><a href="<?= Response::baseUrl('menu') ?>">Thực đơn món ăn</a></li>
                        <li><a href="<?= Response::baseUrl('menu?category=1') ?>">Khai vị & Salad</a></li>
                        <li><a href="<?= Response::baseUrl('menu?category=2') ?>">Cơm & Mì đặc sản</a></li>
                        <li><a href="<?= Response::baseUrl('menu?category=4') ?>">Hải sản & Lẩu</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Khách Hàng</h4>
                    <ul class="footer-links">
                        <li><a href="<?= Response::baseUrl('orders') ?>">Lịch sử đơn hàng</a></li>
                        <li><a href="<?= Response::baseUrl('cart') ?>">Giỏ hàng</a></li>
                        <li><a href="<?= Response::baseUrl('login') ?>">Đăng nhập tài khoản</a></li>
                        <li><a href="<?= Response::baseUrl('register') ?>">Đăng ký thành viên</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="footer-title">Liên Hệ & Giờ Phục Vụ</h4>
                    <p style="font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.5rem;">
                        📍 150 Nguyễn Như Hạnh, Hòa Minh, Liên Chiểu, Đà Nẵng
                    </p>
                    <p style="font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.5rem;">
                        📞 Hotline: <strong style="color: #ffffff;">0778591167</strong>
                    </p>
                    <p style="font-size: 0.875rem; color: #94a3b8;">
                        ⏰ Mở cửa: <strong>08:00 - 23:00</strong> (Hằng ngày)
                    </p>
                </div>
            </div>

            <div class="footer-bottom">
                &copy; <?= date('Y') ?> Nhà Hàng Trần Lộc. Hệ thống website đặt đồ ăn trực tuyến tích hợp Chatbot AI.
            </div>
        </div>
    </footer>

    <!-- 4. FLOATING AI CHATBOT WIDGET -->
    <div class="chatbot-widget">
        <!-- Bong bóng gợi ý -->
        <div class="chatbot-pill-hint" onclick="Chatbot.toggle()" id="chatbot-hint-pill">
            <span>✨ Hỏi Trợ lý món ngon AI</span>
        </div>

        <!-- Nút tròn mở widget -->
        <button type="button" class="chatbot-toggle-btn" onclick="Chatbot.toggle()" id="chatbot-toggle-btn" aria-label="Mở Trợ lý AI">
            💬
        </button>
    </div>

    <!-- Cửa sổ Chatbot AI -->
    <div class="chatbot-window" id="chatbot-window">
        <div class="chatbot-header">
            <div class="chatbot-header-title">
                <div class="chatbot-avatar">🍲</div>
                <div class="chatbot-info">
                    <h4>Trợ Lý Món Ngon</h4>
                    <span id="chatbot-status-subtitle">Trần Lộc AI Assistant</span>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:0.5rem;">
                <button type="button" class="chatbot-icon-btn" onclick="Chatbot.clear()" title="Bắt đầu hội thoại mới" style="background:none; border:none; color:#ffffff; cursor:pointer; font-size:1rem; opacity:0.85; padding:4px;" aria-label="Làm mới hội thoại">🔄</button>
                <button type="button" class="chatbot-close-btn" onclick="Chatbot.close()" aria-label="Đóng">&times;</button>
            </div>
        </div>

        <div class="chatbot-body" id="chatbot-body">
            <!-- Tin nhắn chào mừng ban đầu -->
            <div class="chat-bubble chat-bubble-bot">
                <span style="display:inline-block; font-size:0.65rem; background:#ffedd5; color:#9a3412; padding:1px 6px; border-radius:10px; margin-bottom:4px; font-weight:700;">🍲 Trợ lý Món Ngon</span><br>
                Chào bạn! Mình là Trợ lý món ngon của <strong>Nhà Hàng Trần Lộc</strong>.<br><br>
                Bạn có thể hỏi mình về thực đơn, gợi ý món ăn theo ngân sách, giờ mở cửa hay phí giao hàng nhé!
            </div>
        </div>

        <!-- Gợi ý câu hỏi nhanh -->
        <div class="chatbot-chips">
            <button type="button" class="chatbot-chip" onclick="Chatbot.askPrompt('Có món nào dưới 50k không?')">
                💰 Dưới 50.000đ
            </button>
            <button type="button" class="chatbot-chip" onclick="Chatbot.askPrompt('Nhà mình có 4 người thì nên gọi món gì?')">
                👨‍👩‍👧‍👦 Tư vấn cho 4 người
            </button>
            <button type="button" class="chatbot-chip" onclick="Chatbot.askPrompt('Gợi ý cho tôi món ăn trưa ngon')">
                🍱 Gợi ý món ăn trưa
            </button>
            <button type="button" class="chatbot-chip" onclick="Chatbot.askPrompt('Nhà hàng mở cửa mấy giờ và phí ship thế nào?')">
                ⏰ Giờ mở cửa & Phí ship
            </button>
        </div>

        <!-- Khung nhập tin nhắn -->
        <div class="chatbot-footer">
            <textarea id="chatbot-input" class="chatbot-input" placeholder="Hỏi Trợ lý AI về món ăn, ngân sách, số người..." rows="1" maxlength="500"></textarea>
            <button type="button" class="chatbot-send-btn" id="chatbot-send-btn" onclick="Chatbot.send()" aria-label="Gửi tin nhắn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                </svg>
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= Response::baseUrl('assets/js/app.js') ?>"></script>
    <script src="<?= Response::baseUrl('assets/js/customer.js') ?>"></script>
    <script src="<?= Response::baseUrl('assets/js/chatbot.js') ?>"></script>

    <!-- Kích hoạt Flash Message dạng Toast nếu có -->
    <?php
    $successFlash = Session::getFlash('success');
    $errorFlash = Session::getFlash('error');
    $warningFlash = Session::getFlash('warning');
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            <?php if (!empty($successFlash)): ?>
                Toast.show('success', <?= json_encode($successFlash) ?>);
            <?php endif; ?>
            <?php if (!empty($errorFlash)): ?>
                Toast.show('error', <?= json_encode($errorFlash) ?>);
            <?php endif; ?>
            <?php if (!empty($warningFlash)): ?>
                Toast.show('warning', <?= json_encode($warningFlash) ?>);
            <?php endif; ?>
        });
    </script>
</body>
</html>
