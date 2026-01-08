<?php
/**
 * Complete Messenger System Installer
 * Installs messages table + groups tables
 * Open this file in your browser: http://localhost/huge-app/install-messenger-complete.php
 * Delete this file after installation for security!
 */

// Database configuration (from config.development.php)
$host = '127.0.0.1';
$dbname = 'huge';
$user = 'root';
$pass = '';

try {
    // Connect to database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h1>Installing Complete Messenger System</h1>";
    echo "<p>Connecting to database: <strong>$dbname</strong>...</p>";
    echo "<hr>";

    // Step 1: Create messages table
    echo "<h3>Step 1: Creating messages table...</h3>";
    $sql_messages = "CREATE TABLE IF NOT EXISTS `messages` (
        `message_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `sender_id` int(11) unsigned NOT NULL,
        `receiver_id` int(11) unsigned DEFAULT NULL COMMENT 'NULL for group messages',
        `receiver_group` varchar(50) DEFAULT NULL COMMENT 'e.g., admin, all - NULL for individual messages',
        `group_id` int(11) unsigned DEFAULT NULL COMMENT 'For custom group messages',
        `message_text` text NOT NULL,
        `is_read` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`message_id`),
        KEY `sender_id` (`sender_id`),
        KEY `receiver_id` (`receiver_id`),
        KEY `is_read` (`is_read`),
        KEY `group_id` (`group_id`),
        CONSTRAINT `messages_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
        CONSTRAINT `messages_receiver_fk` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user messages for messenger system'";

    $pdo->exec($sql_messages);
    echo "<p style='color: green;'>✓ Messages table created successfully!</p>";

    // Step 2: Create groups table
    echo "<h3>Step 2: Creating groups table...</h3>";
    $sql_groups = "CREATE TABLE IF NOT EXISTS `groups` (
        `group_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `group_name` varchar(100) NOT NULL,
        `created_by` int(11) unsigned NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`group_id`),
        KEY `created_by` (`created_by`),
        CONSTRAINT `groups_creator_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='chat groups'";

    $pdo->exec($sql_groups);
    echo "<p style='color: green;'>✓ Groups table created successfully!</p>";

    // Step 3: Create group_members table
    echo "<h3>Step 3: Creating group_members table...</h3>";
    $sql_group_members = "CREATE TABLE IF NOT EXISTS `group_members` (
        `group_member_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `group_id` int(11) unsigned NOT NULL,
        `user_id` int(11) unsigned NOT NULL,
        `joined_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`group_member_id`),
        UNIQUE KEY `unique_group_user` (`group_id`, `user_id`),
        KEY `group_id` (`group_id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `group_members_group_fk` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE,
        CONSTRAINT `group_members_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='group membership'";

    $pdo->exec($sql_group_members);
    echo "<p style='color: green;'>✓ Group members table created successfully!</p>";

    // Step 4: Add foreign key constraint for group_id in messages table
    echo "<h3>Step 4: Adding foreign key constraint...</h3>";
    try {
        // Check if constraint already exists
        $check_sql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                      WHERE TABLE_SCHEMA = '$dbname'
                      AND TABLE_NAME = 'messages'
                      AND CONSTRAINT_NAME = 'messages_group_fk'";
        $result = $pdo->query($check_sql);

        if ($result->rowCount() == 0) {
            $sql_fk = "ALTER TABLE `messages`
                       ADD CONSTRAINT `messages_group_fk` FOREIGN KEY (`group_id`) REFERENCES `groups` (`group_id`) ON DELETE CASCADE";
            $pdo->exec($sql_fk);
            echo "<p style='color: green;'>✓ Foreign key constraint added successfully!</p>";
        } else {
            echo "<p style='color: blue;'>→ Foreign key constraint already exists, skipped.</p>";
        }
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), '1826') !== false) {
            echo "<p style='color: blue;'>→ Foreign key constraint already exists, skipped.</p>";
        } else {
            throw $e;
        }
    }

    echo "<hr>";
    echo "<p style='color: green; font-size: 24px; font-weight: bold;'>✓ SUCCESS!</p>";
    echo "<h2>Installation Complete!</h2>";
    echo "<p>All messenger system tables have been created successfully!</p>";
    echo "<hr>";
    echo "<h3>Next steps:</h3>";
    echo "<ol>";
    echo "<li><strong style='color: red;'>Delete this file (install-messenger-complete.php) for security reasons</strong></li>";
    echo "<li>Go to your application: <a href='./'>http://localhost/huge-app/</a></li>";
    echo "<li>Login and test the messenger features:</li>";
    echo "<ul>";
    echo "<li><a href='./message/index'>Messages</a> - View all conversations</li>";
    echo "<li>Click 'New Chat' to start a conversation with another user</li>";
    echo "<li>Click 'Create Group' to create a group chat</li>";
    echo "</ul>";
    echo "</ol>";

    echo "<hr>";
    echo "<h3>Features Available:</h3>";
    echo "<ul>";
    echo "<li>✓ One-on-one messaging</li>";
    echo "<li>✓ Group chats with multiple users</li>";
    echo "<li>✓ Unread message counters</li>";
    echo "<li>✓ Real-time message display</li>";
    echo "<li>✓ User avatars in chats</li>";
    echo "<li>✓ Date separators</li>";
    echo "<li>✓ Admin group messaging</li>";
    echo "</ul>";

} catch (PDOException $e) {
    echo "<h1 style='color: red;'>Error!</h1>";
    echo "<p><strong>Error message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<hr>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Make sure the database 'huge' exists</li>";
    echo "<li>Make sure the 'users' table exists (run the basic HUGE installation first)</li>";
    echo "<li>Check the database credentials in this file match your configuration</li>";
    echo "<li>If you see 'table already exists' errors, the tables may already be installed</li>";
    echo "</ul>";
    echo "<hr>";
    echo "<h4>Full Error Details:</h4>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messenger System Installer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        h2 {
            color: #007bff;
        }
        h3 {
            color: #28a745;
        }
        p, li {
            line-height: 1.8;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        hr {
            margin: 30px 0;
            border: none;
            border-top: 2px solid #ddd;
        }
        ul, ol {
            line-height: 1.8;
        }
        pre {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
</body>
</html>
