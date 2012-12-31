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

require_once("includes/backend.php");

function __get_university_id_by_code($univ_code) {
    $db_result = mysql_query("SELECT univ_id FROM university WHERE univ_code = '$univ_code'");
    $row = mysql_fetch_assoc($db_result);
    return $row['univ_id'];
}

function __get_team_id_by_codes($univ_code, $team_code) {
    $univ_id = __get_university_id_by_code($univ_code);
    $db_result = mysql_query("SELECT team_id FROM team WHERE univ_id = '$univ_id' AND team_code = '$team_code'");
    $row = mysql_fetch_assoc($db_result);
    return $row['team_id'];
}

function get_adjudicator_by_id($adjud_id) {
    $query = "SELECT adjud_name, adjud_id, univ_id, ranking, status FROM adjudicator WHERE adjud_id='$adjud_id'";
    $db_result = mysql_query($query);
    $result = array();
    $row = mysql_fetch_assoc($db_result);
    $adjudicator = array();
    $adjudicator['adjud_name'] = $row['adjud_name'];
    $adjudicator['adjud_id'] = $row['adjud_id'];
    $adjudicator['ranking'] = $row['ranking'];
	$adjudicator['status'] = $row['status'];
	$adjudicator['team_conflicts'] = array();
	$adjudicator['univ_conflicts'] = array();
    //$adjudicator['univ_conflicts'][] = $row['univ_id']; - self-strike automatically added
	$query = "SELECT univ_id FROM strikes WHERE adjud_id=$adjud_id AND team_id IS NULL";
	$strikeresult=mysql_query($query);
	while($strike=mysql_fetch_assoc($strikeresult)) {
		$adjudicator['univ_conflicts'][]=$strike['univ_id'];
		}
	$query = "SELECT team_id FROM strikes WHERE adjud_id=$adjud_id AND team_id IS NOT NULL";
	$strikeresult=mysql_query($query);
	while($strike=mysql_fetch_assoc($strikeresult)) {$adjudicator['team_conflicts'][]=$strike['team_id'];}
	//echo("Adjudicator: ".$adjud_id."<br/>");
	//foreach($adjudicator['univ_conflicts'] as $uconflict){
	//	echo("Conflicted university ".$uconflict."<br/>");
	//}
	//foreach($adjudicator['team_conflicts'] as $tconflict){
	//	echo("Conflicted team ".$tconflict."<br/>");
	//}
	//echo("<br/><br/>");
    //$adjudicator['team_conflicts'] = array();
    //$conflicts = preg_split("/,/", $row['conflicts'], -1, PREG_SPLIT_NO_EMPTY);
    //foreach ($conflicts as $conflict) {
    //    $parts = split("[.]", $conflict);
    //    if (sizeof($parts) == 1)
    //        $adjudicator['univ_conflicts'][] = __get_university_id_by_code($conflict);
    //    elseif (sizeof($parts == 2)) {
    //            $adjudicator['team_conflicts'][] = __get_team_id_by_codes($parts[0], $parts[1]);
    //    }
    //}
    return $adjudicator;
}

function get_active_adjudicators($order_by='adjud_id') {
    $db_result = mysql_query("SELECT adjud_id FROM adjudicator WHERE active='Y' ORDER BY $order_by");
    $result = array();
    while ($row = mysql_fetch_assoc($db_result)) {
        $result[] = get_adjudicator_by_id($row['adjud_id']);
    }
    return $result;
}

function create_temp_adjudicator_table($round) {
    $tablename = "temp_adjud_round_$round";
    mysql_query("DROP TABLE $tablename");

    $query = "CREATE TABLE `$tablename` (";
	$query .= " `time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ,";
	$query .= " `debate_id` MEDIUMINT NOT NULL ,";
    $query .= " `adjud_id` MEDIUMINT NOT NULL ,";
    $query .= " `status` ENUM( 'chair', 'panelist', 'trainee' ) NOT NULL , UNIQUE KEY `adjud_id` (`adjud_id`) );";
    $db_result = mysql_query($query);
    if (!$db_result)
        return mysql_error();
}

function debates_from_temp_draw_no_adjudicators($round) {
    //join with debates_from_temp_draw_with_adjudicators
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

function debates_from_temp_draw_with_adjudicators($round) {
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
        $new_row['adjudicators'] = array();
        $adjudicators_db_result = mysql_query("SELECT adjud_id FROM temp_adjud_round_$round WHERE debate_id='{$row['debate_id']}'");
	while ($row2 = mysql_fetch_assoc($adjudicators_db_result)) {
            $new_row['adjudicators'][] = get_adjudicator_by_id($row2['adjud_id']);
        }
        $result[] = $new_row;
    }
    return $result;
}

function get_chair($round, $debate_id) {
	$adj_query = "SELECT DA.adjud_id AS adjud_id, AJ.adjud_name AS adjud_name FROM draw_adjud DA, adjudicator AJ WHERE DA.round_no = $round AND DA.debate_id = $debate_id AND DA.adjud_id = AJ.adjud_id AND DA.status = 'chair'";
	$adj_result=mysql_query($adj_query);
	if ($adj_result) {
		$adj_row=mysql_fetch_assoc($adj_result);
		return $adj_row['adjud_name'];
	} else {
		return FALSE;
	}
}

function get_panel($round, $debate_id) {
	$pan_query = "SELECT DA.adjud_id AS adjud_id, AJ.adjud_name AS adjud_name FROM draw_adjud AS DA, adjudicator AS AJ WHERE DA.round_no = $round AND debate_id = $debate_id AND DA.adjud_id = AJ.adjud_id AND DA.status = 'panelist' ";
	$pan_result = mysql_query($pan_query);
	$rv = array();
	if(mysql_num_rows($pan_result) > 0){
		while($pan_row=mysql_fetch_assoc($pan_result)) {
			$rv[]=$pan_row['adjud_name'];
		}
	}
	return $rv;
}

function get_trainees($round, $debate_id) {
	$trainee_query = "SELECT DA.adjud_id AS adjud_id, AJ.adjud_name AS adjud_name FROM draw_adjud AS DA, adjudicator AS AJ WHERE DA.round_no=$round AND debate_id = $debate_id AND DA.adjud_id = AJ.adjud_id AND DA.status = 'trainee' ";
	$trainee_result=mysql_query($trainee_query);

	$num_trainee=mysql_num_rows($trainee_result);
	$rv=array();
	if ($num_trainee > 0){
		while($trainee_row=mysql_fetch_assoc($trainee_result)) {    
			$rv[] = $trainee_row['adjud_name'];
		}
	}
	return $rv;
}
?>
