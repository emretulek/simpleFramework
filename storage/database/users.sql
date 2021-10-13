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

-- Dumping structure for table phpfw.users
CREATE TABLE IF NOT EXISTS `users` (
                                       `userID` int(10) unsigned NOT NULL AUTO_INCREMENT,
                                       `username` varchar(256) DEFAULT NULL,
                                       `email` varchar(256) NOT NULL,
                                       `password` varchar(256) NOT NULL,
                                       `roleID` int(11) unsigned DEFAULT NULL,
                                       `ip` varchar(50) DEFAULT NULL,
                                       `status` enum('0','1') NOT NULL DEFAULT '0',
                                       `name` varchar(256) DEFAULT NULL,
                                       `register_ip` varchar(50) DEFAULT NULL,
                                       `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
                                       `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                       `deleted_at` datetime DEFAULT NULL,
                                       `last_login` datetime DEFAULT CURRENT_TIMESTAMP,
                                       `rememberme` varchar(256) DEFAULT NULL,
                                       `activation_code` varchar(256) DEFAULT NULL,
                                       `session_id` varchar(64) DEFAULT NULL,
                                       PRIMARY KEY (`userID`),
                                       UNIQUE KEY `userEmail` (`email`),
                                       KEY `FK_users_user_groups` (`roleID`),
                                       CONSTRAINT `FK_users_role_role_id` FOREIGN KEY (`roleID`) REFERENCES `user_roles` (`roleID`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- Dumping data for table phpfw.users: ~1 rows (approximately)
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` (`userID`, `username`, `email`, `password`, `roleID`, `ip`, `status`, `name`, `register_ip`, `created_at`, `updated_at`, `deleted_at`, `last_login`, `rememberme`, `activation_code`, `session_id`) VALUES
(1, 'admin', 'admin@admin.com', '670b14728ad9902aecba32e22fa4f6bd', 1, NULL, '1', NULL, NULL, '2020-06-29 12:11:12', '2021-05-10 05:46:48', NULL, '2020-06-29 12:11:12', NULL, NULL, NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
