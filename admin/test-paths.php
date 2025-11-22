<?php
echo "Current directory: " . __DIR__ . "<br>";
echo "Trying to include permissions.php from: includes/permissions.php<br>";

if (file_exists('includes/permissions.php')) {
    echo "✓ File exists!<br>";
    require_once 'includes/permissions.php';
    echo "✓ File loaded successfully!<br>";
} else {
    echo "✗ File not found!<br>";
    echo "Available files in includes/:<br>";
    $files = scandir('includes/');
    foreach ($files as $file) {
        echo "- $file<br>";
    }
}
?>