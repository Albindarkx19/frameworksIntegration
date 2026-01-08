<?php

/**
 * MessageModel
 * Handles all message-related database operations for the messenger system
 */
class MessageModel
{
    /**
     * Send a message to a specific user
     * @param int $receiver_id ID of the user to send message to
     * @param string $message_text The message text
     * @return bool success status
     */
    public static function sendMessage($receiver_id, $message_text)
    {
        if (!$receiver_id || !$message_text || strlen($message_text) == 0) {
            Session::add('feedback_negative', 'Message sending failed: Invalid data');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO messages (sender_id, receiver_id, message_text) VALUES (:sender_id, :receiver_id, :message_text)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender_id' => Session::get('user_id'),
            ':receiver_id' => $receiver_id,
            ':message_text' => $message_text
        ));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', 'Message sent successfully');
            return true;
        }

        Session::add('feedback_negative', 'Message sending failed');
        return false;
    }

    /**
     * Send a message to a group (e.g., all admins)
     * @param string $receiver_group Group name (e.g., 'admin')
     * @param string $message_text The message text
     * @return bool success status
     */
    public static function sendGroupMessage($receiver_group, $message_text)
    {
        if (!$receiver_group || !$message_text || strlen($message_text) == 0) {
            Session::add('feedback_negative', 'Group message sending failed: Invalid data');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO messages (sender_id, receiver_group, message_text) VALUES (:sender_id, :receiver_group, :message_text)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender_id' => Session::get('user_id'),
            ':receiver_group' => $receiver_group,
            ':message_text' => $message_text
        ));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', 'Group message sent successfully');
            return true;
        }

        Session::add('feedback_negative', 'Group message sending failed');
        return false;
    }

    /**
     * Send a message to a custom group
     * @param int $group_id Group ID
     * @param string $message_text The message text
     * @return bool success status
     */
    public static function sendCustomGroupMessage($group_id, $message_text)
    {
        if (!$group_id || !$message_text || strlen($message_text) == 0) {
            Session::add('feedback_negative', 'Message sending failed: Invalid data');
            return false;
        }

        // Check if user is member of the group
        if (!GroupModel::isMember($group_id, Session::get('user_id'))) {
            Session::add('feedback_negative', 'You are not a member of this group');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "INSERT INTO messages (sender_id, group_id, message_text) VALUES (:sender_id, :group_id, :message_text)";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':sender_id' => Session::get('user_id'),
            ':group_id' => $group_id,
            ':message_text' => $message_text
        ));

        if ($query->rowCount() == 1) {
            Session::add('feedback_positive', 'Message sent successfully');
            return true;
        }

        Session::add('feedback_negative', 'Message sending failed');
        return false;
    }

    /**
     * Get messages for a custom group
     * @param int $group_id Group ID
     * @return array all messages in the group
     */
    public static function getGroupConversation($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        // Check if user is member
        if (!GroupModel::isMember($group_id, $current_user_id)) {
            return array();
        }

        $sql = "SELECT m.*, sender.user_name as sender_name, sender.user_has_avatar as sender_has_avatar
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.user_id
                WHERE m.group_id = :group_id
                ORDER BY m.created_at ASC";

        $query = $database->prepare($sql);
        $query->execute(array(':group_id' => $group_id));

        return $query->fetchAll();
    }

    /**
     * Get conversation with a specific user
     * @param int $user_id ID of the user to get conversation with
     * @return array all messages in the conversation
     */
    public static function getConversation($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        $sql = "SELECT m.*,
                       sender.user_name as sender_name,
                       sender.user_has_avatar as sender_has_avatar,
                       receiver.user_name as receiver_name,
                       receiver.user_has_avatar as receiver_has_avatar
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.user_id
                LEFT JOIN users receiver ON m.receiver_id = receiver.user_id
                WHERE (m.sender_id = :current_user_id AND m.receiver_id = :user_id)
                   OR (m.sender_id = :user_id AND m.receiver_id = :current_user_id)
                ORDER BY m.created_at ASC";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':current_user_id' => $current_user_id,
            ':user_id' => $user_id
        ));

        return $query->fetchAll();
    }

    /**
     * Get all conversations for the current user
     * Returns a list of users the current user has conversations with
     * @return array list of conversations with last message info
     */
    public static function getAllConversations()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        $sql = "SELECT u.user_id, u.user_name, u.user_has_avatar,
                       latest.message_text as last_message,
                       latest.created_at as last_message_time,
                       latest.sender_id as last_sender_id,
                       COUNT(CASE WHEN m.is_read = 0 AND m.receiver_id = :current_user_id THEN 1 END) as unread_count
                FROM users u
                INNER JOIN messages latest ON (
                    (latest.sender_id = u.user_id AND latest.receiver_id = :current_user_id)
                    OR (latest.receiver_id = u.user_id AND latest.sender_id = :current_user_id)
                )
                LEFT JOIN messages m ON (
                    (m.sender_id = u.user_id AND m.receiver_id = :current_user_id)
                    OR (m.receiver_id = u.user_id AND m.sender_id = :current_user_id)
                )
                WHERE latest.message_id = (
                    SELECT MAX(m2.message_id)
                    FROM messages m2
                    WHERE (m2.sender_id = u.user_id AND m2.receiver_id = :current_user_id)
                       OR (m2.receiver_id = u.user_id AND m2.sender_id = :current_user_id)
                )
                GROUP BY u.user_id, u.user_name, u.user_has_avatar, latest.message_text, latest.created_at, latest.sender_id
                ORDER BY latest.created_at DESC";

        $query = $database->prepare($sql);
        $query->execute(array(':current_user_id' => $current_user_id));

        return $query->fetchAll();
    }

    /**
     * Get count of unread messages for the current user
     * @return int number of unread messages
     */
    public static function getUnreadCount()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        // Individual messages
        $sql = "SELECT COUNT(*) as unread_count
                FROM messages
                WHERE receiver_id = :user_id AND is_read = 0";

        $query = $database->prepare($sql);
        $query->execute(array(':user_id' => $current_user_id));
        $result = $query->fetch();

        $individual_count = $result ? $result->unread_count : 0;

        // Group messages
        $user_type = Session::get('user_account_type');
        $group_count = 0;

        if ($user_type == 7) { // Admin user
            $sql_group = "SELECT COUNT(*) as unread_count
                          FROM messages
                          WHERE receiver_group = 'admin' AND is_read = 0 AND sender_id != :user_id";

            $query_group = $database->prepare($sql_group);
            $query_group->execute(array(':user_id' => $current_user_id));
            $result_group = $query_group->fetch();

            $group_count = $result_group ? $result_group->unread_count : 0;
        }

        return $individual_count + $group_count;
    }

    /**
     * Mark a message as read
     * @param int $message_id ID of the message to mark as read
     * @return bool success status
     */
    public static function markAsRead($message_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        $sql = "UPDATE messages SET is_read = 1
                WHERE message_id = :message_id
                AND receiver_id = :user_id
                LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':message_id' => $message_id,
            ':user_id' => $current_user_id
        ));

        return $query->rowCount() == 1;
    }

    /**
     * Mark all messages in a conversation as read
     * @param int $user_id ID of the user whose messages to mark as read
     * @return bool success status
     */
    public static function markConversationAsRead($user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        $sql = "UPDATE messages SET is_read = 1
                WHERE sender_id = :user_id
                AND receiver_id = :current_user_id
                AND is_read = 0";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':user_id' => $user_id,
            ':current_user_id' => $current_user_id
        ));

        return true;
    }

    /**
     * Get group messages for the current user
     * @return array all group messages
     */
    public static function getGroupMessages()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $user_type = Session::get('user_account_type');

        if ($user_type != 7) { // Only admins can see admin group messages
            return array();
        }

        $sql = "SELECT m.*, sender.user_name as sender_name, sender.user_has_avatar as sender_has_avatar
                FROM messages m
                LEFT JOIN users sender ON m.sender_id = sender.user_id
                WHERE m.receiver_group = 'admin'
                ORDER BY m.created_at DESC";

        $query = $database->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }
}
