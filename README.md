# 🍲 Restaurant Food Ordering Website with AI Chatbot
### Phần mềm Website Đặt Đồ Ăn Trực Tuyến Tích Hợp Chatbot AI Cho Một Nhà Hàng
**Đồ án môn học:** Kiểm thử chất lượng phần mềm (Software Quality Assurance)  
**Nhà hàng:** Nhà Hàng Trần Lộc (Vietnamese Cuisine & Hotpot)  
**Công nghệ:** PHP 8+ (Pure MVC), MySQL (PDO Prepared Statements), HTML5, CSS3 thuần, JavaScript thuần.

---

## 1. GIỚI THIỆU DỰ ÁN
Website đặt đồ ăn trực tuyến phục vụ **DUY NHẤT một nhà hàng**, tối ưu hóa cho quy trình chọn món, giỏ hàng, đặt hàng thanh toán khi nhận hàng (Cash on Delivery - COD), theo dõi tiến trình chế biến và tích hợp **Trợ lý ẩm thực AI** thông minh hỗ trợ khách hàng tìm kiếm món ăn theo ngân sách, sở thích và giải đáp chính sách nhà hàng.

Toàn bộ hệ thống được xây dựng bằng **PHP thuần theo kiến trúc MVC**, không sử dụng framework nặng (như Laravel hay React), nhằm phục vụ việc học tập, nghiên cứu và thực hiện các kỹ thuật kiểm thử phần mềm (Kiểm thử hộp đen, hộp trắng, kiểm thử phân quyền RBAC, kiểm thử API với Postman và kiểm thử giao diện tự động với Selenium).

---

## 2. CÔNG NGHỆ SỬ DỤNG
- **Frontend:** HTML5, CSS3 (CSS Variables, Flexbox, CSS Grid, không dùng Tailwind hay Bootstrap), JavaScript thuần (ES6+ AJAX, Fetch API).
- **Backend:** PHP 8.1+ thuần hướng đối tượng theo mô hình MVC (Single Entry Point `public/index.php`, Router regex, Controller, Service, Model, Middleware).
- **Database:** MySQL (InnoDB engine, khóa ngoại Foreign Keys, ràng buộc toàn vẹn, UTF-8 MB4).
- **Truy cập Database:** PDO với Prepared Statements chống hoàn toàn SQL Injection.
- **Trợ lý AI:** Tích hợp mô hình Gemini (Google AI) hoặc chế độ Mock AI thông minh bám sát thực đơn thực tế trong database.
- **Môi trường phát triển:** XAMPP (Apache + MySQL), VS Code, Chrome.

---

## 3. DANH SÁCH CHỨC NĂNG THEO BA VAI TRÒ (RBAC)

### 3.1. Khách hàng (Customer)
1. **Khám phá thực đơn:**
   - Xem trang chủ với Hero banner, món bán chạy, món nổi bật.
   - Lọc món theo danh mục (Khai vị & Salad, Cơm & Mì, Món Bò & Gà, Hải sản & Lẩu, Đồ uống, Tráng miệng).
   - Tìm kiếm món theo tên hoặc nguyên liệu.
   - Sắp xếp món theo giá tăng dần, giá giảm dần hoặc tên A-Z.
   - Xem chi tiết món ăn (ảnh, mô tả, tình trạng còn hàng).
2. **Giỏ hàng & Đặt hàng:**
   - Thêm món vào giỏ hàng bằng AJAX không tải lại trang.
   - Tăng, giảm số lượng (1 - 99), xóa món khỏi giỏ.
   - Xác minh giá từ Database tại thời điểm thêm và đặt (bảo vệ toàn vẹn dữ liệu).
   - Đặt hàng thanh toán khi nhận hàng (COD) với kiểm tra số điện thoại Việt Nam hợp lệ.
   - Nhận mã đơn hàng duy nhất (`ORD-YYYYMMDD-XXXX`).
3. **Theo dõi đơn hàng:**
   - Xem lịch sử các đơn hàng đã đặt.
   - Xem chi tiết đơn hàng (danh sách món, snapshot đơn giá, địa chỉ nhận hàng).
   - Theo dõi tiến trình chuyển trạng thái đơn (Audit Log thời gian thực).
   - **Hủy đơn hàng:** Chỉ cho phép khách hàng tự hủy khi đơn đang ở trạng thái `PENDING`.
