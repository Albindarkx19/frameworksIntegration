<?php

/**
 * FoodOrderModel
 * Handles all food order database operations
 */
class FoodOrderModel
{
    /**
     * Get available food menu items
     * @return array Array of food items with prices
     */
    public static function getFoodMenu()
    {
        return [
            'Pizza Margherita' => 8.50,
            'Pizza Salami' => 9.50,
            'Pizza Quattro Formaggi' => 10.50,
            'Pasta Carbonara' => 9.00,
            'Pasta Bolognese' => 8.50,
            'Pasta Aglio e Olio' => 7.50,
            'Caesar Salad' => 7.00,
            'Greek Salad' => 6.50,
            'Chicken Burger' => 9.50,
            'Beef Burger' => 10.00,
            'Veggie Burger' => 8.50,
            'Fish & Chips' => 11.00,
            'Schnitzel' => 12.00,
            'Lasagne' => 9.50,
            'Sushi Platte' => 15.00
        ];
    }

    /**
     * Create a new food order
     * @param int $user_id User ID
     * @param string $user_name User name
     * @param string $food_item Selected food item
     * @param float $price Price of the item
     * @param bool $is_menu Whether menu option is selected
     * @param string $delivery_time Selected delivery time (11:30 or 12:30)
     * @return bool True if successful, false otherwise
     */
    public static function createOrder($user_id, $user_name, $food_item, $price, $is_menu, $delivery_time)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO food_orders (user_id, user_name, food_item, price, is_menu, delivery_time)
                VALUES (:user_id, :user_name, :food_item, :price, :is_menu, :delivery_time)";

        $query = $database->prepare($sql);
        $query->execute([
            ':user_id' => $user_id,
            ':user_name' => $user_name,
            ':food_item' => $food_item,
            ':price' => $price,
            ':is_menu' => $is_menu ? 1 : 0,
            ':delivery_time' => $delivery_time
        ]);

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', 'Order placed successfully!');
            return true;
        }

        Session::add('feedback_negative', 'Failed to place order. Please try again.');
        return false;
    }

    /**
     * Get all orders for a specific user
     * @param int $user_id User ID
     * @return array Array of orders
     */
    public static function getUserOrders($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT * FROM food_orders
                WHERE user_id = :user_id
                ORDER BY order_date DESC";

        $query = $database->prepare($sql);
        $query->execute([':user_id' => $user_id]);

        return $query->fetchAll();
    }

    /**
     * Get all orders (for admin view)
     * @return array Array of all orders
     */
    public static function getAllOrders()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT * FROM food_orders ORDER BY order_date DESC";
        $query = $database->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Get a single order by ID
     * @param int $order_id Order ID
     * @return object|bool Order object or false if not found
     */
    public static function getOrder($order_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT * FROM food_orders WHERE order_id = :order_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute([':order_id' => $order_id]);

        return $query->fetch();
    }

    /**
     * Get today's orders grouped by delivery time
     * @return array Array with orders grouped by time
     */
    public static function getTodaysOrders()
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT * FROM food_orders
                WHERE DATE(order_date) = CURDATE()
                ORDER BY delivery_time, user_name";

        $query = $database->prepare($sql);
        $query->execute();

        $orders = $query->fetchAll();

        // Group by delivery time
        $grouped = [
            '11:30' => [],
            '12:30' => []
        ];

        foreach ($orders as $order) {
            $grouped[$order->delivery_time][] = $order;
        }

        return $grouped;
    }

    /**
     * Update order status
     * @param int $order_id Order ID
     * @param string $status New status
     * @return bool True if successful
     */
    public static function updateOrderStatus($order_id, $status)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "UPDATE food_orders SET status = :status WHERE order_id = :order_id";
        $query = $database->prepare($sql);
        $query->execute([
            ':status' => $status,
            ':order_id' => $order_id
        ]);

        return $query->rowCount() == 1;
    }

    /**
     * Delete an order
     * @param int $order_id Order ID
     * @param int $user_id User ID (for verification)
     * @return bool True if successful
     */
    public static function deleteOrder($order_id, $user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM food_orders
                WHERE order_id = :order_id AND user_id = :user_id";

        $query = $database->prepare($sql);
        $query->execute([
            ':order_id' => $order_id,
            ':user_id' => $user_id
        ]);

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', 'Order cancelled successfully.');
            return true;
        }

        Session::add('feedback_negative', 'Failed to cancel order.');
        return false;
    }
}
