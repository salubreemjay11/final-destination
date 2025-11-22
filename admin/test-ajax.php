<?php
// test-ajax.php - Simple test for AJAX endpoint
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Test successful', 'test' => true]);
?>