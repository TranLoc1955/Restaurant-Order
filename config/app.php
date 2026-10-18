<?php
/**
 * Application Configuration
 * Cấu hình tổng quan ứng dụng và Chatbot AI
 */

return [
    'app_name'     => 'Nhà Hàng Trần Lộc - Food Ordering & AI Chatbot',
    'app_env'      => getenv('APP_ENV') ?: 'development',
    'base_url'     => getenv('APP_URL') ?: '',
    
    // Cấu hình Chatbot AI
    'ai' => [
        'provider'    => 'gemini', // 'gemini' hoặc 'mock'
        'api_key'     => getenv('GEMINI_API_KEY') ?: '',
        'model'       => 'gemini-1.5-flash',
        'mock_mode'   => empty(getenv('GEMINI_API_KEY')), // Tự động bật mock mode nếu không có key
        'timeout'     => 10, // giây
        'max_tokens'  => 500,
    ],
    
    // Cấu hình giỏ hàng & đơn hàng
    'orders' => [
        'currency_symbol' => 'đ',
        'code_prefix'     => 'ORD',
    ],
];
