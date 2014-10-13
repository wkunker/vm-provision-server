-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: crawlManagerDB_testing
-- ------------------------------------------------------
-- Server version	5.1.73

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `clientSystemMgmtHistoryTBL`
--

create database if not exists crawlManagerDB;
use crawlManagerDB;

DROP TABLE IF EXISTS `clientSystemMgmtHistoryTBL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientSystemMgmtHistoryTBL` (
  `clientUUID` varchar(100) NOT NULL DEFAULT '',
  `VMUUID` varchar(100) NOT NULL DEFAULT '',
  `macAddressID` varchar(100) NOT NULL DEFAULT '',
  `action` int(10) unsigned DEFAULT '0',
  `status` int(10) unsigned DEFAULT '0',
  `enable` int(10) unsigned DEFAULT '0',
  `valid` int(10) unsigned DEFAULT '0',
  `active` int(10) unsigned DEFAULT '0',
  `clientSystemUUID` varchar(100) NOT NULL DEFAULT '',
  `Date` int(10) unsigned DEFAULT '0',
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `ID` (`ID`),
  KEY `clientSystemMgmtHistoryTBL_clientUUID` (`clientUUID`),
  KEY `clientSystemMgmtHistoryTBL_VMUUID` (`VMUUID`),
  KEY `clientSystemMgmtHistoryTBL_macAddressID` (`macAddressID`),
  KEY `clientSystemMgmtHistoryTBL_clientSystemUUID` (`clientSystemUUID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientSystemMgmtHistoryTBL`
--

LOCK TABLES `clientSystemMgmtHistoryTBL` WRITE;
/*!40000 ALTER TABLE `clientSystemMgmtHistoryTBL` DISABLE KEYS */;
/*!40000 ALTER TABLE `clientSystemMgmtHistoryTBL` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientSystemMgmtTBL`
--

DROP TABLE IF EXISTS `clientSystemMgmtTBL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientSystemMgmtTBL` (
  `clientUUID` varchar(100) NOT NULL DEFAULT '',
  `VMUUID` varchar(100) NOT NULL DEFAULT '',
  `macAddressID` varchar(100) NOT NULL DEFAULT '',
  `action` int(10) unsigned DEFAULT '0',
  `status` int(10) unsigned DEFAULT '0',
  `enable` int(10) unsigned DEFAULT '0',
  `valid` int(10) unsigned DEFAULT '0',
  `active` int(10) unsigned DEFAULT '0',
  `masterTunnelPort` varchar(10) NOT NULL DEFAULT '0',
  `clientSystemUUID` varchar(100) NOT NULL DEFAULT '',
  `Date` int(10) unsigned DEFAULT '0',
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  `pubkey` varchar(10000) NOT NULL DEFAULT '',
  UNIQUE KEY `ID` (`ID`),
  KEY `clientSystemMgmtTBL_clientUUID` (`clientUUID`),
  KEY `clientSystemMgmtTBL_VMUUID` (`VMUUID`),
  KEY `clientSystemMgmtTBL_macAddressID` (`macAddressID`),
  KEY `clientSystemMgmtTBL_clientSystemUUID` (`clientSystemUUID`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clientSystemSetupHistoryTBL`
--

DROP TABLE IF EXISTS `clientSystemSetupHistoryTBL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientSystemSetupHistoryTBL` (
  `clientSystemName` varchar(100) NOT NULL DEFAULT '',
  `Owner` varchar(100) NOT NULL DEFAULT '',
  `Email` varchar(100) NOT NULL DEFAULT '',
  `Phone` varchar(100) NOT NULL DEFAULT '',
  `clientSystemBaseOS` varchar(100) NOT NULL DEFAULT '',
  `networkType` int(10) NOT NULL DEFAULT '0',
  `clientUUID` varchar(100) NOT NULL DEFAULT '',
  `Date` int(10) unsigned DEFAULT '0',
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `ID` (`ID`),
  KEY `clientSystemSetupHistoryTBL_clientUUID` (`clientUUID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `clientSystemSetupTBL`
--

DROP TABLE IF EXISTS `clientSystemSetupTBL`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientSystemSetupTBL` (
  `clientSystemName` varchar(100) NOT NULL DEFAULT '',
  `Owner` varchar(100) NOT NULL DEFAULT '',
  `Email` varchar(100) NOT NULL DEFAULT '',
  `Phone` varchar(100) NOT NULL DEFAULT '',
  `clientSystemBaseOS` varchar(100) NOT NULL DEFAULT '',
  `networkType` int(10) NOT NULL DEFAULT '0',
  `clientUUID` varchar(100) NOT NULL DEFAULT '',
  `ID` int(10) NOT NULL AUTO_INCREMENT,
  UNIQUE KEY `ID` (`ID`),
  KEY `clientSystemSetupTBL_clientUUID` (`clientUUID`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

