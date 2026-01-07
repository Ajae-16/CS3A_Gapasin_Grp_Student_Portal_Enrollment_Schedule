<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Check user role - if admin, skip verification and go to dashboard
$userRole = $_SESSION['userRole'] ?? 'student';
if ($userRole === 'admin') {
    header('Location: dashboard.php');
    exit;
}

// Session timeout disabled - no auto-logout
// $_SESSION['last_activity'] = time();

// Get user information
$userName = $_SESSION['fullName'] ?? $_SESSION['username'];
$userRoleDisplay = ucfirst($userRole ?? 'User');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>Enrollment Schedule - CvSU Student Portal</title>
    <link rel="stylesheet" href="css/login.css">
</head>

<body class="login-page"
    style="background-image: url('images/background.png'); background-position: center center; background-repeat: no-repeat; background-size: cover;">
    <div class="dashboard-box">
        <!-- Welcome Header -->
        <div class="welcome-header">
            <img src="images/logo.png" alt="CvSU Logo" style="width: 70px;">
            <h2>Welcome, <span id="studentName">Student</span>!</h2>
            <p class="subtitle">Your Enrollment Verification</p>
        </div>

        <!-- Student Info Card -->
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">
                    <span class="icon">📚</span>
                    Year Level
                </span>
                <span id="studentYear" class="info-value">-</span>
            </div>
            <div class="info-row">
                <span class="info-label">
                    <span class="icon">📅</span>
                    Enrollment Period
                </span>
                <span id="enrollmentPeriod" class="info-value">-</span>
            </div>
            <div class="info-row">
                <span class="info-label">
                    <span class="icon">🕐</span>
                    Current Date
                </span>
                <span id="currentDate" class="info-value">-</span>
            </div>
        </div>

        <!-- Enrollment Message -->
        <div id="enrollMessage" class="message-box"></div>

        <!-- Progress Indicator -->
        <div id="progressIndicator" class="progress-indicator" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <p class="progress-text">Enrollment period in progress</p>
        </div>

        <!-- Proceed Button -->
        <button id="proceedBtn" class="proceed-button" style="display:none;">
            <span class="button-icon">→</span>
            Proceed to Dashboard
        </button>

        <!-- Action Links -->
        <div class="action-links">
            <a href="index.php" class="back-link">
                <span class="arrow">←</span>
                Back to Login
            </a>
            <span class="divider-link">|</span>
            <a href="#" class="help-link">
                <span class="icon-link">❓</span>
                Need Help?
            </a>
        </div>
    </div>

    <script src="javascript/script.js"></script>

    <script>
        // Display current date
        const currentDateEl = document.getElementById('currentDate');
        if (currentDateEl) {
            const today = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            currentDateEl.textContent = today.toLocaleDateString('en-US', options);
        }

        // Load student info and enrollment period from API
        document.addEventListener('DOMContentLoaded', function() {
            const studentNameEl = document.getElementById('studentName');
            const studentYearEl = document.getElementById('studentYear');
            const enrollmentPeriodEl = document.getElementById('enrollmentPeriod');
            const enrollMessageEl = document.getElementById('enrollMessage');
            const progressIndicatorEl = document.getElementById('progressIndicator');
            const proceedBtn = document.getElementById('proceedBtn');

            // Get student data from localStorage (set during login)
            const studentName = localStorage.getItem('StudentName');
            const yearLevel = localStorage.getItem('YearLevel');

            if (studentNameEl && studentName) {
                studentNameEl.textContent = studentName;
            }

            if (studentYearEl && yearLevel) {
                studentYearEl.textContent = yearLevel;
            }

            // Fetch enrollment period from database
            if (yearLevel) {
                fetch(`api/enrollment_period.php?YearLevel=${encodeURIComponent(yearLevel)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data) {
                            const period = data.data;
                            const startDate = new Date(period.StartDate);
                            const endDate = new Date(period.EndDate);
                            const today = new Date();

                            // Format dates for display
                            const options = { year: 'numeric', month: 'short', day: 'numeric' };
                            const periodText = `${startDate.toLocaleDateString('en-US', options)} - ${endDate.toLocaleDateString('en-US', options)}`;
                            enrollmentPeriodEl.textContent = periodText;

                            // Create date objects at midnight to avoid time comparison issues
                            const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                            const startDateMidnight = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                            const endDateMidnight = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());

                            // Check if current date is within enrollment period
                            if (todayDate >= startDateMidnight && todayDate <= endDateMidnight) {
                                // Within period
                                enrollMessageEl.innerHTML = `
                                    <div style="background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; padding: 16px; color: #155724;">
                                        <h3 style="margin: 0 0 8px 0;">✅ Enrollment Period Active</h3>
                                        <p style="margin: 0;">You can proceed with your enrollment now.</p>
                                    </div>
                                `;
                                progressIndicatorEl.style.display = 'block';
                                proceedBtn.style.display = 'block';
                            } else if (todayDate < startDateMidnight) {
                                // Before period
                                enrollMessageEl.innerHTML = `
                                    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 16px; color: #856404;">
                                        <h3 style="margin: 0 0 8px 0;">⏳ Enrollment Period Not Started</h3>
                                        <p style="margin: 0;">Your enrollment period will start on ${startDate.toLocaleDateString('en-US', options)}.</p>
                                    </div>
                                `;
                            } else {
                                // After period
                                enrollMessageEl.innerHTML = `
                                    <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 16px; color: #721c24;">
                                        <h3 style="margin: 0 0 8px 0;">❌ Enrollment Period Ended</h3>
                                        <p style="margin: 0;">Your enrollment period ended on ${endDate.toLocaleDateString('en-US', options)}. Please contact your advisor.</p>
                                    </div>
                                `;
                            }
                        } else {
                            enrollmentPeriodEl.textContent = 'Not Available';
                            enrollMessageEl.innerHTML = `
                                <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 16px; color: #721c24;">
                                    <h3 style="margin: 0 0 8px 0;">⚠️ Enrollment Period Not Set</h3>
                                    <p style="margin: 0;">Unable to load enrollment period. Please contact your advisor.</p>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading enrollment period:', error);
                        enrollmentPeriodEl.textContent = 'Error Loading';
                        enrollMessageEl.innerHTML = `
                            <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 16px; color: #721c24;">
                                <h3 style="margin: 0 0 8px 0;">❌ Error Loading Enrollment Period</h3>
                                <p style="margin: 0;">Please try refreshing the page or contact support.</p>
                            </div>
                        `;
                    });
            } else {
                enrollMessageEl.innerHTML = `
                    <div style="background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 16px; color: #721c24;">
                        <h3 style="margin: 0 0 8px 0;">⚠️ Student Information Not Found</h3>
                        <p style="margin: 0;">Please log in again.</p>
                    </div>
                `;
            }

            // Handle proceed button
            if (proceedBtn) {
                proceedBtn.addEventListener('click', function() {
                    window.location.href = 'dashboard.php';
                });
            }
        });
    </script>
</body>

</html>