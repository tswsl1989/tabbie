-- Tabbie, Debating Tabbing Software
-- Copyright Contributors
-- 
-- This file is part of Tabbie
-- 
-- Tabbie is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
-- 
-- Tabbie is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
-- 
-- You should have received a copy of the GNU General Public License
-- along with Tabbie; if not, write to the Free Software
-- Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

CREATE TABLE tournaments (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    short_name VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
    UNIQUE KEY (short_name)
)   TYPE=MyISAM;

CREATE TABLE phases (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tournament_id MEDIUMINT UNSIGNED NOT NULL,
    seq MEDIUMINT UNSIGNED NOT NULL,
    name VARCHAR(100),
    PRIMARY KEY  (id)
) TYPE=MyISAM;

CREATE TABLE rounds (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    phase_id MEDIUMINT UNSIGNED NOT NULL,
    seq MEDIUMINT UNSIGNED NOT NULL,
    motion VARCHAR(2048) NOT NULL,
    PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE persons (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tournament_id MEDIUMINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    UNIQUE KEY (tournament_id, name),
    PRIMARY KEY (id),
) TYPE=MyISAM;

CREATE TABLE speakers (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    person_id MEDIUMINT UNSIGNED NOT NULL,
    team_id MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (person_id, team_id)
) TYPE=MyISAM;

CREATE TABLE teams (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    university_id MEDIUMINT UNSIGNED NOT NULL,
    short_name VARCHAR(20) NOT NULL,
    active BOOLEAN NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (university_id, short_name)
) TYPE=MyISAM;

CREATE TABLE adjudicators (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    university_id MEDIUMINT UNSIGNED NOT NULL,
    person_id MEDIUMINT UNSIGNED NOT NULL,
    points MEDIUMINT NOT NULL,
    active BOOLEAN NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (person_id)
) TYPE=MyISAM;

CREATE TABLE team_scratches (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    adjudicator_id MEDIUMINT UNSIGNED NOT NULL,
    team_id MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (adjudicator_id, team_id)
) TYPE=MyISAM;

CREATE TABLE university_scratches (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    adjudicator_id MEDIUMINT UNSIGNED NOT NULL,
    team_id MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (adjudicator_id, team_id)
) TYPE=MyISAM;

CREATE TABLE team_allocation (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    round_id MEDIUMINT UNSIGNED NOT NULL,
    team_id MEDIUMINT UNSIGNED NOT NULL,
    room_id MEDIUMINT UNSIGNED NOT NULL,
    team_role_id MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (round_id, team_id)
) TYPE=MyISAM;

CREATE TABLE judge_allocation (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    round_id MEDIUMINT UNSIGNED NOT NULL,
    adjudicator_id MEDIUMINT UNSIGNED NOT NULL,
    room_id MEDIUMINT UNSIGNED NOT NULL,
    adjudicator_role_id MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (round_id, adjudicator_id)
) TYPE=MyISAM;

CREATE TABLE team_points (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    team_id MEDIUMINT UNSIGNED NOT NULL,
    round_id MEDIUMINT UNSIGNED NOT NULL,
    points MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (round_id, team_id)
) TYPE=MyISAM;

CREATE TABLE speaker_points (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    speaker_id MEDIUMINT UNSIGNED NOT NULL,
    round_id MEDIUMINT UNSIGNED NOT NULL,
    points MEDIUMINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (round_id, speaker_id)
) TYPE=MyISAM;

CREATE TABLE team_roles (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE judge_roles (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE team_properties (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    team_id MEDIUMINT UNSIGNED NOT NULL,
    type_id MEDIUMINT UNSIGNED NOT NULL,
    value VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE team_property_types (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(20) NOT NULL,
    PRIMARY KEY (id)
) TYPE=MyISAM;

CREATE TABLE universities (
    id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(20) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY (short_name)
) TYPE=MyISAM;

CREATE TABLE rooms (
  id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  building_id MEDIUMINT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  active BOOLEAN NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (name)
) TYPE=MyISAM;

CREATE TABLE buildings (
  id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY (name)
) TYPE=MyISAM;

CREATE TABLE configuration (
  configuration_name VARCHAR(100),
  configuration_value DOUBLE,
  PRIMARY KEY (configuration_name)
) TYPE=MyISAM;

INSERT INTO configuration VALUES ('university_conflict', 10000);
INSERT INTO configuration VALUES ('team_conflict', 10000);
INSERT INTO configuration VALUES ('chair_not_perfect', 0);
INSERT INTO configuration VALUES ('chair_not_ciaran_perfect', 1);
INSERT INTO configuration VALUES ('panel_steepness', 0.1);
INSERT INTO configuration VALUES ('panel_strength_not_perfect', 1);
INSERT INTO configuration VALUES ('panel_size_not_perfect', 0);
INSERT INTO configuration VALUES ('panel_size_out_of_bounds', 1000);
INSERT INTO configuration VALUES ('adjudicator_met_adjudicator', 0);
INSERT INTO configuration VALUES ('adjudicator_met_team', 0);
INSERT INTO configuration VALUES ('lock', 0);

--- not for data, but for appliction:

CREATE TABLE IF NOT EXISTS  `ci_sessions` (
    session_id varchar(40) DEFAULT '0' NOT NULL,
    ip_address varchar(16) DEFAULT '0' NOT NULL,
    user_agent varchar(50) NOT NULL,
    last_activity int(10) unsigned DEFAULT 0 NOT NULL,
    PRIMARY KEY (session_id)
);
