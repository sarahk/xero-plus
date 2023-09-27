-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Sep 25, 2023 at 03:41 AM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


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

CREATE TABLE `addresses` (
  `address_id` int(11) NOT NULL,
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
  `attention_to` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cabins`
--

CREATE TABLE `cabins` (
  `cabin_id` int(11) NOT NULL,
  `cabinnumber` varchar(5) DEFAULT NULL,
  `style` varchar(20) DEFAULT NULL,
  `purchasedate` date DEFAULT NULL,
  `disposaldate` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `notes` text,
  `paintgrey` tinyint(3) DEFAULT '1',
  `paintinside` tinyint(3) DEFAULT '1',
  `updated` datetime DEFAULT NULL,
  `xerotenant_id` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
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
  `xerotenant_id` char(36) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts` (
  `contract_id` int(11) NOT NULL,
  `repeating_invoice_id` varchar(50) NOT NULL,
  `cabin_id` int(11) DEFAULT NULL,
  `ckcontact_id` int(11) NOT NULL,
  `contact_id` char(36) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'New',
  `schedule_unit` varchar(25) NOT NULL,
  `reference` varchar(25) NOT NULL,
  `cabin_type` varchar(10) DEFAULT NULL,
  `hiab` char(3) NOT NULL DEFAULT 'No',
  `painted` varchar(10) NOT NULL DEFAULT '---',
  `winz` varchar(10) DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `scheduled_delivery_date` date DEFAULT NULL,
  `delivery_time` char(5) DEFAULT NULL,
  `pickup_date` date DEFAULT NULL,
  `scheduled_pickup_date` date DEFAULT NULL,
  `address_line1` varchar(100) DEFAULT NULL,
  `address_line2` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `postal_code` varchar(4) DEFAULT NULL,
  `lat` varchar(25) DEFAULT NULL,
  `long` varchar(25) DEFAULT NULL,
  `place_id` varchar(150) DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  `total` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` char(36) CHARACTER SET utf8 NOT NULL,
  `contact_id` char(36) CHARACTER SET utf8 DEFAULT NULL,
  `contract_id` int(11) NOT NULL,
  `repeating_invoice_id` varchar(50) NOT NULL,
  `status` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `invoice_number` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `reference` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `amount_due` decimal(10,2) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `updated_date_utc` datetime DEFAULT NULL,
  `xerotenant_id` char(36) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `foreign_id` int(11) NOT NULL,
  `parent` varchar(25) NOT NULL,
  `note` text NOT NULL,
  `createdby` int(11) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` char(36) CHARACTER SET utf8 NOT NULL,
  `invoice_id` char(36) CHARACTER SET utf8 DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `status` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `reference` varchar(20) CHARACTER SET utf8 DEFAULT NULL,
  `is_reconciled` varchar(10) CHARACTER SET utf8 DEFAULT NULL,
  `updated_date_utc` datetime DEFAULT NULL,
  `xerotenant_id` char(36) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `phones`
--

CREATE TABLE `phones` (
  `ckcontact_id` int(11) NOT NULL,
  `contact_id` char(36) NOT NULL,
  `phone_type` varchar(10) NOT NULL DEFAULT 'MOBILE',
  `phone_number` varchar(20) DEFAULT NULL,
  `phone_area_code` varchar(10) DEFAULT NULL,
  `phone_country_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `xerotenant_id` char(36) NOT NULL,
  `category` varchar(100) NOT NULL,
  `key` varchar(100) NOT NULL,
  `value` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tenancies`
--

CREATE TABLE `tenancies` (
  `tenant_id` char(36) NOT NULL,
  `name` varchar(25) NOT NULL,
  `shortname` varchar(25) NOT NULL,
  `colour` varchar(20) NOT NULL,
  `sortorder` int(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` char(36) NOT NULL,
  `first_name` varchar(25) NOT NULL,
  `last_name` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vamountdue`
-- (See below for the actual view)
--
CREATE TABLE `vamountdue` (
`contact_id` char(36)
,`total_due` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `numberplate` char(6) NOT NULL,
  `status` varchar(20) NOT NULL,
  `created` timestamp NOT NULL,
  `modified` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_log`
--

CREATE TABLE `vehicle_log` (
  `id` int(9) NOT NULL,
  `vehicle_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `start_time` datetime NOT NULL,
  `start_kilometres` int(9) NOT NULL,
  `end_time` datetime NOT NULL,
  `end_kilometres` int(9) NOT NULL,
  `used_for` varchar(20) NOT NULL,
  `notes` text NOT NULL,
  `created` timestamp NOT NULL,
  `modified` timestamp NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure for view `vamountdue`
--
DROP TABLE IF EXISTS `vamountdue`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vamountdue`  AS   (select `invoices`.`contact_id` AS `contact_id`,sum(`invoices`.`amount_due`) AS `total_due` from `invoices` where (`invoices`.`status` = 'AUTHORISED') group by `invoices`.`contact_id` order by `invoices`.`contact_id`)  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`address_id`);

--
-- Indexes for table `cabins`
--
ALTER TABLE `cabins`
  ADD PRIMARY KEY (`cabin_id`),
  ADD KEY `cabinnumber` (`cabinnumber`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`contract_id`),
  ADD UNIQUE KEY `repeating_invoice_id` (`repeating_invoice_id`),
  ADD KEY `cabin_id` (`cabin_id`),
  ADD KEY `contact_id` (`contact_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`),
  ADD KEY `contact_id` (`contact_id`),
  ADD KEY `contract_id` (`contract_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `phones`
--
ALTER TABLE `phones`
  ADD PRIMARY KEY (`contact_id`,`phone_type`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`xerotenant_id`,`category`,`key`);

--
-- Indexes for table `tenancies`
--
ALTER TABLE `tenancies`
  ADD PRIMARY KEY (`tenant_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_log`
--
ALTER TABLE `vehicle_log`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cabins`
--
ALTER TABLE `cabins`
  MODIFY `cabin_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contracts`
--
ALTER TABLE `contracts`
  MODIFY `contract_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vehicle_log`
--
ALTER TABLE `vehicle_log`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
