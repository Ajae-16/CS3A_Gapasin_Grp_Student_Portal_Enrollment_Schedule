<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

define('DB_ACCESS', true);

$dbPath = dirname(__DIR__) . '/database/db_config.php';
if (!file_exists($dbPath)) {
    die(json_encode(['success' => false, 'message' => 'DB config not found']));
}

require_once $dbPath;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$studentId = $_GET['StudentId'] ?? null;
$action = $_GET['action'] ?? null;
$input = [];

// For POST requests, get StudentId from JSON body
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $studentId = $input['StudentId'] ?? null;
}

if (!$studentId) {
    die(json_encode(['success' => false, 'message' => 'StudentId required']));
}

// GET: Check eligibility
if ($method === 'GET' && $action === 'check_eligibility') {
    $sql = "SELECT YearLevel FROM enrollment_data WHERE StudentId = ? AND Semester = '1st Semester' ORDER BY CreatedAt DESC LIMIT 1";
    $result = executeQuery($sql, [$studentId], 's');
    
    if (empty($result)) {
        die(json_encode(['success' => false, 'message' => 'No 1st semester enrollment']));
    }
    
    $yearLevel = $result[0]['YearLevel'];
    
    $gradesSql = "SELECT GradeValue FROM grades WHERE StudentId = ? AND Semester = '1st Semester'";
    $grades = executeQuery($gradesSql, [$studentId], 's');
    
    if (empty($grades)) {
        die(json_encode(['success' => false, 'message' => 'No grades found']));
    }
    
    $totalSubjects = count($grades);
    $passedSubjects = 0;
    $failedSubjects = 0;
    
    foreach ($grades as $grade) {
        if ((int)$grade['GradeValue'] <= 3) {
            $passedSubjects++;
        } else {
            $failedSubjects++;
        }
    }
    
    $studentType = ($failedSubjects === 0) ? 'Regular' : 'Irregular';
    
    die(json_encode([
        'success' => true,
        'data' => [
            'StudentId' => $studentId,
            'IsEligible' => true,
            'StudentType' => $studentType,
            'TotalSubjects' => $totalSubjects,
            'PassedSubjects' => $passedSubjects,
            'FailedSubjects' => $failedSubjects,
            'CurrentYearLevel' => $yearLevel
        ],
        'message' => 'Eligibility check passed'
    ]));
}

// GET: Get enrollment data
if ($method === 'GET') {
    $sql = "SELECT YearLevel FROM enrollment_data WHERE StudentId = ? AND Semester = '1st Semester' ORDER BY CreatedAt DESC LIMIT 1";
    $result = executeQuery($sql, [$studentId], 's');
    
    if (empty($result)) {
        die(json_encode(['success' => false, 'message' => 'No enrollment found']));
    }
    
    $yearLevel = $result[0]['YearLevel'];
    
    $gradesSql = "SELECT COUNT(*) as total, SUM(CASE WHEN GradeValue <= 3 THEN 1 ELSE 0 END) as passed FROM grades WHERE StudentId = ? AND Semester = '1st Semester'";
    $gradeSummary = executeQuery($gradesSql, [$studentId], 's');
    $failedCount = (int)$gradeSummary[0]['total'] - (int)$gradeSummary[0]['passed'];
    $studentType = ($failedCount === 0) ? 'Regular' : 'Irregular';
    
    $subjectsSql = "SELECT s.ScheduleId, s.CourseId, c.CourseName, c.Unit, s.InstructorId, CONCAT(COALESCE(i.FirstName, ''), ' ', COALESCE(i.LastName, '')) as InstructorName, s.Room, s.DayOfWeek, s.StartTime, s.EndTime FROM schedule s LEFT JOIN course_data c ON s.CourseId = c.CourseId LEFT JOIN instructors i ON s.InstructorId = i.InstructorId WHERE s.Semester = '2nd Semester' AND s.YearLevel = ? ORDER BY s.DayOfWeek, s.StartTime";
    $subjects = executeQuery($subjectsSql, [$yearLevel], 's');
    
    if (empty($subjects)) {
        die(json_encode(['success' => false, 'message' => 'No 2nd semester subjects available']));
    }
    
    $totalUnits = 0;
    foreach ($subjects as $subject) {
        $totalUnits += (int)($subject['Unit'] ?? 0);
    }
    
    $feePerUnit = 500.00;
    $miscFee = 1000.00;
    $irregularFee = ($studentType === 'Irregular') ? 2000.00 : 0.00;
    $totalFee = ($totalUnits * $feePerUnit) + $miscFee + $irregularFee;
    
    die(json_encode([
        'success' => true,
        'data' => [
            'StudentId' => $studentId,
            'StudentType' => $studentType,
            'YearLevel' => $yearLevel,
            'Subjects' => $subjects,
            'TotalUnits' => $totalUnits,
            'FeeBreakdown' => [
                'FeePerUnit' => $feePerUnit,
                'UnitCount' => $totalUnits,
                'UnitsFee' => (float)($totalUnits * $feePerUnit),
                'MiscFee' => $miscFee,
                'IrregularFee' => $irregularFee,
                'TotalFee' => (float)$totalFee
            ]
        ],
        'message' => 'Data retrieved'
    ]));
}

