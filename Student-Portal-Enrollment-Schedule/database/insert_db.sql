SET FOREIGN_KEY_CHECKS = 0;

INSERT INTO `program_data` (`ProgramID`, `ProgramName`, `CreatedAt`, `UpdatedAt`) VALUES
('BSCS', 'Bachelor of Science in Computer Science', '2025-12-06 05:33:19', '2025-12-06 05:33:19');

INSERT INTO `course_data` (`CourseID`, `CourseName`, `Unit`, `CreatedAt`, `UpdatedAt`) VALUES
('COSC80', 'Operating System', 3, '2025-12-06 05:33:19', '2025-12-06 05:33:19');

INSERT INTO `instructors`
(`InstructorID`, `FirstName`, `MiddleName`, `LastName`, `Email`, `PhoneNumber`, `Department`, `HireDate`, `Status`, `CreatedAt`, `UpdatedAt`)
VALUES
('Ins000000', 'Dwight Dominic', 'N/A', 'Papa', 'Dwight_Papa@gmail.com', NULL, NULL, NULL, NULL, '2025-12-06 05:33:19', '2025-12-06 05:33:19');

INSERT INTO `student_data`
(`StudentID`, `FirstName`, `MiddleName`, `LastName`, `Major`, `DateOfBirth`, `Sex`, `Citizenship`, `ContactNumber`,
 `StreetName`, `Barangay`, `Province`, `Municipality`, `CivilStatus`, `Religion`, `Email`,
 `GuardianName`, `GuardianContact`, `FatherName`, `FatherOccupation`, `MotherName`, `MotherOccupation`,
 `CreatedAt`, `UpdatedAt`)
VALUES
('202313511', 'Adrian Jae', 'Reyes', 'Antonio', 'N/A', '2002-01-16', 'Male', 'Filipino', '09565213552',
 '596 JN Mateo Street', '10 M Kingfisher', 'Cavite', 'Cavite City', 'Single', 'Cat Holic', 'ajaemontage@gmail.com',
 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', '2025-12-06 05:37:40', '2025-12-06 05:37:40'),
('admin', 'Admin', NULL, 'User', 'N/A', '1990-01-01', 'Other', 'Filipino', '0000000000',
 'N/A', 'N/A', 'N/A', 'N/A', 'Single', 'N/A', 'admin@example.com',
 NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-05 04:25:21', '2025-12-05 04:25:21');

INSERT INTO `student_account`
(`StudentID`, `FullName`, `Password`, `Role`, `CreatedAt`, `UpdatedAt`)
VALUES
('admin', 'Full Admin', '$2y$10$tVczWr2r9IMMFpNMrkt3/OqNFGWX7pe5/3CX.KKDLu1z4XvMiIOwe', 'admin', '2025-12-06 05:33:19', '2025-12-06 05:33:33'),
('202313511', 'Adrian Jae Reyes Antonio', '$2y$10$WvFav74r9FOWnbU/M7WpxuVVKH392VCmyOAcqQ.BnCM5pIaK8BjWC', 'student', '2025-12-06 05:37:40', '2025-12-06 05:37:40');

INSERT INTO `enrollment_data`
(`StudentID`, `ProgramID`, `YearLevel`, `Semester`, `CreatedAt`, `UpdatedAt`)
VALUES
('202313511', 'BSCS', '1', '1st Semester', '2025-12-06 05:37:40', '2025-12-06 05:37:40');

INSERT INTO `year_level_access_period`
(`YearLevel`, `StartDate`, `EndDate`, `CreatedAt`, `UpdatedAt`)
VALUES
('1', '2000-01-01', '2027-01-01', '2025-12-06 01:50:47', '2025-12-06 01:50:47');

SET FOREIGN_KEY_CHECKS = 1;