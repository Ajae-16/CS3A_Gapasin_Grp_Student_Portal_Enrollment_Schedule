<?php
/**
 * Authentication API Endpoint
 * 
 * Handles user authentication and session management:
 * - POST /login: User login
 * - POST /logout: User logout
 * - GET /session: Check session status
 * - GET /online: Get online users
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

define('DB_ACCESS', true);
require_once '../database/db_config.php';

session_start();

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'login';
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'POST':
        if ($action === 'login') {
            handleLogin($input);
        } elseif ($action === 'logout') {
            handleLogout();
        } else {
            sendJSON(false, null, 'Invalid action');
        }
        break;
    case 'GET':
        if ($action === 'session') {
            checkSession();
        } elseif ($action === 'online') {
            getOnlineUsers();
        } else {
            sendJSON(false, null, 'Invalid action');
        }
        break;
    default:
        sendJSON(false, null, 'Invalid request method');
}

/**
 * Handle user login
 */
function handleLogin($data) {
    $required = ['username', 'password'];
    $missing = validateRequired($data, $required);
    
    if (!empty($missing)) {
        sendJSON(false, null, 'Username and password are required');
    }
    
    $username = sanitizeInput($data['username']);
    $password = $data['password']; // Don't sanitize password
    
    // Get user from database
    $sql = "SELECT * FROM employees WHERE username = ? AND isActive = TRUE";
    $result = executeQuery($sql, [$username], 's');
    
    if (empty($result)) {
        logFailedLogin($username);
        sendJSON(false, null, 'Invalid username or password');
    }
    
    $user = $result[0];
    
    // Verify password
    // Check if password is hashed (starts with $2y$) or plain text (for migration)
    if (strpos($user['password'], '$2y$') === 0) {
        // Hashed password
        $passwordValid = password_verify($password, $user['password']);
    } else {
        // Plain text password (for backward compatibility)
        $passwordValid = ($password === $user['password']);
        
        // Hash the password for future use
        if ($passwordValid) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE employees SET password = ? WHERE id = ?";
            executeQuery($sql, [$hashedPassword, $user['id']], 'ss');
        }
    }
    
    if (!$passwordValid) {
        logFailedLogin($username);
        sendJSON(false, null, 'Invalid username or password');
    }
    
    // Create session
    $_SESSION['userId'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['userRole'] = strtolower($user['role']);
    $_SESSION['fullName'] = $user['fullName'];
    $_SESSION['loginTime'] = time();
    
    // Add to online users
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $sql = "INSERT INTO online_users (username, ipAddress) VALUES (?, ?)";
    executeQuery($sql, [$username, $ipAddress], 'ss');
    
    // Log activity
    logActivity($user['id'], $username, 'LOGIN', 'AUTH', $user['id'], 'User logged in');
    
    // Prepare response data
    $userData = [
        'id' => $user['id'],
        'username' => $user['username'],
        'fullName' => $user['fullName'],
        'role' => $user['role'],
        'firstName' => $user['firstName'],
        'lastName' => $user['lastName'],
        'redirect' => 'student-info.php' 
    ];
    
    sendJSON(true, $userData, 'Login successful');
}

/**
 * Handle user logout
 */
function handleLogout() {
    $username = $_SESSION['username'] ?? null;
    $userId = $_SESSION['userId'] ?? null;
    
    if ($username) {
        // Remove from online users
        $sql = "DELETE FROM online_users WHERE username = ?";
        executeQuery($sql, [$username], 's');
        
        // Log activity
        if ($userId) {
            logActivity($userId, $username, 'LOGOUT', 'AUTH', $userId, 'User logged out');
        }
    }
    
    // Destroy session
    session_unset();
    session_destroy();
    
    sendJSON(true, null, 'Logout successful');
}

/**
 * Check if user has active session
 */
function checkSession() {
    if (isset($_SESSION['userId']) && isset($_SESSION['username'])) {
        $userData = [
            'id' => $_SESSION['userId'],
            'username' => $_SESSION['username'],
            'fullName' => $_SESSION['fullName'] ?? '',
            'role' => $_SESSION['userRole'] ?? 'employee',
            'loginTime' => $_SESSION['loginTime'] ?? 0
        ];
        sendJSON(true, $userData, 'Session active');
    } else {
        sendJSON(false, null, 'No active session');
    }
}

/**
 * Get list of online users
 */
function getOnlineUsers() {
    $sql = "SELECT 
                ou.username,
                e.fullName,
                e.role,
                ou.loginTime,
                ou.lastActivity
            FROM online_users ou
            LEFT JOIN employees e ON ou.username = e.username
            WHERE ou.lastActivity >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ORDER BY ou.loginTime DESC";
    
    $result = executeQuery($sql);
    sendJSON(true, $result ?: []);
}

/**
 * Log failed login attempt
 */
function logFailedLogin($username) {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    logActivity('', $username, 'LOGIN', 'AUTH', '', "Failed login attempt from IP: $ipAddress");
}
?>

