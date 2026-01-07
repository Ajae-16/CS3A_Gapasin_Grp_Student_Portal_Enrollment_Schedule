-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 07, 2026 at 12:27 PM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u130505235_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `counter`
--

CREATE TABLE `counter` (
  `CounterId` int(11) NOT NULL,
  `Type` varchar(50) NOT NULL,
  `CurrentValue` int(11) NOT NULL DEFAULT 0,
  `Date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_data`
--

CREATE TABLE `course_data` (
  `CourseId` varchar(50) NOT NULL,
  `CourseName` varchar(255) NOT NULL,
  `Unit` int(11) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_data`
--

INSERT INTO `course_data` (`CourseId`, `CourseName`, `Unit`, `CreatedAt`, `UpdatedAt`) VALUES
('COSC101', 'CS ELECTIVE 1', 3, '2025-12-29 09:22:07', '2025-12-29 09:22:07'),
('COSC102', 'Data Structures', 4, '2026-01-05 15:01:12', '2026-01-05 15:01:12'),
('COSC201', 'Introduction to Data Structures', 3, '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
('COSC202', 'Database Management Systems', 3, '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
('COSC301', 'Web Development', 3, '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
('COSC302', 'Software Engineering', 3, '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
('COSC303', 'Networks and Security', 3, '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
('COSC75', 'SOFTWARE ENGINEERING II', 3, '2025-12-29 09:20:18', '2026-01-03 01:58:41'),
('COSC80', 'OPERATING SYSTEMS', 3, '2025-12-06 05:33:19', '2025-12-30 03:36:39'),
('COSC85', 'NETWORKS AND COMMUNICATION', 3, '2025-12-29 09:21:47', '2025-12-29 09:21:47'),
('CS200B', 'Thesis', 3, '2026-01-06 06:37:58', '2026-01-06 06:37:58'),
('CS201', 'Web Development', 3, '2026-01-05 15:01:12', '2026-01-05 15:01:12'),
('CS202', 'Database Management System', 3, '2026-01-05 14:22:28', '2026-01-05 14:22:28'),
('DCIT26', 'APPLICATIONS DEVELOPMENT AND EMERGING TECHNOLOGIES', 3, '2025-12-29 09:22:26', '2025-12-29 09:22:26'),
('DCIT65', 'SOCIAL AND PROFESSIONAL ISSUES', 3, '2025-12-29 09:22:49', '2025-12-29 09:22:49'),
('ENG102', 'English Composition II', 3, '2026-01-05 14:22:28', '2026-01-05 14:22:28'),
('MATH102', 'Calculus 2', 3, '2026-01-05 15:01:12', '2026-01-05 15:01:12'),
('MATH3', 'LINEAR ALGEBRA', 3, '2025-12-29 09:20:45', '2025-12-29 09:20:45');

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_data`
--

CREATE TABLE `enrollment_data` (
  `EnrollmentId` int(11) NOT NULL,
  `StudentId` varchar(50) NOT NULL,
  `ProgramId` varchar(50) NOT NULL,
  `YearLevel` varchar(20) NOT NULL,
  `Semester` varchar(20) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `EnrollmentStatus` varchar(20) DEFAULT 'Active' COMMENT 'Active, Dropped, etc',
  `StudentType` varchar(20) DEFAULT 'Regular' COMMENT 'Regular, Irregular',
  `TotalUnits` int(11) DEFAULT 0,
  `TotalFee` decimal(10,2) DEFAULT 0.00,
  `FeePerUnit` decimal(10,2) DEFAULT 500.00,
  `IrregularFee` decimal(10,2) DEFAULT 0.00,
  `MiscFee` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_data`
--

INSERT INTO `enrollment_data` (`EnrollmentId`, `StudentId`, `ProgramId`, `YearLevel`, `Semester`, `CreatedAt`, `UpdatedAt`, `EnrollmentStatus`, `StudentType`, `TotalUnits`, `TotalFee`, `FeePerUnit`, `IrregularFee`, `MiscFee`) VALUES
(2, '202313511', 'BSCS', '1', '1st Semester', '2025-12-06 05:37:40', '2025-12-06 05:37:40', 'Active', 'Regular', 0, 0.00, 500.00, 0.00, 0.00),
(3, '202313162', 'BSCS', '3', '1st Semester', '2025-12-29 09:08:31', '2025-12-29 09:08:31', 'Active', 'Regular', 0, 0.00, 500.00, 0.00, 0.00),
(4, '202313378', 'BSCS', '2', '1st Semester', '2026-01-03 02:43:58', '2026-01-03 02:43:58', 'Active', 'Regular', 0, 0.00, 500.00, 0.00, 0.00),
(5, '202313443', 'BSCS', '4', '1st Semester', '2026-01-03 02:45:25', '2026-01-03 02:45:25', 'Active', 'Regular', 0, 0.00, 500.00, 0.00, 0.00),
(6, '202108292', 'BSCS', '3', '1st Semester', '2026-01-03 02:47:03', '2026-01-03 02:47:03', 'Active', 'Regular', 0, 0.00, 500.00, 0.00, 0.00),
(7, '202313758', 'BSCS', '3', '1st Semester', '2026-01-03 02:48:35', '2026-01-03 02:48:35', 'Active', 'Regular', 0, 0.00, 500.00, 0.00, 0.00),
(8, '202313305', 'BSCS', '3', '1st Semester', '2026-01-03 02:50:11', '2026-01-03 02:50:11', 'Active', 'Regular', 0, 0.00, 500.00, 0.00, 0.00),
(13, '202313162', 'BSCS', '3', '2nd Semester', '2026-01-06 00:57:25', '2026-01-06 00:57:25', 'Active', '0', 27, 14500.00, 500.00, 0.00, 1000.00),
(14, '2021245484515774', 'BSCS', '2', '1st Semester', '2026-01-06 06:25:08', '2026-01-06 06:25:08', 'Active', 'Regular', 0, 0.00, 500.00, 0.00, 0.00),
(15, '202313305', 'BSCS', '3', '2nd Semester', '2026-01-06 06:46:32', '2026-01-06 06:46:32', 'Active', '0', 15, 10500.00, 500.00, 2000.00, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `enrollment_history`
--

CREATE TABLE `enrollment_history` (
  `EnrollmentHistoryId` int(11) NOT NULL,
  `EnrollmentId` int(11) NOT NULL,
  `StudentId` varchar(50) NOT NULL,
  `Semester` varchar(20) NOT NULL,
  `Action` varchar(100) DEFAULT NULL,
  `PreviousStatus` varchar(20) DEFAULT NULL,
  `NewStatus` varchar(20) DEFAULT NULL,
  `Notes` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_configuration`
--

CREATE TABLE `fee_configuration` (
  `FeeConfigId` varchar(50) NOT NULL,
  `FeeType` varchar(50) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `YearLevel` varchar(20) DEFAULT NULL,
  `Semester` varchar(20) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_configuration`
--

INSERT INTO `fee_configuration` (`FeeConfigId`, `FeeType`, `Amount`, `YearLevel`, `Semester`, `Description`, `CreatedAt`, `UpdatedAt`) VALUES
('FEE_IRREGULAR', 'Irregular Student Fee', 2000.00, NULL, NULL, 'Additional fee for irregular students', '2026-01-05 14:12:05', '2026-01-05 14:12:05'),
('FEE_MISC', 'Miscellaneous Fee', 1000.00, NULL, NULL, 'General miscellaneous fee', '2026-01-05 14:12:05', '2026-01-05 14:12:05'),
('FEE_PER_UNIT', 'Per Unit Fee', 500.00, NULL, NULL, 'Fee charged per credit unit', '2026-01-05 14:12:05', '2026-01-05 14:12:05');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `GradeId` varchar(50) NOT NULL,
  `StudentId` varchar(50) NOT NULL,
  `CourseId` varchar(50) NOT NULL,
  `GradeValue` decimal(3,2) NOT NULL,
  `SchoolYear` varchar(20) NOT NULL,
  `Semester` varchar(20) NOT NULL,
  `MakeupGrade` decimal(3,2) DEFAULT NULL,
  `FinalUnits` int(11) DEFAULT NULL,
  `Remarks` varchar(50) DEFAULT NULL,
  `CreatedAt` timestamp NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `grades`
--

INSERT INTO `grades` (`GradeId`, `StudentId`, `CourseId`, `GradeValue`, `SchoolYear`, `Semester`, `MakeupGrade`, `FinalUnits`, `Remarks`, `CreatedAt`, `UpdatedAt`) VALUES
('G-001', '202313511', 'COSC75', 1.75, '2024-2025', '1st Semester', NULL, 3, 'Passed', '2026-01-03 01:49:36', '2026-01-03 01:49:36'),
('G-002', '202313511', 'COSC80', 1.50, '2024-2025', '1st Semester', NULL, 3, 'Passed', '2026-01-03 01:49:36', '2026-01-03 01:49:36'),
('G-003', '202313511', 'MATH3', 2.25, '2024-2025', '1st Semester', NULL, 3, 'Passed', '2026-01-03 01:49:36', '2026-01-03 01:49:36'),
('G-004', '202313511', 'DCIT65', 1.00, '2024-2025', '1st Semester', NULL, 3, 'Passed', '2026-01-03 01:49:36', '2026-01-03 01:49:36'),
('G-005', '202313511', 'COSC101', 2.00, '2024-2025', '1st Semester', NULL, 3, 'Passed', '2026-01-03 01:49:36', '2026-01-03 01:49:36'),
('G-006', '202313511', 'COSC85', 1.75, '2024-2025', '1st Semester', NULL, 3, 'Passed', '2026-01-03 01:49:36', '2026-01-03 01:49:36'),
('G-007', '202313511', 'DCIT26', 1.50, '2024-2025', '1st Semester', NULL, 3, 'Passed', '2026-01-03 01:49:36', '2026-01-03 01:49:36'),
('G-695876040A60B', '202313162', 'COSC101', 1.75, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-03 01:51:00', '2026-01-03 01:51:00'),
('G-6958764BF11D1', '202313162', 'DCIT26', 2.25, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-03 01:52:11', '2026-01-03 01:52:11'),
('G-6958766F08E98', '202313162', 'MATH3', 3.00, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-03 01:52:47', '2026-01-03 01:52:47'),
('G-695876A0721A5', '202313162', 'COSC85', 2.50, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-03 01:53:36', '2026-01-03 01:53:36'),
('G-695876DD26265', '202313162', 'COSC80', 1.25, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-03 01:54:37', '2026-01-03 01:54:37'),
('G-6958770D3FD17', '202313162', 'DCIT65', 2.25, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-03 01:55:25', '2026-01-03 01:55:25'),
('G-695877FE14221', '202313162', 'COSC75', 2.00, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-03 01:59:26', '2026-01-03 01:59:26'),
('G-695CAE398867E', '202313305', 'COSC101', 1.75, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-06 06:39:53', '2026-01-06 06:39:53'),
('G-695CAE80CD449', '202313305', 'MATH3', 3.00, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-06 06:41:04', '2026-01-06 06:41:04'),
('G-695CAEA0A8BD4', '202313305', 'COSC80', 5.00, '2024-2025', '1st Semester', 0.00, 3, 'Failed', '2026-01-06 06:41:36', '2026-01-06 06:41:36'),
('G-695CAF3714704', '202313305', 'DCIT26', 1.00, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-06 06:44:07', '2026-01-06 06:44:07'),
('G-695CAF4A8345A', '202313305', 'CS202', 1.00, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-06 06:44:26', '2026-01-06 06:44:26'),
('G-695CAF73B28AB', '202313305', 'COSC85', 1.00, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-06 06:45:07', '2026-01-06 06:45:07'),
('G-695CAF9F8ED07', '202313305', 'DCIT65', 1.00, '2024-2025', '1st Semester', 0.00, 3, 'Passed', '2026-01-06 06:45:51', '2026-01-06 06:45:51');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `InstructorId` varchar(50) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) NOT NULL,
  `Email` varchar(150) NOT NULL,
  `PhoneNumber` varchar(20) DEFAULT NULL,
  `Department` varchar(100) DEFAULT NULL,
  `HireDate` date DEFAULT NULL,
  `Status` varchar(20) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`InstructorId`, `FirstName`, `MiddleName`, `LastName`, `Email`, `PhoneNumber`, `Department`, `HireDate`, `Status`, `CreatedAt`, `UpdatedAt`) VALUES
('Ins000000', 'Dwight Dominic', '', 'Papa', 'Dwight_Papa@gmail.com', NULL, NULL, NULL, NULL, '2025-12-06 05:33:19', '2026-01-04 08:06:50'),
('Ins000001', 'Armin', '', 'Aragoncillo', 'armin.aragoncillo@sample.com', NULL, NULL, NULL, NULL, '2025-12-30 02:48:15', '2025-12-30 02:48:15'),
('Ins000002', 'Christine Joyce', '', 'Rellosa', 'christinejoyce.rellosa@sample.com', NULL, NULL, NULL, NULL, '2025-12-30 02:49:06', '2025-12-30 02:49:06'),
('Ins000003', 'Patrick Ardel', '', 'Manahan', 'patrickardel.manahan@sample.com', NULL, NULL, NULL, NULL, '2025-12-30 02:49:56', '2025-12-30 02:49:56'),
('Ins000004', 'King David', '', 'Agreda', 'kingdavid.agreda@sample.com', NULL, NULL, NULL, NULL, '2025-12-30 02:50:52', '2025-12-30 02:50:52');

-- --------------------------------------------------------

--
-- Table structure for table `program_data`
--

CREATE TABLE `program_data` (
  `ProgramId` varchar(50) NOT NULL,
  `ProgramName` varchar(255) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_data`
--

INSERT INTO `program_data` (`ProgramId`, `ProgramName`, `CreatedAt`, `UpdatedAt`) VALUES
('BSCS', 'Bachelor of Science in Computer Science', '2025-12-06 05:33:19', '2025-12-06 05:33:19'),
('BSIT', 'BSHM', '2026-01-06 06:36:02', '2026-01-06 06:36:36');

-- --------------------------------------------------------

--
-- Table structure for table `program_subjects`
--

CREATE TABLE `program_subjects` (
  `ProgramSubjectId` int(11) NOT NULL,
  `ProgramId` varchar(50) NOT NULL,
  `CourseId` varchar(50) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_subjects`
--

INSERT INTO `program_subjects` (`ProgramSubjectId`, `ProgramId`, `CourseId`, `CreatedAt`, `UpdatedAt`) VALUES
(1, 'BSCS', 'COSC101', '2026-01-05 21:01:36', '2026-01-05 21:01:36'),
(2, 'BSCS', 'COSC102', '2026-01-05 21:01:36', '2026-01-05 21:01:36'),
(11, 'BSCS', 'COSC201', '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
(12, 'BSCS', 'COSC202', '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
(13, 'BSCS', 'COSC301', '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
(14, 'BSCS', 'COSC302', '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
(15, 'BSCS', 'COSC303', '2026-01-05 21:03:47', '2026-01-05 21:03:47'),
(17, 'BSIT', 'COSC75', '2026-01-06 06:36:36', '2026-01-06 06:36:36');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
  `id` int(11) NOT NULL,
  `ScheduleId` varchar(50) NOT NULL,
  `CourseId` varchar(50) NOT NULL,
  `InstructorId` varchar(50) NOT NULL,
  `Room` varchar(50) NOT NULL,
  `DayOfWeek` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `Semester` varchar(20) NOT NULL,
  `YearLevel` varchar(20) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `ScheduleId`, `CourseId`, `InstructorId`, `Room`, `DayOfWeek`, `StartTime`, `EndTime`, `Semester`, `YearLevel`, `CreatedAt`, `UpdatedAt`) VALUES
(10, '202530635', 'DCIT65', 'Ins000004', '107', 'Mon', '11:00:00', '13:00:00', '1st Semester', '3', '2025-12-30 04:38:10', '2025-12-30 05:00:11'),
(11, '202530636', 'DCIT26', 'Ins000003', '220', 'Mon', '15:00:00', '17:00:00', '1st Semester', '3', '2025-12-30 04:38:57', '2025-12-30 05:00:18'),
(12, '202530636', 'DCIT26', 'Ins000003', '104', 'Mon', '17:00:00', '19:00:00', '1st Semester', '3', '2025-12-30 04:39:25', '2026-01-02 08:46:28'),
(13, '202530631', 'COSC75', 'Ins000001', '109', 'Tue', '07:00:00', '09:00:00', '1st Semester', '3', '2025-12-30 04:40:06', '2025-12-30 04:56:16'),
(14, '202530631', 'COSC75', 'Ins000001', '107', 'Tue', '09:00:00', '11:00:00', '1st Semester', '3', '2025-12-30 04:41:09', '2026-01-02 08:44:03'),
(15, '202530634', 'COSC101', 'Ins000000', '217', 'Tue', '13:00:00', '15:00:00', '1st Semester', '3', '2025-12-30 04:42:02', '2026-01-03 03:11:09'),
(17, '202530630', 'MATH3', 'Ins000002', '220', 'Wed', '11:00:00', '13:00:00', '1st Semester', '3', '2025-12-30 04:43:13', '2025-12-30 04:55:35'),
(18, '202530632', 'COSC80', 'Ins000000', '218', 'Thu', '09:00:00', '11:00:00', '1st Semester', '3', '2025-12-30 04:44:19', '2025-12-30 04:59:15'),
(19, '202530632', 'COSC80', 'Ins000000', '109', 'Thu', '11:00:00', '13:00:00', '1st Semester', '3', '2025-12-30 04:45:04', '2026-01-02 08:50:23'),
(20, '202530633', 'COSC85', 'Ins000000', '218', 'Thu', '15:00:00', '17:00:00', '1st Semester', '3', '2025-12-30 04:46:38', '2025-12-30 04:59:47'),
(24, 'SCH2ND002', 'CS202', 'Ins000000', 'Room 102', 'Mon', '10:00:00', '11:30:00', '2nd Semester', '1', '2026-01-05 14:22:28', '2026-01-05 14:22:28'),
(25, 'SCH2ND002', 'CS202', 'Ins000000', 'Room 102', 'Wed', '10:00:00', '11:30:00', '2nd Semester', '1', '2026-01-05 14:22:28', '2026-01-05 14:22:28'),
(26, 'SCH2ND003', 'COSC75', 'Ins000000', 'Room 103', 'Tue', '08:00:00', '09:30:00', '2nd Semester', '1', '2026-01-05 14:22:28', '2026-01-05 14:22:28'),
(27, 'SCH2ND003', 'COSC75', 'Ins000000', 'Room 103', 'Thu', '08:00:00', '09:30:00', '2nd Semester', '1', '2026-01-05 14:22:28', '2026-01-05 14:22:28'),
(28, 'SCH2ND004', 'MATH103', 'Ins000000', 'Room 104', 'Tue', '10:00:00', '11:30:00', '2nd Semester', '1', '2026-01-05 14:22:28', '2026-01-05 14:22:28'),
(30, 'SCH2ND005', 'ENG102', 'Ins000000', 'Room 105', 'Fri', '08:00:00', '09:30:00', '2nd Semester', '1', '2026-01-05 14:22:28', '2026-01-05 14:22:28'),
(36, 'SCH2ND006', 'COSC102', 'Ins000000', 'Room 104', 'Mon', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:01:12', '2026-01-05 15:01:12'),
(37, 'SCH2ND007', 'COSC102', 'Ins000000', 'Room 104', 'Wed', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:01:12', '2026-01-05 15:01:12'),
(38, 'SCH2ND008', 'CS201', 'Ins000000', 'Room 105', 'Tue', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:01:12', '2026-01-05 15:01:12'),
(41, 'SCH2ND002', 'COSC101', 'Ins000000', 'Room 101', 'Wed', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 15:02:16', '2026-01-05 15:02:16'),
(58, 'SCH2ND001', 'COSC101', '', 'Room 101', 'Mon', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(59, 'SCH2ND002', 'COSC101', '', 'Room 101', 'Wed', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(60, 'SCH2ND003', 'MATH102', '', 'Room 102', 'Tue', '10:00:00', '12:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(61, 'SCH2ND004', 'MATH102', '', 'Room 102', 'Thu', '10:00:00', '12:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(62, 'SCH2ND005', 'ENG102', '', 'Room 103', 'Fri', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(63, 'SCH2ND006', 'COSC102', '', 'Room 104', 'Mon', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(64, 'SCH2ND007', 'COSC102', '', 'Room 104', 'Wed', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(65, 'SCH2ND008', 'CS201', '', 'Room 105', 'Tue', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(66, 'SCH2ND009', 'CS201', '', 'Room 105', 'Thu', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:15:31', '2026-01-05 15:15:31'),
(76, 'SCH2ND001', 'COSC101', '', 'Room 101', 'Mon', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(77, 'SCH2ND002', 'COSC101', '', 'Room 101', 'Wed', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(78, 'SCH2ND003', 'MATH102', '', 'Room 102', 'Tue', '10:00:00', '12:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(79, 'SCH2ND004', 'MATH102', '', 'Room 102', 'Thu', '10:00:00', '12:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(80, 'SCH2ND005', 'ENG102', '', 'Room 103', 'Fri', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(81, 'SCH2ND006', 'COSC102', '', 'Room 104', 'Mon', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(82, 'SCH2ND007', 'COSC102', '', 'Room 104', 'Wed', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(83, 'SCH2ND008', 'CS201', '', 'Room 105', 'Tue', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(84, 'SCH2ND009', 'CS201', '', 'Room 105', 'Thu', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:36:03', '2026-01-05 15:36:03'),
(96, 'SCH2ND003', 'MATH102', 'Ins000000', 'Room 102', 'Tue', '10:00:00', '12:00:00', '2nd Semester', '1', '2026-01-05 15:52:56', '2026-01-05 15:52:56'),
(102, 'SCH2ND009', 'CS201', 'Ins000000', 'Room 105', 'Thu', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:52:56', '2026-01-05 15:52:56'),
(106, 'SCH2ND004', 'MATH102', 'Ins000000', 'Room 102', 'Thu', '10:00:00', '12:00:00', '2nd Semester', '1', '2026-01-05 15:56:33', '2026-01-05 15:56:33'),
(107, 'SCH2ND005', 'ENG102', 'Ins000000', 'Room 103', 'Tue', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:56:33', '2026-01-05 15:56:33'),
(108, 'SCH2ND006', 'ENG102', 'Ins000000', 'Room 103', 'Thu', '13:00:00', '15:00:00', '2nd Semester', '1', '2026-01-05 15:56:33', '2026-01-05 15:56:33'),
(109, 'SCH2ND007', 'COSC102', 'Ins000000', 'Room 104', 'Fri', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 15:56:33', '2026-01-05 15:56:33'),
(110, 'SCH2ND008', 'COSC102', 'Ins000000', 'Room 104', 'Sat', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 15:56:33', '2026-01-05 15:56:33'),
(111, 'SCH2ND009', 'CS201', 'Ins000000', 'Room 105', 'Tue', '10:00:00', '12:00:00', '2nd Semester', '1', '2026-01-05 15:56:33', '2026-01-05 15:56:33'),
(112, 'SCH2ND001', 'COSC101', 'Ins000000', 'Room 101', 'Mon', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 16:11:25', '2026-01-05 16:11:25'),
(114, 'SCH2ND003', 'MATH102', 'Ins000000', 'Room 102', 'Mon', '10:00:00', '12:00:00', '2nd Semester', '1', '2026-01-05 16:11:25', '2026-01-05 16:11:25'),
(119, 'SCH2ND008', 'COSC102', 'Ins000000', 'Room 104', 'Sat', '08:00:00', '10:00:00', '2nd Semester', '1', '2026-01-05 16:11:25', '2026-01-05 16:11:25'),
(121, 'SCH2ND0300', 'COSC301', 'Ins000000', 'Room 301', '', '08:00:00', '09:30:00', '2nd Semester', '3', '2026-01-05 21:01:36', '2026-01-05 21:01:36'),
(123, 'SCH2ND0302', 'COSC302', 'Ins000001', 'Room 302', '', '10:00:00', '11:30:00', '2nd Semester', '3', '2026-01-05 21:01:36', '2026-01-05 21:01:36'),
(125, 'SCH2ND0304', 'COSC303', 'Ins000002', 'Room 303', '', '08:00:00', '09:30:00', '2nd Semester', '3', '2026-01-05 21:01:36', '2026-01-05 21:01:36'),
(128, 'SCH2ND0307', 'COSC201', 'Ins000003', 'Room 201', '', '10:00:00', '11:30:00', '2nd Semester', '3', '2026-01-05 21:01:36', '2026-01-05 21:01:36'),
(129, 'SCH2ND0308', 'COSC202', 'Ins000004', 'Room 202', '', '08:00:00', '09:30:00', '2nd Semester', '3', '2026-01-05 21:01:36', '2026-01-05 21:01:36');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_old_backup`
--

CREATE TABLE `schedule_old_backup` (
  `ScheduleId` varchar(50) NOT NULL,
  `CourseId` varchar(50) NOT NULL,
  `InstructorId` varchar(50) NOT NULL,
  `Room` varchar(50) NOT NULL,
  `DayOfWeek` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `StartTime` time NOT NULL,
  `EndTime` time NOT NULL,
  `Semester` varchar(20) NOT NULL,
  `YearLevel` varchar(20) NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_old_backup`
--

INSERT INTO `schedule_old_backup` (`ScheduleId`, `CourseId`, `InstructorId`, `Room`, `DayOfWeek`, `StartTime`, `EndTime`, `Semester`, `YearLevel`, `CreatedAt`, `UpdatedAt`) VALUES
('202530630', 'MATH3', 'Ins000002', '220', '', '11:00:00', '13:00:00', '1st Semester', '3', '2025-12-30 03:20:21', '2025-12-30 03:20:21'),
('202530631', 'COSC75', 'Ins000001', '107', '', '07:00:00', '09:00:00', '1st Semester', '3', '2025-12-30 03:18:45', '2025-12-30 03:18:45'),
('202530632', 'COSC80', 'Ins000000', '218', '', '09:00:00', '11:00:00', '1st Semester', '3', '2025-12-30 03:21:42', '2025-12-30 03:21:42'),
('202530634', 'COSC101', 'Ins000000', '217', '', '13:00:00', '15:00:00', '1st Semester', '3', '2025-12-30 03:28:05', '2025-12-30 03:28:05'),
('202530635', 'DCIT65', 'Ins000004', '107', '', '11:00:00', '13:00:00', '1st Semester', '3', '2025-12-30 03:23:15', '2025-12-30 03:23:15'),
('202530636', 'DCIT26', 'Ins000003', '220', '', '15:00:00', '17:00:00', '1st Semester', '3', '2025-12-30 03:24:11', '2025-12-30 03:24:11'),
('202530637', 'DCIT26', 'Ins000003', '104', '', '17:00:00', '19:00:00', '1st Semester', '3', '2025-12-30 03:26:08', '2025-12-30 03:26:08');

-- --------------------------------------------------------

--
-- Table structure for table `student_account`
--

CREATE TABLE `student_account` (
  `AccountId` int(11) NOT NULL,
  `StudentId` varchar(50) NOT NULL,
  `FullName` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Role` enum('admin','student') NOT NULL DEFAULT 'student',
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_account`
--

INSERT INTO `student_account` (`AccountId`, `StudentId`, `FullName`, `Password`, `Role`, `CreatedAt`, `UpdatedAt`) VALUES
(4, 'admin', 'Full Admin', '$2y$10$tVczWr2r9IMMFpNMrkt3/OqNFGWX7pe5/3CX.KKDLu1z4XvMiIOwe', 'admin', '2025-12-06 05:33:19', '2025-12-06 05:33:33'),
(5, '202313511', 'Adrian Jae Reyes Antonio', '$2y$10$WvFav74r9FOWnbU/M7WpxuVVKH392VCmyOAcqQ.BnCM5pIaK8BjWC', 'student', '2025-12-06 05:37:40', '2025-12-06 05:37:40'),
(6, '202313162', 'Amiel Ron Perez Gapasin', '$2y$10$QxwonP3Na4VGEa25wQ9/5O6lcbWt7anR7ZsOq4R3JbyXGMqtTPtQi', 'student', '2025-12-29 09:08:31', '2025-12-29 09:08:31'),
(7, '202313378', 'Roxanne Ortinez Ansus', '$2y$10$N6cKriVgIi7Xuubgx.FrQOmNiA3Hs9pNyHMkae8LxDxqEoHtffKtK', 'student', '2026-01-03 02:43:58', '2026-01-03 02:43:58'),
(8, '202313443', 'Katrina Enriquez', '$2y$10$et37vggUDhKP4mK4mQxycOei2k5Kst1cd1lqkaQV941YdbEiTm9Aq', 'student', '2026-01-03 02:45:25', '2026-01-03 02:45:25'),
(9, '202108292', 'John Alex Ornales', '$2y$10$oW4WH0/5pRGTzfFB3T.4JeGJ640yk5VLUvtn/W/YZYrCquPKtL596', 'student', '2026-01-03 02:47:03', '2026-01-03 02:47:03'),
(10, '202313758', 'Joshua Jose', '$2y$10$SPCaIPrxhTR5NeyYJdWOsOnuE0vioontOQtXrXNDHtOj3sZz3/8Su', 'student', '2026-01-03 02:48:35', '2026-01-03 02:48:35'),
(11, '202313305', 'Nieum Kaizer Redondo', '$2y$10$XESC/T9FPFICe00TKYwoBO7ECh7EMOVbviWOprgRq9CKKwY/wbNJu', 'student', '2026-01-03 02:50:11', '2026-01-03 02:50:11'),
(12, '2021245484515774', 'Joshua Jose', '$2y$10$obaBNuXkovLbQcY0MVlpduQaVyQae5MoZUpAu.zuuIyb3cPvQkiTu', 'student', '2026-01-06 06:25:08', '2026-01-06 06:25:08');

-- --------------------------------------------------------

--
-- Table structure for table `student_data`
--

CREATE TABLE `student_data` (
  `StudentId` varchar(50) NOT NULL,
  `FirstName` varchar(100) NOT NULL,
  `MiddleName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) NOT NULL,
  `Major` varchar(255) DEFAULT NULL,
  `DateOfBirth` date NOT NULL,
  `Sex` enum('Male','Female','Other') NOT NULL,
  `Citizenship` varchar(100) NOT NULL,
  `ContactNumber` varchar(20) NOT NULL,
  `StreetName` varchar(255) NOT NULL,
  `Barangay` varchar(255) NOT NULL,
  `Province` varchar(255) NOT NULL,
  `Municipality` varchar(255) NOT NULL,
  `CivilStatus` enum('Single','Married','Widowed','Legally Separated') NOT NULL,
  `Religion` varchar(100) DEFAULT NULL,
  `Email` varchar(255) NOT NULL,
  `GuardianName` varchar(255) DEFAULT NULL,
  `GuardianContact` varchar(20) DEFAULT NULL,
  `FatherName` varchar(255) DEFAULT NULL,
  `FatherOccupation` varchar(255) DEFAULT NULL,
  `MotherName` varchar(255) DEFAULT NULL,
  `MotherOccupation` varchar(255) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_data`
--

INSERT INTO `student_data` (`StudentId`, `FirstName`, `MiddleName`, `LastName`, `Major`, `DateOfBirth`, `Sex`, `Citizenship`, `ContactNumber`, `StreetName`, `Barangay`, `Province`, `Municipality`, `CivilStatus`, `Religion`, `Email`, `GuardianName`, `GuardianContact`, `FatherName`, `FatherOccupation`, `MotherName`, `MotherOccupation`, `CreatedAt`, `UpdatedAt`) VALUES
('202108292', 'John Alex', 'N/A', 'Ornales', 'N/A', '2000-03-03', 'Male', 'Filipino', '09352276839', '126 Street', 'Barangay 3', 'Cavite', 'Cavite City', 'Single', 'Catholic', 'johnornales@sample.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2026-01-03 02:47:03', '2026-01-03 02:47:03'),
('2021245484515774', 'Joshua', 'N/A', 'Jose', 'N/A', '2040-02-25', 'Male', 'filipino', '0926584888888', '1234 street', 'barangay 76', 'cavite', 'cavite city', 'Single', 'catholic', 'joshuajose2025@gmail.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2026-01-06 06:25:08', '2026-01-06 06:25:08'),
('202201234', 'John', NULL, '', NULL, '0000-00-00', 'Male', '', '', '', '', '', '', 'Single', NULL, '', NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-02 10:34:08', '2026-01-02 10:34:08'),
('202313162', 'Amiel Ron', 'Perez', 'Gapasin', 'N/A', '2003-02-25', 'Male', 'Filipino', '09192345678', '123 Street', 'Barangay 22 Leo', 'Cavite', 'Cavite City', 'Single', 'Catholic', 'amiel.ron.gapasin@gmail.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2025-12-29 09:08:31', '2025-12-29 09:08:31'),
('202313305', 'Nieum Kaizer', 'N/A', 'Redondo', 'N/A', '2000-05-05', 'Male', 'Filipino', '09663412908', '128 Street', 'Barangay 5', 'Cavite', 'Cavite City', 'Single', 'Iglesia', 'nieumredondo@sample.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2026-01-03 02:50:11', '2026-01-03 02:50:11'),
('202313378', 'Roxanne', 'Ortinez', 'Ansus', 'N/A', '2000-01-01', 'Female', 'Filipino', '09174829501', '124 Street', 'Barangay 1', 'Cavite', 'Cavite City', 'Single', 'Catholic', 'roxanneansus@sample.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2026-01-03 02:43:58', '2026-01-03 02:43:58'),
('202313443', 'Katrina', 'N/A', 'Enriquez', 'N/A', '2000-02-02', 'Female', 'Filipino', '0928593-142', '125 Street', 'Barangay 2', 'Cavite', 'Cavite City', 'Single', 'Catholic', 'katrinaenriquez@sample.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2026-01-03 02:45:25', '2026-01-03 02:45:25'),
('202313511', 'Adrian Jae', 'Reyes', 'Antonio', 'N/A', '2002-01-16', 'Male', 'Filipino', '09565213552', '596 JN Mateo Street', '10 M Kingfisher', 'Cavite', 'Cavite City', 'Single', 'Cat Holic', 'ajaemontage@gmail.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2025-12-06 05:37:40', '2025-12-06 05:37:40'),
('202313758', 'Joshua', 'N/A', 'Jose', 'N/A', '2000-04-04', 'Male', 'Filipino', '09498105574', '127 Street', 'Barangay 4', 'Cavite', 'Cavite City', 'Married', 'Catholic', 'joshuajose@sample.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2026-01-03 02:48:35', '2026-01-03 02:48:35'),
('admin', 'Admin', NULL, 'User', 'N/A', '1990-01-01', 'Other', 'Filipino', '0000000000', 'N/A', 'N/A', 'N/A', 'N/A', 'Single', 'N/A', 'admin@example.com', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 04:25:21', '2025-12-05 04:25:21');

-- --------------------------------------------------------

--
-- Table structure for table `student_schedule`
--

CREATE TABLE `student_schedule` (
  `StudentScheduleId` varchar(50) NOT NULL,
  `StudentId` varchar(50) NOT NULL,
  `ScheduleId` varchar(50) NOT NULL,
  `Section` varchar(20) DEFAULT NULL,
  `EnrollmentStatus` varchar(20) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `year_level_access_period`
--

CREATE TABLE `year_level_access_period` (
  `YearLevel` varchar(20) NOT NULL,
  `StartDate` date NOT NULL,
  `EndDate` date NOT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `year_level_access_period`
--

INSERT INTO `year_level_access_period` (`YearLevel`, `StartDate`, `EndDate`, `CreatedAt`, `UpdatedAt`) VALUES
('1', '2000-01-01', '2027-01-01', '2025-12-06 01:50:47', '2025-12-06 01:50:47'),
('3', '2025-12-29', '2026-01-07', '2025-12-29 09:14:06', '2025-12-29 09:14:06'),
('4', '1980-01-01', '1981-01-01', '2026-01-06 06:51:49', '2026-01-06 06:51:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `counter`
--
ALTER TABLE `counter`
  ADD PRIMARY KEY (`CounterId`),
  ADD UNIQUE KEY `unique_type_date` (`Type`,`Date`);

--
-- Indexes for table `course_data`
--
ALTER TABLE `course_data`
  ADD PRIMARY KEY (`CourseId`);

--
-- Indexes for table `enrollment_data`
--
ALTER TABLE `enrollment_data`
  ADD PRIMARY KEY (`EnrollmentId`),
  ADD KEY `fk_student_id` (`StudentId`),
  ADD KEY `fk_program_id` (`ProgramId`);

--
-- Indexes for table `enrollment_history`
--
ALTER TABLE `enrollment_history`
  ADD PRIMARY KEY (`EnrollmentHistoryId`);

--
-- Indexes for table `fee_configuration`
--
ALTER TABLE `fee_configuration`
  ADD PRIMARY KEY (`FeeConfigId`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`GradeId`),
  ADD UNIQUE KEY `unique_grade` (`StudentId`,`CourseId`,`SchoolYear`,`Semester`),
  ADD KEY `idx_student` (`StudentId`),
  ADD KEY `idx_course` (`CourseId`),
  ADD KEY `idx_term` (`SchoolYear`,`Semester`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`InstructorId`),
  ADD UNIQUE KEY `unique_email` (`Email`);

--
-- Indexes for table `program_data`
--
ALTER TABLE `program_data`
  ADD PRIMARY KEY (`ProgramId`);

--
-- Indexes for table `program_subjects`
--
ALTER TABLE `program_subjects`
  ADD PRIMARY KEY (`ProgramSubjectId`),
  ADD UNIQUE KEY `unique_program_course_instructor` (`ProgramId`,`CourseId`),
  ADD KEY `fk_program_subjects_program_id` (`ProgramId`),
  ADD KEY `fk_program_subjects_course_id` (`CourseId`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_id` (`ScheduleId`),
  ADD KEY `idx_course_id` (`CourseId`),
  ADD KEY `idx_instructor_id` (`InstructorId`);

--
-- Indexes for table `schedule_old_backup`
--
ALTER TABLE `schedule_old_backup`
  ADD PRIMARY KEY (`ScheduleId`),
  ADD KEY `fk_schedule_course_id` (`CourseId`),
  ADD KEY `fk_schedule_instructor_id` (`InstructorId`);

--
-- Indexes for table `student_account`
--
ALTER TABLE `student_account`
  ADD PRIMARY KEY (`AccountId`),
  ADD UNIQUE KEY `unique_student_id` (`StudentId`),
  ADD KEY `fk_student_account_id` (`StudentId`);

--
-- Indexes for table `student_data`
--
ALTER TABLE `student_data`
  ADD PRIMARY KEY (`StudentId`);

--
-- Indexes for table `student_schedule`
--
ALTER TABLE `student_schedule`
  ADD PRIMARY KEY (`StudentScheduleId`),
  ADD KEY `fk_student_schedule_student_id` (`StudentId`),
  ADD KEY `fk_student_schedule_schedule_id` (`ScheduleId`);

--
-- Indexes for table `year_level_access_period`
--
ALTER TABLE `year_level_access_period`
  ADD PRIMARY KEY (`YearLevel`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `counter`
--
ALTER TABLE `counter`
  MODIFY `CounterId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `enrollment_data`
--
ALTER TABLE `enrollment_data`
  MODIFY `EnrollmentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `enrollment_history`
--
ALTER TABLE `enrollment_history`
  MODIFY `EnrollmentHistoryId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program_subjects`
--
ALTER TABLE `program_subjects`
  MODIFY `ProgramSubjectId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `schedule`
--
ALTER TABLE `schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT for table `student_account`
--
ALTER TABLE `student_account`
  MODIFY `AccountId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollment_data`
--
ALTER TABLE `enrollment_data`
  ADD CONSTRAINT `fk_program_id` FOREIGN KEY (`ProgramId`) REFERENCES `program_data` (`ProgramId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student_id` FOREIGN KEY (`StudentId`) REFERENCES `student_data` (`StudentId`) ON DELETE CASCADE;

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_grades_course` FOREIGN KEY (`CourseId`) REFERENCES `course_data` (`CourseId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grades_student` FOREIGN KEY (`StudentId`) REFERENCES `student_data` (`StudentId`) ON DELETE CASCADE;

--
-- Constraints for table `program_subjects`
--
ALTER TABLE `program_subjects`
  ADD CONSTRAINT `fk_program_subjects_course_id` FOREIGN KEY (`CourseId`) REFERENCES `course_data` (`CourseId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_program_subjects_program_id` FOREIGN KEY (`ProgramId`) REFERENCES `program_data` (`ProgramId`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_old_backup`
--
ALTER TABLE `schedule_old_backup`
  ADD CONSTRAINT `fk_schedule_course_id` FOREIGN KEY (`CourseId`) REFERENCES `course_data` (`CourseId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedule_instructor_id` FOREIGN KEY (`InstructorId`) REFERENCES `instructors` (`InstructorId`) ON DELETE CASCADE;

--
-- Constraints for table `student_account`
--
ALTER TABLE `student_account`
  ADD CONSTRAINT `fk_student_account_id` FOREIGN KEY (`StudentId`) REFERENCES `student_data` (`StudentId`) ON DELETE CASCADE;

--
-- Constraints for table `student_schedule`
--
ALTER TABLE `student_schedule`
  ADD CONSTRAINT `fk_student_schedule_schedule_id` FOREIGN KEY (`ScheduleId`) REFERENCES `schedule_old_backup` (`ScheduleId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student_schedule_student_id` FOREIGN KEY (`StudentId`) REFERENCES `student_data` (`StudentId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
