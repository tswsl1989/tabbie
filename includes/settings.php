<?php
/* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 * 
 *     This file is part of Tabbie
 * 
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * end license */

function ensure_settings_in_db() {
	$result = q("SHOW TABLES LIKE 'settings'");
	if (!$result->RecordCount()) {
	$result = q("SHOW TABLES LIKE 'configure_adjud_draw'");
		if (!$result->RecordCount()) {
			//for backwards compatability with Tabbie versions <= 1.3.1
			q("CREATE TABLE settings (param_name varchar(100), param_value double, PRIMARY KEY (param_name))");
			print "No settings or configure_adjud_draw table found. Creating settings table<br />";
		} else {
			q("RENAME TABLE configure_adjud_draw TO settings");
			print "Renaming configure_adjud_draw to settings<br />";
		}
	}
	$hl=q("SHOW TABLES LIKE 'highlight'");
	if ($hl->RecordCount()) {
		print "Highlight table found - migrating<br />";
		$r = q("SELECT * FROM highlight");
		if ($r->RecordCount() > 1) {
			print "Multiple highlights defined - will not migrate<br />";
		} else {
			$h = $r->FetchRow();
			if (qp("INSERT INTO settings VALUES ('highlight_lowerlimit',?) ('highlight_upperlimit', ?)", array($h['lowerlimit'], $h['upperlimit']))) {
				q("DROP TABLE highlight");
			}
		}
	}
	$values = array(
		'adjudicator_met_adjudicator' => 0,
		'adjudicator_met_team' => 0,
		'chair_not_ciaran_perfect' => 1,
		'chair_not_perfect' => 0,
		'draw_table_speed' => 8,
		'highlight_lowerlimit' => 50,
		'highlight_upperlimit' => 90,
		'lock' => 0,
		'panel_size_not_perfect' => 0,
		'panel_size_out_of_bounds' => 1000,
		'panel_steepness' => 0.1,
		'panel_strength_not_perfect' => 1,
		'round' => 0,
		'team_conflict' => 10000,
		'trainee_in_chair' => 300,
		'university_conflict' => 10000,
		'watched_not_watched' => 150,
		'watcher_not_in_chair' => 0,
		'eballots_enabled' => 1
	);

	$defapplied = 0;
	$defs = array();
	foreach ($values as $name => $value) {
		$db_res = qp("SELECT * FROM settings WHERE param_name=?", array($name));
		if (!($db_res->RecordCount() > 0)) {
			qp("INSERT INTO settings (param_name, param_value) VALUES (?, ?)", array($name, $value));
			$defapplied = 1;
			$defs[]=$name;
		}
	}
	if ($defapplied == 1) {
		print "Some settings missing - default values used for: ".implode(", ", $defs);
	}
}

function get_settings_from_db() {
    ensure_settings_in_db();
    $params = array();
    $db_res = q("SELECT param_name, param_value FROM settings");
    while ($row = $db_res->FetchRow()) {
        $params[$row['param_name']] = $row['param_value'];
    }
    return $params;
}

function get_setting($name) {
	$rs = qp("SELECT param_value FROM settings WHERE param_name=?", array($name));
	$r = $rs->FetchRow();
	return $r['param_value'];
}

function store_settings_to_db($params) {
	foreach ($params as $param => $pvalue) {
		qp("UPDATE settings SET param_value=? WHERE param_name=?", array($pvalue, $param));
	}
}

$scoring_factors = get_settings_from_db();
?>
