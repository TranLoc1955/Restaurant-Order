/**
 * Application Core JavaScript
 * Xử lý Toast notification, Form loading state và Helper điền thông tin demo
 */

// Quản lý Toast Notification
const Toast = {
    container: null,

    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            this.container.id = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show(type = 'info', message = '', duration = 4000) {
        this.init();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        
        let iconSvg = '';
        if (type === 'success') {
            iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>';
        } else if (type === 'error') {
            iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
        } else if (type === 'warning') {
            iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
        } else {
            iconSvg = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>';
        }

        toast.innerHTML = `
            ${iconSvg}
            <div class="toast-content">${message}</div>
            <button type="button" class="toast-close" aria-label="Đóng">&times;</button>
        `;

        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.addEventListener('click', () => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 250);
        });

        this.container.appendChild(toast);

        if (duration > 0) {
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => toast.remove(), 250);
                }
            }, duration);
        }
    }
};

// Helper điền nhanh tài khoản demo phục vụ chấm bài kiểm thử
function fillDemoAccount(role) {
    const emailInput = document.getElementById('login-email');
    const passInput = document.getElementById('login-password');
    if (!emailInput || !passInput) return;

    if (role === 'admin') {
        emailInput.value = 'admin@restaurant.com';
        passInput.value = 'admin123';
        Toast.show('info', 'Đã điền tài khoản Quản trị viên (Admin)');
    } else if (role === 'staff') {
        emailInput.value = 'staff1@restaurant.com';
        passInput.value = 'staff123';
        Toast.show('info', 'Đã điền tài khoản Nhân viên (Staff)');
    } else if (role === 'customer') {
        emailInput.value = 'customer1@gmail.com';
        passInput.value = 'customer123';
        Toast.show('info', 'Đã điền tài khoản Khách hàng (Customer)');
    } else if (role === 'locked') {
        emailInput.value = 'locked_user@gmail.com';
        passInput.value = 'customer123';
        Toast.show('warning', 'Đã điền tài khoản Bị Khóa (để test chặn)');
    }
}

// Tự động kích hoạt trạng thái loading cho button khi submit form
document.addEventListener('DOMContentLoaded', () => {
    const forms = document.querySelectorAll('form[data-loading]');
    forms.forEach(form => {
        form.addEventListener('submit', () => {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('btn-loading');
            }
        });
    });
});
