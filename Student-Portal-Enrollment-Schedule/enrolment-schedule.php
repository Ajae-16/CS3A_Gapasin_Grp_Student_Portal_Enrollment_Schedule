<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'database/db_config.php';

$message = '';
$messageType = '';

// Fetch year level access periods
$accessPeriods = [];
try {
    $sql = "SELECT * FROM year_level_access_period ORDER BY YearLevel ASC";
    $accessPeriods = executeQuery($sql) ?: [];
} catch (Exception $e) {
    $message = 'Error fetching access periods: ' . $e->getMessage();
    $messageType = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolment Schedule - School Portal MIS</title>
    <link rel="stylesheet" href="css/management.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="management-content">
        <div class="management-area">
            <div class="enrollment-schedule-container">
                <div class="enrollment-schedule-header">
                    <h1 class="enrollment-schedule-title">Enrolment Schedule</h1>
                    <button id="create-schedule-btn" class="enrollment-refresh-btn">Create Schedule</button>
                </div>

                <?php if ($message): ?>
                    <div class="message <?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <div style="overflow-x: auto;">
                    <table class="enrollment-schedule-table">
                        <thead>
                            <tr>
                                <th>Year Level</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($accessPeriods)): ?>
                                <tr>
                                    <td colspan="4" class="enrollment-no-data">No access periods found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($accessPeriods as $period): ?>
                                    <tr>
                                        <td>
                                            <span class="enrollment-year-badge"><?php echo htmlspecialchars($period['YearLevel']); ?> Year</span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($period['StartDate'])); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($period['EndDate'])); ?></td>
                                        <td>
                                            <button class="enrollment-edit-btn" data-year-level="<?php echo htmlspecialchars($period['YearLevel']); ?>" data-start-date="<?php echo htmlspecialchars($period['StartDate']); ?>" data-end-date="<?php echo htmlspecialchars($period['EndDate']); ?>">Edit</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for Creating Schedule -->
    <div id="createScheduleModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="close">&times;</span>    
                <h2 class="modal-title">Create Year Level Access Period</h2>
                
            </div>
            <div class="modal-body">
                <form id="createScheduleForm">
                    <div class="form-group">
                        <label for="YearLevel">Year Level <span style="color: #ef4444;">*</span></label>
                        <select id="YearLevel" name="YearLevel" required>
                            <option value="">Select Year Level</option>
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="StartDate">Start Date <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="StartDate" name="StartDate" required>
                    </div>
                    <div class="form-group">
                        <label for="EndDate">End Date <span style="color: #ef4444;">*</span></label>
                        <input type="date" id="EndDate" name="EndDate" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelBtn">Cancel</button>
                <button type="button" class="btn-primary" id="saveBtn">Create Schedule</button>
            </div>
        </div>
    </div>

    <script>
        // Modal functionality
        const modal = document.getElementById('createScheduleModal');
        const btn = document.getElementById('create-schedule-btn');
        const span = document.getElementsByClassName('close')[0];
        const cancelBtn = document.getElementById('cancelBtn');
        const saveBtn = document.getElementById('saveBtn');
        const modalTitle = document.querySelector('.modal-title');
        const form = document.getElementById('createScheduleForm');
        let isEditMode = false;
        let currentYearLevel = null;

        // Open modal for create
        btn.onclick = function() {
            isEditMode = false;
            currentYearLevel = null;
            modalTitle.textContent = 'Create Year Level Access Period';
            saveBtn.textContent = 'Create Schedule';
            form.reset();
            modal.style.display = 'block';
        }

        // Open modal for edit
        document.querySelectorAll('.enrollment-edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                isEditMode = true;
                currentYearLevel = this.getAttribute('data-year-level');
                const startDate = this.getAttribute('data-start-date');
                const endDate = this.getAttribute('data-end-date');

                modalTitle.textContent = 'Edit Year Level Access Period';
                saveBtn.textContent = 'Update Schedule';

                // Populate form
                document.getElementById('YearLevel').value = currentYearLevel;
                document.getElementById('StartDate').value = startDate;
                document.getElementById('EndDate').value = endDate;

                modal.style.display = 'block';
            });
        });

        // Close modal
        span.onclick = function() {
            modal.style.display = 'none';
        }

        cancelBtn.onclick = function() {
            modal.style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Save/Update schedule
        saveBtn.onclick = function() {
            const formData = new FormData(form);

            // Validate form
            const yearLevel = formData.get('YearLevel');
            const startDate = formData.get('StartDate');
            const endDate = formData.get('EndDate');

            if (!yearLevel || !startDate || !endDate) {
                alert('Please fill in all required fields.');
                return;
            }

            if (new Date(startDate) >= new Date(endDate)) {
                alert('End date must be after start date.');
                return;
            }

            // Add action to form data
            formData.append('action', isEditMode ? 'update' : 'create');

            // Send data to server
            fetch('api/create_schedule.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(isEditMode ? 'Schedule updated successfully!' : 'Schedule created successfully!');
                    modal.style.display = 'none';
                    form.reset();
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the schedule.');
            });
        }
    </script>
</body>
</html>
