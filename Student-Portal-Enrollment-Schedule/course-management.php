<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database/db_config.php';

// Get all courses for display
try {
    $coursesSql = "SELECT * FROM course_data ORDER BY CourseName ASC";
    $courses = executeQuery($coursesSql);
} catch (Exception $e) {
    $courses = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - School Portal MIS</title>
    <link rel="stylesheet" href="css/management.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="management-content">
        <div class="management-area">
            <div class="course-management-container">
                <h1>Course Management</h1>

                <button type="button" class="btn-primary" onclick="openCourseModal()">
                    Add New Course
                </button>

                <div id="message-container"></div>

                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p>Loading courses...</p>
                </div>

                <div class="course-management-table-container" id="course-table-container">
                    <?php if (empty($courses)): ?>
                        <div class="course-management-empty-state">
                            <h3>No courses found</h3>
                            <p>Get started by adding your first course.</p>
                            <button type="button" class="btn-primary" onclick="openCourseModal()">
                                + Add New Course
                            </button>
                        </div>
                    <?php else: ?>
                        <table class="course-management-table">
                            <thead>
                                <tr>
                                    <th>Course ID</th>
                                    <th>Course Name</th>
                                    <th>Units</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="course-table-body">
                                <?php foreach ($courses as $course): ?>
                                    <tr data-course-id="<?php echo htmlspecialchars($course['CourseId']); ?>">
                                        <td><?php echo htmlspecialchars($course['CourseId']); ?></td>
                                        <td><?php echo htmlspecialchars($course['CourseName']); ?></td>
                                        <td><?php echo htmlspecialchars($course['Unit']); ?></td>
                                        <td>
                                            <div class="course-management-actions">
                                                <button type="button" class="btn-secondary" onclick="editCourse('<?php echo htmlspecialchars($course['CourseId']); ?>')">
                                                    Edit
                                                </button>
                                                <button type="button" class="btn-danger" onclick="deleteCourse('<?php echo htmlspecialchars($course['CourseId']); ?>', '<?php echo htmlspecialchars($course['CourseName']); ?>')">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Course Modal -->
    <div id="courseModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span type="button" class="modal-close" onclick="closeCourseModal()">&times;</span>
                <h2 class="modal-title" id="modalTitle">Add New Course</h2>
            </div>
            <div class="modal-body">
                <form id="courseForm">
                    <div class="form-group">
                        <label for="modalCourseId">Course ID <span class="required">*</span></label>
                        <input type="text" id="modalCourseId" name="CourseId" required placeholder="e.g., CS101, MATH201">
                    </div>
                    <div class="form-group">
                        <label for="modalCourseName">Course Name <span class="required">*</span></label>
                        <input type="text" id="modalCourseName" name="CourseName" required placeholder="e.g., Introduction to Computer Science">
                    </div>
                    <div class="form-group">
                        <label for="modalUnit">Units <span class="required">*</span></label>
                        <input type="number" id="modalUnit" name="Unit" required min="1" max="10" placeholder="e.g., 3">
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-cancel" onclick="closeCourseModal()">Cancel</button>
                        <button type="submit" class="btn-save" id="saveButton">Save Course</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="javascript/script.js"></script>
    <script>
        let editingCourseId = null;

        function showMessage(message, type = 'success') {
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = `<div class="message ${type}">${message}</div>`;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 5000);
        }

        function openCourseModal(courseId = null) {
            const modal = document.getElementById('courseModal');
            const modalTitle = document.getElementById('modalTitle');
            const form = document.getElementById('courseForm');
            const saveButton = document.getElementById('saveButton');
            
            // Reset form
            form.reset();
            editingCourseId = courseId;
            
            if (courseId) {
                // Edit mode
                modalTitle.textContent = 'Edit Course';
                saveButton.textContent = 'Update Course';
                
                // Find course data from table row
                const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
                if (row) {
                    const cells = row.querySelectorAll('td');
                    document.getElementById('modalCourseId').value = cells[0].textContent;
                    document.getElementById('modalCourseName').value = cells[1].textContent;
                    document.getElementById('modalUnit').value = cells[2].textContent;
                    
                    // Disable course ID field in edit mode
                    document.getElementById('modalCourseId').readOnly = true;
                }
            } else {
                // Add mode
                modalTitle.textContent = 'Add New Course';
                saveButton.textContent = 'Save Course';
                document.getElementById('modalCourseId').readOnly = false;
            }
            
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }

        function closeCourseModal() {
            const modal = document.getElementById('courseModal');
            modal.classList.remove('show');
            document.body.style.overflow = 'auto';
            editingCourseId = null;
        }

        function editCourse(courseId) {
            openCourseModal(courseId);
        }

        async function deleteCourse(courseId, courseName) {
            if (!confirm(`Are you sure you want to delete the course "${courseName}"?\n\nThis action cannot be undone.`)) {
                return;
            }

            try {
                const response = await fetch('api/course_data.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ CourseId: courseId })
                });

                const result = await response.json();

                if (result.success) {
                    showMessage('Course deleted successfully!', 'success');
                    // Remove row from table
                    const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
                    if (row) {
                        row.remove();
                    }
                    
                    // Check if table is empty
                    const tbody = document.getElementById('course-table-body');
                    if (tbody.children.length === 0) {
                        location.reload(); // Reload to show empty state
                    }
                } else {
                    showMessage(result.message || 'Failed to delete course', 'error');
                }
            } catch (error) {
                console.error('Delete error:', error);
                showMessage('An error occurred while deleting the course', 'error');
            }
        }

        // Handle form submission
        document.getElementById('courseForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const courseData = {
                CourseId: formData.get('CourseId'),
                CourseName: formData.get('CourseName'),
                Unit: parseInt(formData.get('Unit'))
            };

            const method = editingCourseId ? 'PUT' : 'POST';
            const url = 'api/course_data.php';

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(courseData)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(editingCourseId ? 'Course updated successfully!' : 'Course added successfully!', 'success');
                    closeCourseModal();
                    
                    // Reload the page to refresh the table
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showMessage(result.message || 'Failed to save course', 'error');
                }
            } catch (error) {
                console.error('Save error:', error);
                showMessage('An error occurred while saving the course', 'error');
            }
        });

        // Close modal when clicking outside
        document.getElementById('courseModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCourseModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCourseModal();
            }
        });
    </script>
</body>
</html>
