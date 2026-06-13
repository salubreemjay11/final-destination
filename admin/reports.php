<?php
$pageTitle = 'Reports - Orphanfare';
require_once 'includes/header.php';

// Check if user has view permission for reports
if (!$permissionManager->hasPermission('reports', 'view')) {
    header('Location: access-denied.php');
    exit();
}

// Check permissions for display
$canExport = $permissionManager->hasPermission('reports', 'edit');
$canPrint = $permissionManager->hasPermission('reports', 'edit');

// Get active tab from URL or default to 'child'
$activeTab = $_GET['active_tab'] ?? 'child';

$whereClause = "";
$params = [];

// Pagination settings
$recordsPerPage = 10;
$currentPage = $_GET['page'] ?? 1;
$offset = ($currentPage - 1) * $recordsPerPage;

// Calculate total pages for each report type
try {
    // For Child Management
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM children $whereClause");
    $stmt->execute($params);
    $totalChildren = $stmt->fetch()['total'];
    $totalChildPages = ceil($totalChildren / $recordsPerPage);
    
    // For Case Management  
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cases $whereClause");
    $stmt->execute($params);
    $totalCases = $stmt->fetch()['total'];
    $totalCasePages = ceil($totalCases / $recordsPerPage);
    
    // For Donations
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations $whereClause");
    $stmt->execute($params);
    $totalDonations = $stmt->fetch()['total'];
    $totalDonationPages = ceil($totalDonations / $recordsPerPage);
    
    
    
    // For Foster Parents
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM foster_parents $whereClause");
    $stmt->execute($params);
    $totalFosterFamilies = $stmt->fetch()['total'];
    $totalFosterPages = ceil($totalFosterFamilies / $recordsPerPage);
    
    // For Events
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM events WHERE is_active = 1 $whereClause");
    $stmt->execute($params);
    $totalEvents = $stmt->fetch()['total'];
    $totalEventPages = ceil($totalEvents / $recordsPerPage);
    
} catch (Exception $e) {
    $totalChildPages = 1;
    $totalCasePages = 1;
    $totalDonationPages = 1;
    $totalInventoryPages = 1;
    $totalFosterPages = 1;
    $totalEventPages = 1;
}

// Get child management statistics from database
try {
    // Children status breakdown
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM children 
        GROUP BY status
    ");
    $stmt->execute();
    $statusBreakdown = $stmt->fetchAll();
    
    // Successful adoptions this month
    $currentMonth = date('Y-m');
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM children 
        WHERE status = 'Adopted' 
        AND DATE_FORMAT(updated_at, '%Y-%m') = ?
    ");
    $stmt->execute([$currentMonth]);
    $monthlyAdoptions = $stmt->fetch()['count'] ?? 0;
    
    // Gender distribution
    $stmt = $pdo->prepare("
        SELECT gender, COUNT(*) as count 
        FROM children 
        GROUP BY gender
    ");
    $stmt->execute();
    $genderDistribution = $stmt->fetchAll();
    
    // Age distribution
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN age BETWEEN 0 AND 5 THEN '0-5 years'
                WHEN age BETWEEN 6 AND 12 THEN '6-12 years' 
                WHEN age BETWEEN 13 AND 18 THEN '13-18 years'
                ELSE '18+ years'
            END as age_group,
            COUNT(*) as count,
            ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM children)), 1) as percentage
        FROM children 
        GROUP BY age_group
        ORDER BY 
            CASE age_group
                WHEN '0-5 years' THEN 1
                WHEN '6-12 years' THEN 2
                WHEN '13-18 years' THEN 3
                ELSE 4
            END
    ");
    $stmt->execute();
    $ageDistribution = $stmt->fetchAll();
    
    // Recent children for the report table
    $stmt = $pdo->prepare("
        SELECT child_id, name, age, gender, status, entry_date, notes 
        FROM children 
        ORDER BY created_at DESC 
        LIMIT $offset, $recordsPerPage
    ");
    $stmt->execute();
    $recentChildren = $stmt->fetchAll();
    
    // Total children count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM children");
    $stmt->execute();
    $totalChildren = $stmt->fetch()['total'];
    
    // Calculate success rate (adoptions vs total processed)
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_processed,
            SUM(CASE WHEN status = 'Adopted' THEN 1 ELSE 0 END) as successful_adoptions
        FROM children 
        WHERE status IN ('Adopted', 'Reintegrated', 'In Care')
    ");
    $stmt->execute();
    $adoptionStats = $stmt->fetch();
    $successRate = $adoptionStats['total_processed'] > 0 ? 
        round(($adoptionStats['successful_adoptions'] / $adoptionStats['total_processed']) * 100) : 0;
        
} catch (Exception $e) {
    error_log("Reports page error: " . $e->getMessage());
    $statusBreakdown = [];
    $monthlyAdoptions = 0;
    $genderDistribution = [];
    $ageDistribution = [];
    $recentChildren = [];
    $totalChildren = 0;
    $successRate = 0;
}

// Get case management statistics from database - FIXED VERSION
try {
    // Entry reasons breakdown
    $stmt = $pdo->prepare("
        SELECT case_type, COUNT(*) as count,
               ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM cases)), 1) as percentage
        FROM cases 
        GROUP BY case_type
        ORDER BY count DESC
    ");
    $stmt->execute();
    $entryReasons = $stmt->fetchAll();
    
    // Case status distribution
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM cases 
        GROUP BY status
    ");
    $stmt->execute();
    $caseStatus = $stmt->fetchAll();
    
    // Cases by priority
    $stmt = $pdo->prepare("
        SELECT priority, COUNT(*) as count 
        FROM cases 
        WHERE priority IS NOT NULL 
        GROUP BY priority
        ORDER BY FIELD(priority, 'urgent', 'high', 'medium', 'low')
    ");
    $stmt->execute();
    $priorityCases = $stmt->fetchAll();
    
    // Recent cases for the report table - FIXED COLUMN NAMES
    $stmt = $pdo->prepare("
        SELECT c.case_id, c.child_name, c.social_worker, c.status, 
               c.created_date, c.priority, c.case_type
        FROM cases c 
        ORDER BY c.created_date DESC 
        LIMIT $offset, $recordsPerPage
    ");
    $stmt->execute();
    $recentCases = $stmt->fetchAll();
    
    // Total cases count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM cases");
    $stmt->execute();
    $totalCases = $stmt->fetch()['total'];
    
    // Get social worker names for display
    $socialWorkers = [
        'maria-santos' => 'Maria Santos',
        'juan-cruz' => 'Juan Cruz', 
        'lisa-gonzalez' => 'Lisa Gonzalez',
        'carlos-reyes' => 'Carlos Reyes'
    ];
    
} catch (Exception $e) {
    error_log("Case reports page error: " . $e->getMessage());
    $entryReasons = [];
    $caseStatus = [];
    $priorityCases = [];
    $recentCases = [];
    $totalCases = 0;
    $socialWorkers = [];
}

// Get donation statistics from database
try {
    // Donation types breakdown
    $stmt = $pdo->prepare("
        SELECT donation_type, COUNT(*) as count 
        FROM donations 
        GROUP BY donation_type
    ");
    $stmt->execute();
    $donationTypes = $stmt->fetchAll();
    
    // Donation sources breakdown
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN donor_email LIKE '%@corporation%' OR donor_name LIKE '%Inc%' OR donor_name LIKE '%Corp%' THEN 'Corporate Sponsors'
                WHEN donor_email LIKE '%@foundation%' OR donor_name LIKE '%Foundation%' THEN 'Grants'
                WHEN donor_name LIKE '%Event%' OR notes LIKE '%event%' THEN 'Fundraising Events'
                ELSE 'Individual Donors'
            END as source_type,
            COUNT(*) as count,
            ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM donations)), 1) as percentage
        FROM donations 
        GROUP BY source_type
        ORDER BY count DESC
    ");
    $stmt->execute();
    $donationSources = $stmt->fetchAll();
    
    // Recent donations for the report table
    $stmt = $pdo->prepare("
        SELECT donation_id, donor_name, donation_type, date_received, status, description 
        FROM donations 
        ORDER BY date_received DESC 
        LIMIT $offset, $recordsPerPage
    ");
    $stmt->execute();
    $recentDonations = $stmt->fetchAll();
    
    // Total donations count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM donations");
    $stmt->execute();
    $totalDonations = $stmt->fetch()['total'];
    
} catch (Exception $e) {
    error_log("Donation reports page error: " . $e->getMessage());
    $donationTypes = [];
    $donationSources = [];
    $recentDonations = [];
    $totalDonations = 0;
}

