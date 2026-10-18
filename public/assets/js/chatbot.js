/**
 * Chatbot JavaScript
 * Xử lý giao diện Trợ lý món ngon AI, gửi câu hỏi, hiển thị typing và gợi ý nhanh
 */

const Chatbot = {
    window: null,
    body: null,
    input: null,
    isOpen: false,
    isWaiting: false,

    init() {
        this.window = document.getElementById('chatbot-window');
        this.body = document.getElementById('chatbot-body');
        this.input = document.getElementById('chatbot-input');

        if (!this.window || !this.body || !this.input) return;

        // Bắt sự kiện Enter trong ô nhập
        this.input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.send();
            }
        });

        // Tự động cuộn xuống dưới cùng
        this.scrollToBottom();
    },

    toggle() {
        this.isOpen = !this.isOpen;
        if (this.isOpen) {
            this.window.classList.add('is-open');
            setTimeout(() => this.input.focus(), 200);
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

    send() {
        const message = this.input.value.trim();
        if (message === '' || this.isWaiting) return;

        // 1. Thêm tin nhắn của người dùng vào giao diện
        this.appendMessage('user', message);
        this.input.value = '';

        // 2. Hiển thị trạng thái đang nhập (Typing dots)
        this.isWaiting = true;
        const typingEl = this.showTypingIndicator();

        // 3. Gửi API request tới Backend proxy
        fetch(window.APP_BASE_URL + '/api/chatbot/message', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ message: message })
        })
        .then(res => res.json())
        .then(data => {
            this.removeTypingIndicator(typingEl);
            this.isWaiting = false;

            if (data.success) {
                this.appendMessage('bot', data.reply, data.mode);
            } else {
                this.appendMessage('bot', data.message || 'Xin lỗi, hiện tại mình chưa thể trả lời câu hỏi này.');
            }
        })
        .catch(err => {
            console.error('Chatbot error:', err);
            this.removeTypingIndicator(typingEl);
            this.isWaiting = false;
            this.appendMessage('bot', 'Dạ, kết nối bị gián đoạn. Bạn vui lòng thử lại sau ít giây nhé!');
        });
    },

    appendMessage(sender, text, mode = 'mock') {
        const bubble = document.createElement('div');
        bubble.className = `chat-bubble chat-bubble-${sender}`;

        // Format văn bản: xuống dòng thành <br>, bullet points
        let formatted = text
            .replace(/\n/g, '<br>')
            .replace(/• /g, '&bull; ');

        if (sender === 'bot') {
            const modeBadge = (mode === 'ai') 
                ? '<span style="display:inline-block; font-size:0.65rem; background:#ede9fe; color:#5b21b6; padding:1px 6px; border-radius:10px; margin-bottom:4px; font-weight:700;">✨ Trợ lý AI</span><br>'
                : '<span style="display:inline-block; font-size:0.65rem; background:#ffedd5; color:#9a3412; padding:1px 6px; border-radius:10px; margin-bottom:4px; font-weight:700;">🍲 Trợ lý Món Ngon</span><br>';
            bubble.innerHTML = modeBadge + formatted;
        } else {
            bubble.textContent = text;
        }

        this.body.appendChild(bubble);
        this.scrollToBottom();
    },

    showTypingIndicator() {
        const typingBubble = document.createElement('div');
        typingBubble.className = 'chat-bubble chat-bubble-bot';
        typingBubble.id = 'typing-indicator';
        typingBubble.innerHTML = `
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
