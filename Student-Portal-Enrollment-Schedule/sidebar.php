<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information
$userName = $_SESSION['fullName'] ?? $_SESSION['username'] ?? 'User';
$userRole = strtolower($_SESSION['userRole'] ?? 'student'); // Convert to lowercase for consistency
$userInitial = strtoupper(substr($userName, 0, 1));
$userRoleDisplay = ucfirst($userRole); // For display purposes
?>

<style>
    /* TOGGLE BUTTON */
    .toggle-btn {
        background: #006400;
        color: white;
        border: none;
        font-size: 20px;
        cursor: pointer;
        padding: 10px 14px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 10px;
        width: 100%;
    }

    .toggle-btn:hover {
        background: #004d00;
        transform: scale(1.05);
    }

    /* Modern Sidebar Styles */
    .sidebar {
        position: fixed;
        left: 0;
        top: 0;
        height: 100%;
        width: 13%;
        background: linear-gradient(180deg,rgb(231, 234, 238) 0%,rgb(172, 172, 173) 100%);
        padding: 0;
        z-index: 1000;
        box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        overflow-y: auto;
        transition: width 0.3s ease;
    }

    .sidebar-header {
        padding: 15px 20px 12px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 10px;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        color: white;
        text-decoration: none;
    }

    .sidebar-brand img {
        width: 40px;
        height: 40px;
        border-radius: 10px;
    }

    .sidebar-brand-text {
        font-size: 18px;
        font-weight: 700;
    }

    .sidebar-user {
        padding: 0 20px;
        margin-bottom: 10px;
    }

    .user-info {
        background: rgba(255, 255, 255, 0.05);
        padding: 12px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: linear-gradient(135deg,rgb(98, 193, 236),rgb(46, 206, 179));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }

    .user-details {
        flex: 1;
        overflow: hidden;
    }

    .user-name {
        color: rgb(0, 0, 0);
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .user-role {
        color:rgb(22, 26, 32);
        font-size: 12px;
    }

    .sidebar-menu {
        list-style: none;
        padding: 0 10px;
        margin: 0;
    }

    .menu-item {
        margin-bottom: 4px;
    }

    .menu-link {
        display: flex !important;
        flex-direction: row !important;
        align-items: center !important;
        gap: 14px;
        padding: 10px 15px;
        color:rgb(36, 41, 49);
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.3s ease;
        position: relative;
    }

    .menu-link:hover {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .menu-link.active {
        background: linear-gradient(135deg,rgb(224, 226, 235),rgb(33, 163, 72));
        color: white;
    }

    .menu-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 24px;
        background: white;
        border-radius: 0 4px 4px 0;
    }

    .menu-icon {
        width: 20px;
        height: 20px;
        font-size: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .menu-icon i {
        line-height: 1;
    }

    .menu-text {
        font-size: 14px;
        font-weight: 500;
        line-height: 1;
        flex: 1;
    }

    .menu-separator {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 8px 20px;
    }

    /* Scrollbar styling for sidebar */
    .sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }


    /* Responsive: Sidebar expands more and overlay for mobile */
    @media (max-width: 768px) {
        .sidebar {
            width: 80vw;
            max-width: 340px;
            min-width: 220px;
            left: -100vw;
            transition: left 0.3s cubic-bezier(0.4,0,0.2,1);
            z-index: 1200;
        }
        .sidebar.mobile-open {
            left: 0;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.3);
            z-index: 1199;
            transition: opacity 0.3s;
        }
        .sidebar.mobile-open ~ .sidebar-overlay {
            display: block;
        }
        .main-content, .content-area, .management-content, .management-area {
            margin-left: 0 !important;
        }
    }

    /* Collapsed sidebar */
    .sidebar.collapsed {
        width: 60px;
    }

    .sidebar.collapsed .sidebar-header,
    .sidebar.collapsed .sidebar-user,
    .sidebar.collapsed .menu-text {
        display: none;
    }

    .sidebar.collapsed .menu-link {
        justify-content: center;
        padding: 10px;
    }

    .sidebar.collapsed .menu-icon {
        width: 24px;
        height: 24px;
        font-size: 20px;
    }

    /* Main content and nav adjustments */
    body.sidebar-collapsed .main-content,
    body.sidebar-collapsed .content-area,
    body.sidebar-collapsed .management-content,
    body.sidebar-collapsed .management-area {
        margin-left: 60px;
    }

    body.sidebar-collapsed .nav {
        left: 60px;
    }

    .main-content,
    .content-area {
        margin-left: 13%;
        transition: margin-left 0.3s ease;
    }

    .management-content,
    .management-area{
        margin-left: 8%;
        transition: margin-left 0.3s ease;
    }

    .nav {
        position: relative;
        left: 13%;
        transition: left 0.3s ease;
    }
