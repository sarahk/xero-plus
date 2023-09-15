-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Sep 15, 2023 at 02:04 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xeroplus`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `ckcontact_id` int(11) NOT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `address_type` varchar(10) NOT NULL DEFAULT 'STREET',
  `address_line1` varchar(100) DEFAULT NULL,
  `address_line2` varchar(100) DEFAULT NULL,
  `address_line3` varchar(100) DEFAULT NULL,
  `address_line4` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `postal_code` char(4) DEFAULT NULL,
  `country` char(11) DEFAULT 'New Zealand',
  `attention_to` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cabins`
--

DROP TABLE IF EXISTS `cabins`;
CREATE TABLE IF NOT EXISTS `cabins` (
  `cabin_id` int(11) NOT NULL AUTO_INCREMENT,
  `cabinnumber` varchar(5) DEFAULT NULL,
  `style` varchar(20) DEFAULT NULL,
  `purchasedate` date DEFAULT NULL,
  `disposaldate` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `notes` text,
  `paintgrey` tinyint(3) DEFAULT '1',
  `paintinside` tinyint(3) DEFAULT '1',
  `updated` datetime DEFAULT NULL,
  `xerotenant_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`cabin_id`),
  KEY `cabinnumber` (`cabinnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_id` char(36) DEFAULT NULL,
  `contact_status` varchar(20) DEFAULT 'New',
  `name` varchar(100) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email_address` varchar(250) DEFAULT NULL,
  `best_way_to_contact` varchar(25) DEFAULT NULL,
  `how_did_you_hear` varchar(25) DEFAULT NULL,
  `is_supplier` tinyint(1) DEFAULT '0',
  `is_customer` tinyint(1) DEFAULT '1',
  `website` varchar(250) DEFAULT NULL,
  `discount` varchar(100) DEFAULT NULL,
  `updated_date_utc` datetime DEFAULT NULL,
  `xerotenant_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

DROP TABLE IF EXISTS `contracts`;
CREATE TABLE IF NOT EXISTS `contracts` (
  `contract_id` int(11) NOT NULL AUTO_INCREMENT,
  `cabin_id` int(11) DEFAULT NULL,
  `ckcontact_id` int(11) NOT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `cabin_type` varchar(10) NOT NULL,
  `hiab` char(3) NOT NULL DEFAULT 'No',
  `painted` varchar(10) NOT NULL DEFAULT '---',
  `winz` varchar(10) NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `scheduled_delivery_date` date NOT NULL,
  `delivery_time` char(5) NOT NULL,
  `pickup_date` date DEFAULT NULL,
  `scheduled_pickup_date` date DEFAULT NULL,
  `address_line1` varchar(100) DEFAULT NULL,
  `address_line2` varchar(100) DEFAULT NULL,
  `address_line3` varchar(100) DEFAULT NULL,
  `address_line4` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `postal_code` varchar(4) DEFAULT NULL,
  `country` char(11) DEFAULT NULL,
  `address` varchar(100) NOT NULL,
  `lat` varchar(25) NOT NULL,
  `long` varchar(25) NOT NULL,
  `place_id` varchar(150) NOT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`contract_id`),
  KEY `cabin_id` (`cabin_id`),
  KEY `contact_id` (`contact_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

DROP TABLE IF EXISTS `invoices`;
CREATE TABLE IF NOT EXISTS `invoices` (
  `invoice_id` char(36) CHARACTER SET utf8 NOT NULL,
  `contact_id` char(36) CHARACTER SET utf8 DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `invoice_number` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `reference` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `amount_due` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `updated_date_utc` datetime DEFAULT NULL,
  `json` text CHARACTER SET utf8,
  `xerotenant_id` char(36) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`invoice_id`),
  KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `foreign_id` int(11) NOT NULL,
  `parent` varchar(25) NOT NULL,
  `notes` text NOT NULL,
  `createdby` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` char(36) CHARACTER SET utf8 NOT NULL,
  `invoice_id` char(36) CHARACTER SET utf8 DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `reference` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `is_reconciled` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `updated_date_utc` datetime DEFAULT NULL,
  `xerotenant_id` char(36) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  KEY `invoice_id` (`invoice_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `phones`
--

DROP TABLE IF EXISTS `phones`;
CREATE TABLE IF NOT EXISTS `phones` (
  `ckcontact_id` int(11) NOT NULL,
  `contact_id` char(36) NOT NULL,
  `phone_type` varchar(10) NOT NULL DEFAULT 'MOBILE',
  `phone_number` varchar(20) DEFAULT NULL,
  `phone_area_code` varchar(10) DEFAULT NULL,
  `phone_country_code` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`contact_id`,`phone_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `xerotenant_id` char(36) NOT NULL,
  `category` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`xerotenant_id`,`category`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tenancies`
--

DROP TABLE IF EXISTS `tenancies`;
CREATE TABLE IF NOT EXISTS `tenancies` (
  `tenant_id` char(36) NOT NULL,
  `name` varchar(25) NOT NULL,
  `shortname` varchar(25) NOT NULL,
  `colour` varchar(20) NOT NULL,
  `sortorder` int(3) NOT NULL,
  PRIMARY KEY (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` char(36) NOT NULL,
  `first_name` varchar(25) NOT NULL,
  `last_name` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vamountdue`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vamountdue`;
CREATE TABLE IF NOT EXISTS `vamountdue` (
`contact_id` char(36)
,`total_due` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

DROP TABLE IF EXISTS `vehicles`;
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `numberplate` char(6) NOT NULL,
  `status` varchar(20) NOT NULL,
  `created` timestamp NOT NULL,
  `modified` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_log`
--

DROP TABLE IF EXISTS `vehicle_log`;
CREATE TABLE IF NOT EXISTS `vehicle_log` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `vehicle_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `start_time` datetime NOT NULL,
  `start_kilometres` int(9) NOT NULL,
  `end_time` datetime NOT NULL,
  `end_kilometres` int(9) NOT NULL,
  `used_for` varchar(20) NOT NULL,
  `notes` text NOT NULL,
  `created` timestamp NOT NULL,
  `modified` timestamp NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `vamountdue`
--
DROP TABLE IF EXISTS `vamountdue`;

DROP VIEW IF EXISTS `vamountdue`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vamountdue`  AS   (select `invoices`.`contact_id` AS `contact_id`,sum(`invoices`.`amount_due`) AS `total_due` from `invoices` where (`invoices`.`status` = 'AUTHORISED') group by `invoices`.`contact_id` order by `invoices`.`contact_id`)  ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
