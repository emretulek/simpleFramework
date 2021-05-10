-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               5.7.24 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             10.2.0.5599
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table phpfw.role_permissions
CREATE TABLE IF NOT EXISTS `role_permissions` (
                                                  `roleID` int(11) unsigned NOT NULL,
                                                  `permissionID` int(11) unsigned NOT NULL,
                                                  PRIMARY KEY (`roleID`,`permissionID`),
                                                  KEY `FK_user_group_perm_user_permissions` (`permissionID`),
                                                  CONSTRAINT `FK_role_permission_permission_id` FOREIGN KEY (`permissionID`) REFERENCES `permissions` (`permissionID`) ON DELETE CASCADE,
                                                  CONSTRAINT `FK_role_permission_role_id` FOREIGN KEY (`roleID`) REFERENCES `user_roles` (`roleID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Dumping data for table phpfw.role_permissions: ~2 rows (approximately)
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
INSERT INTO `role_permissions` (`roleID`, `permissionID`) VALUES
(1, 1),
(1, 2);
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
