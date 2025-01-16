## mcombo
DROP TABLE IF EXISTS `mcombo`;
CREATE TABLE `mcombo` AS
SELECT *
FROM `vcombo`;
CREATE INDEX `idx_mcombo_1` ON `mcombo` (`contract_id`, `date`);
CREATE INDEX `idx_mcombo_2` ON `mcombo` (`xerotenant_id`);


## mold_debts
DROP TABLE IF EXISTS `mold_debts`;
CREATE TABLE `mold_debts` AS
SELECT *
FROM `vold_debts`;
CREATE INDEX `idx_mold_debts_1` ON `mold_debts` (`xerotenant_id`, `contact_id`);


## mcombo
DROP TABLE IF EXISTS `mbdmgmt`;
CREATE TABLE `mbdmgmt` AS
SELECT *
FROM `vbdmgmt`;
CREATE INDEX `idx_mbdmgmt_1` ON `mbdmgmt` (`xerotenant_id`);
CREATE INDEX `idx_mbdmgmt_2` ON `mbdmgmt` (`ckcontact_id`);
