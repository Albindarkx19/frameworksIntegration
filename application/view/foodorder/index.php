<div class="food-order-container">
    <div class="food-header">
        <h1>🍕 Food Order System</h1>
        <div class="food-actions">
            <a href="<?= Config::get('URL'); ?>foodorder/myorders" class="btn btn-my-orders">
                📋 My Orders
            </a>
            <?php if (Session::get("user_account_type") == 7) { ?>
                <a href="<?= Config::get('URL'); ?>foodorder/allorders" class="btn btn-admin">
                    ⚙️ All Orders (Admin)
                </a>
            <?php } ?>
            <a href="<?= Config::get('URL'); ?>message/index" class="btn btn-back">
                ← Back to Messages
            </a>
        </div>
    </div>

    <div class="food-content-box">
        <?php $this->renderFeedbackMessages(); ?>

        <div class="order-form-container">
            <h2>Place Your Order</h2>
            <form action="<?= Config::get('URL'); ?>foodorder/placeOrder" method="post" id="orderForm">

                <!-- Food Selection -->
                <div class="form-group">
                    <label for="food_item">Select Food Item:</label>
                    <select name="food_item" id="food_item" class="form-control" required>
                        <option value="">-- Choose your meal --</option>
                        <?php foreach ($this->food_menu as $item => $price) { ?>
                            <option value="<?= htmlspecialchars($item); ?>" data-price="<?= $price; ?>">
                                <?= htmlspecialchars($item); ?> - €<?= number_format($price, 2); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Menu Option Checkbox -->
                <div class="form-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="is_menu" id="is_menu" value="1">
                        <span class="checkmark"></span>
                        Add Menu (Drink + Dessert) - €2.00 extra
                    </label>
                    <p class="form-help">Includes: 1 Drink (Coke, Fanta, or Water) + 1 Dessert (Ice Cream or Pudding)</p>
                </div>

                <!-- Delivery Time Selection -->
                <div class="form-group">
                    <label>Delivery Time:</label>
                    <div class="time-selection">
                        <label class="time-option">
                            <input type="radio" name="delivery_time" value="11:30" required>
                            <span class="time-label">🕐 11:30 AM</span>
                        </label>
                        <label class="time-option">
                            <input type="radio" name="delivery_time" value="12:30" required>
                            <span class="time-label">🕐 12:30 PM</span>
                        </label>
                    </div>
                </div>

                <!-- Price Preview -->
                <div class="price-preview">
                    <div class="price-row">
                        <span>Base Price:</span>
                        <span id="basePrice">€0.00</span>
                    </div>
                    <div class="price-row" id="menuPriceRow" style="display: none;">
                        <span>Menu Option:</span>
                        <span>€2.00</span>
                    </div>
                    <div class="price-row price-total">
                        <span>Total Price:</span>
                        <span id="totalPrice">€0.00</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-submit">
                    🛒 Place Order
                </button>
            </form>
        </div>

        <!-- Recent Orders Preview -->
        <?php if (!empty($this->user_orders)) { ?>
            <div class="recent-orders-preview">
                <h3>Your Recent Orders</h3>
                <div class="orders-list">
                    <?php
                    $recent_orders = array_slice($this->user_orders, 0, 3);
                    foreach ($recent_orders as $order) {
                    ?>
                        <div class="order-preview-card">
                            <div class="order-preview-header">
                                <span class="order-item"><?= htmlspecialchars($order->food_item); ?></span>
                                <span class="order-price">€<?= number_format($order->price, 2); ?></span>
                            </div>
                            <div class="order-preview-details">
                                <span>⏱️ <?= htmlspecialchars($order->delivery_time); ?></span>
                                <span>📅 <?= date('M j, Y', strtotime($order->order_date)); ?></span>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <a href="<?= Config::get('URL'); ?>foodorder/myorders" class="btn-view-all">
                    View All Orders →
                </a>
            </div>
        <?php } ?>
    </div>
</div>

<style>
/* Food Order Container */
.food-order-container {
    max-width: 900px;
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
}

.btn-my-orders {
    background: #0366d6;
    color: white;
}

.btn-my-orders:hover {
    background: #0256c7;
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

/* Order Form */
.order-form-container {
    margin-bottom: 40px;
}

.order-form-container h2 {
    margin-bottom: 25px;
    color: #24292e;
    font-size: 24px;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #24292e;
    font-size: 16px;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e1e4e8;
    border-radius: 8px;
    font-size: 16px;
    font-family: inherit;
    transition: border-color 0.2s;
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: #0366d6;
}

.form-help {
    margin: 8px 0 0 0;
    font-size: 14px;
    color: #586069;
}

/* Checkbox Styling */
.checkbox-container {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-weight: 500;
    font-size: 16px;
    padding: 12px;
    background: #f6f8fa;
    border-radius: 8px;
    transition: background 0.2s;
}

.checkbox-container:hover {
    background: #e1e4e8;
}

.checkbox-container input[type="checkbox"] {
    margin-right: 12px;
    width: 20px;
    height: 20px;
    cursor: pointer;
}

/* Time Selection */
.time-selection {
    display: flex;
    gap: 16px;
}

.time-option {
    flex: 1;
    cursor: pointer;
}

.time-option input[type="radio"] {
    display: none;
}

.time-label {
    display: block;
    padding: 16px;
    background: #f6f8fa;
    border: 2px solid #e1e4e8;
    border-radius: 8px;
    text-align: center;
    font-weight: 600;
    font-size: 18px;
    transition: all 0.2s;
}

.time-option input[type="radio"]:checked + .time-label {
    background: #0366d6;
    color: white;
    border-color: #0366d6;
    transform: scale(1.05);
}

.time-label:hover {
    border-color: #0366d6;
    background: #e1f0ff;
}

/* Price Preview */
.price-preview {
    background: #f6f8fa;
    padding: 20px;
    border-radius: 8px;
    margin: 25px 0;
    border: 2px solid #e1e4e8;
}

.price-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    font-size: 16px;
}

.price-total {
    border-top: 2px solid #e1e4e8;
    margin-top: 10px;
    padding-top: 12px;
    font-size: 20px;
    font-weight: 700;
    color: #0366d6;
}

/* Submit Button */
.btn-submit {
    width: 100%;
    padding: 16px;
    background: #28a745;
    color: white;
    font-size: 18px;
    font-weight: 700;
    border-radius: 8px;
    transition: all 0.2s;
}

.btn-submit:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
}

/* Recent Orders Preview */
.recent-orders-preview {
    margin-top: 40px;
    padding-top: 30px;
    border-top: 2px solid #e1e4e8;
}

.recent-orders-preview h3 {
    margin-bottom: 20px;
    color: #24292e;
    font-size: 20px;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.order-preview-card {
    background: #f6f8fa;
    padding: 16px;
    border-radius: 8px;
    border: 1px solid #e1e4e8;
}

.order-preview-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.order-item {
    font-weight: 600;
    color: #24292e;
}

.order-price {
    font-weight: 700;
    color: #28a745;
}

.order-preview-details {
    display: flex;
    gap: 16px;
    font-size: 14px;
    color: #586069;
}

.btn-view-all {
    display: inline-block;
    color: #0366d6;
    text-decoration: none;
    font-weight: 600;
    font-size: 16px;
}

.btn-view-all:hover {
    text-decoration: underline;
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

    .time-selection {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const foodSelect = document.getElementById('food_item');
    const menuCheckbox = document.getElementById('is_menu');
    const basePrice = document.getElementById('basePrice');
    const totalPrice = document.getElementById('totalPrice');
    const menuPriceRow = document.getElementById('menuPriceRow');

    function updatePrice() {
        const selectedOption = foodSelect.options[foodSelect.selectedIndex];
        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
        const menuExtra = menuCheckbox.checked ? 2.00 : 0;
        const total = price + menuExtra;

        basePrice.textContent = '€' + price.toFixed(2);
        totalPrice.textContent = '€' + total.toFixed(2);

        if (menuCheckbox.checked) {
            menuPriceRow.style.display = 'flex';
        } else {
            menuPriceRow.style.display = 'none';
        }
    }

    foodSelect.addEventListener('change', updatePrice);
    menuCheckbox.addEventListener('change', updatePrice);
});
</script>