// POST: Process enrollment
if ($method === 'POST') {
    $programId = htmlspecialchars(trim($input['ProgramId'] ?? 'BSCS'), ENT_QUOTES, 'UTF-8');
    $yearLevel = htmlspecialchars(trim($input['YearLevel'] ?? '1'), ENT_QUOTES, 'UTF-8');
    
    $studentId = htmlspecialchars(trim($studentId), ENT_QUOTES, 'UTF-8');
    
    $studentCheck = executeQuery("SELECT StudentId FROM student_data WHERE StudentId = ?", [$studentId], 's');
    if (empty($studentCheck)) {
        die(json_encode(['success' => false, 'message' => 'Student not found']));
    }
    
    $existingCheck = executeQuery("SELECT EnrollmentId FROM enrollment_data WHERE StudentId = ? AND Semester = '2nd Semester'", [$studentId], 's');
    if (!empty($existingCheck)) {
        die(json_encode(['success' => false, 'message' => 'Already enrolled']));
    }
    
    $gradesSql = "SELECT COUNT(*) as total, SUM(CASE WHEN GradeValue <= 3 THEN 1 ELSE 0 END) as passed FROM grades WHERE StudentId = ? AND Semester = '1st Semester'";
    $gradeSummary = executeQuery($gradesSql, [$studentId], 's');
    $failedCount = (int)$gradeSummary[0]['total'] - (int)$gradeSummary[0]['passed'];
    $studentType = ($failedCount === 0) ? 'Regular' : 'Irregular';
    
    $feePerUnit = 500.00;
    $miscFee = 1000.00;
    $subjectsSql = "SELECT COALESCE(SUM(c.Unit), 0) as totalUnits FROM schedule s LEFT JOIN course_data c ON s.CourseId = c.CourseId WHERE s.Semester = '2nd Semester' AND s.YearLevel = ?";
    $unitsResult = executeQuery($subjectsSql, [$yearLevel], 's');
    $totalUnits = (int)($unitsResult[0]['totalUnits'] ?? 0);
    
    $irregularFee = ($studentType === 'Irregular') ? 2000.00 : 0.00;
    $totalFee = ($totalUnits * $feePerUnit) + $miscFee + $irregularFee;
    
    $enrollmentSql = "INSERT INTO enrollment_data (StudentId, ProgramId, YearLevel, Semester, StudentType, TotalUnits, TotalFee, FeePerUnit, IrregularFee, MiscFee, EnrollmentStatus) VALUES (?, ?, ?, '2nd Semester', ?, ?, ?, ?, ?, ?, 'Active')";
    executeQuery($enrollmentSql, [$studentId, $programId, $yearLevel, $studentType, $totalUnits, $totalFee, $feePerUnit, $irregularFee, $miscFee], 'sssiddddd');
    
    $lastEnrollment = executeQuery("SELECT EnrollmentId FROM enrollment_data WHERE StudentId = ? AND Semester = '2nd Semester' ORDER BY CreatedAt DESC LIMIT 1", [$studentId], 's');
    $enrollmentId = $lastEnrollment[0]['EnrollmentId'] ?? 0;
    
    $schedulesSql = "SELECT ScheduleId FROM schedule WHERE Semester = '2nd Semester' AND YearLevel = ?";
    $schedules = executeQuery($schedulesSql, [$yearLevel], 's');
    
    if (!empty($schedules)) {
        foreach ($schedules as $schedule) {
            $scheduleId = $schedule['ScheduleId'];
            $studentScheduleId = 'STSCH_' . $studentId . '_' . $scheduleId . '_2ND';
            $assignSql = "INSERT IGNORE INTO student_schedule (StudentScheduleId, StudentId, ScheduleId, EnrollmentStatus) VALUES (?, ?, ?, 'Enrolled')";
            executeQuery($assignSql, [$studentScheduleId, $studentId, $scheduleId], 'sss');
        }
    }
    
    die(json_encode([
        'success' => true,
        'data' => [
            'EnrollmentId' => $enrollmentId,
            'StudentId' => $studentId,
            'Semester' => '2nd Semester',
            'StudentType' => $studentType,
            'TotalUnits' => $totalUnits,
            'TotalFee' => $totalFee,
            'SchedulesAssigned' => count($schedules ?? []),
            'EnrollmentStatus' => 'Success'
        ],
        'message' => 'Enrolled successfully'
    ]));
}

die(json_encode(['success' => false, 'message' => 'Invalid request']));
?>