-- phpMyAdmin SQL Dump
-- version 4.0.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 18, 2014 at 04:56 PM
-- Server version: 5.5.40-0ubuntu0.14.04.1
-- PHP Version: 5.5.18-1+deb.sury.org~trusty+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `postfix_test`
--

-- --------------------------------------------------------

--
-- Table structure for table `DomainAssignment`
--

CREATE TABLE IF NOT EXISTS `DomainAssignment` (
  `database` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  UNIQUE KEY `domain` (`domain`),
  UNIQUE KEY `database` (`database`,`domain`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP PROCEDURE IF EXISTS procPrepareAndCreateView;
DELIMITER &&
CREATE PROCEDURE procPrepareAndCreateView(IN _name VARCHAR(255), IN _string LONGTEXT)
  BEGIN
    SET @dropString = CONCAT('DROP VIEW IF EXISTS ', _name);
    PREPARE dropViewStmt FROM @dropString;
    EXECUTE dropViewStmt;
    DEALLOCATE PREPARE dropViewStmt;

    SET @string = CONCAT('CREATE VIEW ', _name, ' AS ', _string);
    PREPARE createViewStmt FROM @string;
    EXECUTE createViewStmt;
    DEALLOCATE PREPARE createViewStmt;
  END&&
DELIMITER ;

DROP PROCEDURE IF EXISTS procCreateUnionAllIfNotEmpty;
DELIMITER &&
CREATE PROCEDURE procCreateUnionAllIfNotEmpty(INOUT _string LONGTEXT)
  BEGIN
    IF _string != ''
    THEN
      SET _string = CONCAT(_string, ' UNION ALL ');
    END IF;
  END&&
DELIMITER ;


DROP PROCEDURE IF EXISTS procCreateUnionIfNotEmpty;
DELIMITER &&
CREATE PROCEDURE procCreateUnionIfNotEmpty(INOUT _string LONGTEXT)
  BEGIN
    IF _string != ''
    THEN
      SET _string = CONCAT(_string, ' UNION ');
    END IF;
  END&&
DELIMITER ;

DROP PROCEDURE IF EXISTS procCreateEmptyPseudoAlias;
DELIMITER &&
CREATE PROCEDURE procCreateEmptyPseudoAlias(IN _name VARCHAR(255))
  BEGIN
    CALL procPrepareAndCreateView(_name, 'SELECT domain AS user, domain AS name, domain, domain AS target FROM DomainAssignment WHERE 1 = 2');
  END&&
DELIMITER ;


DROP PROCEDURE IF EXISTS procCreateEmptyPseudoDomainAlias;
DELIMITER &&
CREATE PROCEDURE procCreateEmptyPseudoDomainAlias(IN _name VARCHAR(255))
  BEGIN
    CALL procPrepareAndCreateView(_name, 'SELECT domain AS alias_domain, domain AS target_domain FROM DomainAssignment WHERE 1 = 2');
  END&&
DELIMITER ;

DROP PROCEDURE IF EXISTS procCreateEmptyPseudoMail;
DELIMITER &&
CREATE PROCEDURE procCreateEmptyPseudoMail(IN _name VARCHAR(255))
  BEGIN
    CALL procPrepareAndCreateView(_name, 'SELECT domain AS user, domain AS name, domain, domain AS mailDir FROM DomainAssignment WHERE 1 = 2');
  END&&
DELIMITER ;

DROP PROCEDURE IF EXISTS procCreateEmptyPseudoMailLogin;
DELIMITER &&
CREATE PROCEDURE procCreateEmptyPseudoMailLogin(IN _name VARCHAR(255))
  BEGIN
    CALL procPrepareAndCreateView(_name, 'SELECT domain AS user, domain AS password, domain AS name, domain AS mailDir FROM DomainAssignment WHERE 1 = 2');
  END&&
DELIMITER ;

DROP PROCEDURE IF EXISTS procCreateEmptyPseudoDomain;
DELIMITER &&
CREATE PROCEDURE procCreateEmptyPseudoDomain(IN _name VARCHAR(255))
  BEGIN
    CALL procPrepareAndCreateView(_name, 'SELECT domain FROM DomainAssignment WHERE 1 = 2');
  END&&
DELIMITER ;

DROP PROCEDURE IF EXISTS procCreateMailboxViewString;
DELIMITER &&
CREATE PROCEDURE procCreateMailboxViewString(IN _dbname VARCHAR(255), IN _domain VARCHAR(255), INOUT _string LONGTEXT)
  BEGIN
    CALL procCreateUnionAllIfNotEmpty(_string);
    SET _string = CONCAT(_string,
                         'SELECT CONCAT(_ma.name,\'@\', _ma.domain) as user,_ma.name, _ma.domain, LOWER(CONCAT(_ma.domain,\'/\',_ma.name,\'/\')) AS mailDir FROM ',
                         _dbname, '.MailMailbox AS _mb , ', _dbname,
                         '.MailAddress AS _ma WHERE _mb.primary_address_id = _ma.id AND _ma.domain = \'', _domain,
                         '\' AND _ma.active = 1');
  END&&
DELIMITER ;

DROP PROCEDURE IF EXISTS procCreateMailboxLoginViewString;
DELIMITER &&
CREATE PROCEDURE procCreateMailboxLoginViewString(IN _dbname VARCHAR(255), IN _domain VARCHAR(255),
  INOUT                                              _string LONGTEXT)
  BEGIN
    CALL procCreateUnionAllIfNotEmpty(_string);
    SET _string = CONCAT(_string,
                         'SELECT CONCAT(_ma.name,\'@\', _ma.domain) as user, _mb.password, _mb.name , LOWER(CONCAT(_ma.domain,\'/\',_ma2.name,\'/\')) AS mailDir FROM ',
                         _dbname, '.MailMailbox AS _mb , ', _dbname, '.MailAddress AS _ma,', _dbname,
                         '.MailAddress AS _ma2  WHERE (_mb.primary_address_id = _ma.id OR _mb.secondary_address_id = _ma.id ) AND _ma.domain = \'',
                         _domain, '\' AND _ma2.domain = \'', _domain,
                         '\' AND _mb.primary_address_id = _ma2.id AND _ma.active = 1 AND _ma2.active = 1');
  END&&
DELIMITER ;

DROP PROCEDURE IF EXISTS procCreateDomainViewString;
DELIMITER &&
CREATE PROCEDURE procCreateDomainViewString(IN _dbname VARCHAR(255), IN _domain VARCHAR(255), INOUT _string LONGTEXT)
  BEGIN
    CALL procCreateUnionAllIfNotEmpty(_string);
    SET _string = CONCAT(_string, 'SELECT domain FROM ', _dbname, '.MailDomain WHERE domain = \'', _domain,
                         '\' AND active = 1');
  END&&
DELIMITER ;


DROP PROCEDURE IF EXISTS procCreateAliasNonGroupedViewString;
DELIMITER &&
CREATE PROCEDURE procCreateAliasNonGroupedViewString(IN _dbname VARCHAR(255), IN _domain VARCHAR(255),
  INOUT                                                 _string LONGTEXT)
  BEGIN
    CALL procCreateUnionAllIfNotEmpty(_string);
    SET _string = CONCAT(_string,
                         'SELECT CONCAT(_ma2.name,\'@\', _ma2.domain) as user,_ma2.name, _ma2.domain , CONCAT(_ma1.name,\'@\', _ma1.domain) AS target FROM ',
                         _dbname, '.MailMailbox AS _mb , ', _dbname, '.MailAddress AS _ma1, ', _dbname,
                         '.MailAddress AS _ma2  WHERE _ma1.domain = \'', _domain, '\' AND _ma2.domain = \'',
                         _domain,
                         '\' AND _mb.primary_address_id = _ma1.id AND (_mb.secondary_address_id = _ma2.id OR _mb.primary_address_id = _ma2.id) AND _ma1.active = 1 AND _ma2.active = 1');
    CALL procCreateUnionAllIfNotEmpty(_string);
    SET _string = CONCAT(_string, 'SELECT CONCAT(name,\'@\', domain) as user, name , domain , target FROM ', _dbname,
                         '.MailAddress AS _mad , ', _dbname, '.MailAlias AS _mal WHERE _mad.domain = \'', _domain,
                         '\' AND _mad.id = _mal.address_id AND _mad.active = 1');

  END&&
DELIMITER ;


DROP PROCEDURE IF EXISTS procCreateDomainAliasViewString;
DELIMITER &&
CREATE PROCEDURE procCreateDomainAliasViewString(IN _dbname VARCHAR(255), INOUT _string LONGTEXT)
  BEGIN
    CALL procCreateUnionAllIfNotEmpty(_string);
    SET _string = CONCAT(_string,
                         'SELECT _dal.alias_domain, _dal.target_domain FROM ', _dbname,
                         '.MailDomainAlias AS _dal, DomainAssignment AS _das1, DomainAssignment AS _das2 WHERE _dal.alias_domain = _das1.domain AND _dal.target_domain = _das2.domain AND _das1.database = _das2.database AND _das1.database = \'',
                         _dbname, '\'');

  END&&
DELIMITER ;


DROP PROCEDURE IF EXISTS procCreateDomainAliasMailboxAndAliasView;
DELIMITER &&
CREATE PROCEDURE procCreateDomainAliasMailboxAndAliasView()
  BEGIN
    DROP VIEW IF EXISTS AliasView;
    CREATE VIEW AliasView AS
      SELECT
        user,
        name,
        domain,
        GROUP_CONCAT(target) AS target
      FROM AliasNonGroupedView
      GROUP BY user;

    DROP VIEW IF EXISTS DomainAliasMailboxView;
    CREATE VIEW DomainAliasMailboxView AS
      SELECT
        CONCAT(_m.name, '@', _da.alias_domain)  AS user,
        _m.name,
        _da.alias_domain                        AS domain,
        CONCAT(_m.name, '@', _da.target_domain) AS target
      FROM DomainAliasView AS _da, MailboxView AS _m
      WHERE _da.target_domain = _m.domain;

    DROP VIEW IF EXISTS DomainAliasAliasView;
    CREATE VIEW DomainAliasAliasView AS
      SELECT
        CONCAT(_a.name, '@', _da.alias_domain) AS user,
        _a.name,
        _da.alias_domain                       AS domain,
        _a.target
      FROM DomainAliasView AS _da, AliasView AS _a;


  END&&
DELIMITER ;


DROP PROCEDURE IF EXISTS procCreateViews;
DELIMITER &&
CREATE PROCEDURE procCreateViews()
  BEGIN
    DECLARE done BOOLEAN DEFAULT FALSE;
    DECLARE _dbname VARCHAR(255);
    DECLARE _domain VARCHAR(255);
    DECLARE _mailboxViewString LONGTEXT DEFAULT '';
    DECLARE _mailboxLoginViewString LONGTEXT DEFAULT '';
    DECLARE _domainViewString LONGTEXT DEFAULT '';
    DECLARE _aliasViewString LONGTEXT DEFAULT '';
    DECLARE _domainAliasViewString LONGTEXT DEFAULT '';
    DECLARE cur CURSOR FOR SELECT DISTINCT
                             `database`
                           FROM DomainAssignment;

    DECLARE cur2 CURSOR FOR SELECT
                              `database`,
                              domain
                            FROM DomainAssignment;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done := TRUE;

    IF (SELECT
          COUNT(domain)
        FROM DomainAssignment) = 0
    THEN
      CALL procCreateEmptyPseudoMail('MailboxView');
      CALL procCreateEmptyPseudoMailLogin('MailboxLoginView');
      CALL procCreateEmptyPseudoDomain('DomainView');
      CALL procCreateEmptyPseudoAlias('AliasNonGroupedView');
      CALL procCreateEmptyPseudoDomainAlias('DomainAliasView');

    ELSE


      OPEN cur;
      testLoop: LOOP
        FETCH cur
        INTO _dbname;
        IF done
        THEN
          LEAVE testLoop;
        END IF;
        CALL procCreateDomainAliasViewString(_dbname, _domainAliasViewString);
      END LOOP testLoop;

      CLOSE cur;

      SET done = FALSE;

      OPEN cur2;
      testLoop2: LOOP
        FETCH cur2
        INTO _dbname, _domain;
        IF done
        THEN
          LEAVE testLoop2;
        END IF;
        CALL procCreateMailboxViewString(_dbname, _domain, _mailboxViewString);
        CALL procCreateMailboxLoginViewString(_dbname, _domain, _mailboxLoginViewString);
        CALL procCreateDomainViewString(_dbname, _domain, _domainViewString);
        CALL procCreateAliasNonGroupedViewString(_dbname, _domain, _aliasViewString);

      END LOOP testLoop2;

      CLOSE cur2;

      CALL procPrepareAndCreateView('MailboxView', _mailboxViewString);
      CALL procPrepareAndCreateView('MailboxLoginView', _mailboxLoginViewString);
      CALL procPrepareAndCreateView('DomainView', _domainViewString);
      CALL procPrepareAndCreateView('AliasNonGroupedView', _aliasViewString);
      CALL procPrepareAndCreateView('DomainAliasView', _domainAliasViewString);

    END IF;
    CALL procCreateDomainAliasMailboxAndAliasView();

  END &&
DELIMITER ;


CALL procCreateViews();


