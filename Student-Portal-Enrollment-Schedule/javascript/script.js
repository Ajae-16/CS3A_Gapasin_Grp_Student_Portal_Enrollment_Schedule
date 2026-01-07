// Student MIS JavaScript
// Handles UI interactions for student management, enrollment, and courses

// Load API adapter
document.addEventListener('DOMContentLoaded', function() {
    // Initialize when DOM is ready
    initializeStudentMIS();
});

function initializeStudentMIS() {
    // Load student data if on student pages
    if (document.querySelector('.student-dashboard')) {
        loadStudentDashboard();
    }

    // Initialize forms
    initializeStudentForm();
    initializeEnrollmentForm();
    initializeCourseForm();

    // Initialize search functionality
    initializeSearch();
}

// Student Dashboard Functions
function loadStudentDashboard() {
    const studentId = localStorage.getItem('StudentId');
    if (!studentId) {
        window.location.href = 'index.php';
        return;
    }

    // Load student info
    StudentAPI.getById(studentId)
        .then(data => {
            if (data.success) {
                displayStudentInfo(data.data);
            } else {
                APIUtils.showNotification('Failed to load student information', 'error');
            }
        })
        .catch(error => APIUtils.handleError(error, 'Loading student info'));

    // Load enrollments
    EnrollmentAPI.getByStudent(studentId)
        .then(data => {
            if (data.success) {
                displayEnrollments(data.data);
            }
        })
        .catch(error => APIUtils.handleError(error, 'Loading enrollments'));
}

function displayStudentInfo(student) {
            const infoContainer = document.getElementById('student-info');
    if (infoContainer) {
        infoContainer.innerHTML = `
            <h3>${student.FirstName} ${student.LastName}</h3>
            <p><strong>Student ID:</strong> ${student.StudentId}</p>
            <p><strong>Date of Birth:</strong> ${formatDate(student.DateOfBirth)}</p>
            <p><strong>Sex:</strong> ${student.Sex}</p>
            <p><strong>Citizenship:</strong> ${student.Citizenship}</p>
            <p><strong>Contact:</strong> ${student.ContactNumber}</p>
            <p><strong>Street:</strong> ${student.StreetName}</p>
        `;
    }
}

function displayEnrollments(enrollments) {
    const container = document.getElementById('enrollments-list');
    if (container) {
        if (enrollments.length === 0) {
            container.innerHTML = '<p>No enrollments found.</p>';
            return;
        }

        const html = enrollments.map(enrollment => `
            <div class="enrollment-item">
                <h4>${enrollment.CourseName}</h4>
                <p><strong>Year Level:</strong> ${enrollment.YearLevel}</p>
                <p><strong>Semester:</strong> ${enrollment.Semester}</p>
                <p><strong>Units:</strong> ${enrollment.Unit}</p>
            </div>
        `).join('');

        container.innerHTML = html;
    }
}

// Student Form Functions
function initializeStudentForm() {
    const form = document.getElementById('student-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const studentData = {
            StudentId: formData.get('StudentId'),
            FirstName: formData.get('FirstName'),
            MiddleName: formData.get('MiddleName'),
            LastName: formData.get('LastName'),
            DateOfBirth: formData.get('DateOfBirth'),
            Sex: formData.get('Sex'),
            Citizenship: formData.get('Citizenship'),
            StreetName: formData.get('StreetName'),
            ContactNumber: formData.get('ContactNumber'),
            FatherName: formData.get('FatherName'),
            FatherOccupation: formData.get('FatherOccupation'),
            MotherName: formData.get('MotherName'),
            MotherOccupation: formData.get('MotherOccupation')
        };

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        APIUtils.showLoading(submitBtn);

        StudentAPI.create(studentData)
            .then(data => {
                if (data.success) {
                    APIUtils.showNotification('Student created successfully', 'success');
                    form.reset();
                } else {
                    APIUtils.showNotification(data.message, 'error');
                }
            })
            .catch(error => APIUtils.handleError(error, 'Creating student'))
            .finally(() => APIUtils.hideLoading(submitBtn, originalText));
    });
}

// Enrollment Form Functions
function initializeEnrollmentForm() {
    const form = document.getElementById('enrollment-form');
    if (!form) return;

    // Load courses for dropdown
    loadCoursesForEnrollment();

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const enrollmentData = {
            StudentId: formData.get('StudentId'),
            CourseId: formData.get('CourseId'),
            YearLevel: formData.get('YearLevel'),
            Semester: formData.get('Semester')
        };

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        APIUtils.showLoading(submitBtn);

        EnrollmentAPI.create(enrollmentData)
            .then(data => {
                if (data.success) {
                    APIUtils.showNotification('Enrollment created successfully', 'success');
                    form.reset();
                } else {
                    APIUtils.showNotification(data.message, 'error');
                }
            })
            .catch(error => APIUtils.handleError(error, 'Creating enrollment'))
            .finally(() => APIUtils.hideLoading(submitBtn, originalText));
    });
}

function loadCoursesForEnrollment() {
    const courseSelect = document.getElementById('courseId');
    if (!courseSelect) return;

    CourseAPI.getAll()
        .then(data => {
            if (data.success) {
                const options = data.data.map(course =>
                    `<option value="${course.CourseId}">${course.CourseName} (${course.Unit} units)</option>`
                ).join('');
                courseSelect.innerHTML = '<option value="">Select a course</option>' + options;
            }
        })
        .catch(error => APIUtils.handleError(error, 'Loading courses'));
}

