<?php
/**
 * Database Configuration File for Hostinger
 */
// Prevent direct access
defined('DB_ACCESS') or define('DB_ACCESS', true);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u130505235_cvsuportal');
define('DB_PASS', 'Cvsuportal2025');
define('DB_NAME', 'u130505235_database');
define('DB_CHARSET', 'utf8mb4');

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Manila');

/**
 * Get database connection
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            error_log("Database connection failed: " . $conn->connect_error);
            return null;
        }
        $conn->set_charset(DB_CHARSET);
    }
    
    return $conn;
}

/**
 * Execute a prepared SQL query with error message retrieval
 * FIXED VERSION - Handles empty params correctly
 * 
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for the query
 * @param string $types Parameter types (e.g., 'ss' for two strings)
 * @param string|null $errorMessage Reference variable to store error message
 * @return array|bool|null Query results or false on failure
 */
function executeQuery($sql, $params = [], $types = '', &$errorMessage = null) {
    $conn = getDBConnection();
    if (!$conn) {
        if ($errorMessage !== null) $errorMessage = "No DB connection";
        error_log("executeQuery: No database connection");
        return false;
    }
    
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            $errMsg = "Prepare failed: " . $conn->error;
            if ($errorMessage !== null) $errorMessage = $errMsg;
            error_log("executeQuery Prepare Error: " . $errMsg . " | SQL: " . $sql);
            return false;
        }
        
        // Only bind parameters if both params and types are provided
        if (!empty($params) && !empty($types)) {
            if (!$stmt->bind_param($types, ...$params)) {
                $errMsg = "Bind param failed: " . $stmt->error;
                if ($errorMessage !== null) $errorMessage = $errMsg;
                error_log("executeQuery Bind Error: " . $errMsg);
                $stmt->close();
                return false;
            }
        }
        
        if (!$stmt->execute()) {
            $errMsg = "Execute failed: " . $stmt->error;
            if ($errorMessage !== null) $errorMessage = $errMsg;
            error_log("executeQuery Execute Error: " . $errMsg . " | SQL: " . $sql);
            $stmt->close();
            return false;
        }

        // Return data if SELECT
        if (stripos(trim($sql), 'SELECT') === 0) {
            $result = $stmt->get_result();
            if (!$result) {
                $errMsg = "Get result failed: " . $stmt->error;
                if ($errorMessage !== null) $errorMessage = $errMsg;
                error_log("executeQuery Get Result Error: " . $errMsg);
                $stmt->close();
                return false;
            }
            
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $stmt->close();
            
            error_log("executeQuery SELECT success: " . count($data) . " rows returned");
            return $data;
        }
        
        // For INSERT/UPDATE/DELETE, return true/false based on success
        $affected = $stmt->affected_rows;
        $stmt->close();
        
        error_log("executeQuery INSERT/UPDATE/DELETE success: " . $affected . " rows affected");
        return true;
        
    } catch (Exception $e) {
        $errMsg = "Exception: " . $e->getMessage();
        if ($errorMessage !== null) $errorMessage = $errMsg;
        error_log("executeQuery Exception: " . $errMsg);
        return false;
    }
}

/**
 * Send JSON response
 * @param bool $success Success status
 * @param mixed $data Response data
 * @param string $message Response message
 */
function sendJSON($success, $data = null, $message = '') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'data' => $data,
        'message' => $message
    ]);
    exit;
}

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate required fields
 * @param array $data Input data
 * @param array $required Required field names
 * @return array Missing field names
 */
function validateRequired($data, $required) {
    $missing = [];
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $missing[] = $field;
        }
    }
    return $missing;
}

/**
 * Log activity (optional - for student activities)
 * @param int $userId User ID
 * @param string $username Username
 * @param string $action Action performed
 * @param string $category Action category
 * @param string $targetId Target ID
 * @param string $details Action details
 */
function logActivity($userId, $username, $action, $category, $targetId, $details) {
    // Only log if activity_logs table exists
    $conn = getDBConnection();
    if (!$conn) return;
    
    $sql = "INSERT INTO activity_logs (userId, username, action, category, targetId, details, ipAddress) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param('sssssss', $userId, $username, $action, $category, $targetId, $details, $ipAddress);
        $stmt->execute();
        $stmt->close();
    }
}

// Initialize connection on include
$db_conn = getDBConnection();
if (!$db_conn) {
    error_log("Database connection failed during initialization");
}


/**
 * Begin database transaction
 */
function beginTransaction() {
    $conn = getDBConnection();
    if ($conn) {
        $conn->begin_transaction();
        return true;
    }
    return false;
}

/**
 * Commit database transaction
 */
function commitTransaction() {
    $conn = getDBConnection();
    if ($conn) {
        $conn->commit();
        return true;
    }
    return false;
}

/**
 * Rollback database transaction
 */
function rollbackTransaction() {
    $conn = getDBConnection();
    if ($conn) {
        $conn->rollback();
        return true;
    }
    return false;
}
?>