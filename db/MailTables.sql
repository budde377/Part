-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 06. 07 2014 kl. 14:04:02
-- Serverversion: 5.5.35
-- PHP-version: 5.5.14-1+deb.sury.org~precise+1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
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
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`alias_domain`),
  KEY `active` (`active`),
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
  KEY `primary_address_id` (`primary_address_id`),
  KEY `secondary_address_id` (`secondary_address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Admin - Virtual Mailboxes';

--
-- Triggers `MailMailbox`
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
  ADD CONSTRAINT `MailMailbox_ibfk_2` FOREIGN KEY (`secondary_address_id`) REFERENCES `MailAddress` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `MailMailbox_ibfk_1` FOREIGN KEY (`primary_address_id`) REFERENCES `MailAddress` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
