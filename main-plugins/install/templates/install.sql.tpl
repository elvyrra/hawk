/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE IF NOT EXISTS `{{ $dbname }}` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `{{ $dbname }}`;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}Language` (
  `tag` varchar(2) NOT NULL,
  `label` varchar(64) NOT NULL,
  `isDefault` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `{{ $prefix }}Language` DISABLE KEYS */;
INSERT IGNORE INTO `{{ $prefix }}Language` (`tag`, `label`, `isDefault`, `active`) VALUES
	('en', 'English', 1, 1),
	('fr', 'Français', 0, 1);
/*!40000 ALTER TABLE `{{ $prefix }}Language` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}MenuItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin` VARCHAR(32) NOT NULL DEFAULT '0',
  `name` varchar(64) NOT NULL,
  `parentId` int(11) NOT NULL DEFAULT 0,
  `labelKey` varchar(128) NOT NULL,
  `action` VARCHAR(128) NOT NULL,
  `actionParameters` VARCHAR(1024) NOT NULL,
  `target` VARCHAR(64) NOT NULL,
  `order` int(2) NOT NULL DEFAULT 0,
  `permissionId` INT(11) NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `Index 2` (`plugin`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `{{ $prefix }}MenuItem` DISABLE KEYS */;
INSERT IGNORE INTO `{{ $prefix }}MenuItem` (`id`, `plugin`, `name`, `parentId`, `labelKey`, `action`, `actionParameters`, `target`, `order`, `permissionId`, `active`) VALUES
(1, 'main', 'user', 0, 'user.username', '', '', '', 0, 0, 1),
(2, 'admin', 'admin', 0, 'main.menu-admin-title', '', '', '', 1, 0, 1),
(3, 'admin', 'settings', 2, 'main.menu-admin-settings-title', 'main-settings', '', '', 0, 1, 1),
(4, 'admin', 'users', 2, 'main.menu-admin-users-title', 'manage-users', '', '', 1, 2, 1),
(5, 'admin', 'permissions', 2, 'main.menu-admin-roles-title', 'permissions', '', '', 2, 2, 1),
(6, 'admin', 'themes', 2, 'main.menu-admin-display-title', 'manage-themes', '', '', 3, 3, 1),
(7, 'admin', 'plugins', 2, 'main.menu-admin-plugins-title', 'manage-plugins', '', '', 4, 1, 1),
(8, 'admin', 'translations', 2, 'main.menu-admin-language-title', 'manage-languages', '', '', 5, 5, 1),
(9, 'user', 'profile', 1, 'main.menu-my-profile', 'edit-profile', '', '', 0, 6, 1),
(10, 'user', 'change-password', 1, 'main.menu-change-password', 'change-password', '', 'dialog', 1, 6, 1),
(11, 'user', 'logout', 1, 'main.menu-logout', 'javascript: location = app.getUri(''logout'');', '', '', 2, 6, 1);
/*!40000 ALTER TABLE `{{ $prefix }}MenuItem` ENABLE KEYS */;



CREATE TABLE IF NOT EXISTS `{{ $prefix }}Option` (
  `plugin` varchar(32) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`plugin`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `{{ $prefix }}Option` DISABLE KEYS */;
INSERT IGNORE INTO `{{ $prefix }}Option` (`plugin`, `key`, `value`) VALUES
	('main', 'allow-guest', '0'),
	('main', 'language', '{{ $language }}'),
	('main', 'selected-theme', 'hawk'),
	('main', 'timezone', '{{ $timezone }}'),
	('main', 'title', {{ $title }}),
	('main', 'home-page-type', 'custom'),
	('roles', 'default-role', '1');
/*!40000 ALTER TABLE `{{ $prefix }}Option` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}Permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(32) NOT NULL,
  `key` varchar(64) NOT NULL,
  `availableForGuests` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugin` (`plugin`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `{{ $prefix }}Permission` DISABLE KEYS */;
INSERT IGNORE INTO `{{ $prefix }}Permission` (`id`, `plugin`, `key`, `availableForGuests`) VALUES
	(1, 'admin', 'all', 0),
	(2, 'admin', 'users', 0),
	(3, 'admin', 'themes', 0),
	(4, 'admin', 'plugins', 0),
	(5, 'admin', 'languages', 0),
  (6, 'main', 'user-actions', 0);
/*!40000 ALTER TABLE `{{ $prefix }}Permission` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}Plugin` (
  `name` varchar(32) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `removable` tinyint(1) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `{{ $prefix }}ProfileQuestion` (
  `name` varchar(32) NOT NULL,
  `type` varchar(16) NOT NULL,
  `parameters` text NOT NULL,
  `editable` tinyint(1) NOT NULL,
  `displayInRegister` tinyint(1) NOT NULL,
  `displayInProfile` tinyint(1) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `{{ $prefix }}ProfileQuestion` DISABLE KEYS */;
INSERT IGNORE INTO `{{ $prefix }}ProfileQuestion` (`name`, `type`, `parameters`, `editable`, `displayInRegister`, `displayInProfile`, `order`) VALUES
	('language', 'select', '{"options":[],"required":true}', 0, 0, 0, 0),
  ('avatar', 'file', '', 0, 1, 1, 2),
	('realname', 'text', '{"required" : true}', 0, 1, 1, 1);
/*!40000 ALTER TABLE `{{ $prefix }}ProfileQuestion` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}ProfileQuestionValue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(32) NOT NULL,
  `userId` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_2` (`question`,`userId`),
  KEY `question` (`question`),
  CONSTRAINT `ProfileQuestionValue_ibfk_1` FOREIGN KEY (`question`) REFERENCES `{{ $prefix }}ProfileQuestion` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}Role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `removable` tinyint(1) NOT NULL DEFAULT '1',
  `color` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `{{ $prefix }}Role` DISABLE KEYS */;
INSERT IGNORE INTO `{{ $prefix }}Role` (`id`, `name`, `removable`, `color`) VALUES
	(0, 'guest', 0, '#000'),
	(1, 'admin', 0, '#080');
/*!40000 ALTER TABLE `{{ $prefix }}Role` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}RolePermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleId` int(11) NOT NULL,
  `permissionId` int(11) NOT NULL,
  `value` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roleId_2` (`roleId`,`permissionId`),
  KEY `roleId` (`roleId`),
  KEY `permissionId` (`permissionId`),
  CONSTRAINT `RolePermission_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `{{ $prefix }}Role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RolePermission_ibfk_2` FOREIGN KEY (`permissionId`) REFERENCES `{{ $prefix }}Permission` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `{{ $prefix }}RolePermission` DISABLE KEYS */;
INSERT IGNORE INTO `{{ $prefix }}RolePermission` (`roleId`, `permissionId`, `value`) VALUES
	(1, 1, 1),
	(1, 2, 1),
	(1, 3, 1),
	(1, 4, 1),
	(1, 5, 1);
/*!40000 ALTER TABLE `{{ $prefix }}RolePermission` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}Session` (
  `id` varchar(64) NOT NULL,
  `data` mediumtext NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(512) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createTime` int(11) NOT NULL,
  `createIp` varchar(15) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `{{ $prefix }}User` DISABLE KEYS */;
INSERT IGNORE INTO `{{ $prefix }}User` (`id`, `email`, `username`, `password`, `active`, `createTime`, `createIp`, `roleId`) VALUES
	(0, '', 'guest', '', 0, 0, '', 0),
	(1, {{ $email }}, {{ $login }}, {{ $password }}, 1, UNIX_TIMESTAMP(), {{ $ip }}, 1);
/*!40000 ALTER TABLE `{{ $prefix }}User` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;


CREATE TABLE IF NOT EXISTS `{{ $prefix }}UserOption`(
  `userId`  INT(11) NOT NULL DEFAULT 0,
  `userIp` VARCHAR(15) NOT NULL DEFAULT '',
  `plugin` VARCHAR(32) NOT NULL,
  `key` VARCHAR(64) NOT NULL,
  `value` VARCHAR(4096),
  UNIQUE INDEX(`userId`, `plugin`, `key`),
  UNIQUE INDEX(`userIp`, `plugin`, `key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;