<?php
// Start session and include necessary files
session_start();
require_once '../config.php'; // Adjust path based on your structure
require_once '../includes/header.php'; // For database connection

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $event_id = $_POST['event_id'] ?? '';
    
    if (empty($event_id)) {
        throw new Exception('Event ID is required');
    }

    // Get event photos from database using your existing $pdo connection
    $stmt = $pdo->prepare("
        SELECT * FROM events_gallery 
        WHERE event_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$event_id]);
    $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process image paths to make them web-accessible
    foreach ($photos as &$photo) {
        // Convert backslashes to forward slashes for web compatibility
        $photo['image_path'] = str_replace('\\', '/', $photo['image_path']);
        
        // Make path relative if it's absolute
        if (strpos($photo['image_path'], $_SERVER['DOCUMENT_ROOT']) === 0) {
            $photo['image_path'] = str_replace($_SERVER['DOCUMENT_ROOT'], '', $photo['image_path']);
        }
        
        // Ensure the path starts correctly
        if (strpos($photo['image_path'], 'uploads/') === 0) {
            // Path is already good
        } elseif (strpos($photo['image_path'], '/uploads/') === 0) {
            $photo['image_path'] = ltrim($photo['image_path'], '/');
        } else {
            // Add uploads/ prefix if missing
            $photo['image_path'] = 'uploads/' . ltrim($photo['image_path'], '/');
        }
    }

    echo json_encode([
        'success' => true,
        'photos' => $photos
    ]);

} catch (Exception $e) {
    error_log("Gallery Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'photos' => []
    ]);
}
?>