-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 06, 2025 at 06:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `studentportal_db`
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
('COSC80', 'Operating System', 3, '2025-12-06 05:33:19', '2025-12-06 05:33:19');

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
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollment_data`
--

INSERT INTO `enrollment_data` (`EnrollmentId`, `StudentId`, `ProgramId`, `YearLevel`, `Semester`, `CreatedAt`, `UpdatedAt`) VALUES
(1, '202313511', 'BSCS', '1', '1st Semester', '2025-12-06 05:37:40', '2025-12-06 05:37:40');

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `GradeId` varchar(50) NOT NULL,
  `StudentId` varchar(50) NOT NULL,
  `CourseId` varchar(50) NOT NULL,
  `InstructorId` varchar(50) NOT NULL,
  `Semester` varchar(20) NOT NULL,
  `SchoolYear` varchar(20) NOT NULL,
  `GradeValue` tinyint(4) NOT NULL CHECK (`GradeValue` between 1 and 5),
  `Remarks` varchar(255) DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `UpdatedAt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
('Ins000000', 'Dwight Dominic', 'N/A', 'Papa', 'Dwight_Papa@gmail.com', NULL, NULL, NULL, NULL, '2025-12-06 05:33:19', '2025-12-06 05:33:19');

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
('BSCS', 'Bachelor of Science in Computer Science', '2025-12-06 05:33:19', '2025-12-06 05:33:19');

-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE `schedule` (
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
(1, 'admin', 'Full Admin', '$2y$10$tVczWr2r9IMMFpNMrkt3/OqNFGWX7pe5/3CX.KKDLu1z4XvMiIOwe', 'admin', '2025-12-06 05:33:19', '2025-12-06 05:33:33'),
(3, '202313511', 'Adrian Jae Reyes Antonio', '$2y$10$WvFav74r9FOWnbU/M7WpxuVVKH392VCmyOAcqQ.BnCM5pIaK8BjWC', 'student', '2025-12-06 05:37:40', '2025-12-06 05:37:40');

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
('202313511', 'Adrian Jae', 'Reyes', 'Antonio', 'N/A', '2002-01-16', 'Male', 'Filipino', '09565213552', '596 JN Mateo Street', '10 M Kingfisher', 'Cavite', 'Cavite City', 'Single', 'Cat Holic', 'ajaemontage@gmail.com', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2025-12-06 05:37:40', '2025-12-06 05:37:40'),
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
('1', '2000-01-01', '2027-01-01', '2025-12-06 01:50:47', '2025-12-06 01:50:47');

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
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`GradeId`),
  ADD KEY `fk_grades_student_id` (`StudentId`),
  ADD KEY `fk_grades_course_id` (`CourseId`),
  ADD KEY `fk_grades_instructor_id` (`InstructorId`);

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
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
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
-- Indexes for table `program_subjects`
--
ALTER TABLE `program_subjects`
  ADD PRIMARY KEY (`ProgramSubjectId`),
  ADD UNIQUE KEY `unique_program_course_instructor` (`ProgramId`,`CourseId`),
  ADD KEY `fk_program_subjects_program_id` (`ProgramId`),
  ADD KEY `fk_program_subjects_course_id` (`CourseId`);

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
  MODIFY `EnrollmentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `program_subjects`
--
ALTER TABLE `program_subjects`
  MODIFY `ProgramSubjectId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_account`
--
ALTER TABLE `student_account`
  MODIFY `AccountId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  ADD CONSTRAINT `fk_grades_course_id` FOREIGN KEY (`CourseId`) REFERENCES `course_data` (`CourseId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grades_instructor_id` FOREIGN KEY (`InstructorId`) REFERENCES `instructors` (`InstructorId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_grades_student_id` FOREIGN KEY (`StudentId`) REFERENCES `student_data` (`StudentId`) ON DELETE CASCADE;

--
-- Constraints for table `schedule`
--
ALTER TABLE `schedule`
  ADD CONSTRAINT `fk_schedule_course_id` FOREIGN KEY (`CourseId`) REFERENCES `course_data` (`CourseId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_schedule_instructor_id` FOREIGN KEY (`InstructorId`) REFERENCES `instructors` (`InstructorId`) ON DELETE CASCADE;

--
-- Constraints for table `student_account`
--
ALTER TABLE `student_account`
  ADD CONSTRAINT `fk_student_account_id` FOREIGN KEY (`StudentId`) REFERENCES `student_data` (`StudentId`) ON DELETE CASCADE;

--
-- Constraints for table `program_subjects`
--
ALTER TABLE `program_subjects`
  ADD CONSTRAINT `fk_program_subjects_program_id` FOREIGN KEY (`ProgramId`) REFERENCES `program_data` (`ProgramId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_program_subjects_course_id` FOREIGN KEY (`CourseId`) REFERENCES `course_data` (`CourseId`) ON DELETE CASCADE;
  
--
-- Constraints for table `student_schedule`
--
ALTER TABLE `student_schedule`
  ADD CONSTRAINT `fk_student_schedule_schedule_id` FOREIGN KEY (`ScheduleId`) REFERENCES `schedule` (`ScheduleId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_student_schedule_student_id` FOREIGN KEY (`StudentId`) REFERENCES `student_data` (`StudentId`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
