-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 05, 2026 at 12:28 PM
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
-- Database: `guitarinventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_brands`
--

CREATE TABLE `tbl_brands` (
  `brand_id` int(11) NOT NULL,
  `brand_name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_brands`
--

INSERT INTO `tbl_brands` (`brand_id`, `brand_name`, `is_active`, `phone`, `email`, `address`) VALUES
(1, 'Fender', 1, '09123456789', 'contact@fender.com', 'Scottsdale, Arizona, USA'),
(2, 'Gibson', 1, '09234567890', 'contact@gibson.com', 'Nashville, Tennessee, USA'),
(3, 'Ibanez', 1, '09345678901', 'contact@ibanez.com', 'Nagoya, Aichi, Japan'),
(4, 'Boss', 1, '09456789012', 'contact@boss.com', 'Hamamatsu, Shizuoka, Japan'),
(5, 'D\'Addario', 1, '09567890123', 'contact@daddario.com', 'Farmingdale, New York, USA');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_categories`
--

CREATE TABLE `tbl_categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_categories`
--

INSERT INTO `tbl_categories` (`category_id`, `category_name`) VALUES
(1, 'Guitars'),
(2, 'Amplifiers'),
(3, 'Effects Pedals'),
(4, 'Accessories');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_products`
--

CREATE TABLE `tbl_products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `brand_id` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity_on_hand` int(11) NOT NULL DEFAULT 0,
  `location` varchar(100) NOT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_products`
--

INSERT INTO `tbl_products` (`product_id`, `product_name`, `category_id`, `brand_id`, `unit_price`, `quantity_on_hand`, `location`, `is_available`) VALUES
(1, 'Affinity Series Stratocaster', 1, 1, 45000.00, 15, 'Showroom Wall A', 1),
(2, 'Les Paul Standard 60s', 1, 2, 145000.00, 3, 'Premium Glass Cabinet', 1),
(3, 'AW54CE Artwood Acoustic', 1, 3, 37000.00, 21, 'Showroom Wall B', 1),
(4, 'Katana 50 MkII', 2, 4, 18500.00, 8, 'Amp Section Row 1', 1),
(5, 'Fender Mustang LT25', 2, 1, 12500.00, 5, 'Amp Section Row 1', 1),
(6, 'DS-1 Distortion', 3, 4, 4500.00, 12, 'Pedal Display Case', 1),
(7, 'CH-1 Super Chorus', 3, 4, 6200.00, 0, 'Pedal Display Case', 0),
(8, 'XL Nickel Wound Guitar Strings (09-42)', 4, 5, 450.00, 50, 'Accessory Rack 1', 1),
(9, 'Fender Custom Shop Instrument Cable 10ft', 4, 1, 1500.00, 25, 'Accessory Rack 2', 1),
(10, 'D\'Addario Planet Waves Guitar Strap', 4, 5, 950.00, 18, 'Accessory Rack 3', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_roles`
--

CREATE TABLE `tbl_roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_roles`
--

INSERT INTO `tbl_roles` (`role_id`, `role_name`) VALUES
(1, 'Administrator'),
(2, 'Staff');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_stock_history`
--

CREATE TABLE `tbl_stock_history` (
  `stockHistory_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `stock_type` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL,
  `stockHistory_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_stock_history`
--

INSERT INTO `tbl_stock_history` (`stockHistory_id`, `product_id`, `user_id`, `stock_type`, `quantity`, `stockHistory_date`) VALUES
(1, 1, 1, 'Stock-In', 15, '2026-05-16 09:00:00'),
(2, 2, 1, 'Stock-In', 4, '2026-05-16 09:30:00'),
(3, 4, 1, 'Stock-In', 8, '2026-05-16 10:00:00'),
(4, 7, 1, 'Stock-In', 2, '2026-05-16 11:15:00'),
(5, 8, 1, 'Stock-In', 50, '2026-05-16 13:00:00'),
(6, 1, 2, 'Stock-Out', 1, '2026-05-17 14:22:15'),
(7, 2, 3, 'Stock-Out', 1, '2026-05-18 11:05:42'),
(8, 8, 2, 'Stock-Out', 3, '2026-05-19 16:45:00'),
(9, 7, 3, 'Stock-Out', 2, '2026-05-20 10:30:12'),
(10, 3, 1, 'Stock-In', 21, '2026-05-22 08:45:20'),
(11, 9, 1, 'Stock-In', 25, '2026-05-22 09:15:00'),
(12, 10, 1, 'Stock-In', 18, '2026-05-22 09:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `full_name`, `username`, `password`, `role_id`) VALUES
(1, 'Jian Karlo H. Alatiit', 'karlo_admin', 'karlopogi123', 1),
(2, 'Gian Rizen A. Lacao', 'gian_staff', 'percyjackson', 2),
(3, 'Micheal P. Samia', 'sam_staff', 'sam123', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_brands`
--
ALTER TABLE `tbl_brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `tbl_categories`
--
ALTER TABLE `tbl_categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `brand_id` (`brand_id`);

--
-- Indexes for table `tbl_roles`
--
ALTER TABLE `tbl_roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `tbl_stock_history`
--
ALTER TABLE `tbl_stock_history`
  ADD PRIMARY KEY (`stockHistory_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_brands`
--
ALTER TABLE `tbl_brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_categories`
--
ALTER TABLE `tbl_categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_products`
--
ALTER TABLE `tbl_products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_roles`
--
ALTER TABLE `tbl_roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_stock_history`
--
ALTER TABLE `tbl_stock_history`
  MODIFY `stockHistory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_products`
--
ALTER TABLE `tbl_products`
  ADD CONSTRAINT `tbl_products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `tbl_categories` (`category_id`),
  ADD CONSTRAINT `tbl_products_ibfk_2` FOREIGN KEY (`brand_id`) REFERENCES `tbl_brands` (`brand_id`);

--
-- Constraints for table `tbl_stock_history`
--
ALTER TABLE `tbl_stock_history`
  ADD CONSTRAINT `tbl_stock_history_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `tbl_products` (`product_id`),
  ADD CONSTRAINT `tbl_stock_history_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`);

--
-- Constraints for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD CONSTRAINT `tbl_users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `tbl_roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
