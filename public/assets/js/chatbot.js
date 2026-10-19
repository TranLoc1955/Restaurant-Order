/**
 * Chatbot JavaScript
 * Xử lý giao diện Trợ lý món ngon AI hội thoại thực sự
 * Hỗ trợ: Nhập nhiều dòng (Shift+Enter), Enter gửi tin, phục hồi lịch sử đa phiên,
 * hiển thị trạng thái AI / Demo / Error, nút thử lại (Retry) và làm mới hội thoại.
 */

const Chatbot = {
    window: null,
    body: null,
    input: null,
    sendBtn: null,
    isOpen: false,
    isWaiting: false,
    lastUserMessage: '',

    init() {
        this.window = document.getElementById('chatbot-window');
        this.body = document.getElementById('chatbot-body');
        this.input = document.getElementById('chatbot-input');
        this.sendBtn = document.getElementById('chatbot-send-btn');

        if (!this.window || !this.body || !this.input) return;

        // Xử lý phím Enter gửi tin, Shift+Enter xuống dòng
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.send();
            }
        });

        // Tự động co giãn chiều cao của Textarea khi gõ
        this.input.addEventListener('input', () => {
            this.input.style.height = 'auto';
            const newHeight = Math.min(this.input.scrollHeight, 90);
            this.input.style.height = `${newHeight}px`;
        });

        // Tải lại lịch sử trò chuyện trong phiên từ Backend để không bị mất khi chuyển trang
        this.loadHistory();

        // Tự động cuộn xuống dưới cùng
        this.scrollToBottom();
    },

    toggle() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.window.classList.add('is-open');
            setTimeout(() => {
                this.input.focus();
                this.scrollToBottom();
            }, 200);
        } else {
            this.window.classList.remove('is-open');
        }
    },

    close() {
        this.isOpen = false;
        this.window.classList.remove('is-open');
    },

    askPrompt(text) {
        if (this.isWaiting) return;
        this.input.value = text;
        this.send();
    },

    send(retryMessage = null) {
        const message = retryMessage !== null ? retryMessage.trim() : this.input.value.trim();
        if (message === '' || this.isWaiting) return;

        this.lastUserMessage = message;

        // 1. Thêm tin nhắn của người dùng vào giao diện nếu không phải retry
        if (retryMessage === null) {
            this.appendMessage('user', message);
            this.input.value = '';
            this.input.style.height = 'auto';
        }

        // 2. Hiển thị trạng thái đang trả lời (Typing indicator)
        this.isWaiting = true;
        if (this.sendBtn) this.sendBtn.disabled = true;
        const typingEl = this.showTypingIndicator();

        // 3. Gửi API request tới Backend Proxy
        fetch(window.APP_BASE_URL + '/api/chatbot/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => {
            if (!res.ok) {
                throw new Error(`HTTP Error ${res.status}`);
            }
            return res.json();
        })
        .then(data => {
            this.removeTypingIndicator(typingEl);
            this.isWaiting = false;
            if (this.sendBtn) this.sendBtn.disabled = false;

            if (data.success) {
                this.appendMessage('bot', data.reply, data.mode);
            } else {
                this.appendMessage('bot', data.message || 'Xin lỗi, hiện tại Trợ lý AI chưa thể xử lý yêu cầu này.', 'error', true);
            }
        })
        .catch(err => {
            console.error('Chatbot network error:', err);
            this.removeTypingIndicator(typingEl);
            this.isWaiting = false;
            if (this.sendBtn) this.sendBtn.disabled = false;

            this.appendMessage(
                'bot', 
                'Dạ, kết nối tới máy chủ bị gián đoạn hoặc quá thời gian phản hồi. Bạn vui lòng kiểm tra lại kết nối mạng và thử lại nhé!',
                'error',
                true // Có nút thử lại
            );
        });
    },

    retry() {
        if (!this.lastUserMessage || this.isWaiting) return;
        this.send(this.lastUserMessage);
    },

    clear() {
        if (!confirm('Bạn có muốn bắt đầu một cuộc trò chuyện mới không? Lịch sử trong phiên này sẽ được làm mới.')) {
            return;
        }

        fetch(window.APP_BASE_URL + '/api/chatbot/clear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            // Xóa sạch nội dung giao diện và nạp lại lời chào
            this.body.innerHTML = `
                <div class="chat-bubble chat-bubble-bot">
                    <span class="chat-badge chat-badge-demo">🍲 Trợ lý Món Ngon</span><br>
                    Chào bạn! Mình là Trợ lý món ngon của <strong>Nhà Hàng Trần Lộc</strong>.<br><br>
                    Bạn có thể hỏi mình về thực đơn, gợi ý món ăn theo ngân sách, giờ mở cửa hay phí giao hàng nhé!
                </div>
            `;
            this.lastUserMessage = '';
        })
        .catch(err => {
            console.error('Failed to clear chat:', err);
        });
    },

    loadHistory() {
        fetch(window.APP_BASE_URL + '/api/chatbot/history')
            .then(res => res.json())
            .then(data => {
                if (data.success && Array.isArray(data.history) && data.history.length > 0) {
                    // Giữ lại lời chào đầu tiên nếu muốn, hoặc hiển thị toàn bộ lịch sử đã có
                    this.body.innerHTML = '';
                    data.history.forEach(item => {
                        if (item.user_message) {
                            this.appendMessage('user', item.user_message, 'user', false, false);
                        }
                        if (item.bot_reply) {
                            this.appendMessage('bot', item.bot_reply, item.mode, false, false);
                        }
                    });
                    this.scrollToBottom();
                }
            })
            .catch(err => {
                // Nếu lỗi thì giữ nguyên màn hình mặc định
                console.log('No previous history loaded or offline.');
            });
    },

    formatText(text) {
        if (!text) return '';

        // Chống XSS thô trước khi parse định dạng cơ bản
        let escaped = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // Cho phép các thẻ HTML an toàn đã được backend cố ý sinh ra
        escaped = escaped
            .replace(/&lt;strong&gt;/g, '<strong>')
            .replace(/&lt;\/strong&gt;/g, '</strong>')
            .replace(/&lt;em&gt;/g, '<em>')
            .replace(/&lt;\/em&gt;/g, '</em>')
            .replace(/&lt;b&gt;/g, '<b>')
            .replace(/&lt;\/b&gt;/g, '</b>');

        // Định dạng Markdown cơ bản: **in đậm**, *nghiêng*, dòng bullet
        let formatted = escaped
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/`([^`]+)`/g, '<code style="background:#f1f5f9; padding:1px 4px; border-radius:3px; font-size:0.8em; color:#0f172a;">$1</code>')
            .replace(/\n/g, '<br>')
            .replace(/(^|<br>)(•|-)\s+/g, '$1&bull; ');

        return formatted;
    },

    appendMessage(sender, text, mode = 'demo', hasRetry = false, doScroll = true) {
        const bubble = document.createElement('div');
        bubble.className = `chat-bubble chat-bubble-${sender}`;

        if (sender === 'bot') {
            let badgeHtml = '';
            if (mode === 'ai') {
                badgeHtml = '<span class="chat-badge chat-badge-ai">✨ Trợ lý AI (Gemini)</span><br>';
            } else if (mode === 'error') {
                badgeHtml = '<span class="chat-badge chat-badge-error">⚠️ Lỗi kết nối AI</span><br>';
            } else {
                badgeHtml = '<span class="chat-badge chat-badge-demo">📋 Chế độ Thử nghiệm (Demo)</span><br>';
            }

            let contentHtml = badgeHtml + this.formatText(text);

            if (hasRetry) {
                contentHtml += `<br><button type="button" class="chatbot-retry-btn" onclick="Chatbot.retry()">↻ Thử gửi lại</button>`;
            }

            bubble.innerHTML = contentHtml;
        } else {
            bubble.textContent = text;
        }

        this.body.appendChild(bubble);
        if (doScroll) {
            this.scrollToBottom();
        }
    },

    showTypingIndicator() {
        const typingBubble = document.createElement('div');
        typingBubble.className = 'chat-bubble chat-bubble-bot';
        typingBubble.id = 'typing-indicator';
        typingBubble.innerHTML = `
            <div style="font-size:0.75rem; color:var(--text-muted); margin-bottom:4px;">✨ Trợ lý đang soạn câu trả lời...</div>
            <div class="typing-dots">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </div>
        `;
        this.body.appendChild(typingBubble);
        this.scrollToBottom();
        return typingBubble;
    },

    removeTypingIndicator(el) {
        if (el && el.parentElement) {
            el.remove();
        }
    },

    scrollToBottom() {
        if (this.body) {
            this.body.scrollTop = this.body.scrollHeight;
        }
    }
};

document.addEventListener('DOMContentLoaded', () => {
    Chatbot.init();
});
