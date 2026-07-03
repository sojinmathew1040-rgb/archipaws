-- Archipaws Database Backup
-- Generated: 2026-07-03 08:39:21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET FOREIGN_KEY_CHECKS=0;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
('1', 'admin', '$2y$10$xqaxVvJX8NQAYw1nQYvCk.UA8GHCXEuhvkqVNcv2t0EUM8NIfOuaG');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `image_path` varchar(255) DEFAULT 'assets/images/placeholder.jpg',
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `image_path`, `sort_order`) VALUES
('4', 'DOG', 'uploads/1782106746_download.jpeg', '1'),
('11', 'CAT', 'uploads/1782106766_download (1).jpeg', '2');

-- --------------------------------------------------------

--
-- Table structure for table `deal_of_the_day`
--

DROP TABLE IF EXISTS `deal_of_the_day`;
CREATE TABLE `deal_of_the_day` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `end_time` datetime NOT NULL,
  `original_price` decimal(10,2) DEFAULT 0.00,
  `original_old_price` decimal(10,2) DEFAULT NULL,
  `discount_rate` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deal_of_the_day`
--

-- --------------------------------------------------------

--
-- Table structure for table `hero_slides`
--

DROP TABLE IF EXISTS `hero_slides`;
CREATE TABLE `hero_slides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `offer_text` varchar(255) DEFAULT NULL,
  `title_line1` varchar(255) DEFAULT NULL,
  `title_line2` varchar(255) DEFAULT NULL,
  `button_text` varchar(255) DEFAULT NULL,
  `button_link` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `phone_number` varchar(100) DEFAULT NULL,
  `email_address` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hero_slides`
--

INSERT INTO `hero_slides` (`id`, `offer_text`, `title_line1`, `title_line2`, `button_text`, `button_link`, `image_path`, `sort_order`, `phone_number`, `email_address`) VALUES
('21', '', '', '', '', 'shop.php', 'uploads/1782106574_5923101-hd_1920_1080_25fps.mp4', '0', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `variation_details` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `created_at`, `variation_details`) VALUES
('4', '4', '6', '1', '5000.00', '2026-04-07 13:37:15', NULL),
('6', '6', '14', '1', '3500.00', '2026-04-10 06:27:05', NULL),
('7', '7', '6', '2', '5050.00', '2026-06-14 12:47:11', 'Color: Red, Size: Medium'),
('8', '7', '6', '1', '5060.00', '2026-06-14 12:47:11', 'Color: Blue, Size: Medium');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `shipping_address` text NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `name`, `email`, `phone`, `shipping_address`, `total_price`, `status`, `created_at`) VALUES
('4', '1', 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', '8943804920', 'kandoth house varappetty p.o kothamagaalam', '5000.00', 'Processing', '2026-04-07 13:37:15'),
('6', '1', 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', '8943804920', 'kakk', '3500.00', 'Pending', '2026-04-10 06:27:05'),
('7', '3', 'Test User', 'testuser@example.com', '1234567890', '123 Test Street, Test City', '15160.00', 'Pending', '2026-06-14 12:47:11');

-- --------------------------------------------------------

--
-- Table structure for table `product_customizations`
--

DROP TABLE IF EXISTS `product_customizations`;
CREATE TABLE `product_customizations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `customization_details` text NOT NULL,
  `status` varchar(50) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `product_customizations_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_customizations_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_customizations`
--

