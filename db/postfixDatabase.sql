-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Vært: localhost
-- Genereringstid: 04. 07 2014 kl. 16:36:29
-- Serverversion: 5.5.35
-- PHP-version: 5.5.14-1+deb.sury.org~precise+1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `postfix_production`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `address`
--

CREATE TABLE IF NOT EXISTS `address` (
  `name` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `id` varchar(255) NOT NULL,
  `alias_id` varchar(255) DEFAULT NULL,
  `mailbox_id` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  UNIQUE KEY `name` (`name`,`domain`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `alias_id` (`alias_id`),
  UNIQUE KEY `mailbox_id` (`mailbox_id`),
  KEY `address_ibfk_1` (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `alias`
--

CREATE TABLE IF NOT EXISTS `alias` (
  `target_id` varchar(255) DEFAULT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`),
  KEY `target_id` (`target_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Admin - Virtual Aliases';

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `alias_target`
--

CREATE TABLE IF NOT EXISTS `alias_target` (
  `alias_id` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  UNIQUE KEY `alias_id` (`alias_id`,`target`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `domain`
--

CREATE TABLE IF NOT EXISTS `domain` (
  `domain` varchar(255) NOT NULL,
  `description` varchar(255) CHARACTER SET utf8 NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Admin - Virtual Domains';

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `domain_alias`
--

CREATE TABLE IF NOT EXISTS `domain_alias` (
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
-- Struktur-dump for tabellen `mailbox`
--

CREATE TABLE IF NOT EXISTS `mailbox` (
  `password` varchar(255) NOT NULL,
  `name` varchar(255) CHARACTER SET utf8 NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `id` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Postfix Admin - Virtual Mailboxes';

--
-- Begrænsninger for dumpede tabeller
--

--
-- Begrænsninger for tabel `address`
--
ALTER TABLE `address`
ADD CONSTRAINT `address_ibfk_1` FOREIGN KEY (`domain`) REFERENCES `domain` (`domain`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `address_ibfk_2` FOREIGN KEY (`alias_id`) REFERENCES `alias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `address_ibfk_3` FOREIGN KEY (`mailbox_id`) REFERENCES `mailbox` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `alias`
--
ALTER TABLE `alias`
ADD CONSTRAINT `alias_ibfk_1` FOREIGN KEY (`target_id`) REFERENCES `address` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `alias_target`
--
ALTER TABLE `alias_target`
ADD CONSTRAINT `alias_target_ibfk_1` FOREIGN KEY (`alias_id`) REFERENCES `alias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Begrænsninger for tabel `domain_alias`
--
ALTER TABLE `domain_alias`
ADD CONSTRAINT `domain_alias_ibfk_1` FOREIGN KEY (`alias_domain`) REFERENCES `domain` (`domain`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT `domain_alias_ibfk_2` FOREIGN KEY (`target_domain`) REFERENCES `domain` (`domain`) ON DELETE CASCADE ON UPDATE CASCADE;
