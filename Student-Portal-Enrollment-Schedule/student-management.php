<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database/db_config.php';

$message = '';
$messageType = '';

// Fetch programs for dropdown
$programs = [];
try {
    $programs = executeQuery("SELECT ProgramId, ProgramName FROM program_data ORDER BY ProgramName ASC") ?: [];
} catch (Exception $e) {
    // Handle error silently for now
}

// Fetch enrolled students
$enrolledStudents = [];
try {
    $sql = "SELECT e.StudentId, s.FirstName, s.LastName, p.ProgramName, e.YearLevel, e.Semester
            FROM enrollment_data e
            JOIN student_data s ON e.StudentId = s.StudentId
            JOIN program_data p ON e.ProgramId = p.ProgramId
            ORDER BY s.LastName ASC, s.FirstName ASC";
    $enrolledStudents = executeQuery($sql) ?: [];
} catch (Exception $e) {
    // Handle error silently for now
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    $studentId = isset($_POST['StudentId']) && !empty(trim($_POST['StudentId'])) 
        ? trim($_POST['StudentId']) 
        : (isset($_POST['editStudentId']) ? trim($_POST['editStudentId']) : '');
    $firstName = isset($_POST['FirstName']) ? trim($_POST['FirstName']) : '';
    $middleName = !empty(trim($_POST['MiddleName'] ?? '')) ? trim($_POST['MiddleName']) : 'N/A';
    $lastName = isset($_POST['LastName']) ? trim($_POST['LastName']) : '';
    $major = !empty(trim($_POST['Major'] ?? '')) ? trim($_POST['Major']) : 'N/A';
    $dateOfBirth = $_POST['DateOfBirth'] ?? '';
    $sex = $_POST['Sex'] ?? '';
    $citizenship = isset($_POST['Citizenship']) ? trim($_POST['Citizenship']) : '';
    $streetName = isset($_POST['StreetName']) ? trim($_POST['StreetName']) : '';
    $barangay = isset($_POST['Barangay']) ? trim($_POST['Barangay']) : '';
    $province = !empty(trim($_POST['Province'] ?? '')) ? trim($_POST['Province']) : 'N/A';
    $municipality = isset($_POST['Municipality']) ? trim($_POST['Municipality']) : '';
    $civilStatus = $_POST['CivilStatus'] ?? '';
    $religion = isset($_POST['Religion']) ? trim($_POST['Religion']) : '';
    $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
    $contactNumber = isset($_POST['ContactNumber']) ? trim($_POST['ContactNumber']) : '';
    $guardianName = !empty(trim($_POST['GuardianName'] ?? '')) ? trim($_POST['GuardianName']) : 'N/A';
    $guardianContact = !empty(trim($_POST['GuardianContact'] ?? '')) ? trim($_POST['GuardianContact']) : 'N/A';
    $fatherName = !empty(trim($_POST['FatherName'] ?? '')) ? trim($_POST['FatherName']) : 'N/A';
    $fatherOccupation = !empty(trim($_POST['FatherOccupation'] ?? '')) ? trim($_POST['FatherOccupation']) : 'N/A';
    $motherName = !empty(trim($_POST['MotherName'] ?? '')) ? trim($_POST['MotherName']) : 'N/A';
    $motherOccupation = !empty(trim($_POST['MotherOccupation'] ?? '')) ? trim($_POST['MotherOccupation']) : 'N/A';
    $programId = isset($_POST['ProgramId']) ? trim($_POST['ProgramId']) : '';
    $yearLevel = isset($_POST['YearLevel']) ? trim($_POST['YearLevel']) : '';
    $semester = isset($_POST['Semester']) ? trim($_POST['Semester']) : '';

    // Validate required fields
    if (empty($studentId) || empty($firstName) || empty($lastName) || empty($dateOfBirth) || empty($sex) || empty($citizenship) || empty($streetName) || empty($barangay) || empty($municipality) || empty($civilStatus) || empty($religion) || empty($email) || empty($contactNumber) || empty($programId) || empty($yearLevel) || empty($semester)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    } else {
        try {
            if ($action === 'add') {
                // Check if student ID already exists
                $checkSql = "SELECT StudentId FROM student_data WHERE StudentId = ?";
                $existing = executeQuery($checkSql, [$studentId], 's');

                if (!empty($existing)) {
                    $message = 'Student ID already exists. Please use a different ID.';
                    $messageType = 'error';
                } else {
                    // Begin transaction
                    $conn = getDBConnection();

                    // Insert into student_data
                    $result1 = executeQuery("INSERT INTO student_data (StudentId, FirstName, MiddleName, LastName, Major, DateOfBirth, Sex, Citizenship, StreetName, Barangay, Province, Municipality, CivilStatus, Religion, Email, ContactNumber, GuardianName, GuardianContact, FatherName, FatherOccupation, MotherName, MotherOccupation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [$studentId, $firstName, $middleName, $lastName, $major, $dateOfBirth, $sex, $citizenship, $streetName, $barangay, $province, $municipality, $civilStatus, $religion, $email, $contactNumber, $guardianName, $guardianContact, $fatherName, $fatherOccupation, $motherName, $motherOccupation], 'ssssssssssssssssssssss');
                    if ($result1 === false) {
                        throw new Exception("Failed to insert student data");
                    }

                    // Create full name for account
                    $fullName = $firstName . ' ' . ($middleName && $middleName !== 'N/A' ? $middleName . ' ' : '') . $lastName;

                    // Hash the default password
                    $defaultPassword = '1234';
                    $hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

                    // Insert into student_account
                    $result2 = executeQuery("INSERT INTO student_account (StudentId, FullName, Password) VALUES (?, ?, ?)", [$studentId, $fullName, $hashedPassword], 'sss');
                    if ($result2 === false) {
                        throw new Exception("Failed to insert student account");
                    }

                    // Insert into enrollment_data
                    $result3 = executeQuery("INSERT INTO enrollment_data (StudentId, ProgramId, YearLevel, Semester) VALUES (?, ?, ?, ?)", [$studentId, $programId, $yearLevel, $semester], 'ssss');
                    if ($result3 === false) {
                        throw new Exception("Failed to insert enrollment data");
                    }

                    // Commit transaction
                    commitTransaction();

                    $message = 'Student added successfully! Student ID: ' . $studentId . ', Password: ' . $defaultPassword;
                    $messageType = 'success';
                    
                    // Refresh students list
                    $enrolledStudents = executeQuery("SELECT e.StudentId, s.FirstName, s.LastName, p.ProgramName, e.YearLevel, e.Semester
            FROM enrollment_data e
            JOIN student_data s ON e.StudentId = s.StudentId
            JOIN program_data p ON e.ProgramId = p.ProgramId
            ORDER BY s.LastName ASC, s.FirstName ASC") ?: [];
                }
            } elseif ($action === 'edit') {
                // Begin transaction
                beginTransaction();

                // Update student_data
                $updateSql = "UPDATE student_data SET FirstName = ?, MiddleName = ?, LastName = ?, Major = ?, DateOfBirth = ?, Sex = ?, Citizenship = ?, StreetName = ?, Barangay = ?, Province = ?, Municipality = ?, CivilStatus = ?, Religion = ?, Email = ?, ContactNumber = ?, GuardianName = ?, GuardianContact = ?, FatherName = ?, FatherOccupation = ?, MotherName = ?, MotherOccupation = ? WHERE StudentId = ?";
                $result1 = executeQuery($updateSql, [$firstName, $middleName, $lastName, $major, $dateOfBirth, $sex, $citizenship, $streetName, $barangay, $province, $municipality, $civilStatus, $religion, $email, $contactNumber, $guardianName, $guardianContact, $fatherName, $fatherOccupation, $motherName, $motherOccupation, $studentId], 'ssssssssssssssssssssss');
                if ($result1 === false) {
                    throw new Exception("Failed to update student data");
                }

                // Update full name in student_account
                $fullName = $firstName . ' ' . ($middleName && $middleName !== 'N/A' ? $middleName . ' ' : '') . $lastName;
                $result2 = executeQuery("UPDATE student_account SET FullName = ? WHERE StudentId = ?", [$fullName, $studentId], 'ss');
                if ($result2 === false) {
                    throw new Exception("Failed to update student account");
                }

                // Update enrollment_data
                $result3 = executeQuery("UPDATE enrollment_data SET ProgramId = ?, YearLevel = ?, Semester = ? WHERE StudentId = ?", [$programId, $yearLevel, $semester, $studentId], 'ssss');
                if ($result3 === false) {
                    throw new Exception("Failed to update enrollment data");
                }

                // Commit transaction
                commitTransaction();

                $message = 'Student updated successfully!';
                $messageType = 'success';
                
                // Refresh students list
                $enrolledStudents = executeQuery("SELECT e.StudentId, s.FirstName, s.LastName, p.ProgramName, e.YearLevel, e.Semester
            FROM enrollment_data e
            JOIN student_data s ON e.StudentId = s.StudentId
            JOIN program_data p ON e.ProgramId = p.ProgramId
            ORDER BY s.LastName ASC, s.FirstName ASC") ?: [];
            } elseif ($action === 'delete') {
                // Delete student (cascade will handle related records)
                $deleteSql = "DELETE FROM student_data WHERE StudentId = ?";
                $result = executeQuery($deleteSql, [$studentId], 's');

                if ($result !== false) {
                    $message = 'Student deleted successfully!';
                    $messageType = 'success';
                    
                    // Refresh students list
                    $enrolledStudents = executeQuery("SELECT e.StudentId, s.FirstName, s.LastName, p.ProgramName, e.YearLevel, e.Semester
            FROM enrollment_data e
            JOIN student_data s ON e.StudentId = s.StudentId
            JOIN program_data p ON e.ProgramId = p.ProgramId
            ORDER BY s.LastName ASC, s.FirstName ASC") ?: [];
                } else {
                    $message = 'Failed to delete student. Please try again.';
                    $messageType = 'error';
                }
            }
        } catch (Exception $e) {
            // Rollback transaction on error
            rollbackTransaction();
            $message = 'Error: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - School Portal MIS</title>
    <link rel="stylesheet" href="css/management.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="management-content">
        <div class="management-area">
            <div class="student-management-container">
                <h1>Student Management</h1>

                <button class="student-management-btn-add" onclick="openModal()">Add Student</button>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <table class="student-management-table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Program</th>
                            <th>Year Level</th>
                            <th>Semester</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($enrolledStudents)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No enrolled students found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($enrolledStudents as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['StudentId']); ?></td>
                                    <td><?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?></td>
                                    <td><?php echo htmlspecialchars($student['ProgramName']); ?></td>
                                    <td><?php echo htmlspecialchars($student['YearLevel']); ?></td>
                                    <td><?php echo htmlspecialchars($student['Semester']); ?></td>
                                    <td class="student-management-action-cell">
                                        <button class="student-management-btn-edit" onclick="editStudent('<?php echo htmlspecialchars($student['StudentId']); ?>')">Edit</button>
                                        <button class="student-management-btn-delete" onclick="deleteStudent('<?php echo htmlspecialchars($student['StudentId']); ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Student Form -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Student</h2>

            <div class="form-container">
                <form method="POST" action="">
                    <input type="hidden" id="action" name="action" value="add">
                    <input type="hidden" id="editStudentId" name="editStudentId" value="">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="StudentId">Student ID <span class="required">*</span></label>
                            <input type="text" id="StudentId" name="StudentId" required>
                        </div>
                        <div class="form-group">
                            <label for="FirstName">First Name <span class="required">*</span></label>
                            <input type="text" id="FirstName" name="FirstName" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="MiddleName">Middle Name</label>
                            <input type="text" id="MiddleName" name="MiddleName">
                        </div>
                        <div class="form-group">
                            <label for="LastName">Last Name <span class="required">*</span></label>
                            <input type="text" id="LastName" name="LastName" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Major">Major</label>
                        <input type="text" id="Major" name="Major">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="DateOfBirth">Date of Birth <span class="required">*</span></label>
                            <input type="date" id="DateOfBirth" name="DateOfBirth" required>
                        </div>
                        <div class="form-group">
                            <label for="Sex">Sex <span class="required">*</span></label>
                            <select id="Sex" name="Sex" required>
                                <option value="">Select Sex</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="Citizenship">Citizenship <span class="required">*</span></label>
                            <input type="text" id="Citizenship" name="Citizenship" required>
                        </div>
                        <div class="form-group">
                            <label for="ContactNumber">Contact Number <span class="required">*</span></label>
                            <input type="text" id="ContactNumber" name="ContactNumber" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="StreetName">Street Name <span class="required">*</span></label>
                            <input type="text" id="StreetName" name="StreetName" required>
                        </div>
                        <div class="form-group">
                            <label for="Barangay">Barangay <span class="required">*</span></label>
                            <input type="text" id="Barangay" name="Barangay" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="Province">Province <span class="required">*</span></label>
                            <input type="text" id="Province" name="Province" required>
                        </div>
                        <div class="form-group">
                            <label for="Municipality">Municipality <span class="required">*</span></label>
                            <input type="text" id="Municipality" name="Municipality" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="CivilStatus">Civil Status <span class="required">*</span></label>
                            <select id="CivilStatus" name="CivilStatus" required>
                                <option value="">Select Civil Status</option>
                                <option value="Single">Single</option>
                                <option value="Married">Married</option>
                                <option value="Divorced">Divorced</option>
                                <option value="Widowed">Widowed</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Religion">Religion <span class="required">*</span></label>
                            <input type="text" id="Religion" name="Religion" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Email">Email <span class="required">*</span></label>
                        <input type="email" id="Email" name="Email" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="GuardianName">Guardian's Name</label>
                            <input type="text" id="GuardianName" name="GuardianName">
                        </div>
                        <div class="form-group">
                            <label for="GuardianContact">Guardian's Contact</label>
                            <input type="text" id="GuardianContact" name="GuardianContact">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="FatherName">Father's Name</label>
                            <input type="text" id="FatherName" name="FatherName">
                        </div>
                        <div class="form-group">
                            <label for="FatherOccupation">Father's Occupation</label>
                            <input type="text" id="FatherOccupation" name="FatherOccupation">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="MotherName">Mother's Name</label>
                            <input type="text" id="MotherName" name="MotherName">
                        </div>
                        <div class="form-group">
                            <label for="MotherOccupation">Mother's Occupation</label>
                            <input type="text" id="MotherOccupation" name="MotherOccupation">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ProgramId">Program <span class="required">*</span></label>
                            <select id="ProgramId" name="ProgramId" required>
                                <option value="">Select Program</option>
                                <?php foreach ($programs as $program): ?>
                                    <option value="<?php echo htmlspecialchars($program['ProgramId']); ?>">
                                        <?php echo htmlspecialchars($program['ProgramName']); ?> (<?php echo htmlspecialchars($program['ProgramId']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="YearLevel">Year Level <span class="required">*</span></label>
                            <select id="YearLevel" name="YearLevel" required>
                                <option value="">Select Year Level</option>
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="Semester">Semester <span class="required">*</span></label>
                        <select id="Semester" name="Semester" required>
                            <option value="">Select Semester</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                        </select>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">Add Student</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h2>Confirm Delete</h2>
            <p style="margin: 20px 0; color: #6b7280;">Are you sure you want to delete this student?</p>

            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteStudentId" name="StudentId" value="">

                <button type="button" class="btn-edit" style="width: 48%; margin-right: 4%;" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-delete" style="width: 48%; margin-left: 4%;">Delete</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functions for Add/Edit
        function openModal() {
            document.getElementById('action').value = 'add';
            document.getElementById('modalTitle').textContent = 'Add New Student';
            document.getElementById('submitBtn').textContent = 'Add Student';
            document.getElementById('StudentId').value = '';
            document.getElementById('StudentId').disabled = false;
            document.getElementById('editStudentId').value = '';
            
            // Reset all form fields
            document.querySelectorAll('input[type="text"], input[type="date"], input[type="email"], select, textarea').forEach(field => {
                field.value = '';
                field.disabled = false;
            });
            
            document.getElementById('addStudentModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addStudentModal').style.display = 'none';
        }

        function editStudent(studentId) {
            document.getElementById('action').value = 'edit';
            document.getElementById('modalTitle').textContent = 'Edit Student';
            document.getElementById('submitBtn').textContent = 'Update Student';
            document.getElementById('StudentId').disabled = true;
            document.getElementById('editStudentId').value = studentId;

            // Fetch student data
            fetch('api/student_data.php?StudentId=' + encodeURIComponent(studentId))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error, status = ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success && data.student) {
                        const student = data.student;
                        document.getElementById('StudentId').value = student['StudentId'];
                        document.getElementById('FirstName').value = student['FirstName'] || '';
                        document.getElementById('MiddleName').value = student['MiddleName'] || '';
                        document.getElementById('LastName').value = student['LastName'] || '';
                        document.getElementById('Major').value = student['Major'] || '';
                        document.getElementById('DateOfBirth').value = student['DateOfBirth'] || '';
                        document.getElementById('Sex').value = student['Sex'] || '';
                        document.getElementById('Citizenship').value = student['Citizenship'] || '';
                        document.getElementById('StreetName').value = student['StreetName'] || '';
                        document.getElementById('Barangay').value = student['Barangay'] || '';
                        document.getElementById('Province').value = student['Province'] || '';
                        document.getElementById('Municipality').value = student['Municipality'] || '';
                        document.getElementById('CivilStatus').value = student['CivilStatus'] || '';
                        document.getElementById('Religion').value = student['Religion'] || '';
                        document.getElementById('Email').value = student['Email'] || '';
                        document.getElementById('ContactNumber').value = student['ContactNumber'] || '';
                        document.getElementById('GuardianName').value = student['GuardianName'] || '';
                        document.getElementById('GuardianContact').value = student['GuardianContact'] || '';
                        document.getElementById('FatherName').value = student['FatherName'] || '';
                        document.getElementById('FatherOccupation').value = student['FatherOccupation'] || '';
                        document.getElementById('MotherName').value = student['MotherName'] || '';
                        document.getElementById('MotherOccupation').value = student['MotherOccupation'] || '';
                        document.getElementById('ProgramId').value = student['ProgramId'] || '';
                        document.getElementById('YearLevel').value = student['YearLevel'] || '';
                        document.getElementById('Semester').value = student['Semester'] || '';
                    } else {
                        throw new Error(data.message || 'Failed to load student data');
                    }
                })
                .catch(error => {
                    console.error('Error fetching student:', error);
                    alert('Error loading student data: ' + error.message);
                });

            document.getElementById('addStudentModal').style.display = 'block';
        }

        function deleteStudent(studentId) {
            document.getElementById('deleteStudentId').value = studentId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var studentModal = document.getElementById('addStudentModal');
            var deleteModal = document.getElementById('deleteModal');
            if (event.target == studentModal) {
                studentModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
