<?php /* begin license *
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

//Energy calculation:

function ensure_scoring_factors_in_db() {
    $result = mysql_query("SHOW TABLES LIKE 'settings'");
    if (!mysql_num_rows($result)) {
        $result = mysql_query("SHOW TABLES LIKE 'configure_adjud_draw'");
        if (!mysql_num_rows($result)) {
            //for backwards compatability with Tabbie versions <= 1.3.1
            mysql_query("CREATE TABLE settings (param_name varchar(100), param_value double, PRIMARY KEY (param_name))");
	    print "No settings or configure_adjud_draw table found. Creating settings table<br />";
	} else {
            mysql_query("RENAME TABLE configure_adjud_draw TO settings");
            print "Renaming configure_adjud_draw to settings<br />";
	}
    }
    if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'highlight'"))) {
	print "Highlight table found - migrating<br />";
	$r = mysql_query("SELECT * FROM highlight");
        if (mysql_num_rows($r) > 1) {
	    print "Multiple highlights defined - will not migrate<br />";
	} else {
            $h = mysql_fetch_assoc($r);
	    if (mysql_query("INSERT INTO settings VALUES ('highlight_lowerlimit',".$h['lowerlimit']."), ('highlight_upperlimit', ".$h['upperlimit'].")")) {
                mysql_query("DROP TABLE highlight");
	    }
	}
    }
    $values = array('adjudicator_met_adjudicator' => 0,
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
            'team_conflict' => 10000,
            'trainee_in_chair' => 300,
            'university_conflict' => 10000,
            'watcher_not_watched' => 150,
	    'watcher_not_in_chair' => 0);
    $defapplied = 0;
    foreach ($values as $name => $value) {
        $db_res = mysql_query("SELECT * FROM settings WHERE param_name='$name'");
        if (!(mysql_fetch_assoc($db_res))) {
            mysql_query("INSERT INTO settings (param_name, param_value) VALUES ('$name', $value)");
	    $defapplied = 1;
        }
    }
    if ($defapplied == 1) {
	    print "Some settings missing - default values used";
    }
}

function get_scoring_factors_from_db() {
    ensure_scoring_factors_in_db();
    $params = array();
    $db_res = mysql_query("SELECT param_name, param_value FROM settings");
    while ($row = mysql_fetch_assoc($db_res)) {
        $params[$row['param_name']] = $row['param_value'];
    }
    return $params;
}

function store_scoring_factors_to_db($params) {
    foreach ($params as $param => $pvalue) {
        mysql_query("UPDATE settings SET param_value='$pvalue' WHERE param_name='$param'");
    }
}

$scoring_factors = get_scoring_factors_from_db();
//store_scoring_factors_to_db($scoring_factors);

?>