// Course Form Functions
function initializeCourseForm() {
    const form = document.getElementById('course-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const courseData = {
            CourseId: formData.get('CourseId'),
            CourseName: formData.get('CourseName'),
            Unit: parseInt(formData.get('Unit'))
        };

        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        APIUtils.showLoading(submitBtn);

        CourseAPI.create(courseData)
            .then(data => {
                if (data.success) {
                    APIUtils.showNotification('Course created successfully', 'success');
                    form.reset();
                } else {
                    APIUtils.showNotification(data.message, 'error');
                }
            })
            .catch(error => APIUtils.handleError(error, 'Creating course'))
            .finally(() => APIUtils.hideLoading(submitBtn, originalText));
    });
}

// Search Functions
function initializeSearch() {
    const studentSearch = document.getElementById('student-search');
    const courseSearch = document.getElementById('course-search');

    if (studentSearch) {
        studentSearch.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 2) {
                searchStudents(query);
            } else if (query.length === 0) {
                loadAllStudents();
            }
        });
    }

    if (courseSearch) {
        courseSearch.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 2) {
                searchCourses(query);
            } else if (query.length === 0) {
                loadAllCourses();
            }
        });
    }
}

function searchStudents(query) {
    const container = document.getElementById('students-list');
    if (!container) return;

    StudentAPI.search(query)
        .then(data => {
            if (data.success) {
                displayStudentsList(data.data);
            }
        })
        .catch(error => APIUtils.handleError(error, 'Searching students'));
}

function loadAllStudents() {
    const container = document.getElementById('students-list');
    if (!container) return;

    StudentAPI.getAll()
        .then(data => {
            if (data.success) {
                displayStudentsList(data.data);
            }
        })
        .catch(error => APIUtils.handleError(error, 'Loading students'));
}

function displayStudentsList(students) {
    const container = document.getElementById('students-list');
    if (!container) return;

    if (students.length === 0) {
        container.innerHTML = '<p>No students found.</p>';
        return;
    }

                const html = students.map(student => `
        <div class="student-item">
            <h4>${student.FirstName} ${student.LastName}</h4>
            <p><strong>ID:</strong> ${student.StudentId}</p>
            <p><strong>Contact:</strong> ${student.ContactNumber}</p>
            <button onclick="viewStudentDetails('${student.StudentId}')">View Details</button>
        </div>
    `).join('');

    container.innerHTML = html;
}

function searchCourses(query) {
    const container = document.getElementById('courses-list');
    if (!container) return;

    CourseAPI.search(query)
        .then(data => {
            if (data.success) {
                displayCoursesList(data.data);
            }
        })
        .catch(error => APIUtils.handleError(error, 'Searching courses'));
}

function loadAllCourses() {
    const container = document.getElementById('courses-list');
    if (!container) return;

    CourseAPI.getAll()
        .then(data => {
            if (data.success) {
                displayCoursesList(data.data);
            }
        })
        .catch(error => APIUtils.handleError(error, 'Loading courses'));
}

function displayCoursesList(courses) {
    const container = document.getElementById('courses-list');
    if (!container) return;

    if (courses.length === 0) {
        container.innerHTML = '<p>No courses found.</p>';
        return;
    }

    const html = courses.map(course => `
        <div class="course-item">
            <h4>${course.CourseName}</h4>
            <p><strong>Code:</strong> ${course.CourseId}</p>
            <p><strong>Units:</strong> ${course.Unit}</p>
        </div>
    `).join('');

    container.innerHTML = html;
}

function viewStudentDetails(studentId) {
    StudentAPI.getById(studentId)
        .then(data => {
            if (data.success) {
                // Show modal or redirect to details page
                alert(`Student Details:\n${JSON.stringify(data.data, null, 2)}`);
            }
        })
        .catch(error => APIUtils.handleError(error, 'Loading student details'));
}

// Utility functions for backward compatibility
function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

// Student login logic using API
const loginBtn = document.getElementById("loginBtn");
if (loginBtn) {
    loginBtn.addEventListener("click", function () {
        const enteredId = document.getElementById("studentId").value.trim();
        const enteredPass = document.getElementById("studentPass").value.trim();
        const errorMessage = document.getElementById("errorMessage");

        errorMessage.style.display = "none";

        if (!enteredId || !enteredPass) {
            errorMessage.textContent = "Please enter both Student Number and Password.";
            errorMessage.style.display = "block";
            return;
        }

        // Disable button during login
        loginBtn.disabled = true;
        loginBtn.textContent = "Signing In...";

        // Make API call to student authentication
        fetch('api/student_auth.php?action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                username: enteredId,
                password: enteredPass
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store minimal data in localStorage for client-side use
                localStorage.setItem("StudentId", enteredId);
                localStorage.setItem("StudentName", data.data.fullName);
                localStorage.setItem("YearLevel", data.data.yearLevel);

                // Redirect to verification page
                window.location.href = "verify_year.php";
            } else {
                errorMessage.textContent = "❌ " + (data.message || "Invalid Student ID or Password. Please try again.");
                errorMessage.style.display = "block";
            }
        })
        .catch(error => {
            console.error('Login error:', error);
            errorMessage.textContent = "❌ An error occurred. Please try again.";
            errorMessage.style.display = "block";
        })
        .finally(() => {
            // Re-enable button
            loginBtn.disabled = false;
            loginBtn.textContent = "Sign In";
        });
    });
}
