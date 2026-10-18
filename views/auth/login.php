<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Cột thương hiệu bên trái -->
        <div class="auth-brand-side">
            <div class="auth-brand-header">
                <div class="auth-brand-logo">🍲</div>
                <h2 class="auth-brand-title">Trần Lộc Restaurant</h2>
            </div>

            <div class="auth-brand-content">
                <h2>Tinh Hoa Ẩm Thực Việt & Trợ Lý AI</h2>
                <p>Khám phá thực đơn phong phú, đặt món trực tuyến nhanh chóng với sự hỗ trợ đắc lực từ Trợ lý ẩm thực AI thông minh.</p>

                <ul class="auth-feature-list">
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        <span>Món ăn tươi ngon nấu chuẩn hương vị truyền thống</span>
                    </li>
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        <span>Trợ lý AI tư vấn món ăn phù hợp với ngân sách</span>
                    </li>
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        <span>Theo dõi trạng thái đơn hàng thời gian thực</span>
                    </li>
                </ul>
            </div>

            <div class="auth-brand-footer">
                &copy; <?= date('Y') ?> Nhà Hàng Trần Lộc. Phục vụ với sự tận tâm.
            </div>
        </div>

        <!-- Cột biểu mẫu đăng nhập bên phải -->
        <div class="auth-form-side">
            <div class="auth-form-header">
                <h1>Đăng nhập</h1>
                <p>Vui lòng nhập tài khoản của bạn để tiếp tục đặt món</p>
            </div>

            <form action="<?= Response::baseUrl('login') ?>" method="POST" data-loading novalidate>
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

                <!-- Email -->
                <div class="form-group">
                    <label for="login-email" class="form-label">Email đăng nhập <span class="required">*</span></label>
                    <input 
                        type="email" 
                        name="email" 
                        id="login-email" 
                        class="form-control <?= isset($errors['email']) || isset($errors['login']) ? 'is-invalid' : '' ?>" 
                        placeholder="tenban@example.com"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        required 
                        autocomplete="email"
                    >
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <div class="d-flex justify-between align-center mb-1">
                        <label for="login-password" class="form-label mb-0">Mật khẩu <span class="required">*</span></label>
                    </div>
                    <input 
                        type="password" 
                        name="password" 
                        id="login-password" 
                        class="form-control <?= isset($errors['password']) || isset($errors['login']) ? 'is-invalid' : '' ?>" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                    <?php if (isset($errors['password'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Nút Đăng nhập -->
                <button type="submit" id="login-submit" class="btn btn-primary btn-block btn-lg mt-3">
                    Đăng nhập ngay
                </button>
            </form>
<!--  // Hộp chọn nhanh tài khoản thử nghiệm phục vụ chấm bài kiểm thử 
            <div class="demo-accounts-box">
                <div class="demo-accounts-title">
                    <span>⚡ Điền nhanh tài khoản kiểm thử:</span>
                </div>
                <div class="demo-buttons-grid">
                    <button type="button" class="btn-demo" onclick="fillDemoAccount('admin')" title="Quyền Quản trị viên">
                        👑 Admin
                    </button>
                    <button type="button" class="btn-demo" onclick="fillDemoAccount('staff')" title="Quyền Nhân viên xử lý đơn">
                        🧑‍🍳 Staff
                    </button>
                    <button type="button" class="btn-demo" onclick="fillDemoAccount('customer')" title="Khách hàng hợp lệ">
                        👤 Khách hàng
                    </button>
                </div>
                <div class="mt-2 text-center">
                    <button type="button" class="btn-demo w-100" onclick="fillDemoAccount('locked')" style="color: var(--danger-color); font-size: 0.72rem;">
                        🔒 Thử nghiệm: Tài khoản bị khóa (Test chặn)
                    </button>
                </div>
            </div>-->
            

            <!-- Liên kết đăng ký -->
            <div class="auth-alt-link">
                Chưa có tài khoản? 
                <a href="<?= Response::baseUrl('register') ?>" id="link-to-register" class="font-bold">Đăng ký tài khoản mới</a>
            </div>
        </div>
    </div>
</div>
