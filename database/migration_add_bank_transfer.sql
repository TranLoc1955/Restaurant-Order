-- Migration: Thêm phương thức thanh toán chuyển khoản ngân hàng (BANK_TRANSFER)
USE `restaurant_ordering_db`;

ALTER TABLE `orders` 
MODIFY COLUMN `payment_method` ENUM('COD', 'BANK_TRANSFER') NOT NULL DEFAULT 'COD';
