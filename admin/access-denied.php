<?php
$pageTitle = 'Access Denied - Orphanfare';
require_once 'includes/header.php';

// access-denied.php
echo "<!-- ACCESS DENIED DEBUG -->";
echo "<!-- User Role: " . ($_SESSION['role'] ?? 'NOT SET') . " -->";
echo "<!-- Requested Page: " . ($_SERVER['HTTP_REFERER'] ?? 'UNKNOWN') . " -->";
?>

<main class="main-content">
    <div class="access-denied" style="text-align: center; padding: 50px 20px;">
        <h1 style="color: #dc3545; font-size: 48px; margin-bottom: 20px;">⛔</h1>
        <h2 style="color: #ffffff; margin-bottom: 15px;">Access Denied</h2>
        <p style="color: #cccccc; font-size: 16px; margin-bottom: 30px;">
            You don't have permission to access this page.
        </p>
        <a href="dashboard.php" class="btn btn-primary">Return to Dashboard</a>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>