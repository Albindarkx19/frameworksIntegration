<div class="food-order-container">
    <div class="food-header">
        <h1>⚙️ All Food Orders (Admin)</h1>
        <div class="food-actions">
            <a href="<?= Config::get('URL'); ?>foodorder/index" class="btn btn-new-order">
                ➕ Place Order
            </a>
            <a href="<?= Config::get('URL'); ?>message/index" class="btn btn-back">
                ← Back to Messages
            </a>
        </div>
    </div>

    <div class="food-content-box">
        <?php $this->renderFeedbackMessages(); ?>

        <!-- Today's Orders Summary -->
        <div class="todays-summary">
            <h2>📅 Today's Orders</h2>

            <?php
            $has_todays_orders = false;
            foreach ($this->todays_orders as $time => $orders) {
                if (!empty($orders)) {
                    $has_todays_orders = true;
                    break;
                }
            }
            ?>

            <?php if ($has_todays_orders) { ?>
                <?php foreach (['11:30', '12:30'] as $time) { ?>
                    <?php if (!empty($this->todays_orders[$time])) { ?>
                        <div class="time-group">
                            <h3 class="time-header">🕐 Delivery Time: <?= $time; ?></h3>
                            <div class="orders-summary-table">
                                <table class="summary-table">
                                    <thead>
                                        <tr>
                                            <th>Order #</th>
                                            <th>Customer</th>
                                            <th>Food Item</th>
                                            <th>Menu</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($this->todays_orders[$time] as $order) { ?>
                                            <tr>
                                                <td>#<?= $order->order_id; ?></td>
                                                <td><?= htmlspecialchars($order->user_name); ?></td>
                                                <td><?= htmlspecialchars($order->food_item); ?></td>
                                                <td>
                                                    <?php if ($order->is_menu) { ?>
                                                        <span class="badge badge-menu">Yes</span>
                                                    <?php } else { ?>
                                                        <span class="badge badge-no-menu">No</span>
                                                    <?php } ?>
                                                </td>
                                                <td class="price-cell">€<?= number_format($order->price, 2); ?></td>
                                                <td>
                                                    <span class="status-badge status-<?= $order->status; ?>">
                                                        <?= ucfirst($order->status); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="<?= Config::get('URL'); ?>foodorder/receipt/<?= $order->order_id; ?>"
                                                       class="btn-small btn-view"
                                                       target="_blank">
                                                        View
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>

                                <?php
                                $time_total = 0;
                                foreach ($this->todays_orders[$time] as $order) {
                                    $time_total += $order->price;
                                }
                                ?>
                                <div class="time-total">
                                    Total for <?= $time; ?>: <strong>€<?= number_format($time_total, 2); ?></strong>
                                    (<?= count($this->todays_orders[$time]); ?> orders)
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php
                $grand_total = 0;
                $total_orders = 0;
                foreach ($this->todays_orders as $orders) {
                    foreach ($orders as $order) {
                        $grand_total += $order->price;
                        $total_orders++;
                    }
                }
                ?>
                <div class="grand-total">
                    <strong>Today's Grand Total:</strong> €<?= number_format($grand_total, 2); ?>
                    <span class="order-count">(<?= $total_orders; ?> total orders)</span>
                </div>
            <?php } else { ?>
                <div class="empty-today">
                    <p>No orders placed today yet.</p>
                </div>
            <?php } ?>
        </div>

        <!-- All Orders History -->
        <div class="all-orders-section">
            <h2>📋 All Orders History</h2>

            <?php if (!empty($this->orders)) { ?>
                <div class="orders-table-container">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Food Item</th>
                                <th>Menu</th>
                                <th>Delivery Time</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->orders as $order) {
                                $order_date = new DateTime($order->order_date);
                                $is_today = $order_date->format('Y-m-d') == date('Y-m-d');
                            ?>
                                <tr class="<?= $is_today ? 'row-today' : ''; ?>">
                                    <td>#<?= $order->order_id; ?></td>
                                    <td>
                                        <?= $order_date->format('M j, Y'); ?>
                                        <br>
                                        <small><?= $order_date->format('g:i A'); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($order->user_name); ?></td>
                                    <td><?= htmlspecialchars($order->food_item); ?></td>
                                    <td>
                                        <?php if ($order->is_menu) { ?>
                                            <span class="badge badge-menu">Yes</span>
                                        <?php } else { ?>
                                            <span class="badge badge-no-menu">No</span>
                                        <?php } ?>
                                    </td>
                                    <td class="delivery-time">🕐 <?= htmlspecialchars($order->delivery_time); ?></td>
                                    <td class="price-cell">€<?= number_format($order->price, 2); ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $order->status; ?>">
                                            <?= ucfirst($order->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= Config::get('URL'); ?>foodorder/receipt/<?= $order->order_id; ?>"
                                           class="btn-small btn-view"
                                           target="_blank">
                                            View Receipt
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="empty-state">
                    <p>No orders found in the system.</p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<style>
/* Food Order Container */
.food-order-container {
    max-width: 1400px;
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

/* Today's Summary */
.todays-summary {
    background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
    padding: 25px;
    border-radius: 12px;
    border: 2px solid #28a745;
    margin-bottom: 40px;
}

.todays-summary h2 {
    margin-bottom: 25px;
    color: #24292e;
    font-size: 24px;
}

.time-group {
    margin-bottom: 30px;
}

.time-header {
    background: #28a745;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 18px;
}

/* Tables */
.summary-table,
.orders-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
}

.summary-table th,
.orders-table th {
    background: #f6f8fa;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    color: #24292e;
    border-bottom: 2px solid #e1e4e8;
    font-size: 14px;
}

.summary-table td,
.orders-table td {
    padding: 12px;
    border-bottom: 1px solid #e1e4e8;
    color: #24292e;
    font-size: 14px;
}

.summary-table tr:last-child td,
.orders-table tr:last-child td {
    border-bottom: none;
}

.summary-table tr:hover,
.orders-table tr:hover {
    background: #f6f8fa;
}

.row-today {
    background: #f0fff4 !important;
}

.price-cell {
    font-weight: 700;
    color: #28a745;
}

.delivery-time {
    white-space: nowrap;
}

/* Badges */
.badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.badge-menu {
    background: #667eea;
    color: white;
}

.badge-no-menu {
    background: #e1e4e8;
    color: #586069;
}

.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    display: inline-block;
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

/* Small Buttons */
.btn-small {
    padding: 6px 14px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
    transition: all 0.2s;
}

.btn-view {
    background: #0366d6;
    color: white;
}

.btn-view:hover {
    background: #0256c7;
}

/* Totals */
.time-total {
    background: #f6f8fa;
    padding: 12px 20px;
    margin-top: 10px;
    border-radius: 6px;
    text-align: right;
    font-size: 16px;
}

.grand-total {
    background: #28a745;
    color: white;
    padding: 16px 24px;
    border-radius: 8px;
    text-align: center;
    font-size: 20px;
    margin-top: 20px;
}

.order-count {
    font-size: 16px;
    opacity: 0.9;
    margin-left: 10px;
}

/* All Orders Section */
.all-orders-section {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #e1e4e8;
}

.all-orders-section h2 {
    margin-bottom: 20px;
    color: #24292e;
    font-size: 24px;
}

.orders-table-container {
    overflow-x: auto;
}

/* Empty States */
.empty-today,
.empty-state {
    text-align: center;
    padding: 40px;
    color: #586069;
    font-size: 16px;
}

/* Responsive */
@media (max-width: 1024px) {
    .orders-table-container {
        overflow-x: scroll;
    }

    .summary-table,
    .orders-table {
        min-width: 800px;
    }
}

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
}
</style>
