<?php
header('Content-Type: application/json');
require_once '../database/db_config.php';

try {
    $studentId = isset($_GET['StudentId']) ? trim($_GET['StudentId']) : '';
    
    if (empty($studentId)) {
        echo json_encode(['success' => false, 'message' => 'Student ID is required']);
        exit;
    }
    
    // Get latest enrollment
    $enrollmentSql = "SELECT * FROM enrollment_data WHERE StudentId = ? ORDER BY CreatedAt DESC LIMIT 1";
    $enrollment = executeQuery($enrollmentSql, [$studentId], 's');
    
    if (empty($enrollment)) {
        echo json_encode(['success' => false, 'message' => 'No enrollment found']);
        exit;
    }
    
    $enrollData = $enrollment[0];
    
    // Prepare fee breakdown
    $feeBreakdown = [
        'FeePerUnit' => floatval($enrollData['FeePerUnit'] ?? 500.00),
        'UnitCount' => intval($enrollData['TotalUnits'] ?? 0),
        'UnitsFee' => floatval($enrollData['TotalUnits'] ?? 0) * floatval($enrollData['FeePerUnit'] ?? 500.00),
        'MiscFee' => floatval($enrollData['MiscFee'] ?? 1000.00),
        'IrregularFee' => floatval($enrollData['IrregularFee'] ?? 0.00),
        'TotalFee' => floatval($enrollData['TotalFee'] ?? 0.00)
    ];
    
    $responseData = [
        'StudentId' => $studentId,
        'StudentType' => $enrollData['StudentType'] ?? 'Regular',
        'TotalUnits' => intval($enrollData['TotalUnits'] ?? 0),
        'FeeBreakdown' => $feeBreakdown,
        'EnrollmentStatus' => $enrollData['EnrollmentStatus'] ?? 'Active'
    ];
    
    echo json_encode(['success' => true, 'data' => $responseData]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}