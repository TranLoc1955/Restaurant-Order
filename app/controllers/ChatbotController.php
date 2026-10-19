<?php
/**
 * Chatbot Controller
 * Xử lý tin nhắn khách hàng gửi tới Trợ lý ẩm thực AI
 * Quản lý lịch sử hội thoại, nhận tin nhắn, xóa phiên hội thoại
 */

class ChatbotController {
    private AIService $aiService;

    public function __construct() {
        $this->aiService = new AIService();
    }

    /**
     * Lấy hoặc khởi tạo Session ID duy nhất cho phiên trò chuyện
     */
    private function getOrCreateSessionId(): string {
        $sessionId = Session::get('chatbot_session_id');
        if (!$sessionId) {
            $sessionId = 'sess_' . bin2hex(random_bytes(8));
            Session::set('chatbot_session_id', $sessionId);
        }
        return $sessionId;
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

        // Giới hạn độ dài tin nhắn tối đa 500 ký tự để chống spam và tràn token
        if (mb_strlen($message) > 500) {
            Response::json([
                'success' => false,
                'message' => 'Tin nhắn quá dài (tối đa 500 ký tự).'
            ], 422);
        }

        $sessionId = $this->getOrCreateSessionId();
        $userId = Session::isLoggedIn() ? (int)Session::user()['id'] : null;

        try {
            $result = $this->aiService->ask($message, $sessionId, $userId);
            Response::json([
                'success'   => true,
                'reply'     => $result['reply'],
                'mode'      => $result['mode'], // 'ai', 'demo' hoặc 'error'
                'is_ai'     => $result['is_ai'] ?? ($result['mode'] === 'ai'),
                'provider'  => ($result['mode'] === 'ai') ? 'Google Gemini' : 'Local Grounding',
            ], 200);
        } catch (Throwable $e) {
            error_log("Chatbot Controller Error: " . $e->getMessage());
            Response::json([
                'success'   => true,
                'reply'     => "Dạ, hiện tại Trợ lý AI đang bận xử lý hoặc gặp sự cố kết nối. Bạn có thể xem thực đơn trực tiếp hoặc liên hệ hotline nhà hàng để được hỗ trợ nhanh nhất nhé!",
                'mode'      => 'error',
                'is_ai'     => false,
            ], 200);
        }
    }

    /**
     * API Lấy lịch sử tin nhắn trong phiên hiện tại (phục hồi chat khi đổi trang/tải lại trang)
     */
    public function history(): void {
        $sessionId = Session::get('chatbot_session_id');
        if (!$sessionId) {
            Response::json([
                'success' => true,
                'history' => []
            ], 200);
        }

        $history = $this->aiService->getSessionHistory($sessionId, 30);
        Response::json([
            'success' => true,
            'history' => $history
        ], 200);
    }

    /**
     * API Làm mới / Xóa cuộc trò chuyện để bắt đầu cuộc trò chuyện mới
     */
    public function clear(): void {
        $sessionId = Session::get('chatbot_session_id');
        if ($sessionId) {
            $this->aiService->clearSessionHistory($sessionId);
            // Tạo session ID mới cho phiên tiếp theo
            $newSessionId = 'sess_' . bin2hex(random_bytes(8));
            Session::set('chatbot_session_id', $newSessionId);
        }

        Response::json([
            'success' => true,
            'message' => 'Đã làm mới cuộc hội thoại thành công.'
        ], 200);
    }
}
