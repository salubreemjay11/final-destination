<?php
class CustomFieldManager {
    private $pdo;
    
    public function __construct($pdoConnection) {
        $this->pdo = $pdoConnection;
    }
    
    public function getAvailableModules() {
        return [
            'children' => 'Children Management',
            'cases' => 'Case Management', 
            'foster' => 'Foster Information',
            'donations' => 'Donations',
        ];
    }
    
    public function getFieldTypes() {
        return [
            'text' => 'Text Field',
            'textarea' => 'Text Area', 
            'number' => 'Number Field',
            'date' => 'Date Field',
            'select' => 'Dropdown/Select', // Only this shows options
            'checkbox' => 'Checkbox',
            'radio' => 'Radio Buttons' // This also shows options
        ];
    }

    public function fixFieldTypes() {
        try {
            // Update any 'dropdown' types to 'select'
            $stmt = $this->pdo->prepare("UPDATE custom_fields SET field_type = 'select' WHERE field_type = 'dropdown'");
            $stmt->execute();
            
            // Fix any empty or null field types
            $stmt = $this->pdo->prepare("UPDATE custom_fields SET field_type = 'text' WHERE field_type IS NULL OR field_type = ''");
            $stmt->execute();
            
            error_log("Field types fixed: dropdown->select and empty->text");
            return true;
        } catch (Exception $e) {
            error_log("Error fixing field types: " . $e->getMessage());
            return false;
        }
    }

    public function getModuleTableMap() {
        return [
            'children' => 'children',
            'cases' => 'cases',
            'foster' => 'foster_parents',
            'donations' => 'donations',
            'inventory' => 'inventory',
            'users' => 'users',
            'schedule' => 'events'
        ];
    }

    public function getFieldLocations($module) {
        $locations = [
            'children' => [
                'unified-registration.php - Child Information Tab',
                'child-management.php - Edit Child Modal', 
                'child-management.php - Child Details View',
                'case-management.php - Linked Child Information'
            ],
            'cases' => [
                'unified-registration.php - Case Information Tab',
                'case-management.php - Case Details',
                'case-management.php - Case Edit Form'
            ],
            'foster' => [
                'foster-info.php - Foster Home Registration',
                'foster-info.php - Foster Home Details',
                'matchmaking.php - Foster Matching'
            ],
            'donations' => [
                'donation.php - Donation Form',
                'donation.php - Donation Records',
                'donation-reports.php - Donation Reports'
            ],
            'inventory' => [
                'inventory.php - Inventory Items',
                'inventory.php - Stock Management'
            ],
            'users' => [
                'user-management.php - User Profiles',
                'settings.php - User Settings'
            ],     
            'schedule' => [ // ADDED SCHEDULE LOCATIONS
                'schedule.php - Event Creation Modal',
                'schedule.php - Event Details View',
                'schedule.php - Event Edit Form'
            ]
        ];
        
        return $locations[$module] ?? ['Various system locations'];
    }

