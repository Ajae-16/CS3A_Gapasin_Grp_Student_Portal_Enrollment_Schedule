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
$yearLevel = $_SESSION['yearLevel'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Form - CvSU Cavite City Campus</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
        <!-- HEADER WITH LOGO -->
        <header class="header">
            <div>
                <h1>CAVITE STATE UNIVERSITY<br>CAVITE CITY CAMPUS</h1>
                <p>Pulo 2, Dalahican, Cavite City, Cavite</p>
            </div>
        </header>

        <!-- DOCUMENT TITLE -->
        <section class="card">
            <h2 style="text-align: center; color: var(--primary-green); margin: 0; font-size: 22px;">
                CERTIFICATE OF REGISTRATION
            </h2>
            <p style="text-align: center; color: var(--text-secondary); margin-top: 8px; font-size: 14px;">
                Official Enrollment Document for Academic Year 2024-2025
            </p>
        </section>

        <!-- STUDENT INFORMATION TABLE (matching grades.php style) -->
        <section class="student-info-table">
            <h3>Student Information</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Program</th>
                        <th>Year Level</th>
                    </tr>
                </thead>
                <tbody id="studentInfoTable">
                    <tr>
                        <td colspan="6" class="loading" style="text-align: center; padding: 20px;">Loading student information...</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- ENROLLED SUBJECTS TABLE (matching schedule.php style) -->
        <section class="grades-table">
            <h3>Enrolled Subjects</h3>
            <table>
                <thead>
                    <tr>
                        <th>Schedule Code</th>
                        <th>Course Code</th>
                        <th>Course Title</th>
                        <th>Units</th>
                        <th>Instructor</th>
                        <th>Day</th>
                        <th>Time</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody id="enrolledSubjectsTable">
                    <tr>
                        <td colspan="8" class="loading" style="text-align: center; padding: 20px;">Loading enrolled subjects...</td>
                    </tr>
                </tbody>
                <tfoot id="totalUnitsFooter" style="display: none;">
                    <tr style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                        <td colspan="3" style="text-align: right; font-weight: 700; padding: 12px;">Total Units:</td>
                        <td id="totalUnitsValue" style="font-weight: 700; color: var(--primary-green); padding: 12px;">0.00</td>
                        <td colspan="4"></td>
                    </tr>
                </tfoot>
            </table>
        </section>

        <!-- FEE CALCULATION SECTION -->
        <section class="card" id="feeCalculationSection" style="display: none;">
            <h3>Fee Calculation & Breakdown</h3>
            <div class="fee-breakdown-table" style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="background: #f0f0f0;">
                        <tr>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #006400;">Description</th>
                            <th style="padding: 12px; text-align: right; border-bottom: 2px solid #006400;">Amount</th>
                        </tr>
                    </thead>
                    <tbody id="feeDetailsTable">
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 20px; color: #999;">Loading fee details...</td>
                        </tr>
                    </tbody>
                    <tfoot style="background: linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%);">
                        <tr>
                            <td style="padding: 12px; font-weight: 700; border-top: 2px solid #006400;">Total Enrollment Fee</td>
                            <td style="padding: 12px; font-weight: 700; color: var(--primary-green); border-top: 2px solid #006400; text-align: right;">₱<span id="totalFeeAmount">0.00</span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div id="studentTypeIndicator" style="margin-top: 15px; padding: 12px; background: #d4edda; border-left: 4px solid #28a745; border-radius: 4px; display: none;">
                <strong style="color: #155724;">Student Type:</strong> <span id="studentTypeText" style="color: #155724;"></span>
            </div>
            <div id="irregularWarningSection" style="margin-top: 15px; padding: 12px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; display: none;">
                <strong style="color: #856404;">⚠️ Irregular Student Notice:</strong>
                <p style="margin: 8px 0 0 0; color: #856404; font-size: 14px;">An additional irregular student fee has been applied because you have failed one or more subjects in the previous semester.</p>
            </div>
        </section>

        <!-- ASSESSMENT SUMMARY -->
        <section class="card">
            <h3>Assessment Summary</h3>
            <div class="assessment-grid">
                <div class="assessment-item">
                    <span class="assessment-label">Total Units Enrolled</span>
                    <span class="assessment-value" id="summaryTotalUnits">0.00</span>
                </div>
                <div class="assessment-item">
                    <span class="assessment-label">Number of Subjects</span>
                    <span class="assessment-value" id="summaryTotalSubjects">0</span>
                </div>
                <div class="assessment-item">
                    <span class="assessment-label">Registration Status</span>
                    <span class="assessment-value status-confirmed">Confirmed</span>
                </div>
                <div class="assessment-item">
                    <span class="assessment-label">Enrollment Date</span>
                    <span class="assessment-value" id="enrollmentDate" style="font-size: 18px;">-</span>
                </div>
            </div>
        </section>

        <!-- ACTION BUTTONS -->
        <section class="card">
            <div class="action-buttons">
                <button class="btn-green" onclick="printForm()">
                    🖨️ Print Registration Form
                </button>
                <button class="btn-outline" onclick="downloadPDF()">
                    📥 Download as PDF
                </button>
            </div>
        </section>

        <!-- IMPORTANT NOTES -->
        <section class="card">
            <h3>Important Notes</h3>
            <ul class="notes-list">
                <li>This is an official Certificate of Registration. Keep this document for your records.</li>
                <li>Present this form when required by university offices or instructors.</li>
                <li>Any changes to your enrollment must be processed through the Registrar's Office.</li>
                <li>Complete payment within the designated payment period to avoid penalties.</li>
                <li>Verify all information carefully. Report any discrepancies immediately.</li>
            </ul>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <span>© 2025 Cavite State University Cavite City Campus | Version 2.0.0</span>
    </footer>

    <script>
        const studentId = '<?php echo $studentId; ?>';
        let enrollmentData = null;

        // Load all data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadStudentInfo();
            loadEnrolledSubjects();
            loadEnrollmentFees();
        });

        // Load Student Information
        async function loadStudentInfo() {
            try {
                const response = await fetch(`api/student_data.php?StudentId=${encodeURIComponent(studentId)}`);
                const data = await response.json();

                const tbody = document.getElementById('studentInfoTable');

                if (data.success && data.student) {
                    const student = data.student;
                    tbody.innerHTML = `
                        <tr>
                            <td>${student.StudentId || 'N/A'}</td>
                            <td>${student.FirstName || ''} ${student.MiddleName || ''} ${student.LastName || ''}</td>
                            <td>2024-2025</td>
                            <td>${student.Semester || '1st Semester'}</td>
                            <td>${student.ProgramName || 'N/A'}</td>
                            <td>${student.YearLevel || 'N/A'}</td>
                        </tr>
                    `;
                } else {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center;">Unable to load student information.</td></tr>';
                }
            } catch (error) {
                console.error('Error loading student info:', error);
                document.getElementById('studentInfoTable').innerHTML = '<tr><td colspan="6" style="text-align: center;">Error loading student information.</td></tr>';
            }
        }

        // Load Enrolled Subjects
        async function loadEnrolledSubjects() {
            try {
                const response = await fetch(`api/student_schedule.php?StudentId=${encodeURIComponent(studentId)}`);
                const data = await response.json();
                
                console.log('API Response:', data);
        
                const tbody = document.getElementById('enrolledSubjectsTable');
        
                if (data.success && data.data && data.data.length > 0) {
                    // Group schedules by ScheduleId to handle multiple time slots
                    const groupedSchedules = {};
                    
                    data.data.forEach(schedule => {
                        if (!groupedSchedules[schedule.ScheduleId]) {
                            groupedSchedules[schedule.ScheduleId] = {
                                ScheduleId: schedule.ScheduleId,
                                CourseId: schedule.CourseId,
                                CourseName: schedule.CourseName,
                                Unit: schedule.Unit,
                                InstructorName: schedule.InstructorName,
                                timeSlots: []
                            };
                        }
                        
                        groupedSchedules[schedule.ScheduleId].timeSlots.push({
                            Day: schedule.DayOfWeek,
                            StartTime: schedule.StartTime,
                            EndTime: schedule.EndTime,
                            Room: schedule.Room
                        });
                    });

                    // Convert to array
                    const uniqueSubjects = Object.values(groupedSchedules);
                    
                    // Calculate total units
                    let totalUnits = 0;
                    uniqueSubjects.forEach(subject => {
                        totalUnits += parseFloat(subject.Unit || 0);
                    });

                    // Render table rows
                    const html = uniqueSubjects.map(subject => {
                        // Combine all time slots for this subject
                        const days = subject.timeSlots.map(slot => formatDay(slot.Day)).join(' / ');
                        const times = subject.timeSlots.map(slot => 
                            `${convertTo12Hour(slot.StartTime)}-${convertTo12Hour(slot.EndTime)}`
                        ).join(' / ');
                        const rooms = subject.timeSlots.map(slot => slot.Room).join(' / ');

                        return `
                            <tr>
                                <td><strong>${subject.ScheduleId || '-'}</strong></td>
                                <td><strong>${subject.CourseId || '-'}</strong></td>
                                <td>${subject.CourseName || '-'}</td>
                                <td style="text-align: center;"><strong>${parseFloat(subject.Unit || 0).toFixed(2)}</strong></td>
                                <td>${subject.InstructorName || '-'}</td>
                                <td>${days}</td>
                                <td>${times}</td>
                                <td>${rooms}</td>
                            </tr>
                        `;
                    }).join('');
        
                    tbody.innerHTML = html;

                    // Update total units in footer and summary
                    document.getElementById('totalUnitsValue').textContent = totalUnits.toFixed(2);
                    document.getElementById('totalUnitsFooter').style.display = '';
                    document.getElementById('summaryTotalUnits').textContent = totalUnits.toFixed(2);
                    document.getElementById('summaryTotalSubjects').textContent = uniqueSubjects.length;

                    // Set enrollment date to today
                    const today = new Date().toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    document.getElementById('enrollmentDate').textContent = today;
                    
                } else {
                    console.log('No data or empty array');
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No enrolled subjects found.</td></tr>';
                }
            } catch (error) {
                console.error('Error loading subjects:', error);
                document.getElementById('enrolledSubjectsTable').innerHTML = '<tr><td colspan="8" style="text-align: center;">Error loading enrolled subjects.</td></tr>';
            }
        }

        // Load Enrollment Fees
        async function loadEnrollmentFees() {
            try {
                const response = await fetch(`api/enrollment_fees.php?StudentId=${encodeURIComponent(studentId)}`);
                const data = await response.json();
                
                if (data.success && data.data) {
                    enrollmentData = data.data;
                    renderFeeBreakdown(data.data);
                }
            } catch (error) {
                console.error('Error loading fees:', error);
                // Fees are optional, so don't show error
            }
        }

        // Render Fee Breakdown
        function renderFeeBreakdown(feeData) {
            const feeSection = document.getElementById('feeCalculationSection');
            const feeTableBody = document.getElementById('feeDetailsTable');

            if (!feeData || !feeData.FeeBreakdown) {
                return;
            }

            const fees = feeData.FeeBreakdown;
            const html = `
                <tr>
                    <td style="padding: 12px;">Fee Per Unit</td>
                    <td style="padding: 12px; text-align: right;">₱${parseFloat(fees.FeePerUnit || 0).toFixed(2)}</td>
                </tr>
                <tr style="background: #f9f9f9;">
                    <td style="padding: 12px;">${fees.UnitCount || 0} Units × ₱${parseFloat(fees.FeePerUnit || 0).toFixed(2)}/unit</td>
                    <td style="padding: 12px; text-align: right;">₱${parseFloat(fees.UnitsFee || 0).toFixed(2)}</td>
                </tr>
                <tr>
                    <td style="padding: 12px;">Miscellaneous Fee</td>
                    <td style="padding: 12px; text-align: right;">₱${parseFloat(fees.MiscFee || 0).toFixed(2)}</td>
                </tr>
                ${fees.IrregularFee > 0 ? `
                <tr style="background: #fff3cd;">
                    <td style="padding: 12px;"><strong>Irregular Student Fee</strong></td>
                    <td style="padding: 12px; text-align: right;"><strong>₱${parseFloat(fees.IrregularFee).toFixed(2)}</strong></td>
                </tr>
                ` : ''}
            `;

            feeTableBody.innerHTML = html;
            document.getElementById('totalFeeAmount').textContent = parseFloat(fees.TotalFee || 0).toFixed(2);
            
            // Show fee section
            feeSection.style.display = 'block';

            // Show student type indicator
            const typeIndicator = document.getElementById('studentTypeIndicator');
            const typeText = document.getElementById('studentTypeText');
            typeIndicator.style.display = 'block';
            typeText.textContent = feeData.StudentType || 'Regular';
            typeText.style.color = feeData.StudentType === 'Irregular' ? '#dc3545' : '#155724';

            // Show irregular warning if applicable
            if (feeData.StudentType === 'Irregular') {
                document.getElementById('irregularWarningSection').style.display = 'block';
            }
        }

        // Convert 24-hour to 12-hour format
        function convertTo12Hour(time24) {
            if (!time24) return '';
            const [hours, minutes] = time24.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${minutes} ${ampm}`;
        }

        // Format day abbreviation
        function formatDay(day) {
            const dayMap = {
                'Mon': 'M',
                'Tue': 'T',
                'Wed': 'W',
                'Thu': 'Th',
                'Fri': 'F',
                'Sat': 'S',
                'Sun': 'Su'
            };
            return dayMap[day] || day;
        }

        // Action button functions
        function printForm() {
            window.print();
        }

        // Enhanced PDF Download with html2pdf library
        function downloadPDF() {
            const element = document.querySelector('.main-content');
            const opt = {
                margin: 10,
                filename: `Registration_Form_${studentId}_${new Date().getTime()}.pdf`,
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
            };

            // Check if html2pdf is available
            if (typeof html2pdf !== 'undefined') {
                html2pdf().set(opt).from(element).save();
            } else {
                // Fallback: load html2pdf from CDN and then download
                const script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
                script.onload = function() {
                    html2pdf().set(opt).from(element).save();
                };
                document.head.appendChild(script);
            }
        }
    </script>

    <style>
        /* Assessment Grid */
        .assessment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .assessment-item {
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            border: 2px solid var(--border-color);
            transition: var(--transition);
        }

        .assessment-item:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-green);
        }

        .assessment-label {
            display: block;
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .assessment-value {
            display: block;
            font-size: 28px;
            color: var(--primary-green);
            font-weight: 700;
            margin-top: 8px;
        }

        .status-confirmed {
            color: #28a745 !important;
        }

        .status-pending {
            color: #ffc107 !important;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }

        .action-buttons button {
            flex: 1;
            min-width: 200px;
            max-width: 300px;
        }

        /* Notes List */
        .notes-list {
            list-style: none;
            padding: 0;
            margin-top: 16px;
        }

        .notes-list li {
            padding: 12px 16px;
            margin-bottom: 8px;
            background: #f8f9fa;
            border-left: 4px solid var(--primary-green);
            border-radius: 4px;
            font-size: 14px;
            color: var(--text-primary);
            line-height: 1.6;
        }

        .notes-list li::before {
            content: "ℹ️";
            margin-right: 10px;
        }

        .loading {
            color: #6c757d;
            font-style: italic;
        }

        /* Print Styles */
        @media print {
            .nav,
            .sidebar,
            .toggle-btn,
            .footer,
            .action-buttons {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                margin-top: 0 !important;
            }

            .card,
            .student-info-table,
            .grades-table {
                box-shadow: none !important;
                page-break-inside: avoid;
            }
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .action-buttons button {
                max-width: 100%;
            }
        }
    </style>
</body>

</html>