4. **Trợ lý ẩm thực AI (Chatbot):**
   - Nút chat nổi ở góc dưới bên phải màn hình.
   - Hỏi đáp món ăn theo ngân sách (Ví dụ: *"Có món nào dưới 50k không?"*).
   - Gợi ý món ăn trưa, món khai vị, đồ uống, lẩu.
   - Hỏi giờ mở cửa, địa chỉ, phí giao hàng, hướng dẫn cách đặt hàng.
   - Tự động chuyển đổi giữa Gemini AI thật và Mock AI thông minh khi không có API Key.

### 3.2. Nhân viên (Staff)
1. **Quản lý đơn hàng:**
   - Xem danh sách toàn bộ đơn hàng của nhà hàng.
   - Bộ lọc đơn hàng theo trạng thái (`PENDING`, `CONFIRMED`, `PREPARING`, `READY`, `DELIVERED`, `REJECTED`, `CANCELLED`).
   - Lọc đơn theo ngày đặt hoặc tìm kiếm nhanh theo mã đơn / SĐT khách.
   - Xem chi tiết đơn hàng dạng Drawer / Modal (món ăn, tổng tiền, ghi chú, thông tin khách).
2. **Quy trình chuyển trạng thái đơn hàng hợp lệ:**
   - Tiếp nhận đơn mới: Bấm `Duyệt đơn` (`PENDING` $\to$ `CONFIRMED`).
   - Từ chối đơn: Bấm `Từ chối` (`PENDING` $\to$ `REJECTED`), **bắt buộc nhập lý do từ chối** để thông báo cho khách.
   - Bắt đầu nấu: Bấm `Bắt đầu nấu` (`CONFIRMED` $\to$ `PREPARING`).
   - Nấu xong: Bấm `Sẵn sàng giao` (`PREPARING` $\to$ `READY`).
   - Giao hàng thành công: Bấm `Đã giao thành công` (`READY` $\to$ `DELIVERED`), hệ thống tự động cập nhật `payment_status = 'paid'`.
   - **Ngăn chặn chuyển trạng thái sai quy trình ở cả Frontend và Backend.**

### 3.3. Quản trị viên (Admin)
1. **Dashboard Tổng quan:**
   - Thống kê doanh thu hợp lệ (**chỉ tính các đơn DELIVERED**; loại bỏ đơn hủy/từ chối).
   - Thống kê tổng số đơn, đơn chờ duyệt, đơn hoàn tất.
   - Bảng xếp hạng Top 5 món bán chạy nhất.
   - Danh sách đơn hàng mới nhất.
2. **Quản lý Thực đơn (Món ăn & Danh mục):**
   - Thêm món mới, sửa giá, cập nhật mô tả và danh mục.
   - Nút chuyển nhanh tình trạng: `Còn hàng` / `Tạm hết` / `Đang ẩn`.
   - Xóa an toàn: Nếu món đã từng có trong đơn hàng cũ, hệ thống tự động ẩn món để bảo toàn lịch sử đơn.
   - Quản lý danh mục: Thêm, sửa, ẩn/hiện danh mục.
3. **Quản lý Tài khoản & Phân quyền:**
   - Xem danh sách khách hàng và nhân viên.
   - Tạo tài khoản nhân viên mới (`role = 'staff'`).
   - Khóa hoặc mở khóa tài khoản người dùng (`active` / `locked`).
   - **Quy tắc bảo vệ Admin:** Không cho phép khóa tài khoản Admin duy nhất còn lại trong hệ thống.
4. **Cài đặt Nhà hàng:**
   - Cập nhật tên thương hiệu, địa chỉ, số hotline, giờ mở cửa.
   - Cấu hình phí giao hàng mặc định (mặc định: 15.000đ).
   - Cấu hình giá trị đơn hàng tối thiểu (mặc định: 30.000đ).

---

## 4. CẤU TRÚC THƯ MỤC DỰ ÁN

