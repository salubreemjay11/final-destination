<?php
$pageTitle = 'Foster Information Management - Orphanfare';
require_once 'includes/header.php';

// Handle search and filters
$search = $_GET['search'] ?? '';
$adopterType = $_GET['adopter_type'] ?? '';
$location = $_GET['location'] ?? '';
$status = $_GET['status'] ?? '';

// Build query with filters
$query = "SELECT * FROM foster_parents WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR contact_number LIKE ? OR address LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($adopterType) && $adopterType !== 'All Adopters') {
    $query .= " AND adopter_type = ?";
    $params[] = $adopterType;
}

if (!empty($location) && $location !== 'All locations') {
    $query .= " AND address LIKE ?";
    $params[] = "%$location%";
}

if (!empty($status) && $status !== 'All Status') {
    $query .= " AND status = ?";
    $params[] = $status;
}

$query .= " ORDER BY created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $fosterParents = $stmt->fetchAll();
    
    $totalFosters = count($fosterParents);
    
} catch (Exception $e) {
    error_log("Foster info error: " . $e->getMessage());
    $fosterParents = [];
    $totalFosters = 0;
}

// Check permissions for display
$canCreate = $permissionManager->hasPermission('foster_info', 'create');
$canEdit = $permissionManager->hasPermission('foster_info', 'edit');
$canDelete = $permissionManager->hasPermission('foster_info', 'delete');

// Handle success/error messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

// Helper function for status badges
function getStatusBadgeClass($status) {
    switch($status) {
        case 'Active': return 'status-active';
        case 'Approved': return 'status-approved';
        case 'Pending': return 'status-pending';
        case 'Inactive': return 'status-rejected';
        case 'Rejected': return 'status-rejected';
        default: return 'status-pending';
    }
}

?>

