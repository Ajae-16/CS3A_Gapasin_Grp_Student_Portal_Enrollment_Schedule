<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is admin
$userRole = strtolower($_SESSION['userRole'] ?? 'student');
if ($userRole === 'student') {
    header('Location: dashboard.php');
    exit;
}

require_once 'database/db_config.php';

$message = '';
$messageType = '';

// Fetch all instructors
$instructors = [];
try {
    $sql = "SELECT InstructorId, FirstName, MiddleName, LastName, Email FROM instructors ORDER BY LastName ASC, FirstName ASC";
    $instructors = executeQuery($sql) ?: [];
} catch (Exception $e) {
    // Handle error silently for now
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    
    // FIX: Handle DELETE action FIRST, before validation
    if ($action === 'delete') {
        $instructorId = isset($_POST['InstructorId']) ? trim($_POST['InstructorId']) : '';
        
        if (!empty($instructorId)) {
            try {
                $deleteSql = "DELETE FROM instructors WHERE InstructorId = ?";
                $result = executeQuery($deleteSql, [$instructorId], 's');

                if ($result !== false) {
                    $message = 'Instructor deleted successfully!';
                    $messageType = 'success';
                    
                    // Refresh instructors list
                    $instructors = executeQuery("SELECT InstructorId, FirstName, MiddleName, LastName, Email FROM instructors ORDER BY LastName ASC, FirstName ASC") ?: [];
                } else {
                    $message = 'Failed to delete instructor. Please try again.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid instructor ID for deletion.';
            $messageType = 'error';
        }
    }
    // Handle ADD and EDIT actions (these require validation)
    else {
        $instructorId = isset($_POST['InstructorId']) && !empty(trim($_POST['InstructorId'])) 
            ? trim($_POST['InstructorId']) 
            : (isset($_POST['editInstructorId']) ? trim($_POST['editInstructorId']) : '');
        $firstName = isset($_POST['FirstName']) ? trim($_POST['FirstName']) : '';
        $middleName = !empty(trim($_POST['MiddleName'] ?? '')) ? trim($_POST['MiddleName']) : '';
        $lastName = isset($_POST['LastName']) ? trim($_POST['LastName']) : '';
        $email = isset($_POST['Email']) ? trim($_POST['Email']) : '';

        // Validate required fields for ADD and EDIT only
        if (empty($instructorId) || empty($firstName) || empty($lastName) || empty($email)) {
            $message = 'Please fill in all required fields (Instructor ID, First Name, Last Name, Email).';
            $messageType = 'error';
        } else {
            try {
                if ($action === 'add') {
                    // Check if instructor ID already exists
                    $checkSql = "SELECT InstructorId FROM instructors WHERE InstructorId = ?";
                    $existing = executeQuery($checkSql, [$instructorId], 's');

                    if (!empty($existing)) {
                        $message = 'Instructor ID already exists. Please use a different ID.';
                        $messageType = 'error';
                    } else {
                        // Insert new instructor
                        $insertSql = "INSERT INTO instructors (InstructorId, FirstName, MiddleName, LastName, Email) VALUES (?, ?, ?, ?, ?)";
                        $result = executeQuery($insertSql, [$instructorId, $firstName, $middleName, $lastName, $email], 'sssss');

                        if ($result !== false) {
                            $message = 'Instructor added successfully! Instructor ID: ' . $instructorId;
                            $messageType = 'success';
                            
                            // Refresh instructors list
                            $instructors = executeQuery("SELECT InstructorId, FirstName, MiddleName, LastName, Email FROM instructors ORDER BY LastName ASC, FirstName ASC") ?: [];
                        } else {
                            $message = 'Failed to add instructor. Please try again.';
                            $messageType = 'error';
                        }
                    }
                } elseif ($action === 'edit') {
                    // Update instructor
                    $updateSql = "UPDATE instructors SET FirstName = ?, MiddleName = ?, LastName = ?, Email = ? WHERE InstructorId = ?";
                    $result = executeQuery($updateSql, [$firstName, $middleName, $lastName, $email, $instructorId], 'sssss');

                    if ($result !== false) {
                        $message = 'Instructor updated successfully!';
                        $messageType = 'success';
                        
                        // Refresh instructors list
                        $instructors = executeQuery("SELECT InstructorId, FirstName, MiddleName, LastName, Email FROM instructors ORDER BY LastName ASC, FirstName ASC") ?: [];
                    } else {
                        $message = 'Failed to update instructor. Please try again.';
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
    <title>Instructor Management - School Portal MIS</title>
    <link rel="stylesheet" href="css/management.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="management-content">
        <div class="management-area">
            <div class="student-management-container">
                <h1>Instructor Management</h1>

                <button class="student-management-btn-add" onclick="openModal()">Add Instructor</button>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <table class="student-management-table">
                    <thead>
                        <tr>
                            <th>Instructor ID</th>
                            <th>First Name</th>
                            <th>Middle Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($instructors)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center;">No instructors found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($instructors as $instructor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($instructor['InstructorId']); ?></td>
                                    <td><?php echo htmlspecialchars($instructor['FirstName']); ?></td>
                                    <td><?php echo htmlspecialchars($instructor['MiddleName'] ?: '-'); ?></td>
                                    <td><?php echo htmlspecialchars($instructor['LastName']); ?></td>
                                    <td><?php echo htmlspecialchars($instructor['Email']); ?></td>
                                    <td class="student-management-action-cell">
                                        <button class="student-management-btn-edit" onclick="editInstructor('<?php echo htmlspecialchars($instructor['InstructorId']); ?>')">Edit</button>
                                        <button class="student-management-btn-delete" onclick="deleteInstructor('<?php echo htmlspecialchars($instructor['InstructorId']); ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Instructor Form -->
    <div id="addInstructorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Instructor</h2>

            <div class="form-container">
                <form method="POST" action="">
                    <input type="hidden" id="action" name="action" value="add">
                    <input type="hidden" id="editInstructorId" name="editInstructorId" value="">
                    
                    <div class="form-group">
                        <label for="InstructorId">Instructor ID <span class="required">*</span></label>
                        <input type="text" id="InstructorId" name="InstructorId" required placeholder="e.g., INST-001">
                    </div>

                    <div class="form-group">
                        <label for="FirstName">First Name <span class="required">*</span></label>
                        <input type="text" id="FirstName" name="FirstName" required>
                    </div>

                    <div class="form-group">
                        <label for="MiddleName">Middle Name</label>
                        <input type="text" id="MiddleName" name="MiddleName" placeholder="Optional">
                    </div>

                    <div class="form-group">
                        <label for="LastName">Last Name <span class="required">*</span></label>
                        <input type="text" id="LastName" name="LastName" required>
                    </div>

                    <div class="form-group">
                        <label for="Email">Email <span class="required">*</span></label>
                        <input type="email" id="Email" name="Email" required placeholder="instructor@cvsu.edu.ph">
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">Add Instructor</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h2>Confirm Delete</h2>
            <p style="margin: 20px 0; color: #6b7280;">Are you sure you want to delete this instructor?</p>

            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteInstructorId" name="InstructorId" value="">

                <button type="button" class="btn-edit" style="width: 48%; margin-right: 4%;" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-delete" style="width: 48%; margin-left: 4%;">Delete</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functions for Add/Edit
        function openModal() {
            document.getElementById('action').value = 'add';
            document.getElementById('modalTitle').textContent = 'Add New Instructor';
            document.getElementById('submitBtn').textContent = 'Add Instructor';
            document.getElementById('InstructorId').value = '';
            document.getElementById('InstructorId').disabled = false;
            document.getElementById('editInstructorId').value = '';
            
            // Reset all form fields
            document.querySelectorAll('input[type="text"], input[type="email"]').forEach(field => {
                field.value = '';
                field.disabled = false;
            });
            
            document.getElementById('addInstructorModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addInstructorModal').style.display = 'none';
        }

        function editInstructor(instructorId) {
            document.getElementById('action').value = 'edit';
            document.getElementById('modalTitle').textContent = 'Edit Instructor';
            document.getElementById('submitBtn').textContent = 'Update Instructor';
            document.getElementById('InstructorId').disabled = true;
            document.getElementById('editInstructorId').value = instructorId;

            // Fetch instructor data
            fetch('api/instructors.php?InstructorId=' + encodeURIComponent(instructorId))
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error, status = ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success && data.data) {
                        const instructor = data.data;
                        document.getElementById('InstructorId').value = instructor['InstructorId'];
                        document.getElementById('FirstName').value = instructor['FirstName'] || '';
                        document.getElementById('MiddleName').value = instructor['MiddleName'] || '';
                        document.getElementById('LastName').value = instructor['LastName'] || '';
                        document.getElementById('Email').value = instructor['Email'] || '';
                    } else {
                        throw new Error(data.message || 'Failed to load instructor data');
                    }
                })
                .catch(error => {
                    console.error('Error fetching instructor:', error);
                    alert('Error loading instructor data: ' + error.message);
                });

            document.getElementById('addInstructorModal').style.display = 'block';
        }

        function deleteInstructor(instructorId) {
            document.getElementById('deleteInstructorId').value = instructorId;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var instructorModal = document.getElementById('addInstructorModal');
            var deleteModal = document.getElementById('deleteModal');
            if (event.target == instructorModal) {
                instructorModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>