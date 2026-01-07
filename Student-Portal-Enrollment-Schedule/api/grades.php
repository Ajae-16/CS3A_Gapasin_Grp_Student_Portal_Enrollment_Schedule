<?php
header('Content-Type: application/json');
require_once '../database/db_config.php';

try {
    $studentId = isset($_GET['StudentId']) ? trim($_GET['StudentId']) : '';
    
    if (empty($studentId)) {
        echo json_encode(['success' => false, 'message' => 'Student ID is required']);
        exit;
    }
    
    // Fetch grades with course information
    $sql = "SELECT 
                g.GradeId,
                g.StudentId,
                g.CourseId,
                c.CourseName,
                c.Unit,
                g.GradeValue,
                g.MakeupGrade,
                g.FinalUnits,
                g.Remarks,
                g.SchoolYear,
                g.Semester
            FROM grades g
            LEFT JOIN course_data c ON g.CourseId = c.CourseId
            WHERE g.StudentId = ?
            ORDER BY g.SchoolYear DESC, g.Semester DESC, c.CourseName ASC";
    
    $grades = executeQuery($sql, [$studentId], 's');
    
    if ($grades === false) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }
    
    if (empty($grades)) {
        echo json_encode(['success' => true, 'data' => [], 'message' => 'No grades found']);
        exit;
    }
    
    echo json_encode(['success' => true, 'data' => $grades]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}