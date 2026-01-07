<?php
define('DB_ACCESS', true);
require_once 'database/db_config.php';

echo "Testing database connection...<br>";

$conn = getDBConnection();
if ($conn) {
    echo "✅ Database connected successfully!<br>";
    
    // Test query
    $result = executeQuery("SELECT COUNT(*) as count FROM student_account");
    if ($result) {
        echo "✅ Query executed successfully!<br>";
        echo "Student accounts found: " . $result[0]['count'] . "<br>";
    } else {
        echo "❌ Query failed!<br>";
    }
} else {
    echo "❌ Database connection failed!<br>";
}
?>