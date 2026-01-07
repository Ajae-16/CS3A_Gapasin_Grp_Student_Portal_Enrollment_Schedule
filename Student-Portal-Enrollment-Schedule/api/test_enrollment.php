<?php
/**
 * Quick diagnostic test for 2nd Semester Enrollment API
 * Place this in /public_html/api/test_enrollment.php
 * Visit: yoursite.com/api/test_enrollment.php
 */

echo "<h2>2nd Semester Enrollment API - Diagnostic Test</h2>";
echo "<pre>";

// Test 1: Check if db_config.php exists
$dbConfigPath = dirname(__DIR__) . '/database/db_config.php';
echo "1. Checking db_config.php...\n";
echo "   Path: $dbConfigPath\n";
echo "   Exists: " . (file_exists($dbConfigPath) ? "YES ✓" : "NO ✗") . "\n\n";

// Test 2: Try to include it
echo "2. Including db_config.php...\n";
if (file_exists($dbConfigPath)) {
    try {
        require_once $dbConfigPath;
        echo "   Loaded successfully ✓\n\n";
    } catch (Exception $e) {
        echo "   Error loading: " . $e->getMessage() . " ✗\n\n";
    }
} else {
    echo "   Cannot load - file not found ✗\n\n";
}

// Test 3: Check if functions exist
echo "3. Checking required functions...\n";
$functions = ['getDBConnection', 'executeQuery', 'sendJSON'];
foreach ($functions as $func) {
    echo "   $func: " . (function_exists($func) ? "YES ✓" : "NO ✗") . "\n";
}
echo "\n";

// Test 4: Try to connect to database
echo "4. Testing database connection...\n";
if (function_exists('getDBConnection')) {
    try {
        $conn = getDBConnection();
        if ($conn) {
            echo "   Connected successfully ✓\n";
            echo "   Database: " . $conn->select_db(getenv('DB_NAME') ?: 'u130505235_database') . "\n\n";
        } else {
            echo "   Connection failed ✗\n\n";
        }
    } catch (Exception $e) {
        echo "   Error: " . $e->getMessage() . " ✗\n\n";
    }
} else {
    echo "   getDBConnection not found ✗\n\n";
}

// Test 5: Check if student exists
echo "5. Testing student data query (202313511)...\n";
if (function_exists('executeQuery')) {
    try {
        $result = executeQuery("SELECT StudentId, FirstName, LastName FROM student_data WHERE StudentId = ?", ['202313511'], 's');
        if ($result && !empty($result)) {
            echo "   Student found: " . $result[0]['FirstName'] . " " . $result[0]['LastName'] . " ✓\n\n";
        } else {
            echo "   Student not found ✗\n\n";
        }
    } catch (Exception $e) {
        echo "   Error: " . $e->getMessage() . " ✗\n\n";
    }
} else {
    echo "   executeQuery not found ✗\n\n";
}

// Test 6: Check database tables
echo "6. Checking required tables...\n";
if (function_exists('getDBConnection')) {
    try {
        $conn = getDBConnection();
        $tables = ['enrollment_data', 'grades', 'schedule', 'student_data'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            echo "   $table: " . ($result && $result->num_rows > 0 ? "YES ✓" : "NO ✗") . "\n";
        }
        echo "\n";
    } catch (Exception $e) {
        echo "   Error: " . $e->getMessage() . " ✗\n\n";
    }
} else {
    echo "   Cannot check - getDBConnection not found ✗\n\n";
}

echo "</pre>";
echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ul>";
echo "<li>If all tests show ✓, the API should work</li>";
echo "<li>If any tests show ✗, that's the problem</li>";
echo "<li>Check your Hostinger error logs if you see any error messages</li>";
echo "<li>Delete this file after testing (it's for diagnosis only)</li>";
echo "</ul>";
?>
