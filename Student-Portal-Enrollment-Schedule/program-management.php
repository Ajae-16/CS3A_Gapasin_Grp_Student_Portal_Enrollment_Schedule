<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database/db_config.php';

$message = '';
$messageType = '';

// Fetch all programs
$programs = [];
try {
    $programs = executeQuery("SELECT ProgramId, ProgramName FROM program_data ORDER BY ProgramName ASC") ?: [];
} catch (Exception $e) {
    // Handle error silently
}

// Fetch all courses
$courses = [];
try {
    $courses = executeQuery("SELECT CourseId, CourseName FROM course_data ORDER BY CourseName ASC") ?: [];
} catch (Exception $e) {
    // Handle error silently
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    // For edit mode, use editProgramId if ProgramId is not provided
    $programId = isset($_POST['ProgramId']) && !empty(trim($_POST['ProgramId'])) 
        ? trim($_POST['ProgramId']) 
        : (isset($_POST['editProgramId']) ? trim($_POST['editProgramId']) : '');
    $programName = isset($_POST['ProgramName']) ? trim($_POST['ProgramName']) : '';
    $courseIds = isset($_POST['Courses']) ? trim($_POST['Courses']) : '';

    // Validate required fields
    if (empty($programId) || empty($programName)) {
        $message = 'Please fill in all required fields with valid values.';
        $messageType = 'error';
    } else {
        try {
            if ($action === 'add') {
                // Check if program ID already exists
                $checkSql = "SELECT ProgramId FROM program_data WHERE ProgramId = ?";
                $existing = executeQuery($checkSql, [$programId], 's');

                if (!empty($existing)) {
                    $message = 'Program ID already exists. Please use a different ID.';
                    $messageType = 'error';
                } else {
                    beginTransaction();

                    // Insert into program_data
                    $insertSql = "INSERT INTO program_data (ProgramId, ProgramName) VALUES (?, ?)";
                    $result = executeQuery($insertSql, [$programId, $programName], 'ss');

                    if ($result !== false) {
                        // Insert program subjects if courses are provided
                        if (!empty($courseIds)) {
                            $courseArray = array_map('trim', explode(',', $courseIds));
                            foreach ($courseArray as $courseId) {
                                // Verify course exists
                                $checkCourse = executeQuery("SELECT CourseId FROM course_data WHERE CourseId = ?", [$courseId], 's');
                                if (!empty($checkCourse)) {
                                    executeQuery("INSERT INTO program_subjects (ProgramId, CourseId) VALUES (?, ?)", [$programId, $courseId], 'ss');
                                }
                            }
                        }

                        commitTransaction();
                        $message = 'Program added successfully! Program ID: ' . $programId;
                        $messageType = 'success';

                        // Refresh programs list
                        $programs = executeQuery("SELECT ProgramId, ProgramName FROM program_data ORDER BY ProgramName ASC") ?: [];
                    } else {
                        rollbackTransaction();
                        $message = 'Failed to add program. Please try again.';
                        $messageType = 'error';
                    }
                }
            } elseif ($action === 'edit') {
                beginTransaction();

                // Update program_data
                $updateSql = "UPDATE program_data SET ProgramName = ? WHERE ProgramId = ?";
                $result = executeQuery($updateSql, [$programName, $programId], 'ss');

                if ($result !== false) {
                    // Delete existing program subjects
                    executeQuery("DELETE FROM program_subjects WHERE ProgramId = ?", [$programId], 's');

                    // Insert new program subjects if courses are provided
                    if (!empty($courseIds)) {
                        $courseArray = array_map('trim', explode(',', $courseIds));
                        foreach ($courseArray as $courseId) {
                            // Verify course exists
                            $checkCourse = executeQuery("SELECT CourseId FROM course_data WHERE CourseId = ?", [$courseId], 's');
                            if (!empty($checkCourse)) {
                                executeQuery("INSERT INTO program_subjects (ProgramId, CourseId) VALUES (?, ?)", [$programId, $courseId], 'ss');
                            }
                        }
                    }

                    commitTransaction();
                    $message = 'Program updated successfully!';
                    $messageType = 'success';

                    // Refresh programs list
                    $programs = executeQuery("SELECT ProgramId, ProgramName FROM program_data ORDER BY ProgramName ASC") ?: [];
                } else {
                    rollbackTransaction();
                    $message = 'Failed to update program. Please try again.';
                    $messageType = 'error';
                }
            } elseif ($action === 'delete') {
                // Delete program (cascade will handle program_subjects)
                $deleteSql = "DELETE FROM program_data WHERE ProgramId = ?";
                $result = executeQuery($deleteSql, [$programId], 's');

                if ($result !== false) {
                    $message = 'Program deleted successfully!';
                    $messageType = 'success';

                    // Refresh programs list
                    $programs = executeQuery("SELECT ProgramId, ProgramName FROM program_data ORDER BY ProgramName ASC") ?: [];
                } else {
                    $message = 'Failed to delete program. Please try again.';
                    $messageType = 'error';
                }
            }
        } catch (Exception $e) {
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
    <title>Program Management - School Portal MIS</title>
    <link rel="stylesheet" href="css/management.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="management-content">
        <div class="management-area">
            <div class="program-management-container">
                <h1>Program Management</h1>

                <button class="program-management-btn-add" onclick="openModal()">Add Program</button>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <table class="program-management-table">
                    <thead>
                        <tr>
                            <th>Program ID</th>
                            <th>Program Name</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($programs)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">No programs found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($programs as $program): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($program['ProgramId']); ?></td>
                                    <td><?php echo htmlspecialchars($program['ProgramName']); ?></td>
                                    <td class="program-management-action-cell">
                                        <button class="program-management-btn-edit" onclick="editProgram('<?php echo htmlspecialchars($program['ProgramId']); ?>', '<?php echo htmlspecialchars($program['ProgramName']); ?>')">Edit</button>
                                        <button class="program-management-btn-delete" onclick="deleteProgram('<?php echo htmlspecialchars($program['ProgramId']); ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Program Form -->
    <div id="programModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Program</h2>

            <div class="form-container">
                <form method="POST" action="">
                    <input type="hidden" id="action" name="action" value="add">
                    <input type="hidden" id="editProgramId" name="editProgramId" value="">

                    <div class="form-group">
                        <label for="ProgramId">Program ID <span class="required">*</span></label>
                        <input type="text" id="ProgramId" name="ProgramId" required placeholder="e.g., BSIT, BSCS">
                        <div class="form-description">Unique identifier for the program</div>
                    </div>

                    <div class="form-group">
                        <label for="ProgramName">Program Name <span class="required">*</span></label>
                        <input type="text" id="ProgramName" name="ProgramName" required placeholder="e.g., Bachelor of Science in Information Technology">
                        <div class="form-description">Full name of the program</div>
                    </div>

                    <div class="form-group">
                        <label for="Courses">Courses (Optional)</label>
                        <input type="text" id="Courses" name="Courses" placeholder="e.g., COSC80, CS101, CS102">
                        <div class="form-description">Enter course IDs separated by commas (e.g., COSC80, CS101, CS102)</div>
                    </div>

                    <div style="margin-bottom: 15px; padding: 12px; background: #f0f9ff; border-left: 4px solid #3b82f6; border-radius: 4px;">
                        <strong>Available Courses:</strong>
                        <div style="margin-top: 8px; font-size: 13px; color: #475569;">
                            <?php if (!empty($courses)): ?>
                                <?php foreach ($courses as $course): ?>
                                    <div style="padding: 4px 0;">
                                        <code style="background: white; padding: 2px 6px; border-radius: 3px;"><?php echo htmlspecialchars($course['CourseId']); ?></code> - <?php echo htmlspecialchars($course['CourseName']); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <span style="color: #dc2626;">No courses available</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">Add Program</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h2>Confirm Delete</h2>
            <p style="margin: 20px 0; color: #6b7280;">Are you sure you want to delete this program?</p>

            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteProgramId" name="ProgramId" value="">
                <input type="hidden" id="deleteProgramName" name="ProgramName" value="">

                <button type="button" class="btn-edit" style="width: 48%; margin-right: 4%;" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-delete" style="width: 48%; margin-left: 4%;">Delete</button>
            </form>
        </div>
    </div>

    <script>
        // Get courses data from PHP
        const allCourses = <?php echo json_encode($courses); ?>;

        // Modal functions for Add/Edit
        function openModal() {
            document.getElementById('action').value = 'add';
            document.getElementById('modalTitle').textContent = 'Add New Program';
            document.getElementById('submitBtn').textContent = 'Add Program';
            document.getElementById('ProgramId').value = '';
            document.getElementById('ProgramName').value = '';
            document.getElementById('Courses').value = '';
            document.getElementById('editProgramId').value = '';
            document.getElementById('ProgramId').disabled = false;
            document.getElementById('programModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('programModal').style.display = 'none';
        }

        function editProgram(programId, programName) {
            document.getElementById('action').value = 'edit';
            document.getElementById('modalTitle').textContent = 'Edit Program';
            document.getElementById('submitBtn').textContent = 'Update Program';
            document.getElementById('ProgramId').value = programId;
            document.getElementById('ProgramName').value = programName;
            document.getElementById('editProgramId').value = programId;
            document.getElementById('Courses').value = '';
            document.getElementById('ProgramId').disabled = true;

            // Fetch existing courses for this program
            fetch('api/program_courses.php?ProgramId=' + encodeURIComponent(programId))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error, status = ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Program courses response:', data);
                    if (data.success && data.courses) {
                        document.getElementById('Courses').value = data.courses.join(', ');
                    }
                })
                .catch(error => {
                    console.error('Error fetching program courses:', error);
                });

            document.getElementById('programModal').style.display = 'block';
        }

        function deleteProgram(programId) {
            document.getElementById('deleteProgramId').value = programId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var programModal = document.getElementById('programModal');
            var deleteModal = document.getElementById('deleteModal');
            if (event.target == programModal) {
                programModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
