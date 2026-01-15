<div class="food-order-container">
    <div class="food-header">
        <h1>📋 My Food Orders</h1>
        <div class="food-actions">
            <a href="<?= Config::get('URL'); ?>foodorder/index" class="btn btn-new-order">
                ➕ New Order
            </a>
            <a href="<?= Config::get('URL'); ?>message/index" class="btn btn-back">
                ← Back to Messages
            </a>
        </div>
    </div>

    <div class="food-content-box">
        <?php $this->renderFeedbackMessages(); ?>

        <?php if (!empty($this->orders)) { ?>
            <div class="orders-grid">
                <?php foreach ($this->orders as $order) {
                    $order_date = new DateTime($order->order_date);
                    $is_today = $order_date->format('Y-m-d') == date('Y-m-d');
                ?>
                    <div class="order-card <?= $is_today ? 'order-today' : ''; ?>">
                        <div class="order-card-header">
                            <div class="order-id">
                                Order #<?= $order->order_id; ?>
                                <?php if ($is_today) { ?>
                                    <span class="badge badge-today">Today</span>
                                <?php } ?>
                            </div>
                            <div class="order-status status-<?= htmlspecialchars($order->status); ?>">
                                <?= ucfirst(htmlspecialchars($order->status)); ?>
                            </div>
                        </div>

                        <div class="order-card-body">
                            <div class="order-item-name">
                                🍽️ <?= htmlspecialchars($order->food_item); ?>
                            </div>

                            <?php if ($order->is_menu) { ?>
                                <div class="order-menu-badge">
                                    ✨ Menu Option Included
                                </div>
                            <?php } ?>

                            <div class="order-details">
                                <div class="detail-row">
                                    <span class="detail-label">💰 Price:</span>
                                    <span class="detail-value">€<?= number_format($order->price, 2); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">⏱️ Delivery Time:</span>
                                    <span class="detail-value"><?= htmlspecialchars($order->delivery_time); ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="detail-label">📅 Order Date:</span>
                                    <span class="detail-value"><?= $order_date->format('M j, Y g:i A'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="order-card-footer">
                            <a href="<?= Config::get('URL'); ?>foodorder/receipt/<?= $order->order_id; ?>"
                               class="btn btn-receipt"
                               target="_blank">
                                📄 View Receipt
                            </a>
                            <?php if ($order->status == 'pending' && $is_today) { ?>
                                <a href="<?= Config::get('URL'); ?>foodorder/cancel/<?= $order->order_id; ?>"
                                   class="btn btn-cancel"
                                   onclick="return confirm('Are you sure you want to cancel this order?');">
                                    ❌ Cancel
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } else { ?>
            <div class="empty-state">
                <div class="empty-icon">🍽️</div>
                <h2 class="empty-title">No orders yet</h2>
                <p class="empty-text">Place your first food order and it will appear here.</p>
                <div class="empty-actions">
                    <a href="<?= Config::get('URL'); ?>foodorder/index" class="btn btn-primary-large">
                        Place Your First Order
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
</div>

<style>
/* Food Order Container */
.food-order-container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 0 20px;
}

.food-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e1e4e8;
    flex-wrap: wrap;
    gap: 15px;
}

.food-header h1 {
    margin: 0;
    font-size: 32px;
    color: #24292e;
}

.food-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s;
    display: inline-block;
    border: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-new-order {
    background: #28a745;
    color: white;
}

.btn-new-order:hover {
    background: #218838;
    transform: translateY(-2px);
}

.btn-back {
    background: #6a737d;
    color: white;
}

.btn-back:hover {
    background: #586069;
    transform: translateY(-2px);
}

.food-content-box {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Orders Grid */
.orders-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

/* Order Card */
.order-card {
    background: #f6f8fa;
    border: 2px solid #e1e4e8;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s;
}

.order-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.1);
}

.order-card.order-today {
    border-color: #28a745;
    background: linear-gradient(135deg, #f0fff4 0%, #f6f8fa 100%);
}

.order-card-header {
    background: white;
    padding: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 2px solid #e1e4e8;
}

.order-today .order-card-header {
    background: #28a745;
    color: white;
}

.order-id {
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.badge-today {
    background: white;
    color: #28a745;
}

.order-today .order-id {
    color: white;
}

.order-status {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #ffd700;
    color: #856404;
}

.status-confirmed {
    background: #28a745;
    color: white;
}

.status-delivered {
    background: #0366d6;
    color: white;
}

.status-cancelled {
    background: #d73a49;
    color: white;
}

/* Order Card Body */
.order-card-body {
    padding: 20px;
}

.order-item-name {
    font-size: 20px;
    font-weight: 700;
    color: #24292e;
    margin-bottom: 12px;
}

.order-menu-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: 16px;
}

.order-details {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    font-size: 15px;
}

.detail-label {
    color: #586069;
    font-weight: 500;
}

.detail-value {
    color: #24292e;
    font-weight: 600;
}

/* Order Card Footer */
.order-card-footer {
    padding: 16px;
    background: white;
    border-top: 2px solid #e1e4e8;
    display: flex;
    gap: 10px;
}

.btn-receipt {
    flex: 1;
    background: #0366d6;
    color: white;
    text-align: center;
}

.btn-receipt:hover {
    background: #0256c7;
}

.btn-cancel {
    flex: 1;
    background: #d73a49;
    color: white;
    text-align: center;
}

.btn-cancel:hover {
    background: #cb2431;
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
    background: #28a745;
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
    background: #218838;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .food-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .food-actions {
        width: 100%;
    }

    .btn {
        flex: 1;
        text-align: center;
    }

    .orders-grid {
        grid-template-columns: 1fr;
    }
}
</style>
