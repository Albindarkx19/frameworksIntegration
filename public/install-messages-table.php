<?php
/**
 * Quick installer for messages table
 * Open this file in your browser: http://localhost/huge-app/install-messages-table.php
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

    echo "<h1>Installing Messages Table</h1>";
    echo "<p>Connecting to database: <strong>$dbname</strong>...</p>";

    // Create messages table
    $sql = "CREATE TABLE IF NOT EXISTS `messages` (
        `message_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
        `sender_id` int(11) unsigned NOT NULL,
        `receiver_id` int(11) unsigned DEFAULT NULL COMMENT 'NULL for group messages',
        `receiver_group` varchar(50) DEFAULT NULL COMMENT 'e.g., admin, all - NULL for individual messages',
        `message_text` text NOT NULL,
        `is_read` tinyint(1) NOT NULL DEFAULT 0,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`message_id`),
        KEY `sender_id` (`sender_id`),
        KEY `receiver_id` (`receiver_id`),
        KEY `is_read` (`is_read`),
        CONSTRAINT `messages_sender_fk` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
        CONSTRAINT `messages_receiver_fk` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='user messages for messenger system'";

    $pdo->exec($sql);

    echo "<p style='color: green; font-size: 18px; font-weight: bold;'>✓ SUCCESS!</p>";
    echo "<p>The 'messages' table has been created successfully!</p>";
    echo "<hr>";
    echo "<h3>Next steps:</h3>";
    echo "<ol>";
    echo "<li>Delete this file (install-messages-table.php) for security reasons</li>";
    echo "<li>Go to your application: <a href='./'>http://localhost/huge-app/</a></li>";
    echo "<li>Login and test the messenger: <a href='./message/index'>Messages</a></li>";
    echo "</ol>";

} catch (PDOException $e) {
    echo "<h1 style='color: red;'>Error!</h1>";
    echo "<p><strong>Error message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<hr>";
    echo "<h3>Troubleshooting:</h3>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL is running</li>";
    echo "<li>Make sure the database 'huge' exists</li>";
    echo "<li>Check the database credentials in this file match your configuration</li>";
    echo "</ul>";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Messages Table Installer</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        p, li {
            line-height: 1.6;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
</body>
</html>
