-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2026 at 11:32 AM
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
-- Database: `solidwaste_scheduling`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

CREATE TABLE `announcement` (
  `announcement_id` int(11) NOT NULL,
  `lgu_admin_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `content` text NOT NULL,
  `published_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `barangay_admin`
--

CREATE TABLE `barangay_admin` (
  `barangay_admin_id` int(11) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_admin`
--

INSERT INTO `barangay_admin` (`barangay_admin_id`, `firstname`, `lastname`, `email`, `password`) VALUES
(1, 'Joshua', 'Urot', 'jadeegonzaga.patilan@my.smciligan.edu.ph', 'C23-1106');

-- --------------------------------------------------------

--
-- Table structure for table `collection_schedule`
--

CREATE TABLE `collection_schedule` (
  `collection_schedule_id` int(11) NOT NULL,
  `waste_category_id` int(11) DEFAULT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `schedule_status` varchar(50) DEFAULT NULL,
  `barangay_admin_id` int(11) DEFAULT NULL,
  `garbage_collector_id` int(11) DEFAULT NULL,
  `route_id` int(11) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_report`
--

CREATE TABLE `feedback_report` (
  `feedback_report_id` int(11) NOT NULL,
  `lgu_admin_id` int(11) DEFAULT NULL,
  `resident_id` int(11) DEFAULT NULL,
  `report_type` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `garbage_collector`
--

CREATE TABLE `garbage_collector` (
  `garbage_collector_id` int(11) NOT NULL,
  `route_id` int(11) DEFAULT NULL,
  `truck_id` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lgu_admin`
--

CREATE TABLE `lgu_admin` (
  `lgu_admin_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lgu_admin`
--

INSERT INTO `lgu_admin` (`lgu_admin_id`, `email`, `password`, `first_name`, `last_name`) VALUES
(1, 'jadeegonzaga.patilan@my.smciligan.edu.ph', 'admin123', 'Joshua', 'Urot');

-- --------------------------------------------------------

--
-- Table structure for table `location_history`
--

CREATE TABLE `location_history` (
  `location_history_id` int(11) NOT NULL,
  `garbage_collector_id` int(11) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `lgu_admin_id` int(11) DEFAULT NULL,
  `report_type` varchar(100) DEFAULT NULL,
  `generated_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resident`
--

CREATE TABLE `resident` (
  `resident_id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `home_address` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `route`
--

CREATE TABLE `route` (
  `route_id` int(11) NOT NULL,
  `path_coordinates` text DEFAULT NULL,
  `barangay_admin_id` int(11) DEFAULT NULL,
  `zone_name` varchar(50) DEFAULT NULL,
  `collection_days` varchar(100) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `truck`
--

CREATE TABLE `truck` (
  `truck_id` int(11) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `max_capacity_kg` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `waste_category`
--

CREATE TABLE `waste_category` (
  `waste_category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `disposal_guidelines` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waste_category`
--

INSERT INTO `waste_category` (`waste_category_id`, `category_name`, `disposal_guidelines`) VALUES
(2, 'Biodegradable', 'was');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `lgu_admin_id` (`lgu_admin_id`);

--
-- Indexes for table `barangay_admin`
--
ALTER TABLE `barangay_admin`
  ADD PRIMARY KEY (`barangay_admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `collection_schedule`
--
ALTER TABLE `collection_schedule`
  ADD PRIMARY KEY (`collection_schedule_id`),
  ADD KEY `waste_category_id` (`waste_category_id`),
  ADD KEY `fk_collection_schedule_barangay_admin` (`barangay_admin_id`);

--
-- Indexes for table `feedback_report`
--
ALTER TABLE `feedback_report`
  ADD PRIMARY KEY (`feedback_report_id`),
  ADD KEY `lgu_admin_id` (`lgu_admin_id`),
  ADD KEY `resident_id` (`resident_id`);

--
-- Indexes for table `garbage_collector`
--
ALTER TABLE `garbage_collector`
  ADD PRIMARY KEY (`garbage_collector_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `route_id` (`route_id`),
  ADD KEY `truck_id` (`truck_id`);

--
-- Indexes for table `lgu_admin`
--
ALTER TABLE `lgu_admin`
  ADD PRIMARY KEY (`lgu_admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `location_history`
--
ALTER TABLE `location_history`
  ADD PRIMARY KEY (`location_history_id`),
  ADD KEY `garbage_collector_id` (`garbage_collector_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `lgu_admin_id` (`lgu_admin_id`);

--
-- Indexes for table `resident`
--
ALTER TABLE `resident`
  ADD PRIMARY KEY (`resident_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `route`
--
ALTER TABLE `route`
  ADD PRIMARY KEY (`route_id`),
  ADD KEY `fk_route_barangay_admin` (`barangay_admin_id`);

--
-- Indexes for table `truck`
--
ALTER TABLE `truck`
  ADD PRIMARY KEY (`truck_id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- Indexes for table `waste_category`
--
ALTER TABLE `waste_category`
  ADD PRIMARY KEY (`waste_category_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcement`
--
ALTER TABLE `announcement`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `barangay_admin`
--
ALTER TABLE `barangay_admin`
  MODIFY `barangay_admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `collection_schedule`
--
ALTER TABLE `collection_schedule`
  MODIFY `collection_schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback_report`
--
ALTER TABLE `feedback_report`
  MODIFY `feedback_report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `garbage_collector`
--
ALTER TABLE `garbage_collector`
  MODIFY `garbage_collector_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lgu_admin`
--
ALTER TABLE `lgu_admin`
  MODIFY `lgu_admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `location_history`
--
ALTER TABLE `location_history`
  MODIFY `location_history_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resident`
--
ALTER TABLE `resident`
  MODIFY `resident_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `route`
--
ALTER TABLE `route`
  MODIFY `route_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `truck`
--
ALTER TABLE `truck`
  MODIFY `truck_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `waste_category`
--
ALTER TABLE `waste_category`
  MODIFY `waste_category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `announcement_ibfk_1` FOREIGN KEY (`lgu_admin_id`) REFERENCES `lgu_admin` (`lgu_admin_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `collection_schedule`
--
ALTER TABLE `collection_schedule`
  ADD CONSTRAINT `collection_schedule_ibfk_2` FOREIGN KEY (`waste_category_id`) REFERENCES `waste_category` (`waste_category_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_collection_schedule_barangay_admin` FOREIGN KEY (`barangay_admin_id`) REFERENCES `barangay_admin` (`barangay_admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `feedback_report`
--
ALTER TABLE `feedback_report`
  ADD CONSTRAINT `feedback_report_ibfk_1` FOREIGN KEY (`lgu_admin_id`) REFERENCES `lgu_admin` (`lgu_admin_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `feedback_report_ibfk_2` FOREIGN KEY (`resident_id`) REFERENCES `resident` (`resident_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `garbage_collector`
--
ALTER TABLE `garbage_collector`
  ADD CONSTRAINT `garbage_collector_ibfk_1` FOREIGN KEY (`route_id`) REFERENCES `route` (`route_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `garbage_collector_ibfk_2` FOREIGN KEY (`truck_id`) REFERENCES `truck` (`truck_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `location_history`
--
ALTER TABLE `location_history`
  ADD CONSTRAINT `location_history_ibfk_1` FOREIGN KEY (`garbage_collector_id`) REFERENCES `garbage_collector` (`garbage_collector_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`lgu_admin_id`) REFERENCES `lgu_admin` (`lgu_admin_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `route`
--
ALTER TABLE `route`
  ADD CONSTRAINT `fk_route_barangay_admin` FOREIGN KEY (`barangay_admin_id`) REFERENCES `barangay_admin` (`barangay_admin_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
