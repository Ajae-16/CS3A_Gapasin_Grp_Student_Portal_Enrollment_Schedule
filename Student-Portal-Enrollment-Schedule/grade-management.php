<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database/db_config.php';



$message = '';
$messageType = '';

// Fetch students for dropdown
$students = [];
try {
    $sql = "SELECT sd.StudentId, CONCAT(sd.FirstName, ' ', sd.LastName) as FullName
            FROM student_data sd
            INNER JOIN student_account sa ON sd.StudentId = sa.StudentId
            WHERE sa.Role = 'student'
            ORDER BY sd.LastName ASC, sd.FirstName ASC";
    $students = executeQuery($sql) ?: [];
    // Debug: Uncomment to see what we get
    // echo "<!-- DEBUG: Students: " . json_encode($students) . " -->";
} catch (Exception $e) {
    // Handle error silently
    // Debug: Uncomment to see errors
    // echo "<!-- DEBUG: Error: " . $e->getMessage() . " -->";
}

// Fetch courses for dropdown
$courses = [];
try {
    $courses = executeQuery("SELECT CourseId, CourseName, Unit FROM course_data ORDER BY CourseName ASC") ?: [];
} catch (Exception $e) {
    // Handle error silently
}

// Fetch all grades with student and course info
$grades = [];
try {
    $sql = "SELECT g.*,
            CONCAT(s.FirstName, ' ', s.LastName) as StudentName,
            e.YearLevel,
            c.CourseName,
            c.Unit
            FROM grades g
            LEFT JOIN student_data s ON g.StudentId = s.StudentId
            LEFT JOIN enrollment_data e ON g.StudentId = e.StudentId AND g.Semester = e.Semester
            LEFT JOIN course_data c ON g.CourseId = c.CourseId
            ORDER BY g.SchoolYear DESC, g.Semester DESC, s.LastName ASC";
    $grades = executeQuery($sql) ?: [];
} catch (Exception $e) {
    // Handle error silently
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    
    // DELETE operation - check first before validation
    if ($action === 'delete') {
        $gradeId = isset($_POST['GradeId']) ? trim($_POST['GradeId']) : '';
        
        if (!empty($gradeId)) {
            try {
                $deleteSql = "DELETE FROM grades WHERE GradeId = ?";
                $result = executeQuery($deleteSql, [$gradeId], 's');

                if ($result !== false) {
                    $message = 'Grade deleted successfully!';
                    $messageType = 'success';
                    
                    // Refresh grades list
                    $grades = executeQuery("SELECT g.*,
                            CONCAT(s.FirstName, ' ', s.LastName) as StudentName,
                            e.YearLevel,
                            c.CourseName,
                            c.Unit
                            FROM grades g
                            LEFT JOIN student_data s ON g.StudentId = s.StudentId
                            LEFT JOIN enrollment_data e ON g.StudentId = e.StudentId AND g.Semester = e.Semester
                            LEFT JOIN course_data c ON g.CourseId = c.CourseId
                            ORDER BY g.SchoolYear DESC, g.Semester DESC, s.LastName ASC") ?: [];
                } else {
                    $message = 'Failed to delete grade. Please try again.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
    // ADD and UPDATE operations
    else {
        $gradeId = isset($_POST['GradeId']) ? trim($_POST['GradeId']) : '';
        $studentId = isset($_POST['StudentId']) ? trim($_POST['StudentId']) : '';
        $courseId = isset($_POST['CourseId']) ? trim($_POST['CourseId']) : '';
        $gradeValue = isset($_POST['GradeValue']) ? trim($_POST['GradeValue']) : '';
        $schoolYear = isset($_POST['SchoolYear']) ? trim($_POST['SchoolYear']) : '';
        $semester = isset($_POST['Semester']) ? trim($_POST['Semester']) : '';
        $makeupGrade = isset($_POST['MakeupGrade']) ? trim($_POST['MakeupGrade']) : null;
        $remarks = isset($_POST['Remarks']) ? trim($_POST['Remarks']) : null;

        // Validate required fields
        if (empty($studentId) || empty($courseId) || empty($gradeValue) || empty($schoolYear) || empty($semester)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        } else {
            try {
                if ($action === 'add') {
                    // Generate unique GradeId
                    $gradeId = 'G-' . strtoupper(uniqid());
                    
                    // Check if grade already exists for this student-course-term combination
                    $checkSql = "SELECT GradeId FROM grades WHERE StudentId = ? AND CourseId = ? AND SchoolYear = ? AND Semester = ?";
                    $existing = executeQuery($checkSql, [$studentId, $courseId, $schoolYear, $semester], 'ssss');

                    if (!empty($existing)) {
                        $message = 'Grade already exists for this student, course, and term. Please update the existing grade instead.';
                        $messageType = 'error';
                    } else {
                        // Get course unit
                        $courseInfo = executeQuery("SELECT Unit FROM course_data WHERE CourseId = ?", [$courseId], 's');
                        $finalUnits = !empty($courseInfo) ? $courseInfo[0]['Unit'] : 0;
                        
                        // Determine remarks based on grade
                        if (empty($remarks)) {
                            $gradeVal = floatval($gradeValue);
                            if ($gradeVal > 0 && $gradeVal <= 3.00) {
                                $remarks = 'Passed';
                            } elseif ($gradeVal >= 5.00) {
                                $remarks = 'Failed';
                            } else {
                                $remarks = 'In Progress';
                            }
                        }

                        $insertSql = "INSERT INTO grades (GradeId, StudentId, CourseId, GradeValue, SchoolYear, Semester, MakeupGrade, FinalUnits, Remarks) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $result = executeQuery($insertSql, [$gradeId, $studentId, $courseId, $gradeValue, $schoolYear, $semester, $makeupGrade, $finalUnits, $remarks], 'sssssssss');

                        if ($result !== false) {
                            $message = 'Grade added successfully!';
                            $messageType = 'success';
                            
                            // Refresh grades list
                            $grades = executeQuery("SELECT g.*,
                                    CONCAT(s.FirstName, ' ', s.LastName) as StudentName,
                                    e.YearLevel,
                                    c.CourseName,
                                    c.Unit
                                    FROM grades g
                                    LEFT JOIN student_data s ON g.StudentId = s.StudentId
                                    LEFT JOIN enrollment_data e ON g.StudentId = e.StudentId AND g.Semester = e.Semester
                                    LEFT JOIN course_data c ON g.CourseId = c.CourseId
                                    ORDER BY g.SchoolYear DESC, g.Semester DESC, s.LastName ASC") ?: [];
                        } else {
                            $message = 'Failed to add grade. Please try again.';
                            $messageType = 'error';
                        }
                    }
                } elseif ($action === 'update') {
                    // Determine remarks based on grade if not provided
                    if (empty($remarks)) {
                        $gradeVal = floatval($gradeValue);
                        if ($gradeVal > 0 && $gradeVal <= 3.00) {
                            $remarks = 'Passed';
                        } elseif ($gradeVal >= 5.00) {
                            $remarks = 'Failed';
                        } else {
                            $remarks = 'In Progress';
                        }
                    }

                    $updateSql = "UPDATE grades SET GradeValue = ?, MakeupGrade = ?, Remarks = ? WHERE GradeId = ?";
                    $result = executeQuery($updateSql, [$gradeValue, $makeupGrade, $remarks, $gradeId], 'ssss');

                    if ($result !== false) {
                        $message = 'Grade updated successfully!';
                        $messageType = 'success';
                        
                        // Refresh grades list
                        $grades = executeQuery("SELECT g.*,
                                CONCAT(s.FirstName, ' ', s.LastName) as StudentName,
                                e.YearLevel,
                                c.CourseName,
                                c.Unit
                                FROM grades g
                                LEFT JOIN student_data s ON g.StudentId = s.StudentId
                                LEFT JOIN enrollment_data e ON g.StudentId = e.StudentId AND g.Semester = e.Semester
                                LEFT JOIN course_data c ON g.CourseId = c.CourseId
                                ORDER BY g.SchoolYear DESC, g.Semester DESC, s.LastName ASC") ?: [];
                    } else {
                        $message = 'Failed to update grade. Please try again.';
                        $messageType = 'error';
                    }
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade Management - School Portal MIS</title>
    <link rel="stylesheet" href="css/management.css">
    <style>
        .filter-section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #555;
        }
        
        .grade-excellent { background: #d4edda; color: #155724; font-weight: 700; }
        .grade-very-good { background: #d1ecf1; color: #0c5460; font-weight: 700; }
        .grade-good { background: #fff3cd; color: #856404; font-weight: 700; }
        .grade-fair { background: #f8d7da; color: #721c24; font-weight: 700; }
        .grade-failed { background: #f5c6cb; color: #721c24; font-weight: 700; }
        
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .grade-scale {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .grade-scale span {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="management-content">
        <div class="management-area">
            <div class="student-management-container">
                <h1>Grade Management</h1>

                <!-- Information Box -->
                <div class="info-box">
                    <p><strong>💡 Grade Scale:</strong></p>
                    <div class="grade-scale">
                        <span class="grade-excellent">1.00-1.75 Excellent</span>
                        <span class="grade-very-good">2.00-2.25 Very Good</span>
                        <span class="grade-good">2.50-2.75 Good</span>
                        <span class="grade-fair">3.00 Fair/Passing</span>
                        <span class="grade-failed">5.00 Failed</span>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <h3>Filter Grades</h3>
                    <div class="filter-row">
                        <div class="filter-group">
                            <label>School Year</label>
                            <select id="filterSchoolYear">
                                <option value="">All Years</option>
                                <option value="2024-2025" selected>2024-2025</option>
                                <option value="2023-2024">2023-2024</option>
                                <option value="2022-2023">2022-2023</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Semester</label>
                            <select id="filterSemester">
                                <option value="">All Semesters</option>
                                <option value="1st Semester" selected>1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label>Student</label>
                            <select id="filterStudent">
                                <option value="">All Students</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo htmlspecialchars($student['StudentId']); ?>">
                                        <?php echo htmlspecialchars($student['FullName']); ?> (<?php echo htmlspecialchars($student['StudentId']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button class="student-management-btn-add" onclick="applyFilters()">Apply Filters</button>
                    <button class="btn-edit" onclick="resetFilters()">Reset</button>
                </div>

                <button class="student-management-btn-add" onclick="openModal()">Add Grade</button>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <table class="student-management-table">
                    <thead>
                        <tr>
                            <th>Grade ID</th>
                            <th>Student</th>
                            <th>Year Level</th>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Units</th>
                            <th>Grade</th>
                            <th>Makeup</th>
                            <th>School Year</th>
                            <th>Semester</th>
                            <th>Remarks</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="gradesTableBody">
                        <?php if (empty($grades)): ?>
                            <tr>
                                <td colspan="12" style="text-align: center;">No grades found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($grades as $grade): 
                                $gradeValue = floatval($grade['GradeValue']);
                                $gradeClass = '';
                                if ($gradeValue <= 1.75) $gradeClass = 'grade-excellent';
                                elseif ($gradeValue <= 2.25) $gradeClass = 'grade-very-good';
                                elseif ($gradeValue <= 2.75) $gradeClass = 'grade-good';
                                elseif ($gradeValue <= 3.00) $gradeClass = 'grade-fair';
                                else $gradeClass = 'grade-failed';
                            ?>
                                <tr data-student="<?php echo htmlspecialchars($grade['StudentId']); ?>" 
                                    data-year="<?php echo htmlspecialchars($grade['SchoolYear']); ?>" 
                                    data-semester="<?php echo htmlspecialchars($grade['Semester']); ?>">
                                    <td><?php echo htmlspecialchars($grade['GradeId']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['StudentName']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['YearLevel']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($grade['CourseId']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($grade['CourseName']); ?></td>
                                    <td style="text-align: center;"><?php echo htmlspecialchars($grade['Unit']); ?></td>
                                    <td class="<?php echo $gradeClass; ?>" style="text-align: center; font-weight: 700;">
                                        <?php echo number_format($gradeValue, 2); ?>
                                    </td>
                                    <td style="text-align: center;"><?php echo htmlspecialchars($grade['MakeupGrade'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($grade['SchoolYear']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['Semester']); ?></td>
                                    <td><?php echo htmlspecialchars($grade['Remarks'] ?? '-'); ?></td>
                                    <td class="student-management-action-cell">
                                        <button class="student-management-btn-edit" 
                                                onclick="editGrade('<?php echo htmlspecialchars($grade['GradeId']); ?>', 
                                                                   '<?php echo htmlspecialchars($grade['StudentId']); ?>', 
                                                                   '<?php echo htmlspecialchars($grade['CourseId']); ?>', 
                                                                   '<?php echo htmlspecialchars($grade['GradeValue']); ?>', 
                                                                   '<?php echo htmlspecialchars($grade['SchoolYear']); ?>', 
                                                                   '<?php echo htmlspecialchars($grade['Semester']); ?>', 
                                                                   '<?php echo htmlspecialchars($grade['MakeupGrade'] ?? ''); ?>', 
                                                                   '<?php echo htmlspecialchars($grade['Remarks'] ?? ''); ?>')">Edit</button>
                                        <button class="student-management-btn-delete" 
                                                onclick="deleteGrade('<?php echo htmlspecialchars($grade['GradeId']); ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Grade -->
    <div id="gradeModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Grade</h2>

            <div class="form-container">
                <form method="POST" action="">
                    <input type="hidden" id="action" name="action" value="add">
                    <input type="hidden" id="GradeId" name="GradeId" value="">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="StudentId">Student <span class="required">*</span></label>
                            <select id="StudentId" name="StudentId" required>
                                <option value="">Select Student</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo htmlspecialchars($student['StudentId']); ?>">
                                        <?php echo htmlspecialchars($student['FullName']); ?> (<?php echo htmlspecialchars($student['StudentId']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="CourseId">Course <span class="required">*</span></label>
                            <select id="CourseId" name="CourseId" required>
                                <option value="">Select Course</option>
                                <?php foreach ($courses as $course): ?>
                                    <option value="<?php echo htmlspecialchars($course['CourseId']); ?>">
                                        <?php echo htmlspecialchars($course['CourseName']); ?> (<?php echo htmlspecialchars($course['CourseId']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="GradeValue">Grade <span class="required">*</span></label>
                            <input type="number" step="0.01" min="1.00" max="5.00" id="GradeValue" name="GradeValue" required placeholder="e.g., 1.75">
                            <small style="color: #666;">Enter grade from 1.00 to 5.00</small>
                        </div>
                        <div class="form-group">
                            <label for="MakeupGrade">Makeup Grade</label>
                            <input type="number" step="0.01" min="1.00" max="5.00" id="MakeupGrade" name="MakeupGrade" placeholder="Optional">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="SchoolYear">School Year <span class="required">*</span></label>
                            <select id="SchoolYear" name="SchoolYear" required>
                                <option value="">Select School Year</option>
                                <option value="2024-2025" selected>2024-2025</option>
                                <option value="2023-2024">2023-2024</option>
                                <option value="2022-2023">2022-2023</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Semester">Semester <span class="required">*</span></label>
                            <select id="Semester" name="Semester" required>
                                <option value="">Select Semester</option>
                                <option value="1st Semester" selected>1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                                <option value="Summer">Summer</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Remarks">Remarks</label>
                        <select id="Remarks" name="Remarks">
                            <option value="">Auto-generate based on grade</option>
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Incomplete">Incomplete</option>
                            <option value="Dropped">Dropped</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">Add Grade</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h2>Confirm Delete</h2>
            <p style="margin: 20px 0; color: #6b7280;">Are you sure you want to delete this grade?</p>

            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteGradeId" name="GradeId" value="">

                <button type="button" class="btn-edit" style="width: 48%; margin-right: 4%;" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-delete" style="width: 48%; margin-left: 4%;">Delete</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openModal() {
            document.getElementById('action').value = 'add';
            document.getElementById('modalTitle').textContent = 'Add New Grade';
            document.getElementById('submitBtn').textContent = 'Add Grade';
            document.getElementById('GradeId').value = '';
            document.getElementById('StudentId').disabled = false;
            document.getElementById('CourseId').disabled = false;
            document.getElementById('SchoolYear').disabled = false;
            document.getElementById('Semester').disabled = false;

            // Reset form
            document.querySelectorAll('input[type="number"], select').forEach(field => {
                if (field.id !== 'action') field.value = '';
            });

            document.getElementById('gradeModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('gradeModal').style.display = 'none';
        }

        function editGrade(gradeId, studentId, courseId, gradeValue, schoolYear, semester, makeupGrade, remarks) {
            document.getElementById('action').value = 'update';
            document.getElementById('modalTitle').textContent = 'Edit Grade';
            document.getElementById('submitBtn').textContent = 'Update Grade';
            document.getElementById('GradeId').value = gradeId;
            document.getElementById('StudentId').value = studentId;
            document.getElementById('StudentId').disabled = true;
            document.getElementById('CourseId').value = courseId;
            document.getElementById('CourseId').disabled = true;
            document.getElementById('GradeValue').value = gradeValue;
            document.getElementById('SchoolYear').value = schoolYear;
            document.getElementById('SchoolYear').disabled = true;
            document.getElementById('Semester').value = semester;
            document.getElementById('Semester').disabled = true;
            document.getElementById('MakeupGrade').value = makeupGrade || '';
            document.getElementById('Remarks').value = remarks || '';

            document.getElementById('gradeModal').style.display = 'block';
        }

        function deleteGrade(gradeId) {
            document.getElementById('deleteGradeId').value = gradeId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Filter functions
        function applyFilters() {
            const year = document.getElementById('filterSchoolYear').value;
            const semester = document.getElementById('filterSemester').value;
            const student = document.getElementById('filterStudent').value;
            
            const rows = document.querySelectorAll('#gradesTableBody tr');
            
            rows.forEach(row => {
                if (row.cells.length <= 1) return; // Skip "no data" row
                
                const matchYear = !year || row.dataset.year === year;
                const matchSemester = !semester || row.dataset.semester === semester;
                const matchStudent = !student || row.dataset.student === student;
                
                if (matchYear && matchSemester && matchStudent) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function resetFilters() {
            document.getElementById('filterSchoolYear').value = '2024-2025';
            document.getElementById('filterSemester').value = '1st Semester';
            document.getElementById('filterStudent').value = '';
            applyFilters();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const gradeModal = document.getElementById('gradeModal');
            const deleteModal = document.getElementById('deleteModal');
            if (event.target == gradeModal) {
                gradeModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>