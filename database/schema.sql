-- ====================================================================
-- DATABASE SCHEMA: restaurant_ordering_db
-- Hệ thống: Restaurant Food Ordering Website with AI Chatbot
-- Môn học: Kiểm thử chất lượng phần mềm
-- Ràng buộc: Chuẩn hóa 3NF, Khóa ngoại (InnoDB), UTF-8 MB4
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `restaurant_ordering_db` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `restaurant_ordering_db`;

-- Vô hiệu hóa kiểm tra khóa ngoại tạm thời để thực hiện drop bảng an toàn
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `chatbot_logs`;
DROP TABLE IF EXISTS `order_status_history`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `restaurant_settings`;

SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------
-- 1. BẢNG restaurant_settings (Cấu hình thông tin nhà hàng)
-- --------------------------------------------------------------------
CREATE TABLE `restaurant_settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `restaurant_name` VARCHAR(150) NOT NULL,
    `address` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `opening_hours` VARCHAR(100) NOT NULL,
    `delivery_fee` DECIMAL(12, 2) NOT NULL DEFAULT 20000.00,
    `minimum_order_amount` DECIMAL(12, 2) NOT NULL DEFAULT 50000.00,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. BẢNG users (Người dùng: Customer, Staff, Admin)
-- --------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('customer', 'staff', 'admin') NOT NULL DEFAULT 'customer',
    `status` ENUM('active', 'locked') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. BẢNG categories (Danh mục món ăn)
-- --------------------------------------------------------------------
CREATE TABLE `categories` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL UNIQUE,
    `description` TEXT NULL,
    `status` ENUM('active', 'hidden') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_categories_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. BẢNG products (Món ăn / Đồ uống)
-- --------------------------------------------------------------------
CREATE TABLE `products` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `category_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `image` VARCHAR(255) NULL,
    `availability_status` ENUM('in_stock', 'out_of_stock', 'hidden') NOT NULL DEFAULT 'in_stock',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_products_category` 
        FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_products_category` (`category_id`),
    INDEX `idx_products_availability` (`availability_status`),
    INDEX `idx_products_price` (`price`),
    INDEX `idx_products_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. BẢNG orders (Đơn hàng)
-- --------------------------------------------------------------------
CREATE TABLE `orders` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_code` VARCHAR(32) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED NOT NULL,
    `recipient_name` VARCHAR(100) NOT NULL,
    `recipient_phone` VARCHAR(20) NOT NULL,
    `delivery_address` VARCHAR(255) NOT NULL,
    `note` TEXT NULL,
    `subtotal` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `shipping_fee` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    `payment_method` ENUM('COD') NOT NULL DEFAULT 'COD',
    `payment_status` ENUM('unpaid', 'paid') NOT NULL DEFAULT 'unpaid',
    `order_status` ENUM('PENDING', 'CONFIRMED', 'PREPARING', 'READY', 'DELIVERED', 'REJECTED', 'CANCELLED') NOT NULL DEFAULT 'PENDING',
    `rejection_reason` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT `fk_orders_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_orders_code` (`order_code`),
    INDEX `idx_orders_user` (`user_id`),
    INDEX `idx_orders_status` (`order_status`),
    INDEX `idx_orders_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. BẢNG order_items (Chi tiết món ăn lúc đặt)
-- --------------------------------------------------------------------
CREATE TABLE `order_items` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NULL,
    `product_name_snapshot` VARCHAR(150) NOT NULL,
    `unit_price` DECIMAL(12, 2) NOT NULL,
    `quantity` INT UNSIGNED NOT NULL DEFAULT 1,
    `line_total` DECIMAL(12, 2) NOT NULL,
    CONSTRAINT `fk_order_items_order` 
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_order_items_product` 
        FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_order_items_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 7. BẢNG order_status_history (Nhật ký thay đổi trạng thái đơn)
-- --------------------------------------------------------------------
CREATE TABLE `order_status_history` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT UNSIGNED NOT NULL,
    `old_status` VARCHAR(30) NULL,
    `new_status` VARCHAR(30) NOT NULL,
    `changed_by` INT UNSIGNED NOT NULL,
    `note` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_history_order` 
        FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_history_user` 
        FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) 
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_history_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 8. BẢNG chatbot_logs (Nhật ký hội thoại với Chatbot AI)
-- --------------------------------------------------------------------
CREATE TABLE `chatbot_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NULL,
    `session_id` VARCHAR(100) NOT NULL,
    `user_message` TEXT NOT NULL,
    `bot_reply` TEXT NOT NULL,
    `mode` ENUM('ai', 'mock') NOT NULL DEFAULT 'mock',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_chatbot_user` 
        FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_chatbot_session` (`session_id`),
    INDEX `idx_chatbot_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
