<?php
session_start();

// Include database configuration
define('DB_ACCESS', true);
require_once '../database/db_config.php';

header('Content-Type: application/json');

// Helper functions
function sendError($message) {
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

function sendSuccess($message, $data = []) {
    echo json_encode(array_merge(['success' => true, 'message' => $message], $data));
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendError('Invalid request method');
}

// Get and sanitize input
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validation: Check empty fields
if (empty($username) || empty($password)) {
    sendError('Username and password are required');
}

// Get database connection
$conn = getDBConnection();
if (!$conn) {
    sendError('Database connection failed. Please try again later.');
}

try {
    // Check if user exists and is active
    $stmt = $conn->prepare("SELECT accountId, firstName, lastName, fullName, username, password, userRole FROM accounts WHERE BINARY username = ? AND isActive = TRUE");
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        // Log failed login attempt
        logActivity('', $username, 'LOGIN', 'AUTH', '', 'Failed login attempt - user not found');
        sendError('Invalid username or password');
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Verify password
    if (!password_verify($password, $user['password'])) {
        // Log failed login attempt
        logActivity('', $username, 'LOGIN', 'AUTH', '', 'Failed login attempt - invalid password');
        sendError('Invalid username or password');
    }

    // Set session variables
    $_SESSION['logged_in'] = true;
    $_SESSION['user_id'] = $user['accountId'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['fullName'];
    $_SESSION['userRole'] = $user['userRole'];

    // Update online users table
    $stmt = $conn->prepare("INSERT INTO online_users (username, ipAddress) VALUES (?, ?) ON DUPLICATE KEY UPDATE lastActivity = NOW(), ipAddress = VALUES(ipAddress)");
    $stmt->bind_param("ss", $username, $_SERVER['REMOTE_ADDR']);
    $stmt->execute();
    $stmt->close();

    // Log successful login
    logActivity($user['accountId'], $username, 'LOGIN', 'AUTH', $user['accountId'], 'User logged in successfully');

    sendSuccess('Login successful', [
        'user' => [
            'id' => $user['accountId'],
            'username' => $user['username'],
            'fullName' => $user['fullName'],
            'role' => $user['userRole']
        ]
    ]);

} catch (Exception $e) {
    error_log("Login Error: " . $e->getMessage());
    sendError('An error occurred during login. Please try again.');
}
?>
