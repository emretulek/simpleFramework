CREATE TABLE IF NOT EXISTS `user_permissions` (
  `permID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `permName` varchar(32) NOT NULL,
  `permDesc` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`permID`),
  UNIQUE KEY `permName` (`permName`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
