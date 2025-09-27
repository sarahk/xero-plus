ALTER TABLE `xeroplus`.`contracts`
    CHANGE COLUMN `reference` `reference` VARCHAR(50) NULL DEFAULT NULL;
ALTER TABLE `xeroplus`.`contracts`
    ADD INDEX `schedule_unit` (`schedule_unit`);

CREATE INDEX idx_contracts_cabin_date_id
    ON contracts (cabin_id, delivery_date, contract_id);

CREATE INDEX idx_cabins_tenant_status_num
    ON cabins (xerotenant_id, status, cabinnumber);

CREATE OR REPLACE VIEW vcabin_contracts AS
SELECT ca.cabin_id,
       ca.cabinnumber,
       ca.status,
       ca.style,
       ca.paintinside,
       ca.xerotenant_id,
       ct.contract_id,
       ct.contact_id,
       ct.delivery_date,
       ct.pickup_date,
       ct.scheduled_pickup_date
FROM cabins AS ca
         LEFT JOIN contracts AS ct
                   ON ct.cabin_id = ca.cabin_id
                       AND ct.delivery_date = (SELECT MAX(c2.delivery_date)
                                               FROM contracts c2
                                               WHERE c2.cabin_id = ca.cabin_id
                                                 AND c2.delivery_date IS NOT NULL
                                                 AND c2.delivery_date <> '0000-00-00')
                       AND ct.contract_id = (SELECT MAX(c3.contract_id)
                                             FROM contracts c3
                                             WHERE c3.cabin_id = ca.cabin_id
                                               AND c3.delivery_date = ct.delivery_date);


ALTER TABLE `xeroplus`.`cabins`
    ADD COLUMN `status_old`    VARCHAR(20) NULL DEFAULT NULL AFTER `xerotenant_id`,
    ADD COLUMN `status_change` DATE        NULL AFTER `status_old`,
    ADD COLUMN `style_old`     VARCHAR(20) NULL DEFAULT NULL AFTER `status_change`,
    ADD COLUMN `style_change`  DATE        NULL DEFAULT NULL AFTER `style_old`;

ALTER TABLE `xeroplus`.`tasks`
    CHANGE COLUMN `id` `task_id` INT NOT NULL AUTO_INCREMENT,
    ADD COLUMN `assigned_to` INT NULL;

ALTER TABLE `xeroplus`.`tasks`
    CHANGE COLUMN `status` `status` VARCHAR(25) NOT NULL DEFAULT 'active';

ALTER TABLE `xeroplus`.`tasks`
    ADD COLUMN `scheduled_date` DATE NULL DEFAULT NULL AFTER `due_date`;

ALTER DATABASE xeroplus CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE tasks
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE tasks
    MODIFY status VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

SET NAMES utf8mb4 COLLATE utf8mb4_0900_ai_ci;
SET collation_connection = 'utf8mb4_0900_ai_ci';

-- Convert whole tables (updates all text columns)
ALTER TABLE tasks
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
ALTER TABLE tenancies
    CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE tasks
    MODIFY status VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
    MODIFY task_type VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
    MODIFY xerotenant_id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

ALTER TABLE tenancies
    MODIFY tenant_id CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci,
    MODIFY colour VARCHAR(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

