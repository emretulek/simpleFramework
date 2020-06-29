CREATE TABLE IF NOT EXISTS `users` (
  `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userName` varchar(256) NOT NULL,
  `userEmail` varchar(256) NOT NULL,
  `userPassword` varchar(256) NOT NULL,
  `userGroup` int(11) unsigned DEFAULT NULL,
  `userIP` varchar(50) DEFAULT NULL,
  `activate` enum('0','1') NOT NULL DEFAULT '0',
  `nameSurname` varchar(256) NOT NULL,
  `registerIP` varchar(50) DEFAULT NULL,
  `registerDate` datetime DEFAULT CURRENT_TIMESTAMP,
  `lastLogin` datetime DEFAULT CURRENT_TIMESTAMP,
  `sessionID` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`userID`),
  UNIQUE KEY `userName` (`userName`),
  UNIQUE KEY `userEmail` (`userEmail`),
  KEY `FK_users_user_groups` (`userGroup`),
  CONSTRAINT `FK_users_user_groups` FOREIGN KEY (`userGroup`) REFERENCES `user_groups` (`groupID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

INSERT INTO `users` (`userID`, `userName`, `userEmail`, `userPassword`, `userGroup`, `userIP`, `activate`, `nameSurname`, `registerIP`, `registerDate`, `lastLogin`, `sessionID`) VALUES
	(1, 'admin', 'admin@admin.com', '', 1, NULL, '0', '', NULL, '2020-06-29 12:11:12', '2020-06-29 12:11:12', NULL);
