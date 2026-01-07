<?php
session_start();

// Include database configuration
define('DB_ACCESS', true);
require_once '../database/db_config.php';

$username = $_SESSION['username'] ?? null;
$userId = $_SESSION['user_id'] ?? null;

// Remove from online users
if ($username) {
    $conn = getDBConnection();
    if ($conn) {
        $stmt = $conn->prepare("DELETE FROM online_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();

        // Log activity
        if ($userId) {
            logActivity($userId, $username, 'LOGOUT', 'AUTH', $userId, 'User logged out');
        }
    }
}

// Destroy session
session_unset();
session_destroy();

// Delete remember me cookie
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Redirect to login page
header('Location: ../index.php?logout=success');
exit;
?>
