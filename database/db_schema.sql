-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2025 at 02:58 AM
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
-- Database: `quotation_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bundles`
--

CREATE TABLE `bundles` (
  `bundle_id` int(11) NOT NULL,
  `bundle_type` enum('AMD','INTEL') NOT NULL,
  `processor` varchar(50) NOT NULL,
  `motherboard` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bundles`
--

INSERT INTO `bundles` (`bundle_id`, `bundle_type`, `processor`, `motherboard`, `price`) VALUES
(1, 'AMD', 'A8 7680', 'RAMSTA A88MP', 2250.00),
(2, 'AMD', 'A8 7680', 'ONDA A68', 2750.00),
(3, 'AMD', 'R3 3200G', 'BIOSTAR B450M', 6000.00),
(4, 'AMD', 'R5 2400G', 'BIOSTAR B450M', 6200.00),
(5, 'AMD', 'R5 2400G', 'WHALEKOM B450MV1', 5950.00),
(6, 'AMD', 'R5 3400G', 'WHALEKOM B450MV1', 6100.00),
(7, 'AMD', 'R5 5600GT', 'WHALEKOM B450MV1', 8900.00),
(8, 'AMD', 'R7 5700G', 'ASROCK B450M-HDV', 10900.00),
(9, 'AMD', 'R5 5600X', 'ASROCK B550M PRO 4', 11000.00),
(10, 'AMD', 'R7 5700X', 'ASROCK B550M', 12000.00),
(11, 'INTEL', 'I3 10100', 'BIOSTAR H510M', 8100.00),
(12, 'INTEL', 'I3 12100', 'ASUS H610M', 9800.00),
(13, 'INTEL', 'I5 11400', 'RAMSTA RS-H510M', 9150.00),
(14, 'INTEL', 'I5 12400', 'RAMSTA RS-H610MP', 10350.00),
(15, 'INTEL', 'I5 12400', 'ASUS H610M', 11300.00),
(16, 'INTEL', 'I5 13400', 'BIOSTAR H610M', 11450.00),
(17, 'INTEL', 'I7 12700', 'ASUS H610M', 17200.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`) VALUES
(1, 'VIDEOCARD'),
(2, 'MONITOR'),
(3, 'ACCESSORIES'),
(4, 'RAM'),
(5, 'SSD'),
(6, 'SODIMM'),
(7, 'PSU'),
(8, 'UPS'),
(9, 'CASING'),
(10, 'SOFTWARE');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_items`
--

CREATE TABLE `delivery_items` (
  `delivery_item_id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_delivered` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_items`
--

