-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 20, 2025 at 07:37 AM
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
-- Database: `colab_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_number` varchar(50) DEFAULT NULL,
  `account_type` enum('checking','savings','business','investment') DEFAULT 'checking',
  `initial_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'PHP',
  `description` text DEFAULT NULL,
  `status` enum('active','inactive','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `account_name`, `account_number`, `account_type`, `initial_balance`, `current_balance`, `currency`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Main Business Account', 'BA001', 'business', 100000.00, 100000.00, 'PHP', 'Primary business operating account', 'active', '2025-07-19 08:21:39', '2025-07-19 08:21:39'),
(2, 'Savings Account', 'BA002', 'savings', 50000.00, 30000.00, 'PHP', 'Business savings for emergencies', 'active', '2025-07-19 08:21:39', '2025-07-19 11:51:04');

-- --------------------------------------------------------

--
-- Table structure for table `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL,
  `bank_account_id` int(11) NOT NULL,
  `transaction_type` enum('deposit','withdrawal','transfer_in','transfer_out','fee','interest','adjustment') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `related_transaction_id` int(11) DEFAULT NULL,
  `status` enum('pending','completed','cancelled','failed') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bank_transactions`
--

INSERT INTO `bank_transactions` (`id`, `bank_account_id`, `transaction_type`, `amount`, `balance_before`, `balance_after`, `description`, `reference_number`, `transaction_date`, `category`, `notes`, `user_id`, `related_transaction_id`, `status`, `created_at`) VALUES
(1, 1, 'deposit', 100000.00, 0.00, 100000.00, 'Initial deposit for Main Business Account', 'INIT-001', '2025-07-19 16:21:39', 'Initial Setup', NULL, NULL, NULL, 'completed', '2025-07-19 08:21:39'),
(2, 2, 'deposit', 50000.00, 0.00, 50000.00, 'Initial deposit for Savings Account', 'INIT-002', '2025-07-19 16:21:39', 'Initial Setup', NULL, NULL, NULL, 'completed', '2025-07-19 08:21:39'),
(4, 2, 'deposit', 20000.00, 50000.00, 70000.00, 'Emergency', 'TXN-20250719-3944', '2025-07-19 19:50:20', 'Expenses', '', NULL, NULL, 'completed', '2025-07-19 11:50:20'),
(5, 2, 'withdrawal', 40000.00, 70000.00, 30000.00, 'Emergency', 'TXN-20250719-0029', '2025-07-19 19:51:04', 'Expenses', '', NULL, NULL, 'completed', '2025-07-19 11:51:04');

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `account_type` varchar(30) NOT NULL,
  `account_code` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chart_of_accounts`
--

INSERT INTO `chart_of_accounts` (`id`, `account_name`, `account_type`, `account_code`, `description`, `created_at`) VALUES
(1, 'NIggaaCHKUU', 'Asset', '321313', 'di ko na alam', '2025-07-19 00:24:15');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `credit_limit` decimal(10,2) DEFAULT 0.00,
  `payment_terms` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fired_employees`
--

CREATE TABLE `fired_employees` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `date_fired` date NOT NULL,
  `reason` text NOT NULL,
  `fired_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_images`
--

CREATE TABLE `inventory_images` (
  `image_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_images`
--

INSERT INTO `inventory_images` (`image_id`, `inventory_id`, `image_path`) VALUES
(1, 2, 'uploads/inv_687b0b38698db0.80375230.jpg'),
(2, 3, 'uploads/inv_687b0ed1342855.78305997.jpg'),
(3, 4, 'uploads/inv_687b0f1822bc47.33678871.jpg'),
(4, 5, 'uploads/inv_687b101e33e1e3.06464841.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`id`, `name`, `quantity`, `price`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Chicken', 6546, 64654.00, 'Masarap Kahit Walang Sauce', '2025-07-19 03:04:24', '2025-07-19 03:04:24'),
(3, 'Fish', 6546, 46465.00, 'wlang Tinik', '2025-07-19 03:19:45', '2025-07-19 03:19:45'),
(4, 'Pig', 6456, 46546.00, 'wlang taba', '2025-07-19 03:20:56', '2025-07-19 03:20:56'),
(5, 'cow', 5465, 54646.00, 'Shesss', '2025-07-19 03:25:18', '2025-07-19 03:25:18');

-- --------------------------------------------------------

--
-- Table structure for table `login_approvals`
--

CREATE TABLE `login_approvals` (
  `approval_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `status` enum('pending','approved','expired') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_employees`
--

CREATE TABLE `payroll_employees` (
  `id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `job_title` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone_number` varchar(30) DEFAULT NULL,
  `date_hired` date DEFAULT NULL,
  `status` enum('active','inactive','terminated') DEFAULT 'active',
  `photo_path` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_employees`
--

