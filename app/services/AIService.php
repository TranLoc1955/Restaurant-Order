<?php
/**
 * AI Service
 * Tích hợp Trợ lý ẩm thực AI với cơ chế Grounding thực đơn thời gian thực & Mock engine dự phòng
 */

class AIService {
    private PDO $db;
    private array $appConfig;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->appConfig = require dirname(__DIR__, 2) . '/config/app.php';
    }

    /**
     * Xử lý tin nhắn của khách hàng và tạo phản hồi
     */
    public function ask(string $message, string $sessionId, ?int $userId = null): array {
        $message = trim($message);
        if ($message === '') {
            return [
                'reply' => 'Dạ, bạn có thể hỏi mình về các món ăn trong thực đơn, món theo ngân sách hoặc giờ mở cửa của nhà hàng ạ!',
                'mode'  => 'mock'
            ];
        }

        // 1. Nạp dữ liệu thực đơn thời gian thực từ Database (Grounding Context)
        $menuData = $this->loadMenuContext();
        $settings = $this->loadSettings();

        $apiKey = $this->appConfig['ai']['api_key'] ?? '';
        $useMock = empty($apiKey) || ($this->appConfig['ai']['mock_mode'] ?? false);

        $reply = '';
        $mode = 'mock';

        if (!$useMock) {
            try {
                $aiResponse = $this->callGeminiApi($message, $menuData, $settings, $apiKey);
                if (!empty($aiResponse)) {
                    $reply = $aiResponse;
                    $mode = 'ai';
                }
            } catch (Throwable $e) {
                error_log("Gemini API Error: " . $e->getMessage() . " -> Falling back to Mock engine.");
            }
        }

        // Nếu chế độ mock hoặc gọi AI API thất bại -> Chạy Mock Engine thông minh
        if (empty($reply)) {
            $reply = $this->generateMockReply($message, $menuData, $settings);
            $mode = 'mock';
        }

        // 2. Ghi nhật ký hội thoại vào bảng chatbot_logs
        $this->logMessage($userId, $sessionId, $message, $reply, $mode);

        return [
            'reply' => $reply,
            'mode'  => $mode,
        ];
    }

    /**
     * Nạp danh sách món ăn đang bán từ database
     */
    private function loadMenuContext(): array {
        $stmt = $this->db->query("
            SELECT p.id, p.name, p.price, p.description, c.name as category_name
            FROM products p
            JOIN categories c ON p.category_id = c.id AND c.status = 'active'
            WHERE p.availability_status = 'in_stock'
            ORDER BY c.id ASC, p.price ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Nạp cấu hình nhà hàng
     */
    private function loadSettings(): array {
        $stmt = $this->db->query("SELECT * FROM restaurant_settings WHERE id = 1 LIMIT 1");
        $row = $stmt->fetch();
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
     * Gọi Gemini API
     */
    private function callGeminiApi(string $userMessage, array $menuData, array $settings, string $apiKey): ?string {
        $model = $this->appConfig['ai']['model'] ?? 'gemini-1.5-flash';
        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

        // Chuẩn bị danh sách thực đơn tóm tắt
        $menuListText = "";
        foreach ($menuData as $item) {
            $formattedPrice = number_format((float)$item['price'], 0, ',', '.') . 'đ';
            $menuListText .= "- [{$item['category_name']}] {$item['name']}: {$formattedPrice}. ({$item['description']})\n";
        }

        $systemInstruction = "Bạn là Trợ lý món ngon thông minh của '{$settings['restaurant_name']}'.
Địa chỉ nhà hàng: {$settings['address']}. Số điện thoại: {$settings['phone']}.
Giờ phục vụ: {$settings['opening_hours']}. Phí giao hàng: " . number_format((float)$settings['delivery_fee'], 0, ',', '.') . "đ.
Đơn hàng tối thiểu: " . number_format((float)$settings['minimum_order_amount'], 0, ',', '.') . "đ. Phương thức thanh toán: COD (tiền mặt khi nhận hàng).

DƯỚI ĐÂY LÀ THỰC ĐƠN ĐANG BÁN TẠI NHÀ HÀNG:
{$menuListText}

QUY TẮC BẮT BUỘC:
1. Chỉ gợi ý và trả lời thông tin dựa trên thực đơn và dữ liệu trên. Tuyệt đối không sáng tạo món không có trong danh sách.
2. Giá cả, phí ship phải tuyệt đối chính xác với số liệu trên. Không bịa đặt chương trình giảm giá.
3. Nếu khách hỏi món không có trong thực đơn, hãy lịch sự thông báo nhà hàng chưa phục vụ món đó và gợi ý món tương tự có sẵn.
4. Trả lời thân thiện, lịch sự, dùng tiếng Việt ngắn gọn, dễ đọc, khơi gợi cảm giác ngon miệng.";

        $postData = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => "Hướng dẫn hệ thống:\n{$systemInstruction}\n\nKhách hàng hỏi: {$userMessage}"]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature'     => 0.4,
                'maxOutputTokens' => (int)($this->appConfig['ai']['max_tokens'] ?? 400),
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, (int)($this->appConfig['ai']['timeout'] ?? 10));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $result = json_decode($response, true);
            $candidateText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
            if ($candidateText) {
                return trim($candidateText);
            }
        }

        return null;
    }

    /**
     * Mock AI Engine: Phân tích ngữ nghĩa câu hỏi dựa trên từ khóa và dữ liệu thực tế trong DB
     */
    private function generateMockReply(string $msg, array $menuData, array $settings): string {
        $lower = mb_strtolower($msg);

        // 1. Hỏi về địa chỉ, liên hệ, giờ mở cửa
        if (str_contains($lower, 'giờ mở cửa') || str_contains($lower, 'mấy giờ') || str_contains($lower, 'đóng cửa') || str_contains($lower, 'thời gian')) {
            return "Dạ, {$settings['restaurant_name']} mở cửa phục vụ từ <strong>{$settings['opening_hours']}</strong> hằng ngày ạ! Bạn có thể đặt món trực tiếp trên website để được giao tận nơi.";
        }

        if (str_contains($lower, 'địa chỉ') || str_contains($lower, 'ở đâu') || str_contains($lower, 'vị trí')) {
            return "Nhà hàng tọa lạc tại: <strong>{$settings['address']}</strong>. Hotline hỗ trợ: <strong>{$settings['phone']}</strong> ạ!";
        }

        if (str_contains($lower, 'phí ship') || str_contains($lower, 'phí giao hàng') || str_contains($lower, 'giao hàng bao nhiêu')) {
            $fee = number_format((float)$settings['delivery_fee'], 0, ',', '.');
            $min = number_format((float)$settings['minimum_order_amount'], 0, ',', '.');
            return "Phí giao hàng tận nơi của nhà hàng hiện là <strong>{$fee}đ</strong> cho mỗi đơn hàng (đơn tối thiểu từ <strong>{$min}đ</strong>) và áp dụng thanh toán COD khi nhận hàng ạ.";
        }

        if (str_contains($lower, 'đặt hàng') || str_contains($lower, 'cách đặt') || str_contains($lower, 'thanh toán')) {
            return "Dạ để đặt hàng rất đơn giản: Bạn chỉ cần vào mục <strong>Thực đơn</strong>, chọn món yêu thích bấm <strong>'Thêm vào giỏ'</strong>, sau đó vào <strong>Giỏ hàng</strong> bấm <strong>'Tiến hành đặt hàng'</strong> và nhập địa chỉ nhận hàng. Nhà hàng hỗ trợ thanh toán tiền mặt (COD) khi nhận hàng ạ!";
        }

        if (str_contains($lower, 'kiểm tra đơn') || str_contains($lower, 'xem đơn') || str_contains($lower, 'trạng thái đơn')) {
            return "Để theo dõi đơn hàng, bạn hãy đăng nhập tài khoản và bấm vào <strong>'Lịch sử đơn hàng'</strong> ở góc trên thanh menu để xem chi tiết tiến độ nấu và giao hàng thời gian thực nhé!";
        }

        // 2. Phân tích câu hỏi về Ngân sách (Ví dụ: "dưới 50k", "dưới 50.000", "< 70k", "dưới 100k")
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
                    $reply .= "• <strong>{$dish['name']}</strong>: " . number_format((float)$dish['price'], 0, ',', '.') . "đ\n";
                    if (++$count >= 6) break;
                }
                $reply .= "\nBạn muốn thưởng thức món nào để mình hướng dẫn thêm vào giỏ nhé!";
                return $reply;
            } else {
                return "Dạ với ngân sách " . number_format($budgetLimit, 0, ',', '.') . "đ hiện tại nhà hàng chưa có món ăn chính phù hợp. Món có mức giá tiết kiệm nhất hiện tại là từ 25.000đ - 35.000đ (như Bánh flan cốt dừa hoặc Khoai tây lắc phô mai) ạ!";
            }
        }

        // 3. Phân tích câu hỏi theo danh mục / loại món ăn
        $matchedKeyword = null;
        $categoryMatches = [];

        if (str_contains($lower, 'ăn trưa') || str_contains($lower, 'cơm') || str_contains($lower, 'mì') || str_contains($lower, 'bún') || str_contains($lower, 'phở')) {
            $matchedKeyword = 'Cơm & Mì';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Cơm') || str_contains($p['category_name'], 'Mì') || str_contains($p['name'], 'Cơm') || str_contains($p['name'], 'Phở') || str_contains($p['name'], 'Mì'));
        } elseif (str_contains($lower, 'khai vị') || str_contains($lower, 'salad') || str_contains($lower, 'gỏi') || str_contains($lower, 'chả giò')) {
            $matchedKeyword = 'Khai vị & Salad';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Khai vị') || str_contains($p['name'], 'Gỏi') || str_contains($p['name'], 'Salad'));
        } elseif (str_contains($lower, 'thịt bò') || str_contains($lower, 'bò') || str_contains($lower, 'thịt gà') || str_contains($lower, 'gà')) {
            $matchedKeyword = 'Món Bò & Gà';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['name'], 'Bò') || str_contains($p['name'], 'Gà'));
        } elseif (str_contains($lower, 'hải sản') || str_contains($lower, 'lẩu') || str_contains($lower, 'tôm') || str_contains($lower, 'mực') || str_contains($lower, 'cá')) {
            $matchedKeyword = 'Hải sản & Lẩu';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Lẩu') || str_contains($p['name'], 'Tôm') || str_contains($p['name'], 'Mực') || str_contains($p['name'], 'Lẩu'));
        } elseif (str_contains($lower, 'nước') || str_contains($lower, 'uống') || str_contains($lower, 'trà') || str_contains($lower, 'cà phê') || str_contains($lower, 'sinh tố') || str_contains($lower, 'nước ép')) {
            $matchedKeyword = 'Đồ uống & Trà hoa quả';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Đồ uống') || str_contains($p['name'], 'Trà') || str_contains($p['name'], 'Nước') || str_contains($p['name'], 'Cà phê'));
        } elseif (str_contains($lower, 'tráng miệng') || str_contains($lower, 'kem') || str_contains($lower, 'chè') || str_contains($lower, 'bánh')) {
            $matchedKeyword = 'Tráng miệng';
            $categoryMatches = array_filter($menuData, fn($p) => str_contains($p['category_name'], 'Tráng miệng') || str_contains($p['name'], 'Chè') || str_contains($p['name'], 'Kem') || str_contains($p['name'], 'Bánh'));
        }

        if (!empty($categoryMatches)) {
            $reply = "Dạ, gợi ý các món thuộc nhóm <strong>{$matchedKeyword}</strong> đang được nhiều khách hàng yêu thích tại quán:\n\n";
            $count = 0;
            foreach ($categoryMatches as $dish) {
                $reply .= "• <strong>{$dish['name']}</strong>: " . number_format((float)$dish['price'], 0, ',', '.') . "đ\n";
                if (++$count >= 5) break;
            }
            $reply .= "\nBạn có muốn mình tư vấn thêm chi tiết món nào không ạ?";
            return $reply;
        }

        // 4. Tìm kiếm món cụ thể nếu khách gõ đúng tên món
        foreach ($menuData as $dish) {
            if (str_contains($lower, mb_strtolower($dish['name']))) {
                $p = number_format((float)$dish['price'], 0, ',', '.');
                return "Món <strong>{$dish['name']}</strong> hiện đang sẵn sàng phục vụ tại nhà hàng với giá <strong>{$p}đ</strong>.\n\nMô tả: {$dish['description']}.\nBạn có muốn đặt món này vào bữa ăn hôm nay không ạ?";
            }
        }

        // 5. Câu trả lời mặc định thân thiện
        $sampleDishes = array_slice($menuData, 0, 3);
        $reply = "Chào bạn! Mình là Trợ lý món ngon của {$settings['restaurant_name']} 🍲.\n\nNhà hàng đang phục vụ rất nhiều món ăn đặc sắc như:\n";
        foreach ($sampleDishes as $d) {
            $reply .= "• <strong>{$d['name']}</strong> - " . number_format((float)$d['price'], 0, ',', '.') . "đ\n";
        }
        $reply .= "\nBạn có thể hỏi mình: <em>'Có món nào dưới 50k không?'</em>, <em>'Gợi ý món ăn trưa'</em>, hoặc <em>'Nhà hàng mở cửa mấy giờ?'</em> nhé!";
        return $reply;
    }

    /**
     * Ghi nhật ký vào database
     */
    private function logMessage(?int $userId, string $sessionId, string $message, string $reply, string $mode): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO chatbot_logs (user_id, session_id, user_message, bot_reply, mode)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $sessionId, $message, $reply, $mode]);
        } catch (Throwable $e) {
            error_log("Failed to log chatbot interaction: " . $e->getMessage());
        }
    }
}
