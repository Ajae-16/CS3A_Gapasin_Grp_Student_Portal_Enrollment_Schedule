<?php
/**
 * Fee Calculation Helper Module
 * 
 * Provides functions for calculating enrollment fees
 */

define('DB_ACCESS', true);
require_once 'db_config.php';

class FeeCalculator {
    
    /**
     * Default fee configuration
     */
    const DEFAULT_FEE_PER_UNIT = 500.00;
    const DEFAULT_MISC_FEE = 1000.00;
    const DEFAULT_IRREGULAR_FEE = 2000.00;

    /**
     * Get fee configuration from database
     * @param string $feeType Type of fee (e.g., 'Per Unit Fee')
     * @return float Fee amount
     */
    public static function getFeeAmount($feeType) {
        $sql = "SELECT Amount FROM fee_configuration WHERE FeeType = ?";
        $result = executeQuery($sql, [$feeType], 's');
        
        if (!empty($result)) {
            return (float)$result[0]['Amount'];
        }

        // Return defaults
        switch ($feeType) {
            case 'Per Unit Fee':
                return self::DEFAULT_FEE_PER_UNIT;
            case 'Miscellaneous Fee':
                return self::DEFAULT_MISC_FEE;
            case 'Irregular Student Fee':
                return self::DEFAULT_IRREGULAR_FEE;
            default:
                return 0.00;
        }
    }

    /**
     * Calculate total enrollment fee
     * @param int $totalUnits Total units enrolled
     * @param bool $isIrregular Whether student is irregular
     * @param array $customFees Optional array of custom fees
     * @return array Fee breakdown
     */
    public static function calculateFees($totalUnits, $isIrregular = false, $customFees = []) {
        $feePerUnit = $customFees['feePerUnit'] ?? self::getFeeAmount('Per Unit Fee');
        $miscFee = $customFees['miscFee'] ?? self::getFeeAmount('Miscellaneous Fee');
        $irregularFee = $isIrregular ? ($customFees['irregularFee'] ?? self::getFeeAmount('Irregular Student Fee')) : 0.00;

        $unitsFee = $totalUnits * $feePerUnit;
        $totalFee = $unitsFee + $miscFee + $irregularFee;

        return [
            'feePerUnit' => $feePerUnit,
            'unitCount' => $totalUnits,
            'unitsFee' => $unitsFee,
            'miscFee' => $miscFee,
            'irregularFee' => $irregularFee,
            'totalFee' => $totalFee
        ];
    }

    /**
     * Check if student is irregular based on grades
     * @param string $studentId Student ID
     * @param string $semester Semester to check
     * @return bool True if irregular (has failed subjects)
     */
    public static function isIrregularStudent($studentId, $semester = '1st Semester') {
        $sql = "SELECT COUNT(*) as failedCount FROM grades
                WHERE StudentId = ? AND Semester = ? AND GradeValue > 3.00";
        
        $result = executeQuery($sql, [$studentId, $semester], 'ss');
        
        if (!empty($result) && $result[0]['failedCount'] > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get detailed fee breakdown for student
     * @param string $studentId Student ID
     * @param string $semester Semester
     * @param int $yearLevel Year level
     * @return array Detailed fee breakdown
     */
    public static function getDetailedFeeBreakdown($studentId, $semester, $yearLevel) {
        // Get total units for semester/year level
        $unitsSql = "SELECT COALESCE(SUM(c.Unit), 0) as totalUnits
                    FROM schedule s
                    JOIN course_data c ON s.CourseId = c.CourseId
                    WHERE s.Semester = ? AND s.YearLevel = ?";
        
        $unitsResult = executeQuery($unitsSql, [$semester, $yearLevel], 'ss');
        $totalUnits = $unitsResult[0]['totalUnits'] ?? 0;

        // Check if irregular
        $isIrregular = self::isIrregularStudent($studentId, '1st Semester');

        // Calculate fees
        $feeBreakdown = self::calculateFees($totalUnits, $isIrregular);

        return array_merge($feeBreakdown, [
            'isIrregular' => $isIrregular,
            'studentType' => $isIrregular ? 'Irregular' : 'Regular',
            'semester' => $semester,
            'yearLevel' => $yearLevel
        ]);
    }

    /**
     * Generate fee receipt
     * @param int $enrollmentId Enrollment ID
     * @return array Receipt data
     */
    public static function generateFeeReceipt($enrollmentId) {
        $sql = "SELECT e.*, s.FirstName, s.LastName, s.StudentId, s.Email, p.ProgramName
                FROM enrollment_data e
                JOIN student_data s ON e.StudentId = s.StudentId
                JOIN program_data p ON e.ProgramId = p.ProgramId
                WHERE e.EnrollmentId = ?";
        
        $enrollment = executeQuery($sql, [$enrollmentId], 'i');

        if (empty($enrollment)) {
            return null;
        }

        $data = $enrollment[0];
        
        return [
            'ReceiptId' => 'REC_' . $enrollmentId . '_' . date('YmdHi'),
            'EnrollmentId' => $enrollmentId,
            'StudentId' => $data['StudentId'],
            'StudentName' => $data['FirstName'] . ' ' . $data['LastName'],
            'Program' => $data['ProgramName'],
            'YearLevel' => $data['YearLevel'],
            'Semester' => $data['Semester'],
            'StudentType' => $data['StudentType'],
            'TotalUnits' => $data['TotalUnits'],
            'FeeDetails' => [
                'FeePerUnit' => $data['FeePerUnit'],
                'UnitsFee' => $data['TotalUnits'] * $data['FeePerUnit'],
                'MiscFee' => $data['MiscFee'],
                'IrregularFee' => $data['IrregularFee'],
                'TotalFee' => $data['TotalFee']
            ],
            'EnrollmentDate' => $data['CreatedAt'],
            'Email' => $data['Email']
        ];
    }

    /**
     * Update fee configuration
     * @param string $feeType Fee type
     * @param float $amount New amount
     * @return bool Success
     */
    public static function updateFeeConfiguration($feeType, $amount) {
        $feeConfigId = 'FEE_' . strtoupper(str_replace(' ', '_', $feeType));
        
        $sql = "INSERT INTO fee_configuration (FeeConfigId, FeeType, Amount)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE Amount = ?";
        
        return executeQuery($sql, [$feeConfigId, $feeType, $amount, $amount], 'ssdd');
    }
}
?>