// Get inventory statistics from database
try {
    // Inventory status breakdown
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN quantity > min_stock_level * 2 THEN 'In Stock'
                WHEN quantity <= min_stock_level AND quantity > 0 THEN 'Low Stock'
                WHEN quantity = 0 THEN 'Out of Stock'
                ELSE 'Adequate Stock'
            END as stock_status,
            COUNT(*) as count
        FROM inventory 
        GROUP BY stock_status
    ");
    $stmt->execute();
    $inventoryStatus = $stmt->fetchAll();
    
    // Value by category
    $stmt = $pdo->prepare("
        SELECT 
            category,
            COUNT(*) as item_count,
            SUM(quantity) as total_quantity
        FROM inventory 
        GROUP BY category
        ORDER BY item_count DESC
    ");
    $stmt->execute();
    $categoryBreakdown = $stmt->fetchAll();
    
    // Items by category for bar chart
    $stmt = $pdo->prepare("
        SELECT category, COUNT(*) as count 
        FROM inventory 
        GROUP BY category 
        ORDER BY count DESC
        LIMIT 5
    ");
    $stmt->execute();
    $itemsByCategory = $stmt->fetchAll();
    
    // Recent inventory activities
    $stmt = $pdo->prepare("
        SELECT item_id, item_name, category, quantity, min_stock_level, 
               location, supplier, last_restocked,
               CASE 
                   WHEN quantity <= min_stock_level THEN 'critical'
                   WHEN quantity <= min_stock_level * 2 THEN 'fair'
                   ELSE 'good'
               END as stock_level
        FROM inventory 
        ORDER BY 
            CASE 
                WHEN quantity <= min_stock_level THEN 1
                WHEN quantity <= min_stock_level * 2 THEN 2
                ELSE 3
            END,
            item_name
        LIMIT $offset, $recordsPerPage
    ");
    $stmt->execute();
    $inventoryItems = $stmt->fetchAll();
    
    // Total inventory count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM inventory");
    $stmt->execute();
    $totalItems = $stmt->fetch()['total'];
    
    // Calculate stock percentages for donut chart
    $stockData = [
        'In Stock' => 0,
        'Low Stock' => 0, 
        'Out of Stock' => 0,
        'Adequate Stock' => 0
    ];
    
    foreach ($inventoryStatus as $status) {
        $stockData[$status['stock_status']] = $status['count'];
    }
    
    // Assign colors for categories
    $categoryColors = [
        'Clothing' => '#3b82f6',
        'Food & Nutrition' => '#10b981',
        'Medical' => '#ef4444',
        'Educational' => '#8b5cf6',
        'Recreation' => '#f59e0b',
        'Hygiene' => '#06b6d4',
        'Furniture' => '#84cc16',
        'Other' => '#64748b'
    ];
    
} catch (Exception $e) {
    error_log("Inventory reports page error: " . $e->getMessage());
    $inventoryStatus = [];
    $categoryBreakdown = [];
    $itemsByCategory = [];
    $inventoryItems = [];
    $totalItems = 0;
    $stockData = ['In Stock' => 0, 'Low Stock' => 0, 'Out of Stock' => 0, 'Adequate Stock' => 0];
    $categoryColors = [];
}

// Calculate priority heights for chart
$priorityHeights = [];
$maxPriorityCount = 0;
foreach ($priorityCases as $priority) {
    if ($priority['count'] > $maxPriorityCount) {
        $maxPriorityCount = $priority['count'];
    }
}

foreach ($priorityCases as $priority) {
    $height = $maxPriorityCount > 0 ? ($priority['count'] / $maxPriorityCount) * 180 : 0;
    $priorityHeights[$priority['priority']] = $height;
}

// Get status counts for easier access
$statusCounts = [];
foreach ($statusBreakdown as $status) {
    $statusCounts[$status['status']] = $status['count'];
}
// Get foster info statistics from database
try {
    // Foster parents status breakdown - USING CORRECT TABLE NAME
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM foster_parents 
        GROUP BY status
    ");
    $stmt->execute();
    $fosterStatus = $stmt->fetchAll();
    
    // Debug: Check what status data we got
    error_log("Foster Status: " . print_r($fosterStatus, true));
    
    // Foster parents by type
    $stmt = $pdo->prepare("
        SELECT 
            CASE 
                WHEN civil_status = 'Single' THEN 'Single Parents'
                WHEN civil_status = 'Married' THEN 'Married Couples'
                ELSE 'Other'
            END as family_type,
            COUNT(*) as count
        FROM foster_parents 
        GROUP BY family_type
    ");
    $stmt->execute();
    $fosterTypes = $stmt->fetchAll();
    
    // Current children in foster care
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(current_children), 0) as count 
        FROM foster_parents 
        WHERE status = 'Active'
    ");
    $stmt->execute();
    $currentFosterChildren = $stmt->fetch()['count'] ?? 0;
    
    // Available foster homes
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM foster_parents 
        WHERE status = 'Active' AND current_children < capacity
    ");
    $stmt->execute();
    $availableFosterHomes = $stmt->fetch()['count'] ?? 0;
    
    // Recent foster parents for the report table
    $stmt = $pdo->prepare("
        SELECT foster_id, name, 
               CASE 
                   WHEN civil_status = 'Single' THEN 'Single Parent'
                   WHEN civil_status = 'Married' THEN 'Married Couple'
                   ELSE civil_status
               END as family_type, 
               contact_number, email, 
               current_children, capacity, status, created_at
        FROM foster_parents 
        ORDER BY created_at DESC 
        LIMIT $offset, $recordsPerPage
    ");
    $stmt->execute();
    $recentFosterFamilies = $stmt->fetchAll();
    
    // Debug: Check recent families data
    error_log("Recent Foster Families: " . print_r($recentFosterFamilies, true));
    
    // Total foster parents count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM foster_parents");
    $stmt->execute();
    $totalFosterFamilies = $stmt->fetch()['total'];
    
    // Foster placement statistics
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_families,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active_families,
            COALESCE(AVG(current_children), 0) as avg_children_per_family
        FROM foster_parents
    ");
    $stmt->execute();
    $placementStats = $stmt->fetch();
    
    // Assign colors for foster types
    $fosterTypeColors = [
        'Single Parents' => '#ef4444',
        'Married Couples' => '#10b981',
        'Other' => '#64748b'
    ];
    
} catch (Exception $e) {
    error_log("Foster info reports page error: " . $e->getMessage());
    // Provide default empty data
    $fosterStatus = [];
    $fosterTypes = [];
    $currentFosterChildren = 0;
    $availableFosterHomes = 0;
    $recentFosterFamilies = [];
    $totalFosterFamilies = 0;
    $placementStats = ['total_families' => 0, 'active_families' => 0, 'avg_children_per_family' => 0];
    $fosterTypeColors = [];
}

// Get schedule and events statistics from database - FIXED VERSION
try {
    // First check if events table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'events'")->fetch();
    
    if (!$tableCheck) {
        // Create events table if it doesn't exist
        $createTable = "
        CREATE TABLE events (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id VARCHAR(20) UNIQUE NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT,
            event_type ENUM('home_visit', 'meeting', 'team_building', 'staff_training', 'financial', 'orientation', 'calamity_duty') NOT NULL,
            event_date DATE NOT NULL,
            event_time TIME NOT NULL,
            location VARCHAR(255),
            assigned_to VARCHAR(255),
            notes TEXT,
            status ENUM('Scheduled', 'Completed', 'Cancelled') DEFAULT 'Scheduled',
            is_active BOOLEAN DEFAULT 1,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $pdo->exec($createTable);
        
        // Insert sample data for testing
        $sampleData = [
            ['EVT-2024-001', 'Team Meeting', 'Weekly team sync', 'meeting', '2024-01-15', '14:00:00', 'Conference Room', 'John Doe', 'Weekly team meeting', 'Completed'],
            ['EVT-2024-002', 'Home Visit - Family Smith', 'Regular home visit', 'home_visit', '2024-01-20', '10:00:00', '123 Main St', 'Jane Smith', 'Follow-up visit', 'Completed'],
            ['EVT-2024-003', 'Staff Training', 'New software training', 'staff_training', '2024-01-25', '09:00:00', 'Training Room', 'Mike Johnson', 'New system training', 'Scheduled'],
            ['EVT-2024-004', 'Budget Review', 'Monthly financial review', 'financial', '2024-02-01', '11:00:00', 'Finance Office', 'Sarah Wilson', 'Q1 budget planning', 'Scheduled'],
            ['EVT-2024-005', 'Volunteer Orientation', 'New volunteer training', 'orientation', '2024-02-05', '13:00:00', 'Main Hall', 'All Staff', 'New volunteers', 'Scheduled']
        ];
        
        $insertStmt = $pdo->prepare("
            INSERT INTO events (event_id, title, description, event_type, event_date, event_time, location, assigned_to, notes, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        foreach ($sampleData as $data) {
            $insertStmt->execute($data);
        }
        
        error_log("Created events table and inserted sample data");
    }

    // Event types breakdown
    $stmt = $pdo->prepare("
        SELECT event_type, COUNT(*) as count
        FROM events 
        WHERE is_active = 1
        GROUP BY event_type
        ORDER BY count DESC
    ");
    $stmt->execute();
    $eventTypes = $stmt->fetchAll();
    
    // Event status distribution
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as count 
        FROM events 
        WHERE is_active = 1
        GROUP BY status
        ORDER BY status
    ");
    $stmt->execute();
    $eventStatus = $stmt->fetchAll();
    
    // Weekly distribution (last 30 days)
    $stmt = $pdo->prepare("
        SELECT 
            DAYNAME(event_date) as day_name,
            DAYOFWEEK(event_date) as day_num,
            COUNT(*) as count
        FROM events 
        WHERE event_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        AND is_active = 1
        GROUP BY DAYNAME(event_date), DAYOFWEEK(event_date)
        ORDER BY DAYOFWEEK(event_date)
    ");
    $stmt->execute();
    $weeklyDistribution = $stmt->fetchAll();
    
    // Recent events for the report table
    $stmt = $pdo->prepare("
        SELECT event_id, title, event_type, event_date, event_time, 
               status, location, assigned_to, description
        FROM events 
        WHERE is_active = 1
        ORDER BY event_date DESC, event_time DESC 
        LIMIT $offset, $recordsPerPage
    ");
    $stmt->execute();
    $recentEvents = $stmt->fetchAll();
    
    // Total events count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM events WHERE is_active = 1");
    $stmt->execute();
    $totalEvents = $stmt->fetch()['total'];
    
    // Event type labels for display
    $eventTypeLabels = [
        'home_visit' => 'Home Visit',
        'meeting' => 'Meeting',
        'team_building' => 'Team Building',
        'staff_training' => 'Staff Training',
        'financial' => 'Financial',
        'orientation' => 'Orientation',
        'calamity_duty' => 'Calamity Duty'
    ];
    

    
    error_log("Schedule data loaded - Events: " . $totalEvents . ", Types: " . count($eventTypes));
    
} catch (Exception $e) {
    error_log("Schedule reports page error: " . $e->getMessage());
    // Provide default empty data
    $eventTypes = [];
    $eventStatus = [];
    $weeklyDistribution = [];
    $recentEvents = [];
    $totalEvents = 0;
    $eventTypeLabels = [];
    $eventTypeIcons = [];
}

// Calculate weekly distribution heights for chart
$weeklyHeights = [];
$maxWeeklyCount = 0;

// Initialize all days with 0
$daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$weeklyData = array_fill_keys($daysOfWeek, 0);

// Fill with actual data
foreach ($weeklyDistribution as $day) {
    $weeklyData[$day['day_name']] = $day['count'];
    if ($day['count'] > $maxWeeklyCount) {
        $maxWeeklyCount = $day['count'];
    }
}

// Calculate heights (percentage of max)
foreach ($daysOfWeek as $day) {
    $height = $maxWeeklyCount > 0 ? ($weeklyData[$day] / $maxWeeklyCount) * 180 : 0;
    $weeklyHeights[$day] = $height;
}

// Initialize filter variables with proper defaults
$dateRange = $_GET['date_range'] ?? 'this_month';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';
$genderFilter = $_GET['gender'] ?? 'all';
$ageGroupFilter = $_GET['age_group'] ?? 'all';
$caseTypeFilter = $_GET['case_type'] ?? 'all';
$statusFilter = $_GET['status'] ?? 'all';
$reportType = $_GET['report_type'] ?? 'summary';
$searchFilter = $_GET['search'] ?? '';

// Set default date range if not provided
if (!$startDate || !$endDate) {
    switch ($dateRange) {
        case 'today':
            $startDate = $endDate = date('Y-m-d');
            break;
        case 'yesterday':
            $startDate = $endDate = date('Y-m-d', strtotime('-1 day'));
            break;
        case 'this_week':
            $startDate = date('Y-m-d', strtotime('monday this week'));
            $endDate = date('Y-m-d', strtotime('sunday this week'));
            break;
        case 'last_week':
            $startDate = date('Y-m-d', strtotime('monday last week'));
            $endDate = date('Y-m-d', strtotime('sunday last week'));
            break;
        case 'this_month':
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
            break;
        case 'last_month':
            $startDate = date('Y-m-01', strtotime('-1 month'));
            $endDate = date('Y-m-t', strtotime('-1 month'));
            break;
        case 'this_quarter':
            $quarter = ceil(date('n') / 3);
            $startDate = date('Y-m-d', mktime(0, 0, 0, ($quarter * 3) - 2, 1, date('Y')));
            $endDate = date('Y-m-t', mktime(0, 0, 0, $quarter * 3, 1, date('Y')));
            break;
        case 'last_quarter':
            $quarter = ceil(date('n') / 3) - 1;
            if ($quarter == 0) {
                $quarter = 4;
                $year = date('Y') - 1;
            } else {
                $year = date('Y');
            }
            $startDate = date('Y-m-d', mktime(0, 0, 0, ($quarter * 3) - 2, 1, $year));
            $endDate = date('Y-m-t', mktime(0, 0, 0, $quarter * 3, 1, $year));
            break;
        case 'this_year':
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
            break;
        case 'last_year':
            $year = date('Y') - 1;
            $startDate = $year . '-01-01';
            $endDate = $year . '-12-31';
            break;
        default:
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
    }
}

// Build WHERE clauses for filters
$whereConditions = [];
$params = [];

// Date range condition
$whereConditions[] = "DATE(created_at) BETWEEN ? AND ?";
$params[] = $startDate;
$params[] = $endDate;

// Gender filter
if ($genderFilter !== 'all') {
    $whereConditions[] = "gender = ?";
    $params[] = $genderFilter;
}

// Age group filter
if ($ageGroupFilter !== 'all') {
    switch ($ageGroupFilter) {
        case 'infant':
            $whereConditions[] = "age BETWEEN 0 AND 2";
            break;
        case 'toddler':
            $whereConditions[] = "age BETWEEN 3 AND 5";
            break;
        case 'child':
            $whereConditions[] = "age BETWEEN 6 AND 12";
            break;
        case 'teen':
            $whereConditions[] = "age BETWEEN 13 AND 18";
            break;
        case 'adult':
            $whereConditions[] = "age > 18";
            break;
    }
}

// Case type filter
if ($caseTypeFilter !== 'all') {
    $whereConditions[] = "case_type = ?";
    $params[] = $caseTypeFilter;
}

// Status filter
if ($statusFilter !== 'all') {
    $whereConditions[] = "status = ?";
    $params[] = $statusFilter;
}

$whereClause = $whereConditions ? "WHERE " . implode(" AND ", $whereConditions) : "";   
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Reports & Analytics</h1>
    </div>

    <!-- Show read-only banner if no export/print permission -->
    <?php if (!$canExport || !$canPrint): ?>
    <div class="read-only-banner">
        <strong>🔒 Read-Only Mode:</strong> You have view-only access to reports. <?php echo !$canExport ? 'Export disabled. ' : ''; ?><?php echo !$canPrint ? 'Print disabled.' : ''; ?>
    </div>
    <?php endif; ?>

    <!-- Report Tabs -->
    <div class="tabs">
        <button class="tab <?php echo $activeTab === 'child' ? 'active' : ''; ?>" onclick="switchReportTab('child', this)">Child Management</button>
        <button class="tab <?php echo $activeTab === 'case' ? 'active' : ''; ?>" onclick="switchReportTab('case', this)">Case Management</button>
        <button class="tab <?php echo $activeTab === 'donation' ? 'active' : ''; ?>" onclick="switchReportTab('donation', this)">Donation</button>
        <button class="tab <?php echo $activeTab === 'foster' ? 'active' : ''; ?>" onclick="switchReportTab('foster', this)">Foster Info</button>
        <button class="tab <?php echo $activeTab === 'schedule' ? 'active' : ''; ?>" onclick="switchReportTab('schedule', this)">Schedule & Events</button>
    </div>

    <!-- Advanced Filter Panel -->
    <div class="filter-panel" id="filterPanel" style="display: none;">
        <div class="filter-header">
            <h3>Advanced Filters & Analytics</h3>
            <button class="close-btn" onclick="toggleFilters()">&times;</button>
        </div>
        
        <form id="filterForm" method="GET" action="">
            <input type="hidden" name="active_tab" id="activeTabInput" value="<?php echo $activeTab; ?>">
            
            <div class="filter-row">
                <div class="filter-group">
                    <label for="dateRange">Date Range</label>
                    <select id="dateRange" name="date_range" onchange="toggleCustomDates()">
                        <option value="today" <?php echo $dateRange === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="yesterday" <?php echo $dateRange === 'yesterday' ? 'selected' : ''; ?>>Yesterday</option>
                        <option value="this_week" <?php echo $dateRange === 'this_week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="last_week" <?php echo $dateRange === 'last_week' ? 'selected' : ''; ?>>Last Week</option>
                        <option value="this_month" <?php echo $dateRange === 'this_month' ? 'selected' : ''; ?>>This Month</option>
                        <option value="last_month" <?php echo $dateRange === 'last_month' ? 'selected' : ''; ?>>Last Month</option>
                        <option value="this_quarter" <?php echo $dateRange === 'this_quarter' ? 'selected' : ''; ?>>This Quarter</option>
                        <option value="last_quarter" <?php echo $dateRange === 'last_quarter' ? 'selected' : ''; ?>>Last Quarter</option>
                        <option value="this_year" <?php echo $dateRange === 'this_year' ? 'selected' : ''; ?>>This Year</option>
                        <option value="last_year" <?php echo $dateRange === 'last_year' ? 'selected' : ''; ?>>Last Year</option>
                        <option value="custom" <?php echo $dateRange === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                    </select>
                </div>
                
                <div class="filter-group" id="customDates" style="<?php echo $dateRange === 'custom' ? 'display: flex;' : 'display: none;'; ?>">
                    <label for="startDate">Start Date</label>
                    <input type="date" id="startDate" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                
                <div class="filter-group" id="customDatesEnd" style="<?php echo $dateRange === 'custom' ? 'display: flex;' : 'display: none;'; ?>">
                    <label for="endDate">End Date</label>
                    <input type="date" id="endDate" name="end_date" value="<?php echo $endDate; ?>">
                </div>
            </div>

            <!-- Dynamic Filters Based on Active Tab -->
            <div class="filter-row" id="childFilters" style="<?php echo $activeTab === 'child' ? 'display: flex;' : 'display: none;'; ?>">
                <div class="filter-group">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="all" <?php echo $genderFilter === 'all' ? 'selected' : ''; ?>>All Genders</option>
                        <option value="Male" <?php echo $genderFilter === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $genderFilter === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="ageGroup">Age Group</label>
                    <select id="ageGroup" name="age_group">
                        <option value="all" <?php echo $ageGroupFilter === 'all' ? 'selected' : ''; ?>>All Ages</option>
                        <option value="infant" <?php echo $ageGroupFilter === 'infant' ? 'selected' : ''; ?>>Infant (0-2)</option>
                        <option value="toddler" <?php echo $ageGroupFilter === 'toddler' ? 'selected' : ''; ?>>Toddler (3-5)</option>
                        <option value="child" <?php echo $ageGroupFilter === 'child' ? 'selected' : ''; ?>>Child (6-12)</option>
                        <option value="teen" <?php echo $ageGroupFilter === 'teen' ? 'selected' : ''; ?>>Teen (13-18)</option>
                        <option value="adult" <?php echo $ageGroupFilter === 'adult' ? 'selected' : ''; ?>>Adult (18+)</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Adoptable" <?php echo $statusFilter === 'Adoptable' ? 'selected' : ''; ?>>Adoptable</option>
                        <option value="Adopted" <?php echo $statusFilter === 'Adopted' ? 'selected' : ''; ?>>Adopted</option>
                        <option value="In Care" <?php echo $statusFilter === 'In Care' ? 'selected' : ''; ?>>In Care</option>
                        <option value="Reintegrated" <?php echo $statusFilter === 'Reintegrated' ? 'selected' : ''; ?>>Reintegrated</option>
                    </select>
                </div>
            </div>

            <div class="filter-row" id="caseFilters" style="<?php echo $activeTab === 'case' ? 'display: flex;' : 'display: none;'; ?>">
                <div class="filter-group">
                    <label for="caseType">Case Type</label>
                    <select id="caseType" name="case_type">
                        <option value="all" <?php echo $caseTypeFilter === 'all' ? 'selected' : ''; ?>>All Case Types</option>
                        <option value="Abandonment" <?php echo $caseTypeFilter === 'Abandonment' ? 'selected' : ''; ?>>Abandonment</option>
                        <option value="Abuse" <?php echo $caseTypeFilter === 'Abuse' ? 'selected' : ''; ?>>Abuse</option>
                        <option value="Neglect" <?php echo $caseTypeFilter === 'Neglect' ? 'selected' : ''; ?>>Neglect</option>
                        <option value="Orphaned" <?php echo $caseTypeFilter === 'Orphaned' ? 'selected' : ''; ?>>Orphaned</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Case Status</label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Open" <?php echo $statusFilter === 'Open' ? 'selected' : ''; ?>>Open</option>
                        <option value="Under Investigation" <?php echo $statusFilter === 'Under Investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                        <option value="Closed" <?php echo $statusFilter === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="Court Action Pending" <?php echo $statusFilter === 'Court Action Pending' ? 'selected' : ''; ?>>Court Action Pending</option>
                    </select>
                </div>
            </div>

            <div class="filter-row" id="donationFilters" style="<?php echo $activeTab === 'donation' ? 'display: flex;' : 'display: none;'; ?>">
                <div class="filter-group">
                    <label for="donationType">Donation Type</label>
                    <select id="donationType" name="donation_type">
                        <option value="all" <?php echo $donationTypeFilter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="Money" <?php echo $donationTypeFilter === 'Money' ? 'selected' : ''; ?>>Money</option>
                        <option value="Goods" <?php echo $donationTypeFilter === 'Goods' ? 'selected' : ''; ?>>Goods</option>
                        <option value="Services" <?php echo $donationTypeFilter === 'Services' ? 'selected' : ''; ?>>Services</option>
                        <option value="Clothing" <?php echo $donationTypeFilter === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                        <option value="Food" <?php echo $donationTypeFilter === 'Food' ? 'selected' : ''; ?>>Food</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Donation Status</label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Received" <?php echo $statusFilter === 'Received' ? 'selected' : ''; ?>>Received</option>
                        <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Processed" <?php echo $statusFilter === 'Processed' ? 'selected' : ''; ?>>Processed</option>
                    </select>
                </div>
            </div>

            <div class="filter-row" id="inventoryFilters" style="<?php echo $activeTab === 'inventory' ? 'display: flex;' : 'display: none;'; ?>">
                <div class="filter-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="all" <?php echo $categoryFilter === 'all' ? 'selected' : ''; ?>>All Categories</option>
                        <option value="Clothing" <?php echo $categoryFilter === 'Clothing' ? 'selected' : ''; ?>>Clothing</option>
                        <option value="Food & Nutrition" <?php echo $categoryFilter === 'Food & Nutrition' ? 'selected' : ''; ?>>Food & Nutrition</option>
                        <option value="Medical" <?php echo $categoryFilter === 'Medical' ? 'selected' : ''; ?>>Medical</option>
                        <option value="Educational" <?php echo $categoryFilter === 'Educational' ? 'selected' : ''; ?>>Educational</option>
                        <option value="Recreation" <?php echo $categoryFilter === 'Recreation' ? 'selected' : ''; ?>>Recreation</option>
                        <option value="Hygiene" <?php echo $categoryFilter === 'Hygiene' ? 'selected' : ''; ?>>Hygiene</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Stock Status</label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="in_stock" <?php echo $statusFilter === 'in_stock' ? 'selected' : ''; ?>>In Stock</option>
                        <option value="low_stock" <?php echo $statusFilter === 'low_stock' ? 'selected' : ''; ?>>Low Stock</option>
                        <option value="out_of_stock" <?php echo $statusFilter === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                        <option value="adequate_stock" <?php echo $statusFilter === 'adequate_stock' ? 'selected' : ''; ?>>Adequate Stock</option>
                    </select>
                </div>
            </div>

            <div class="filter-row" id="fosterFilters" style="<?php echo $activeTab === 'foster' ? 'display: flex;' : 'display: none;'; ?>">
                <div class="filter-group">
                    <label for="fosterType">Family Type</label>
                    <select id="fosterType" name="foster_type">
                        <option value="all" <?php echo $fosterTypeFilter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="single" <?php echo $fosterTypeFilter === 'single' ? 'selected' : ''; ?>>Single Parents</option>
                        <option value="married" <?php echo $fosterTypeFilter === 'married' ? 'selected' : ''; ?>>Married Couples</option>
                        <option value="other" <?php echo $fosterTypeFilter === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Foster Status</label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Active" <?php echo $statusFilter === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Inactive" <?php echo $statusFilter === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="filter-row" id="scheduleFilters" style="<?php echo $activeTab === 'schedule' ? 'display: flex;' : 'display: none;'; ?>">
                <div class="filter-group">
                    <label for="eventType">Event Type</label>
                    <select id="eventType" name="event_type">
                        <option value="all" <?php echo $eventTypeFilter === 'all' ? 'selected' : ''; ?>>All Types</option>
                        <option value="home_visit" <?php echo $eventTypeFilter === 'home_visit' ? 'selected' : ''; ?>>Home Visit</option>
                        <option value="meeting" <?php echo $eventTypeFilter === 'meeting' ? 'selected' : ''; ?>>Meeting</option>
                        <option value="team_building" <?php echo $eventTypeFilter === 'team_building' ? 'selected' : ''; ?>>Team Building</option>
                        <option value="staff_training" <?php echo $eventTypeFilter === 'staff_training' ? 'selected' : ''; ?>>Staff Training</option>
                        <option value="financial" <?php echo $eventTypeFilter === 'financial' ? 'selected' : ''; ?>>Financial</option>
                        <option value="orientation" <?php echo $eventTypeFilter === 'orientation' ? 'selected' : ''; ?>>Orientation</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="status">Event Status</label>
                    <select id="status" name="status">
                        <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="Scheduled" <?php echo $statusFilter === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                        <option value="Completed" <?php echo $statusFilter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <div class="filter-actions">
                <button type="button" class="btn btn-outline" onclick="resetFilters()">Reset Filters</button>
                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </div>
        </form>
    </div>


    <!-- Search and Filter Bar -->
    <div class="search-bar">
        <input type="text" class="search-input" id="reportSearch" placeholder="Search reports...">
        
        <!-- Filter Button -->
        
        
        <?php if ($canPrint): ?>
            <button class="btn btn-primary" onclick="printReport()">Print</button>
        <?php else: ?>
            <button class="btn btn-secondary" disabled title="No permission to print">Print</button>
        <?php endif; ?>
    </div>




    <!-- Child Management Report Content -->
    <div id="childReport" class="report-content active">
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Status Breakdown</h3>
                <?php foreach ($statusBreakdown as $status): ?>
                <div class="stat-item">
                    <span class="stat-label"><?php echo htmlspecialchars($status['status']); ?></span>
                    <span class="stat-value"><?php echo htmlspecialchars($status['count']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="success-card">
                <h3>Successful Adoptions</h3>
                <div class="success-number"><?php echo htmlspecialchars($monthlyAdoptions); ?></div>
                <div class="success-label">This month</div>
                <div class="success-rate">Success rate: <?php echo htmlspecialchars($successRate); ?>%</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="chart-section">
            <div class="chart-card">
                <h3>Children Demographics Analysis</h3>
                <div class="chart-item">
                    <div class="chart-label">
                        <span><strong>Gender Distribution</strong></span>
                    </div>
                </div>
                <?php 
                $totalGender = array_sum(array_column($genderDistribution, 'count'));
                foreach ($genderDistribution as $gender): 
                    $percentage = $totalGender > 0 ? round(($gender['count'] / $totalGender) * 100) : 0;
                ?>
                <div class="chart-item">
                    <div class="chart-label">
                        <span><?php echo htmlspecialchars($gender['gender']); ?></span>
                        <span><?php echo htmlspecialchars($gender['count']); ?> (<?php echo $percentage; ?>%)</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill progress-<?php echo strtolower($gender['gender']) === 'male' ? 'blue' : 'green'; ?>" 
                             style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="chart-card">
                <h3>Age Distribution</h3>
                <?php foreach ($ageDistribution as $ageGroup): ?>
                <div class="chart-item">
                    <div class="chart-label">
                        <span><?php echo htmlspecialchars($ageGroup['age_group']); ?></span>
                        <span><?php echo htmlspecialchars($ageGroup['percentage']); ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill progress-<?php 
                            echo $ageGroup['age_group'] === '0-5 years' ? 'blue' : 
                                 ($ageGroup['age_group'] === '6-12 years' ? 'green' : 'red'); 
                        ?>" style="width: <?php echo htmlspecialchars($ageGroup['percentage']); ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Report Table -->
        <div class="table-section">
            <div class="table-header">
                <h3>Child Management Report</h3>
                <span class="table-date">Generated on <?php echo date('m/d/Y'); ?></span>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Child ID</th>
                        <th>Age</th>
                        <th>Gender</th>
                        <th>Status</th>
                        <th>Entry Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="childReportTableBody">
                    <?php foreach ($recentChildren as $child): ?>
                    <tr>
                        <td class="child-id"><?php echo htmlspecialchars($child['child_id']); ?></td>
                        <td><?php echo htmlspecialchars($child['age']); ?></td>
                        <td><?php echo htmlspecialchars($child['gender']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $child['status'] === 'Adopted' ? 'success' : 
                                     ($child['status'] === 'Adoptable' ? 'info' : 'danger'); 
                            ?>">
                                <?php echo htmlspecialchars($child['status']); ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($child['entry_date']); ?></td>
                        <td><?php echo htmlspecialchars($child['notes'] ?? 'No notes'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="pagination">
            <span>Total Records: <?php echo $totalChildren; ?> (Showing: <?php echo count($recentChildren); ?>)</span>
            
            <!-- Previous Button -->
            <button class="btn-primary page-btn" 
                    onclick="changePage(<?php echo max(1, $currentPage - 1); ?>)"
                    <?php echo $currentPage == 1 ? 'disabled' : ''; ?>>
                Previous
            </button>
            
            <!-- Page Numbers -->
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalChildPages, $currentPage + 2);
            
            for ($page = $startPage; $page <= $endPage; $page++):
            ?>
                <button class="page-btn <?php echo $page == $currentPage ? 'active' : ''; ?>" 
                        onclick="changePage(<?php echo $page; ?>)">
                    <?php echo $page; ?>
                </button>
            <?php endfor; ?>
            
            <!-- Next Button -->
            <button class="btn-primary page-btn" 
                    onclick="changePage(<?php echo min($totalChildPages, $currentPage + 1); ?>)"
                    <?php echo $currentPage == $totalChildPages ? 'disabled' : ''; ?>>
                Next
            </button>
            
            <!-- Page Info -->
            <span class="page-info">
                Page <?php echo $currentPage; ?> of <?php echo $totalChildPages; ?>
            </span>
        </div>
        </div>

        <div class="signature-section print-only">
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Authorized Signature</div>
            </div>
            <br>
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Director</div>
            </div>
        </div>
    </div>   
    
    <!-- Case Management Report Content -->
    <div id="caseReport" class="report-content">
        <!-- Case Management Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Entry Reasons Card -->
            <div class="card">
                <h2 class="card-title">Entry Reasons</h2>
                <div class="entry-reasons">
                    <?php 
                    $colors = ['red', 'blue', 'gray', 'green', 'red'];
                    $colorIndex = 0;
                    foreach ($entryReasons as $reason): 
                    ?>
                    <div class="reason-item">
                        <span class="reason-label"><?php echo htmlspecialchars($reason['case_type']); ?></span>
                        <div class="progress-bar">
                            <div class="progress-fill <?php echo $colors[$colorIndex % count($colors)]; ?>" 
                                 style="width: <?php echo htmlspecialchars($reason['percentage']); ?>%"></div>
                        </div>
                        <span class="reason-percent"><?php echo htmlspecialchars($reason['percentage']); ?>%</span>
                    </div>
                    <?php 
                    $colorIndex++;
                    endforeach; 
                    ?>
                </div>
            </div>

            <!-- Case Status Distribution Card -->
            <div class="card">
                <h2 class="card-title">Case Status Distribution</h2>
                <div class="donut-chart">
                    <canvas id="donutChart"></canvas>
                    <div class="donut-center">
                        <div class="donut-number"><?php echo htmlspecialchars($totalCases); ?></div>
                        <div class="donut-label">Total Cases</div>
                    </div>
                </div>
                <div class="legend">
                    <?php 
                    $statusColors = [
                        'Open' => '#60a5fa',
                        'Under Investigation' => '#10b981', 
                        'Closed' => '#fbbf24',
                        'Court Action Pending' => '#f87171',
                        'Active' => '#60a5fa',
                        'Pending' => '#10b981'
                    ];
                    
                    foreach ($caseStatus as $status): 
                        $color = $statusColors[$status['status']] ?? '#6b7280';
                    ?>
                    <div class="legend-item">
                        <div class="legend-color" style="background: <?php echo $color; ?>"></div>
                        <span><?php echo htmlspecialchars($status['status']); ?> - <?php echo htmlspecialchars($status['count']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Cases by Priority Card -->
            <div class="card">
                <h2 class="card-title">Cases by Priority</h2>
                <div class="priority-chart">
                    <?php 
                    $priorityLabels = [
                        'urgent' => 'Urgent',
                        'mild' => 'Mid', 
                        'common' => 'Common'
                    ];
                    
                    $priorityColors = [
                        'urgent' => 'red',
                        'mild' => 'blue',
                        'common' => 'green'
                    ];
                    
                    foreach ($priorityCases as $priority): 
                        $height = $priorityHeights[$priority['priority']] ?? 0;
                    ?>
                    <div class="bar-container">
                        <div class="bar <?php echo $priorityColors[$priority['priority']]; ?>" 
                             style="height: <?php echo $height; ?>px"></div>
                        <div class="bar-label"><?php echo htmlspecialchars($priorityLabels[$priority['priority']] ?? ucfirst($priority['priority'])); ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Case Management Report Table -->
        <div class="report-section">
            <div class="report-header">
                <h2 class="report-title">Case Management Report</h2>
                <div class="report-date">Generated on: <?php echo date('n/j/Y'); ?></div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Case ID</th>
                            <th>Case Type</th>
                            <th>Case Worker</th>
                            <th>Status</th>
                            <th>Last Update</th>
                            <th>Priority</th>
                        </tr>
                    </thead>
                    <tbody id="caseReportTableBody">
                        <?php foreach ($recentCases as $case): ?>
                        <tr>
                            <td class="child-id"><?php echo htmlspecialchars($case['case_id']); ?></td>
                            <td><?php echo htmlspecialchars($case['case_type']); ?></td>
                            <td><?php echo htmlspecialchars($socialWorkers[$case['social_worker']] ?? 'Unassigned'); ?></td>
                            <td>
                                <span class="status-badge status-<?php 
                                    echo strtolower(str_replace(' ', '-', $case['status'])); 
                                ?>">
                                    <?php echo htmlspecialchars($case['status']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($case['updated_at'] ?? $case['created_date']); ?></td>
                            <td>
                                <span class="priority-badge priority-<?php 
                                    echo $case['priority'] ? strtolower($case['priority']) : 'medium'; 
                                ?>">
                                    <?php echo $case['priority'] ? ucfirst($case['priority']) : 'Medium'; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>


            <div class="pagination">
                <span>Total Records: <?php echo $totalCases; ?> (Showing: <?php echo count($recentCases); ?>)</span>
                
                <!-- Previous Button -->
                <button class="btn-primary page-btn" 
                        onclick="changePage(<?php echo max(1, $currentPage - 1); ?>)"
                        <?php echo $currentPage == 1 ? 'disabled' : ''; ?>>
                    Previous
                </button>
                
                <!-- Page Numbers -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalCasePages, $currentPage + 2);
                
                for ($page = $startPage; $page <= $endPage; $page++):
                ?>
                    <button class="page-btn <?php echo $page == $currentPage ? 'active' : ''; ?>" 
                            onclick="changePage(<?php echo $page; ?>)">
                        <?php echo $page; ?>
                    </button>
                <?php endfor; ?>
                
                <!-- Next Button -->
                <button class="btn-primary page-btn" 
                        onclick="changePage(<?php echo min($totalCasePages, $currentPage + 1); ?>)"
                        <?php echo $currentPage == $totalCasePages ? 'disabled' : ''; ?>>
                    Next
                </button>
                
                <!-- Page Info -->
                <span class="page-info">
                    Page <?php echo $currentPage; ?> of <?php echo $totalCasePages; ?>
                </span>
            </div>
        </div>
        <div class="signature-section print-only">
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Authorized Signature</div>
            </div>
            <br>
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Director</div>
            </div>
        </div>
    </div>
                            
    <!-- Donation Report Content -->
    <div id="donationReport" class="report-content">
        <!-- Donation Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Donation Types Card -->
            <div class="card">
                <h2 class="card-title">Donation Types</h2>
                <div class="donut-chart-container">
                    <canvas id="donationDonutChart" width="400" height="400"></canvas>
                    <div class="donut-center">
                        <div class="donut-number"><?php echo htmlspecialchars($totalDonations); ?></div>
                        <div class="donut-label">Total Donations</div>
                    </div>
                </div>
                <div class="legend">
                    <?php 
                    $donationColors = [
                        'Money' => '#f87171',
                        'Goods' => '#60a5fa', 
                        'Services' => '#fbbf24',
                        'Clothing' => '#10b981',
                        'Food' => '#8b5cf6',
                        'Toys' => '#06b6d4'
                    ];
                    
                    if (empty($donationTypes)): ?>
                        <div class="legend-item">
                            <div class="legend-color" style="background: #95a5a6"></div>
                            <span>No donation data available</span>
                        </div>
                    <?php else: 
                        foreach ($donationTypes as $type): 
                            $color = $donationColors[$type['donation_type']] ?? '#6b7280';
                    ?>
                        <div class="legend-item">
                            <div class="legend-color" style="background: <?php echo $color; ?>"></div>
                            <span><?php echo htmlspecialchars($type['donation_type']); ?> - <?php echo htmlspecialchars($type['count']); ?></span>
                        </div>
                    <?php endforeach; 
                    endif; ?>
                </div>
            </div>

            <!-- Donation Sources Card -->
            <div class="card">
                <h2 class="card-title">Donation Sources</h2>
                <div class="source-list">
                    <?php 
                    $sourceColors = [
                        'Individual Donors' => '#60a5fa',
                        'Corporate Sponsors' => '#10b981',
                        'Grants' => '#fbbf24',
                        'Fundraising Events' => '#f87171'
                    ];
                    
                    foreach ($donationSources as $source): 
                        $color = $sourceColors[$source['source_type']] ?? '#6b7280';
                    ?>
                    <div class="source-item">
                        <div class="source-info">
                            <div class="source-dot" style="background: <?php echo $color; ?>"></div>
                            <span class="source-name"><?php echo htmlspecialchars($source['source_type']); ?></span>
                        </div>
                        <div class="source-stats">
                            <span class="source-type">Cash & Goods</span>
                            <span class="source-count">(<?php echo htmlspecialchars($source['percentage']); ?>%)</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Donation Report Table -->
        <div class="report-section">
            <div class="report-header">
                <h2 class="report-title">Donation Report</h2>
                <div class="report-date">Generated on: <?php echo date('n/j/Y'); ?></div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Donation ID</th>
                            <th>Donor</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody id="donationReportTableBody">
                        <?php foreach ($recentDonations as $donation): ?>
                        <tr>
                            <td class="child-id"><?php echo htmlspecialchars($donation['donation_id']); ?></td>
                            <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                            <td><?php echo htmlspecialchars($donation['donation_type']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo strtolower($donation['status']); ?>">
                                    <?php echo htmlspecialchars($donation['status']); ?>
                                </span>
                            </td>
                            <td><?php echo formatDate($donation['date_received']); ?></td>
                            <td><?php echo htmlspecialchars($donation['description'] ?? 'General Donation'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <span>Total Records: <?php echo $totalDonations; ?> (Showing: <?php echo count($recentDonations); ?>)</span>
                
                <!-- Previous Button -->
                <button class="btn-primary page-btn" 
                        onclick="changePage(<?php echo max(1, $currentPage - 1); ?>)"
                        <?php echo $currentPage == 1 ? 'disabled' : ''; ?>>
                    Previous
                </button>
                
                <!-- Page Numbers -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalDonationPages, $currentPage + 2);
                
                for ($page = $startPage; $page <= $endPage; $page++):
                ?>
                    <button class="page-btn <?php echo $page == $currentPage ? 'active' : ''; ?>" 
                            onclick="changePage(<?php echo $page; ?>)">
                        <?php echo $page; ?>
                    </button>
                <?php endfor; ?>
                
                <!-- Next Button -->
                <button class="btn-primary page-btn" 
                        onclick="changePage(<?php echo min($totalDonationPages, $currentPage + 1); ?>)"
                        <?php echo $currentPage == $totalDonationPages ? 'disabled' : ''; ?>>
                    Next
                </button>
                
                <!-- Page Info -->
                <span class="page-info">
                    Page <?php echo $currentPage; ?> of <?php echo $totalDonationPages; ?>
                </span>
            </div>
        </div>
        <div class="signature-section print-only">
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Authorized Signature</div>
            </div>
            <br>
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Director</div>
            </div>
        </div>
    </div>


        <!-- Foster Info Report Content -->
        <div id="fosterReport" class="report-content">
        <!-- Foster Info Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Foster Families Status -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Foster Families Status</h3>
                </div>
                <div class="chart-container">
                    <canvas id="fosterStatusChart" height="300"></canvas>
                </div>
            </div>


            <!-- Foster Families by Type -->
            <div class="card" style="grid-column: span 2;">
                <div class="card-header">
                    <h3 class="card-title">Foster Families by Type</h3>
                </div>
                <div class="value-list">
                    <?php 
                    $totalFosterTypes = array_sum(array_column($fosterTypes, 'count'));
                    foreach ($fosterTypes as $fosterType): 
                        $percentage = $totalFosterTypes > 0 ? round(($fosterType['count'] / $totalFosterTypes) * 100) : 0;
                        $color = $fosterTypeColors[$fosterType['family_type']] ?? '#64748b';
                    ?>
                    <div class="value-item">
                        <div class="value-label">
                            <span class="color-dot" style="background: <?php echo $color; ?>"></span>
                            <span><?php echo htmlspecialchars($fosterType['family_type']); ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $color; ?>"></div>
                        </div>
                        <span><?php echo htmlspecialchars($fosterType['count']); ?> families</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Foster Families Table -->
        <div class="table-section">
            <div class="table-header">
                <h3 class="card-title">Foster Parents Report</h3>
                <p style="color: #0E7490; font-size: 14px; ">Generated on <?php echo date('m/d/Y'); ?></p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Foster ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Current Children</th>
                        <th>Capacity</th>
                        <th>Status</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody id="fosterReportTableBody">
                    <?php foreach ($recentFosterFamilies as $family): ?>
                    <tr>
                        <td class="child-id"><?php echo htmlspecialchars($family['foster_id']); ?></td>
                        <td><?php echo htmlspecialchars($family['name']); ?></td>
                        <td>
                            <span class="badge badge-<?php 
                                echo $family['family_type'] === 'Single Parent' ? 'fair' : 
                                    ($family['family_type'] === 'Married Couple' ? 'good' : 'other'); 
                            ?>">
                                <?php echo htmlspecialchars($family['family_type']); ?>
                            </span>
                        </td>
                        <td>
                            <div><?php echo htmlspecialchars($family['contact_number']); ?></div>
                            <div style="font-size: 12px; color: #0E7490;"><?php echo htmlspecialchars($family['email'] ?? 'No email'); ?></div>
                        </td>
                        <td><?php echo htmlspecialchars($family['current_children']); ?></td>
                        <td><?php echo htmlspecialchars($family['capacity']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $family['status'] === 'Active' ? 'good' : ($family['status'] === 'Pending' ? 'fair' : 'critical'); ?>">
                                <?php echo htmlspecialchars($family['status']); ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($family['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination">
                <span>Total Records: <?php echo $totalFosterFamilies; ?> (Showing: <?php echo count($recentFosterFamilies); ?>)</span>
                
                <!-- Previous Button -->
                <button class="btn-primary page-btn" 
                        onclick="changePage(<?php echo max(1, $currentPage - 1); ?>)"
                        <?php echo $currentPage == 1 ? 'disabled' : ''; ?>>
                    Previous
                </button>
                
                <!-- Page Numbers -->
                <?php
                $startPage = max(1, $currentPage - 2);
                $endPage = min($totalFosterPages, $currentPage + 2);
                
                for ($page = $startPage; $page <= $endPage; $page++):
                ?>
                    <button class="page-btn <?php echo $page == $currentPage ? 'active' : ''; ?>" 
                            onclick="changePage(<?php echo $page; ?>)">
                        <?php echo $page; ?>
                    </button>
                <?php endfor; ?>
                
                <!-- Next Button -->
                <button class="btn-primary page-btn" 
                        onclick="changePage(<?php echo min($totalFosterPages, $currentPage + 1); ?>)"
                        <?php echo $currentPage == $totalFosterPages ? 'disabled' : ''; ?>>
                    Next
                </button>
                
                <!-- Page Info -->
                <span class="page-info">
                    Page <?php echo $currentPage; ?> of <?php echo $totalFosterPages; ?>
                </span>
            </div>
        </div>
        <div class="signature-section print-only">
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Authorized Signature</div>
            </div>
            <br>
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Director</div>
            </div>
        </div>
    </div>

    <!-- Schedule & Events Report Content -->
<div id="scheduleReport" class="report-content">
    <!-- Debug Info -->
    

    <!-- Schedule Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Event Types Distribution -->
        <div class="card">
            <h2 class="card-title">Event Types Distribution</h2>
            <div class="donut-chart-container">
                <canvas id="eventTypesDonutChart" width="400" height="400"></canvas>
                <div class="donut-center">
                    <div class="donut-number"><?php echo htmlspecialchars($totalEvents); ?></div>
                    <div class="donut-label">Total Events</div>
                </div>
            </div>
            <div class="legend">
                <?php 
                $eventTypeColors = [
                    'home_visit' => '#3498db',
                    'meeting' => '#2ecc71',
                    'team_building' => '#1abc9c',
                    'staff_training' => '#9b59b6',
                    'financial' => '#e74c3c',
                    'orientation' => '#f39c12',
                    'calamity_duty' => '#e67e22'
                ];
                
                if (empty($eventTypes)): ?>
                    <div class="legend-item">
                        <div class="legend-color" style="background: #95a5a6"></div>
                        <span>No events data available</span>
                    </div>
                <?php else: 
                    foreach ($eventTypes as $eventType): 
                        $color = $eventTypeColors[$eventType['event_type']] ?? '#95a5a6';
                        $label = $eventTypeLabels[$eventType['event_type']] ?? ucfirst(str_replace('_', ' ', $eventType['event_type']));
                ?>
                    <div class="legend-item">
                        <div class="legend-color" style="background: <?php echo $color; ?>"></div>
                        <span><?php echo htmlspecialchars($label); ?> - <?php echo htmlspecialchars($eventType['count']); ?></span>
                    </div>
                <?php endforeach; 
                endif; ?>
            </div>
        </div>

        <!-- Event Status Overview -->
        <div class="card">
            <h2 class="card-title">Event Status Overview</h2>
            <div class="stats-grid-mini">
                <?php 
                $statusIcons = [
                    'Scheduled' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-check" viewBox="0 0 16 16">
                    <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                    <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                    </svg>',
                    'Completed' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check2" viewBox="0 0 16 16">
                    <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0"/>
                    </svg>',
                    'Cancelled' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                    <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z"/>
                    </svg>'
                ];
                
                if (empty($eventStatus)): ?>
                    <div class="stat-mini-card">
                        <div class="stat-mini-icon"></div>
                        <div class="stat-mini-value">0</div>
                        <div class="stat-mini-label">No Data</div>
                    </div>
                <?php else: 
                    foreach ($eventStatus as $status): 
                        $icon = $statusIcons[$status['status']] ?? '';
                ?>
                    <div class="stat-mini-card">
                        <div class="stat-mini-icon"><?php echo $icon; ?></div>
                        <div class="stat-mini-value"><?php echo htmlspecialchars($status['count']); ?></div>
                        <div class="stat-mini-label"><?php echo htmlspecialchars($status['status']); ?></div>
                    </div>
                <?php endforeach; 
                endif; ?>
            </div>
        </div>
    </div>

    <!-- Schedule Report Table -->
    <div class="report-section">
        <div class="report-header">
            <h2 class="report-title">Schedule & Events Report</h2>
            <div class="report-date">Generated on: <?php echo date('n/j/Y'); ?></div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Event ID</th>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Assigned Staff</th>
                    </tr>
                </thead>
                <tbody id="scheduleReportTableBody">
                    <?php if (empty($recentEvents)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px; color: #888;">
                                <div style="font-size: 48px; margin-bottom: 10px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-check" viewBox="0 0 16 16">
                                <path d="M10.854 7.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 9.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4z"/>
                                </svg></div>
                                No events found in the database
                            </td>
                        </tr>
                    <?php else: 
                        foreach ($recentEvents as $event): 
                            $eventDate = new DateTime($event['event_date']);
                            $eventTime = new DateTime($event['event_time']);
                            $icon = $eventTypeIcons[$event['event_type']] ?? '';
                            $typeLabel = $eventTypeLabels[$event['event_type']] ?? ucfirst(str_replace('_', ' ', $event['event_type']));
                    ?>
                    <tr>
                        <td class="event-id" ><?php echo htmlspecialchars($event['event_id']); ?></td>
                        <td class="event-name">
                            <span class="event-icon"><?php echo $icon; ?></span>
                            <?php echo htmlspecialchars($event['title']); ?>
                        </td>
                        <td><?php echo $eventDate->format('M j, Y'); ?></td>
                        <td><?php echo $eventTime->format('g:i A'); ?></td>
                        <td>
                            <span class="event-type-badge"><?php echo htmlspecialchars($typeLabel); ?></span>
                        </td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($event['status']); ?>">
                                <?php echo htmlspecialchars($event['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($event['location'] ?? 'Not specified'); ?></td>
                        <td><?php echo htmlspecialchars($event['assigned_to'] ?? 'Unassigned'); ?></td>
                    </tr>
                    <?php endforeach; 
                    endif; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <span>Total Records: <?php echo $totalEvents; ?> (Showing: <?php echo count($recentEvents); ?>)</span>
            
            <!-- Previous Button -->
            <button class="btn-primary page-btn" 
                    onclick="changePage(<?php echo max(1, $currentPage - 1); ?>)"
                    <?php echo $currentPage == 1 ? 'disabled' : ''; ?>>
                Previous
            </button>
            
            <!-- Page Numbers -->
            <?php
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalEventPages, $currentPage + 2);
            
            for ($page = $startPage; $page <= $endPage; $page++):
            ?>
                <button class="page-btn <?php echo $page == $currentPage ? 'active' : ''; ?>" 
                        onclick="changePage(<?php echo $page; ?>)">
                    <?php echo $page; ?>
                </button>
            <?php endfor; ?>
            
            <!-- Next Button -->
            <button class="btn-primary page-btn" 
                    onclick="changePage(<?php echo min($totalEventPages, $currentPage + 1); ?>)"
                    <?php echo $currentPage == $totalEventPages ? 'disabled' : ''; ?>>
                Next
            </button>
            
            <!-- Page Info -->
            <span class="page-info">
                Page <?php echo $currentPage; ?> of <?php echo $totalEventPages; ?>
            </span>
        </div>
    </div>
    <div class="signature-section print-only">
        <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Authorized Signature</div>
            </div>
            <br>
            <div class="signature-box">
                <div class="signature-name">_________________________</div>
                <div class="signature-title">Director</div>
            </div>
        </div>
    </div>
</div>
</main>

<style>
.light-theme .child-id {
    color: #000;
}

.filter-panel {
    background: #2a2a2a;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 25px;
    border: 1px solid #3a3a3a;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #3a3a3a;
}

.filter-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    color: #9ca3af;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    color: #fff;
}

.filter-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
    flex: 1;
    min-width: 200px;
}

.filter-group label {
    font-size: 14px;
    color: #9ca3af;
    font-weight: 500;
}

.filter-group select,
.filter-group input {
    padding: 10px 12px;
    background: #1a1a1a;
    border: 1px solid #444;
    border-radius: 4px;
    color: #fff;
    font-size: 14px;
}

.filter-group select:focus,
.filter-group input:focus {
    outline: none;
    border-color: #2563eb;
}

.filter-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #3a3a3a;
}

.btn-outline {
    background: transparent;
    border: 1px solid #6b7280;
    color: #9ca3af;
}

.btn-outline:hover {
    background: #374151;
    border-color: #9ca3af;
    color: #fff;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .filter-row {
        flex-direction: column;
    }
    
    .filter-group {
        min-width: 100%;
    }
    
    .filter-actions {
        flex-direction: column;
    }
}

.print-only {
    display: none;
}

@media print {
    .print-only {
        display: block !important;
    }
    
    .signature-section {
        margin-top: 50px;
        padding-top: 20px;
        border-top: 1px solid #000;
    }
    
    .signature-box {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .signature-name {
        font-weight: bold;
        margin-bottom: 5px;
    }
    
    .signature-title {
        color: #666;
        font-size: 14px;
    }
}

.light-theme .pagination span {
    color: #000;
}

.tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
    flex-wrap: wrap;
}

.tab {
    background: #2563eb;
    padding: 10px 20px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.3s;
    border: none;
    color: white;
}

.tab:hover {
    background: #1d4ed8;
}

.tab.active {
    background: #1e40af;
}

.search-bar {
    display: flex;
    gap: 15px;
    margin-bottom: 30px;
    align-items: center;
    flex-wrap: wrap;
}

.search-input {
    flex: 1;
    min-width: 250px;
    padding: 10px 15px;
    background: #2a2a2a;
    border: 1px solid #444;
    border-radius: 4px;
    color: #fff;
    font-size: 14px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-primary {
    background: #2563eb;
    color: #fff;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-success {
    background: #16a34a;
    color: #fff;
}

.btn-success:hover {
    background: #15803d;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.stat-card {
    background: #2a2a2a;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #3a3a3a;
}

.dark-theme .stat-card h3 {
    font-size: 16px;
    margin-bottom: 20px;
    font-weight: 500;
}

.light-theme .stat-card h3{
    font-size: 16px;
    margin-bottom: 20px;
    font-weight: 500;
    color: #1e293b;
}
.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #3a3a3a;
}

.stat-item:last-child {
    border-bottom: none;
}

.dark-theme .stat-label {
    color: #9ca3af;
}

.light-theme .stat-label {
    color: #1a2744;
}

.stat-value {
    font-size: 20px;
    font-weight: 600;
}

.dark-theme .success-card {
    background: #2a2a2a;
    padding: 25px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #3a3a3a;
}

.light-theme .chart-card{
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.light-theme .chart-card:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
}

.light-theme .chart-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}

.light-theme .success-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    text-align: center;
    color: #1e293b;
    padding: 24px;
}

.light-theme .success-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
}

.light-theme .success-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}

.light-theme #childReportTableBody td {
    background: #f1f5f9;
    color: black;
    font-size: 15px;
}


.light-theme #caseReportTableBody td {
    background: #f1f5f9;
    color: black;
}

.success-number {
    font-size: 48px;
    font-weight: bold;
    color: #10b981;
    margin-bottom: 10px;
}

.success-label {
    color: #10b981;
    margin-bottom: 5px;
}

.success-rate {
    color: #6b7280;
    font-size: 14px;
}

.chart-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.chart-card {
    background: #2a2a2a;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #3a3a3a;
}

.dark-theme .chart-card h3 {
    font-size: 16px;
    margin-bottom: 20px;
    font-weight: 500;
}

.light-theme .chart-card h3 {
    font-size: 16px;
    margin-bottom: 20px;
    font-weight: 500;
    color: #1e293b;
}


.chart-item {
    margin-bottom: 15px;
}

.chart-label {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 14px;
}

.light-theme .chart-label strong {
    color: #1e293b;
}

.light-theme .chart-label span {
    color: #1e293b;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #3a3a3a;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 4px;
    transition: width 0.5s ease;
}

.progress-blue {
    background: #3b82f6;
}

.progress-green {
    background: #10b981;
}

.progress-red {
    background: #ef4444;
}

.dark-theme .table-section {
    background: #2a2a2a;
    padding: 25px;
    border-radius: 8px;
    border: 1px solid #3a3a3a;
    margin-bottom: 30px;
}

.light-theme .table-section {
    padding: 25px;
    border-radius: 8px;
    
    margin-bottom: 30px;
}

.light-theme .debug-info {
    background: #f1f5f9;
    color: #000;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
    border: 1px solid #e2e8f0;
}

.dark-theme .debug-info {
    background: #2a2a2a;
    color: #fff;
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 14px;
    border: 1px solid #3a3a3a;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.light-theme .table-header h3 {
    color: black;
}
.table-header h3 {
    font-size: 18px;
    font-weight: 500;
}

.table-date {
    color: #6b7280;
    font-size: 14px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

thead {
    background: #1a1a1a;
}

th {
    padding: 15px;
    text-align: left;
    font-weight: 500;
    font-size: 14px;
    border-bottom: 1px solid #3a3a3a;
}

td {
    padding: 15px;
    border-bottom: 1px solid #3a3a3a;
    font-size: 14px;
}

tbody tr:hover {
    background: #333;
}

.badge {
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
}

.badge-success {
    background: rgba(39, 174, 96, 0.2);
    color: #27ae60;
}

.badge-info {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
}

.badge-danger {
    background: #f8d7da;
    color: #721c24;
}

.pagination {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    margin-top: 20px;
}

.page-btn {
    padding: 8px 12px;
    background: #3a3a3a;
    border: none;
    color: #fff;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.page-btn:hover {
    background: #4a4a4a;
}

.page-btn.active {
    background: #2563eb;
}

.report-content {
    display: none;
}

.report-content.active {
    display: block;
}

/* Case Management Specific Styles */
.dashboard-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-bottom: 32px;
}

.dark-theme .card {
    background: #2c2c2c;
    border-radius: 8px;
    padding: 24px;
}

.light-theme .card {
    border-radius: 8px;
    padding: 24px;
    border: 1px solid #e2e8f0;
    background: #f1f5f9;
    color: #000;
}

.dark-theme .card-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 80px;
}

.light-theme .card-title {
    color: black;
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 80px;
}


.entry-reasons {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.reason-item {
    display: flex;
    align-items: center;
    gap: 12px;
}

.dark-theme .reason-label {
    min-width: 100px;
    font-size: 14px;
}

.light-theme .reason-label {
    min-width: 100px;
    font-size: 14px;
    color: black;
}

.dark-theme .reason-percent {
    min-width: 40px;
    text-align: right;
    font-size: 14px;
}

.light-theme .reason-percent {
    min-width: 40px;
    text-align: right;
    font-size: 14px;
    color: #0E7490;
}

.priority-chart {
    display: flex;
    justify-content: space-around;
    align-items: flex-end;
    height: 200px;
    padding: 20px 0;
}

.bar-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.bar {
    width: 60px;
    border-radius: 4px 4px 0 0;
    transition: height 0.3s;
}

.bar.green { background: #10b981; }
.bar.blue { background: #2563eb; }
.bar.red { background: #dc3545; }

.bar-label {
    font-size: 14px;
    color: #999;
}

.donut-chart {
    width: 280px;
    height: 280px;
    margin: 0 auto;
    position: relative;
}

.donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.dark-theme .donut-number {
    font-size: 32px;
    font-weight: 700;
}

.dark-theme .donut-number {
    font-size: 32px;
    font-weight: 700;
    margin-top: -60px;
}

.light-theme .donut-number {
    font-size: 32px;
    font-weight: 700;
    color: #000;
    margin-top: -60px;
}

/* Add these to your existing CSS */
.progress-fill.red { background: #ef4444; }
.progress-fill.blue { background: #3b82f6; }
.progress-fill.gray { background: #6b7280; }
.progress-fill.green { background: #10b981; }

.dark-theme .donut-label {
    font-size: 12px;
    color: #999;
}

.light-theme .donut-label {
    font-size: 12px;
    color: #0E7490;
}

.legend {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-top: 20px;
}

.dark-theme .legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.light-theme .legend-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: black;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 2px;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.light-theme .report-title {
    color: black;
}

.light-theme .report-date {
    color: black;
}

.status-open,
.status-active { 
    background: rgba(39, 174, 96, 0.2);
    color: #27ae60;
}

.status-closed { 
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
}

.status-under-investigation,
.status-review { 
    background: #f8d7da;
    color: #721c24;
}

.status-court-action-pending {
    background: #fff3cd;
    color: #856404;
}

.priority-badge {
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
}

.priority-medium { background:rgb(187, 203, 238); color: rgb(5, 49, 143); }
.priority-low,
.priority-common { background: rgb(155, 235, 208); color: rgb(3, 151, 102); }
.priority-high,
.priority-urgent { background: #f8d7da; color: #721c24; }   
.priority-mild { background: #fbbf24; color: black; }

.total-records {
    font-size: 13px;
    color: #999;
    margin-top: 12px;
}

canvas {
    max-width: 100%;
}

/* Donation Report Specific Styles */
.source-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.dark-theme .source-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    background: #1a1a1a;
    border-radius: 6px;
    transition: background 0.2s;
}

.light-theme .source-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px;
    border-radius: 6px;
    transition: background 0.2s;
}

.dark-theme .source-item:hover {
    background: #2a2a2a;
}

.light-theme .source-item:hover {
    background: rgb(155, 235, 208);
}

.light-theme #donationReportTableBody td {
    color: black;
}

.source-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.source-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.dark-theme .source-name {
    font-size: 14px;
    font-weight: 500;
}

.light-theme .source-name {
    color: black;
    font-size: 14px;
    font-weight: 500;
}

.source-stats {
    display: flex;
    gap: 20px;
    align-items: center;
}

.dark-theme .source-type, .dark-theme .source-count {
    font-size: 13px;
    color: #999;
}

.light-theme .source-type, .light-theme .source-count {
    color: #0E7490;
}

.status-completed { 
    background: rgba(39, 174, 96, 0.2);
    color: #27ae60;
}

.status-ongoing { 
    background: #f8d7da;
    color: #721c24;
}

.status-payment,
.status-received { 
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.light-theme .stat-mini-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.light-theme .stat-mini-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #3b82f6, #8b5cf6);
}

.light-theme .stat-mini-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
}


/* Foster Info Specific Styles */
.stats-grid-mini {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 20px;
}

.stat-mini-card {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #3a3a3a;
}

.stat-mini-value {
    font-size: 28px;
    font-weight: bold;
    color: #3b82f6;
    margin-bottom: 8px;
}

.dark-theme .stat-mini-label {
    font-size: 12px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.light-theme .stat-mini-label {
    font-size: 12px;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stats-grid-mini {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .stats-grid, .chart-section, .dashboard-grid {
        grid-template-columns: 1fr;
    }

    table {
        font-size: 12px;
    }

    th, td {
        padding: 10px;
    }

    .search-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .search-input {
        min-width: auto;
    }
    .source-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .source-stats {
        align-self: flex-end;
    }
}

/* Schedule Reports Specific Styles */
.donut-chart-container {
    position: relative;
    width: 250px;
    height: 250px;
    margin: 0 auto;
}

.donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.donut-number {
    font-size: 32px;
    font-weight: bold;
    color: #fff;
}

.donut-label {
    font-size: 12px;
    color: #b8c5ff;
}

.bar-chart {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    height: 200px;
    gap: 10px;
    padding: 20px 0;
}

.bar-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    flex: 1;
}

.bar {
    width: 100%;
    background: #3498db;
    border-radius: 5px 5px 0 0;
    transition: all 0.3s;
    min-height: 10px;
}

.bar:hover {
    background: #5dade2;
    transform: scaleY(1.05);
}

.bar-label {
    font-size: 11px;
    color: #b8c5ff;
    text-transform: uppercase;
}

.light-theme .bar-label {
    color: #0E7490;
}

.bar-count {
    font-size: 10px;
    color: #b8c5ff;
    font-weight: 600;
}

.light-theme #fosterReportTableBody td {
    color: black;
}
.stats-grid-mini {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 16px;
    margin-top: 20px;
}

.stat-mini-card {
    background: #1a1a1a;
    padding: 20px;
    border-radius: 8px;
    text-align: center;
    border: 1px solid #3a3a3a;
}

.stat-mini-icon {
    font-size: 24px;
    margin-bottom: 8px;
}

.stat-mini-value {
    font-size: 28px;
    font-weight: bold;
    color: #3b82f6;
    margin-bottom: 8px;
}

.light-theme .stat-mini-value {
    font-size: 28px;
    font-weight: bold;
    color: #18338c;
    margin-bottom: 8px;
}


.stat-mini-label {
    font-size: 12px;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dark-theme .event-id {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: white;
}

.light-theme .event-id {
    font-family: 'Courier New', monospace;
    font-size: 12px;
    color: black;
}

.light-theme #scheduleReportTableBody td {
    color: black;
}

.event-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.event-icon {
    font-size: 16px;
}

.event-type-badge {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.status-scheduled {
    background: rgba(52, 152, 219, 0.2);
    color: #3498db;
}

.status-completed {
    background: rgba(39, 174, 96, 0.2);
    color: #27ae60;
}

.status-cancelled {
    background: rgba(231, 76, 60, 0.2);
    color: #e74c3c;
}

.donut-chart {
    position: relative;
    width: 280px;
    height: 280px;
    margin: 0 auto;
}

.donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.donut-number {
    font-size: 32px;
    font-weight: 700;
    color: #fff;
}

.donut-label {
    font-size: 12px;
    color: #b8c5ff;
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

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
// Store chart instances globally so we can destroy them later
let chartInstances = {
    inventoryChart: null,
    itemsChart: null,
    fosterStatusChart: null,
    donationDonutChart: null,
    donutChart: null,
    eventTypesDonutChart: null
};

// Get active tab from URL or default to 'child'
function getActiveTabFromURL() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('active_tab') || 'child';
}

// Set active tab in URL
function setActiveTabInURL(tabName) {
    const url = new URL(window.location);
    url.searchParams.set('active_tab', tabName);
    // Use replaceState to avoid adding to browser history
    window.history.replaceState({}, '', url);
}

// Report tab switching - FIXED VERSION
function switchReportTab(tabName, element) {
    console.log('Switching to tab:', tabName);
    
    // Remove active class from all tabs and content
    document.querySelectorAll('.tab').forEach(tab => {
        if (tab) tab.classList.remove('active');
    });
    
    document.querySelectorAll('.report-content').forEach(content => {
        if (content) content.classList.remove('active');
    });
    
    // Add active class to clicked tab
    if (element) {
        element.classList.add('active');
    }
    
    // Store active tab in URL
    setActiveTabInURL(tabName);
    
    // Find the correct content element
    const contentId = getContentIdForTab(tabName);
    const contentElement = document.getElementById(contentId);
    
    if (contentElement) {
        contentElement.classList.add('active');
        console.log('Successfully activated:', contentId);
        
        // Initialize charts when switching to specific tabs
        setTimeout(() => {
            if (tabName === 'inventory') {
                initializeInventoryCharts();
            } else if (tabName === 'foster') {
                initializeFosterCharts();
            } else if (tabName === 'schedule') {
                initializeEventTypesChart();
            } else if (tabName === 'case') {
                initializeCaseDonutChart();
            } else if (tabName === 'donation') {
                initializeDonationChart();
            }
        }, 100);
    } else {
        console.error('Content element not found for tab:', tabName, 'Looking for ID:', contentId);
    }
}

// Helper function to map tab names to content IDs
function getContentIdForTab(tabName) {
    const tabMap = {
        'child': 'childReport',
        'case': 'caseReport', 
        'donation': 'donationReport',
        'inventory': 'inventoryReport',
        'foster': 'fosterReport',
        'schedule': 'scheduleReport'
    };
    return tabMap[tabName] || (tabName + 'Report');
}

// Initialize active tab on page load
function initializeActiveTab() {
    const activeTabName = getActiveTabFromURL();
    console.log('Initializing active tab:', activeTabName);
    
    // Find the tab button for the active tab
    const tabs = document.querySelectorAll('.tab');
    let activeTabElement = null;
    
    tabs.forEach(tab => {
        const tabText = tab.textContent.trim().toLowerCase();
        if (tabText.includes(activeTabName)) {
            activeTabElement = tab;
        }
    });
    
    // If no specific tab found, use the first one (child management)
    if (!activeTabElement && tabs.length > 0) {
        activeTabElement = tabs[0];
    }
    
    // Switch to the active tab
    if (activeTabElement) {
        const tabName = getTabNameFromElement(activeTabElement);
        switchReportTab(tabName, activeTabElement);
    }
}

// Get tab name from tab element
function getTabNameFromElement(tabElement) {
    const tabText = tabElement.textContent.trim().toLowerCase();
    if (tabText.includes('child')) return 'child';
    if (tabText.includes('case')) return 'case';
    if (tabText.includes('donation')) return 'donation';
    if (tabText.includes('inventory')) return 'inventory';
    if (tabText.includes('foster')) return 'foster';
    if (tabText.includes('schedule')) return 'schedule';
    return 'child'; // default
}

// Search functionality for all reports
function initializeSearch() {
    const reportSearch = document.getElementById('reportSearch');
    if (reportSearch) {
        reportSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const activeReport = document.querySelector('.report-content.active');
            if (!activeReport) return;
            
            const activeTab = activeReport.id;
            let tableBodyId = '';
            
            // Map report IDs to table body IDs
            const tableMap = {
                'childReport': 'childReportTableBody',
                'caseReport': 'caseReportTableBody',
                'donationReport': 'donationReportTableBody',
                'inventoryReport': 'inventoryReportTableBody',
                'fosterReport': 'fosterReportTableBody',
                'scheduleReport': 'scheduleReportTableBody'
            };
            
            tableBodyId = tableMap[activeTab];
            
            if (tableBodyId) {
                const rows = document.querySelectorAll(`#${tableBodyId} tr`);
                let visibleCount = 0;
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    if (text.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Update showing count
                const paginationSpan = activeReport.querySelector('.pagination span');
                if (paginationSpan) {
                    const totalText = paginationSpan.textContent.match(/Total Records: (\d+)/);
                    if (totalText) {
                        paginationSpan.textContent = `Total Records: ${totalText[1]} (Showing: ${visibleCount})`;
                    }
                }
            }
        });
    }
}

// Export functionality
function exportReport() {
    const canExport = <?php echo $canExport ? 'true' : 'false'; ?>;
    if (!canExport) {
        alert('Permission denied - You cannot export reports');
        return;
    }
    
    const activeTab = document.querySelector('.tab.active');
    if (activeTab) {
        alert('Exporting ' + activeTab.textContent + ' report data...');
        // Add actual export logic here
    } else {
        alert('Exporting report data...');
    }
}

// Print functionality
function printReport() {
    const canPrint = <?php echo $canPrint ? 'true' : 'false'; ?>;
    if (!canPrint) {
        alert('Permission denied - You cannot print reports');
        return;
    }
    
    window.print();
}

// Filter functionality
function toggleFilters() {
    const filterPanel = document.getElementById('filterPanel');
    if (filterPanel) {
        if (filterPanel.style.display === 'none' || !filterPanel.style.display) {
            filterPanel.style.display = 'block';
        } else {
            filterPanel.style.display = 'none';
        }
    }
}

function toggleCustomDates() {
    const dateRange = document.getElementById('dateRange');
    const customDates = document.getElementById('customDates');
    
    if (dateRange && customDates) {
        if (dateRange.value === 'custom') {
            customDates.style.display = 'flex';
        } else {
            customDates.style.display = 'none';
        }
    }
}

function resetFilters() {
    // Get current active tab
    const activeTab = getActiveTabFromURL();
    
    // Create a new URL without filter parameters but keep the active tab
    const url = new URL(window.location);
    const params = new URLSearchParams();
    
    // Only keep essential parameters
    params.set('active_tab', activeTab);
    if (url.searchParams.get('report_type')) {
        params.set('report_type', url.searchParams.get('report_type'));
    }
    
    // Redirect to clean URL
    window.location.href = url.pathname + (params.toString() ? '?' + params.toString() : '');
}

// Update filter form to include active_tab
function updateFilterForm() {
    const filterForm = document.getElementById('filterForm');
    if (filterForm) {
        // Add hidden input for active tab
        let activeTabInput = filterForm.querySelector('input[name="active_tab"]');
        if (!activeTabInput) {
            activeTabInput = document.createElement('input');
            activeTabInput.type = 'hidden';
            activeTabInput.name = 'active_tab';
            filterForm.appendChild(activeTabInput);
        }
        
        // Update the value on form submit
        filterForm.addEventListener('submit', function() {
            const activeTab = getActiveTabFromURL();
            activeTabInput.value = activeTab;
        });
    }
}

// Close filters when clicking outside
document.addEventListener('click', function(event) {
    const filterPanel = document.getElementById('filterPanel');
    const filterBtn = document.querySelector('.btn-primary[onclick="toggleFilters()"]');
    
    if (filterPanel && filterPanel.style.display === 'block' && 
        !filterPanel.contains(event.target) && 
        event.target !== filterBtn && 
        (!filterBtn || !filterBtn.contains(event.target))) {
        filterPanel.style.display = 'none';
    }
});

// Handle filter form submission
function initializeFilterForm() {
    const filterForm = document.getElementById('filterForm');
    const activeTabInput = document.getElementById('activeTabInput');
    
    if (filterForm && activeTabInput) {
        filterForm.addEventListener('submit', function(e) {
            // Update the active tab value before submitting
            const currentActiveTab = getActiveTabFromURL();
            activeTabInput.value = currentActiveTab;
            console.log('Submitting filters for tab:', currentActiveTab);
        });
    }
}

// Destroy chart if it exists
function destroyChart(chartKey) {
    if (chartInstances[chartKey]) {
        chartInstances[chartKey].destroy();
        chartInstances[chartKey] = null;
    }
}

// Chart Initialization Functions (keep your existing chart functions, but I'll include key ones)
function initializeInventoryCharts() {
    destroyChart('inventoryChart');
    destroyChart('itemsChart');

    const inventoryCtx = document.getElementById('inventoryChart');
    if (inventoryCtx) {
        try {
            chartInstances.inventoryChart = new Chart(inventoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['In Stock', 'Low Stock', 'Out of Stock', 'Adequate Stock'],
                    datasets: [{
                        data: [
                            <?php echo $stockData['In Stock']; ?>,
                            <?php echo $stockData['Low Stock']; ?>,
                            <?php echo $stockData['Out of Stock']; ?>,
                            <?php echo $stockData['Adequate Stock']; ?>
                        ],
                        backgroundColor: ['#3b82f6', '#fbbf24', '#ef4444', '#10b981'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        } catch (error) {
            console.error('Error initializing inventory chart:', error);
        }
    }

    const itemsCtx = document.getElementById('itemsChart');
    if (itemsCtx) {
        try {
            const categories = <?php echo json_encode(array_column($itemsByCategory, 'category')); ?>;
            const counts = <?php echo json_encode(array_column($itemsByCategory, 'count')); ?>;
            
            chartInstances.itemsChart = new Chart(itemsCtx, {
                type: 'bar',
                data: {
                    labels: categories,
                    datasets: [{
                        label: 'Number of Items',
                        data: counts,
                        backgroundColor: '#3b82f6',
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true } }
                }
            });
        } catch (error) {
            console.error('Error initializing items chart:', error);
        }
    }
}

function initializeFosterCharts() {
    destroyChart('fosterStatusChart');

    const fosterStatusCtx = document.getElementById('fosterStatusChart');
    if (fosterStatusCtx) {
        try {
            const statusLabels = <?php echo json_encode(array_column($fosterStatus, 'status')); ?>;
            const statusCounts = <?php echo json_encode(array_column($fosterStatus, 'count')); ?>;
            
            chartInstances.fosterStatusChart = new Chart(fosterStatusCtx, {
                type: 'doughnut',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        data: statusCounts,
                        backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        } catch (error) {
            console.error('Error initializing foster status chart:', error);
        }
    }
}

function initializeDonationChart() {
    destroyChart('donationDonutChart');

    const ctx = document.getElementById('donationDonutChart');
    if (!ctx) return;
    
    const donationTypes = <?php echo json_encode($donationTypes); ?>;
    
    if (!donationTypes || donationTypes.length === 0) return;
    
    const labels = donationTypes.map(item => item.donation_type);
    const data = donationTypes.map(item => item.count);
    
    chartInstances.donationDonutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: ['#f87171', '#60a5fa', '#fbbf24', '#10b981', '#8b5cf6', '#06b6d4'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

function initializeCaseDonutChart() {
    destroyChart('donutChart');

    const ctx = document.getElementById('donutChart');
    if (!ctx) return;
    
    const caseStatus = <?php echo json_encode($caseStatus); ?>;
    if (!caseStatus || caseStatus.length === 0) return;
    
    const labels = caseStatus.map(item => item.status);
    const data = caseStatus.map(item => item.count);
    
    chartInstances.donutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: ['#60a5fa', '#10b981', '#fbbf24', '#f87171'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
}

function initializeEventTypesChart() {
    destroyChart('eventTypesDonutChart');

    const ctx = document.getElementById('eventTypesDonutChart');
    if (!ctx) return;
    
    const eventTypes = <?php echo json_encode($eventTypes); ?>;
    if (!eventTypes || eventTypes.length === 0) return;
    
    const labels = eventTypes.map(item => {
        const labelMap = {
            'home_visit': 'Home Visit', 'meeting': 'Meeting', 'team_building': 'Team Building',
            'staff_training': 'Staff Training', 'financial': 'Financial', 'orientation': 'Orientation',
            'calamity_duty': 'Calamity Duty'
        };
        return labelMap[item.event_type] || item.event_type;
    });
    
    const data = eventTypes.map(item => item.count);
    
    chartInstances.eventTypesDonutChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: ['#3498db', '#2ecc71', '#1abc9c', '#9b59b6', '#e74c3c', '#f39c12', '#e67e22'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
}

// Pagination function
function changePage(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    // Preserve active tab
    const activeTab = getActiveTabFromURL();
    url.searchParams.set('active_tab', activeTab);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing reports page...');
    
    // Initialize filters
    toggleCustomDates();
    initializeFilterForm(); // Add this line
    
    // Initialize active tab
    initializeActiveTab();
    
    // Initialize search
    initializeSearch();
    
    // Add click handlers to tabs
    document.querySelectorAll('.tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = getTabNameFromElement(this);
            switchReportTab(tabName, this);
        });
    });
});

// Clean up charts when leaving the page
window.addEventListener('beforeunload', function() {
    Object.keys(chartInstances).forEach(key => {
        destroyChart(key);
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>