<?php
/**
 * Custom Fields Database Setup Script
 * Run this script once to initialize custom fields tables
 * Usage: php setup-custom-fields.php
 */

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Creating custom fields tables...\n";

// Create custom_fields table
$sql_fields = "
    CREATE TABLE IF NOT EXISTS `custom_fields` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `field_id` varchar(20) NOT NULL UNIQUE,
      `field_name` varchar(100) NOT NULL,
      `field_label` varchar(150) NOT NULL,
      `field_type` enum('text','textarea','number','date','dropdown','radio','checkbox','file','email','phone') DEFAULT 'text',
      `module` varchar(100) NOT NULL,
      `placeholder_text` varchar(255),
      `default_value` text,
      `help_text` text,
      `is_required` tinyint(1) DEFAULT 0,
      `is_active` tinyint(1) DEFAULT 1,
      `field_order` int(11) DEFAULT 0,
      `field_options` json,
      `validation_rules` json,
      `created_by` int(11),
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `field_id` (`field_id`),
      KEY `idx_module` (`module`),
      KEY `idx_field_type` (`field_type`),
      KEY `idx_is_active` (`is_active`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conn->query($sql_fields) === TRUE) {
    echo "✓ custom_fields table created successfully\n";
} else {
    echo "✗ Error creating custom_fields table: " . $conn->error . "\n";
}

// Create custom_field_values table
$sql_values = "
    CREATE TABLE IF NOT EXISTS `custom_field_values` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `field_id` int(11) NOT NULL,
      `record_id` varchar(50) NOT NULL,
      `record_type` varchar(50) NOT NULL,
      `field_value` longtext,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_field_value` (`field_id`,`record_id`,`record_type`),
      KEY `idx_record` (`record_id`,`record_type`),
      CONSTRAINT `custom_field_values_ibfk_1` FOREIGN KEY (`field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conn->query($sql_values) === TRUE) {
    echo "✓ custom_field_values table created successfully\n";
} else {
    echo "✗ Error creating custom_field_values table: " . $conn->error . "\n";
}

echo "\nDatabase setup completed!\n";
$conn->close();
?>
