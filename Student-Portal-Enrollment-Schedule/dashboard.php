<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

// Redirect non-student users
$userRole = strtolower($_SESSION['userRole'] ?? 'student');
if ($userRole !== 'student') {
    header('Location: student-info.php');
    exit;
}

// User info
$userName = $_SESSION['fullName'] ?? $_SESSION['username'];
$userRole = ucfirst($_SESSION['userRole'] ?? 'User');
$userId   = $_SESSION['userId'] ?? '';
$currentDate = date('l, F j, Y');
$currentTime = date('g:i A');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CvSU Cavite City Campus</title>
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>

<!-- NAVIGATION BAR -->
<div class="nav">
    <nav>
        <img src="images/logo.png" alt="CvSU Logo" class="logo">
        <span>CvSU CCC Student Portal</span>
    </nav>
</div>

<!-- SIDEBAR -->
<?php include 'sidebar.php'; ?>


<!-- MAIN CONTENT -->
<main class="main-content">
    <h2>Dashboard</h2>

    <div class="content-grid">
        <!-- Left Column -->
        <div class="column">
            <div class="card">
                <div id="calendar" class="calendar"></div>
            </div>

            <div class="card">
                <h3>Upcoming Events</h3>
                <table class="event-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Event Name</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Provincial Scholarship</td>
                            <td>Open for all CvSU Students</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Enrollment Reminder</td>
                            <td>Submit forms before deadline</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column -->
        <div class="column">
            <div class="card">
                <h3>Cavite State University Mission</h3>

                <h4>English Version</h4>
                <p>
                    Cavite State University shall provide excellent, equitable, and relevant educational
                    opportunities in the arts, sciences, and technology through quality instruction and
                    responsive research and development activities.
                </p>

                <h4>Tagalog Version</h4>
                <p>
                    Ang Cavite State University ay makapagbigay ng mahusay, pantay at makabuluhang edukasyon
                    sa sining, agham at teknolohiya sa pamamagitan ng may kalidad na pagtuturo.
                </p>

                <h4>Chabacano Version</h4>
                <p>
                    La Universidad de Estado de Cavite mantini un excelente y justo oportunidad para educacion
                    en arte, ciencia y tecnologia.
                </p>
            </div>

            <div class="card">
                <h3>Cavite State University Vision</h3>

                <h4>English Version</h4>
                <p>
                    The premier university in historic Cavite recognized for excellence in the development
                    of globally competitive and morally upright individuals.
                </p>

                <h4>Tagalog Version</h4>
                <p>
                    Ang nangungunang pamantasan sa makasaysayang Kabite na kinikilala sa kahusayan.
                </p>

                <h4>Chabacano Version</h4>
                <p>
                    La primera Universidad historica de Cavite reconocida por su excelencia.
                </p>
            </div>
        </div>
    </div>
</main>

<!-- FOOTER -->
<footer class="footer">
    <span>© 2025 Cavite State University Cavite City Campus | Version 2.0.0</span>
</footer>

<!-- CALENDAR SCRIPT -->
    <script>
        function generateCalendar() {
            const calendar = document.getElementById("calendar");
            const now = new Date();
            const month = now.getMonth();
            const year = now.getFullYear();
        
            const firstDay = new Date(year, month, 1).getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
        
            let html = `<h3>${now.toLocaleString('default', { month: 'long' })} ${year}</h3>`;
            html += "<table><thead><tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr></thead><tbody><tr>";
        
            for (let i = 0; i < firstDay; i++) html += "<td></td>";
        
            for (let day = 1; day <= daysInMonth; day++) {
                const isToday = day === now.getDate();
                html += `<td class="${isToday ? 'today' : ''}">${day}</td>`;
                if ((day + firstDay) % 7 === 0) html += "</tr><tr>";
            }
        
            html += "</tr></tbody></table>";
            calendar.innerHTML = html;
        }
        generateCalendar();
    </script>


</body>
</html>
