<div class="new-chat-container">
    <div class="new-chat-header">
        <h1>Start New Chat</h1>
        <a href="<?= Config::get('URL'); ?>message/index" class="btn-back">← Back to Messages</a>
    </div>

    <div class="new-chat-content">
        <?php $this->renderFeedbackMessages(); ?>

        <div class="new-chat-info">
            <h2>Select a user to start chatting</h2>
            <p>Click on any user below to start a conversation</p>
        </div>

        <?php if (!empty($this->users)) { ?>
            <div class="users-grid">
                <?php foreach ($this->users as $user) { ?>
                    <a href="<?= Config::get('URL') . 'message/conversation/' . $user->user_id; ?>"
                       class="user-select-button">
                        <div class="user-select-avatar">
                            <?php if (isset($user->user_avatar_link)) { ?>
                                <img src="<?= $user->user_avatar_link; ?>" alt="Avatar" />
                            <?php } else { ?>
                                <img src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($user->user_email))); ?>?s=60&d=mm" alt="Avatar" />
                            <?php } ?>
                        </div>
                        <div class="user-select-info">
                            <div class="user-select-name"><?= htmlspecialchars($user->user_name); ?></div>
                            <div class="user-select-email"><?= htmlspecialchars($user->user_email); ?></div>
                        </div>
                        <div class="user-select-arrow">→</div>
                    </a>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="no-users-state">
                <div class="no-users-icon">😔</div>
                <p>No users available to chat with</p>
            </div>
        <?php } ?>
    </div>
</div>

<style>
/* New Chat Container */
.new-chat-container {
    max-width: 1000px;
    margin: 20px auto;
    padding: 0 20px;
}

.new-chat-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e4e8;
}

.new-chat-header h1 {
    margin: 0;
    font-size: 32px;
    color: #24292e;
}

.btn-back {
    background: #6c757d;
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-block;
}

.btn-back:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.new-chat-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.new-chat-info {
    text-align: center;
    margin-bottom: 30px;
}

.new-chat-info h2 {
    font-size: 24px;
    color: #24292e;
    margin: 0 0 8px 0;
}

.new-chat-info p {
    color: #586069;
    font-size: 16px;
    margin: 0;
}

/* Users Grid */
.users-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
}

.user-select-button {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f8f9fa;
    border: 2px solid #e1e4e8;
    border-radius: 12px;
    text-decoration: none;
    color: #24292e;
    transition: all 0.3s;
    position: relative;
}

.user-select-button:hover {
    background: white;
    border-color: #0366d6;
    transform: translateX(8px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.user-select-button:hover .user-select-arrow {
    transform: translateX(4px);
    color: #0366d6;
}

.user-select-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 3px solid #e1e4e8;
    transition: border-color 0.3s;
}

.user-select-button:hover .user-select-avatar {
    border-color: #0366d6;
}

.user-select-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-select-info {
    flex: 1;
}

.user-select-name {
    font-size: 18px;
    font-weight: 700;
    color: #24292e;
    margin-bottom: 4px;
}

.user-select-email {
    font-size: 14px;
    color: #586069;
}

.user-select-arrow {
    font-size: 24px;
    color: #959da5;
    transition: all 0.3s;
}

/* No Users State */
.no-users-state {
    text-align: center;
    padding: 60px 20px;
}

.no-users-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

.no-users-state p {
    font-size: 18px;
    color: #586069;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .new-chat-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .users-grid {
        grid-template-columns: 1fr;
    }

    .user-select-button:hover {
        transform: translateY(-4px) translateX(0);
    }
}
</style>
