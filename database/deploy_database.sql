-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: restaurant_ordering_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','hidden') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_categories_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Khai vị & Salad','Các món khai vị thanh mát, kích thích vị giác đầu bữa tiệc','active','2026-10-17 09:41:41'),(2,'Cơm & Mì đặc sản','Món ăn no truyền thống kết hợp hương vị ẩm thực Việt','active','2026-10-17 09:41:41'),(3,'Món chính Bò & Gà','Thịt bò tươi mềm và thịt gà ta thả vườn chế biến đậm đà','active','2026-10-17 09:41:41'),(4,'Hải sản & Lẩu','Hải sản tươi sống trong ngày cùng các loại lẩu thơm ngon','active','2026-10-17 09:41:41'),(5,'Đồ uống & Trà hoa quả','Nước ép trái cây tươi, trà thanh nhiệt giải khát','active','2026-10-17 09:41:41'),(6,'Tráng miệng','Bánh ngọt truyền thống, chè dưỡng nhan và kem mát lạnh','active','2026-10-17 09:41:41'),(7,'Danh mục thử nghiệm nội bộ','Danh mục ẩn chỉ dùng để kiểm thử bộ lọc','hidden','2026-10-17 09:41:41');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chatbot_logs`
--

DROP TABLE IF EXISTS `chatbot_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `chatbot_logs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `user_message` text NOT NULL,
  `bot_reply` text NOT NULL,
  `mode` enum('ai','mock') NOT NULL DEFAULT 'mock',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_chatbot_user` (`user_id`),
  KEY `idx_chatbot_session` (`session_id`),
  KEY `idx_chatbot_created` (`created_at`),
  CONSTRAINT `fk_chatbot_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chatbot_logs`
--

