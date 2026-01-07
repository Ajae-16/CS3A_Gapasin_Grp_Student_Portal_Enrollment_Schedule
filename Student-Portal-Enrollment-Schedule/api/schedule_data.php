<?php
/**
 * Schedule Data API Endpoint
 *
 * Handles schedule data operations:
 * - GET: Read schedule records for a student
 */

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
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

// Route to appropriate function
switch ($method) {
    case 'GET':
        handleGet();
        break;
    default:
        sendJSON(false, null, 'Invalid request method');
}

/**
 * Handle GET requests - Read schedule for a student
 */
function handleGet() {
    $studentId = $_GET['StudentId'] ?? null;

    if (!$studentId) {
        sendJSON(false, null, 'Student ID is required');
    }

        // Get enrolled subjects and schedule info
        $sql = "SELECT ss.StudentScheduleId, ss.ScheduleId, ss.Section, ss.EnrollmentStatus,
                 s.CourseId, s.InstructorId, s.Room, s.DayOfWeek, s.StartTime, s.EndTime, s.Semester, s.YearLevel, s.SchoolYear,
                 c.CourseName, c.Unit,
                 CONCAT(i.FirstName, ' ', i.LastName) as InstructorName
             FROM student_schedule ss
             JOIN schedule s ON ss.ScheduleId = s.ScheduleId
             JOIN course_data c ON s.CourseId = c.CourseId
             JOIN instructors i ON s.InstructorId = i.InstructorId
             WHERE ss.StudentId = ?
             ORDER BY s.DayOfWeek, s.StartTime";

    $result = executeQuery($sql, [$studentId], 's');

    if ($result !== false) {
        sendJSON(true, $result ?: []);
    } else {
        sendJSON(false, null, 'Failed to fetch schedule');
    }
}
?>
