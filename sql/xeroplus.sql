# ************************************************************
# Sequel Ace SQL dump
# Version 20051
#
# https://sequel-ace.com/
# https://github.com/Sequel-Ace/Sequel-Ace
#
# Host: localhost (MySQL 5.7.39)
# Database: xeroplus
# Generation Time: 2023-10-02 09:28:00 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0 */;
/*!40101 SET @OLD_SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO', SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES = @@SQL_NOTES, SQL_NOTES = 0 */;

CREATE TABLE `activity`
(
    `activity_id`     int(11)     NOT NULL AUTO_INCREMENT,
    `ckcontact_id`    int(11)     NOT NULL,
    `contact_id`      char(36)             DEFAULT NULL,
    `activity_type`   varchar(10) NOT NULL DEFAULT 'SMS',
    `activity_date`   datetime,
    `activity_status` varchar(100)         DEFAULT 'New',
    `subject`         varchar(100)         DEFAULT '',
    `body`            text,
    `sent`            datetime,
    PRIMARY KEY (`activity_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;


# Dump of table addresses
# ------------------------------------------------------------

CREATE TABLE `addresses`
(
    `address_id`    int(11)     NOT NULL AUTO_INCREMENT,
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
    `attention_to`  varchar(100)         DEFAULT NULL,
    PRIMARY KEY (`address_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;



# Dump of table cabins
# ------------------------------------------------------------

CREATE TABLE `cabins`
(
    `cabin_id`      int(11) NOT NULL AUTO_INCREMENT,
    `cabinnumber`   varchar(5)  DEFAULT NULL,
    `style`         varchar(20) DEFAULT NULL,
    `purchasedate`  date        DEFAULT NULL,
    `disposaldate`  date        DEFAULT NULL,
    `status`        varchar(20) DEFAULT NULL,
    `notes`         text,
    `paintgrey`     tinyint(3)  DEFAULT '1',
    `paintinside`   tinyint(3)  DEFAULT '1',
    `updated`       datetime    DEFAULT NULL,
    `xerotenant_id` char(36)    DEFAULT NULL,
    PRIMARY KEY (`cabin_id`),
    KEY `cabinnumber` (`cabinnumber`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;



# Dump of table contacts
# ------------------------------------------------------------

CREATE TABLE `contacts`
(
    `id`                  int(11) NOT NULL AUTO_INCREMENT,
    `contact_id`          char(36)     DEFAULT NULL,
    `contact_status`      varchar(20)  DEFAULT 'New',
    `name`                varchar(100) DEFAULT NULL,
    `first_name`          varchar(100) DEFAULT NULL,
    `last_name`           varchar(100) DEFAULT NULL,
    `email_address`       varchar(250) DEFAULT NULL,
    `best_way_to_contact` varchar(25)  DEFAULT NULL,
    `how_did_you_hear`    varchar(25)  DEFAULT NULL,
    `is_supplier`         tinyint(1)   DEFAULT '0',
    `is_customer`         tinyint(1)   DEFAULT '1',
    `website`             varchar(250) DEFAULT NULL,
    `discount`            varchar(100) DEFAULT NULL,
    `updated_date_utc`    datetime     DEFAULT NULL,
    `xerotenant_id`       char(36)     DEFAULT NULL,
    `stub`                tinyint(4)   DEFAULT '0',
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;



# Dump of table contracts
# ------------------------------------------------------------

CREATE TABLE `contracts`
(
    `contract_id`             int(11)     NOT NULL AUTO_INCREMENT,
    `repeating_invoice_id`    varchar(50) NOT NULL,
    `cabin_id`                int(11)              DEFAULT NULL,
    `ckcontact_id`            int(11)     NOT NULL,
    `contact_id`              char(36)             DEFAULT NULL,
    `status`                  varchar(20) NOT NULL DEFAULT 'New',
    `schedule_unit`           varchar(25) NOT NULL,
    `reference`               varchar(25) NOT NULL,
    `cabin_type`              varchar(10)          DEFAULT NULL,
    `hiab`                    char(3)     NOT NULL DEFAULT 'No',
    `painted`                 varchar(10) NOT NULL DEFAULT '---',
    `winz`                    varchar(10)          DEFAULT NULL,
    `delivery_date`           date                 DEFAULT NULL,
    `scheduled_delivery_date` date                 DEFAULT NULL,
    `delivery_time`           char(5)              DEFAULT NULL,
    `pickup_date`             date                 DEFAULT NULL,
    `scheduled_pickup_date`   date                 DEFAULT NULL,
    `address_line1`           varchar(100)         DEFAULT NULL,
    `address_line2`           varchar(100)         DEFAULT NULL,
    `city`                    varchar(100)         DEFAULT NULL,
    `region`                  varchar(100)         DEFAULT NULL,
    `postal_code`             varchar(4)           DEFAULT NULL,
    `lat`                     varchar(25)          DEFAULT NULL,
    `long`                    varchar(25)          DEFAULT NULL,
    `place_id`                varchar(150)         DEFAULT NULL,
    `updated`                 datetime             DEFAULT NULL,
    `total`                   int(5)               DEFAULT NULL,
    `stub`                    tinyint(4)           DEFAULT '0',
    PRIMARY KEY (`contract_id`),
    UNIQUE KEY `repeating_invoice_id` (`repeating_invoice_id`),
    KEY `cabin_id` (`cabin_id`),
    KEY `contact_id` (`contact_id`),
    KEY `status` (`status`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;



# Dump of table invoices
# ------------------------------------------------------------

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
    `xerotenant_id`        char(36) CHARACTER SET utf8    DEFAULT NULL,
    UNIQUE KEY `invoice_id` (`invoice_id`),
    KEY `contact_id` (`contact_id`),
    KEY `contract_id` (`contract_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;



# Dump of table notes
# ------------------------------------------------------------

CREATE TABLE `notes`
(
    `id`         int(11)     NOT NULL AUTO_INCREMENT,
    `foreign_id` int(11)     NOT NULL,
    `parent`     varchar(25) NOT NULL,
    `note`       text        NOT NULL,
    `createdby`  int(11)     NOT NULL,
    `created`    timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;



# Dump of table payments
# ------------------------------------------------------------

CREATE TABLE `payments`
(
    `payment_id`       char(36) CHARACTER SET utf8 NOT NULL,
    `invoice_id`       char(36) CHARACTER SET utf8    DEFAULT NULL,
    `date`             datetime                       DEFAULT NULL,
    `status`           varchar(20) CHARACTER SET utf8 DEFAULT NULL,
    `amount`           decimal(10, 2)                 DEFAULT NULL,
    `reference`        varchar(20) CHARACTER SET utf8 DEFAULT NULL,
    `is_reconciled`    varchar(10) CHARACTER SET utf8 DEFAULT NULL,
    `updated_date_utc` datetime                       DEFAULT NULL,
    `xerotenant_id`    char(36) CHARACTER SET utf8    DEFAULT NULL,
    PRIMARY KEY (`payment_id`),
    KEY `invoice_id` (`invoice_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;



# Dump of table phones
# ------------------------------------------------------------

CREATE TABLE `phones`
(
    `ckcontact_id`       int(11)     NOT NULL,
    `contact_id`         char(36)    NOT NULL,
    `phone_type`         varchar(10) NOT NULL DEFAULT 'MOBILE',
    `phone_number`       varchar(20)          DEFAULT NULL,
    `phone_area_code`    varchar(10)          DEFAULT NULL,
    `phone_country_code` varchar(10)          DEFAULT NULL,
    PRIMARY KEY (`contact_id`, `phone_type`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;



# Dump of table settings
# ------------------------------------------------------------

CREATE TABLE `settings`
(
    `xerotenant_id` char(36)     NOT NULL,
    `category`      varchar(100) NOT NULL,
    `key`           varchar(100) NOT NULL,
    `value`         varchar(100) DEFAULT NULL,
    PRIMARY KEY (`xerotenant_id`, `category`, `key`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;



# Dump of table tenancies
# ------------------------------------------------------------

CREATE TABLE `tenancies`
(
    `tenant_id` char(36)    NOT NULL,
    `name`      varchar(25) NOT NULL,
    `shortname` varchar(25) NOT NULL,
    `colour`    varchar(20) NOT NULL,
    `sortorder` int(3)      NOT NULL,
    PRIMARY KEY (`tenant_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

LOCK TABLES `tenancies` WRITE;
/*!40000 ALTER TABLE `tenancies`
    DISABLE KEYS */;

INSERT INTO `tenancies` (`tenant_id`, `name`, `shortname`, `colour`, `sortorder`)
VALUES ('ae75d056-4af7-484d-b709-94439130faa4', 'Cabin King', 'auckland', 'yellow', 1),
       ('e95df930-c903-4c58-aee9-bbc21b78bde7', 'Cabin King Waikato', 'waikato', 'cyan', 2),
       ('eafd3b39-46c7-41e4-ba4e-6ea6685e39f7', 'Cabin King BoP', 'bop', 'purple', 3);

/*!40000 ALTER TABLE `tenancies`
    ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

CREATE TABLE `users`
(
    `id`         int(11)      NOT NULL AUTO_INCREMENT,
    `user_id`    char(36)     NOT NULL,
    `first_name` varchar(25)  NOT NULL,
    `last_name`  varchar(25)  NOT NULL,
    `email`      varchar(100) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users`
    DISABLE KEYS */;

INSERT INTO `users` (`id`, `user_id`, `first_name`, `last_name`, `email`)
VALUES (1, '87ff2891-a223-4f3e-8e65-cfbd5db3c717', 'Sarah', 'King', 'sarah@itamer.com');

/*!40000 ALTER TABLE `users`
    ENABLE KEYS */;
UNLOCK TABLES;



# Dump of table vehicle_log
# ------------------------------------------------------------

CREATE TABLE `vehicle_log`
(
    `id`               int(9)      NOT NULL AUTO_INCREMENT,
    `vehicle_id`       int(9)      NOT NULL,
    `user_id`          int(9)      NOT NULL,
    `start_time`       datetime    NOT NULL,
    `start_kilometres` int(9)      NOT NULL,
    `end_time`         datetime    NOT NULL,
    `end_kilometres`   int(9)      NOT NULL,
    `used_for`         varchar(20) NOT NULL,
    `notes`            text        NOT NULL,
    `created`          timestamp   NOT NULL,
    `modified`         timestamp   NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;



# Dump of table vehicles
# ------------------------------------------------------------

CREATE TABLE `vehicles`
(
    `id`          int(11)     NOT NULL AUTO_INCREMENT,
    `numberplate` char(6)     NOT NULL,
    `status`      varchar(20) NOT NULL,
    `created`     timestamp   NOT NULL,
    `modified`    timestamp   NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE `templates`
(
    `id`          int(11)                        NOT NULL AUTO_INCREMENT,
    `status`      tinyint(3)                     NOT NULL,
    `messagetype` char(5) CHARACTER SET utf8     NOT NULL,
    `label`       varchar(30) CHARACTER SET utf8 NOT NULL,
    `subject`     varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `body`        text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
    `dateupdate`  datetime                       NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8mb4;
# Dump of view vamountdue
# ------------------------------------------------------------

CREATE ALGORITHM = UNDEFINED DEFINER =`root`@`localhost` SQL SECURITY DEFINER VIEW `vamountdue` AS
(
select `invoices`.`contact_id` AS `contact_id`, sum(`invoices`.`amount_due`) AS `total_due`
from `invoices`
where (`invoices`.`status` = 'AUTHORISED')
group by `invoices`.`contact_id`
order by `invoices`.`contact_id`);


/*!40111 SET SQL_NOTES = @OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE = @OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
