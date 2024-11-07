-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Nov 05, 2024 at 04:22 AM
-- Server version: 5.7.44
-- PHP Version: 8.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xeroplus`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity`
(
    `activity_id`     int(11)     NOT NULL,
    `ckcontact_id`    int(11)     NOT NULL,
    `contact_id`      char(36)             DEFAULT NULL,
    `activity_type`   varchar(10) NOT NULL DEFAULT 'SMS',
    `activity_date`   datetime             DEFAULT NULL,
    `activity_status` varchar(100)         DEFAULT 'New',
    `subject`         varchar(100)         DEFAULT '',
    `body`            text,
    `sent`            datetime             DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses`
(
    `address_id`    int(11)     NOT NULL,
    `ckcontact_id`  int(11)     NOT NULL,
    `contact_id`    char(36)             DEFAULT NULL,
    `address_type`  varchar(10) NOT NULL DEFAULT 'STREET',
    `address_line1` varchar(100)         DEFAULT NULL,
    `address_line2` varchar(100)         DEFAULT NULL,
    `address_line3` varchar(100)         DEFAULT NULL,
    `address_line4` varchar(100)         DEFAULT NULL,
    `city`          varchar(100)         DEFAULT NULL,
    `region`        varchar(100)         DEFAULT NULL,
    `postal_code`   char(4)              DEFAULT NULL,
    `country`       char(11)             DEFAULT 'New Zealand',
    `attention_to`  varchar(100)         DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cabins`
--

CREATE TABLE `cabins`
(
    `cabin_id`      int(11) NOT NULL,
    `cabinnumber`   varchar(5)       DEFAULT NULL,
    `style`         varchar(20)      DEFAULT NULL,
    `purchasedate`  date             DEFAULT NULL,
    `disposaldate`  date             DEFAULT NULL,
    `status`        varchar(20)      DEFAULT NULL,
    `notes`         text,
    `paintgrey`     tinyint(3)       DEFAULT '1',
    `paintinside`   varchar(10)      DEFAULT 'unpainted',
    `owner`         char(3) NOT NULL DEFAULT '---',
    `updated`       datetime         DEFAULT NULL,
    `xerotenant_id` char(36)         DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contactjoins`
--

CREATE TABLE `contactjoins`
(
    `id`           int(11) NOT NULL,
    `ckcontact_id` int(11)                                DEFAULT NULL,
    `join_type`    varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `foreign_id`   int(11)                                DEFAULT NULL,
    `sort_order`   tinyint(3)                             DEFAULT NULL,
    `updated`      datetime                               DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts`
(
    `id`                  int(11) NOT NULL,
    `contact_id`          char(36)     DEFAULT NULL,
    `contact_status`      varchar(20)  DEFAULT 'New',
    `xero_status`         varchar(20)  DEFAULT NULL,
    `name`                varchar(100) DEFAULT NULL,
    `first_name`          varchar(100) DEFAULT NULL,
    `last_name`           varchar(100) DEFAULT NULL,
    `email_address`       varchar(250) DEFAULT NULL,
    `best_way_to_contact` varchar(25)  DEFAULT NULL,
    `is_supplier`         tinyint(1)   DEFAULT '0',
    `date_of_birth`       date         DEFAULT NULL,
    `is_customer`         tinyint(1)   DEFAULT '1',
    `website`             varchar(250) DEFAULT NULL,
    `discount`            varchar(100) DEFAULT NULL,
    `updated_date_utc`    datetime     DEFAULT NULL,
    `xerotenant_id`       char(36)     DEFAULT NULL,
    `stub`                tinyint(4)   DEFAULT '0'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contracts`
--

CREATE TABLE `contracts`
(
    `contract_id`             int(11)     NOT NULL,
    `repeating_invoice_id`    varchar(50) NOT NULL,
    `cabin_id`                int(11)              DEFAULT NULL,
    `ckcontact_id`            int(11)     NOT NULL,
    `contact_id`              char(36)             DEFAULT NULL,
    `status`                  varchar(20) NOT NULL DEFAULT 'New',
    `schedule_unit`           varchar(25)          DEFAULT NULL,
    `cabin_use`               varchar(15)          DEFAULT NULL,
    `sms_reminder_invoice`    char(3)              DEFAULT 'DK',
    `reference`               varchar(25)          DEFAULT NULL,
    `tax_type`                varchar(10)          DEFAULT 'NONE',
    `cabin_type`              varchar(10)          DEFAULT NULL,
    `hiab`                    char(3)     NOT NULL DEFAULT 'No',
    `painted`                 varchar(10) NOT NULL DEFAULT '---',
    `winz`                    varchar(10)          DEFAULT NULL,
    `delivery_date`           date                 DEFAULT NULL,
    `how_did_you_hear`        varchar(25)          DEFAULT '',
    `scheduled_delivery_date` date                 DEFAULT NULL,
    `delivery_time`           char(5)              DEFAULT NULL,
    `pickup_date`             date                 DEFAULT NULL,
    `scheduled_pickup_date`   date                 DEFAULT NULL,
    `address_line1`           varchar(100)         DEFAULT NULL,
    `enquiry_rating`          tinyint(3)           DEFAULT '0',
    `address_line2`           varchar(100)         DEFAULT NULL,
    `city`                    varchar(100)         DEFAULT NULL,
    `region`                  varchar(100)         DEFAULT NULL,
    `postal_code`             varchar(4)           DEFAULT NULL,
    `lat`                     varchar(25)          DEFAULT NULL,
    `long`                    varchar(25)          DEFAULT NULL,
    `place_id`                varchar(150)         DEFAULT NULL,
    `updated`                 datetime             DEFAULT NULL,
    `date`                    datetime             DEFAULT NULL,
    `total`                   int(5) UNSIGNED      DEFAULT NULL,
    `stub`                    tinyint(4)           DEFAULT '0',
    `xerotenant_id`           char(36)             DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices`
(
    `invoice_id`           char(36) CHARACTER SET utf8 NOT NULL,
    `contact_id`           char(36) CHARACTER SET utf8    DEFAULT NULL,
    `contract_id`          int(11)                     NOT NULL,
    `repeating_invoice_id` varchar(50)                 NOT NULL,
    `status`               varchar(20) CHARACTER SET utf8 DEFAULT NULL,
    `invoice_number`       varchar(20) CHARACTER SET utf8 DEFAULT NULL,
    `reference`            varchar(20) CHARACTER SET utf8 DEFAULT NULL,
    `total`                decimal(10, 2)                 DEFAULT NULL,
    `amount_due`           decimal(10, 2)                 DEFAULT NULL,
    `amount_paid`          decimal(10, 2)                 DEFAULT NULL,
    `date`                 datetime                       DEFAULT NULL,
    `due_date`             datetime                       DEFAULT NULL,
    `updated_date_utc`     datetime                       DEFAULT NULL,
    `xerotenant_id`        char(36) CHARACTER SET utf8    DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes`
(
    `id`         int(11)     NOT NULL,
    `foreign_id` int(11)     NOT NULL,
    `parent`     varchar(25) NOT NULL,
    `note`       text        NOT NULL,
    `createdby`  int(11)     NOT NULL,
    `created`    timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments`
(
    `payment_id`       char(36) CHARACTER SET utf8 NOT NULL,
    `invoice_id`       char(36) CHARACTER SET utf8    DEFAULT NULL,
    `contract_id`      int(11)                        DEFAULT NULL,
    `contact_id`       char(36)                       DEFAULT NULL,
    `date`             datetime                       DEFAULT NULL,
    `status`           varchar(20) CHARACTER SET utf8 DEFAULT NULL,
    `amount`           decimal(10, 2)                 DEFAULT NULL,
    `reference`        varchar(20) CHARACTER SET utf8 DEFAULT NULL,
    `is_reconciled`    varchar(10) CHARACTER SET utf8 DEFAULT NULL,
    `updated`          datetime                       DEFAULT NULL,
    `is_batch`         tinyint(3)                     DEFAULT NULL,
    `updated_date_utc` datetime                       DEFAULT NULL,
    `payment_type`     varchar(25)                    DEFAULT NULL,
    `xerotenant_id`    char(36) CHARACTER SET utf8    DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `phones`
--

CREATE TABLE `phones`
(
    `ckcontact_id`       int(11)     NOT NULL,
    `contact_id`         char(36)    NOT NULL,
    `phone_type`         varchar(10) NOT NULL DEFAULT 'MOBILE',
    `phone_number`       varchar(20)          DEFAULT NULL,
    `phone_area_code`    varchar(10)          DEFAULT NULL,
    `phone_country_code` varchar(10)          DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings`
(
    `xerotenant_id` char(36)     NOT NULL,
    `category`      varchar(100) NOT NULL,
    `key`           varchar(100) NOT NULL,
    `value`         varchar(100) DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks`
(
    `id`            int(11)     NOT NULL,
    `xerotenant_id` char(36)    NOT NULL,
    `cabin_id`      int(11)              DEFAULT NULL,
    `name`          varchar(25) NOT NULL,
    `details`       text,
    `task_type`     varchar(25) NOT NULL,
    `due_date`      date        NOT NULL,
    `status`        varchar(25) NOT NULL DEFAULT 'new',
    `updated`       datetime    NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

CREATE TABLE `templates`
(
    `id`          int(11)                        NOT NULL,
    `status`      tinyint(3)                     NOT NULL,
    `messagetype` char(5) CHARACTER SET utf8     NOT NULL,
    `label`       varchar(30) CHARACTER SET utf8 NOT NULL,
    `subject`     varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `body`        text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    `dateupdate`  datetime                       NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `tenancies`
--

CREATE TABLE `tenancies`
(
    `tenant_id` char(36)    NOT NULL,
    `name`      varchar(25) NOT NULL,
    `shortname` varchar(25) NOT NULL,
    `colour`    varchar(20) NOT NULL,
    `sortorder` int(3)      NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

--
-- Dumping data for table `tenancies`
--

INSERT INTO `tenancies` (`tenant_id`, `name`, `shortname`, `colour`, `sortorder`)
VALUES ('ae75d056-4af7-484d-b709-94439130faa4', 'Cabin King', 'auckland', 'yellow', 1),
       ('e95df930-c903-4c58-aee9-bbc21b78bde7', 'Cabin King Waikato', 'waikato', 'cyan', 2),
       ('eafd3b39-46c7-41e4-ba4e-6ea6685e39f7', 'Cabin King BoP', 'bop', 'purple', 3);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users`
(
    `id`         int(11)      NOT NULL,
    `user_id`    char(36)     NOT NULL,
    `first_name` varchar(25)  NOT NULL,
    `last_name`  varchar(25)  NOT NULL,
    `email`      varchar(100) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `first_name`, `last_name`, `email`)
VALUES (1, '87ff2891-a223-4f3e-8e65-cfbd5db3c717', 'Sarah', 'King', 'sarah@itamer.com');

-- --------------------------------------------------------

--
-- Table structure for table `userstenancies`
--

CREATE TABLE `userstenancies`
(
    `user_id`       int(11)                             NOT NULL,
    `xerouser_id`   char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
    `xerotenant_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

--
-- Dumping data for table `userstenancies`
--

INSERT INTO `userstenancies` (`user_id`, `xerouser_id`, `xerotenant_id`)
VALUES (1, '98299269-5603-4c21-8a02-9e5b5b2b373c', 'ae75d056-4af7-484d-b709-94439130faa4'),
       (1, '5cf767b2-c0d5-4fed-8e0b-913bced62d5e', 'e95df930-c903-4c58-aee9-bbc21b78bde7'),
       (1, 'b748e3b0-b813-48bb-8c7c-9f803ef711e7', 'eafd3b39-46c7-41e4-ba4e-6ea6685e39f7');

-- --------------------------------------------------------

--
-- Stand-in structure for view `vamountdue`
-- (See below for the actual view)
--
CREATE TABLE `vamountdue`
(
    `contact_id` char(36),
    `total_due`  decimal(32, 2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vcombo`
-- (See below for the actual view)
--
CREATE TABLE `vcombo`
(
    `row_type`       varchar(1),
    `invoice_id`     char(36),
    `payment_id`     varchar(36),
    `status`         varchar(20),
    `invoice_number` varchar(20),
    `contract_id`    int(11),
    `reference`      varchar(20),
    `amount`         decimal(10, 2),
    `amount_due`     decimal(18, 2),
    `date`           datetime,
    `contact_id`     char(36),
    `xerotenant_id`  char(36)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vdebts`
-- (See below for the actual view)
--
CREATE TABLE `vdebts`
(
    `xerotenant_id`        char(36),
    `repeating_invoice_id` varchar(50),
    `contact_id`           char(36),
    `total`                decimal(32, 2),
    `amount_due`           decimal(32, 2),
    `counter`              bigint(21),
    `oldest`               datetime,
    `newest`               datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles`
(
    `id`            int(11)     NOT NULL,
    `numberplate`   char(6)     NOT NULL,
    `status`        varchar(20) NOT NULL,
    `notes`         text,
    `xerotenant_id` char(36)    NOT NULL,
    `created`       timestamp   NOT NULL,
    `modified`      timestamp   NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_log`
--

CREATE TABLE `vehicle_log`
(
    `id`               int(9)      NOT NULL,
    `vehicle_id`       int(9)      NOT NULL,
    `user_id`          int(9)      NOT NULL,
    `start_time`       datetime    NOT NULL,
    `start_kilometres` int(9)      NOT NULL,
    `end_time`         datetime    NOT NULL,
    `end_kilometres`   int(9)      NOT NULL,
    `used_for`         varchar(20) NOT NULL,
    `notes`            text        NOT NULL,
    `created`          timestamp   NOT NULL,
    `modified`         timestamp   NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vinvoices`
-- (See below for the actual view)
--
CREATE TABLE `vinvoices`
(
    `contract_id`          int(11),
    `xerotenant_id`        char(36),
    `repeating_invoice_id` varchar(50),
    `pickup_date`          date,
    `contract_status`      varchar(20),
    `enquiry_rating`       tinyint(3),
    `invoice_id`           char(36),
    `contact_id`           char(36),
    `status`               varchar(20),
    `invoice_number`       varchar(20),
    `reference`            varchar(20),
    `total`                decimal(10, 2),
    `amount_due`           decimal(10, 2),
    `amount_paid`          decimal(10, 2),
    `date`                 datetime,
    `due_date`             datetime,
    `updated_date_utc`     datetime
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vold_debts`
-- (See below for the actual view)
--
CREATE TABLE `vold_debts`
(
    `xerotenant_id`        char(36),
    `repeating_invoice_id` varchar(50),
    `contact_id`           char(36),
    `total_amount`         decimal(32, 2),
    `amount_due`           decimal(32, 2),
    `total_weeks`          bigint(21),
    `oldest`               datetime,
    `newest`               datetime,
    `weeks_due`            bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `weeks`
--

CREATE TABLE `weeks`
(
    `week_number` int(11) NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure for view `vamountdue`
--
DROP TABLE IF EXISTS `vamountdue`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `vamountdue` AS
(
select `invoices`.`contact_id` AS `contact_id`, sum(`invoices`.`amount_due`) AS `total_due`
from `invoices`
where (`invoices`.`status` = 'AUTHORISED')
group by `invoices`.`contact_id`
order by `invoices`.`contact_id`);

-- --------------------------------------------------------

--
-- Structure for view `vcombo`
--
DROP TABLE IF EXISTS `vcombo`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `vcombo` AS
SELECT 'I'                         AS `row_type`,
       `invoices`.`invoice_id`     AS `invoice_id`,
       ''                          AS `payment_id`,
       `invoices`.`status`         AS `status`,
       `invoices`.`invoice_number` AS `invoice_number`,
       `invoices`.`contract_id`    AS `contract_id`,
       `invoices`.`reference`      AS `reference`,
       `invoices`.`total`          AS `amount`,
       `invoices`.`amount_due`     AS `amount_due`,
       `invoices`.`date`           AS `date`,
       `invoices`.`contact_id`     AS `contact_id`,
       `invoices`.`xerotenant_id`  AS `xerotenant_id`
FROM `invoices`
union
select 'P'                        AS `row_type`,
       `payments`.`invoice_id`    AS `invoice_id`,
       `payments`.`payment_id`    AS `payment_id`,
       `payments`.`status`        AS `status`,
       ''                         AS `invoice_number`,
       `payments`.`contract_id`   AS `contract_id`,
       `payments`.`reference`     AS `reference`,
       `payments`.`amount`        AS `amount`,
       0                          AS `amount_due`,
       `payments`.`date`          AS `date`,
       `payments`.`contact_id`    AS `contact_id`,
       `payments`.`xerotenant_id` AS `xerotenant_id`
from `payments`;

-- --------------------------------------------------------

--
-- Structure for view `vdebts`
--
DROP TABLE IF EXISTS `vdebts`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `vdebts` AS
(
select `invoices`.`xerotenant_id`                                                                              AS `xerotenant_id`,
       `invoices`.`repeating_invoice_id`                                                                       AS `repeating_invoice_id`,
       `invoices`.`contact_id`                                                                                 AS `contact_id`,
       sum(`invoices`.`total`)                                                                                 AS `total`,
       sum(`invoices`.`amount_due`)                                                                            AS `amount_due`,
       count(`invoices`.`invoice_id`)                                                                          AS `counter`,
       (select min(`i2`.`date`)
        from `invoices` `i2`
        where ((`i2`.`repeating_invoice_id` = `invoices`.`repeating_invoice_id`) and
               (`i2`.`amount_due` > 0)))                                                                       AS `oldest`,
       (select max(`i3`.`date`)
        from `invoices` `i3`
        where ((`i3`.`repeating_invoice_id` = `invoices`.`repeating_invoice_id`) and
               (`i3`.`amount_due` > 0)))                                                                       AS `newest`
from `invoices`
where ((`invoices`.`amount_due` > 0) and (`invoices`.`status` = 'AUTHORISED') and
       ((to_days(`invoices`.`date`) - to_days(now())) < -(7)))
group by `invoices`.`xerotenant_id`, `invoices`.`repeating_invoice_id`, `invoices`.`contact_id`);

-- --------------------------------------------------------

--
-- Structure for view `vinvoices`
--
DROP TABLE IF EXISTS `vinvoices`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `vinvoices` AS
SELECT `contracts`.`contract_id`          AS `contract_id`,
       `contracts`.`xerotenant_id`        AS `xerotenant_id`,
       `contracts`.`repeating_invoice_id` AS `repeating_invoice_id`,
       `contracts`.`pickup_date`          AS `pickup_date`,
       `contracts`.`status`               AS `contract_status`,
       `contracts`.`enquiry_rating`       AS `enquiry_rating`,
       `invoices`.`invoice_id`            AS `invoice_id`,
       `invoices`.`contact_id`            AS `contact_id`,
       `invoices`.`status`                AS `status`,
       `invoices`.`invoice_number`        AS `invoice_number`,
       `invoices`.`reference`             AS `reference`,
       `invoices`.`total`                 AS `total`,
       `invoices`.`amount_due`            AS `amount_due`,
       `invoices`.`amount_paid`           AS `amount_paid`,
       `invoices`.`date`                  AS `date`,
       `invoices`.`due_date`              AS `due_date`,
       `invoices`.`updated_date_utc`      AS `updated_date_utc`
FROM (`contracts` left join `invoices` on ((`invoices`.`repeating_invoice_id` = `contracts`.`repeating_invoice_id`)))
WHERE ((`invoices`.`status` = 'AUTHORISED') AND ((to_days(`invoices`.`date`) - to_days(now())) < -(7)));

-- --------------------------------------------------------

--
-- Structure for view `vold_debts`
--
DROP TABLE IF EXISTS `vold_debts`;

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `vold_debts` AS
SELECT `vinvoices`.`xerotenant_id`        AS `xerotenant_id`,
       `vinvoices`.`repeating_invoice_id` AS `repeating_invoice_id`,
       `vinvoices`.`contact_id`           AS `contact_id`,
       `agg1`.`total_amount`              AS `total_amount`,
       `agg1`.`amount_due`                AS `amount_due`,
       `agg1`.`total_weeks`               AS `total_weeks`,
       `agg2`.`oldest`                    AS `oldest`,
       `agg2`.`newest`                    AS `newest`,
       `agg2`.`weeks_due`                 AS `weeks_due`
FROM ((`vinvoices` left join (select `vinvoices`.`repeating_invoice_id` AS `repeating_invoice_id`,
                                     sum(`vinvoices`.`total`)           AS `total_amount`,
                                     sum(`vinvoices`.`amount_due`)      AS `amount_due`,
                                     count(`vinvoices`.`invoice_id`)    AS `total_weeks`
                              from `vinvoices`
                              group by `vinvoices`.`xerotenant_id`, `vinvoices`.`repeating_invoice_id`) `agg1`
       on ((`agg1`.`repeating_invoice_id` = `vinvoices`.`repeating_invoice_id`))) left join (select `vinvoices`.`repeating_invoice_id` AS `repeating_invoice_id`,
                                                                                                    min(`vinvoices`.`date`)            AS `oldest`,
                                                                                                    max(`vinvoices`.`date`)            AS `newest`,
                                                                                                    count(0)                           AS `weeks_due`
                                                                                             from `vinvoices`
                                                                                             where (`vinvoices`.`amount_due` > 0)
                                                                                             group by `vinvoices`.`repeating_invoice_id`) `agg2`
      on ((`agg2`.`repeating_invoice_id` = `vinvoices`.`repeating_invoice_id`)))
GROUP BY `vinvoices`.`xerotenant_id`, `vinvoices`.`repeating_invoice_id`, `vinvoices`.`contact_id`;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
    ADD PRIMARY KEY (`activity_id`);

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
-- Indexes for table `contactjoins`
--
ALTER TABLE `contactjoins`
    ADD PRIMARY KEY (`id`),
    ADD KEY `ckcontact_id` (`ckcontact_id`),
    ADD KEY `join` (`join_type`, `foreign_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
    ADD PRIMARY KEY (`id`),
    ADD KEY `contact_id` (`contact_id`),
    ADD KEY `xerotenant_id` (`xerotenant_id`);

--
-- Indexes for table `contracts`
--
ALTER TABLE `contracts`
    ADD PRIMARY KEY (`contract_id`),
    ADD UNIQUE KEY `repeating_invoice_id` (`repeating_invoice_id`),
    ADD KEY `cabin_id` (`cabin_id`),
    ADD KEY `contact_id` (`contact_id`),
    ADD KEY `status` (`status`),
    ADD KEY `xerotenant_id` (`xerotenant_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
    ADD UNIQUE KEY `invoice_id` (`invoice_id`),
    ADD KEY `contact_id` (`contact_id`),
    ADD KEY `contract_id` (`contract_id`),
    ADD KEY `repeating_invoice_id` (`repeating_invoice_id`),
    ADD KEY `status` (`status`),
    ADD KEY `xerotenant_id` (`xerotenant_id`),
    ADD KEY `invoice_number` (`invoice_number`),
    ADD KEY `date` (`date`),
    ADD KEY `amount_due` (`amount_due`);

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
    ADD KEY `invoice_id` (`invoice_id`),
    ADD KEY `contact_id` (`contact_id`),
    ADD KEY `xeroTenantId` (`xerotenant_id`);

--
-- Indexes for table `phones`
--
ALTER TABLE `phones`
    ADD PRIMARY KEY (`contact_id`, `phone_type`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
    ADD PRIMARY KEY (`xerotenant_id`, `category`, `key`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `templates`
--
ALTER TABLE `templates`
    ADD PRIMARY KEY (`id`);

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
    ADD PRIMARY KEY (`id`),
    ADD KEY `xerotenant_id` (`xerotenant_id`),
    ADD KEY `numberplate` (`numberplate`);

--
-- Indexes for table `vehicle_log`
--
ALTER TABLE `vehicle_log`
    ADD PRIMARY KEY (`id`);

--
-- Indexes for table `weeks`
--
ALTER TABLE `weeks`
    ADD PRIMARY KEY (`week_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity`
--
ALTER TABLE `activity`
    MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `contactjoins`
--
ALTER TABLE `contactjoins`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `templates`
--
ALTER TABLE `templates`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,
    AUTO_INCREMENT = 2;

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

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
