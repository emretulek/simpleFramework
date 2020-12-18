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

-- Dumping structure for table phpfw.logger
CREATE TABLE IF NOT EXISTS `logger` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level` varchar(32) NOT NULL,
  `message` varchar(256) DEFAULT NULL,
  `data` text,
  `time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type` (`level`,`time`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- Dumping data for table phpfw.logger: ~4 rows (approximately)
/*!40000 ALTER TABLE `logger` DISABLE KEYS */;
INSERT INTO `logger` (`id`, `level`, `message`, `data`, `time`) VALUES
	(1, 'test', 'DEBUG', NULL, '2020-02-13 21:58:09'),
	(2, 'DEBUG', 'test', 'test', '2020-02-13 21:58:56'),
	(3, 'info', 'test messajı', '{"line":13}', '2020-12-15 20:17:06'),
	(4, 'info', 'test messajı', '{"line":13}', '2020-12-15 20:17:39');
/*!40000 ALTER TABLE `logger` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
