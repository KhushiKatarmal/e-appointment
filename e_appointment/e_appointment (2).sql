-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2026 at 07:06 AM
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
-- Database: `e_appointment`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_super` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `is_super`) VALUES
(0, 'Khushi Katarmal', '$2y$10$sYY14DWmQVMMVs1yJ5CDkulSzcLjps7qX7NRlYQu19s.ahHrMhtB.', 1);

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Approved','Cancelled','Completed') NOT NULL,
  `created_at` datetime NOT NULL,
  `cancelled_by` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `created_at`, `cancelled_by`) VALUES
(1, 1, 0, '2026-07-02', '12:00:00', 'Cancelled', '2026-07-01 10:50:58', NULL),
(2, 1, 1, '2026-07-08', '09:00:00', 'Cancelled', '2026-07-06 12:02:46', NULL),
(3, 1, 1, '2026-07-09', '12:00:00', 'Cancelled', '2026-07-06 12:03:21', NULL),
(4, 1, 1, '2026-07-07', '10:00:00', 'Cancelled', '2026-07-07 19:30:03', NULL),
(5, 2, 1, '2026-07-08', '11:00:00', 'Pending', '2026-07-07 20:02:50', NULL),
(6, 1, 1, '2026-07-14', '11:00:00', 'Cancelled', '2026-07-13 11:23:03', NULL),
(7, 3, 2, '2026-09-10', '13:00:00', 'Cancelled', '2026-09-08 08:24:22', 'Admin'),
(8, 1, 2, '2026-09-15', '11:00:00', 'Cancelled', '2026-09-13 13:53:50', NULL),
(9, 1, 4, '2026-10-12', '11:00:00', 'Cancelled', '2026-10-03 15:57:03', 'Admin');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `doctor_name` varchar(100) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `experience_years` int(11) NOT NULL DEFAULT 0,
  `phone` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `consultation_fee` decimal(10,0) NOT NULL,
  `status` enum('active','inactive') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `doctor_name`, `specialization`, `experience_years`, `phone`, `email`, `consultation_fee`, `status`) VALUES
(1, 'Suchi Patel', 'Gynecologist', 5, '7896451236', 'suchipatel@gmail.com', 500, 'active'),
(2, 'Satya Sharma', 'Orthopedist', 7, '784512369', 'satyasharma34@gmail.com', 600, 'active'),
(4, 'Krishna  Sharma', 'Cardiologist', 6, '7896451236', 'krishnasharma@gmail.com', 800, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_schedule`
--

CREATE TABLE `doctor_schedule` (
  `schedule_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` varchar(15) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `full_name`, `email`, `password`, `phone`, `created_at`) VALUES
(1, 'khushi bhanushali', 'khushibhanushali51@gmail.com', '$2y$10$e3FAr/LHhCyv/iVibv6jN.dIOpI4tshz/kBLQ3eJc1JxedZfgewya', '9924515185', '2026-06-30 17:58:08'),
(2, 'nisha katarmal', 'nishakatarmal@gmail.com', '$2y$10$NAafYB5Y/9LutLPMzfpNw.qyWP3n64.RfILTJOnp8C9ud3JN.W1La', '785961236', '2026-07-07 19:29:17'),
(3, 'krishna', 'krishna23@gmail.com', '$2y$10$TCClSveeYx7yr.g9SuHK6OPXrbWOJKq3SEHnsqTKcf6aUV0rdPcBq', '789645135', '2026-09-08 08:21:12');

-- --------------------------------------------------------

--
-- Table structure for table `specialization_keywords`
--

