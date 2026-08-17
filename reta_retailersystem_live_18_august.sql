-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 17, 2026 at 06:36 PM
-- Server version: 10.11.16-MariaDB-ubu2404
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reta_retailersystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `name` varchar(111) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=>active',
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=>deleted',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `user_id`, `name`, `status`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 2, 'Mr Ebby King', 1, 0, '2026-08-03 09:05:11', '2026-08-03 09:05:11');

--
-- Triggers `accounts`
--
DELIMITER $$
CREATE TRIGGER `trg_after_account_created` AFTER INSERT ON `accounts` FOR EACH ROW BEGIN

    -- Payment Types
    INSERT INTO payment_types
    (account_id, short_name, name, status, created_at, updated_at)
    VALUES
    (NEW.id, 'full', 'Full Payment', 1, NOW(), NOW()),
    (NEW.id, 'partial', 'Split Payment', 1, NOW(), NOW()),
    (NEW.id, 'credit', 'Credit Payment', 1, NOW(), NOW());

    -- Payment Methods
    INSERT INTO payment_methods
    (account_id, name, short_name, status, created_by, created_at, updated_at)
    VALUES
    (NEW.id, 'Cash', 'cash', 1, NULL, NOW(), NOW()),
    (NEW.id, 'Bank Transfer', 'transfer', 1, NULL, NOW(), NOW()),
    (NEW.id, 'Card Payment', 'card', 1, NULL, NOW(), NOW()),
    (NEW.id, 'Mobile Money', 'mobile_money', 0, NULL, NOW(), NOW()),
    (NEW.id, 'POS', 'pos', 1, NULL, NOW(), NOW()),
    (NEW.id, 'Cheque', 'cheque', 0, NULL, NOW(), NOW());

    -- Credit Durations
    INSERT INTO credit_durations
    (account_id, name, duration_days, interest, status, created_at, updated_at, created_by)
    VALUES
    (NEW.id, '7 Days', 7, 0, 1, NOW(), NOW(), NULL),
    (NEW.id, '14 Days', 14, 2, 1, NOW(), NOW(), NULL),
    (NEW.id, '30 Days', 30, 5, 1, NOW(), NOW(), NULL),
    (NEW.id, '60 Days', 60, 10, 1, NOW(), NOW(), NULL);

    -- Account Settings
    INSERT INTO account_settings
    (account_id, module, settings, created_at, updated_at)
    VALUES
    (
        NEW.id,
        'general',
        '{"tax":10,"session_timeout":5,"warning_before":30,"keep_alive":1,"author":"Manvendra Pratap Singh | Contact No: 8707643218","slogan":"Sell smarter. Manage better. Grow faster.","address":"Location: Kilometer 3, New Umuahia Road, Aba","hbphone":"07034619334","website":"www.retailersystem.com","address1":"3125 Maxwell Farm Road","address2":"Lagos Nigeria","currency":"₦","shopname":"Havana Super Market, Aba Abia State","shop_name":"Havana Super Market, Aba Abia State","pagination":20,"mainwebsite":"www.havanaworlds.com","phonenumber":"09164978999 | 09031609398","websitename":"Retailer System","emailcontact":"support@retailersystem.com","passwordlength":5,"allowafterdecimal":3,"allowdigitbeforedecimal":10}',
        NOW(),
        NOW()
    ),
    (
        NEW.id,
        'date_format',
        '{"dmy":"d-m-Y h:i A","showdate":"d-m-Y","showtime":"H:i:s","slashdmy":"d/m/Y h:i A","datepicker":"Y-m-d","slashdmyonly":"d/m/Y","showtimeAMAPM":"h:i A","dateTimepicker":"Y-m-d h:i","showtimeAMANDPM":"H:i"}',
        NOW(),
        NOW()
    ),
    (
        NEW.id,
        'inventory',
        '{"typesofinventory":{"1":"Add","2":"Sale","3":"Return","4":"Damage","5":"Deduct"},"productstockstatus":{"in_stock":"In Stock","low_stock":"Low Stock"},"requisition_status":{"0":"Cancel","1":"Active","2":"Partial Moved","3":"Complete Moved"}}',
        NOW(),
        NOW()
    );

END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_after_account_insert` AFTER INSERT ON `accounts` FOR EACH ROW BEGIN

    INSERT INTO designations
    (
        account_id,
        name,
        status,
        created_at,
        updated_at
    )
    VALUES
    (NEW.id, 'Admin', 1, NOW(), NOW()),
    (NEW.id, 'Cashier', 1, NOW(), NOW()),
    (NEW.id, 'WareHouse Manager', 1, NOW(), NOW()),
    (NEW.id, 'Supervisor', 1, NOW(), NOW());

END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `account_modules`
--

CREATE TABLE `account_modules` (
  `id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `account_modules`
--

