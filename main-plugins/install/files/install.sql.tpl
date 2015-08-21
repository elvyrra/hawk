/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

CREATE DATABASE IF NOT EXISTS `{{ $dbname }}` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `{{ $dbname }}`;


CREATE TABLE IF NOT EXISTS `Language` (
  `tag` varchar(2) NOT NULL,
  `label` varchar(64) NOT NULL,
  `isDefault` tinyint(1) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `Language` DISABLE KEYS */;
INSERT INTO `Language` (`tag`, `label`, `isDefault`, `active`) VALUES
	('en', 'English', 1, 1),
	('fr', 'Français', 0, 1);
/*!40000 ALTER TABLE `Language` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `Menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `labelKey` varchar(128) NOT NULL,
  `order` int(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `Menu` DISABLE KEYS */;
INSERT INTO `Menu` (`id`, `name`, `labelKey`, `order`) VALUES
(1, 'user', 'user.username', 1),
(2, 'admin', 'main.menu-admin-title', 0);
/*!40000 ALTER TABLE `Menu` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `MenuItem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menuId` int(11) NOT NULL,
  `name` varchar(64) NOT NULL,
  `labelKey` varchar(128) NOT NULL,
  `action` varchar(128) NOT NULL,
  `actionParameters` varchar(1024) NOT NULL DEFAULT '',
  `target` varchar(32) NOT NULL,
  `order` int(2) NOT NULL,
  `permissionId` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `menuId_2` (`menuId`,`name`),
  KEY `menuId` (`menuId`),
  CONSTRAINT `MenuItem_ibfk_1` FOREIGN KEY (`menuId`) REFERENCES `Menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `MenuItem` DISABLE KEYS */;
INSERT INTO `MenuItem` (`id`, `menuId`, `name`, `labelKey`, `action`, `actionParameters`, `target`, `order`, `permissionId`) VALUES
(1, 2, 'settings', 'main.menu-admin-settings-title', 'main-settings', '', '', 0, 1),
(2, 2, 'users', 'main.menu-admin-users-title', 'manage-users', '', '', 1, 2),
(3, 2, 'permissions', 'main.menu-admin-roles-title', 'permissions', '', '', 2, 2),
(4, 2, 'themes', 'main.menu-admin-display-title', 'manage-themes', '', '', 3, 3),
(5, 2, 'plugins', 'main.menu-admin-plugins-title', 'manage-plugins', '', '', 4, 1),
(6, 2, 'translations', 'main.menu-admin-language-title', 'manage-languages', '', '', 5, 5),
(7, 1, 'profile', 'main.menu-my-profile', 'edit-profile', '', 'dialog', 0, 0),
(8, 1, 'change-password', 'main.menu-change-password', 'change-password', '', 'dialog', 1, 0),
(9, 1, 'logout', 'main.menu-logout', 'javascript: location = app.getUri(''logout'');', '', '', 2, 0);
/*!40000 ALTER TABLE `MenuItem` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `Option` (
  `plugin` varchar(32) NOT NULL,
  `key` varchar(64) NOT NULL,
  `value` varchar(1024) DEFAULT NULL,
  PRIMARY KEY (`plugin`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `Option` DISABLE KEYS */;
INSERT INTO `Option` (`plugin`, `key`, `value`) VALUES
	('main', 'allow-guest', '0'),
	('main', 'language', '{{ $language }}'),
	('main', 'selected-theme', 'hawk'),
	('main', 'timezone', '{{ $timezone }}'),
	('main', 'title', {{ $title }}),
	('main', 'home-page-type', 'custom'),
	('roles', 'default-role', '1');
/*!40000 ALTER TABLE `Option` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `Permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin` varchar(32) NOT NULL,
  `key` varchar(64) NOT NULL,
  `availableForGuests` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugin` (`plugin`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `Permission` DISABLE KEYS */;
INSERT INTO `Permission` (`id`, `plugin`, `key`, `availableForGuests`) VALUES
	(1, 'admin', 'all', 0),
	(2, 'admin', 'users', 0),
	(3, 'admin', 'themes', 0),
	(4, 'admin', 'plugins', 0),
	(5, 'admin', 'languages', 0);
/*!40000 ALTER TABLE `Permission` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `Plugin` (
  `name` varchar(32) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `removable` tinyint(1) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `ProfileQuestion` (
  `name` varchar(32) NOT NULL,
  `type` varchar(16) NOT NULL,
  `parameters` text NOT NULL,
  `editable` tinyint(1) NOT NULL,
  `displayInRegister` tinyint(1) NOT NULL,
  `displayInProfile` tinyint(1) NOT NULL,
  `order` int(11) NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `ProfileQuestion` DISABLE KEYS */;
INSERT INTO `ProfileQuestion` (`name`, `type`, `parameters`, `editable`, `displayInRegister`, `displayInProfile`, `order`) VALUES
	('language', 'select', '{"options":[],"required":true}'),
  ('avatar', 'file', '', 0, 1, 1, 2),
	('realname', 'text', '{"required" : true}', 0, 1, 1, 1);
/*!40000 ALTER TABLE `ProfileQuestion` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `ProfileQuestionValue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(32) NOT NULL,
  `userId` int(11) NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `question_2` (`question`,`userId`),
  KEY `question` (`question`),
  CONSTRAINT `ProfileQuestionValue_ibfk_1` FOREIGN KEY (`question`) REFERENCES `ProfileQuestion` (`name`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `Role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `removable` tinyint(1) NOT NULL DEFAULT '1',
  `color` varchar(32) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `Role` DISABLE KEYS */;
INSERT INTO `Role` (`id`, `name`, `removable`, `color`) VALUES
	(0, 'guest', 0, '#000'),
	(1, 'admin', 0, '#080');
/*!40000 ALTER TABLE `Role` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `RolePermission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleId` int(11) NOT NULL,
  `permissionId` int(11) NOT NULL,
  `value` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roleId_2` (`roleId`,`permissionId`),
  KEY `roleId` (`roleId`),
  KEY `permissionId` (`permissionId`),
  CONSTRAINT `RolePermission_ibfk_1` FOREIGN KEY (`roleId`) REFERENCES `Role` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `RolePermission_ibfk_2` FOREIGN KEY (`permissionId`) REFERENCES `Permission` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `RolePermission` DISABLE KEYS */;
INSERT INTO `RolePermission` (`roleId`, `permissionId`, `value`) VALUES
	(1, 1, 1),
	(1, 2, 1),
	(1, 3, 1),
	(1, 4, 1),
	(1, 5, 1);
/*!40000 ALTER TABLE `RolePermission` ENABLE KEYS */;


CREATE TABLE IF NOT EXISTS `Session` (
  `id` varchar(64) NOT NULL,
  `data` mediumtext NOT NULL,
  `mtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `User` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(128) NOT NULL,
  `username` varchar(64) NOT NULL,
  `password` varchar(512) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `createTime` int(11) NOT NULL,
  `createIp` varchar(15) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40000 ALTER TABLE `User` DISABLE KEYS */;
INSERT INTO `User` (`id`, `email`, `username`, `password`, `active`, `createTime`, `createIp`, `roleId`) VALUES
	(0, '', 'guest', '', 0, 0, '', 0),
	(1, {{ $email }}, {{ $login }}, {{ $password }}, 1, UNIX_TIMESTAMP(), {{ $ip }}, 1);	
/*!40000 ALTER TABLE `User` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
