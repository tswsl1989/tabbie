CREATE TABLE `adjudicator` (
  `adjud_id` mediumint(9) NOT NULL auto_increment,
  `univ_id` mediumint(9) NOT NULL default '0',
  `adjud_name` varchar(100) NOT NULL default '',
  `ranking` mediumint(9) NOT NULL default '0',
  `active` enum('Y','N') NOT NULL default 'Y',
  `status` ENUM( 'normal', 'trainee', 'watcher', 'watched' ) NOT NULL default 'normal',
  `conflicts` varchar(100) default NULL,
  PRIMARY KEY  (`adjud_id`),
  UNIQUE KEY `adjud_name` (`adjud_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Adjudicator Table';

CREATE TABLE `configure_adjud_draw` (
  `param_name` varchar(100) NOT NULL default '',
  `param_value` double default NULL,
  PRIMARY KEY  (`param_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Adjudicator Draw Parameter Table';


INSERT INTO `configure_adjud_draw` VALUES 
	('university_conflict',10000),
	('team_conflict',10000),
	('chair_not_perfect',0),
	('chair_not_ciaran_perfect',1),
	('panel_steepness',0.1),
	('panel_strength_not_perfect',1),
	('panel_size_not_perfect',0),
	('panel_size_out_of_bounds',1000),
	('adjudicator_met_adjudicator',0),
	('adjudicator_met_team',0),
	('trainee_in_chair',300),
	('watcher_not_in_chair',0),
	('watched_not_watched',150),
	('lock',0),
	('draw_table_speed',0);
	
CREATE TABLE `highlight` (
  `lowerlimit` char(50) default NULL,
  `upperlimit` char(50) default NULL,
  `type` char(50) default NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `highlight` VALUES ('50','100','result');

CREATE TABLE `motions` (
  `round_no` smallint(6) NOT NULL default '0',
  `motion` text NOT NULL,
  PRIMARY KEY  (`round_no`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `speaker` (
  `speaker_id` mediumint(9) NOT NULL auto_increment,
  `team_id` mediumint(9) NOT NULL default '0',
  `speaker_name` varchar(100) NOT NULL default '',
  `speaker_esl` char(3) NOT NULL default 'N',
  PRIMARY KEY  (`speaker_id`),
  UNIQUE KEY `team_id` (`team_id`,`speaker_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Speaker Table';

CREATE TABLE `team` (
  `team_id` mediumint(9) NOT NULL auto_increment,
  `univ_id` mediumint(9) NOT NULL default '0',
  `team_code` varchar(20) NOT NULL default '',
  `esl` varchar(3) default NULL,
  `active` enum('N','Y') NOT NULL default 'N',
  `composite` enum('N','Y') NOT NULL default 'Y',
  PRIMARY KEY  (`team_id`),
  UNIQUE KEY `univ_id` (`univ_id`,`team_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Team Table';

CREATE TABLE `university` (
  `univ_id` mediumint(9) NOT NULL auto_increment,
  `univ_name` varchar(100) NOT NULL default '',
  `univ_code` varchar(20) NOT NULL default '',
  PRIMARY KEY  (`univ_id`),
  UNIQUE KEY `univ_code` (`univ_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='University Table';


CREATE TABLE `venue` (
  `venue_id` mediumint(9) NOT NULL auto_increment,
  `venue_name` varchar(50) NOT NULL default '',
  `venue_location` varchar(50) NOT NULL default '',
  `active` enum('Y','N') NOT NULL default 'Y',
  PRIMARY KEY  (`venue_id`),
  UNIQUE KEY `venue_name` (`venue_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Venue Table';

CREATE TABLE `strikes` (
  `adjud_id` int(11) NOT NULL,
  `team_id` int(11) default NULL,
  `univ_id` int(11) default NULL,
  `strike_id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`strike_id`),
  KEY `univ_id` (`univ_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Conflict Table';

