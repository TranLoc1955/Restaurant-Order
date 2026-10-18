-- ====================================================================
-- SEED DATA: restaurant_ordering_db
-- Dữ liệu mẫu phong phú phục vụ kiểm thử thủ công và tự động
-- ====================================================================

USE `restaurant_ordering_db`;

-- Xóa dữ liệu cũ theo thứ tự khóa ngoại
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `chatbot_logs`;
TRUNCATE TABLE `order_status_history`;
TRUNCATE TABLE `order_items`;
TRUNCATE TABLE `orders`;
TRUNCATE TABLE `products`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `users`;
TRUNCATE TABLE `restaurant_settings`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------
-- 1. CÀI ĐẶT NHÀ HÀNG
-- --------------------------------------------------------------------
INSERT INTO `restaurant_settings` 
(`id`, `restaurant_name`, `address`, `phone`, `opening_hours`, `delivery_fee`, `minimum_order_amount`) 
VALUES 
(1, 'Nhà Hàng Trần Lộc', '150 Nguyễn Như Hạnh, Hòa Minh, Liên Chiểu, Đà Nẵng', '0778591167', '08:00 - 23:00 (Hằng ngày)', 20000.00, 50000.00);

-- --------------------------------------------------------------------
-- 2. TÀI KHOẢN NGƯỜI DÙNG (Mật khẩu được mã hóa bcrypt)
-- Admin: admin123 ($2y$10$9SLyA3ufUJ7KXlbgYacf9uCtx8NcO/tc2LePtsw18tv0IDu8JU4X2)
-- Staff: staff123 ($2y$10$A1Fw2YyUNPy6RlfZ8XjLue2wLfLFt6UbWvFVKuzyrMN5nYrc1bhTO)
-- Customer: customer123 ($2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO)
-- --------------------------------------------------------------------
INSERT INTO `users` 
(`id`, `full_name`, `email`, `phone`, `password_hash`, `role`, `status`) 
VALUES 
(1, 'Quản Trị Viên (Admin)', 'admin@restaurant.com', '0909000001', '$2y$10$9SLyA3ufUJ7KXlbgYacf9uCtx8NcO/tc2LePtsw18tv0IDu8JU4X2', 'admin', 'active'),
(2, 'Nhân Viên Thu Ngân (Staff 1)', 'staff1@restaurant.com', '0909000002', '$2y$10$A1Fw2YyUNPy6RlfZ8XjLue2wLfLFt6UbWvFVKuzyrMN5nYrc1bhTO', 'staff', 'active'),
(3, 'Nhân Viên Bếp (Staff 2)', 'staff2@restaurant.com', '0909000003', '$2y$10$A1Fw2YyUNPy6RlfZ8XjLue2wLfLFt6UbWvFVKuzyrMN5nYrc1bhTO', 'staff', 'active'),
(4, 'Trần Minh Tuấn (Customer 1)', 'customer1@gmail.com', '0912345678', '$2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO', 'customer', 'active'),
(5, 'Lê Thị Mai (Customer 2)', 'customer2@gmail.com', '0923456789', '$2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO', 'customer', 'active'),
(6, 'Phạm Hoàng Nam (Customer 3)', 'customer3@gmail.com', '0934567890', '$2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO', 'customer', 'active'),
(7, 'Tài Khoản Bị Khóa (Locked Customer)', 'locked_user@gmail.com', '0945678901', '$2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO', 'customer', 'locked');

-- --------------------------------------------------------------------
-- 3. DANH MỤC MÓN ĂN (6 danh mục hoạt động + 1 danh mục ẩn để kiểm thử)
-- --------------------------------------------------------------------
INSERT INTO `categories` 
(`id`, `name`, `description`, `status`) 
VALUES 
(1, 'Khai vị & Salad', 'Các món khai vị thanh mát, kích thích vị giác đầu bữa tiệc', 'active'),
(2, 'Cơm & Mì đặc sản', 'Món ăn no truyền thống kết hợp hương vị ẩm thực Việt', 'active'),
(3, 'Món chính Bò & Gà', 'Thịt bò tươi mềm và thịt gà ta thả vườn chế biến đậm đà', 'active'),
(4, 'Hải sản & Lẩu', 'Hải sản tươi sống trong ngày cùng các loại lẩu thơm ngon', 'active'),
(5, 'Đồ uống & Trà hoa quả', 'Nước ép trái cây tươi, trà thanh nhiệt giải khát', 'active'),
(6, 'Tráng miệng', 'Bánh ngọt truyền thống, chè dưỡng nhan và kem mát lạnh', 'active'),
(7, 'Danh mục thử nghiệm nội bộ', 'Danh mục ẩn chỉ dùng để kiểm thử bộ lọc', 'hidden');

