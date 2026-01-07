<?php
/**
 * Student Schedule API Endpoint
 *
 * Handles schedule operations:
 * - GET: Fetch schedule(s) data
 * - POST: Create/Update schedule
 * - DELETE: Remove schedule
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

// Handle POST from form submission (not JSON)
if ($method === 'POST') {
    $action = $_POST['action'] ?? 'add';
    
    if ($action === 'add') {
        handleAdd();
    } elseif ($action === 'edit') {
        handleEdit();
    } elseif ($action === 'delete') {
        handleDelete();
    } else {
        sendJSON(false, null, 'Invalid action');
    }
} elseif ($method === 'GET') {
    handleGet();
} else {
    sendJSON(false, null, 'Invalid request method');
}

/**
 * Handle GET request - Fetch student's schedule
 */
function handleGet() {
    if (isset($_GET['StudentId'])) {
        $studentId = sanitizeInput($_GET['StudentId']);
        
        // First, check what's in enrollment_data for this student
        $debugSql = "SELECT StudentId, YearLevel, Semester FROM enrollment_data WHERE StudentId = ?";
        $debugResult = executeQuery($debugSql, [$studentId], 's');
        
        if (!empty($debugResult)) {
            $enrollment = $debugResult[0];
            $yearLevel = $enrollment['YearLevel'];
            $semester = $enrollment['Semester'];
            
            // Now query schedules with exact match
            $sql = "SELECT 
                        s.ScheduleId,
                        s.CourseId,
                        c.CourseName,
                        c.Unit,
                        s.InstructorId,
                        CONCAT(i.FirstName, ' ', i.LastName) as InstructorName,
                        s.Room,
                        s.DayOfWeek,
                        s.StartTime,
                        s.EndTime,
                        s.Semester,
                        s.YearLevel
                    FROM schedule s
                    JOIN course_data c ON s.CourseId = c.CourseId
                    JOIN instructors i ON s.InstructorId = i.InstructorId
                    WHERE s.YearLevel = ? AND s.Semester = ?
                    ORDER BY 
                        FIELD(s.DayOfWeek, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
                        s.StartTime ASC";
            
            $result = executeQuery($sql, [$yearLevel, $semester], 'ss');
            
            if ($result !== false && !empty($result)) {
                sendJSON(true, $result, 'Schedule retrieved successfully');
            } else {
                // Send debug info
                sendJSON(false, [
                    'debug' => [
                        'yearLevel' => $yearLevel,
                        'semester' => $semester,
                        'message' => 'No matching schedules found for this year level and semester'
                    ]
                ], 'No schedule found');
            }
        } else {
            sendJSON(false, [], 'Student not enrolled');
        }
    } else {
        sendJSON(false, null, 'StudentId parameter is required');
    }
}

/**
 * Handle Add schedule
 */
function handleAdd() {
    $required = ['ScheduleId', 'CourseId', 'InstructorId', 'Room', 'DayOfWeek', 'StartTime', 'EndTime', 'Semester', 'YearLevel'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            sendJSON(false, null, "Missing required field: $field");
        }
    }
    
    $scheduleId = sanitizeInput($_POST['ScheduleId']);
    $courseId = sanitizeInput($_POST['CourseId']);
    $instructorId = sanitizeInput($_POST['InstructorId']);
    $room = sanitizeInput($_POST['Room']);
    $dayOfWeek = sanitizeInput($_POST['DayOfWeek']);
    $startTime = sanitizeInput($_POST['StartTime']);
    $endTime = sanitizeInput($_POST['EndTime']);
    $semester = sanitizeInput($_POST['Semester']);
    $yearLevel = sanitizeInput($_POST['YearLevel']);
    
    // Check if schedule ID already exists
    $checkSql = "SELECT ScheduleId FROM schedule WHERE ScheduleId = ?";
    $existing = executeQuery($checkSql, [$scheduleId], 's');
    
    if (!empty($existing)) {
        sendJSON(false, null, 'Schedule ID already exists');
    }
    
    // Insert new schedule
    $sql = "INSERT INTO schedule (ScheduleId, CourseId, InstructorId, Room, DayOfWeek, StartTime, EndTime, Semester, YearLevel) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $result = executeQuery($sql, [$scheduleId, $courseId, $instructorId, $room, $dayOfWeek, $startTime, $endTime, $semester, $yearLevel], 'sssssssss');
    
    if ($result !== false) {
        sendJSON(true, ['ScheduleId' => $scheduleId], 'Schedule added successfully');
    } else {
        sendJSON(false, null, 'Failed to add schedule');
    }
}

/**
 * Handle Edit schedule
 */
function handleEdit() {
    $scheduleId = sanitizeInput($_POST['editScheduleId'] ?? $_POST['ScheduleId']);
    
    if (empty($scheduleId)) {
        sendJSON(false, null, 'Schedule ID is required');
    }
    
    $courseId = sanitizeInput($_POST['CourseId']);
    $instructorId = sanitizeInput($_POST['InstructorId']);
    $room = sanitizeInput($_POST['Room']);
    $dayOfWeek = sanitizeInput($_POST['DayOfWeek']);
    $startTime = sanitizeInput($_POST['StartTime']);
    $endTime = sanitizeInput($_POST['EndTime']);
    $semester = sanitizeInput($_POST['Semester']);
    $yearLevel = sanitizeInput($_POST['YearLevel']);
    
    // Update schedule
    $sql = "UPDATE schedule SET CourseId = ?, InstructorId = ?, Room = ?, DayOfWeek = ?, StartTime = ?, EndTime = ?, Semester = ?, YearLevel = ? 
            WHERE ScheduleId = ?";
    $result = executeQuery($sql, [$courseId, $instructorId, $room, $dayOfWeek, $startTime, $endTime, $semester, $yearLevel, $scheduleId], 'sssssssss');
    
    if ($result !== false) {
        sendJSON(true, ['ScheduleId' => $scheduleId], 'Schedule updated successfully');
    } else {
        sendJSON(false, null, 'Failed to update schedule');
    }
}

/**
 * Handle Delete schedule
 */
function handleDelete() {
    $scheduleId = sanitizeInput($_POST['ScheduleId']);
    
    if (empty($scheduleId)) {
        sendJSON(false, null, 'Schedule ID is required');
    }
    
    $sql = "DELETE FROM schedule WHERE ScheduleId = ?";
    $result = executeQuery($sql, [$scheduleId], 's');
    
    if ($result !== false) {
        sendJSON(true, null, 'Schedule deleted successfully');
    } else {
        sendJSON(false, null, 'Failed to delete schedule');
    }
}



?>