```
WebsiteTest/
├── app/
│   ├── controllers/
│   │   ├── HomeController.php             # Trang chủ khách hàng
│   │   ├── MenuController.php             # Thực đơn, lọc, tìm kiếm, chi tiết món
│   │   ├── CartController.php             # Giỏ hàng và API thêm/sửa/xóa món
│   │   ├── OrderController.php            # Đặt hàng COD, lịch sử, hủy đơn
│   │   ├── AuthController.php             # Đăng ký, đăng nhập, đăng xuất Web & API
│   │   ├── StaffOrderController.php       # Xử lý đơn hàng cho nhân viên
│   │   ├── AdminDashboardController.php   # Dashboard thống kê doanh thu hợp lệ
│   │   ├── AdminProductController.php     # Quản lý món ăn thực đơn
│   │   ├── AdminCategoryController.php    # Quản lý danh mục
│   │   ├── AdminUserController.php        # Quản lý tài khoản & tạo nhân viên
│   │   ├── AdminSettingController.php     # Cài đặt nhà hàng & phí ship
│   │   └── ChatbotController.php          # API Trợ lý ẩm thực AI
│   ├── models/
│   │   ├── User.php                       # Quản lý bảng users, bcrypt, chống tiêm admin
│   │   ├── Category.php                   # Quản lý bảng categories
│   │   ├── Product.php                    # Quản lý bảng products
│   │   ├── Order.php                      # Quản lý đơn hàng, state machine, doanh thu
│   │   └── RestaurantSetting.php          # Quản lý cấu hình nhà hàng
│   ├── services/
│   │   ├── CartService.php                # Nghiệp vụ giỏ hàng Session & kiểm tra giá DB
│   │   └── AIService.php                  # Grounding thực đơn thời gian thực & Mock AI
│   ├── middleware/
│   │   ├── AuthMiddleware.php             # Phân quyền RBAC, kiểm tra tài khoản bị khóa
│   │   └── CsrfMiddleware.php             # Chống tấn công CSRF cho POST requests
│   └── helpers/
│       ├── Database.php                   # Singleton PDO MySQL an toàn
│       ├── Response.php                   # Render view, layout, json, redirect
│       ├── Router.php                     # Engine định tuyến URL & middleware
│       ├── Session.php                    # Quản lý phiên, flash message, CSRF token
│       └── Validator.php                  # Kiểm tra dữ liệu đầu vào & chống XSS
├── config/
│   ├── database.php                       # Cấu hình kết nối MySQL PDO
│   └── app.php                            # Cấu hình AI Gemini, Model, Timeout
├── database/
│   ├── schema.sql                         # Cấu trúc 8 bảng chuẩn 3NF, Foreign Keys
│   └── seed.sql                           # Dữ liệu mẫu phong phú đại diện mọi trạng thái
├── public/
│   ├── index.php                          # Front Controller tiếp nhận mọi request
│   ├── .htaccess                          # Apache URL Rewriting
│   └── assets/
│       ├── css/
│       │   ├── variables.css              # Bảng màu ẩm thực (cam đất, xanh lá, bo góc)
│       │   ├── base.css                   # CSS Reset, typography, layout container
│       │   ├── components.css             # Nút bấm, form inputs, badges, cards, toast
│       │   ├── customer.css               # Header, hero, food cards, cart, footer
│       │   ├── auth.css                   # Layout 2 cột cho đăng nhập/đăng ký
│       │   └── chatbot.css                # Widget chat nổi, typing dots, suggestions
│       ├── js/
│       │   ├── app.js                     # Quản lý Toast, loading spinner, demo filler
│       │   ├── customer.js                # Thêm giỏ AJAX, đồng bộ badge, tăng giảm số lượng
│       │   └── chatbot.js                 # Widget Trợ lý AI, gửi tin nhắn, format Markdown
│       └── images/
│           ├── logo.svg                   # Logo nhà hàng Trần Lộc
│           └── products/
│               └── default-food.svg       # Hình minh họa món ăn ấm áp
├── views/
│   ├── layouts/
│   │   ├── main.php                       # Layout chung cho khách hàng + Chatbot widget
│   │   ├── admin.php                      # Layout chung cho Admin và Staff
│   │   └── auth.php                       # Layout cho Login & Register
│   ├── customer/
│   │   ├── home.php                       # Trang chủ
│   │   ├── menu.php                       # Thực đơn, lọc, tìm kiếm
│   │   ├── product-detail.php             # Chi tiết món ăn
│   │   ├── cart.php                       # Giỏ hàng
│   │   ├── checkout.php                   # Thanh toán COD
│   │   ├── order-success.php              # Đặt hàng thành công
│   │   ├── order-history.php              # Lịch sử đơn hàng
│   │   └── order-detail.php               # Chi tiết đơn & timeline trạng thái
│   ├── auth/
│   │   ├── login.php                      # Đăng nhập (có nút điền nhanh tài khoản demo)
│   │   └── register.php                   # Đăng ký tài khoản
│   ├── staff/
│   │   └── orders.php                     # Dashboard xử lý đơn hàng nhân viên
│   ├── admin/
│   │   ├── dashboard.php                  # Thống kê doanh thu & bán chạy
│   │   ├── products/index.php             # Quản trị thực đơn món ăn
│   │   ├── categories/index.php           # Quản trị danh mục
│   │   ├── users/index.php                # Quản trị người dùng & tạo staff
│   │   └── settings/index.php             # Cấu hình nhà hàng
│   └── errors/
│       ├── 403.php                        # Trang lỗi 403 Forbidden
│       ├── 404.php                        # Trang lỗi 404 Not Found
│       └── 500.php                        # Trang lỗi 500 Server Error
├── storage/
│   ├── logs/                              # Nhật ký hệ thống
│   └── uploads/                           # Tải lên tệp
├── .env.example                           # Biến môi trường mẫu
├── .gitignore
└── README.md
```

