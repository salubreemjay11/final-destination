<?php
$pageTitle = 'Case Management - Orphanfare';
require_once 'includes/header.php';

// Check if user has view permission for case management
if (!$permissionManager->hasPermission('case_management', 'view')) {
    header('Location: access-denied.php');
    exit();
}

// Helper functions
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'urgent': return 'status-urgent';
        case 'high': return 'status-progress';
        case 'medium': return 'status-mild';
        case 'low': return 'status-common';
        case 'open': return 'status-active';
        case 'closed': return 'status-approved';
        case 'court action pending': return 'status-warning';
        case 'under investigation': return 'status-progress';
        default: return 'status-common';
    }
}

function getSocialWorkerName($socialWorkerId) {
    $socialWorkers = [
        'maria-santos' => 'Maria Santos',
        'juan-cruz' => 'Juan Cruz', 
        'lisa-gonzalez' => 'Lisa Gonzalez',
        'carlos-reyes' => 'Carlos Reyes'
    ];
    return $socialWorkers[$socialWorkerId] ?? 'Not assigned';
}

// Get legal action types for filtering
function getLegalActionTypes($pdo) {
    $types = [];
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT type FROM legal_actions WHERE type IS NOT NULL AND type != '' ORDER BY type");
        $stmt->execute();
        $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error fetching legal action types: " . $e->getMessage());
    }
    return $types;
}

// Get social service types for filtering
function getSocialServiceTypes($pdo) {
    $types = [];
    try {
        $stmt = $pdo->prepare("SELECT DISTINCT type FROM social_services WHERE type IS NOT NULL AND type != '' ORDER BY type");
        $stmt->execute();
        $types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) {
        error_log("Error fetching social service types: " . $e->getMessage());
    }
    return $types;
}

