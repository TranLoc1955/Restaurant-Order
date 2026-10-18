<?php
/**
 * API Routes
 * Khai báo các endpoint API nội bộ cho Auth, Giỏ hàng, Chatbot và Staff
 */

// Xác thực
Router::post('/api/auth/login', [AuthController::class, 'apiLogin']);
Router::post('/api/auth/register', [AuthController::class, 'apiRegister']);
Router::get('/api/auth/me', [AuthController::class, 'apiMe']);
Router::post('/api/auth/logout', [AuthController::class, 'apiLogout']);

// Giỏ hàng
Router::post('/api/cart/add', [CartController::class, 'add']);
Router::post('/api/cart/update', [CartController::class, 'update']);
Router::post('/api/cart/remove', [CartController::class, 'remove']);
Router::get('/api/cart/count', [CartController::class, 'count']);

// Nhân viên: Chi tiết đơn hàng
Router::get('/api/staff/orders/{id}', [StaffOrderController::class, 'detail']);

// Chatbot AI
Router::post('/api/chatbot/message', [ChatbotController::class, 'message']);
