-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2025 at 02:54 PM
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
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `job_ID` int(11) NOT NULL,
  `job_Title` varchar(256) NOT NULL,
  `job_Description` text NOT NULL,
  `job_Category` varchar(256) NOT NULL,
  `job_Vacancy` int(50) NOT NULL,
  `job_Offered` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`job_ID`, `job_Title`, `job_Description`, `job_Category`, `job_Vacancy`, `job_Offered`) VALUES
(1, 'Professional Chatgpt User', 'We are seeking an AI Chatbot Specialist with expertise in using ChatGPT to enhance customer interactions, automate workflows, and improve business efficiency. In this role, you will design, implement, and optimize AI-driven conversational experiences, leveraging ChatGPT’s capabilities for various use cases, including customer support, content generation, and data analysis.', 'IT', 3, '2025-01-26 20:46:13'),
(2, 'AI Chatbot Developer', 'We are looking for an AI Chatbot Developer to build and maintain AI-powered chatbots using ChatGPT and other NLP models. Responsibilities include chatbot development, API integration, and prompt optimization.', 'IT', 5, '2025-01-07 08:47:25'),
(3, 'ChatGPT Content Strategist', 'We are seeking a ChatGPT Content Strategist to leverage AI-generated content for marketing campaigns, SEO strategies, and customer engagement. The ideal candidate will have experience in AI-driven content generation.', 'Marketing', 2, '2025-01-15 13:47:03');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`job_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `job_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
