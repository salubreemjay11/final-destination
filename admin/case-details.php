<?php
$pageTitle = 'Case Details - Orphanfare';
require_once 'includes/header.php';

// Get case ID from URL
$caseId = $_GET['case_id'] ?? null;
$caseData = null;
$childData = null;

function getSocialWorkerName($socialWorkerId) {
    $socialWorkers = [
        'maria-santos' => 'Maria Santos',
        'juan-cruz' => 'Juan Cruz', 
        'lisa-gonzalez' => 'Lisa Gonzalez',
        'carlos-reyes' => 'Carlos Reyes'
    ];
    return $socialWorkers[$socialWorkerId] ?? 'Not assigned';
}

if ($caseId) {
    try {
        // Get case data
        $stmt = $pdo->prepare("SELECT * FROM cases WHERE case_id = ?");
        $stmt->execute([$caseId]);
        $caseData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($caseData) {
            // Get child data
            $childId = $caseData['linked_child_id'] ?? $caseId; // Use case_id if unified
            $stmt = $pdo->prepare("SELECT * FROM children WHERE child_id = ?");
            $stmt->execute([$childId]);
            $childData = $stmt->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        error_log("Error loading case details: " . $e->getMessage());
        $error = "Error loading case details: " . $e->getMessage();
    }
} else {
    $error = "No case ID provided";
}
?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Case Details</h1>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="window.location.href='case-management.php'">← Back to Cases</button>
            <?php if ($caseData): ?>
            <button class="btn btn-primary" onclick="window.location.href='unified-registration.php?case_id=<?php echo $caseId; ?>&mode=view'">Edit Record</button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!$caseData): ?>
        <div class="alert alert-warning">
            Case not found or no case ID provided.
        </div>
    <?php else: ?>
        <div class="case-details-container">
            <!-- Case Header -->
            <div class="case-header">
                <div class="case-id-badge">
                    Case ID: <?php echo htmlspecialchars($caseData['case_id']); ?>
                </div>
                <div class="case-status-badge status-<?php echo strtolower(str_replace(' ', '-', $caseData['status'])); ?>">
                    <?php echo htmlspecialchars($caseData['status']); ?>
                </div>
            </div>

            <!-- Child Information Section -->
            <div class="details-section">
                <h2 class="section-title">Child Information</h2>
                <div class="details-grid">
                    <?php if ($childData): ?>
                        <div class="detail-item">
                            <label>Child ID:</label>
                            <span><?php echo htmlspecialchars($childData['child_id']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Age:</label>
                            <span><?php echo htmlspecialchars($childData['age']); ?> years old</span>
                        </div>
                        <div class="detail-item">
                            <label>Gender:</label>
                            <span><?php echo htmlspecialchars($childData['gender']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Date of Birth:</label>
                            <span><?php echo $childData['date_of_birth'] ? formatDate($childData['date_of_birth']) : 'Not specified'; ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Entry Date:</label>
                            <span><?php echo formatDate($childData['entry_date']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Status:</label>
                            <span class="status-badge <?php echo getStatusBadgeClass($childData['status']); ?>">
                                <?php echo htmlspecialchars($childData['status']); ?>
                            </span>
                        </div>
                        <div class="detail-item full-width">
                            <label>Address:</label>
                            <span><?php echo htmlspecialchars($childData['address'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Health Status:</label>
                            <span><?php echo htmlspecialchars($childData['health_status'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Allergies:</label>
                            <span><?php echo htmlspecialchars($childData['allergies'] ?? 'None reported'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Emergency Contact:</label>
                            <span><?php echo htmlspecialchars($childData['emergency_contact'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Contact Phone:</label>
                            <span><?php echo htmlspecialchars($childData['contact_phone'] ?? 'Not specified'); ?></span>
                        </div>
                        <div class="detail-item full-width">
                            <label>Problem Description:</label>
                            <div class="detail-content"><?php echo htmlspecialchars($childData['problem_description'] ?? 'No description provided'); ?></div>
                        </div>
                        <div class="detail-item full-width">
                            <label>Additional Notes:</label>
                            <div class="detail-content"><?php echo htmlspecialchars($childData['notes'] ?? 'No additional notes'); ?></div>
                        </div>
                    <?php else: ?>
                        <div class="detail-item full-width">
                            <span class="text-muted">No child information linked to this case.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Case Information Section -->
            <div class="details-section">
                <h2 class="section-title">Case Information</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <label>Case Type:</label>
                        <span><?php echo htmlspecialchars($caseData['case_type']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Priority:</label>
                        <span class="priority-badge priority-<?php echo htmlspecialchars($caseData['priority']); ?>">
                            <?php echo ucfirst(htmlspecialchars($caseData['priority'])); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Created Date:</label>
                        <span><?php echo formatDate($caseData['created_date']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Expected Date:</label>
                        <span><?php echo formatDate($caseData['expected_date']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Assigned Social Worker:</label>
                        <span><?php echo htmlspecialchars(getSocialWorkerName($caseData['social_worker'] ?? '')); ?></span>
                    </div>
                    <div class="detail-item full-width">
                        <label>Case Description:</label>
                        <div class="detail-content"><?php echo htmlspecialchars($caseData['description']); ?></div>
                    </div>
                </div>
            </div>

            <!-- Reporter Information Section -->
            <div class="details-section">
                <h2 class="section-title">Reporter Information</h2>
                <div class="details-grid">
                    <div class="detail-item">
                        <label>Reported By:</label>
                        <span><?php echo htmlspecialchars($caseData['reported_by']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Relation to Child:</label>
                        <span><?php echo htmlspecialchars($caseData['reporter_relation'] ?? 'Not specified'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Reporter Phone:</label>
                        <span><?php echo htmlspecialchars($caseData['reporter_phone'] ?? 'Not specified'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Reporter Email:</label>
                        <span><?php echo htmlspecialchars($caseData['reporter_email'] ?? 'Not specified'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn btn-secondary" onclick="window.location.href='case-management.php'">Back to Cases</button>
                <button class="btn btn-primary" onclick="window.location.href='unified-registration.php?case_id=<?php echo $caseId; ?>&mode=view'">Edit Record</button>
                
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
.case-details-container {
    background: #2a2a2a;
    border-radius: 12px;
    padding: 24px;
    border: 1px solid #3a3a3a;
}

.case-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #3a3a3a;
}

.case-id-badge {
    background: #3b82f6;
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.case-status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 14px;
}

.status-open { background: #d4edda; color: #155724; }
.status-under-investigation { background: #fff3cd; color: #856404; }
.status-court-action-pending { background: #f8d7da; color: #721c24; }
.status-closed { background: #e2e3e5; color: #383d41; }

.details-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #3a3a3a;
}

.details-section:last-child {
    border-bottom: none;
}

.section-title {
    color: #ffffff;
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-item label {
    color: #b8c5ff;
    font-weight: 500;
    font-size: 14px;
}

.detail-item span {
    color: #ffffff;
    font-size: 16px;
}

.detail-content {
    background: #1a1a1a;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #3a3a3a;
    color: #cccccc;
    line-height: 1.5;
}

.priority-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.priority-urgent { background: #f8d7da; color: #721c24; }
.priority-high { background: #fff3cd; color: #856404; }
.priority-medium { background: #d1ecf1; color: #0c5460; }
.priority-low { background: #d4edda; color: #155724; }
.priority-mild { background: #e2e3e5; color: #383d41; }
.priority-common { background: #d1ecf1; color: #0c5460; }

.header-actions {
    display: flex;
    gap: 12px;
}

.action-buttons {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #3a3a3a;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-warning:hover {
    background: #e0a800;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
}

.btn-danger:hover {
    background: #c82333;
}

.text-muted {
    color: #888;
    font-style: italic;
}
</style>

<script>
function closeCase(caseId) {
    if (confirm('Are you sure you want to close this case?')) {
        fetch('case-management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=close_case&case_id=' + encodeURIComponent(caseId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error closing case');
        });
    }
}

function escalateCase(caseId) {
    if (confirm('Are you sure you want to escalate this case to court action?')) {
        fetch('case-management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=escalate_case&case_id=' + encodeURIComponent(caseId)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error escalating case');
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>