<?php
/**
 * Detailed Enrollment Logic Test
 * Tests the actual enrollment insert process
 */

require_once dirname(__DIR__) . '/database/db_config.php';

echo "<h2>Enrollment Insert Logic Test</h2>";
echo "<pre>";

// Test 1: Check if student_schedule table exists
echo "1. Checking student_schedule table...\n";
try {
    $conn = getDBConnection();
    $result = $conn->query("SHOW TABLES LIKE 'student_schedule'");
    if ($result && $result->num_rows > 0) {
        echo "   Table exists ✓\n";
        
        // Check structure
        $structResult = $conn->query("DESCRIBE student_schedule");
        echo "   Columns: ";
        $cols = [];
        while ($row = $structResult->fetch_assoc()) {
            $cols[] = $row['Field'];
        }
        echo implode(', ', $cols) . "\n\n";
    } else {
        echo "   Table NOT found ✗\n";
        echo "   This table is REQUIRED for enrollment!\n";
        echo "   Need to create it...\n\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . " ✗\n\n";
}

// Test 2: Check enrollment_data primary key
echo "2. Checking enrollment_data table...\n";
try {
    $conn = getDBConnection();
    $result = $conn->query("DESCRIBE enrollment_data");
    echo "   Columns and details:\n";
    $hasPK = false;
    while ($row = $result->fetch_assoc()) {
        $pk = ($row['Key'] === 'PRI') ? " [PRIMARY KEY]" : "";
        $extra = ($row['Extra'] === 'auto_increment') ? " [AUTO_INCREMENT]" : "";
        echo "      - " . $row['Field'] . " (" . $row['Type'] . ")" . $pk . $extra . "\n";
        if ($row['Key'] === 'PRI') {
            $hasPK = true;
        }
    }
    echo "   Has Primary Key: " . ($hasPK ? "YES ✓" : "NO ✗") . "\n\n";
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . " ✗\n\n";
}

// Test 3: Test the actual INSERT statement
echo "3. Testing enrollment INSERT statement...\n";
try {
    $conn = getDBConnection();
    $studentId = '202313511';
    $programId = 'BSCS';
    $yearLevel = '1';
    $studentType = 'Regular';
    $totalUnits = 18;
    $totalFee = 10000.00;
    $feePerUnit = 500.00;
    $irregularFee = 0.00;
    $miscFee = 1000.00;
    
    // First check if student already has 2nd sem enrollment
    $checkResult = $conn->query("SELECT EnrollmentId FROM enrollment_data WHERE StudentId = '$studentId' AND Semester = '2nd Semester'");
    if ($checkResult && $checkResult->num_rows > 0) {
        echo "   Student already enrolled for 2nd semester\n";
        echo "   Need to delete to test insert:\n";
        $deleteResult = $conn->query("DELETE FROM enrollment_data WHERE StudentId = '$studentId' AND Semester = '2nd Semester'");
        echo "   Deleted: " . $conn->affected_rows . " rows\n\n";
    }
    
    // Now try the insert with full column list
    $insertSQL = "INSERT INTO enrollment_data 
                  (StudentId, ProgramId, YearLevel, Semester, StudentType, TotalUnits, TotalFee, 
                   FeePerUnit, IrregularFee, MiscFee, EnrollmentStatus)
                  VALUES ('$studentId', '$programId', '$yearLevel', '2nd Semester', '$studentType', 
                          $totalUnits, $totalFee, $feePerUnit, $irregularFee, $miscFee, 'Active')";
    
    if ($conn->query($insertSQL)) {
        echo "   INSERT succeeded ✓\n";
        echo "   Inserted EnrollmentId: " . $conn->insert_id . "\n";
        echo "   Affected rows: " . $conn->affected_rows . "\n\n";
        
        // Clean up - delete the test record
        $conn->query("DELETE FROM enrollment_data WHERE StudentId = '$studentId' AND Semester = '2nd Semester' ORDER BY EnrollmentId DESC LIMIT 1");
        echo "   (Test record deleted)\n\n";
    } else {
        echo "   INSERT failed ✗\n";
        echo "   Error: " . $conn->error . "\n\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . " ✗\n\n";
}

// Test 4: Test schedule assignment
echo "4. Testing schedule assignment...\n";
try {
    $conn = getDBConnection();
    $studentId = '202313511';
    $yearLevel = '1';
    
    // Get schedules
    $schedules = $conn->query("SELECT ScheduleId FROM schedule WHERE Semester = '2nd Semester' AND YearLevel = '$yearLevel' LIMIT 2");
    $scheduleCount = $schedules->num_rows;
    echo "   Found $scheduleCount schedules for year level $yearLevel\n";
    
    if ($scheduleCount > 0) {
        // Test INSERT IGNORE for student_schedule
        $testScheduleId = null;
        while ($row = $schedules->fetch_assoc()) {
            $testScheduleId = $row['ScheduleId'];
            break;
        }
        
        if ($testScheduleId) {
            $studentScheduleId = 'TESTSCH_' . $studentId . '_' . $testScheduleId . '_2ND';
            $insertSQL = "INSERT IGNORE INTO student_schedule 
                         (StudentScheduleId, StudentId, ScheduleId, EnrollmentStatus)
                         VALUES ('$studentScheduleId', '$studentId', '$testScheduleId', 'Enrolled')";
            
            if ($conn->query($insertSQL)) {
                echo "   Schedule assignment INSERT succeeded ✓\n";
                
                // Clean up
                $conn->query("DELETE FROM student_schedule WHERE StudentScheduleId = '$studentScheduleId'");
                echo "   (Test record deleted)\n\n";
            } else {
                echo "   Schedule assignment INSERT failed ✗\n";
                echo "   Error: " . $conn->error . "\n\n";
            }
        }
    } else {
        echo "   No schedules found for testing\n\n";
    }
} catch (Exception $e) {
    echo "   Error: " . $e->getMessage() . " ✗\n\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><strong>Summary:</strong></p>";
echo "<ul>";
echo "<li>If all tests show ✓, the enrollment should work</li>";
echo "<li>If any test shows ✗, that's the blocking issue</li>";
echo "<li>If student_schedule table is missing, we need to create it</li>";
echo "<li>Delete this file after diagnosis</li>";
echo "</ul>";
?>
