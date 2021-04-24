CREATE TABLE `sessions` (
    `session_id` VARCHAR(256) NOT NULL,
    `data` TEXT NULL,
    `userID` INT(11) UNSIGNED NULL DEFAULT NULL,
    `ip` VARCHAR(50) NULL DEFAULT NULL,
    `referer` VARCHAR(512) NULL DEFAULT NULL,
    `user_agent` VARCHAR(512) NULL DEFAULT NULL,
    `expire` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (`session_id`),
    INDEX `FK_sessions_users` (`userID`),
    CONSTRAINT `FK_sessions_users` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE SET NULL
)
    COLLATE='utf8mb4_general_ci'
    ENGINE=InnoDB
;
