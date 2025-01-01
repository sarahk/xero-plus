ALTER TABLE `xeroplus`.`contracts`
    CHANGE COLUMN `reference` `reference` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `xeroplus`.`contracts`
    ADD INDEX `schedule_unit` (`schedule_unit`);