-- --------------------------------------------------------------------
-- 4. MÓN ĂN & ĐỒ UỐNG (25 món: 22 in_stock, 2 out_of_stock, 1 hidden)
-- --------------------------------------------------------------------
INSERT INTO `products` 
(`id`, `category_id`, `name`, `description`, `price`, `image`, `availability_status`) 
VALUES 
-- Danh mục 1: Khai vị & Salad
(1, 1, 'Gỏi ngó sen tôm thịt', 'Ngó sen giòn rụm kèm tôm sú tươi và thịt ba chỉ luộc, rắc đậu phộng thơm phức', 65000.00, 'goi-ngo-sen.jpg', 'in_stock'),
(2, 1, 'Chả giò hải sản giòn rụm', 'Chả giò cuốn tôm mực tươi ngon, chiên vàng ươm ăn kèm rau sống và sốt mayonnaise', 55000.00, 'cha-gio-hai-san.jpg', 'in_stock'),
(3, 1, 'Khoai tây chiên lắc phô mai', 'Khoai tây cọng giòn rụm phủ lớp bột phô mai hảo hạng béo ngậy', 35000.00, 'khoai-tay-chien.jpg', 'in_stock'),
(4, 1, 'Salad ức gà sốt mè rang', 'Xà lách romaine, cà chua bi, dưa leo cùng ức gà áp chảo và sốt mè rang Nhật', 49000.00, 'salad-uc-ga.jpg', 'in_stock'),

-- Danh mục 2: Cơm & Mì đặc sản
(5, 2, 'Cơm chiên hải sản hoàng kim', 'Hạt cơm tơi vàng óng ánh quyện cùng tôm, mực tươi, trứng muối thơm lừng', 75000.00, 'com-chien-hai-san.jpg', 'in_stock'),
(6, 2, 'Cơm sườn nướng mật ong', 'Sườn non ướp mật ong rừng nướng than hoa, kèm chả trứng và đồ chua nhà làm', 65000.00, 'com-suon-nuong.jpg', 'in_stock'),
(7, 2, 'Mì xào giòn thịt bò sốt tiêu', 'Sợi mì chiên phồng giòn tan phủ sốt bò tiêu đen đậm đà kèm rau cải xanh', 70000.00, 'mi-xao-bo.jpg', 'in_stock'),
(8, 2, 'Phở bò tái lăn đặc biệt', 'Nước dùng hầm xương bò 12 tiếng, thịt bò xào lăn thơm mùi tỏi và gừng tươi', 60000.00, 'pho-bo-tai-lan.jpg', 'in_stock'),
(9, 2, 'Cơm gà xối mỡ giòn bì', 'Gà thả vườn chiên xối mỡ giòn rụm bên ngoài mọng nước bên trong (Tạm hết)', 59000.00, 'com-ga-xoi-mo.jpg', 'out_of_stock'),

-- Danh mục 3: Món chính Bò & Gà
(10, 3, 'Bò lúc lắc khoai tây', 'Thịt thăn bò mềm ngọt xào lửa lớn cùng ớt chuông, hành tây và khoai tây múi cau', 120000.00, 'bo-luc-lac.jpg', 'in_stock'),
(11, 3, 'Bò né hoa thiên lý', 'Bò phi lê xào nhanh cùng hoa thiên lý đồng quê thơm ngát bổ dưỡng', 95000.00, 'bo-ne-thien-ly.jpg', 'in_stock'),
(12, 3, 'Gà nướng muối ớt Tây Bắc', 'Nửa con gà ta ướp hạt dổi mắc khén nướng than hồng thơm nức mũi', 135000.00, 'ga-nuong-muoi-ot.jpg', 'in_stock'),
(13, 3, 'Gà hấp lá chanh truyền thống', 'Thịt gà ta da vàng óng ngọt thịt chấm muối tiêu chanh chuẩn vị quê nhà', 110000.00, 'ga-hap-la-chanh.jpg', 'in_stock'),

