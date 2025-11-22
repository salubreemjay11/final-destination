
<?php
session_start();

// Check if user is logged in but 2FA is required
if (!isset($_SESSION['user_id']) || !isset($_SESSION['requires_2fa']) || $_SESSION['requires_2fa'] !== true) {
    header('Location: ../login.php');
    exit();
}

// Use mysqli connection (same as login.php)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "orphanfare";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Include TwoFactorAuth
require_once 'includes/TwoFactorAuth.php';
$twoFactorAuth = new TwoFactorAuth($conn);

$userId = $_SESSION['user_id'];

// Get user info including role
$stmt = $conn->prepare("SELECT username, email, role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $verificationCode = $_POST['verification_code'] ?? '';
    
    if (empty($verificationCode)) {
        $error = 'Please enter the verification code.';
    } elseif (strlen($verificationCode) !== 6 || !is_numeric($verificationCode)) {
        $error = 'Please enter a valid 6-digit code.';
    } else {
        // Get 2FA status
        $twoFactorStatus = $twoFactorAuth->get2FAStatus($userId);
        
        if ($twoFactorStatus && $twoFactorStatus['two_factor_enabled'] == 1) {
            // Verify the code
            if ($twoFactorAuth->verifyCode($twoFactorStatus['two_factor_secret'], $verificationCode)) {
                // 2FA successful - mark as verified and redirect to appropriate dashboard
                $_SESSION['2fa_verified'] = true;
                unset($_SESSION['requires_2fa']);
                
                // Log the successful 2FA verification
                error_log("2FA verification successful for user: " . $user['username'] . " with role: " . $user['role']);
                
                // Redirect based on user role
                if ($user['role'] === 'super_admin') {
                    header('Location: ../superadmin/superadmin.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid verification code. Please try again.';
            }
        } else {
            $error = 'Two-factor authentication is not enabled for your account.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication - Orphanfare</title>
    <link rel="stylesheet" href="../css/common.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #18338c 0%, #2a2a2a 100%);
            padding: 20px;
        }

        .login-card {
            background: #2a2a2a;
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            border: 1px solid #3a3a3a;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            text-align: center;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo h1 {
            color: #ffffff;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .login-logo p {
            color: #b8c5ff;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            color: #b8c5ff;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 6px;
            text-align: left;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            background-color: #1a1a1a;
            border: 1px solid #3a3a3a;
            border-radius: 6px;
            color: #ffffff;
            font-size: 18px;
            text-align: center;
            letter-spacing: 8px;
            transition: border-color 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .login-btn {
            width: 100%;
            padding: 12px;
            background: #3b82f6;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }

        .login-btn:hover {
            background-color: #2563eb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .user-info {
            background: rgba(59, 130, 246, 0.1);
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            border-left: 4px solid #3b82f6;
        }

        .user-info p {
            margin: 5px 0;
            color: #b8c5ff;
            font-size: 14px;
        }

        .back-link {
            margin-top: 20px;
        }

        .back-link a {
            color: #b8c5ff;
            text-decoration: none;
            font-size: 14px;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .help-text {
            color: #888;
            font-size: 12px;
            margin-top: 8px;
        }
        
        .role-badge {
            display: inline-block;
            background: rgba(139, 92, 246, 0.2);
            color: #8b5cf6;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            margin-left: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <h1>Orphanfare</h1>
                <p>Two-Factor Authentication</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <div class="user-info">
                <p><strong>User:</strong> <?php echo htmlspecialchars($user['username']); ?> 
                    <span class="role-badge"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $user['role']))); ?></span>
                </p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                
            </div>

            <form method="POST" action="login-2fa.php">
                <div class="form-group">
                    <label class="form-label">Enter 6-digit verification code</label>
                    <input type="text" 
                           class="form-input" 
                           name="verification_code" 
                           placeholder="000000" 
                           maxlength="6" 
                           pattern="[0-9]{6}"
                           required
                           autocomplete="off"
                           autofocus>
                    <div class="help-text">Enter the code from your authenticator app</div>
                </div>

                <button type="submit" class="login-btn">Verify & Continue</button>
            </form>

            <div class="back-link">
                <a href="../login.php">← Back to Login</a>
            </div>

            <div style="margin-top: 20px; padding: 15px; background: rgba(255, 193, 7, 0.1); border-radius: 6px;">
                <p style="color: #ffc107; font-size: 12px; margin: 0;">
                    <strong>Need help?</strong> Use your backup codes if you lost access to your authenticator app.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-advance and format the input
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.querySelector('input[name="verification_code"]');
            
            input.addEventListener('input', function(e) {
                // Remove non-numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
                
                // Auto-submit when 6 digits are entered
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
            
            // Prevent paste with non-numeric characters
            input.addEventListener('paste', function(e) {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').replace(/[^0-9]/g, '');
                this.value = pasteData.substring(0, 6);
                
                if (this.value.length === 6) {
                    this.form.submit();
                }
            });
        });
    </script>
</body>
</html>
