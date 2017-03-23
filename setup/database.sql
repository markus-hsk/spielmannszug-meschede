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