-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2026 at 01:28 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `archipaws_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', '$2y$10$xqaxVvJX8NQAYw1nQYvCk.UA8GHCXEuhvkqVNcv2t0EUM8NIfOuaG');

-- --------------------------------------------------------

--
-- Table structure for table `hero_slides`
--

CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL,
  `offer_text` varchar(255) DEFAULT NULL,
  `title_line1` varchar(255) DEFAULT NULL,
  `title_line2` varchar(255) DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `offer_text`, `title_line1`, `title_line2`, `button_text`, `button_link`, `image_path`, `sort_order`) VALUES
(13, 'SAVE 10 - 20 % OFF', 'Best Destination', 'Your Pets', 'SHOP NOW', 'shop.php', 'uploads/1775545614_1.png', 0),
(14, 'SAVE 10 - 20 % OFF', 'CHOOSE YOU KENNEL', 'Your Pets', 'SHOP NOW', 'shop.php', 'uploads/1775545650_3.png', 0),
(15, 'SAVE 10 - 20 % OFF', 'CHOOSE YOU KENNEL', 'Your Pets', 'SHOP NOW', 'shop.php', 'uploads/1775545707_4.png', 0),
(16, 'SAVE 10 - 20 % OFF', 'CHOOSE YOU KENNEL', 'For Your Pets', 'CLICK HERE', 'shop.php', 'uploads/1775545728_5.png', 0);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `shipping_address` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `email`, `phone`, `shipping_address`, `total_price`, `status`, `created_at`) VALUES
(4, 1, 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', '8943804920', 'kandoth house varappetty p.o kothamagaalam', '5000.00', 'Processing', '2026-04-07 08:07:15'),
(6, 1, 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', '8943804920', 'kakk', '3500.00', 'Received', '2026-04-10 00:57:05');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`) VALUES
(4, 4, 6, 1, '5000.00', '2026-04-07 08:07:15'),
(6, 6, 14, 1, '3500.00', '2026-04-10 00:57:05');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `stock_status` varchar(50) DEFAULT 'In Stock',
  `stock_quantity` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `description`, `price`, `old_price`, `category`, `badge`, `rating`, `stock_status`, `stock_quantity`, `created_at`) VALUES
(5, 'Wooden Kennel', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '3500.00', '5000.00', 'dog', '', 5, 'In Stock', 12, '2026-04-07 00:37:47'),
(6, 'WOODEN KENNELS', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '5000.00', '7000.00', 'dog', '', 5, 'In Stock', 10, '2026-04-07 00:38:55'),
(8, 'WOODEN TEAK KENNEL', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '10000.00', '12000.00', 'dog', '', 5, 'In Stock', 19, '2026-04-07 00:44:53'),
(9, 'WOODEN KENNEL', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '8200.00', '9000.00', 'dog', '', 5, 'In Stock', 12, '2026-04-07 00:45:45'),
(10, 'WOODEN KENNEL', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '6000.00', '7000.00', 'dog', '', 5, 'In Stock', 10, '2026-04-07 00:47:15'),
(12, 'WOOODEN MAHAGONI', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '14000.00', '20000.00', 'dog', '', 5, 'In Stock', 10, '2026-04-07 01:46:41'),
(14, 'STEEL KENNEL', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '3500.00', '7555.00', 'dog', '', 5, 'In Stock', 14, '2026-04-07 01:53:28');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`) VALUES
(12, 5, 'uploads/1775542067_0_pic4.webp', 0),
(13, 5, 'uploads/1775542067_1_pic3.webp', 1),
(14, 5, 'uploads/1775542067_2_pic2.webp', 2),
(15, 5, 'uploads/1775542067_3_pic1.webp', 3),
(16, 6, 'uploads/1775542135_0_pic2.4.webp', 0),
(17, 6, 'uploads/1775542135_1_pic2.3.webp', 1),
(18, 6, 'uploads/1775542135_2_pic2.2.webp', 2),
(19, 6, 'uploads/1775542135_3_pic2.1.webp', 3),
(26, 8, 'uploads/1775542493_0_pic3.6.webp', 0),
(27, 8, 'uploads/1775542493_1_pic3.5.webp', 1),
(28, 8, 'uploads/1775542493_2_pic3.4.webp', 2),
(29, 8, 'uploads/1775542493_3_pic3.3.webp', 3),
(30, 8, 'uploads/1775542493_4_pic3.2.webp', 4),
(31, 8, 'uploads/1775542493_5_pic3.1.webp', 5),
(32, 9, 'uploads/1775542545_0_pic4.4.webp', 0),
(33, 9, 'uploads/1775542545_1_pic4.3.webp', 1),
(34, 9, 'uploads/1775542545_2_pic4.2.webp', 2),
(35, 9, 'uploads/1775542545_3_pic4.1.webp', 3),
(36, 10, 'uploads/1775542635_0_pic5.4.webp', 0),
(37, 10, 'uploads/1775542635_1_pic5.3.webp', 1),
(38, 10, 'uploads/1775542635_2_pic5.2.webp', 2),
(39, 10, 'uploads/1775542635_3_pic5.1.webp', 3),
(45, 12, 'uploads/1775546201_0_pic8.5.webp', 0),
(46, 12, 'uploads/1775546201_1_pic8.4.webp', 1),
(47, 12, 'uploads/1775546201_2_pic8.3.webp', 2),
-- Constraints for table `product_images`
(48, 12, 'uploads/1775546201_3_pic8.2.webp', 3),
(49, 12, 'uploads/1775546201_4_pic8.1.webp', 4),
(56, 14, 'uploads/1775546608_0_pic7.7.webp', 0),
(57, 14, 'uploads/1775546608_1_pic7.6.webp', 1),
(58, 14, 'uploads/1775546608_2_pic7.5.webp', 2),
(59, 14, 'uploads/1775546608_3_pic7.4.webp', 3),
(60, 14, 'uploads/1775546608_4_pic7.3.webp', 4),
(61, 14, 'uploads/1775546608_5_pic7.2.webp', 5),
(62, 14, 'uploads/1775546608_6_pic7.1.webp', 6);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `created_at`) VALUES
(1, 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', '$2y$10$eAVSRLk2.z1.lnDTzi2mdeEIq.lCoXZAK0ui49NB4EfYF5xmZ8N0m', NULL, NULL, '2026-04-06 03:55:07'),
(2, 'dijo', 'dijo@gmail.com', '$2y$10$Im.j/idrHzRzEkmPRmt/TubaLq8oLfomUEMEOwO9191sFVqjzGgPO', NULL, NULL, '2026-04-06 04:26:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `hero_slides`
--
ALTER TABLE `hero_slides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hero_slides`
--
ALTER TABLE `hero_slides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
