<?php

/**
 * GroupModel
 * Handles all group-related database operations
 */
class GroupModel
{
    /**
     * Create a new group
     * @param string $group_name Name of the group
     * @param array $member_ids Array of user IDs to add to the group
     * @return int|bool Group ID on success, false on failure
     */
    public static function createGroup($group_name, $member_ids = array())
    {
        if (!$group_name || strlen($group_name) == 0) {
            Session::add('feedback_negative', 'Group name is required');
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        try {
            // Start transaction
            $database->beginTransaction();

            // Create the group
            $sql = "INSERT INTO groups (group_name, created_by) VALUES (:group_name, :created_by)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_name' => $group_name,
                ':created_by' => $current_user_id
            ));

            $group_id = $database->lastInsertId();

            // Add creator to the group
            $sql_member = "INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)";
            $query_member = $database->prepare($sql_member);
            $query_member->execute(array(
                ':group_id' => $group_id,
                ':user_id' => $current_user_id
            ));

            // Add other members
            if (!empty($member_ids)) {
                foreach ($member_ids as $member_id) {
                    if ($member_id != $current_user_id && is_numeric($member_id)) {
                        $query_member->execute(array(
                            ':group_id' => $group_id,
                            ':user_id' => $member_id
                        ));
                    }
                }
            }

            // Commit transaction
            $database->commit();

            Session::add('feedback_positive', 'Group created successfully');
            return $group_id;

        } catch (Exception $e) {
            $database->rollBack();
            Session::add('feedback_negative', 'Failed to create group: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get all groups for the current user
     * @return array List of groups
     */
    public static function getUserGroups()
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        $sql = "SELECT g.*, u.user_name as creator_name,
                       COUNT(gm.user_id) as member_count,
                       (SELECT message_text FROM messages WHERE group_id = g.group_id ORDER BY created_at DESC LIMIT 1) as last_message,
                       (SELECT created_at FROM messages WHERE group_id = g.group_id ORDER BY created_at DESC LIMIT 1) as last_message_time,
                       (SELECT COUNT(*) FROM messages WHERE group_id = g.group_id AND is_read = 0 AND sender_id != :current_user_id) as unread_count
                FROM groups g
                INNER JOIN group_members gm_current ON g.group_id = gm_current.group_id AND gm_current.user_id = :current_user_id
                LEFT JOIN users u ON g.created_by = u.user_id
                LEFT JOIN group_members gm ON g.group_id = gm.group_id
                GROUP BY g.group_id
                ORDER BY last_message_time DESC";

        $query = $database->prepare($sql);
        $query->execute(array(':current_user_id' => $current_user_id));

        return $query->fetchAll();
    }

    /**
     * Get group details
     * @param int $group_id Group ID
     * @return object|bool Group details or false
     */
    public static function getGroup($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        $sql = "SELECT g.*, u.user_name as creator_name
                FROM groups g
                INNER JOIN group_members gm ON g.group_id = gm.group_id AND gm.user_id = :current_user_id
                LEFT JOIN users u ON g.created_by = u.user_id
                WHERE g.group_id = :group_id
                LIMIT 1";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_id' => $group_id,
            ':current_user_id' => $current_user_id
        ));

        return $query->fetch();
    }

    /**
     * Get all members of a group
     * @param int $group_id Group ID
     * @return array List of members
     */
    public static function getGroupMembers($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT u.user_id, u.user_name, u.user_email, u.user_has_avatar, gm.joined_at
                FROM group_members gm
                INNER JOIN users u ON gm.user_id = u.user_id
                WHERE gm.group_id = :group_id
                ORDER BY gm.joined_at ASC";

        $query = $database->prepare($sql);
        $query->execute(array(':group_id' => $group_id));

        return $query->fetchAll();
    }

    /**
     * Add a member to a group
     * @param int $group_id Group ID
     * @param int $user_id User ID to add
     * @return bool Success status
     */
    public static function addMember($group_id, $user_id)
    {
        if (!$group_id || !$user_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        try {
            $sql = "INSERT INTO group_members (group_id, user_id) VALUES (:group_id, :user_id)";
            $query = $database->prepare($sql);
            $query->execute(array(
                ':group_id' => $group_id,
                ':user_id' => $user_id
            ));

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Remove a member from a group
     * @param int $group_id Group ID
     * @param int $user_id User ID to remove
     * @return bool Success status
     */
    public static function removeMember($group_id, $user_id)
    {
        if (!$group_id || !$user_id) {
            return false;
        }

        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "DELETE FROM group_members WHERE group_id = :group_id AND user_id = :user_id LIMIT 1";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_id' => $group_id,
            ':user_id' => $user_id
        ));

        return $query->rowCount() == 1;
    }

    /**
     * Check if user is member of a group
     * @param int $group_id Group ID
     * @param int $user_id User ID
     * @return bool True if member, false otherwise
     */
    public static function isMember($group_id, $user_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();

        $sql = "SELECT COUNT(*) as count FROM group_members WHERE group_id = :group_id AND user_id = :user_id";
        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_id' => $group_id,
            ':user_id' => $user_id
        ));

        $result = $query->fetch();
        return $result->count > 0;
    }

    /**
     * Get all users not in a specific group (for adding members)
     * @param int $group_id Group ID
     * @return array List of users
     */
    public static function getUsersNotInGroup($group_id)
    {
        $database = DatabaseFactory::getFactory()->getConnection();
        $current_user_id = Session::get('user_id');

        $sql = "SELECT u.user_id, u.user_name, u.user_email, u.user_has_avatar
                FROM users u
                WHERE u.user_id NOT IN (
                    SELECT user_id FROM group_members WHERE group_id = :group_id
                )
                AND u.user_active = 1
                AND u.user_deleted = 0
                AND u.user_id != :current_user_id
                ORDER BY u.user_name ASC";

        $query = $database->prepare($sql);
        $query->execute(array(
            ':group_id' => $group_id,
            ':current_user_id' => $current_user_id
        ));

        return $query->fetchAll();
    }
}
