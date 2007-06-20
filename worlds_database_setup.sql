# phpMyAdmin MySQL-Dump
# version 2.3.3pl1
# http://www.phpmyadmin.net/ (download page)
#
# Host: localhost
# Generation Time: Nov 30, 2003 at 03:31 PM
# Server version: 3.23.54
# PHP Version: 4.3.1-dev
# Database : `worlds`
# --------------------------------------------------------

#
# Table structure for table `adjudicator`
#

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
# --------------------------------------------------------

#
# Table structure for table `motions`
#

CREATE TABLE motions (
  round_no smallint(6) NOT NULL default '0',
  motion text NOT NULL,
  PRIMARY KEY  (round_no)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `speaker`
#

CREATE TABLE speaker (
  speaker_id mediumint(9) NOT NULL auto_increment,
  team_id mediumint(9) NOT NULL default '0',
  speaker_name varchar(100) NOT NULL default '',
  PRIMARY KEY  (speaker_id),
  UNIQUE KEY team_id (team_id,speaker_name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `team`
#

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
# --------------------------------------------------------

#
# Table structure for table `university`
#

CREATE TABLE university (
  univ_id mediumint(9) NOT NULL auto_increment,
  univ_name varchar(100) NOT NULL default '',
  univ_code varchar(20) NOT NULL default '',
  PRIMARY KEY  (univ_id),
  UNIQUE KEY univ_code (univ_code)
) TYPE=MyISAM COMMENT='University Table';
# --------------------------------------------------------

#
# Table structure for table `venue`
#

CREATE TABLE venue (
  venue_id mediumint(9) NOT NULL auto_increment,
  venue_name varchar(50) NOT NULL default '',
  venue_location varchar(50) NOT NULL default '',
  active enum('Y','N') NOT NULL default 'Y',
  PRIMARY KEY  (venue_id),
  UNIQUE KEY venue_name (venue_name)
) TYPE=MyISAM;
# --------------------------------------------------------

#
# Table structure for table `highlight`
#

CREATE TABLE highlight (
  lowerlimit char(50),
  upperlimit char(50),
  type char(50)
) TYPE=MyISAM;