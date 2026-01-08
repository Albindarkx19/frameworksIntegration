<div class="create-group-container">
    <div class="create-group-header">
        <h1>Create New Group</h1>
        <a href="<?= Config::get('URL'); ?>message/index" class="btn-back">← Back to Messages</a>
    </div>

    <div class="create-group-content">
        <?php $this->renderFeedbackMessages(); ?>

        <form action="<?= Config::get('URL'); ?>message/createGroup" method="post" class="create-group-form">

            <div class="form-section">
                <div class="form-section-header">
                    <h2>Group Details</h2>
                    <p>Enter a name for your group</p>
                </div>

                <div class="form-input-wrapper">
                    <label for="group_name">Group Name *</label>
                    <input type="text"
                           id="group_name"
                           name="group_name"
                           placeholder="e.g., Project Team, Study Group..."
                           required
                           maxlength="100"
                           class="group-name-input" />
                </div>
            </div>

            <div class="form-section">
                <div class="form-section-header">
                    <h2>Select Members</h2>
                    <p>Choose who you want to add to this group (you will be added automatically)</p>
                </div>

                <?php if (!empty($this->users)) { ?>
                    <div class="members-grid">
                        <?php foreach ($this->users as $user) { ?>
                            <label class="member-select-card">
                                <input type="checkbox" name="members[]" value="<?= $user->user_id; ?>" class="member-checkbox-input" />
                                <div class="member-card-content">
                                    <div class="member-card-avatar">
                                        <?php if (isset($user->user_avatar_link)) { ?>
                                            <img src="<?= $user->user_avatar_link; ?>" alt="Avatar" />
                                        <?php } else { ?>
                                            <img src="https://www.gravatar.com/avatar/<?= md5(strtolower(trim($user->user_email))); ?>?s=50&d=mm" alt="Avatar" />
                                        <?php } ?>
                                    </div>
                                    <div class="member-card-info">
                                        <div class="member-card-name"><?= htmlspecialchars($user->user_name); ?></div>
                                        <div class="member-card-email"><?= htmlspecialchars($user->user_email); ?></div>
                                    </div>
                                    <div class="member-card-checkbox">
                                        <span class="checkbox-icon">✓</span>
                                    </div>
                                </div>
                            </label>
                        <?php } ?>
                    </div>
                <?php } else { ?>
                    <div class="no-members-state">
                        <div class="no-members-icon">👥</div>
                        <p>No other users available to add to the group</p>
                    </div>
                <?php } ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-create-group">
                    👥 Create Group
                </button>
                <a href="<?= Config::get('URL'); ?>message/index" class="btn btn-cancel">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<style>
/* Create Group Container */
.create-group-container {
    max-width: 900px;
    margin: 20px auto;
    padding: 0 20px;
}

.create-group-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e4e8;
}

.create-group-header h1 {
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

.create-group-content {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Form Sections */
.form-section {
    margin-bottom: 40px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.form-section-header {
    margin-bottom: 20px;
}

.form-section-header h2 {
    font-size: 20px;
    color: #24292e;
    margin: 0 0 8px 0;
}

.form-section-header p {
    color: #586069;
    font-size: 14px;
    margin: 0;
}

/* Group Name Input */
.form-input-wrapper {
    margin-bottom: 20px;
}

.form-input-wrapper label {
    display: block;
    font-weight: 600;
    color: #24292e;
    margin-bottom: 8px;
    font-size: 14px;
}

.group-name-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e4e8;
    border-radius: 8px;
    font-size: 16px;
    font-family: inherit;
    transition: all 0.2s;
    outline: none;
}

.group-name-input:focus {
    border-color: #0366d6;
    box-shadow: 0 0 0 3px rgba(3, 102, 214, 0.1);
}

/* Members Grid */
.members-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
}

.member-select-card {
    cursor: pointer;
    display: block;
}

.member-checkbox-input {
    display: none;
}

.member-card-content {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #f8f9fa;
    border: 2px solid #e1e4e8;
    border-radius: 8px;
    transition: all 0.2s;
}

.member-select-card:hover .member-card-content {
    background: white;
    border-color: #0366d6;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.member-checkbox-input:checked + .member-card-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: #667eea;
    color: white;
}

.member-card-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    border: 2px solid #e1e4e8;
    transition: border-color 0.2s;
}

.member-checkbox-input:checked + .member-card-content .member-card-avatar {
    border-color: white;
}

.member-card-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.member-card-info {
    flex: 1;
}

.member-card-name {
    font-size: 16px;
    font-weight: 600;
    color: #24292e;
    margin-bottom: 2px;
}

.member-checkbox-input:checked + .member-card-content .member-card-name {
    color: white;
}

.member-card-email {
    font-size: 13px;
    color: #586069;
}

.member-checkbox-input:checked + .member-card-content .member-card-email {
    color: rgba(255, 255, 255, 0.9);
}

.member-card-checkbox {
    width: 24px;
    height: 24px;
    border: 2px solid #e1e4e8;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.member-select-card:hover .member-card-checkbox {
    border-color: #0366d6;
}

.member-checkbox-input:checked + .member-card-content .member-card-checkbox {
    background: white;
    border-color: white;
}

.checkbox-icon {
    color: white;
    font-size: 16px;
    font-weight: bold;
    opacity: 0;
    transition: opacity 0.2s;
}

.member-checkbox-input:checked + .member-card-content .checkbox-icon {
    opacity: 1;
    color: #667eea;
}

/* No Members State */
.no-members-state {
    text-align: center;
    padding: 60px 20px;
}

.no-members-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

.no-members-state p {
    font-size: 16px;
    color: #586069;
    margin: 0;
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 12px;
    padding-top: 30px;
    border-top: 1px solid #e1e4e8;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
    transition: all 0.2s;
    display: inline-block;
    cursor: pointer;
    border: none;
    font-family: inherit;
}

.btn-create-group {
    background: #28a745;
    color: white;
    flex: 1;
}

.btn-create-group:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .create-group-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }

    .members-grid {
        grid-template-columns: 1fr;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        text-align: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxInputs = document.querySelectorAll('.member-checkbox-input');

    checkboxInputs.forEach(function(checkbox) {
        checkbox.addEventListener('change', function() {
            // Animation for visual feedback
            const card = this.nextElementSibling;
            if (this.checked) {
                card.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    card.style.transform = 'translateY(-2px)';
                }, 100);
            }
        });
    });
});
</script>
