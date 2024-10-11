SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `activity`
(
    `activity_id`     int(11)     NOT NULL AUTO_INCREMENT,
    `ckcontact_id`    int(11)     NOT NULL,
    `contact_id`      char(36)             DEFAULT NULL,
    `activity_type`   varchar(10) NOT NULL DEFAULT 'SMS',
    `activity_date`   datetime             DEFAULT NULL,
    `activity_status` varchar(100)         DEFAULT 'New',
    `subject`         varchar(100)         DEFAULT '',
    `body`            text,
    `sent`            datetime             DEFAULT NULL,
    PRIMARY KEY (`activity_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `addresses`
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

CREATE TABLE IF NOT EXISTS `cabins`
(
    `cabin_id`      int(11) NOT NULL AUTO_INCREMENT,
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
    `xerotenant_id` char(36)         DEFAULT NULL,
    PRIMARY KEY (`cabin_id`),
    KEY `cabinnumber` (`cabinnumber`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 271
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `contactjoins`
(
    `id`           int(11) NOT NULL AUTO_INCREMENT,
    `ckcontact_id` int(11)                                DEFAULT NULL,
    `join_type`    varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `foreign_id`   int(11)                                DEFAULT NULL,
    `updated`      datetime                               DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `ckcontact_id` (`ckcontact_id`),
    KEY `join` (`join_type`, `foreign_id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 786
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contacts`
(
    `id`                  int(11) NOT NULL AUTO_INCREMENT,
    `contact_id`          char(36)     DEFAULT NULL,
    `contact_status`      varchar(20)  DEFAULT 'New',
    `xero_status`         varchar(20)  DEFAULT NULL,
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
    PRIMARY KEY (`id`),
    KEY `contact_id` (`contact_id`),
    KEY `xerotenant_id` (`xerotenant_id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 17502
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `contracts`
(
    `contract_id`             int(11)     NOT NULL AUTO_INCREMENT,
    `repeating_invoice_id`    varchar(50) NOT NULL,
    `cabin_id`                int(11)              DEFAULT NULL,
    `ckcontact_id`            int(11)     NOT NULL,
    `contact_id`              char(36)             DEFAULT NULL,
    `status`                  varchar(20) NOT NULL DEFAULT 'New',
    `schedule_unit`           varchar(25)          DEFAULT NULL,
    `reference`               varchar(25)          DEFAULT NULL,
    `tax_type`                varchar(10)          DEFAULT 'NONE',
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
    `total`                   int(5) UNSIGNED      DEFAULT NULL,
    `stub`                    tinyint(4)           DEFAULT '0',
    `xerotenant_id`           char(36)             DEFAULT NULL,
    PRIMARY KEY (`contract_id`),
    UNIQUE KEY `repeating_invoice_id` (`repeating_invoice_id`),
    KEY `cabin_id` (`cabin_id`),
    KEY `contact_id` (`contact_id`),
    KEY `status` (`status`),
    KEY `xerotenant_id` (`xerotenant_id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 9906
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `invoices`
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
    KEY `contract_id` (`contract_id`),
    KEY `repeating_invoice_id` (`repeating_invoice_id`),
    KEY `status` (`status`),
    KEY `xerotenant_id` (`xerotenant_id`),
    KEY `invoice_number` (`invoice_number`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `notes`
(
    `id`         int(11)     NOT NULL AUTO_INCREMENT,
    `foreign_id` int(11)     NOT NULL,
    `parent`     varchar(25) NOT NULL,
    `note`       text        NOT NULL,
    `createdby`  int(11)     NOT NULL,
    `created`    timestamp   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 7
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `payments`
(
    `payment_id`       char(36) CHARACTER SET utf8 NOT NULL,
    `invoice_id`       char(36) CHARACTER SET utf8    DEFAULT NULL,
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
    `xerotenant_id`    char(36) CHARACTER SET utf8    DEFAULT NULL,
    PRIMARY KEY (`payment_id`),
    KEY `invoice_id` (`invoice_id`),
    KEY `contact_id` (`contact_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `phones`
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

CREATE TABLE IF NOT EXISTS `settings`
(
    `xerotenant_id` char(36)     NOT NULL,
    `category`      varchar(100) NOT NULL,
    `key`           varchar(100) NOT NULL,
    `value`         varchar(100) DEFAULT NULL,
    PRIMARY KEY (`xerotenant_id`, `category`, `key`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `tasks`
(
    `id`            int(11)     NOT NULL AUTO_INCREMENT,
    `xerotenant_id` char(36)    NOT NULL,
    `cabin_id`      int(11)              DEFAULT NULL,
    `name`          varchar(25) NOT NULL,
    `details`       text,
    `task_type`     varchar(25) NOT NULL,
    `due_date`      date        NOT NULL,
    `status`        varchar(25) NOT NULL DEFAULT 'new',
    `updated`       datetime    NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 103
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `templates`
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
  AUTO_INCREMENT = 3
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `tenancies`
(
    `tenant_id` char(36)    NOT NULL,
    `name`      varchar(25) NOT NULL,
    `shortname` varchar(25) NOT NULL,
    `colour`    varchar(20) NOT NULL,
    `sortorder` int(3)      NOT NULL,
    PRIMARY KEY (`tenant_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

INSERT INTO `tenancies` (`tenant_id`, `name`, `shortname`, `colour`, `sortorder`)
VALUES ('ae75d056-4af7-484d-b709-94439130faa4', 'Cabin King', 'auckland', 'yellow', 1),
       ('e95df930-c903-4c58-aee9-bbc21b78bde7', 'Cabin King Waikato', 'waikato', 'cyan', 2),
       ('eafd3b39-46c7-41e4-ba4e-6ea6685e39f7', 'Cabin King BoP', 'bop', 'purple', 3);

CREATE TABLE IF NOT EXISTS `users`
(
    `id`         int(11)      NOT NULL AUTO_INCREMENT,
    `user_id`    char(36)     NOT NULL,
    `first_name` varchar(25)  NOT NULL,
    `last_name`  varchar(25)  NOT NULL,
    `email`      varchar(100) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 2
  DEFAULT CHARSET = utf8;

INSERT INTO `users` (`id`, `user_id`, `first_name`, `last_name`, `email`)
VALUES (1, '87ff2891-a223-4f3e-8e65-cfbd5db3c717', 'Sarah', 'King', 'sarah@itamer.com');

CREATE TABLE IF NOT EXISTS `vehicles`
(
    `id`          int(11)     NOT NULL AUTO_INCREMENT,
    `numberplate` char(6)     NOT NULL,
    `status`      varchar(20) NOT NULL,
    `created`     timestamp   NOT NULL,
    `modified`    timestamp   NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

CREATE TABLE IF NOT EXISTS `vehicle_log`
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

CREATE TABLE IF NOT EXISTS `weeks`
(
    `week_number` int(11) NOT NULL,
    PRIMARY KEY (`week_number`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

INSERT INTO `weeks` (`week_number`)
VALUES (0),
       (1),
       (2),
       (3),
       (4),
       (5),
       (6),
       (7),
       (8),
       (9),
       (10),
       (11),
       (12),
       (13),
       (14),
       (15),
       (16);