    // Create field with database column
    public function createField($data) {
        try {
            // Validate required fields
            if (empty($data['field_name']) || empty($data['field_label']) || empty($data['module'])) {
                return ['success' => false, 'error' => 'Field name, label, and module are required'];
            }

            // Check if field already exists
            $existingField = $this->fieldExists($data['field_name'], $data['module']);
            if ($existingField) {
                return ['success' => false, 'error' => "Field '{$data['field_name']}' already exists in module '{$data['module']}'"];
            }
            
            // Create database column for the field first
            $columnCreated = $this->createFieldColumn($data);
            if (!$columnCreated['success']) {
                return ['success' => false, 'error' => 'Database column creation failed: ' . $columnCreated['error']];
            }
            
            // Prepare field options - ensure it's never null
            $options = '';
            if (isset($data['field_options']) && is_array($data['field_options']) && !empty($data['field_options'])) {
                $options = json_encode($data['field_options']);
            } else {
                $options = json_encode([]);
            }

            // Prepare the insert
            $sql = "INSERT INTO custom_fields (
                field_name, field_label, field_type, module, placeholder_text, 
                default_value, help_text, field_options, is_required, field_order,
                display_order, is_active, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW())";
            
            $dbColumnName = 'cf_' . $data['field_name'];
            
            $stmt = $this->pdo->prepare($sql);
            
            $result = $stmt->execute([
                $data['field_name'],
                $data['field_label'],
                $data['field_type'],
                $data['module'],
                $data['placeholder_text'] ?? '',
                $data['default_value'] ?? '',
                $data['help_text'] ?? '',
                $options,
                $data['is_required'] ?? 0,
                $data['field_order'] ?? 0,
                $data['field_order'] ?? 0,
                $data['created_by']
            ]);
            
            if ($result) {
                $fieldId = $this->pdo->lastInsertId();
                return [
                    'success' => true, 
                    'field_id' => $fieldId,
                    'db_column' => $dbColumnName,
                    'sql_preview' => $this->generateSQLPreview($data)
                ];
            } else {
                $errorInfo = $stmt->errorInfo();
                return ['success' => false, 'error' => 'Insert failed: ' . $errorInfo[2]];
            }
            
        } catch (Exception $e) {
            error_log("Custom Field Create Error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Create actual database column
    private function createFieldColumn($fieldData) {
        try {
            $tableMap = $this->getModuleTableMap();
            $tableName = $tableMap[$fieldData['module']] ?? null;
            
            if (!$tableName) {
                return ['success' => false, 'error' => 'No table mapping found for module: ' . $fieldData['module']];
            }

            // Check if table exists
            $checkResult = $this->pdo->query("SHOW TABLES LIKE '$tableName'");
            if (!$checkResult || $checkResult->rowCount() === 0) {
                return ['success' => false, 'error' => "Table '$tableName' does not exist"];
            }

            $columnName = 'cf_' . $fieldData['field_name'];
            $columnType = $this->getSQLColumnType($fieldData['field_type']);
            
            // DEBUG: Show the exact SQL being executed
            $sql = "ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnType";
            error_log("Executing SQL: " . $sql);
            
            // Execute directly
            $result = $this->pdo->exec($sql);
            
            if ($result === false) {
                $errorInfo = $this->pdo->errorInfo();
                return ['success' => false, 'error' => "SQL Error: " . $errorInfo[2]];
            }
            
            return ['success' => true, 'column_name' => $columnName];
            
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Improved method to get proper SQL column types
    private function getSQLColumnType($fieldType) {
        $typeMap = [
            'text' => 'VARCHAR(255)',
            'textarea' => 'TEXT',
            'number' => 'INT',
            'date' => 'DATE',
            'select' => 'VARCHAR(255)',
            'checkbox' => 'VARCHAR(255)',
            'radio' => 'VARCHAR(255)',
            'email' => 'VARCHAR(255)',
            'tel' => 'VARCHAR(20)',
            'url' => 'VARCHAR(255)'
        ];
        
        return $typeMap[$fieldType] ?? 'VARCHAR(255)';
    }

    // Generate SQL preview for admin
    private function generateSQLPreview($fieldData) {
        $tableMap = $this->getModuleTableMap();
        $tableName = $tableMap[$fieldData['module']] ?? 'unknown_table';
        $columnName = 'cf_' . $fieldData['field_name'];
        $columnType = $this->getSQLColumnType($fieldData['field_type']);
        
        return "ALTER TABLE `$tableName` ADD COLUMN `$columnName` $columnType;";
    }

    // Get field with database info
    public function getField($fieldId) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM custom_fields WHERE id = ?");
            $stmt->execute([$fieldId]);
            $field = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($field) {
                if ($field['field_options']) {
                    $field['field_options'] = json_decode($field['field_options'], true);
                }
                // Add location info
                $field['locations'] = $this->getFieldLocations($field['module']);
                $field['table_name'] = $this->getModuleTableMap()[$field['module']] ?? 'unknown';
            }
            
            return $field;
        } catch (Exception $e) {
            error_log("Custom Field Get Error: " . $e->getMessage());
            return null;
        }
    }

    // Get all fields with location info
    public function getAllFields($page = 1, $limit = 50) {
        try {
            $offset = ($page - 1) * $limit;
            $sql = "SELECT * FROM custom_fields ORDER BY module, field_order, field_label LIMIT $limit OFFSET $offset";
            error_log("DEBUG: Executing getAllFields SQL: " . $sql);
            
            $stmt = $this->pdo->query($sql);
            
            $fields = [];
            $count = 0;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $count++;
                error_log("DEBUG: Found field #$count - ID: " . $row['id'] . ", Name: " . $row['field_name'] . ", Active: " . $row['is_active']);
                
                if ($row['field_options']) {
                    $row['field_options'] = json_decode($row['field_options'], true);
                }
                // Add location info for each field
                $row['locations'] = $this->getFieldLocations($row['module']);
                $row['table_name'] = $this->getModuleTableMap()[$row['module']] ?? 'unknown';
                $fields[] = $row;
            }
            
            error_log("DEBUG: Total fields found: " . $count);
            return $fields;
            
        } catch (Exception $e) {
            error_log("Custom Fields GetAll Error: " . $e->getMessage());
            error_log("DEBUG: Error details: " . print_r($this->pdo->errorInfo(), true));
            return [];
        }
    }

    // Check if field exists
    public function fieldExists($fieldName, $module) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, field_name, module, is_active FROM custom_fields WHERE field_name = ? AND module = ?");
            $stmt->execute([$fieldName, $module]);
            $field = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($field) {
                error_log("DEBUG: Field exists - ID: " . $field['id'] . ", Name: " . $field['field_name'] . ", Module: " . $field['module'] . ", Active: " . $field['is_active']);
                return $field;
            }
            
            return false;
        } catch (Exception $e) {
            error_log("Field Exists Check Error: " . $e->getMessage());
            return false;
        }
    }

    public function getModuleFields($module, $activeOnly = true) {
        try {
            $sql = "SELECT * FROM custom_fields WHERE module = ?";
            if ($activeOnly) $sql .= " AND is_active = 1";
            $sql .= " ORDER BY field_order, field_label";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$module]);
            
            $fields = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Properly decode field_options with error handling
                if (!empty($row['field_options'])) {
                    $decodedOptions = json_decode($row['field_options'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedOptions)) {
                        $row['field_options'] = $decodedOptions;
                    } else {
                        // If JSON decode fails, try to parse as string
                        $row['field_options'] = $this->parseFieldOptions($row['field_options']);
                        error_log("JSON decode failed for field {$row['field_name']}, using fallback parsing");
                    }
                } else {
                    $row['field_options'] = [];
                }
                
                // Normalize field type
                $row['field_type'] = strtolower(trim($row['field_type']));
                
                $fields[] = $row;
            }
            
            return $fields;
        } catch (Exception $e) {
            error_log("Custom Fields GetModule Error: " . $e->getMessage());
            return [];
        }
    }

    // Add this helper method to parse field options
    private function parseFieldOptions($optionsString) {
        if (empty($optionsString)) return [];
        
        // Try various parsing methods
        if (is_array($optionsString)) return $optionsString;
        
        if (is_string($optionsString)) {
            $decoded = json_decode($optionsString, true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
            
            // Try to parse as serialized PHP
            $unserialized = @unserialize($optionsString);
            if ($unserialized !== false) return $unserialized;
            
            // Try simple key:value parsing
            $lines = explode("\n", $optionsString);
            $options = [];
            foreach ($lines as $line) {
                if (strpos($line, ':') !== false) {
                    list($key, $value) = explode(':', $line, 2);
                    $options[trim($key)] = trim($value);
                }
            }
            if (!empty($options)) return $options;
        }
        
        return [];
    }

    // FIXED: Save field value to database column
    public function saveFieldValue($recordId, $module, $fieldName, $value) {
        try {
            error_log("=== SAVE FIELD VALUE START ===");
            error_log("Record ID: $recordId, Module: $module, Field: $fieldName, Value: '$value'");
    
            // Handle empty values
            if ($value === '' || $value === null) {
                $value = null;
            }
    
            // Handle checkbox arrays
            if (is_array($value)) {
                $value = implode(',', array_filter($value));
                error_log("Checkbox array converted to: '$value'");
            }
    
            $tableMap = $this->getModuleTableMap();
            $tableName = $tableMap[$module] ?? null;
            $idColumn = $this->getIdColumn($tableName);
            
            if (!$tableName || !$idColumn) {
                error_log("ERROR: No table mapping found for module: $module");
                return false;
            }
            
            $dbColumn = 'cf_' . $fieldName;
    
            // DEBUG: Check if table and column exist
            error_log("Table: $tableName, ID Column: $idColumn, DB Column: $dbColumn");
    
            // Check if record exists
            $checkRecord = $this->pdo->prepare("SELECT COUNT(*) FROM `$tableName` WHERE `$idColumn` = ?");
            $checkRecord->execute([$recordId]);
            $recordExists = $checkRecord->fetchColumn();
            
            error_log("Record exists: " . ($recordExists ? 'YES' : 'NO'));
    
            if (!$recordExists) {
                error_log("ERROR: Record $recordId not found in $tableName");
                return false;
            }
    
            // SIMPLIFIED: Direct update without checking column existence
            // If column doesn't exist, this will fail gracefully
            $sql = "UPDATE `$tableName` SET `$dbColumn` = ? WHERE `$idColumn` = ?";
            error_log("Executing SQL: $sql");
            error_log("With values: [" . ($value ?? 'NULL') . ", $recordId]");
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([$value, $recordId]);
            
            error_log("Execute result: " . ($result ? 'SUCCESS' : 'FAILED'));
            
            if ($stmt->rowCount() > 0) {
                error_log("Rows affected: " . $stmt->rowCount());
            } else {
                error_log("No rows affected - value might be the same or column doesn't exist");
            }
    
            // Verify the save worked
            $verifyStmt = $this->pdo->prepare("SELECT `$dbColumn` FROM `$tableName` WHERE `$idColumn` = ?");
            $verifyStmt->execute([$recordId]);
            $savedValue = $verifyStmt->fetchColumn();
            
            error_log("Verified saved value: '" . $savedValue . "'");
            error_log("=== SAVE FIELD VALUE COMPLETE ===");
    
            return $result;
            
        } catch (Exception $e) {
            error_log("SAVE FIELD VALUE ERROR: " . $e->getMessage());
            error_log("Error details: " . print_r($this->pdo->errorInfo(), true));
            return false;
        }
    }

    // Get the appropriate ID column for each table
    private function getIdColumn($tableName) {
        $idColumns = [
            'children' => 'child_id',
            'cases' => 'case_id',
            'foster_parents' => 'foster_id',
            'donations' => 'donation_id',
            'inventory' => 'item_id',
            'users' => 'id'
        ];
        
        return $idColumns[$tableName] ?? 'id';
    }

    // Get field by name and module
    public function getFieldByName($fieldName, $module) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM custom_fields WHERE field_name = ? AND module = ?");
            $stmt->execute([$fieldName, $module]);
            $field = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($field && $field['field_options']) {
                $field['field_options'] = json_decode($field['field_options'], true);
            }
            
            return $field;
        } catch (Exception $e) {
            error_log("Get Field By Name Error: " . $e->getMessage());
            return null;
        }
    }

    // Get field values from database columns
    public function getFieldValues($recordId, $module) {
        try {
            error_log("DEBUG getFieldValues: recordId=$recordId, module=$module");
            
            $fields = $this->getModuleFields($module);
            $tableMap = $this->getModuleTableMap();
            $tableName = $tableMap[$module] ?? null;
            
            error_log("DEBUG: Found " . count($fields) . " fields for module $module");
            error_log("DEBUG: Table name: $tableName");
            
            $values = [];
            
            // First try to get values from main table columns
            if ($tableName && !empty($fields)) {
                $selectColumns = [];
                $idColumn = $this->getIdColumn($tableName);
                
                // Build the select query with all custom fields
                $selectColumns[] = $idColumn; // Include the ID column for reference
                
                foreach ($fields as $field) {
                    $dbColumnName = 'cf_' . $field['field_name'];
                    $selectColumns[] = "`$dbColumnName`";
                    error_log("DEBUG: Looking for column: $dbColumnName for field: {$field['field_name']}");
                }

                if (!empty($selectColumns)) {
                    $sql = "SELECT " . implode(', ', $selectColumns) . " FROM `$tableName` WHERE `$idColumn` = ?";
                    
                    error_log("DEBUG: Executing SQL: $sql");
                    error_log("DEBUG: With recordId: $recordId");
                    
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([$recordId]);
                    $result = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    error_log("DEBUG: Database result: " . print_r($result, true));
                    
                    if ($result) {
                        foreach ($result as $column => $value) {
                            // Only process custom field columns (starting with cf_)
                            if (strpos($column, 'cf_') === 0) {
                                $fieldName = str_replace('cf_', '', $column);
                                // Only add non-null values
                                if ($value !== null && $value !== '') {
                                    $values[$fieldName] = $value;
                                    error_log("DEBUG: Found value for $fieldName: '$value'");
                                } else {
                                    error_log("DEBUG: Empty value for $fieldName: '$value'");
                                }
                            }
                        }
                    } else {
                        error_log("DEBUG: No record found in $tableName for $idColumn = $recordId");
                    }
                }
            }
            
            // Then get from custom_field_values table (fallback)
            $customValues = $this->getFromCustomFieldValues($recordId, $module);
            error_log("DEBUG: Custom values from fallback table: " . print_r($customValues, true));
            
            // Merge with custom values taking precedence (in case of conflicts)
            $finalValues = array_merge($values, $customValues);
            
            error_log("DEBUG: Final merged values for $module: " . print_r($finalValues, true));
            
            return $finalValues;
            
        } catch (Exception $e) {
            error_log("Custom Field Get Values Error: " . $e->getMessage());
            return $this->getFromCustomFieldValues($recordId, $module);
        }
    }

    // Get values from custom_field_values table
    private function getFromCustomFieldValues($recordId, $module) {
        try {
            $stmt = $this->pdo->prepare("SELECT field_name, field_value FROM custom_field_values WHERE record_id = ? AND module = ?");
            $stmt->execute([$recordId, $module]);
            
            $values = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $values[$row['field_name']] = $row['field_value'];
            }
            
            return $values;
        } catch (Exception $e) {
            error_log("Custom Field Values Get Error: " . $e->getMessage());
            return [];
        }
    }

    // IMPROVED: Render field with proper input types and options
    public function renderField($field, $value = '') {
        error_log("=== RENDER FIELD START ===");
        error_log("Field: " . $field['field_name']);
        error_log("Original Type: " . $field['field_type']);
        error_log("Options: " . print_r($field['field_options'] ?? 'None', true));
        error_log("Current Value: '" . $value . "'");
        
        $html = '';
        $fieldName = "custom_field_" . $field['field_name'];
        $fieldId = "custom_field_" . $field['field_name'];
        $required = $field['is_required'] ? 'required' : '';
        
        $html .= '<div class="form-group custom-field">';
        $html .= '<label class="form-label">' . htmlspecialchars($field['field_label']);
        if ($field['is_required']) $html .= ' <span class="required">*</span>';
        $html .= '</label>';
        
        if ($field['help_text']) {
            $html .= '<div class="help-text">' . htmlspecialchars($field['help_text']) . '</div>';
        }
        
        // CRITICAL FIX: Use the ACTUAL field type from database
        $fieldType = strtolower(trim($field['field_type']));
        error_log("Database Field Type: " . $fieldType);
        
        // Only normalize, don't change the type
        $fieldType = $this->normalizeFieldType($fieldType);
        error_log("Normalized Field Type: " . $fieldType);
        
        switch ($fieldType) {
            case 'text': 
                $placeholder = $field['placeholder_text'] ?? '';
                $html .= '<input type="text" id="' . $fieldId . '" name="' . $fieldName . '" class="form-input" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
                
            case 'textarea':
                $placeholder = $field['placeholder_text'] ?? '';
                $html .= '<textarea id="' . $fieldId . '" name="' . $fieldName . '" class="form-input" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . ' rows="3">' . htmlspecialchars($value) . '</textarea>';
                break;
                
            case 'number':
                $placeholder = $field['placeholder_text'] ?? '';
                $html .= '<input type="number" id="' . $fieldId . '" name="' . $fieldName . '" class="form-input" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" ' . $required . '>';
                break;
                
            case 'date':
                $html .= '<input type="date" id="' . $fieldId . '" name="' . $fieldName . '" class="form-input" value="' . htmlspecialchars($value) . '" ' . $required . '>';
                break;
                
            case 'email':
                $placeholder = $field['placeholder_text'] ?? 'example@email.com';
                $html .= '<input type="email" id="' . $fieldId . '" name="' . $fieldName . '" class="form-input" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$" ' . $required . '>';
                $html .= '<div class="help-text">Must be a valid email address (e.g., name@example.com)</div>';
                break;
                
            case 'tel':
            case 'phone':
                $placeholder = $field['placeholder_text'] ?? '123-456-7890';
                $html .= '<input type="tel" id="' . $fieldId . '" name="' . $fieldName . '" class="form-input" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" pattern="[0-9\-]+" ' . $required . '>';
                $html .= '<div class="help-text">Enter phone number (digits and hyphens only)</div>';
                break;
                
            case 'url':
                $placeholder = $field['placeholder_text'] ?? 'https://example.com';
                $html .= '<input type="url" id="' . $fieldId . '" name="' . $fieldName . '" class="form-input" value="' . htmlspecialchars($value) . '" placeholder="' . htmlspecialchars($placeholder) . '" pattern="https?://.+" ' . $required . '>';
                $html .= '<div class="help-text">Must be a valid URL (e.g., https://example.com)</div>';
                break;
                
            case 'select':
                error_log("Rendering SELECT field with options");
                $html .= '<select id="' . $fieldId . '" name="' . $fieldName . '" class="form-input" ' . $required . '>';
                $html .= '<option value="">-- Select ' . htmlspecialchars($field['field_label']) . ' --</option>';
                
                $fieldOptions = $field['field_options'] ?? [];
                if (!empty($fieldOptions) && is_array($fieldOptions)) {
                    foreach ($fieldOptions as $optValue => $optLabel) {
                        $selected = ($value == $optValue) ? 'selected' : '';
                        $html .= '<option value="' . htmlspecialchars($optValue) . '" ' . $selected . '>' . htmlspecialchars($optLabel) . '</option>';
                    }
                } else {
                    $html .= '<option value="">No options available</option>';
                    error_log("WARNING: No options available for select field: " . $field['field_name']);
                }
                $html .= '</select>';
                break;
                
            case 'radio':
                $fieldOptions = $field['field_options'] ?? [];
                if (!empty($fieldOptions) && is_array($fieldOptions)) {
                    foreach ($fieldOptions as $optValue => $optLabel) {
                        $checked = ($value == $optValue) ? 'checked' : '';
                        $html .= '<div class="radio-option">';
                        $html .= '<input type="radio" id="' . $fieldId . '_' . $optValue . '" name="' . $fieldName . '" value="' . htmlspecialchars($optValue) . '" ' . $checked . ' ' . $required . '>';
                        $html .= '<label for="' . $fieldId . '_' . $optValue . '">' . htmlspecialchars($optLabel) . '</label>';
                        $html .= '</div>';
                    }
                } else {
                    $html .= '<div class="help-text" style="color: #dc3545;">No options configured for this radio field</div>';
                }
                break;
                
                case 'checkbox':
                    // FIXED: Checkbox now shows options like radio buttons with actual values
                    $fieldOptions = $field['field_options'] ?? [];
                    
                    error_log("Checkbox field options: " . print_r($fieldOptions, true));
                    error_log("Current checkbox value: " . $value);
                    
                    if (!empty($fieldOptions) && is_array($fieldOptions)) {
                        // Multiple checkbox options (like radio buttons)
                        $currentValues = is_array($value) ? $value : explode(',', $value);
                        error_log("Current values array: " . print_r($currentValues, true));
                        
                        foreach ($fieldOptions as $optValue => $optLabel) {
                            // For checkboxes with options, store the actual value not 1/0
                            $checked = in_array($optValue, $currentValues) ? 'checked' : '';
                            $html .= '<div class="checkbox-option">';
                            $html .= '<input type="checkbox" id="' . $fieldId . '_' . $optValue . '" name="' . $fieldName . '[]" value="' . htmlspecialchars($optValue) . '" ' . $checked . '>';
                            $html .= '<label for="' . $fieldId . '_' . $optValue . '">' . htmlspecialchars($optLabel) . '</label>';
                            $html .= '</div>';
                        }
                    } else {
                        // Single checkbox (stores 1/0) - this is the fallback for legacy fields
                        $checked = ($value == '1' || $value === true || $value === 1) ? 'checked' : '';
                        $html .= '<div class="checkbox-option">';
                        $html .= '<input type="checkbox" id="' . $fieldId . '" name="' . $fieldName . '" value="1" ' . $checked . '>';
                        $html .= '<label for="' . $fieldId . '">' . htmlspecialchars($field['field_label']) . '</label>';
                        $html .= '</div>';
                    }
                    break;
        }
        
        $html .= '</div>';
        error_log("=== RENDER FIELD END ===");
        return $html;
    }

    // FIXED: Normalize field types without changing email/tel/url to select
    private function normalizeFieldType($fieldType) {
        $typeMap = [
            'dropdown' => 'select',
            'select' => 'select',
            'list' => 'select',
            'options' => 'select',
            'radio' => 'radio',
            'radiobutton' => 'radio',
            'checkbox' => 'checkbox',
            'check' => 'checkbox',
            'text' => 'text',
            'string' => 'text',
            'textarea' => 'textarea',
            'number' => 'number',
            'integer' => 'number',
            'date' => 'date',
            'datetime' => 'date',
            // PRESERVE these types - DO NOT change to select
            'email' => 'email',
            'phone' => 'tel', 
            'tel' => 'tel',
            'telephone' => 'tel',
            'url' => 'url',
            'website' => 'url',
            'link' => 'url'
        ];
        
        $normalized = $typeMap[strtolower(trim($fieldType))] ?? 'text';
        
        error_log("Normalize: '$fieldType' -> '$normalized'");
        return $normalized;
    }


    // Add this method to debug field rendering
    public function debugFieldRendering($field) {
        error_log("=== FIELD RENDERING DEBUG ===");
        error_log("Field Name: " . $field['field_name']);
        error_log("Original Field Type: " . $field['field_type']);
        error_log("Field Options: " . print_r($field['field_options'] ?? 'None', true));
        
        $fieldType = strtolower(trim($field['field_type']));
        $normalizedType = $this->normalizeFieldType($fieldType);
        
        error_log("Normalized Field Type: " . $normalizedType);
        error_log("Should show options: " . (in_array($normalizedType, ['select', 'radio']) ? 'YES' : 'NO'));
        error_log("=== END DEBUG ===");
    }

    // Add this method to CustomFieldManager.php
    public function fixFieldTypesAutomatically() {
        try {
            // Fix 1: Fields with options but no type -> set to 'select'
            $stmt1 = $this->pdo->prepare("UPDATE custom_fields SET field_type = 'select' WHERE (field_type IS NULL OR field_type = '') AND field_options IS NOT NULL AND field_options != ''");
            $stmt1->execute();
            $fixed1 = $stmt1->rowCount();
            
            // Fix 2: Fields with 'dropdown' type -> set to 'select'
            $stmt2 = $this->pdo->prepare("UPDATE custom_fields SET field_type = 'select' WHERE field_type = 'dropdown'");
            $stmt2->execute();
            $fixed2 = $stmt2->rowCount();
            
            // Fix 3: Any remaining empty field types -> set to 'text'
            $stmt3 = $this->pdo->prepare("UPDATE custom_fields SET field_type = 'text' WHERE field_type IS NULL OR field_type = ''");
            $stmt3->execute();
            $fixed3 = $stmt3->rowCount();
            
            error_log("Field types fixed: $fixed1 fields with options->select, $fixed2 dropdown->select, $fixed3 empty->text");
            return ['success' => true, 'fixed' => $fixed1 + $fixed2 + $fixed3];
            
        } catch (Exception $e) {
            error_log("Error fixing field types: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Update field
    public function updateField($fieldId, $data) {
        try {
            // Prepare field options
            $options = '';
            if (isset($data['field_options']) && is_array($data['field_options']) && !empty($data['field_options'])) {
                $options = json_encode($data['field_options']);
            } else {
                $options = json_encode([]);
            }

            $sql = "UPDATE custom_fields SET 
                field_label = ?, 
                field_type = ?, 
                module = ?, 
                placeholder_text = ?, 
                default_value = ?, 
                help_text = ?, 
                field_options = ?, 
                is_required = ?, 
                field_order = ?,
                display_order = ?,
                updated_at = NOW()
            WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute([
                $data['field_label'],
                $data['field_type'],
                $data['module'],
                $data['placeholder_text'] ?? '',
                $data['default_value'] ?? '',
                $data['help_text'] ?? '',
                $options,
                $data['is_required'] ?? 0,
                $data['field_order'] ?? 0,
                $data['field_order'] ?? 0,
                $fieldId
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Custom Field Update Error: " . $e->getMessage());
            return false;
        }
    }

    // Toggle field status
    public function toggleFieldStatus($fieldId, $status) {
        try {
            $stmt = $this->pdo->prepare("UPDATE custom_fields SET is_active = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$status, $fieldId]);
        } catch (Exception $e) {
            error_log("Toggle Field Status Error: " . $e->getMessage());
            return false;
        }
    }

    // Delete field and its database column
    public function deleteField($fieldId) {
        try {
            $field = $this->getField($fieldId);
            if (!$field) {
                return false;
            }

            // Drop database column
            $this->dropFieldColumn($field);

            // Delete from custom_fields table
            $stmt = $this->pdo->prepare("DELETE FROM custom_fields WHERE id = ?");
            return $stmt->execute([$fieldId]);
            
        } catch (Exception $e) {
            error_log("Custom Field Delete Error: " . $e->getMessage());
            return false;
        }
    }

    // Drop database column when field is deleted
    private function dropFieldColumn($field) {
        try {
            $tableMap = $this->getModuleTableMap();
            $tableName = $tableMap[$field['module']] ?? null;
            
            if (!$tableName) {
                return false;
            }

            $columnName = 'cf_' . $field['field_name'];
            $sql = "ALTER TABLE `$tableName` DROP COLUMN `$columnName`";
            
            // Execute directly without prepared statement
            $this->pdo->exec($sql);
            return true;
            
        } catch (Exception $e) {
            error_log("Drop Field Column Error: " . $e->getMessage());
            return false;
        }
    }

    // Clean up orphaned or problematic fields
    public function cleanupOrphanedFields() {
        try {
            error_log("DEBUG: Starting cleanup of orphaned fields");
            
            // Find fields that might have database columns but no custom_fields record
            $tables = ['children', 'cases', 'foster_parents', 'donations', 'inventory', 'users'];
            $orphanedColumns = [];
            
            foreach ($tables as $table) {
                $stmt = $this->pdo->query("SHOW COLUMNS FROM `$table` LIKE 'cf_%'");
                $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                foreach ($columns as $column) {
                    $fieldName = str_replace('cf_', '', $column);
                    $checkStmt = $this->pdo->prepare("SELECT id FROM custom_fields WHERE field_name = ?");
                    $checkStmt->execute([$fieldName]);
                    
                    if (!$checkStmt->fetch()) {
                        $orphanedColumns[] = [
                            'table' => $table,
                            'column' => $column,
                            'field_name' => $fieldName
                        ];
                    }
                }
            }
            
            error_log("DEBUG: Found " . count($orphanedColumns) . " orphaned columns");
            return $orphanedColumns;
            
        } catch (Exception $e) {
            error_log("Cleanup Orphaned Fields Error: " . $e->getMessage());
            return [];
        }
    }
}
?>