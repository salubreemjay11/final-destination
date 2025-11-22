<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers FIRST before any output
header('Content-Type: application/json');

try {
    // Get the absolute path to the project root
    $rootPath = dirname(__DIR__); // Goes up from admin folder to superadmin folder
    
    // Include required files with absolute paths
    require_once $rootPath . '/config/database.php';
    require_once $rootPath . '/admin/includes/auth.php';
    
    // Start session and check login
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Simple login check
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        echo json_encode([
            'success' => false,
            'message' => 'Authentication required'
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $fosterId = $_GET['foster_id'] ?? '';
        
        if (!empty($fosterId)) {
            // Check if foster_documents table exists
            $tableCheck = $pdo->query("SHOW TABLES LIKE 'foster_documents'")->fetch();
            if (!$tableCheck) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Foster documents table does not exist'
                ]);
                exit;
            }
            
            $stmt = $pdo->prepare("
                SELECT * FROM foster_documents 
                WHERE foster_id = ? 
                ORDER BY date_uploaded DESC, created_at DESC
            ");
            $stmt->execute([$fosterId]);
            $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'documents' => $documents
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Foster ID required'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid method'
        ]);
    }
} catch (Exception $e) {
    error_log("Error in get-foster-documents.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>