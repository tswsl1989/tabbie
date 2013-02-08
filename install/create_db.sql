CREATE TABLE `adjudicator` (
  `adjud_id` mediumint(9) NOT NULL auto_increment,
  `univ_id` mediumint(9) NOT NULL DEFAULT '0',
  `adjud_name` varchar(100) NOT NULL DEFAULT '',
  `ranking` mediumint(9) NOT NULL DEFAULT '0',
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  `status` ENUM( 'normal', 'trainee', 'watcher', 'watched' ) NOT NULL DEFAULT 'normal',
  `conflicts` varchar(100) DEFAULT NULL,
  PRIMARY KEY  (`adjud_id`),
  UNIQUE KEY `adjud_name` (`adjud_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Adjudicator Table';

CREATE TABLE `settings` (
  `param_name` varchar(100) NOT NULL DEFAULT '',
  `param_value` double DEFAULT NULL,
  PRIMARY KEY  (`param_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Adjudicator Draw Parameter Table';


INSERT INTO `settings` VALUES 
	('adjudicator_met_adjudicator',0),
	('adjudicator_met_team',0),
	('chair_not_perfect',0),
	('chair_not_ciaran_perfect',1),
	('round',0),
	('lock',0),
	('panel_size_not_perfect',0),
	('panel_size_out_of_bounds',1000),
	('panel_steepness',0.1),
	('panel_strength_not_perfect',1),
	('team_conflict',10000),
	('trainee_in_chair',300),
	('university_conflict',10000),
	('watcher_not_in_chair',0),
	('watched_not_watched',150),
	('draw_table_speed',8),
	('highlight_lowerlimit', 50),
	('highlight_upperlimit', 90);
	
CREATE TABLE `motions` (
  `round_no` smallint(6) NOT NULL DEFAULT '0',
  `motion` text NOT NULL,
  `info_slide` enum('Y','N') NOT NULL DEFAULT 'N',
  `info` text NOT NULL,
  PRIMARY KEY  (`round_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `speaker` (
  `speaker_id` mediumint(9) NOT NULL auto_increment,
  `team_id` mediumint(9) NOT NULL DEFAULT '0',
  `speaker_name` varchar(100) NOT NULL DEFAULT '',
  `speaker_esl` char(3) NOT NULL DEFAULT 'N',
  PRIMARY KEY  (`speaker_id`),
  UNIQUE KEY `team_id` (`team_id`,`speaker_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Speaker Table';

CREATE TABLE `team` (
  `team_id` mediumint(9) NOT NULL auto_increment,
  `univ_id` mediumint(9) NOT NULL DEFAULT '0',
  `team_code` varchar(50) NOT NULL DEFAULT '',
  `esl` varchar(3) DEFAULT NULL,
  `active` enum('N','Y') NOT NULL DEFAULT 'N',
  `composite` enum('N','Y') NOT NULL DEFAULT 'Y',
  PRIMARY KEY  (`team_id`),
  UNIQUE KEY `univ_id` (`univ_id`,`team_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Team Table';

CREATE TABLE `university` (
  `univ_id` mediumint(9) NOT NULL auto_increment,
  `univ_name` varchar(100) NOT NULL DEFAULT '',
  `univ_code` varchar(20) NOT NULL DEFAULT '',
  PRIMARY KEY  (`univ_id`),
  UNIQUE KEY `univ_code` (`univ_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='University Table';


CREATE TABLE `venue` (
  `venue_id` mediumint(9) NOT NULL auto_increment,
  `venue_name` varchar(50) NOT NULL DEFAULT '',
  `venue_location` varchar(50) NOT NULL DEFAULT '',
  `active` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY  (`venue_id`),
  UNIQUE KEY `venue_name` (`venue_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Venue Table';

CREATE TABLE `strikes` (
  `adjud_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `univ_id` int(11) DEFAULT NULL,
  `strike_id` int(11) NOT NULL auto_increment,
  PRIMARY KEY  (`strike_id`),
  KEY `univ_id` (`univ_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Conflict Table';


CREATE TABLE draws (
	`round_no` MEDIUMINT(9) NOT NULL,
	`debate_id` MEDIUMINT(9) NOT NULL ,
	`og` MEDIUMINT(9) NOT NULL ,
	`oo` MEDIUMINT(9) NOT NULL ,
	`cg` MEDIUMINT(9) NOT NULL ,
	`co` MEDIUMINT(9) NOT NULL ,
	`venue_id` MEDIUMINT(9) NOT NULL ,
	PRIMARY KEY (debate_id, round_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Draws Table";

CREATE TABLE draw_adjud ( 
	`round_no` MEDIUMINT(9) NOT NULL,
	`debate_id` MEDIUMINT(9) NOT NULL,
	`adjud_id` MEDIUMINT(9) NOT NULL,
	`status` ENUM( 'chair', 'panelist', 'trainee' ) NOT NULL 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Adjudicator Allocations table";

CREATE TABLE `results` (
	`round_no` MEDIUMINT(9) NOT NULL,
	`debate_id` MEDIUMINT(9) NOT NULL DEFAULT '0',
	`first` MEDIUMINT(9) NOT NULL DEFAULT '0',
	`second` MEDIUMINT(9) NOT NULL DEFAULT '0',
	`third` MEDIUMINT(9) NOT NULL DEFAULT '0',
	`fourth` MEDIUMINT(9) NOT NULL DEFAULT '0',
	PRIMARY KEY  (`debate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Team results";

CREATE TABLE `speaker_results` (
	`round_no` MEDIUMINT(9) NOT NULL DEFAULT '0',
	`speaker_id` mediumint(9) NOT NULL DEFAULT '0', 
	`debate_id` mediumint(9) NOT NULL DEFAULT '0',
	`points` smallint(9) NOT NULL DEFAULT '0',
	PRIMARY KEY (`speaker_id`, `round_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT="Speaker results";
