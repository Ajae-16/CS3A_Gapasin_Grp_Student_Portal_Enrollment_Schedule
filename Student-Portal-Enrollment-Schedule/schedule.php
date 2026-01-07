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
    <title>My Schedule - CvSU Cavite City Campus</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        /* Calendar Grid Styles */
        .calendar-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
            border: 1px solid #e1e8ed;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: 80px repeat(6, 1fr);
            border-collapse: collapse;
        }

        .calendar-header {
            background: linear-gradient(135deg, #006400 0%, #28a745 100%);
            color: white;
            padding: 12px 8px;
            text-align: center;
            font-weight: 700;
            font-size: 13px;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        .time-label {
            background: #f8f9fa;
            padding: 8px;
            text-align: center;
            font-size: 11px;
            font-weight: 600;
            color: #495057;
            border-right: 1px solid #e1e8ed;
            border-bottom: 1px solid #e1e8ed;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 60px;
        }

        .calendar-cell {
            border-right: 1px solid #e1e8ed;
            border-bottom: 1px solid #e1e8ed;
            min-height: 60px;
            position: relative;
            background: white;
        }

        .class-block {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 6px;
            font-size: 11px;
            line-height: 1.3;
            color: white;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .class-block:hover {
            opacity: 0.9;
            transform: scale(1.02);
            z-index: 10;
        }

        .class-time {
            font-weight: 700;
            margin-bottom: 2px;
        }

        .class-code {
            font-weight: 700;
            font-size: 12px;
        }

        .class-room {
            font-size: 10px;
            opacity: 0.95;
        }

        /* Color palette for different courses */
        .color-1 { background: linear-gradient(135deg, #5a67d8 0%, #4c51bf 100%); }
        .color-2 { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
        .color-3 { background: linear-gradient(135deg, #ed8936 0%, #dd6b20 100%); }
        .color-4 { background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%); }
        .color-5 { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }
        .color-6 { background: linear-gradient(135deg, #9f7aea 0%, #805ad5 100%); }
        .color-7 { background: linear-gradient(135deg, #38b2ac 0%, #319795 100%); }

        .loading {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 14px;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .calendar-grid {
                grid-template-columns: 60px repeat(6, minmax(100px, 1fr));
            }
            .class-block {
                font-size: 10px;
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
        <h2>My Schedule</h2>

        <!-- STUDENT INFORMATION TABLE -->
        <section class="student-info-table">
            <h3>Student Information</h3>
            <table>
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Student Name</th>
                        <th>School Year</th>
                        <th>Semester</th>
                        <th>Course</th>
                        <th>Year Level</th>
                        <th>Section</th>
                    </tr>
                </thead>
                <tbody id="studentInfoTable">

                </tbody>
            </table>
        </section>

        <!-- ENROLLED SUBJECTS TABLE -->
        <section class="grades-table">
            <h3>Enrolled Subjects</h3>
            <table>
                <thead>
                    <tr>
                        <th>Course Code</th>
                        <th>Course Name</th>
                        <th>Units</th>
                        <th>Instructor</th>
                    </tr>
                </thead>
                <tbody id="subjectsTableBody">
                    
                </tbody>
            </table>
        </section>

        <!-- WEEKLY SCHEDULE OVERVIEW -->
        <section class="card">
            <h3>Weekly Schedule Overview</h3>
            <div class="calendar-container" id="weeklyScheduleContainer">

            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <span>© 2025 Cavite State University Cavite City Campus | Version 2.0.0</span>
    </footer>

    <script>
        const studentId = '<?php echo $studentId; ?>';

        // Load all data
        document.addEventListener('DOMContentLoaded', function() {
            loadStudentInfo();
            loadEnrolledSubjects();
        });

        // Convert 24-hour to 12-hour format
        function convertTo12Hour(time24) {
            if (!time24) return '';
            const [hours, minutes] = time24.split(':');
            const hour = parseInt(hours);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${minutes} ${ampm}`;
        }

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
                            <td>${student.FirstName || ''} ${student.LastName || ''}</td>
                            <td>2024-2025</td>
                            <td>${student.Semester || '1st Semester'}</td>
                            <td>${student.Course || 'N/A'}</td>
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

        // Load Enrolled Subjects (unique courses only)
        async function loadEnrolledSubjects() {
            try {
                const response = await fetch(`api/student_schedule.php?StudentId=${encodeURIComponent(studentId)}`);
                const data = await response.json();
                
                console.log('API Response:', data);
        
                const tbody = document.getElementById('subjectsTableBody');
        
                if (data.success && data.data && data.data.length > 0) {
                    // Get unique courses only (no duplicates)
                    const uniqueCourses = [];
                    const seen = new Set();
                    
                    data.data.forEach(subject => {
                        if (!seen.has(subject.CourseId)) {
                            seen.add(subject.CourseId);
                            uniqueCourses.push(subject);
                        }
                    });
        
                    console.log('Unique Courses:', uniqueCourses); // DEBUG
        
                    // Render table
                    const html = uniqueCourses.map(subject => `
                        <tr>
                            <td><strong>${subject.CourseId || '-'}</strong></td>
                            <td>${subject.CourseName || '-'}</td>
                            <td style="text-align: center;"><strong>${subject.Unit || '-'}</strong></td>
                            <td>${subject.InstructorName || '-'}</td>
                        </tr>
                    `).join('');
        
                    console.log('Generated HTML:', html); // DEBUG
                    console.log('Target tbody:', tbody); // DEBUG
        
                    tbody.innerHTML = html;
        
                    console.log('HTML inserted, tbody content:', tbody.innerHTML); // DEBUG
        
                    // Load weekly schedule after subjects are loaded
                    loadWeeklySchedule(data.data);
                } else {
                    console.log('No data or empty array');
                    tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">No enrolled subjects found.</td></tr>';
                }
            } catch (error) {
                console.error('Error loading subjects:', error);
                document.getElementById('subjectsTableBody').innerHTML = '<tr><td colspan="4" style="text-align: center;">Error loading enrolled subjects.</td></tr>';
            }
        }

        // Load Weekly Schedule (Calendar Grid)
        function loadWeeklySchedule(schedules) {
            const container = document.getElementById('weeklyScheduleContainer');
        
            if (!schedules || schedules.length === 0) {
                container.innerHTML = '<div class="loading">No schedule available.</div>';
                return;
            }
        
            console.log('Loading schedules:', schedules); // DEBUG
        
            // Map abbreviated days to full names
            const dayMap = {
                'Mon': 'Monday',
                'Tue': 'Tuesday', 
                'Wed': 'Wednesday',
                'Thu': 'Thursday',
                'Fri': 'Friday',
                'Sat': 'Saturday',
                'Sun': 'Sunday'
            };
        
            // Define time slots (7 AM to 8 PM)
            const timeSlots = [
                '07:00', '08:00', '09:00', '10:00', '11:00', '12:00',
                '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00'
            ];
        
            const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
            const dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
            // Assign colors to courses
            const courseColors = {};
            let colorIndex = 1;
            schedules.forEach(s => {
                if (!courseColors[s.CourseId]) {
                    courseColors[s.CourseId] = `color-${colorIndex}`;
                    colorIndex = (colorIndex % 7) + 1;
                }
            });
        
            // Helper: Check if a class starts at this time slot
            function getClassesAtTimeSlot(day, timeSlot) {
                return schedules.filter(s => {
                    // Normalize the day of week
                    const scheduleDay = s.DayOfWeek;
                    
                    // Extract hour from schedule start time (e.g., "09:00:00" -> "09:00")
                    const scheduleStartTime = s.StartTime ? s.StartTime.substring(0, 5) : '';
                    
                    const matches = scheduleDay === day && scheduleStartTime === timeSlot;
                    
                    if (matches) {
                        console.log(`Match found: ${s.CourseId} on ${day} at ${timeSlot}`);
                    }
                    
                    return matches;
                });
            }
        
            // Build calendar grid
            let html = '<div class="calendar-grid">';
        
            // Header row
            html += '<div class="calendar-header">Time</div>';
            dayLabels.forEach(dayLabel => {
                html += `<div class="calendar-header">${dayLabel}</div>`;
            });
        
            // Time rows
            timeSlots.forEach(time => {
                // Time label
                html += `<div class="time-label">${convertTo12Hour(time)}</div>`;
        
                // Day cells
                days.forEach(day => {
                    const classesAtTime = getClassesAtTimeSlot(day, time);
        
                    html += '<div class="calendar-cell">';
        
                    classesAtTime.forEach(cls => {
                        // Extract hours from start and end time
                        const startTime = cls.StartTime ? cls.StartTime.substring(0, 5) : '00:00';
                        const endTime = cls.EndTime ? cls.EndTime.substring(0, 5) : '00:00';
                        
                        const startHour = parseInt(startTime.split(':')[0]);
                        const endHour = parseInt(endTime.split(':')[0]);
                        const duration = endHour - startHour;
        
                        html += `
                            <div class="class-block ${courseColors[cls.CourseId]}" style="height: ${duration * 100}%;">
                                <div class="class-time">${convertTo12Hour(startTime)} - ${convertTo12Hour(endTime)}</div>
                                <div class="class-code">${cls.CourseId}</div>
                                <div class="class-room">${cls.Room || ''}</div>
                            </div>
                        `;
                    });
        
                    html += '</div>';
                });
            });
        
            html += '</div>';
            container.innerHTML = html;
            
            console.log('Calendar rendered successfully');
        }
    </script>
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