CREATE TABLE `specialization_keywords` (
  `keyword_id` int(11) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `keyword` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `specialization_keywords`
--

INSERT INTO `specialization_keywords` (`keyword_id`, `specialization`, `keyword`) VALUES
(6, 'Cardiologist', 'blood pressure'),
(70, 'Cardiologist', 'blood pressure issue'),
(5, 'Cardiologist', 'bp'),
(66, 'Cardiologist', 'breathlessness'),
(3, 'Cardiologist', 'cardiac'),
(2, 'Cardiologist', 'cardio'),
(4, 'Cardiologist', 'chest pain'),
(65, 'Cardiologist', 'fast heartbeat'),
(1, 'Cardiologist', 'heart'),
(64, 'Cardiologist', 'heart flutter'),
(62, 'Cardiologist', 'heart problem'),
(69, 'Cardiologist', 'high blood pressure'),
(68, 'Cardiologist', 'high bp'),
(7, 'Cardiologist', 'hypertension'),
(63, 'Cardiologist', 'pain in chest'),
(67, 'Cardiologist', 'shortness of breath'),
(125, 'Dentist', 'bad tooth'),
(126, 'Dentist', 'broken tooth'),
(50, 'Dentist', 'cavity'),
(49, 'Dentist', 'dental'),
(44, 'Dentist', 'dentist'),
(48, 'Dentist', 'gum pain'),
(122, 'Dentist', 'hurting tooth'),
(123, 'Dentist', 'mouth pain'),
(124, 'Dentist', 'sore gum'),
(46, 'Dentist', 'teeth'),
(127, 'Dentist', 'teeth hurting'),
(47, 'Dentist', 'tooth'),
(121, 'Dentist', 'tooth ache'),
(45, 'Dentist', 'tooth pain'),
(9, 'Dermatologist', 'acne'),
(79, 'Dermatologist', 'dry skin'),
(13, 'Dermatologist', 'eczema'),
(76, 'Dermatologist', 'face acne'),
(72, 'Dermatologist', 'itchy skin'),
(10, 'Dermatologist', 'pimple'),
(11, 'Dermatologist', 'pimples'),
(77, 'Dermatologist', 'pimples on face'),
(14, 'Dermatologist', 'psoriasis'),
(12, 'Dermatologist', 'rash'),
(78, 'Dermatologist', 'rashes'),
(74, 'Dermatologist', 'red spots'),
(8, 'Dermatologist', 'skin'),
(80, 'Dermatologist', 'skin allergy'),
(73, 'Dermatologist', 'skin itching'),
(71, 'Dermatologist', 'skin problem'),
(75, 'Dermatologist', 'spots on skin'),
(115, 'Endocrinologist', 'blood sugar'),
(40, 'Endocrinologist', 'diabetes'),
(116, 'Endocrinologist', 'high sugar'),
(43, 'Endocrinologist', 'hormone'),
(120, 'Endocrinologist', 'hormone imbalance'),
(117, 'Endocrinologist', 'low sugar'),
(41, 'Endocrinologist', 'sugar'),
(114, 'Endocrinologist', 'sugar problem'),
(42, 'Endocrinologist', 'thyroid'),
(118, 'Endocrinologist', 'thyroid problem'),
(119, 'Endocrinologist', 'weight change'),
(128, 'General Physician', 'body pain'),
(53, 'General Physician', 'cold'),
(52, 'General Physician', 'cough'),
(51, 'General Physician', 'fever'),
(54, 'General Physician', 'flu'),
(56, 'General Physician', 'headache'),
(55, 'General Physician', 'infection'),
(131, 'General Physician', 'not feeling well'),
(133, 'General Physician', 'runny nose'),
(132, 'General Physician', 'sore throat'),
(129, 'General Physician', 'stomach pain'),
(130, 'General Physician', 'stomach upset'),
(134, 'General Physician', 'vomiting'),
(113, 'Gynecologist', 'female issue'),
(38, 'Gynecologist', 'fertility'),
(39, 'Gynecologist', 'menopause'),
(37, 'Gynecologist', 'menstrual'),
(107, 'Gynecologist', 'missed period'),
(112, 'Gynecologist', 'monthly cycle'),
(36, 'Gynecologist', 'period pain'),
(108, 'Gynecologist', 'period problem'),
(34, 'Gynecologist', 'pregnancy'),
(111, 'Gynecologist', 'pregnancy test'),
(110, 'Gynecologist', 'pregnant'),
(35, 'Gynecologist', 'prenatal'),
(109, 'Gynecologist', 'trying to conceive'),
(135, 'Neurologist', 'dizziness'),
(140, 'Neurologist', 'fainting'),
(136, 'Neurologist', 'head spinning'),
(58, 'Neurologist', 'headache'),
(57, 'Neurologist', 'migraine'),
(59, 'Neurologist', 'nerve'),
(137, 'Neurologist', 'numbness'),
(60, 'Neurologist', 'seizure'),
(61, 'Neurologist', 'stroke'),
(138, 'Neurologist', 'tingling'),
(139, 'Neurologist', 'weakness'),
(99, 'Ophthalmologist', 'blurred view'),
(31, 'Ophthalmologist', 'blurred vision'),
(32, 'Ophthalmologist', 'cataract'),
(28, 'Ophthalmologist', 'eye'),
(101, 'Ophthalmologist', 'eye pain'),
(102, 'Ophthalmologist', 'eye redness'),
(29, 'Ophthalmologist', 'eyes'),
(33, 'Ophthalmologist', 'glaucoma'),
(104, 'Ophthalmologist', 'poor vision'),
(106, 'Ophthalmologist', 'red eye'),
(100, 'Ophthalmologist', 'trouble seeing'),
(30, 'Ophthalmologist', 'vision'),
(103, 'Ophthalmologist', 'vision problem'),
(105, 'Ophthalmologist', 'watering eyes'),
(85, 'Orthopedic Surgeon', 'ankle pain'),
(84, 'Orthopedic Surgeon', 'arm pain'),
(20, 'Orthopedic Surgeon', 'back pain'),
(15, 'Orthopedic Surgeon', 'bone'),
(82, 'Orthopedic Surgeon', 'bone pain'),
(16, 'Orthopedic Surgeon', 'bones'),
(81, 'Orthopedic Surgeon', 'broken bone'),
(17, 'Orthopedic Surgeon', 'fracture'),
(89, 'Orthopedic Surgeon', 'fractured hand'),
(18, 'Orthopedic Surgeon', 'joint pain'),
(19, 'Orthopedic Surgeon', 'knee'),
(86, 'Orthopedic Surgeon', 'knee pain'),
(83, 'Orthopedic Surgeon', 'leg pain'),
(88, 'Orthopedic Surgeon', 'sprained ankle'),
(87, 'Orthopedic Surgeon', 'wrist pain'),
(22, 'Pediatrician', 'baby'),
(95, 'Pediatrician', 'baby cough'),
(94, 'Pediatrician', 'baby fever'),
(91, 'Pediatrician', 'baby not feeling well'),
(21, 'Pediatrician', 'child'),
(98, 'Pediatrician', 'child checkup'),
(96, 'Pediatrician', 'child cough'),
(92, 'Pediatrician', 'child fever'),
(90, 'Pediatrician', 'child not well'),
(23, 'Pediatrician', 'infant'),
(25, 'Pediatrician', 'kids'),
(93, 'Pediatrician', 'kids fever'),
(26, 'Pediatrician', 'newborn'),
(97, 'Pediatrician', 'newborn care'),
(24, 'Pediatrician', 'toddler'),
(27, 'Pediatrician', 'vaccination');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`);

--
-- Indexes for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email_unique` (`email`),
  ADD UNIQUE KEY `phone_num` (`phone`);

--
-- Indexes for table `specialization_keywords`
--
ALTER TABLE `specialization_keywords`
  ADD PRIMARY KEY (`keyword_id`),
  ADD UNIQUE KEY `uk_specialization_keyword` (`specialization`,`keyword`),
  ADD KEY `idx_keyword` (`keyword`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `specialization_keywords`
--
ALTER TABLE `specialization_keywords`
  MODIFY `keyword_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=142;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`);

--
-- Constraints for table `doctor_schedule`
--
ALTER TABLE `doctor_schedule`
  ADD CONSTRAINT `doctor_schedule_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
