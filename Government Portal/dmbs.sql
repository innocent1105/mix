-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 15, 2025 at 04:08 PM
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
-- Database: `dmbs`
--

-- --------------------------------------------------------

--
-- Table structure for table `certificates`
--

CREATE TABLE `certificates` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `certificate_number` varchar(50) NOT NULL,
  `issued_by` varchar(200) DEFAULT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `certificates`
--

INSERT INTO `certificates` (`id`, `request_id`, `certificate_number`, `issued_by`, `issued_at`) VALUES
(1, 1566346, ' 1349659', 'Innocent Mugwadi', '2025-03-15 13:40:36'),
(2, 1748491, ' 1845859', 'admin', '2025-03-15 15:03:14');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` bigint(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `token_status` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permits`
--

CREATE TABLE `permits` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `permit_number` varchar(50) NOT NULL,
  `issued_by` varchar(200) DEFAULT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permits`
--

INSERT INTO `permits` (`id`, `request_id`, `permit_number`, `issued_by`, `issued_at`) VALUES
(1, 1396781, ' 1655934', 'Innocent Mugwadi', '2025-03-15 12:09:29');

-- --------------------------------------------------------

--
-- Table structure for table `requests`
--

CREATE TABLE `requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_type` enum('permit','certificate') NOT NULL,
  `request_category` enum('business','construction', 'event', 'birth', 'marriage', 'police clearance') NOT NULL,
  `request_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_id` int(11) NOT NULL,
  `request_description` varchar(255) DEFAULT 'No description',
  `request_file` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note_type` enum('permit','certificate') NOT NULL,
  `category` enum('business','construction', 'event', 'birth', 'marriage', 'police clearance') NOT NULL,
  `request_id` int(11) NOT NULL,
  `notification_name` varchar(255) DEFAULT '',
  `notification_description` varchar(255) DEFAULT 'No description',
  `status` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;



CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` varchar (200),
  `comment` varchar (250),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;







--
-- Dumping data for table `requests`
--

INSERT INTO `requests` (`id`, `user_id`, `request_type`, `request_status`, `request_id`, `request_description`, `request_file`, `created_at`, `updated_at`) VALUES
(1, 1069812, 'permit', 'approved', 1396781, '', NULL, '2025-03-15 11:56:56', '2025-03-15 12:09:29'),
(2, 1069812, 'certificate', 'approved', 1566346, '', NULL, '2025-03-15 11:58:43', '2025-03-15 13:40:36'),
(3, 1069812, 'permit', 'pending', 1161794, '', NULL, '2025-03-15 13:39:49', '2025-03-15 13:39:49'),
(4, 1069812, 'permit', 'pending', 1527370, '', NULL, '2025-03-15 13:39:56', '2025-03-15 13:39:56'),
(5, 1069812, 'permit', 'pending', 1450775, '', NULL, '2025-03-15 14:07:29', '2025-03-15 14:07:29'),
(6, 1069812, 'certificate', 'pending', 1828042, '', NULL, '2025-03-15 14:07:42', '2025-03-15 14:07:42'),
(7, 1069812, 'permit', 'pending', 1374533, '', NULL, '2025-03-15 14:31:45', '2025-03-15 14:31:45'),
(8, 1069812, 'permit', 'pending', 1070399, '', NULL, '2025-03-15 14:32:46', '2025-03-15 14:32:46'),
(9, 1069812, 'certificate', 'pending', 1593338, '', NULL, '2025-03-15 14:33:58', '2025-03-15 14:33:58'),
(10, 1850290, 'certificate', 'approved', 1748491, '', NULL, '2025-03-15 15:02:32', '2025-03-15 15:03:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(150) NOT NULL,
  `user_id` bigint(150) NOT NULL,
  `user_name` varchar(250) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `account_type` varchar(150) NOT NULL,
  `phone_number` varchar(150) DEFAULT NULL,
  `province` varchar(150) DEFAULT NULL,
  `address` varchar(70) DEFAULT NULL,
  `date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `user_name`, `password`, `email`, `account_type`, `phone_number`, `province`, `address`, `date`) VALUES
(3, 1830597, 'admin', '$2y$10$GIDRPhitBww16OiTETdpP.cPTVuR1JoeBKhkWG2/E6CY0M/S4R.Yu', 'admin@gmail.com', 'admin', NULL, NULL, NULL, '2025-03-15 14:54:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `certificates`
--
ALTER TABLE `certificates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD UNIQUE KEY `certificate_number` (`certificate_number`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permits`
--
ALTER TABLE `permits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_id` (`request_id`),
  ADD UNIQUE KEY `permit_number` (`permit_number`);

--
-- Indexes for table `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `certificates`
--
ALTER TABLE `certificates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permits`
--
ALTER TABLE `permits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `requests`
--
ALTER TABLE `requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(150) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