INSERT INTO `product_customizations` (`id`, `product_id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `customization_details`, `status`, `created_at`) VALUES
('1', '16', '1', 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', '4125588566655', 'lkankgkjarnglkjn', 'Completed', '2026-06-14 13:41:05'),
('2', '6', NULL, 'Guest Customer', 'guest@example.com', '+91 9988776655', 'Custom size 120x80cm, dark grey finish', 'Pending', '2026-06-14 13:41:21');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE `product_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  `color_value` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=106 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `sort_order`, `color_value`) VALUES
('63', '14', 'uploads/1781234108_0_pic8.5.webp', '1', NULL),
('64', '14', 'uploads/1781234108_1_pic8.4.webp', '2', NULL),
('65', '14', 'uploads/1781234108_2_pic8.3.webp', '0', NULL),
('66', '14', 'uploads/1781234108_3_pic8.2.webp', '3', NULL),
('67', '12', 'uploads/1781234188_0_pic3.2.webp', '1', NULL),
('68', '12', 'uploads/1781234188_1_pic3.1.webp', '2', NULL),
('69', '12', 'uploads/1781234188_2_pic2.4.webp', '3', NULL),
('70', '12', 'uploads/1781234188_3_pic2.3.webp', '4', NULL),
('71', '12', 'uploads/1781234188_4_pic2.2.webp', '5', NULL),
('72', '10', 'uploads/1781234237_0_pic4.webp', '1', NULL),
('73', '10', 'uploads/1781234237_1_pic3.webp', '2', NULL),
('74', '10', 'uploads/1781234237_2_pic2.webp', '3', NULL),
('75', '10', 'uploads/1781234237_3_pic1.webp', '4', NULL),
('76', '9', 'uploads/1781234269_0_pic7.5.webp', '1', NULL),
('77', '9', 'uploads/1781234269_1_pic7.4.webp', '2', NULL),
('78', '9', 'uploads/1781234269_2_pic7.2.webp', '3', NULL),
('79', '9', 'uploads/1781234269_3_pic7.1.webp', '4', NULL),
('80', '8', 'uploads/1781234454_0_pic6.4.webp', '1', NULL),
('81', '8', 'uploads/1781234454_1_pic6.3.webp', '2', NULL),
('82', '8', 'uploads/1781234454_2_pic6.2.webp', '3', NULL),
('83', '8', 'uploads/1781234454_3_pic6.1.webp', '4', NULL),
('84', '8', 'uploads/1781234454_4_pic5.4.webp', '5', NULL),
('92', '6', 'assets/images/16.jpeg', '1', NULL),
('93', '6', 'uploads/1775542067_0_pic4.webp', '2', 'Red'),
('94', '6', 'assets/images/12.jpeg', '3', 'Blue'),
('98', '16', 'uploads/1781423912_0_0_pic8.5.webp', '0', 'RED'),
('99', '16', 'uploads/1781423912_0_1_pic8.4.webp', '1', 'RED'),
('100', '16', 'uploads/1781423912_0_2_pic8.3.webp', '2', 'RED'),
('101', '16', 'uploads/1781423912_0_3_pic8.2.webp', '3', 'RED'),
('102', '16', 'uploads/1781423912_2_0_pic2.3.webp', '4', 'BLACK'),
('103', '16', 'uploads/1781423912_2_1_pic2.2.webp', '5', 'BLACK'),
('104', '16', 'uploads/1781423912_2_2_pic3.1.webp', '6', 'BLACK'),
('105', '16', 'uploads/1781423912_2_3_pic2.4.webp', '7', 'BLACK');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `review_text` text NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_reviews`
--

INSERT INTO `product_reviews` (`id`, `product_id`, `user_id`, `rating`, `review_text`, `photo_path`, `admin_reply`, `created_at`) VALUES
('3', '6', '2', '4', 'A must-have for any pet owner.', NULL, 'Thank you so much for your feedback! We\'re thrilled you like it.', '2026-05-29 10:13:31'),
('4', '8', '2', '4', 'Super cute and exactly as described.', NULL, 'Thank you so much for your feedback! We\'re thrilled you like it.', '2026-05-29 10:13:31'),
('5', '8', '2', '5', 'Elegantly designed and very durable.', NULL, 'Thank you so much for your feedback! We\'re thrilled you like it.', '2026-05-29 10:13:31'),
('6', '9', '2', '5', 'Good value for the price, but shipping was a bit slow.', NULL, NULL, '2026-05-29 10:13:31'),
('7', '9', '2', '4', 'Absolutely love this product! The quality is amazing.', NULL, 'Thank you so much for your feedback! We\'re thrilled you like it.', '2026-05-29 10:13:31'),
('8', '10', '2', '4', 'Good value for the price, but shipping was a bit slow.', NULL, NULL, '2026-05-29 10:13:31'),
('9', '10', '2', '5', 'Elegantly designed and very durable.', NULL, NULL, '2026-05-29 10:13:31'),
('10', '12', '2', '5', 'My pet won\'t stop playing with this. Highly recommended!', NULL, NULL, '2026-05-29 10:13:31'),
('11', '14', '2', '4', 'My pet won\'t stop playing with this. Highly recommended!', NULL, NULL, '2026-05-29 10:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `product_specifications`
--

DROP TABLE IF EXISTS `product_specifications`;
CREATE TABLE `product_specifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `spec_name` varchar(255) NOT NULL,
  `spec_value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_specifications`
--

INSERT INTO `product_specifications` (`id`, `product_id`, `spec_name`, `spec_value`) VALUES
('1', '16', 'weigtth', '10kg');

-- --------------------------------------------------------

--
-- Table structure for table `product_variations`
--

DROP TABLE IF EXISTS `product_variations`;
CREATE TABLE `product_variations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `variation_name` varchar(255) NOT NULL,
  `variation_value` varchar(255) NOT NULL,
  `price_modifier` decimal(10,2) DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 10,
  `height` decimal(10,2) DEFAULT NULL,
  `width` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variations`
