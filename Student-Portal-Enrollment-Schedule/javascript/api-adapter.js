// API Adapter for Student MIS
// Handles all API calls to the backend

const API_BASE_URL = 'api/';

// Helper function to make API requests
async function apiRequest(endpoint, method = 'GET', data = null) {
    const url = `${API_BASE_URL}${endpoint}`;
    const config = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
    };

    if (data && (method === 'POST' || method === 'PUT')) {
        config.body = JSON.stringify(data);
    }

    try {
        const response = await fetch(url, config);
        const result = await response.json();
        return result;
    } catch (error) {
        console.error('API request failed:', error);
        throw error;
    }
}

// Student Data API functions
const StudentAPI = {
    // Get all students
    getAll: () => apiRequest('student_data.php'),

    // Get student by ID
    getById: (studentId) => apiRequest(`student_data.php?StudentId=${studentId}`),

    // Search students
    search: (query) => apiRequest(`student_data.php?search=${encodeURIComponent(query)}`),

    // Create new student
    create: (studentData) => apiRequest('student_data.php', 'POST', studentData),

    // Update student
    update: (studentData) => apiRequest('student_data.php', 'PUT', studentData),

    // Delete student
    delete: (studentId) => apiRequest('student_data.php', 'DELETE', { StudentId: studentId })
};

// Enrollment Data API functions
const EnrollmentAPI = {
    // Get all enrollments
    getAll: () => apiRequest('enrollment_data.php'),

    // Get enrollment by ID
    getById: (enrollmentId) => apiRequest(`enrollment_data.php?EnrollmentId=${enrollmentId}`),

    // Get enrollments by student
    getByStudent: (studentId) => apiRequest(`enrollment_data.php?StudentId=${studentId}`),

    // Get enrollments by course
    getByCourse: (courseId) => apiRequest(`enrollment_data.php?CourseId=${courseId}`),

    // Create new enrollment
    create: (enrollmentData) => apiRequest('enrollment_data.php', 'POST', enrollmentData),

    // Update enrollment
    update: (enrollmentData) => apiRequest('enrollment_data.php', 'PUT', enrollmentData),

    // Delete enrollment
    delete: (enrollmentId) => apiRequest('enrollment_data.php', 'DELETE', { EnrollmentId: enrollmentId })
};

// Course Data API functions
const CourseAPI = {
    // Get all courses
    getAll: () => apiRequest('course_data.php'),

    // Get course by ID
    getById: (courseId) => apiRequest(`course_data.php?CourseId=${courseId}`),

    // Search courses
    search: (query) => apiRequest(`course_data.php?search=${encodeURIComponent(query)}`),

    // Create new course
    create: (courseData) => apiRequest('course_data.php', 'POST', courseData),

    // Update course
    update: (courseData) => apiRequest('course_data.php', 'PUT', courseData),

    // Delete course
    delete: (courseId) => apiRequest('course_data.php', 'DELETE', { CourseId: courseId })
};

// Auth API functions (assuming auth.php exists)
const AuthAPI = {
    // Login
    login: (credentials) => apiRequest('auth.php', 'POST', credentials),

    // Logout
    logout: () => apiRequest('auth.php', 'POST', { action: 'logout' }),

    // Check session
    checkSession: () => apiRequest('auth.php?check=session')
};

// Utility functions
const APIUtils = {
    // Show loading spinner
    showLoading: (element) => {
        if (element) {
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            element.disabled = true;
        }
    },

    // Hide loading spinner
    hideLoading: (element, originalText) => {
        if (element) {
            element.innerHTML = originalText;
            element.disabled = false;
        }
    },

    // Show notification
    showNotification: (message, type = 'info') => {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    },

    // Handle API errors
    handleError: (error, context = '') => {
        console.error(`${context} Error:`, error);
        APIUtils.showNotification('An error occurred. Please try again.', 'error');
    }
};

// Export for use in other scripts
window.StudentAPI = StudentAPI;
window.EnrollmentAPI = EnrollmentAPI;
window.CourseAPI = CourseAPI;
window.AuthAPI = AuthAPI;
window.APIUtils = APIUtils;
