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

    // Get event articles from database
    $stmt = $pdo->prepare("
        SELECT ea.*, u.username as author_name 
        FROM event_articles ea 
        LEFT JOIN users u ON ea.author_id = u.id 
        WHERE ea.event_id = ? 
        ORDER BY ea.created_at DESC
    ");
    $stmt->execute([$event_id]);
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'articles' => $articles
    ]);

} catch (Exception $e) {
    error_log("Articles Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'articles' => []
    ]);
}
?>