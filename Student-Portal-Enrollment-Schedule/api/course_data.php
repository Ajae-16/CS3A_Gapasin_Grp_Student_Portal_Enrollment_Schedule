<?php
/**
 * Course Data API Endpoint
 *
 * Handles all course data operations:
 * - GET: Read course records
 * - POST: Create new course
 * - PUT: Update existing course
 * - DELETE: Delete course
 */

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database configuration
define('DB_ACCESS', true);
require_once '../database/db_config.php';

// Start session for user tracking
session_start();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Get request data
$input = json_decode(file_get_contents('php://input'), true);

// Route to appropriate function
switch ($method) {
    case 'GET':
        handleGet();
        break;
    case 'POST':
        handlePost($input);
        break;
    case 'PUT':
        handlePut($input);
        break;
    case 'DELETE':
        handleDelete($input);
        break;
    default:
        sendJSON(false, null, 'Invalid request method');
}

/**
 * Handle GET requests - Read courses
 */
function handleGet() {
    $courseId = $_GET['CourseId'] ?? null;
    $search = $_GET['search'] ?? null;
    $limit = $_GET['limit'] ?? null;
    $offset = $_GET['offset'] ?? null;

    if ($courseId) {
        // Get specific course
        $sql = "SELECT * FROM course_data WHERE CourseId = ?";
        $result = executeQuery($sql, [$courseId], 's');

        if (!empty($result)) {
            sendJSON(true, $result[0]);
        } else {
            sendJSON(false, null, 'Course not found');
        }
    } elseif ($search) {
        // Search courses
        $searchTerm = "%$search%";
        $sql = "SELECT * FROM course_data
                WHERE CourseName LIKE ? OR CourseId LIKE ?
                ORDER BY CourseName ASC";
        if ($limit) {
            $sql .= " LIMIT ?";
            if ($offset) {
                $sql .= " OFFSET ?";
                $result = executeQuery($sql, [$searchTerm, $searchTerm, (int)$limit, (int)$offset], 'ssii');
            } else {
                $result = executeQuery($sql, [$searchTerm, $searchTerm, (int)$limit], 'ssi');
            }
        } else {
            $result = executeQuery($sql, [$searchTerm, $searchTerm], 'ss');
        }
        sendJSON(true, $result);
    } else {
        // Get all courses
        $sql = "SELECT * FROM course_data ORDER BY CourseName ASC";
        if ($limit) {
            $sql .= " LIMIT ?";
            if ($offset) {
                $sql .= " OFFSET ?";
                $result = executeQuery($sql, [(int)$limit, (int)$offset], 'ii');
            } else {
                $result = executeQuery($sql, [(int)$limit], 'i');
            }
        } else {
            $result = executeQuery($sql);
        }
        sendJSON(true, $result ?: []);
    }
}

/**
 * Handle POST requests - Create new course
 */
function handlePost($data) {
    // Validate required fields
    $required = ['CourseId', 'CourseName', 'Unit'];
    $missing = validateRequired($data, $required);

    if (!empty($missing)) {
        sendJSON(false, null, 'Missing required fields: ' . implode(', ', $missing));
    }

    // Sanitize input
    $courseId = sanitizeInput($data['CourseId']);
    $courseName = sanitizeInput($data['CourseName']);
    $unit = (int)$data['Unit'];

    // Check if CourseId already exists
    $sql = "SELECT CourseId FROM course_data WHERE CourseId = ?";
    $result = executeQuery($sql, [$courseId], 's');
    if (!empty($result)) {
        sendJSON(false, null, 'Course ID already exists');
    }

    // Insert into database
    $sql = "INSERT INTO course_data (CourseId, CourseName, Unit) VALUES (?, ?, ?)";

    $result = executeQuery($sql, [$courseId, $courseName, $unit], 'ssi');

    if ($result !== false) {
        sendJSON(true, ['CourseId' => $courseId], 'Course created successfully');
    } else {
        sendJSON(false, null, 'Failed to create course');
    }
}

/**
 * Handle PUT requests - Update existing course
 */
function handlePut($data) {
    if (empty($data['CourseId'])) {
        sendJSON(false, null, 'Course ID is required');
    }

    $courseId = sanitizeInput($data['CourseId']);

    // Build update query dynamically based on provided fields
    $updateFields = [];
    $params = [];
    $types = '';

    $allowedFields = ['CourseName', 'Unit'];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = $field === 'Unit' ? (int)$data[$field] : sanitizeInput($data[$field]);
            $types .= $field === 'Unit' ? 'i' : 's';
        }
    }

    if (empty($updateFields)) {
        sendJSON(false, null, 'No fields to update');
    }

    // Add CourseId to params
    $params[] = $courseId;
    $types .= 's';

    $sql = "UPDATE course_data SET " . implode(', ', $updateFields) . " WHERE CourseId = ?";
    $result = executeQuery($sql, $params, $types);

    if ($result !== false) {
        sendJSON(true, null, 'Course updated successfully');
    } else {
        sendJSON(false, null, 'Failed to update course');
    }
}

/**
 * Handle DELETE requests - Delete course
 */
function handleDelete($data) {
    if (empty($data['CourseId'])) {
        // Try to get from query string
        $courseId = $_GET['CourseId'] ?? null;
        if (!$courseId) {
            sendJSON(false, null, 'Course ID is required');
        }
    } else {
        $courseId = $data['CourseId'];
    }

    $courseId = sanitizeInput($courseId);

    // Delete from database
    $sql = "DELETE FROM course_data WHERE CourseId = ?";
    $result = executeQuery($sql, [$courseId], 's');

    if ($result !== false) {
        sendJSON(true, null, 'Course deleted successfully');
    } else {
        sendJSON(false, null, 'Failed to delete course');
    }
}
?>