INSERT INTO `delivery_items` (`delivery_item_id`, `receipt_id`, `item_id`, `quantity_delivered`, `created_at`) VALUES
(162, 13, 163, 1.00, '2025-04-01 11:22:16'),
(163, 14, 130, 2.00, '2025-04-03 04:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_receipts`
--

CREATE TABLE `delivery_receipts` (
  `receipt_id` int(11) NOT NULL,
  `receipt_number` varchar(20) DEFAULT NULL,
  `quotation_id` int(11) NOT NULL,
  `delivery_date` date NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `recipient_position` varchar(255) DEFAULT NULL,
  `recipient_signature` text DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `delivery_status` enum('pending','in_transit','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_receipts`
--

INSERT INTO `delivery_receipts` (`receipt_id`, `receipt_number`, `quotation_id`, `delivery_date`, `recipient_name`, `recipient_position`, `recipient_signature`, `delivery_address`, `delivery_notes`, `delivery_status`, `created_at`, `updated_at`) VALUES
(5, 'DR-20250320-005', 2, '2025-03-20', 'Isabela State University', '', NULL, 'RANG-AYAN, ROXAS, ISABELA', '', 'delivered', '2025-03-20 07:43:49', '2025-03-25 01:27:29'),
(6, 'DR-20250324-006', 3, '2025-03-24', 'Isabela State University', '', NULL, 'RANG-AYAN, ROXAS, ISABELA', '', 'delivered', '2025-03-24 08:23:41', '2025-03-25 01:27:29'),
(7, 'DR-20250325-001', 3, '2025-03-24', 'Isabela State University Roxas Campus', '', NULL, 'RANG-AYAN, ROXAS, ISABELA', '', 'delivered', '2025-03-25 01:33:31', '2025-03-25 01:49:59'),
(8, 'DR-20250325-002', 2, '2025-03-20', 'Isabela State University Roxas Campus', '', NULL, 'RANG-AYAN, ROXAS, ISABELA', '', 'delivered', '2025-03-25 01:50:52', '2025-03-25 01:50:59'),
(9, 'DR-20250325-003', 3, '2025-03-25', 'Isabela State University Roxas Campus', '', NULL, 'RANG-AYAN, ROXAS, ISABELA', '', 'delivered', '2025-03-25 01:58:41', '2025-03-25 01:58:41'),
(10, 'DR-20250325-004', 3, '2025-03-25', 'Isabela State University Roxas Campus', '', NULL, 'RANG-AYAN, ROXAS, ISABELA', '', 'delivered', '2025-03-25 01:58:59', '2025-03-25 01:58:59'),
(11, 'DR-20250325-005', 3, '2025-03-24', 'Isabela State University Roxas Campus', '', NULL, 'RANG-AYAN, ROXAS, ISABELA', '', 'delivered', '2025-03-25 01:59:15', '2025-03-25 01:59:15'),
(12, 'DR-20250401-001', 10, '2025-04-01', 'Joemar Tisado', '', NULL, 'Mallig, Isabela', '', 'delivered', '2025-04-01 11:17:57', '2025-04-01 11:17:57'),
(13, 'DR-20250401-002', 10, '2025-04-01', 'Joemar Tisado', '', NULL, 'Mallig, Isabela', '', 'delivered', '2025-04-01 11:22:16', '2025-04-01 11:22:16'),
(14, 'DR-20250403-001', 8, '2025-03-26', 'Isabela State University Roxas Campus', '', NULL, 'Rang-ayan, Roxas, Isabela', '', 'delivered', '2025-04-03 04:24:51', '2025-04-03 04:24:51');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `category_id`, `item_name`, `price`) VALUES
(1, 1, 'RAMSTA GT730 2GB', 1550.00),
(2, 1, 'RAMSTA RX550 4GB', 2750.00),
(3, 1, 'RAMSTA RX580 8GB', 4900.00),
(4, 1, 'PALIT RTX3050 6GB', 10700.00),
(5, 1, 'MSI VENTUS GTX1650 DDR6 4GB', 9050.00),
(6, 1, 'GALAX RTX3060 12GB', 16300.00),
(7, 1, 'PNY PALIT RTX4060 DUAL 8GB-WHITE', 18950.00),
(8, 1, 'PNY PALIT RTX4060 VERTO 8GB DUAL FAN', 17850.00),
(9, 1, 'MSI RTX4060TI GAMING X 8GB', 26500.00),
(10, 1, 'ASROCK RX6600 8GB WHITE', 14100.00),
(11, 1, 'ASROCK RX6600 8GB BLACK', 13000.00),
(12, 2, '19\" VIEWPOINT V1900HD LED', 1500.00),
(13, 2, '20\" NVISION N200HD-V8 LED', 1750.00),
(14, 2, '20\" YGT LED', 1700.00),
(15, 2, '22\" NVISION H22V8 LED', 2220.00),
(16, 2, '22\" YGT LED', 1850.00),
(17, 2, '24\" YGT LED 75HZ', 2150.00),
(18, 2, '24\" NVISION N2410 100HZ', 3290.00),
(19, 2, '24\" VIEWPOINT VP24F1S IPS', 2800.00),
(20, 2, '24\" NVISION EG24S1 PRO 200HZ', 4700.00),
(21, 2, '25\" NVISION EG25TI 180HZ', 4550.00),
(22, 2, '27\" VIEWPOINT 120HZ CURVED WHITE', 5200.00),
(23, 2, '34\" VIEWPOINT K34WQC PRO 180HZ', 9500.00),
(24, 3, 'FAN RAINBOW', 100.00),
(25, 3, 'AVR SECURE', 250.00),
(26, 3, 'BADWOLF HEADSET', 300.00),
(27, 3, 'NEXION GK-140 COMBO', 300.00),
(28, 3, 'A4TECH USB COMBO', 500.00),
(29, 3, 'INPLAY STX240 4in1 COMBO', 550.00),
(30, 4, '8GB KINGSTON DDR3', 600.00),
(31, 4, '8GB FASPEED DDR4', 800.00),
(32, 4, '8GB HYPER X 3200', 900.00),
(33, 4, '16GB TEAM ELITE 3200', 1500.00),
(34, 5, '128GB RAMSTA 2.5 SATA', 600.00),
(35, 5, '256GB RAMSTA 2.5 SATA', 900.00),
(36, 5, '1TB WD BLUE 2.5 SATA', 3000.00),
(37, 5, '512GB TEAMGROUP 2.5 SATA', 1650.00),
(38, 5, '120GB FASPEED M.2', 750.00),
(39, 5, '240GB FASPEED M.2', 970.00),
(40, 5, '512GB APACER M.2', 1750.00),
(41, 5, '1TB TEAMGROUP M.2', 3100.00),
(42, 6, '8GB SODIMM DDR3 KINGSTON', 600.00),
(43, 6, '8GB SODIMM DDR4 KINGSTON', 850.00),
(44, 6, '16GB SODIMM KINGSTON 3200', 1250.00),
(45, 7, 'KEYTECH VP700L', 500.00),
(46, 7, 'THUNDERVOLT PSU', 550.00),
(47, 7, 'KEYTECH BTS-550 BRONZE', 1000.00),
(48, 7, 'KEYTECH BTS-650 BRONZE', 1200.00),
(49, 7, 'KEYTECH BTS-750 BRONZE', 1500.00),
(50, 8, '650VA UPS SECURE', 1300.00),
(51, 9, 'CASE W/ PSU', 750.00),
(52, 9, 'B709 BLACK', 1200.00),
(53, 9, 'STORMSTROOPER', 1000.00),
(54, 9, 'INPLAY METEOR 03 BLACK', 1000.00),
(55, 9, 'YGT N195 BLACK', 900.00),
(56, 10, 'WINDOWS 10', 1100.00),
(57, 10, 'WINDOWS 11', 1300.00);

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `quotation_id` int(11) NOT NULL,
  `quotation_number` varchar(20) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `agency_name` varchar(255) DEFAULT NULL,
  `agency_address` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `quotation_date` date NOT NULL,
  `valid_until` date DEFAULT NULL,
  `status` enum('draft','sent','accepted','rejected') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`quotation_id`, `quotation_number`, `customer_name`, `customer_email`, `customer_phone`, `agency_name`, `agency_address`, `contact_person`, `quotation_date`, `valid_until`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(2, 'QUO-20250312-002', 'ISABELA STATE UNIVERSITY - ROXAS (IICT Network Supplies)', 'procurement.roxas@isu.edu.ph', '', '', '', '', '2025-03-12', '2025-04-11', 'accepted', '', '2025-03-12 07:55:32', '2025-03-28 14:39:48'),
(3, 'QUO-20250314-003', 'ISABELA STATE UNIVERSITY - ROXAS (IICT Office Supplies)', 'procurement.roxas@isu.edu.ph', '', '', '', '', '2025-03-14', '2025-04-13', 'accepted', '', '2025-03-14 00:54:31', '2025-03-28 14:39:32'),
(5, 'QUO-20250324-005', 'ISABELA STATE UNIVERSITY - CAUAYAN', 'procurement.isu.cauayan@isu.edu.ph', '', '', '', '', '2025-03-24', '2025-04-23', 'draft', '', '2025-03-24 08:46:20', '2025-03-27 09:32:31'),
(6, 'QUO-20250327-001', 'ISABELA STATE UNIVERSITY - CAUAYAN', 'procurement.isu.cauayan@isu.edu.ph', '', '', '', '', '2025-03-27', '2025-04-26', 'draft', '', '2025-03-27 09:31:48', '2025-03-27 09:31:48'),
(7, 'QUO-20250327-002', 'ISABELA STATE UNIVERSITY - CAUAYAN', 'procurement.isu.cauayan@isu.edu.ph', '', '', '', '', '2025-03-27', '2025-04-26', 'draft', '', '2025-03-27 09:37:08', '2025-03-27 09:37:08'),
(8, 'QUO-20250328-001', 'ISABELA STATE UNIVERSITY - ROXAS (CA Maintenance Box)', 'procurement.roxas@isu.edu.ph', '', '', '', '', '2025-03-28', '2025-04-27', 'accepted', '', '2025-03-28 02:34:11', '2025-03-28 14:39:01'),
(9, 'QUO-20250328-002', 'ISABELA STATE UNIVERSITY - CAUAYAN (Sublimation Printer)', 'procurement.roxas@isu.edu.ph', '', '', '', '', '2025-03-28', '2025-04-27', 'draft', '', '2025-03-28 09:10:19', '2025-03-28 09:10:19'),
(10, 'QUO-20250401-001', 'joemar Tisado', '', '', '', '', '', '2025-04-01', '2025-05-01', 'accepted', '', '2025-04-01 11:17:10', '2025-04-01 11:17:10'),
(11, 'QUO-20250402-001', 'MUNICIPALITY OF QUEZON, NUEVA VIZCAYA (Procurement of Laptop (BPLO)', 'jcbar_quezon@yahoo.com.ph', '63-0927-3633485', '', '', '', '2025-04-02', '2025-05-02', 'draft', '', '2025-04-02 21:11:26', '2025-04-02 21:11:26'),
(12, 'QUO-20250403-001', 'ISABELA STATE UNIVERSITY - ROXAS (research printer)', 'procurement.roxas@isu.edu.ph', '', '', '', '', '2025-04-03', '2025-05-03', 'draft', '', '2025-04-03 02:45:35', '2025-04-03 03:54:20'),
(13, 'QUO-20250403-002', 'ISABELA STATE UNIVERSITY - CAUAYAN (techno ink)', 'procurement.isu.cauayan@isu.edu.ph', '', '', '', '', '2025-04-03', '2025-05-03', 'draft', '', '2025-04-03 03:31:25', '2025-04-03 03:31:25');

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `item_id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `item_no` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `description` text NOT NULL,
  `original_price` decimal(10,2) NOT NULL,
  `markup_percentage` decimal(10,2) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`item_id`, `quotation_id`, `item_no`, `quantity`, `unit`, `description`, `original_price`, `markup_percentage`, `unit_price`, `total_amount`) VALUES
(88, 7, 1, 1.00, '', 'Desktop i5 14th gen', 29000.00, 30.00, 37700.00, 37700.00),
(95, 6, 1, 1.00, 'pcs', 'Epson EcoTank L3210 A4 All-in-One Ink Tank Printer', 8500.00, 15.00, 9775.00, 9775.00),
(96, 6, 2, 4.00, 'pcs', 'Epson 001 inks black', 530.00, 15.00, 609.50, 2438.00),
(97, 6, 3, 2.00, 'pcs', 'Epson 001 inks yellow', 340.00, 15.00, 391.00, 782.00),
(98, 6, 4, 2.00, 'pcs', 'Epson 001 inks cyan', 340.00, 15.00, 391.00, 782.00),
(99, 6, 5, 2.00, 'pcs', 'Epson 001 inks magenta', 340.00, 15.00, 391.00, 782.00),
(100, 6, 6, 1.00, 'pcs', 'COMIX S6610 35L Micro Cut Heavy Duty Paper Shredder', 24970.00, 20.00, 29964.00, 29964.00),
(101, 6, 7, 2.00, '', 'Epson EcoTank L3210 A4 All-in-One Ink Tank Printer', 8500.00, 15.00, 9775.00, 19550.00),
(104, 9, 1, 1.00, '', 'EPSON L121 PRINTER WITH YASEN SUBLIMATION INK 100ML 4 COLORS SINGLE FUNCTION', 7699.50, 29.00, 9932.35, 9932.35),
(130, 8, 1, 2.00, 'pcs', 'Inkrite C9345 Maintenance Box for Epson', 308.00, 881.33, 3022.50, 6044.99),
(131, 3, 1, 1.00, 'pcs', '3 Hole Puncher', 330.00, 50.00, 495.00, 495.00),
(132, 3, 2, 10.00, 'pcs', 'Adventurer View Binder, 3-Ring Binder, 2.0&amp;amp;amp;amp;quot; - A4 Size', 318.00, 50.00, 477.00, 4770.00),
(133, 3, 3, 3.00, 'box', 'Etona Heavy Duty Staple Wire 23/6 6mm ', 133.00, 50.00, 199.50, 598.50),
(134, 3, 4, 3.00, 'box', 'Etona Heavy Duty Staple Wire 23/13 13mm', 264.00, 50.00, 396.00, 1188.00),
(135, 3, 5, 2.00, 'pcs', 'BT5000Y', 107.00, 50.00, 160.50, 321.00),
(136, 3, 6, 2.00, 'pcs', 'BT5000M', 104.00, 50.00, 156.00, 312.00),
(137, 3, 7, 2.00, 'pcs', 'BT5000C', 100.00, 50.00, 150.00, 300.00),
(138, 3, 8, 5.00, 'pcs', 'BT5000K', 116.00, 50.00, 174.00, 870.00),
(139, 3, 9, 2.00, 'pcs', 'Epson Ink 008', 1374.00, 50.00, 2061.00, 4122.00),
(140, 3, 10, 1.00, 'pcs', 'C9345 Epson Maintenance Box Chip Resetter For Epson L15150', 2015.00, 50.00, 3022.50, 3022.50),
(141, 3, 11, 1.00, 'box', 'Hard Copy Bond Paper Box Long', 995.00, 50.00, 1492.50, 1492.50),
(142, 3, 12, 2.00, 'box', 'Hard Copy Bond Paper Box a4', 900.00, 50.00, 1350.00, 2700.00),
(143, 3, 13, 5.00, 'pcs', 'Duct Tape Heavy Duty Silver 2 inches', 37.00, 50.00, 55.50, 277.50),
(144, 3, 14, 5.00, 'packs', 'Quaff Matte Sticker Paper A4', 109.00, 50.00, 163.50, 817.50),
(145, 3, 15, 5.00, 'packs', 'QUAFF Double Sided Photo Paper A4', 165.00, 50.00, 247.50, 1237.50),
(146, 2, 1, 10.00, 'pcs', 'Allan Superstore HDMI', 193.00, 100.00, 386.00, 3860.00),
(147, 2, 2, 5.00, 'pcs', '4k wireless hdmi transmitter receiver Full', 2464.00, 100.00, 4928.00, 24640.00),
(148, 2, 3, 2.00, 'pcs', 'Allan Blower', 618.00, 100.00, 1236.00, 2472.00),
(149, 2, 4, 5.00, 'pcs', 'Easy RJ45 Crimper', 336.00, 100.00, 672.00, 3360.00),
(150, 2, 5, 5.00, 'pcs', 'KEBETEME LAN Network Cable Tester', 528.00, 100.00, 1056.00, 5280.00),
(151, 2, 6, 3.00, 'pcs', 'Allan All-in-one Laser Power FTTH Fiber Optic', 649.00, 100.00, 1298.00, 3894.00),
(152, 2, 7, 3.00, 'pcs', 'ALLAN FC-6S Optical Fiber Cleaver', 587.00, 100.00, 1174.00, 3522.00),
(153, 2, 8, 3.00, 'pcs', 'ALLAN Fiber Optic Stripping Tool FTTH Fiber Optic Cable Stripper Striping', 304.00, 100.00, 608.00, 1824.00),
(154, 2, 9, 50.00, 'pcs', 'Allan Fast Connector SC UPC', 20.00, 100.00, 40.00, 2000.00),
(163, 10, 1, 1.00, 'pcs', 'AMD Ryzen 5 3400G PC Package', 25000.00, 0.00, 25000.00, 25000.00),
(164, 11, 1, 1.00, '', 'Asus TUF Gaming A15 FA507NUR-LP051W', 61145.00, 39.00, 84991.55, 84991.55),
(165, 5, 1, 1.00, 'pcs', 'RAIDMAX RX-500XT POWER SUPPLY UNIT (PSU)', 1755.00, 200.00, 5265.00, 5265.00),
(166, 5, 2, 2.00, 'pcs', 'Original M5Y1K Laptop Battery for Dell Inspiron 15', 880.00, 200.00, 2640.00, 5280.00),
(176, 13, 1, 3.00, 'pcs', 'TECHNO ECO CYAN INK', 1680.00, 50.00, 2520.00, 7560.00),
(177, 13, 2, 3.00, 'pcs', 'TECHNO ECO MAGENTA INK', 1680.00, 50.00, 2520.00, 7560.00),
(178, 13, 3, 3.00, 'pcs', 'TECHNO ECO YELLOW NK', 1680.00, 50.00, 2520.00, 7560.00),
(179, 13, 4, 5.00, 'pcs', 'TECHNO ECO BLACK NK', 1680.00, 50.00, 2520.00, 12600.00),
(180, 13, 5, 3.00, 'pcs', 'TECHNO ECO CLEANING INK', 896.00, 50.00, 1344.00, 4032.00),
(188, 12, 1, 1.00, 'pcs', 'Epson EcoTank L15150', 49695.00, 10.00, 54664.50, 54664.50),
(189, 12, 2, 10.00, '', 'Energizer AA/AAA NiMH rechargeable battery', 418.00, 20.00, 501.60, 5016.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bundles`
--
ALTER TABLE `bundles`
  ADD PRIMARY KEY (`bundle_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD PRIMARY KEY (`delivery_item_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `delivery_receipts`
--
ALTER TABLE `delivery_receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD UNIQUE KEY `idx_receipt_number` (`receipt_number`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`quotation_id`),
  ADD UNIQUE KEY `idx_quotation_number` (`quotation_number`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bundles`
--
ALTER TABLE `bundles`
  MODIFY `bundle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `delivery_items`
--
ALTER TABLE `delivery_items`
  MODIFY `delivery_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=164;

--
-- AUTO_INCREMENT for table `delivery_receipts`
--
ALTER TABLE `delivery_receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `quotation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=190;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `delivery_items`
--
ALTER TABLE `delivery_items`
  ADD CONSTRAINT `delivery_items_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `delivery_receipts` (`receipt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `delivery_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `quotation_items` (`item_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_receipts`
--
ALTER TABLE `delivery_receipts`
  ADD CONSTRAINT `delivery_receipts_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`quotation_id`) ON DELETE CASCADE;

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `items_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`quotation_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
