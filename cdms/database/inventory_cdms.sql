-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2025 at 07:34 AM
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
-- Database: `inventory_cdms`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `inv_id` varchar(20) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(20) DEFAULT '''pcs''',
  `location_name` varchar(100) NOT NULL,
  `supplier` varchar(100) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_consumable` tinyint(1) DEFAULT 0,
  `archived` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `inv_id`, `item_name`, `category`, `quantity`, `unit`, `location_name`, `supplier`, `date_added`, `last_updated`, `is_consumable`, `archived`) VALUES
(96, 'CM-001', 'SnR Toilet Paper Roll', 'Cleaning Materials', 2, 'pack(s)', 'Holding Rm - Bathroom', 'n/a', '2025-06-30 23:40:04', '2025-06-30 23:40:04', 0, 0),
(97, 'CM-002', 'SnR Toilet Paper Roll', 'Cleaning Materials', 4, 'pack(s)', 'Pavilion - Bathroom', 'n/a', '2025-06-30 23:40:04', '2025-06-30 23:40:04', 0, 0),
(98, 'CM-003', 'SnR Toilet Paper Roll', 'Cleaning Materials', 3, 'pack(s)', 'Villa 1', 'n/a', '2025-06-30 23:40:04', '2025-06-30 23:40:04', 0, 0),
(99, 'CM-004', 'SnR Toilet Paper Roll', 'Cleaning Materials', 3, 'pack(s)', 'Villa 2', 'n/a', '2025-06-30 23:40:04', '2025-06-30 23:40:04', 0, 0),
(100, 'CM-005', 'SnR Toilet Paper Roll', 'Cleaning Materials', 2, 'pack(s)', 'Other', 'n/a', '2025-06-30 23:40:04', '2025-06-30 23:40:04', 0, 0),
(101, 'CM-006', 'SnR Toilet Paper Roll', 'Cleaning Materials', 1, 'pack(s)', 'Barkada 1', 'n/a', '2025-06-30 23:40:04', '2025-06-30 23:40:04', 0, 0),
(102, 'CM-007', 'SnR Toilet Paper Roll', 'Cleaning Materials', 1, 'pack(s)', 'Other', 'n/a', '2025-06-30 23:40:04', '2025-06-30 23:40:04', 0, 0),
(104, 'CM-009', 'Toilet Paper Roll Small', 'Cleaning Materials', 4, 'pcs', 'Pavilion - Bathroom', 'n/a', '2025-06-30 23:43:37', '2025-06-30 23:43:37', 0, 0),
(105, 'CM-010', 'Paper Towel', 'Cleaning Materials', 6, 'pcs', 'Pavilion - Bathroom', 'n/a', '2025-06-30 23:43:37', '2025-06-30 23:43:37', 0, 0),
(107, 'CM-012', 'Fabuloso', 'Cleaning Materials', 2, 'set(s)', 'Pavilion', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(108, 'CM-013', 'Lysol', 'Cleaning Materials', 2, 'set(s)', 'Villa 1', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(109, 'CM-014', 'Lysol', 'Cleaning Materials', 2, 'set(s)', 'Pavilion', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(110, 'CM-015', 'Mr. Muscle', 'Cleaning Materials', 2, 'set(s)', 'Villa 1', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(111, 'CM-016', 'Mr. Muscle', 'Cleaning Materials', 2, 'set(s)', 'Pavilion', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(112, 'CM-017', 'Raid', 'Cleaning Materials', 2, 'set(s)', 'Villa 1', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(113, 'CM-018', 'Raid', 'Cleaning Materials', 2, 'set(s)', 'Pavilion', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(114, 'CM-019', 'Febreeze', 'Cleaning Materials', 2, 'set(s)', 'Villa 1', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(115, 'CM-020', 'Febreeze', 'Cleaning Materials', 2, 'set(s)', 'Pavilion', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(116, 'CM-021', 'Clorox', 'Cleaning Materials', 2, 'set(s)', 'Villa 1', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(117, 'CM-022', 'Clorox', 'Cleaning Materials', 2, 'set(s)', 'Pavilion', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(118, 'CM-023', 'Powder Detergent', 'Cleaning Materials', 2, 'set(s)', 'Villa 1', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(119, 'CM-024', 'Powder Detergent', 'Cleaning Materials', 2, 'set(s)', 'Pavilion', 'n/a', '2025-06-30 23:46:51', '2025-06-30 23:46:51', 0, 0),
(129, 'FB-001', 'Baliwag', 'Food & Beverage', 40, '', 'Poolside - Cabinet', 'mr. bean', '2025-07-01 22:53:21', '2025-07-01 22:54:12', 1, 0),
(130, 'FB-002', 'Baliwag2', 'Food & Beverage', 40, '', 'Poolside - Cabinet', 'mr. bean', '2025-07-01 22:53:21', '2025-07-01 22:54:18', 1, 0),
(131, 'FB-003', 'Baliwag3', 'Food & Beverage', 40, '', 'Poolside - Cabinet', 'mr. bean', '2025-07-01 22:53:21', '2025-07-01 22:54:24', 1, 0),
(132, 'FB-004', 'Baliwag4', 'Food & Beverage', 40, '', 'Poolside - Cabinet', 'mr. bean', '2025-07-01 22:53:21', '2025-07-01 22:54:29', 1, 0),
(133, 'FB-005', 'Baliwag5', 'Food & Beverage', 40, '', 'Poolside - Cabinet', 'mr. bean', '2025-07-01 22:53:21', '2025-07-01 22:54:34', 1, 0),
(134, 'LN-001', 'linen1', 'Linens', 13, 'pcs', 'CTR - Storage', 'bornak', '2025-07-01 22:59:38', '2025-07-01 22:59:38', 0, 0),
(135, 'LN-002', 'linen2', 'Linens', 132, 'pcs', 'CTR - Storage', 'bornak', '2025-07-01 22:59:38', '2025-07-01 22:59:38', 0, 0),
(136, 'LN-003', 'linen3', 'Linens', 133, 'pcs', 'CTR - Storage', 'bornak', '2025-07-01 22:59:38', '2025-07-01 22:59:38', 0, 0),
(137, 'LN-004', 'linen4', 'Linens', 134, 'pcs', 'CTR - Storage', 'bornak', '2025-07-01 22:59:38', '2025-07-01 22:59:38', 0, 0),
(138, 'LN-005', 'linen5', 'Linens', 135, 'pcs', 'CTR - Storage', 'bornak', '2025-07-01 22:59:38', '2025-07-01 22:59:38', 0, 0),
(139, 'AP-001', 'appliances', 'Appliances', 12, 'pcs', 'Villa 1', 'mr. diy', '2025-07-01 23:02:04', '2025-07-01 23:02:04', 0, 0),
(140, 'AP-002', 'appliances2', 'Appliances', 122, 'pcs', 'Villa 1', 'mr. diy', '2025-07-01 23:02:04', '2025-07-01 23:02:04', 0, 0),
(141, 'AP-003', 'appliances3', 'Appliances', 123, 'pcs', 'Villa 1', 'mr. diy', '2025-07-01 23:02:04', '2025-07-01 23:02:04', 0, 0),
(142, 'AP-004', 'appliances4', 'Appliances', 124, 'pcs', 'Villa 1', 'mr. diy', '2025-07-01 23:02:04', '2025-07-01 23:02:04', 0, 0),
(143, 'AP-005', 'appliances5', 'Appliances', 125, 'pcs', 'Villa 1', 'mr. diy', '2025-07-01 23:02:04', '2025-07-01 23:02:04', 0, 0),
(144, 'LS-001', 'Furniture', 'Lights & Sounds', 62, 'pcs', 'Villa 2', 'boss kahoy', '2025-07-01 23:02:55', '2025-07-01 23:02:55', 0, 0),
(145, 'LS-002', 'Furniture2', 'Lights & Sounds', 622, 'pcs', 'Villa 2', 'boss kahoy', '2025-07-01 23:02:55', '2025-07-01 23:02:55', 0, 0),
(146, 'LS-003', 'Furniture3', 'Lights & Sounds', 6234, 'pcs', 'Villa 2', 'boss kahoy', '2025-07-01 23:02:55', '2025-07-01 23:02:55', 0, 0),
(147, 'LS-004', 'Furniture4', 'Lights & Sounds', 624, 'pcs', 'Villa 2', 'boss kahoy', '2025-07-01 23:02:55', '2025-07-01 23:02:55', 0, 0),
(148, 'LS-005', 'Furniture5', 'Lights & Sounds', 625, 'pcs', 'Villa 2', 'boss kahoy', '2025-07-01 23:02:55', '2025-07-01 23:02:55', 0, 0),
(149, 'LS-006', 'ilaw ng tahanan', 'Lights & Sounds', 16, 'pcs', 'Poolside - Cabinet', 'dad', '2025-07-01 23:04:38', '2025-07-01 23:04:38', 0, 0),
(150, 'LS-007', 'ilaw ng tahanan2', 'Lights & Sounds', 162, 'pcs', 'Poolside - Cabinet', 'dad', '2025-07-01 23:04:38', '2025-07-01 23:04:38', 0, 0),
(151, 'LS-008', 'ilaw ng tahanan3', 'Lights & Sounds', 163, 'pcs', 'Poolside - Cabinet', 'dad', '2025-07-01 23:04:38', '2025-07-01 23:04:38', 0, 0),
(152, 'LS-009', 'ilaw ng tahanan4', 'Lights & Sounds', 164, 'pcs', 'Poolside - Cabinet', 'dad', '2025-07-01 23:04:38', '2025-07-01 23:04:38', 0, 0),
(153, 'LS-010', 'ilaw ng tahanan5', 'Lights & Sounds', 165, 'pcs', 'Poolside - Cabinet', 'dad', '2025-07-01 23:04:38', '2025-07-01 23:04:38', 0, 0),
(154, 'TC-001', 'crafting table', 'Tables & Chairs', 12, 'box(s)', 'Pavilion', 'kumag', '2025-07-01 23:06:06', '2025-07-01 23:06:06', 0, 0),
(155, 'TC-002', 'crafting table1', 'Tables & Chairs', 122, 'box(s)', 'Pavilion', 'kumag', '2025-07-01 23:06:06', '2025-07-01 23:06:06', 0, 0),
(156, 'TC-003', 'crafting table2', 'Tables & Chairs', 123, 'box(s)', 'Pavilion', 'kumag', '2025-07-01 23:06:06', '2025-07-01 23:06:06', 0, 0),
(157, 'TC-004', 'crafting table3', 'Tables & Chairs', 124, 'box(s)', 'Pavilion', 'kumag', '2025-07-01 23:06:06', '2025-07-01 23:06:06', 0, 0),
(158, 'TC-005', 'crafting table4', 'Tables & Chairs', 125, 'box(s)', 'Pavilion', 'kumag', '2025-07-01 23:06:06', '2025-07-01 23:06:06', 0, 0),
(159, 'TC-006', 'crafting table5', 'Tables & Chairs', 126, 'box(s)', 'Pavilion', 'kumag', '2025-07-01 23:06:06', '2025-07-01 23:06:06', 0, 0),
(160, 'UT-001', 'kutsara ni kokey', 'Utensils', 50, 'pcs', 'Villa 2', 'steel wool', '2025-07-01 23:07:08', '2025-07-01 23:07:08', 0, 0),
(161, 'UT-002', 'kutsara ni kokey2', 'Utensils', 502, 'pcs', 'Villa 2', 'steel wool', '2025-07-01 23:07:08', '2025-07-01 23:07:08', 0, 0),
(162, 'UT-003', 'kutsara ni kokey3', 'Utensils', 503, 'pcs', 'Villa 2', 'steel wool', '2025-07-01 23:07:08', '2025-07-01 23:07:08', 0, 0),
(163, 'UT-004', 'kutsara ni kokey4', 'Utensils', 504, 'pcs', 'Villa 2', 'steel wool', '2025-07-01 23:07:08', '2025-07-01 23:07:08', 0, 0),
(164, 'UT-005', 'kutsara ni kokey5', 'Utensils', 505, 'pcs', 'Villa 2', 'steel wool', '2025-07-01 23:07:08', '2025-07-01 23:07:08', 0, 0),
(165, 'UT-006', 'kutsara ni kokey6', 'Utensils', 506, 'pcs', 'Villa 2', 'steel wool', '2025-07-01 23:07:08', '2025-07-01 23:07:08', 0, 0),
(168, 'UF-003', 'damit3 ni peter', 'Uniform', 261, 'pcs', 'Villa 2 - Kitchen', 'n/a', '2025-07-01 23:08:33', '2025-07-06 10:38:12', 1, 0),
(169, 'UF-004', 'damit4 ni peter', 'Uniform', 284, 'pcs', 'Villa 2 - Kitchen', 'n/a', '2025-07-01 23:08:33', '2025-07-01 23:08:33', 0, 0),
(170, 'UF-005', 'damit5 ni peter', 'Uniform', 2856, 'pcs', 'Villa 2 - Kitchen', 'n/a', '2025-07-01 23:08:33', '2025-07-01 23:08:33', 0, 0),
(171, 'SS-001', 'bornak', 'Staff Supplies', 15, 'pcs', 'Poolside - Storage', 'bornak', '2025-07-01 23:09:51', '2025-07-04 23:53:19', 0, 0),
(172, 'SS-002', 'bornak', 'Staff Supplies', 321, 'pcs', 'Barn', 'Poolside - Storage', '2025-07-01 23:09:51', '2025-07-05 23:34:59', 1, 0),
(173, 'SS-003', 'bornak', 'Staff Supplies', 15, 'pcs', 'Poolside', 'Poolside - Storage', '2025-07-01 23:09:51', '2025-07-04 23:53:23', 0, 0),
(174, 'SS-004', 'bornak', 'Staff Supplies', 10, 'pcs', 'Poolside - Storage', 'bornak', '2025-07-01 23:09:51', '2025-07-05 12:25:09', 1, 0),
(175, 'SS-005', 'bornak', 'Staff Supplies', 15, 'pcs', 'Poolside - Storage', 'bornak', '2025-07-01 23:09:51', '2025-07-04 23:53:28', 0, 0),
(207, 'FB-006', 'archive item test', 'Food & Beverage', 22, 'box(s)', 'Pavilion - Storage', 'todd', '2025-07-04 04:08:26', '2025-07-06 10:37:54', 0, 1),
(208, 'LN-006', 'arhive test 2', 'Linens', 2, 'pack(s)', 'Villa 1 - Kitchen', 'N/A', '2025-07-04 04:32:06', '2025-07-04 12:33:32', 0, 1),
(209, 'LS-011', 'arhive test 3', 'Lights & Sounds', 9, 'pcs', 'Villa 1 - Storage', 'N/A', '2025-07-04 04:32:06', '2025-07-04 12:33:28', 0, 1),
(210, 'TC-007', 'arhive test 4', 'Tables & Chairs', 4, 'meter(s)', 'Villa 1 - Storage', 'N/A', '2025-07-04 04:32:06', '2025-07-04 12:33:25', 0, 1),
(211, 'UF-006', 'arhive test 5', 'Uniform', 9, 'box(s)', 'Poolside - Storage', 'N/A', '2025-07-04 04:32:06', '2025-07-04 12:33:21', 0, 1),
(212, 'SS-006', 'arhive test 6', 'Staff Supplies', 4, 'box(s)', 'Villa 1', 'N/A', '2025-07-04 04:32:06', '2025-07-04 12:33:18', 0, 1),
(213, 'FB-007', 'test archive multiadd', 'Food & Beverage', 3, 'pcs', 'Pavilion - Bathroom', 'N/A', '2025-07-04 15:44:04', '2025-07-04 23:49:22', 0, 1),
(214, 'FB-008', 'test1 archive multiadd', 'Food & Beverage', 3, 'pcs', 'Pavilion - Bathroom', 'N/A', '2025-07-04 15:44:04', '2025-07-04 23:56:11', 0, 1),
(215, 'FB-009', 'test2 archive multiadd', 'Food & Beverage', 3, 'pcs', 'Pavilion - Bathroom', 'N/A', '2025-07-04 15:44:04', '2025-07-04 23:56:18', 0, 1),
(216, 'FB-010', 'test3 archive multiadd', 'Food & Beverage', 3, 'pcs', 'Pavilion - Bathroom', 'N/A', '2025-07-04 15:44:04', '2025-07-04 23:56:18', 0, 1),
(217, 'FB-011', 'test4 archive multiadd', 'Food & Beverage', 3, 'pcs', 'Pavilion - Bathroom', 'N/A', '2025-07-04 15:44:04', '2025-07-04 23:56:18', 0, 1),
(218, 'FB-012', 'test55 archive multiadd', 'Food & Beverage', 3, 'pcs', 'Pavilion - Bathroom', 'N/A', '2025-07-04 15:44:04', '2025-07-04 23:56:24', 0, 1);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_checklist`
--

