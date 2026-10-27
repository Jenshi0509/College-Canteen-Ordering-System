-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 27, 2026 at 05:12 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `canteen_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `generate_daily_sales` (IN `p_date` DATE)   BEGIN
    INSERT INTO Daily_Sales (sale_date, total_orders, total_revenue)
    SELECT p_date, COUNT(*), COALESCE(SUM(total_amount), 0)
      FROM Orders
     WHERE order_date = p_date AND status <> 'Cancelled'
    ON DUPLICATE KEY UPDATE
        total_orders  = VALUES(total_orders),
        total_revenue = VALUES(total_revenue);
END$$

--
-- Functions
--
CREATE DEFINER=`root`@`localhost` FUNCTION `get_next_token` (`p_date` DATE) RETURNS INT(11) READS SQL DATA BEGIN
    DECLARE t INT;
    SELECT COALESCE(MAX(token_no), 0) + 1 INTO t
      FROM Orders WHERE order_date = p_date;
    RETURN t;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `daily_sales`
--

CREATE TABLE `daily_sales` (
  `sale_date` date NOT NULL,
  `total_orders` int(11) NOT NULL DEFAULT 0,
  `total_revenue` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_sales`
--

INSERT INTO `daily_sales` (`sale_date`, `total_orders`, `total_revenue`) VALUES
('2026-10-24', 6, 515.00),
('2026-10-25', 11, 790.00),
('2026-10-27', 0, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `food_items`
--

CREATE TABLE `food_items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(50) NOT NULL,
  `category` varchar(20) NOT NULL,
  `price` decimal(6,2) NOT NULL,
  `available_qty` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `food_items`
--

INSERT INTO `food_items` (`item_id`, `item_name`, `category`, `price`, `available_qty`) VALUES
(1, 'Samosa', 'Snacks', 15.00, 56),
(2, 'Vada Pav', 'Snacks', 20.00, 56),
(3, 'Sandwich', 'Snacks', 40.00, 38),
(4, 'French Fries', 'Snacks', 50.00, 29),
(5, 'Veg Puff', 'Snacks', 25.00, 40),
(6, 'Poha', 'Breakfast', 25.00, 28),
(7, 'Idli Sambar', 'Breakfast', 40.00, 26),
(8, 'Masala Dosa', 'Meals', 60.00, 5),
(9, 'Veg Thali', 'Meals', 90.00, 37),
(10, 'Pav Bhaji', 'Meals', 70.00, 28),
(11, 'Fried Rice', 'Meals', 80.00, 24),
(12, 'Tea', 'Beverages', 10.00, 94),
(13, 'Coffee', 'Beverages', 20.00, 77),
(14, 'Cold Drink', 'Beverages', 30.00, 46),
(15, 'Ice Cream', 'Desserts', 35.00, 4);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `token_no` int(11) NOT NULL,
  `status` enum('Pending','Preparing','Ready','Collected','Cancelled') NOT NULL DEFAULT 'Pending',
  `total_amount` decimal(8,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `student_id`, `order_date`, `token_no`, `status`, `total_amount`) VALUES
(1, 1, '2026-10-24', 1, 'Collected', 50.00),
(2, 2, '2026-10-24', 2, 'Collected', 120.00),
(3, 3, '2026-10-24', 3, 'Collected', 80.00),
(4, 4, '2026-10-24', 4, 'Collected', 50.00),
(5, 5, '2026-10-24', 5, 'Collected', 105.00),
(6, 6, '2026-10-24', 6, 'Cancelled', 70.00),
(7, 7, '2026-10-24', 7, 'Collected', 110.00),
(8, 1, '2026-10-25', 1, 'Collected', 35.00),
(9, 8, '2026-10-25', 2, 'Collected', 180.00),
(10, 2, '2026-10-25', 3, 'Collected', 110.00),
(11, 9, '2026-10-25', 4, 'Ready', 60.00),
(12, 3, '2026-10-25', 5, 'Cancelled', 75.00),
(13, 10, '2026-10-25', 6, 'Cancelled', 100.00),
(14, 5, '2026-10-25', 7, 'Cancelled', 90.00),
(15, 4, '2026-10-25', 8, 'Cancelled', 155.00),
(16, 4, '2026-10-25', 9, 'Collected', 40.00),
(17, 1, '2026-10-25', 10, 'Collected', 10.00),
(18, 5, '2026-10-25', 11, 'Collected', 20.00),
(19, 1, '2026-10-25', 12, 'Pending', 150.00),
(20, 1, '2026-10-25', 13, 'Pending', 80.00),
(21, 6, '2026-10-25', 14, 'Pending', 25.00),
(22, 6, '2026-10-25', 15, 'Pending', 80.00),
(23, 1, '2026-10-27', 1, 'Cancelled', 20.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(6,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `item_id`, `quantity`, `unit_price`) VALUES
(1, 1, 1, 2, 15.00),
(2, 1, 12, 2, 10.00),
(3, 2, 9, 1, 90.00),
(4, 2, 14, 1, 30.00),
(5, 3, 8, 1, 60.00),
(6, 3, 13, 1, 20.00),
(7, 4, 2, 2, 20.00),
(8, 4, 12, 1, 10.00),
(9, 5, 10, 1, 70.00),
(10, 5, 15, 1, 35.00),
(11, 6, 3, 1, 40.00),
(12, 6, 14, 1, 30.00),
(13, 7, 11, 1, 80.00),
(14, 7, 14, 1, 30.00),
(15, 8, 6, 1, 25.00),
(16, 8, 12, 1, 10.00),
(17, 9, 9, 2, 90.00),
(18, 10, 4, 1, 50.00),
(19, 10, 14, 2, 30.00),
(20, 11, 7, 1, 40.00),
(21, 11, 13, 1, 20.00),
(22, 12, 1, 3, 15.00),
(23, 12, 12, 3, 10.00),
(24, 13, 10, 1, 70.00),
(25, 13, 14, 1, 30.00),
(26, 14, 5, 2, 25.00),
(27, 14, 13, 2, 20.00),
(28, 15, 8, 2, 60.00),
(29, 15, 15, 1, 35.00),
(30, 16, 12, 1, 10.00),
(31, 16, 1, 2, 15.00),
(32, 17, 12, 1, 10.00),
(33, 18, 13, 1, 20.00),
(34, 19, 7, 1, 40.00),
(35, 19, 10, 1, 70.00),
(36, 19, 2, 2, 20.00),
(37, 20, 7, 2, 40.00),
(38, 21, 6, 1, 25.00),
(39, 22, 3, 2, 40.00),
(40, 23, 13, 1, 20.00);

--
-- Triggers `order_items`
--
DELIMITER $$
CREATE TRIGGER `trg_after_order_item_insert` AFTER INSERT ON `order_items` FOR EACH ROW BEGIN
    UPDATE Food_Items
       SET available_qty = available_qty - NEW.quantity
     WHERE item_id = NEW.item_id;
    UPDATE Orders
       SET total_amount = total_amount + (NEW.quantity * NEW.unit_price)
     WHERE order_id = NEW.order_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `enrollment_no` varchar(15) NOT NULL,
  `name` varchar(50) NOT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `department` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `enrollment_no`, `name`, `phone`, `department`) VALUES
(1, '202403001', 'Aarav Patel', '9800000001', 'CE'),
(2, '202403002', 'Riya Shah', '9800000002', 'IT'),
(3, '202403003', 'Karan Mehta', '9800000003', 'CE'),
(4, '202403004', 'Neha Desai', '9800000004', 'CA'),
(5, '202403005', 'Dev Joshi', '9800000005', 'IT'),
(6, '202403006', 'Isha Trivedi', '9800000006', 'CE'),
(7, '202403007', 'Harsh Parmar', '9800000007', 'CA'),
(8, '202403008', 'Pooja Naik', '9800000008', 'IT'),
(9, '202403009', 'Yash Gandhi', '9800000009', 'CE'),
(10, '202403010', 'Simran Kaur', '9800000010', 'CA');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_low_stock_items`
-- (See below for the actual view)
--
CREATE TABLE `v_low_stock_items` (
`item_id` int(11)
,`item_name` varchar(50)
,`available_qty` int(11)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_todays_orders`
-- (See below for the actual view)
--
CREATE TABLE `v_todays_orders` (
`token_no` int(11)
,`student_name` varchar(50)
,`status` enum('Pending','Preparing','Ready','Collected','Cancelled')
,`total_amount` decimal(8,2)
);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_sales`
--
ALTER TABLE `daily_sales`
  ADD PRIMARY KEY (`sale_date`);

--
-- Indexes for table `food_items`
--
ALTER TABLE `food_items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `item_name` (`item_name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `uq_token_per_day` (`order_date`,`token_no`),
  ADD KEY `fk_orders_student` (`student_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `fk_oi_order` (`order_id`),
  ADD KEY `fk_oi_item` (`item_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `enrollment_no` (`enrollment_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `food_items`
--
ALTER TABLE `food_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- --------------------------------------------------------

--
-- Structure for view `v_low_stock_items`
--
DROP TABLE IF EXISTS `v_low_stock_items`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_low_stock_items`  AS SELECT `food_items`.`item_id` AS `item_id`, `food_items`.`item_name` AS `item_name`, `food_items`.`available_qty` AS `available_qty` FROM `food_items` WHERE `food_items`.`available_qty` < 10 ;

-- --------------------------------------------------------

--
-- Structure for view `v_todays_orders`
--
DROP TABLE IF EXISTS `v_todays_orders`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_todays_orders`  AS SELECT `o`.`token_no` AS `token_no`, `s`.`name` AS `student_name`, `o`.`status` AS `status`, `o`.`total_amount` AS `total_amount` FROM (`orders` `o` join `students` `s` on(`o`.`student_id` = `s`.`student_id`)) WHERE `o`.`order_date` = curdate() ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_oi_item` FOREIGN KEY (`item_id`) REFERENCES `food_items` (`item_id`),
  ADD CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
