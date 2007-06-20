DROP DATABASE tabbie;
CREATE DATABASE tabbie;

CREATE TABLE adjudicator (
  adjud_id mediumint(9) NOT NULL auto_increment,
  univ_id mediumint(9) NOT NULL default '0',
  adjud_name varchar(100) NOT NULL default '',
  ranking mediumint(9) NOT NULL default '0',
  active enum('Y','N') NOT NULL default 'Y',
  conflicts varchar(100) default NULL,
  PRIMARY KEY  (adjud_id),
  UNIQUE KEY adjud_name (adjud_name)
) TYPE=MyISAM COMMENT='Adjudicator Table';

CREATE TABLE motions (
  round_no smallint(6) NOT NULL default '0',
  motion text NOT NULL,
  PRIMARY KEY  (round_no)
) TYPE=MyISAM;

CREATE TABLE speaker (
  speaker_id mediumint(9) NOT NULL auto_increment,
  team_id mediumint(9) NOT NULL default '0',
  speaker_name varchar(100) NOT NULL default '',
  PRIMARY KEY  (speaker_id),
  UNIQUE KEY team_id (team_id,speaker_name)
) TYPE=MyISAM;

CREATE TABLE team (
  team_id mediumint(9) NOT NULL auto_increment,
  univ_id mediumint(9) NOT NULL default '0',
  team_code char(2) NOT NULL default '',
  esl enum('N','Y') NOT NULL default 'N',
  active enum('N','Y') NOT NULL default 'N',
  composite enum('N','Y') NOT NULL default 'Y',
  PRIMARY KEY  (team_id),
  UNIQUE KEY univ_id (univ_id,team_code)
) TYPE=MyISAM COMMENT='Team Table';

CREATE TABLE university (
  univ_id mediumint(9) NOT NULL auto_increment,
  univ_name varchar(100) NOT NULL default '',
  univ_code varchar(20) NOT NULL default '',
  PRIMARY KEY  (univ_id),
  UNIQUE KEY univ_code (univ_code)
) TYPE=MyISAM COMMENT='University Table';

CREATE TABLE venue (
  venue_id mediumint(9) NOT NULL auto_increment,
  venue_name varchar(50) NOT NULL default '',
  venue_location varchar(50) NOT NULL default '',
  active enum('Y','N') NOT NULL default 'Y',
  PRIMARY KEY  (venue_id),
  UNIQUE KEY venue_name (venue_name)
) TYPE=MyISAM;

CREATE TABLE highlight (
  lowerlimit char(50),
  upperlimit char(50),
  type char(50)
) TYPE=MyISAM;