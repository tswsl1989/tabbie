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

//team-level conflicts should be a possibility
//but uni-level is a good starting point

require_once("includes/backend.php");

function get_active_adjudicators() {
    $query = "SELECT adjud_name, adjud_id, univ_id, conflicts, ranking FROM adjudicator WHERE active='Y' ORDER BY adjud_id";
    $db_result = mysql_query($query);
    
    $result = array();
    while ($row = mysql_fetch_assoc($db_result)) {
        $adjudicator = array();
        $adjudicator['adjud_name'] = $row['adjud_name'];
        $adjudicator['adjud_id'] = $row['adjud_id'];
        $adjudicator['ranking'] = $row['ranking'];
        $adjudicator['univ_conflicts'][] = $row['univ_id'];
        $conflicts = preg_split("/,/", $row['conflicts'], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($conflicts as $conflict) {
            $univ_result = mysql_query("SELECT univ_id FROM university WHERE univ_code = '$conflict'");
            $univ = mysql_fetch_assoc($univ_result);
            //insert checks here...
            $adjudicator['univ_conflicts'][] = $univ['univ_id'];
        }
        $result[] = $adjudicator;
    }
    return $result;
}

function create_temp_adjudicator_table($round) {
    $tablename = "temp_adjud_round_$round";
    mysql_query("DROP TABLE $tablename");

    $query = "CREATE TABLE `$tablename` ( `debate_id` MEDIUMINT NOT NULL ,";
    $query .= " `adjud_id` MEDIUMINT NOT NULL ,";
    $query .= " `status` ENUM( 'chair', 'panelist', 'trainee' ) NOT NULL );";
    $db_result = mysql_query($query);
    if (!$db_result)
        return mysql_error();
}

function temp_debates_foobar($round) {
    $query =
        "SELECT debate_id, og, oo, cg, co, T1.univ_id AS univ_1, T2.univ_id AS univ_2, " . 
        "T3.univ_id AS univ_3, T4.univ_id AS univ_4 " .
        
        "FROM temp_draw_round_$round AS D, team AS T1, team AS T2, team AS T3, team AS T4 " .
        
        "WHERE D.og = T1.team_id AND D.oo = T2.team_id AND D.cg = T3.team_id AND D.co = T4.team_id " .
        "ORDER BY debate_id";
    $db_result = mysql_query($query);
    print mysql_error();
    $result = array();
    while ($row = mysql_fetch_assoc($db_result)) {
        $new_row = array();
        $new_row['debate_id'] = $row['debate_id'];
        $new_row['universities'] = array($row['univ_1'], $row['univ_2'], $row['univ_3'], $row['univ_4']);
        
        $new_row['points'] = 0;
        $new_row['teams'] = array();
        foreach (array('og', 'oo', 'cg', 'co') as $position) {
            $new_row['teams'][] = $row[$position];
            $new_row['points'] += points_for_team($row[$position], $round - 1);
        }
        $result[] = $new_row;
    }
    return $result;
}


?>
