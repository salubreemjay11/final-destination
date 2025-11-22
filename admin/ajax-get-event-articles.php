<?php
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    try {
        $query = "SELECT ea.*, u.username as author_name 
                 FROM event_articles ea 
                 LEFT JOIN users u ON ea.author_id = u.id 
                 WHERE ea.event_id = ? 
                 ORDER BY ea.created_at DESC";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$event_id]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'articles' => $articles
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Failed to load articles: ' . $e->getMessage()
        ]);
    }
}
?>