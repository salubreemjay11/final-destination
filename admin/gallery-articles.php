<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    try {
        $articles_query = "
            SELECT ea.*, u.username as author_name 
            FROM event_articles ea 
            LEFT JOIN users u ON ea.author_id = u.id 
            WHERE ea.event_id = ? 
            ORDER BY ea.created_at DESC
        ";
        $stmt = $pdo->prepare($articles_query);
        $stmt->execute([$event_id]);
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($articles)) {
            foreach ($articles as $article) {
                echo '<div class="article-item">';
                echo '<div class="article-title">' . htmlspecialchars($article['title']) . '</div>';
                echo '<div class="article-content">' . nl2br(htmlspecialchars($article['content'])) . '</div>';
                echo '<div class="article-meta">';
                echo '<span>By: ' . htmlspecialchars($article['author_name'] ?? 'Unknown') . '</span>';
                echo '<span>' . date('M j, Y', strtotime($article['created_at'])) . '</span>';
                echo '</div></div>';
            }
        } else {
            echo '<div style="text-align: center; padding: 20px; color: #888;">';
            echo '<div style="font-size: 48px; margin-bottom: 16px;">📝</div>';
            echo '<p>No articles written yet</p>';
            echo '<small>Write your first article below</small>';
            echo '</div>';
        }
        
    } catch (Exception $e) {
        error_log("Error loading articles: " . $e->getMessage());
        echo '<div class="error">Error loading articles</div>';
    }
}
?>