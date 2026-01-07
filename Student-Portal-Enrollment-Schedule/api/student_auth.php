

<?php
/**
 * Student Authentication API Endpoint
 *
 * Handles student authentication and session management:
 * - POST /login: Student login
 * - POST /logout: Student logout
 * - GET /session: Check student session status
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
            handleStudentLogin($input);
        } elseif ($action === 'logout') {
            handleStudentLogout();
        } else {
            sendJSON(false, null, 'Invalid action');
        }
        break;
    case 'GET':
        if ($action === 'session') {
            checkStudentSession();
        } else {
            sendJSON(false, null, 'Invalid action');
        }
        break;
    default:
        sendJSON(false, null, 'Invalid request method');
}

/**
 * Handle student login
 */
function handleStudentLogin($data) {
    $required = ['username', 'password'];
    $missing = validateRequired($data, $required);

    if (!empty($missing)) {
        sendJSON(false, null, 'Username and password are required');
    }

    $username = sanitizeInput($data['username']);
    $password = $data['password']; // Don't sanitize password

    // Get student account from database
    $sql = "SELECT sa.* FROM student_account sa WHERE sa.StudentId = ?";
    $result = executeQuery($sql, [$username], 's');

    if (empty($result)) {
        logFailedStudentLogin($username);
        sendJSON(false, null, 'Invalid username or password');
    }

    $student = $result[0];

    // Verify password
    // Check if password is hashed (starts with $2y$) or plain text (for migration)
    if (strpos($student['Password'], '$2y$') === 0) {
        // Hashed password
        $passwordValid = password_verify($password, $student['Password']);
    } else {
        // Plain text password (for backward compatibility)
        $passwordValid = ($password === $student['Password']);

        // Hash the password for future use
        if ($passwordValid) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE student_account SET Password = ? WHERE AccountId = ?";
            executeQuery($sql, [$hashedPassword, $student['AccountId']], 'si');
        }
    }

    if (!$passwordValid) {
        logFailedStudentLogin($username);
        sendJSON(false, null, 'Invalid username or password');
    }

    // Get student's year level from enrollment_data
    $sql = "SELECT YearLevel FROM enrollment_data WHERE StudentId = ? ORDER BY CreatedAt DESC LIMIT 1";
    $enrollmentResult = executeQuery($sql, [$username], 's');
    $yearLevel = !empty($enrollmentResult) ? $enrollmentResult[0]['YearLevel'] : 'Unknown';

    // Create session
    $_SESSION['logged_in'] = true;
    $_SESSION['StudentId'] = $student['AccountId'];
    $_SESSION['username'] = $student['StudentId'];
    $_SESSION['fullName'] = $student['FullName'];
    $_SESSION['userRole'] = $student['Role'];
    $_SESSION['yearLevel'] = $yearLevel;
    $_SESSION['loginTime'] = time();

    // Log activity (optional, since we don't have activity_logs table for students)
    // logStudentActivity($student['AccountId'], $username, 'LOGIN', 'AUTH', $student['AccountId'], 'Student logged in');

    // Prepare response data
    $userData = [
        'id' => $student['AccountId'],
        'username' => $student['StudentId'],
        'fullName' => $student['FullName'],
        'yearLevel' => $yearLevel,
        'role' => $student['Role'],
        'redirect' => 'verify_year.php' 
    ];

    sendJSON(true, $userData, 'Login successful');
}

/**
 * Handle student logout
 */
function handleStudentLogout() {
    $username = $_SESSION['username'] ?? null;
    $studentId = $_SESSION['StudentId'] ?? null;

    // Destroy session
    session_unset();
    session_destroy();

    sendJSON(true, null, 'Logout successful');
}

/**
 * Check if student has active session
 */
function checkStudentSession() {
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['StudentId'])) {
        $userData = [
            'id' => $_SESSION['StudentId'],
            'username' => $_SESSION['username'] ?? '',
            'fullName' => $_SESSION['fullName'] ?? '',
            'yearLevel' => $_SESSION['yearLevel'] ?? '',
            'role' => $_SESSION['userRole'] ?? 'student',
            'loginTime' => $_SESSION['loginTime'] ?? 0
        ];
        sendJSON(true, $userData, 'Session active');
    } else {
        sendJSON(false, null, 'No active session');
    }
}

/**
 * Log failed student login attempt
 */
function logFailedStudentLogin($username) {
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    // Since we don't have activity_logs for students, we'll just log to PHP error log
    error_log("Failed student login attempt for username: $username from IP: $ipAddress");
}
?>
