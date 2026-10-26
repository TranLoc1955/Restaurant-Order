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
        'provider'      => getenv('AI_PROVIDER') ?: 'gemini', // 'gemini'
        'api_key'       => getenv('GEMINI_API_KEY') ?: getenv('AI_API_KEY') ?: '',
        'model'         => getenv('GEMINI_MODEL') ?: 'gemini-1.5-flash',
        'timeout'       => (int)(getenv('AI_TIMEOUT') ?: 15), // thời gian chờ tối đa (giây)
        'max_tokens'    => 800,
        'temperature'   => 0.4,
        'history_limit' => 6, // Số lượt tin nhắn lịch sử gần nhất nạp vào prompt để hiểu câu hỏi tiếp nối
    ],
    
    // Cấu hình giỏ hàng & đơn hàng
    'orders' => [
        'currency_symbol' => 'đ',
        'code_prefix'     => 'ORD',
    ],

    // Cấu hình thanh toán chuyển khoản ngân hàng & mã VietQR
    'banking' => [
        'bank_id'      => getenv('BANK_ID') ?: 'VCB', // MB, VCB, TCB, ACB, VPB, BIDV, ICB...
        'bank_name'    => getenv('BANK_NAME') ?: 'VietComBank',
        'account_no'   => getenv('BANK_ACCOUNT_NO') ?: '1036646124',
        'account_name' => getenv('BANK_ACCOUNT_NAME') ?: 'TRAN VAN LOC',
    ],
];
