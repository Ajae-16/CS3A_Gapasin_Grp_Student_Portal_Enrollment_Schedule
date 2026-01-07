<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Login - CvSU Student Portal</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body class="login-page"
    style="background-image: url(images/background.png); background-position: center; background-repeat: no-repeat; background-size: cover;">
    <div class="login-box">
        <img src="images/logo.png" alt="CvSU Logo">

        <p style="font-weight: 600; color: var(--text-primary); font-size: 15px; margin-bottom: 16px;">
            Cavite State University<br>
            Cavite City Campus
        </p>

        <h2>Student Portal</h2>

        <p style="color: var(--text-secondary); margin-bottom: 24px;">
            Sign in to start your session
        </p>

        <input type="text" id="studentId" placeholder="Student Number" aria-label="Student Number" required>
        <input type="password" id="studentPass" placeholder="Password" aria-label="Password" required>

        <button id="loginBtn">Sign In</button>

        <div id="errorMessage" class="error" style="display: none; margin-top: 16px;"></div>
    </div>

    <script src="javascript/script.js"></script>

    <style>
        .login-footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e8ed;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }

        .forgot-link,
        .help-link {
            color: var(--primary-green);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .forgot-link:hover,
        .help-link:hover {
            color: var(--primary-green-dark);
            text-decoration: underline;
        }

        .divider {
            color: #adb5bd;
        }

        #errorMessage {
            padding: 12px;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            color: #721c24;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</body>

</html>