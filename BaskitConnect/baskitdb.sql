-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 15, 2025 at 10:42 AM
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
-- Database: `baskitdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_tokens`
--

CREATE TABLE `access_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `created_at`) VALUES
(2, 'JoroseAdmin', '$2y$10$jsMPCCpoPMKo4J3w8w0bZ.HoZB5kQQfuqOQkv6PtRzjwLIGCwOGWi', '2025-02-09 19:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `admin_access_tokens`
--

CREATE TABLE `admin_access_tokens` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `access_token` varchar(64) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_access_tokens`
--

INSERT INTO `admin_access_tokens` (`id`, `admin_id`, `access_token`, `created_at`) VALUES
(6, 2, '98edf2499b1e748c3cbca175ae2afc45c48df0686ff3763cac07f38db2d25ab3', '2025-02-09 19:13:00');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_quantity` int(11) NOT NULL,
  `product_portion` enum('1pc','1/4kg','1/2kg','1kg') NOT NULL,
  `product_origin` enum('DAGUPAN','CALASIAO') NOT NULL,
  `store_id` int(11) NOT NULL,
  `store_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `product_name`, `product_price`, `product_quantity`, `product_portion`, `product_origin`, `store_id`, `store_name`, `created_at`, `updated_at`) VALUES
(2, 4, 5, 'Apple', 15.00, 5, '1pc', 'DAGUPAN', 1, 'Paul store', '2025-02-10 09:03:10', '2025-02-10 09:03:10');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `product_category` enum('STORE','VEGETABLES','MEAT','FISH','FROZEN','SPICES','FRUITS') DEFAULT NULL,
  `product_origin` enum('DAGUPAN','CALASIAO') DEFAULT NULL,
  `store_id` int(11) NOT NULL,
  `store_name` varchar(255) DEFAULT NULL,
  `store_phone_number` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `owner_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `product_price`, `product_category`, `product_origin`, `store_id`, `store_name`, `store_phone_number`, `created_at`, `updated_at`, `owner_name`) VALUES
(5, 'Apple', 15.00, 'FRUITS', 'DAGUPAN', 1, 'Paul store', '09322232424', '2025-02-10 05:02:11', '2025-02-10 05:02:11', 'PaulAko'),
(6, 'Apple', 15.00, 'FRUITS', 'CALASIAO', 3, 'Jorose Store', '09123456789', '2025-02-10 07:47:19', '2025-02-10 07:47:19', 'Jorose'),
(7, 'Orange', 15.00, 'FRUITS', 'CALASIAO', 3, 'Jorose Store', '09123456789', '2025-02-10 07:47:29', '2025-02-10 07:47:29', 'Jorose'),
(8, 'Pepper', 10.00, 'SPICES', 'CALASIAO', 3, 'Jorose Store', '09123456789', '2025-02-10 08:27:05', '2025-02-10 08:27:05', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` int(11) NOT NULL,
  `store_name` varchar(255) NOT NULL,
  `owner_name` varchar(255) DEFAULT NULL,
  `store_phone_number` varchar(50) NOT NULL,
  `store_origin` enum('DAGUPAN','CALASIAO') NOT NULL,
  `store_rating` decimal(3,2) DEFAULT 0.00,
  `store_status` enum('Standard','Partner') DEFAULT 'Standard',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `store_name`, `owner_name`, `store_phone_number`, `store_origin`, `store_rating`, `store_status`, `created_at`) VALUES
(1, 'Paul store', NULL, '09322232424', 'DAGUPAN', 0.00, 'Standard', '2025-02-05 14:03:19'),
(2, 'Paul store', NULL, '09123456789', 'DAGUPAN', 0.00, 'Standard', '2025-02-09 19:15:49'),
(3, 'Jorose Store', NULL, '09123456789', 'CALASIAO', 0.00, 'Partner', '2025-02-09 19:16:46'),
(4, 'Victoria Store', 'Victoria', '09123456789', 'CALASIAO', 0.00, 'Standard', '2025-02-10 08:08:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mobile_number` varchar(13) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `age` int(11) NOT NULL,
  `is_verified` enum('Pending','Verified') DEFAULT 'Pending',
  `role` enum('Consumer','Tagabili') DEFAULT 'Consumer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `mobile_number`, `password`, `firstname`, `lastname`, `age`, `is_verified`, `role`, `created_at`) VALUES
(4, 'Jorose', 'jorosepooo@gmail.com', '09322222222', '$2y$10$t/H2QozN3HPsaUMYEY51x.BCYqWORWYY0CgnHF7c5aaLalhgwfbEK', 'Paul', 'ako', 20, 'Verified', 'Consumer', '2025-02-06 13:48:20'),
(20, 'Victoria', 'jorosespotify@gmail.com', '09123456789', '$2y$10$HH7lUsr7uZJNWJWtzv6LjefJtUEUuAKc7LbIFe9d83xrFjiiQsxWy', 'Victoria', 'Catabay', 19, 'Pending', 'Consumer', '2025-02-11 18:47:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_tokens`
--
ALTER TABLE `access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `access_token` (`access_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `admin_access_tokens`
--
ALTER TABLE `admin_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `access_token` (`access_token`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `store_id` (`store_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `mobile_number` (`mobile_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_tokens`
--
ALTER TABLE `access_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_access_tokens`
--
ALTER TABLE `admin_access_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_tokens`
--
ALTER TABLE `access_tokens`
  ADD CONSTRAINT `access_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_access_tokens`
--
ALTER TABLE `admin_access_tokens`
  ADD CONSTRAINT `admin_access_tokens_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
