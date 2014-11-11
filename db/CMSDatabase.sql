-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 10. 11 2014 kl. 21:23:24
-- Serverversion: 5.5.38-0ubuntu0.14.04.1
-- PHP-version: 5.5.17-2+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `cms2012testdb`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `MailAddress`
--

CREATE TABLE IF NOT EXISTS `MailAddress` (
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `id` varchar(255) NOT NULL,
  `mailbox_id` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `name` (`name`,`domain`),
  UNIQUE KEY `id` (`id`),
  KEY `address_ibfk_1` (`domain`),
  KEY `mailbox_id` (`mailbox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `MailAddressUserOwnership`
--

CREATE TABLE IF NOT EXISTS `MailAddressUserOwnership` (
  `username` varchar(255) NOT NULL,
  `address_id` varchar(255) NOT NULL,
  UNIQUE KEY `username_2` (`username`,`address_id`),
  KEY `username` (`username`),
  KEY `address_id` (`address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `MailAlias`
--

CREATE TABLE IF NOT EXISTS `MailAlias` (
  `address_id` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  UNIQUE KEY `alias_id` (`address_id`,`target`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `MailDomain`
--

CREATE TABLE IF NOT EXISTS `MailDomain` (
  `domain` varchar(255) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Admin - Virtual Domains';

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `MailDomainAlias`
--

CREATE TABLE IF NOT EXISTS `MailDomainAlias` (
  `alias_domain` varchar(255) NOT NULL,
  `target_domain` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`alias_domain`),
  KEY `target_domain` (`target_domain`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Admin - Domain Aliases';

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `MailMailbox`
--

CREATE TABLE IF NOT EXISTS `MailMailbox` (
  `primary_address_id` varchar(255) NOT NULL,
  `secondary_address_id` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `primary_address_id` (`primary_address_id`),
  UNIQUE KEY `secondary_address_id` (`secondary_address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Admin - Virtual Mailboxes';

--
-- Triggers/udløsere `MailMailbox`
--
DROP TRIGGER IF EXISTS `mailbox_insert`;
DELIMITER //
CREATE TRIGGER `mailbox_insert` BEFORE INSERT ON `MailMailbox`
FOR EACH ROW SET NEW.primary_address_id = IF(
    NEW.primary_address_id = NEW.secondary_address_id,
    NULL,
    NEW.primary_address_id)
//
DELIMITER ;
DROP TRIGGER IF EXISTS `mailbox_update`;
DELIMITER //
CREATE TRIGGER `mailbox_update` BEFORE UPDATE ON `MailMailbox`
FOR EACH ROW SET NEW.primary_address_id = IF(
    NEW.primary_address_id = NEW.secondary_address_id,
    NULL,
    NEW.primary_address_id)
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `OrderedList`
--

CREATE TABLE IF NOT EXISTS `OrderedList` (
  `list_id` varchar(255) NOT NULL,
  `element_id` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  UNIQUE KEY `list_id` (`list_id`,`order`),
  UNIQUE KEY `element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `OrderedListElement`
--

CREATE TABLE IF NOT EXISTS `OrderedListElement` (
  `element_id` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `val` longtext NOT NULL,
  UNIQUE KEY `element_id` (`element_id`,`key`)
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
  `value` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  UNIQUE KEY `key` (`key`,`username`),
  KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `MailAddress`
--
ALTER TABLE `MailAddress`
ADD CONSTRAINT `MailAddress_ibfk_2` FOREIGN KEY (`domain`) REFERENCES `MailDomain` (`domain`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `MailAddress_ibfk_3` FOREIGN KEY (`mailbox_id`) REFERENCES `MailMailbox` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `MailAddressUserOwnership`
--
ALTER TABLE `MailAddressUserOwnership`
ADD CONSTRAINT `MailAddressUserOwnership_ibfk_1` FOREIGN KEY (`username`) REFERENCES `User` (`username`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `MailAddressUserOwnership_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `MailAddress` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `MailAlias`
--
ALTER TABLE `MailAlias`
ADD CONSTRAINT `MailAlias_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `MailAddress` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `MailDomainAlias`
--
ALTER TABLE `MailDomainAlias`
ADD CONSTRAINT `MailDomainAlias_ibfk_1` FOREIGN KEY (`alias_domain`) REFERENCES `MailDomain` (`domain`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `MailDomainAlias_ibfk_2` FOREIGN KEY (`target_domain`) REFERENCES `MailDomain` (`domain`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `MailMailbox`
--
ALTER TABLE `MailMailbox`
ADD CONSTRAINT `MailMailbox_ibfk_1` FOREIGN KEY (`primary_address_id`) REFERENCES `MailAddress` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `MailMailbox_ibfk_2` FOREIGN KEY (`secondary_address_id`) REFERENCES `MailAddress` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `OrderedListElement`
--
ALTER TABLE `OrderedListElement`
ADD CONSTRAINT `OrderedListElement_ibfk_1` FOREIGN KEY (`element_id`) REFERENCES `OrderedList` (`element_id`) ON DELETE CASCADE ON UPDATE CASCADE;

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
