<?php
/**
 * Chatbot Controller
 * Xử lý tin nhắn khách hàng gửi tới Trợ lý ẩm thực AI
 */

class ChatbotController {
    private AIService $aiService;

    public function __construct() {
        $this->aiService = new AIService();
    }

    /**
     * API Nhận tin nhắn và trả lời
     */
    public function message(): void {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $message = trim($input['message'] ?? '');

        if ($message === '') {
            Response::json([
                'success' => false,
                'message' => 'Nội dung tin nhắn không được để trống.'
            ], 422);
        }

        // Giới hạn độ dài tin nhắn tối đa 500 ký tự để chống spam
        if (mb_strlen($message) > 500) {
            Response::json([
                'success' => false,
                'message' => 'Tin nhắn quá dài (tối đa 500 ký tự).'
            ], 422);
        }

        $sessionId = Session::get('chatbot_session_id');
        if (!$sessionId) {
            $sessionId = 'sess_' . bin2hex(random_bytes(8));
            Session::set('chatbot_session_id', $sessionId);
        }

        $userId = Session::isLoggedIn() ? (int)Session::user()['id'] : null;

        try {
            $result = $this->aiService->ask($message, $sessionId, $userId);
            Response::json([
                'success' => true,
                'reply'   => $result['reply'],
                'mode'    => $result['mode'], // 'ai' hoặc 'mock'
            ], 200);
        } catch (Throwable $e) {
            error_log("Chatbot Controller Error: " . $e->getMessage());
            Response::json([
                'success' => true,
                'reply'   => "Dạ, hiện tại Trợ lý AI đang bận một chút. Bạn có thể xem thực đơn trực tiếp hoặc liên hệ hotline nhà hàng để được phục vụ ngay nhé!",
                'mode'    => 'mock',
            ], 200);
        }
    }
}
