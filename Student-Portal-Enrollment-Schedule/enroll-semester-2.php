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
    <title>2nd Semester Enrollment - CvSU Cavite City Campus</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .enrollment-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }

        .enrollment-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .enrollment-card h2 {
            color: #006400;
            margin-bottom: 20px;
            border-bottom: 2px solid #006400;
            padding-bottom: 12px;
        }

        .eligibility-status {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .status-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #006400;
        }

        .status-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .status-value {
            font-size: 20px;
            font-weight: 700;
            color: #006400;
        }

        .status-item.warning {
            border-left-color: #ffc107;
            background: #fffbf0;
        }

        .status-item.warning .status-value {
            color: #ff9800;
        }

        .subjects-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .subjects-table thead {
            background: #f0f0f0;
        }

        .subjects-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #006400;
        }

        .subjects-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
        }

        .subjects-table tr:hover {
            background: #f9f9f9;
        }

        .fee-breakdown {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .fee-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid rgba(0, 100, 0, 0.1);
            font-size: 15px;
        }

        .fee-row:last-child {
            border-bottom: none;
        }

        .fee-label {
            color: #333;
            font-weight: 500;
        }

        .fee-amount {
            color: #006400;
            font-weight: 600;
        }

        .fee-row.total {
            border-top: 2px solid #006400;
            font-size: 18px;
            font-weight: 700;
            padding-top: 15px;
        }

        .fee-row.total .fee-amount {
            color: #006400;
            font-size: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 25px;
            flex-wrap: wrap;
        }

        .btn-enroll {
            background: linear-gradient(135deg, #006400 0%, #004d00 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
            min-width: 200px;
        }

        .btn-enroll:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 100, 0, 0.3);
        }

        .btn-enroll:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .btn-back {
            background: #f0f0f0;
            color: #333;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            flex: 1;
            min-width: 200px;
        }

        .btn-back:hover {
            background: #e0e0e0;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
            font-size: 18px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .irregular-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }

        .irregular-warning strong {
            color: #856404;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-content h2 {
            color: #006400;
            margin-bottom: 15px;
        }

        .modal-content p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #006400;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .enrollment-container {
                padding: 15px;
            }

            .subjects-table {
                font-size: 14px;
            }

            .subjects-table th,
            .subjects-table td {
                padding: 8px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-enroll,
            .btn-back {
                min-width: auto;
            }
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
        <div class="enrollment-container">
            
            <!-- HEADER -->
            <div class="enrollment-card">
                <h2>2nd Semester Enrollment</h2>
                <p style="color: #666; font-size: 16px; margin: 0;">
                    Complete your 2nd semester enrollment based on your 1st semester performance
                </p>
            </div>

            <!-- LOADING STATE -->
            <div id="loadingState" class="enrollment-card" style="display: none;">
                <div class="loading">
                    <div class="spinner"></div>
                    <p>Loading your enrollment information...</p>
                </div>
            </div>

            <!-- ERROR STATE -->
            <div id="errorState" style="display: none;">
                <div class="enrollment-card">
                    <div class="error" id="errorMessage"></div>
                </div>
            </div>

            <!-- ENROLLMENT FORM -->
            <div id="enrollmentForm" style="display: none;">
                
                <!-- ELIGIBILITY STATUS -->
                <div class="enrollment-card">
                    <h2>Academic Status Summary</h2>
                    <div class="eligibility-status">
                        <div class="status-item">
                            <div class="status-label">Student Type</div>
                            <div class="status-value" id="studentTypeDisplay">-</div>
                        </div>
                        <div class="status-item">
                            <div class="status-label">Subjects Passed</div>
                            <div class="status-value" id="passedSubjectsDisplay">0/0</div>
                        </div>
                        <div class="status-item" id="failedDisplay" style="display: none;">
                            <div class="status-label">Subjects Failed</div>
                            <div class="status-value" id="failedSubjectsDisplay">0</div>
                        </div>
                        <div class="status-item">
                            <div class="status-label">Total Units</div>
                            <div class="status-value" id="totalUnitsDisplay">0</div>
                        </div>
                    </div>

                    <!-- WARNING FOR IRREGULAR STUDENTS -->
                    <div id="irregularWarning" class="irregular-warning" style="display: none;">
                        <strong>⚠️ Irregular Student Status</strong>
                        <p>You have failed one or more subjects in the 1st semester. As an irregular student, you will be charged an additional irregular fee. However, you can still enroll for the 2nd semester.</p>
                    </div>
                </div>

                <!-- ENROLLED SUBJECTS -->
                <div class="enrollment-card">
                    <h2>2nd Semester Subjects</h2>
                    <div style="overflow-x: auto;">
                        <table class="subjects-table">
                            <thead>
                                <tr>
                                    <th>Course Code</th>
                                    <th>Course Title</th>
                                    <th>Units</th>
                                    <th>Instructor</th>
                                    <th>Schedule</th>
                                    <th>Room</th>
                                </tr>
                            </thead>
                            <tbody id="subjectsTableBody">
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 30px; color: #999;">Loading subjects...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- FEE BREAKDOWN -->
                <div class="enrollment-card">
                    <h2>Fee Calculation</h2>
                    <div class="fee-breakdown">
                        <div class="fee-row">
                            <span class="fee-label">Fee Per Unit</span>
                            <span class="fee-amount">₱<span id="feePerUnit">0.00</span></span>
                        </div>
                        <div class="fee-row">
                            <span class="fee-label">Total Units × Fee Per Unit</span>
                            <span class="fee-amount">₱<span id="unitsFee">0.00</span></span>
                        </div>
                        <div class="fee-row">
                            <span class="fee-label">Miscellaneous Fee</span>
                            <span class="fee-amount">₱<span id="miscFee">0.00</span></span>
                        </div>
                        <div class="fee-row" id="irregularFeeRow" style="display: none;">
                            <span class="fee-label">Irregular Student Fee</span>
                            <span class="fee-amount">₱<span id="irregularFee">0.00</span></span>
                        </div>
                        <div class="fee-row total">
                            <span class="fee-label">Total Enrollment Fee</span>
                            <span class="fee-amount">₱<span id="totalFeeDisplay">0.00</span></span>
                        </div>
                    </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div class="enrollment-card">
                    <div class="action-buttons">
                        <button type="button" class="btn-enroll" id="enrollBtn" onclick="processEnrollment()">
                            ✓ Confirm Enrollment
                        </button>
                        <button type="button" class="btn-back" onclick="window.history.back()">
                            ← Go Back
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <span>© 2025 Cavite State University Cavite City Campus | Version 2.0.0</span>
    </footer>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="modal-overlay">
        <div class="modal-content">
            <h2>✓ Enrollment Successful!</h2>
            <p>You have successfully enrolled for the 2nd semester.</p>
            <p id="enrollmentSummary"></p>
            <div class="action-buttons" style="margin-top: 20px;">
                <button class="btn-enroll" onclick="goToDashboard()" style="width: 100%;">
                    Continue to Dashboard
                </button>
            </div>
        </div>
    </div>

    <script>
        const studentId = '<?php echo htmlspecialchars($studentId); ?>';
        let enrollmentData = null;

        // Load enrollment data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadEnrollmentData();
        });
        
        

        /**
         * Load enrollment eligibility and fee data
         */
