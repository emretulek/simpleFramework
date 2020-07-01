CREATE TABLE IF NOT EXISTS `user_group_perm` (
  `groupID` int(11) unsigned NOT NULL,
  `permID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`groupID`,`permID`),
  KEY `FK_user_group_perm_user_permissions` (`permID`),
  CONSTRAINT `FK_user_group_perm_user_groups` FOREIGN KEY (`groupID`) REFERENCES `user_groups` (`groupID`) ON DELETE CASCADE,
  CONSTRAINT `FK_user_group_perm_user_permissions` FOREIGN KEY (`permID`) REFERENCES `user_permissions` (`permID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