---

## 5. HƯỚNG DẪN CÀI ĐẶT VÀ CHẠY ỨNG DỤNG

### 5.1. Yêu cầu hệ thống
- XAMPP (Apache + MySQL / MariaDB, PHP 8.0 trở lên).
- Trình duyệt web hiện đại: Google Chrome, Microsoft Edge, Firefox.

### 5.2. Các bước cài đặt trên XAMPP
1. **Khởi động dịch vụ:** Mở **XAMPP Control Panel**, nhấn `Start` cho cả **Apache** và **MySQL**.
2. **Tạo Cơ sở dữ liệu:**
   - Mở trình duyệt truy cập: `http://localhost/phpmyadmin`
   - Nhấn vào tab **Import** (Nhập).
   - Chọn tệp `database/schema.sql` rồi nhấn **Import** để khởi tạo cấu trúc bảng.
   - Tiếp tục chọn tệp `database/seed.sql` rồi nhấn **Import** để nạp dữ liệu mẫu ban đầu.
3. **Chạy Website:**
   - **Cách 1 (Khuyên dùng - Chạy bằng PHP Built-in Server nhanh gọn):**
     Mở terminal tại thư mục dự án và gõ:
     ```bash
     php -S 127.0.0.1:8000 -t public
     ```
     Sau đó mở trình duyệt truy cập: **`http://127.0.0.1:8000`**
   - **Cách 2 (Chạy trực tiếp trong thư mục htdocs của XAMPP):**
     Truy cập theo đường dẫn: **`http://localhost/CS462C/WebsiteTest/public`**

---

## 6. DANH SÁCH TÀI KHOẢN THỬ NGHIỆM

Tại trang Đăng nhập (`/login`), hệ thống đã tích hợp sẵn **Hộp nút bấm điền nhanh tài khoản** để tiện lợi nhất cho người kiểm thử / chấm bài:

| Vai trò (Role) | Email đăng nhập | Mật khẩu | Mục đích sử dụng |
| :--- | :--- | :---: | :--- |
| **Quản trị viên (Admin)** | `admin@restaurant.com` | `admin123` | Xem thống kê doanh thu hợp lệ, quản lý thực đơn món ăn, danh mục, tài khoản, cấu hình nhà hàng. |
| **Nhân viên 1 (Staff)** | `staff1@restaurant.com` | `staff123` | Xử lý duyệt đơn (`PENDING` $\to$ `CONFIRMED`), từ chối đơn kèm lý do, chuyển trạng thái nấu và giao hàng. |
| **Nhân viên 2 (Staff)** | `staff2@restaurant.com` | `staff123` | Đăng nhập xử lý đơn bếp. |
| **Khách hàng 1 (Customer)** | `customer1@gmail.com` | `customer123` | Đặt món, xem đơn hoàn thành (`DELIVERED`), trò chuyện với Trợ lý AI. |
| **Khách hàng 2 (Customer)** | `customer2@gmail.com` | `customer123` | Theo dõi đơn đang nấu (`PREPARING`) và đơn mới (`PENDING`). |
| **Tài khoản Bị Khóa** | `locked_user@gmail.com` | `customer123` | **Kiểm thử bảo mật:** Đăng nhập thất bại với thông báo tài khoản bị khóa. |

---

## 7. CẤU HÌNH TRỢ LÝ AI CHATBOT VÀ CHẾ ĐỘ MOCK

