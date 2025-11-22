<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Administrator - Orphanfare</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #18338c 0%, #2a2a2a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .contact-container {
            background: #2a2a2a;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 100%;
            color: white;
        }
        
        .contact-title {
            color: #b8c5ff;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .contact-info {
            background: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .btn {
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <h1 class="contact-title">Contact Administrator</h1>
        
        <div class="contact-info">
            <h3>Having trouble logging in?</h3>
            <p>Please contact the system administrator for assistance:</p>
            <ul>
                <li><strong>Email:</strong> admin@orphanfare.org</li>
                <li><strong>Phone:</strong> +63 (969) 164-5421</li>
                <li><strong>Office Hours:</strong> 8:00 AM - 5:00 PM</li>
            </ul>
            <p>Please provide your email address and the issue you're experiencing when contacting the administrator.</p>
        </div>
        
        <div style="text-align: center;">
            <a href="login.php" class="btn">Back to Login</a>
        </div>
    </div>
</body>
</html>