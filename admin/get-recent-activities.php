<?php
require_once 'includes/header.php';

try {
    // Get real recent activities from audit logs
    $stmt = $pdo->prepare("
        SELECT al.action, al.table_name, al.record_id, al.created_at,
               u.full_name as user_name,
               i.item_name,
               CASE 
                   WHEN al.action = 'Inventory Item Added' THEN 'donation'
                   WHEN al.action = 'Inventory Item Updated' THEN 'stock-update'
                   WHEN al.action = 'Inventory Item Deleted' THEN 'alert'
                   ELSE 'stock-update'
               END as activity_type
        FROM audit_logs al
        LEFT JOIN users u ON al.user_id = u.id
        LEFT JOIN inventory i ON al.record_id = i.item_id
        WHERE al.table_name = 'inventory' 
           OR (al.table_name = 'donations' AND al.action LIKE '%Donation%')
        ORDER BY al.created_at DESC 
        LIMIT 5
    ");
    $stmt->execute();
    $recentActivities = $stmt->fetchAll();

    $html = '';
    
    if (empty($recentActivities)) {
        $html = '<div class="no-activities"><p>No recent activities to display.</p></div>';
    } else {
        foreach ($recentActivities as $activity) {
            $html .= '
            <div class="activity-item ' . htmlspecialchars($activity['activity_type']) . '">
                <div class="activity-title">' . 
                    getActivityTitle($activity['action']) . 
                '</div>
                <div class="activity-description">' . 
                    htmlspecialchars($activity['item_name'] ?? 'Inventory item') . 
                '</div>
                <div class="activity-meta">
                    <span class="activity-user">By: ' . htmlspecialchars($activity['user_name'] ?? 'System') . '</span>
                    <span class="activity-time">' . timeAgo($activity['created_at']) . '</span>
                </div>
            </div>';
        }
    }
    
    echo json_encode(['success' => true, 'html' => $html]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading activities']);
}

function getActivityTitle($action) {
    switch($action) {
        case 'Inventory Item Added':
            return "New Item Added:";
        case 'Inventory Item Updated':
            return "Stock Updated:";
        case 'Inventory Item Deleted':
            return "Item Removed:";
        case 'Donation Recorded':
            return "New Donation:";
        default:
            return htmlspecialchars($action) . ":";
    }
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $time_diff = time() - $time;
    
    if ($time_diff < 60) {
        return 'Just now';
    } elseif ($time_diff < 3600) {
        return floor($time_diff / 60) . ' minutes ago';
    } elseif ($time_diff < 86400) {
        return floor($time_diff / 3600) . ' hours ago';
    } else {
        return floor($time_diff / 86400) . ' days ago';
    }
}
?>