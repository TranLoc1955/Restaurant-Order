<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Cột thương hiệu bên trái -->
        <div class="auth-brand-side">
            <div class="auth-brand-header">
                <div class="auth-brand-logo">🍲</div>
                <h2 class="auth-brand-title">Trần Lộc Restaurant</h2>
            </div>

            <div class="auth-brand-content">
                <h2>Tham Gia Cùng Trần Lộc</h2>
                <p>Tạo tài khoản để nhận những ưu đãi hấp dẫn, lưu món ăn yêu thích và đặt hàng dễ dàng chỉ với vài cú chạm.</p>

                <ul class="auth-feature-list">
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        <span>Đăng ký nhanh chóng, bảo mật thông tin an toàn</span>
                    </li>
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        <span>Quản lý lịch sử và theo dõi tiến độ đơn hàng COD</span>
                    </li>
                    <li class="auth-feature-item">
                        <span class="auth-feature-icon">✓</span>
                        <span>Nhận hỗ trợ tư vấn món 24/7 từ Trợ lý AI</span>
                    </li>
                </ul>
            </div>

            <div class="auth-brand-footer">
                &copy; <?= date('Y') ?> Nhà Hàng Trần Lộc. Phục vụ với sự tận tâm.
            </div>
        </div>

        <!-- Cột biểu mẫu đăng ký bên phải -->
        <div class="auth-form-side">
            <div class="auth-form-header">
                <h1>Đăng ký tài khoản</h1>
                <p>Trở thành thành viên để bắt đầu trải nghiệm ẩm thực</p>
            </div>

            <form action="<?= Response::baseUrl('register') ?>" method="POST" data-loading novalidate>
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

                <!-- Họ và tên -->
                <div class="form-group">
                    <label for="register-fullname" class="form-label">Họ và tên <span class="required">*</span></label>
                    <input 
                        type="text" 
                        name="full_name" 
                        id="register-fullname" 
                        class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>" 
                        placeholder="Ví dụ: Nguyễn Văn An"
                        value="<?= htmlspecialchars($old['full_name'] ?? '') ?>"
                        required
                    >
                    <?php if (isset($errors['full_name'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['full_name']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label for="register-email" class="form-label">Địa chỉ Email <span class="required">*</span></label>
                    <input 
                        type="email" 
                        name="email" 
                        id="register-email" 
                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                        placeholder="tenban@gmail.com"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        required
                    >
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Số điện thoại -->
                <div class="form-group">
                    <label for="register-phone" class="form-label">Số điện thoại nhận hàng <span class="required">*</span></label>
                    <input 
                        type="tel" 
                        name="phone" 
                        id="register-phone" 
                        class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>" 
                        placeholder="0912345678"
                        value="<?= htmlspecialchars($old['phone'] ?? '') ?>"
                        required
                    >
                    <div class="form-hint">Nhập 10 chữ số bắt đầu bằng 03, 05, 07, 08, 09.</div>
                    <?php if (isset($errors['phone'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Mật khẩu -->
                <div class="form-group">
                    <label for="register-password" class="form-label">Mật khẩu <span class="required">*</span></label>
                    <input 
                        type="password" 
                        name="password" 
                        id="register-password" 
                        class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                        placeholder="Ít nhất 6 ký tự"
                        required
                    >
                    <?php if (isset($errors['password'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Xác nhận mật khẩu -->
                <div class="form-group">
                    <label for="register-confirm-password" class="form-label">Xác nhận mật khẩu <span class="required">*</span></label>
                    <input 
                        type="password" 
                        name="confirm_password" 
                        id="register-confirm-password" 
                        class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" 
                        placeholder="Nhập lại mật khẩu"
                        required
                    >
                    <?php if (isset($errors['confirm_password'])): ?>
                        <div class="form-error"><?= htmlspecialchars($errors['confirm_password']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Nút Đăng ký -->
                <button type="submit" id="register-submit" class="btn btn-primary btn-block btn-lg mt-3">
                    Đăng ký tài khoản
                </button>
            </form>

            <!-- Liên kết quay lại đăng nhập -->
            <div class="auth-alt-link">
                Đã có tài khoản? 
                <a href="<?= Response::baseUrl('login') ?>" id="link-to-login" class="font-bold">Đăng nhập tại đây</a>
            </div>
        </div>
    </div>
</div>