</style>

<!-- Modern Sidebar -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-user">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo $userInitial; ?>
            </div>
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                <div class="user-role"><?php echo htmlspecialchars($userRoleDisplay); ?></div>
            </div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-item">
            <div id="toggle-btn" class="menu-link">
                <span class="menu-icon">☰</span>
                <span class="menu-text">Menu</span>
            </div> 
        </li>
        
        <?php if ($userRole === 'student'): ?>
            <!-- STUDENT MENU -->
            <li class="menu-item">
                <a href="dashboard.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">🏠</span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="student-info.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'student-info.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">👤</span>
                    <span class="menu-text">Student Information</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="schedule.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'schedule.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">📅</span>
                    <span class="menu-text">Schedule</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="grades.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'grades.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">📊</span>
                    <span class="menu-text">Grades</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="registration-form.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'registration-form.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">📝</span>
                    <span class="menu-text">Registration Form</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="enroll-semester-2.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'enroll-semester-2.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">✏️</span>
                    <span class="menu-text">2nd Semester Enrollment</span>
                </a>
            </li>
        <?php else: ?>
            <!-- ADMIN MENU -->
            <li class="menu-item">
                <a href="student-info.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'student-info.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">👤</span>
                    <span class="menu-text">Admin Information</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="student-management.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'student-management.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">👨‍🎓</span>
                    <span class="menu-text">Student Management</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="schedule-management.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'schedule-management.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">📅</span>
                    <span class="menu-text">Schedule Management</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="program-management.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'program-management.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">🎓</span>
                    <span class="menu-text">Program Management</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="course-management.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'course-management.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">📚</span>
                    <span class="menu-text">Course Management</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="grade-management.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'grade-management.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">✏️</span>
                    <span class="menu-text">Grades Management</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="enrolment-schedule.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'enrolment-schedule.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">📋</span>
                    <span class="menu-text">Enrolment Schedule</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="instructor-management.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'instructor-management.php' ? 'active' : ''; ?>">
                    <span class="menu-icon">👨‍🏫</span>
                    <span class="menu-text">Instructor Management</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- Exit menu item for all users -->
        <li class="menu-separator"></li>
        <li class="menu-item">
            <a href="index.php" class="menu-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                <span class="menu-icon">🚪</span>
                <span class="menu-text">Exit</span>
            </a>
        </li>
    </ul>
</aside>
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
    // Active menu item based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.menu-link');
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage || (currentPage === '' && href === 'dashboard.php')) {
            link.classList.add('active');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-btn');
        const overlay = document.getElementById('sidebar-overlay');
        const mqMobile = window.matchMedia('(max-width: 768px)');

        // Always close sidebar on page load (for navigation)
        function closeSidebarMobile() {
            sidebar.classList.remove('mobile-open');
            if (overlay) overlay.style.display = 'none';
        }
        function openSidebarMobile() {
            sidebar.classList.add('mobile-open');
            if (overlay) overlay.style.display = 'block';
        }

        function closeSidebarDesktop() {
            sidebar.classList.remove('collapsed');
            document.body.classList.remove('sidebar-collapsed');
        }

        // Responsive toggle
        function handleToggle() {
            if (mqMobile.matches) {
                if (sidebar.classList.contains('mobile-open')) {
                    closeSidebarMobile();
                } else {
                    openSidebarMobile();
                }
            } else {
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
            }
        }

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', handleToggle);
        }
        if (overlay) {
            overlay.addEventListener('click', closeSidebarMobile);
        }

        // On page load, always close sidebar on mobile
        if (mqMobile.matches) {
            closeSidebarMobile();
        } else {
            closeSidebarDesktop();
        }

        // On resize, reset sidebar state
        window.addEventListener('resize', () => {
            if (mqMobile.matches) {
                closeSidebarDesktop();
                closeSidebarMobile();
            } else {
                closeSidebarMobile();
                closeSidebarDesktop();
            }
        });
    });
</script>