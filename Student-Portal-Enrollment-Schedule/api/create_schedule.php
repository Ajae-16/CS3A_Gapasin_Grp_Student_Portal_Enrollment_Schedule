<?php
/**
 * Create Schedule API Endpoint
 *
 * Handles creation of year level access periods
 */

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include database configuration
define('DB_ACCESS', true);
require_once '../database/db_config.php';

// Start session for user tracking
session_start();

// Get request method
$method = $_SERVER['REQUEST_METHOD'];

// Only allow POST requests
if ($method !== 'POST') {
    sendJSON(false, null, 'Invalid request method');
}

// Get form data
$action = $_POST['action'] ?? 'create';
$yearLevel = $_POST['YearLevel'] ?? null;
$startDate = $_POST['StartDate'] ?? null;
$endDate = $_POST['EndDate'] ?? null;

// Validate required fields
if (empty($yearLevel) || empty($startDate) || empty($endDate)) {
    sendJSON(false, null, 'Missing required fields: year_level, start_date, end_date');
}

// Validate date format and logic
if (!strtotime($startDate) || !strtotime($endDate)) {
    sendJSON(false, null, 'Invalid date format');
}

if (strtotime($startDate) >= strtotime($endDate)) {
    sendJSON(false, null, 'End date must be after start date');
}

// Handle create or update action
if ($action === 'create') {
    // Check if year level already exists
    $sql = "SELECT YearLevel FROM year_level_access_period WHERE YearLevel = ?";
    $result = executeQuery($sql, [$yearLevel], 's');
    if (!empty($result)) {
        sendJSON(false, null, 'Year level access period already exists for this year level');
    }

    // Insert into database
    $sql = "INSERT INTO year_level_access_period (YearLevel, StartDate, EndDate) VALUES (?, ?, ?)";
    $result = executeQuery($sql, [$yearLevel, $startDate, $endDate], 'sss');

    if ($result !== false) {
        sendJSON(true, ['YearLevel' => $yearLevel], 'Year level access period created successfully');
    } else {
        sendJSON(false, null, 'Failed to create year level access period');
    }
} elseif ($action === 'update') {
    // Update existing record
    $sql = "UPDATE year_level_access_period SET StartDate = ?, EndDate = ? WHERE YearLevel = ?";
    $result = executeQuery($sql, [$startDate, $endDate, $yearLevel], 'sss');

    if ($result !== false) {
        sendJSON(true, ['YearLevel' => $yearLevel], 'Year level access period updated successfully');
    } else {
        sendJSON(false, null, 'Failed to update year level access period');
    }
} else {
    sendJSON(false, null, 'Invalid action specified');
}
?>
