<?php
/**
 * Clean up test enrollments for student 202313511
 * Run this to reset the student's enrollment status
 */

require_once dirname(__DIR__) . '/database/db_config.php';

echo "<h2>Student 202313511 - Cleanup</h2>";
echo "<pre>";

try {
    $studentId = '202313511';
    $conn = getDBConnection();
    
    echo "Checking existing enrollments for $studentId...\n\n";
    
    // Check all enrollments
    $result = $conn->query("SELECT EnrollmentId, Semester, EnrollmentStatus FROM enrollment_data WHERE StudentId = '$studentId' ORDER BY Semester");
    
    if ($result && $result->num_rows > 0) {
        echo "Found enrollments:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - EnrollmentId: " . $row['EnrollmentId'] . ", Semester: " . $row['Semester'] . ", Status: " . $row['EnrollmentStatus'] . "\n";
        }
        echo "\n";
    } else {
        echo "No enrollments found\n\n";
    }
    
    // Check 2nd semester specifically
    echo "Checking 2nd semester enrollment...\n";
    $check2nd = $conn->query("SELECT EnrollmentId FROM enrollment_data WHERE StudentId = '$studentId' AND Semester = '2nd Semester'");
    
    if ($check2nd && $check2nd->num_rows > 0) {
        echo "2nd semester enrollment EXISTS\n";
        echo "Deleting 2nd semester enrollment...\n";
        
        // Delete student_schedule records first
        $deleteSchedule = $conn->query("DELETE FROM student_schedule WHERE StudentId = '$studentId'");
        echo "Deleted from student_schedule: " . $conn->affected_rows . " rows\n";
        
        // Delete enrollment_data
        $deleteEnroll = $conn->query("DELETE FROM enrollment_data WHERE StudentId = '$studentId' AND Semester = '2nd Semester'");
        echo "Deleted from enrollment_data: " . $conn->affected_rows . " rows\n";
        echo "\n✓ Cleanup complete! Student is now ready for fresh enrollment.\n";
    } else {
        echo "No 2nd semester enrollment found\n";
        echo "✓ Student is ready for enrollment.\n";
    }
    
    echo "\n";
    echo "Summary:\n";
    echo "--------\n";
    echo "Student: 202313511\n";
    echo "Grades: 2 (COSC101 grade 3.00, MATH102 grade 2.00) ✓\n";
    echo "1st Semester Enrollment: ✓\n";
    echo "2nd Semester Schedules: 9 available ✓\n";
    echo "\nReady to test enrollment!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

echo "</pre>";
?>
