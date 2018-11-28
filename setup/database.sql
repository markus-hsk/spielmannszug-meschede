CREATE TABLE IF NOT EXISTS `spz_contact_informations` (
  `CONTACT_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `MEMBER_ID` int(10) unsigned NOT NULL,
  `CONTACT_TYPE` varchar(10) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `VALUE` varchar(80) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL COMMENT 'enthaelt telnummer, email, messanger, etc.',
  PRIMARY KEY (`CONTACT_ID`),
  UNIQUE KEY `TYPE` (`MEMBER_ID`,`CONTACT_TYPE`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spz_members` (
  `MEMBER_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `LASTNAME` varchar(50) NOT NULL,
  `FIRSTNAME` varchar(50) NOT NULL,
  `BIRTHNAME` varchar(50),
  `GENDER` char(1) COMMENT 'm: maenlich, w: weiblich',
  `STREET` varchar(50),
  `ZIP` varchar(10),
  `CITY` varchar(50),
  `BIRTHDATE` date DEFAULT '0000-00-00',
  `DEATHDATE` date DEFAULT NULL,
  `INSTRUMENT` varchar(25),
  `NOTES` text,
  `INSERT_TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UPDATE_TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`MEMBER_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `spz_membership_states` (
  `MEMBERSHIP_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `MEMBER_ID` int(10) unsigned NOT NULL,
  `STATE` varchar(25) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `START_DATE` date NOT NULL DEFAULT '0000-00-00',
  `END_DATE` date DEFAULT NULL,
  PRIMARY KEY (`MEMBERSHIP_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Diese Tabelle speichert die Statuswechsel eines Mitglieds';

CREATE TABLE IF NOT EXISTS `spz_events` (
  `EVENT_ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `NAME` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `INSERT_TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UPDATE_TS` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`EVENT_ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


/* Default-Daten: */
INSERT IGNORE INTO `spz_members` (`MEMBER_ID`, `LASTNAME`, `FIRSTNAME`, `BIRTHNAME`, `GENDER`, `STREET`, `ZIP`, `CITY`, `BIRTHDATE`, `DEATHDATE`, `INSTRUMENT`) VALUES
(1, 'Mustermann', 'Max', '', 'm', 'Straße 1', '12345', 'Irgendwo', '1980-01-01', NULL, 'Trommel');

INSERT IGNORE INTO `spz_contact_informations` (`CONTACT_ID`, `MEMBER_ID`, `CONTACT_TYPE`, `VALUE`) VALUES
(1, 1, 'email', 'max@zumbeispiel.de'),
(2, 1, 'mobile', '0175/1111111111'),
(3, 1, 'phone', '0815/11111111111');

INSERT IGNORE INTO `spz_membership_states` (`MEMBERSHIP_ID`, `MEMBER_ID`, `STATE`, `START_DATE`, `END_DATE`) VALUES
(1, 1, 'aktiv', '1998-01-01', NULL);