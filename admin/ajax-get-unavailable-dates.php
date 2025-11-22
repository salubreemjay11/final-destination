<?php
session_start();

// Use the same database connection as above
$host = 'localhost';
$dbname = 'orphanfare';
$username = 'root'; // Change to your MySQL username
$password = ''; // Change to your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

header('Content-Type: application/json');

try {
    // Check if calendar_availability table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'calendar_availability'");
    if ($tableCheck->rowCount() == 0) {
        echo json_encode([
            'success' => true,
            'dates' => [],
            'message' => 'Availability table not created yet'
        ]);
        exit();
    }
    
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
        'error' => 'Failed to load unavailable dates',
        'dates' => []
    ]);
}
?>