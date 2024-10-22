-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 18, 2024 at 10:30 AM
-- Server version: 8.0.39-0ubuntu0.24.04.2
-- PHP Version: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `lu_yosh_catering`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Sumos'),
(2, 'Refrescos'),
(3, 'Comidas');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('chef','waiter','manager') NOT NULL,
  `hire_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `image_path` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `description`, `price`, `category`, `image_path`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Hamburguer de Galinha', 'Com Batata', 250.00, 'Fast Food', '../uploads/menu/670ece14e08b1.png', 1, '2024-10-15 19:39:21', '2024-10-15 20:18:28');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `table_id` int DEFAULT NULL,
  `status` enum('active','completed','paid') DEFAULT 'active',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `table_id`, `status`, `total_amount`, `created_at`) VALUES
(6, 1, 'completed', 1850.00, '2024-10-16 12:49:26'),
(7, 1, 'completed', 1850.00, '2024-10-16 12:56:53'),
(8, 2, 'completed', 4050.00, '2024-10-16 12:59:04'),
(9, 2, 'completed', 4050.00, '2024-10-16 13:00:02'),
(10, 1, 'completed', 0.00, '2024-10-16 13:11:44'),
(11, 1, 'completed', 700.00, '2024-10-16 13:13:09'),
(12, 3, 'completed', 700.00, '2024-10-17 10:21:21'),
(13, 1, 'completed', 700.00, '2024-10-17 11:34:46');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`) VALUES
(6, 6, 1, 1),
(7, 6, 4, 2),
(8, 6, 2, 3),
(9, 6, 3, 2),
(10, 7, 1, 1),
(11, 7, 4, 2),
(12, 7, 2, 3),
(13, 7, 3, 2),
(14, 8, 1, 1),
(15, 8, 4, 3),
(16, 8, 2, 10),
(17, 8, 3, 3),
(18, 9, 1, 1),
(19, 9, 4, 3),
(20, 9, 2, 10),
(21, 9, 3, 3),
(22, 11, 1, 1),
(23, 11, 2, 1),
(24, 11, 2, 1),
(25, 12, 1, 1),
(26, 12, 2, 1),
(27, 12, 3, 1),
(28, 13, 1, 1),
(29, 13, 2, 1),
(30, 13, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int NOT NULL DEFAULT '0',
  `category_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock_quantity`, `category_id`) VALUES
(1, 'Ceres', '1lt', 200.00, 96, 1),
(2, 'Hamburguer de Galinha', 'Hamburguer de Galinha', 250.00, 94, 3),
(3, 'Hamburguer de Vaca', 'com Batatas', 250.00, 100, 3),
(4, 'Chicker Pops', 'Pipoca de Galinha', 200.00, 495, 3);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int NOT NULL,
  `sale_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(20) DEFAULT 'completed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `sale_date`, `total_amount`, `payment_method`, `status`) VALUES
(16, '2024-10-16 15:00:18', 4050.00, 'Dinheiro', 'completed'),
(17, '2024-10-16 15:10:57', 1850.00, 'Dinheiro', 'completed'),
(18, '2024-10-16 15:11:00', 1850.00, 'Dinheiro', 'completed'),
(19, '2024-10-16 15:11:03', 4050.00, 'Dinheiro', 'completed'),
(20, '2024-10-16 15:12:13', 0.00, 'Dinheiro', 'completed'),
(21, '2024-10-16 15:34:08', 200.00, 'mpesa', 'completed'),
(22, '2024-10-16 18:30:16', 700.00, 'Dinheiro', 'completed'),
(23, '2024-10-17 12:15:40', 200.00, 'cash', 'completed'),
(24, '2024-10-17 12:22:26', 700.00, 'Dinheiro', 'completed'),
(25, '2024-10-17 13:06:52', 400.00, 'cash', 'completed'),
(26, '2024-10-17 13:33:40', 400.00, 'cash', 'completed'),
(27, '2024-10-17 13:35:13', 700.00, 'Dinheiro', 'completed'),
(28, '2024-10-17 13:36:38', 200.00, 'cash', 'completed');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int NOT NULL,
  `sale_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`) VALUES
(16, 16, 1, 1, 200.00),
(17, 16, 2, 10, 250.00),
(18, 16, 3, 3, 250.00),
(19, 16, 4, 3, 200.00),
(20, 17, 1, 1, 200.00),
(21, 17, 2, 3, 250.00),
(22, 17, 3, 2, 250.00),
(23, 17, 4, 2, 200.00),
(24, 18, 1, 1, 200.00),
(25, 18, 2, 3, 250.00),
(26, 18, 3, 2, 250.00),
(27, 18, 4, 2, 200.00),
(28, 19, 1, 1, 200.00),
(29, 19, 2, 10, 250.00),
(30, 19, 3, 3, 250.00),
(31, 19, 4, 3, 200.00),
(32, 21, 4, 1, 200.00),
(33, 22, 1, 1, 200.00),
(34, 22, 2, 1, 250.00),
(35, 22, 2, 1, 250.00),
(36, 23, 1, 1, 200.00),
(37, 24, 1, 1, 200.00),
(38, 24, 2, 1, 250.00),
(39, 24, 3, 1, 250.00),
(40, 25, 1, 1, 200.00),
(41, 25, 4, 1, 200.00),
(42, 26, 1, 1, 200.00),
(43, 26, 4, 1, 200.00),
(44, 27, 1, 1, 200.00),
(45, 27, 2, 1, 250.00),
(46, 27, 3, 1, 250.00),
(47, 28, 1, 1, 200.00);

-- --------------------------------------------------------

--
-- Table structure for table `tables`
--

CREATE TABLE `tables` (
  `id` int NOT NULL,
  `number` int NOT NULL,
  `capacity` int NOT NULL,
  `status` enum('free','occupied') DEFAULT 'free'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tables`
--

INSERT INTO `tables` (`id`, `number`, `capacity`, `status`) VALUES
(1, 1, 4, 'occupied'),
(2, 2, 4, 'free'),
(3, 3, 4, 'free'),
(4, 4, 4, 'free'),
(5, 5, 4, 'free'),
(6, 6, 4, 'free');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('admin','manager','waiter') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`) VALUES
(1, 'luadmin', '$2y$10$OfNV6oX/BF6wrUHRQS48x.erCZbRk68ZpcLdx3TmzbGO.XWhgzfw.', 'Administrador', 'admin'),
(2, 'filipedomingos198@gmail.com', '$2y$10$GDSB4tjKhE5RLTsMiedRKOmmDXFomy2ld..lZPfmk7EXyYaSvxrEG', 'Filipe dos Santos', 'manager'),
(4, 'luis@lifechurch.mz', '$2y$10$7GtSUt5QxwXqmnBQAomDm.Nd3jHK2AMOGmJMMUdaTtNm5vH24T9Uy', 'Gerente Principal', 'manager');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `table_id` (`table_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `tables`
--
ALTER TABLE `tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT for table `tables`
--
ALTER TABLE `tables`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`),
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