### 7.1. Chế độ Mock AI (Mặc định - Sẵn sàng chạy ngay không cần API Key)
- Nếu bạn không nhập API Key, hệ thống **tự động kích hoạt Mock AI Engine**.
- Mock AI Engine được lập trình thông minh:
  - Phân tích câu hỏi ngân sách (Ví dụ: *"Có món nào dưới 50k không?"* $\to$ tự động truy vấn danh sách món $\le 50.000$đ trong database và trả lời có định dạng).
  - Phân tích câu hỏi bữa ăn (*"Ăn trưa"*, *"Cơm"*, *"Món gà"*, *"Lẩu"* $\to$ gợi ý các món tương ứng).
  - Trả lời chính xác giờ mở cửa, phí giao hàng, địa chỉ lấy từ database.
  - Phục vụ kiểm thử hoàn hảo và ổn định 100%.

### 7.2. Cấu hình Gemini AI thật (Tùy chọn)
Nếu bạn có Google Gemini API Key:
1. Tạo tệp `.env` tại thư mục gốc từ `.env.example`:
   ```ini
   GEMINI_API_KEY=AIzaSy...your_gemini_api_key_here
   ```
2. Khởi động lại server. Hệ thống sẽ tự động gửi câu hỏi kèm context thực đơn thực tế lên Gemini Flash để tạo câu trả lời tự nhiên.
3. Nếu API Key gặp lỗi hoặc mạng bị mất kết nối, hệ thống sẽ tự động dự phòng sang Mock AI, đảm bảo website đặt món không bao giờ bị gián đoạn.

---

## 8. DANH SÁCH API NỘI BỘ (INTERNAL APIS)

| Phương thức | Tuyến đường (Endpoint) | Quyền hạn | Mô tả |
| :---: | :--- | :---: | :--- |
| `POST` | `/api/auth/login` | Public | Đăng nhập trả về token/user info JSON |
| `POST` | `/api/auth/register` | Public | Đăng ký tài khoản khách hàng mới |
| `GET` | `/api/auth/me` | Logged In | Lấy thông tin tài khoản hiện tại |
| `POST` | `/api/auth/logout` | Logged In | Đăng xuất hủy session |
| `POST` | `/api/cart/add` | Public/Guest | Thêm món vào giỏ hàng (`product_id`, `quantity`) |
| `POST` | `/api/cart/update` | Public/Guest | Cập nhật số lượng món trong giỏ |
| `POST` | `/api/cart/remove` | Public/Guest | Xóa món khỏi giỏ hàng |
| `GET` | `/api/cart/count` | Public/Guest | Lấy tổng số lượng món cho huy hiệu giỏ |
| `GET` | `/api/staff/orders/{id}` | Staff/Admin | Lấy chi tiết món và lịch sử audit của một đơn |
| `POST` | `/api/chatbot/message` | Public | Gửi câu hỏi tới Trợ lý ẩm thực AI |

---

## 9. CÁC BIỆN PHÁP BẢO MẬT ĐÃ TRIỂN KHAI
1. **PDO Prepared Statements:** Chống tấn công SQL Injection ở 100% các câu truy vấn.
2. **Mã hóa mật khẩu Bcrypt:** Sử dụng `password_hash()` và `password_verify()`.
3. **CSRF Protection:** Xác thực token cho các thao tác POST/PUT/DELETE.
4. **Phòng vệ leo thang đặc quyền (Privilege Escalation):** Đăng ký công khai luôn bị ép cứng vai trò `customer`.
5. **Bảo vệ tài khoản Quản trị viên tối cao:** Ngăn chặn việc khóa hoặc vô hiệu hóa tài khoản Admin duy nhất còn lại trong hệ thống.
6. **Kiểm tra khóa tài khoản thời gian thực:** Ngắt phiên làm việc ngay lập tức nếu tài khoản bị Admin đánh dấu `locked`.
7. **Xác minh toàn vẹn giá tiền:** Backend luôn tự tính lại giá từ Database; không tin tưởng bất kỳ tổng tiền nào gửi từ phía trình duyệt.
8. **Chống XSS:** Escape toàn bộ dữ liệu hiển thị bằng `htmlspecialchars()`.

---

## 10. GIỚI HẠN VÀ HƯỚNG PHÁT TRIỂN
- **Giới hạn hiện tại:**
  - Hệ thống chỉ phục vụ một nhà hàng duy nhất (đúng theo yêu cầu đồ án).
  - Chỉ hỗ trợ phương thức thanh toán tiền mặt khi nhận hàng (COD), chưa tích hợp cổng thanh toán trực tuyến thực tế (Momo/VNPAY).
- **Hướng phát triển:**
  - Tích hợp quét mã QR thanh toán động VietQR.
  - Hỗ trợ thông báo đơn hàng theo thời gian thực qua WebSocket.
  - Tích hợp in hóa đơn nhiệt cho nhân viên quầy thu ngân.
