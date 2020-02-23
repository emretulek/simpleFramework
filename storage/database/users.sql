CREATE TABLE `users` (
     `userID` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
     `userName` VARCHAR(256) NOT NULL,
     `userEmail` VARCHAR(256) NOT NULL,
     `userPassword` VARCHAR(256) NOT NULL,
     `userLevel` INT(3) UNSIGNED NOT NULL DEFAULT '1',
     `userIP` VARCHAR(50) NULL DEFAULT NULL,
     `activate` ENUM('0','1') NOT NULL DEFAULT '0',
     `nameSurname` VARCHAR(256) NOT NULL,
     `registerIP` VARCHAR(50) NULL DEFAULT NULL,
     `registerDate` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
     `lastLogin` DATETIME NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (`userID`),
     UNIQUE INDEX `userName` (`userName`),
     UNIQUE INDEX `userEmail` (`userEmail`)
)
COLLATE='utf8mb4_general_ci'
ENGINE=InnoDB
AUTO_INCREMENT=1;
