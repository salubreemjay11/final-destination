<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    try {
        $photos_query = "SELECT * FROM events_gallery WHERE event_id = ? ORDER BY created_at DESC";
        $stmt = $pdo->prepare($photos_query);
        $stmt->execute([$event_id]);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($photos)) {
            foreach ($photos as $photo) {
                echo '<div class="photo-item">';
                echo '<img src="' . htmlspecialchars($photo['image_path']) . '" alt="' . htmlspecialchars($photo['caption'] ?? 'Event photo') . '" class="photo-image">';
                echo '<div class="photo-info">';
                echo '<div class="photo-caption">' . htmlspecialchars($photo['caption'] ?? 'No caption') . '</div>';
                echo '<div class="photo-description">' . htmlspecialchars($photo['description'] ?? 'No description') . '</div>';
                echo '<div class="photo-date">' . date('M j, Y', strtotime($photo['created_at'])) . '</div>';
                echo '</div></div>';
            }
        } else {
            echo '<div class="no-photos" style="text-align: center; padding: 40px; color: #888; grid-column: 1 / -1;">';
            echo '<div style="font-size: 48px; margin-bottom: 16px;">📸</div>';
            echo '<p>No photos uploaded yet</p>';
            echo '<small>Switch to the Upload tab to add photos</small>';
            echo '</div>';
        }
        
    } catch (Exception $e) {
        error_log("Error loading photos: " . $e->getMessage());
        echo '<div class="error">Error loading photos</div>';
    }
}
?><?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    
    try {
        $photos_query = "SELECT * FROM events_gallery WHERE event_id = ? ORDER BY created_at DESC";
        $stmt = $pdo->prepare($photos_query);
        $stmt->execute([$event_id]);
        $photos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (!empty($photos)) {
            foreach ($photos as $photo) {
                echo '<div class="photo-item">';
                echo '<img src="' . htmlspecialchars($photo['image_path']) . '" alt="' . htmlspecialchars($photo['caption'] ?? 'Event photo') . '" class="photo-image">';
                echo '<div class="photo-info">';
                echo '<div class="photo-caption">' . htmlspecialchars($photo['caption'] ?? 'No caption') . '</div>';
                echo '<div class="photo-description">' . htmlspecialchars($photo['description'] ?? 'No description') . '</div>';
                echo '<div class="photo-date">' . date('M j, Y', strtotime($photo['created_at'])) . '</div>';
                echo '</div></div>';
            }
        } else {
            echo '<div class="no-photos" style="text-align: center; padding: 40px; color: #888; grid-column: 1 / -1;">';
            echo '<div style="font-size: 48px; margin-bottom: 16px;">📸</div>';
            echo '<p>No photos uploaded yet</p>';
            echo '<small>Switch to the Upload tab to add photos</small>';
            echo '</div>';
        }
        
    } catch (Exception $e) {
        error_log("Error loading photos: " . $e->getMessage());
        echo '<div class="error">Error loading photos</div>';
    }
}
?>