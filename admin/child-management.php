<?php
// Only load the regular page content if it's not an AJAX request
$pageTitle = 'Child Management - Orphanfare';
require_once 'includes/header.php';

// Check and create missing columns for family intake
try {
    $checkColumns = [
        'civil_status' => 'VARCHAR(50) NULL',
        'birth_place' => 'VARCHAR(255) NULL',
        'educational_attainment' => 'VARCHAR(100) NULL',
        'occupation' => 'VARCHAR(100) NULL',
        'monthly_income' => 'VARCHAR(50) NULL',
        'religion' => 'VARCHAR(100) NULL',
        'family_composition' => 'TEXT NULL',
        'problem_presented' => 'TEXT NULL',
        'assessment_recommendation' => 'TEXT NULL'
    ];
    
    foreach ($checkColumns as $columnName => $columnDefinition) {
        $checkStmt = $pdo->prepare("SHOW COLUMNS FROM children LIKE ?");
        $checkStmt->execute([$columnName]);
        $columnExists = $checkStmt->fetch();
        
        if (!$columnExists) {
            $alterStmt = $pdo->prepare("ALTER TABLE children ADD COLUMN $columnName $columnDefinition");
            $alterStmt->execute();
            error_log("Added missing column: $columnName");
        }
    }
} catch (Exception $e) {
    error_log("Column check error: " . $e->getMessage());
}

// Check if user has view permission for child management
if (!$permissionManager->hasPermission('child_management', 'view')) {
    header('Location: access-denied.php');
    exit();
}

// Helper function for status badges
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Adoptable': return 'status-active';
        case 'Adopted': return 'status-approved';
        case 'In Care': return 'status-progress';
        case 'Reintegrated': return 'status-mild';
        default: return 'status-common';
    }
}

// Get children with filters
$whereClause = "WHERE 1=1";
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $whereClause .= " AND (name LIKE ? OR notes LIKE ? OR child_id LIKE ?)";
    $params = array_merge($params, [$search, $search, $search]);
}

if (isset($_GET['age_filter']) && !empty($_GET['age_filter'])) {
    switch ($_GET['age_filter']) {
        case '0-5':
            $whereClause .= " AND age BETWEEN 0 AND 5";
            break;
        case '6-10':
            $whereClause .= " AND age BETWEEN 6 AND 10";
            break;
        case '11-15':
            $whereClause .= " AND age BETWEEN 11 AND 15";
            break;
        case '16+':
            $whereClause .= " AND age >= 16";
            break;
    }
}

if (isset($_GET['gender_filter']) && !empty($_GET['gender_filter'])) {
    $whereClause .= " AND gender = ?";
    $params[] = $_GET['gender_filter'];
}

if (isset($_GET['status_filter']) && !empty($_GET['status_filter'])) {
    $whereClause .= " AND status = ?";
    $params[] = $_GET['status_filter'];
}

$children = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM children $whereClause ORDER BY created_at DESC");
    $stmt->execute($params);
    $children = $stmt->fetchAll();
} catch (Exception $e) {
    // If table doesn't exist yet, use empty array
    $children = [];
}

$totalChildren = count($children);

