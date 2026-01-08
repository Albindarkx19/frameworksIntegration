<div class="chat-container">
    <div class="chat-sidebar">
        <div class="chat-sidebar-header">
            <h2>Messages</h2>
            <a href="<?= Config::get('URL'); ?>message/newChat" class="btn-new-chat-small">+ New</a>
        </div>

        <div class="chat-user-buttons">
            <!-- Current active user -->
            <a href="<?= Config::get('URL') . 'message/conversation/' . $this->other_user->user_id; ?>"
               class="user-chat-button active">
                <span class="user-button-name"><?= htmlspecialchars($this->other_user->user_name); ?></span>
            </a>

            <!-- Other conversations -->
            <?php if (!empty($this->conversations)) { ?>
                <?php foreach ($this->conversations as $conv) { ?>
                    <?php if ($conv->user_id == $this->other_user->user_id) continue; ?>
                    <a href="<?= Config::get('URL') . 'message/conversation/' . $conv->user_id; ?>"
                       class="user-chat-button">
                        <span class="user-button-name"><?= htmlspecialchars($conv->user_name); ?></span>
                        <?php if ($conv->unread_count > 0) { ?>
                            <span class="user-unread-badge"><?= $conv->unread_count; ?></span>
                        <?php } ?>
                    </a>
                <?php } ?>
            <?php } ?>
        </div>

        <div class="chat-sidebar-footer">
            <a href="<?= Config::get('URL'); ?>message/index" class="btn-back-messages">← All Messages</a>
        </div>
    </div>

    <div class="chat-main">
        <!-- Chat Header -->
        <div class="chat-header">
            <div class="chat-header-user">
                <div class="chat-header-avatar">
                    <?php if ($this->other_user->user_has_avatar) { ?>
                        <img src="<?= Config::get('URL') . 'avatars/' . $this->other_user->user_id . '.jpg'; ?>" alt="Avatar" />
                    <?php } else { ?>
                        <img src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($this->other_user->user_email))); ?>?s=50&d=mm" alt="Avatar" />
                    <?php } ?>
                </div>
                <div class="chat-header-info">
                    <h3><?= htmlspecialchars($this->other_user->user_name); ?></h3>
                    <span class="chat-header-email"><?= htmlspecialchars($this->other_user->user_email); ?></span>
                </div>
            </div>
        </div>

        <!-- Chat Messages Area -->
        <div class="chat-messages-area" id="chatMessagesArea">
            <?php $this->renderFeedbackMessages(); ?>

            <?php if (!empty($this->messages)) { ?>
                <?php
                $current_date = '';
                foreach ($this->messages as $msg) {
                    $msg_date = date('Y-m-d', strtotime($msg->created_at));
                    if ($msg_date != $current_date) {
                        $current_date = $msg_date;
                        $date_label = date('F j, Y', strtotime($msg->created_at));
                        if ($msg_date == date('Y-m-d')) {
                            $date_label = 'Today';
                        } elseif ($msg_date == date('Y-m-d', strtotime('-1 day'))) {
                            $date_label = 'Yesterday';
                        }
                ?>
                        <div class="chat-date-divider"><?= $date_label; ?></div>
                <?php
                    }

                    $is_mine = ($msg->sender_id == Session::get('user_id'));
                ?>
                    <div class="chat-message <?= $is_mine ? 'message-mine' : 'message-theirs'; ?>">
                        <div class="message-bubble">
                            <div class="message-text"><?= nl2br(htmlspecialchars($msg->message_text)); ?></div>
                            <div class="message-timestamp"><?= date('g:i A', strtotime($msg->created_at)); ?></div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="chat-empty-state">
                    <p>No messages yet. Start the conversation!</p>
                </div>
            <?php } ?>
        </div>

        <!-- Chat Input Area -->
        <div class="chat-input-area">
            <form action="<?= Config::get('URL'); ?>message/send" method="post" id="chatForm">
                <input type="hidden" name="receiver_id" value="<?= $this->other_user->user_id; ?>" />
                <div class="chat-input-wrapper">
                    <textarea
                        name="message_text"
                        id="messageInput"
                        placeholder="Type your message..."
                        rows="1"
                        required></textarea>
                    <button type="submit" class="btn-send-message">Send</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Chat Container Layout */
.chat-container {
    display: flex;
    height: calc(100vh - 100px);
    max-width: 1400px;
    margin: 20px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

/* Sidebar */
.chat-sidebar {
    width: 280px;
    background: #f8f9fa;
    border-right: 1px solid #e1e4e8;
    display: flex;
    flex-direction: column;
}

.chat-sidebar-header {
    padding: 20px;
    border-bottom: 1px solid #e1e4e8;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-sidebar-header h2 {
    margin: 0;
    font-size: 20px;
    color: #24292e;
}

.btn-new-chat-small {
    background: #0366d6;
    color: white;
    padding: 6px 12px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
}

.btn-new-chat-small:hover {
    background: #0256c7;
}

/* User Buttons */
.chat-user-buttons {
    flex: 1;
    overflow-y: auto;
    padding: 10px;
}

.user-chat-button {
    display: block;
    width: 100%;
    padding: 12px 16px;
    margin-bottom: 8px;
    background: white;
    border: 2px solid #e1e4e8;
    border-radius: 8px;
    text-decoration: none;
    color: #24292e;
    font-weight: 500;
    transition: all 0.2s;
    position: relative;
}

.user-chat-button:hover {
    background: #f6f8fa;
    border-color: #0366d6;
    transform: translateX(4px);
}

.user-chat-button.active {
    background: #0366d6;
    color: white;
    border-color: #0366d6;
}

.user-button-name {
    display: block;
}

.user-unread-badge {
    position: absolute;
    top: 8px;
    right: 12px;
    background: #d73a49;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: bold;
}

.user-chat-button.active .user-unread-badge {
    background: white;
    color: #0366d6;
}

.chat-sidebar-footer {
    padding: 20px;
    border-top: 1px solid #e1e4e8;
}

.btn-back-messages {
    display: block;
    text-align: center;
    padding: 10px;
    background: white;
    border: 1px solid #e1e4e8;
    border-radius: 6px;
    text-decoration: none;
    color: #586069;
    font-weight: 500;
}

.btn-back-messages:hover {
    background: #f6f8fa;
}

/* Chat Main Area */
.chat-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Chat Header */
.chat-header {
    background: rgba(255, 255, 255, 0.95);
    padding: 16px 24px;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}

.chat-header-user {
    display: flex;
    align-items: center;
}

.chat-header-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 16px;
}

.chat-header-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.chat-header-info h3 {
    margin: 0 0 4px 0;
    font-size: 18px;
    color: #24292e;
}

.chat-header-email {
    color: #586069;
    font-size: 14px;
}

/* Messages Area */
.chat-messages-area {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.chat-date-divider {
    text-align: center;
    color: rgba(255, 255, 255, 0.9);
    font-size: 13px;
    margin: 20px 0;
    font-weight: 500;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.chat-message {
    margin-bottom: 16px;
    display: flex;
}

.chat-message.message-mine {
    justify-content: flex-start;
}

.chat-message.message-theirs {
    justify-content: flex-end;
}

.message-bubble {
    max-width: 60%;
    padding: 12px 16px;
    border-radius: 18px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.message-mine .message-bubble {
    background: white;
    color: #24292e;
    border-bottom-left-radius: 4px;
}

.message-theirs .message-bubble {
    background: #0366d6;
    color: white;
    border-bottom-right-radius: 4px;
}

.message-text {
    margin-bottom: 4px;
    line-height: 1.5;
}

.message-timestamp {
    font-size: 11px;
    opacity: 0.7;
}

.chat-empty-state {
    text-align: center;
    color: rgba(255, 255, 255, 0.9);
    padding: 40px;
    font-size: 16px;
}

/* Input Area */
.chat-input-area {
    background: rgba(255, 255, 255, 0.95);
    padding: 16px 24px;
    border-top: 1px solid rgba(0,0,0,0.1);
}

.chat-input-wrapper {
    display: flex;
    gap: 12px;
    align-items: flex-end;
}

#messageInput {
    flex: 1;
    padding: 12px 16px;
    border: 2px solid #e1e4e8;
    border-radius: 24px;
    font-size: 14px;
    font-family: inherit;
    resize: none;
    max-height: 120px;
    outline: none;
}

#messageInput:focus {
    border-color: #0366d6;
}

.btn-send-message {
    background: #0366d6;
    color: white;
    border: none;
    padding: 12px 24px;
    border-radius: 24px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-send-message:hover {
    background: #0256c7;
}

/* Scrollbar Styling */
.chat-user-buttons::-webkit-scrollbar,
.chat-messages-area::-webkit-scrollbar {
    width: 8px;
}

.chat-user-buttons::-webkit-scrollbar-track {
    background: transparent;
}

.chat-user-buttons::-webkit-scrollbar-thumb {
    background: #cbd2d9;
    border-radius: 4px;
}

.chat-messages-area::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

/* Responsive */
@media (max-width: 768px) {
    .chat-container {
        height: calc(100vh - 60px);
        margin: 10px;
    }

    .chat-sidebar {
        width: 220px;
    }

    .message-bubble {
        max-width: 80%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chatMessagesArea');
    const messageInput = document.getElementById('messageInput');
    const chatForm = document.getElementById('chatForm');

    // Auto-scroll to bottom
    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Focus on input
    if (messageInput) {
        messageInput.focus();

        // Auto-resize textarea
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });

        // Handle Enter key (Send on Enter, new line on Shift+Enter)
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.submit();
            }
        });
    }
});
</script>