-- Danh mục 4: Hải sản & Lẩu
(14, 4, 'Mực xào sa tế cay nồng', 'Mực ống tươi dày thịt xào ớt chuông với sa tế tôm cay nồng kích thích', 85000.00, 'muc-xao-sa-te.jpg', 'in_stock'),
(15, 4, 'Tôm sú hấp nước dừa', 'Tôm sú loại 1 hấp nước dừa xiêm ngọt thanh tự nhiên', 115000.00, 'tom-su-hap-dua.jpg', 'in_stock'),
(16, 4, 'Lẩu Thái hải sản chua cay', 'Nước lẩu Tom Yum đậm đà kèm tôm sú, mực, ngao, nấm và rau lẩu phong phú', 220000.00, 'lau-thai-hai-san.jpg', 'in_stock'),
(17, 4, 'Lẩu gà lá giang thơm ngát', 'Lẩu gà ta nấu cùng lá giang chua dịu ăn kèm bún tươi và hoa chuối', 195000.00, 'lau-ga-la-giang.jpg', 'in_stock'),
(18, 4, 'Cá chẽm sốt chanh dây', 'Cá chẽm phi lê chiên giòn sốt chanh dây chua ngọt hấp dẫn (Tạm hết)', 140000.00, 'ca-chem-chanh-day.jpg', 'out_of_stock'),

-- Danh mục 5: Đồ uống & Trà hoa quả
(19, 5, 'Trà đào cam sả thanh nhiệt', 'Trà đen ủ lạnh kết hợp đào miếng giòn ngọt, nước cam tươi và sả thơm ngát', 35000.00, 'tra-dao-cam-sa.jpg', 'in_stock'),
(20, 5, 'Trà sữa trân châu hoàng gia', 'Hồng trà pha sữa tươi béo ngậy kèm trân châu đen dai dẻo nấu mật mía', 39000.00, 'tra-sua-tran-chau.jpg', 'in_stock'),
(21, 5, 'Nước ép dưa hấu tươi', 'Dưa hấu nguyên chất không pha đường, giàu vitamin và giải nhiệt tức thì', 32000.00, 'nuoc-ep-dua-hau.jpg', 'in_stock'),
(22, 5, 'Sinh tố bơ sáp Đắk Lắk', 'Bơ sáp béo ngậy xay mịn cùng sữa đặc và sữa tươi dinh dưỡng', 45000.00, 'sinh-to-bo.jpg', 'in_stock'),
(23, 5, 'Cà phê sữa đá Sài Gòn', 'Cà phê Robusta pha phin đậm đặc hòa quyện cùng sữa đặc hảo hạng', 28000.00, 'ca-phe-sua-da.jpg', 'in_stock'),

-- Danh mục 6: Tráng miệng
(24, 6, 'Chè dưỡng nhan tuyết yến', 'Chè thanh mát bổ dưỡng gồm tuyết yến, nhựa đào, hạt sen, táo đỏ và kỷ tử', 35000.00, 'che-duong-nhan.jpg', 'in_stock'),
(25, 6, 'Bánh flan caramel cốt dừa', 'Bánh flan mềm mịn tan trong miệng cùng sốt cà phê caramel và nước cốt dừa', 25000.00, 'banh-flan-caramel.jpg', 'in_stock'),
(26, 6, 'Kem dừa Côn Đảo tươi mát', 'Kem dừa tươi bùi béo rắc đậu phộng rang giòn và dừa khô thơm lừng', 30000.00, 'kem-dua.jpg', 'in_stock'),

-- Danh mục 7: Món thử nghiệm ẩn
(27, 7, 'Món thử nghiệm nội bộ', 'Món ăn bảo mật dùng để kiểm thử nghiệp vụ ẩn món', 999000.00, 'hidden-food.jpg', 'hidden');

-- --------------------------------------------------------------------
-- 5. ĐƠN HÀNG MẪU (8 đơn hàng đại diện cho tất cả các trạng thái)
-- --------------------------------------------------------------------