// Get upcoming trials for today and future
function getUpcomingTrials($pdo) {
    $today = date('Y-m-d');
    $trials = [];
    
    try {
        $stmt = $pdo->prepare("
            SELECT la.*, c.case_id, c.child_name, c.case_type 
            FROM legal_actions la 
            LEFT JOIN cases c ON la.case_id = c.case_id 
            WHERE la.type LIKE '%court%' OR la.type LIKE '%trial%' OR la.type LIKE '%hearing%'
            AND la.date >= ? 
            ORDER BY la.date ASC
        ");
        $stmt->execute([$today]);
        $trials = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("Error fetching upcoming trials: " . $e->getMessage());
    }
    
    return $trials;
}

// Handle AJAX requests - WITH PERMISSION CHECKS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    // Check permission for close action
    if ($_POST['action'] === 'close_case') {
        if (!$permissionManager->hasPermission('case_management', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied - You cannot close cases']);
            exit();
        }
        
        $caseId = $_POST['case_id'];
        try {
            $stmt = $pdo->prepare("UPDATE cases SET status = 'Closed', closed_date = NOW() WHERE case_id = ?");
            $result = $stmt->execute([$caseId]);
            
            if ($result) {
                logActivity($currentUser['id'], 'Case Closed', 'cases', $caseId);
                echo json_encode(['success' => true, 'message' => 'Case closed successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to close case']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
    
    // Check permission for escalate action
    if ($_POST['action'] === 'escalate_case') {
        if (!$permissionManager->hasPermission('case_management', 'edit')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied - You cannot escalate cases']);
            exit();
        }
        
        $caseId = $_POST['case_id'];
        try {
            $stmt = $pdo->prepare("UPDATE cases SET status = 'Court Action Pending', escalated_date = NOW() WHERE case_id = ?");
            $result = $stmt->execute([$caseId]);
            
            if ($result) {
                logActivity($currentUser['id'], 'Case Escalated', 'cases', $caseId);
                echo json_encode(['success' => true, 'message' => 'Case escalated to court action']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to escalate case']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
}

// Get filter types for dropdowns
$legalActionTypes = getLegalActionTypes($pdo);
$socialServiceTypes = getSocialServiceTypes($pdo);

// Get cases with search and sort - ENHANCED VERSION
$whereClause = "WHERE 1=1";
$params = [];
$orderBy = "ORDER BY created_date DESC";

// Enhanced search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $whereClause .= " AND (c.case_id LIKE ? OR c.child_name LIKE ? OR c.case_type LIKE ? OR c.description LIKE ?)";
    $params = array_merge($params, [$search, $search, $search, $search]);
}

// Enhanced filtering
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $whereClause .= " AND c.status = ?";
    $params[] = $_GET['status'];
}

if (isset($_GET['case_type']) && !empty($_GET['case_type'])) {
    $whereClause .= " AND c.case_type = ?";
    $params[] = $_GET['case_type'];
}

// Legal Action Type Filter
if (isset($_GET['legal_action_type']) && !empty($_GET['legal_action_type'])) {
    $whereClause .= " AND EXISTS (SELECT 1 FROM legal_actions la WHERE la.case_id = c.case_id AND la.type = ?)";
    $params[] = $_GET['legal_action_type'];
}

// Social Service Type Filter
if (isset($_GET['social_service_type']) && !empty($_GET['social_service_type'])) {
    $whereClause .= " AND EXISTS (SELECT 1 FROM social_services ss WHERE ss.case_id = c.case_id AND ss.type = ?)";
    $params[] = $_GET['social_service_type'];
}

// Specific legal action filters
if (isset($_GET['has_legal_action']) && $_GET['has_legal_action'] === 'yes') {
    $whereClause .= " AND EXISTS (SELECT 1 FROM legal_actions la WHERE la.case_id = c.case_id)";
}

if (isset($_GET['has_social_services']) && $_GET['has_social_services'] === 'yes') {
    $whereClause .= " AND EXISTS (SELECT 1 FROM social_services ss WHERE ss.case_id = c.case_id)";
}

if (isset($_GET['upcoming_trials']) && $_GET['upcoming_trials'] === 'yes') {
    $today = date('Y-m-d');
    $whereClause .= " AND EXISTS (
        SELECT 1 FROM legal_actions la 
        WHERE la.case_id = c.case_id 
        AND (la.type LIKE '%court%' OR la.type LIKE '%trial%' OR la.type LIKE '%hearing%')
        AND la.date >= ?
    )";
    $params[] = $today;
}

if (isset($_GET['sort']) && !empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'date-desc':
            $orderBy = "ORDER BY c.created_date DESC";
            break;
        case 'date-asc':
            $orderBy = "ORDER BY c.created_date ASC";
            break;
        case 'status':
            $orderBy = "ORDER BY c.status ASC";
            break;
        case 'urgency':
            $orderBy = "ORDER BY FIELD(c.priority, 'urgent', 'high', 'medium', 'low') ASC";
            break;
        case 'name':
            $orderBy = "ORDER BY c.child_name ASC";
            break;
        case 'case-type':
            $orderBy = "ORDER BY c.case_type ASC";
            break;
        case 'next-court-date':
            $orderBy = "ORDER BY next_court_date ASC";
            break;
    }
}

$cases = [];
$legalActionsCount = [];
$socialServicesCount = [];
$upcomingCourtDates = [];
$caseLegalActions = []; // Store legal actions for each case
$caseSocialServices = []; // Store social services for each case

try {
    // Get cases with enhanced data
    $query = "
        SELECT c.*, 
               (SELECT COUNT(*) FROM legal_actions la WHERE la.case_id = c.case_id) as legal_actions_count,
               (SELECT COUNT(*) FROM social_services ss WHERE ss.case_id = c.case_id) as social_services_count,
               (SELECT MIN(la.date) FROM legal_actions la 
                WHERE la.case_id = c.case_id 
                AND (la.type LIKE '%court%' OR la.type LIKE '%trial%' OR la.type LIKE '%hearing%')
                AND la.date >= CURDATE()) as next_court_date
        FROM cases c 
        $whereClause 
        $orderBy
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $cases = $stmt->fetchAll();
    
    // Get legal actions and social services for each case
    foreach ($cases as $case) {
        $caseId = $case['case_id'];
        
        // Get legal actions for this case
        $stmt = $pdo->prepare("SELECT type, date, status FROM legal_actions WHERE case_id = ? ORDER BY date DESC");
        $stmt->execute([$caseId]);
        $caseLegalActions[$caseId] = $stmt->fetchAll();
        
        // Get social services for this case
        $stmt = $pdo->prepare("SELECT type, date_started, status FROM social_services WHERE case_id = ? ORDER BY date_started DESC");
        $stmt->execute([$caseId]);
        $caseSocialServices[$caseId] = $stmt->fetchAll();
        
        // Get counts for display
        $legalActionsCount[$caseId] = $case['legal_actions_count'];
        $socialServicesCount[$caseId] = $case['social_services_count'];
        $upcomingCourtDates[$caseId] = $case['next_court_date'];
    }
    
} catch (Exception $e) {
    error_log("Database error in case management: " . $e->getMessage());
}

// Get upcoming trials for the sidebar
$upcomingTrials = getUpcomingTrials($pdo);

// Check permissions for display
$canCreate = $permissionManager->hasPermission('case_management', 'create');
$canEdit = $permissionManager->hasPermission('case_management', 'edit');
$canDelete = $permissionManager->hasPermission('case_management', 'delete');
$canView = $permissionManager->hasPermission('case_management', 'view');
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Case Management
            <?php if (!$canEdit): ?>
                <span class="status-badge status-mild" style="font-size: 14px; margin-left: 10px;">Read-Only</span>
            <?php endif; ?>
        </h1>
    </div>
                <!-- Sidebar with Upcoming Trials -->
                <div class="case-sidebar">
                        <!-- Upcoming Trials Section -->
                        <div class="sidebar-section">
                            <h3 class="sidebar-title">
                                <div class="icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3" viewBox="0 0 16 16">
                                    <path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>
                                    <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/>
                                    </svg>
                                </div>
                                Upcoming Court Dates
                                <span class="sidebar-badge"><?php echo count($upcomingTrials); ?></span>
                            </h3>
                            
                                <?php if (!empty($upcomingTrials)): ?>
                                    <div class="upcoming-trials-list">
                                        <?php foreach ($upcomingTrials as $trial): ?>
                                            <div class="trial-item">
                                                <div class="trial-date">
                                                    <?php echo date('M j', strtotime($trial['date'])); ?>
                                                </div>
                                                <div class="trial-info">
                                                    <div class="trial-case">Case: <?php echo htmlspecialchars($trial['case_id']); ?></div>
                                                    <div class="trial-type"><?php echo htmlspecialchars($trial['type']); ?></div>
                                                    <div class="trial-child">Child: <?php echo htmlspecialchars($trial['child_name'] ?? 'Unknown'); ?></div>
                                                </div>
                                                <div class="trial-actions">
                                                    <button class="btn-small" onclick="viewCase('<?php echo htmlspecialchars($trial['case_id']); ?>')">View</button>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="no-upcoming-trials">
                                        No upcoming court dates
                                    </div>
                                <?php endif; ?>     
                        </div>

                        <!-- Quick Stats -->
                        <div class="sidebar-section">
                            <h3 class="sidebar-title">📊 Quick Stats</h3>
                            <div class="stats-grid">
                                <div class="stat-item">
                                    <div class="stat-number"><?php echo count($cases); ?></div>
                                    <div class="stat-label">Total Cases</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <?php 
                                        $courtCases = array_filter($cases, function($case) use ($upcomingCourtDates) {
                                            return !empty($upcomingCourtDates[$case['case_id']]);
                                        });
                                        echo count($courtCases);
                                        ?>
                                    </div>
                                    <div class="stat-label">With Court Dates</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">
                                        <?php 
                                        $urgentCases = array_filter($cases, function($case) {
                                            return ($case['priority'] ?? '') === 'urgent';
                                        });
                                        echo count($urgentCases);
                                        ?>
                                    </div>
                                    <div class="stat-label">Urgent Priority</div>
                                </div>
                            </div>
                        </div>

    <!-- Show read-only banner if no edit permission -->
    <?php if (!$canEdit): ?>
    <div class="read-only-banner">
        <strong>🔒 Read-Only Mode:</strong> You have view-only access to case management. You cannot perform any actions.
    </div>
    <?php endif; ?>

    <div class="case-management-layout">
        <!-- Main Content -->
        <div class="case-main-content">
            <!-- Search and Advanced Filters -->
            <div class="search-container">
                <form method="GET" class="search-form">
                    <div class="search-row">
                        <input type="text" class="search-input" name="search" placeholder="Search cases..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        <select class="filter-select" name="sort" onchange="this.form.submit()">
                            <option value="">Sort by...</option>
                            <option value="date-desc" <?php echo ($_GET['sort'] ?? '') === 'date-desc' ? 'selected' : ''; ?>>Date (Newest)</option>
                            <option value="date-asc" <?php echo ($_GET['sort'] ?? '') === 'date-asc' ? 'selected' : ''; ?>>Date (Oldest)</option>
                            <option value="status" <?php echo ($_GET['sort'] ?? '') === 'status' ? 'selected' : ''; ?>>Status</option>
                            <option value="urgency" <?php echo ($_GET['sort'] ?? '') === 'urgency' ? 'selected' : ''; ?>>Urgency</option>
                            <option value="name" <?php echo ($_GET['sort'] ?? '') === 'name' ? 'selected' : ''; ?>>Child Name</option>
                            <option value="case-type" <?php echo ($_GET['sort'] ?? '') === 'case-type' ? 'selected' : ''; ?>>Case Type</option>
                            <option value="next-court-date" <?php echo ($_GET['sort'] ?? '') === 'next-court-date' ? 'selected' : ''; ?>>Next Court Date</option>
                        </select>
                        <button type="submit" class="search-btn">Search</button>
                        <?php if (!empty($_GET['search']) || !empty($_GET['sort']) || !empty($_GET['status']) || !empty($_GET['case_type']) || !empty($_GET['legal_action_type']) || !empty($_GET['social_service_type'])): ?>
                        <a href="case-management.php" class="btn btn-clear">Clear All</a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="advanced-filters">
                        <div class="filter-group">
                            <label>Status:</label>
                            <select name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <option value="Open" <?php echo ($_GET['status'] ?? '') === 'Open' ? 'selected' : ''; ?>>Open</option>
                                <option value="Under Investigation" <?php echo ($_GET['status'] ?? '') === 'Under Investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                                <option value="Court Action Pending" <?php echo ($_GET['status'] ?? '') === 'Court Action Pending' ? 'selected' : ''; ?>>Court Action Pending</option>
                                <option value="Closed" <?php echo ($_GET['status'] ?? '') === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Case Type:</label>
                            <select name="case_type" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="Physical Abuse" <?php echo ($_GET['case_type'] ?? '') === 'Physical Abuse' ? 'selected' : ''; ?>>Physical Abuse</option>
                                <option value="Sexual Abuse" <?php echo ($_GET['case_type'] ?? '') === 'Sexual Abuse' ? 'selected' : ''; ?>>Sexual Abuse</option>
                                <option value="Neglect" <?php echo ($_GET['case_type'] ?? '') === 'Neglect' ? 'selected' : ''; ?>>Neglect</option>
                                <option value="Abandonment" <?php echo ($_GET['case_type'] ?? '') === 'Abandonment' ? 'selected' : ''; ?>>Abandonment</option>
                                <option value="Exploitation" <?php echo ($_GET['case_type'] ?? '') === 'Exploitation' ? 'selected' : ''; ?>>Exploitation</option>
                            </select>
                        </div>
                        
                        <!-- Legal Action Type Filter -->
                        <div class="filter-group">
                            <label>Legal Action Type:</label>
                            <select name="legal_action_type" onchange="this.form.submit()">
                                <option value="">All Legal Actions</option>
                                <?php foreach ($legalActionTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" 
                                        <?php echo ($_GET['legal_action_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Social Service Type Filter -->
                        <div class="filter-group">
                            <label>Service Type:</label>
                            <select name="social_service_type" onchange="this.form.submit()">
                                <option value="">All Services</option>
                                <?php foreach ($socialServiceTypes as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" 
                                        <?php echo ($_GET['social_service_type'] ?? '') === $type ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <label>Special Filters:</label>
                            <select name="has_legal_action" onchange="this.form.submit()">
                                <option value="">All Cases</option>
                                <option value="yes" <?php echo ($_GET['has_legal_action'] ?? '') === 'yes' ? 'selected' : ''; ?>>With Legal Actions</option>
                            </select>
                            
                            <select name="has_social_services" onchange="this.form.submit()">
                                <option value="">All Cases</option>
                                <option value="yes" <?php echo ($_GET['has_social_services'] ?? '') === 'yes' ? 'selected' : ''; ?>>With Social Services</option>
                            </select>
                            
                            <select name="upcoming_trials" onchange="this.form.submit()">
                                <option value="">All Cases</option>
                                <option value="yes" <?php echo ($_GET['upcoming_trials'] ?? '') === 'yes' ? 'selected' : ''; ?>>With Upcoming Trials</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Cases Table -->
            <div class="registry-section">
                <div class="registry-header">
                    <h2 class="registry-title">Case Registry</h2>
                    <span class="total-count">Total: <?php echo count($cases); ?> cases</span>
                </div>
                
                <table class="foster-table">
                    <thead>
                        <tr>
                            <th>Case ID</th>
                            <th>Case Type</th>
                            <th>Description</th>
                            <th>Social Worker</th>
                            <th>Legal Actions</th>
                            <th>Social Services</th>
                            <th>Next Court Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="caseTableBody">
                        <?php foreach ($cases as $case): 
                            $caseId = $case['case_id'];
                            $legalCount = $legalActionsCount[$caseId] ?? 0;
                            $socialCount = $socialServicesCount[$caseId] ?? 0;
                            $nextCourtDate = $upcomingCourtDates[$caseId] ?? null;
                            $legalActions = $caseLegalActions[$caseId] ?? [];
                            $socialServices = $caseSocialServices[$caseId] ?? [];
                        ?>
                        <tr <?php if ($canEdit): ?>onclick="viewCaseDetails('<?php echo htmlspecialchars($caseId); ?>')" style="cursor: pointer;"<?php else: ?>style="cursor: not-allowed;"<?php endif; ?>>
                            <td class="foster-id"><?php echo htmlspecialchars($caseId); ?>
                                <?php if ($case['linked_child_id']): ?>
                                    <br><small style="color: #0E7490;">Child: <?php echo htmlspecialchars($case['linked_child_id']); ?></small>
                                <?php endif; ?>
                            </td>
                            
                            <td><?php echo htmlspecialchars(ucfirst($case['case_type'] ?? 'Unknown')); ?></td>
                            <td class="notes-cell"><?php echo htmlspecialchars($case['description'] ? substr($case['description'], 0, 50) . '...' : 'No description'); ?></td>
                            <td><?php echo htmlspecialchars(getSocialWorkerName($case['social_worker'] ?? '')); ?></td>
                            
                            <!-- Legal Actions Column with Details -->
                            <td>
                                <?php if ($legalCount > 0): ?>
                                    <div class="actions-dropdown">
                                        <span class="count-badge legal-count" onclick="event.stopPropagation(); toggleActions(this)">
                                            <?php echo $legalCount; ?> legal ▼
                                        </span>
                                        <div class="actions-list">
                                            <?php foreach ($legalActions as $action): ?>
                                                <div class="action-item">
                                                    <strong><?php echo htmlspecialchars($action['type']); ?></strong>
                                                    <div class="action-date"><?php echo date('M j, Y', strtotime($action['date'])); ?></div>
                                                    <div class="action-status"><?php echo htmlspecialchars($action['status']); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="count-badge no-data">None</span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Social Services Column with Details -->
                            <td>
                                <?php if ($socialCount > 0): ?>
                                    <div class="services-dropdown">
                                        <span class="count-badge social-count" onclick="event.stopPropagation(); toggleServices(this)">
                                            <?php echo $socialCount; ?> services ▼
                                        </span>
                                        <div class="services-list">
                                            <?php foreach ($socialServices as $service): ?>
                                                <div class="service-item">
                                                    <strong><?php echo htmlspecialchars($service['type']); ?></strong>
                                                    <div class="service-date">Started: <?php echo date('M j, Y', strtotime($service['date_started'])); ?></div>
                                                    <div class="service-status"><?php echo htmlspecialchars($service['status']); ?></div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="count-badge no-data">None</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php if ($nextCourtDate): ?>
                                    <span class="court-date upcoming"><?php echo date('M j, Y', strtotime($nextCourtDate)); ?></span>
                                <?php else: ?>
                                    <span class="court-date none">No trial</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <?php
                                $statusText = $case['status'] ?? 'Unknown';
                                $statusClass = getStatusBadgeClass($case['status'] ?? '');
                                if (($case['status'] ?? '') === 'Open' && ($case['priority'] ?? '')) {
                                    $statusText = ucfirst($case['priority']);
                                    $statusClass = getStatusBadgeClass($case['priority']);
                                }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($statusText); ?></span>
                            </td>
                            
                            <td onclick="event.stopPropagation();">
                                <?php if ($canEdit): ?>
                                    <button class="view-btn" onclick="viewCase('<?php echo htmlspecialchars($caseId); ?>')">View</button>
                                <?php else: ?>
                                    <button class="view-btn" disabled title="Read-only mode - No actions allowed">View</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($cases)): ?>
                        <tr>
                            <td colspan="9" class="no-cases-message">
                                <?php if (!empty($_GET['search']) || !empty($_GET['legal_action_type']) || !empty($_GET['social_service_type'])): ?>
                                    No cases match your current filters.
                                    <?php if (!empty($_GET['legal_action_type'])): ?>
                                        <br><small>Legal Action Type: "<?php echo htmlspecialchars($_GET['legal_action_type']); ?>"</small>
                                    <?php endif; ?>
                                    <?php if (!empty($_GET['social_service_type'])): ?>
                                        <br><small>Service Type: "<?php echo htmlspecialchars($_GET['social_service_type']); ?>"</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    No cases found. 
                                    <?php if ($canCreate): ?>
                                        <a href="case-registration.php">Create your first case</a>
                                    <?php else: ?>
                                        No cases available for viewing.
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>


            <!-- Active Filters Summary -->
            <?php if (!empty($_GET['legal_action_type']) || !empty($_GET['social_service_type'])): ?>
            <div class="sidebar-section">
                <h3 class="sidebar-title">🔍 Active Filters</h3>
                <div class="active-filters">
                    <?php if (!empty($_GET['legal_action_type'])): ?>
                        <div class="active-filter">
                            <span class="filter-label">Legal Action:</span>
                            <span class="filter-value"><?php echo htmlspecialchars($_GET['legal_action_type']); ?></span>
                            <a href="<?php echo removeFilter('legal_action_type'); ?>" class="remove-filter">×</a>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($_GET['social_service_type'])): ?>
                        <div class="active-filter">
                            <span class="filter-label">Service Type:</span>
                            <span class="filter-value"><?php echo htmlspecialchars($_GET['social_service_type']); ?></span>
                            <a href="<?php echo removeFilter('social_service_type'); ?>" class="remove-filter">×</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
/* ... (previous CSS remains the same) ... */

/* Actions and Services Dropdown Styles */
.actions-dropdown, .services-dropdown {
    position: relative;
    display: inline-block;
}

.actions-list, .services-list {
    display: none;
    position: absolute;
    background: #2a2a2a;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    padding: 8px;
    min-width: 250px;
    max-height: 300px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    left: 0;
    top: 100%;
}

.actions-dropdown:hover .actions-list,
.services-dropdown:hover .services-list {
    display: block;
}

.action-item, .service-item {
    padding: 8px;
    border-bottom: 1px solid #3a3a3a;
    font-size: 12px;
}

.action-item:last-child, .service-item:last-child {
    border-bottom: none;
}

.action-item strong, .service-item strong {
    color: #3b82f6;
    display: block;
    margin-bottom: 2px;
}

.action-date, .service-date {
    color: #888;
    font-size: 11px;
}

.action-status, .service-status {
    color: #28a745;
    font-size: 11px;
    font-weight: 500;
}

/* Active Filters Styles */
.active-filters {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.active-filter {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #333;
    padding: 8px 12px;
    border-radius: 6px;
    border-left: 3px solid #3b82f6;
}

.filter-label {
    color: #b8c5ff;
    font-size: 12px;
    font-weight: 500;
}

.filter-value {
    color: #ffffff;
    font-size: 12px;
    flex: 1;
    margin: 0 8px;
}

.remove-filter {
    color: #dc3545;
    text-decoration: none;
    font-weight: bold;
    font-size: 14px;
    padding: 2px 6px;
    border-radius: 50%;
}

.remove-filter:hover {
    background: #dc3545;
    color: white;
}

/* Make count badges clickable */
.count-badge {
    cursor: pointer;
    transition: background-color 0.2s;
}

.count-badge:hover {
    opacity: 0.9;
}

.case-management-layout {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
    align-items: start;
}

.case-main-content {
    min-width: 0; /* Prevent overflow */
}

.case-sidebar {
    top: 20px;
}

.case-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

/* Search and Filters */
.search-form {
    background: #2a2a2a;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.dark-theme .search-form {
    background: #2a2a2a;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.light-theme .search-form {
    background: linear-gradient(90deg, #ffffff 0%, #f8fafc 100%);
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);

    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.search-row {
    display: flex;
    gap: 12px;
    margin-bottom: 16px;
    flex-wrap: wrap;
}

.search-input {
    flex: 1;
    min-width: 200px;
    padding: 10px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
}

.filter-select {
    padding: 10px;
    border: 1px solid #3a3a3a;
    border-radius: 6px;
    background: #1a1a1a;
    color: white;
    min-width: 150px;
}

.search-btn, .btn-clear {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    font-size: 14px;
}

.search-btn {
    background: #3b82f6;
    color: white;
}

.btn-clear {
    background: #6c757d;
    color: white;
}

.advanced-filters {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    padding-top: 16px;
    border-top: 1px solid #3a3a3a;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.dark-theme .filter-group label {
    color: #b8c5ff;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    font-size: 15px;
}

.light-theme .filter-group label {
    color: #475569;
    font-size: 14px;
    font-weight: 500;
    white-space: nowrap;
    font-size: 15px;
}

.dark-theme .filter-group select {
    padding: 6px 10px;
    border: 1px solid #3a3a3a;
    border-radius: 4px;
    background: #1a1a1a;
    color: white;
    font-size: 13px;
}

.light-theme .filter-group select {
    padding: 6px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 4px;
    background: #ffffff;
    color: #334155;
    font-size: 13px;
}

/* Count Badges */
.count-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.legal-count {
    background: #e3f2fd;
    color: #1565c0;
}

.social-count {
    background: #e8f5e8;
    color: #2e7d32;
}

.no-data {
    background: #f5f5f5;
    color: #666;
}

/* Court Date Styles */
.court-date {
    font-size: 12px;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 4px;
}

.court-date.upcoming {
    background: #fff3cd;
    color: #856404;
}

.court-date.none {
    background: #f8f9fa;
    color: #6c757d;
    font-style: italic;
}

/* Sidebar Styles */
.dark-theme .sidebar-section {
    background: #2a2a2a;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}

.light-theme .sidebar-section {
    background: #ffffff;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 16px;
}

.dark-theme .sidebar-title {
    color: #ffffff;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
}

.icon {
    margin-right: 10px;
}

.light-theme .sidebar-title {
    color: #1e293b;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
}

.sidebar-badge {
    background: #3b82f6;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: bold;
    display: flex;
    position: absolute;
    right: 50px;
}

/* Upcoming Trials */
.upcoming-trials-list {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.dark-theme .trial-item {
    background: #333;
    border-radius: 6px;
    padding: 12px;
    border-left: 3px solid #3b82f6;
}

.light-theme .trial-item{
    background: rgba(63, 61, 61, 0.1);
    border-radius: 6px;
    padding: 12px;
    border-left: 3px solid #2d5f8d;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
   
}

.trial-date {
    background: #2d5f8d;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
    display: inline-block;
}

.trial-info {
    margin-bottom: 8px;
}

.trial-case {
    color: #b8c5ff;
    font-size: 13px;
    font-weight: 500;
}

.light-theme .trial-case {
    color: #2d5f8d;
    font-size: 15px;
    font-weight: 500;
}

.trial-type {
    color: #ffffff;
    font-size: 12px;
    margin: 2px 0;
}

.light-theme .trial-type {
    color: #475569;
    font-size: 14px;
    margin: 2px 0;
}

.trial-child {
    color: #888;
    font-size: 11px;
}

.light-theme .trial-child {
    color: #0E7490;
    font-size: 13px;
}

.trial-actions {
    display: flex;
    justify-content: flex-end;
}

.btn-small {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 14px;
    cursor: pointer;
}

.btn-small:hover {
    background-color: #2563eb;
}

.no-upcoming-trials {
    text-align: center;
    color: #888;
    font-style: italic;
    padding: 20px;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.dark-theme .stat-item {
    text-align: center;
    padding: 12px 8px;
    background: #333;
    border-radius: 6px;
}

.light-theme .stat-item {
    text-align: center;
    padding: 12px 8px;
    background: #ffffff;
    border-radius: 6px;
}

.light-theme .stat-item {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.light-theme .stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: #2d5f8d;
}

.light-theme .stat-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}

.stat-number {
    color: #2d5f8d;
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 4px;
}

.dark-theme .stat-label {
    color: #b8c5ff;
    font-size: 14px;
}

.light-theme .stat-label {
    color: #0E7490;
    font-size: 14px;
}

/* Existing table styles remain the same */
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

.dark-theme .foster-table th {
    background: #333;
    color: #b8c5ff;
    padding: 12px;
    text-align: left;
    font-weight: 600;
    border-bottom: 1px solid #3a3a3a;
}

.light-theme .foster-table th {
    color: #b8c5ff;
    padding: 12px;
    text-align: left;
    font-weight: bold;
    border-bottom: 1px solid #3a3a3a;
}

.foster-table td {
    padding: 12px;
    border-bottom: 1px solid #3a3a3a;
}

.foster-id {
    color: #3b82f6;
    font-weight: 600;
}

.notes-cell {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.view-btn {
    padding: 6px 12px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
}

.view-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.status-urgent { background: #f8d7da; color: #721c24; }
.status-progress { background: #fff3cd; color: #856404; }
.status-mild { background: #e2e3e5; color: #383d41; }
.status-common { background: #d1ecf1; color: #0c5460; }
.status-active { background: #d4edda; color: #155724; }
.status-approved { background: #d1ecf1; color: #0c5460; }
.status-warning { background: #f8d7da; color: #721c24; }

.read-only-banner {
    background-color: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
}

.no-cases-message {
    text-align: center;
    color: #888;
    padding: 40px;
}

.no-cases-message a {
    color: #3b82f6;
    text-decoration: none;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .case-management-layout {
        grid-template-columns: 1fr;
    }
    
    .case-sidebar {
        order: -1;
    }
}

@media (max-width: 768px) {
    .search-row, .advanced-filters {
        flex-direction: column;
    }
    
    .search-input, .filter-select {
        min-width: 100%;
    }
    
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
function viewCase(caseId) {
    window.location.href = 'case-info.php?case_id=' + encodeURIComponent(caseId);
}

function viewCaseDetails(caseId) {
    viewCase(caseId);
}

function toggleActions(element) {
    const dropdown = element.closest('.actions-dropdown');
    const list = dropdown.querySelector('.actions-list');
    list.style.display = list.style.display === 'block' ? 'none' : 'block';
}

function toggleServices(element) {
    const dropdown = element.closest('.services-dropdown');
    const list = dropdown.querySelector('.services-list');
    list.style.display = list.style.display === 'block' ? 'none' : 'block';
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.actions-dropdown')) {
        document.querySelectorAll('.actions-list').forEach(list => {
            list.style.display = 'none';
        });
    }
    if (!event.target.closest('.services-dropdown')) {
        document.querySelectorAll('.services-list').forEach(list => {
            list.style.display = 'none';
        });
    }
});

// Auto-refresh upcoming trials every 5 minutes
setInterval(function() {
    // This would typically make an AJAX call to refresh the sidebar
    // For now, we'll just reload the page
    // location.reload();
}, 300000); // 5 minutes
</script>

<?php 
// Helper function to remove specific filters
function removeFilter($filterName) {
    $params = $_GET;
    unset($params[$filterName]);
    return 'case-management.php?' . http_build_query($params);
}

require_once 'includes/footer.php'; 
?>