INSERT INTO `payroll_employees` (`id`, `first_name`, `last_name`, `department`, `job_title`, `email`, `phone_number`, `date_hired`, `status`, `photo_path`, `address`, `birthday`, `gender`, `created_at`, `updated_at`) VALUES
(3, 'Julian Isiah', 'Belacse', 'PROGRAMMING Dep.', 'Senior High School English Teacher', 'julian.belacse@gmail.com', '0906-789-1234', '0000-00-00', 'active', 'uploads/emp_687c600cf38ea6.73323698.jpg', '45 Lakandula St., Barangay Sto. Niño, Mandaluyong City, Philippines', '1999-01-05', 'male', '2025-07-20 03:18:36', '2025-07-20 03:18:36'),
(4, 'Jossalyn', 'Cortejos', 'Sleeping Dep.', 'Part-Time Printing Assistant', 'Jossalyncortejos16@gmail.com', '09854605780', '0000-00-00', 'active', 'uploads/emp_687c6e5cb3bd06.05889733.jpg', 'Brgy. 39- D, Purok 10 Washington StreetDavao City, Davao del Sur', '2005-09-16', 'female', '2025-07-20 04:19:40', '2025-07-20 04:19:40'),
(5, 'Rose', 'Aronce', 'Human Resources', 'Administrative Assistant', 'rose.aronce20@gmail.com', '0928-765-4321', '2025-07-20', 'active', 'uploads/emp_687c7f5ac6f252.94183640.jpg', '89 Mabuhay Lane, Barangay San Roque, Cebu City, Philippines', '2000-08-22', 'female', '2025-07-20 05:32:10', '2025-07-20 05:32:10');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `movement_type` enum('IN','OUT') NOT NULL,
  `quantity` int(11) NOT NULL CHECK (`quantity` > 0),
  `reason` varchar(100) NOT NULL,
  `notes` text DEFAULT NULL,
  `movement_date` datetime DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `reference_number` varchar(50) DEFAULT NULL,
  `cost_per_unit` decimal(10,2) DEFAULT NULL,
  `total_cost` decimal(10,2) DEFAULT NULL,
  `payment_status` enum('paid','pending','partial') DEFAULT 'pending',
  `movement_category` enum('purchase','sale','return','adjustment','loss','transfer') DEFAULT 'adjustment',
  `batch_number` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `quality_status` enum('good','damaged','expired') DEFAULT 'good'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_login`
--

CREATE TABLE `user_login` (
  `user_id` int(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `password_secret` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `phone_confirmed` tinyint(1) NOT NULL DEFAULT 0,
  `two_step_on` tinyint(1) NOT NULL DEFAULT 0,
  `date_joined` datetime NOT NULL DEFAULT current_timestamp(),
  `last_update` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `failed_attempts` int(11) NOT NULL DEFAULT 0,
  `last_failed_attempt` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `reset_code` varchar(255) DEFAULT NULL,
  `reset_code_expiry` datetime DEFAULT NULL,
  `reset_code_used` tinyint(1) DEFAULT 0,
  `job_title` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_login`
--

INSERT INTO `user_login` (`user_id`, `email_address`, `password_secret`, `phone_number`, `phone_confirmed`, `two_step_on`, `date_joined`, `last_update`, `failed_attempts`, `last_failed_attempt`, `status`, `reset_code`, `reset_code_expiry`, `reset_code_used`, `job_title`) VALUES
(9, 'johnroedlahaylahay2231@gmail.com', '$2y$10$BHNxOJM9E0PomwyTGN14Pu9Knc.qL9u7p4aTT4baxmYCG7snC6BUu', NULL, 0, 0, '2025-07-20 03:13:26', '2025-07-20 03:13:26', 0, NULL, 'active', NULL, NULL, 0, 'executives'),
(10, 'Jossalyncortejos16@gmail.com', '$2y$10$Vt6VrMtYD34hNCzUs9ILVuZ7V3n/JDTWSYuMqjzJPm6rQ/V4FMvMO', NULL, 0, 0, '2025-07-20 12:22:39', '2025-07-20 12:22:53', 0, NULL, 'active', NULL, NULL, 0, 'senior_manager');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_number` (`account_number`);

--
-- Indexes for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_account_id` (`bank_account_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `transaction_date` (`transaction_date`),
  ADD KEY `related_transaction_id` (`related_transaction_id`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fired_employees`
--
ALTER TABLE `fired_employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date_fired` (`date_fired`),
  ADD KEY `fired_employees_ibfk_2` (`fired_by`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `inventory_images`
--
ALTER TABLE `inventory_images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_approvals`
--
ALTER TABLE `login_approvals`
  ADD PRIMARY KEY (`approval_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payroll_employees`
--
ALTER TABLE `payroll_employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_login`
--
ALTER TABLE `user_login`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fired_employees`
--
ALTER TABLE `fired_employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_images`
--
ALTER TABLE `inventory_images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `login_approvals`
--
ALTER TABLE `login_approvals`
  MODIFY `approval_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `payroll_employees`
--
ALTER TABLE `payroll_employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_login`
--
ALTER TABLE `user_login`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD CONSTRAINT `bank_transactions_ibfk_1` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bank_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user_login` (`user_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bank_transactions_ibfk_3` FOREIGN KEY (`related_transaction_id`) REFERENCES `bank_transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fired_employees`
--
ALTER TABLE `fired_employees`
  ADD CONSTRAINT `fired_employees_ibfk_2` FOREIGN KEY (`fired_by`) REFERENCES `user_login` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_images`
--
ALTER TABLE `inventory_images`
  ADD CONSTRAINT `inventory_images_ibfk_1` FOREIGN KEY (`inventory_id`) REFERENCES `inventory_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `login_approvals`
--
ALTER TABLE `login_approvals`
  ADD CONSTRAINT `login_approvals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user_login` (`user_id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_ibfk_3` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
