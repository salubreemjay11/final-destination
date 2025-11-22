<?php
require_once 'includes/header.php';

// Test if images are accessible
$test_images = [
    'uploads/schedule/gallery/event_EVT-2025-001_176352...',
    'uploads/schedule/gallery/event_EVT-2025-007_176379...',
    'uploads/schedule/gallery/event_EVT-2025-008_176379...'
];

foreach ($test_images as $image_path) {
    echo "<h3>Testing: $image_path</h3>";
    echo "<p>File exists: " . (file_exists($image_path) ? 'YES' : 'NO') . "</p>";
    if (file_exists($image_path)) {
        echo "<img src='$image_path' style='max-width: 300px;'><br><br>";
    } else {
        echo "<p style='color: red;'>File not found!</p>";
    }
}
?>