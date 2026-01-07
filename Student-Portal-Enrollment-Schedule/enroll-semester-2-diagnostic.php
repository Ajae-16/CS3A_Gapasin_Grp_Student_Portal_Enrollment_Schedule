<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Redirect admin to their page
$userRole = strtolower($_SESSION['userRole'] ?? 'student');
if ($userRole !== 'student') {
    header('Location: schedule-management.php');
    exit;
}

// Get user information
$studentId = $_SESSION['username'] ?? '';
$userName = $_SESSION['fullName'] ?? '';
$yearLevel = $_SESSION['yearLevel'] ?? '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>2nd Semester Enrollment - Diagnostic</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .container { max-width: 1200px; margin: 30px auto; padding: 20px; }
        .card { background: white; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
    </style>
</head>
<body>
    <div class="nav">
        <nav>
            <img src="images/logo.png" alt="CvSU Logo" class="logo">
            <span>CvSU CCC Student Portal - Enrollment Diagnostic</span>
        </nav>
    </div>

    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="card">
                <h2>2nd Semester Enrollment - Diagnostic Mode</h2>
                <p>This page shows real-time debugging information.</p>
            </div>

            <div class="card">
                <h3>Session Information</h3>
                <div class="info">
                    <strong>StudentId:</strong> <?php echo htmlspecialchars($studentId); ?><br>
                    <strong>Full Name:</strong> <?php echo htmlspecialchars($userName); ?><br>
                    <strong>Year Level (Session):</strong> <?php echo htmlspecialchars($yearLevel); ?><br>
                    <strong>User Role:</strong> <?php echo htmlspecialchars($userRole); ?>
                </div>
            </div>

            <div class="card">
                <h3>API Testing</h3>
                <p>Click buttons below to test API endpoints:</p>
                <button onclick="testEligibility()">Test Eligibility Check</button>
                <button onclick="testEnrollmentData()">Test Enrollment Data</button>
                <div id="apiResults"></div>
            </div>

            <div class="card">
                <h3>Console Debug Output</h3>
                <p>Open your browser's F12 Console (Ctrl+Shift+J) and check for errors.</p>
                <p>Look for messages like:</p>
                <pre>StudentId: 202313162
Eligibility URL: api/enroll_second_semester.php?StudentId=...
Eligibility Response: {...}</pre>
            </div>

            <div class="card">
                <h3>Enrollment Form (Debug)</h3>
                <div id="enrollmentDebug"></div>
            </div>
        </div>
    </main>

    <script>
        const studentId = '<?php echo htmlspecialchars($studentId); ?>';
        const yearLevel = '<?php echo htmlspecialchars($yearLevel); ?>';

        // Test eligibility
        async function testEligibility() {
            console.log('Testing Eligibility...');
            const url = `api/enroll_second_semester.php?StudentId=${encodeURIComponent(studentId)}&action=check_eligibility`;
            console.log('URL:', url);
            
            try {
                const response = await fetch(url);
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);
                
                const resultsDiv = document.getElementById('apiResults');
                resultsDiv.innerHTML += `
                    <div class="info">
                        <strong>Eligibility Check Result:</strong><br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
            } catch (error) {
                console.error('Error:', error);
                const resultsDiv = document.getElementById('apiResults');
                resultsDiv.innerHTML += `
                    <div class="error">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            }
        }

        // Test enrollment data
        async function testEnrollmentData() {
            console.log('Testing Enrollment Data...');
            const url = `api/enroll_second_semester.php?StudentId=${encodeURIComponent(studentId)}`;
            console.log('URL:', url);
            
            try {
                const response = await fetch(url);
                console.log('Response status:', response.status);
                const data = await response.json();
                console.log('Response data:', data);
                
                const resultsDiv = document.getElementById('apiResults');
                resultsDiv.innerHTML += `
                    <div class="info">
                        <strong>Enrollment Data Result:</strong><br>
                        <pre>${JSON.stringify(data, null, 2)}</pre>
                    </div>
                `;
            } catch (error) {
                console.error('Error:', error);
                const resultsDiv = document.getElementById('apiResults');
                resultsDiv.innerHTML += `
                    <div class="error">
                        <strong>Error:</strong> ${error.message}
                    </div>
                `;
            }
        }

        // Auto-test on load
        window.addEventListener('load', function() {
            console.log('=== ENROLLMENT PAGE LOADED ===');
            console.log('StudentId:', studentId);
            console.log('YearLevel:', yearLevel);
            
            const debugDiv = document.getElementById('enrollmentDebug');
            debugDiv.innerHTML = `
                <div class="success">
                    ✅ Page loaded successfully<br>
                    StudentId: ${studentId}<br>
                    YearLevel: ${yearLevel}<br><br>
                    <button onclick="testEligibility()">Click here to test API</button>
                </div>
            `;
        });

        // Log to page
        console.log('Script initialized');
    </script>
</body>
</html>