-- ====================================================================
-- DATABASE MIGRATION: Vouchers & Khách hàng thân thiết / Tích điểm
-- Hệ thống: Restaurant Food Ordering Website with AI Chatbot
-- ====================================================================

USE `restaurant_ordering_db`;

-- 1. Mở rộng bảng users: Thêm điểm tích lũy thưởng (reward_points)
ALTER TABLE `users` 
ADD COLUMN IF NOT EXISTS `reward_points` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `role`;

-- 2. Mở rộng bảng orders: Thêm các cột giảm giá voucher và điểm thưởng
ALTER TABLE `orders` 
ADD COLUMN IF NOT EXISTS `voucher_code` VARCHAR(32) NULL AFTER `payment_status`,
ADD COLUMN IF NOT EXISTS `discount_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `voucher_code`,
ADD COLUMN IF NOT EXISTS `points_used` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `discount_amount`,
ADD COLUMN IF NOT EXISTS `points_discount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00 AFTER `points_used`,
ADD COLUMN IF NOT EXISTS `points_earned` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `points_discount`;

-- 3. BẢNG vouchers: Quản lý mã giảm giá
CREATE TABLE IF NOT EXISTS `vouchers` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(32) NOT NULL UNIQUE,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `discount_type` ENUM('FIXED', 'PERCENT') NOT NULL DEFAULT 'FIXED',
    `discount_value` DECIMAL(12, 2) NOT NULL,
    `max_discount_amount` DECIMAL(12, 2) NULL,
    `min_order_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `usage_limit` INT UNSIGNED NULL,
    `used_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `usage_limit_per_user` INT UNSIGNED NOT NULL DEFAULT 1,
    `customer_condition` ENUM('ALL', 'RETURNING_ONLY', 'NEW_ONLY') NOT NULL DEFAULT 'ALL',
    `start_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `end_date` DATETIME NULL,
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_vouchers_code` (`code`),
    INDEX `idx_vouchers_status` (`status`),
    INDEX `idx_vouchers_condition` (`customer_condition`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. BẢNG voucher_usages: Lịch sử áp dụng mã voucher của khách hàng
CREATE TABLE IF NOT EXISTS `voucher_usages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `voucher_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `discount_amount` DECIMAL(12, 2) NOT NULL,
    `used_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_voucher_usages_voucher` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_voucher_usages_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_voucher_usages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX `idx_usages_user_voucher` (`user_id`, `voucher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. BẢNG point_transactions: Nhật ký biến động điểm thưởng (Audit Trail)
CREATE TABLE IF NOT EXISTS `point_transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `order_id` INT UNSIGNED NULL,
    `points` INT NOT NULL,
    `type` ENUM('EARN', 'REDEEM', 'REFUND', 'BONUS') NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_point_trans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_point_trans_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_point_user` (`user_id`),
    INDEX `idx_point_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. DỮ LIỆU MẪU BAN ĐẦU: Các Voucher hấp dẫn
INSERT INTO `vouchers` 
(`code`, `title`, `description`, `discount_type`, `discount_value`, `max_discount_amount`, `min_order_amount`, `usage_limit`, `usage_limit_per_user`, `customer_condition`, `start_date`, `end_date`, `status`)
VALUES 
('TRIANKHACHCU', 'Tri Ân Khách Hàng Thân Thiết', 'Giảm 30.000đ dành riêng cho quý khách hàng đã từng đặt món thành công tại Nhà Hàng', 'FIXED', 30000.00, NULL, 120000.00, 200, 2, 'RETURNING_ONLY', NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 90 DAY, 'active'),
('BANMOI20K', 'Chào Bạn Mới Đặt Món Lần Đầu', 'Giảm 20.000đ cho đơn hàng đầu tiên trải nghiệm ẩm thực tại Nhà Hàng', 'FIXED', 20000.00, NULL, 80000.00, 500, 1, 'NEW_ONLY', NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 90 DAY, 'active'),
('GIAM10', 'Ưu Đãi 10% Bữa Tiệc Sum Vầy', 'Giảm 10% (tối đa 50.000đ) áp dụng cho mọi khách hàng đặt tiệc từ 150.000đ', 'PERCENT', 10.00, 50000.00, 150000.00, 300, 3, 'ALL', NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 90 DAY, 'active'),
('FREESHIP', 'Miễn Phí Giao Hàng Tận Cửa', 'Giảm ngay 20.000đ tiền ship cho đơn hàng từ 100.000đ', 'FIXED', 20000.00, NULL, 100000.00, 500, 5, 'ALL', NOW() - INTERVAL 1 DAY, NOW() + INTERVAL 90 DAY, 'active')
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`), `description` = VALUES(`description`);

-- 7. Cấp điểm tích lũy khởi đầu cho các khách hàng đã từng có đơn DELIVERED trong hệ thống
UPDATE `users` u
SET `reward_points` = (
    SELECT COALESCE(FLOOR(SUM(o.total_amount) / 10000), 0)
    FROM `orders` o
    WHERE o.user_id = u.id AND o.order_status = 'DELIVERED'
)
WHERE u.role = 'customer';
