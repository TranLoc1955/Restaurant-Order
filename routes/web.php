<?php
/**
 * Web Routes
 * Định tuyến toàn bộ giao diện khách hàng, nhân viên và quản trị viên
 */

// ==========================================
// 1. GIAO DIỆN KHÁCH HÀNG
// ==========================================
Router::get('/', [HomeController::class, 'index']);
Router::get('/menu', [MenuController::class, 'index']);
Router::get('/products/{id}', [MenuController::class, 'detail']);

// Giỏ hàng
Router::get('/cart', [CartController::class, 'index']);

// Đặt hàng & Lịch sử đơn (Yêu cầu đăng nhập)
Router::get('/checkout', [OrderController::class, 'showCheckout']);
Router::post('/checkout', [OrderController::class, 'processCheckout']);
Router::get('/order-success/{code}', [OrderController::class, 'success']);
Router::get('/orders', [OrderController::class, 'history']);
Router::get('/orders/{code}', [OrderController::class, 'detail']);
Router::post('/orders/{code}/cancel', [OrderController::class, 'cancel']);

// ==========================================
// 2. XÁC THỰC TÀI KHOẢN (Auth)
// ==========================================
Router::get('/login', [AuthController::class, 'showLogin']);
Router::post('/login', [AuthController::class, 'login']);

Router::get('/register', [AuthController::class, 'showRegister']);
Router::post('/register', [AuthController::class, 'register']);

Router::get('/logout', [AuthController::class, 'logout']);
Router::post('/logout', [AuthController::class, 'logout']);

// ==========================================
// 3. KHU VỰC NHÂN VIÊN (Staff)
// ==========================================
Router::get('/staff/orders', [StaffOrderController::class, 'index']);
Router::post('/staff/orders/status', [StaffOrderController::class, 'updateStatus']);
Router::post('/staff/orders/payment-status', [StaffOrderController::class, 'updatePaymentStatus']);

// ==========================================
// 4. KHU VỰC QUẢN TRỊ VIÊN (Admin)
// ==========================================
Router::get('/admin/dashboard', [AdminDashboardController::class, 'index']);

// Quản lý món ăn
Router::get('/admin/products', [AdminProductController::class, 'index']);
Router::post('/admin/products', [AdminProductController::class, 'store']);
Router::post('/admin/products/{id}/update', [AdminProductController::class, 'update']);
Router::post('/admin/products/{id}/availability', [AdminProductController::class, 'toggleAvailability']);
Router::post('/admin/products/{id}/delete', [AdminProductController::class, 'delete']);

// Quản lý danh mục
Router::get('/admin/categories', [AdminCategoryController::class, 'index']);
Router::post('/admin/categories', [AdminCategoryController::class, 'store']);
Router::post('/admin/categories/{id}/update', [AdminCategoryController::class, 'update']);
Router::post('/admin/categories/{id}/toggle', [AdminCategoryController::class, 'toggleStatus']);

// Quản lý người dùng
Router::get('/admin/users', [AdminUserController::class, 'index']);
Router::post('/admin/users', [AdminUserController::class, 'store']);
Router::post('/admin/users/create-staff', [AdminUserController::class, 'createStaff']);
Router::post('/admin/users/{id}/update', [AdminUserController::class, 'update']);
Router::post('/admin/users/{id}/delete', [AdminUserController::class, 'delete']);
Router::post('/admin/users/{id}/toggle-lock', [AdminUserController::class, 'toggleLock']);

// Quản lý mã giảm giá & Voucher
Router::get('/admin/vouchers', [AdminVoucherController::class, 'index']);
Router::post('/admin/vouchers', [AdminVoucherController::class, 'store']);
Router::post('/admin/vouchers/{id}/toggle', [AdminVoucherController::class, 'toggleStatus']);
Router::post('/admin/vouchers/{id}/delete', [AdminVoucherController::class, 'delete']);

// Cài đặt nhà hàng
Router::get('/admin/settings', [AdminSettingController::class, 'index']);
Router::post('/admin/settings', [AdminSettingController::class, 'update']);