// Check permissions for display
$canCreate = $permissionManager->hasPermission('child_management', 'create');
$canEdit = $permissionManager->hasPermission('child_management', 'edit');
$canDelete = $permissionManager->hasPermission('child_management', 'delete');
$canView = $permissionManager->hasPermission('child_management', 'view');
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Child List
            <?php if (!$canEdit): ?>
                <span class="status-badge status-mild" style="font-size: 14px; margin-left: 10px;">Read-Only</span>
            <?php endif; ?>
        </h1>
        
        <!-- Add New Child Button - Only show if user has create permission -->
        <?php if ($canCreate): ?>
            <button class="btn btn-primary" onclick="window.location.href='unified-registration.php'">Add New Child & Case</button>
        <?php else: ?>
            <button class="btn btn-secondary" disabled title="No permission to create children">Add New Child & Case</button>
        <?php endif; ?>
    </div>

    <!-- Success/Error Notifications -->
    <?php if (isset($_GET['success'])): ?>
        <div class="notification success show">
            <div class="notification-icon">✓</div>
            <div class="notification-content">
                <div class="notification-title">Success!</div>
                <div class="notification-message">
                    <?php 
                    switch ($_GET['success']) {
                        case 'unified_created':
                            echo 'Child and case record created successfully!';
                            break;
                        case 'unified_updated':
                            echo 'Child and case record updated successfully!';
                            break;
                        case 'child_updated':
                            echo 'Child information updated successfully!';
                            break;
                        default:
                            echo 'Operation completed successfully!';
                    }
                    ?>
                </div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="notification error show">
            <div class="notification-icon">⚠</div>
            <div class="notification-content">
                <div class="notification-title">Error!</div>
                <div class="notification-message">
                    <?php 
                    switch ($_GET['error']) {
                        case 'permission_denied':
                            echo 'You do not have permission to perform this action.';
                            break;
                        case 'update_failed':
                            echo 'Failed to update child information. Please try again.';
                            break;
                        case 'create_failed':
                            echo 'Failed to create new record. Please try again.';
                            break;
                        default:
                            echo 'An error occurred. Please try again.';
                    }
                    ?>
                </div>
            </div>
            <button class="notification-close" onclick="this.parentElement.classList.remove('show')">×</button>
        </div>
    <?php endif; ?>

    <!-- Show read-only banner if no edit permission -->
    <?php if (!$canEdit): ?>
    <div class="read-only-banner" style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
        <strong>🔒 Read-Only Mode:</strong> You have view-only access to child management. You cannot perform any actions.
    </div>
    <?php endif; ?>

   <!-- Search and Filters -->
    <form method="GET" class="search-filters-container">
        <div class="search-section">
            <input type="text" class="search-input" name="search" placeholder="Search by name, notes, child ID..." 
                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            <button type="submit" class="search-btn">Search</button>
        </div>
        
        <div class="filters-section">
            <div class="filter-group">
                <select class="filter-select" name="age_filter">
                    <option value="">All Ages</option>
                    <option value="0-5" <?php echo ($_GET['age_filter'] ?? '') === '0-5' ? 'selected' : ''; ?>>0-5 years</option>
                    <option value="6-10" <?php echo ($_GET['age_filter'] ?? '') === '6-10' ? 'selected' : ''; ?>>6-10 years</option>
                    <option value="11-15" <?php echo ($_GET['age_filter'] ?? '') === '11-15' ? 'selected' : ''; ?>>11-15 years</option>
                    <option value="16+" <?php echo ($_GET['age_filter'] ?? '') === '16+' ? 'selected' : ''; ?>>16+ years</option>
                </select>
            </div>
            
            <div class="filter-group">
                <select class="filter-select" name="gender_filter">
                    <option value="">All Genders</option>
                    <option value="Male" <?php echo ($_GET['gender_filter'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo ($_GET['gender_filter'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>
            
            <div class="filter-group">
                <select class="filter-select" name="status_filter">
                    <option value="">All Status</option>
                    <option value="Adoptable" <?php echo ($_GET['status_filter'] ?? '') === 'Adoptable' ? 'selected' : ''; ?>>Adoptable</option>
                    <option value="Adopted" <?php echo ($_GET['status_filter'] ?? '') === 'Adopted' ? 'selected' : ''; ?>>Adopted</option>
                    <option value="In Care" <?php echo ($_GET['status_filter'] ?? '') === 'In Care' ? 'selected' : ''; ?>>In Care</option>
                    <option value="Reintegrated" <?php echo ($_GET['status_filter'] ?? '') === 'Reintegrated' ? 'selected' : ''; ?>>Reintegrated</option>
                </select>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">Apply Filters</button>
                <?php if (!empty($_GET['search']) || !empty($_GET['age_filter']) || !empty($_GET['gender_filter']) || !empty($_GET['status_filter'])): ?>
                <a href="child-management.php" class="btn btn-secondary">Clear All</a>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <!-- Child Registry -->
    <div class="registry-section">
        <div class="registry-header">
            <h2 class="registry-title">Child Registry</h2>
            <span class="total-count">Total: <?php echo $totalChildren; ?> children</span>
        </div>
        
        <table class="foster-table">
            <thead>
                <tr>
                    <th>Child ID</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Entry Date</th>
                    <th>Status</th>
                    <th>Key Notes/Tags</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($children as $child): ?>
                <tr>
                    <td class="foster-id clickable-id" 
                        <?php if ($canEdit): ?>onclick="showChildDetails('<?php echo htmlspecialchars($child['child_id']); ?>')" style="cursor: pointer; color: #2d5f8d;"<?php else: ?>style="cursor: not-allowed; color: #cccccc;"<?php endif; ?>>
                        <?php echo htmlspecialchars($child['child_id']); ?>
                        <?php if ($child['linked_case_id']): ?>
                        <br><small style="color: #0E7490;">Case: <?php echo htmlspecialchars($child['linked_case_id']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($child['age']); ?></td>
                    <td><?php echo htmlspecialchars($child['gender']); ?></td>
                    <td><?php echo formatDate($child['entry_date'] ?? $child['created_at']); ?></td>
                    <td><span class="status-badge <?php echo getStatusBadgeClass($child['status']); ?>">
                        <?php echo htmlspecialchars($child['status']); ?>
                    </span></td>
                    <td class="notes-cell"><?php echo htmlspecialchars($child['notes'] ?? 'No notes'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>

<!-- Child Details Modal -->
<div class="modal-overlay" id="childModal" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Loading Indicator -->
            <div id="modalLoading" class="loading-indicator" style="display: none;">
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading child information...</div>
            </div>

            <div id="modalContent">
                <div class="child-profile">
                    <div class="child-photo">
                        <!-- Use a data URI placeholder image to avoid 404 errors -->
                        <img id="childPhoto" src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiByeD0iNDAiIGZpbGw9IiMzYTNhM2EiLz4KPHN2ZyB4PSIyMCIgeT0iMjAiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNiOGM1ZmYiIHN0cm9rZS13aWR0aD0iMiI+CjxwYXRoIGQ9Ik0yMCAyMVYxOUEyIDIgMCAwIDEgMjIgMTdIMjhBMiAyIDAgMCAxIDMwIDE5VjIxTTE2IDVBNyA3IDAgMSAxIDIgNUExNiAxNiAwIDAgMSAxNiA1WiIvPgo8L3N2Zz4KPC9zdmc+" alt="Child Photo">
                    </div>
                    <div class="child-basic-info">
                        <p id="childId">ID: Loading...</p>
                        <div class="child-meta">
                            <span>Age: <span id="childAge">-</span></span>
                            <span id="childGender">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-tabs">
                    <button class="tab-btn active" onclick="switchTab('basic')">Basic info</button>
                    <button class="tab-btn" onclick="switchTab('family')">Family Intake</button>
                    <button class="tab-btn" onclick="switchTab('health')">Health Records</button>
                    <button class="tab-btn" onclick="switchTab('educational')">Educational</button>
                    <button class="tab-btn" onclick="switchTab('custom')">Additional Information</button>
                </div>
                
                <div class="tab-content">
                    <div id="basicTab" class="tab-pane active">
                        <div class="info-row">
                            <span class="info-label">Date of Birth:</span>
                            <span class="info-value" id="dateOfBirth"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Gender:</span>
                            <span class="info-value" id="modalGender"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Entry Date:</span>
                            <span class="info-value" id="entryDate"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status:</span>
                            <span class="info-value" id="childStatus"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Address:</span>
                            <span class="info-value" id="childAddress"></span>
                        </div>
                    </div>

                    <div id="familyTab" class="tab-pane">
                        <div class="info-section">
                            <h4>Identifying Information</h4>
                            <div class="info-row">
                                <span class="info-label">Civil Status:</span>
                                <span class="info-value" id="familyCivilStatus"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Birth Place:</span>
                                <span class="info-value" id="familyBirthPlace"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Educational Attainment:</span>
                                <span class="info-value" id="familyEducation"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Occupation:</span>
                                <span class="info-value" id="familyOccupation"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Monthly Income:</span>
                                <span class="info-value" id="familyIncome"></span>
                            </div>
                            <div class="info-row">
                                <span class="info-label">Religion:</span>
                                <span class="info-value" id="familyReligion"></span>
                            </div>
                        </div>

                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Family Composition</h4>
                            <div class="table-container">
                                <table id="familyCompositionTable">
                                    <thead>
                                        <tr>
                                            <th>Members</th>
                                            <th>Relationship</th>
                                            <th>Age</th>
                                            <th>Sex</th>
                                            <th>Civil Status</th>
                                            <th>Educational Attainment</th>
                                            <th>Occupation/Income</th>
                                        </tr>
                                    </thead>
                                    <tbody id="familyCompositionBody">
                                        <!-- Family members will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Problem Presented</h4>
                            <div class="info-content" id="problemPresented"></div>
                        </div>

                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Assessment & Recommendation</h4>
                            <div class="info-content" id="assessmentRecommendation"></div>
                        </div>
                    </div>
                    
                    <div id="healthTab" class="tab-pane">
                        <div class="info-row">
                            <span class="info-label">Health Status:</span>
                            <span class="info-value" id="healthStatus"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Allergies:</span>
                            <span class="info-value" id="allergies"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Emergency Contact:</span>
                            <span class="info-value" id="emergencyContact"></span>
                        </div>
                        <div class="health-notes">
                            <p id="healthNotes"></p>
                        </div>
                    </div>
                    
                    <div id="educationalTab" class="tab-pane">
                        <div class="info-row">
                            <span class="info-label">Date:</span>
                            <span class="info-value" id="eduDate"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Subject:</span>
                            <span class="info-value" id="subject">General Education</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Performance:</span>
                            <span class="info-value" id="performance">Good</span>
                        </div>
                        <div class="educational-notes">
                            <p id="educationalNotes"></p>
                        </div>
                    </div>

                    <!-- Add this with the other tab-pane divs -->
                    <div id="customTab" class="tab-pane">
                        <div class="custom-fields-section">
                            <h3>Additional Information</h3>
                            <div id="customFieldsContent">
                                <!-- Custom fields will be loaded here dynamically -->
                                <p>Loading additional information...</p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="modal-actions">
                    <!-- Edit Button -->
                    <?php if ($canEdit): ?>
                        <button class="edit-btn" onclick="enableEditMode()">Edit</button>
                    <?php else: ?>
                        <button class="edit-btn" disabled style="opacity: 0.6; cursor: not-allowed;" title="Read-only mode">Edit</button>
                    <?php endif; ?>
                    
                    <!-- Case Button - Use data attribute approach -->
                    <?php if ($canCreate): ?>
                        <button class="case-btn" id="caseActionBtn" data-child-id="">Add Case</button>
                    <?php else: ?>
                        <button class="case-btn" disabled style="opacity: 0.6; cursor: not-allowed;" title="No permission to create cases">Add Case</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* ... your existing CSS styles remain exactly the same ... */
/* Improved Filter Styles */
.search-filters-container {
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.search-section {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.search-section .search-input {
    flex: 1;
}

.filters-section {
    display: flex;
    gap: 15px;
    align-items: end;
    flex-wrap: wrap;
    font-size: 15px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 12px;
    color: #b8c5ff;
    font-weight: 500;
}

.filter-actions {
    display: flex;
    gap: 10px;
    margin-left: auto;
}

/* Reports filter improvements */
.custom-dates-group {
    display: flex;
    gap: 15px;
    align-items: end;
}

.date-input-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.date-input-group label {
    font-size: 12px;
    color: #b8c5ff;
    font-weight: 500;
}

/* Notification Styles */
.notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 400px;
    transform: translateX(400px);
    opacity: 0;
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.notification.show {
    transform: translateX(0);
    opacity: 1;
}

.notification.success {
    border-left-color: #28a745;
    background: #d4edda;
    color: #155724;
}

.notification.error {
    border-left-color: #dc3545;
    background: #f8d7da;
    color: #721c24;
}

.notification.warning {
    border-left-color: #ffc107;
    background: #fff3cd;
    color: #856404;
}

.notification.info {
    border-left-color: #17a2b8;
    background: #d1ecf1;
    color: #0c5460;
}

.notification-icon {
    font-size: 20px;
    font-weight: bold;
}

.notification-content {
    flex: 1;
}

.notification-title {
    font-weight: 600;
    margin-bottom: 4px;
}

.notification-message {
    font-size: 14px;
    opacity: 0.9;
}

.notification-close {
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.7;
    padding: 0;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-close:hover {
    opacity: 1;
}

/* Loading Indicator */
.loading-indicator {
    text-align: center;
    padding: 40px 20px;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

.loading-text {
    color: #666;
    font-size: 14px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Your existing CSS styles remain the same */
.info-section {
    margin-bottom: 20px;
}

.info-section h4 {
    color: #3b82f6;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #3a3a3a;
}

.dark-theme .info-content {
    background: #1a1a1a;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #3a3a3a;
    color: #cccccc;
    line-height: 1.5;
}

.light-theme .info-content {
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #3a3a3a;
    color: black;
    line-height: 1.5;
}

#familyCompositionTable {
    width: 100%;
    border-collapse: collapse;
    background: #1a1a1a;
}

.dark-theme #familyCompositionTable th {
    background: #333333;
    color: #b8c5ff;
    padding: 10px 8px;
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    border: 1px solid #3a3a3a;
}

.light-theme #familyCompositionTable th {
    color: #b8c5ff;
    padding: 10px 8px;
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    border: 1px solid #3a3a3a;
}

.dark-theme #familyCompositionTable td {
    padding: 8px;
    border: 1px solid #3a3a3a;
    color: #cccccc;
    font-size: 13px;
}

.light-theme #familyCompositionTable td {
    padding: 8px;
    border: 1px solid #3a3a3a;
    background-color: white;
    color: black;
    font-size: 13px;
}

.modal-actions {
    margin-top: 20px;
    text-align: right;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.edit-btn {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.edit-btn:hover {
    background: #2563eb;
}

.edit-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.case-btn {
    background: #28a745;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.case-btn:hover {
    background: #218838;
}

.case-btn:disabled {
    background: #6c757d;
    cursor: not-allowed;
}

.linked-case-badge {
    background: #28a745;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    margin-left: 10px;
}

/* Child Management Styles */
.search-container {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.search-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
}

.search-btn {
    padding: 10px 20px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}

.filters-row {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.filter-select {
    padding: 8px 12px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
    min-width: 150px;
}

.registry-section {
    background: #2a2a2a;
    border-radius: 8px;
    padding: 20px;
   
}

.registry-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.registry-title {
    color: #ffffff;
    font-size: 20px;
    font-weight: 600;
}

.total-count {
    color: #b8c5ff;
    font-size: 14px;
}

.foster-table {
    width: 100%;
    border-collapse: collapse;
}

.light-theme .foster-table th {
    background: #2d5f8d;
    color:rgb(255, 255, 255);
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid #3a3a3a;
    font-size: 15px;
}

.foster-table td {
    padding: 12px;
    border-bottom: 1px solid #3a3a3a;
}

.foster-id {
    font-weight: 600;
    color: #3b82f6;
}

.clickable-name:hover {
    text-decoration: underline;
}

.notes-cell {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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
    justify-content: flex-end;
    padding: 15px 20px;
    border-bottom: 1px solid #3a3a3a;
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

.child-profile {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    align-items: center;
}

.child-photo img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #3a3a3a;
}

.child-basic-info h2 {
    color: #ffffff;
    margin: 0 0 5px 0;
    font-size: 24px;
}

.dark-theme .child-basic-info p {
    color: #b8c5ff;
    margin: 0 0 5px 0;
}

.light-theme .child-basic-info p {
    color: #2d5f8d;
    margin: 0 0 5px 0;
    font-size: 15px;
    font-weight: 600;
}

.child-meta {
    display: flex;
    gap: 15px;
    color: #cccccc;
    font-size: 14px;
}

.light-theme .child-meta span {
    color: #0E7490;
}

.modal-tabs {
    display: flex;
    border-bottom: 1px solid #3a3a3a;
    margin-bottom: 20px;
}

.dark-theme .tab-btn {
    background: none;
    border: none;
    color: #cccccc;
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
}

.light-theme .tab-btn {
    background: none;
    border: none;
    color: #2d5f8d;
    padding: 12px 24px;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
}

.tab-btn.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
    border-bottom: 2px solid #2d5f8d;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
    
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #3a3a3a;
}

.info-label {
    color: #b8c5ff;
    font-weight: 500;
}

.info-value {
    color: #cccccc;
}

.dark-theme .health-notes {
    margin-top: 15px;
    padding: 15px;
    background: #1a1a1a;
    border-radius: 6px;
    color: #cccccc;
}

.dark-theme .educational-notes {
    margin-top: 15px;
    padding: 15px;
    background: #1a1a1a;
    border-radius: 6px;
    color: #cccccc;
}

.light-theme .health-notes {
    margin-top: 15px;
    padding: 15px;
    background: white;
    border-radius: 6px;
    color: black;
}

.light-theme .educational-notes {
    margin-top: 15px;
    padding: 15px;
    background: #ffffff;
    border-radius: 6px;
    color: #334155;
}   

.modal-actions {
    margin-top: 20px;
    text-align: right;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-active { background: #d4edda; color: #155724; font-weight: bold; font-size: 13px;}
.status-approved { background: #d1ecf1; color: #003366; font-weight: bold; font-size: 13px;}
.status-progress { background: #fff3cd; color: #856404; font-weight: bold; font-size: 13px;}
.status-mild { background: #e2e3e5; color: #383d41; font-weight: bold; font-size: 13px;}
.status-common { background: #f8d7da; color: #721c24; font-weight: bold; font-size: 13px;}

/* Edit Form Styles */
.edit-form {
    padding: 10px 0;
}

.edit-form .form-group {
    margin-bottom: 15px;
}

.edit-form .form-label {
    display: block;
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    margin-bottom: 5px;
}

.edit-form .form-input,
.edit-form .form-select,
.edit-form .form-textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #3a3a3a;
    border-radius: 4px;
    background: #1a1a1a;
    color: white;
    font-size: 14px;
}

.edit-form .form-input:focus,
.edit-form .form-select:focus,
.edit-form .form-textarea:focus {
    outline: none;
    border-color: #3b82f6;
}

.edit-form .form-textarea {
    resize: vertical;
    min-height: 80px;
}

/* Make sure modal content can handle the forms */
.modal-content {
    max-width: 700px;
}

.tab-pane {
    min-height: 300px;
}

.read-only-banner {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
}

/* Custom Fields Styling */
.custom-fields-section {
    margin-top: 20px;
}

.custom-fields-section h3 {
    color: #3b82f6;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #3a3a3a;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #3a3a3a;
}

.info-label {
    color: #b8c5ff;
    font-weight: 500;
    flex: 1;
}

.info-value {
    color: #cccccc;
    flex: 2;
    text-align: right;
}


</style>

<script>
let currentChildId = null;
let currentChildData = null;
let isEditMode = false;
let isSaving = false;

// Get PHP permissions from the page
const canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
const canCreate = <?php echo $canCreate ? 'true' : 'false'; ?>;

// Define showChildDetails FIRST so it's available when HTML is rendered
function showChildDetails(childId) {
    if (!canEdit) {
        showNotification('Read-only mode - You cannot view child details', 'error');
        return;
    }
    
    // Check if modal exists
    const childModal = document.getElementById('childModal');
    if (!childModal) {
        console.error('Child modal not found in DOM');
        showNotification('Error: Cannot open child details', 'error');
        return;
    }

    // ALWAYS reset to view mode when opening modal
    isEditMode = false;
    isSaving = false;
    
    // Reset buttons to view mode
    const editBtn = document.querySelector('.edit-btn');
    const caseBtn = document.querySelector('.case-btn');
    
    if (editBtn) {
        editBtn.textContent = 'Edit';
        editBtn.disabled = false;
    }
    if (caseBtn) {
        caseBtn.style.display = 'inline-block';
    }
    
    currentChildId = childId;
    
    console.log('Fetching details for child:', childId);
    
    // Show modal immediately
    childModal.classList.add('active');
    
    // Load child details
    loadChildDetails(childId);
}

// Utility function for safe DOM operations
function safeQuerySelector(selector) {
    const element = document.querySelector(selector);
    if (!element) {
        console.warn(`Element not found: ${selector}`);
    }
    return element;
}

function safeGetElement(id) {
    const element = document.getElementById(id);
    if (!element) {
        console.warn(`Element not found: #${id}`);
    }
    return element;
}

// Reset modal state completely
function resetModalState() {
    isEditMode = false;
    isSaving = false;
    
    // Reset buttons to view mode
    const editBtn = document.querySelector('.edit-btn');
    const caseBtn = document.querySelector('.case-btn');
    
    if (editBtn) {
        editBtn.textContent = 'Edit';
        editBtn.disabled = false;
    }
    if (caseBtn) {
        caseBtn.style.display = 'inline-block';
    }
}

// Notification system
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `notification ${type} show`;
    notification.innerHTML = `
        <div class="notification-icon">${type === 'success' ? '✓' : '⚠'}</div>
        <div class="notification-content">
            <div class="notification-title">${type === 'success' ? 'Success!' : 'Error!'}</div>
            <div class="notification-message">${message}</div>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">×</button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function populateModal(child) {
    // Safety check - ensure we're in view mode
    if (isEditMode) {
        console.warn('populateModal called while in edit mode, forcing view mode');
        isEditMode = false;
        resetModalState();
    }

    console.log('Child data received:', child);
    currentChildData = child;
    
    try {
        // Update basic info - with null checks
        document.getElementById('childId').textContent = 'ID: ' + (child.child_id || 'Unknown');
        document.getElementById('childAge').textContent = child.age || 'Unknown';
        document.getElementById('childGender').textContent = child.gender || 'Unknown';
        
        // Only set modalGender if the element exists
        const modalGender = document.getElementById('modalGender');
        if (modalGender) {
            modalGender.textContent = child.gender || 'Unknown';
        }
        
        // Basic info tab - with null checks
        const dateOfBirth = document.getElementById('dateOfBirth');
        if (dateOfBirth) dateOfBirth.textContent = child.date_of_birth || 'Not specified';
        
        const entryDate = document.getElementById('entryDate');
        if (entryDate) entryDate.textContent = child.entry_date || 'Not specified';
        
        const childAddress = document.getElementById('childAddress');
        if (childAddress) childAddress.textContent = child.address || 'Not specified';
        
        // Status field
        const childStatus = document.getElementById('childStatus');
        if (childStatus) childStatus.textContent = child.status || 'Not specified';
        
        // Health tab - with null checks
        const healthStatus = document.getElementById('healthStatus');
        if (healthStatus) healthStatus.textContent = child.health_status || 'Not specified';
        
        const allergies = document.getElementById('allergies');
        if (allergies) allergies.textContent = child.allergies || 'None reported';
        
        const emergencyContact = document.getElementById('emergencyContact');
        if (emergencyContact) emergencyContact.textContent = child.emergency_contact || 'Not specified';
        
        const healthNotes = document.getElementById('healthNotes');
        if (healthNotes) healthNotes.textContent = child.problem_description || 'No health notes available';
        
        // Educational tab - with null checks
        const educationalNotes = document.getElementById('educationalNotes');
        if (educationalNotes) educationalNotes.textContent = child.notes || 'No educational notes available';

        // Family Intake Information - with null checks
        const familyCivilStatus = document.getElementById('familyCivilStatus');
        if (familyCivilStatus) familyCivilStatus.textContent = child.civil_status || 'Not specified';
        
        const familyBirthPlace = document.getElementById('familyBirthPlace');
        if (familyBirthPlace) familyBirthPlace.textContent = child.birth_place || 'Not specified';
        
        const familyEducation = document.getElementById('familyEducation');
        if (familyEducation) familyEducation.textContent = child.educational_attainment || 'Not specified';
        
        const familyOccupation = document.getElementById('familyOccupation');
        if (familyOccupation) familyOccupation.textContent = child.occupation || 'Not specified';
        
        const familyIncome = document.getElementById('familyIncome');
        if (familyIncome) familyIncome.textContent = child.monthly_income || 'Not specified';
        
        const familyReligion = document.getElementById('familyReligion');
        if (familyReligion) familyReligion.textContent = child.religion || 'Not specified';
        
        const problemPresented = document.getElementById('problemPresented');
        if (problemPresented) problemPresented.textContent = child.problem_presented || 'No information provided';
        
        const assessmentRecommendation = document.getElementById('assessmentRecommendation');
        if (assessmentRecommendation) assessmentRecommendation.textContent = child.assessment_recommendation || 'No assessment provided';
        
        // Update case button based on whether child already has a case
        updateCaseButton(child);

        // Populate family composition table
        const familyCompositionBody = document.getElementById('familyCompositionBody');
        if (familyCompositionBody) {
            familyCompositionBody.innerHTML = '';
            
            if (child.family_composition) {
                try {
                    const familyMembers = JSON.parse(child.family_composition);
                    if (familyMembers && familyMembers.length > 0) {
                        familyMembers.forEach(member => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${member.name || ''}</td>
                                <td>${member.relationship || ''}</td>
                                <td>${member.age || ''}</td>
                                <td>${member.sex || ''}</td>
                                <td>${member.civil_status || ''}</td>
                                <td>${member.educational_attainment || ''}</td>
                                <td>${member.occupation_income || ''}</td>
                            `;
                            familyCompositionBody.appendChild(row);
                        });
                    } else {
                        familyCompositionBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No family members recorded</td></tr>';
                    }
                } catch (e) {
                    console.error('Error parsing family composition:', e);
                    familyCompositionBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Error loading family composition</td></tr>';
                }
            } else {
                familyCompositionBody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No family composition data available</td></tr>';
            }
        }
        
        // Handle photo
        const childPhoto = document.getElementById('childPhoto');
        if (childPhoto) {
            // Reset to placeholder first
            childPhoto.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiByeD0iNDAiIGZpbGw9IiMzYTNhM2EiLz4KPHN2ZyB4PSIyMCIgeT0iMjAiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNiOGM1ZmYiIHN0cm9rZS13aWR0aD0iMiI+CjxwYXRoIGQ9Ik0yMCAyMVYxOUEyIDIgMCAwIDEgMjIgMTdIMjhBMiAyIDAgMCAxIDMwIDE5VjIxTTE2IDVBNyA3IDAgMSAxIDIgNUExNiAxNiAwIDAgMSAxNiA1WiIvPgo8L3N2Zz4KPC9zdmc+';
            
            if (child.photo_path && child.photo_path !== 'public/placeholder.jpg') {
                let imagePath = child.photo_path;
                
                // Clean up the path
                if (imagePath.startsWith('/')) {
                    imagePath = imagePath.substring(1);
                }
                
                // Remove any leading ../ or ./
                imagePath = imagePath.replace(/^(\.\.\/|\.\/)+/, '');
                
                // Check if the path is already a full URL
                if (imagePath.startsWith('http')) {
                    childPhoto.src = imagePath;
                } else {
                    // Try different possible path structures
                    const possiblePaths = [
                        imagePath,
                        '../' + imagePath,
                        'uploads/children/' + child.child_id + '.jpg',
                        'uploads/children/' + child.child_id + '.png',
                        'uploads/children/' + child.child_id + '.jpeg'
                    ];
                    
                    // Function to try loading image
                    const tryLoadImage = (path, index) => {
                        const img = new Image();
                        img.onload = function() {
                            console.log('Image loaded successfully from:', path);
                            childPhoto.src = path;
                        };
                        img.onerror = function() {
                            console.log('Failed to load image from:', path);
                            if (index < possiblePaths.length - 1) {
                                tryLoadImage(possiblePaths[index + 1], index + 1);
                            }
                        };
                        img.src = path;
                    };
                    
                    // Start trying paths
                    tryLoadImage(possiblePaths[0], 0);
                }
                
                // Add error handler as backup
                childPhoto.onerror = function() {
                    this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjgwIiBoZWlnaHQ9IjgwIiByeD0iNDAiIGZpbGw9IiMzYTNhM2EiLz4KPHN2ZyB4PSIyMCIgeT0iMjAiIHdpZHRoPSI0MCIgaGVpZ2h0PSI0MCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiNiOGM1ZmYiIHN0cm9rZS13aWR0aD0iMiI+CjxwYXRoIGQ9Ik0yMCAyMVYxOUEyIDIgMCAwIDEgMjIgMTdIMjhBMiAyIDAgMCAxIDMwIDE5VjIxTTE2IDVBNyA3IDAgMSAxIDIgNUExNiAxNiAwIDAgMSAxNiA1WiIvPgo8L3N2Zz4KPC9zdmc+';
                };
            }
        }

        // Load custom fields
        loadCustomFields(child.child_id);
        
    } catch (error) {
        console.error('Error populating modal:', error);
        showNotification('Error loading child details', 'error');
    }
}

// Function to load custom fields for a child
function loadCustomFields(childId) {
    fetch('ajax-get-custom-fields.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_child_custom_fields&child_id=' + encodeURIComponent(childId)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayCustomFields(data.fields);
        } else {
            document.getElementById('customFieldsContent').innerHTML = 
                '<p>No additional information available.</p>';
        }
    })
    .catch(error => {
        console.error('Error loading custom fields:', error);
        document.getElementById('customFieldsContent').innerHTML = 
            '<p>Error loading additional information.</p>';
    });
}

// Function to display custom fields in the modal
function displayCustomFields(fields) {
    const container = document.getElementById('customFieldsContent');
    
    if (!fields || Object.keys(fields).length === 0) {
        container.innerHTML = '<p>No additional information available.</p>';
        return;
    }

    let html = '';
    for (const [fieldName, fieldValue] of Object.entries(fields)) {
        if (fieldValue) { // Only show fields with values
            // Get the field label from your field definition
            const fieldLabel = fieldName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            html += `
                <div class="info-row">
                    <span class="info-label">${fieldLabel}:</span>
                    <span class="info-value">${fieldValue}</span>
                </div>
            `;
        }
    }

    if (html === '') {
        html = '<p>No additional information available.</p>';
    }
    
    container.innerHTML = html;
}

// Initialize case button event listener
document.addEventListener('DOMContentLoaded', function() {
    const caseBtn = document.getElementById('caseActionBtn');
    if (caseBtn) {
        caseBtn.addEventListener('click', function() {
            addCaseForChild();
        });
    }
});

function updateCaseButton(child) {
    const caseBtn = document.querySelector('.case-btn');
    if (!caseBtn) return;
    
    if (child.linked_case_id) {
        // Child already has a case
        caseBtn.innerHTML = 'View Case';
        caseBtn.onclick = function() { 
            viewExistingCase(child.linked_case_id); 
        };
        caseBtn.style.background = '#17a2b8';
    } else {
        // Child doesn't have a case
        caseBtn.innerHTML = 'Add Case';
        caseBtn.onclick = function() { 
            addCaseForChild(); 
        };
        caseBtn.style.background = '#28a745';
    }
    
    // Re-enable the button if user has permission
    if (canCreate) {
        caseBtn.disabled = false;
        caseBtn.style.opacity = '1';
        caseBtn.style.cursor = 'pointer';
    }
}

function enableEditMode() {
    if (!canEdit) {
        showNotification('Permission denied - You cannot edit children', 'error');
        return;
    }
    
    if (!currentChildData) {
        showNotification('Error: No child data available', 'error');
        return;
    }
    
    if (isEditMode) {
        // Already in edit mode, save changes
        saveChildChanges();
    } else {
        // Switch to edit mode
        isEditMode = true;
        document.querySelector('.edit-btn').textContent = 'Save';
        document.querySelector('.case-btn').style.display = 'none';
        
        // Create edit forms
        createEditForms();
    }
}

function createEditForms() {
    // Store the current active tab before switching to edit mode
    const activeTab = document.querySelector('.tab-pane.active').id;
    
    // Basic Info Tab Edit Form
    const basicTab = document.getElementById('basicTab');
    basicTab.innerHTML = `
        <div class="edit-form">
            <div class="form-group">
                <label class="form-label">Age *</label>
                <input type="number" id="editAge" value="${currentChildData.age || ''}" class="form-input" min="0" max="18" required>
            </div>
            <div class="form-group">
                <label class="form-label">Gender *</label>
                <select id="editGender" class="form-select" required>
                    <option value="Male" ${currentChildData.gender === 'Male' ? 'selected' : ''}>Male</option>
                    <option value="Female" ${currentChildData.gender === 'Female' ? 'selected' : ''}>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Status *</label>
                <select id="editStatus" class="form-select" required>
                    <option value="In Care" ${currentChildData.status === 'In Care' ? 'selected' : ''}>In Care</option>
                    <option value="Adoptable" ${currentChildData.status === 'Adoptable' ? 'selected' : ''}>Adoptable</option>
                    <option value="Adopted" ${currentChildData.status === 'Adopted' ? 'selected' : ''}>Adopted</option>
                    <option value="Reintegrated" ${currentChildData.status === 'Reintegrated' ? 'selected' : ''}>Reintegrated</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Date of Birth</label>
                <input type="date" id="editBirthDate" value="${currentChildData.date_of_birth || ''}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Entry Date</label>
                <input type="date" id="editEntryDate" value="${currentChildData.entry_date || ''}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Address</label>
                <textarea id="editAddress" class="form-textarea">${currentChildData.address || ''}</textarea>
            </div>
        </div>
    `;
    
    // Health Tab Edit Form
    const healthTab = document.getElementById('healthTab');
    healthTab.innerHTML = `
        <div class="edit-form">
            <div class="form-group">
                <label class="form-label">Health Status</label>
                <input type="text" id="editHealthStatus" value="${currentChildData.health_status || ''}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Allergies</label>
                <textarea id="editAllergies" class="form-textarea">${currentChildData.allergies || ''}</textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Emergency Contact</label>
                <input type="text" id="editEmergencyContact" value="${currentChildData.emergency_contact || ''}" class="form-input">
            </div>
            <div class="form-group">
                <label class="form-label">Problem Description</label>
                <textarea id="editProblemDescription" class="form-textarea">${currentChildData.problem_description || ''}</textarea>
            </div>
        </div>
    `;
    
    // Educational Tab Edit Form
    const educationalTab = document.getElementById('educationalTab');
    educationalTab.innerHTML = `
        <div class="edit-form">
            <div class="form-group">
                <label class="form-label">Additional Notes</label>
                <textarea id="editNotes" class="form-textarea">${currentChildData.notes || ''}</textarea>
            </div>
        </div>
    `;
    
    // Family Tab Edit Form
    const familyTab = document.getElementById('familyTab');
    familyTab.innerHTML = `
        <div class="edit-form">
            <div class="info-section">
                <h4>Identifying Information</h4>
                <div class="form-group">
                    <label class="form-label">Civil Status</label>
                    <input type="text" id="editCivilStatus" value="${currentChildData.civil_status || ''}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Birth Place</label>
                    <input type="text" id="editBirthPlace" value="${currentChildData.birth_place || ''}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Educational Attainment</label>
                    <input type="text" id="editEducationalAttainment" value="${currentChildData.educational_attainment || ''}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Occupation</label>
                    <input type="text" id="editOccupation" value="${currentChildData.occupation || ''}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Income</label>
                    <input type="text" id="editMonthlyIncome" value="${currentChildData.monthly_income || ''}" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Religion</label>
                    <input type="text" id="editReligion" value="${currentChildData.religion || ''}" class="form-input">
                </div>
            </div>
            
            <div class="info-section" style="margin-top: 20px;">
                <h4>Problem Presented</h4>
                <div class="form-group">
                    <textarea id="editProblemPresented" class="form-textarea">${currentChildData.problem_presented || ''}</textarea>
                </div>
            </div>
            
            <div class="info-section" style="margin-top: 20px;">
                <h4>Assessment & Recommendation</h4>
                <div class="form-group">
                    <textarea id="editAssessmentRecommendation" class="form-textarea">${currentChildData.assessment_recommendation || ''}</textarea>
                </div>
            </div>
        </div>
    `;
    
    // Restore the active tab
    setTimeout(() => {
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        
        const tabName = activeTab.replace('Tab', '');
        document.querySelector(`[onclick="switchTab('${tabName}')"]`).classList.add('active');
        document.getElementById(activeTab).classList.add('active');
    }, 100);
}

function saveChildChanges() {
    if (!currentChildId || isSaving) return;
    
    // Collect all edited data
    const updatedData = {
        age: document.getElementById('editAge').value,
        gender: document.getElementById('editGender').value,
        status: document.getElementById('editStatus').value,
        date_of_birth: document.getElementById('editBirthDate').value,
        entry_date: document.getElementById('editEntryDate').value,
        address: document.getElementById('editAddress').value,
        health_status: document.getElementById('editHealthStatus').value,
        allergies: document.getElementById('editAllergies').value,
        emergency_contact: document.getElementById('editEmergencyContact').value,
        problem_description: document.getElementById('editProblemDescription').value,
        notes: document.getElementById('editNotes').value,
        civil_status: document.getElementById('editCivilStatus')?.value || '',
        birth_place: document.getElementById('editBirthPlace')?.value || '',
        educational_attainment: document.getElementById('editEducationalAttainment')?.value || '',
        occupation: document.getElementById('editOccupation')?.value || '',
        monthly_income: document.getElementById('editMonthlyIncome')?.value || '',
        religion: document.getElementById('editReligion')?.value || '',
        problem_presented: document.getElementById('editProblemPresented')?.value || '',
        assessment_recommendation: document.getElementById('editAssessmentRecommendation')?.value || ''
    };
    
    // Validate required fields
    if (!updatedData.age || updatedData.age < 0 || updatedData.age > 18) {
        showNotification('Please enter a valid age between 0 and 18', 'error');
        return;
    }
    
    if (!updatedData.status) {
        showNotification('Please select a status', 'error');
        return;
    }
    
    // Set saving flag
    isSaving = true;
    
    // Show loading state
    const editBtn = document.querySelector('.edit-btn');
    editBtn.textContent = 'Saving...';
    editBtn.disabled = true;
    
    // Send update request
    const formData = new FormData();
    formData.append('action', 'update_child');
    formData.append('child_id', currentChildId);
    
    Object.keys(updatedData).forEach(key => {
        formData.append(key, updatedData[key]);
    });
    
    fetch('ajax-update-child.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        isSaving = false;
        
        if (data.success) {
            showNotification('Child information updated successfully!');
            
            // SIMPLE SOLUTION: Close modal and refresh the page after a short delay
            closeModal();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            
        } else {
            showNotification('Error: ' + data.message, 'error');
            const editBtn = document.querySelector('.edit-btn');
            if (editBtn) {
                editBtn.textContent = 'Save';
                editBtn.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        isSaving = false;
        showNotification('Error updating child information', 'error');
        const editBtn = document.querySelector('.edit-btn');
        if (editBtn) {
            editBtn.textContent = 'Save';
            editBtn.disabled = false;
        }
    });
}

function addCaseForChild() {
    console.log('Add Case button clicked');
    console.log('currentChildData:', currentChildData);
    
    if (!canCreate) {
        showNotification('Permission denied - You cannot create cases', 'error');
        return;
    }
    
   
    // Check if child already has a case
    if (currentChildData.linked_case_id) {
        showNotification('This child already has a linked case', 'info');
        viewExistingCase(currentChildData.linked_case_id);
        return;
    }
    
    // Use the child_id directly
    const childId = currentChildData.child_id;
    
    console.log('Redirecting with child ID:', childId);
    
    // Close the modal first
    closeModal();
    
    // Then redirect to unified registration
    setTimeout(() => {
        window.location.href = 'unified-registration.php?child_id=' + encodeURIComponent(childId) + '&source=child_management';
    }, 100);
}

function viewExistingCase(caseId) {
    console.log('Viewing existing case:', caseId);
    // Close the modal and redirect to case details
    closeModal();
    setTimeout(() => {
        window.location.href = 'case-details.php?case_id=' + encodeURIComponent(caseId);
    }, 300);
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    
    const childModal = document.getElementById('childModal');
    if (!childModal) return;
    
    // Check if modal is actually open and we need to refresh
    const wasModalOpen = childModal.classList.contains('active');
    const wasInEditMode = isEditMode;
    
    // Close the modal
    childModal.classList.remove('active');
    
    // Refresh page if modal was open AND we were in edit mode
    if (wasModalOpen && wasInEditMode) {
        setTimeout(() => {
            window.location.reload();
        }, 300);
    }
    
    // Reset states
    isEditMode = false;
    isSaving = false;
    currentChildId = null;
    currentChildData = null;
}

function switchTab(tabName) {
    // Remove active class from all tabs and panes
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    
    // Add active class to clicked tab and corresponding pane
    event.target.classList.add('active');
    document.getElementById(tabName + 'Tab').classList.add('active');
}

function loadChildDetails(childId) {
    console.log('Loading details for child:', childId);
    
    // Show loading state with null checks
    const modalLoading = document.getElementById('modalLoading');
    const modalContent = document.getElementById('modalContent');
    
    if (modalLoading) modalLoading.style.display = 'block';
    if (modalContent) modalContent.style.display = 'none';
    
    // Load basic child details
    fetch('ajax-child-details.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_child_details&child_id=' + encodeURIComponent(childId)
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading and show content with null checks
        if (modalLoading) modalLoading.style.display = 'none';
        if (modalContent) modalContent.style.display = 'block';
        
        if (data.success) {
            // CRITICAL: Ensure we're in view mode when loading data
            resetModalState();
            populateModal(data.child);
        } else {
            showNotification('Error: ' + data.message, 'error');
            closeModal();
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        // Hide loading with null check
        if (modalLoading) modalLoading.style.display = 'none';
        showNotification('Error loading child details', 'error');
        closeModal();
    });
}

// Auto-open child modal if view_child parameter is present
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const viewChildId = urlParams.get('view_child');
    
    if (viewChildId && canEdit) {
        // Small delay to ensure page is fully loaded
        setTimeout(() => {
            showChildDetails(viewChildId);
        }, 500);
    }
});

// Make functions globally available
window.showChildDetails = showChildDetails;
window.switchTab = switchTab;
window.enableEditMode = enableEditMode;
window.addCaseForChild = addCaseForChild;
window.closeModal = closeModal;
window.showNotification = showNotification;
</script>

<?php require_once 'includes/footer.php'; ?>