--

INSERT INTO `product_variations` (`id`, `product_id`, `variation_name`, `variation_value`, `price_modifier`, `stock_quantity`, `height`, `width`) VALUES
('15', '15', 'Combination', 'red / s', '1500.00', '10', '15.00', '55.00'),
('16', '15', 'Combination', 'red / m', '12555.00', '10', '55.00', '55.00'),
('17', '15', 'Combination', 'green / s', '13633.00', '10', '30.00', '55.00'),
('18', '15', 'Combination', 'green / m', '15666.00', '10', '20.00', '20.00'),
('19', '15', 'Combination', 'black / s', '12255.00', '10', '15.00', '10.00'),
('20', '15', 'Combination', 'black / m', '15666.00', '10', '30.00', '25.00'),
('21', '6', 'Combination', 'Red / Medium', '-150.00', '5', '50.00', '40.00'),
('22', '6', 'Combination', 'Red / Large', '250.00', '8', '80.00', '60.00'),
('27', '16', 'Combination', 'RED / M', '100.00', '10', '150.00', '200.00'),
('28', '16', 'Combination', 'RED / L', '200.00', '10', '250.00', '300.00'),
('29', '16', 'Combination', 'BLACK / M', '100.00', '10', '150.00', '200.00'),
('30', '16', 'Combination', 'BLACK / L', '200.00', '10', '250.00', '300.00');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `old_price` decimal(10,2) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `rating` int(11) DEFAULT 5,
  `stock_status` varchar(50) DEFAULT 'In Stock',
  `stock_quantity` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_trending` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `title`, `description`, `price`, `old_price`, `category`, `badge`, `rating`, `stock_status`, `stock_quantity`, `created_at`, `is_trending`) VALUES
('6', 'WOODEN KENNELS', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '5000.00', '7000.00', 'DOG', '', '5', 'In Stock', '7', '2026-04-07 06:08:55', '1'),
('8', 'WOODEN TEAK KENNEL', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '10000.00', '12000.00', 'DOG', '', '5', 'In Stock', '19', '2026-04-07 06:14:53', '1'),
('9', 'WOODEN KENNEL', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '8200.00', '9000.00', 'DOG', '', '5', 'In Stock', '12', '2026-04-07 06:15:45', '0'),
('10', 'WOODEN KENNEL', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '6000.00', '7000.00', 'DOG', '', '5', 'In Stock', '10', '2026-04-07 06:17:15', '0'),
('12', 'WOOODEN MAHAGONI', 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum', '14000.00', '20000.00', 'DOG', '', '5', 'In Stock', '10', '2026-04-07 07:16:41', '0'),
('14', 'STEEL KENNEL', '<p><strong>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum</strong></p>', '3500.00', '7555.00', 'DOG', '', '5', 'In Stock', '14', '2026-04-07 07:23:28', '0'),
('16', 'pet house', '<p><span style=\"background-color: rgb(255, 255, 255); color: rgb(0, 0, 0);\">Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of \"de Finibus Bonorum et Malorum\" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, \"Lorem ipsum dolor sit amet..\", comes from a line in section 1.10.32.</span></p>', '0.00', NULL, 'DOG', '', '5', 'In Stock', '10', '2026-06-14 13:28:32', '1');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `quote` text NOT NULL,
  `rating` int(11) DEFAULT 5,
  `image_path` varchar(255) DEFAULT 'assets/images/user-placeholder.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `customer_name`, `quote`, `rating`, `image_path`, `created_at`) VALUES
('1', 'Sojin Mathew', 'Archipaws pet shop has the best premium kennels ever. Very happy with the quality!', '5', 'assets/images/user-placeholder.jpg', '2026-05-29 10:13:31'),
('2', 'Dijo', 'Extremely durable teak wood dog house. Five stars!', '5', 'assets/images/user-placeholder.jpg', '2026-05-29 10:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `created_at`) VALUES
('1', 'SOJIN MATHEW', 'sojinmathew1040@gmail.com', '$2y$10$eAVSRLk2.z1.lnDTzi2mdeEIq.lCoXZAK0ui49NB4EfYF5xmZ8N0m', NULL, NULL, '2026-04-06 09:25:07'),
('2', 'dijo', 'dijo@gmail.com', '$2y$10$Im.j/idrHzRzEkmPRmt/TubaLq8oLfomUEMEOwO9191sFVqjzGgPO', NULL, NULL, '2026-04-06 09:56:33'),
('3', 'Test User', 'testuser@example.com', '$2y$10$E9jD3/7XIX28bGUAsLbrSegyy3wKhxZ28sFKY676GPRhXVG6YoMGy', NULL, NULL, '2026-06-14 12:42:11');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
