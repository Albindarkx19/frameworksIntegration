<?php

/**
 * FoodOrderController
 * Manages all food order functionality
 */
class FoodOrderController extends Controller
{
    /**
     * Constructor
     * Requires user authentication
     */
    public function __construct()
    {
        parent::__construct();
        Auth::checkAuthentication();
    }

    /**
     * Main food order page - shows order form
     */
    public function index()
    {
        $this->View->render('foodorder/index', array(
            'food_menu' => FoodOrderModel::getFoodMenu(),
            'user_orders' => FoodOrderModel::getUserOrders(Session::get('user_id'))
        ));
    }

    /**
     * Place a new order
     */
    public function placeOrder()
    {
        if (Request::post('food_item')) {
            $food_item = Request::post('food_item');
            $is_menu = Request::post('is_menu') ? true : false;
            $delivery_time = Request::post('delivery_time');

            // Get menu and validate
            $menu = FoodOrderModel::getFoodMenu();

            if (!isset($menu[$food_item])) {
                Session::add('feedback_negative', 'Invalid food item selected.');
                Redirect::to('foodorder/index');
                return;
            }

            // Calculate price (add €2 for menu option)
            $price = $menu[$food_item];
            if ($is_menu) {
                $price += 2.00;
            }

            // Validate delivery time
            if (!in_array($delivery_time, ['11:30', '12:30'])) {
                Session::add('feedback_negative', 'Invalid delivery time selected.');
                Redirect::to('foodorder/index');
                return;
            }

            // Create order
            FoodOrderModel::createOrder(
                Session::get('user_id'),
                Session::get('user_name'),
                $food_item,
                $price,
                $is_menu,
                $delivery_time
            );

            // Send notification message to admin group
            $order_message = "New food order from " . Session::get('user_name') . ":\n" .
                           "Item: " . $food_item . "\n" .
                           "Menu: " . ($is_menu ? 'Yes' : 'No') . "\n" .
                           "Price: €" . number_format($price, 2) . "\n" .
                           "Delivery: " . $delivery_time;

            // If admin group exists, send message there
            // MessageModel::sendGroupMessage('admin', $order_message);
        }

        Redirect::to('foodorder/myorders');
    }

    /**
     * Show user's orders
     */
    public function myorders()
    {
        $this->View->render('foodorder/myorders', array(
            'orders' => FoodOrderModel::getUserOrders(Session::get('user_id'))
        ));
    }

    /**
     * Show all orders (admin only)
     */
    public function allorders()
    {
        // Check if user is admin
        if (Session::get('user_account_type') != 7) {
            Session::add('feedback_negative', 'Access denied. Admin only.');
            Redirect::to('foodorder/index');
            return;
        }

        $this->View->render('foodorder/allorders', array(
            'orders' => FoodOrderModel::getAllOrders(),
            'todays_orders' => FoodOrderModel::getTodaysOrders()
        ));
    }

    /**
     * Cancel an order
     */
    public function cancel($order_id)
    {
        if (!$order_id || !is_numeric($order_id)) {
            Session::add('feedback_negative', 'Invalid order ID.');
            Redirect::to('foodorder/myorders');
            return;
        }

        FoodOrderModel::deleteOrder($order_id, Session::get('user_id'));
        Redirect::to('foodorder/myorders');
    }

    /**
     * Generate PDF receipt for an order
     */
    public function downloadPDF($order_id)
    {
        if (!$order_id || !is_numeric($order_id)) {
            Session::add('feedback_negative', 'Invalid order ID.');
            Redirect::to('foodorder/myorders');
            return;
        }

        $order = FoodOrderModel::getOrder($order_id);

        // Verify order belongs to user (or user is admin)
        if (!$order || ($order->user_id != Session::get('user_id') && Session::get('user_account_type') != 7)) {
            Session::add('feedback_negative', 'Order not found or access denied.');
            Redirect::to('foodorder/myorders');
            return;
        }

        // Generate PDF using simple HTML to PDF
        $this->generateSimplePDF($order);
    }

    /**
     * Generate a simple PDF receipt
     */
    private function generateSimplePDF($order)
    {
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="order_receipt_' . $order->order_id . '.pdf"');

        // For a simple implementation without external libraries,
        // we'll use HTML and let the browser convert to PDF
        // For production, consider using TCPDF or FPDF library

        // Redirect to a printable receipt page
        Session::set('pdf_order_id', $order->order_id);
        Redirect::to('foodorder/receipt/' . $order->order_id);
    }

    /**
     * Show printable receipt
     */
    public function receipt($order_id)
    {
        if (!$order_id || !is_numeric($order_id)) {
            Session::add('feedback_negative', 'Invalid order ID.');
            Redirect::to('foodorder/myorders');
            return;
        }

        $order = FoodOrderModel::getOrder($order_id);

        // Verify order belongs to user (or user is admin)
        if (!$order || ($order->user_id != Session::get('user_id') && Session::get('user_account_type') != 7)) {
            Session::add('feedback_negative', 'Order not found or access denied.');
            Redirect::to('foodorder/myorders');
            return;
        }

        $this->View->render('foodorder/receipt', array(
            'order' => $order
        ), true); // true = minimal layout for printing
    }
}
