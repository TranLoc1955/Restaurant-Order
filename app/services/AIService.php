<?php
/**
 * AI Service
 * Tích hợp Trợ lý ẩm thực AI hội thoại thực sự
 * Hỗ trợ: Grounding dữ liệu MySQL thời gian thực, Lịch sử hội thoại đa lượt (Multi-turn Context),
 * Gọi API Google Gemini an toàn qua Backend Proxy, xử lý lỗi/timeout và thông báo minh bạch khi chưa có API Key.
 */

class AIService {
    private PDO $db;
    private array $appConfig;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->appConfig = require dirname(__DIR__, 2) . '/config/app.php';
    }

    /**
     * Xử lý tin nhắn của khách hàng và tạo phản hồi hội thoại
     */
    public function ask(string $message, string $sessionId, ?int $userId = null): array {
        $message = trim($message);
        if ($message === '') {
            return [
                'reply'   => 'Dạ, bạn có thể hỏi mình về các món ăn trong thực đơn, tư vấn theo ngân sách, số lượng người hoặc giờ mở cửa của nhà hàng ạ!',
                'mode'    => 'demo',
                'is_ai'   => false,
            ];
        }

        // 1. Nạp dữ liệu thực đơn thời gian thực từ Database (Grounding Context)
        $menuData = $this->loadMenuContext();
        $settings = $this->loadSettings();

        // 2. Nạp lịch sử hội thoại gần nhất trong phiên để duy trì ngữ cảnh đa lượt
        $historyLimit = (int)($this->appConfig['ai']['history_limit'] ?? 6);
        $chatHistory = $this->loadRecentHistory($sessionId, $historyLimit);

        $apiKey = trim($this->appConfig['ai']['api_key'] ?? '');

        // 3. Trường hợp CHƯA cấu hình API Key: Thông báo minh bạch, không giả vờ đã kết nối AI
        if (empty($apiKey)) {
            $demoReply = $this->generateDatabaseGroundedReply($message, $menuData, $settings, $chatHistory);
            $fullReply = "💡 **[Chế độ Thử nghiệm - Chưa cấu hình API Key]**\n"
                       . "Hệ thống đang phản hồi ở chế độ mẫu dựa trên dữ liệu thực tế trong Database. Để kích hoạt Trợ lý AI hội thoại thực sự với Google Gemini, bạn chỉ cần mở tệp `.env` và thêm: `GEMINI_API_KEY=your_gemini_key_here`.\n\n"
                       . $demoReply;

            // Ghi nhật ký vào database với mode 'mock' (phù hợp ENUM database)
            $this->logMessage($userId, $sessionId, $message, $fullReply, 'mock');

            return [
                'reply'   => $fullReply,
                'mode'    => 'demo',
                'is_ai'   => false,
            ];
        }

        // 4. Trường hợp ĐÃ có API Key: Gọi trực tiếp LLM API (Google Gemini)
        $apiResult = $this->callGeminiApi($message, $menuData, $settings, $chatHistory, $apiKey);

        if ($apiResult['success']) {
            $reply = $apiResult['text'];
            $mode = 'ai';

            // Ghi nhật ký với mode 'ai'
            $this->logMessage($userId, $sessionId, $message, $reply, 'ai');

            return [
                'reply'   => $reply,
                'mode'    => 'ai',
                'is_ai'   => true,
            ];
        }

        // 5. Nếu gọi API thất bại (API Key sai, Quota 429, Timeout, v.v.): Báo lỗi rõ ràng kèm giải pháp
        $errorMessage = $apiResult['error_message'];
        $errorReply = "⚠️ **[Lỗi kết nối Trợ lý AI]**: {$errorMessage}\n\n"
                    . "Dưới đây là câu trả lời tạm thời dựa trên thực đơn có sẵn của quán:\n\n"
                    . $this->generateDatabaseGroundedReply($message, $menuData, $settings, $chatHistory);

        $this->logMessage($userId, $sessionId, $message, $errorReply, 'mock');

        return [
            'reply'   => $errorReply,
            'mode'    => 'error',
            'is_ai'   => false,
            'error'   => $apiResult['error_detail'] ?? null,
        ];
    }

    /**
     * Nạp danh sách món ăn đang bán từ database (chỉ lấy món in_stock và danh mục active)
     */
    public function loadMenuContext(): array {
        $stmt = $this->db->query("
            SELECT p.id, p.name, p.price, p.description, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id AND c.status = 'active'
            WHERE p.availability_status = 'in_stock'
            ORDER BY c.id ASC, p.price ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Nạp cấu hình nhà hàng từ database
     */
    public function loadSettings(): array {
        $stmt = $this->db->query("SELECT * FROM restaurant_settings WHERE id = 1 LIMIT 1");
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [
            'restaurant_name'      => 'Nhà Hàng Trần Lộc',
            'address'              => '150 Nguyễn Như Hạnh, Hòa Minh, Liên Chiểu, Đà Nẵng',
            'phone'                => '0778591167',
            'opening_hours'        => '08:00 - 23:00 (Hằng ngày)',
            'delivery_fee'         => 20000.00,
            'minimum_order_amount' => 50000.00,
        ];
    }

    /**
     * Lấy các lượt hội thoại gần nhất trong phiên trò chuyện để tạo ngữ cảnh Multi-turn
     */
    public function loadRecentHistory(string $sessionId, int $limit = 6): array {
        try {
            $stmt = $this->db->prepare("
                SELECT user_message, bot_reply, mode, created_at
                FROM chatbot_logs
                WHERE session_id = ?
                ORDER BY id DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $sessionId, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Đảo ngược lại thứ tự thời gian tăng dần
            return array_reverse($rows);
        } catch (Throwable $e) {
            error_log("Failed to load chatbot history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lấy toàn bộ lịch sử trò chuyện trong phiên hiện tại (dùng cho Frontend khôi phục UI)
     */
    public function getSessionHistory(string $sessionId, int $limit = 30): array {
        try {
            $stmt = $this->db->prepare("
                SELECT id, user_message, bot_reply, mode, created_at
                FROM chatbot_logs
                WHERE session_id = ?
                ORDER BY id ASC
                LIMIT ?
            ");
            $stmt->bindValue(1, $sessionId, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Failed to get session history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Xóa lịch sử trò chuyện của phiên hiện tại
     */
    public function clearSessionHistory(string $sessionId): bool {
        try {
            $stmt = $this->db->prepare("DELETE FROM chatbot_logs WHERE session_id = ?");
            return $stmt->execute([$sessionId]);
        } catch (Throwable $e) {
            error_log("Failed to clear chatbot session: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gọi Gemini API (Google AI) với System Instruction, Dữ liệu thực đơn và Lịch sử hội thoại
     */
    private function callGeminiApi(string $userMessage, array $menuData, array $settings, array $chatHistory, string $apiKey): array {
        $model = $this->appConfig['ai']['model'] ?? 'gemini-1.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

        // 1. Chuẩn bị danh sách thực đơn có cấu trúc phân theo danh mục
        $menuByCategory = [];
        foreach ($menuData as $item) {
            $cat = $item['category_name'];
            if (!isset($menuByCategory[$cat])) {
                $menuByCategory[$cat] = [];
            }
            $formattedPrice = number_format((float)$item['price'], 0, ',', '.') . 'đ';
            $menuByCategory[$cat][] = "- {$item['name']}: {$formattedPrice} ({$item['description']})";
        }

        $menuListText = "";
        foreach ($menuByCategory as $cat => $dishes) {
            $menuListText .= "\n[DANH MỤC: {$cat}]\n" . implode("\n", $dishes) . "\n";
        }

        // 2. Định nghĩa System Instruction nghiêm ngặt chống bịa đặt (Hallucination)
        $systemInstruction = "Bạn là Trợ lý món ngon AI thông minh, nhiệt tình và am hiểu ẩm thực của '{$settings['restaurant_name']}'.
THÔNG TIN CHÍNH THỨC CỦA NHÀ HÀNG:
- Địa chỉ: {$settings['address']}
- Hotline tư vấn / hỗ trợ: {$settings['phone']}
- Giờ mở cửa phục vụ: {$settings['opening_hours']}
- Phí giao hàng tận nơi: " . number_format((float)$settings['delivery_fee'], 0, ',', '.') . "đ cho mỗi đơn.
- Đơn hàng tối thiểu: " . number_format((float)$settings['minimum_order_amount'], 0, ',', '.') . "đ.
- Phương thức thanh toán: Nhà hàng hỗ trợ 2 hình thức:
  + Tiền mặt khi nhận hàng (COD)
  + Chuyển khoản qua tài khoản ngân hàng (Quét mã VietQR tiện lợi). Thông tin chuyển khoản: Ngân hàng " . ($this->appConfig['banking']['bank_name'] ?? 'VietComBank') . ", Số tài khoản: " . ($this->appConfig['banking']['account_no'] ?? '1036646124') . ", Chủ tài khoản: " . ($this->appConfig['banking']['account_name'] ?? 'TRAN VAN LOC') . ", nội dung là Mã đơn hàng.
- CHƯƠNG TRÌNH KHUYẾN MÃI & MÃ GIẢM GIÁ (VOUCHER):
  + TRIANKHACHCU: Giảm 30.000đ dành riêng cho khách hàng thân thiết từng đặt món thành công tại nhà hàng (đơn từ 120.000đ).
  + BANMOI20K: Giảm 20.000đ cho đơn hàng đầu tiên của khách hàng mới (đơn từ 80.000đ).
  + GIAM10: Giảm 10% (tối đa 50.000đ) cho đơn tiệc từ 150.000đ.
  + FREESHIP: Giảm ngay 20.000đ phí giao hàng cho đơn từ 100.000đ.
- CHƯƠNG TRÌNH KHÁCH HÀNG THÂN THIẾT & TÍCH ĐIỂM:
  + Tích lũy tự động: Cứ mỗi 10.000đ chi tiêu = 1 điểm thưởng (được cộng vào tài khoản khi đơn hàng giao thành công).
  + Đổi thưởng: 1 điểm = 1.000đ giảm giá trực tiếp vào đơn hàng khi đặt món tại trang thanh toán.

DANH SÁCH TOÀN BỘ MÓN ĂN ĐANG BÁN TẠI NHÀ HÀNG:
{$menuListText}

CÁC NGUYÊN TẮC BẮT BUỘC KHI TƯ VẤN:
1. TRUNG THỰC VÀ BÁM SÁT DỮ LIỆU: Chỉ gợi ý các món ăn, giá tiền, địa chỉ, giờ mở cửa và phí ship có trong danh sách trên. TUYỆT ĐỐI KHÔNG BỊA ĐẶT tên món, giá tiền hoặc chương trình khuyến mãi không có trong dữ liệu.
2. XỬ LÝ MÓN KHÔNG CÓ TRONG MENU: Nếu khách hỏi món nhà hàng không bán (ví dụ: pizza, sushi, bún đậu, bánh mì kẹp...), hãy lịch sự giải thích quán hiện chưa phục vụ món đó và gợi ý 1-2 món tương tự hoặc đặc sản có sẵn của quán.
3. TƯ VẤN THEO NGÂN SÁCH & SỐ LƯỢNG NGƯỜI: Khi khách hỏi tư vấn món theo số người (ví dụ: 2 người, 4 người, gia đình) hoặc theo ngân sách (ví dụ: dưới 50k, 200k, 500k), hãy khéo léo chọn các món có sẵn phù hợp, tính tổng tiền rõ ràng, cân đối giữa món chính, khai vị và đồ uống.
4. DUY TRÌ NGỮ CẢNH ĐA LƯỢT: Bạn nhận được lịch sử hội thoại gần đây của khách. Hãy liên kết và hiểu các câu hỏi tiếp nối (ví dụ: khách hỏi 'món đó có cay không?', 'cho 2 người ăn đủ không?', 'giá bao nhiêu?').
5. HỎI LẠI ĐỂ LÀM RÕ: Nếu câu hỏi của khách còn mơ hồ (ví dụ: 'gợi ý món ăn ngon', 'tôi muốn ăn gì đó lạ'), hãy hỏi thêm về khẩu vị (thích hải sản, bò, gà hay thanh đạm) hoặc số lượng người dùng bữa.
6. BẢO MẬT & GIỚI HẠN CHỨC NĂNG: Bạn là trợ lý tư vấn món ăn. Không viết hoặc thực thi câu lệnh SQL, không sửa đổi dữ liệu hệ thống, không tự ý tạo hoặc hủy đơn hàng của khách qua chat. Hướng dẫn khách bấm 'Thêm vào giỏ' hoặc truy cập mục 'Thực đơn' / 'Giỏ hàng' trên website để đặt hàng.
7. PHONG CÁCH: Sử dụng tiếng Việt tự nhiên, ngắn gọn, lịch sự, thân thiện, dùng định dạng Markdown khéo léo (in đậm món ăn và giá tiền).";

        // 3. Xây dựng mảng Contents luân phiên (User -> Model -> User)
        $contents = [];

        foreach ($chatHistory as $h) {
            $userPart = trim($h['user_message'] ?? '');
            $botPart = trim($h['bot_reply'] ?? '');

            if ($userPart !== '') {
                $contents[] = [
                    'role'  => 'user',
                    'parts' => [['text' => $userPart]]
                ];
            }
            if ($botPart !== '') {
                $contents[] = [
                    'role'  => 'model',
                    'parts' => [['text' => $botPart]]
                ];
            }
        }

        // Thêm câu hỏi hiện tại của khách
        $contents[] = [
            'role'  => 'user',
            'parts' => [['text' => $userMessage]]
        ];

        // 4. Chuẩn bị Payload gửi Gemini API
        $postData = [
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature'     => (float)($this->appConfig['ai']['temperature'] ?? 0.4),
                'maxOutputTokens' => (int)($this->appConfig['ai']['max_tokens'] ?? 800),
            ]
        ];

        $timeout = (int)($this->appConfig['ai']['timeout'] ?? 15);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $curlErrno = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Kiểm tra lỗi mạng / timeout
        if ($curlErrno !== 0) {
            error_log("Gemini API Network/cURL Error ({$curlErrno}): {$curlError}");
            return [
                'success'       => false,
                'error_message' => "Quá thời gian kết nối tới máy chủ Google Gemini ({$timeout}s) hoặc lỗi mạng ({$curlError}).",
                'error_detail'  => $curlError,
            ];
        }

        $result = json_decode($response ?: '', true);

        // Kiểm tra lỗi từ phía Gemini API
        if ($httpCode !== 200) {
            $apiErrorMsg = $result['error']['message'] ?? "Mã phản hồi HTTP {$httpCode}";
            error_log("Gemini API Error HTTP {$httpCode}: {$apiErrorMsg}");

            if ($httpCode === 400 || $httpCode === 403) {
                return [
                    'success'       => false,
                    'error_message' => "API Key Google Gemini không hợp lệ hoặc không có quyền truy cập. Vui lòng kiểm tra lại cấu hình GEMINI_API_KEY trong tệp .env.",
                    'error_detail'  => $apiErrorMsg,
                ];
            } elseif ($httpCode === 429) {
                return [
                    'success'       => false,
                    'error_message' => "API Key đã chạm giới hạn lưu lượng (Rate Limit / Quota). Vui lòng thử lại sau vài giây.",
                    'error_detail'  => $apiErrorMsg,
                ];
            }

            return [
                'success'       => false,
                'error_message' => "Máy chủ AI trả về lỗi: {$apiErrorMsg}",
                'error_detail'  => $apiErrorMsg,
            ];
        }

        // Trích xuất văn bản phản hồi từ cấu trúc JSON của Gemini
        $candidateText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if (!empty($candidateText)) {
            return [
                'success' => true,
                'text'    => trim($candidateText),
            ];
        }

        return [
            'success'       => false,
            'error_message' => "Mô hình AI trả về kết quả rỗng.",
            'error_detail'  => $response,
        ];
    }

    /**
     * Bộ phản hồi mẫu thông minh dựa trên Database thực tế (Grounding)
     * Dùng khi chưa cấu hình API Key hoặc khi mạng bị gián đoạn
     */
    private function generateDatabaseGroundedReply(string $msg, array $menuData, array $settings, array $chatHistory = []): string {
        $lower = mb_strtolower($msg);

        // 1. Chào hỏi
        if (preg_match('/^(chào|alo|hello|hi|helo|xin chào|hey)\b/ui', $lower)) {
            return "Chào bạn! Mình là Trợ lý món ngon của <strong>{$settings['restaurant_name']}</strong> 🍲.\n\n"
                 . "Hôm nay bạn muốn thưởng thức món gì? Bạn có thể hỏi mình gợi ý món ngon cho bữa trưa, món lẩu hải sản, hoặc món ăn theo ngân sách nhé!";
        }

        // 2. Hỏi về địa chỉ, vị trí
        if (str_contains($lower, 'địa chỉ') || str_contains($lower, 'ở đâu') || str_contains($lower, 'vị trí') || str_contains($lower, 'quán ở')) {
            return "Dạ, {$settings['restaurant_name']} tọa lạc tại: <strong>{$settings['address']}</strong>.\nHotline liên hệ đặt bàn hoặc hỗ trợ giao hàng: <strong>{$settings['phone']}</strong> ạ!";
        }

        // 3. Giờ mở cửa
        if (str_contains($lower, 'giờ mở cửa') || str_contains($lower, 'mấy giờ') || str_contains($lower, 'đóng cửa') || str_contains($lower, 'phục vụ lúc')) {
            return "Dạ, nhà hàng mở cửa phục vụ từ <strong>{$settings['opening_hours']}</strong> hằng ngày ạ! Bạn có thể đặt món trực tiếp trên website để được giao tận nơi.";
        }

        // 4. Phí ship, giao hàng
        if (str_contains($lower, 'phí ship') || str_contains($lower, 'phí giao hàng') || str_contains($lower, 'giao hàng bao nhiêu') || str_contains($lower, 'tiền ship')) {
            $fee = number_format((float)$settings['delivery_fee'], 0, ',', '.');
            $min = number_format((float)$settings['minimum_order_amount'], 0, ',', '.');
            return "Phí giao hàng tận nơi của nhà hàng là <strong>{$fee}đ</strong> cho mỗi đơn hàng (đơn tối thiểu từ <strong>{$min}đ</strong>). Bạn có thể chọn thanh toán tiền mặt (COD) hoặc chuyển khoản ngân hàng qua mã VietQR ạ!";
        }

        // 5. Hướng dẫn đặt hàng / thanh toán / chuyển khoản ngân hàng
        if (str_contains($lower, 'đặt hàng') || str_contains($lower, 'cách đặt') || str_contains($lower, 'thanh toán') || str_contains($lower, 'chuyển khoản') || str_contains($lower, 'số tài khoản') || str_contains($lower, 'stk') || str_contains($lower, 'ngân hàng') || str_contains($lower, 'vietqr') || str_contains($lower, 'order')) {
            $banking = $this->appConfig['banking'] ?? [
                'bank_id'      => 'VCB',
                'bank_name'    => 'VietComBank',
                'account_no'   => '1036646124',
                'account_name' => 'TRAN VAN LOC'
            ];

            return "Nhà Hàng Trần Lộc hỗ trợ 2 hình thức thanh toán thuận tiện:\n\n"
                 . "1. 💵 **Thanh toán tiền mặt khi nhận hàng (COD)**: Nhận món ăn nóng hổi và gửi tiền trực tiếp cho shipper.\n"
                 . "2. 💳 **Chuyển khoản trực tuyến qua tài khoản (VietQR)**: Quét mã QR tiện lợi qua mọi app ngân hàng (MB, Vietcombank, Techcombank, MoMo...).\n\n"
                 . "🏦 **Thông tin tài khoản nhà hàng**:\n"
                 . "• Ngân hàng: <strong>{$banking['bank_name']}</strong>\n"
                 . "• Số tài khoản: <strong>{$banking['account_no']}</strong>\n"
                 . "• Chủ tài khoản: <strong>{$banking['account_name']}</strong>\n"
                 . "• Nội dung chuyển tiền: Ghi rõ **Mã đơn hàng** (Ví dụ: ORD-...) để hệ thống xử lý ngay nhé!";
        }

        // 6. Theo dõi đơn hàng
        if (str_contains($lower, 'kiểm tra đơn') || str_contains($lower, 'xem đơn') || str_contains($lower, 'đơn của tôi') || str_contains($lower, 'tiến độ')) {
            return "Để theo dõi đơn hàng, bạn chỉ cần đăng nhập tài khoản và bấm vào mục **'Đơn của tôi'** trên thanh điều hướng để xem chi tiết tiến trình đầu bếp nấu và shipper giao hàng thời gian thực nhé!";
        }

        // 7. Hỏi về Khuyến mãi, Mã giảm giá (Voucher) và Tích điểm
        if (str_contains($lower, 'voucher') || str_contains($lower, 'mã giảm giá') || str_contains($lower, 'khuyến mãi') || str_contains($lower, 'ưu đãi') || str_contains($lower, 'giảm giá') || str_contains($lower, 'tích điểm') || str_contains($lower, 'điểm thưởng') || str_contains($lower, 'tri ân')) {
            return "🎁 **Chương trình Ưu Đãi & Tích Điểm tại Nhà Hàng:**\n\n"
                 . "1. ⭐️ **Mã 'TRIANKHACHCU'**: Giảm **30.000đ** dành riêng cho khách hàng thân thiết từng đặt món thành công (đơn từ 120.000đ).\n"
                 . "2. ✨ **Mã 'BANMOI20K'**: Giảm ngay **20.000đ** cho đơn đặt đầu tiên của bạn mới (đơn từ 80.000đ).\n"
                 . "3. 🎉 **Mã 'GIAM10'**: Giảm **10%** (tối đa 50.000đ) cho đơn từ 150.000đ.\n"
                 . "4. 🚚 **Mã 'FREESHIP'**: Giảm **20.000đ** phí vận chuyển cho đơn từ 100.000đ.\n\n"
                 . "🌟 **Chính sách Tích Điểm Thành Viên**:\n"
                 . "• Mỗi **10.000đ** chi tiêu tích lũy ngay **1 điểm thưởng**.\n"
                 . "• Khi đặt món, **1 điểm = 1.000đ** giảm giá trực tiếp vào đơn hàng tại trang thanh toán!\n\n"
                 . "Bạn có thể nhập hoặc chọn trực tiếp các mã ưu đãi này khi bấm vào trang Đặt hàng nhé!";
        }

        // 8. Hỏi về ngân sách (Ví dụ: "dưới 50k", "dưới 100k", "< 70.000")
        $budgetLimit = null;
        if (preg_match('/dưới\s*([0-9]+)\s*(k|nghìn|ngàn|000)/ui', $lower, $matches)) {
            $num = (int)$matches[1];
            $budgetLimit = ($num < 1000) ? $num * 1000 : $num;
        } elseif (preg_match('/([0-9]+)\s*k/ui', $lower, $matches)) {
            $budgetLimit = (int)$matches[1] * 1000;
        }

        if ($budgetLimit !== null) {
            $matchedDishes = array_filter($menuData, fn($p) => (float)$p['price'] <= $budgetLimit);
            if (!empty($matchedDishes)) {
                $reply = "Dạ, với ngân sách dưới " . number_format($budgetLimit, 0, ',', '.') . "đ, nhà hàng có các món ngon đang bán sẵn phục vụ bạn nè:\n\n";
                $count = 0;
                foreach ($matchedDishes as $dish) {
                    $reply .= "• <strong>{$dish['name']}</strong> (" . htmlspecialchars($dish['category_name']) . "): " . number_format((float)$dish['price'], 0, ',', '.') . "đ\n";
                    if (++$count >= 6) break;
                }
                $reply .= "\nBạn muốn thưởng thức món nào để mình hướng dẫn bạn thêm vào giỏ nhé!";
                return $reply;
            } else {
                return "Dạ với mức ngân sách " . number_format($budgetLimit, 0, ',', '.') . "đ hiện tại nhà hàng chưa có món ăn chính phù hợp. Các món tiết kiệm nhất đang có giá từ 25.000đ - 35.000đ (như Bánh flan cốt dừa hoặc Khoai tây chiên lắc phô mai) ạ!";
            }
        }

        // 8. Món không có trong thực đơn (Ví dụ: pizza, sushi, bún đậu, burger...)
        $unsupportedKeywords = ['pizza', 'sushi', 'bún đậu', 'burger', 'hamburger', 'tacos', 'tokbokki', 'kimchi', 'mì cay'];
        foreach ($unsupportedKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return "Dạ, hiện tại {$settings['restaurant_name']} chuyên về ẩm thực truyền thống Việt Nam, hải sản và lẩu nên **chưa phục vụ món {$kw}** ạ.\n\n"
                     . "Quán có các món đặc sản rất ngon như <strong>Lẩu Thái hải sản</strong>, <strong>Bò lúc lắc khoai tây</strong> hoặc <strong>Cơm chiên hải sản Hoàng Kim</strong>. Bạn có muốn thử qua không ạ?";
            }
        }

        // 9. Hỏi theo danh mục / loại món
        $matchedKeyword = null;
        $categoryMatches = [];

        if (str_contains($lower, 'ăn trưa') || str_contains($lower, 'cơm') || str_contains($lower, 'mì') || str_contains($lower, 'bún')) {
            $matchedKeyword = 'Cơm & Mì';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Cơm') || str_contains($p['category_name'], 'Mì') || str_contains($p['name'], 'Cơm') || str_contains($p['name'], 'Mì'));
        } elseif (str_contains($lower, 'hải sản') || str_contains($lower, 'lẩu') || str_contains($lower, 'tôm') || str_contains($lower, 'mực') || str_contains($lower, 'cá')) {
            $matchedKeyword = 'Hải sản & Lẩu';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Lẩu') || str_contains($p['name'], 'Lẩu') || str_contains($p['name'], 'Tôm') || str_contains($p['name'], 'Mực'));
        } elseif (str_contains($lower, 'bò') || str_contains($lower, 'thịt bò') || str_contains($lower, 'gà') || str_contains($lower, 'thịt gà')) {
            $matchedKeyword = 'Món Bò & Gà';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['name'], 'Bò') || str_contains($p['name'], 'Gà'));
        } elseif (str_contains($lower, 'khai vị') || str_contains($lower, 'salad') || str_contains($lower, 'gỏi')) {
            $matchedKeyword = 'Khai vị & Salad';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Khai vị') || str_contains($p['name'], 'Gỏi') || str_contains($p['name'], 'Salad'));
        } elseif (str_contains($lower, 'đồ uống') || str_contains($lower, 'nước') || str_contains($lower, 'trà') || str_contains($lower, 'cà phê')) {
            $matchedKeyword = 'Đồ uống & Trà hoa quả';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Đồ uống') || str_contains($p['name'], 'Trà') || str_contains($p['name'], 'Nước') || str_contains($p['name'], 'Cà phê'));
        } elseif (str_contains($lower, 'tráng miệng') || str_contains($lower, 'kem') || str_contains($lower, 'chè') || str_contains($lower, 'bánh')) {
            $matchedKeyword = 'Tráng miệng';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Tráng miệng') || str_contains($p['name'], 'Chè') || str_contains($p['name'], 'Kem') || str_contains($p['name'], 'Bánh'));
        }

        if (!empty($categoryMatches)) {
            $reply = "Dạ, gợi ý các món thuộc nhóm <strong>{$matchedKeyword}</strong> đang được nhiều thực khách yêu thích tại {$settings['restaurant_name']}:\n\n";
            $count = 0;
            foreach ($categoryMatches as $dish) {
                $reply .= "• <strong>{$dish['name']}</strong>: " . number_format((float)$dish['price'], 0, ',', '.') . "đ\n";
                if (++$count >= 5) break;
            }
            $reply .= "\nBạn muốn mình tư vấn thêm chi tiết về hương vị món nào không ạ?";
            return $reply;
        }

        // 10. Tìm kiếm món cụ thể nếu khách gõ tên món có trong database
        foreach ($menuData as $dish) {
            if (str_contains($lower, mb_strtolower($dish['name']))) {
                $p = number_format((float)$dish['price'], 0, ',', '.');
                return "Món <strong>{$dish['name']}</strong> hiện đang sẵn sàng phục vụ tại nhà hàng với giá <strong>{$p}đ</strong>.\n\n"
                     . "Mô tả: {$dish['description']}.\n"
                     . "Bạn có muốn thêm món này vào giỏ hàng ngay không ạ?";
            }
        }

        // 11. Phản hồi mặc định
        $sampleDishes = array_slice($menuData, 0, 4);
        $reply = "Dạ, {$settings['restaurant_name']} đang phục vụ thực đơn phong phú với các món nổi bật:\n\n";
        foreach ($sampleDishes as $d) {
            $reply .= "• <strong>{$d['name']}</strong> - " . number_format((float)$d['price'], 0, ',', '.') . "đ\n";
        }
        $reply .= "\nBạn có thể hỏi mình: <em>'Tư vấn món ăn cho 4 người'</em>, <em>'Có món nào dưới 50k không?'</em>, hoặc <em>'Phí giao hàng thế nào?'</em> nhé!";
        return $reply;
    }

    /**
     * Ghi nhật ký vào database bảng chatbot_logs
     */
    private function logMessage(?int $userId, string $sessionId, string $message, string $reply, string $mode): void {
        try {
            // Đảm bảo mode chỉ nhận 'ai' hoặc 'mock' theo đúng ENUM trong MySQL
            $dbMode = ($mode === 'ai') ? 'ai' : 'mock';

            $stmt = $this->db->prepare("
                INSERT INTO chatbot_logs (user_id, session_id, user_message, bot_reply, mode)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $sessionId, $message, $reply, $dbMode]);
        } catch (Throwable $e) {
            error_log("Failed to log chatbot interaction: " . $e->getMessage());
        }
    }
}
