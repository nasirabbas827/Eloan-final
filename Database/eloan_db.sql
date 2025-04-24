-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2025 at 11:19 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `eloan_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `loan_applications`
--

CREATE TABLE `loan_applications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `loan_amount` decimal(10,2) NOT NULL,
  `loan_type` varchar(255) NOT NULL,
  `purpose` text NOT NULL,
  `repayment_period` int(11) NOT NULL,
  `proof_of_income` varchar(255) NOT NULL,
  `proof_of_identification` varchar(255) NOT NULL,
  `credit_history` varchar(255) NOT NULL,
  `application_status` enum('submitted','under_review','approved','rejected','partially_completed','draft') DEFAULT 'submitted',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `review` text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_applications`
--

INSERT INTO `loan_applications` (`id`, `user_id`, `loan_amount`, `loan_type`, `purpose`, `repayment_period`, `proof_of_income`, `proof_of_identification`, `credit_history`, `application_status`, `created_at`, `updated_at`, `review`) VALUES
(1, 1, 30000.00, 'Home', 'ds', 3, 'Seating_Arrangement.pdf', 'Seating_Arrangement.pdf', 'Seating_Arrangement.pdf', 'draft', '2025-04-23 07:00:53', '2025-04-23 07:00:53', ''),
(2, 1, 30000.00, 'Home', 'ds', 3, 'Seating_Arrangement.pdf', 'Seating_Arrangement.pdf', 'Seating_Arrangement.pdf', 'approved', '2025-04-23 07:06:35', '2025-04-24 09:13:54', 'No dsa'),
(3, 1, 30000.00, 'Home', 'ds', 3, 'Seating_Arrangement.pdf', 'Seating_Arrangement.pdf', 'Seating_Arrangement.pdf', 'approved', '2025-04-23 07:07:39', '2025-04-24 08:40:56', 'The application is approved');

-- --------------------------------------------------------

--
-- Table structure for table `loan_disbursement`
--

CREATE TABLE `loan_disbursement` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `account_holder_name` varchar(255) DEFAULT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `account_number` varchar(255) DEFAULT NULL,
  `ifsc_code` varchar(50) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_disbursement`
--

INSERT INTO `loan_disbursement` (`id`, `application_id`, `account_holder_name`, `bank_name`, `account_number`, `ifsc_code`, `submitted_at`) VALUES
(1, 2, 'Nasir Abbas', 'FD', '3232222222223', '3234', '2025-04-23 07:51:53'),
(2, 3, 'dssd', 'FD', '3232222222223', 'dsssd', '2025-04-23 08:02:27');

-- --------------------------------------------------------

--
-- Table structure for table `loan_repayment_schedule`
--

CREATE TABLE `loan_repayment_schedule` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `due_date` date NOT NULL,
  `installment_amount` decimal(10,2) NOT NULL,
  `status` enum('unpaid','paid') DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loan_repayment_schedule`
--

INSERT INTO `loan_repayment_schedule` (`id`, `application_id`, `due_date`, `installment_amount`, `status`) VALUES
(1, 2, '2025-05-23', 12000.00, 'unpaid'),
(2, 2, '2025-06-23', 12000.00, 'unpaid'),
(3, 2, '2025-07-23', 12000.00, 'unpaid'),
(4, 3, '2025-05-23', 11000.00, 'paid'),
(5, 3, '2025-06-23', 11000.00, 'unpaid'),
(6, 3, '2025-07-23', 11000.00, 'unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `usertype` enum('borrower','lender') NOT NULL DEFAULT 'borrower',
  `status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `phone`, `reset_token`, `usertype`, `status`) VALUES
(1, 'borrower123', '$2y$10$zecMFDp1yV4DwcPjKKm1ne7Pd9oBXGsNpUnH.jQMNHqoRCz8avBTu', 'nasiryt.827s@gmail.com', '3176526827', '1cd59d6a24ec2c23c757816f540df80d95c82d93e59e376e2d090d7038342c247c5e816d8794ef58200f71b018a1fc8e6716', 'borrower', 'approved'),
(2, 'lender123', '$2y$10$5ypb/fF5RyW0FacSHrkjPOy02ufAtxQfMIG4jUAyUglL.eesH7p.G', 'saifx280@gmail.com', '3176526826', NULL, 'lender', 'approved'),
(4, 'testnaseir', '$2y$10$f8BZ7OPF6xLwnePETnjQBOhcJSZGp5FlGIEP5b5czfxoVBksCZY3e', 'nasiryt.827@gmail.com', '03188789565', NULL, 'borrower', 'approved');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `loan_disbursement`
--
ALTER TABLE `loan_disbursement`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `loan_repayment_schedule`
--
ALTER TABLE `loan_repayment_schedule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `loan_applications`
--
ALTER TABLE `loan_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loan_disbursement`
--
ALTER TABLE `loan_disbursement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loan_repayment_schedule`
--
ALTER TABLE `loan_repayment_schedule`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loan_applications`
--
ALTER TABLE `loan_applications`
  ADD CONSTRAINT `loan_applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `loan_disbursement`
--
ALTER TABLE `loan_disbursement`
  ADD CONSTRAINT `loan_disbursement_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`);

--
-- Constraints for table `loan_repayment_schedule`
--
ALTER TABLE `loan_repayment_schedule`
  ADD CONSTRAINT `loan_repayment_schedule_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `loan_applications` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
