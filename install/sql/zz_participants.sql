DROP TABLE IF EXISTS `zz_participants`;
CREATE TABLE `zz_participants` (
  `killID` int(32) NOT NULL,
  `solarSystemID` int(16) NOT NULL,
  `regionID` int(16) NOT NULL DEFAULT '0',
  `dttm` datetime NOT NULL,
  `total_price` decimal(16,2) NOT NULL DEFAULT '0.00',
  `points` mediumint(4) NOT NULL,
  `number_involved` smallint(4) NOT NULL,
  `isVictim` tinyint(1) NOT NULL,
  `shipTypeID` mediumint(8) unsigned NOT NULL,
  `groupID` mediumint(8) unsigned NOT NULL,
  `vGroupID` mediumint(8) unsigned NOT NULL,
  `weaponTypeID` mediumint(8) unsigned NOT NULL,
  `shipPrice` decimal(16,2) NOT NULL,
  `damage` int(8) NOT NULL,
  `factionID` int(16) NOT NULL,
  `allianceID` int(16) NOT NULL,
  `corporationID` int(16) NOT NULL,
  `characterID` int(16) NOT NULL,
  `finalBlow` tinyint(1) NOT NULL,
  `isNPC` int(1) NOT NULL DEFAULT '0',
  KEY `number_involved` (`number_involved`),
  KEY `shipTypeID_index` (`shipTypeID`),
  KEY `killID` (`killID`,`dttm`),
  KEY `killID_isVictim` (`killID`,`isVictim`),
  KEY `total_price_killID` (`killID`,`total_price`),
  KEY `dttm` (`dttm`),
  KEY `factionID` (`factionID`),
  KEY `allianceID` (`allianceID`),
  KEY `characterID` (`characterID`),
  KEY `corporationID` (`corporationID`),
  KEY `regionID` (`regionID`),
  KEY `solarSystemID` (`solarSystemID`),
  KEY `shipTypeID` (`shipTypeID`),
  KEY `groupID` (`groupID`),
  KEY `vGroupID` (`vGroupID`),
  KEY `weaponTypeID` (`weaponTypeID`),
  KEY `shipTypeID_dttm` (`shipTypeID`,`dttm`),
  KEY `solarSystemID_dttm` (`solarSystemID`,`dttm`),
  KEY `corporationID_dttm` (`corporationID`,`dttm`),
  KEY `allianceID_dttm` (`allianceID`,`dttm`),
  KEY `characterID_dttm` (`characterID`,`dttm`),
  KEY `isNPC` (`killID`,`isNPC`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPRESSED;
