-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 13, 2025 at 10:09 AM
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
-- Database: `company_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`) VALUES
(1, 'DIPLOMA IN BUSINESS ACCOUNTING & FINANCE'),
(2, 'DIPLOMA IN ENTREPRENEURSHIP & MARKETING STRATEGIES'),
(3, 'DIPLOMA IN HUMAN CAPITAL MANAGEMENT'),
(4, 'DIPLOMA APPRENTICESHIP IN HOSPITALITY MANAGEMENT AND OPERATIONS'),
(5, 'DIPLOMA IN APPLICATIONS DEVELOPMENT'),
(6, 'DIPLOMA IN CLOUD AND NETWORKING'),
(7, 'DIPLOMA IN DATA ANALYTICS'),
(8, 'DIGITAL ARTS AND MEDIA'),
(9, 'DIPLOMA IN WEB TECHNOLOGY'),
(10, 'DIPLOMA IN HEALTH SCIENCE (NURSING)'),
(11, 'DIPLOMA IN HEALTH SCIENCE (MIDWIFERY)'),
(12, 'DIPLOMA IN HEALTH SCIENCE (PARAMEDIC)'),
(13, 'DIPLOMA IN HEALTH SCIENCE (CARDIOVASCULAR TECHNOLOGY)'),
(14, 'DIPLOMA IN HEALTH SCIENCE (PUBLIC HEALTH)'),
(15, 'DIPLOMA IN ARCHITECTURE'),
(16, 'DIPLOMA IN INTERIOR DESIGN'),
(17, 'DIPLOMA IN CIVIL ENGINEERING'),
(18, 'DIPLOMA IN ELECTRICAL ENGINEERING'),
(19, 'DIPLOMA IN ELECTRONIC AND COMMUNICATION ENGINEERING'),
(20, 'DIPLOMA IN MECHANICAL ENGINEERING'),
(21, 'DIPLOMA IN PETROLEUM ENGINEERING');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_ID` int(11) NOT NULL,
  `job_Title` varchar(256) NOT NULL,
  `job_Description` text NOT NULL,
  `job_Category_id` int(11) NOT NULL,
  `job_Vacancy` int(50) NOT NULL,
  `job_Created` datetime NOT NULL DEFAULT current_timestamp(),
  `job_Updated` datetime NOT NULL DEFAULT current_timestamp(),
  `application_deadline` datetime DEFAULT NULL,
  `job_location` enum('Brunei Muara','Kuala Belait','Tutong','Temburong') NOT NULL,
  `job_Requirements` text NOT NULL,
  `minimum_salary` int(11) DEFAULT NULL,
  `maximum_salary` int(11) DEFAULT NULL,
  `is_expired` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_ID`, `job_Title`, `job_Description`, `job_Category_id`, `job_Vacancy`, `job_Created`, `job_Updated`, `application_deadline`, `job_location`, `job_Requirements`, `minimum_salary`, `maximum_salary`, `is_expired`) VALUES
(15, 'asd', 'asd', 5, 2, '2025-03-12 21:05:40', '2025-03-12 21:05:40', '2025-04-12 21:05:00', 'Brunei Muara', 'asd', 1000, 2000, 0),
(16, 'asd', 'asd', 5, 2, '2025-03-12 21:50:58', '2025-03-12 21:50:58', '2025-04-12 21:05:00', 'Brunei Muara', 'asd', 1000, 2000, 0),
(17, 'asd', 'asd', 5, 2, '2025-03-12 23:14:26', '2025-03-12 23:14:26', '2025-04-12 21:05:00', 'Brunei Muara', 'asd', 1000, 2000, 0),
(18, '99', '99', 5, 2, '2025-03-13 09:52:31', '2025-03-13 09:52:31', '2025-03-21 09:52:00', 'Brunei Muara', '99', 1000, 1500, 0);

--
-- Triggers `jobs`
--
DELIMITER $$
CREATE TRIGGER `update_is_expired` BEFORE INSERT ON `jobs` FOR EACH ROW BEGIN
    IF NEW.application_deadline < CURDATE() THEN
        SET NEW.is_expired = TRUE;
    ELSE
        SET NEW.is_expired = FALSE;
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `update_is_expired_on_update` BEFORE UPDATE ON `jobs` FOR EACH ROW BEGIN
    IF NEW.application_deadline < CURDATE() THEN
        SET NEW.is_expired = TRUE;
    ELSE
        SET NEW.is_expired = FALSE;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `job_categories`
--

CREATE TABLE `job_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_categories`
--

INSERT INTO `job_categories` (`category_id`, `category_name`) VALUES
(1, 'Accounting & Finance'),
(2, 'Marketing & Sales'),
(3, 'Human Resources & Recruitment'),
(4, 'Hospitality & Tourism'),
(5, 'Software Development'),
(6, 'Cloud Computing & Networking'),
(7, 'Data Science & Analytics'),
(8, 'Graphic Design & Multimedia'),
(9, 'Healthcare & Nursing'),
(10, 'Medical Technology'),
(11, 'Architecture & Interior Design'),
(12, 'Civil Engineering'),
(13, 'Electrical Engineering'),
(14, 'Telecommunications & Electronics'),
(15, 'Mechanical Engineering'),
(16, 'Oil & Gas Industry');

-- --------------------------------------------------------

--
-- Table structure for table `job_category_courses`
--

CREATE TABLE `job_category_courses` (
  `job_category_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_category_courses`
--

INSERT INTO `job_category_courses` (`job_category_id`, `course_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(5, 9),
(6, 6),
(7, 7),
(8, 8),
(9, 10),
(9, 11),
(9, 12),
(9, 14),
(10, 13),
(11, 15),
(11, 16),
(12, 17),
(13, 18),
(14, 19),
(15, 20),
(16, 21);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_ID`),
  ADD KEY `fk_job_category` (`job_Category_id`);

--
-- Indexes for table `job_categories`
--
ALTER TABLE `job_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `job_category_courses`
--
ALTER TABLE `job_category_courses`
  ADD PRIMARY KEY (`job_category_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `job_categories`
--
ALTER TABLE `job_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_job_category` FOREIGN KEY (`job_Category_id`) REFERENCES `job_categories` (`category_id`);

--
-- Constraints for table `job_category_courses`
--
ALTER TABLE `job_category_courses`
  ADD CONSTRAINT `job_category_courses_ibfk_1` FOREIGN KEY (`job_category_id`) REFERENCES `job_categories` (`category_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `job_category_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
