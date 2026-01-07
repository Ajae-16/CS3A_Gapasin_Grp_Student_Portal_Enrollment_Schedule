// Student MIS Authentication Script
// Handles authentication-related UI interactions

// Switch to Register Form
function switchToRegister(event) {
    event.preventDefault();
    const container = document.getElementById('container');
    container.classList.add('active');
}

// Switch to Login Form
function switchToLogin(event) {
    event.preventDefault();
    const container = document.getElementById('container');
    container.classList.remove('active');
}

// Toggle Password Visibility
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = event.target.closest('.toggle-password').querySelector('i');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Show notification
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotification = document.querySelector('.notification');
    if (existingNotification) {
        existingNotification.remove();
    }

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
}

// Form Validation and Submission
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    // Login Form Submission
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';

            // For Student MIS, use local authentication for now
            // TODO: Replace with API call when auth system is implemented
            const studentId = formData.get('username');
            const password = formData.get('password');

            // This section is now handled by script.js for student login
            // Keeping for admin/employee login if needed
            showNotification('Student login should use the main login form.', 'info');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;

            // Uncomment below when API is ready
            /*
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
            */
        });
    }

    // Register Form Submission (for admin/staff registration)
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const password = document.getElementById('register-password').value;
            const confirmPassword = document.getElementById('confirm-password').value;
            const terms = this.querySelector('input[name="terms"]');

            // Client-side validation
            if (password !== confirmPassword) {
                showNotification('Passwords do not match!', 'error');
                return false;
            }

            if (terms && !terms.checked) {
                showNotification('Please accept the Terms & Conditions', 'error');
                return false;
            }

            if (password.length < 6) {
                showNotification('Password must be at least 6 characters long', 'error');
                return false;
            }

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;

            // Disable button and show loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';

            // Uncomment when API is ready
            /*
            fetch('api/auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    setTimeout(() => {
                        switchToLogin(new Event('click'));
                        registerForm.reset();
                    }, 1500);
                } else {
                    showNotification(data.message, 'error');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
            */

            // For now, show success message
            showNotification('Registration feature coming soon!', 'info');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        });
    }

    // Input Focus Animation
    const inputs = document.querySelectorAll('.input-group input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });

    // Check for logout success message
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('logout') === 'success') {
        showNotification('You have been successfully logged out', 'success');
        // Clean URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});
