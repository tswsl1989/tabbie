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
    //for backwards compatability with Tabbie versions <= 1.3.1
    $result = mysql_query("SHOW TABLES LIKE 'configure_adjud_draw'");
    if (!mysql_num_rows($result))
        mysql_query("CREATE TABLE configure_adjud_draw ( 
  param_name varchar(100), 
  param_value double, 
  PRIMARY KEY (param_name) 
)");

    $values = array('university_conflict', 'team_conflict', 'chair_not_perfect', 'chair_not_ciaran_perfect', 'panel_steepness', 'panel_strength_not_perfect', 'panel_size_not_perfect', 'panel_size_out_of_bounds', 'adjudicator_met_adjudicator', 'adjudicator_met_team', 'lock');
    foreach ($values as $value) {
        $db_res = mysql_query("SELECT * FROM configure_adjud_draw WHERE param_name='$value'");
        if (!(mysql_fetch_assoc($db_res)))
            mysql_query("INSERT INTO configure_adjud_draw (param_name, param_value) VALUES ('$value', 0)");
    }
}

function get_scoring_factors_from_db() {
    ensure_scoring_factors_in_db();
    $params = array();
    $db_res = mysql_query("SELECT param_name, param_value FROM configure_adjud_draw");
    while ($row = mysql_fetch_assoc($db_res)) {
        $params[$row['param_name']] = $row['param_value'];
    }
    return $params;
}

function store_scoring_factors_to_db($params) {
    foreach ($params as $param => $pvalue) {
        mysql_query("UPDATE configure_adjud_draw SET param_value='$pvalue' WHERE param_name='$param'");
    }
}

$scoring_factors = get_scoring_factors_from_db();
//store_scoring_factors_to_db($scoring_factors);

?>