<main class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <h1 class="page-title">Foster List</h1>
        <?php if ($canCreate): ?>
            <button class="btn btn-primary" onclick="window.location.href='new-foster.php'">Add New Foster</button>
        <?php else: ?>
            <button class="btn btn-secondary" disabled title="No permission to add foster parents">Add New Foster</button>
        <?php endif; ?>
    </div>

    <!-- Show read-only banner if no edit permission -->
    <?php if (!$canEdit): ?>
    <div class="read-only-banner" style="background-color: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 12px 15px; border-radius: 6px; margin-bottom: 20px; font-size: 14px;">
        <strong>🔒 Read-Only Mode:</strong> You have view-only access to foster information. You cannot make any changes.
    </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <?php 
            switch($success) {
                case 'foster_added':
                    echo "Foster parent added successfully!";
                    break;
                case 'foster_updated':
                    echo "Foster parent updated successfully!";
                    break;
                case 'foster_deleted':
                    echo "Foster parent deleted successfully!";
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
                    echo "Failed to add foster parent. Please try again.";
                    break;
                case 'update_failed':
                    echo "Failed to update foster parent. Please try again.";
                    break;
                case 'delete_failed':
                    echo "Failed to delete foster parent. Please try again.";
                    break;
                default:
                    echo "An error occurred. Please try again.";
            }
            ?>
        </div>
    <?php endif; ?>

    <!-- Search and Filters -->
    <form method="GET" action="foster-info.php" class="search-container">
        <input style="width: 100%;" type="text" name="search" class="search-input" 
               placeholder="Search by name, email, phone, etc..." 
               value="<?php echo htmlspecialchars($search); ?>">
        
        <div class="filters-row">
            <select class="filter-select" name="adopter_type">
                <option value="">All Adopters</option>
                <option value="Single Parents" <?php echo $adopterType === 'Single Parents' ? 'selected' : ''; ?>>Single Parents</option>
                <option value="Married Couples" <?php echo $adopterType === 'Married Couples' ? 'selected' : ''; ?>>Married Couples</option>
                <option value="Extended Family" <?php echo $adopterType === 'Extended Family' ? 'selected' : ''; ?>>Extended Family</option>
            </select>
            
            <select class="filter-select" name="location">
                <option value="">All locations</option>
                <option value="Manila" <?php echo $location === 'Manila' ? 'selected' : ''; ?>>Manila</option>
                <option value="Quezon City" <?php echo $location === 'Quezon City' ? 'selected' : ''; ?>>Quezon City</option>
                <option value="Makati" <?php echo $location === 'Makati' ? 'selected' : ''; ?>>Makati</option>
                <option value="Pasig" <?php echo $location === 'Pasig' ? 'selected' : ''; ?>>Pasig</option>
            </select>
            
            <select class="filter-select" name="status">
                <option value="">All Status</option>
                <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Approved" <?php echo $status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
                <option value="Rejected" <?php echo $status === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            
            <button type="submit" class="search-btn">Search</button>
            <button type="button" class="btn-cancel" onclick="window.location.href='foster-info.php'">Clear Filters</button>
        </div>
    </form>

    <!-- Foster Registry -->
    <div class="registry-section">
        <div class="registry-header">
            <h2 class="registry-title">Foster Registry</h2>
            <span class="total-count">Total: <?php echo $totalFosters; ?> foster<?php echo $totalFosters !== 1 ? 's' : ''; ?></span>
        </div>
        
        <?php if (empty($fosterParents)): ?>
            <div class="no-items" style="text-align: center; padding: 40px; color: #888;">
                <p>No foster parents found.</p>
                <button class="btn btn-primary" onclick="window.location.href='new-foster.php'">Add Your First Foster Parent</button>
            </div>
        <?php else: ?>
            <table class="foster-table">
                <thead>
                    <tr>
                        <th>Foster ID</th>
                        <th>Name</th>
                        <th>Age</th>
                        <th>Contact Info</th>
                        <th>Location</th>
                        <th>Application Date</th>
                        <th>Status</th>
                        <th>Capacity</th>
                        <th>Current Children</th>
                        <th>Notes</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($fosterParents as $foster): ?>
                    <tr>
                        <td class="foster-id clickable-id" 
                            <?php if ($canEdit): ?>onclick="showFosterDetails('<?php echo htmlspecialchars($foster['foster_id']); ?>')"<?php else: ?>style="cursor: not-allowed; color: #cccccc;"<?php endif; ?>>
                            <?php echo htmlspecialchars($foster['foster_id'] ?? 'N/A'); ?>
                        </td>
                        <td>
                            <a href="foster-details.php?foster_id=<?php echo $foster['foster_id']; ?>" class="clickable-name">
                                <?php echo htmlspecialchars($foster['name'] ?? 'Unknown'); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($foster['age'] ?? 'N/A'); ?></td>
                        <td class="contact-info">
                            <?php echo htmlspecialchars($foster['contact_number'] ?? 'N/A'); ?>
                        </td>
                        <td>
                            <?php 
                            // Extract city from address for display
                            $address = $foster['address'] ?? '';
                            $cities = ['Manila', 'Quezon City', 'Makati', 'Pasig'];
                            $displayLocation = 'Other';
                            foreach ($cities as $city) {
                                if (stripos($address, $city) !== false) {
                                    $displayLocation = $city;
                                    break;
                                }
                            }
                            echo htmlspecialchars($displayLocation);
                            ?>
                        </td>
                        <td><?php echo formatDate($foster['created_at'] ?? ''); ?></td>
                        <td>
                            <?php 
                            $statusClass = 'status-pending';
                            switch($foster['status'] ?? 'Pending') {
                                case 'Approved':
                                    $statusClass = 'status-approved';
                                    break;
                                case 'Active':
                                    $statusClass = 'status-active';
                                    break;
                                case 'Rejected':
                                    $statusClass = 'status-rejected';
                                    break;
                                default:
                                    $statusClass = 'status-pending';
                            }
                            ?>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo htmlspecialchars($foster['status'] ?? 'Pending'); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($foster['capacity'] ?? 1); ?></td>
                        <td><?php echo htmlspecialchars($foster['current_children'] ?? 0); ?></td>
                        <td class="notes-cell" title="<?php echo htmlspecialchars($foster['notes'] ?? ''); ?>">
                            <?php 
                            $notes = $foster['notes'] ?? '';
                            if (strlen($notes) > 50) {
                                echo htmlspecialchars(substr($notes, 0, 50)) . '...';
                            } else {
                                echo htmlspecialchars($notes);
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($canEdit): ?>
                                <div class="action-buttons" style="display: flex; gap: 5px;">
                                    <button class="btn-small btn-update" 
                                            onclick="window.location.href='foster-details.php?foster_id=<?php echo $foster['foster_id']; ?>'">
                                        Edit
                                    </button>
                                    <?php if ($canDelete): ?>
                                    <button class="btn-small btn-delete" 
                                            onclick="deleteFoster('<?php echo $foster['foster_id']; ?>', '<?php echo htmlspecialchars($foster['name'] ?? 'Unknown'); ?>')">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
                                            <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
                                            <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
                                            </svg>     
                                    </button>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span style="color: #888; font-style: italic;">Read-only</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</main>

<!-- Foster Details Modal -->
<div class="modal-overlay" id="fosterModal" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Loading Indicator -->
            <div id="modalLoading" class="loading-indicator" style="display: none;">
                <div class="loading-spinner"></div>
                <div class="loading-text">Loading foster information...</div>
            </div>

            <div id="modalContent">
                <div class="foster-profile">
                    <div class="foster-basic-info">
                        <h2 id="fosterName">Loading...</h2>
                        <p id="fosterId">ID: Loading...</p>
                        <div class="foster-meta">
                            <span>Age: <span id="fosterAge">-</span></span>
                            <span id="fosterGender">-</span>
                            <span id="fosterStatus">-</span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-tabs">
                    <button class="tab-btn active" onclick="switchTab('basic')">Basic info</button>
                    <button class="tab-btn" onclick="switchTab('family')">Family Details</button>
                    <button class="tab-btn" onclick="switchTab('assessment')">Assessment</button>
                    <button class="tab-btn" onclick="switchTab('preferences')">Preferences</button>
                </div>
                
                <div class="tab-content">
                    <div id="basicTab" class="tab-pane active">
                        <div class="info-row">
                            <span class="info-label">Contact Number</span>
                            <span class="info-value" id="contactNumber"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email</span>
                            <span class="info-value" id="fosterEmail"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Address</span>
                            <span class="info-value" id="fosterAddress"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Occupation</span>
                            <span class="info-value" id="fosterOccupation"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Monthly Income</span>
                            <span class="info-value" id="monthlyIncome"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Civil Status</span>
                            <span class="info-value" id="civilStatus"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Educational Attainment</span>
                            <span class="info-value" id="educationalAttainment"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Religion</span>
                            <span class="info-value" id="fosterReligion"></span>
                        </div>
                    </div>

                    <div id="familyTab" class="tab-pane">
                        <div class="info-section">
                            <h4>Family Planning</h4>
                            <div class="info-content" id="familyPlanning"></div>
                        </div>
                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Adoption Awareness</h4>
                            <div class="info-content" id="adoptionAwareness"></div>
                        </div>
                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Parenting Approach</h4>
                            <div class="info-content" id="parentingApproach"></div>
                        </div>
                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Family Composition</h4>
                            <div class="table-container">
                                <table id="familyCompositionTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Relationship</th>
                                            <th>Age</th>
                                            <th>Gender</th>
                                            <th>Civil Status</th>
                                            <th>Education</th>
                                            <th>Occupation/Income</th>
                                        </tr>
                                    </thead>
                                    <tbody id="familyCompositionBody">
                                        <!-- Family members will be populated here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div id="assessmentTab" class="tab-pane">
                        <div class="info-row">
                            <span class="info-label">Assessment Date</span>
                            <span class="info-value" id="assessmentDate"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Social Worker</span>
                            <span class="info-value" id="socialWorker"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Psychological Evaluation</span>
                            <span class="info-value" id="psychologicalEvaluation"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Overall Assessment</span>
                            <span class="info-value" id="overallAssessment"></span>
                        </div>
                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Problem Presented</h4>
                            <div class="info-content" id="problemPresented"></div>
                        </div>
                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Assessment & Recommendation</h4>
                            <div class="info-content" id="assessmentRecommendation"></div>
                        </div>
                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Psychologist's Notes</h4>
                            <div class="info-content" id="psychologistNotes"></div>
                        </div>
                    </div>
                    
                    <div id="preferencesTab" class="tab-pane">
                        <div class="info-row">
                            <span class="info-label">Age Preference</span>
                            <span class="info-value" id="agePreference"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Gender Preference</span>
                            <span class="info-value" id="genderPreference"></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Experience Level</span>
                            <span class="info-value" id="experienceLevel"></span>
                        </div>
                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Interests</h4>
                            <div class="info-content" id="fosterInterests"></div>
                        </div>
                        <div class="info-section" style="margin-top: 20px;">
                            <h4>Personality Traits</h4>
                            <div class="info-content" id="personalityTraits"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <!-- Edit Button - Only show if user has edit permission -->
                    <?php if ($canEdit): ?>
                        <button class="edit-btn" onclick="editFoster()">Edit</button>
                    <?php else: ?>
                        <button class="edit-btn" disabled style="opacity: 0.6; cursor: not-allowed;" title="Read-only mode">Edit</button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteFosterModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Delete Foster Parent</h3>
            <button class="modal-close" onclick="hideDeleteFosterModal()">×</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete this foster parent? This action cannot be undone.</p>
            <div class="delete-item-info" id="deleteFosterInfo"></div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="hideDeleteFosterModal()">Cancel</button>
                <button type="button" class="btn-submit btn-danger" onclick="confirmDeleteFoster()">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
.light-theme .foster-id {
    color: #1e40af;
    cursor: pointer;
    text-decoration: underline;
}
.dark-theme .btn-update{
    padding: 6px 12px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: background-color 0.2s;
}

.light-theme .btn-update {
    padding: 6px 12px;
    background: #3b82f6;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: background-color 0.2s;
}

.dark-theme .btn-delete {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.2s;
}

.btn-update:hover{
    background-color: #047857;
}

.light-theme .btn-delete {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 12px;
    transition: background-color 0.2s;
}

.btn-delete:hover {
    background-color: #c82333;
}

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

.foster-profile {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    align-items: center;
}

.foster-basic-info h2 {
    color: #ffffff;
    margin: 0 0 5px 0;
    font-size: 24px;
}

.foster-basic-info p {
    color: #b8c5ff;
    margin: 0 0 5px 0;
}

.foster-meta {
    display: flex;
    gap: 15px;
    color: #cccccc;
    font-size: 14px;
}

.modal-tabs {
    display: flex;
    border-bottom: 1px solid #3a3a3a;
    margin-bottom: 20px;
}

.tab-btn {
    background: none;
    border: none;
    color: #cccccc;
    padding: 10px 20px;
    cursor: pointer;
    border-bottom: 2px solid transparent;
}

.tab-btn.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
}