LOCK TABLES `chatbot_logs` WRITE;
/*!40000 ALTER TABLE `chatbot_logs` DISABLE KEYS */;
INSERT INTO `chatbot_logs` VALUES (1,4,'sess_demo_001','Có món nào dưới 50.000 đồng không?','Chào bạn! Nhà hàng Hương Sen có các món dưới 50.000đ như: Khoai tây chiên lắc phô mai (35.000đ), Salad ức gà (49.000đ), Nước ép dưa hấu tươi (32.000đ), Cà phê sữa đá (28.000đ), Bánh flan caramel cốt dừa (25.000đ), Kem dừa Côn Đảo (30.000đ). Bạn muốn đặt món nào không ạ?','mock','2026-10-17 02:00:00'),(2,NULL,'test_sess_1792347140','Có món nào dưới 50.000đ không?','💡 **[Chế độ Thử nghiệm - Chưa cấu hình API Key]**\nHệ thống đang phản hồi ở chế độ mẫu dựa trên dữ liệu thực tế trong Database. Để kích hoạt Trợ lý AI hội thoại thực sự với Google Gemini, bạn chỉ cần mở tệp `.env` và thêm: `GEMINI_API_KEY=your_gemini_key_here`.\n\nDạ, Nhà Hàng Trần Lộc đang phục vụ thực đơn phong phú với các món nổi bật:\n\n• <strong>Khoai tây chiên lắc phô mai</strong> - 35.000đ\n• <strong>Salad ức gà sốt mè rang</strong> - 49.000đ\n• <strong>Chả giò hải sản giòn rụm</strong> - 55.000đ\n• <strong>Gỏi ngó sen tôm thịt</strong> - 65.000đ\n\nBạn có thể hỏi mình: <em>\'Tư vấn món ăn cho 4 người\'</em>, <em>\'Có món nào dưới 50k không?\'</em>, hoặc <em>\'Phí giao hàng thế nào?\'</em> nhé!','mock','2026-10-18 18:12:20'),(3,NULL,'test_sess_1792347140','Quán có bán pizza hải sản hoặc sushi không?','💡 **[Chế độ Thử nghiệm - Chưa cấu hình API Key]**\nHệ thống đang phản hồi ở chế độ mẫu dựa trên dữ liệu thực tế trong Database. Để kích hoạt Trợ lý AI hội thoại thực sự với Google Gemini, bạn chỉ cần mở tệp `.env` và thêm: `GEMINI_API_KEY=your_gemini_key_here`.\n\nDạ, gợi ý các món thuộc nhóm <strong>Hải sản & Lẩu</strong> đang được nhiều thực khách yêu thích tại Nhà Hàng Trần Lộc:\n\n• <strong>Mực xào sa tế cay nồng</strong>: 85.000đ\n• <strong>Tôm sú hấp nước dừa</strong>: 115.000đ\n• <strong>Lẩu gà lá giang thơm ngát</strong>: 195.000đ\n• <strong>Lẩu Thái hải sản chua cay</strong>: 220.000đ\n\nBạn muốn mình tư vấn thêm chi tiết về hương vị món nào không ạ?','mock','2026-10-18 18:12:20'),(6,NULL,'err_sess_1792347325','Xin chào','⚠️ **[Lỗi kết nối Trợ lý AI]**: API Key Google Gemini không hợp lệ hoặc không có quyền truy cập. Vui lòng kiểm tra lại cấu hình GEMINI_API_KEY trong tệp .env.\n\nDưới đây là câu trả lời tạm thời dựa trên thực đơn có sẵn của quán:\n\nChào bạn! Mình là Trợ lý món ngon của <strong>Nhà Hàng Trần Lộc</strong> 🍲.\n\nHôm nay bạn muốn thưởng thức món gì? Bạn có thể hỏi mình gợi ý món ngon cho bữa trưa, món lẩu hải sản, hoặc món ăn theo ngân sách nhé!','mock','2026-10-18 18:15:25'),(7,NULL,'sess_3a3ec4054ec2e8e5','Xin chào nhà hàng','💡 **[Chế độ Thử nghiệm - Chưa cấu hình API Key]**\nHệ thống đang phản hồi ở chế độ mẫu dựa trên dữ liệu thực tế trong Database. Để kích hoạt Trợ lý AI hội thoại thực sự với Google Gemini, bạn chỉ cần mở tệp `.env` và thêm: `GEMINI_API_KEY=your_gemini_key_here`.\n\nChào bạn! Mình là Trợ lý món ngon của <strong>Nhà Hàng Trần Lộc</strong> 🍲.\n\nHôm nay bạn muốn thưởng thức món gì? Bạn có thể hỏi mình gợi ý món ngon cho bữa trưa, món lẩu hải sản, hoặc món ăn theo ngân sách nhé!','mock','2026-10-18 18:24:17'),(8,NULL,'sess_f96e6afe95bd2b0d','Gợi ý món lẩu ngon','💡 **[Chế độ Thử nghiệm - Chưa cấu hình API Key]**\nHệ thống đang phản hồi ở chế độ mẫu dựa trên dữ liệu thực tế trong Database. Để kích hoạt Trợ lý AI hội thoại thực sự với Google Gemini, bạn chỉ cần mở tệp `.env` và thêm: `GEMINI_API_KEY=your_gemini_key_here`.\n\nDạ, gợi ý các món thuộc nhóm <strong>Hải sản & Lẩu</strong> đang được nhiều thực khách yêu thích tại Nhà Hàng Trần Lộc:\n\n• <strong>Mực xào sa tế cay nồng</strong>: 85.000đ\n• <strong>Tôm sú hấp nước dừa</strong>: 115.000đ\n• <strong>Lẩu gà lá giang thơm ngát</strong>: 195.000đ\n• <strong>Lẩu Thái hải sản chua cay</strong>: 220.000đ\n\nBạn muốn mình tư vấn thêm chi tiết về hương vị món nào không ạ?','mock','2026-10-18 18:24:49'),(10,11,'sess_b8396349cff825b8','món nào ít cay nhỉ','💡 **[Chế độ Thử nghiệm - Chưa cấu hình API Key]**\nHệ thống đang phản hồi ở chế độ mẫu dựa trên dữ liệu thực tế trong Database. Để kích hoạt Trợ lý AI hội thoại thực sự với Google Gemini, bạn chỉ cần mở tệp `.env` và thêm: `GEMINI_API_KEY=your_gemini_key_here`.\n\nDạ, Nhà Hàng Trần Lộc đang phục vụ thực đơn phong phú với các món nổi bật:\n\n• <strong>Khoai tây chiên lắc phô mai</strong> - 35.000đ\n• <strong>Salad ức gà sốt mè rang</strong> - 49.000đ\n• <strong>Chả giò hải sản giòn rụm</strong> - 55.000đ\n• <strong>Gỏi ngó sen tôm thịt</strong> - 65.000đ\n\nBạn có thể hỏi mình: <em>\'Tư vấn món ăn cho 4 người\'</em>, <em>\'Có món nào dưới 50k không?\'</em>, hoặc <em>\'Phí giao hàng thế nào?\'</em> nhé!','mock','2026-10-18 18:28:43');
/*!40000 ALTER TABLE `chatbot_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_items` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned DEFAULT NULL,
  `product_name_snapshot` varchar(150) NOT NULL,
  `unit_price` decimal(12,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 1,
  `line_total` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_product` (`product_id`),
  KEY `idx_order_items_order` (`order_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,1,'Gỏi ngó sen tôm thịt',65000.00,1,65000.00),(2,1,5,'Cơm chiên hải sản hoàng kim',75000.00,1,75000.00),(3,1,19,'Trà đào cam sả thanh nhiệt',35000.00,1,35000.00),(4,2,10,'Bò lúc lắc khoai tây',120000.00,1,120000.00),(5,2,17,'Lẩu gà lá giang thơm ngát',195000.00,1,195000.00),(6,2,20,'Trà sữa trân châu hoàng gia',39000.00,2,78000.00),(7,3,8,'Phở bò tái lăn đặc biệt',60000.00,2,120000.00),(8,3,23,'Cà phê sữa đá Sài Gòn',28000.00,1,28000.00),(9,4,2,'Chả giò hải sản giòn rụm',55000.00,2,110000.00),(10,4,22,'Sinh tố bơ sáp Đắk Lắk',45000.00,1,45000.00),(11,5,16,'Lẩu Thái hải sản chua cay',220000.00,1,220000.00),(12,5,21,'Nước ép dưa hấu tươi',32000.00,2,64000.00),(13,6,7,'Mì xào giòn thịt bò sốt tiêu',70000.00,1,70000.00),(14,6,25,'Bánh flan caramel cốt dừa',25000.00,2,50000.00),(15,7,18,'Cá chẽm sốt chanh dây',140000.00,1,140000.00),(16,8,6,'Cơm sườn nướng mật ong',65000.00,1,65000.00),(17,9,1,'Gỏi ngó sen tôm thịt',65000.00,1,65000.00),(18,10,2,'Chả giò hải sản giòn rụm',55000.00,1,55000.00),(19,10,3,'Khoai tây chiên lắc phô mai',35000.00,1,35000.00),(20,11,2,'Chả giò hải sản giòn rụm',55000.00,1,55000.00),(21,11,1,'Gỏi ngó sen tôm thịt',65000.00,1,65000.00),(22,12,1,'Gỏi ngó sen tôm thịt',65000.00,1,65000.00),(23,12,2,'Chả giò hải sản giòn rụm',55000.00,1,55000.00),(24,13,1,'Gỏi ngó sen tôm thịt',65000.00,1,65000.00),(25,13,2,'Chả giò hải sản giòn rụm',55000.00,1,55000.00),(26,14,1,'Gỏi ngó sen tôm thịt',65000.00,1,65000.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_status_history` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `changed_by` int(10) unsigned NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_history_user` (`changed_by`),
  KEY `idx_history_order` (`order_id`),
  CONSTRAINT `fk_history_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_history_user` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=53 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_status_history`
--

LOCK TABLES `order_status_history` WRITE;
/*!40000 ALTER TABLE `order_status_history` DISABLE KEYS */;
INSERT INTO `order_status_history` VALUES (1,1,NULL,'PENDING',4,'Khách hàng tạo đơn COD thành công','2026-10-15 04:30:00'),(2,1,'PENDING','CONFIRMED',2,'Nhân viên xác nhận đơn','2026-10-15 04:32:00'),(3,1,'CONFIRMED','PREPARING',2,'Bếp bắt đầu chế biến món','2026-10-15 04:35:00'),(4,1,'PREPARING','READY',2,'Món đã hoàn thành, chờ shipper','2026-10-15 04:50:00'),(5,1,'READY','DELIVERED',2,'Đã giao thành công đến khách và thu tiền COD','2026-10-15 05:15:00'),(6,2,NULL,'PENDING',4,'Khách hàng tạo đơn COD thành công','2026-10-16 11:20:00'),(7,2,'PENDING','CONFIRMED',2,'Nhân viên xác nhận đơn','2026-10-16 11:22:00'),(8,2,'CONFIRMED','PREPARING',2,'Bếp bắt đầu chuẩn bị lẩu và món bò','2026-10-16 11:25:00'),(9,2,'PREPARING','READY',2,'Sẵn sàng giao','2026-10-16 11:45:00'),(10,2,'READY','DELIVERED',2,'Giao hàng thành công','2026-10-16 12:10:00'),(11,3,NULL,'PENDING',5,'Khách hàng tạo đơn COD thành công','2026-10-17 03:15:00'),(12,4,NULL,'PENDING',5,'Khách hàng tạo đơn COD thành công','2026-10-17 02:40:00'),(13,4,'PENDING','CONFIRMED',2,'Nhân viên xác nhận đơn hàng','2026-10-17 02:42:00'),(14,5,NULL,'PENDING',5,'Khách hàng tạo đơn COD thành công','2026-10-17 02:10:00'),(15,5,'PENDING','CONFIRMED',2,'Nhân viên xác nhận đơn hàng','2026-10-17 02:12:00'),(16,5,'CONFIRMED','PREPARING',3,'Bếp đang nấu lẩu Thái','2026-10-17 02:15:00'),(17,6,NULL,'PENDING',6,'Khách hàng tạo đơn COD thành công','2026-10-17 01:50:00'),(18,6,'PENDING','CONFIRMED',2,'Nhân viên xác nhận đơn hàng','2026-10-17 01:52:00'),(19,6,'CONFIRMED','PREPARING',3,'Bếp nấu mì xào bò','2026-10-17 01:55:00'),(20,6,'PREPARING','READY',2,'Món ăn đã đóng gói xong','2026-10-17 02:05:00'),(21,7,NULL,'PENDING',6,'Khách hàng tạo đơn COD thành công','2026-10-16 04:00:00'),(22,7,'PENDING','REJECTED',2,'Nhà hàng tạm hết cá chẽm tươi trong ngày, xin quý khách thông cảm đổi món khác.','2026-10-16 04:05:00'),(23,8,NULL,'PENDING',6,'Khách hàng tạo đơn COD thành công','2026-10-17 00:30:00'),(24,8,'PENDING','CANCELLED',6,'Khách hàng tự hủy đơn khi đơn còn ở trạng thái PENDING','2026-10-17 00:35:00'),(25,6,'READY','DELIVERED',1,'Chuyển trạng thái sang DELIVERED','2026-10-17 09:47:37'),(26,5,'PREPARING','READY',1,'Chuyển trạng thái sang READY','2026-10-17 09:47:38'),(27,5,'READY','DELIVERED',1,'Chuyển trạng thái sang DELIVERED','2026-10-17 09:47:39'),(28,4,'CONFIRMED','PREPARING',1,'Chuyển trạng thái sang PREPARING','2026-10-17 09:47:40'),(29,3,'PENDING','CONFIRMED',1,'Chuyển trạng thái sang CONFIRMED','2026-10-17 09:47:41'),(30,9,NULL,'PENDING',4,'Khách hàng tạo đơn hàng COD mới','2026-10-17 09:48:55'),(31,9,'PENDING','REJECTED',2,'Địa chỉ chưa đủ rõ ràng','2026-10-17 09:49:38'),(32,3,'CONFIRMED','PREPARING',2,'Chuyển trạng thái sang PREPARING','2026-10-17 09:50:17'),(33,4,'PREPARING','READY',2,'Chuyển trạng thái sang READY','2026-10-17 09:50:19'),(34,4,'READY','DELIVERED',2,'Chuyển trạng thái sang DELIVERED','2026-10-17 09:50:24'),(35,3,'PREPARING','READY',2,'Chuyển trạng thái sang READY','2026-10-17 09:54:50'),(36,3,'READY','DELIVERED',2,'Chuyển trạng thái sang DELIVERED','2026-10-17 09:54:51'),(37,10,NULL,'PENDING',4,'Khách hàng tạo đơn hàng COD mới','2026-10-17 10:51:43'),(38,10,'PENDING','CONFIRMED',1,'Chuyển trạng thái sang CONFIRMED','2026-10-17 10:52:20'),(39,10,'CONFIRMED','PREPARING',1,'Chuyển trạng thái sang PREPARING','2026-10-17 10:52:28'),(40,10,'PREPARING','READY',1,'Chuyển trạng thái sang READY','2026-10-17 10:52:32'),(41,10,'READY','DELIVERED',1,'Chuyển trạng thái sang DELIVERED','2026-10-17 10:52:36'),(42,11,NULL,'PENDING',11,'Khách hàng tạo đơn hàng COD mới','2026-10-18 08:57:37'),(43,11,'PENDING','CONFIRMED',1,'Chuyển trạng thái sang CONFIRMED','2026-10-18 08:57:49'),(44,11,'CONFIRMED','PREPARING',1,'Chuyển trạng thái sang PREPARING','2026-10-18 08:57:55'),(45,11,'PREPARING','READY',1,'Chuyển trạng thái sang READY','2026-10-18 08:57:59'),(46,11,'READY','DELIVERED',1,'Chuyển trạng thái sang DELIVERED','2026-10-18 08:58:04'),(47,12,NULL,'PENDING',4,'Khách hàng tạo đơn hàng COD mới','2026-10-18 11:47:51'),(48,12,'PENDING','CANCELLED',4,'Khách hủy: Test hủy đơn','2026-10-18 11:47:51'),(49,13,NULL,'PENDING',4,'Khách hàng tạo đơn hàng COD mới','2026-10-18 11:48:30'),(50,13,'PENDING','CANCELLED',4,'Khách hủy: Test hủy đơn','2026-10-18 11:48:30'),(51,14,NULL,'PENDING',11,'Khách hàng tạo đơn hàng COD mới','2026-10-18 11:52:52'),(52,14,'PENDING','CANCELLED',11,'Khách hàng tự hủy đơn khi đang chờ duyệt','2026-10-18 11:53:04');
/*!40000 ALTER TABLE `order_status_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_code` varchar(32) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `recipient_name` varchar(100) NOT NULL,
  `recipient_phone` varchar(20) NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `note` text DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('COD') NOT NULL DEFAULT 'COD',
  `payment_status` enum('unpaid','paid') NOT NULL DEFAULT 'unpaid',
  `order_status` enum('PENDING','CONFIRMED','PREPARING','READY','DELIVERED','REJECTED','CANCELLED') NOT NULL DEFAULT 'PENDING',
  `rejection_reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_code` (`order_code`),
  KEY `idx_orders_code` (`order_code`),
  KEY `idx_orders_user` (`user_id`),
  KEY `idx_orders_status` (`order_status`),
  KEY `idx_orders_created` (`created_at`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'ORD-20261015-0001',4,'Trần Minh Tuấn','0912345678','Tòa nhà Bitexco, Số 2 Hải Triều, Bến Nghé, Quận 1, TP.HCM','Giao tầng 12, gọi trước khi đến',175000.00,15000.00,190000.00,'COD','paid','DELIVERED',NULL,'2026-10-15 04:30:00','2026-10-17 09:41:41'),(2,'ORD-20261016-0002',4,'Trần Minh Tuấn','0912345678','Số 45 Lê Duẩn, Bến Nghé, Quận 1, TP.HCM','Để trước cửa phòng lễ tân',393000.00,15000.00,408000.00,'COD','paid','DELIVERED',NULL,'2026-10-16 11:20:00','2026-10-17 09:41:41'),(3,'ORD-20261017-0003',5,'Lê Thị Mai','0923456789','Chung cư Hùng Vương Plaza, 126 Hồng Bàng, Phường 12, Quận 5','Đơn cần giao nhanh buổi trưa',148000.00,15000.00,163000.00,'COD','paid','DELIVERED',NULL,'2026-10-17 03:15:00','2026-10-17 09:54:51'),(4,'ORD-20261017-0004',5,'Lê Thị Mai','0923456789','Số 88 An Dương Vương, Phường 9, Quận 5','Cho xin thêm ớt và tương ớt',155000.00,15000.00,170000.00,'COD','paid','DELIVERED',NULL,'2026-10-17 02:40:00','2026-10-17 09:50:24'),(5,'ORD-20261017-0005',5,'Lê Thị Mai','0923456789','Số 15 Trần Hưng Đạo, Phường Cầu Ông Lãnh, Quận 1','Nước lẩu làm ít cay giùm',284000.00,15000.00,299000.00,'COD','paid','DELIVERED',NULL,'2026-10-17 02:10:00','2026-10-17 09:47:39'),(6,'ORD-20261017-0006',6,'Phạm Hoàng Nam','0934567890','Số 220 Nguyễn Tri Phương, Phường 4, Quận 10','Giao đúng giờ',120000.00,15000.00,135000.00,'COD','paid','DELIVERED',NULL,'2026-10-17 01:50:00','2026-10-17 09:47:37'),(7,'ORD-20261016-0007',6,'Phạm Hoàng Nam','0934567890','Số 91 CMT8, Bến Thành, Quận 1','Giao trước 12h trưa',140000.00,15000.00,155000.00,'COD','unpaid','REJECTED','Nhà hàng tạm hết cá chẽm tươi trong ngày, xin quý khách thông cảm đổi món khác.','2026-10-16 04:00:00','2026-10-17 09:41:41'),(8,'ORD-20261017-0008',6,'Phạm Hoàng Nam','0934567890','Số 12 Lý Thường Kiệt, Quận 10','Khách đổi địa chỉ nên hủy đặt lại',65000.00,15000.00,80000.00,'COD','unpaid','CANCELLED',NULL,'2026-10-17 00:30:00','2026-10-17 09:41:41'),(9,'ORD-20261017-8EFD02',4,'Trần Minh Tuấn (Customer 1)','0912345678','02 Nguyễn Như Hạnh','',65000.00,15000.00,80000.00,'COD','unpaid','REJECTED','Địa chỉ chưa đủ rõ ràng','2026-10-17 09:48:55','2026-10-17 09:49:38'),(10,'ORD-20261017-F4EA1A',4,'Trần Minh Tuấn (Customer 1)','0912345678','02 Nguyễn Như Hạnh','',90000.00,15000.00,105000.00,'COD','paid','DELIVERED',NULL,'2026-10-17 10:51:43','2026-10-17 10:52:36'),(11,'ORD-20261018-8E610C',11,'Trần Văn Lộc','0778591167','02 Nguyễn Như Hạnh','Ít cay',120000.00,20000.00,140000.00,'COD','paid','DELIVERED',NULL,'2026-10-18 08:57:37','2026-10-18 08:58:04'),(12,'ORD-20261018-D8CA5D',4,'Trần Lộc Test','0778591167','150 Nguyễn Như Hạnh, Đà Nẵng','Test tạo đơn tự động',120000.00,20000.00,140000.00,'COD','unpaid','CANCELLED',NULL,'2026-10-18 11:47:51','2026-10-18 11:47:51'),(13,'ORD-20261018-9DA593',4,'Trần Lộc Test','0778591167','150 Nguyễn Như Hạnh, Đà Nẵng','Test tạo đơn tự động',120000.00,20000.00,140000.00,'COD','unpaid','CANCELLED',NULL,'2026-10-18 11:48:30','2026-10-18 11:48:30'),(14,'ORD-20261018-5772E4',11,'Trần Văn Lộc','0778591167','02 Nguyễn Như Hạnh','',65000.00,20000.00,85000.00,'COD','unpaid','CANCELLED',NULL,'2026-10-18 11:52:52','2026-10-18 11:53:04');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(10) unsigned NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `availability_status` enum('in_stock','out_of_stock','hidden') NOT NULL DEFAULT 'in_stock',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_availability` (`availability_status`),
  KEY `idx_products_price` (`price`),
  KEY `idx_products_name` (`name`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'Gỏi ngó sen tôm thịt','Ngó sen giòn rụm kèm tôm sú tươi và thịt ba chỉ luộc, rắc đậu phộng thơm phức',65000.00,'https://cooponline.vn/tin-tuc/wp-content/uploads/2025/10/goi-ngo-sen-tom-thit-cong-thuc-lam-mon-goi-thanh-mat-gion-ngot-chuan-vi-viet.png','in_stock','2026-10-17 09:41:41','2026-10-17 10:37:15'),(2,1,'Chả giò hải sản giòn rụm','Chả giò cuốn tôm mực tươi ngon, chiên vàng ươm ăn kèm rau sống và sốt mayonnaise',55000.00,'https://i.ytimg.com/vi/WBrEtVEEV8Y/hq720.jpg?sqp=-oaymwEhCK4FEIIDSFryq4qpAxMIARUAAAAAGAElAADIQj0AgKJD&rs=AOn4CLCqua-WKYDsPDJvU5_ZhY5txUZLrg','in_stock','2026-10-17 09:41:41','2026-10-17 10:38:04'),(3,1,'Khoai tây chiên lắc phô mai','Khoai tây cọng giòn rụm phủ lớp bột phô mai hảo hạng béo ngậy',35000.00,'https://daylambanh.edu.vn/wp-content/uploads/2020/02/khoai-tay-lac-pho-mai.jpg','in_stock','2026-10-17 09:41:41','2026-10-17 10:39:42'),(4,1,'Salad ức gà sốt mè rang','Xà lách romaine, cà chua bi, dưa leo cùng ức gà áp chảo và sốt mè rang Nhật',49000.00,'https://th.bing.com/th/id/OIP.ZCV0yCg-c6xyrufVC-H6NAHaFL?r=0&o=7rm=3&rs=1&pid=ImgDetMain&o=7&rm=3','in_stock','2026-10-17 09:41:41','2026-10-17 10:40:44'),(5,2,'Cơm chiên hải sản hoàng kim','Hạt cơm tơi vàng óng ánh quyện cùng tôm, mực tươi, trứng muối thơm lừng',75000.00,'https://vuongquocruou.vn/wp-content/uploads/2025/03/com-chien-hai-san.png','in_stock','2026-10-17 09:41:41','2026-10-17 10:47:40'),(6,2,'Cơm sườn nướng mật ong','Sườn non ướp mật ong rừng nướng than hoa, kèm chả trứng và đồ chua nhà làm',65000.00,'https://image-us.eva.vn/upload/4-2019/images/2019-10-30/cuoi-tuan-me-lam-com-tam-suon-nuong-mat-ong-ca-nha-xuyt-xoa-khen-ngon-74164457_447046746001520_4124878074158252032_n-1572407677-670-width700height520.jpg','in_stock','2026-10-17 09:41:41','2026-10-17 10:49:38'),(7,2,'Mì xào giòn thịt bò sốt tiêu','Sợi mì chiên phồng giòn tan phủ sốt bò tiêu đen đậm đà kèm rau cải xanh',70000.00,'https://product.hstatic.net/200000408931/product/image005_c127ee2965f24ab9941bbe403364c580_master.png','in_stock','2026-10-17 09:41:41','2026-10-17 10:50:42'),(8,2,'Phở bò tái lăn đặc biệt','Nước dùng hầm xương bò 12 tiếng, thịt bò xào lăn thơm mùi tỏi và gừng tươi',60000.00,'https://thegioihinhanh.com/uploads/images/news/1431441444_news_2414.png','in_stock','2026-10-17 09:41:41','2026-10-17 10:38:50'),(9,2,'Cơm gà xối mỡ giòn bì','Gà thả vườn chiên xối mỡ giòn rụm bên ngoài mọng nước bên trong (Tạm hết)',59000.00,'com-ga-xoi-mo.jpg','out_of_stock','2026-10-17 09:41:41','2026-10-17 09:41:41'),(10,3,'Bò lúc lắc khoai tây','Thịt thăn bò mềm ngọt xào lửa lớn cùng ớt chuông, hành tây và khoai tây múi cau',120000.00,'bo-luc-lac.jpg','in_stock','2026-10-17 09:41:41','2026-10-17 09:41:41'),(11,3,'Bò né hoa thiên lý','Bò phi lê xào nhanh cùng hoa thiên lý đồng quê thơm ngát bổ dưỡng',95000.00,'bo-ne-thien-ly.jpg','in_stock','2026-10-17 09:41:41','2026-10-17 09:41:41'),(12,3,'Gà nướng muối ớt Tây Bắc','Nửa con gà ta ướp hạt dổi mắc khén nướng than hồng thơm nức mũi',135000.00,'ga-nuong-muoi-ot.jpg','in_stock','2026-10-17 09:41:41','2026-10-17 09:41:41'),(13,3,'Gà hấp lá chanh truyền thống','Thịt gà ta da vàng óng ngọt thịt chấm muối tiêu chanh chuẩn vị quê nhà',110000.00,'ga-hap-la-chanh.jpg','in_stock','2026-10-17 09:41:41','2026-10-17 09:41:41'),(14,4,'Mực xào sa tế cay nồng','Mực ống tươi dày thịt xào ớt chuông với sa tế tôm cay nồng kích thích',85000.00,'https://bepxua.vn/wp-content/uploads/2022/09/1-1.jpg','in_stock','2026-10-17 09:41:41','2026-10-18 03:21:31'),(15,4,'Tôm sú hấp nước dừa','Tôm sú loại 1 hấp nước dừa xiêm ngọt thanh tự nhiên',115000.00,'https://cdn2.fptshop.com.vn/unsafe/1920x0/filters:format(webp):quality(75)/2023_10_23_638336931005345107_tom-hap-nuoc-dua.jpg','in_stock','2026-10-17 09:41:41','2026-10-18 03:21:04'),(16,4,'Lẩu Thái hải sản chua cay','Nước lẩu Tom Yum đậm đà kèm tôm sú, mực, ngao, nấm và rau lẩu phong phú',220000.00,'https://tse1.mm.bing.net/th/id/OIP.oq5ui4VCs-UI6xtSLt5jwAHaE8?r=0&rs=1&pid=ImgDetMain&o=7&rm=3','in_stock','2026-10-17 09:41:41','2026-10-17 10:35:01'),(17,4,'Lẩu gà lá giang thơm ngát','Lẩu gà ta nấu cùng lá giang chua dịu ăn kèm bún tươi và hoa chuối',195000.00,'https://tse4.mm.bing.net/th/id/OIP.L_Ms1j2JKZYu9Hvuapv31gHaEL?r=0&rs=1&pid=ImgDetMain&o=7&rm=3','in_stock','2026-10-17 09:41:41','2026-10-17 10:34:25'),(18,4,'Cá chẽm sốt chanh dây','Cá chẽm phi lê chiên giòn sốt chanh dây chua ngọt hấp dẫn (Tạm hết)',140000.00,'https://product.hstatic.net/200000679901/product/ca_chem_sot_chanh_day_9075a12036024921944bef9894e8e880_master.jpg','out_of_stock','2026-10-17 09:41:41','2026-10-17 10:33:55'),(19,5,'Trà đào cam sả thanh nhiệt','Trà đen ủ lạnh kết hợp đào miếng giòn ngọt, nước cam tươi và sả thơm ngát',35000.00,'https://lypham.vn/wp-content/uploads/2024/09/cach-lam-tra-dao-cam-sa.jpg','in_stock','2026-10-17 09:41:41','2026-10-17 10:33:34'),(20,5,'Trà sữa trân châu hoàng gia','Hồng trà pha sữa tươi béo ngậy kèm trân châu đen dai dẻo nấu mật mía',39000.00,'https://tse4.mm.bing.net/th/id/OIP.7RTe34P0sTEgERSKLZwWmwHaE8?r=0&rs=1&pid=ImgDetMain&o=7&rm=3','in_stock','2026-10-17 09:41:41','2026-10-17 10:33:10'),(21,5,'Nước ép dưa hấu tươi','Dưa hấu nguyên chất không pha đường, giàu vitamin và giải nhiệt tức thì',32000.00,'https://tse1.mm.bing.net/th/id/OIP.aNdldeZ8PyvFELljmOIDrQHaE8?r=0&rs=1&pid=ImgDetMain&o=7&rm=3','in_stock','2026-10-17 09:41:41','2026-10-17 10:32:27'),(22,5,'Sinh tố bơ sáp Đắk Lắk','Bơ sáp béo ngậy xay mịn cùng sữa đặc và sữa tươi dinh dưỡng',45000.00,'https://tse1.mm.bing.net/th/id/OIP.h6zQmVBWt3dTDTOxQdie5AHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3','in_stock','2026-10-17 09:41:41','2026-10-17 10:32:04'),(23,5,'Cà phê sữa đá Sài Gòn','Cà phê Robusta pha phin đậm đặc hòa quyện cùng sữa đặc hảo hạng',28000.00,'https://tse3.mm.bing.net/th/id/OIP.oy32FnkDF3YotXVK5EfgzAHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3','in_stock','2026-10-17 09:41:41','2026-10-17 10:31:31'),(24,6,'Chè dưỡng nhan tuyết yến','Chè thanh mát bổ dưỡng gồm tuyết yến, nhựa đào, hạt sen, táo đỏ và kỷ tử',35000.00,'https://tse2.mm.bing.net/th/id/OIP.63fbP0NnjFD7S7S0rQ4SMgHaHa?r=0&rs=1&pid=ImgDetMain&o=7&rm=3','in_stock','2026-10-17 09:41:41','2026-10-17 10:31:04'),(25,6,'Bánh flan caramel cốt dừa','Bánh flan mềm mịn tan trong miệng cùng sốt cà phê caramel và nước cốt dừa',25000.00,'https://bundaumamtomtienhai.vn/wp-content/uploads/2026/01/cach-lam-banh-flan-nuoc-cot-dua-4bac4f.webp','in_stock','2026-10-17 09:41:41','2026-10-17 10:30:36'),(26,6,'Kem dừa Côn Đảo tươi mát','Kem dừa tươi bùi béo rắc đậu phộng rang giòn và dừa khô thơm lừng',30000.00,'https://condao.com.vn/uploads/news/2025_09/kem-dua-dat-con-dao.jpg','in_stock','2026-10-17 09:41:41','2026-10-17 10:15:35'),(27,7,'Món thử nghiệm nội bộ','Món ăn bảo mật dùng để kiểm thử nghiệp vụ ẩn món',999000.00,'hidden-food.jpg','hidden','2026-10-17 09:41:41','2026-10-17 09:41:41');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restaurant_settings`
--

DROP TABLE IF EXISTS `restaurant_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `restaurant_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `restaurant_name` varchar(150) NOT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `opening_hours` varchar(100) NOT NULL,
  `delivery_fee` decimal(12,2) NOT NULL DEFAULT 15000.00,
  `minimum_order_amount` decimal(12,2) NOT NULL DEFAULT 30000.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restaurant_settings`
--

LOCK TABLES `restaurant_settings` WRITE;
/*!40000 ALTER TABLE `restaurant_settings` DISABLE KEYS */;
INSERT INTO `restaurant_settings` VALUES (1,'Nhà Hàng Trần Lộc','150 Nguyễn Như Hạnh, Hòa Minh, Liên Chiểu, Đà Nẵng','0778591167','08:00 - 23:00 (Hằng ngày)',20000.00,50000.00,'2026-10-18 03:44:43');
/*!40000 ALTER TABLE `restaurant_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('customer','staff','admin') NOT NULL DEFAULT 'customer',
  `status` enum('active','locked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Quản Trị Viên (Admin)','admin@restaurant.com','0909000001','$2y$10$9SLyA3ufUJ7KXlbgYacf9uCtx8NcO/tc2LePtsw18tv0IDu8JU4X2','admin','active','2026-10-17 09:41:41','2026-10-17 09:41:41'),(2,'Nhân Viên Thu Ngân (Staff 1)','staff1@restaurant.com','0909000002','$2y$10$A1Fw2YyUNPy6RlfZ8XjLue2wLfLFt6UbWvFVKuzyrMN5nYrc1bhTO','staff','active','2026-10-17 09:41:41','2026-10-17 09:41:41'),(3,'Nhân Viên Bếp (Staff 2)','staff2@restaurant.com','0909000003','$2y$10$A1Fw2YyUNPy6RlfZ8XjLue2wLfLFt6UbWvFVKuzyrMN5nYrc1bhTO','staff','active','2026-10-17 09:41:41','2026-10-17 09:41:41'),(4,'Trần Minh Tuấn (Customer 1)','customer1@gmail.com','0912345678','$2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO','customer','active','2026-10-17 09:41:41','2026-10-17 09:41:41'),(5,'Lê Thị Mai (Customer 2)','customer2@gmail.com','0923456789','$2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO','customer','active','2026-10-17 09:41:41','2026-10-17 09:41:41'),(6,'Phạm Hoàng Nam (Customer 3)','customer3@gmail.com','0934567890','$2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO','customer','active','2026-10-17 09:41:41','2026-10-17 09:41:41'),(7,'Tài Khoản Bị Khóa (Locked Customer)','locked_user@gmail.com','0945678901','$2y$10$M0IGG8yhxvHaA6DGsFb7POsRV2RcqRH75c9n0.wt13E3uSdcLFQnO','customer','locked','2026-10-17 09:41:41','2026-10-17 09:41:41'),(11,'Trần Văn Lộc','tranloc@gmail.com','0778591167','$2y$10$vh1m6ZrIZpgFWeSQ4JQ/Suanggnww4mlC31czI1vSKT4ITVLlw9Ey','customer','active','2026-10-18 03:29:13','2026-10-18 03:29:13');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-19 10:36:38
