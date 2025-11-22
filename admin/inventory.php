<?php
$pageTitle = 'Inventory Management - Orphanfare';
require_once 'includes/header.php';

// Get inventory statistics
try {
    // Total items count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total_items FROM inventory");
    $stmt->execute();
    $totalItems = $stmt->fetch()['total_items'] ?? 0;

    // Low stock items count (below minimum stock level)
    $stmt = $pdo->prepare("SELECT COUNT(*) as low_stock_items FROM inventory WHERE quantity <= min_stock_level");
    $stmt->execute();
    $lowStockItems = $stmt->fetch()['low_stock_items'] ?? 0;

    // Categories count
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT category) as category_count FROM inventory");
    $stmt->execute();
    $categoryCount = $stmt->fetch()['category_count'] ?? 0;

    // Get inventory items
    $stmt = $pdo->prepare("
        SELECT item_id, item_name, category, quantity, min_stock_level, unit, 
               location, supplier, last_restocked, notes,
               CASE 
                   WHEN quantity <= min_stock_level THEN 'low'
                   WHEN quantity <= min_stock_level * 2 THEN 'medium'
                   ELSE 'good'
               END as stock_status
        FROM inventory 
        ORDER BY 
            CASE 
                WHEN quantity <= min_stock_level THEN 1
                WHEN quantity <= min_stock_level * 2 THEN 2
                ELSE 3
            END,
            item_name
    ");
    $stmt->execute();
    $inventoryItems = $stmt->fetchAll();

    // Get recent activities
    $recentActivities = [];
    try {
        $stmt = $pdo->prepare("
            SELECT 
                'inventory_update' as activity_type,
                CONCAT('Stock Updated: ', item_name) as title,
                CONCAT('Quantity: ', quantity, ' ', unit, ' (Min: ', min_stock_level, ')') as description,
                updated_at as activity_time,
                'stock-update' as css_class
            FROM inventory 
            WHERE updated_at IS NOT NULL
            UNION ALL
            SELECT 
                'low_stock' as activity_type,
                'Low Stock Alert:' as title,
                CONCAT(item_name, ' - Only ', quantity, ' ', unit, ' remaining') as description,
                NOW() as activity_time,
                'alert' as css_class
            FROM inventory 
            WHERE quantity <= min_stock_level
            UNION ALL
            SELECT 
                'new_donation' as activity_type,
                'New Donation Received:' as title,
                CONCAT(item_name, ' - ', quantity, ' ', unit, ' added') as description,
                created_at as activity_time,
                'donation' as css_class
            FROM inventory 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            ORDER BY activity_time DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $recentActivities = $stmt->fetchAll();
    } catch (Exception $e) {
        // If complex query fails, use simple one
        $stmt = $pdo->prepare("
            SELECT 
                item_name as title,
                CONCAT('Current stock: ', quantity, ' ', unit) as description,
                updated_at as activity_time,
                'stock-update' as css_class
            FROM inventory 
            ORDER BY updated_at DESC 
            LIMIT 5
        ");
        $stmt->execute();
        $recentActivities = $stmt->fetchAll();
    }

} catch (Exception $e) {
    error_log("Inventory page error: " . $e->getMessage());
    $totalItems = 0;
    $lowStockItems = 0;
    $categoryCount = 0;
    $inventoryItems = [];
    $recentActivities = [];
}

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Time ago function
function timeAgo($datetime) {
    if (empty($datetime)) return 'Unknown time';
    
    $time = strtotime($datetime);
    $time_diff = time() - $time;
    
    if ($time_diff < 60) {
        return 'Just now';
    } elseif ($time_diff < 3600) {
        $minutes = floor($time_diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($time_diff < 86400) {
        $hours = floor($time_diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($time_diff < 2592000) {
        $days = floor($time_diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>

<main class="main-content">
    <h1 class="page-title">Inventory & Resources</h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php 
            switch($success) {
                case 'item_added':
                    echo "Inventory item added successfully!";
                    break;
                case 'item_updated':
                    echo "Inventory item updated successfully!";
                    break;
                case 'item_deleted':
                    echo "Inventory item deleted successfully!";
                    break;
                default:
                    echo "Operation completed successfully!";
            }
            ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-error">
            <?php 
            switch($error) {
                case 'add_failed':
                    echo "Failed to add inventory item. Please try again.";
                    break;
                case 'update_failed':
                    echo "Failed to update inventory item. Please try again.";
                    break;
                case 'delete_failed':
                    echo "Failed to delete inventory item. Please try again.";
                    break;
                default:
                    echo "An error occurred. Please try again.";
            }
            ?>
        </div>
    <?php endif; ?>

    <!-- Low Inventory Alert -->
    <?php if ($lowStockItems > 0): ?>
    <div class="low-inventory-alert">
        <p>
            <span class="alert-title">Low Inventory Alert:</span>
            <?php echo htmlspecialchars($lowStockItems); ?> item<?php echo $lowStockItems !== 1 ? 's are' : ' is'; ?> running low and need restocking
        </p>
    </div>
    <?php endif; ?>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo htmlspecialchars($totalItems); ?></div>
            <div class="stat-header">Total Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo htmlspecialchars($lowStockItems); ?></div>
            <div class="stat-header">Low Stock Items</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo htmlspecialchars($categoryCount); ?></div>
            <div class="stat-header">Categories</div>
        </div>
    </div>

    <!-- Inventory Management Section -->
    <div class="inventory-section">
        <div class="section-header">
            <h2 class="section-title">Inventory Management</h2>
            <div class="action-buttons">
                <button class="btn btn-primary" onclick="showAddItemModal()">Add New Item</button>
                <button class="btn btn-success" onclick="generateInventoryReport()">Generate Report</button>
            </div>
        </div>

        <!-- Search -->
        <div class="search-container">
            <input type="text" class="search-input" id="inventorySearch" placeholder="Search Inventory Items..." onkeyup="filterInventory()">
        </div>

        <!-- Inventory Items -->
        <div class="inventory-items" id="inventoryItemsList">
            <?php if (empty($inventoryItems)): ?>
                <div class="no-items">
                    <p>No inventory items found.</p>
                    <button class="btn btn-primary" onclick="showAddItemModal()">Add Your First Item</button>
                </div>
            <?php else: ?>
                <?php foreach ($inventoryItems as $item): ?>
                <div class="inventory-item" data-name="<?php echo strtolower(htmlspecialchars($item['item_name'])); ?>" data-category="<?php echo strtolower(htmlspecialchars($item['category'])); ?>">
                    <div class="item-info">
                        <div class="item-name"><?php echo htmlspecialchars($item['item_name']); ?></div>
                        <div class="item-quantity quantity-<?php echo htmlspecialchars($item['stock_status']); ?>">
                            ● <?php echo htmlspecialchars($item['quantity']); ?> <?php echo htmlspecialchars($item['unit']); ?>
                            <?php if ($item['stock_status'] === 'low'): ?>
                                <span class="low-stock-warning">(Low Stock!)</span>
                            <?php endif; ?>
                        </div>
                        <div class="item-details">
                            <span class="item-location">Location: <?php echo htmlspecialchars($item['location'] ?? 'Not specified'); ?></span>
                            <?php if ($item['last_restocked']): ?>
                                <span class="item-updated">Last restocked: <?php echo formatDate($item['last_restocked']); ?></span>
                            <?php else: ?>
                                <span class="item-updated">Never restocked</span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($item['notes'])): ?>
                            <div class="item-notes"><?php echo htmlspecialchars($item['notes']); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="item-category"><?php echo htmlspecialchars($item['category']); ?></div>
                    <div class="item-actions">
                        <button class="btn-small btn-update" onclick="showUpdateModal('<?php echo $item['item_id']; ?>')">Update</button>
                        <button class="btn-small btn-delete" onclick="deleteItem('<?php echo $item['item_id']; ?>', '<?php echo htmlspecialchars($item['item_name']); ?>')">🗑</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="recent-activities">
        <div class="activities-header">
            <h3 class="section-title">Recent Activities</h3>
            <button class="btn-refresh" onclick="refreshActivities()" title="Refresh Activities">🔄</button>
        </div>
        <div class="activities-content" id="activitiesContent">
            <?php if (empty($recentActivities)): ?>
                <div class="no-activities">
                    <p>No recent activities to display.</p>
                    <p class="activity-help">Activities will appear here when you add, update, or manage inventory items.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recentActivities as $activity): ?>
                <div class="activity-item <?php echo htmlspecialchars($activity['css_class'] ?? 'stock-update'); ?>">
                    <div class="activity-title"><?php echo htmlspecialchars($activity['title']); ?></div>
                    <div class="activity-description"><?php echo htmlspecialchars($activity['description']); ?></div>
                    <div class="activity-time"><?php echo timeAgo($activity['activity_time']); ?></div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Add Item Modal -->
<div class="modal-overlay" id="addItemModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Inventory Item</h3>
            <button class="modal-close" onclick="hideAddItemModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="addItemForm" method="POST" action="process-inventory.php">
                <input type="hidden" name="action" value="add_item">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Item Name *</label>
                        <input type="text" name="item_name" class="form-input" required 
                               placeholder="e.g., Children's Clothing (2-5 years)">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Food & Nutrition">Food & Nutrition</option>
                            <option value="Medical">Medical</option>
                            <option value="Educational">Educational</option>
                            <option value="Recreation">Recreation</option>
                            <option value="Hygiene">Hygiene</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" class="form-input" 
                               min="0" value="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Unit *</label>
                        <select name="unit" class="form-select" required>
                            <option value="pcs">Pieces</option>
                            <option value="cans">Cans</option>
                            <option value="kits">Kits</option>
                            <option value="books">Books</option>
                            <option value="packs">Packs</option>
                            <option value="boxes">Boxes</option>
                            <option value="bottles">Bottles</option>
                            <option value="sets">Sets</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Minimum Stock Level *</label>
                        <input type="number" name="min_stock_level" class="form-input" 
                               min="1" value="10" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" class="form-input" 
                               placeholder="e.g., Storage Room A, Shelf 3">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Supplier</label>
                        <input type="text" name="supplier" class="form-input" 
                               placeholder="e.g., Local Supplier Inc.">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Restocked</label>
                        <input type="date" name="last_restocked" class="form-input">
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-textarea" rows="3" 
                                  placeholder="Additional notes about this item..."></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideAddItemModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Add Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Item Modal -->
<div class="modal-overlay" id="updateItemModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Inventory Item</h3>
            <button class="modal-close" onclick="hideUpdateModal()">×</button>
        </div>
        <div class="modal-body">
            <form id="updateItemForm" method="POST" action="process-inventory.php">
                <input type="hidden" name="action" value="update_item">
                <input type="hidden" name="item_id" id="updateItemId">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Item Name *</label>
                        <input type="text" name="item_name" id="updateItemName" class="form-input" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category" id="updateCategory" class="form-select" required>
                            <option value="">Select Category</option>
                            <option value="Clothing">Clothing</option>
                            <option value="Food & Nutrition">Food & Nutrition</option>
                            <option value="Medical">Medical</option>
                            <option value="Educational">Educational</option>
                            <option value="Recreation">Recreation</option>
                            <option value="Hygiene">Hygiene</option>
                            <option value="Furniture">Furniture</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Quantity *</label>
                        <input type="number" name="quantity" id="updateQuantity" class="form-input" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Unit *</label>
                        <select name="unit" id="updateUnit" class="form-select" required>
                            <option value="pcs">Pieces</option>
                            <option value="cans">Cans</option>
                            <option value="kits">Kits</option>
                            <option value="books">Books</option>
                            <option value="packs">Packs</option>
                            <option value="boxes">Boxes</option>
                            <option value="bottles">Bottles</option>
                            <option value="sets">Sets</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Minimum Stock Level *</label>
                        <input type="number" name="min_stock_level" id="updateMinStock" class="form-input" min="1" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Location</label>
                        <input type="text" name="location" id="updateLocation" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Supplier</label>
                        <input type="text" name="supplier" id="updateSupplier" class="form-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Last Restocked</label>
                        <input type="date" name="last_restocked" id="updateLastRestocked" class="form-input">
                    </div>
                    
                    <div class="form-group full-width">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" id="updateNotes" class="form-textarea" rows="3"></textarea>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideUpdateModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Item Confirmation Modal -->
<div class="modal-overlay" id="deleteItemModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Inventory Item</h3>
            <button class="modal-close" onclick="hideDeleteItemModal()">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this inventory item? This action cannot be undone.</p>
            <div class="delete-item-info" id="deleteItemInfo"></div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="hideDeleteItemModal()">Cancel</button>
                <button type="button" class="btn-submit btn-danger" onclick="confirmDeleteItem()">Yes, Delete Item</button>
            </div>
        </div>
    </div>
</div>

<style>
/* Inventory Management Styles */
.low-inventory-alert {
    background-color: #fff3cd;
    border: 1px solid #ffeeba;
    color: #856404;
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-title {
    font-weight: 600;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: #2a2a2a;
    border: 1px solid #3a3a3a;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
}

.stat-header {
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 10px;
}

.stat-value {
    color: #ffffff;
    font-size: 32px;
    font-weight: 600;
}

.inventory-section {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 24px;
    margin-top: 24px;
    border: 1px solid #3a3a3a;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.section-title {
    color: #ffffff;
    font-size: 20px;
    font-weight: 600;
    margin: 0;
}

.action-buttons {
    display: flex;
    gap: 12px;
}

.search-container {
    margin-bottom: 20px;
}

.search-input {
    width: 100%;
    padding: 12px 16px;
    background: #1a1a1a;
    border: 1px solid #3a3a3a;
    border-radius: 8px;
    color: white;
    font-size: 14px;
}

.search-input:focus {
    outline: none;
    border-color: #3b82f6;
}

.inventory-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.inventory-item {
    background: #333;
    border: 1px solid #3a3a3a;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}

.inventory-item:hover {
    background: #3a3a3a;
    border-color: #4a4a4a;
}

.item-info {
    flex: 1;
}

.item-name {
    color: #ffffff;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 8px;
}

.item-quantity {
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.quantity-low {
    color: #dc3545;
}

.quantity-medium {
    color: #ffc107;
}

.quantity-good {
    color: #28a745;
}

.low-stock-warning {
    background: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.item-details {
    display: flex;
    gap: 16px;
    font-size: 12px;
    color: #888;
    margin-bottom: 6px;
}

.item-notes {
    color: #b8c5ff;
    font-size: 13px;
    margin-top: 6px;
    font-style: italic;
    background: rgba(255,255,255,0.1);
    padding: 6px 8px;
    border-radius: 4px;
    border-left: 3px solid #3b82f6;
}

.item-category {
    background: #3b82f6;
    color: white;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0 16px;
    white-space: nowrap;
    min-width: 80px;
    text-align: center;
}

.item-actions {
    display: flex;
    gap: 8px;
}

.btn-small {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.2s;
    min-width: 60px;
}

.btn-update {
    background: #17a2b8;
    color: white;
}

.btn-update:hover {
    background: #138496;
    transform: translateY(-1px);
}

.btn-delete {
    background: #dc3545;
    color: white;
}

.btn-delete:hover {
    background: #c82333;
    transform: translateY(-1px);
}

.no-items {
    text-align: center;
    padding: 40px 20px;
    color: #888;
}

.no-items p {
    margin-bottom: 16px;
}

/* Recent Activities Styles */
.recent-activities {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 24px;
    margin-top: 24px;
    border: 1px solid #3a3a3a;
}

.activities-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.activities-header h3 {
    color: #ffffff;
    font-size: 18px;
    font-weight: 600;
    margin: 0;
}

.btn-refresh {
    background: #6c757d;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-refresh:hover {
    background: #5a6268;
    transform: scale(1.05);
}

.activities-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    padding: 16px;
    border-radius: 8px;
    border-left: 4px solid;
    background: #333;
    transition: all 0.2s;
}

.activity-item:hover {
    background: #3a3a3a;
    transform: translateX(4px);
}

.activity-item.donation {
    border-left-color: #28a745;
    background: rgba(40, 167, 69, 0.1);
}

.activity-item.stock-update {
    border-left-color: #17a2b8;
    background: rgba(23, 162, 184, 0.1);
}

.activity-item.alert {
    border-left-color: #ffc107;
    background: rgba(255, 193, 7, 0.1);
}

.activity-title {
    color: #ffffff;
    font-weight: 600;
    margin-bottom: 6px;
    font-size: 14px;
}

.activity-description {
    color: #b8c5ff;
    font-size: 13px;
    margin-bottom: 6px;
    line-height: 1.4;
}

.activity-time {
    color: #888;
    font-size: 11px;
    font-weight: 500;
}

.no-activities {
    text-align: center;
    padding: 40px 20px;
    color: #888;
}

.no-activities p {
    margin-bottom: 8px;
}

.activity-help {
    font-size: 12px;
    color: #666;
    margin-top: 8px;
}

.loading-activities {
    text-align: center;
    padding: 30px;
    color: #888;
    font-size: 14px;
}

.loading-activities::after {
    content: '...';
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0%, 33% { content: '.'; }
    34%, 66% { content: '..'; }
    67%, 100% { content: '...'; }
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-content {
    background: #2a2a2a;
    border-radius: 12px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid #3a3a3a;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #3a3a3a;
}

.modal-header h3 {
    color: #ffffff;
    margin: 0;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    color: #cccccc;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-body {
    padding: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.form-group {
    margin-bottom: 16px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    display: block;
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 6px;
}

.form-input, .form-select, .form-textarea {
    width: 100%;
    padding: 10px 12px;
    background-color: #1a1a1a;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    color: #ffffff;
    font-size: 14px;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #3a3a3a;
}

.btn-cancel {
    background: #6c757d;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-cancel:hover {
    background: #5a6268;
}

.btn-submit {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-submit:hover {
    background: #2563eb;
}

.btn-danger {
    background: #dc3545 !important;
}

.btn-danger:hover {
    background: #c82333 !important;
}

.delete-item-info {
    background: #333;
    padding: 12px;
    border-radius: 6px;
    margin: 16px 0;
    border-left: 4px solid #dc3545;
}

.delete-item-info h4 {
    color: #fff;
    margin-bottom: 8px;
}

.delete-item-details {
    color: #b8c5ff;
    font-size: 14px;
}

@media (max-width: 768px) {
    .section-header {
        flex-direction: column;
        gap: 16px;
        align-items: flex-start;
    }
    
    .action-buttons {
        width: 100%;
        justify-content: flex-start;
    }
    
    .inventory-item {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }
    
    .item-category {
        align-self: flex-start;
        margin: 0;
    }
    
    .item-actions {
        justify-content: flex-end;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
let currentItemId = null;

function showAddItemModal() {
    document.getElementById('addItemModal').classList.add('active');
}

function hideAddItemModal() {
    document.getElementById('addItemModal').classList.remove('active');
}

function showUpdateModal(itemId) {
    currentItemId = itemId;
    
    // For now, we'll use a simple approach since get-inventory-item.php might not exist
    // In a real implementation, you'd fetch the item data from the server
    console.log('Update item:', itemId);
    
    // Show the modal - the form will need to be populated by a separate PHP file
    document.getElementById('updateItemModal').classList.add('active');
    
    // Set the item ID in the form
    document.getElementById('updateItemId').value = itemId;
}

function hideUpdateModal() {
    document.getElementById('updateItemModal').classList.remove('active');
    currentItemId = null;
}

function showDeleteItemModal(itemId, itemName) {
    currentItemId = itemId;
    
    const infoDiv = document.getElementById('deleteItemInfo');
    infoDiv.innerHTML = `
        <h4>Item Details</h4>
        <div class="delete-item-details">
            <strong>Item Name:</strong> ${itemName}<br>
            <strong>Item ID:</strong> ${itemId}
        </div>
    `;
    
    document.getElementById('deleteItemModal').classList.add('active');
}

function hideDeleteItemModal() {
    document.getElementById('deleteItemModal').classList.remove('active');
    currentItemId = null;
}

function deleteItem(itemId, itemName) {
    showDeleteItemModal(itemId, itemName);
}

function confirmDeleteItem() {
    if (!currentItemId) return;
    
    fetch('process-inventory.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete_item&item_id=${currentItemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'inventory.php?success=item_deleted';
        } else {
            window.location.href = 'inventory.php?error=delete_failed';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.location.href = 'inventory.php?error=delete_failed';
    });
}

function filterInventory() {
    const searchTerm = document.getElementById('inventorySearch').value.toLowerCase();
    const items = document.querySelectorAll('.inventory-item');
    
    items.forEach(item => {
        const itemName = item.getAttribute('data-name');
        const itemCategory = item.getAttribute('data-category');
        
        if (itemName.includes(searchTerm) || itemCategory.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function generateInventoryReport() {
    alert('Inventory report generation would be implemented here.');
    // window.open('inventory-report.php', '_blank');
}

function refreshActivities() {
    const activitiesContent = document.getElementById('activitiesContent');
    activitiesContent.innerHTML = '<div class="loading-activities">Loading activities</div>';
    
    // Reload the page to get fresh activities
    setTimeout(() => {
        window.location.reload();
    }, 500);
}

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const addItemModal = document.getElementById('addItemModal');
    const updateItemModal = document.getElementById('updateItemModal');
    const deleteItemModal = document.getElementById('deleteItemModal');
    
    if (event.target === addItemModal) hideAddItemModal();
    if (event.target === updateItemModal) hideUpdateModal();
    if (event.target === deleteItemModal) hideDeleteItemModal();
});

// Handle form submission
document.getElementById('addItemForm')?.addEventListener('submit', function(e) {
    const itemName = this.querySelector('input[name="item_name"]').value.trim();
    
    if (!itemName) {
        e.preventDefault();
        alert('Please enter item name');
        return;
    }
});

document.getElementById('updateItemForm')?.addEventListener('submit', function(e) {
    const itemName = this.querySelector('input[name="item_name"]').value.trim();
    
    if (!itemName) {
        e.preventDefault();
        alert('Please enter item name');
        return;
    }
});

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert.parentNode) {
                alert.style.display = 'none';
            }
        }, 5000);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>