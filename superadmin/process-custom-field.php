<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in as super admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'super_admin') {
    echo "error:Unauthorized access";
    exit();
}

// Handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    if ($_GET['action'] === 'get_field' && isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM custom_fields WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $field = $result->fetch_assoc();
        
        if ($field) {
            echo json_encode(['success' => true, 'field' => $field]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Field not found']);
        }
        exit();
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data from POST
    $id = $_POST['id'] ?? '';
    $field_name = trim($_POST['field_name'] ?? '');
    $field_label = trim($_POST['field_label'] ?? '');
    $field_type = $_POST['field_type'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $default_value = trim($_POST['default_value'] ?? '');
    $is_required = isset($_POST['is_required']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $display_order = intval($_POST['display_order'] ?? 0);
    
    // Get modules
    $modules = $_POST['modules'] ?? [];
    $module = !empty($modules) ? $modules[0] : 'general';
    
    // Get field options
    $field_options = null;
    if (isset($_POST['field_options']) && !empty($_POST['field_options'])) {
        $options_array = json_decode($_POST['field_options'], true);
        if (is_array($options_array) && !empty($options_array)) {
            $field_options = json_encode($options_array);
        }
    }
    
    // Delete action
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (empty($_POST['id'])) {
            echo "error:Field ID is required for deletion";
            exit();
        }

        // Check if field is in use
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM custom_field_values WHERE field_id = ?");
        $stmt->bind_param("i", $_POST['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $usage = $result->fetch_assoc();
        
        if ($usage['count'] > 0) {
            echo "error:Field is currently in use and cannot be deleted";
            exit();
        }
        
        // Delete the field
        $stmt = $conn->prepare("DELETE FROM custom_fields WHERE id = ?");
        $stmt->bind_param("i", $_POST['id']);
        
        if ($stmt->execute()) {
            echo "success:Field deleted successfully";
        } else {
            echo "error:Failed to delete field";
        }
        exit();
    }
    
    // Validate required fields for create/update
    if (empty($field_name) || empty($field_label) || empty($field_type)) {
        echo "error:Field name, label, and type are required";
        exit();
    }
    
    // Sanitize field name
    $field_name = preg_replace('/[^a-z0-9_]/', '', strtolower($field_name));
    
    if (empty($field_name)) {
        echo "error:Invalid field name. Only lowercase letters, numbers and underscores allowed";
        exit();
    }

    if (!empty($id)) {
        // UPDATE existing field
        $sql = "UPDATE custom_fields SET 
                field_name = ?,
                field_label = ?,
                field_type = ?,
                field_options = ?,
                module = ?,
                is_required = ?,
                display_order = ?,
                is_active = ?,
                updated_at = NOW()
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiiiii", 
            $field_name,
            $field_label,
            $field_type,
            $field_options,
            $module,
            $is_required,
            $display_order,
            $is_active,
            $id
        );

        if ($stmt->execute()) {
            echo "success:Field updated successfully";
        } else {
            echo "error:Failed to update field: " . $conn->error;
        }
    } else {
        // CREATE new field
        // Check if field name already exists
        $stmt = $conn->prepare("SELECT id FROM custom_fields WHERE field_name = ?");
        $stmt->bind_param("s", $field_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "error:Field name already exists";
            exit();
        }

        $created_by = $_SESSION['user_id'] ?? 1;

        $sql = "INSERT INTO custom_fields (
                field_name, field_label, field_type, field_options,
                module, is_required, display_order, is_active, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssiiiii",
            $field_name,
            $field_label,
            $field_type,
            $field_options,
            $module,
            $is_required,
            $display_order,
            $is_active,
            $created_by
        );

        if ($stmt->execute()) {
            echo "success:Field created successfully";
        } else {
            echo "error:Failed to create field: " . $conn->error;
        }
    }
    exit();
}

echo "error:Invalid request";
$conn->close();
?>