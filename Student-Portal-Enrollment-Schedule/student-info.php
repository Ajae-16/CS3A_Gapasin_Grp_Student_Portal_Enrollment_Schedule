<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$userRole = strtolower($_SESSION['userRole'] ?? 'student');
$isAdmin  = ($userRole === 'admin');


// Include database configuration
define('DB_ACCESS', true);
require_once 'database/db_config.php';

// Get user information
$userName = $_SESSION['fullName'] ?? $_SESSION['username'];
$userRole = ucfirst($_SESSION['userRole'] ?? 'User');

// Get student ID from session
$studentId = $_SESSION['username'];

// Fetch student data from database
$studentData = null;
$enrollmentData = null;
$programData = null;

try {
    // Get student personal data
    $sql = "SELECT * FROM student_data WHERE StudentId = ?";
    $result = executeQuery($sql, [$studentId], 's');
    if (!empty($result)) {
        $studentData = $result[0];
    }

    // Get enrollment data (latest enrollment)
    $sql = "SELECT * FROM enrollment_data WHERE StudentId = ? ORDER BY CreatedAt DESC LIMIT 1";
    $result = executeQuery($sql, [$studentId], 's');
    if (!empty($result)) {
        $enrollmentData = $result[0];
    }

    // Get program data if enrollment exists
    if ($enrollmentData && isset($enrollmentData['ProgramId'])) {
        $sql = "SELECT * FROM program_data WHERE ProgramId = ?";
        $result = executeQuery($sql, [$enrollmentData['ProgramId']], 's');
        if (!empty($result)) {
            $programData = $result[0];
        }
    }
} catch (Exception $e) {
    // Log error and continue with null data
    error_log("Error fetching student data: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information - CvSU Cavite City Campus</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        /* Profile Card Styling */
        .profile-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 30px;
        }

        .profile-pic-container {
            flex-shrink: 0;
        }

        .profile-pic-lg {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #006400 0%, #28a745 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
            font-weight: 700;
            border: 5px solid #e8f5e9;
            box-shadow: 0 4px 12px rgba(0, 100, 0, 0.2);
        }

        .profile-details {
            flex: 1;
        }

        .student-id {
            font-size: 18px;
            color: #666;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .student-name {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
        }

        .quick-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .quick-info-item {
            background: #f8f9fa;
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-green);
        }

        .quick-info-label {
            display: block;
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }

        .quick-info-value {
            display: block;
            font-size: 16px;
            color: #333;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <!-- NAVIGATION BAR -->
    <div class="nav">
        <nav>
            <img src="images/logo.png" alt="CvSU Logo" class="logo">
            <span>CvSU CCC Student Portal</span>
        </nav>
    </div>

    <!-- SIDEBAR MENU -->
    <?php include 'sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">
<h2><?php echo $isAdmin ? 'Admin Information' : 'Student Information'; ?></h2>

        <!-- PROFILE CARD -->
        <div class="profile-card">
            <div class="profile-pic-container">
                <div class="profile-pic-lg">
                    <?php 
                    // Display first letter of first name
                    $firstName = $studentData['FirstName'] ?? 'S';
                    echo strtoupper(substr($firstName, 0, 1)); 
                    ?>
                </div>
            </div>
            <div class="profile-details">
                <div class="student-id"><?php echo htmlspecialchars($studentData['StudentId'] ?? $studentId); ?></div>
                <div class="student-name">
                    <?php echo htmlspecialchars(($studentData['FirstName'] ?? '') . ' ' . ($studentData['MiddleName'] ? $studentData['MiddleName'] . ' ' : '') . ($studentData['LastName'] ?? '')); ?>
                </div>
                <div class="quick-info">
                    <div class="quick-info-item">
                        <span class="quick-info-label">Program</span>
                        <span class="quick-info-value"><?php echo htmlspecialchars($programData['ProgramName'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="quick-info-item">
                        <span class="quick-info-label">Major</span>
                        <span class="quick-info-value"><?php echo htmlspecialchars($studentData['Major'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="quick-info-item">
                        <span class="quick-info-label">Year Level</span>
                        <span class="quick-info-value"><?php echo htmlspecialchars($enrollmentData['YearLevel'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PERSONAL INFORMATION TABLE (matching grades/schedule style) -->
        <section class="student-info-table">
            <h3>Personal Information</h3>
            <table>
                <thead>
                    <tr>
                        <th>Street Name</th>
                        <th>Barangay</th>
                        <th>Municipality</th>
                        <th>Civil Status</th>
                        <th>Citizenship</th>
                        <th>Religion</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($studentData['StreetName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['Barangay'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['Municipality'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['CivilStatus'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['Citizenship'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['Religion'] ?? 'N/A'); ?></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- CONTACT INFORMATION TABLE (matching grades/schedule style) -->
        <section class="grades-table">
            <h3>Contact Information</h3>
            <table>
                <thead>
                    <tr>
                        <th>Guardian Name</th>
                        <th>Guardian Contact Number</th>
                        <th>Email Address</th>
                        <th>Student Portal ID</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($studentData['GuardianName'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['GuardianContact'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['Email'] ?? 'N/A'); ?></td>
                        <td><strong><?php echo htmlspecialchars($studentId); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- ADDITIONAL DETAILS TABLE -->
        <section class="student-info-table">
            <h3>Additional Details</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Contact Number</th>
                        <th>Enrollment Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($studentData['DateOfBirth'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['Gender'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($studentData['ContactNumber'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            $status = $enrollmentData['EnrollmentStatus'] ?? 'Unknown';
                            $statusClass = '';
                            if ($status === 'Enrolled') {
                                $statusClass = 'badge-passed';
                            } elseif ($status === 'Pending') {
                                $statusClass = 'badge-pending';
                            } else {
                                $statusClass = 'badge-failed';
                            }
                            ?>
                            <span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- ACADEMIC INFORMATION TABLE -->
        <section class="grades-table">
            <h3>Academic Information</h3>
            <table>
                <thead>
                    <tr>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Section</th>
                        <th>Enrollment Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($enrollmentData['SchoolYear'] ?? '2024-2025'); ?></strong></td>
                        <td><?php echo htmlspecialchars($enrollmentData['Semester'] ?? '1st Semester'); ?></td>
                        <td><?php echo htmlspecialchars($enrollmentData['Section'] ?? 'N/A'); ?></td>
                        <td>
                            <?php 
                            $enrollDate = $enrollmentData['CreatedAt'] ?? null;
                            if ($enrollDate) {
                                echo date('F j, Y', strtotime($enrollDate));
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <span>© 2025 Cavite State University Cavite City Campus | Version 2.0.0</span>
    </footer>

    <style>
        /* Additional badges for status */
        .badge-passed {
            background: #d4edda;
            color: #155724;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 12px;
        }

        .badge-pending {
            background: #fff3cd;
            color: #856404;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 12px;
        }

        .badge-failed {
            background: #f8d7da;
            color: #721c24;
            padding: 4px 12px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 12px;
        }
    </style>
    <button class="mobile-menu-btn" onclick="toggleMobileSidebar()" style="display: none;">☰</button>
    <script>
    function toggleMobileSidebar() {
        document.getElementById('sidebar')?.classList.toggle('mobile-open');
    }
    if (window.innerWidth <= 768) document.querySelector('.mobile-menu-btn').style.display = 'block';
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const btn = document.querySelector('.mobile-menu-btn');
        if (window.innerWidth <= 768 && sidebar && btn && !sidebar.contains(e.target) && !btn.contains(e.target)) {
            sidebar.classList.remove('mobile-open');
        }
    });
    </script>
</body>

</html>
