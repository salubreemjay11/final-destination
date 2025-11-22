<?php
require_once 'includes/superheader.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    die('Access denied');
}

// Your generateSqlDump function here
function generateSqlDump($conn) {
    // ... the function code from above
}

generateSqlDump($conn);