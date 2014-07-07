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


DROP PROCEDURE IF EXISTS procCreateMailboxViewString;
DELIMITER &&
CREATE PROCEDURE procCreateMailboxViewString(IN _dbname VARCHAR(255), IN _domain VARCHAR(255), INOUT _string LONGTEXT)
  BEGIN
    CALL procCreateUnionAllIfNotEmpty(_string);
    SET _string = CONCAT(_string,
                         'SELECT CONCAT(_ma.name,\'@\', _ma.domain) as user,_ma.name, _ma.domain, CONCAT(_ma.domain,\'/\',_ma.name,\'/\') AS mailDir FROM ',
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
                         'SELECT CONCAT(_ma.name,\'@\', _ma.domain) as user, _mb.password, _mb.name , CONCAT(_ma.domain,\'/\',_ma2.name,\'/\') AS mailDir FROM ',
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
CREATE PROCEDURE procCreateAliasNonGroupedViewString(IN _dbname VARCHAR(255), IN _domain VARCHAR(255), INOUT _string LONGTEXT)
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
                         _dbname, '\' AND _dal.active = 1 ');

  END&&
DELIMITER ;


DROP PROCEDURE IF EXISTS procCreateDomainAliasMailboxAndAliasView;
DELIMITER &&
CREATE PROCEDURE procCreateDomainAliasMailboxAndAliasView()
  BEGIN
    DROP VIEW IF EXISTS AliasView;
    CREATE VIEW AliasView AS
        SELECT user,name,domain, GROUP_CONCAT(target) AS target FROM AliasNonGroupedView GROUP BY user;

    DROP VIEW IF EXISTS DomainAliasMailboxView;
    CREATE VIEW DomainAliasMailboxView AS
      SELECT
        CONCAT(_m.name,'@', _da.alias_domain) as user,
        _m.name,
        _da.alias_domain                        AS domain,
        CONCAT(_m.name, '@', _da.target_domain) AS target
      FROM DomainAliasView AS _da, MailboxView AS _m
      WHERE _da.target_domain = _m.domain;

    DROP VIEW IF EXISTS DomainAliasAliasView;
    CREATE VIEW DomainAliasAliasView AS
      SELECT
        CONCAT(_a.name,'@', _da.alias_domain) as user,
        _a.name,
        _da.alias_domain AS domain,
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

    CALL procCreateDomainAliasMailboxAndAliasView();

  END &&
DELIMITER ;


CALL procCreateViews();