-- Đơn 1: DELIVERED (Đã giao thành công -> Tính vào doanh thu: 175k + 15k = 190k)
INSERT INTO `orders` 
(`id`, `order_code`, `user_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `note`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`) 
VALUES 
(1, 'ORD-20261015-0001', 4, 'Trần Minh Tuấn', '0912345678', 'Tòa nhà Bitexco, Số 2 Hải Triều, Bến Nghé, Quận 1, TP.HCM', 'Giao tầng 12, gọi trước khi đến', 175000.00, 15000.00, 190000.00, 'COD', 'paid', 'DELIVERED', '2026-10-15 11:30:00');

-- Đơn 2: DELIVERED (Đã giao thành công -> Tính vào doanh thu: 393k + 15k = 408k)
INSERT INTO `orders` 
(`id`, `order_code`, `user_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `note`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`) 
VALUES 
(2, 'ORD-20261016-0002', 4, 'Trần Minh Tuấn', '0912345678', 'Số 45 Lê Duẩn, Bến Nghé, Quận 1, TP.HCM', 'Để trước cửa phòng lễ tân', 393000.00, 15000.00, 408000.00, 'COD', 'paid', 'DELIVERED', '2026-10-16 18:20:00');

-- Đơn 3: PENDING (Đơn mới đặt, chờ nhân viên xác nhận)
INSERT INTO `orders` 
(`id`, `order_code`, `user_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `note`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`) 
VALUES 
(3, 'ORD-20261017-0003', 5, 'Lê Thị Mai', '0923456789', 'Chung cư Hùng Vương Plaza, 126 Hồng Bàng, Phường 12, Quận 5', 'Đơn cần giao nhanh buổi trưa', 148000.00, 15000.00, 163000.00, 'COD', 'unpaid', 'PENDING', '2026-10-17 10:15:00');

-- Đơn 4: CONFIRMED (Nhân viên đã xác nhận, chờ chuyển xuống bếp)
INSERT INTO `orders` 
(`id`, `order_code`, `user_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `note`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`) 
VALUES 
(4, 'ORD-20261017-0004', 5, 'Lê Thị Mai', '0923456789', 'Số 88 An Dương Vương, Phường 9, Quận 5', 'Cho xin thêm ớt và tương ớt', 155000.00, 15000.00, 170000.00, 'COD', 'unpaid', 'CONFIRMED', '2026-10-17 09:40:00');

-- Đơn 5: PREPARING (Bếp đang chuẩn bị chế biến)
INSERT INTO `orders` 
(`id`, `order_code`, `user_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `note`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`) 
VALUES 
(5, 'ORD-20261017-0005', 5, 'Lê Thị Mai', '0923456789', 'Số 15 Trần Hưng Đạo, Phường Cầu Ông Lãnh, Quận 1', 'Nước lẩu làm ít cay giùm', 284000.00, 15000.00, 299000.00, 'COD', 'unpaid', 'PREPARING', '2026-10-17 09:10:00');

-- Đơn 6: READY (Bếp đã xong, chuẩn bị bàn giao giao hàng)
INSERT INTO `orders` 
(`id`, `order_code`, `user_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `note`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`) 
VALUES 
(6, 'ORD-20261017-0006', 6, 'Phạm Hoàng Nam', '0934567890', 'Số 220 Nguyễn Tri Phương, Phường 4, Quận 10', 'Giao đúng giờ', 120000.00, 15000.00, 135000.00, 'COD', 'unpaid', 'READY', '2026-10-17 08:50:00');