INSERT INTO `account_modules` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'general', '2026-06-28 23:30:18', NULL),
(2, 'date_format', '2026-06-28 23:30:18', NULL),
(3, 'inventory', '2026-06-28 23:30:40', NULL),
(4, 'inventory', '2026-06-28 23:30:40', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `account_settings`
--

CREATE TABLE `account_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `module` varchar(50) NOT NULL,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_settings`
--

INSERT INTO `account_settings` (`id`, `account_id`, `module`, `settings`, `created_at`, `updated_at`) VALUES
(1, 1, 'general', '{\"tax\":\"03\",\"session_timeout\":\"5\",\"warning_before\":\"30\",\"keep_alive\":1,\"author\":\"Manvendra Pratap Singh | Contact No: 8707643218\",\"slogan\":\"Sell smarter. Manage better. Grow faster.\",\"address\":\"Location: Kilometer 3, New Umuahia Road, Aba\",\"hbphone\":\"07034619334\",\"website\":\"www.retailersystem.com\",\"address1\":\"3125 Maxwell Farm Road\",\"address2\":\"Lagos Nigeria\",\"currency\":\"\\u20a6\",\"shopname\":\"Havana Super Market, Aba Abia State\",\"shop_name\":\"Havana Super Market, Aba Abia State\",\"pagination\":\"10\",\"mainwebsite\":\"www.havanaworlds.com\",\"phonenumber\":\"09164978999 | 09031609398\",\"websitename\":\"Retailer System\",\"emailcontact\":\"support@retailersystem.com\",\"passwordlength\":5,\"allowafterdecimal\":3,\"allowdigitbeforedecimal\":10}', '2026-08-03 09:05:11', '2026-08-13 09:06:58'),
(2, 1, 'date_format', '{\"dmy\":\"d-m-Y h:i A\",\"showdate\":\"d-m-Y\",\"showtime\":\"H:i:s\",\"slashdmy\":\"d/m/Y h:i A\",\"datepicker\":\"Y-m-d\",\"slashdmyonly\":\"d/m/Y\",\"showtimeAMAPM\":\"h:i A\",\"dateTimepicker\":\"Y-m-d h:i\",\"showtimeAMANDPM\":\"H:i\"}', '2026-08-03 09:05:11', '2026-08-03 09:05:11'),
(3, 1, 'inventory', '{\"typesofinventory\":{\"1\":\"Add\",\"2\":\"Sale\",\"3\":\"Return\",\"4\":\"Damage\",\"5\":\"Deduct\"},\"productstockstatus\":{\"in_stock\":\"In Stock\",\"low_stock\":\"Low Stock\"},\"requisition_status\":{\"0\":\"Cancel\",\"1\":\"Active\",\"2\":\"Partial Moved\",\"3\":\"Complete Moved\"}}', '2026-08-03 09:05:11', '2026-08-03 09:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `account_subscription`
--

CREATE TABLE `account_subscription` (
  `id` int(11) NOT NULL,
  `account_id` bigint(20) NOT NULL,
  `subscription_id` bigint(20) NOT NULL,
  `subscription_name` varchar(100) DEFAULT NULL,
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `amount_paid` int(11) NOT NULL DEFAULT 0,
  `subscription_price` int(11) NOT NULL DEFAULT 0,
  `discount` int(11) NOT NULL DEFAULT 0,
  `is_expired` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=>expired,0=>active',
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '0=>inactive',
  `created_by` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1=>deleted'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `account_subscription`
--

INSERT INTO `account_subscription` (`id`, `account_id`, `subscription_id`, `subscription_name`, `start_date`, `end_date`, `amount_paid`, `subscription_price`, `discount`, `is_expired`, `status`, `created_by`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 1, '90 Days Trial Plan', '2026-08-03 00:00:00', '2026-11-03 00:00:00', 0, 0, 0, 0, 1, 1, '2026-08-03 09:07:10', '2026-08-03 09:07:10', 0);

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` varchar(30) DEFAULT 'Present',
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `work_hours` decimal(8,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('retailer-system-cache-account_settings_1', 'a:3:{s:11:\"date_format\";a:9:{s:3:\"dmy\";s:11:\"d-m-Y h:i A\";s:8:\"showdate\";s:5:\"d-m-Y\";s:8:\"showtime\";s:5:\"H:i:s\";s:8:\"slashdmy\";s:11:\"d/m/Y h:i A\";s:10:\"datepicker\";s:5:\"Y-m-d\";s:12:\"slashdmyonly\";s:5:\"d/m/Y\";s:13:\"showtimeAMAPM\";s:5:\"h:i A\";s:14:\"dateTimepicker\";s:9:\"Y-m-d h:i\";s:15:\"showtimeAMANDPM\";s:3:\"H:i\";}s:7:\"general\";a:19:{s:3:\"tax\";i:10;s:6:\"author\";s:47:\"Manvendra Pratap Singh | Contact No: 8707643218\";s:6:\"slogan\";s:41:\"Sell smarter. Manage better. Grow faster.\";s:7:\"address\";s:44:\"Location: Kilometer 3, New Umuahia Road, Aba\";s:7:\"hbphone\";s:11:\"07034619334\";s:7:\"website\";s:22:\"www.retailersystem.com\";s:8:\"address1\";s:22:\"3125 Maxwell Farm Road\";s:8:\"address2\";s:13:\"Lagos Nigeria\";s:8:\"currency\";s:3:\"₦\";s:8:\"shopname\";s:35:\"Havana Super Market, Aba Abia State\";s:9:\"shop_name\";s:35:\"Havana Super Market, Aba Abia State\";s:10:\"pagination\";i:20;s:11:\"mainwebsite\";s:20:\"www.havanaworlds.com\";s:11:\"phonenumber\";s:25:\"09164978999 | 09031609398\";s:11:\"websitename\";s:15:\"Retailer System\";s:12:\"emailcontact\";s:26:\"support@retailersystem.com\";s:14:\"passwordlength\";i:5;s:17:\"allowafterdecimal\";i:3;s:23:\"allowdigitbeforedecimal\";i:10;}s:9:\"inventory\";a:3:{s:16:\"typesofinventory\";a:5:{i:1;s:3:\"Add\";i:2;s:4:\"Sale\";i:3;s:6:\"Return\";i:4;s:6:\"Damage\";i:5;s:6:\"Deduct\";}s:18:\"productstockstatus\";a:2:{s:8:\"in_stock\";s:8:\"In Stock\";s:9:\"low_stock\";s:9:\"Low Stock\";}s:18:\"requisition_status\";a:4:{i:0;s:6:\"Cancel\";i:1;s:6:\"Active\";i:2;s:13:\"Partial Moved\";i:3;s:14:\"Complete Moved\";}}}', 2098320891),
('retailer-system-cache-account_settings_3', 'a:3:{s:11:\"date_format\";a:9:{s:3:\"dmy\";s:11:\"d-m-Y h:i A\";s:8:\"showdate\";s:5:\"d-m-Y\";s:8:\"showtime\";s:5:\"H:i:s\";s:8:\"slashdmy\";s:11:\"d/m/Y h:i A\";s:10:\"datepicker\";s:5:\"Y-m-d\";s:12:\"slashdmyonly\";s:5:\"d/m/Y\";s:13:\"showtimeAMAPM\";s:5:\"h:i A\";s:14:\"dateTimepicker\";s:9:\"Y-m-d h:i\";s:15:\"showtimeAMANDPM\";s:3:\"H:i\";}s:7:\"general\";a:19:{s:3:\"tax\";s:2:\"13\";s:6:\"author\";s:47:\"Manvendra Pratap Singh | Contact No: 8707643218\";s:6:\"slogan\";s:41:\"Sell smarter. Manage better. Grow faster.\";s:7:\"address\";s:44:\"Location: Kilometer 3, New Umuahia Road, Aba\";s:7:\"hbphone\";s:16:\"9898765432344444\";s:7:\"website\";s:22:\"www.retailersystem.com\";s:8:\"address1\";s:22:\"3125 Maxwell Farm Road\";s:8:\"address2\";s:13:\"Lagos Nigeria\";s:8:\"currency\";s:3:\"₦\";s:8:\"shopname\";s:35:\"Havana Super Market, Aba Abia State\";s:9:\"shop_name\";s:35:\"Havana Super Market, Aba Abia State\";s:10:\"pagination\";s:1:\"5\";s:11:\"mainwebsite\";s:20:\"www.havanaworlds.com\";s:11:\"phonenumber\";s:25:\"09164978999 | 09031609398\";s:11:\"websitename\";s:15:\"Retailer System\";s:12:\"emailcontact\";s:26:\"support@retailersystem.com\";s:14:\"passwordlength\";s:1:\"5\";s:17:\"allowafterdecimal\";s:1:\"3\";s:23:\"allowdigitbeforedecimal\";s:2:\"10\";}s:9:\"inventory\";a:3:{s:16:\"typesofinventory\";a:5:{i:1;s:3:\"Add\";i:2;s:4:\"Sale\";i:3;s:6:\"Return\";i:4;s:6:\"Damage\";i:5;s:6:\"Deduct\";}s:18:\"productstockstatus\";a:2:{s:8:\"in_stock\";s:8:\"In Stock\";s:9:\"low_stock\";s:9:\"Low Stock\";}s:18:\"requisition_status\";a:4:{i:0;s:6:\"Cancel\";i:1;s:6:\"Active\";i:2;s:13:\"Partial Moved\";i:3;s:14:\"Complete Moved\";}}}', 2100004652),
('retailer-system-management-cache-account_settings_0', 'a:0:{}', 2100775358),
('retailer-system-management-cache-account_settings_1', 'a:3:{s:7:\"general\";a:22:{s:3:\"tax\";s:2:\"03\";s:15:\"session_timeout\";s:1:\"5\";s:14:\"warning_before\";s:2:\"30\";s:10:\"keep_alive\";i:1;s:6:\"author\";s:47:\"Manvendra Pratap Singh | Contact No: 8707643218\";s:6:\"slogan\";s:41:\"Sell smarter. Manage better. Grow faster.\";s:7:\"address\";s:44:\"Location: Kilometer 3, New Umuahia Road, Aba\";s:7:\"hbphone\";s:11:\"07034619334\";s:7:\"website\";s:22:\"www.retailersystem.com\";s:8:\"address1\";s:22:\"3125 Maxwell Farm Road\";s:8:\"address2\";s:13:\"Lagos Nigeria\";s:8:\"currency\";s:3:\"₦\";s:8:\"shopname\";s:35:\"Havana Super Market, Aba Abia State\";s:9:\"shop_name\";s:35:\"Havana Super Market, Aba Abia State\";s:10:\"pagination\";s:2:\"10\";s:11:\"mainwebsite\";s:20:\"www.havanaworlds.com\";s:11:\"phonenumber\";s:25:\"09164978999 | 09031609398\";s:11:\"websitename\";s:15:\"Retailer System\";s:12:\"emailcontact\";s:26:\"support@retailersystem.com\";s:14:\"passwordlength\";i:5;s:17:\"allowafterdecimal\";i:3;s:23:\"allowdigitbeforedecimal\";i:10;}s:11:\"date_format\";a:9:{s:3:\"dmy\";s:11:\"d-m-Y h:i A\";s:8:\"showdate\";s:5:\"d-m-Y\";s:8:\"showtime\";s:5:\"H:i:s\";s:8:\"slashdmy\";s:11:\"d/m/Y h:i A\";s:10:\"datepicker\";s:5:\"Y-m-d\";s:12:\"slashdmyonly\";s:5:\"d/m/Y\";s:13:\"showtimeAMAPM\";s:5:\"h:i A\";s:14:\"dateTimepicker\";s:9:\"Y-m-d h:i\";s:15:\"showtimeAMANDPM\";s:3:\"H:i\";}s:9:\"inventory\";a:3:{s:16:\"typesofinventory\";a:5:{i:1;s:3:\"Add\";i:2;s:4:\"Sale\";i:3;s:6:\"Return\";i:4;s:6:\"Damage\";i:5;s:6:\"Deduct\";}s:18:\"productstockstatus\";a:2:{s:8:\"in_stock\";s:8:\"In Stock\";s:9:\"low_stock\";s:9:\"Low Stock\";}s:18:\"requisition_status\";a:4:{i:0;s:6:\"Cancel\";i:1;s:6:\"Active\";i:2;s:13:\"Partial Moved\";i:3;s:14:\"Complete Moved\";}}}', 2101972018),
('retailer-system-management-cache-account_settings_3', 'a:3:{s:11:\"date_format\";a:9:{s:3:\"dmy\";s:11:\"d-m-Y h:i A\";s:8:\"showdate\";s:5:\"d-m-Y\";s:8:\"showtime\";s:5:\"H:i:s\";s:8:\"slashdmy\";s:11:\"d/m/Y h:i A\";s:10:\"datepicker\";s:5:\"Y-m-d\";s:12:\"slashdmyonly\";s:5:\"d/m/Y\";s:13:\"showtimeAMAPM\";s:5:\"h:i A\";s:14:\"dateTimepicker\";s:9:\"Y-m-d h:i\";s:15:\"showtimeAMANDPM\";s:3:\"H:i\";}s:7:\"general\";a:22:{s:3:\"tax\";s:2:\"13\";s:15:\"session_timeout\";i:5;s:14:\"warning_before\";i:30;s:10:\"keep_alive\";i:1;s:6:\"author\";s:47:\"Manvendra Pratap Singh | Contact No: 8707643218\";s:6:\"slogan\";s:41:\"Sell smarter. Manage better. Grow faster.\";s:7:\"address\";s:44:\"Location: Kilometer 3, New Umuahia Road, Aba\";s:7:\"hbphone\";s:16:\"9898765432344444\";s:7:\"website\";s:22:\"www.retailersystem.com\";s:8:\"address1\";s:22:\"3125 Maxwell Farm Road\";s:8:\"address2\";s:13:\"Lagos Nigeria\";s:8:\"currency\";s:3:\"₦\";s:8:\"shopname\";s:35:\"Havana Super Market, Aba Abia State\";s:9:\"shop_name\";s:35:\"Havana Super Market, Aba Abia State\";s:10:\"pagination\";s:1:\"5\";s:11:\"mainwebsite\";s:20:\"www.havanaworlds.com\";s:11:\"phonenumber\";s:25:\"09164978999 | 09031609398\";s:11:\"websitename\";s:15:\"Retailer System\";s:12:\"emailcontact\";s:26:\"support@retailersystem.com\";s:14:\"passwordlength\";s:1:\"5\";s:17:\"allowafterdecimal\";s:1:\"3\";s:23:\"allowdigitbeforedecimal\";s:2:\"10\";}s:9:\"inventory\";a:3:{s:16:\"typesofinventory\";a:5:{i:1;s:3:\"Add\";i:2;s:4:\"Sale\";i:3;s:6:\"Return\";i:4;s:6:\"Damage\";i:5;s:6:\"Deduct\";}s:18:\"productstockstatus\";a:2:{s:8:\"in_stock\";s:8:\"In Stock\";s:9:\"low_stock\";s:9:\"Low Stock\";}s:18:\"requisition_status\";a:4:{i:0;s:6:\"Cancel\";i:1;s:6:\"Active\";i:2;s:13:\"Partial Moved\";i:3;s:14:\"Complete Moved\";}}}', 2100429438),
('retailer-system-management-cache-account_settings_7', 'a:3:{s:11:\"date_format\";a:9:{s:3:\"dmy\";s:11:\"d-m-Y h:i A\";s:8:\"showdate\";s:5:\"d-m-Y\";s:8:\"showtime\";s:5:\"H:i:s\";s:8:\"slashdmy\";s:11:\"d/m/Y h:i A\";s:10:\"datepicker\";s:5:\"Y-m-d\";s:12:\"slashdmyonly\";s:5:\"d/m/Y\";s:13:\"showtimeAMAPM\";s:5:\"h:i A\";s:14:\"dateTimepicker\";s:9:\"Y-m-d h:i\";s:15:\"showtimeAMANDPM\";s:3:\"H:i\";}s:7:\"general\";a:22:{s:3:\"tax\";i:10;s:15:\"session_timeout\";i:5;s:14:\"warning_before\";i:30;s:10:\"keep_alive\";i:1;s:6:\"author\";s:47:\"Manvendra Pratap Singh | Contact No: 8707643218\";s:6:\"slogan\";s:41:\"Sell smarter. Manage better. Grow faster.\";s:7:\"address\";s:44:\"Location: Kilometer 3, New Umuahia Road, Aba\";s:7:\"hbphone\";s:11:\"07034619334\";s:7:\"website\";s:22:\"www.retailersystem.com\";s:8:\"address1\";s:22:\"3125 Maxwell Farm Road\";s:8:\"address2\";s:13:\"Lagos Nigeria\";s:8:\"currency\";s:3:\"₦\";s:8:\"shopname\";s:35:\"Havana Super Market, Aba Abia State\";s:9:\"shop_name\";s:35:\"Havana Super Market, Aba Abia State\";s:10:\"pagination\";i:20;s:11:\"mainwebsite\";s:20:\"www.havanaworlds.com\";s:11:\"phonenumber\";s:25:\"09164978999 | 09031609398\";s:11:\"websitename\";s:15:\"Retailer System\";s:12:\"emailcontact\";s:26:\"support@retailersystem.com\";s:14:\"passwordlength\";i:5;s:17:\"allowafterdecimal\";i:3;s:23:\"allowdigitbeforedecimal\";i:10;}s:9:\"inventory\";a:3:{s:16:\"typesofinventory\";a:5:{i:1;s:3:\"Add\";i:2;s:4:\"Sale\";i:3;s:6:\"Return\";i:4;s:6:\"Damage\";i:5;s:6:\"Deduct\";}s:18:\"productstockstatus\";a:2:{s:8:\"in_stock\";s:8:\"In Stock\";s:9:\"low_stock\";s:9:\"Low Stock\";}s:18:\"requisition_status\";a:4:{i:0;s:6:\"Cancel\";i:1;s:6:\"Active\";i:2;s:13:\"Partial Moved\";i:3;s:14:\"Complete Moved\";}}}', 2101098002);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `status` tinyint(1) DEFAULT 1,
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `created_by` bigint(20) NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `account_id`, `name`, `slug`, `description`, `image`, `status`, `is_deleted`, `created_at`, `created_by`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Televisions', 'televisions', 'Smart TVs, LED TVs and accessories', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(2, 1, 'Air Conditioners', 'air-conditioners', 'Split and window AC units', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(3, 1, 'Refrigerators', 'refrigerators', 'Single door, double door and freezers', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(4, 1, 'Washing Machines', 'washing-machines', 'Automatic and semi-automatic washing machines', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(5, 1, 'Kitchen Appliances', 'kitchen-appliances', 'Microwave ovens, blenders, kettles and more', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(6, 1, 'Gas Cookers', 'gas-cookers', 'Gas stoves and cooking appliances', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(7, 1, 'Home Theatre & Audio', 'home-theatre-audio', 'Speakers, sound bars and home theatre systems', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(8, 1, 'Mobile Phones', 'mobile-phones', 'Smartphones and feature phones', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(9, 1, 'Tablets', 'tablets', 'Android and iOS tablets', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(10, 1, 'Laptops', 'laptops', 'Laptops and notebooks', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(11, 1, 'Computer Accessories', 'computer-accessories', 'Keyboards, mouse, monitors and peripherals', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(12, 1, 'Networking Devices', 'networking-devices', 'Routers, switches and networking equipment', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(13, 1, 'Printers & Scanners', 'printers-scanners', 'Office printing and scanning devices', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(14, 1, 'Power Solutions', 'power-solutions', 'UPS, inverters and power banks', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(15, 1, 'Generators', 'generators', 'Portable and industrial generators', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(16, 1, 'Solar Products', 'solar-products', 'Solar panels and solar accessories', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(17, 1, 'Security Systems', 'security-systems', 'CCTV cameras and surveillance equipment', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(18, 1, 'Water Dispensers', 'water-dispensers', 'Hot and cold water dispensers', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(19, 1, 'Fans', 'fans', 'Ceiling, standing and table fans', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL),
(20, 1, 'Accessories', 'accessories', 'Cables, chargers, adapters and miscellaneous items', NULL, 1, 0, '2026-06-16 10:32:31', 1, '2026-06-16 10:32:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `organization` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Pending,1=Read,2=Replied',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `countries`
--

CREATE TABLE `countries` (
  `id` int(11) NOT NULL,
  `country_code` varchar(2) NOT NULL DEFAULT '',
  `country_name` varchar(100) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `countries`
--

INSERT INTO `countries` (`id`, `country_code`, `country_name`) VALUES
(1, 'AF', 'Afghanistan'),
(2, 'AL', 'Albania'),
(3, 'DZ', 'Algeria'),
(4, 'AS', 'American Samoa'),
(5, 'AD', 'Andorra'),
(6, 'AO', 'Angola'),
(7, 'AI', 'Anguilla'),
(8, 'AQ', 'Antarctica'),
(9, 'AG', 'Antigua and Barbuda'),
(10, 'AR', 'Argentina'),
(11, 'AM', 'Armenia'),
(12, 'AW', 'Aruba'),
(13, 'AU', 'Australia'),
(14, 'AT', 'Austria'),
(15, 'AZ', 'Azerbaijan'),
(16, 'BS', 'Bahamas'),
(17, 'BH', 'Bahrain'),
(18, 'BD', 'Bangladesh'),
(19, 'BB', 'Barbados'),
(20, 'BY', 'Belarus'),
(21, 'BE', 'Belgium'),
(22, 'BZ', 'Belize'),
(23, 'BJ', 'Benin'),
(24, 'BM', 'Bermuda'),
(25, 'BT', 'Bhutan'),
(26, 'BO', 'Bolivia'),
(27, 'BA', 'Bosnia and Herzegovina'),
(28, 'BW', 'Botswana'),
(29, 'BV', 'Bouvet Island'),
(30, 'BR', 'Brazil'),
(31, 'IO', 'British Indian Ocean Territory'),
(32, 'BN', 'Brunei Darussalam'),
(33, 'BG', 'Bulgaria'),
(34, 'BF', 'Burkina Faso'),
(35, 'BI', 'Burundi'),
(36, 'KH', 'Cambodia'),
(37, 'CM', 'Cameroon'),
(38, 'CA', 'Canada'),
(39, 'CV', 'Cape Verde'),
(40, 'KY', 'Cayman Islands'),
(41, 'CF', 'Central African Republic'),
(42, 'TD', 'Chad'),
(43, 'CL', 'Chile'),
(44, 'CN', 'China'),
(45, 'CX', 'Christmas Island'),
(46, 'CC', 'Cocos (Keeling) Islands'),
(47, 'CO', 'Colombia'),
(48, 'KM', 'Comoros'),
(49, 'CD', 'Democratic Republic of the Congo'),
(50, 'CG', 'Republic of Congo'),
(51, 'CK', 'Cook Islands'),
(52, 'CR', 'Costa Rica'),
(53, 'HR', 'Croatia (Hrvatska)'),
(54, 'CU', 'Cuba'),
(55, 'CY', 'Cyprus'),
(56, 'CZ', 'Czech Republic'),
(57, 'DK', 'Denmark'),
(58, 'DJ', 'Djibouti'),
(59, 'DM', 'Dominica'),
(60, 'DO', 'Dominican Republic'),
(61, 'TL', 'East Timor'),
(62, 'EC', 'Ecuador'),
(63, 'EG', 'Egypt'),
(64, 'SV', 'El Salvador'),
(65, 'GQ', 'Equatorial Guinea'),
(66, 'ER', 'Eritrea'),
(67, 'EE', 'Estonia'),
(68, 'ET', 'Ethiopia'),
(69, 'FK', 'Falkland Islands (Malvinas)'),
(70, 'FO', 'Faroe Islands'),
(71, 'FJ', 'Fiji'),
(72, 'FI', 'Finland'),
(73, 'FR', 'France'),
(74, 'FX', 'France, Metropolitan'),
(75, 'GF', 'French Guiana'),
(76, 'PF', 'French Polynesia'),
(77, 'TF', 'French Southern Territories'),
(78, 'GA', 'Gabon'),
(79, 'GM', 'Gambia'),
(80, 'GE', 'Georgia'),
(81, 'DE', 'Germany'),
(82, 'GH', 'Ghana'),
(83, 'GI', 'Gibraltar'),
(84, 'GG', 'Guernsey'),
(85, 'GR', 'Greece'),
(86, 'GL', 'Greenland'),
(87, 'GD', 'Grenada'),
(88, 'GP', 'Guadeloupe'),
(89, 'GU', 'Guam'),
(90, 'GT', 'Guatemala'),
(91, 'GN', 'Guinea'),
(92, 'GW', 'Guinea-Bissau'),
(93, 'GY', 'Guyana'),
(94, 'HT', 'Haiti'),
(95, 'HM', 'Heard and Mc Donald Islands'),
(96, 'HN', 'Honduras'),
(97, 'HK', 'Hong Kong'),
(98, 'HU', 'Hungary'),
(99, 'IS', 'Iceland'),
(100, 'IN', 'India'),
(101, 'IM', 'Isle of Man'),
(102, 'ID', 'Indonesia'),
(103, 'IR', 'Iran (Islamic Republic of)'),
(104, 'IQ', 'Iraq'),
(105, 'IE', 'Ireland'),
(106, 'IL', 'Israel'),
(107, 'IT', 'Italy'),
(108, 'CI', 'Ivory Coast'),
(109, 'JE', 'Jersey'),
(110, 'JM', 'Jamaica'),
(111, 'JP', 'Japan'),
(112, 'JO', 'Jordan'),
(113, 'KZ', 'Kazakhstan'),
(114, 'KE', 'Kenya'),
(115, 'KI', 'Kiribati'),
(116, 'KP', 'Korea, Democratic People\'s Republic of'),
(117, 'KR', 'Korea, Republic of'),
(118, 'XK', 'Kosovo'),
(119, 'KW', 'Kuwait'),
(120, 'KG', 'Kyrgyzstan'),
(121, 'LA', 'Lao People\'s Democratic Republic'),
(122, 'LV', 'Latvia'),
(123, 'LB', 'Lebanon'),
(124, 'LS', 'Lesotho'),
(125, 'LR', 'Liberia'),
(126, 'LY', 'Libyan Arab Jamahiriya'),
(127, 'LI', 'Liechtenstein'),
(128, 'LT', 'Lithuania'),
(129, 'LU', 'Luxembourg'),
(130, 'MO', 'Macau'),
(131, 'MK', 'North Macedonia'),
(132, 'MG', 'Madagascar'),
(133, 'MW', 'Malawi'),
(134, 'MY', 'Malaysia'),
(135, 'MV', 'Maldives'),
(136, 'ML', 'Mali'),
(137, 'MT', 'Malta'),
(138, 'MH', 'Marshall Islands'),
(139, 'MQ', 'Martinique'),
(140, 'MR', 'Mauritania'),
(141, 'MU', 'Mauritius'),
(142, 'YT', 'Mayotte'),
(143, 'MX', 'Mexico'),
(144, 'FM', 'Micronesia, Federated States of'),
(145, 'MD', 'Moldova, Republic of'),
(146, 'MC', 'Monaco'),
(147, 'MN', 'Mongolia'),
(148, 'ME', 'Montenegro'),
(149, 'MS', 'Montserrat'),
(150, 'MA', 'Morocco'),
(151, 'MZ', 'Mozambique'),
(152, 'MM', 'Myanmar'),
(153, 'NA', 'Namibia'),
(154, 'NR', 'Nauru'),
(155, 'NP', 'Nepal'),
(156, 'NL', 'Netherlands'),
(157, 'AN', 'Netherlands Antilles'),
(158, 'NC', 'New Caledonia'),
(159, 'NZ', 'New Zealand'),
(160, 'NI', 'Nicaragua'),
(161, 'NE', 'Niger'),
(162, 'NG', 'Nigeria'),
(163, 'NU', 'Niue'),
(164, 'NF', 'Norfolk Island'),
(165, 'MP', 'Northern Mariana Islands'),
(166, 'NO', 'Norway'),
(167, 'OM', 'Oman'),
(168, 'PK', 'Pakistan'),
(169, 'PW', 'Palau'),
(170, 'PS', 'Palestine'),
(171, 'PA', 'Panama'),
(172, 'PG', 'Papua New Guinea'),
(173, 'PY', 'Paraguay'),
(174, 'PE', 'Peru'),
(175, 'PH', 'Philippines'),
(176, 'PN', 'Pitcairn'),
(177, 'PL', 'Poland'),
(178, 'PT', 'Portugal'),
(179, 'PR', 'Puerto Rico'),
(180, 'QA', 'Qatar'),
(181, 'RE', 'Reunion'),
(182, 'RO', 'Romania'),
(183, 'RU', 'Russian Federation'),
(184, 'RW', 'Rwanda'),
(185, 'KN', 'Saint Kitts and Nevis'),
(186, 'LC', 'Saint Lucia'),
(187, 'VC', 'Saint Vincent and the Grenadines'),
(188, 'WS', 'Samoa'),
(189, 'SM', 'San Marino'),
(190, 'ST', 'Sao Tome and Principe'),
(191, 'SA', 'Saudi Arabia'),
(192, 'SN', 'Senegal'),
(193, 'RS', 'Serbia'),
(194, 'SC', 'Seychelles'),
(195, 'SL', 'Sierra Leone'),
(196, 'SG', 'Singapore'),
(197, 'SK', 'Slovakia'),
(198, 'SI', 'Slovenia'),
(199, 'SB', 'Solomon Islands'),
(200, 'SO', 'Somalia'),
(201, 'ZA', 'South Africa'),
(202, 'GS', 'South Georgia South Sandwich Islands'),
(203, 'SS', 'South Sudan'),
(204, 'ES', 'Spain'),
(205, 'LK', 'Sri Lanka'),
(206, 'SH', 'St. Helena'),
(207, 'PM', 'St. Pierre and Miquelon'),
(208, 'SD', 'Sudan'),
(209, 'SR', 'Suriname'),
(210, 'SJ', 'Svalbard and Jan Mayen Islands'),
(211, 'SZ', 'Eswatini'),
(212, 'SE', 'Sweden'),
(213, 'CH', 'Switzerland'),
(214, 'SY', 'Syrian Arab Republic'),
(215, 'TW', 'Taiwan'),
(216, 'TJ', 'Tajikistan'),
(217, 'TZ', 'Tanzania, United Republic of'),
(218, 'TH', 'Thailand'),
(219, 'TG', 'Togo'),
(220, 'TK', 'Tokelau'),
(221, 'TO', 'Tonga'),
(222, 'TT', 'Trinidad and Tobago'),
(223, 'TN', 'Tunisia'),
(224, 'TR', 'Turkey'),
(225, 'TM', 'Turkmenistan'),
(226, 'TC', 'Turks and Caicos Islands'),
(227, 'TV', 'Tuvalu'),
(228, 'UG', 'Uganda'),
(229, 'UA', 'Ukraine'),
(230, 'AE', 'United Arab Emirates'),
(231, 'GB', 'United Kingdom'),
(232, 'US', 'United States'),
(233, 'UM', 'United States minor outlying islands'),
(234, 'UY', 'Uruguay'),
(235, 'UZ', 'Uzbekistan'),
(236, 'VU', 'Vanuatu'),
(237, 'VA', 'Vatican City State'),
(238, 'VE', 'Venezuela'),
(239, 'VN', 'Vietnam'),
(240, 'VG', 'Virgin Islands (British)'),
(241, 'VI', 'Virgin Islands (U.S.)'),
(242, 'WF', 'Wallis and Futuna Islands'),
(243, 'EH', 'Western Sahara'),
(244, 'YE', 'Yemen'),
(245, 'ZM', 'Zambia'),
(246, 'ZW', 'Zimbabwe');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `type` enum('flat','percent') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_amount` decimal(10,2) DEFAULT NULL,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `expires_at` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `account_id`, `code`, `type`, `value`, `min_amount`, `max_discount`, `expires_at`, `is_active`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 1, '100OFF', 'flat', 1000.00, 100.00, NULL, '2028-08-15', 1, 0, '2026-08-15 11:38:07', '2026-08-15 11:38:07'),
(2, 1, '7 DAYS', 'flat', 1000.00, 1000.00, NULL, '2028-08-16', 1, 0, '2026-08-16 05:35:07', '2026-08-16 05:35:07');

-- --------------------------------------------------------

--
-- Table structure for table `credit_durations`
--

CREATE TABLE `credit_durations` (
  `id` bigint(20) NOT NULL,
  `account_id` bigint(20) NOT NULL,
  `name` varchar(50) NOT NULL,
  `duration_days` int(11) NOT NULL DEFAULT 0,
  `interest` int(11) NOT NULL DEFAULT 0,
  `status` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `credit_durations`
--

INSERT INTO `credit_durations` (`id`, `account_id`, `name`, `duration_days`, `interest`, `status`, `created_at`, `updated_at`, `created_by`) VALUES
(1, 1, '7 Days', 7, 2, 1, '2026-08-16 11:05:54', '2026-08-16 11:05:54', 2),
(2, 1, '15 Days', 15, 3, 1, '2026-08-16 11:06:08', '2026-08-16 11:06:08', 2);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `wallet_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `account_id`, `name`, `phone`, `email`, `wallet_balance`, `status`, `created_at`, `updated_at`, `is_deleted`) VALUES
(1, 1, 'manvendra', '8707643218', 'manvendra@gmail.com', 4400.00, 1, '2026-08-16 06:05:45', '2026-08-16 06:12:24', 0);

-- --------------------------------------------------------

--
-- Table structure for table `default_site_configs`
--

CREATE TABLE `default_site_configs` (
  `id` int(10) UNSIGNED NOT NULL,
  `account_id` bigint(20) NOT NULL,
  `web_name` varchar(255) DEFAULT 'Vintage System',
  `site_title` varchar(255) DEFAULT 'Vintage System',
  `about_company` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` text DEFAULT NULL,
  `country_name` varchar(255) DEFAULT NULL,
  `phone` text NOT NULL,
  `facebook` varchar(255) NOT NULL,
  `twitter` varchar(255) NOT NULL,
  `linkedin` varchar(255) NOT NULL,
  `instagram` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `location` longtext DEFAULT NULL,
  `default_password` varchar(15) NOT NULL DEFAULT '11111111',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=>active,0=>inactive',
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `account_id`, `name`, `created_at`, `status`, `is_deleted`, `updated_at`) VALUES
(1, 1, 'Admin', '2026-08-03 14:35:11', 1, 0, '2026-08-03 14:35:11'),
(2, 1, 'Cashier', '2026-08-03 14:35:11', 1, 0, '2026-08-03 14:35:11'),
(3, 1, 'WareHouse Manager', '2026-08-03 14:35:11', 1, 0, '2026-08-03 14:35:11'),
(4, 1, 'Supervisor', '2026-08-03 14:35:11', 1, 0, '2026-08-03 14:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `designation_permissions`
--

CREATE TABLE `designation_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `designation_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `designation_route`
--

CREATE TABLE `designation_route` (
  `designation_id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) DEFAULT NULL,
  `route_id` bigint(20) UNSIGNED NOT NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designation_route`
--

INSERT INTO `designation_route` (`designation_id`, `account_id`, `route_id`, `is_allowed`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 2, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 3, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 4, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 5, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 6, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 7, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 8, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 10, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 11, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 12, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 13, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 14, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 17, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 19, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 20, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 21, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 22, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 23, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 24, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 25, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 26, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 27, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 28, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 29, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 30, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 31, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 32, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 33, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 34, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 35, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 36, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 37, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 38, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 39, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 40, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 41, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 42, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 43, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 44, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 45, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 46, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 47, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 48, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 49, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 50, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 51, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 52, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 53, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 54, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 55, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 56, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 57, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 58, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 59, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 60, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 61, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 62, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 63, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 64, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 65, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 66, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 67, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 68, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 69, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 70, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 71, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 72, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 73, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 74, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 75, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 76, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 77, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 78, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 79, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 80, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 81, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 82, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 83, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 84, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 85, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 86, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 87, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 88, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 89, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 90, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 91, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 92, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 93, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 94, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 95, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 96, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 97, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 98, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 99, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 100, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 101, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 102, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 103, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 104, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 105, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 106, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 107, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 108, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 109, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 110, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 111, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 112, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 113, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 114, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 115, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 116, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 117, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 118, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 119, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 120, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 121, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 122, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 123, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 124, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 125, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 126, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 127, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 128, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 129, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 130, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 131, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 132, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 133, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 134, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 135, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 136, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 137, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 138, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 139, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 140, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 141, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 142, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 144, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 145, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 146, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 147, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 148, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 149, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 150, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 151, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 152, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 153, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 154, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 155, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 156, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 157, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 158, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 159, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 160, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 161, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 162, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 163, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 164, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 165, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 166, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 167, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 168, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 169, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 170, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 171, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 172, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 173, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 174, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 175, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 176, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 177, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 178, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 179, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 180, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 181, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 182, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 183, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 184, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 185, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 186, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 187, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 189, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 190, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 191, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 192, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 193, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 195, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 196, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 197, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 198, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 199, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 200, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 201, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 202, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 203, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 204, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 205, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 206, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 207, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 208, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 209, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 210, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 211, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 212, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 213, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 214, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 225, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 226, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 227, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 228, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 229, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 230, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 231, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 232, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 233, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 234, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 235, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 236, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 237, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 238, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 239, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 240, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 241, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 242, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 243, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 244, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 245, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 246, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 247, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 248, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 249, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 250, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 251, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 252, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 253, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 254, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 255, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 256, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 257, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 258, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 259, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 260, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 261, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 262, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 263, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 264, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 265, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 266, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 267, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 268, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 269, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 270, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 271, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 272, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 273, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 274, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 275, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 276, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 277, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 278, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 279, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 280, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 281, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 282, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 283, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 284, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 285, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 286, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 287, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 288, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 289, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 290, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 291, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 292, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 293, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 294, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 295, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 296, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 297, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 298, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 299, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 300, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 301, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 302, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 303, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 304, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 305, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 306, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 307, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 308, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 309, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 310, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 311, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 312, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 313, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 314, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 315, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 316, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 317, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 318, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 321, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 323, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 324, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 325, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 326, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 327, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(1, 1, 328, 1, '2026-08-03 05:46:54', '2026-08-03 05:46:54'),
(1, 1, 329, 1, '2026-08-05 11:40:28', '2026-08-05 11:40:28'),
(1, 1, 330, 1, '2026-08-05 11:40:28', '2026-08-05 11:40:28'),
(1, 1, 331, 1, '2026-08-05 12:23:36', '2026-08-05 12:23:36'),
(1, 1, 332, 1, '2026-08-05 12:23:36', '2026-08-05 12:23:36'),
(1, 1, 333, 1, '2026-08-07 08:18:15', '2026-08-07 08:18:15'),
(1, 1, 334, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(1, 1, 335, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(1, 1, 336, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(1, 1, 337, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(1, 1, 338, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(1, 1, 339, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(1, 1, 340, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(1, 1, 341, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(1, 1, 342, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(1, 1, 343, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(1, 1, 344, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(1, 1, 345, 1, '2026-08-11 06:44:54', '2026-08-11 06:44:54'),
(1, 1, 346, 1, '2026-08-11 06:44:54', '2026-08-11 06:44:54'),
(1, 1, 347, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(1, 1, 348, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(1, 1, 349, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(2, 1, 1, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 2, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 3, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 4, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 5, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 6, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 7, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 8, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 10, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 11, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 12, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 13, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 14, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 17, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 19, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 20, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 21, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 22, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 23, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 24, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 25, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 26, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 27, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 28, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 29, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 30, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 31, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 32, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 33, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 34, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 35, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 36, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 37, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 38, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 39, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 40, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 41, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 42, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 43, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 44, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 45, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 46, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 47, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 48, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 49, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 50, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 51, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 52, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 53, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 54, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 55, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 56, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 57, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 58, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 59, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 60, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 61, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 62, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 63, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 64, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 65, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 66, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 67, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 68, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 69, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 70, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 71, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 72, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 73, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 74, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 75, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 76, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 77, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 78, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 79, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 80, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 81, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 82, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 83, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 84, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 85, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 86, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 87, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 88, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 89, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 90, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 91, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 92, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 93, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 94, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 95, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 96, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 97, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 98, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 99, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 100, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 101, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 102, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 103, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 104, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 105, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 106, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 107, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 108, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 109, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 110, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 111, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 112, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 113, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 114, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 115, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 116, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 117, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 118, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 119, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 120, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 121, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 122, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 123, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 124, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 125, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 126, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 127, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 128, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 129, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 130, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 131, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 132, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 133, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 134, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 135, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 136, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 137, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 138, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 139, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 140, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 141, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 142, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 144, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 145, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 146, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 147, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 148, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 149, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 150, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 151, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 152, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 153, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 154, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 155, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 156, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 157, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 158, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 159, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 160, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 161, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 162, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 163, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 164, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 165, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 166, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 167, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 168, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 169, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 170, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 171, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 172, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 173, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 174, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 175, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 176, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 177, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 178, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 179, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 180, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 181, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 182, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 183, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 184, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 185, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 186, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 187, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 189, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 190, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 191, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 192, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 193, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 195, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 196, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 197, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 198, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 199, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 200, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 201, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 202, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 203, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 204, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 205, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 206, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 207, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 208, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 209, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 210, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 211, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 212, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 213, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 214, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 225, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 226, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 227, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 228, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 229, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 230, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 231, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 232, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 233, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 234, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 235, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 236, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 237, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 238, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 239, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 240, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 241, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 242, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 243, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 244, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 245, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 246, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 247, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 248, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 249, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 250, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 251, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 252, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 253, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 254, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 255, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 256, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 257, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 258, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 259, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 260, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 261, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 262, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 263, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 264, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 265, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 266, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 267, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 268, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 269, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 270, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 271, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 272, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 273, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 274, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 275, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 276, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 277, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 278, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 279, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 280, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 281, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 282, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 283, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 284, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 285, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 286, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 287, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 288, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 289, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 290, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 291, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 292, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 293, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 294, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 295, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 296, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 297, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 298, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 299, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 300, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 301, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 302, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 303, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 304, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 305, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 306, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 307, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 308, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 309, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 310, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 311, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 312, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 313, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 314, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 315, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 316, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 317, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 318, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 321, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 323, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 324, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 325, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 326, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 327, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(2, 1, 328, 1, '2026-08-03 05:46:54', '2026-08-03 05:46:54'),
(2, 1, 329, 1, '2026-08-05 11:40:28', '2026-08-05 11:40:28'),
(2, 1, 330, 1, '2026-08-05 11:40:28', '2026-08-05 11:40:28'),
(2, 1, 331, 1, '2026-08-05 12:23:36', '2026-08-05 12:23:36'),
(2, 1, 332, 1, '2026-08-05 12:23:36', '2026-08-05 12:23:36'),
(2, 1, 333, 1, '2026-08-07 08:18:15', '2026-08-07 08:18:15'),
(2, 1, 334, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(2, 1, 335, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(2, 1, 336, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(2, 1, 337, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(2, 1, 338, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(2, 1, 339, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(2, 1, 340, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(2, 1, 341, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(2, 1, 342, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(2, 1, 343, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(2, 1, 344, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(2, 1, 345, 1, '2026-08-11 06:44:54', '2026-08-11 06:44:54'),
(2, 1, 346, 1, '2026-08-11 06:44:54', '2026-08-11 06:44:54'),
(2, 1, 347, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(2, 1, 348, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(2, 1, 349, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(3, 1, 1, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 2, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 3, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 4, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 5, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 6, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 7, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 8, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 10, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 11, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 12, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 13, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 14, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 17, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 19, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 20, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 21, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 22, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 23, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 24, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 25, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 26, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 27, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 28, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 29, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 30, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 31, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 32, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 33, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 34, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 35, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 36, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 37, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 38, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 39, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 40, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 41, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 42, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 43, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 44, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 45, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 46, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 47, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 48, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 49, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 50, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 51, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 52, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 53, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 54, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 55, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 56, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 57, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 58, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 59, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 60, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 61, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 62, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 63, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 64, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 65, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 66, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 67, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 68, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 69, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 70, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 71, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 72, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 73, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 74, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 75, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 76, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 77, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 78, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 79, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 80, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 81, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 82, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 83, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 84, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 85, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 86, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 87, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 88, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 89, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 90, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 91, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 92, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 93, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 94, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 95, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 96, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 97, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 98, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 99, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 100, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 101, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 102, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 103, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 104, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 105, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 106, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 107, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 108, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 109, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 110, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 111, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 112, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 113, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 114, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 115, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 116, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 117, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 118, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 119, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 120, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 121, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 122, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 123, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 124, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 125, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 126, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 127, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 128, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 129, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 130, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 131, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 132, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 133, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 134, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 135, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 136, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 137, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 138, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 139, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 140, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 141, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 142, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 144, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 145, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 146, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 147, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 148, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 149, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 150, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 151, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 152, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 153, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 154, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 155, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 156, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 157, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 158, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 159, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 160, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 161, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 162, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 163, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 164, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 165, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 166, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 167, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 168, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 169, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 170, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 171, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 172, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 173, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 174, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 175, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 176, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 177, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 178, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 179, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 180, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 181, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 182, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 183, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10');
INSERT INTO `designation_route` (`designation_id`, `account_id`, `route_id`, `is_allowed`, `created_at`, `updated_at`) VALUES
(3, 1, 184, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 185, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 186, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 187, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 189, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 190, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 191, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 192, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 193, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 195, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 196, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 197, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 198, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 199, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 200, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 201, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 202, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 203, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 204, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 205, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 206, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 207, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 208, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 209, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 210, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 211, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 212, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 213, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 214, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 225, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 226, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 227, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 228, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 229, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 230, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 231, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 232, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 233, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 234, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 235, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 236, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 237, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 238, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 239, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 240, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 241, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 242, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 243, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 244, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 245, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 246, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 247, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 248, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 249, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 250, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 251, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 252, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 253, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 254, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 255, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 256, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 257, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 258, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 259, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 260, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 261, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 262, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 263, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 264, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 265, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 266, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 267, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 268, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 269, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 270, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 271, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 272, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 273, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 274, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 275, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 276, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 277, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 278, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 279, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 280, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 281, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 282, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 283, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 284, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 285, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 286, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 287, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 288, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 289, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 290, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 291, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 292, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 293, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 294, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 295, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 296, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 297, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 298, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 299, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 300, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 301, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 302, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 303, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 304, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 305, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 306, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 307, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 308, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 309, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 310, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 311, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 312, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 313, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 314, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 315, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 316, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 317, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 318, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 321, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 323, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 324, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 325, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 326, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 327, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(3, 1, 328, 1, '2026-08-03 05:46:54', '2026-08-03 05:46:54'),
(3, 1, 329, 1, '2026-08-05 11:40:28', '2026-08-05 11:40:28'),
(3, 1, 330, 1, '2026-08-05 11:40:28', '2026-08-05 11:40:28'),
(3, 1, 331, 1, '2026-08-05 12:23:36', '2026-08-05 12:23:36'),
(3, 1, 332, 1, '2026-08-05 12:23:36', '2026-08-05 12:23:36'),
(3, 1, 333, 1, '2026-08-07 08:18:15', '2026-08-07 08:18:15'),
(3, 1, 334, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(3, 1, 335, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(3, 1, 336, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(3, 1, 337, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(3, 1, 338, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(3, 1, 339, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(3, 1, 340, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(3, 1, 341, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(3, 1, 342, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(3, 1, 343, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(3, 1, 344, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(3, 1, 345, 1, '2026-08-11 06:44:54', '2026-08-11 06:44:54'),
(3, 1, 346, 1, '2026-08-11 06:44:54', '2026-08-11 06:44:54'),
(3, 1, 347, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(3, 1, 348, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(3, 1, 349, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(4, 1, 1, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 2, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 3, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 4, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 5, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 6, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 7, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 8, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 10, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 11, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 12, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 13, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 14, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 17, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 19, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 20, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 21, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 22, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 23, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 24, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 25, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 26, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 27, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 28, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 29, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 30, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 31, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 32, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 33, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 34, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 35, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 36, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 37, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 38, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 39, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 40, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 41, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 42, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 43, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 44, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 45, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 46, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 47, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 48, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 49, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 50, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 51, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 52, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 53, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 54, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 55, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 56, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 57, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 58, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 59, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 60, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 61, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 62, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 63, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 64, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 65, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 66, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 67, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 68, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 69, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 70, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 71, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 72, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 73, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 74, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 75, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 76, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 77, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 78, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 79, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 80, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 81, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 82, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 83, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 84, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 85, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 86, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 87, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 88, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 89, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 90, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 91, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 92, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 93, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 94, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 95, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 96, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 97, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 98, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 99, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 100, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 101, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 102, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 103, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 104, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 105, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 106, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 107, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 108, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 109, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 110, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 111, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 112, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 113, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 114, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 115, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 116, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 117, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 118, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 119, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 120, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 121, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 122, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 123, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 124, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 125, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 126, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 127, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 128, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 129, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 130, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 131, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 132, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 133, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 134, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 135, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 136, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 137, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 138, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 139, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 140, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 141, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 142, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 144, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 145, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 146, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 147, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 148, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 149, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 150, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 151, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 152, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 153, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 154, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 155, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 156, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 157, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 158, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 159, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 160, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 161, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 162, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 163, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 164, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 165, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 166, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 167, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 168, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 169, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 170, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 171, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 172, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 173, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 174, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 175, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 176, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 177, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 178, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 179, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 180, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 181, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 182, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 183, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 184, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 185, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 186, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 187, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 189, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 190, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 191, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 192, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 193, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 195, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 196, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 197, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 198, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 199, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 200, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 201, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 202, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 203, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 204, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 205, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 206, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 207, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 208, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 209, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 210, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 211, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 212, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 213, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 214, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 225, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 226, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 227, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 228, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 229, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 230, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 231, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 232, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 233, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 234, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 235, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 236, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 237, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 238, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 239, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 240, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 241, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 242, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 243, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 244, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 245, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 246, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 247, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 248, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 249, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 250, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 251, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 252, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 253, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 254, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 255, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 256, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 257, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 258, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 259, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 260, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 261, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 262, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 263, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 264, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 265, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 266, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 267, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 268, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 269, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 270, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 271, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 272, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 273, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 274, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 275, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 276, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 277, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 278, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 279, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 280, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 281, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 282, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 283, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 284, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 285, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 286, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 287, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 288, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 289, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 290, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 291, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 292, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 293, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 294, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 295, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 296, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 297, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 298, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 299, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 300, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 301, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 302, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 303, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 304, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 305, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 306, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 307, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 308, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 309, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 310, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 311, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 312, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 313, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 314, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 315, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 316, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 317, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 318, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 321, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 323, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 324, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 325, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 326, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 327, 1, '2026-08-03 03:38:10', '2026-08-03 03:38:10'),
(4, 1, 328, 1, '2026-08-03 05:46:54', '2026-08-03 05:46:54'),
(4, 1, 329, 1, '2026-08-05 11:40:28', '2026-08-05 11:40:28'),
(4, 1, 330, 1, '2026-08-05 11:40:28', '2026-08-05 11:40:28'),
(4, 1, 331, 1, '2026-08-05 12:23:36', '2026-08-05 12:23:36'),
(4, 1, 332, 1, '2026-08-05 12:23:36', '2026-08-05 12:23:36'),
(4, 1, 333, 1, '2026-08-07 08:18:15', '2026-08-07 08:18:15'),
(4, 1, 334, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(4, 1, 335, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(4, 1, 336, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(4, 1, 337, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(4, 1, 338, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(4, 1, 339, 1, '2026-08-08 01:56:47', '2026-08-08 01:56:47'),
(4, 1, 340, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(4, 1, 341, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(4, 1, 342, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(4, 1, 343, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(4, 1, 344, 1, '2026-08-10 06:23:01', '2026-08-10 06:23:01'),
(4, 1, 345, 1, '2026-08-11 06:44:54', '2026-08-11 06:44:54'),
(4, 1, 346, 1, '2026-08-11 06:44:54', '2026-08-11 06:44:54'),
(4, 1, 347, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(4, 1, 348, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50'),
(4, 1, 349, 1, '2026-08-16 04:40:50', '2026-08-16 04:40:50');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `stock` int(11) DEFAULT 0,
  `low_stock_alert` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `account_id`, `product_id`, `stock`, `low_stock_alert`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 0, '2026-08-16 05:43:12', '2026-08-17 10:25:06'),
(2, 1, 2, 1, 0, '2026-08-16 05:43:27', '2026-08-16 05:43:27'),
(3, 1, 3, 1, 0, '2026-08-16 05:44:40', '2026-08-16 05:44:40'),
(4, 1, 4, 1, 0, '2026-08-16 05:45:01', '2026-08-16 05:45:01'),
(5, 1, 5, 2, 0, '2026-08-16 05:55:39', '2026-08-16 07:07:48'),
(6, 1, 6, 0, 0, '2026-08-16 05:56:28', '2026-08-16 08:44:39'),
(7, 1, 7, 1, 0, '2026-08-16 05:56:44', '2026-08-16 08:45:59'),
(8, 1, 8, 0, 0, '2026-08-16 05:57:17', '2026-08-16 08:42:23'),
(9, 1, 9, 0, 0, '2026-08-16 05:57:41', '2026-08-16 08:38:11'),
(10, 1, 10, 0, 0, '2026-08-16 05:57:59', '2026-08-16 05:59:11');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `local_governments`
--

CREATE TABLE `local_governments` (
  `id` int(11) NOT NULL,
  `state_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci COMMENT='Local governments in Nigeria.';

--
-- Dumping data for table `local_governments`
--

INSERT INTO `local_governments` (`id`, `state_id`, `name`) VALUES
(1, 1, 'Aba North'),
(2, 1, 'Aba South'),
(3, 1, 'Arochukwu'),
(4, 1, 'Bende'),
(5, 1, 'Ikwuano'),
(6, 1, 'Isiala Ngwa North'),
(7, 1, 'Isiala Ngwa South'),
(8, 1, 'Isuikwuato'),
(9, 1, 'Obi Ngwa'),
(10, 1, 'Ohafia'),
(11, 1, 'Osisioma'),
(12, 1, 'Ugwunagbo'),
(13, 1, 'Ukwa East'),
(14, 1, 'Ukwa West'),
(15, 1, 'Umuahia North'),
(16, 1, 'Umuahia South'),
(17, 1, 'Umu Nneochi'),
(18, 2, 'Demsa'),
(19, 2, 'Fufure'),
(20, 2, 'Ganye'),
(21, 2, 'Gayuk'),
(22, 2, 'Gombi'),
(23, 2, 'Grie'),
(24, 2, 'Hong'),
(25, 2, 'Jada'),
(26, 2, 'Larmurde'),
(27, 2, 'Madagali'),
(28, 2, 'Maiha'),
(29, 2, 'Mayo Belwa'),
(30, 2, 'Michika'),
(31, 2, 'Mubi North'),
(32, 2, 'Mubi South'),
(33, 2, 'Numan'),
(34, 2, 'Shelleng'),
(35, 2, 'Song'),
(36, 2, 'Toungo'),
(37, 2, 'Yola North'),
(38, 2, 'Yola South'),
(39, 3, 'Abak'),
(40, 3, 'Eastern Obolo'),
(41, 3, 'Eket'),
(42, 3, 'Esit Eket'),
(43, 3, 'Essien Udim'),
(44, 3, 'Etim Ekpo'),
(45, 3, 'Etinan'),
(46, 3, 'Ibeno'),
(47, 3, 'Ibesikpo Asutan'),
(48, 3, 'Ibiono-Ibom'),
(49, 3, 'Ika'),
(50, 3, 'Ikono'),
(51, 3, 'Ikot Abasi'),
(52, 3, 'Ikot Ekpene'),
(53, 3, 'Ini'),
(54, 3, 'Itu'),
(55, 3, 'Mbo'),
(56, 3, 'Mkpat-Enin'),
(57, 3, 'Nsit-Atai'),
(58, 3, 'Nsit-Ibom'),
(59, 3, 'Nsit-Ubium'),
(60, 3, 'Obot Akara'),
(61, 3, 'Okobo'),
(62, 3, 'Onna'),
(63, 3, 'Oron'),
(64, 3, 'Oruk Anam'),
(65, 3, 'Udung-Uko'),
(66, 3, 'Ukanafun'),
(67, 3, 'Uruan'),
(68, 3, 'Urue-Offong/Oruko'),
(69, 3, 'Uyo'),
(70, 4, 'Aguata'),
(71, 4, 'Anambra East'),
(72, 4, 'Anambra West'),
(73, 4, 'Anaocha'),
(74, 4, 'Awka North'),
(75, 4, 'Awka South'),
(76, 4, 'Ayamelum'),
(77, 4, 'Dunukofia'),
(78, 4, 'Ekwusigo'),
(79, 4, 'Idemili North'),
(80, 4, 'Idemili South'),
(81, 4, 'Ihiala'),
(82, 4, 'Njikoka'),
(83, 4, 'Nnewi North'),
(84, 4, 'Nnewi South'),
(85, 4, 'Ogbaru'),
(86, 4, 'Onitsha North'),
(87, 4, 'Onitsha South'),
(88, 4, 'Orumba North'),
(89, 4, 'Orumba South'),
(90, 4, 'Oyi'),
(91, 5, 'Alkaleri'),
(92, 5, 'Bauchi'),
(93, 5, 'Bogoro'),
(94, 5, 'Damban'),
(95, 5, 'Darazo'),
(96, 5, 'Dass'),
(97, 5, 'Gamawa'),
(98, 5, 'Ganjuwa'),
(99, 5, 'Giade'),
(100, 5, 'Itas/Gadau'),
(101, 5, 'Jama\'are'),
(102, 5, 'Katagum'),
(103, 5, 'Kirfi'),
(104, 5, 'Misau'),
(105, 5, 'Ningi'),
(106, 5, 'Shira'),
(107, 5, 'Tafawa Balewa'),
(108, 5, 'Toro'),
(109, 5, 'Warji'),
(110, 5, 'Zaki'),
(111, 6, 'Brass'),
(112, 6, 'Ekeremor'),
(113, 6, 'Kolokuma/Opokuma'),
(114, 6, 'Nembe'),
(115, 6, 'Ogbia'),
(116, 6, 'Sagbama'),
(117, 6, 'Southern Ijaw'),
(118, 6, 'Yenagoa'),
(119, 7, 'Agatu'),
(120, 7, 'Apa'),
(121, 7, 'Ado'),
(122, 7, 'Buruku'),
(123, 7, 'Gboko'),
(124, 7, 'Guma'),
(125, 7, 'Gwer East'),
(126, 7, 'Gwer West'),
(127, 7, 'Katsina-Ala'),
(128, 7, 'Konshisha'),
(129, 7, 'Kwande'),
(130, 7, 'Logo'),
(131, 7, 'Makurdi'),
(132, 7, 'Obi'),
(133, 7, 'Ogbadibo'),
(134, 7, 'Ohimini'),
(135, 7, 'Oju'),
(136, 7, 'Okpokwu'),
(137, 7, 'Oturkpo'),
(138, 7, 'Tarka'),
(139, 7, 'Ukum'),
(140, 7, 'Ushongo'),
(141, 7, 'Vandeikya'),
(142, 8, 'Abadam'),
(143, 8, 'Askira/Uba'),
(144, 8, 'Bama'),
(145, 8, 'Bayo'),
(146, 8, 'Biu'),
(147, 8, 'Chibok'),
(148, 8, 'Damboa'),
(149, 8, 'Dikwa'),
(150, 8, 'Gubio'),
(151, 8, 'Guzamala'),
(152, 8, 'Gwoza'),
(153, 8, 'Hawul'),
(154, 8, 'Jere'),
(155, 8, 'Kaga'),
(156, 8, 'Kala/Balge'),
(157, 8, 'Konduga'),
(158, 8, 'Kukawa'),
(159, 8, 'Kwaya Kusar'),
(160, 8, 'Mafa'),
(161, 8, 'Magumeri'),
(162, 8, 'Maiduguri'),
(163, 8, 'Marte'),
(164, 8, 'Mobbar'),
(165, 8, 'Monguno'),
(166, 8, 'Ngala'),
(167, 8, 'Nganzai'),
(168, 8, 'Shani'),
(169, 9, 'Abi'),
(170, 9, 'Akamkpa'),
(171, 9, 'Akpabuyo'),
(172, 9, 'Bakassi'),
(173, 9, 'Bekwarra'),
(174, 9, 'Biase'),
(175, 9, 'Boki'),
(176, 9, 'Calabar Municipal'),
(177, 9, 'Calabar South'),
(178, 9, 'Etung'),
(179, 9, 'Ikom'),
(180, 9, 'Obanliku'),
(181, 9, 'Obubra'),
(182, 9, 'Obudu'),
(183, 9, 'Odukpani'),
(184, 9, 'Ogoja'),
(185, 9, 'Yakuur'),
(186, 9, 'Yala'),
(187, 10, 'Aniocha North'),
(188, 10, 'Aniocha South'),
(189, 10, 'Bomadi'),
(190, 10, 'Burutu'),
(191, 10, 'Ethiope East'),
(192, 10, 'Ethiope West'),
(193, 10, 'Ika North East'),
(194, 10, 'Ika South'),
(195, 10, 'Isoko North'),
(196, 10, 'Isoko South'),
(197, 10, 'Ndokwa East'),
(198, 10, 'Ndokwa West'),
(199, 10, 'Okpe'),
(200, 10, 'Oshimili North'),
(201, 10, 'Oshimili South'),
(202, 10, 'Patani'),
(203, 10, 'Sapele, Delta'),
(204, 10, 'Udu'),
(205, 10, 'Ughelli North'),
(206, 10, 'Ughelli South'),
(207, 10, 'Ukwuani'),
(208, 10, 'Uvwie'),
(209, 10, 'Warri North'),
(210, 10, 'Warri South'),
(211, 10, 'Warri South West'),
(212, 11, 'Abakaliki'),
(213, 11, 'Afikpo North'),
(214, 11, 'Afikpo South'),
(215, 11, 'Ebonyi'),
(216, 11, 'Ezza North'),
(217, 11, 'Ezza South'),
(218, 11, 'Ikwo'),
(219, 11, 'Ishielu'),
(220, 11, 'Ivo'),
(221, 11, 'Izzi'),
(222, 11, 'Ohaozara'),
(223, 11, 'Ohaukwu'),
(224, 11, 'Onicha'),
(225, 12, 'Akoko-Edo'),
(226, 12, 'Egor'),
(227, 12, 'Esan Central'),
(228, 12, 'Esan North-East'),
(229, 12, 'Esan South-East'),
(230, 12, 'Esan West'),
(231, 12, 'Etsako Central'),
(232, 12, 'Etsako East'),
(233, 12, 'Etsako West'),
(234, 12, 'Igueben'),
(235, 12, 'Ikpoba Okha'),
(236, 12, 'Orhionmwon'),
(237, 12, 'Oredo'),
(238, 12, 'Ovia North-East'),
(239, 12, 'Ovia South-West'),
(240, 12, 'Owan East'),
(241, 12, 'Owan West'),
(242, 12, 'Uhunmwonde'),
(243, 13, 'Ado Ekiti'),
(244, 13, 'Efon'),
(245, 13, 'Ekiti East'),
(246, 13, 'Ekiti South-West'),
(247, 13, 'Ekiti West'),
(248, 13, 'Emure'),
(249, 13, 'Gbonyin'),
(250, 13, 'Ido Osi'),
(251, 13, 'Ijero'),
(252, 13, 'Ikere'),
(253, 13, 'Ikole'),
(254, 13, 'Ilejemeje'),
(255, 13, 'Irepodun/Ifelodun'),
(256, 13, 'Ise/Orun'),
(257, 13, 'Moba'),
(258, 13, 'Oye'),
(259, 14, 'Aninri'),
(260, 14, 'Awgu'),
(261, 14, 'Enugu East'),
(262, 14, 'Enugu North'),
(263, 14, 'Enugu South'),
(264, 14, 'Ezeagu'),
(265, 14, 'Igbo Etiti'),
(266, 14, 'Igbo Eze North'),
(267, 14, 'Igbo Eze South'),
(268, 14, 'Isi Uzo'),
(269, 14, 'Nkanu East'),
(270, 14, 'Nkanu West'),
(271, 14, 'Nsukka'),
(272, 14, 'Oji River'),
(273, 14, 'Udenu'),
(274, 14, 'Udi'),
(275, 14, 'Uzo Uwani'),
(276, 15, 'Abaji'),
(277, 15, 'Bwari'),
(278, 15, 'Gwagwalada'),
(279, 15, 'Kuje'),
(280, 15, 'Kwali'),
(281, 15, 'Municipal Area Council'),
(282, 16, 'Akko'),
(283, 16, 'Balanga'),
(284, 16, 'Billiri'),
(285, 16, 'Dukku'),
(286, 16, 'Funakaye'),
(287, 16, 'Gombe'),
(288, 16, 'Kaltungo'),
(289, 16, 'Kwami'),
(290, 16, 'Nafada'),
(291, 16, 'Shongom'),
(292, 16, 'Yamaltu/Deba'),
(293, 17, 'Aboh Mbaise'),
(294, 17, 'Ahiazu Mbaise'),
(295, 17, 'Ehime Mbano'),
(296, 17, 'Ezinihitte'),
(297, 17, 'Ideato North'),
(298, 17, 'Ideato South'),
(299, 17, 'Ihitte/Uboma'),
(300, 17, 'Ikeduru'),
(301, 17, 'Isiala Mbano'),
(302, 17, 'Isu'),
(303, 17, 'Mbaitoli'),
(304, 17, 'Ngor Okpala'),
(305, 17, 'Njaba'),
(306, 17, 'Nkwerre'),
(307, 17, 'Nwangele'),
(308, 17, 'Obowo'),
(309, 17, 'Oguta'),
(310, 17, 'Ohaji/Egbema'),
(311, 17, 'Okigwe'),
(312, 17, 'Orlu'),
(313, 17, 'Orsu'),
(314, 17, 'Oru East'),
(315, 17, 'Oru West'),
(316, 17, 'Owerri Municipal'),
(317, 17, 'Owerri North'),
(318, 17, 'Owerri West'),
(319, 17, 'Unuimo'),
(320, 18, 'Auyo'),
(321, 18, 'Babura'),
(322, 18, 'Biriniwa'),
(323, 18, 'Birnin Kudu'),
(324, 18, 'Buji'),
(325, 18, 'Dutse'),
(326, 18, 'Gagarawa'),
(327, 18, 'Garki'),
(328, 18, 'Gumel'),
(329, 18, 'Guri'),
(330, 18, 'Gwaram'),
(331, 18, 'Gwiwa'),
(332, 18, 'Hadejia'),
(333, 18, 'Jahun'),
(334, 18, 'Kafin Hausa'),
(335, 18, 'Kazaure'),
(336, 18, 'Kiri Kasama'),
(337, 18, 'Kiyawa'),
(338, 18, 'Kaugama'),
(339, 18, 'Maigatari'),
(340, 18, 'Malam Madori'),
(341, 18, 'Miga'),
(342, 18, 'Ringim'),
(343, 18, 'Roni'),
(344, 18, 'Sule Tankarkar'),
(345, 18, 'Taura'),
(346, 18, 'Yankwashi'),
(347, 19, 'Birnin Gwari'),
(348, 19, 'Chikun'),
(349, 19, 'Giwa'),
(350, 19, 'Igabi'),
(351, 19, 'Ikara'),
(352, 19, 'Jaba'),
(353, 19, 'Jema\'a'),
(354, 19, 'Kachia'),
(355, 19, 'Kaduna North'),
(356, 19, 'Kaduna South'),
(357, 19, 'Kagarko'),
(358, 19, 'Kajuru'),
(359, 19, 'Kaura'),
(360, 19, 'Kauru'),
(361, 19, 'Kubau'),
(362, 19, 'Kudan'),
(363, 19, 'Lere'),
(364, 19, 'Makarfi'),
(365, 19, 'Sabon Gari'),
(366, 19, 'Sanga'),
(367, 19, 'Soba'),
(368, 19, 'Zangon Kataf'),
(369, 19, 'Zaria'),
(370, 20, 'Ajingi'),
(371, 20, 'Albasu'),
(372, 20, 'Bagwai'),
(373, 20, 'Bebeji'),
(374, 20, 'Bichi'),
(375, 20, 'Bunkure'),
(376, 20, 'Dala'),
(377, 20, 'Dambatta'),
(378, 20, 'Dawakin Kudu'),
(379, 20, 'Dawakin Tofa'),
(380, 20, 'Doguwa'),
(381, 20, 'Fagge'),
(382, 20, 'Gabasawa'),
(383, 20, 'Garko'),
(384, 20, 'Garun Mallam'),
(385, 20, 'Gaya'),
(386, 20, 'Gezawa'),
(387, 20, 'Gwale'),
(388, 20, 'Gwarzo'),
(389, 20, 'Kabo'),
(390, 20, 'Kano Municipal'),
(391, 20, 'Karaye'),
(392, 20, 'Kibiya'),
(393, 20, 'Kiru'),
(394, 20, 'Kumbotso'),
(395, 20, 'Kunchi'),
(396, 20, 'Kura'),
(397, 20, 'Madobi'),
(398, 20, 'Makoda'),
(399, 20, 'Minjibir'),
(400, 20, 'Nasarawa'),
(401, 20, 'Rano'),
(402, 20, 'Rimin Gado'),
(403, 20, 'Rogo'),
(404, 20, 'Shanono'),
(405, 20, 'Sumaila'),
(406, 20, 'Takai'),
(407, 20, 'Tarauni'),
(408, 20, 'Tofa'),
(409, 20, 'Tsanyawa'),
(410, 20, 'Tudun Wada'),
(411, 20, 'Ungogo'),
(412, 20, 'Warawa'),
(413, 20, 'Wudil'),
(414, 21, 'Bakori'),
(415, 21, 'Batagarawa'),
(416, 21, 'Batsari'),
(417, 21, 'Baure'),
(418, 21, 'Bindawa'),
(419, 21, 'Charanchi'),
(420, 21, 'Dandume'),
(421, 21, 'Danja'),
(422, 21, 'Dan Musa'),
(423, 21, 'Daura'),
(424, 21, 'Dutsi'),
(425, 21, 'Dutsin Ma'),
(426, 21, 'Faskari'),
(427, 21, 'Funtua'),
(428, 21, 'Ingawa'),
(429, 21, 'Jibia'),
(430, 21, 'Kafur'),
(431, 21, 'Kaita'),
(432, 21, 'Kankara'),
(433, 21, 'Kankia'),
(434, 21, 'Katsina'),
(435, 21, 'Kurfi'),
(436, 21, 'Kusada'),
(437, 21, 'Mai\'Adua'),
(438, 21, 'Malumfashi'),
(439, 21, 'Mani'),
(440, 21, 'Mashi'),
(441, 21, 'Matazu'),
(442, 21, 'Musawa'),
(443, 21, 'Rimi'),
(444, 21, 'Sabuwa'),
(445, 21, 'Safana'),
(446, 21, 'Sandamu'),
(447, 21, 'Zango'),
(448, 22, 'Aleiro'),
(449, 22, 'Arewa Dandi'),
(450, 22, 'Argungu'),
(451, 22, 'Augie'),
(452, 22, 'Bagudo'),
(453, 22, 'Birnin Kebbi'),
(454, 22, 'Bunza'),
(455, 22, 'Dandi'),
(456, 22, 'Fakai'),
(457, 22, 'Gwandu'),
(458, 22, 'Jega'),
(459, 22, 'Kalgo'),
(460, 22, 'Koko/Besse'),
(461, 22, 'Maiyama'),
(462, 22, 'Ngaski'),
(463, 22, 'Sakaba'),
(464, 22, 'Shanga'),
(465, 22, 'Suru'),
(466, 22, 'Wasagu/Danko'),
(467, 22, 'Yauri'),
(468, 22, 'Zuru'),
(469, 23, 'Adavi'),
(470, 23, 'Ajaokuta'),
(471, 23, 'Ankpa'),
(472, 23, 'Bassa'),
(473, 23, 'Dekina'),
(474, 23, 'Ibaji'),
(475, 23, 'Idah'),
(476, 23, 'Igalamela Odolu'),
(477, 23, 'Ijumu'),
(478, 23, 'Kabba/Bunu'),
(479, 23, 'Kogi'),
(480, 23, 'Lokoja'),
(481, 23, 'Mopa Muro'),
(482, 23, 'Ofu'),
(483, 23, 'Ogori/Magongo'),
(484, 23, 'Okehi'),
(485, 23, 'Okene'),
(486, 23, 'Olamaboro'),
(487, 23, 'Omala'),
(488, 23, 'Yagba East'),
(489, 23, 'Yagba West'),
(490, 24, 'Asa'),
(491, 24, 'Baruten'),
(492, 24, 'Edu'),
(493, 24, 'Ekiti, Kwara State'),
(494, 24, 'Ifelodun'),
(495, 24, 'Ilorin East'),
(496, 24, 'Ilorin South'),
(497, 24, 'Ilorin West'),
(498, 24, 'Irepodun'),
(499, 24, 'Isin'),
(500, 24, 'Kaiama'),
(501, 24, 'Moro'),
(502, 24, 'Offa'),
(503, 24, 'Oke Ero'),
(504, 24, 'Oyun'),
(505, 24, 'Pategi'),
(506, 25, 'Agege'),
(507, 25, 'Ajeromi-Ifelodun'),
(508, 25, 'Alimosho'),
(509, 25, 'Amuwo-Odofin'),
(510, 25, 'Apapa'),
(511, 25, 'Badagry'),
(512, 25, 'Epe'),
(513, 25, 'Eti Osa'),
(514, 25, 'Ibeju-Lekki'),
(515, 25, 'Ifako-Ijaiye'),
(516, 25, 'Ikeja'),
(517, 25, 'Ikorodu'),
(518, 25, 'Kosofe'),
(519, 25, 'Lagos Island'),
(520, 25, 'Lagos Mainland'),
(521, 25, 'Mushin'),
(522, 25, 'Ojo'),
(523, 25, 'Oshodi-Isolo'),
(524, 25, 'Shomolu'),
(525, 25, 'Surulere, Lagos State'),
(526, 26, 'Akwanga'),
(527, 26, 'Awe'),
(528, 26, 'Doma'),
(529, 26, 'Karu'),
(530, 26, 'Keana'),
(531, 26, 'Keffi'),
(532, 26, 'Kokona'),
(533, 26, 'Lafia'),
(534, 26, 'Nasarawa'),
(535, 26, 'Nasarawa Egon'),
(536, 26, 'Obi'),
(537, 26, 'Toto'),
(538, 26, 'Wamba'),
(539, 27, 'Agaie'),
(540, 27, 'Agwara'),
(541, 27, 'Bida'),
(542, 27, 'Borgu'),
(543, 27, 'Bosso'),
(544, 27, 'Chanchaga'),
(545, 27, 'Edati'),
(546, 27, 'Gbako'),
(547, 27, 'Gurara'),
(548, 27, 'Katcha'),
(549, 27, 'Kontagora'),
(550, 27, 'Lapai'),
(551, 27, 'Lavun'),
(552, 27, 'Magama'),
(553, 27, 'Mariga'),
(554, 27, 'Mashegu'),
(555, 27, 'Mokwa'),
(556, 27, 'Moya'),
(557, 27, 'Paikoro'),
(558, 27, 'Rafi'),
(559, 27, 'Rijau'),
(560, 27, 'Shiroro'),
(561, 27, 'Suleja'),
(562, 27, 'Tafa'),
(563, 27, 'Wushishi'),
(564, 28, 'Abeokuta North'),
(565, 28, 'Abeokuta South'),
(566, 28, 'Ado-Odo/Ota'),
(567, 28, 'Egbado North'),
(568, 28, 'Egbado South'),
(569, 28, 'Ewekoro'),
(570, 28, 'Ifo'),
(571, 28, 'Ijebu East'),
(572, 28, 'Ijebu North'),
(573, 28, 'Ijebu North East'),
(574, 28, 'Ijebu Ode'),
(575, 28, 'Ikenne'),
(576, 28, 'Imeko Afon'),
(577, 28, 'Ipokia'),
(578, 28, 'Obafemi Owode'),
(579, 28, 'Odeda'),
(580, 28, 'Odogbolu'),
(581, 28, 'Ogun Waterside'),
(582, 28, 'Remo North'),
(583, 28, 'Shagamu'),
(584, 29, 'Akoko North-East'),
(585, 29, 'Akoko North-West'),
(586, 29, 'Akoko South-West'),
(587, 29, 'Akoko South-East'),
(588, 29, 'Akure North'),
(589, 29, 'Akure South'),
(590, 29, 'Ese Odo'),
(591, 29, 'Idanre'),
(592, 29, 'Ifedore'),
(593, 29, 'Ilaje'),
(594, 29, 'Ile Oluji/Okeigbo'),
(595, 29, 'Irele'),
(596, 29, 'Odigbo'),
(597, 29, 'Okitipupa'),
(598, 29, 'Ondo East'),
(599, 29, 'Ondo West'),
(600, 29, 'Ose'),
(601, 29, 'Owo'),
(602, 30, 'Atakunmosa East'),
(603, 30, 'Atakunmosa West'),
(604, 30, 'Aiyedaade'),
(605, 30, 'Aiyedire'),
(606, 30, 'Boluwaduro'),
(607, 30, 'Boripe'),
(608, 30, 'Ede North'),
(609, 30, 'Ede South'),
(610, 30, 'Ife Central'),
(611, 30, 'Ife East'),
(612, 30, 'Ife North'),
(613, 30, 'Ife South'),
(614, 30, 'Egbedore'),
(615, 30, 'Ejigbo'),
(616, 30, 'Ifedayo'),
(617, 30, 'Ifelodun'),
(618, 30, 'Ila'),
(619, 30, 'Ilesa East'),
(620, 30, 'Ilesa West'),
(621, 30, 'Irepodun'),
(622, 30, 'Irewole'),
(623, 30, 'Isokan'),
(624, 30, 'Iwo'),
(625, 30, 'Obokun'),
(626, 30, 'Odo Otin'),
(627, 30, 'Ola Oluwa'),
(628, 30, 'Olorunda'),
(629, 30, 'Oriade'),
(630, 30, 'Orolu'),
(631, 30, 'Osogbo'),
(632, 31, 'Afijio'),
(633, 31, 'Akinyele'),
(634, 31, 'Atiba'),
(635, 31, 'Atisbo'),
(636, 31, 'Egbeda'),
(637, 31, 'Ibadan North'),
(638, 31, 'Ibadan North-East'),
(639, 31, 'Ibadan North-West'),
(640, 31, 'Ibadan South-East'),
(641, 31, 'Ibadan South-West'),
(642, 31, 'Ibarapa Central'),
(643, 31, 'Ibarapa East'),
(644, 31, 'Ibarapa North'),
(645, 31, 'Ido'),
(646, 31, 'Irepo'),
(647, 31, 'Iseyin'),
(648, 31, 'Itesiwaju'),
(649, 31, 'Iwajowa'),
(650, 31, 'Kajola'),
(651, 31, 'Lagelu'),
(652, 31, 'Ogbomosho North'),
(653, 31, 'Ogbomosho South'),
(654, 31, 'Ogo Oluwa'),
(655, 31, 'Olorunsogo'),
(656, 31, 'Oluyole'),
(657, 31, 'Ona Ara'),
(658, 31, 'Orelope'),
(659, 31, 'Ori Ire'),
(660, 31, 'Oyo'),
(661, 31, 'Oyo East'),
(662, 31, 'Saki East'),
(663, 31, 'Saki West'),
(664, 31, 'Surulere, Oyo State'),
(665, 32, 'Bokkos'),
(666, 32, 'Barkin Ladi'),
(667, 32, 'Bassa'),
(668, 32, 'Jos East'),
(669, 32, 'Jos North'),
(670, 32, 'Jos South'),
(671, 32, 'Kanam'),
(672, 32, 'Kanke'),
(673, 32, 'Langtang South'),
(674, 32, 'Langtang North'),
(675, 32, 'Mangu'),
(676, 32, 'Mikang'),
(677, 32, 'Pankshin'),
(678, 32, 'Qua\'an Pan'),
(679, 32, 'Riyom'),
(680, 32, 'Shendam'),
(681, 32, 'Wase'),
(682, 33, 'Abua/Odual'),
(683, 33, 'Ahoada East'),
(684, 33, 'Ahoada West'),
(685, 33, 'Akuku-Toru'),
(686, 33, 'Andoni'),
(687, 33, 'Asari-Toru'),
(688, 33, 'Bonny'),
(689, 33, 'Degema'),
(690, 33, 'Eleme'),
(691, 33, 'Emuoha'),
(692, 33, 'Etche'),
(693, 33, 'Gokana'),
(694, 33, 'Ikwerre'),
(695, 33, 'Khana'),
(696, 33, 'Obio/Akpor'),
(697, 33, 'Ogba/Egbema/Ndoni'),
(698, 33, 'Ogu/Bolo'),
(699, 33, 'Okrika'),
(700, 33, 'Omuma'),
(701, 33, 'Opobo/Nkoro'),
(702, 33, 'Oyigbo'),
(703, 33, 'Port Harcourt'),
(704, 33, 'Tai'),
(705, 34, 'Binji'),
(706, 34, 'Bodinga'),
(707, 34, 'Dange Shuni'),
(708, 34, 'Gada'),
(709, 34, 'Goronyo'),
(710, 34, 'Gudu'),
(711, 34, 'Gwadabawa'),
(712, 34, 'Illela'),
(713, 34, 'Isa'),
(714, 34, 'Kebbe'),
(715, 34, 'Kware'),
(716, 34, 'Rabah'),
(717, 34, 'Sabon Birni'),
(718, 34, 'Shagari'),
(719, 34, 'Silame'),
(720, 34, 'Sokoto North'),
(721, 34, 'Sokoto South'),
(722, 34, 'Tambuwal'),
(723, 34, 'Tangaza'),
(724, 34, 'Tureta'),
(725, 34, 'Wamako'),
(726, 34, 'Wurno'),
(727, 34, 'Yabo'),
(728, 35, 'Ardo Kola'),
(729, 35, 'Bali'),
(730, 35, 'Donga'),
(731, 35, 'Gashaka'),
(732, 35, 'Gassol'),
(733, 35, 'Ibi'),
(734, 35, 'Jalingo'),
(735, 35, 'Karim Lamido'),
(736, 35, 'Kumi'),
(737, 35, 'Lau'),
(738, 35, 'Sardauna'),
(739, 35, 'Takum'),
(740, 35, 'Ussa'),
(741, 35, 'Wukari'),
(742, 35, 'Yorro'),
(743, 35, 'Zing'),
(744, 36, 'Bade'),
(745, 36, 'Bursari'),
(746, 36, 'Damaturu'),
(747, 36, 'Fika'),
(748, 36, 'Fune'),
(749, 36, 'Geidam'),
(750, 36, 'Gujba'),
(751, 36, 'Gulani'),
(752, 36, 'Jakusko'),
(753, 36, 'Karasuwa'),
(754, 36, 'Machina'),
(755, 36, 'Nangere'),
(756, 36, 'Nguru'),
(757, 36, 'Potiskum'),
(758, 36, 'Tarmuwa'),
(759, 36, 'Yunusari'),
(760, 36, 'Yusufari'),
(761, 37, 'Anka'),
(762, 37, 'Bakura'),
(763, 37, 'Birnin Magaji/Kiyaw'),
(764, 37, 'Bukkuyum'),
(765, 37, 'Bungudu'),
(766, 37, 'Gummi'),
(767, 37, 'Gusau'),
(768, 37, 'Kaura Namoda'),
(769, 37, 'Maradun'),
(770, 37, 'Maru'),
(771, 37, 'Shinkafi'),
(772, 37, 'Talata Mafara'),
(773, 37, 'Chafe'),
(774, 37, 'Zurmi');

-- --------------------------------------------------------

--
-- Table structure for table `master_items`
--

CREATE TABLE `master_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active,0=inactive',
  `image` varchar(50) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `master_items`
--

INSERT INTO `master_items` (`id`, `account_id`, `category_id`, `name`, `code`, `description`, `status`, `image`, `created_by`, `created_at`, `updated_at`, `is_deleted`, `deleted_at`) VALUES
(1, 1, 1, 'Smart TV 32 Inch', 'TV001', '32 inch Android Smart TV', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:03:57', 0, NULL),
(2, 1, 1, 'Smart TV 43 Inch', 'TV002', '43 inch Android Smart TV', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:03:57', 0, NULL),
(3, 1, 1, 'Smart TV 55 Inch', 'TV003', '55 inch UHD Smart TV', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:03:57', 0, NULL),
(4, 1, 1, 'LED TV 24 Inch', 'TV004', '24 inch LED Television', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:03:57', 0, NULL),
(5, 1, 2, 'Air Conditioner 1HP', 'AC001', 'Split Air Conditioner 1HP', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(6, 1, 2, 'Air Conditioner 1.5HP', 'AC002', 'Split Air Conditioner 1.5HP', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(7, 1, 2, 'Air Conditioner 2HP', 'AC003', 'Split Air Conditioner 2HP', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(8, 1, 3, 'Double Door Refrigerator', 'FR001', 'Double Door Fridge', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(9, 1, 3, 'Single Door Refrigerator', 'FR002', 'Single Door Fridge', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(10, 1, 3, 'Chest Freezer 200L', 'FR003', 'Deep Freezer 200 Litres', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(11, 1, 3, 'Chest Freezer 300L', 'FR004', 'Deep Freezer 300 Litres', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(12, 1, 18, 'Water Dispenser', 'WD001', 'Hot and Cold Water Dispenser', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(13, 1, 5, 'Microwave Oven 20L', 'MW001', '20 Litre Microwave Oven', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(14, 1, 5, 'Microwave Oven 30L', 'MW002', '30 Litre Microwave Oven', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(15, 1, 6, 'Gas Cooker 2 Burner', 'GC001', '2 Burner Gas Cooker', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(16, 1, 6, 'Gas Cooker 4 Burner', 'GC002', '4 Burner Gas Cooker', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(17, 1, 7, 'Home Theatre', 'HT001', '5.1 Channel Home Theatre System', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(18, 1, 7, 'Sound Bar', 'SB001', 'Bluetooth Sound Bar', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(19, 1, 7, 'Bluetooth Speaker', 'SP001', 'Portable Bluetooth Speaker', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(20, 1, 4, 'Washing Machine 7KG', 'WM001', 'Automatic Washing Machine', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(21, 1, 4, 'Washing Machine 10KG', 'WM002', 'Front Load Washing Machine', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(22, 1, 5, 'Electric Kettle', 'EK001', '1.8L Electric Kettle', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(23, 1, 5, 'Rice Cooker', 'RC001', 'Electric Rice Cooker', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(24, 1, 19, 'Standing Fan', 'FN001', '16 Inch Standing Fan', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(25, 1, 19, 'Ceiling Fan', 'FN002', '56 Inch Ceiling Fan', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(26, 1, 19, 'Table Fan', 'FN003', '12 Inch Table Fan', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(27, 1, 5, 'Blender', 'BL001', 'Kitchen Blender', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(28, 1, 5, 'Juicer', 'JC001', 'Electric Juicer', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(29, 1, 5, 'Toaster', 'TS001', '2 Slice Toaster', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(30, 1, 15, 'Generator 2.5KVA', 'GN001', 'Portable Generator', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(31, 1, 15, 'Generator 5KVA', 'GN002', 'Heavy Duty Generator', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(32, 1, 14, 'Inverter 3.5KVA', 'IV001', 'Home Inverter System', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(33, 1, 16, 'Solar Panel 300W', 'SL001', '300 Watt Solar Panel', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(34, 1, 1, 'DSTV Decoder', 'DC001', 'Satellite TV Decoder', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:03:57', 0, NULL),
(35, 1, 1, 'DVD Player', 'DVD001', 'DVD Multimedia Player', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:03:57', 0, NULL),
(36, 1, 1, 'Projector', 'PJ001', 'HD Multimedia Projector', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:03:57', 0, NULL),
(37, 1, 8, 'Smartphone Android', 'PH001', 'Android Smartphone', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(38, 1, 9, 'Tablet 10 Inch', 'TB001', 'Android Tablet', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(39, 1, 10, 'Laptop Core i5', 'LP001', 'Laptop Computer', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(40, 1, 14, 'Power Bank 20000mAh', 'PB001', 'Portable Power Bank', 1, NULL, 1, '2026-06-16 10:18:03', '2026-08-04 18:08:42', 0, NULL),
(41, 1, 19, 'high speed cooler', 'ITM1001', 'High Speed Cooler', 1, NULL, 2, '2026-08-04 13:04:24', '2026-08-04 13:18:05', 0, NULL),
(42, 1, 19, 'wall hanging fan', 'ITM1002', 'Wall Hanging Fan', 1, NULL, 2, '2026-08-04 13:33:59', '2026-08-04 13:33:59', 0, NULL),
(43, 1, 2, 'asdsadsad', 'ITM1003', 'asdsadsa', 1, NULL, 2, '2026-08-10 09:56:59', '2026-08-10 09:56:59', 0, NULL),
(44, 1, 2, 'ac', 'ITM1004', 'sddfdsfsdf', 1, NULL, 2, '2026-08-10 10:08:05', '2026-08-10 10:08:18', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `module_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `route_name` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_12_29_111037_create_categories_table', 2),
(5, '2025_12_29_111323_create_products_table', 2),
(6, '2025_12_29_111440_create_product_images_table', 2),
(7, '2025_12_29_111532_create_orders_table', 2),
(8, '2025_12_29_111618_create_order_items_table', 2),
(9, '2025_12_29_112302_add_image_to_categories_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `modifier_options`
--

CREATE TABLE `modifier_options` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `modifier_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `account_id`, `name`, `slug`, `icon`, `sort_order`, `status`, `is_deleted`, `created_at`, `updated_at`) VALUES
(1, 3, 'Designation', 'designation', NULL, 0, 1, 0, '2026-07-24 13:17:00', '2026-07-24 13:22:00'),
(2, 3, 'Asasa', 'as', NULL, 0, 0, 1, '2026-07-24 13:19:59', '2026-07-24 13:35:21');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('addmin@mnclientsuite.com', '$2y$12$BZ/EQJy34E7vrA0eX5urXuvVAZ89QX.tpKuSpl1iWPtsO8JbeZpzW', '2026-01-24 07:35:44'),
('adeala.amara@gmail.com', '$2y$12$c8jhEIsUN7hgvv4OMDtV3e8gmxXPaqztRMrLi4aSmLUShCYmo2/0O', '2026-07-25 17:51:44'),
('jerry@ebere.com', '$2y$12$HfwE2vbq5gakUnApC7yh4uoxtZI46gntbN8BBKqWBE6LerBSSC0Oq', '2026-04-12 10:04:17');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `short_name` varchar(50) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `account_id`, `name`, `short_name`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cash', 'cash', 1, NULL, '2026-08-03 09:05:11', '2026-08-03 09:05:11'),
(2, 1, 'Bank Transfer', 'transfer', 1, NULL, '2026-08-03 09:05:11', '2026-08-03 09:05:11'),
(3, 1, 'Card Payment', 'card', 1, NULL, '2026-08-03 09:05:11', '2026-08-03 09:05:11'),
(4, 1, 'Mobile Money', 'mobile_money', 0, NULL, '2026-08-03 09:05:11', '2026-08-03 09:05:11'),
(5, 1, 'POS', 'pos', 1, NULL, '2026-08-03 09:05:11', '2026-08-03 09:05:11'),
(6, 1, 'Cheque', 'cheque', 0, NULL, '2026-08-03 09:05:11', '2026-08-03 09:05:11');

-- --------------------------------------------------------

--
-- Table structure for table `payment_types`
--

CREATE TABLE `payment_types` (
  `id` bigint(20) NOT NULL,
  `account_id` int(11) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=>acctive, 0 inactive',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `payment_types`
--

INSERT INTO `payment_types` (`id`, `account_id`, `short_name`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'full', 'Full Payment', 1, '2026-08-03 14:35:11', '2026-08-03 14:35:11'),
(2, 1, 'partial', 'Split Payment', 1, '2026-08-03 14:35:11', '2026-08-03 14:35:11'),
(3, 1, 'credit', 'Credit Payment', 1, '2026-08-03 14:35:11', '2026-08-03 14:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `module_id` bigint(20) UNSIGNED NOT NULL,
  `menu_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `master_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `barcode` varchar(100) DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `image` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `track_stock` tinyint(1) DEFAULT 1,
  `status` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL,
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=>not deleted,1=>deleted',
  `deleted_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `account_id`, `category_id`, `master_item_id`, `name`, `sku`, `barcode`, `selling_price`, `cost_price`, `image`, `description`, `slug`, `track_stock`, `status`, `created_at`, `updated_at`, `deleted_at`, `is_deleted`, `deleted_by`) VALUES
(1, 1, 7, 17, 'home theatre', 'HOM-1', '1893176989052', 6000.00, 6000.00, NULL, '5.1 Channel Home Theatre System', 'home-theatre', 1, 1, '2026-08-16 05:43:12', '2026-08-16 05:43:12', NULL, 0, NULL),
(2, 1, 7, 17, 'home theatre', 'HOM-2', '1330485756703', 7000.00, 7000.00, NULL, '5.1 Channel Home Theatre System', 'home-theatre', 1, 1, '2026-08-16 05:43:27', '2026-08-16 05:43:27', NULL, 0, NULL),
(3, 1, 7, 17, 'home theatre', 'HOM-3', '6486684537430', 6000.00, 6000.00, NULL, '5.1 Channel Home Theatre System', 'home-theatre', 1, 1, '2026-08-16 05:44:40', '2026-08-16 05:44:40', NULL, 0, NULL),
(4, 1, 7, 17, 'home theatre', 'HOM-4', '9249405620012', 5500.00, 5500.00, NULL, '5.1 Channel Home Theatre System', 'home-theatre', 1, 1, '2026-08-16 05:45:01', '2026-08-16 05:45:01', NULL, 0, NULL),
(5, 1, 7, 17, 'home theatre', 'HOM-5', '8854833544536', 5500.00, 5500.00, NULL, '5.1 Channel Home Theatre System', 'home-theatre', 1, 1, '2026-08-16 05:55:39', '2026-08-16 05:55:39', NULL, 0, NULL),
(6, 1, 5, 27, 'blender', 'KIT-6', '9716638208251', 2100.00, 2100.00, NULL, 'Kitchen Blender', 'blender', 1, 1, '2026-08-16 05:56:27', '2026-08-16 05:56:27', NULL, 0, NULL),
(7, 1, 5, 27, 'blender', 'KIT-7', '7631999422981', 2200.00, 2200.00, NULL, 'Kitchen Blender', 'blender', 1, 1, '2026-08-16 05:56:44', '2026-08-16 05:56:44', NULL, 0, NULL),
(8, 1, 5, 27, 'blender', 'KIT-8', '5010478172685', 2100.00, 2100.00, NULL, 'Kitchen Blender', 'blender', 1, 1, '2026-08-16 05:57:17', '2026-08-16 05:57:17', NULL, 0, NULL),
(9, 1, 5, 27, 'blender', 'KIT-9', '6112938937830', 2200.00, 2200.00, NULL, 'Kitchen Blender', 'blender', 1, 1, '2026-08-16 05:57:41', '2026-08-16 05:57:41', NULL, 0, NULL),
(10, 1, 5, 27, 'blender', 'KIT-10', '5905128381817', 2300.00, 2300.00, NULL, 'Kitchen Blender', 'blender', 1, 1, '2026-08-16 05:57:59', '2026-08-16 05:57:59', NULL, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_modifiers`
--

CREATE TABLE `product_modifiers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_stocks`
--

CREATE TABLE `product_stocks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `master_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reserved_stock` decimal(15,2) NOT NULL DEFAULT 0.00,
  `low_stock_alert` decimal(15,2) DEFAULT 10.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_stocks`
--

INSERT INTO `product_stocks` (`id`, `account_id`, `warehouse_id`, `master_item_id`, `stock`, `reserved_stock`, `low_stock_alert`, `created_at`, `updated_at`) VALUES
(1, 1, 11, 17, 0.00, 0.00, 10.00, '2026-08-16 05:36:49', '2026-08-16 05:39:57'),
(2, 1, 11, 27, 0.00, 0.00, 10.00, '2026-08-16 05:36:49', '2026-08-16 05:39:57');

-- --------------------------------------------------------

--
-- Table structure for table `product_trackings`
--

CREATE TABLE `product_trackings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_id` bigint(20) UNSIGNED NOT NULL,
  `master_item_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `barcode` varchar(255) NOT NULL,
  `tracking_type` enum('batch','individual') NOT NULL,
  `batch_no` varchar(255) DEFAULT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `status` enum('in_stock','sold','returned','damaged','transferred') NOT NULL DEFAULT 'in_stock',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) DEFAULT NULL,
  `vendor_id` bigint(20) DEFAULT NULL,
  `warehouse_id` bigint(20) DEFAULT NULL,
  `purchase_no` varchar(50) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `account_id`, `vendor_id`, `warehouse_id`, `purchase_no`, `total`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 16, 11, 'PUR-20260816-9298', 35000.00, 1, 2, '2026-08-16 05:36:49', '2026-08-16 05:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` bigint(20) DEFAULT NULL,
  `master_item_id` bigint(20) DEFAULT NULL,
  `quantity` decimal(15,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `tracking_type` varchar(20) NOT NULL DEFAULT 'none',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `master_item_id`, `quantity`, `cost_price`, `total`, `tracking_type`, `created_at`, `updated_at`) VALUES
(1, 1, 17, 5.00, 5000.00, 25000.00, 'none', '2026-08-16 05:36:49', '2026-08-16 05:36:49'),
(2, 1, 27, 5.00, 2000.00, 10000.00, 'none', '2026-08-16 05:36:49', '2026-08-16 05:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_item_trackings`
--

CREATE TABLE `purchase_item_trackings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requisition_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requisition_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `barcode` varchar(255) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `batch_no` varchar(255) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `tracking_type` enum('none','batch','individual') NOT NULL DEFAULT 'none',
  `quantity` decimal(12,2) NOT NULL DEFAULT 1.00,
  `is_sold` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 not sold, 1 => sold, 2=> return,3=>damage',
  `is_reserved` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=>active,0=>cancel',
  `sold_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_item_trackings`
--

INSERT INTO `purchase_item_trackings` (`id`, `purchase_item_id`, `warehouse_id`, `store_id`, `requisition_id`, `requisition_item_id`, `barcode`, `serial_no`, `batch_no`, `expiry_date`, `tracking_type`, `quantity`, `is_sold`, `is_reserved`, `status`, `sold_at`, `created_at`, `updated_at`) VALUES
(1, 1, 11, NULL, 1, 1, '1893176989052', NULL, NULL, NULL, 'none', 1.00, 0, 1, 1, NULL, '2026-08-16 05:36:49', '2026-08-17 10:25:06'),
(2, 1, 11, NULL, 1, 2, '1330485756703', NULL, NULL, NULL, 'none', 1.00, 0, 1, 1, NULL, '2026-08-16 05:36:49', '2026-08-16 05:39:57'),
(3, 1, 11, NULL, 1, 3, '8854833544536', NULL, NULL, NULL, 'none', 1.00, 0, 1, 1, NULL, '2026-08-16 05:36:49', '2026-08-16 07:07:48'),
(4, 1, 11, NULL, 1, 4, '9249405620012', NULL, NULL, NULL, 'none', 1.00, 0, 1, 1, NULL, '2026-08-16 05:36:49', '2026-08-16 05:39:57'),
(5, 1, 11, NULL, 1, 5, '6486684537430', NULL, NULL, NULL, 'none', 1.00, 0, 1, 1, NULL, '2026-08-16 05:36:49', '2026-08-16 05:39:57'),
(6, 2, 11, 1, 1, 6, '5905128381817', NULL, NULL, NULL, 'none', 1.00, 1, 1, 1, '2026-08-16 05:59:11', '2026-08-16 05:36:49', '2026-08-16 05:59:11'),
(7, 2, 11, 1, 1, 7, '6112938937830', NULL, NULL, NULL, 'none', 1.00, 1, 1, 1, '2026-08-16 08:38:11', '2026-08-16 05:36:49', '2026-08-16 08:38:11'),
(8, 2, 11, 1, 1, 8, '5010478172685', NULL, NULL, NULL, 'none', 1.00, 1, 1, 1, '2026-08-16 08:42:23', '2026-08-16 05:36:49', '2026-08-16 08:42:23'),
(9, 2, 11, NULL, 1, 9, '7631999422981', NULL, NULL, NULL, 'none', 1.00, 0, 1, 1, NULL, '2026-08-16 05:36:49', '2026-08-16 08:45:59'),
(10, 2, 11, 1, 1, 10, '9716638208251', NULL, NULL, NULL, 'none', 1.00, 1, 1, 1, '2026-08-16 08:44:39', '2026-08-16 05:36:49', '2026-08-16 08:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `requisitions`
--

CREATE TABLE `requisitions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `from_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `for_store_id` bigint(20) UNSIGNED NOT NULL,
  `requisition_no` varchar(100) NOT NULL,
  `date` datetime NOT NULL,
  `total_qty` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active,0=cancel,2=>Partial moved to store, 3=>Complete Moved',
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisitions`
--

INSERT INTO `requisitions` (`id`, `account_id`, `from_warehouse_id`, `for_store_id`, `requisition_no`, `date`, `total_qty`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 11, 1, 'REQ-20260816-7756', '2026-08-16 00:00:00', 10.00, 3, 2, '2026-08-16 05:39:57', '2026-08-16 05:57:59');

-- --------------------------------------------------------

--
-- Table structure for table `requisition_items`
--

CREATE TABLE `requisition_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `requisition_id` bigint(20) UNSIGNED NOT NULL,
  `master_item_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_tracking_id` bigint(20) DEFAULT NULL,
  `qty` decimal(12,2) NOT NULL,
  `accepted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '0=cancelled,1=active,2=accepted',
  `cancelled_by` bigint(20) DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requisition_items`
--

INSERT INTO `requisition_items` (`id`, `requisition_id`, `master_item_id`, `purchase_item_tracking_id`, `qty`, `accepted_by`, `created_at`, `updated_at`, `status`, `cancelled_by`, `cancelled_at`) VALUES
(1, 1, 17, 1, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:43:12', 1, NULL, NULL),
(2, 1, 17, 2, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:43:27', 1, NULL, NULL),
(3, 1, 17, 3, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:55:39', 1, NULL, NULL),
(4, 1, 17, 4, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:45:01', 1, NULL, NULL),
(5, 1, 17, 5, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:44:40', 1, NULL, NULL),
(6, 1, 27, 6, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:57:59', 1, NULL, NULL),
(7, 1, 27, 7, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:57:41', 1, NULL, NULL),
(8, 1, 27, 8, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:57:17', 1, NULL, NULL),
(9, 1, 27, 9, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:56:44', 1, NULL, NULL),
(10, 1, 27, 10, 1.00, 2, '2026-08-16 05:39:57', '2026-08-16 05:56:27', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `requisition_item_trackings`
--

CREATE TABLE `requisition_item_trackings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `requisition_item_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_tracking_id` bigint(20) UNSIGNED NOT NULL,
  `barcode` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE `routes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `uri` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `middleware` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `routes`
--

INSERT INTO `routes` (`id`, `method`, `uri`, `name`, `action`, `middleware`, `created_at`, `updated_at`) VALUES
(1, 'GET,HEAD', 'admin', 'dashboard', '\\App\\Http\\Controllers\\Admin\\DashboardController@index', 'web,auth,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(2, 'GET,HEAD', 'admin/staff', 'admin.staff', 'App\\Http\\Controllers\\Admin\\StaffController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(3, 'GET,HEAD', 'admin/staff/index', 'admin.staff.index', 'App\\Http\\Controllers\\Admin\\StaffController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(4, 'GET,HEAD', 'admin/staff/add', 'admin.staff.add', 'App\\Http\\Controllers\\Admin\\StaffController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(5, 'POST', 'admin/staff/store', 'admin.staff.store', 'App\\Http\\Controllers\\Admin\\StaffController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(6, 'GET,HEAD', 'admin/staff/edit/{id}', 'admin.staff.edit', 'App\\Http\\Controllers\\Admin\\StaffController@editstaff', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(7, 'POST', 'admin/staff/update', 'admin.staff.update', 'App\\Http\\Controllers\\Admin\\StaffController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(8, 'POST', 'admin/staff/updatepassword', 'admin.staff.updatepassword', 'App\\Http\\Controllers\\Admin\\StaffController@updatepassword', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(10, 'POST', 'admin/members/destroy', 'destroy', 'App\\Http\\Controllers\\Admin\\StaffController@delete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(11, 'POST', 'admin/members/status-update', 'statusUpdate', 'App\\Http\\Controllers\\Admin\\StaffController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(12, 'GET,HEAD', 'profile', 'profile.edit', 'App\\Http\\Controllers\\ProfileController@edit', 'web,route.permission,auth,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(13, 'PATCH', 'profile', 'profile.update', 'App\\Http\\Controllers\\ProfileController@update', 'web,route.permission,auth,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(14, 'DELETE', 'profile', 'profile.destroy', 'App\\Http\\Controllers\\ProfileController@destroy', 'web,route.permission,auth,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(17, 'POST', 'update-password', 'update-password', 'App\\Http\\Controllers\\Auth\\PasswordController@update', 'web', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(19, 'POST', 'billing/scan', 'billing.scan', 'App\\Http\\Controllers\\BillingController@scanProduct', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(20, 'POST', 'billing/complete', 'billing.complete', 'App\\Http\\Controllers\\BillingController@completeSale', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(21, 'GET,HEAD', 'register', 'register', 'App\\Http\\Controllers\\Auth\\RegisteredUserController@create', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(22, 'POST', 'modelregister', 'register.store', 'App\\Http\\Controllers\\Auth\\RegisteredUserController@modelstore', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(23, 'POST', 'check-email', 'check.email', 'App\\Http\\Controllers\\Auth\\RegisteredUserController@checkEmail', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(24, 'GET,HEAD', 'login', 'login', 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@create', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(25, 'POST', 'model-login', 'model.login', 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@modellogin', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(26, 'GET,HEAD', 'forgot-password', 'password.request', 'App\\Http\\Controllers\\Auth\\PasswordResetLinkController@create', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(27, 'POST', 'forgot-password', 'password.email', 'App\\Http\\Controllers\\Auth\\PasswordResetLinkController@store', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(28, 'POST', 'forgot-password-model', 'password.email.model', 'App\\Http\\Controllers\\Auth\\PasswordResetLinkController@storeModel', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(29, 'GET,HEAD', 'reset-password/{token}', 'password.reset', 'App\\Http\\Controllers\\Auth\\NewPasswordController@create', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(30, 'POST', 'reset-password', 'password.store', 'App\\Http\\Controllers\\Auth\\NewPasswordController@store', 'web,guest', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(31, 'GET,HEAD', 'verify-email', 'verification.notice', 'App\\Http\\Controllers\\Auth\\EmailVerificationPromptController', 'web,auth', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(32, 'GET,HEAD', 'verify-email/{id}/{hash}', 'verification.verify', 'App\\Http\\Controllers\\Auth\\VerifyEmailController', 'web,auth,signed,throttle:6,1', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(33, 'POST', 'email/verification-notification', 'verification.send', 'App\\Http\\Controllers\\Auth\\EmailVerificationNotificationController@store', 'web,auth,throttle:6,1', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(34, 'GET,HEAD', 'confirm-password', 'password.confirm', 'App\\Http\\Controllers\\Auth\\ConfirmablePasswordController@show', 'web,auth', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(35, 'PUT', 'password', 'password.update', 'App\\Http\\Controllers\\Auth\\PasswordController@update', 'web,auth', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(36, 'POST', 'logout', 'logout', 'App\\Http\\Controllers\\Auth\\AuthenticatedSessionController@destroy', 'web,auth', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(37, 'GET,HEAD', 'admin/dashboard', 'admin.dashboard', 'App\\Http\\Controllers\\Admin\\DashboardController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(38, 'GET,HEAD', 'admin/change-password', 'admin.change-password', 'App\\Http\\Controllers\\Auth\\PasswordController@editPassword', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(39, 'GET,HEAD', 'admin/profile', 'admin.profile', 'App\\Http\\Controllers\\ProfileController@editprofile', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(40, 'POST', 'admin/updateprofile', 'update.profile', 'App\\Http\\Controllers\\ProfileController@updateProfile', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(41, 'GET,HEAD', 'admin/categories', 'admin.categories.index', 'App\\Http\\Controllers\\Admin\\CategoryController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(42, 'GET,HEAD', 'admin/categories/create', 'admin.categories.create', 'App\\Http\\Controllers\\Admin\\CategoryController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(43, 'POST', 'admin/categories/store', 'admin.categories.store', 'App\\Http\\Controllers\\Admin\\CategoryController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(44, 'GET,HEAD', 'admin/categories/edit/{id}', 'admin.categories.edit', 'App\\Http\\Controllers\\Admin\\CategoryController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(45, 'POST', 'admin/categories/update', 'admin.categories.update', 'App\\Http\\Controllers\\Admin\\CategoryController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(46, 'POST', 'admin/categories/delete', 'admin.categories.delete', 'App\\Http\\Controllers\\Admin\\CategoryController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(47, 'POST', 'admin/categories/status', 'admin.categories.statusUpdate', 'App\\Http\\Controllers\\Admin\\CategoryController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(48, 'POST', 'admin/categories/softdelete', 'admin.categories.softdelete', 'App\\Http\\Controllers\\Admin\\CategoryController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(49, 'GET,HEAD', 'admin/products', 'admin.products', 'App\\Http\\Controllers\\Admin\\ProductController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(50, 'GET,HEAD', 'admin/products/create/{token?}', 'admin.products.create', 'App\\Http\\Controllers\\Admin\\ProductController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(51, 'POST', 'admin/products/store', 'admin.products.store', 'App\\Http\\Controllers\\Admin\\ProductController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(52, 'GET,HEAD', 'admin/products/edit/{id}', 'admin.products.edit', 'App\\Http\\Controllers\\Admin\\ProductController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(53, 'POST', 'admin/products/update', 'admin.products.update', 'App\\Http\\Controllers\\Admin\\ProductController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(54, 'POST', 'admin/products/products/delete', 'products.delete', 'App\\Http\\Controllers\\Admin\\ProductController@destroy', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(55, 'POST', 'admin/products/products/softdelete', 'admin.products.softdelete', 'App\\Http\\Controllers\\Admin\\ProductController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(56, 'POST', 'admin/modifiers/store', 'admin.modifiers.store', 'App\\Http\\Controllers\\Admin\\ProductModifierController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(57, 'GET,HEAD', 'admin/inventory', 'admin.inventory', 'App\\Http\\Controllers\\Admin\\InventoryController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(58, 'GET,HEAD', 'admin/inventory/manage/{id?}', 'admin.inventory.manage', 'App\\Http\\Controllers\\Admin\\InventoryController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(59, 'GET,HEAD', 'admin/inventory/manage/update/{token}', 'admin.inventory.update', 'App\\Http\\Controllers\\Admin\\InventoryController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(60, 'POST', 'admin/stock-adjust', 'admin.stock.adjust', 'App\\Http\\Controllers\\Admin\\StockAdjustmentController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(61, 'GET,HEAD', 'admin/barcode', 'admin.barcode', 'App\\Http\\Controllers\\Admin\\BarcodeController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(62, 'GET,HEAD', 'admin/barcode/no-barcode', 'admin.no-barcode', 'App\\Http\\Controllers\\Admin\\BarcodeController@nobarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(63, 'GET,HEAD', 'admin/barcode/sales-barcode', 'admin.sales-barcode', 'App\\Http\\Controllers\\Admin\\BarcodeController@salesBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(64, 'GET,HEAD', 'admin/barcode/return-barcode', 'admin.return-barcode', 'App\\Http\\Controllers\\Admin\\BarcodeController@returnBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(65, 'GET,HEAD', 'admin/barcode/damage-barcode', 'admin.damage-barcode', 'App\\Http\\Controllers\\Admin\\BarcodeController@damageBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(66, 'GET,HEAD', 'admin/barcode/deduct-barcode', 'admin.deduct-barcode', 'App\\Http\\Controllers\\Admin\\BarcodeController@deductBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(67, 'POST', 'admin/barcode/validateBarcode', 'admin.barcode.validateBarcode', 'App\\Http\\Controllers\\Admin\\BarcodeController@validateBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(68, 'GET,HEAD', 'administrator', 'administrator.dashboard', 'App\\Http\\Controllers\\Administrator\\DashboardController@dashboard', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(69, 'GET,HEAD', 'administrator/dashboard', 'administrator.dashboard', 'App\\Http\\Controllers\\Administrator\\DashboardController@dashboard', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(70, 'GET,HEAD', 'administrator/change-password', 'administrator.change-password', 'App\\Http\\Controllers\\Auth\\PasswordController@editPassword', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(71, 'GET,HEAD', 'administrator/profile', 'administrator.profile', 'App\\Http\\Controllers\\ProfileController@editprofile', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(72, 'POST', 'administrator/updateprofile', 'update.profile', 'App\\Http\\Controllers\\ProfileController@updateProfile', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(73, 'GET,HEAD', 'administrator/subscription', 'administrator.subscription', 'App\\Http\\Controllers\\Administrator\\SubscriptionController@index', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(74, 'POST', 'administrator/subscription/update', 'administrator.subscription.update', 'App\\Http\\Controllers\\Administrator\\SubscriptionController@update', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(75, 'GET,HEAD', 'administrator/subscription/add', 'administrator.subscription.add', 'App\\Http\\Controllers\\Administrator\\SubscriptionController@create', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(76, 'POST', 'administrator/subscription/store', 'administrator.subscription.store', 'App\\Http\\Controllers\\Administrator\\SubscriptionController@store', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(77, 'GET,HEAD', 'administrator/subscription/edit/{id}', 'administrator.subscription.edit', 'App\\Http\\Controllers\\Administrator\\SubscriptionController@edit', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(78, 'POST', 'administrator/subscription/statusUpdate', 'administrator.subscription.statusUpdate', 'App\\Http\\Controllers\\Administrator\\SubscriptionController@statusUpdate', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(79, 'POST', 'administrator/subscription/destroy', 'administrator.subscription.destroy', 'App\\Http\\Controllers\\Administrator\\SubscriptionController@delete', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(80, 'GET,HEAD', 'administrator/subscription/downloadsubscriptionpdf', 'administrator.downloadsubscriptionpdf', 'App\\Http\\Controllers\\Administrator\\SubscriptionController@downloadsubscriptionpdf', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(81, 'GET,HEAD', 'administrator/account', 'administrator.accounts', 'App\\Http\\Controllers\\Administrator\\MyAccountController@index', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(82, 'GET,HEAD', 'administrator/account/add', 'administrator.account.add', 'App\\Http\\Controllers\\Administrator\\MyAccountController@create', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(83, 'POST', 'administrator/account/store', 'administrator.account.store', 'App\\Http\\Controllers\\Administrator\\MyAccountController@store', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(84, 'GET,HEAD', 'administrator/account/edit/{id}', 'administrator.account.edit', 'App\\Http\\Controllers\\Administrator\\MyAccountController@edit', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(85, 'POST', 'administrator/account/statusUpdate', 'administrator.account.statusUpdate', 'App\\Http\\Controllers\\Administrator\\MyAccountController@statusUpdate', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(86, 'POST', 'administrator/account/destroy', 'administrator.account.destroy', 'App\\Http\\Controllers\\Administrator\\MyAccountController@delete', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(87, 'POST', 'administrator/account/update', 'administrator.account.update', 'App\\Http\\Controllers\\Administrator\\MyAccountController@update', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(88, 'GET,HEAD', 'administrator/account/downloadHotelpdf', 'administrator.downloadaccountpdf', 'App\\Http\\Controllers\\Administrator\\MyAccountController@downloadaccountpdf', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(89, 'GET,HEAD', 'administrator/account/subscribe/{account}', 'administrator.subscribe', 'App\\Http\\Controllers\\Administrator\\MyAccountController@subscribe', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(90, 'POST', 'administrator/account/storesubscribe', 'administrator.store.subscribe', 'App\\Http\\Controllers\\Administrator\\MyAccountController@storesubscribe', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(91, 'POST', 'administrator/account/subscriptionPrice', 'administrator.getsubscriptionprice', 'App\\Http\\Controllers\\Administrator\\MyAccountController@getsubscriptionprice', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(92, 'POST', 'administrator/account/accountsubscriptionpaymentdetails', 'administrator.accountsubscriptionpaymentdetails', 'App\\Http\\Controllers\\Administrator\\MyAccountController@accountsubscriptionpaymentdetails', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(93, 'POST', 'administrator/account/updatepassword', 'administrator.user.updatepassword', 'App\\Http\\Controllers\\Administrator\\MyAccountController@updatepassword', 'web,auth,role:1', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(94, 'GET,HEAD', 'administrator/acl', 'administrator.acl', 'App\\Http\\Controllers\\Administrator\\AclController@index', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(95, 'GET,HEAD', 'administrator/acl/sync', 'administrator.acl.sync', 'App\\Http\\Controllers\\Administrator\\AclController@sync', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(96, 'POST', 'administrator/acl/update', 'administrator.acl.update', 'App\\Http\\Controllers\\Administrator\\AclController@update', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(97, 'GET,HEAD', 'sales', 'admin.sales.index', 'App\\Http\\Controllers\\SaleController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(98, 'GET,HEAD', 'sales/{sale}', 'admin.sales.show', 'App\\Http\\Controllers\\SaleController@show', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(99, 'GET,HEAD', 'admin/sync-routes', 'syncroutes', 'App\\Http\\Controllers\\Administrator\\AclController@syncRoutes', 'web,auth,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(100, 'GET,HEAD', 'admin/print/invoice/{id}', 'printinvoice', 'App\\Http\\Controllers\\SaleController@printinvoice', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(101, 'GET,HEAD', 'admin/coupons', 'admin.coupons.index', 'App\\Http\\Controllers\\Admin\\CouponController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(102, 'GET,HEAD', 'admin/coupons/create', 'admin.coupons.create', 'App\\Http\\Controllers\\Admin\\CouponController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(103, 'POST', 'admin/coupons/store', 'admin.coupons.store', 'App\\Http\\Controllers\\Admin\\CouponController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(104, 'GET,HEAD', 'admin/coupons/edit/{id}', 'admin.coupons.edit', 'App\\Http\\Controllers\\Admin\\CouponController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(105, 'POST', 'admin/coupons/update', 'admin.coupons.update', 'App\\Http\\Controllers\\Admin\\CouponController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(106, 'POST', 'admin/coupons/delete', 'admin.coupons.destroy', 'App\\Http\\Controllers\\Admin\\CouponController@destroy', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(107, 'POST', 'admin/coupons/status-update', 'admin.coupons.status', 'App\\Http\\Controllers\\Admin\\CouponController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(108, 'POST', 'admin/coupons/soft-delete', 'admin.coupons.softdelete', 'App\\Http\\Controllers\\Admin\\CouponController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(109, 'POST', 'admin/coupon/apply', 'coupon.apply', 'App\\Http\\Controllers\\Admin\\CouponController@apply', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(110, 'GET,HEAD', 'admin/customers', 'admin.customers.index', 'App\\Http\\Controllers\\Admin\\CustomerController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(111, 'GET,HEAD', 'admin/customers/create', 'admin.customers.create', 'App\\Http\\Controllers\\Admin\\CustomerController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(112, 'POST', 'admin/customers/store', 'admin.customers.store', 'App\\Http\\Controllers\\Admin\\CustomerController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(113, 'GET,HEAD', 'admin/customers/edit/{id}', 'admin.customers.edit', 'App\\Http\\Controllers\\Admin\\CustomerController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(114, 'PUT', 'admin/customers/update', 'admin.customers.update', 'App\\Http\\Controllers\\Admin\\CustomerController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(115, 'POST', 'admin/customers/delete', 'admin.customers.destroy', 'App\\Http\\Controllers\\Admin\\CustomerController@destroy', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(116, 'POST', 'admin/customers/soft-delete', 'admin.customers.softdelete', 'App\\Http\\Controllers\\Admin\\CustomerController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(117, 'POST', 'admin/customers/status-update', 'admin.customers.status', 'App\\Http\\Controllers\\Admin\\CustomerController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(118, 'POST', 'admin/customers/find-by-phone', 'admin.customers.findByPhone', 'App\\Http\\Controllers\\Admin\\CustomerController@findByPhone', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(119, 'POST', 'admin/customers/quick-store', 'admin.customers.quickStore', 'App\\Http\\Controllers\\Admin\\CustomerController@quickStore', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(120, 'GET,HEAD', 'create/transaction', 'billing.index', 'App\\Http\\Controllers\\BillingController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(121, 'POST', 'admin/send-invoice-email', 'sendinvoice', 'App\\Http\\Controllers\\SaleController@sendInvoiceEmail', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(122, 'POST', 'admin/customers/update-by-phone', 'admin.customers.updateByPhone', 'App\\Http\\Controllers\\Admin\\CustomerController@updateByPhone', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(123, 'GET,HEAD', 'admin/download-invoice/{id}', 'downloadinvoice', 'App\\Http\\Controllers\\SaleController@downloadInvoice', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(124, 'GET,HEAD', 'admin/graph', 'graph', 'App\\Http\\Controllers\\Admin\\DashboardController@graph', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(125, 'GET,HEAD', 'admin/staff/exportpdf', 'staff.pdf', 'App\\Http\\Controllers\\Admin\\StaffController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(126, 'GET,HEAD', 'admin/staff/exportcsv', 'staff.csv', 'App\\Http\\Controllers\\Admin\\StaffController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(127, 'GET,HEAD', 'sales/export-pdf', 'admin.sales.exportPdf', 'App\\Http\\Controllers\\SaleController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(128, 'GET,HEAD', 'sales/export-csv', 'admin.sales.exportCsv', 'App\\Http\\Controllers\\SaleController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(129, 'GET,HEAD', 'reports/daily-sales', 'reports.daily.sales', 'App\\Http\\Controllers\\ReportController@dailySales', 'web,auth,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(130, 'GET,HEAD', 'reports/daily-sales/pdf', 'reports.daily.sales.pdf', 'App\\Http\\Controllers\\ReportController@dailySalesPdf', 'web,auth,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(131, 'GET,HEAD', 'reports/daily-sales/csv', 'reports.daily.sales.csv', 'App\\Http\\Controllers\\ReportController@dailySalesCsv', 'web,auth,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(132, 'GET,HEAD', 'admin/categories/export-pdf', 'admin.category.exportPdf', 'App\\Http\\Controllers\\Admin\\CategoryController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(133, 'GET,HEAD', 'admin/categories/export-csv', 'admin.category.exportCsv', 'App\\Http\\Controllers\\Admin\\CategoryController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(134, 'GET,HEAD', 'admin/products/pdf', 'admin.products.pdf', 'App\\Http\\Controllers\\Admin\\ProductController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(135, 'GET,HEAD', 'admin/products/csv', 'admin.products.csv', 'App\\Http\\Controllers\\Admin\\ProductController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(136, 'GET,HEAD', 'admin/inventory/export-pdf', 'admin.inventory.exportPdf', 'App\\Http\\Controllers\\Admin\\InventoryController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(137, 'GET,HEAD', 'admin/inventory/export-csv', 'admin.inventory.exportCsv', 'App\\Http\\Controllers\\Admin\\InventoryController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(138, 'GET,HEAD', 'admin/coupons/export-pdf', 'admin.coupons.exportPdf', 'App\\Http\\Controllers\\Admin\\CouponController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(139, 'GET,HEAD', 'admin/coupons/export-csv', 'admin.coupons.exportCsv', 'App\\Http\\Controllers\\Admin\\CouponController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(140, 'GET,HEAD', 'admin/customers/export-pdf', 'admin.customers.exportPdf', 'App\\Http\\Controllers\\Admin\\CustomerController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(141, 'GET,HEAD', 'admin/customers/export-csv', 'admin.customers.exportCsv', 'App\\Http\\Controllers\\Admin\\CustomerController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(142, 'GET,HEAD', 'barcode/scan-product', 'barcode.scan.product', 'App\\Http\\Controllers\\BarcodeController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(144, 'GET,HEAD', 'barcode/{id}', 'barcode.form', 'App\\Http\\Controllers\\BarcodeController@barcodeForm', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(145, 'POST', 'barcode/print', 'barcode.print', 'App\\Http\\Controllers\\BarcodeController@barcodePrint', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(146, 'GET,HEAD', 'attendance', 'attendance.index', 'App\\Http\\Controllers\\Admin\\AttendanceController@index', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(147, 'POST', 'attendance/store', 'attendance.store', 'App\\Http\\Controllers\\Admin\\AttendanceController@store', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(148, 'GET,HEAD', 'attendance/punch-in', 'attendance.punch.in', 'App\\Http\\Controllers\\Admin\\AttendanceController@punchIn', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(149, 'POST', 'attendance/punch-out', 'attendance.punch.out', 'App\\Http\\Controllers\\Admin\\AttendanceController@punchOut', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(150, 'GET,HEAD', 'attendance/report', 'attendance.report', 'App\\Http\\Controllers\\Admin\\AttendanceController@report', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(151, 'GET,HEAD', 'attendance/today-summary', 'attendance.today.summary', 'App\\Http\\Controllers\\Admin\\AttendanceController@todaySummary', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(152, 'DELETE', 'attendance/{id}', 'attendance.destroy', 'App\\Http\\Controllers\\Admin\\AttendanceController@destroy', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(153, 'GET,HEAD', 'attendance/report/pdf', 'attendance.exportPdf', 'App\\Http\\Controllers\\Admin\\AttendanceController@exportPdf', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(154, 'GET,HEAD', 'attendance/report/csv', 'attendance.exportCsv', 'App\\Http\\Controllers\\Admin\\AttendanceController@exportCsv', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(155, 'GET,HEAD', 'admin/vendors', 'admin.vendors.index', 'App\\Http\\Controllers\\Admin\\VendorController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(156, 'GET,HEAD', 'admin/vendors/create', 'admin.vendors.create', 'App\\Http\\Controllers\\Admin\\VendorController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(157, 'POST', 'admin/vendors/store', 'admin.vendors.store', 'App\\Http\\Controllers\\Admin\\VendorController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(158, 'GET,HEAD', 'admin/vendors/edit/{id}', 'admin.vendors.edit', 'App\\Http\\Controllers\\Admin\\VendorController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(159, 'POST', 'admin/vendors/update', 'admin.vendors.update', 'App\\Http\\Controllers\\Admin\\VendorController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(160, 'POST', 'admin/vendors/delete', 'admin.vendors.delete', 'App\\Http\\Controllers\\Admin\\VendorController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(161, 'POST', 'admin/vendors/status-update', 'admin.vendors.statusUpdate', 'App\\Http\\Controllers\\Admin\\VendorController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(162, 'GET,HEAD', 'admin/vendors/export-pdf', 'admin.vendors.exportPdf', 'App\\Http\\Controllers\\Admin\\VendorController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(163, 'GET,HEAD', 'admin/vendors/export-csv', 'admin.vendors.exportCsv', 'App\\Http\\Controllers\\Admin\\VendorController@exportExcel', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(164, 'GET,HEAD', 'admin/vendors/payment/{id}', 'admin.vendors.paymentForm', 'App\\Http\\Controllers\\Admin\\VendorController@paymentForm', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(165, 'POST', 'admin/vendors/payment/store', 'admin.vendors.paymentStore', 'App\\Http\\Controllers\\Admin\\VendorController@paymentStore', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(166, 'GET,HEAD', 'admin/vendors/ledger/{id}', 'admin.vendors.ledger', 'App\\Http\\Controllers\\Admin\\VendorController@ledger', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(167, 'GET,HEAD', 'admin/warehouses', 'admin.warehouses.index', 'App\\Http\\Controllers\\Admin\\WarehouseController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(168, 'GET,HEAD', 'admin/warehouses/create', 'admin.warehouses.create', 'App\\Http\\Controllers\\Admin\\WarehouseController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(169, 'POST', 'admin/warehouses/store', 'admin.warehouses.store', 'App\\Http\\Controllers\\Admin\\WarehouseController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(170, 'GET,HEAD', 'admin/warehouses/edit/{id}', 'admin.warehouses.edit', 'App\\Http\\Controllers\\Admin\\WarehouseController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(171, 'POST', 'admin/warehouses/update/{id}', 'admin.warehouses.update', 'App\\Http\\Controllers\\Admin\\WarehouseController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(172, 'POST', 'admin/warehouses/softdelete/{id}', 'admin.warehouses.softdelete', 'App\\Http\\Controllers\\Admin\\WarehouseController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(173, 'POST', 'admin/warehouses/status-update', 'admin.warehouses.statusUpdate', 'App\\Http\\Controllers\\Admin\\WarehouseController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(174, 'GET,HEAD', 'admin/warehouses/stock-transfer/create', 'admin.warehouses.stockTransfer.create', 'App\\Http\\Controllers\\Admin\\WarehouseController@transferForm', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(175, 'POST', 'admin/warehouses/stock-transfer', 'admin.warehouses.stockTransfer.store', 'App\\Http\\Controllers\\Admin\\WarehouseController@transferStore', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(176, 'GET,HEAD', 'admin/warehouses/stock-transfer/export-pdf', 'admin.warehouses.exportPdf', 'App\\Http\\Controllers\\Admin\\WarehouseController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(177, 'GET,HEAD', 'admin/warehouses/stock-transfer/export-csv', 'admin.warehouses.exportCsv', 'App\\Http\\Controllers\\Admin\\WarehouseController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(178, 'GET,HEAD', 'admin/purchases', 'admin.purchases.index', 'App\\Http\\Controllers\\Admin\\PurchaseController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(179, 'GET,HEAD', 'admin/purchases/create', 'admin.purchases.create', 'App\\Http\\Controllers\\Admin\\PurchaseController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(180, 'POST', 'admin/purchases/store', 'admin.purchases.store', 'App\\Http\\Controllers\\Admin\\PurchaseController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(181, 'GET,HEAD', 'admin/purchases/view/{id}', 'admin.purchases.view', 'App\\Http\\Controllers\\Admin\\PurchaseController@show', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(182, 'POST', 'admin/purchases/cancel', 'admin.purchases.cancel', 'App\\Http\\Controllers\\Admin\\PurchaseController@destroy', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(183, 'POST', 'admin/purchases/softdelete', 'admin.purchases.softdelete', 'App\\Http\\Controllers\\Admin\\PurchaseController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(184, 'POST', 'admin/purchases/status-update', 'admin.purchases.status.update', 'App\\Http\\Controllers\\Admin\\PurchaseController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(185, 'GET,HEAD', 'admin/purchases/exportpdf', 'admin.purchases.exportPdf', 'App\\Http\\Controllers\\Admin\\PurchaseController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(186, 'GET,HEAD', 'admin/purchases/exportcsv', 'admin.purchases.exportCsv', 'App\\Http\\Controllers\\Admin\\PurchaseController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(187, 'GET,HEAD', 'admin/purchases/view/ajax/{id}', 'admin.purchases.view.ajax', 'App\\Http\\Controllers\\Admin\\PurchaseController@viewAjax', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(189, 'GET,HEAD', 'admin/purchase-returns/create/{id}', 'admin.admin.purchase_returns.create', 'App\\Http\\Controllers\\Admin\\PurchaseReturnController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(190, 'POST', 'admin/purchase-returns/store', 'admin.admin.purchase_returns.store', 'App\\Http\\Controllers\\Admin\\PurchaseReturnController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(191, 'GET,HEAD', 'admin/stock-returns', 'admin.stock_returns.index', 'App\\Http\\Controllers\\Admin\\StockReturnController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(192, 'GET,HEAD', 'admin/stock-returns/create', 'admin.stock_returns.create', 'App\\Http\\Controllers\\Admin\\StockReturnController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(193, 'POST', 'admin/stock-returns/store', 'admin.stock_returns.store', 'App\\Http\\Controllers\\Admin\\StockReturnController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(195, 'GET,HEAD', 'admin/warehouses/{id}/products', 'admin.warehouses.products', 'App\\Http\\Controllers\\Admin\\WarehouseController@getWarehouseProducts', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(196, 'GET,HEAD', 'admin/warehouses/warehouse-product-stock', 'admin.warehouses.warehouse.product.stock', 'App\\Http\\Controllers\\Admin\\WarehouseController@getProductStock', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(197, 'GET,HEAD', 'admin/stock-returns/stock-check', 'admin.stock_returns.stock.check', 'App\\Http\\Controllers\\Admin\\StockReturnController@getStock', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(198, 'GET,HEAD', 'admin/stock-returns/show/{id}', 'admin.stock_returns.show', 'App\\Http\\Controllers\\Admin\\StockReturnController@show', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(199, 'GET,HEAD', 'admin/products/last-price', 'admin.products.lastPrice', 'App\\Http\\Controllers\\Admin\\ProductController@getLastPrice', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(200, 'GET,HEAD', 'admin/products/search', 'admin.products.search', 'App\\Http\\Controllers\\Admin\\ProductController@search', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(201, 'GET,HEAD', 'admin/stock-returns/view/ajax/{id}', 'admin.stock_returns.view.ajax', 'App\\Http\\Controllers\\Admin\\StockReturnController@viewAjax', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(202, 'POST', 'admin/stock-returns/cancel', 'admin.stock_returns.cancel', 'App\\Http\\Controllers\\Admin\\StockReturnController@cancel', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(203, 'GET,HEAD', 'admin/requisitions', 'admin.requisitions.index', 'App\\Http\\Controllers\\Admin\\RequisitionController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(204, 'GET,HEAD', 'admin/requisitions/create', 'admin.requisitions.create', 'App\\Http\\Controllers\\Admin\\RequisitionController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(205, 'POST', 'admin/requisitions/store', 'admin.requisitions.store', 'App\\Http\\Controllers\\Admin\\RequisitionController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(206, 'GET,HEAD', 'admin/requisitions/view/{id}', 'admin.requisitions.view', 'App\\Http\\Controllers\\Admin\\RequisitionController@show', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(207, 'POST', 'admin/requisitions/cancel', 'admin.requisitions.cancel', 'App\\Http\\Controllers\\Admin\\RequisitionController@cancel', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(208, 'GET,HEAD', 'admin/requisitions/requisition-products', 'admin.requisitions.requisition.products', 'App\\Http\\Controllers\\Admin\\RequisitionController@requisitionProducts', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(209, 'POST', 'admin/requisitions/complete', 'admin.requisitions.complete', 'App\\Http\\Controllers\\Admin\\RequisitionController@complete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(210, 'GET,HEAD', 'admin/requisitions/exportpdf', 'admin.requisitions.exportPdf', 'App\\Http\\Controllers\\Admin\\RequisitionController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(211, 'GET,HEAD', 'admin/requisitions/exportcsv', 'admin.requisitions.exportCsv', 'App\\Http\\Controllers\\Admin\\RequisitionController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(212, 'GET,HEAD', 'admin/requisitions/pdf/{id}', 'admin.requisitions.pdf', 'App\\Http\\Controllers\\Admin\\RequisitionController@pdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(213, 'GET,HEAD', 'admin/requisitions/csv/{id}', 'admin.requisitions.csv', 'App\\Http\\Controllers\\Admin\\RequisitionController@csv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(214, 'GET,HEAD', 'admin/requisitions/view/ajax/{id}', 'admin.requisitions.view.ajax', 'App\\Http\\Controllers\\Admin\\RequisitionController@viewAjax', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(225, 'GET,HEAD', 'admin/master-items', 'admin.master_items.index', 'App\\Http\\Controllers\\Admin\\MasterItemController@index', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(226, 'GET,HEAD', 'admin/master-items/create', 'admin.master_items.create', 'App\\Http\\Controllers\\Admin\\MasterItemController@create', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(227, 'POST', 'admin/master-items/store', 'admin.master_items.store', 'App\\Http\\Controllers\\Admin\\MasterItemController@store', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(228, 'GET,HEAD', 'admin/master-items/edit/{id}', 'admin.master_items.edit', 'App\\Http\\Controllers\\Admin\\MasterItemController@edit', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(229, 'POST', 'admin/master-items/update', 'admin.master_items.update', 'App\\Http\\Controllers\\Admin\\MasterItemController@update', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(230, 'POST', 'admin/master-items/delete', 'admin.master_items.delete', 'App\\Http\\Controllers\\Admin\\MasterItemController@delete', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(231, 'POST', 'admin/master-items/status-update', 'admin.master_items.status', 'App\\Http\\Controllers\\Admin\\MasterItemController@statusUpdate', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(232, 'GET,HEAD', 'admin/master-items/export-pdf', 'admin.master_items.exportPdf', 'App\\Http\\Controllers\\Admin\\MasterItemController@exportPdf', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(233, 'GET,HEAD', 'admin/master-items/export-csv', 'admin.master_items.exportCsv', 'App\\Http\\Controllers\\Admin\\MasterItemController@exportCsv', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(234, 'GET,HEAD', 'admin/master-items/search', 'admin.master_items.search', 'App\\Http\\Controllers\\Admin\\MasterItemController@search', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(235, 'POST', 'admin/master-items/store/ajax', 'admin.master_items.store.ajax', 'App\\Http\\Controllers\\Admin\\MasterItemController@storeAjax', 'web,auth,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(236, 'GET,HEAD', 'admin/requisitions/pending-posting', 'admin.requisitions.pending.posting', 'App\\Http\\Controllers\\Admin\\RequisitionController@pendingPosting', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(237, 'GET,HEAD', 'admin/warehouses/stock-listing', 'admin.warehouses.stock.listing', 'App\\Http\\Controllers\\Admin\\WarehouseController@stockListing', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(238, 'GET,HEAD', 'admin/warehouses/stock-listing/export/pdf', 'admin.warehouses.stock.listing.pdf', 'App\\Http\\Controllers\\Admin\\WarehouseController@stockListingPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(239, 'GET,HEAD', 'admin/warehouses/stock-listing/export/csv', 'admin.warehouses.stock.listing.csv', 'App\\Http\\Controllers\\Admin\\WarehouseController@stockListingCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(240, 'POST', 'admin/requisitions/cancel-item', 'admin.requisitions.cancel.item', 'App\\Http\\Controllers\\Admin\\RequisitionController@cancelItem', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(241, 'GET,HEAD', 'admin/stores', 'admin.stores.index', 'App\\Http\\Controllers\\Admin\\StoreController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(242, 'GET,HEAD', 'admin/stores/create', 'admin.stores.create', 'App\\Http\\Controllers\\Admin\\StoreController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(243, 'POST', 'admin/stores/store', 'admin.stores.store', 'App\\Http\\Controllers\\Admin\\StoreController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(244, 'GET,HEAD', 'admin/stores/edit/{id}', 'admin.stores.edit', 'App\\Http\\Controllers\\Admin\\StoreController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(245, 'POST', 'admin/stores/update', 'admin.stores.update', 'App\\Http\\Controllers\\Admin\\StoreController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(246, 'POST', 'admin/stores/delete', 'admin.stores.destroy', 'App\\Http\\Controllers\\Admin\\StoreController@destroy', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(247, 'POST', 'admin/stores/status-update', 'admin.stores.status.update', 'App\\Http\\Controllers\\Admin\\StoreController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(248, 'POST', 'admin/stores/soft-delete', 'admin.stores.soft.delete', 'App\\Http\\Controllers\\Admin\\StoreController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(249, 'GET,HEAD', 'admin/stores/export-pdf', 'admin.stores.exportPdf', 'App\\Http\\Controllers\\Admin\\StoreController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(250, 'GET,HEAD', 'admin/stores/export-csv', 'admin.stores.exportCsv', 'App\\Http\\Controllers\\Admin\\StoreController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(251, 'GET,HEAD', 'credit-duration/{id}', 'credit.duration', 'App\\Http\\Controllers\\BillingController@getCreditDuration', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43');
INSERT INTO `routes` (`id`, `method`, `uri`, `name`, `action`, `middleware`, `created_at`, `updated_at`) VALUES
(252, 'GET,HEAD', 'sales/{sale}/payment-details', 'admin.sales.payment-details', 'App\\Http\\Controllers\\SaleController@paymentDetails', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(253, 'POST', 'sales/save-credit-payment', 'admin.sales.save-credit-payment', 'App\\Http\\Controllers\\SaleController@saveCreditPayment', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(254, 'GET,HEAD', 'admin/credit-durations', 'admin.credit-durations.index', 'App\\Http\\Controllers\\Admin\\CreditDurationController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(255, 'GET,HEAD', 'admin/credit-durations/create', 'admin.credit-durations.create', 'App\\Http\\Controllers\\Admin\\CreditDurationController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(256, 'POST', 'admin/credit-durations/store', 'admin.credit-durations.store', 'App\\Http\\Controllers\\Admin\\CreditDurationController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(257, 'GET,HEAD', 'admin/credit-durations/edit/{id}', 'admin.credit-durations.edit', 'App\\Http\\Controllers\\Admin\\CreditDurationController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(258, 'POST', 'admin/credit-durations/update', 'admin.credit-durations.update', 'App\\Http\\Controllers\\Admin\\CreditDurationController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(259, 'POST', 'admin/credit-durations/delete', 'admin.credit-durations.destroy', 'App\\Http\\Controllers\\Admin\\CreditDurationController@destroy', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(260, 'POST', 'admin/credit-durations/status-update', 'admin.credit-durations.status.update', 'App\\Http\\Controllers\\Admin\\CreditDurationController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(261, 'POST', 'admin/credit-durations/soft-delete', 'admin.credit-durations.soft.delete', 'App\\Http\\Controllers\\Admin\\CreditDurationController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(262, 'GET,HEAD', 'admin/credit-durations/export-pdf', 'admin.credit-durations.exportPdf', 'App\\Http\\Controllers\\Admin\\CreditDurationController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(263, 'GET,HEAD', 'admin/credit-durations/export-csv', 'admin.credit-durations.exportCsv', 'App\\Http\\Controllers\\Admin\\CreditDurationController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(264, 'GET,HEAD', 'admin/payment-types', 'admin.payment-types.index', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(265, 'GET,HEAD', 'admin/payment-types/create', 'admin.payment-types.create', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(266, 'POST', 'admin/payment-types/store', 'admin.payment-types.store', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(267, 'GET,HEAD', 'admin/payment-types/edit/{id}', 'admin.payment-types.edit', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(268, 'POST', 'admin/payment-types/update/{id}', 'admin.payment-types.update', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(269, 'POST', 'admin/payment-types/softdelete/{id}', 'admin.payment-types.softdelete', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(270, 'POST', 'admin/payment-types/status-update', 'admin.payment-types.statusUpdate', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(271, 'GET,HEAD', 'admin/payment-types/export-pdf', 'admin.payment-types.exportPdf', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(272, 'GET,HEAD', 'admin/payment-types/export-csv', 'admin.payment-types.exportCsv', 'App\\Http\\Controllers\\Admin\\PaymentTypeController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(273, 'GET,HEAD', 'admin/account-settings', 'admin.account-settings.index', 'App\\Http\\Controllers\\Admin\\AccountSettingController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(274, 'GET,HEAD', 'admin/account-settings/create', 'admin.account-settings.create', 'App\\Http\\Controllers\\Admin\\AccountSettingController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(275, 'POST', 'admin/account-settings', 'admin.account-settings.store', 'App\\Http\\Controllers\\Admin\\AccountSettingController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(276, 'GET,HEAD', 'admin/account-settings/{id}/edit', 'admin.account-settings.edit', 'App\\Http\\Controllers\\Admin\\AccountSettingController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(277, 'PUT', 'admin/account-settings/{id}', 'admin.account-settings.update', 'App\\Http\\Controllers\\Admin\\AccountSettingController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(278, 'GET,HEAD', 'admin/vendors/ledger/{id}/export-pdf', 'admin.vendors.ledger.pdf', 'App\\Http\\Controllers\\Admin\\VendorController@ledgerExportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(279, 'GET,HEAD', 'admin/vendors/ledger/{id}/export-csv', 'admin.vendors.ledger.csv', 'App\\Http\\Controllers\\Admin\\VendorController@ledgerExportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(280, 'POST', 'session/keep-alive', 'session.keepalive', 'App\\Http\\Controllers\\SessionTimeoutController@keepAlive', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(281, 'POST', 'session/logout', 'session.logout', 'App\\Http\\Controllers\\SessionTimeoutController@logout', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(282, 'GET,HEAD', 'admin/warehouses/export-pdf', 'admin.warehouses.warehousePdf', 'App\\Http\\Controllers\\Admin\\WarehouseController@warehousePdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(283, 'GET,HEAD', 'admin/warehouses/export-csv', 'admin.warehouses.warehouseCsv', 'App\\Http\\Controllers\\Admin\\WarehouseController@warehouseCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(284, 'GET,HEAD', 'admin/warehouses/{id}/products/export/pdf', 'admin.warehouses.warehouseproductPdf', 'App\\Http\\Controllers\\Admin\\WarehouseController@warehouseproductPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(285, 'GET,HEAD', 'admin/warehouses/{id}/products/export/csv', 'admin.warehouses.warehouseproductCsv', 'App\\Http\\Controllers\\Admin\\WarehouseController@warehouseproductCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(286, 'GET,HEAD', 'admin/stock-returns/exportpdf', 'admin.stock_returns.exportpdf', 'App\\Http\\Controllers\\Admin\\StockReturnController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(287, 'GET,HEAD', 'admin/stock-returns/exportcsv', 'admin.stock_returns.exportcsv', 'App\\Http\\Controllers\\Admin\\StockReturnController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(288, 'GET,HEAD', 'admin/stock-returns/view/ajax/pdf/{id}', 'admin.stock_returns.view.ajax.pdf', 'App\\Http\\Controllers\\Admin\\StockReturnController@viewAjaxPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(289, 'GET,HEAD', 'admin/requisitions/view/ajax/pdf/{id}', 'admin.requisitions.view.ajax.pdf', 'App\\Http\\Controllers\\Admin\\RequisitionController@viewAjaxPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(290, 'GET,HEAD', 'designations/{designation}/permissions', 'designations.permissions.edit', 'App\\Http\\Controllers\\DesignationPermissionController@edit', 'web', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(291, 'PUT', 'designations/{designation}/permissions', 'designations.permissions.update', 'App\\Http\\Controllers\\DesignationPermissionController@update', 'web', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(292, 'GET,HEAD', 'admin/designations', 'admin.designations.index', 'App\\Http\\Controllers\\Admin\\DesignationController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(293, 'GET,HEAD', 'admin/designations/create', 'admin.designations.create', 'App\\Http\\Controllers\\Admin\\DesignationController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(294, 'POST', 'admin/designations/store', 'admin.designations.store', 'App\\Http\\Controllers\\Admin\\DesignationController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(295, 'GET,HEAD', 'admin/designations/edit/{id}', 'admin.designations.edit', 'App\\Http\\Controllers\\Admin\\DesignationController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(296, 'POST', 'admin/designations/update/{id}', 'admin.designations.update', 'App\\Http\\Controllers\\Admin\\DesignationController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(297, 'DELETE', 'admin/designations/delete/{id}', 'admin.designations.destroy', 'App\\Http\\Controllers\\Admin\\DesignationController@destroy', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(298, 'GET,HEAD', 'admin/designations/export/pdf', 'admin.designations.export.pdf', 'App\\Http\\Controllers\\Admin\\DesignationController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(299, 'GET,HEAD', 'admin/designations/export/csv', 'admin.designations.export.csv', 'App\\Http\\Controllers\\Admin\\DesignationController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(300, 'POST', 'admin/designations/softdelete', 'admin.designations.softdelete', 'App\\Http\\Controllers\\Admin\\DesignationController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(301, 'GET,HEAD', 'admin/modules', 'admin.modules.index', 'App\\Http\\Controllers\\Admin\\ModuleController@index', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(302, 'GET,HEAD', 'admin/modules/create', 'admin.modules.create', 'App\\Http\\Controllers\\Admin\\ModuleController@create', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(303, 'POST', 'admin/modules/store', 'admin.modules.store', 'App\\Http\\Controllers\\Admin\\ModuleController@store', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(304, 'GET,HEAD', 'admin/modules/edit/{id}', 'admin.modules.edit', 'App\\Http\\Controllers\\Admin\\ModuleController@edit', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(305, 'POST', 'admin/modules/update/{id}', 'admin.modules.update', 'App\\Http\\Controllers\\Admin\\ModuleController@update', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(306, 'POST', 'admin/modules/softdelete', 'admin.modules.softdelete', 'App\\Http\\Controllers\\Admin\\ModuleController@softdelete', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(307, 'POST', 'admin/modules/status-update', 'admin.modules.statusUpdate', 'App\\Http\\Controllers\\Admin\\ModuleController@statusUpdate', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(308, 'GET,HEAD', 'admin/modules/exportpdf', 'admin.modules.exportPdf', 'App\\Http\\Controllers\\Admin\\ModuleController@exportPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(309, 'GET,HEAD', 'admin/modules/exportcsv', 'admin.modules.exportCsv', 'App\\Http\\Controllers\\Admin\\ModuleController@exportCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(310, 'POST', 'keep-alive', 'admin.keepalive', 'App\\Http\\Controllers\\KeepAliveController', 'web,auth', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(311, 'POST', 'contact-us', 'contact.store', 'App\\Http\\Controllers\\ContactController@store', 'web', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(312, 'GET,HEAD', 'admin/requisitions/pending-posting/pdf', 'admin.requisitions.pending.posting.pdf', 'App\\Http\\Controllers\\Admin\\RequisitionController@pendingPostingPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(313, 'GET,HEAD', 'admin/requisitions/pending-posting/csv', 'admin.requisitions.pending.posting.csv', 'App\\Http\\Controllers\\Admin\\RequisitionController@pendingPostingCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(314, 'GET,HEAD', 'admin/requisitions/pending-posting-history', 'admin.requisitions.pending.posting.history', 'App\\Http\\Controllers\\Admin\\RequisitionController@pendingPostingHistory', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(315, 'GET,HEAD', 'admin/requisitions/pending-posting-history/pdf', 'admin.requisitions.pending.posting.history.pdf', 'App\\Http\\Controllers\\Admin\\RequisitionController@pendingPostingHistorypdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(316, 'GET,HEAD', 'admin/requisitions/pending-posting-history/csv', 'admin.requisitions.pending.posting.history.csv', 'App\\Http\\Controllers\\Admin\\RequisitionController@pendingPostingHistorycsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(317, 'GET,HEAD', 'admin/requisitions/pending-posting-history-report', 'admin.requisitions.pending.posting.history.report', 'App\\Http\\Controllers\\Admin\\RequisitionController@pendingPostingHistoryReport', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(318, 'POST', 'admin/barcode/purchase/validate-barcode', 'admin.purchase.validateBarcode', 'App\\Http\\Controllers\\Admin\\BarcodeController@validatePurchaseBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(321, 'GET,HEAD', 'admin/purchases/barcode-print/{purchase}', 'admin.purchases.printBarcode', 'App\\Http\\Controllers\\Admin\\PurchaseController@printBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(323, 'GET,HEAD', 'admin/purchases/barcodes', 'admin.purchases.purchase-barcodes', 'App\\Http\\Controllers\\Admin\\PurchaseController@purchaseBarcodes', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(324, 'POST', 'admin/purchases/barcodes-data', 'admin.purchases.purchase-barcodes-data', 'App\\Http\\Controllers\\Admin\\PurchaseController@purchaseBarcodesData', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(325, 'GET,HEAD', 'admin/purchases/barcode-preview/{purchase}', 'admin.purchases.barcodePreview', 'App\\Http\\Controllers\\Admin\\PurchaseController@barcodePreview', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(326, 'POST', 'admin/requisitions/validate-requisition-barcode', 'admin.requisitions.validateRequisitionBarcode', 'App\\Http\\Controllers\\Admin\\RequisitionController@validateRequisitionBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(327, 'POST', 'admin/requisitions/barcode/search', 'admin.requisitions.barcode.search', 'App\\Http\\Controllers\\Admin\\RequisitionController@searchBarcode', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(328, 'POST', 'admin/barcode/validateBarcodeRequisitionId', 'admin.barcode.validateBarcodeRequisitionId', 'App\\Http\\Controllers\\Admin\\BarcodeController@validateBarcodeRequisitionId', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(329, 'GET,HEAD', 'admin/purchases/barcodes/exportpdf', 'admin.purchases.purchase-barcodes-exportPdf', 'App\\Http\\Controllers\\Admin\\PurchaseController@purchaseBarcodesPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(330, 'GET,HEAD', 'admin/purchases/barcodes/exportcsv', 'admin.purchases.purchase-barcodes-exportCsv', 'App\\Http\\Controllers\\Admin\\PurchaseController@purchaseBarcodesCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(331, 'GET,HEAD', 'admin/warehouses/export/exportstocklistingPdf', 'admin.warehouses.exportstocklistingPdf', 'App\\Http\\Controllers\\Admin\\WarehouseController@exportstocklistingPdf', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(332, 'GET,HEAD', 'admin/warehouses/export/exportstocklistingCsv', 'admin.warehouses.exportstocklistingCsv', 'App\\Http\\Controllers\\Admin\\WarehouseController@exportstocklistingCsv', 'web,auth,route.permission,subscription', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(333, 'POST', 'admin/stock-adjust/customerReturn', 'admin.stock.adjust.customerReturn', 'App\\Http\\Controllers\\Admin\\StockAdjustmentController@customerReturn', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(334, 'GET,HEAD', 'admin/sale-returns', 'admin.sale-returns', 'App\\Http\\Controllers\\Admin\\SaleReturnController@index', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(335, 'GET,HEAD', 'admin/sale-returns/create', 'admin.sale-returns.create', 'App\\Http\\Controllers\\Admin\\SaleReturnController@create', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(336, 'POST', 'admin/sale-returns/search-invoice', 'admin.sale-returns.search-invoice', 'App\\Http\\Controllers\\Admin\\SaleReturnController@searchInvoice', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(337, 'POST', 'admin/sale-returns/find-barcode', 'admin.sale-returns.find-barcode', 'App\\Http\\Controllers\\Admin\\SaleReturnController@findBarcode', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(338, 'POST', 'admin/sale-returns/store', 'admin.sale-returns.store', 'App\\Http\\Controllers\\Admin\\SaleReturnController@store', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(339, 'GET,HEAD', 'admin/sale-returns/{id}', 'admin.sale-returns.show', 'App\\Http\\Controllers\\Admin\\SaleReturnController@show', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(340, 'GET,HEAD', 'admin/sales-return', 'admin.sales-return.index', 'App\\Http\\Controllers\\Admin\\SaleReturnController@index', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(341, 'GET,HEAD', 'admin/sales-return/create', 'admin.sales-return.create', 'App\\Http\\Controllers\\Admin\\SaleReturnController@create', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(342, 'POST', 'admin/sales-return/store', 'admin.sales-return.store', 'App\\Http\\Controllers\\Admin\\SaleReturnController@store', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(343, 'GET,HEAD', 'admin/sales-return/sale-details', 'admin.sales-return.sale-details', 'App\\Http\\Controllers\\Admin\\SaleReturnController@saleDetails', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(344, 'GET,HEAD', 'admin/sales-return/scan-barcode', 'admin.sales-return.scan-barcode', 'App\\Http\\Controllers\\Admin\\SaleReturnController@scanBarcode', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(345, 'GET,HEAD', 'logout', 'logout', 'App\\Http\\Controllers\\KeepAliveController@logout', 'web', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(346, 'POST', 'admin/sales-return/assign-customer', 'admin.sales-return.assign-customer', 'App\\Http\\Controllers\\Admin\\SaleReturnController@assignCustomer', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(347, 'POST', 'sales/approve-credit/{id}', 'admin.sales.approve-credit', 'App\\Http\\Controllers\\BillingController@approveCreditSale', 'web,auth,route.permission,subscription', '2026-08-16 05:03:43', '2026-08-16 05:03:43'),
(348, 'GET,HEAD', 'admin/sale-returns/export-pdf', 'admin.sale-returns.exportPdf', 'App\\Http\\Controllers\\Admin\\SaleReturnController@exportPdf', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44'),
(349, 'GET,HEAD', 'admin/sale-returns/export-csv', 'admin.sale-returns.exportCsv', 'App\\Http\\Controllers\\Admin\\SaleReturnController@exportCsv', 'web,auth', '2026-08-16 05:03:44', '2026-08-16 05:03:44');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_no` varchar(255) NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `tax` decimal(12,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `final_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('draft','completed','cancelled','pending') DEFAULT 'draft',
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `delivery_type` varchar(50) DEFAULT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_charge` decimal(15,2) NOT NULL DEFAULT 0.00,
  `delivery_notes` text DEFAULT NULL,
  `payment_approval_status` enum('not_required','pending','reject','approve') DEFAULT 'approve',
  `payment_approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `payment_approved_at` timestamp NULL DEFAULT NULL,
  `payment_approval_note` text DEFAULT NULL,
  `payment_type` varchar(50) DEFAULT 'full',
  `payment_status` varchar(20) DEFAULT 'unpaid',
  `balance_amount` decimal(15,2) DEFAULT 0.00,
  `credit_duration_id` bigint(20) DEFAULT NULL,
  `interest_rate` decimal(10,2) DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `interest_amount` decimal(15,2) DEFAULT 0.00,
  `payable_amount` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `account_id`, `store_id`, `invoice_no`, `customer_id`, `subtotal`, `tax`, `discount`, `total`, `final_amount`, `paid_amount`, `change_amount`, `payment_method`, `status`, `user_id`, `delivery_type`, `delivery_address`, `delivery_charge`, `delivery_notes`, `payment_approval_status`, `payment_approved_by`, `payment_approved_at`, `payment_approval_note`, `payment_type`, `payment_status`, `balance_amount`, `credit_duration_id`, `interest_rate`, `due_date`, `interest_amount`, `payable_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'INV1786879751', NULL, 2300.00, 69.00, 0.00, 2369.00, 0.00, 2369.00, 0.00, '1', 'completed', 2, 'pickup', NULL, 0.00, NULL, 'approve', 2, '2026-08-16 05:59:11', NULL, 'full', 'paid', 0.00, NULL, 0.00, NULL, 0.00, 2369.00, '2026-08-16 05:59:11', '2026-08-16 05:59:11'),
(2, 1, 1, 'INV1786880003', 1, 2200.00, 66.00, 0.00, 2266.00, 0.00, 2266.00, 0.00, NULL, 'completed', 2, 'pickup', NULL, 0.00, NULL, 'approve', 2, '2026-08-16 06:03:23', NULL, 'partial', 'paid', 0.00, NULL, 0.00, NULL, 0.00, 2266.00, '2026-08-16 06:03:23', '2026-08-16 06:05:45'),
(3, 1, 1, 'INV1786880482', 1, 2200.00, 66.00, 0.00, 2266.00, 0.00, 2266.00, 0.00, '1', 'completed', 2, 'pickup', NULL, 0.00, NULL, 'approve', 2, '2026-08-16 06:11:22', NULL, 'full', 'paid', 0.00, NULL, 0.00, NULL, 0.00, 2266.00, '2026-08-16 06:11:22', '2026-08-16 06:12:17'),
(4, 1, 1, 'INV1786881514', 1, 5500.00, 165.00, 0.00, 5836.00, 0.00, 0.00, 0.00, NULL, 'cancelled', 2, 'delivery', '8854833544536', 1.05, 'hello notes', 'reject', 2, '2026-08-16 07:07:48', 'asdcsa asd sadas', 'credit', 'unpaid', 5836.00, 2, 3.00, '2026-08-31', 169.95, 5836.00, '2026-08-16 06:28:34', '2026-08-16 07:07:48'),
(5, 1, 1, 'INV1786889291', 1, 2200.00, 66.00, 0.00, 2333.98, 0.00, 0.00, 0.00, NULL, 'pending', 2, 'pickup', NULL, 0.00, NULL, 'pending', NULL, NULL, NULL, 'credit', 'unpaid', 2333.98, 2, 3.00, '2026-08-31', 67.98, 2333.98, '2026-08-16 08:38:11', '2026-08-16 08:38:11'),
(6, 1, 1, 'INV1786889543', 1, 2100.00, 63.00, 0.00, 2227.89, 0.00, 0.00, 0.00, NULL, 'completed', 2, 'pickup', NULL, 0.00, NULL, 'approve', 2, '2026-08-16 08:46:12', 'good person', 'credit', 'unpaid', 2227.89, 2, 3.00, '2026-08-31', 64.89, 2227.89, '2026-08-16 08:42:23', '2026-08-16 08:46:12'),
(7, 1, 1, 'INV1786889640', 1, 2200.00, 66.00, 0.00, 2333.98, 0.00, 0.00, 0.00, NULL, 'cancelled', 2, 'pickup', NULL, 0.00, NULL, 'reject', 2, '2026-08-16 08:45:59', 'no trusted person', 'credit', 'unpaid', 2333.98, 2, 3.00, '2026-08-31', 67.98, 2333.98, '2026-08-16 08:44:00', '2026-08-16 08:45:59'),
(8, 1, 1, 'INV1786889679', NULL, 2100.00, 63.00, 0.00, 2163.00, 0.00, 2163.00, 0.00, '1', 'completed', 2, 'pickup', NULL, 0.00, NULL, 'approve', 2, '2026-08-16 08:44:39', NULL, 'full', 'paid', 0.00, NULL, 0.00, NULL, 0.00, 2163.00, '2026-08-16 08:44:39', '2026-08-16 08:44:39'),
(9, 1, 1, 'INV1786962249', 1, 6000.00, 180.00, 0.00, 6375.40, 0.00, 0.00, 0.00, NULL, 'cancelled', 2, 'delivery', 'hello street', 10.00, 'please call the customer when reached', 'reject', 2, '2026-08-17 10:25:06', 'not a good credit score', 'credit', 'unpaid', 6375.40, 2, 3.00, '2026-09-01', 185.40, 6375.40, '2026-08-17 10:24:09', '2026-08-17 10:25:06');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `account_id`, `sale_id`, `product_id`, `quantity`, `price`, `total`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 10, 1, 2300.00, 2300.00, '2026-08-16 05:59:11', '2026-08-16 05:59:11'),
(2, 1, 2, 9, 1, 2200.00, 2200.00, '2026-08-16 06:03:23', '2026-08-16 06:03:23'),
(3, 1, 3, 9, 1, 2200.00, 2200.00, '2026-08-16 06:11:22', '2026-08-16 06:11:22'),
(4, 1, 4, 5, 1, 5500.00, 5500.00, '2026-08-16 06:28:34', '2026-08-16 06:28:34'),
(5, 1, 5, 9, 1, 2200.00, 2200.00, '2026-08-16 08:38:11', '2026-08-16 08:38:11'),
(6, 1, 6, 8, 1, 2100.00, 2100.00, '2026-08-16 08:42:23', '2026-08-16 08:42:23'),
(7, 1, 7, 7, 1, 2200.00, 2200.00, '2026-08-16 08:44:00', '2026-08-16 08:44:00'),
(8, 1, 8, 6, 1, 2100.00, 2100.00, '2026-08-16 08:44:39', '2026-08-16 08:44:39'),
(9, 1, 9, 1, 1, 6000.00, 6000.00, '2026-08-17 10:24:09', '2026-08-17 10:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `sale_item_trackings`
--

CREATE TABLE `sale_item_trackings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_item_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_tracking_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_item_trackings`
--

INSERT INTO `sale_item_trackings` (`id`, `sale_item_id`, `purchase_item_tracking_id`, `created_at`, `updated_at`) VALUES
(1, 1, 6, '2026-08-16 05:59:11', '2026-08-16 05:59:11'),
(2, 2, 7, '2026-08-16 06:03:23', '2026-08-16 06:03:23'),
(3, 3, 7, '2026-08-16 06:11:22', '2026-08-16 06:11:22'),
(4, 4, 3, '2026-08-16 06:28:34', '2026-08-16 06:28:34'),
(5, 5, 7, '2026-08-16 08:38:11', '2026-08-16 08:38:11'),
(6, 6, 8, '2026-08-16 08:42:23', '2026-08-16 08:42:23'),
(7, 7, 9, '2026-08-16 08:44:01', '2026-08-16 08:44:01'),
(8, 8, 10, '2026-08-16 08:44:39', '2026-08-16 08:44:39'),
(9, 9, 1, '2026-08-17 10:24:09', '2026-08-17 10:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `sale_payments`
--

CREATE TABLE `sale_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(50) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT 0.00,
  `payment_received_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `sale_payments`
--

INSERT INTO `sale_payments` (`id`, `sale_id`, `method`, `amount`, `payment_received_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'cash', 2369.00, 2, '2026-08-16 05:59:11', '2026-08-16 05:59:11'),
(2, 2, 'transfer', 2000.00, 2, '2026-08-16 06:03:23', '2026-08-16 06:03:23'),
(3, 2, 'card', 200.00, 2, '2026-08-16 06:03:23', '2026-08-16 06:03:23'),
(4, 2, 'cash', 60.00, 2, '2026-08-16 06:03:23', '2026-08-16 06:03:23'),
(5, 2, 'pos', 6.00, 2, '2026-08-16 06:03:23', '2026-08-16 06:03:23'),
(6, 3, 'cash', 2266.00, 2, '2026-08-16 06:11:22', '2026-08-16 06:11:22'),
(7, 8, 'cash', 2163.00, 2, '2026-08-16 08:44:39', '2026-08-16 08:44:39');

-- --------------------------------------------------------

--
-- Table structure for table `sale_returns`
--

CREATE TABLE `sale_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `return_no` varchar(50) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `refund_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `refund_type` varchar(30) NOT NULL DEFAULT 'refund',
  `payment_method` varchar(50) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'completed',
  `reason` varchar(255) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_returns`
--

INSERT INTO `sale_returns` (`id`, `account_id`, `store_id`, `sale_id`, `customer_id`, `return_no`, `total_amount`, `refund_amount`, `refund_type`, `payment_method`, `status`, `reason`, `note`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 1, 'RET-20260816113558', 2200.00, 0.00, 'refund', NULL, 'completed', 'INV1786880003', NULL, 2, '2026-08-16 06:05:58', '2026-08-16 06:05:58'),
(2, 1, 1, 3, 1, 'RET-20260816114224', 2200.00, 0.00, 'refund', NULL, 'completed', 'INV1786880482', NULL, 2, '2026-08-16 06:12:24', '2026-08-16 06:12:24');

-- --------------------------------------------------------

--
-- Table structure for table `sale_return_items`
--

CREATE TABLE `sale_return_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_return_id` bigint(20) UNSIGNED NOT NULL,
  `sale_item_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `purchase_item_tracking_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` decimal(12,2) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_return_items`
--

INSERT INTO `sale_return_items` (`id`, `sale_return_id`, `sale_item_id`, `product_id`, `purchase_item_tracking_id`, `quantity`, `price`, `total`, `reason`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 9, 7, 1.00, 2200.00, 2200.00, NULL, '2026-08-16 06:05:58', '2026-08-16 06:05:58'),
(2, 2, 3, 9, 7, 1.00, 2200.00, 2200.00, NULL, '2026-08-16 06:12:24', '2026-08-16 06:12:24');

-- --------------------------------------------------------

--
-- Table structure for table `sale_return_payments`
--

CREATE TABLE `sale_return_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_return_id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(50) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_received_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('X3NW7NaQYaW09KCC5T24k2NDMLUAtGMfIClJdR37', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiSEZIQkpjTlFLTjk5TDBiRzVIMG5SZWpPZ3kwY1NWZFRBQUxiMGp5YyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1MDoiaHR0cDovL3JldGFpbGVyc3lzdGVtLmlvL2FkbWluL3JlcXVpc2l0aW9ucy9jcmVhdGUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo1MDoiaHR0cDovL3JldGFpbGVyc3lzdGVtLmlvL2FkbWluL3JlcXVpc2l0aW9ucy9jcmVhdGUiO3M6NToicm91dGUiO3M6MjU6ImFkbWluLnJlcXVpc2l0aW9ucy5jcmVhdGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1785664979);

-- --------------------------------------------------------

--
-- Table structure for table `states`
--

CREATE TABLE `states` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32 COLLATE=utf32_general_ci COMMENT='States in Nigeria.';

--
-- Dumping data for table `states`
--

INSERT INTO `states` (`id`, `name`) VALUES
(1, 'Abuja'),
(2, 'Abia'),
(3, 'Adamawa'),
(4, 'Akwa Ibom'),
(5, 'Anambra'),
(6, 'Bauchi'),
(7, 'Bayelsa'),
(8, 'Benue'),
(9, 'Borno'),
(10, 'Cross River'),
(11, 'Delta'),
(12, 'Ebonyi'),
(13, 'Edo'),
(14, 'Ekiti'),
(15, 'Enugu'),
(16, 'Gombe'),
(17, 'Imo'),
(18, 'Jigawa'),
(19, 'Kaduna'),
(20, 'Kano'),
(21, 'Katsina'),
(22, 'Kebbi'),
(23, 'Kogi'),
(24, 'Kwara'),
(25, 'Lagos'),
(26, 'Nassarawa'),
(27, 'Niger'),
(28, 'Ogun'),
(29, 'Ondo'),
(30, 'Osun'),
(31, 'Oyo'),
(32, 'Plateau'),
(33, 'Rivers'),
(34, 'Sokoto'),
(35, 'Taraba'),
(36, 'Yobe'),
(37, 'Zamfara');

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustments`
--

CREATE TABLE `stock_adjustments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('add','deduct','sale','return','damage') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `stock_adjustments`
--

INSERT INTO `stock_adjustments` (`id`, `account_id`, `product_id`, `type`, `quantity`, `reference_id`, `note`, `created_by`, `created_at`) VALUES
(1, 1, 1, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:13:12'),
(2, 1, 2, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:13:27'),
(3, 1, 3, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:14:40'),
(4, 1, 4, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:15:01'),
(5, 1, 5, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:25:39'),
(6, 1, 6, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:26:28'),
(7, 1, 7, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:26:44'),
(8, 1, 8, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:27:17'),
(9, 1, 9, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:27:41'),
(10, 1, 10, 'add', 1, NULL, 'Initial stock added', 2, '2026-08-16 11:27:59'),
(11, 1, 10, 'sale', 1, 1, 'POS Sale Invoice #INV1786879751', 2, '2026-08-16 11:29:11'),
(12, 1, 9, 'sale', 1, 2, 'POS Sale Invoice #INV1786880003', 2, '2026-08-16 11:33:23'),
(13, 1, 9, 'return', 1, 1, 'Customer return RET-20260816113558 - Store ID: 1', 2, '2026-08-16 11:35:58'),
(14, 1, 9, 'sale', 1, 3, 'POS Sale Invoice #INV1786880482', 2, '2026-08-16 11:41:22'),
(15, 1, 9, 'return', 1, 2, 'Customer return RET-20260816114224 - Store ID: 1', 2, '2026-08-16 11:42:24'),
(16, 1, 5, 'sale', 1, 4, 'POS Sale Invoice #INV1786881514', 2, '2026-08-16 11:58:34'),
(17, 1, 5, 'add', 1, 4, 'Credit sale rejected (Invoice #INV1786881514) - Store ID: 1', 2, '2026-08-16 12:37:48'),
(18, 1, 9, 'sale', 1, 5, 'POS Sale Invoice #INV1786889291', 2, '2026-08-16 14:08:11'),
(19, 1, 8, 'sale', 1, 6, 'POS Sale Invoice #INV1786889543', 2, '2026-08-16 14:12:23'),
(20, 1, 7, 'sale', 1, 7, 'POS Sale Invoice #INV1786889640', 2, '2026-08-16 14:14:01'),
(21, 1, 6, 'sale', 1, 8, 'POS Sale Invoice #INV1786889679', 2, '2026-08-16 14:14:39'),
(22, 1, 7, 'add', 1, 7, 'Credit sale rejected (Invoice #INV1786889640) - Store ID: 1', 2, '2026-08-16 14:15:59'),
(23, 1, 1, 'sale', 1, 9, 'POS Sale Invoice #INV1786962249', 2, '2026-08-17 10:24:09'),
(24, 1, 1, 'add', 1, 9, 'Credit sale rejected (Invoice #INV1786962249) - Store ID: 1', 2, '2026-08-17 10:25:06');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `master_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` varchar(50) NOT NULL COMMENT 'purchase,sale,transfer_in,transfer_out,adjustment,opening',
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `qty_in` decimal(15,2) NOT NULL DEFAULT 0.00,
  `qty_out` decimal(15,2) NOT NULL DEFAULT 0.00,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `account_id`, `warehouse_id`, `master_item_id`, `type`, `reference_id`, `qty_in`, `qty_out`, `balance`, `remarks`, `created_by`, `created_at`) VALUES
(1, 1, 11, 17, 'purchase', 1, 5.00, 0.00, 5.00, 'Purchase Entry #PUR-20260816-9298', 2, '2026-08-16 11:06:49'),
(2, 1, 11, 27, 'purchase', 1, 5.00, 0.00, 5.00, 'Purchase Entry #PUR-20260816-9298', 2, '2026-08-16 11:06:49'),
(3, 1, 11, 17, 'transfer_out', 1, 0.00, 5.00, 0.00, 'Requisition OUT #REQ-20260816-7756', 2, '2026-08-16 11:09:57'),
(4, 1, 11, 27, 'transfer_out', 1, 0.00, 5.00, 0.00, 'Requisition OUT #REQ-20260816-7756', 2, '2026-08-16 11:09:57');

-- --------------------------------------------------------

--
-- Table structure for table `stock_returns`
--

CREATE TABLE `stock_returns` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `return_no` varchar(50) NOT NULL,
  `return_date` date DEFAULT NULL,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(4) DEFAULT 1 COMMENT '''1=active, 0=cancelled''',
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_return_items`
--

CREATE TABLE `stock_return_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `return_id` bigint(20) UNSIGNED NOT NULL,
  `master_item_id` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(10,2) NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `transfer_no` varchar(50) NOT NULL,
  `from_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `to_warehouse_id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Pending,1=Completed,2=Cancelled',
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfer_items`
--

CREATE TABLE `stock_transfer_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `transfer_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Tenant / Company ID',
  `name` varchar(255) NOT NULL COMMENT 'Store Name',
  `code` varchar(100) DEFAULT NULL COMMENT 'Store Code',
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `alternate_phone` varchar(20) DEFAULT NULL,
  `gst_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT 'India',
  `pincode` varchar(20) DEFAULT NULL,
  `manager_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'User who manages the store',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `logo` longtext DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0 means not deleted, 1 means deleted',
  `deleted_at` datetime DEFAULT NULL,
  `deleted_by` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `account_id`, `name`, `code`, `email`, `phone`, `alternate_phone`, `gst_number`, `address`, `city`, `state`, `country`, `pincode`, `manager_id`, `status`, `logo`, `created_by`, `updated_by`, `created_at`, `updated_at`, `is_deleted`, `deleted_at`, `deleted_by`) VALUES
(1, 1, 'EBBY KINGS GLOBAL ENTERPRISES LTD', 'STR00001', 'ebereeze11@gmail.com', '08068805115', '08154030110', '', 'Ekeoha Shopping Centre Aba Abia state', 'Aba South', 'Lagos', 'Nigeria', NULL, 2, 1, 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAGkAAABZCAMAAAAKJvqlAAAAQlBMVEUAAADCp06/n0jBpUy/p0zCp07Cp07Cp07Cpk3Cpk7Cpk3CqE7BpU/CqE7CqE3BpkzBpk7Bpk7Ho1DBpky/pUrCp06Xh6weAAAAFXRSTlMA3xBAIL/vn2CAcK8wz49QkM8fkDB2LbkjAAAFrklEQVR42r1YWYLjKgy0QKzek+j+V31jIOBYuLH7TaZ+JtOOKbRQKtL9AoZc909giP4NlaMN9vtEMxEpIo3fJkIg6jtFNP6DIgF2Ar5eqjmVSH49f0Bkwod+S+IXYYkohoKaSH45pNLs6rtVwi5C6PD5/8K7eeg4VAgpYam2n7BTb8StchCYYx2Q9mEMRFocaLy6KSCKIuDzFf9ZGnXoCZw0RTwvM61E1ivGBUT2lFhEPdTK6xv9T0TbHi1QUISEFxGJfa70Ln0+xKOs2PYJV4mQ3t8NXI7FwNIn+sAjk2Dpq0yyLCkWIhrxvTCr2zNGG4KXpSlvMhXxBkw5HY6xjyFyvXVBTqT7JVOHPZF+bX9l+ddEIqxMWn6MysevmOJStvNcU81WKBfb5vdMR6q5J/JHKSHy27MxZu5+9l4sT45IA5HkW9KMiDG1u5zbFHEUOSJGxLq8fXI5FV9giwiO/OqGx4BKTQ0T1DgOAbmWqTu6N7NxoHnv9VSbUXcUdiHy1VHiWUZ9rZ/8ZSZPZKr8Gg/jylSHm+xutTmDADafAKuOsLE8kxkGSfvtWjZc7zdEkW2+XRAftpkDQ0PcLxTvardrB+yaZWoXqn7OXT5UeHaJUuHN68gixw+Vy4nEevLM3evY86egUOdKsuTNVwgeD/kHDxFUWpwE5eOC0HEkuyK2Zc5JvBk1JWhVPf7FjgOROytwWCbthBEKB8SglUUeVDhTc7XxhDSaCkT0uWZHJtV77VUZY5QqtEZWjtqzM5W6y6nQaADw6cpQ7KmY4n+XGbuMYTbZMbOSa8xiwXerFjuUCnu1WwSISC+ylgtgXLEnDBFwnpApwbsxPFMx8afXENlHLvyUpIOIY+RR/mQZNLFq6kehwrjwJPbp+0ye08Uq1zEHC5qOTosL8MOm6ENASjYFm58NDhvq5cp7ScWzU9a+oaLpTNhGUOkeQFOZveT37kxhx1C/tEJYpR3WKPK9Wu66w1/5aYF9PAUmqlQokYlgaLwpYNdy6oIAiz5TAdGQibBrYNp/CZW6+AuiSjbPhiUCdR0tu9GmmuK/LjZDmwghtcN9Kh/HZOgLuEDEE4zriM1arUT02hJi0hINiJGIBj7iAC9tUVgipdISbSLPbWOdin9rshTgLhG5epPA68LlgJ5RutsJONvOoC9sVAAlYCv8QlTfhMFW/q7lzrMvMSqwTb2kugc7Dkd9tpJwRizUDEu2f8ITTgdF9BOenUvfWWhyqUZIaY2nkEQgzgUKDbNfTDJ/EhjhAw/IaG6mn343xrclwmryFLGomenTTry7AmshAZbwA8bp01WJvXssDrc8Bco82YaejuBi0hKbmpzd4LJvV9KkZ72b5fB4DNI6A0SFJ0FWfy7JOiaCmcUFiCGbUDQnT2N9hSg+6lFx9rlgkETDK31cyOYNz4xsXVKyLenX6Y2vmIMufCoG3S+9+oPeONYiOLv40BiX3XgaypyJZ09X9oHdOcQPd9fabFFZpQzTadTnZ1lM7HhmA1nvCL9/TI6rZW8Fp4k9OjP1fLKGztgf6OVIhe/zZbwcYi89htmbkQJUZS5n2ZEdg8u6KVZmge1KVXDz73WWANQnsrXuqLgLHpYaGSySaxUg90V8OvkS4Njxpu5Xne/E/WKxpol9Krc+NzXDbuZKOL2qCEQUHYOMNzaYi+KTvTRz42LYKyc7joa4vkYi0rIxlGncfWOOVZ+8FGckuQdJWZEPYPNa5dIrRY0ztOo3WZcRs/XO9ONuiriy8qvcE5tXaHi/h9as1AKYw7FeeuzakCqPupKhXmmqQa/Gz2JXLiO6G5A9RYzTXF4Ug7TeLSbBOTsPYvdSmjHyBlGedFmCGhfuwU45ZMDuLqSBfTcYZ6PmFYiHtH7qgY3cO2BDNwPeYE9UVqbfsl3pvZ5l+Ld0cpvxwBnW3vgw1/86EAcZMSDeI/gP+XuPve8BcJ4AAAAASUVORK5CYII=', 1, 2, '2026-08-03 03:35:11', '2026-08-03 04:15:36', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `subscription_payment`
--

CREATE TABLE `subscription_payment` (
  `id` bigint(20) NOT NULL,
  `account_subscription_id` bigint(20) NOT NULL,
  `account_id` bigint(20) NOT NULL,
  `payment_method` tinyint(4) NOT NULL COMMENT '1=>pos,2=>transfer',
  `amount` int(11) NOT NULL DEFAULT 0,
  `created_by` bigint(20) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `subscription_plans`
--

CREATE TABLE `subscription_plans` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` int(11) NOT NULL,
  `duration` int(11) NOT NULL COMMENT 'Duration in months',
  `status` tinyint(4) DEFAULT 1 COMMENT '1=>active,0 inactive',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `subscription_plans`
--

INSERT INTO `subscription_plans` (`id`, `name`, `description`, `price`, `duration`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '90 Days Trial Plan', '90 Days Trial Plan', 0, 3, 1, '2026-07-30 12:46:07', '2026-07-30 12:46:07', NULL),
(2, 'Two Year Plan', 'Two Year Plan', 20000, 24, 1, '2026-07-30 12:46:32', '2026-07-30 12:46:32', NULL),
(3, 'One Year Plan', 'One Year Plan', 1000, 12, 1, '2026-07-30 12:46:52', '2026-07-30 12:46:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `types`
--

CREATE TABLE `types` (
  `id` bigint(20) NOT NULL,
  `name` varchar(20) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1 COMMENT '1=>active,0=>inactive',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `types`
--

INSERT INTO `types` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Payment', 1, '2026-05-01 16:44:32', '2026-05-01 16:44:32'),
(2, 'Purchase', 1, '2026-05-01 16:44:32', '2026-05-01 16:44:32'),
(3, 'Purchase Cancel', 1, '2026-05-01 16:44:32', '2026-05-01 16:44:32'),
(4, 'Opening', 1, '2026-05-01 16:44:32', '2026-05-01 16:44:32'),
(5, 'Return', 1, '2026-05-03 22:29:19', '2026-05-03 22:29:19'),
(6, 'Cancel Return', 1, '2026-05-03 22:29:19', '2026-05-03 22:29:19'),
(7, 'Transfer Out', 1, '2026-05-03 22:29:19', '2026-05-03 22:29:19'),
(8, 'Transfer In', 1, '2026-05-03 22:29:19', '2026-05-03 22:29:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` int(11) NOT NULL DEFAULT 0,
  `store_id` bigint(20) NOT NULL DEFAULT 1,
  `user_type_id` tinyint(4) NOT NULL DEFAULT 3,
  `designation_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(150) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(100) NOT NULL DEFAULT 'default.png',
  `is_active` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1->active,2=>inactive',
  `is_staff` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0=>not a staff,1->staff',
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 2 COMMENT '1=> active, 2->inactive, 3=>lead',
  `is_deleted` tinyint(4) NOT NULL DEFAULT 0 COMMENT '1->deleted, 0->no deleted',
  `timezone` varchar(100) NOT NULL DEFAULT 'Africa/Lagos' COMMENT 'default=>Africa/Lagos',
  `created_by` bigint(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `account_id`, `store_id`, `user_type_id`, `designation_id`, `name`, `email`, `username`, `email_verified_at`, `avatar`, `is_active`, `is_staff`, `password`, `remember_token`, `status`, `is_deleted`, `timezone`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 0, 1, 1, 1, 'Mr Jerry Ogbonnah', '4425jch@gmail.com', 'administrator', NULL, '1773841622.png', 1, 0, '$2y$12$2UgG6Q3ru/Pq6Q2egmgFYuXs2R6Y7j7QjqFC2eYwugJ.2TEf6GD5e', 'O5KD5fbAQoRBIoJMsL6DyNn7TfBBm3YLnLJGgCFGsBQ3gRdqc2Ns2LETseQo', 2, 0, 'Africa/Lagos', NULL, '2026-03-09 22:18:25', '2026-07-26 07:23:26'),
(2, 1, 1, 2, 1, 'Mr Ebby King', 'ebereeze11@gmail.com', 'ebby.king', NULL, 'default.png', 1, 0, '$2y$12$9O4QWkBpbptuTr32Fh.R4ex536xH725YNTUBE.i5LqJHv1zMeI6oa', NULL, 2, 0, 'Africa/Lagos', 1, '2026-08-03 03:35:11', '2026-08-03 03:35:11'),
(3, 1, 1, 4, 2, ' Amara Ayo', 'amara.ayo@gmail.com', 'amara.ayo', NULL, '1785751843.png', 1, 1, '$2y$12$biDyOvYQXBoSM/HzLLS4Zuo.3bUkscnCfYVzBLtIi8oykHFQF1WNK', NULL, 1, 0, 'Africa/Lagos', 2, '2026-08-03 04:40:43', '2026-08-03 04:40:43');

--
-- Triggers `users`
--
DELIMITER $$
CREATE TRIGGER `insert_in_user_details_on_new_user_creation` AFTER INSERT ON `users` FOR EACH ROW BEGIN
 INSERT INTO user_details (user_id)
    VALUES (NEW.id);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_account_subscriptions`
--

CREATE TABLE `user_account_subscriptions` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `account_subscription_id` bigint(20) NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '1->active, 2->inactive',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `user_account_subscriptions`
--

INSERT INTO `user_account_subscriptions` (`id`, `user_id`, `account_subscription_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 1, '2026-08-03 09:07:10', '2026-08-03 09:07:10');

-- --------------------------------------------------------

--
-- Table structure for table `user_details`
--

CREATE TABLE `user_details` (
  `id` bigint(20) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `first_name` varchar(150) DEFAULT NULL,
  `last_name` varchar(150) DEFAULT NULL,
  `street_address` varchar(225) DEFAULT NULL,
  `office_phone` bigint(20) DEFAULT NULL,
  `whatsapp_number` bigint(20) DEFAULT NULL,
  `local_government` varchar(100) DEFAULT NULL,
  `country_of_origin` varchar(100) DEFAULT 'Nigeria',
  `state_of_origin` varchar(50) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `nin` varchar(20) DEFAULT NULL,
  `emergency_contact_name` varchar(50) DEFAULT NULL,
  `emergency_phone` varchar(20) DEFAULT NULL,
  `emergency_relationship` varchar(20) DEFAULT NULL,
  `staff_suffix` varchar(5) DEFAULT NULL,
  `emergency_suffix` varchar(5) DEFAULT 'Mr',
  `guarantor_suffix` varchar(5) DEFAULT 'Mr',
  `guarantor_name` varchar(200) DEFAULT NULL,
  `guarantor_address` varchar(225) DEFAULT NULL,
  `guarantor_phone` varchar(13) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `facebook` varchar(150) DEFAULT NULL,
  `twitter` varchar(150) DEFAULT NULL,
  `linkedin` varchar(150) DEFAULT NULL,
  `instagram` varchar(150) DEFAULT NULL,
  `pinterest` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cell_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `user_details`
--

INSERT INTO `user_details` (`id`, `user_id`, `first_name`, `last_name`, `street_address`, `office_phone`, `whatsapp_number`, `local_government`, `country_of_origin`, `state_of_origin`, `date_of_birth`, `hire_date`, `nin`, `emergency_contact_name`, `emergency_phone`, `emergency_relationship`, `staff_suffix`, `emergency_suffix`, `guarantor_suffix`, `guarantor_name`, `guarantor_address`, `guarantor_phone`, `note`, `facebook`, `twitter`, `linkedin`, `instagram`, `pinterest`, `created_at`, `updated_at`, `cell_phone`) VALUES
(1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 'Nigeria', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Mr', 'Mr', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 08:54:55', '2026-08-03 08:54:55', NULL),
(2, 2, 'Ebby', 'King', 'Ekeoha Shopping Centre Aba Abia state', 97999988888, 87765433222, 'Aba North', 'Nigeria', 'Lagos', NULL, NULL, '90909090909', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 09:05:11', '2026-08-03 03:35:11', '08154030110'),
(3, 3, 'Amara', 'Ayo', 'Ekeoha Shopping Centre Aba Abia state', NULL, 9090909090, 'Aba North', 'Nigeria', 'Lagos', '2006-08-03', '2026-08-03', '12222222222', 'Zoe Ada', '00000000000', '5', 'Mr', 'Mr', 'Mr', 'Mia lily', 'Ekeoha Shopping Centre Aba Abia state', '00000000000', NULL, NULL, NULL, NULL, NULL, NULL, '2026-08-03 10:10:43', '2026-08-03 04:40:43', '90909090909');

-- --------------------------------------------------------

--
-- Table structure for table `user_types`
--

CREATE TABLE `user_types` (
  `id` int(11) NOT NULL,
  `name` varchar(20) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `user_types`
--

INSERT INTO `user_types` (`id`, `name`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 1, '2026-03-10 09:13:12', '2026-03-10 09:13:12'),
(2, 'Admin', 1, '2026-03-10 09:13:12', '2026-03-10 09:13:12'),
(3, 'Lead', 1, '2026-03-10 09:13:12', '2026-03-10 09:13:12'),
(4, 'Staff', 1, '2026-03-10 09:13:12', '2026-03-10 09:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `whatsapp_number` varchar(30) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `lga_id` varchar(50) NOT NULL DEFAULT 'Aba North',
  `state_id` varchar(50) NOT NULL DEFAULT 'Lagos',
  `country_id` varchar(50) NOT NULL DEFAULT 'Nigeria',
  `opening_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `current_balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `account_id`, `vendor_code`, `name`, `company_name`, `phone`, `email`, `address`, `whatsapp_number`, `comment`, `website`, `lga_id`, `state_id`, `country_id`, `opening_balance`, `current_balance`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(11, 1, 'VND-LAG-001', 'Ibrahim Musa', 'Musa Wholesale Ventures', '08031234567', 'ibrahim.musa@vendors.ng', '12 Balogun Market, Lagos Island, Lagos', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-08-05 15:14:18'),
(12, 1, 'VND-ABJ-002', 'amina bello', 'bello super supplies ltd', '08035557777', 'amina.bello@vendors.ng', '22 Garki Market Road, Abuja', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 2, '2026-05-08 14:34:28', '2026-08-16 05:07:48'),
(13, 1, 'VND-PH-003', 'Emeka Nwosu', 'Nwosu Distribution Hub', '08034445555', 'emeka.nwosu@vendors.ng', '8 Aba Road, Port Harcourt, Rivers', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-08-05 06:03:36'),
(14, 1, 'VND-KAN-004', 'Abdul Suleiman', 'Suleiman Trading Company', '08036667777', 'abdul.suleiman@vendors.ng', '14 Sabon Gari Market, Kano', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-08-10 17:06:28'),
(15, 1, 'VND-ENU-005', 'Kingsley Eze', 'Eze Mega Stores', '08038889999', 'kingsley.eze@vendors.ng', '4 Ogui Road, Enugu', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-05-11 05:47:16'),
(16, 1, 'VND-IBA-006', 'Tunde Adebayo', 'Adebayo Retail Supplies', '08031112222', 'tunde.adebayo@vendors.ng', '20 Dugbe Market, Ibadan, Oyo', NULL, NULL, NULL, '1', '25', '162', 0.00, 35000.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-08-16 05:36:49'),
(17, 1, 'VND-BEN-007', 'Osaro Idahosa', 'Idahosa Global Traders', '08032223333', 'osaro.idahosa@vendors.ng', '17 Mission Road, Benin City, Edo', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-05-11 05:47:16'),
(18, 1, 'VND-UYO-008', 'Iniobong Etim', 'Etim Supply Chain Ltd', '08039991111', 'iniobong.etim@vendors.ng', '9 Wellington Bassey Way, Uyo, Akwa Ibom', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-08-15 17:42:00'),
(19, 1, 'VND-LAG-009', 'Chinedu Okafor', 'Okafor Electronics & Supplies', '08039876543', 'chinedu.okafor@vendors.ng', '55 Computer Village, Ikeja, Lagos', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-08-10 17:06:28'),
(20, 1, 'VND-ABJ-010', 'Yakubu Danjuma', 'Danjuma Food Distributors', '08037778888', 'yakubu.danjuma@vendors.ng', '11 Wuse Market, Abuja', NULL, NULL, NULL, '1', '25', '162', 0.00, 0.00, 1, 1, 1, '2026-05-08 14:34:28', '2026-08-07 07:10:20');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_ledgers`
--

CREATE TABLE `vendor_ledgers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` bigint(20) DEFAULT NULL,
  `debit` decimal(15,2) DEFAULT 0.00,
  `credit` decimal(15,2) DEFAULT 0.00,
  `balance` decimal(15,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `vendor_ledgers`
--

INSERT INTO `vendor_ledgers` (`id`, `account_id`, `vendor_id`, `type`, `reference_id`, `debit`, `credit`, `balance`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 16, '2', 1, 35000.00, 0.00, 35000.00, 'Purchase #PUR-20260816-9298', '2026-08-16 05:36:49', '2026-08-16 05:36:49');

-- --------------------------------------------------------

--
-- Table structure for table `vendor_payments`
--

CREATE TABLE `vendor_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `vendor_id` bigint(20) UNSIGNED NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `account_id` bigint(20) UNSIGNED NOT NULL,
  `staff_id` bigint(20) UNSIGNED DEFAULT NULL,
  `warehouse_code` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `manager_name` varchar(150) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=Active,0=Inactive',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `account_id`, `staff_id`, `warehouse_code`, `name`, `manager_name`, `phone`, `email`, `address`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(11, 1, 3, 'WH-LAG-001', 'ebby kings lagos central warehouse', 'amara ayo', '08031234567', 'lagos.central@retail.ng', '12 Marina Road, Lagos Island, Lagos', 1, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:41:05', NULL),
(12, 1, 3, 'WH-LAG-002', 'ikeja distribution hub', 'amara ayo', '08039876543', 'ikeja.hub@retail.ng', '45 Allen Avenue, Ikeja, Lagos', 0, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:41:24', NULL),
(13, 1, 3, 'WH-ABJ-001', 'abuja main warehouse', 'amara ayo', '08035557777', 'abuja.main@retail.ng', '20 Garki District, Abuja', 1, 1, 2, '2026-05-08 14:32:06', '2026-08-05 06:38:38', NULL),
(14, 1, 3, 'WH-ABJ-002', 'wuse supply center', 'amara ayo', '08037778888', 'wuse.center@retail.ng', '15 Aminu Kano Crescent, Wuse 2, Abuja', 0, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:41:46', NULL),
(15, 1, 3, 'WH-PH-001', 'port harcourt warehouse', 'amara ayo', '08034445555', 'ph.warehouse@retail.ng', '8 Aba Road, Port Harcourt, Rivers', 0, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:41:58', NULL),
(16, 1, 3, 'WH-KAN-001', 'kano storage facility', 'amara ayo', '08036667777', 'kano.storage@retail.ng', '22 Bompai Industrial Area, Kano', 0, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:42:09', NULL),
(17, 1, 3, 'WH-ENU-001', 'enugu regional warehouse', 'amara ayo', '08038889999', 'enugu.regional@retail.ng', '10 Independence Layout, Enugu', 0, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:42:20', NULL),
(18, 1, 3, 'WH-IBA-001', 'ibadan distribution center', 'amara ayo', '08031112222', 'ibadan.dc@retail.ng', '5 Ring Road, Ibadan, Oyo', 0, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:42:37', NULL),
(19, 1, 3, 'WH-BEN-001', 'benin warehouse', 'amara ayo', '08032223333', 'benin.warehouse@retail.ng', '17 Sapele Road, Benin City, Edo', 0, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:42:53', NULL),
(20, 1, 3, 'WH-UYO-001', 'uyo supply hub', 'amara ayo', '08039991111', 'uyo.hub@retail.ng', '9 Wellington Bassey Way, Uyo, Akwa Ibom', 0, 1, 2, '2026-05-08 14:32:06', '2026-08-03 04:43:08', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account_modules`
--
ALTER TABLE `account_modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `account_settings`
--
ALTER TABLE `account_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_account_module` (`account_id`,`module`);

--
-- Indexes for table `account_subscription`
--
ALTER TABLE `account_subscription`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_staff_date` (`staff_id`,`date`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_account` (`account_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `contacts_account_id_foreign` (`account_id`),
  ADD KEY `contacts_status_index` (`status`),
  ADD KEY `contacts_email_index` (`email`);

--
-- Indexes for table `countries`
--
ALTER TABLE `countries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `account_id_index` (`account_id`);

--
-- Indexes for table `credit_durations`
--
ALTER TABLE `credit_durations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- Indexes for table `default_site_configs`
--
ALTER TABLE `default_site_configs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `designations_account_id_index` (`account_id`);

--
-- Indexes for table `designation_permissions`
--
ALTER TABLE `designation_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `designation_permissions_account_unique` (`account_id`,`designation_id`,`permission_id`),
  ADD KEY `designation_permissions_account_id_index` (`account_id`),
  ADD KEY `designation_permissions_designation_id_index` (`designation_id`),
  ADD KEY `designation_permissions_permission_id_index` (`permission_id`);

--
-- Indexes for table `designation_route`
--
ALTER TABLE `designation_route`
  ADD UNIQUE KEY `designation_route_unique` (`designation_id`,`route_id`),
  ADD KEY `fk_designation_route_route` (`route_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_id` (`product_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `local_governments`
--
ALTER TABLE `local_governments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `state_id` (`state_id`);

--
-- Indexes for table `master_items`
--
ALTER TABLE `master_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `master_items_account_name_unique` (`account_id`,`name`),
  ADD KEY `master_items_account_id_index` (`account_id`),
  ADD KEY `master_items_created_by_index` (`created_by`),
  ADD KEY `master_items_category_id_foreign` (`category_id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menus_account_module_slug_unique` (`account_id`,`module_id`,`slug`),
  ADD KEY `menus_account_id_index` (`account_id`),
  ADD KEY `menus_module_id_index` (`module_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modifier_options`
--
ALTER TABLE `modifier_options`
  ADD PRIMARY KEY (`id`),
  ADD KEY `modifier_id` (`modifier_id`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `modules_account_slug_unique` (`account_id`,`slug`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_types`
--
ALTER TABLE `payment_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_account_status` (`account_id`,`status`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_account_slug_unique` (`account_id`,`slug`),
  ADD KEY `permissions_module_menu_index` (`module_id`,`menu_id`),
  ADD KEY `permissions_status_index` (`status`),
  ADD KEY `permissions_menu_id_foreign` (`menu_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD UNIQUE KEY `barcode` (`barcode`),
  ADD UNIQUE KEY `account_id` (`account_id`,`barcode`),
  ADD KEY `idx_account` (`account_id`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_account_product` (`account_id`,`id`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `master_items` (`master_item_id`) USING BTREE;

--
-- Indexes for table `product_modifiers`
--
ALTER TABLE `product_modifiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `product_stocks`
--
ALTER TABLE `product_stocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_product_per_warehouse` (`account_id`,`warehouse_id`,`master_item_id`),
  ADD KEY `idx_product_id` (`master_item_id`),
  ADD KEY `idx_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_account_warehouse` (`account_id`,`warehouse_id`);

--
-- Indexes for table `product_trackings`
--
ALTER TABLE `product_trackings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_trackings_barcode_unique` (`barcode`),
  ADD KEY `product_trackings_account_id_index` (`account_id`),
  ADD KEY `product_trackings_purchase_id_index` (`purchase_id`),
  ADD KEY `product_trackings_purchase_item_id_index` (`purchase_item_id`),
  ADD KEY `product_trackings_master_item_id_index` (`master_item_id`),
  ADD KEY `product_trackings_warehouse_id_index` (`warehouse_id`),
  ADD KEY `idx_product_tracking_status` (`status`),
  ADD KEY `idx_product_tracking_batch` (`batch_no`),
  ADD KEY `idx_product_tracking_serial` (`serial_no`),
  ADD KEY `idx_product_tracking_expiry` (`expiry_date`),
  ADD KEY `idx_product_tracking_item_warehouse` (`master_item_id`,`warehouse_id`),
  ADD KEY `idx_product_tracking_account_item` (`account_id`,`master_item_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_item_trackings`
--
ALTER TABLE `purchase_item_trackings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_item_trackings_purchase_item_id_foreign` (`purchase_item_id`),
  ADD KEY `purchase_item_trackings_serial_no_index` (`serial_no`),
  ADD KEY `purchase_item_trackings_batch_no_index` (`batch_no`),
  ADD KEY `purchase_item_trackings_tracking_type_index` (`tracking_type`),
  ADD KEY `purchase_item_trackings_is_sold_index` (`is_sold`),
  ADD KEY `purchase_item_trackings_barcode_index` (`barcode`),
  ADD KEY `fk_pit_requisition_item` (`requisition_item_id`),
  ADD KEY `idx_warehouse_barcode` (`warehouse_id`,`barcode`),
  ADD KEY `idx_store_barcode` (`store_id`,`barcode`),
  ADD KEY `idx_barcode` (`barcode`),
  ADD KEY `idx_is_reserved` (`is_reserved`),
  ADD KEY `idx_is_sold` (`is_sold`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_purchase_item` (`purchase_item_id`),
  ADD KEY `idx_requisition` (`requisition_id`),
  ADD KEY `idx_warehouse_status` (`warehouse_id`,`status`,`is_sold`,`is_reserved`),
  ADD KEY `idx_store_status` (`store_id`,`status`,`is_sold`,`is_reserved`);

--
-- Indexes for table `requisitions`
--
ALTER TABLE `requisitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `requisition_no_unique` (`requisition_no`),
  ADD KEY `requisitions_account_id_foreign` (`account_id`),
  ADD KEY `requisitions_from_warehouse_id_foreign` (`from_warehouse_id`),
  ADD KEY `requisitions_created_by_foreign` (`created_by`),
  ADD KEY `requisitions_for_store_id_foreign` (`for_store_id`);

--
-- Indexes for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `requisition_items_requisition_id_foreign` (`requisition_id`),
  ADD KEY `requisition_items_master_item_id_foreign` (`master_item_id`) USING BTREE,
  ADD KEY `requisition_items_accepted_by_foreign` (`accepted_by`);

--
-- Indexes for table `requisition_item_trackings`
--
ALTER TABLE `requisition_item_trackings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rit_requisition_item` (`requisition_item_id`),
  ADD KEY `fk_rit_purchase_item_tracking` (`purchase_item_tracking_id`);

--
-- Indexes for table `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_route` (`uri`,`method`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_no` (`invoice_no`),
  ADD KEY `sales_created_at_index` (`created_at`),
  ADD KEY `fk_sales_store` (`store_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sale_items_sale` (`sale_id`),
  ADD KEY `fk_sale_items_product` (`product_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `sale_items_sale_product_index` (`sale_id`,`product_id`);

--
-- Indexes for table `sale_item_trackings`
--
ALTER TABLE `sale_item_trackings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sit_sale_tracking_unique` (`sale_item_id`,`purchase_item_tracking_id`),
  ADD KEY `sit_tracking_idx` (`purchase_item_tracking_id`);

--
-- Indexes for table `sale_payments`
--
ALTER TABLE `sale_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `sale_returns`
--
ALTER TABLE `sale_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_returns_account_id_return_no_unique` (`account_id`,`return_no`),
  ADD KEY `sale_returns_sale_id_index` (`sale_id`),
  ADD KEY `sale_returns_customer_id_index` (`customer_id`),
  ADD KEY `sale_returns_store_id_index` (`store_id`);

--
-- Indexes for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_return_items_sale_return_id_index` (`sale_return_id`),
  ADD KEY `sale_return_items_sale_item_id_index` (`sale_item_id`),
  ADD KEY `sale_return_items_product_id_index` (`product_id`),
  ADD KEY `sale_return_items_purchase_item_tracking_id_index` (`purchase_item_tracking_id`);

--
-- Indexes for table `sale_return_payments`
--
ALTER TABLE `sale_return_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_return_payments_sale_return_id_index` (`sale_return_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `states`
--
ALTER TABLE `states`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_account_id` (`account_id`),
  ADD KEY `idx_product_id` (`master_item_id`),
  ADD KEY `idx_warehouse_id` (`warehouse_id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `fk_stock_movements_created_by` (`created_by`);

--
-- Indexes for table `stock_returns`
--
ALTER TABLE `stock_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `return_no` (`return_no`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `warehouse_id` (`warehouse_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_return_no` (`return_no`);

--
-- Indexes for table `stock_return_items`
--
ALTER TABLE `stock_return_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `return_id` (`return_id`),
  ADD KEY `master_item_id` (`master_item_id`) USING BTREE;

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_transfer_no_per_account` (`account_id`,`transfer_no`),
  ADD KEY `idx_account_id` (`account_id`),
  ADD KEY `idx_transfer_date` (`date`),
  ADD KEY `fk_transfers_from_warehouse` (`from_warehouse_id`),
  ADD KEY `fk_transfers_to_warehouse` (`to_warehouse_id`),
  ADD KEY `fk_transfers_created_by` (`created_by`),
  ADD KEY `fk_transfers_updated_by` (`updated_by`);

--
-- Indexes for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_transfer_product` (`transfer_id`,`product_id`),
  ADD KEY `fk_transfer_items_product` (`product_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `manager_id` (`manager_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `subscription_payment`
--
ALTER TABLE `subscription_payment`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_account_subscriptions`
--
ALTER TABLE `user_account_subscriptions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_types`
--
ALTER TABLE `user_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_vendor_per_account` (`account_id`,`vendor_code`),
  ADD KEY `idx_account_id` (`account_id`),
  ADD KEY `idx_vendor_code` (`vendor_code`),
  ADD KEY `idx_phone` (`phone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `fk_vendors_created_by` (`created_by`),
  ADD KEY `fk_vendors_updated_by` (`updated_by`);

--
-- Indexes for table `vendor_ledgers`
--
ALTER TABLE `vendor_ledgers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_payments`
--
ALTER TABLE `vendor_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_warehouse_code_per_account` (`account_id`,`warehouse_code`),
  ADD UNIQUE KEY `unique_warehouse_name_per_account` (`account_id`,`name`),
  ADD KEY `idx_account_id` (`account_id`),
  ADD KEY `idx_account_name` (`account_id`,`name`),
  ADD KEY `fk_warehouses_created_by` (`created_by`),
  ADD KEY `fk_warehouses_updated_by` (`updated_by`),
  ADD KEY `warehouses_staff_id_foreign` (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `account_modules`
--
ALTER TABLE `account_modules`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `account_settings`
--
ALTER TABLE `account_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `account_subscription`
--
ALTER TABLE `account_subscription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `countries`
--
ALTER TABLE `countries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `credit_durations`
--
ALTER TABLE `credit_durations`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `default_site_configs`
--
ALTER TABLE `default_site_configs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `designation_permissions`
--
ALTER TABLE `designation_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `local_governments`
--
ALTER TABLE `local_governments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=775;

--
-- AUTO_INCREMENT for table `master_items`
--
ALTER TABLE `master_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `modifier_options`
--
ALTER TABLE `modifier_options`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_types`
--
ALTER TABLE `payment_types`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_modifiers`
--
ALTER TABLE `product_modifiers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_stocks`
--
ALTER TABLE `product_stocks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `product_trackings`
--
ALTER TABLE `product_trackings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_item_trackings`
--
ALTER TABLE `purchase_item_trackings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `requisitions`
--
ALTER TABLE `requisitions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `requisition_items`
--
ALTER TABLE `requisition_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `requisition_item_trackings`
--
ALTER TABLE `requisition_item_trackings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `routes`
--
ALTER TABLE `routes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=350;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sale_item_trackings`
--
ALTER TABLE `sale_item_trackings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sale_payments`
--
ALTER TABLE `sale_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sale_returns`
--
ALTER TABLE `sale_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sale_return_items`
--
ALTER TABLE `sale_return_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sale_return_payments`
--
ALTER TABLE `sale_return_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `states`
--
ALTER TABLE `states`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `stock_adjustments`
--
ALTER TABLE `stock_adjustments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `stock_returns`
--
ALTER TABLE `stock_returns`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_return_items`
--
ALTER TABLE `stock_return_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_transfer_items`
--
ALTER TABLE `stock_transfer_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subscription_payment`
--
ALTER TABLE `subscription_payment`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscription_plans`
--
ALTER TABLE `subscription_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `types`
--
ALTER TABLE `types`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_account_subscriptions`
--
ALTER TABLE `user_account_subscriptions`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_details`
--
ALTER TABLE `user_details`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user_types`
--
ALTER TABLE `user_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `vendor_ledgers`
--
ALTER TABLE `vendor_ledgers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vendor_payments`
--
ALTER TABLE `vendor_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_settings`
--
ALTER TABLE `account_settings`
  ADD CONSTRAINT `fk_account_settings_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupons_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `designations`
--
ALTER TABLE `designations`
  ADD CONSTRAINT `designations_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `designation_permissions`
--
ALTER TABLE `designation_permissions`
  ADD CONSTRAINT `designation_permissions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `designation_permissions_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `designation_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `designation_route`
--
ALTER TABLE `designation_route`
  ADD CONSTRAINT `fk_designation_route_designation` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_designation_route_route` FOREIGN KEY (`route_id`) REFERENCES `routes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `master_items`
--
ALTER TABLE `master_items`
  ADD CONSTRAINT `master_items_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `master_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `master_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `menus_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `modifier_options`
--
ALTER TABLE `modifier_options`
  ADD CONSTRAINT `modifier_options_ibfk_1` FOREIGN KEY (`modifier_id`) REFERENCES `product_modifiers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `modules`
--
ALTER TABLE `modules`
  ADD CONSTRAINT `modules_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `permissions`
--
ALTER TABLE `permissions`
  ADD CONSTRAINT `permissions_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permissions_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_trackings`
--
ALTER TABLE `product_trackings`
  ADD CONSTRAINT `product_trackings_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_trackings_master_item_id_foreign` FOREIGN KEY (`master_item_id`) REFERENCES `master_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_trackings_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_trackings_purchase_item_id_foreign` FOREIGN KEY (`purchase_item_id`) REFERENCES `purchase_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_trackings_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_item_trackings`
--
ALTER TABLE `purchase_item_trackings`
  ADD CONSTRAINT `fk_pit_requisition` FOREIGN KEY (`requisition_id`) REFERENCES `requisitions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pit_requisition_item` FOREIGN KEY (`requisition_item_id`) REFERENCES `requisition_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pit_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pit_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_item_trackings_purchase_item_id_foreign` FOREIGN KEY (`purchase_item_id`) REFERENCES `purchase_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `requisition_items`
--
ALTER TABLE `requisition_items`
  ADD CONSTRAINT `requisition_items_accepted_by_foreign` FOREIGN KEY (`accepted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `requisition_item_trackings`
--
ALTER TABLE `requisition_item_trackings`
  ADD CONSTRAINT `fk_rit_purchase_item_tracking` FOREIGN KEY (`purchase_item_tracking_id`) REFERENCES `purchase_item_trackings` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rit_requisition_item` FOREIGN KEY (`requisition_item_id`) REFERENCES `requisition_items` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `sale_item_trackings`
--
ALTER TABLE `sale_item_trackings`
  ADD CONSTRAINT `sit_sale_item_fk` FOREIGN KEY (`sale_item_id`) REFERENCES `sale_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sit_tracking_fk` FOREIGN KEY (`purchase_item_tracking_id`) REFERENCES `purchase_item_trackings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
