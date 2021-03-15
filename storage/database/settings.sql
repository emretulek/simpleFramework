CREATE TABLE `settings`
(
    `settingID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`      VARCHAR(64)      NOT NULL,
    `value`     VARCHAR(1024)    NULL DEFAULT NULL,
    PRIMARY KEY (`settingID`),
    INDEX `name` (`name`)
)
    COLLATE = 'utf8mb4_general_ci'
    ENGINE = InnoDB
    AUTO_INCREMENT = 1
