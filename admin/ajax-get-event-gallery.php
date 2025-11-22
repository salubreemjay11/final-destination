[file name]: ajax-get-event-gallery.php
[file content begin]
<?php
session_start();
require_once '../config/database.php';

// Initialize database connection - UPDATE THESE CREDENTIALS
try {
    $pdo = new PDO("mysql:host=localhost;dbname=orphanfare", "your_username", "your_password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Simple permission check
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    try {
        // Get photos for the event
        $query = "SELECT eg.*, u.username as uploaded_by_name 
                 FROM events_gallery eg 
                 LEFT JOIN users u ON eg.uploaded_by = u.id 
                 WHERE eg.event_id = ? 
                 ORDER BY eg.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$event_id]);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'photos' => $photos
        ]);
        
    } catch (Exception $e) {
        error_log("Error loading event gallery: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Failed to load photos from database'
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request. Required: POST method with event_id'
    ]);
}
?>
[file content end]