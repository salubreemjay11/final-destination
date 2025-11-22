[file name]: case-info.php
[file content begin]
<?php
    $pageTitle = 'Case Information - Orphanfare';
    require_once 'includes/header.php';

    // Load Custom Field Manager for cases
    $fieldManager = null;
    $caseCustomFields = [];
    $existingCaseCustomValues = [];

    try {
        if (file_exists('../superadmin/includes/CustomFieldManager.php')) {
            require_once '../superadmin/includes/CustomFieldManager.php';
        } elseif (file_exists('includes/CustomFieldManager.php')) {
            require_once 'includes/CustomFieldManager.php';
        } else {
            throw new Exception('CustomFieldManager.php not found');
        }
        
        $fieldManager = new CustomFieldManager($pdo);
        $caseCustomFields = $fieldManager->getModuleFields('cases');
        
    } catch (Exception $e) {
        error_log("Custom Field Manager Error: " . $e->getMessage());
        $customFieldsError = "Custom fields are temporarily unavailable. Please contact administrator.";
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

    if (!function_exists('formatDate')) {
        function formatDate($date) {
            if (!$date || $date === '0000-00-00') return 'Not set';
            return date('M j, Y', strtotime($date));
        }
    }

    if (!function_exists('logActivity')) {
        function logActivity($userId, $action, $table, $recordId) {
            return true;
        }
    }

    // Get case ID from URL parameter
    $caseId = $_GET['case_id'] ?? null;

    if (!$caseId) {
        // Check if we have a stored case ID
        if (isset($_SESSION['selectedCaseId']) && !empty($_SESSION['selectedCaseId'])) {
            $caseId = $_SESSION['selectedCaseId'];
        } else {
            // Redirect back to case management if no case ID
            header('Location: case-management.php');
            exit();
        }
    } else {
        // Store the case ID in session for future use
        $_SESSION['selectedCaseId'] = $caseId;
    }

    // DEBUG: Log the case ID we're trying to load
    error_log("DEBUG: Loading case with ID: " . $caseId);

    // Load existing custom field values for this case AFTER we have the caseId
    if ($caseId && $fieldManager) {
        $existingCaseCustomValues = $fieldManager->getFieldValues($caseId, 'cases');
        
        // DEBUG: Log what we found
        error_log("DEBUG: Loading custom fields for case: " . $caseId);
        error_log("DEBUG: Custom fields found: " . count($caseCustomFields));
        error_log("DEBUG: Existing values found: " . count($existingCaseCustomValues));
        foreach ($existingCaseCustomValues as $key => $value) {
            error_log("DEBUG: Field '$key' = '$value'");
        }
    }

    // Get case information with better error handling
    try {
        $stmt = $pdo->prepare("SELECT * FROM cases WHERE case_id = ?");
        $stmt->execute([$caseId]);
        $case = $stmt->fetch();

        // DEBUG: Log what we found in the case
        error_log("DEBUG: Case query executed");
        if ($case) {
            error_log("DEBUG: Case found - ID: " . $case['case_id']);
            error_log("DEBUG: Case data: " . print_r($case, true));
        } else {
            error_log("DEBUG: No case found with ID: " . $caseId);
        }

    } catch (Exception $e) {
        error_log("ERROR: Failed to load case: " . $e->getMessage());
        $case = null;
    }

    if (!$case) {
        echo "<div style='background: #dc3545; color: white; padding: 20px; border-radius: 6px; margin: 20px; text-align: center;'>";
        echo "<h3>Case Not Found</h3>";
        echo "<p>Case with ID <strong>" . htmlspecialchars($caseId) . "</strong> was not found in the system.</p>";
        echo "<button class='btn btn-primary' onclick='window.location.href=\"case-management.php\"'>Back to Cases</button>";
        echo "</div>";
        require_once 'includes/footer.php';
        exit();
    }

    // Get legal actions
    $legalActions = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM legal_actions WHERE case_id = ? ORDER BY date DESC");
        $stmt->execute([$caseId]);
        $legalActions = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("ERROR: Failed to load legal actions: " . $e->getMessage());
    }

    // Get social services
    $socialServices = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM social_services WHERE case_id = ? ORDER BY date_started DESC");
        $stmt->execute([$caseId]);
        $socialServices = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("ERROR: Failed to load social services: " . $e->getMessage());
    }

    // Get documents
    $documents = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM documents WHERE case_id = ? ORDER BY date_uploaded DESC");
        $stmt->execute([$caseId]);
        $documents = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("ERROR: Failed to load documents: " . $e->getMessage());
    }

    // Get evidence photos
    $evidencePhotos = [];
    try {
        $stmt = $pdo->prepare("SELECT * FROM evidence_photos WHERE case_id = ? ORDER BY uploaded_date DESC");
        $stmt->execute([$caseId]);
        $evidencePhotos = $stmt->fetchAll();
    } catch (Exception $e) {
        error_log("ERROR: Failed to load evidence photos: " . $e->getMessage());
    }

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        if ($_POST['action'] === 'update_case') {
            try {
                // Corrected update query - removed current_status
                $stmt = $pdo->prepare("
                    UPDATE cases SET 
                    child_name = ?, 
                    case_type = ?, 
                    description = ?, 
                    social_worker = ?, 
                    status = ?,  -- Only update the status column, not current_status
                    updated_at = NOW()
                    WHERE case_id = ?
                ");
                
                $result = $stmt->execute([
                    $_POST['childName'] ?? '',
                    $_POST['caseType'] ?? '',
                    $_POST['allegation'] ?? '',
                    $_POST['social_worker'] ?? '',
                    $_POST['currentStatus'] ?? '',  // This will go into the status column
                    $caseId
                ]);

                // Save custom fields
                if ($fieldManager && !empty($caseCustomFields)) {
                    foreach ($_POST as $key => $value) {
                        if (strpos($key, 'custom_field_') === 0) {
                            $fieldName = str_replace('custom_field_', '', $key);
                            foreach ($caseCustomFields as $field) {
                                if ($field['field_name'] === $fieldName) {
                                    $fieldManager->saveFieldValue($caseId, 'cases', $fieldName, $value);
                                    break;
                                }
                            }
                        }
                    }
                    // Reload custom field values after saving
                    $existingCaseCustomValues = $fieldManager->getFieldValues($caseId, 'cases');
                }
                
                if ($result) {
                    logActivity($currentUser['id'], 'Case Updated', 'cases', $caseId);
                    $success = "Case updated successfully!";
                    // Reload case data after update
                    $stmt = $pdo->prepare("SELECT * FROM cases WHERE case_id = ?");
                    $stmt->execute([$caseId]);
                    $case = $stmt->fetch();
                } else {
                    $errorInfo = $stmt->errorInfo();
                    $error = "Failed to update case. Error: " . $errorInfo[2];
                }
                
            } catch (Exception $e) {
                $error = "Database error: " . $e->getMessage();
                error_log("Update error: " . $e->getMessage());
            }
        }
        
        // ... rest of your form handling code remains the same ...
        if ($_POST['action'] === 'add_legal_action') {
            $stmt = $pdo->prepare("
                INSERT INTO legal_actions (case_id, type, date, status, notes) 
                VALUES (?, ?, ?, 'Scheduled', ?)
            ");
            
            $result = $stmt->execute([
                $caseId,
                $_POST['type'],
                $_POST['date'],
                $_POST['notes']
            ]);
            
            if ($result) {
                // Update case status
                $pdo->prepare("UPDATE cases SET status = 'Court Action Pending' WHERE case_id = ?")->execute([$caseId]);
                logActivity($currentUser['id'], 'Legal Action Added', 'legal_actions', $caseId);
                $success = "Legal action added successfully!";
                // Reload legal actions
                $stmt = $pdo->prepare("SELECT * FROM legal_actions WHERE case_id = ? ORDER BY date DESC");
                $stmt->execute([$caseId]);
                $legalActions = $stmt->fetchAll();
            } else {
                $error = "Failed to add legal action.";
            }
        }
        
        if ($_POST['action'] === 'add_intervention') {
            $stmt = $pdo->prepare("
                INSERT INTO social_services (case_id, type, date_started, status, details) 
                VALUES (?, ?, CURDATE(), 'Ongoing', ?)
            ");
            
            $result = $stmt->execute([
                $caseId,
                $_POST['type'],
                $_POST['details']
            ]);
            
            if ($result) {
                logActivity($currentUser['id'], 'Intervention Added', 'social_services', $caseId);
                $success = "Intervention added successfully!";
                // Reload social services
                $stmt = $pdo->prepare("SELECT * FROM social_services WHERE case_id = ? ORDER BY date_started DESC");
                $stmt->execute([$caseId]);
                $socialServices = $stmt->fetchAll();
            } else {
                $error = "Failed to add intervention.";
            }
        }
        
        // Handle multiple document uploads
        if ($_POST['action'] === 'upload_documents') {
            if (!empty($_FILES['documents']['name'][0])) {
                $uploadDir = 'uploads/cases/' . $caseId . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $uploadedCount = 0;
                $totalFiles = count($_FILES['documents']['name']);
                
                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileName = basename($_FILES['documents']['name'][$i]);
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', $fileName);
                        $uploadPath = $uploadDir . $newFileName;
                        
                        // Validate file type
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
                        if (in_array($fileExtension, $allowedExtensions)) {
                            if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $uploadPath)) {
                                // Insert into documents table
                                $stmt = $pdo->prepare("
                                    INSERT INTO documents (case_id, name, type, file_path, date_uploaded, uploaded_by) 
                                    VALUES (?, ?, ?, ?, CURDATE(), ?)
                                ");
                                
                                $documentType = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']) ? 'Photo' : 'Document';
                                $stmt->execute([
                                    $caseId,
                                    $fileName,
                                    $documentType,
                                    $uploadPath,
                                    $currentUser['full_name'] ?? $currentUser['username']
                                ]);
                                
                                $uploadedCount++;
                            }
                        }
                    }
                }
                
                if ($uploadedCount > 0) {
                    $success = "Successfully uploaded $uploadedCount file(s)!";
                    logActivity($currentUser['id'], 'Documents Uploaded', 'documents', $caseId);
                    // Reload documents
                    $stmt = $pdo->prepare("SELECT * FROM documents WHERE case_id = ? ORDER BY date_uploaded DESC");
                    $stmt->execute([$caseId]);
                    $documents = $stmt->fetchAll();
                } else {
                    $error = "No files were uploaded. Please check file types (JPG, PNG, GIF, PDF, DOC, TXT).";
                }
            } else {
                $error = "Please select at least one file to upload.";
            }
        }
        
        // Handle multiple evidence photo uploads
        if ($_POST['action'] === 'upload_evidence_photos') {
            if (!empty($_FILES['evidence_photos']['name'][0])) {
                $uploadDir = 'uploads/cases/' . $caseId . '/evidence/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $uploadedCount = 0;
                $totalFiles = count($_FILES['evidence_photos']['name']);
                
                for ($i = 0; $i < $totalFiles; $i++) {
                    if ($_FILES['evidence_photos']['error'][$i] === UPLOAD_ERR_OK) {
                        $fileName = basename($_FILES['evidence_photos']['name'][$i]);
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', $fileName);
                        $uploadPath = $uploadDir . $newFileName;
                        
                        // Validate image type
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                        if (in_array($fileExtension, $allowedExtensions)) {
                            if (move_uploaded_file($_FILES['evidence_photos']['tmp_name'][$i], $uploadPath)) {
                                // Insert into evidence_photos table
                                $stmt = $pdo->prepare("
                                    INSERT INTO evidence_photos (case_id, name, file_path, uploaded_date, uploaded_by) 
                                    VALUES (?, ?, ?, CURDATE(), ?)
                                ");
                                
                                $stmt->execute([
                                    $caseId,
                                    $fileName,
                                    $uploadPath,
                                    $currentUser['full_name'] ?? $currentUser['username']
                                ]);
                                
                                $uploadedCount++;
                            }
                        }
                    }
                }
                
                if ($uploadedCount > 0) {
                    $success = "Successfully uploaded $uploadedCount evidence photo(s)!";
                    logActivity($currentUser['id'], 'Evidence Photos Uploaded', 'evidence_photos', $caseId);
                    // Reload evidence photos
                    $stmt = $pdo->prepare("SELECT * FROM evidence_photos WHERE case_id = ? ORDER BY uploaded_date DESC");
                    $stmt->execute([$caseId]);
                    $evidencePhotos = $stmt->fetchAll();
                } else {
                    $error = "No photos were uploaded. Please check file types (JPG, PNG, GIF only).";
                }
            } else {
                $error = "Please select at least one photo to upload.";
            }
        }
    }
    ?>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title" id="caseId">Case ID: <?php echo htmlspecialchars($case['case_id']); ?></h1>
            <button class="btn btn-primary" onclick="window.location.href='case-management.php'">Back to Cases</button>
        </div>

        <?php if (isset($success)): ?>
            <div style="background: #28a745; color: white; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div style="background: #dc3545; color: white; padding: 12px; border-radius: 6px; margin-bottom: 20px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- DEBUG: Show case data for troubleshooting -->
        <?php if (false): // Set to true to debug ?>
        <div style="background: #ffeb3b; color: #000; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            <h4>Debug Case Data:</h4>
            <pre><?php print_r($case); ?></pre>
        </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <div class="tab-navigation">
            <button class="tab active" data-tab="investigation">Investigation</button>
            <button class="tab" data-tab="legal">Legal Action</button>
            <button class="tab" data-tab="social">Social Services</button>
            <button class="tab" data-tab="documents">Documents</button>
            <button class="tab" data-tab="evidence">Evidence Photos</button>
            <?php if ($fieldManager && !empty($caseCustomFields)): ?>
                <button class="tab" data-tab="custom">Additional Information</button>
            <?php endif; ?>
        </div>

        <!-- Investigation Tab -->
        <div id="investigation" class="tab-content active">
            <div class="case-info-section">
                <div id="caseInfoDisplay" class="case-info-grid">
                    <div class="info-item">
                        <div class="info-label">Case Type</div>
                        <div class="info-value" id="caseType">
                            <?php echo htmlspecialchars($case['case_type'] ?? 'Not specified'); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Allegation / Description</div>
                        <div class="info-value" id="allegation">
                            <?php echo htmlspecialchars($case['description'] ?? 'Not specified'); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Assigned Social Worker</div>
                        <div class="info-value" id="assignedSocialWorker">
                            <?php echo htmlspecialchars(getSocialWorkerName($case['social_worker'] ?? '')); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Current Status</div>
                        <div class="info-value" id="currentStatus">
                            <?php echo htmlspecialchars($case['status'] ?? $case['current_status'] ?? 'Not specified'); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Priority</div>
                        <div class="info-value" id="priority">
                            <?php echo htmlspecialchars(ucfirst($case['priority'] ?? 'Not specified')); ?>
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date Created</div>
                        <div class="info-value" id="createdDate">
                            <?php echo formatDate($case['created_date'] ?? $case['created_at'] ?? ''); ?>
                        </div>
                    </div>

                    <!-- Display Custom Fields in Investigation Tab (View Mode) -->
                    <?php if ($fieldManager && !empty($caseCustomFields)): ?>
                        <?php 
                        $displayedInInvestigation = false;
                        foreach ($caseCustomFields as $field): 
                            $fieldName = $field['field_name'];
                            $existingValue = $existingCaseCustomValues[$fieldName] ?? '';
                            $fieldLabel = $field['field_label'];
                            $fieldType = $field['field_type'];
                            
                            // Skip empty values in investigation tab to avoid clutter
                            if (empty($existingValue) && $existingValue !== '0' && $existingValue !== 0) {
                                continue;
                            }
                            
                            $displayedInInvestigation = true;
                        ?>
                        <div class="info-item">
                            <div class="info-label"><?php echo htmlspecialchars($fieldLabel); ?></div>
                            <div class="info-value">
                                <?php 
                                if ($fieldType === 'checkbox'):
                                    echo ($existingValue == '1' || $existingValue === true || $existingValue === 1) ? 'Yes' : 'No';
                                elseif ($fieldType === 'textarea'):
                                    echo '<div style="white-space: pre-wrap;">' . htmlspecialchars($existingValue) . '</div>';
                                elseif (($fieldType === 'select' || $fieldType === 'radio') && !empty($field['field_options'])):
                                    $options = is_array($field['field_options']) ? $field['field_options'] : json_decode($field['field_options'], true);
                                    echo htmlspecialchars($options[$existingValue] ?? $existingValue);
                                else:
                                    echo htmlspecialchars($existingValue);
                                endif;
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (!$displayedInInvestigation): ?>
                            <!-- Show a message if no custom fields have values in investigation tab -->
                            <div class="info-item">
                                <div class="info-label">Additional Information</div>
                                <div class="info-value" style="color: #888; font-style: italic;">
                                    No additional information filled. Check the "Additional Information" tab.
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- Edit Form (Hidden by default) -->
                <form id="editForm" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="update_case">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Child Name</label>
                            <input type="text" name="childName" class="form-input" value="<?php echo htmlspecialchars($case['child_name'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Case Type</label>
                            <select name="caseType" class="form-select">
                                <option value="Physical Abuse" <?php echo ($case['case_type'] ?? '') === 'Physical Abuse' ? 'selected' : ''; ?>>Physical Abuse</option>
                                <option value="Sexual Abuse" <?php echo ($case['case_type'] ?? '') === 'Sexual Abuse' ? 'selected' : ''; ?>>Sexual Abuse</option>
                                <option value="Neglect" <?php echo ($case['case_type'] ?? '') === 'Neglect' ? 'selected' : ''; ?>>Neglect</option>
                                <option value="Abandonment" <?php echo ($case['case_type'] ?? '') === 'Abandonment' ? 'selected' : ''; ?>>Abandonment</option>
                                <option value="Exploitation" <?php echo ($case['case_type'] ?? '') === 'Exploitation' ? 'selected' : ''; ?>>Exploitation</option>
                                <option value="Special Laws" <?php echo ($case['case_type'] ?? '') === 'Special Laws' ? 'selected' : ''; ?>>Special Laws</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Allegation / Description</label>
                            <textarea name="allegation" class="form-textarea"><?php echo htmlspecialchars($case['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Assigned Social Worker</label>
                            <select name="social_worker" class="form-select">
                                <option value="">Select Social Worker</option>
                                <option value="maria-santos" <?php echo ($case['social_worker'] ?? '') === 'maria-santos' ? 'selected' : ''; ?>>Maria Santos</option>
                                <option value="juan-cruz" <?php echo ($case['social_worker'] ?? '') === 'juan-cruz' ? 'selected' : ''; ?>>Juan Cruz</option>
                                <option value="lisa-gonzalez" <?php echo ($case['social_worker'] ?? '') === 'lisa-gonzalez' ? 'selected' : ''; ?>>Lisa Gonzalez</option>
                                <option value="carlos-reyes" <?php echo ($case['social_worker'] ?? '') === 'carlos-reyes' ? 'selected' : ''; ?>>Carlos Reyes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Current Status</label>
                            <select name="currentStatus" class="form-select">
                                <option value="Under Investigation" <?php echo ($case['status'] ?? '') === 'Under Investigation' ? 'selected' : ''; ?>>Under Investigation</option>
                                <option value="Open" <?php echo ($case['status'] ?? '') === 'Open' ? 'selected' : ''; ?>>Open</option>
                                <option value="Court Action Pending" <?php echo ($case['status'] ?? '') === 'Court Action Pending' ? 'selected' : ''; ?>>Court Action Pending</option>
                                <option value="Closed" <?php echo ($case['status'] ?? '') === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                    </div>

                    <!-- Custom Fields in Edit Form -->
                    <?php if ($fieldManager && !empty($caseCustomFields)): ?>
                    <div class="custom-fields-section">
                        <h3>Additional Information</h3>
                        <div class="form-grid">
                            <?php foreach ($caseCustomFields as $field): 
                                $existingValue = $existingCaseCustomValues[$field['field_name']] ?? '';
                                $fieldName = "custom_field_" . $field['field_name'];
                            ?>
                            <div class="form-group">
                                <label class="form-label"><?php echo htmlspecialchars($field['field_label']); ?>
                                    <?php if ($field['is_required']): ?>
                                        <span style="color: #dc3545;">*</span>
                                    <?php endif; ?>
                                </label>
                                
                                <?php if ($field['help_text']): ?>
                                    <div class="help-text"><?php echo htmlspecialchars($field['help_text']); ?></div>
                                <?php endif; ?>
                                
                                <?php 
                                // Use the field manager's render method for consistent rendering
                                echo $fieldManager->renderField($field, $existingValue);
                                ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="cancelEdit()">Cancel</button>
                        <button type="submit" class="btn-submit">Save Changes</button>
                    </div>
                </form>

                <div id="caseActions" class="case-actions">
                    <button class="btn btn-primary" onclick="editCase()">Edit Case</button>
                </div>
            </div>
        </div>

        <!-- Legal Action Tab -->
        <div id="legal" class="tab-content">
            <div class="section-header">
                <h3>Legal Actions</h3>
                <button class="btn btn-primary" onclick="showAddLegalActionForm()">Add Legal Action</button>
            </div>
            
            <div id="legalActionsList">
                <?php if (empty($legalActions)): ?>
                    <p style="color: #888; text-align: center; padding: 40px;">No legal actions scheduled yet.</p>
                <?php else: ?>
                    <?php foreach ($legalActions as $action): ?>
                    <div class="info-item">
                        <div class="info-label">Legal Action Type</div>
                        <div class="info-value"><?php echo htmlspecialchars($action['type']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date</div>
                        <div class="info-value"><?php echo formatDate($action['date']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value"><?php echo htmlspecialchars($action['status']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Notes</div>
                        <div class="info-value"><?php echo htmlspecialchars($action['notes'] ?? '-'); ?></div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add Legal Action Form (Hidden) -->
            <form id="addLegalActionForm" method="POST" style="display: none; margin-top: 20px;">
                <input type="hidden" name="action" value="add_legal_action">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Legal Action Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select Legal Action Type</option>
                            <option value="Court Proceedings & Hearings">Court Proceedings & Hearings</option>
                            <option value="Quasi-Judicial & Administrative Proceedings">Quasi-Judicial & Administrative Proceedings</option>
                            <option value="Alternative Dispute Resolution">Alternative Dispute Resolution</option>
                            <option value="Social Welfare-Specific Legal Interventions">Social Welfare-Specific Legal Interventions</option>
                            <option value="Other Legal Actions">Other Legal Actions</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-textarea" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideAddLegalActionForm()">Cancel</button>
                    <button type="submit" class="btn-submit">Add Legal Action</button>
                </div>
            </form>
        </div>

        <!-- Social Services Tab -->
        <div id="social" class="tab-content">
            <div class="section-header">
                <h3>Social Services</h3>
                <button class="btn btn-primary" onclick="showAddInterventionForm()">Add Intervention</button>
            </div>
            
            <div id="socialServicesList">
                <?php if (empty($socialServices)): ?>
                    <p style="color: #888; text-align: center; padding: 40px;">No social services assigned yet.</p>
                <?php else: ?>
                    <?php foreach ($socialServices as $service): ?>
                    <div class="info-item">
                        <div class="info-label">Service Type</div>
                        <div class="info-value"><?php echo htmlspecialchars($service['type']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Date Started</div>
                        <div class="info-value"><?php echo formatDate($service['date_started']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Status</div>
                        <div class="info-value"><?php echo htmlspecialchars($service['status']); ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Details</div>
                        <div class="info-value"><?php echo htmlspecialchars($service['details'] ?? '-'); ?></div>
                    </div>
                    <hr style="border-color: #3a3a3a; margin: 20px 0;">
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Add Intervention Form (Hidden) -->
            <form id="addInterventionForm" method="POST" style="display: none; margin-top: 20px;">
                <input type="hidden" name="action" value="add_intervention">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Service Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select Service Type</option>
                            <option value="Child Development & Education">Child Development & Education</option>
                            <option value="Family & Community Support">Family & Community Support</option>
                            <option value="Child Protection & Crisis Intervention">Child Protection & Crisis Intervention</option>
                            <option value="Children in Conflict with the Law (CICL)">Children in Conflict with the Law (CICL)</option>
                            <option value="Children in Need of Special Protection (CNSP)">Children in Need of Special Protection (CNSP)</option>
                            <option value="Alternative Parental Care">Alternative Parental Care</option>
                            <option value="Health & Nutrition">Health & Nutrition</option>
                            <option value="Other Services">Other Services</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Details</label>
                        <textarea name="details" class="form-textarea" placeholder="Service details..."></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideAddInterventionForm()">Cancel</button>
                    <button type="submit" class="btn-submit">Add Intervention</button>
                </div>
            </form>
        </div>

        <!-- Documents Tab -->
        <div id="documents" class="tab-content">
            <div class="section-header">
                <h3>Documents & Files</h3>
                <button class="btn btn-primary" onclick="showUploadDocumentsForm()">Upload Documents</button>
            </div>
            
            <!-- Upload Documents Form -->
            <form id="uploadDocumentsForm" method="POST" enctype="multipart/form-data" style="display: none; margin-bottom: 30px; background: #333; padding: 20px; border-radius: 8px;">
                <input type="hidden" name="action" value="upload_documents">
                <div class="form-group">
                    <label class="form-label">Select Documents (Multiple files allowed)</label>
                    <div class="file-upload-area" onclick="document.getElementById('documentsInput').click()">
                        <div class="upload-icon">📁</div>
                        <div class="upload-text">Drag & drop files here or click to browse</div>
                        <div class="upload-subtext">Supports: JPG, PNG, GIF, PDF, DOC, DOCX, TXT (Max 10MB each)</div>
                    </div>
                    <input type="file" id="documentsInput" name="documents[]" multiple accept=".jpg,.jpeg,.png,.gif,.pdf,.doc,.docx,.txt" style="display: none;" onchange="handleDocumentUpload(event)">
                    <div id="documentPreview" class="file-preview"></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideUploadDocumentsForm()">Cancel</button>
                    <button type="submit" class="btn-submit">Upload Documents</button>
                </div>
            </form>
            
            <div id="documentsList">
                <?php if (empty($documents)): ?>
                    <p style="color: #888; text-align: center; padding: 40px;">No documents uploaded yet.</p>
                <?php else: ?>
                    <div class="documents-grid">
                        <?php foreach ($documents as $doc): ?>
                        <div class="document-card">
                            <div class="document-icon">
                                <?php 
                                $extension = strtolower(pathinfo($doc['name'], PATHINFO_EXTENSION));
                                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo '🖼️';
                                } elseif ($extension === 'pdf') {
                                    echo '📄';
                                } elseif (in_array($extension, ['doc', 'docx'])) {
                                    echo '📝';
                                } else {
                                    echo '📁';
                                }
                                ?>
                            </div>
                            <div class="document-info">
                                <div class="document-name"><?php echo htmlspecialchars($doc['name']); ?></div>
                                <div class="document-meta">
                                    <span class="document-type"><?php echo htmlspecialchars($doc['type']); ?></span>
                                    <span class="document-date"><?php echo formatDate($doc['date_uploaded']); ?></span>
                                </div>
                                <div class="document-uploader">Uploaded by: <?php echo htmlspecialchars($doc['uploaded_by']); ?></div>
                            </div>
                            <div class="document-actions">
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn-view">View</a>
                                <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" download class="btn-download">Download</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Evidence Photos Tab -->
        <div id="evidence" class="tab-content">
            <div class="section-header">
                <h3>Evidence Photos</h3>
                <button class="btn btn-primary" onclick="showUploadEvidenceForm()">Upload Evidence Photos</button>
            </div>
            
            <!-- Upload Evidence Photos Form -->
            <form id="uploadEvidenceForm" method="POST" enctype="multipart/form-data" style="display: none; margin-bottom: 30px; background: #333; padding: 20px; border-radius: 8px;">
                <input type="hidden" name="action" value="upload_evidence_photos">
                <div class="form-group">
                    <label class="form-label">Select Evidence Photos (Multiple files allowed)</label>
                    <div class="file-upload-area" onclick="document.getElementById('evidenceInput').click()">
                        <div class="upload-icon">📷</div>
                        <div class="upload-text">Drag & drop photos here or click to browse</div>
                        <div class="upload-subtext">Supports: JPG, PNG, GIF only (Max 5MB each)</div>
                    </div>
                    <input type="file" id="evidenceInput" name="evidence_photos[]" multiple accept=".jpg,.jpeg,.png,.gif" style="display: none;" onchange="handleEvidenceUpload(event)">
                    <div id="evidencePreview" class="file-preview"></div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="hideUploadEvidenceForm()">Cancel</button>
                    <button type="submit" class="btn-submit">Upload Photos</button>
                </div>
            </form>
            
            <div id="evidencePhotosList">
                <?php if (empty($evidencePhotos)): ?>
                    <p style="color: #888; text-align: center; padding: 40px;">No evidence photos uploaded yet.</p>
                <?php else: ?>
                    <div class="evidence-grid">
                        <?php foreach ($evidencePhotos as $photo): ?>
                        <div class="evidence-card">
                            <div class="evidence-image">
                                <img src="<?php echo htmlspecialchars($photo['file_path']); ?>" alt="<?php echo htmlspecialchars($photo['name']); ?>" onerror="this.src='public/placeholder.jpg'">
                            </div>
                            <div class="evidence-info">
                                <div class="evidence-name"><?php echo htmlspecialchars($photo['name']); ?></div>
                                <div class="evidence-meta">
                                    <span class="evidence-date"><?php echo formatDate($photo['uploaded_date']); ?></span>
                                    <span class="evidence-uploader">By: <?php echo htmlspecialchars($photo['uploaded_by']); ?></span>
                                </div>
                            </div>
                            <div class="evidence-actions">
                                <a href="<?php echo htmlspecialchars($photo['file_path']); ?>" target="_blank" class="btn-view">View</a>
                                <a href="<?php echo htmlspecialchars($photo['file_path']); ?>" download class="btn-download">Download</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Custom Fields Tab - Only show if custom fields exist -->
        <?php if ($fieldManager && !empty($caseCustomFields)): ?>
        <div id="custom" class="tab-content">
            <div class="section-header">
                <h3>Additional Case Information</h3>
            </div>
            
            <div class="custom-fields-display">
                <?php 
                $hasAnyCustomFieldValue = false;
                foreach ($caseCustomFields as $field): 
                    $fieldName = $field['field_name'];
                    $existingValue = $existingCaseCustomValues[$fieldName] ?? '';
                    $fieldLabel = $field['field_label'];
                    $fieldType = $field['field_type'];
                    
                    // Skip if no value and we're not in debug mode
                    if (empty($existingValue) && $existingValue !== '0' && $existingValue !== 0) {
                        continue;
                    }
                    
                    $hasAnyCustomFieldValue = true;
                ?>
                <div class="info-item">
                    <div class="info-label"><?php echo htmlspecialchars($fieldLabel); ?></div>
                    <div class="info-value">
                        <?php 
                        if ($fieldType === 'checkbox'):
                            echo ($existingValue == '1' || $existingValue === true || $existingValue === 1) ? 'Yes' : 'No';
                        elseif ($fieldType === 'textarea'):
                            echo '<div style="white-space: pre-wrap;">' . htmlspecialchars($existingValue) . '</div>';
                        elseif (($fieldType === 'select' || $fieldType === 'radio') && !empty($field['field_options'])):
                            $options = is_array($field['field_options']) ? $field['field_options'] : json_decode($field['field_options'], true);
                            echo htmlspecialchars($options[$existingValue] ?? $existingValue);
                        else:
                            echo htmlspecialchars($existingValue);
                        endif;
                        ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (!$hasAnyCustomFieldValue): ?>
                    <p style="color: #888; text-align: center; padding: 40px;">
                        No additional information available. 
                        Click "Edit Case" to add information.
                    </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <style>
    /* Tab Content Styling */
    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    /* Tab Navigation */
    .dark-theme .tab-navigation {
        display: flex;
        border-bottom: 1px solid #3a3a3a;
        margin-bottom: 24px;
        background: #2a2a2a;
        border-radius: 8px 8px 0 0;
        padding: 0 10px;
        overflow-x: auto;
    }

    .light-theme .tab-navigation {
        display: flex;
        margin-bottom: 24px;
        border-radius: 8px 8px 0 0;
        padding: 0 10px;
        overflow-x: auto;
    }

    .dark-theme .tab {
        background: none;
        border: none;
        color: #b8c5ff;
        padding: 12px 24px;
        cursor: pointer;
        font-size: 17px;
        font-weight: 500;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .light-theme .tab {
        background: none;
        border: none;
        color: #2d5f8d;
        padding: 12px 24px;
        cursor: pointer;
        font-size: 17px;
        font-weight: 600;
        
        transition: all 0.2s;
        white-space: nowrap;
        flex-shrink: 0;
    }
    

    .tab.active {
        color:rgb(33, 111, 236);
        border-bottom-color: #3b82f6;
        border-bottom: 2px solid #3b82f6;
        background: rgba(59, 130, 246, 0.1);
    }

    .tab:hover {
        background: rgba(59, 130, 246, 0.05);
    }

    /* Tab Content Containers */
    .dark-theme .tab-content {
        background: #2a2a2a;
        border-radius: 0 0 8px 8px;
        padding: 24px;
        margin-top: -1px;
        border: 1px solid #3a3a3a;
        border-top: none;
        min-height: 400px;
    }

    .light-theme .tab-content {
        border-radius: 0 0 8px 8px;
        padding: 24px;
        margin-top: -1px;
        border-top: none;
        min-height: 400px;
    }


    /* Custom Fields Styling */
    .custom-fields-section {
        background: #2a2a2a;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
        border: 1px solid #3a3a3a;
    }

    .custom-fields-section h3 {
        color: #3b82f6;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #3a3a3a;
    }

    .custom-fields-section .form-group {
        margin-bottom: 20px;
        padding: 15px;
        background: #333;
        border-radius: 6px;
        border: 1px solid #3a3a3a;
    }

    .custom-fields-section .form-label {
        color: #b8c5ff;
        font-weight: 500;
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
    }

    .custom-fields-section .form-input,
    .custom-fields-section .form-select,
    .custom-fields-section .form-textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #3a3a3a;
        border-radius: 4px;
        background: #1a1a1a;
        color: white;
        font-size: 14px;
    }

    .custom-fields-section .form-input:focus,
    .custom-fields-section .form-select:focus,
    .custom-fields-section .form-textarea:focus {
        border-color: #3b82f6;
        outline: none;
    }

    .custom-fields-section .help-text {
        color: #888;
        font-size: 12px;
        font-style: italic;
        margin-top: 4px;
    }

    .custom-fields-section .radio-option,
    .custom-fields-section .checkbox-option {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        padding: 5px 0;
    }

    .custom-fields-section .radio-option input,
    .custom-fields-section .checkbox-option input {
        margin-right: 8px;
    }

    .custom-fields-section .radio-option label,
    .custom-fields-section .checkbox-option label {
        color: #ccc;
        margin-bottom: 0;
        font-size: 14px;
    }

    .case-info-section {
        background: #2a2a2a;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }

    .case-info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .info-item {
        padding: 16px 0;
        border-bottom: 1px solid #3a3a3a;
    }

    .info-label {
        color: #b8c5ff;
        font-weight: 500;
        font-size: 14px;
        margin-bottom: 4px;
    }

    .info-value {
        color: #ffffff;
        font-size: 14px;
    }

    .case-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .section-header h3 {
        color: #ffffff;
        font-size: 18px;
        font-weight: 600;
    }

    .light-theme .section-header h3 {
        color: #1e293b;
        font-size: 18px;
        font-weight: 600;
    }

    /* File Upload Styles */
    .file-upload-area {
        border: 2px dashed #3a3a3a;
        border-radius: 8px;
        padding: 40px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background: #2a2a2a;
    }

    .file-upload-area:hover {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.05);
    }

    .file-upload-area.dragover {
        border-color: #3b82f6;
        background: rgba(59, 130, 246, 0.1);
    }

    .upload-icon {
        font-size: 48px;
        margin-bottom: 16px;
    }

    .upload-text {
        color: #3b82f6;
        font-size: 16px;
        font-weight: 500;
        margin-bottom: 8px;
    }

    .upload-subtext {
        color: #888;
        font-size: 14px;
    }

    .file-preview {
        margin-top: 20px;
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .file-preview-item {
        background: #333;
        padding: 10px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }

    .file-preview-item .remove-btn {
        background: #dc3545;
        color: white;
        border: none;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        cursor: pointer;
        font-size: 12px;
    }

    /* Documents Grid */
    .documents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .dark-theme .document-card {
        background: #333;
        border-radius: 8px;
        padding: 16px;
        display: flex;
        align-items: center;
        width: 90vh;
        gap: 12px;
        transition: all 0.2s;
    }

    .light-theme .document-card {
        border-radius: 8px;
        padding: 16px;
        display: flex;
        align-items: center;
        width: 90vh;
        gap: 12px;
        transition: all 0.2s;
        background-color: rgba(63, 61, 61, 0.1);
        border-left: 3px solid #2d5f8d;
    }

    .dark-theme .document-card:hover {
        background: #3a3a3a;
    }

    .light-theme .document-card:hover {
        background: rgba(39, 174, 96, 0.2);
    }

    .document-icon {
        font-size: 32px;
    }

    .document-info {
        flex: 1;
    }

    .dark-theme .document-name {
        color: #fff;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .light-theme .document-name {
        color: black;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .document-meta {
        display: flex;
        gap: 12px;
        font-size: 12px;
        color: #888;
    }

    .dark-theme .document-uploader {
        font-size: 12px;
        color: #b8c5ff;
        margin-top: 4px;
    }

    .light-theme .document-uploader {
        font-size: 12px;
        color: #0E7490;
        margin-top: 4px;
    }

    .document-actions {
        display: flex;
        gap: 8px;
    }

    .btn-view, .btn-download {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
    }

    .btn-view {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
    }

    .btn-view:hover {
        background-color: #2563eb;
    }

    .btn-download {
        background: #28a745;
        color: white;
    }

    .btn-download:hover{
        background-color: rgb(39, 104, 198)
    }
    /* Evidence Grid */
    .evidence-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
    }

    .evidence-card {
        background: #333;
        border-radius: 8px;
        overflow: hidden;
        transition: all 0.2s;
    }

    .evidence-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    }

    .evidence-image {
        height: 200px;
        overflow: hidden;
    }

    .evidence-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .evidence-info {
        padding: 12px;
        background: #ffffff
    }

    .evidence-name {
        color: #1e293b;
        font-weight: 500;
        margin-bottom: 4px;
    }

    .evidence-meta {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #2d5f8d;
    }

    .evidence-actions {
        padding: 12px;
        border-top: 1px solid #444;
        display: flex;
        gap: 8px;
        background-color: #ffffff;
    }

    /* Form Styles */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .form-group {
        margin-bottom: 16px;
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
        border: 1px solid #3a3a3a;
        border-radius: 6px;
        background: #1a1a1a;
        color: white;
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
        gap: 12px;
        margin-top: 20px;
    }

    .btn-cancel {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
    }

    .btn-submit {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
    }

    .btn-cancel:hover {
        background: #5a6268;
    }

    .btn-submit:hover {
        background: #2563eb;
    }
    </style>

    <script>
    // Simple and reliable tab system
    function setupTabs() {
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Activate clicked tab
                this.classList.add('active');
                
                // Show corresponding content
                const tabContent = document.getElementById(tabName);
                if (tabContent) {
                    tabContent.classList.add('active');
                }
            });
        });
    }

    function editCase() {
        document.getElementById('caseInfoDisplay').style.display = 'none';
        document.getElementById('caseActions').style.display = 'none';
        document.getElementById('editForm').style.display = 'block';
    }

    function cancelEdit() {
        document.getElementById('editForm').style.display = 'none';
        document.getElementById('caseInfoDisplay').style.display = 'grid';
        document.getElementById('caseActions').style.display = 'flex';
    }

    function showAddLegalActionForm() {
        document.getElementById('addLegalActionForm').style.display = 'block';
    }

    function hideAddLegalActionForm() {
        document.getElementById('addLegalActionForm').style.display = 'none';
    }

    function showAddInterventionForm() {
        document.getElementById('addInterventionForm').style.display = 'block';
    }

    function hideAddInterventionForm() {
        document.getElementById('addInterventionForm').style.display = 'none';
    }

    // Document Upload Functions
    function showUploadDocumentsForm() {
        document.getElementById('uploadDocumentsForm').style.display = 'block';
    }

    function hideUploadDocumentsForm() {
        document.getElementById('uploadDocumentsForm').style.display = 'none';
        document.getElementById('documentPreview').innerHTML = '';
        document.getElementById('documentsInput').value = '';
    }

    function handleDocumentUpload(event) {
        const files = event.target.files;
        const preview = document.getElementById('documentPreview');
        preview.innerHTML = '';

        for (let file of files) {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-preview-item';
            fileItem.innerHTML = `
                <span>📄 ${file.name}</span>
                <span style="color: #888; font-size: 12px;">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                <button type="button" class="remove-btn" onclick="removeFile(this)">×</button>
            `;
            preview.appendChild(fileItem);
        }
    }

    // Evidence Upload Functions
    function showUploadEvidenceForm() {
        document.getElementById('uploadEvidenceForm').style.display = 'block';
    }

    function hideUploadEvidenceForm() {
        document.getElementById('uploadEvidenceForm').style.display = 'none';
        document.getElementById('evidencePreview').innerHTML = '';
        document.getElementById('evidenceInput').value = '';
    }

    function handleEvidenceUpload(event) {
        const files = event.target.files;
        const preview = document.getElementById('evidencePreview');
        preview.innerHTML = '';

        for (let file of files) {
            const fileItem = document.createElement('div');
            fileItem.className = 'file-preview-item';
            fileItem.innerHTML = `
                <span>📷 ${file.name}</span>
                <span style="color: #888; font-size: 12px;">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                <button type="button" class="remove-btn" onclick="removeFile(this)">×</button>
            `;
            preview.appendChild(fileItem);
        }
    }

    function removeFile(button) {
        button.parentElement.remove();
    }

    // Initialize everything when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tabs
        setupTabs();
        
        // Form validation for edit form
        const editForm = document.getElementById('editForm');
        if (editForm) {
            editForm.addEventListener('submit', function(e) {
                const requiredFields = this.querySelectorAll('[required]');
                let valid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.style.borderColor = '#dc3545';
                    } else {
                        field.style.borderColor = '';
                    }
                });
                
                if (!valid) {
                    e.preventDefault();
                    alert('Please fill in all required fields.');
                }
            });
        }
        
        // Initialize drag and drop for file uploads
        const uploadAreas = document.querySelectorAll('.file-upload-area');
        
        uploadAreas.forEach(area => {
            area.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            
            area.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });
            
            area.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                const input = this.parentElement.querySelector('input[type="file"]');
                
                if (input) {
                    const dt = new DataTransfer();
                    for (let file of files) {
                        dt.items.add(file);
                    }
                    input.files = dt.files;
                    
                    const event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                }
            });
        });
    });
    </script>

    <?php require_once 'includes/footer.php'; ?>
[file content end]