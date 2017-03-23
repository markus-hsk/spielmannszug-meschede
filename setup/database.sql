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
  `LASTNAME` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `FIRSTNAME` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `BIRTHNAME` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `GENDER` char(1) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL COMMENT 'm: maenlich, w: weiblich',
  `STREET` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `ZIP` varchar(10) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `CITY` varchar(50) CHARACTER SET latin1 COLLATE latin1_german2_ci NOT NULL,
  `BIRTHDATE` date NOT NULL DEFAULT '0000-00-00',
  `DEATHDATE` date DEFAULT NULL,
  `INSTRUMENT` varchar(25) NOT NULL,
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


/* Default-Daten: */
INSERT IGNORE INTO `spz_members` (`MEMBER_ID`, `LASTNAME`, `FIRSTNAME`, `BIRTHNAME`, `GENDER`, `STREET`, `ZIP`, `CITY`, `BIRTHDATE`, `DEATHDATE`, `INSTRUMENT`) VALUES
(1, 'Buscher', 'Markus', '', 'm', 'Von Westfalen-Str. 29', '59872', 'Meschede', '1985-11-19', NULL, 'Trommel');

INSERT IGNORE INTO `spz_contact_informations` (`CONTACT_ID`, `MEMBER_ID`, `CONTACT_TYPE`, `VALUE`) VALUES
(1, 1, 'email', 'markus@buscher.de'),
(2, 1, 'mobile', '0175/1515084'),
(3, 1, 'phone', '0291/14497586');

INSERT IGNORE INTO `spz_membership_states` (`MEMBERSHIP_ID`, `MEMBER_ID`, `STATE`, `START_DATE`, `END_DATE`) VALUES
(1, 1, 'aktiv', '1998-01-01', NULL);