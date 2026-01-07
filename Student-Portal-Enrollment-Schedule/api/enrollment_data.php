<?php
/**
 * Enrollment Data API Endpoint
 *
 * Handles all enrollment data operations:
 * - GET: Read enrollment records
 * - POST: Create new enrollment
 * - PUT: Update existing enrollment
 * - DELETE: Delete enrollment
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
 * Handle GET requests - Read enrollments
 */
function handleGet() {
    $enrollmentId = $_GET['EnrollmentId'] ?? null;
    $studentId = $_GET['StudentId'] ?? null;
    $programId = $_GET['ProgramId'] ?? null;
    $limit = $_GET['limit'] ?? null;
    $offset = $_GET['offset'] ?? null;

    if ($enrollmentId) {
        // Get specific enrollment
        $sql = "SELECT e.*, s.FirstName, s.LastName, c.CourseName
                FROM enrollment_data e
                JOIN student_data s ON e.StudentId = s.StudentId
                JOIN course_data c ON e.CourseId = c.CourseId
                WHERE e.EnrollmentId = ?";
        $result = executeQuery($sql, [$enrollmentId], 'i');

        if (!empty($result)) {
            sendJSON(true, $result[0]);
        } else {
            sendJSON(false, null, 'Enrollment not found');
        }
    } elseif ($studentId) {
        // Get enrollments for a student
        $sql = "SELECT e.*, c.CourseName, c.Unit
                FROM enrollment_data e
                JOIN course_data c ON e.CourseId = c.CourseId
                WHERE e.StudentId = ?
                ORDER BY e.CreatedAt DESC";
        if ($limit) {
            $sql .= " LIMIT ?";
            if ($offset) {
                $sql .= " OFFSET ?";
                $result = executeQuery($sql, [$studentId, (int)$limit, (int)$offset], 'sii');
            } else {
                $result = executeQuery($sql, [$studentId, (int)$limit], 'si');
            }
        } else {
            $result = executeQuery($sql, [$studentId], 's');
        }
        sendJSON(true, $result ?: []);
    } elseif ($programId) {
        // Get enrollments for a program
        $sql = "SELECT e.*, s.FirstName, s.LastName
                FROM enrollment_data e
                JOIN student_data s ON e.StudentId = s.StudentId
                WHERE e.ProgramId = ?
                ORDER BY e.CreatedAt DESC";
        if ($limit) {
            $sql .= " LIMIT ?";
            if ($offset) {
                $sql .= " OFFSET ?";
                $result = executeQuery($sql, [$programId, (int)$limit, (int)$offset], 'sii');
            } else {
                $result = executeQuery($sql, [$programId, (int)$limit], 'si');
            }
        } else {
            $result = executeQuery($sql, [$programId], 's');
        }
        sendJSON(true, $result ?: []);
    } else {
        // Get all enrollments
        $sql = "SELECT e.*, s.FirstName, s.LastName, c.CourseName, c.Unit
                FROM enrollment_data e
                JOIN student_data s ON e.StudentId = s.StudentId
                JOIN course_data c ON e.CourseId = c.CourseId
                ORDER BY e.CreatedAt DESC";
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
 * Handle POST requests - Create new enrollment
 */
function handlePost($data) {
    // Validate required fields
    $required = ['StudentId', 'ProgramId', 'YearLevel', 'Semester'];
    $missing = validateRequired($data, $required);

    if (!empty($missing)) {
        sendJSON(false, null, 'Missing required fields: ' . implode(', ', $missing));
    }

    // Sanitize input
    $studentId = sanitizeInput($data['StudentId']);
    $programId = sanitizeInput($data['ProgramId']);
    $yearLevel = sanitizeInput($data['YearLevel']);
    $semester = sanitizeInput($data['Semester']);

    // Check if student exists
    $sql = "SELECT StudentId FROM student_data WHERE StudentId = ?";
    $result = executeQuery($sql, [$studentId], 's');
    if (empty($result)) {
        sendJSON(false, null, 'Student not found');
    }

    // Check if program exists
    $sql = "SELECT ProgramId FROM program_data WHERE ProgramId = ?";
    $result = executeQuery($sql, [$programId], 's');
    if (empty($result)) {
        sendJSON(false, null, 'Program not found');
    }

    // Check if enrollment already exists
    $sql = "SELECT EnrollmentId FROM enrollment_data WHERE StudentId = ? AND ProgramId = ? AND YearLevel = ? AND Semester = ?";
    $result = executeQuery($sql, [$studentId, $programId, $yearLevel, $semester], 'ssss');
    if (!empty($result)) {
        sendJSON(false, null, 'Enrollment already exists for this student, program, year, and semester');
    }

    // Insert into database
    $sql = "INSERT INTO enrollment_data (StudentId, ProgramId, YearLevel, Semester) VALUES (?, ?, ?, ?)";

    $result = executeQuery($sql, [$studentId, $programId, $yearLevel, $semester], 'ssss');

    if ($result !== false) {
        $enrollmentId = getDBConnection()->insert_id;
        sendJSON(true, ['EnrollmentId' => $enrollmentId], 'Enrollment created successfully');
    } else {
        sendJSON(false, null, 'Failed to create enrollment');
    }
}

/**
 * Handle PUT requests - Update existing enrollment
 */
function handlePut($data) {
    if (empty($data['EnrollmentId'])) {
        sendJSON(false, null, 'Enrollment ID is required');
    }

    $enrollmentId = (int)$data['EnrollmentId'];

    // Build update query dynamically based on provided fields
    $updateFields = [];
    $params = [];
    $types = '';

    $allowedFields = ['StudentId', 'ProgramId', 'YearLevel', 'Semester'];

    foreach ($allowedFields as $field) {
        if (isset($data[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = sanitizeInput($data[$field]);
            $types .= 's';
        }
    }

    if (empty($updateFields)) {
        sendJSON(false, null, 'No fields to update');
    }

    // Add EnrollmentId to params
    $params[] = $enrollmentId;
    $types .= 'i';

    $sql = "UPDATE enrollment_data SET " . implode(', ', $updateFields) . " WHERE EnrollmentId = ?";
    $result = executeQuery($sql, $params, $types);

    if ($result !== false) {
        sendJSON(true, null, 'Enrollment updated successfully');
    } else {
        sendJSON(false, null, 'Failed to update enrollment');
    }
}

/**
 * Handle DELETE requests - Delete enrollment
 */
function handleDelete($data) {
    if (empty($data['EnrollmentId'])) {
        // Try to get from query string
        $enrollmentId = $_GET['EnrollmentId'] ?? null;
        if (!$enrollmentId) {
            sendJSON(false, null, 'Enrollment ID is required');
        }
    } else {
        $enrollmentId = $data['EnrollmentId'];
    }

    $enrollmentId = (int)$enrollmentId;

    // Delete from database
    $sql = "DELETE FROM enrollment_data WHERE EnrollmentId = ?";
    $result = executeQuery($sql, [$enrollmentId], 'i');

    if ($result !== false) {
        sendJSON(true, null, 'Enrollment deleted successfully');
    } else {
        sendJSON(false, null, 'Failed to delete enrollment');
    }
}
?>
