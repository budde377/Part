-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 22. 10 2013 kl. 16:05:21
-- Serverversion: 5.5.32
-- PHP-version: 5.5.5-1+debphp.org~precise+1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cms2012testdb`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `MultiSiteUserPrivilege`
--

CREATE TABLE IF NOT EXISTS `MultiSiteUserPrivilege` (
  `username` varchar(255) NOT NULL,
  `type` varchar(10) NOT NULL,
  `site` varchar(255) DEFAULT '',
  `page` varchar(255) DEFAULT '',
  UNIQUE KEY `username_2` (`username`,`type`,`site`,`page`),
  KEY `site` (`site`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `Page`
--

CREATE TABLE IF NOT EXISTS `Page` (
  `page_id` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  `alias` varchar(255) DEFAULT NULL,
  `last_modified` datetime NOT NULL,
  `hidden` tinyint(1) NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `PageContent`
--

CREATE TABLE IF NOT EXISTS `PageContent` (
  `id` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  `page_id` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  UNIQUE KEY `id` (`id`,`time`,`page_id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `PageOrder`
--

CREATE TABLE IF NOT EXISTS `PageOrder` (
  `page_id` varchar(255) NOT NULL,
  `order_no` int(11) NOT NULL,
  `parent_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`page_id`),
  UNIQUE KEY `order_no` (`order_no`,`parent_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `PageVariables`
--

CREATE TABLE IF NOT EXISTS `PageVariables` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `page_id` varchar(255) NOT NULL,
  UNIQUE KEY `key` (`key`,`page_id`),
  KEY `page_id` (`page_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `SiteContent`
--

CREATE TABLE IF NOT EXISTS `SiteContent` (
  `id` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  `content` longtext NOT NULL,
  UNIQUE KEY `id` (`id`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `Sites`
--

CREATE TABLE IF NOT EXISTS `Sites` (
  `title` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `db_host` varchar(255) DEFAULT NULL,
  `db_db` varchar(255) DEFAULT NULL,
  `db_user` varchar(255) DEFAULT NULL,
  `db_password` varchar(255) DEFAULT NULL,
  `ft_user` varchar(255) DEFAULT NULL,
  `ft_password` varchar(255) DEFAULT NULL,
  `ft_host` varchar(255) DEFAULT NULL,
  `ft_port` int(11) DEFAULT NULL,
  `ft_path` varchar(255) DEFAULT NULL,
  `ft_type` int(1) DEFAULT NULL,
  PRIMARY KEY (`title`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `SiteVariables`
--

CREATE TABLE IF NOT EXISTS `SiteVariables` (
  `key` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `User`
--

CREATE TABLE IF NOT EXISTS `User` (
  `username` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `lastLogin` datetime DEFAULT NULL,
  `id` varchar(23) NOT NULL,
  `parent` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`username`),
  UNIQUE KEY `uniqueID` (`id`),
  KEY `parent` (`parent`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `UserPrivileges`
--

CREATE TABLE IF NOT EXISTS `UserPrivileges` (
  `username` varchar(255) NOT NULL,
  `rootPrivileges` tinyint(1) NOT NULL DEFAULT '0',
  `sitePrivileges` tinyint(1) NOT NULL DEFAULT '0',
  `pageId` varchar(255) DEFAULT '',
  UNIQUE KEY `username` (`username`,`rootPrivileges`,`sitePrivileges`,`pageId`),
  KEY `pageId` (`pageId`),
  KEY `userid` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `UserVariables`
--

CREATE TABLE IF NOT EXISTS `UserVariables` (
  `key` varchar(255) NOT NULL,
  `val` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  UNIQUE KEY `key` (`key`,`username`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `MultiSiteUserPrivilege`
--
ALTER TABLE `MultiSiteUserPrivilege`
ADD CONSTRAINT `MultiSiteUserPrivilege_ibfk_1` FOREIGN KEY (`username`) REFERENCES `User` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `MultiSiteUserPrivilege_ibfk_2` FOREIGN KEY (`site`) REFERENCES `Sites` (`title`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `PageContent`
--
ALTER TABLE `PageContent`
ADD CONSTRAINT `PageContent_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `Page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `PageOrder`
--
ALTER TABLE `PageOrder`
ADD CONSTRAINT `PageOrder_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `Page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `PageOrder_ibfk_2` FOREIGN KEY (`parent_id`) REFERENCES `Page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `PageVariables`
--
ALTER TABLE `PageVariables`
ADD CONSTRAINT `PageVariables_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `Page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `User`
--
ALTER TABLE `User`
ADD CONSTRAINT `User_ibfk_4` FOREIGN KEY (`parent`) REFERENCES `User` (`id`);

--
-- Begrænsninger for tabel `UserPrivileges`
--
ALTER TABLE `UserPrivileges`
ADD CONSTRAINT `UserPrivileges_ibfk_2` FOREIGN KEY (`pageId`) REFERENCES `Page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `UserPrivileges_ibfk_3` FOREIGN KEY (`username`) REFERENCES `User` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `UserVariables`
--
ALTER TABLE `UserVariables`
ADD CONSTRAINT `UserVariables_ibfk_1` FOREIGN KEY (`username`) REFERENCES `User` (`username`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;