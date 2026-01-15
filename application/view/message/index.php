<div class="messages-overview-container">
    <div class="messages-header">
        <h1>💬 Messages</h1>
        <div class="messages-actions">
            <a href="<?= Config::get('URL'); ?>foodorder/index" class="btn btn-food-order">
                🍕 Order Food
            </a>
            <a href="<?= Config::get('URL'); ?>message/newChat" class="btn btn-new-chat">
                ➕ New Chat
            </a>
            <a href="<?= Config::get('URL'); ?>message/createGroupForm" class="btn btn-new-group">
                👥 Create Group
            </a>
            <?php if (Session::get("user_account_type") == 7) { ?>
                <a href="<?= Config::get('URL'); ?>message/group" class="btn btn-admin">⚙️ Admin</a>
            <?php } ?>
        </div>
    </div>

    <div class="messages-content-box">
        <?php $this->renderFeedbackMessages(); ?>

        <?php
        $has_items = false;

        // Show groups first
        if (!empty($this->groups)) {
            $has_items = true;
        ?>
            <div class="message-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="section-icon">👥</span> Groups
                    </h2>
                </div>

                <div class="user-buttons-grid">
                    <?php foreach ($this->groups as $group) { ?>
                        <a href="<?= Config::get('URL') . 'message/groupConversation/' . $group->group_id; ?>"
                           class="user-button-card group-button">
                            <div class="user-button-header">
                                <span class="user-button-icon">👥</span>
                                <span class="user-button-title"><?= htmlspecialchars($group->group_name); ?></span>
                                <?php if ($group->unread_count > 0) { ?>
                                    <span class="user-button-badge"><?= $group->unread_count; ?></span>
                                <?php } ?>
                            </div>
                            <?php if ($group->last_message) { ?>
                                <div class="user-button-preview"><?= htmlspecialchars(substr($group->last_message, 0, 40)) . (strlen($group->last_message) > 40 ? '...' : ''); ?></div>
                            <?php } ?>
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php
        }

        // Show individual conversations
        if (!empty($this->conversations)) {
            $has_items = true;
        ?>
            <div class="message-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <span class="section-icon">💬</span> Direct Messages
                    </h2>
                </div>

                <div class="user-buttons-grid">
                    <?php foreach ($this->conversations as $conv) { ?>
                        <a href="<?= Config::get('URL') . 'message/conversation/' . $conv->user_id; ?>"
                           class="user-button-card">
                            <div class="user-button-header">
                                <span class="user-button-name"><?= htmlspecialchars($conv->user_name); ?></span>
                                <?php if ($conv->unread_count > 0) { ?>
                                    <span class="user-button-badge"><?= $conv->unread_count; ?></span>
                                <?php } ?>
                            </div>
                            <div class="user-button-preview">
                                <?php
                                $preview = htmlspecialchars($conv->last_message);
                                echo ($conv->last_sender_id == Session::get('user_id') ? 'You: ' : '') . substr($preview, 0, 40) . (strlen($preview) > 40 ? '...' : '');
                                ?>
                            </div>
                            <div class="user-button-time">⏱️ <?= date('M j, g:i A', strtotime($conv->last_message_time)); ?></div>
                        </a>
                    <?php } ?>
                </div>
            </div>
        <?php
        }

        if (!$has_items) {
        ?>
            <div class="empty-state">
                <div class="empty-icon">💬</div>
                <h2 class="empty-title">No messages yet</h2>
                <p class="empty-text">Start a conversation by clicking "New Chat" or create a group to chat with multiple people.</p>
                <div class="empty-actions">
                    <a href="<?= Config::get('URL'); ?>message/newChat" class="btn btn-primary-large">Start Chatting</a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<style>
/* Messages Overview Container */
.messages-overview-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 20px;
}

.messages-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e4e8;
}

.messages-header h1 {
    margin: 0;
    font-size: 32px;
    color: #24292e;
}

.messages-actions {
    display: flex;
    gap: 12px;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-block;
}

.btn-food-order {
    background: #28a745;
    color: white;
}

.btn-food-order:hover {
    background: #218838;
    transform: translateY(-2px);
}

.btn-new-chat {
    background: #0366d6;
    color: white;
}

.btn-new-chat:hover {
    background: #0256c7;
    transform: translateY(-2px);
}

.btn-new-group {
    background: #28a745;
    color: white;
}

.btn-new-group:hover {
    background: #218838;
    transform: translateY(-2px);
}

.btn-admin {
    background: #6f42c1;
    color: white;
}

.btn-admin:hover {
    background: #5a32a3;
    transform: translateY(-2px);
}

.messages-content-box {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Message Sections */
.message-section {
    margin-bottom: 40px;
}

.message-section:last-child {
    margin-bottom: 0;
}

.section-header {
    margin-bottom: 20px;
}

.section-title {
    font-size: 20px;
    color: #24292e;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.section-icon {
    font-size: 24px;
}

/* User Buttons Grid */
.user-buttons-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 16px;
}

.user-button-card {
    background: #f8f9fa;
    border: 2px solid #e1e4e8;
    border-radius: 12px;
    padding: 16px;
    text-decoration: none;
    color: #24292e;
    transition: all 0.3s;
    display: block;
    position: relative;
    overflow: hidden;
}

.user-button-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-color: #0366d6;
    background: white;
}

.user-button-card.group-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}

.user-button-card.group-button:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
}

.user-button-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    position: relative;
}

.user-button-icon {
    font-size: 20px;
}

.user-button-title,
.user-button-name {
    font-size: 18px;
    font-weight: 700;
    flex: 1;
}

.user-button-badge {
    background: #d73a49;
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    position: absolute;
    top: 0;
    right: 0;
}

.group-button .user-button-badge {
    background: white;
    color: #667eea;
}

.user-button-preview {
    color: #586069;
    font-size: 14px;
    margin-bottom: 4px;
    line-height: 1.4;
}

.group-button .user-button-preview {
    color: rgba(255, 255, 255, 0.9);
}

.user-button-time {
    color: #959da5;
    font-size: 12px;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

.empty-title {
    font-size: 28px;
    color: #24292e;
    margin: 0 0 12px 0;
}

.empty-text {
    font-size: 16px;
    color: #586069;
    margin: 0 0 30px 0;
}

.btn-primary-large {
    background: #0366d6;
    color: white;
    padding: 14px 28px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    display: inline-block;
    transition: all 0.2s;
}

.btn-primary-large:hover {
    background: #0256c7;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .messages-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .messages-actions {
        flex-wrap: wrap;
        width: 100%;
    }

    .btn {
        flex: 1;
        text-align: center;
    }

    .user-buttons-grid {
        grid-template-columns: 1fr;
    }
}
</style>
