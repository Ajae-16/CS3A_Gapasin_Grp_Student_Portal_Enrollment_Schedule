<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database/db_config.php';

$message = '';
$messageType = '';

// Fetch courses for dropdown
$courses = [];
try {
    $courses = executeQuery("SELECT CourseId, CourseName FROM course_data ORDER BY CourseName ASC") ?: [];
} catch (Exception $e) {
    // Handle error silently for now
}

// Fetch instructors for dropdown
$instructors = [];
try {
    $sql = "SELECT InstructorId, CONCAT(FirstName, ' ', LastName) as FullName FROM instructors ORDER BY LastName ASC, FirstName ASC";
    $instructors = executeQuery($sql) ?: [];
} catch (Exception $e) {
    // Handle error silently for now
}

// Fetch schedules
$schedules = [];
try {
    $sql = "SELECT s.*, c.CourseName, CONCAT(i.FirstName, ' ', i.LastName) as InstructorName
            FROM schedule s
            JOIN course_data c ON s.CourseId = c.CourseId
            JOIN instructors i ON s.InstructorId = i.InstructorId
            ORDER BY s.ScheduleId, s.DayOfWeek, s.StartTime ASC";
    $schedules = executeQuery($sql) ?: [];
} catch (Exception $e) {
    // Handle error silently for now
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    
    // FIX 1: Handle DELETE action FIRST, before validation
    if ($action === 'delete') {
        $scheduleId = isset($_POST['ScheduleId']) ? trim($_POST['ScheduleId']) : '';
        $recordId = isset($_POST['RecordId']) ? trim($_POST['RecordId']) : '';
        
        if (!empty($recordId)) {
            // Delete by auto-increment ID (safer for duplicate schedule codes)
            try {
                $deleteSql = "DELETE FROM schedule WHERE id = ?";
                $result = executeQuery($deleteSql, [$recordId], 'i');

                if ($result !== false) {
                    $message = 'Schedule deleted successfully!';
                    $messageType = 'success';
                    
                    // Refresh schedules list
                    $schedules = executeQuery("SELECT s.*, c.CourseName, CONCAT(i.FirstName, ' ', i.LastName) as InstructorName
                            FROM schedule s
                            JOIN course_data c ON s.CourseId = c.CourseId
                            JOIN instructors i ON s.InstructorId = i.InstructorId
                            ORDER BY s.ScheduleId, s.DayOfWeek, s.StartTime ASC") ?: [];
                } else {
                    $message = 'Failed to delete schedule. Please try again.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        } elseif (!empty($scheduleId)) {
            // Fallback: delete by ScheduleId
            try {
                $deleteSql = "DELETE FROM schedule WHERE ScheduleId = ?";
                $result = executeQuery($deleteSql, [$scheduleId], 's');

                if ($result !== false) {
                    $message = 'Schedule deleted successfully!';
                    $messageType = 'success';
                    
                    // Refresh schedules list
                    $schedules = executeQuery("SELECT s.*, c.CourseName, CONCAT(i.FirstName, ' ', i.LastName) as InstructorName
                            FROM schedule s
                            JOIN course_data c ON s.CourseId = c.CourseId
                            JOIN instructors i ON s.InstructorId = i.InstructorId
                            ORDER BY s.ScheduleId, s.DayOfWeek, s.StartTime ASC") ?: [];
                } else {
                    $message = 'Failed to delete schedule. Please try again.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Error: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    } 
    // Handle ADD and EDIT actions (these require validation)
    else {
        $scheduleId = isset($_POST['ScheduleId']) && !empty(trim($_POST['ScheduleId']))
            ? trim($_POST['ScheduleId'])
            : (isset($_POST['editScheduleId']) ? trim($_POST['editScheduleId']) : '');
        $recordId = isset($_POST['RecordId']) ? trim($_POST['RecordId']) : '';
        $courseId = isset($_POST['CourseId']) ? trim($_POST['CourseId']) : '';
        $instructorId = isset($_POST['InstructorId']) ? trim($_POST['InstructorId']) : '';
        $room = isset($_POST['Room']) ? trim($_POST['Room']) : '';
        $dayOfWeek = isset($_POST['DayOfWeek']) ? trim($_POST['DayOfWeek']) : '';
        $startTime = isset($_POST['StartTime']) ? trim($_POST['StartTime']) : '';
        $endTime = isset($_POST['EndTime']) ? trim($_POST['EndTime']) : '';
        $semester = isset($_POST['Semester']) ? trim($_POST['Semester']) : '';
        $yearLevel = isset($_POST['YearLevel']) ? trim($_POST['YearLevel']) : '';

        // Validate required fields for ADD and EDIT only
        if (empty($scheduleId) || empty($courseId) || empty($instructorId) || empty($room) ||
            empty($dayOfWeek) || empty($startTime) || empty($endTime) || empty($semester) || empty($yearLevel)) {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        } else {
            try {
                if ($action === 'add') {
                    // FIX 2: REMOVED the unique check - allow duplicate ScheduleId
                    // This allows multiple time slots for the same subject
                    
                    // Insert new schedule
                    $insertSql = "INSERT INTO schedule (ScheduleId, CourseId, InstructorId, Room, DayOfWeek, StartTime, EndTime, Semester, YearLevel) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $result = executeQuery($insertSql, [$scheduleId, $courseId, $instructorId, $room, $dayOfWeek, $startTime, $endTime, $semester, $yearLevel], 'sssssssss');

                    if ($result !== false) {
                        $message = 'Schedule added successfully! You can add another time slot with the same Schedule ID for split classes.';
                        $messageType = 'success';
                        
                        // Refresh schedules list
                        $schedules = executeQuery("SELECT s.*, c.CourseName, CONCAT(i.FirstName, ' ', i.LastName) as InstructorName
                                FROM schedule s
                                JOIN course_data c ON s.CourseId = c.CourseId
                                JOIN instructors i ON s.InstructorId = i.InstructorId
                                ORDER BY s.ScheduleId, s.DayOfWeek, s.StartTime ASC") ?: [];
                    } else {
                        $message = 'Failed to add schedule. Please try again.';
                        $messageType = 'error';
                    }
                } elseif ($action === 'edit') {
                    // Update schedule - use RecordId if available, otherwise ScheduleId
                    if (!empty($recordId)) {
                        $updateSql = "UPDATE schedule SET ScheduleId = ?, CourseId = ?, InstructorId = ?, Room = ?, DayOfWeek = ?, StartTime = ?, EndTime = ?, Semester = ?, YearLevel = ? WHERE id = ?";
                        $result = executeQuery($updateSql, [$scheduleId, $courseId, $instructorId, $room, $dayOfWeek, $startTime, $endTime, $semester, $yearLevel, $recordId], 'sssssssssi');
                    } else {
                        $updateSql = "UPDATE schedule SET CourseId = ?, InstructorId = ?, Room = ?, DayOfWeek = ?, StartTime = ?, EndTime = ?, Semester = ?, YearLevel = ? WHERE ScheduleId = ?";
                        $result = executeQuery($updateSql, [$courseId, $instructorId, $room, $dayOfWeek, $startTime, $endTime, $semester, $yearLevel, $scheduleId], 'sssssssss');
                    }

                    if ($result !== false) {
                        $message = 'Schedule updated successfully!';
                        $messageType = 'success';
                        
                        // Refresh schedules list
                        $schedules = executeQuery("SELECT s.*, c.CourseName, CONCAT(i.FirstName, ' ', i.LastName) as InstructorName
                                FROM schedule s
                                JOIN course_data c ON s.CourseId = c.CourseId
                                JOIN instructors i ON s.InstructorId = i.InstructorId
                                ORDER BY s.ScheduleId, s.DayOfWeek, s.StartTime ASC") ?: [];
                    } else {
                        $message = 'Failed to update schedule. Please try again.';
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
    <title>Schedule Management - School Portal MIS</title>
    <link rel="stylesheet" href="css/management.css">
    <style>
        /* Add visual grouping for same schedule IDs */
        .schedule-group-highlight {
            background-color: #fff9e6 !important;
            border-left: 4px solid #ffc107;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box p {
            margin: 5px 0;
            color: #1976d2;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="management-content">
        <div class="management-area">
            <div class="student-management-container">
                <h1>Schedule Management</h1>

                <!-- Information Box -->
                <div class="info-box">
                    <p><strong>💡 Tip:</strong> You can use the same Schedule ID for subjects with multiple time slots!</p>
                    <p><strong>Example:</strong> DCIT26 from 03:00-05:00 in Room 220 and 05:00-07:00 in Room 104 can both use Schedule ID "202530636"</p>
                </div>

                <button class="student-management-btn-add" onclick="openModal()">Add Schedule</button>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <table class="student-management-table">
                    <thead>
                        <tr>
                            <th>Schedule ID</th>
                            <th>Course</th>
                            <th>Instructor</th>
                            <th>Room</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Semester</th>
                            <th>Year Level</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($schedules)): ?>
                            <tr>
                                <td colspan="9" style="text-align: center;">No schedules found.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $prevScheduleId = '';
                            foreach ($schedules as $schedule): 
                                // Highlight rows with same schedule ID
                                $isGrouped = ($prevScheduleId === $schedule['ScheduleId']);
                                $prevScheduleId = $schedule['ScheduleId'];
                                $rowClass = $isGrouped ? 'schedule-group-highlight' : '';
                            ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td><?php echo htmlspecialchars($schedule['ScheduleId']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['CourseName']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['InstructorName']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['Room']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['DayOfWeek']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['StartTime'] . ' - ' . $schedule['EndTime']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['Semester']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['YearLevel']); ?></td>
                                    <td class="student-management-action-cell">
                                        <button class="student-management-btn-edit" onclick="editSchedule('<?php echo htmlspecialchars($schedule['ScheduleId']); ?>', '<?php echo isset($schedule['id']) ? htmlspecialchars($schedule['id']) : ''; ?>')">Edit</button>
                                        <button class="student-management-btn-delete" onclick="deleteSchedule('<?php echo htmlspecialchars($schedule['ScheduleId']); ?>', '<?php echo isset($schedule['id']) ? htmlspecialchars($schedule['id']) : ''; ?>')">Delete</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal for Add/Edit Schedule Form -->
    <div id="addScheduleModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle">Add New Schedule</h2>

            <div class="form-container">
                <form method="POST" action="">
                    <input type="hidden" id="action" name="action" value="add">
                    <input type="hidden" id="editScheduleId" name="editScheduleId" value="">
                    <input type="hidden" id="RecordId" name="RecordId" value="">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="ScheduleId">Schedule ID <span class="required">*</span></label>
                            <input type="text" id="ScheduleId" name="ScheduleId" required>
                            <small style="color: #666;">Can be reused for multiple time slots of the same subject</small>
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
                            <label for="InstructorId">Instructor <span class="required">*</span></label>
                            <select id="InstructorId" name="InstructorId" required>
                                <option value="">Select Instructor</option>
                                <?php foreach ($instructors as $instructor): ?>
                                    <option value="<?php echo htmlspecialchars($instructor['InstructorId']); ?>">
                                        <?php echo htmlspecialchars($instructor['FullName']); ?> (<?php echo htmlspecialchars($instructor['InstructorId']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Room">Room <span class="required">*</span></label>
                            <input type="text" id="Room" name="Room" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="DayOfWeek">Day of Week <span class="required">*</span></label>
                            <select id="DayOfWeek" name="DayOfWeek" required>
                                <option value="">Select Day</option>
                                <option value="Mon">Monday</option>
                                <option value="Tue">Tuesday</option>
                                <option value="Wed">Wednesday</option>
                                <option value="Thu">Thursday</option>
                                <option value="Fri">Friday</option>
                                <option value="Sat">Saturday</option>
                                <option value="Sun">Sunday</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="StartTime">Start Time <span class="required">*</span></label>
                            <input type="time" id="StartTime" name="StartTime" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="EndTime">End Time <span class="required">*</span></label>
                            <input type="time" id="EndTime" name="EndTime" required>
                        </div>
                        <div class="form-group">
                            <label for="Semester">Semester <span class="required">*</span></label>
                            <select id="Semester" name="Semester" required>
                                <option value="">Select Semester</option>
                                <option value="1st Semester">1st Semester</option>
                                <option value="2nd Semester">2nd Semester</option>
                            </select>
                        </div>
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

                    <button type="submit" class="btn-submit" id="submitBtn">Add Schedule</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <h2>Confirm Delete</h2>
            <p style="margin: 20px 0; color: #6b7280;">Are you sure you want to delete this schedule?</p>

            <form method="POST" action="" style="display: inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" id="deleteScheduleId" name="ScheduleId" value="">
                <input type="hidden" id="deleteRecordId" name="RecordId" value="">

                <button type="button" class="btn-edit" style="width: 48%; margin-right: 4%;" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn-delete" style="width: 48%; margin-left: 4%;">Delete</button>
            </form>
        </div>
    </div>

    <script>
        // Modal functions for Add/Edit
        function openModal() {
            document.getElementById('action').value = 'add';
            document.getElementById('modalTitle').textContent = 'Add New Schedule';
            document.getElementById('submitBtn').textContent = 'Add Schedule';
            document.getElementById('ScheduleId').value = '';
            document.getElementById('ScheduleId').disabled = false;
            document.getElementById('editScheduleId').value = '';
            document.getElementById('RecordId').value = '';

            // Reset all form fields
            document.querySelectorAll('input[type="text"], input[type="time"], select').forEach(field => {
                field.value = '';
                field.disabled = false;
            });

            document.getElementById('addScheduleModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('addScheduleModal').style.display = 'none';
        }

        function editSchedule(scheduleId, recordId) {
            document.getElementById('action').value = 'edit';
            document.getElementById('modalTitle').textContent = 'Edit Schedule';
            document.getElementById('submitBtn').textContent = 'Update Schedule';
            document.getElementById('ScheduleId').disabled = false; // Allow changing schedule ID
            document.getElementById('editScheduleId').value = scheduleId;
            document.getElementById('RecordId').value = recordId || '';

            // Fetch schedule data
            let url = 'api/student_schedule.php?ScheduleId=' + encodeURIComponent(scheduleId);
            if (recordId) {
                url += '&RecordId=' + encodeURIComponent(recordId);
            }
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('HTTP error, status = ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    if (data.success && data.data) {
                        const schedule = data.data;
                        document.getElementById('ScheduleId').value = schedule['ScheduleId'];
                        document.getElementById('CourseId').value = schedule['CourseId'] || '';
                        document.getElementById('InstructorId').value = schedule['InstructorId'] || '';
                        document.getElementById('Room').value = schedule['Room'] || '';
                        document.getElementById('DayOfWeek').value = schedule['DayOfWeek'] || '';
                        document.getElementById('StartTime').value = schedule['StartTime'] || '';
                        document.getElementById('EndTime').value = schedule['EndTime'] || '';
                        document.getElementById('Semester').value = schedule['Semester'] || '';
                        document.getElementById('YearLevel').value = schedule['YearLevel'] || '';
                    } else {
                        throw new Error(data.message || 'Failed to load schedule data');
                    }
                })
                .catch(error => {
                    console.error('Error fetching schedule:', error);
                    alert('Error loading schedule data: ' + error.message);
                });

            document.getElementById('addScheduleModal').style.display = 'block';
        }

        function deleteSchedule(scheduleId, recordId) {
            document.getElementById('deleteScheduleId').value = scheduleId;
            document.getElementById('deleteRecordId').value = recordId || '';
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            var scheduleModal = document.getElementById('addScheduleModal');
            var deleteModal = document.getElementById('deleteModal');
            if (event.target == scheduleModal) {
                scheduleModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        }
    </script>
</body>
</html>