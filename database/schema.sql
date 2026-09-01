-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Sep 01, 2026 at 10:37 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crumb_and_cream`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int UNSIGNED NOT NULL,
  `customer_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_info` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `size` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `flavor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Graham bars flavor',
  `quantity` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `message` text COLLATE utf8mb4_unicode_ci,
  `amount` decimal(10,2) DEFAULT NULL COMMENT 'Order total in PHP, set when a payment is generated',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'new',
  `payment_status` enum('unpaid','paid','expired','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `payment_intent_id` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'PayMongo payment intent id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `contact_info`, `size`, `flavor`, `quantity`, `message`, `amount`, `status`, `payment_status`, `payment_intent_id`, `created_at`) VALUES
(11, 'Gerardo', '0912345678990870735342', '1 Pieces', 'Cookies and Cream', 1, 'Hehe', 30.00, 'completed', 'unpaid', NULL, '2026-09-01 10:35:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
