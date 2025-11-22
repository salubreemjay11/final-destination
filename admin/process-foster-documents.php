<?php
// Enable error reporting
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

    $currentUser = [
        'id' => $_SESSION['user_id'] ?? 1,
        'full_name' => $_SESSION['username'] ?? 'Admin'
    ];

    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $fosterId = $_POST['foster_id'] ?? '';
        
        if ($action === 'upload_foster_documents' && !empty($fosterId)) {
            $uploadDir = 'uploads/foster/' . $fosterId . '/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $uploadedFiles = [];
            
            // Check if files were uploaded
            if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
                foreach ($_FILES['documents']['name'] as $key => $name) {
                    if ($_FILES['documents']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileName = basename($name);
                        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                        $newFileName = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9-_\.]/', '', $fileName);
                        $uploadPath = $uploadDir . $newFileName;
                        
                        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
                        if (in_array($fileExtension, $allowedExtensions)) {
                            if (move_uploaded_file($_FILES['documents']['tmp_name'][$key], $uploadPath)) {
                                // Determine document type based on extension
                                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    $documentType = 'Photo';
                                } else {
                                    $documentType = 'Document';
                                }
                                
                                // Insert into foster_documents table
                                $stmt = $pdo->prepare("
                                    INSERT INTO foster_documents (foster_id, name, type, file_path, date_uploaded, uploaded_by) 
                                    VALUES (?, ?, ?, ?, CURDATE(), ?)
                                ");
                                
                                $uploadedBy = $currentUser['full_name'] ?? 'System';
                                
                                $stmt->execute([
                                    $fosterId,
                                    $fileName,
                                    $documentType,
                                    $uploadPath,
                                    $uploadedBy
                                ]);
                                
                                $docId = $pdo->lastInsertId();
                                $uploadedFiles[] = [
                                    'id' => $docId,
                                    'name' => $fileName,
                                    'type' => $documentType,
                                    'path' => $uploadPath,
                                    'date_uploaded' => date('Y-m-d')
                                ];
                            }
                        }
                    }
                }
            }
            
            echo json_encode([
                'success' => true, 
                'files' => $uploadedFiles,
                'count' => count($uploadedFiles)
            ]);
            
        }
        elseif ($action === 'delete_foster_document') {
            $docId = $_POST['doc_id'] ?? '';
            
            // First get the file path
            $stmt = $pdo->prepare("SELECT file_path, foster_id FROM foster_documents WHERE id = ?");
            $stmt->execute([$docId]);
            $document = $stmt->fetch();
            
            if ($document) {
                // Delete the physical file
                if (file_exists($document['file_path'])) {
                    unlink($document['file_path']);
                }
                
                // Delete from database
                $deleteStmt = $pdo->prepare("DELETE FROM foster_documents WHERE id = ?");
                $deleteStmt->execute([$docId]);
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Document not found']);
            }
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid method']);
    }
} catch (Exception $e) {
    error_log("Process foster documents error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>