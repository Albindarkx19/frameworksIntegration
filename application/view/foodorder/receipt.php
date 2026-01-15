<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Order Receipt #<?= $this->order->order_id; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .receipt-header {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 3px solid #0366d6;
            margin-bottom: 30px;
        }

        .company-name {
            font-size: 32px;
            font-weight: 700;
            color: #0366d6;
            margin-bottom: 10px;
        }

        .receipt-title {
            font-size: 24px;
            color: #24292e;
            margin-top: 10px;
        }

        .order-number {
            font-size: 18px;
            color: #586069;
            margin-top: 8px;
        }

        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
            padding: 20px;
            background: #f6f8fa;
            border-radius: 8px;
        }

        .info-section h3 {
            font-size: 14px;
            color: #586069;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .info-section p {
            font-size: 16px;
            color: #24292e;
            line-height: 1.6;
            margin: 5px 0;
        }

        .order-details {
            margin-bottom: 40px;
        }

        .order-details h2 {
            font-size: 20px;
            color: #24292e;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e1e4e8;
        }

        .order-table {
            width: 100%;
            border-collapse: collapse;
        }

        .order-table th {
            background: #f6f8fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #24292e;
            border-bottom: 2px solid #e1e4e8;
        }

        .order-table td {
            padding: 15px;
            border-bottom: 1px solid #e1e4e8;
            color: #24292e;
        }

        .order-table tr:last-child td {
            border-bottom: none;
        }

        .item-description {
            font-size: 15px;
            color: #24292e;
            font-weight: 600;
        }

        .item-note {
            font-size: 13px;
            color: #586069;
            margin-top: 4px;
        }

        .price-right {
            text-align: right;
            font-weight: 600;
        }

        .order-summary {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #e1e4e8;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 16px;
        }

        .summary-total {
            font-size: 24px;
            font-weight: 700;
            color: #0366d6;
            border-top: 2px solid #0366d6;
            margin-top: 10px;
            padding-top: 15px;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e1e4e8;
        }

        .footer-note {
            font-size: 14px;
            color: #586069;
            line-height: 1.6;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 10px;
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

        .print-button {
            margin-top: 30px;
            text-align: center;
        }

        .btn-print {
            background: #0366d6;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print:hover {
            background: #0256c7;
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .receipt-container {
                box-shadow: none;
                padding: 20px;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <div class="company-name">🍕 Food Order System</div>
            <div class="receipt-title">Order Receipt</div>
            <div class="order-number">Order #<?= str_pad($this->order->order_id, 6, '0', STR_PAD_LEFT); ?></div>
            <div class="status-badge status-<?= htmlspecialchars($this->order->status); ?>">
                <?= ucfirst(htmlspecialchars($this->order->status)); ?>
            </div>
        </div>

        <div class="receipt-info">
            <div class="info-section">
                <h3>Customer Information</h3>
                <p><strong>Name:</strong> <?= htmlspecialchars($this->order->user_name); ?></p>
                <p><strong>User ID:</strong> <?= $this->order->user_id; ?></p>
            </div>
            <div class="info-section">
                <h3>Order Information</h3>
                <p><strong>Order Date:</strong> <?= date('F j, Y', strtotime($this->order->order_date)); ?></p>
                <p><strong>Order Time:</strong> <?= date('g:i A', strtotime($this->order->order_date)); ?></p>
                <p><strong>Delivery Time:</strong> <?= htmlspecialchars($this->order->delivery_time); ?></p>
            </div>
        </div>

        <div class="order-details">
            <h2>Order Details</h2>
            <table class="order-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Item</th>
                        <th style="width: 20%;">Quantity</th>
                        <th style="width: 20%;" class="price-right">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="item-description"><?= htmlspecialchars($this->order->food_item); ?></div>
                            <?php if ($this->order->is_menu) { ?>
                                <div class="item-note">✨ Base item</div>
                            <?php } ?>
                        </td>
                        <td>1</td>
                        <td class="price-right">
                            €<?= number_format($this->order->price - ($this->order->is_menu ? 2 : 0), 2); ?>
                        </td>
                    </tr>
                    <?php if ($this->order->is_menu) { ?>
                        <tr>
                            <td>
                                <div class="item-description">Menu Option</div>
                                <div class="item-note">Includes: 1 Drink + 1 Dessert</div>
                            </td>
                            <td>1</td>
                            <td class="price-right">€2.00</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div class="order-summary">
                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span>€<?= number_format($this->order->price, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span>Tax (included):</span>
                    <span>€0.00</span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total:</span>
                    <span>€<?= number_format($this->order->price, 2); ?></span>
                </div>
            </div>
        </div>

        <div class="receipt-footer">
            <p class="footer-note">
                Thank you for your order!<br>
                If you have any questions, please contact our support team.<br>
                <strong>Food Order System - Powered by HUGE Framework</strong>
            </p>
        </div>

        <div class="print-button">
            <button onclick="window.print();" class="btn-print">🖨️ Print Receipt</button>
            <a href="<?= Config::get('URL'); ?>foodorder/myorders" class="btn-print" style="background: #6a737d; margin-left: 10px;">
                ← Back to Orders
            </a>
        </div>
    </div>
</body>
</html>
