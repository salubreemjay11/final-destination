<?php
require_once '../config/database.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

try {
    // Get current and future unavailable dates
    $query = "SELECT unavailable_date, start_time, end_time, reason 
              FROM calendar_availability 
              WHERE unavailable_date >= CURDATE() 
              ORDER BY unavailable_date ASC 
              LIMIT 50";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $dates = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'dates' => $dates
    ]);
    
} catch (Exception $e) {
    error_log("Error loading unavailable dates: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load unavailable dates'
    ]);
}
?>