.tab-pane {
    display: none;
}

.tab-pane.active {
    display: block;
}

.info-section {
    margin-bottom: 20px;
}

.info-section h4 {
    color: #3b82f6;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 1px solid #3a3a3a;
}

.info-content {
    background: #1a1a1a;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #3a3a3a;
    color: #cccccc;
    line-height: 1.5;
}

#familyCompositionTable {
    width: 100%;
    border-collapse: collapse;
    background: #1a1a1a;
}

#familyCompositionTable th {
    background: #333333;
    color: #b8c5ff;
    padding: 10px 8px;
    text-align: left;
    font-size: 12px;
    text-transform: uppercase;
    border: 1px solid #3a3a3a;
}

#familyCompositionTable td {
    padding: 8px;
    border: 1px solid #3a3a3a;
    color: #cccccc;
    font-size: 13px;
}

.modal-actions {
    margin-top: 20px;
    text-align: right;
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

/* Update existing styles for clickable IDs */
.clickable-id:hover {
    text-decoration: underline;
}
</style>

<script>
let currentFosterId = null;

// Get PHP permissions from the page
const canCreate = <?php echo $canCreate ? 'true' : 'false'; ?>;
const canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
const canDelete = <?php echo $canDelete ? 'true' : 'false'; ?>;

function showFosterDetails(fosterId) {
    if (!canEdit) {
        showNotification('Read-only mode - You cannot view foster details', 'error');
        return;
    }
    
    // Simply redirect to the foster details page for viewing
    window.location.href = 'foster-details.php?foster_id=' + fosterId + '&view=1';
}

function debugAjaxResponse(response) {
    response.text().then(text => {
        console.log('Raw response:', text);
        // Check if it's HTML error
        if (text.includes('<br />') || text.includes('<b>')) {
            console.error('PHP Error detected in response');
            // Try to extract error message
            const errorMatch = text.match(/<b>(.*?)<\/b>/);
            if (errorMatch) {
                console.error('PHP Error:', errorMatch[1]);
            }
        }
    });
}   

function populateModal(foster) {
    console.log('Foster data received:', foster);
    
    try {
        // Update basic info
        document.getElementById('fosterName').textContent = foster.name || 'Unknown';
        document.getElementById('fosterId').textContent = 'ID: ' + (foster.foster_id || 'Unknown');
        document.getElementById('fosterAge').textContent = foster.age || 'Unknown';
        document.getElementById('fosterGender').textContent = foster.gender || 'Unknown';
        document.getElementById('fosterStatus').textContent = foster.status || 'Unknown';
        
        // Basic info tab
        document.getElementById('contactNumber').textContent = foster.contact_number || 'Not specified';
        document.getElementById('fosterEmail').textContent = foster.email || 'Not specified';
        document.getElementById('fosterAddress').textContent = foster.address || 'Not specified';
        document.getElementById('fosterOccupation').textContent = foster.occupation || 'Not specified';
        document.getElementById('monthlyIncome').textContent = foster.monthly_income || 'Not specified';
        document.getElementById('civilStatus').textContent = foster.civil_status || 'Not specified';
        document.getElementById('educationalAttainment').textContent = foster.educational_attainment || 'Not specified';
        document.getElementById('fosterReligion').textContent = foster.religion || 'Not specified';
        
        // Family tab
        document.getElementById('familyPlanning').textContent = foster.family_planning || 'No information provided';
        document.getElementById('adoptionAwareness').textContent = foster.adoption_awareness || 'No information provided';
        document.getElementById('parentingApproach').textContent = foster.parenting_approach || 'No information provided';
        
        // Populate family composition table
        const familyCompositionBody = document.getElementById('familyCompositionBody');
        if (familyCompositionBody) {
            familyCompositionBody.innerHTML = '';
            
            if (foster.family_composition) {
                try {
                    const familyMembers = JSON.parse(foster.family_composition);
                    if (familyMembers && familyMembers.length > 0) {
                        familyMembers.forEach(member => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${member.name || ''}</td>
                                <td>${member.relationship || ''}</td>
                                <td>${member.age || ''}</td>
                                <td>${member.gender || ''}</td>
                                <td>${member.civil_status || ''}</td>
                                <td>${member.education || ''}</td>
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
        
        // Assessment tab
        document.getElementById('assessmentDate').textContent = foster.assessment_date || 'Not specified';
        document.getElementById('socialWorker').textContent = foster.social_worker_name || 'Not specified';
        document.getElementById('psychologicalEvaluation').textContent = foster.psychological_evaluation || 'Not specified';
        document.getElementById('overallAssessment').textContent = foster.overall_assessment || 'Not specified';
        document.getElementById('problemPresented').textContent = foster.problem_presented || 'No information provided';
        document.getElementById('assessmentRecommendation').textContent = foster.assessment_recommendation || 'No assessment provided';
        document.getElementById('psychologistNotes').textContent = foster.psychologist_notes || 'No notes provided';
        
        // Preferences tab
        document.getElementById('agePreference').textContent = foster.age_preference || 'No preference';
        document.getElementById('genderPreference').textContent = foster.gender_preference || 'No preference';
        document.getElementById('experienceLevel').textContent = foster.experience_level || 'Not specified';
        document.getElementById('fosterInterests').textContent = foster.interests || 'No interests specified';
        document.getElementById('personalityTraits').textContent = foster.personality_traits || 'No traits specified';
        
    } catch (error) {
        console.error('Error populating modal:', error);
        showNotification('Error loading foster details', 'error');
    }
}

function editFoster() {
    if (!canEdit) {
        showNotification('Permission denied - You cannot edit foster parents', 'error');
        return;
    }
    
    if (!currentFosterId) {
        showNotification('Error: No foster data available', 'error');
        return;
    }
    
    // Close modal and redirect to edit page
    closeModal();
    window.location.href = 'foster-details.php?foster_id=' + currentFosterId;
}

function closeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('fosterModal').classList.remove('active');
    currentFosterId = null;
}

function switchTab(tabName) {
    // Remove active class from all tabs and panes
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
    
    // Add active class to clicked tab and corresponding pane
    event.target.classList.add('active');
    document.getElementById(tabName + 'Tab').classList.add('active');
}

// Delete functions (existing from your code)
function deleteFoster(fosterId, fosterName) {
    if (!canDelete) {
        alert('Permission denied - You cannot delete foster parents');
        return;
    }
    
    currentFosterId = fosterId;
    
    const infoDiv = document.getElementById('deleteFosterInfo');
    infoDiv.innerHTML = `
        <div class="delete-item-details">
            <strong>Foster Name:</strong> ${fosterName}<br>
            <strong>Foster ID:</strong> ${fosterId}
        </div>
    `;
    
    document.getElementById('deleteFosterModal').classList.add('active');
}

function hideDeleteFosterModal() {
    document.getElementById('deleteFosterModal').classList.remove('active');
    currentFosterId = null;
}

function confirmDeleteFoster() {
    if (!currentFosterId) return;
    
    fetch('process-foster.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=delete_foster&foster_id=${currentFosterId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'foster-info.php?success=foster_deleted';
        } else {
            alert('Failed to delete foster parent: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting foster parent');
    });
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

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('fosterModal');
    if (event.target === modal) {
        closeModal();
    }
});

// Make functions globally available
window.showFosterDetails = showFosterDetails;
window.switchTab = switchTab;
window.editFoster = editFoster;
window.closeModal = closeModal;
window.showNotification = showNotification;
</script>

<?php require_once 'includes/footer.php'; ?>