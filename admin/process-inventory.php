<?php
require_once 'includes/header.php';

if ($_POST['action'] === 'add_item') {
    try {
        // Generate item ID
        $itemId = generateId('INV', 'inventory', 'item_id');
        
        $stmt = $pdo->prepare("
            INSERT INTO inventory (
                item_id, item_name, category, quantity, min_stock_level, unit,
                location, supplier, last_restocked, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $result = $stmt->execute([
            $itemId,
            $_POST['item_name'],
            $_POST['category'],
            $_POST['quantity'],
            $_POST['min_stock_level'],
            $_POST['unit'],
            $_POST['location'] ?: null,
            $_POST['supplier'] ?: null,
            $_POST['last_restocked'] ?: null,
            $_POST['notes'] ?: null
        ]);
        
        if ($result) {
            logActivity($currentUser['id'], 'Inventory Item Added', 'inventory', $itemId);
            header('Location: inventory.php?success=item_added');
            exit();
        } else {
            header('Location: inventory.php?error=add_failed');
            exit();
        }
        
    } catch (Exception $e) {
        error_log("Inventory item addition error: " . $e->getMessage());
        header('Location: inventory.php?error=add_failed');
        exit();
    }
}

if ($_POST['action'] === 'update_item') {
    try {
        $itemId = $_POST['item_id'];
        
        $stmt = $pdo->prepare("
            UPDATE inventory SET 
                item_name = ?, category = ?, quantity = ?, min_stock_level = ?, unit = ?,
                location = ?, supplier = ?, last_restocked = ?, notes = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE item_id = ?
        ");
        
        $result = $stmt->execute([
            $_POST['item_name'],
            $_POST['category'],
            $_POST['quantity'],
            $_POST['min_stock_level'],
            $_POST['unit'],
            $_POST['location'] ?: null,
            $_POST['supplier'] ?: null,
            $_POST['last_restocked'] ?: null,
            $_POST['notes'] ?: null,
            $itemId
        ]);
        
        if ($result) {
            logActivity($currentUser['id'], 'Inventory Item Updated', 'inventory', $itemId);
            header('Location: inventory.php?success=item_updated');
            exit();
        } else {
            header('Location: inventory.php?error=update_failed');
            exit();
        }
        
    } catch (Exception $e) {
        error_log("Inventory item update error: " . $e->getMessage());
        header('Location: inventory.php?error=update_failed');
        exit();
    }
}

if ($_POST['action'] === 'delete_item') {
    try {
        $itemId = $_POST['item_id'];
        
        // Get item details for logging
        $stmt = $pdo->prepare("SELECT item_name FROM inventory WHERE item_id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch();
        
        if ($item) {
            // Delete the item
            $stmt = $pdo->prepare("DELETE FROM inventory WHERE item_id = ?");
            $result = $stmt->execute([$itemId]);
            
            if ($result) {
                logActivity($currentUser['id'], 'Inventory Item Deleted', 'inventory', $itemId, 
                    json_encode(['item_name' => $item['item_name']]), 
                    null
                );
                echo json_encode(['success' => true]);
                exit();
            }
        }
        
        echo json_encode(['success' => false, 'message' => 'Item not found']);
        exit();
        
    } catch (Exception $e) {
        error_log("Inventory item deletion error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        exit();
    }
}

// If not POST request, redirect back
header('Location: inventory.php');
exit();
?>