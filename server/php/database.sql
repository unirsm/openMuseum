# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.5.64-MariaDB)
# Database: giano
# Generation Time: 2019-09-29 19:05:34 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table bt_speaker
# ------------------------------------------------------------

CREATE TABLE `bt_speaker` (
  `id` int(3) NOT NULL AUTO_INCREMENT COMMENT 'id,noedit',
  `bt_mac` varchar(255) NOT NULL DEFAULT '' COMMENT 'Mac address',
  `bt_descrizione` varchar(255) NOT NULL DEFAULT '' COMMENT 'Description',
  `STATO` enum('Y','N') DEFAULT 'Y' COMMENT 'Attivo,[Si,No]',
  `immaColonna1` tinytext COMMENT 'Immagini colonna 1,file,{"width":"1200","height":"1200","mode":"fitw"}',
  `HISTORY` text COMMENT 'history,noedit',
  `DATA` datetime NOT NULL COMMENT 'data di creazione,noedit',
  `OWNER` varchar(15) NOT NULL DEFAULT '' COMMENT 'autore,noedit',
  `IP` varchar(15) DEFAULT NULL COMMENT 'IP,noedit',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table device
# ------------------------------------------------------------

CREATE TABLE `device` (
  `id` int(3) NOT NULL AUTO_INCREMENT COMMENT 'id,noedit',
  `ip_reader` varchar(16) NOT NULL DEFAULT '192.168.1.X' COMMENT 'Numero IP device',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT 'Description',
  `password` varchar(255) NOT NULL COMMENT 'password',
  `STATO` enum('Y','N') DEFAULT 'Y' COMMENT 'Attivo,[Si,No]',
  `HISTORY` text COMMENT 'history,noedit',
  `DATA` datetime NOT NULL COMMENT 'data di creazione,noedit',
  `OWNER` varchar(15) NOT NULL DEFAULT '' COMMENT 'autore,noedit',
  `IP` varchar(15) DEFAULT NULL COMMENT 'IP,noedit',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table multimedia
# ------------------------------------------------------------

CREATE TABLE `multimedia` (
  `id` int(3) NOT NULL AUTO_INCREMENT COMMENT 'id,noedit',
  `descrizione_media` varchar(255) NOT NULL DEFAULT '' COMMENT 'Description',
  `path_media` tinytext COMMENT 'percorso del file,link',
  `tipo_media` varchar(11) DEFAULT NULL COMMENT 'tipo,select,{"video":"video","audio":"audio","luce":"luce","mqtt":"mqtt sul server"}',
  `titolo_media` varchar(255) DEFAULT NULL COMMENT 'nome locale del file',
  `STATO` enum('Y','N') DEFAULT 'Y' COMMENT 'Attivo,[Si,No]',
  `HISTORY` text COMMENT 'history,noedit',
  `DATA` datetime NOT NULL COMMENT 'data di creazione,noedit',
  `OWNER` varchar(15) NOT NULL DEFAULT '' COMMENT 'autore,noedit',
  `IP` varchar(15) DEFAULT NULL COMMENT 'IP,noedit',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table musei
# ------------------------------------------------------------

CREATE TABLE `musei` (
  `id` int(3) NOT NULL AUTO_INCREMENT COMMENT 'id,noedit',
  `name` varchar(50) NOT NULL DEFAULT '' COMMENT 'name',
  `tagrfid` tinytext COMMENT 'tag RFID,table,{"tb":"tagrfid","value":"id","field1":"tag_uid","field2":"description","where":"STATO=''Y''","order": "tag_uid ASC"}',
  `neopixelspot` tinytext COMMENT 'Spot Led,table,{"tb":"neopixelspot","value":"id","field1":"description","where":"STATO=''Y''","order": "id ASC"}',
  `multimedia` tinytext COMMENT 'materiali multimediali,table,{"tb":"multimedia","value":"id","field1":"descrizione_media","field2":"tipo","where":"STATO=''Y''","order": "id ASC"}',
  `bt_speaker` tinytext COMMENT 'Dispositivi audio,table,{"tb":"bt_speaker","value":"id","field1":"bt_descrizione","where":"STATO=''Y''","order": "id ASC"}',
  `tradfri` tinytext COMMENT 'Lampada IKEA,table,{"tb":"tradfri","value":"id","field1":"bulbo_descrizione","where":"STATO=''Y''","order": "id ASC"}',
  `STATO` enum('Y','N') DEFAULT 'N' COMMENT 'State,[Si,No]',
  `HISTORY` text COMMENT 'history,noedit',
  `DATA` datetime NOT NULL COMMENT 'data di creazione,noedit',
  `OWNER` varchar(15) NOT NULL DEFAULT '' COMMENT 'autore,noedit',
  `IP` varchar(15) DEFAULT NULL COMMENT 'IP,noedit',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table neopixelspot
# ------------------------------------------------------------

CREATE TABLE `neopixelspot` (
  `id` int(16) NOT NULL AUTO_INCREMENT COMMENT 'id,noedit',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT 'description',
  `ip_indirizzo` varchar(255) NOT NULL DEFAULT '' COMMENT 'ip spot led',
  `colorRGBW` varchar(9) NOT NULL DEFAULT '' COMMENT 'colore Esadecimale #ff00000',
  `STATO` enum('Y','N') DEFAULT 'Y' COMMENT 'Attivo,[Si,No]',
  `HISTORY` text COMMENT 'history,noedit',
  `DATA` datetime NOT NULL COMMENT 'data di creazione,noedit',
  `OWNER` varchar(15) NOT NULL DEFAULT '' COMMENT 'autore,noedit',
  `IP` varchar(15) DEFAULT NULL COMMENT 'IP,noedit',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table tagrfid
# ------------------------------------------------------------

CREATE TABLE `tagrfid` (
  `id` int(3) NOT NULL AUTO_INCREMENT COMMENT 'id,noedit',
  `tag_uid` varchar(255) NOT NULL DEFAULT '' COMMENT 'TAG RTF id',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT 'Description',
  `immaColonna1` tinytext,
  `bt_speaker` varchar(255) DEFAULT '' COMMENT 'Dispositivi audio,table,{"tb":"bt_speaker","value":"id","field1":"bt_descrizione","where":"STATO=''Y''","order": "id ASC"}',
  `multimedia` varchar(255) DEFAULT '' COMMENT 'materiali multimediali,table,{"tb":"multimedia","value":"id","field1":"descrizione_media","field2":"tipo","where":"STATO=''Y''","order": "id ASC"}',
  `neopixelspot` varchar(255) DEFAULT '' COMMENT 'Spot Led,table,{"tb":"neopixelspot","value":"id","field1":"description","where":"STATO=''Y''","order": "id ASC"}',
  `tradfri` varchar(255) DEFAULT '' COMMENT 'Lampade IKEA,table,{"tb":"tradfri","value":"id","field1":"bulbo_descrizione","where":"STATO=''Y''","order": "id ASC"}',
  `STATO` enum('Y','N') DEFAULT 'Y' COMMENT 'Attivo,[Si,No]',
  `HISTORY` text COMMENT 'history,noedit',
  `DATA` datetime NOT NULL COMMENT 'data di creazione,noedit',
  `OWNER` varchar(15) NOT NULL DEFAULT '' COMMENT 'autore,noedit',
  `IP` varchar(15) DEFAULT NULL COMMENT 'IP,noedit',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table tradfri
# ------------------------------------------------------------

CREATE TABLE `tradfri` (
  `id` int(3) NOT NULL AUTO_INCREMENT COMMENT 'id,noedit',
  `ip_gateway` varchar(255) NOT NULL DEFAULT '' COMMENT 'ip gateway IKEA',
  `id_bulbo` varchar(255) NOT NULL DEFAULT '' COMMENT 'id bulbo lampada ',
  `bulbo_descrizione` tinytext COMMENT 'descrizione',
  `STATO` enum('Y','N') DEFAULT 'Y' COMMENT 'Attivo,[Si,No]',
  `HISTORY` text COMMENT 'history,noedit',
  `DATA` datetime NOT NULL COMMENT 'data di creazione,noedit',
  `OWNER` varchar(15) NOT NULL DEFAULT '' COMMENT 'autore,noedit',
  `IP` varchar(15) DEFAULT NULL COMMENT 'IP,noedit',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



# Dump of table users
# ------------------------------------------------------------

CREATE TABLE `users` (
  `id` int(4) NOT NULL AUTO_INCREMENT COMMENT 'id,noedit',
  `login` varchar(10) NOT NULL DEFAULT '' COMMENT 'login',
  `password` varchar(255) NOT NULL DEFAULT '' COMMENT 'password',
  `livello` enum('adm','editor','responsabile','tutor','ospite','user') NOT NULL DEFAULT 'editor' COMMENT 'Livello,select,{"adm":"amministratore","editor":"editor","responsabile":"responsabile","tutor":"tutor","ospite":"ospite","user":"utente"}',
  `nome` varchar(30) NOT NULL DEFAULT '' COMMENT 'nome',
  `cognome` varchar(30) NOT NULL DEFAULT '' COMMENT 'cognome',
  `ultimo` datetime DEFAULT NULL COMMENT 'ultimo accesso',
  `accessi` mediumint(5) unsigned NOT NULL DEFAULT '0' COMMENT 'numero di accessi',
  `attempt_accessi` smallint(4) DEFAULT '0' COMMENT 'tentativi sbagliati',
  `mail` varchar(255) NOT NULL DEFAULT '0' COMMENT 'e-mail',
  `gruppo` varchar(255) DEFAULT NULL,
  `STATO` enum('Y','N') DEFAULT NULL COMMENT 'Attivo,[Si,No]',
  `DATA` datetime DEFAULT NULL COMMENT 'data di creazione',
  `OWNER` varchar(10) NOT NULL DEFAULT 'am' COMMENT 'autore,noedit',
  `IP` varchar(15) DEFAULT NULL COMMENT 'IP,noedit',
  `HISTORY` text COMMENT 'history,noedit',
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='dati utenti';


INSERT INTO `users` (`id`, `login`, `password`, `livello`, `nome`, `cognome`, `ultimo`, `accessi`, `attempt_accessi`, `mail`, `gruppo`, `STATO`, `DATA`, `OWNER`, `IP`, `HISTORY`)
VALUES
	(1, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'adm', 'administrator', 'root', '2019-09-28 22:09:16', 68, 0, '0', NULL, 'Y', '2019-02-21 00:24:14', 'admin', '5.170.73.152', '2019-02-21 00:24:14 admin 5.170.73.152');


/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
