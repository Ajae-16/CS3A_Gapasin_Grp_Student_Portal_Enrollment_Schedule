<?php
/**
 * Student Data API Endpoint
 *
 * Handles all student data operations:
 * - GET: Read student records
 * - POST: Create new student
 * - PUT: Update existing student
 * - DELETE: Delete student
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
 * Handle GET requests - Read students
 */
function handleGet() {
    $studentId = $_GET['StudentId'] ?? null;
    $search = $_GET['search'] ?? null;
    $limit = $_GET['limit'] ?? null;
    $offset = $_GET['offset'] ?? null;

    if ($studentId) {
        // Get specific student with enrollment data
        try {
            $sql = "SELECT s.*, e.ProgramId, e.YearLevel, e.Semester
                    FROM student_data s
                    LEFT JOIN enrollment_data e ON s.StudentId = e.StudentId
                    WHERE s.StudentId = ?";
            $result = executeQuery($sql, [$studentId], 's');

            if (!empty($result)) {
                // Return with success: true at top level for modal compatibility
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'student' => $result[0]]);
                exit;
            } else {
                sendJSON(false, null, 'Student not found');
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    } elseif ($search) {
        // Search students
        $searchTerm = "%$search%";
        $sql = "SELECT * FROM student_data
            WHERE FirstName LIKE ? OR LastName LIKE ? OR StudentId LIKE ?
            ORDER BY LastName ASC, FirstName ASC";
        if ($limit) {
            $sql .= " LIMIT ?";
            if ($offset) {
                $sql .= " OFFSET ?";
                $result = executeQuery($sql, [$searchTerm, $searchTerm, $searchTerm, (int)$limit, (int)$offset], 'sssii');
            } else {
                $result = executeQuery($sql, [$searchTerm, $searchTerm, $searchTerm, (int)$limit], 'sssi');
            }
        } else {
            $result = executeQuery($sql, [$searchTerm, $searchTerm, $searchTerm], 'sss');
        }
        sendJSON(true, $result);
    } else {
        // Get all students
        $sql = "SELECT * FROM student_data ORDER BY LastName ASC, FirstName ASC";
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
 * Handle POST requests - Create new student
 */
function handlePost($data) {
    // Validate required fields
    $required = ['StudentId', 'FirstName', 'LastName', 'DateOfBirth', 'Sex', 'Citizenship', 'StreetName', 'Barangay', 'Municipality', 'CivilStatus', 'Religion', 'Email', 'ContactNumber'];
    $missing = validateRequired($data, $required);

    if (!empty($missing)) {
        sendJSON(false, null, 'Missing required fields: ' . implode(', ', $missing));
    }

    // Sanitize input
    $studentId = sanitizeInput($data['StudentId']);
    $firstName = sanitizeInput($data['FirstName']);
    $middleName = isset($data['MiddleName']) ? sanitizeInput($data['MiddleName']) : null;
    $lastName = sanitizeInput($data['LastName']);
    $major = isset($data['Major']) ? sanitizeInput($data['Major']) : null;
    $dateOfBirth = sanitizeInput($data['DateOfBirth']);
    $sex = sanitizeInput($data['Sex']);
    $citizenship = sanitizeInput($data['Citizenship']);
    $streetName = sanitizeInput($data['StreetName']);
    $barangay = sanitizeInput($data['Barangay']);
    $province = isset($data['Province']) ? sanitizeInput($data['Province']) : null;
    $municipality = sanitizeInput($data['Municipality']);
    $civilStatus = sanitizeInput($data['CivilStatus']);
    $religion = sanitizeInput($data['Religion']);
    $email = sanitizeInput($data['Email']);
    $contactNumber = sanitizeInput($data['ContactNumber']);
    $guardianName = isset($data['GuardianName']) ? sanitizeInput($data['GuardianName']) : null;
    $guardianContact = isset($data['GuardianContact']) ? sanitizeInput($data['GuardianContact']) : null;
    $fatherName = isset($data['FatherName']) ? sanitizeInput($data['FatherName']) : null;
    $fatherOccupation = isset($data['FatherOccupation']) ? sanitizeInput($data['FatherOccupation']) : null;
    $motherName = isset($data['MotherName']) ? sanitizeInput($data['MotherName']) : null;
    $motherOccupation = isset($data['MotherOccupation']) ? sanitizeInput($data['MotherOccupation']) : null;

    // Validate sex
    if (!in_array($sex, ['Male', 'Female', 'Other'])) {
        sendJSON(false, null, 'Invalid sex value');
    }

    // Check if Student_Id already exists
    $sql = "SELECT StudentId FROM student_data WHERE StudentId = ?";
    $result = executeQuery($sql, [$studentId], 's');
    if (!empty($result)) {
        sendJSON(false, null, 'Student ID already exists');
    }

    // Insert into database
        $sql = "INSERT INTO student_data (StudentId, FirstName, MiddleName, LastName, Major, DateOfBirth, Sex, Citizenship, StreetName, Barangay, Province, Municipality, CivilStatus, Religion, Email, ContactNumber, GuardianName, GuardianContact, FatherName, FatherOccupation, MotherName, MotherOccupation)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $result = executeQuery($sql, [
        $studentId, $firstName, $middleName, $lastName, $major, $dateOfBirth, $sex, $citizenship, $streetName, $barangay, $province, $municipality, $civilStatus, $religion, $email, $contactNumber, $guardianName, $guardianContact, $fatherName, $fatherOccupation, $motherName, $motherOccupation
    ], 'ssssssssssssssssssssss');

    if ($result !== false) {
        // Log activity if needed
        sendJSON(true, ['StudentId' => $studentId], 'Student created successfully');
    } else {
        sendJSON(false, null, 'Failed to create student');
    }
}

/**
 * Handle PUT requests - Update existing student
 */
function handlePut($data) {
    if (empty($data['StudentId'])) {
        sendJSON(false, null, 'Student ID is required');
    }

    $studentId = sanitizeInput($data['StudentId']);

    // Build update query dynamically based on provided fields
    $updateFields = [];
    $params = [];
    $types = '';

    $allowedFields = ['FirstName', 'MiddleName', 'LastName', 'Major', 'DateOfBirth', 'Sex', 'Citizenship', 'StreetName', 'Barangay', 'Province', 'Municipality', 'CivilStatus', 'Religion', 'Email', 'ContactNumber', 'GuardianName', 'GuardianContact', 'FatherName', 'FatherOccupation', 'MotherName', 'MotherOccupation'];

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

    // Add Student_Id to params
    $params[] = $studentId;
    $types .= 's';

    $sql = "UPDATE student_data SET " . implode(', ', $updateFields) . " WHERE StudentId = ?";
    $result = executeQuery($sql, $params, $types);

    if ($result !== false) {
        sendJSON(true, null, 'Student updated successfully');
    } else {
        sendJSON(false, null, 'Failed to update student');
    }
}

/**
 * Handle DELETE requests - Delete student
 */
function handleDelete($data) {
    if (empty($data['StudentId'])) {
        // Try to get from query string
        $studentId = $_GET['StudentId'] ?? null;
        if (!$studentId) {
            sendJSON(false, null, 'Student ID is required');
        }
    } else {
        $studentId = $data['StudentId'];
    }

    $studentId = sanitizeInput($studentId);

    // Delete from database
    $sql = "DELETE FROM student_data WHERE StudentId = ?";
    $result = executeQuery($sql, [$studentId], 's');

    if ($result !== false) {
        sendJSON(true, null, 'Student deleted successfully');
    } else {
        sendJSON(false, null, 'Failed to delete student');
    }
}
?>
