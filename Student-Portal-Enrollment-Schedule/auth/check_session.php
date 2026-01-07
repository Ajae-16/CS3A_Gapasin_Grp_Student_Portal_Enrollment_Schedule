<?php
session_start();

header('Content-Type: application/json');

$timeout_duration = 1800; // 30 minutes
$active = false;

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) <= $timeout_duration) {
        $_SESSION['last_activity'] = time(); // Update last activity
        $active = true;
    } else {
        // Session expired
        session_unset();
        session_destroy();
    }
} else {
    // Not logged in
    session_unset();
    session_destroy();
}

echo json_encode(['active' => $active]);
?>