/**
 * Load enrollment data on page load
 */
        async function loadEnrollmentData() {
            try {
                showLoading(true);
                console.log('StudentId:', studentId);  // Debug log
        
                // Check eligibility
                const eligibilityUrl = `api/enroll_second_semester.php?StudentId=${encodeURIComponent(studentId)}&action=check_eligibility`;
                console.log('Eligibility URL:', eligibilityUrl);  // Debug log
                
                const eligibilityRes = await fetch(eligibilityUrl);
                const eligibilityData = await eligibilityRes.json();
                
                console.log('Eligibility Response:', eligibilityData);  // Debug log
        
                if (!eligibilityData.success) {
                    showError('You are not eligible for 2nd semester enrollment. ' + (eligibilityData.message || 'No 1st semester enrollment found'));
                    return;
                }
        
                // Get enrollment data with fees
                const enrollmentUrl = `api/enroll_second_semester.php?StudentId=${encodeURIComponent(studentId)}`;
                console.log('Enrollment URL:', enrollmentUrl);  // Debug log
                
                const enrollmentRes = await fetch(enrollmentUrl);
                const enrollment = await enrollmentRes.json();
                
                console.log('Enrollment Response:', enrollment);  // Debug log
        
                if (!enrollment.success) {
                    showError(enrollment.message || 'Failed to load enrollment data');
                    return;
                }
        
                enrollmentData = enrollment.data;
                console.log('Final enrollmentData:', enrollmentData);  // Debug log
                
                renderEnrollmentForm(eligibilityData.data, enrollmentData);
                showLoading(false);
        
            } catch (error) {
                console.error('Error loading enrollment data:', error);
                showError('An error occurred while loading enrollment data. Please refresh the page.');
                showLoading(false);
            }
        }

        /**
         * Render enrollment form with data
         */
        function renderEnrollmentForm(eligibilityData, enrollmentData) {
            // Show form
            document.getElementById('enrollmentForm').style.display = 'block';

            // Update status
            const studentType = eligibilityData.StudentType;
            document.getElementById('studentTypeDisplay').textContent = studentType;
            document.getElementById('passedSubjectsDisplay').textContent = 
                `${eligibilityData.PassedSubjects}/${eligibilityData.TotalSubjects}`;
            
            if (eligibilityData.FailedSubjects > 0) {
                document.getElementById('failedDisplay').style.display = 'block';
                document.getElementById('failedSubjectsDisplay').textContent = eligibilityData.FailedSubjects;
                document.getElementById('irregularWarning').style.display = 'block';
                document.getElementById('irregularFeeRow').style.display = 'flex';
            }

            document.getElementById('totalUnitsDisplay').textContent = enrollmentData.TotalUnits;

            // Render subjects
            const tbody = document.getElementById('subjectsTableBody');
            if (enrollmentData.Subjects && enrollmentData.Subjects.length > 0) {
                tbody.innerHTML = enrollmentData.Subjects.map(subject => {
                    const schedule = `${subject.DayOfWeek} ${formatTime(subject.StartTime)}-${formatTime(subject.EndTime)}`;
                    return `
                        <tr>
                            <td><strong>${subject.CourseId}</strong></td>
                            <td>${subject.CourseName}</td>
                            <td style="text-align: center;">${subject.Unit}</td>
                            <td>${subject.InstructorName}</td>
                            <td>${schedule}</td>
                            <td>${subject.Room}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; color: #999;">No subjects available</td></tr>';
            }

            // Update fees
            const fees = enrollmentData.FeeBreakdown;
            document.getElementById('feePerUnit').textContent = fees.FeePerUnit.toFixed(2);
            document.getElementById('unitsFee').textContent = fees.UnitsFee.toFixed(2);
            document.getElementById('miscFee').textContent = fees.MiscFee.toFixed(2);
            if (fees.IrregularFee > 0) {
                document.getElementById('irregularFee').textContent = fees.IrregularFee.toFixed(2);
            }
            document.getElementById('totalFeeDisplay').textContent = fees.TotalFee.toFixed(2);
        }

        /**
         * Format time to 12-hour format
         */
        function formatTime(time) {
            if (!time) return '';
            const [hours, minutes] = time.split(':');
            const h = parseInt(hours);
            const ampm = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return `${h12}:${minutes} ${ampm}`;
        }

       /**
 * REPLACE THIS FUNCTION in your enroll-semester-2.php
 * Find the processEnrollment() function and replace it with this:
 */

/**
 * REPLACE the processEnrollment() function in enroll-semester-2.php with this:
 */

        async function processEnrollment() {
            // CHECK IF DATA WAS LOADED
            if (!enrollmentData) {
                showError('Enrollment data failed to load. Please refresh the page.');
                return;
            }
        
            const enrollBtn = document.getElementById('enrollBtn');
            enrollBtn.disabled = true;
            enrollBtn.textContent = '⏳ Processing...';
        
            try {
                console.log('Processing enrollment...');
                console.log('StudentId:', studentId);
                console.log('YearLevel:', enrollmentData.YearLevel);
        
                const postData = {
                    StudentId: studentId,
                    ProgramId: 'BSCS',
                    YearLevel: enrollmentData.YearLevel
                };
        
                console.log('POST data:', postData);
        
                const response = await fetch('api/enroll_second_semester.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(postData)
                });
        
                console.log('Response status:', response.status);
        
                const result = await response.json();
        
                console.log('Response data:', result);
        
                if (result.success) {
                    // Show success modal
                    const summary = `
                        <strong>✓ Enrollment Successful!</strong><br><br>
                        <strong>Enrollment ID:</strong> ${result.data.EnrollmentId}<br>
                        <strong>Student Type:</strong> ${result.data.StudentType}<br>
                        <strong>Total Units:</strong> ${result.data.TotalUnits}<br>
                        <strong>Total Fee:</strong> ₱${parseFloat(result.data.TotalFee).toFixed(2)}<br>
                        <strong>Schedules Assigned:</strong> ${result.data.SchedulesAssigned}<br>
                        <strong>Status:</strong> ${result.data.EnrollmentStatus}
                    `;
                    document.getElementById('enrollmentSummary').innerHTML = summary;
                    document.getElementById('successModal').classList.add('active');
                } else {
                    showError(result.message || 'Enrollment failed. Please try again.');
                    enrollBtn.disabled = false;
                    enrollBtn.textContent = '✓ Confirm Enrollment';
                }
            } catch (error) {
                console.error('Enrollment error:', error);
                showError('An error occurred during enrollment. Please try again.');
                enrollBtn.disabled = false;
                enrollBtn.textContent = '✓ Confirm Enrollment';
            }
        }

        /**
         * Navigate to dashboard
         */
        function goToDashboard() {
            window.location.href = 'dashboard.php';
        }

        /**
         * Show loading state
         */
        function showLoading(show) {
            document.getElementById('loadingState').style.display = show ? 'block' : 'none';
            document.getElementById('enrollmentForm').style.display = show ? 'none' : 'block';
            document.getElementById('errorState').style.display = 'none';
        }

        /**
         * Show error message
         */
        function showError(message) {
            document.getElementById('errorMessage').textContent = message;
            document.getElementById('errorState').style.display = 'block';
            document.getElementById('enrollmentForm').style.display = 'none';
            document.getElementById('loadingState').style.display = 'none';
        }
    </script>

</body>

</html>
