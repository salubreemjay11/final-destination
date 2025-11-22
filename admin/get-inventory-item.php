<?php
require_once '../config/database.php';
require_once 'includes/auth.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['item_id'])) {
    echo json_encode(['success' => false, 'message' => 'Item ID not provided']);
    exit();
}

$itemId = $_GET['item_id'];

try {
    $stmt = $pdo->prepare("
        SELECT item_id, item_name, category, quantity, min_stock_level, unit,
               location, supplier, last_restocked, notes
        FROM inventory 
        WHERE item_id = ?
    ");
    $stmt->execute([$itemId]);
    $item = $stmt->fetch();
    
    if ($item) {
        // Format date for HTML input
        if ($item['last_restocked']) {
            $item['last_restocked'] = date('Y-m-d', strtotime($item['last_restocked']));
        }
        
        echo json_encode(['success' => true, 'item' => $item]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>