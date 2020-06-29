CREATE TABLE IF NOT EXISTS `user_groups` (
  `groupID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `groupName` varchar(32) NOT NULL,
  `groupDesc` varchar(32) DEFAULT NULL,
  PRIMARY KEY (`groupID`),
  UNIQUE KEY `groupName` (`groupName`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

INSERT INTO `user_groups` (`groupID`, `groupName`, `groupDesc`) VALUES
	(1, 'admin', NULL),
	(2, 'member', NULL);
