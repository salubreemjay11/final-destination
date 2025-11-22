<?php
class SimpleCustomSaver {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function saveAllCustomFields($recordId) {
        error_log("🎯 === SIMPLE CUSTOM SAVER START ===");
        error_log("Saving for record: $recordId");
        
        $savedCount = 0;
        
        foreach ($_POST as $fieldName => $fieldValue) {
            if (strpos($fieldName, 'custom_field_') === 0) {
                $dbColumn = 'cf_' . str_replace('custom_field_', '', $fieldName);
                
                error_log("💾 Saving: $dbColumn = '$fieldValue'");
                
                try {
                    // DIRECT UPDATE - No complex logic
                    $sql = "UPDATE children SET `$dbColumn` = ? WHERE child_id = ?";
                    $stmt = $this->pdo->prepare($sql);
                    $result = $stmt->execute([$fieldValue, $recordId]);
                    
                    if ($result) {
                        error_log("✅ SUCCESS: Saved $dbColumn");
                        $savedCount++;
                    } else {
                        error_log("❌ FAILED: Could not save $dbColumn");
                    }
                } catch (Exception $e) {
                    error_log("🚨 ERROR: " . $e->getMessage());
                }
            }
        }
        
        error_log("📊 Total saved: $savedCount custom fields");
        error_log("🎯 === SIMPLE CUSTOM SAVER END ===");
        
        return $savedCount;
    }
}
?>