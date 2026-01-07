<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/db_config.php';

header('Content-Type: application/json');

// Get ProgramId from query parameter
$programId = isset($_GET['ProgramId']) ? trim($_GET['ProgramId']) : '';

if (empty($programId)) {
    echo json_encode(['success' => false, 'message' => 'Program ID is required']);
    exit;
}

try {
    // Fetch courses for the program
    $sql = "SELECT CourseId FROM program_subjects WHERE ProgramId = ? ORDER BY CourseId ASC";
    $courses = executeQuery($sql, [$programId], 's') ?: [];
    
    // Extract just the CourseIds
    $courseIds = [];
    foreach ($courses as $course) {
        $courseIds[] = $course['CourseId'];
    }
    
    echo json_encode(['success' => true, 'courses' => $courseIds]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching courses: ' . $e->getMessage()]);
}
?>
