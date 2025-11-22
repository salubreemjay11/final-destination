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
        
        if ($action === 'delete_foster' && !empty($fosterId)) {
            // Check if foster exists using foster_id column (which is the correct one)
            $stmt = $pdo->prepare("SELECT name, id FROM foster_parents WHERE foster_id = ?");
            $stmt->execute([$fosterId]);
            $foster = $stmt->fetch();
            
            if ($foster) {
                error_log("Found foster to delete: " . $foster['name'] . " (foster_id: $fosterId, id: " . $foster['id'] . ")");
                
                // First delete all associated documents from foster_documents table
                try {
                    $docStmt = $pdo->prepare("SELECT file_path FROM foster_documents WHERE foster_id = ?");
                    $docStmt->execute([$fosterId]);
                    $documents = $docStmt->fetchAll();
                    
                    // Delete physical files
                    foreach ($documents as $doc) {
                        if (file_exists($doc['file_path'])) {
                            unlink($doc['file_path']);
                        }
                    }
                    
                    // Delete document records from database
                    $deleteDocsStmt = $pdo->prepare("DELETE FROM foster_documents WHERE foster_id = ?");
                    $deleteDocsStmt->execute([$fosterId]);
                    error_log("Deleted foster documents for foster_id: $fosterId");
                    
                } catch (Exception $e) {
                    error_log("Error deleting foster documents: " . $e->getMessage());
                    // Continue with foster deletion even if document deletion fails
                }
                
                // Delete foster parent using foster_id column
                $deleteStmt = $pdo->prepare("DELETE FROM foster_parents WHERE foster_id = ?");
                $deleteStmt->execute([$fosterId]);
                
                $rowCount = $deleteStmt->rowCount();
                error_log("Foster deletion result: $rowCount row(s) affected");
                
                if ($rowCount > 0) {
                    logActivity($currentUser['id'], 'Foster Parent Deleted', 'foster_parents', $fosterId);
                    echo json_encode(['success' => true]);
                } else {
                    error_log("No rows affected when deleting foster_id: $fosterId");
                    echo json_encode(['success' => false, 'message' => 'No foster parent found with that ID']);
                }
            } else {
                error_log("Foster parent not found with foster_id: $fosterId");
                echo json_encode(['success' => false, 'message' => 'Foster parent not found']);
            }
        }
        elseif ($action === 'delete_document') {
            $docId = $_POST['doc_id'] ?? '';
            
            // First get the file path
            $stmt = $pdo->prepare("SELECT file_path, foster_id FROM documents WHERE id = ?");
            $stmt->execute([$docId]);
            $document = $stmt->fetch();
            
            if ($document) {
                // Delete the physical file
                if (file_exists($document['file_path'])) {
                    unlink($document['file_path']);
                }
                
                // Delete from database
                $deleteStmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
                $deleteStmt->execute([$docId]);
                
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Document not found']);
            }
        }
        elseif ($action === 'upload_documents' && !empty($fosterId)) {
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
                                // Insert into documents table
                                $stmt = $pdo->prepare("
                                    INSERT INTO documents (foster_id, name, type, file_path, date_uploaded, uploaded_by) 
                                    VALUES (?, ?, ?, ?, CURDATE(), ?)
                                ");
                                
                                $documentType = in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif']) ? 'Photo' : 'Document';
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
        else {
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid method']);
    }
} catch (Exception $e) {
    error_log("Process foster error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>