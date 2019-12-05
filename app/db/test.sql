-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.5.53 - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win32
-- HeidiSQL 版本:                  8.0.0.4396
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出 test 的数据库结构
DROP DATABASE IF EXISTS `test`;
CREATE DATABASE IF NOT EXISTS `test` /*!40100 DEFAULT CHARACTER SET latin1 */;
USE `test`;


-- 导出  表 test.hs_demo 结构
DROP TABLE IF EXISTS `hs_demo`;
CREATE TABLE IF NOT EXISTS `hs_demo` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT '',
  `dateline` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- 正在导出表  test.hs_demo 的数据：~4 rows (大约)
/*!40000 ALTER TABLE `hs_demo` DISABLE KEYS */;
REPLACE INTO `hs_demo` (`id`, `title`, `dateline`) VALUES
	(1, 'hello', 0),
	(2, 'world', 0),
	(3, 'hello', 0),
	(4, 'tes1', 0);
/*!40000 ALTER TABLE `hs_demo` ENABLE KEYS */;


-- 导出  表 test.hs_demo_copy 结构
DROP TABLE IF EXISTS `hs_demo_copy`;
CREATE TABLE IF NOT EXISTS `hs_demo_copy` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) DEFAULT '',
  `demo_id` int(10) NOT NULL DEFAULT '0',
  `dateline` int(10) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- 正在导出表  test.hs_demo_copy 的数据：~4 rows (大约)
/*!40000 ALTER TABLE `hs_demo_copy` DISABLE KEYS */;
REPLACE INTO `hs_demo_copy` (`id`, `title`, `demo_id`, `dateline`) VALUES
	(1, 'hello', 0, 0),
	(2, 'world', 1, 0),
	(3, 'hello', 0, 0),
	(4, 'tes1', 2, 0);
/*!40000 ALTER TABLE `hs_demo_copy` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