-- Đơn 7: REJECTED (Bị từ chối bởi nhân viên -> KHÔNG tính vào doanh thu)
INSERT INTO `orders` 
(`id`, `order_code`, `user_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `note`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `rejection_reason`, `created_at`) 
VALUES 
(7, 'ORD-20261016-0007', 6, 'Phạm Hoàng Nam', '0934567890', 'Số 91 CMT8, Bến Thành, Quận 1', 'Giao trước 12h trưa', 140000.00, 15000.00, 155000.00, 'COD', 'unpaid', 'REJECTED', 'Nhà hàng tạm hết cá chẽm tươi trong ngày, xin quý khách thông cảm đổi món khác.', '2026-10-16 11:00:00');

-- Đơn 8: CANCELLED (Khách hàng tự hủy đơn khi PENDING -> KHÔNG tính vào doanh thu)
INSERT INTO `orders` 
(`id`, `order_code`, `user_id`, `recipient_name`, `recipient_phone`, `delivery_address`, `note`, `subtotal`, `shipping_fee`, `total_amount`, `payment_method`, `payment_status`, `order_status`, `created_at`) 
VALUES 
(8, 'ORD-20261017-0008', 6, 'Phạm Hoàng Nam', '0934567890', 'Số 12 Lý Thường Kiệt, Quận 10', 'Khách đổi địa chỉ nên hủy đặt lại', 65000.00, 15000.00, 80000.00, 'COD', 'unpaid', 'CANCELLED', '2026-10-17 07:30:00');

-- --------------------------------------------------------------------
-- 6. CHI TIẾT MÓN ĂN TRONG ĐƠN (Snapshot giá tại thời điểm đặt)
-- --------------------------------------------------------------------
INSERT INTO `order_items` 
(`id`, `order_id`, `product_id`, `product_name_snapshot`, `unit_price`, `quantity`, `line_total`) 
VALUES 
-- Đơn 1
(1, 1, 1, 'Gỏi ngó sen tôm thịt', 65000.00, 1, 65000.00),
(2, 1, 5, 'Cơm chiên hải sản hoàng kim', 75000.00, 1, 75000.00),
(3, 1, 19, 'Trà đào cam sả thanh nhiệt', 35000.00, 1, 35000.00),

-- Đơn 2
(4, 2, 10, 'Bò lúc lắc khoai tây', 120000.00, 1, 120000.00),
(5, 2, 17, 'Lẩu gà lá giang thơm ngát', 195000.00, 1, 195000.00),
(6, 2, 20, 'Trà sữa trân châu hoàng gia', 39000.00, 2, 78000.00),

-- Đơn 3
(7, 3, 8, 'Phở bò tái lăn đặc biệt', 60000.00, 2, 120000.00),
(8, 3, 23, 'Cà phê sữa đá Sài Gòn', 28000.00, 1, 28000.00),

-- Đơn 4
(9, 4, 2, 'Chả giò hải sản giòn rụm', 55000.00, 2, 110000.00),
(10, 4, 22, 'Sinh tố bơ sáp Đắk Lắk', 45000.00, 1, 45000.00),

-- Đơn 5
(11, 5, 16, 'Lẩu Thái hải sản chua cay', 220000.00, 1, 220000.00),
(12, 5, 21, 'Nước ép dưa hấu tươi', 32000.00, 2, 64000.00),

-- Đơn 6
(13, 6, 7, 'Mì xào giòn thịt bò sốt tiêu', 70000.00, 1, 70000.00),
(14, 6, 25, 'Bánh flan caramel cốt dừa', 25000.00, 2, 50000.00),

-- Đơn 7 (Đơn từ chối)
(15, 7, 18, 'Cá chẽm sốt chanh dây', 140000.00, 1, 140000.00),

-- Đơn 8 (Đơn hủy)
(16, 8, 6, 'Cơm sườn nướng mật ong', 65000.00, 1, 65000.00);

-- --------------------------------------------------------------------
-- 7. NHẬT KÝ THAY ĐỔI TRẠNG THÁI ĐƠN HÀNG (Audit Logs)
-- --------------------------------------------------------------------
INSERT INTO `order_status_history` 
(`order_id`, `old_status`, `new_status`, `changed_by`, `note`, `created_at`) 
VALUES 
-- Đơn 1 tiến trình đầy đủ tới DELIVERED
(1, NULL, 'PENDING', 4, 'Khách hàng tạo đơn COD thành công', '2026-10-15 11:30:00'),
(1, 'PENDING', 'CONFIRMED', 2, 'Nhân viên xác nhận đơn', '2026-10-15 11:32:00'),
(1, 'CONFIRMED', 'PREPARING', 2, 'Bếp bắt đầu chế biến món', '2026-10-15 11:35:00'),
(1, 'PREPARING', 'READY', 2, 'Món đã hoàn thành, chờ shipper', '2026-10-15 11:50:00'),
(1, 'READY', 'DELIVERED', 2, 'Đã giao thành công đến khách và thu tiền COD', '2026-10-15 12:15:00'),

-- Đơn 2 tiến trình đầy đủ tới DELIVERED
(2, NULL, 'PENDING', 4, 'Khách hàng tạo đơn COD thành công', '2026-10-16 18:20:00'),
(2, 'PENDING', 'CONFIRMED', 2, 'Nhân viên xác nhận đơn', '2026-10-16 18:22:00'),
(2, 'CONFIRMED', 'PREPARING', 2, 'Bếp bắt đầu chuẩn bị lẩu và món bò', '2026-10-16 18:25:00'),
(2, 'PREPARING', 'READY', 2, 'Sẵn sàng giao', '2026-10-16 18:45:00'),
(2, 'READY', 'DELIVERED', 2, 'Giao hàng thành công', '2026-10-16 19:10:00'),

-- Đơn 3: PENDING
(3, NULL, 'PENDING', 5, 'Khách hàng tạo đơn COD thành công', '2026-10-17 10:15:00'),

-- Đơn 4: CONFIRMED
(4, NULL, 'PENDING', 5, 'Khách hàng tạo đơn COD thành công', '2026-10-17 09:40:00'),
(4, 'PENDING', 'CONFIRMED', 2, 'Nhân viên xác nhận đơn hàng', '2026-10-17 09:42:00'),

-- Đơn 5: PREPARING
(5, NULL, 'PENDING', 5, 'Khách hàng tạo đơn COD thành công', '2026-10-17 09:10:00'),
(5, 'PENDING', 'CONFIRMED', 2, 'Nhân viên xác nhận đơn hàng', '2026-10-17 09:12:00'),
(5, 'CONFIRMED', 'PREPARING', 3, 'Bếp đang nấu lẩu Thái', '2026-10-17 09:15:00'),

-- Đơn 6: READY
(6, NULL, 'PENDING', 6, 'Khách hàng tạo đơn COD thành công', '2026-10-17 08:50:00'),
(6, 'PENDING', 'CONFIRMED', 2, 'Nhân viên xác nhận đơn hàng', '2026-10-17 08:52:00'),
(6, 'CONFIRMED', 'PREPARING', 3, 'Bếp nấu mì xào bò', '2026-10-17 08:55:00'),
(6, 'PREPARING', 'READY', 2, 'Món ăn đã đóng gói xong', '2026-10-17 09:05:00'),

-- Đơn 7: REJECTED
(7, NULL, 'PENDING', 6, 'Khách hàng tạo đơn COD thành công', '2026-10-16 11:00:00'),
(7, 'PENDING', 'REJECTED', 2, 'Nhà hàng tạm hết cá chẽm tươi trong ngày, xin quý khách thông cảm đổi món khác.', '2026-10-16 11:05:00'),

-- Đơn 8: CANCELLED
(8, NULL, 'PENDING', 6, 'Khách hàng tạo đơn COD thành công', '2026-10-17 07:30:00'),
(8, 'PENDING', 'CANCELLED', 6, 'Khách hàng tự hủy đơn khi đơn còn ở trạng thái PENDING', '2026-10-17 07:35:00');

-- --------------------------------------------------------------------
-- 8. NHẬT KÝ CHATBOT MẪU
-- --------------------------------------------------------------------
INSERT INTO `chatbot_logs` 
(`id`, `user_id`, `session_id`, `user_message`, `bot_reply`, `mode`, `created_at`) 
VALUES 
(1, 4, 'sess_demo_001', 'Có món nào dưới 50.000 đồng không?', 'Chào bạn! Nhà hàng Trần Lộc có các món dưới 50.000đ như: Khoai tây chiên lắc phô mai (35.000đ), Salad ức gà (49.000đ), Nước ép dưa hấu tươi (32.000đ), Cà phê sữa đá (28.000đ), Bánh flan caramel cốt dừa (25.000đ), Kem dừa Côn Đảo (30.000đ). Bạn muốn đặt món nào không ạ?', 'mock', '2026-10-17 09:00:00');
