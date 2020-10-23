CREATE TABLE `users`
(
    `userID`         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `userName`       VARCHAR(256)     NOT NULL,
    `userEmail`      VARCHAR(256)     NOT NULL,
    `userPassword`   VARCHAR(256)     NOT NULL,
    `userGroup`      INT(11) UNSIGNED NULL     DEFAULT NULL,
    `userIP`         VARCHAR(50)      NULL     DEFAULT NULL,
    `status`         ENUM ('0','1')   NOT NULL DEFAULT '0',
    `nameSurname`    VARCHAR(256)     NOT NULL,
    `registerIP`     VARCHAR(50)      NULL     DEFAULT NULL,
    `created_at`     DATETIME         NULL     DEFAULT CURRENT_TIMESTAMP,
    `updated_at`     DATETIME         NULL     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `lastLogin`      DATETIME         NULL     DEFAULT CURRENT_TIMESTAMP,
    `rememberToken`  VARCHAR(256)     NULL     DEFAULT NULL,
    `activationCode` VARCHAR(256)     NULL     DEFAULT NULL,
    `sessionID`      VARCHAR(64)      NULL     DEFAULT NULL,
    PRIMARY KEY (`userID`),
    UNIQUE INDEX `userName` (`userName`),
    UNIQUE INDEX `userEmail` (`userEmail`),
    INDEX `FK_users_user_groups` (`userGroup`),
    CONSTRAINT `FK_users_user_groups` FOREIGN KEY (`userGroup`) REFERENCES `user_groups` (`groupID`) ON DELETE SET NULL
)
    COLLATE = 'utf8mb4_general_ci'
    ENGINE = InnoDB
    AUTO_INCREMENT = 2
;
