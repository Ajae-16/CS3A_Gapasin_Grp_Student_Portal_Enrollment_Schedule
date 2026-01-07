<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Session timeout disabled - no auto-logout
// $_SESSION['last_activity'] = time();

// Get user information
$userName = $_SESSION['fullName'] ?? $_SESSION['username'];
$userRole = ucfirst($_SESSION['userRole'] ?? 'User');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grades - CvSU Cavite City Campus</title>
    <link rel="stylesheet" href="css/dashboard.css">
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
        <h2>Student Grades</h2>

        <!-- GRADES MANAGEMENT SECTION -->
        <section class="grades-management">
            <h3>Grades Management</h3>
            <div class="filters">
                <label>
                    School Year:
                    <select id="schoolYear">
                        <option>2021-2022</option>
                        <option>2022-2023</option>
                        <option selected>2024-2025</option>
                    </select>
                </label>
                <label>
                    Semester:
                    <select id="semester">
                        <option>Summer Semester</option>
                        <option selected>1st Semester</option>
                        <option>2nd Semester</option>
                    </select>
                </label>
                <button class="btn-green" onclick="displayGrades()">Display Grades</button>
                <button class="btn-outline" onclick="viewChecklist()">View Student Checklist</button>
            </div>
        </section>

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
                    <!-- Data will be loaded dynamically -->
                </tbody>
            </table>
        </section>

        <!-- GRADES TABLE -->
        <section class="grades-table">
            <h3>Student Grades</h3>
            <table>
                <thead>
                    <tr>
                        <th>Schedule Code</th>
                        <th>Subject Code</th>
                        <th>Subject Name</th>
                        <th>Units</th>
                        <th>Grade</th>
                        <th>Makeup Grade</th>
                        <th>Final Units</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody id="gradesTableBody">
                    <!-- Rows will be rendered by JS. Falls back to sample data if API is unavailable. -->
                </tbody>
            </table>
        </section>

        <!-- GPA SUMMARY CARD -->
        <section class="card">
            <h3>Academic Performance Summary</h3>
            <div class="gpa-summary">
                <div class="gpa-item">
                    <div class="gpa-label">Semester GPA</div>
                    <div class="gpa-value" id="semesterGPA">0.00</div>
                    <div class="gpa-desc" id="gpaStanding">Loading...</div>
                </div>
                <div class="gpa-item">
                    <div class="gpa-label">Total Units Earned</div>
                    <div class="gpa-value" id="totalUnits">0</div>
                    <div class="gpa-desc">Credits</div>
                </div>
                <div class="gpa-item">
                    <div class="gpa-label">Subjects Passed</div>
                    <div class="gpa-value" id="subjectsPassed">0/0</div>
                    <div class="gpa-desc" id="passRate">0% Pass Rate</div>
                </div>
                <div class="gpa-item">
                    <div class="gpa-label">Academic Status</div>
                    <div class="gpa-value">✓</div>
                    <div class="gpa-desc">Regular Student</div>
                </div>
            </div>
        </section>

        <!-- GRADING SCALE REFERENCE -->
        <section class="card">
            <h3>Grading Scale Reference</h3>
            <div class="grading-scale">
                <div class="scale-item">
                    <span class="scale-grade grade-excellent">1.00 - 1.75</span>
                    <span class="scale-desc">Excellent</span>
                </div>
                <div class="scale-item">
                    <span class="scale-grade grade-very-good">2.00 - 2.25</span>
                    <span class="scale-desc">Very Good</span>
                </div>
                <div class="scale-item">
                    <span class="scale-grade grade-good">2.50 - 2.75</span>
                    <span class="scale-desc">Good</span>
                </div>
                <div class="scale-item">
                    <span class="scale-grade grade-fair">3.00</span>
                    <span class="scale-desc">Fair/Passing</span>
                </div>
                <div class="scale-item">
                    <span class="scale-grade grade-failed">5.00</span>
                    <span class="scale-desc">Failed</span>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer class="footer">
        <span>© 2025 Cavite State University Cavite City Campus | Version 2.0.0</span>
    </footer>

    <script>
        // Display grades function
        function displayGrades() {
            const schoolYear = document.getElementById('schoolYear').value;
            const semester = document.getElementById('semester').value;
            alert(`Displaying grades for ${schoolYear} - ${semester}`);
        }

        // View checklist function
        function viewChecklist() {
            alert('Opening student checklist...');
        }
    </script>


    <script>
        // Render grades using PascalCase fields returned by API
        (function() {
            const studentId = '<?php echo $_SESSION['username'] ?? ''; ?>';
            const tbody = document.getElementById('gradesTableBody');
            const studentInfoTbody = document.getElementById('studentInfoTable');
    
            // Load student info
            function loadStudentInfo() {
                if (studentInfoTbody) {
                    studentInfoTbody.innerHTML = `
                        <tr>
                            <td><?php echo $_SESSION['username'] ?? 'N/A'; ?></td>
                            <td><?php echo $_SESSION['fullName'] ?? 'N/A'; ?></td>
                            <td>2024-2025</td>
                            <td>1st Semester</td>
                            <td></td>
                            <td><?php echo $_SESSION['yearLevel'] ?? 'N/A'; ?></td>
                            <td></td>
                        </tr>
                    `;
                }
            }
    
            function renderRows(items) {
                tbody.innerHTML = '';
                if (!items || items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center; padding: 40px; color: #6c757d;">No grades available for this semester.</td></tr>';
                    updateGPASummary([], 0);
                    return;
                }
    
                items.forEach(item => {
                    const tr = document.createElement('tr');
                    const gradeValue = parseFloat(item.GradeValue) || 0;
                    const gradeClass = getGradeClass(gradeValue);
    
                    tr.innerHTML = `
                        <td>${item.GradeId || '-'}</td>
                        <td><strong>${item.CourseId || '-'}</strong></td>
                        <td>${item.CourseName || '-'}</td>
                        <td style="text-align: center;">${item.Unit || '-'}</td>
                        <td class="${gradeClass}" style="text-align: center; font-weight: 700;">${item.GradeValue ? parseFloat(item.GradeValue).toFixed(2) : '-'}</td>
                        <td style="text-align: center;">${item.MakeupGrade || '-'}</td>
                        <td style="text-align: center;">${item.FinalUnits || item.Unit || '-'}</td>
                        <td>${getRemarksBadge(gradeValue, item.Remarks)}</td>
                    `;
    
                    tbody.appendChild(tr);
                });
    
                // Calculate GPA
                const totalUnits = items.reduce((sum, item) => sum + (parseInt(item.Unit) || 0), 0);
                updateGPASummary(items, totalUnits);
            }
    
            function getGradeClass(gradeValue) {
                if (gradeValue === 0) return '';
                if (gradeValue <= 1.75) return 'grade-excellent';
                if (gradeValue <= 2.25) return 'grade-very-good';
                if (gradeValue <= 2.75) return 'grade-good';
                if (gradeValue <= 3.00) return 'grade-fair';
                return 'grade-failed';
            }
    
            function getRemarksBadge(gradeValue, remarks) {
                if (remarks) return remarks;
                if (gradeValue === 0) return '-';
                if (gradeValue <= 3.00) {
                    return '<span class="badge-passed">✓ Passed</span>';
                }
                return '<span class="badge-failed">✗ Failed</span>';
            }
    
            function updateGPASummary(items, totalUnits) {
                // Calculate semester GPA
                let totalGradePoints = 0;
                let totalWeightedUnits = 0;
                let passed = 0;
                let total = items.length;
    
                items.forEach(item => {
                    const grade = parseFloat(item.GradeValue) || 0;
                    const units = parseInt(item.Unit) || 0;
                    if (grade > 0 && grade <= 3.00) {
                        passed++;
                    }
                    if (grade > 0) {
                        totalGradePoints += grade * units;
                        totalWeightedUnits += units;
                    }
                });
    
                const gpa = totalWeightedUnits > 0 ? (totalGradePoints / totalWeightedUnits).toFixed(2) : '0.00';
                const passRate = total > 0 ? Math.round((passed / total) * 100) : 0;
    
                // Update UI
                document.getElementById('semesterGPA').textContent = gpa;
                document.getElementById('totalUnits').textContent = totalUnits;
                document.getElementById('subjectsPassed').textContent = `${passed}/${total}`;
                document.getElementById('passRate').textContent = `${passRate}% Pass Rate`;
    
                // Update GPA standing
                const standing = document.getElementById('gpaStanding');
                const gpaValue = parseFloat(gpa);
                if (gpaValue <= 1.75) {
                    standing.textContent = 'Excellent';
                    standing.style.color = '#28a745';
                } else if (gpaValue <= 2.25) {
                    standing.textContent = 'Very Good';
                    standing.style.color = '#17a2b8';
                } else if (gpaValue <= 2.75) {
                    standing.textContent = 'Good';
                    standing.style.color = '#ffc107';
                } else if (gpaValue <= 3.00) {
                    standing.textContent = 'Fair';
                    standing.style.color = '#fd7e14';
                } else {
                    standing.textContent = 'Needs Improvement';
                    standing.style.color = '#dc3545';
                }
            }
    
            // Fallback sample data
            const sampleData = [
                { GradeId: 'G-001', CourseId: 'COSC101', CourseName: 'Web System and Technologies', Unit: 3, GradeValue: '1.75', MakeupGrade: '-', FinalUnits: 3, Remarks: '' },
                { GradeId: 'G-002', CourseId: 'CS202', CourseName: 'Database Management System', Unit: 3, GradeValue: '1.00', MakeupGrade: '-', FinalUnits: 3, Remarks: '' },
                { GradeId: 'G-003', CourseId: 'COSC75', CourseName: 'Software Engineering', Unit: 3, GradeValue: '1.50', MakeupGrade: '-', FinalUnits: 3, Remarks: '' },
                { GradeId: 'G-004', CourseId: 'MATH3', CourseName: 'Linear Algebra', Unit: 3, GradeValue: '2.25', MakeupGrade: '-', FinalUnits: 3, Remarks: '' }
            ];
    
            // Load student info first
            loadStudentInfo();
    
            // Try to fetch from API; fall back to sample data on error
            if (studentId) {
                fetch(`api/grades.php?StudentId=${encodeURIComponent(studentId)}`)
                    .then(resp => resp.json())
                    .then(json => {
                        if (json && json.success && Array.isArray(json.data)) {
                            renderRows(json.data);
                        } else if (json && Array.isArray(json)) {
                            renderRows(json);
                        } else {
                            renderRows(sampleData);
                        }
                    })
                    .catch(() => renderRows(sampleData));
            } else {
                renderRows(sampleData);
            }
        })();
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