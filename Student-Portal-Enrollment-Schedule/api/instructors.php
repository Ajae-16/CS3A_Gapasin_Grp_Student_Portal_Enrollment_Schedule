<?php
/**
 * Instructors API Endpoint
 *
 * Handles instructor data operations:
 * - GET: Fetch instructor(s) data
 * - POST: Create/Update instructor
 * - DELETE: Remove instructor
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

define('DB_ACCESS', true);
require_once '../database/db_config.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost();
        break;
    case 'DELETE':
        handleDelete();
        break;
    default:
        sendJSON(false, null, 'Invalid request method');
}

/**
 * Handle GET request - Fetch instructor(s)
 */
function handleGet() {
    if (isset($_GET['InstructorId'])) {
        // Get specific instructor
        $instructorId = sanitizeInput($_GET['InstructorId']);
        $sql = "SELECT InstructorId, FirstName, MiddleName, LastName, Email FROM instructors WHERE InstructorId = ?";
        $result = executeQuery($sql, [$instructorId], 's');
        
        if (!empty($result)) {
            sendJSON(true, $result[0], 'Instructor found');
        } else {
            sendJSON(false, null, 'Instructor not found');
        }
    } else {
        // Get all instructors
        $sql = "SELECT InstructorId, FirstName, MiddleName, LastName, Email FROM instructors ORDER BY LastName ASC, FirstName ASC";
        $result = executeQuery($sql);
        sendJSON(true, $result ?: [], 'Instructors retrieved');
    }
}

/**
 * Handle POST request - Create or Update instructor
 */
function handlePost() {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required = ['InstructorId', 'FirstName', 'LastName', 'Email'];
    $missing = validateRequired($input, $required);
    
    if (!empty($missing)) {
        sendJSON(false, null, 'Missing required fields: ' . implode(', ', $missing));
    }
    
    $instructorId = sanitizeInput($input['InstructorId']);
    $firstName = sanitizeInput($input['FirstName']);
    $middleName = sanitizeInput($input['MiddleName'] ?? '');
    $lastName = sanitizeInput($input['LastName']);
    $email = sanitizeInput($input['Email']);
    
    // Check if instructor exists
    $checkSql = "SELECT InstructorId FROM instructors WHERE InstructorId = ?";
    $existing = executeQuery($checkSql, [$instructorId], 's');
    
    if (!empty($existing)) {
        // Update existing instructor
        $sql = "UPDATE instructors SET FirstName = ?, MiddleName = ?, LastName = ?, Email = ? WHERE InstructorId = ?";
        $result = executeQuery($sql, [$firstName, $middleName, $lastName, $email, $instructorId], 'sssss');
        
        if ($result !== false) {
            sendJSON(true, ['InstructorId' => $instructorId], 'Instructor updated successfully');
        } else {
            sendJSON(false, null, 'Failed to update instructor');
        }
    } else {
        // Insert new instructor
        $sql = "INSERT INTO instructors (InstructorId, FirstName, MiddleName, LastName, Email) VALUES (?, ?, ?, ?, ?)";
        $result = executeQuery($sql, [$instructorId, $firstName, $middleName, $lastName, $email], 'sssss');
        
        if ($result !== false) {
            sendJSON(true, ['InstructorId' => $instructorId], 'Instructor created successfully');
        } else {
            sendJSON(false, null, 'Failed to create instructor');
        }
    }
}

/**
 * Handle DELETE request - Remove instructor
 */
function handleDelete() {
    if (!isset($_GET['InstructorId'])) {
        sendJSON(false, null, 'InstructorId is required');
    }
    
    $instructorId = sanitizeInput($_GET['InstructorId']);
    
    $sql = "DELETE FROM instructors WHERE InstructorId = ?";
    $result = executeQuery($sql, [$instructorId], 's');
    
    if ($result !== false) {
        sendJSON(true, null, 'Instructor deleted successfully');
    } else {
        sendJSON(false, null, 'Failed to delete instructor');
    }
}
?>