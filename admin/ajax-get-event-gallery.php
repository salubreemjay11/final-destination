<?php
session_start();

// Simple database connection - USE YOUR ACTUAL CREDENTIALS
$host = 'localhost';
$dbname = 'orphanfare';
$username = 'root'; // Change to your MySQL username
$password = ''; // Change to your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    try {
        // Simple check if events_gallery table exists
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'events_gallery'");
        if ($tableCheck->rowCount() == 0) {
            echo json_encode([
                'success' => true,
                'photos' => [],
                'message' => 'Gallery table not created yet'
            ]);
            exit();
        }
        
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
        echo json_encode([
            'success' => false,
            'error' => 'Failed to load photos',
            'photos' => []
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request'
    ]);
}
?>