CREATE TABLE `inventory_checklist` (
  `checklist_id` int(11) NOT NULL,
  `inv_id` varchar(20) NOT NULL,
  `status_check_in` enum('Good','Missing','Broken','Damaged') DEFAULT 'Good',
  `status_check_out` enum('Good','Missing','Broken','Damaged') DEFAULT 'Good',
  `checked_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_checklist`
--

INSERT INTO `inventory_checklist` (`checklist_id`, `inv_id`, `status_check_in`, `status_check_out`, `checked_at`) VALUES
(48, 'CM-001', 'Good', 'Good', '2025-07-01 15:45:52'),
(49, 'CM-002', 'Good', 'Good', '2025-07-01 15:45:52'),
(51, 'CM-009', 'Good', 'Good', '2025-07-01 15:45:52'),
(52, 'CM-010', 'Good', 'Good', '2025-07-01 15:45:52'),
(53, 'CM-003', 'Good', 'Missing', '2025-07-05 21:17:53'),
(54, 'CM-004', 'Missing', 'Good', '2025-07-06 19:51:21'),
(55, 'CM-005', 'Good', 'Good', '2025-07-01 15:45:52'),
(56, 'CM-007', 'Good', 'Good', '2025-07-01 15:45:52'),
(57, 'CM-006', 'Good', 'Good', '2025-07-01 15:45:52'),
(58, 'CM-013', 'Good', 'Good', '2025-07-01 21:06:53'),
(59, 'CM-015', 'Good', 'Good', '2025-07-01 21:06:53'),
(60, 'CM-017', 'Good', 'Good', '2025-07-01 21:06:53'),
(61, 'CM-019', 'Good', 'Good', '2025-07-01 21:06:53'),
(62, 'CM-021', 'Good', 'Good', '2025-07-01 21:06:53'),
(63, 'CM-023', 'Good', 'Good', '2025-07-01 21:06:53'),
(64, 'CM-012', 'Good', 'Good', '2025-07-01 21:06:53'),
(65, 'CM-014', 'Good', 'Good', '2025-07-01 21:06:53'),
(66, 'CM-016', 'Good', 'Good', '2025-07-01 21:06:53'),
(67, 'CM-018', 'Good', 'Good', '2025-07-01 21:06:53'),
(68, 'CM-020', 'Good', 'Good', '2025-07-01 21:06:53'),
(69, 'CM-022', 'Good', 'Good', '2025-07-01 21:06:53'),
(70, 'CM-024', 'Good', 'Good', '2025-07-01 21:06:53'),
(71, 'FB-007', 'Good', 'Good', '2025-07-05 08:33:28'),
(72, 'FB-008', 'Good', 'Good', '2025-07-05 08:33:28'),
(73, 'FB-009', 'Good', 'Good', '2025-07-05 08:33:28'),
(74, 'FB-010', 'Good', 'Good', '2025-07-05 08:33:28'),
(75, 'FB-011', 'Good', 'Good', '2025-07-05 08:33:28'),
(76, 'FB-012', 'Good', 'Good', '2025-07-05 08:33:28'),
(77, 'AP-001', 'Good', 'Good', '2025-07-05 08:33:28'),
(78, 'AP-002', 'Good', 'Good', '2025-07-05 08:33:28'),
(79, 'AP-003', 'Good', 'Good', '2025-07-05 08:33:28'),
(80, 'AP-004', 'Good', 'Good', '2025-07-05 08:33:28'),
(81, 'AP-005', 'Good', 'Good', '2025-07-05 08:33:28'),
(82, 'SS-006', 'Good', 'Good', '2025-07-05 08:33:28'),
(83, 'LS-001', 'Good', 'Good', '2025-07-05 08:33:28'),
(84, 'LS-002', 'Good', 'Good', '2025-07-05 08:33:28'),
(85, 'LS-003', 'Good', 'Good', '2025-07-05 08:33:28'),
(86, 'LS-004', 'Good', 'Good', '2025-07-05 08:33:28'),
(87, 'LS-005', 'Good', 'Good', '2025-07-05 08:33:28'),
(88, 'UT-001', 'Good', 'Good', '2025-07-05 08:33:28'),
(89, 'UT-002', 'Good', 'Good', '2025-07-05 08:33:28'),
(90, 'UT-003', 'Good', 'Good', '2025-07-05 08:33:28'),
(91, 'UT-004', 'Good', 'Good', '2025-07-05 08:33:28'),
(92, 'UT-005', 'Good', 'Good', '2025-07-05 08:33:28'),
(93, 'UT-006', 'Good', 'Good', '2025-07-05 08:33:28'),
(94, 'TC-001', 'Good', 'Good', '2025-07-05 08:33:28'),
(95, 'TC-002', 'Good', 'Good', '2025-07-05 08:33:28'),
(96, 'TC-003', 'Good', 'Good', '2025-07-05 08:33:28'),
(97, 'TC-004', 'Good', 'Good', '2025-07-05 08:33:28'),
(98, 'TC-005', 'Good', 'Good', '2025-07-05 08:33:28'),
(99, 'TC-006', 'Good', 'Good', '2025-07-05 08:33:28'),
(100, 'FB-001', 'Good', 'Good', '2025-07-05 08:33:28'),
(101, 'FB-002', 'Good', 'Good', '2025-07-05 08:33:28'),
(102, 'FB-003', 'Good', 'Good', '2025-07-05 08:33:28'),
(103, 'FB-004', 'Good', 'Good', '2025-07-05 08:33:28'),
(104, 'FB-005', 'Good', 'Good', '2025-07-05 08:33:28'),
(105, 'LS-006', 'Good', 'Good', '2025-07-05 08:33:29'),
(106, 'LS-007', 'Good', 'Good', '2025-07-05 08:33:29'),
(107, 'LS-008', 'Good', 'Good', '2025-07-05 08:33:29'),
(108, 'LS-009', 'Good', 'Good', '2025-07-05 08:33:29'),
(109, 'LS-010', 'Good', 'Good', '2025-07-05 08:33:29'),
(110, 'SS-002', 'Good', 'Good', '2025-07-05 08:33:29'),
(111, 'LN-001', 'Good', 'Good', '2025-07-05 08:33:29'),
(112, 'LN-002', 'Missing', 'Good', '2025-07-06 19:09:21'),
(113, 'LN-003', 'Good', 'Good', '2025-07-05 08:33:29'),
(114, 'LN-004', 'Good', 'Good', '2025-07-05 08:33:29'),
(115, 'LN-005', 'Good', 'Good', '2025-07-05 08:33:29'),
(116, 'UF-003', 'Good', 'Good', '2025-07-05 08:33:29'),
(117, 'UF-004', 'Good', 'Good', '2025-07-05 08:33:29'),
(118, 'UF-005', 'Good', 'Good', '2025-07-05 08:33:29'),
(119, 'SS-001', 'Good', 'Good', '2025-07-05 08:33:29'),
(120, 'SS-004', 'Good', 'Good', '2025-07-05 08:33:29'),
(121, 'SS-005', 'Good', 'Good', '2025-07-05 08:33:29'),
(122, 'UF-006', 'Good', 'Good', '2025-07-05 08:33:29'),
(123, 'SS-003', 'Good', 'Good', '2025-07-05 08:33:29'),
(124, 'FB-006', 'Good', 'Good', '2025-07-05 08:33:29'),
(125, 'LN-006', 'Good', 'Good', '2025-07-05 08:33:29'),
(126, 'LS-011', 'Good', 'Good', '2025-07-05 08:33:29'),
(127, 'TC-007', 'Good', 'Good', '2025-07-05 08:33:29');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_consumption_log`
--

CREATE TABLE `inventory_consumption_log` (
  `id` int(11) NOT NULL,
  `inv_id` varchar(50) NOT NULL,
  `quantity_consumed` int(11) NOT NULL,
  `consumed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `consumed_by` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_consumption_log`
--

INSERT INTO `inventory_consumption_log` (`id`, `inv_id`, `quantity_consumed`, `consumed_at`, `consumed_by`) VALUES
(1, 'XX-004', 1, '2025-06-28 00:40:14', NULL),
(2, 'XX-009', 1, '2025-06-28 15:23:05', NULL),
(3, 'CM-028', 2, '2025-06-28 21:15:16', NULL),
(4, 'UF-003', 22, '2025-07-05 08:37:16', NULL),
(5, 'SS-004', 2, '2025-07-05 20:23:16', NULL),
(6, 'SS-004', 3, '2025-07-05 20:25:09', NULL),
(7, 'SS-002', 12, '2025-07-06 15:34:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `reservation`
--

CREATE TABLE `reservation` (
  `reservation_id` int(11) NOT NULL,
  `event_type` varchar(100) NOT NULL,
  `venue_type` varchar(100) NOT NULL,
  `guest_number` int(11) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_age` int(11) DEFAULT NULL,
  `guest_sex` varchar(10) DEFAULT NULL,
  `guest_address` varchar(255) DEFAULT NULL,
  `guest_contact` varchar(50) DEFAULT NULL,
  `guest_email` varchar(100) DEFAULT NULL,
  `checkin_date` date DEFAULT NULL,
  `checkout_date` date DEFAULT NULL,
  `reservation_date` datetime DEFAULT current_timestamp(),
  `total_downpayment` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservation`
--

INSERT INTO `reservation` (`reservation_id`, `event_type`, `venue_type`, `guest_number`, `guest_name`, `guest_age`, `guest_sex`, `guest_address`, `guest_contact`, `guest_email`, `checkin_date`, `checkout_date`, `reservation_date`, `total_downpayment`, `payment_mode`) VALUES
(1, 'Birthday Party', 'Pavilion', 50, 'Juan Dela Cruz', 30, 'Male', '123 Main St, City', '09171234567', 'juan@email.com', '2025-07-10', '2025-07-12', '2025-07-05 21:56:17', 5000.00, 'Cash'),
(2, 'Inuman sesh', 'Villa 1', 2, 'Pedro Penduko', 21, 'Male', 'Burol ng Anay St. Patay na daga avenue', '0910', 'shetastelikecola', '2025-07-03', '2025-07-04', '2025-07-06 16:06:06', 29829829.00, 'Gshock'),
(3, 'Born again christian to be born again', 'Villa 2', 3, 'Maalat Gonzales', 22, 'Male', 'Buhay na tubig st. patay na lupa', '0910', 'suihduwhduwh@gmail.com', '2025-07-04', '2025-07-12', '2025-07-06 16:07:54', 910109.00, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `inv_id` (`inv_id`);

--
-- Indexes for table `inventory_checklist`
--
ALTER TABLE `inventory_checklist`
  ADD PRIMARY KEY (`checklist_id`),
  ADD KEY `fk_inventory_checklist` (`inv_id`);

--
-- Indexes for table `inventory_consumption_log`
--
ALTER TABLE `inventory_consumption_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservation`
--
ALTER TABLE `reservation`
  ADD PRIMARY KEY (`reservation_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=223;

--
-- AUTO_INCREMENT for table `inventory_checklist`
--
ALTER TABLE `inventory_checklist`
  MODIFY `checklist_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `inventory_consumption_log`
--
ALTER TABLE `inventory_consumption_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reservation`
--
ALTER TABLE `reservation`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_checklist`
--
ALTER TABLE `inventory_checklist`
  ADD CONSTRAINT `fk_inventory_checklist` FOREIGN KEY (`inv_id`) REFERENCES `inventory` (`inv_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_checklist_ibfk_1` FOREIGN KEY (`inv_id`) REFERENCES `inventory` (`inv_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
