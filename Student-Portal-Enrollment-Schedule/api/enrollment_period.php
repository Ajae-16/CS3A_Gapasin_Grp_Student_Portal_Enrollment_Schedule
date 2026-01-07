<?php
/**
 * Enrollment Period API Endpoint
 *
 * Handles fetching enrollment period for a specific year level
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendJSON(false, null, 'Invalid request method');
}

define('DB_ACCESS', true);
require_once '../database/db_config.php';

$yearLevel = $_GET['YearLevel'] ?? '';

if (empty($yearLevel)) {
    sendJSON(false, null, 'Year level is required');
}

// Sanitize input
$yearLevel = sanitizeInput($yearLevel);

// Get enrollment period from database
$sql = "SELECT * FROM year_level_access_period WHERE YearLevel = ?";
$result = executeQuery($sql, [$yearLevel], 's');

if (empty($result)) {
    sendJSON(false, null, 'Enrollment period not found for this year level');
}

sendJSON(true, $result[0], 'Enrollment period retrieved successfully');
?>
