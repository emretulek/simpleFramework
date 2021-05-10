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

-- Dumping structure for table phpfw.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
                                          `session_id` varchar(256) NOT NULL,
                                          `data` text,
                                          `userID` int(11) unsigned DEFAULT NULL,
                                          `ip` varchar(50) DEFAULT NULL,
                                          `referer` varchar(512) DEFAULT NULL,
                                          `user_agent` varchar(512) DEFAULT NULL,
                                          `expire` int(11) unsigned NOT NULL,
                                          PRIMARY KEY (`session_id`),
                                          KEY `FK_sessions_users` (`userID`),
                                          CONSTRAINT `FK_sessions_users` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data for table phpfw.sessions: ~0 rows (approximately)
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
