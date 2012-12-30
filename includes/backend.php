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

require_once("includes/dbconnection.php");
require_once("includes/db_tools.php");

function get_num_rounds() {
    $result = q("SELECT param_value FROM settings WHERE param_name='round'");
    return mysql_fetch_array($result)[0];
}

function has_temp_draw() {
    $result = q("SHOW TABLES LIKE 'temp_draw_round%'");
    return mysql_num_rows($result);
}

function get_num_completed_rounds() {
    $result = q("SELECT MAX(round_no) FROM (SELECT d.round_no, COUNT(d.debate_id) as dCount, COUNT(r.debate_id) as rCount FROM draws AS d, results AS r WHERE d.round_no = r.round_no GROUP BY d.round_no) AS C");
    $count=mysql_fetch_array($result)[0];
    if ($count === NULL) {
	    return 0;
    } else {
	    return $count;
    }
}

function has_temp_result() {
    $result = q("SHOW TABLES LIKE 'temp_result_round%'");
    return mysql_num_rows($result);
}

function __team_on_ranking($round, $team_id, $ranking) {
    $query = "SELECT $ranking FROM results WHERE round_no=$round AND $ranking = $team_id";
    return (mysql_num_rows(q($query)));
}

function __team_on_position($round, $team_id, $position) {
    $query = "SELECT $position FROM draws WHERE round_no=$round AND $position = '$team_id'";
    return (mysql_num_rows(q($query)));
}

function points_for_ranking($ranking) {
    if ($ranking == "first") return 3;
    if ($ranking == "second") return 2;
    if ($ranking == "third") return 1;
    return 0;
}

function ranking_for_team_in_round($team_id, $round) {
    $RANKINGS = array("first", "second", "third", "fourth");
    $result = "undefined";
    foreach ($RANKINGS as $RANKING) {
        if (__team_on_ranking($round, $team_id, $RANKING))
            return $RANKING;
    }
    return $result;
}

function points_for_team($team_id, $nr_of_rounds) {
    $result = 0;
    for ($i = 1; $i <= $nr_of_rounds; $i++)
        $result += points_for_ranking(ranking_for_team_in_round($team_id, $i));
    return $result;
}

function positions_for_team($team_id, $nr_of_rounds) {
    $POSITIONS = array('og', 'oo', 'cg', 'co');
    $result = array();
    foreach ($POSITIONS as $POSITION)
        $result[$POSITION] = 0;
    for ($i = 1; $i <= $nr_of_rounds; $i++) {
        foreach ($POSITIONS as $POSITION) {
            $result[$POSITION] += __team_on_position($i, $team_id, $POSITION) ? 1 : 0;
        }
    }
    return $result;
}

function get_teams_positions_points($nr_of_rounds) {
    $db_result = q("SELECT team_id FROM team WHERE active='Y' ORDER BY team_id");
    $teams = array();
    while ($team = mysql_fetch_assoc($db_result)) {
        $team['points'] = points_for_team($team['team_id'], $nr_of_rounds);
        $team['positions'] = positions_for_team($team['team_id'], $nr_of_rounds);
        $teams[] = $team;
    }
    return $teams;
}

function results_by_position($round) {
    $result = array();
    $POSITIONS = array('og', 'oo', 'cg', 'co');
    $RANKINGS = array("first", "second", "third", "fourth");
    foreach ($POSITIONS as $POSITION) {
        $result[$POSITION] = array();
        $current =& $result[$POSITION];
        $db_result = q("SELECT $POSITION FROM draw_round_$round");
        while ($row = mysql_fetch_array($db_result)) {
            $team_id = $row[0];
            foreach ($RANKINGS as $RANKING) {
                $team_on_ranking = __team_on_ranking($round, $team_id, $RANKING);
				if(isset($current[$RANKING])){ //avoid throwing error when [$RANKING] not yet index
					@$current[$RANKING] += $team_on_ranking;
				} else {
					@$current[$RANKING] = $team_on_ranking;
				}
                
                if ($team_on_ranking) {
					if(isset($current["total"])){ //avoid throwing error when ["total"] not yet index
						 @$current["total"] += points_for_ranking($RANKING);
					} else {
						 @$current["total"] = points_for_ranking($RANKING);
					}
                }
            }
        }
    }
    $total = 0;
    foreach ($POSITIONS as $POSITION) {
        $total += $result[$POSITION]["total"];
    }
    foreach ($POSITIONS as $POSITION) {
        $result[$POSITION]["percentage"] = sprintf("%001d", $result[$POSITION]["total"] / $total * 100) . "%";
        $result[$POSITION]["normalized"] = sprintf("%001d", $result[$POSITION]["total"] / $total * 400) . "%";
    }
    return $result;
}

function print_teams_css($teams) {
    echo "team_id\tpoints\tog\too\tcg\tco\n";
    foreach ($teams as $team) {
        echo $team["team_id"] . "\t" .
            $team["points"] . "\t" .
            $team["og"] . "\t" .
            $team["oo"] . "\t" .
            $team["cg"] . "\t" .
            $team["co"] . "\n";
    }
}

function get_adjudicators_venues($round) {
    $result["header"] = array("Adjudicator Name", "Venue", "Venue Location");
    if (!$round) {
        $result["data"] = array();
        return $result;
    }
    $query = "SELECT v.*, a.* FROM adjudicator AS a, drawd AS d, " .
                "venue AS v, draw_adjud AS adjud  " .
                "WHERE d.round_no=$round AND adjud.round_no=$round AND d.venue_id=v.venue_id AND adjud.debate_id = d.debate_id AND " .
                "a.adjud_id = adjud.adjud_id ORDER BY adjud_name";
    
    $query_result = mysql_query($query);
    $data = array();
    
    while ($row =mysql_fetch_assoc($query_result)) {
        $data[] = array($row["adjud_name"], $row["venue_name"], $row["venue_location"]);
    }
    $result["data"] = $data;
    return $result;
}

function get_teams_venues($round) {
    $result["header"] = array("Team Name", "Venue", "Venue Location", "Position");
    if (!$round) {
        $result["data"] = array();
        return $result;
    }

    $query = "SELECT v.venue_id AS venue_id, v.venue_location AS venue_location, v.venue_name AS venue_name, t.team_id AS team_id, t.team_code AS team_code, u.univ_code AS univ_code ";
        $query.="FROM team AS t, university AS u, draws AS d, venue AS v ";
        $query.="WHERE d.round_no=$round AND d.venue_id=v.venue_id AND (t.team_id=d.og OR t.team_id=d.oo OR t.team_id=d.cg OR t.team_id=d.co) AND t.univ_id=u.univ_id ";
        $query.="ORDER BY univ_code, team_code ";

    $query_result = mysql_query($query);

    $data = array();
    while ($row = mysql_fetch_assoc($query_result)) {
        // Find the Position
        $positions = array("og", "oo", "cg", "co");
        $pos_string = "";
        foreach($positions as $position) {
            $pos_string = $position;
            $pos_query = "SELECT $position FROM draws WHERE round_no=$round AND $position={$row['team_id']}";
            $pos_query_result = mysql_query($pos_query);
            if (mysql_num_rows($pos_query_result) > 0)
                break;
        }
        $data[] = array($row['univ_code'] . " " . $row['team_code'], $row["venue_name"], $row["venue_location"], strtoupper($pos_string));
    }
    $result["data"] = $data;
    return $result;
}

function get_motion_for_round($round) {
    $motion_query = "SELECT motion FROM motions WHERE round_no = $round ";
    $motion_result = mysql_query($motion_query);
    $motion_row = mysql_fetch_assoc($motion_result);
    return $motion_row['motion'];
}

function adjudicator_sheets($round) {
    // Get the motion for the round
    $motion_query = "SELECT motion FROM motions WHERE round_no = $round ";
    $motion_result = mysql_query($motion_query);
    $motion_row=mysql_fetch_assoc($motion_result);
    $motion = $motion_row['motion'];
    
    // Get the individual debate details
    $venue_query = "SELECT draw.debate_id AS debate_id, draw.og AS ogid, draw.oo AS ooid, draw.cg AS cgid, draw.co AS coid, draw.venue_id AS venue_id, venue.venue_name AS venue_name, venue.venue_location AS venue_location, oguniv.univ_code AS og_univ_code, ogteam.team_code AS og_team_code, oouniv.univ_code AS oo_univ_code, ooteam.team_code AS oo_team_code, cguniv.univ_code AS cg_univ_code, cgteam.team_code AS cg_team_code, couniv.univ_code AS co_univ_code, coteam.team_code AS co_team_code ";
    $venue_query .= "FROM draws AS draw, venue AS venue, university AS oguniv, team AS ogteam, university AS oouniv, team AS ooteam, university AS cguniv, team AS cgteam, university AS couniv, team AS coteam ";
    $venue_query .= "WHERE draw.round_no=$round AND draw.venue_id = venue.venue_id AND ogteam.team_id = draw.og AND oguniv.univ_id = ogteam.univ_id AND ooteam.team_id = draw.oo AND oouniv.univ_id = ooteam.univ_id AND cgteam.team_id = draw.cg AND cguniv.univ_id = cgteam.univ_id AND coteam.team_id = draw.co AND couniv.univ_id = coteam.univ_id ";
    $venue_query .= "ORDER BY venue_location, venue_name ";
    
    $venue_result = mysql_query($venue_query);
    
    $data = array();
    while ($venue_row=mysql_fetch_assoc($venue_result))
    {    $venue_location = $venue_row['venue_location'];
        $venue_name = $venue_row['venue_name'];
        $debate_id = $venue_row['debate_id'];    
        $ogid = $venue_row['ogid'];
        $ooid = $venue_row['ooid'];
        $cgid = $venue_row['cgid'];
        $coid = $venue_row['coid'];
        $og = $venue_row['og_univ_code'].' '.$venue_row['og_team_code'];
        $oo = $venue_row['oo_univ_code'].' '.$venue_row['oo_team_code'];
        $cg = $venue_row['cg_univ_code'].' '.$venue_row['cg_team_code'];
        $co = $venue_row['co_univ_code'].' '.$venue_row['co_team_code'];
        
        // Get Chair
        $chfadj_query = "SELECT adjud.adjud_name AS adjud_name FROM draw_adjud AS round, adjudicator AS adjud WHERE round.round_no=$round AND round.debate_id = $debate_id AND round.status = 'chair' AND adjud.adjud_id = round.adjud_id ";
        $chfadj_result = mysql_query($chfadj_query);
        $chfadj_row=mysql_fetch_assoc($chfadj_result);
        $chair = $chfadj_row['adjud_name'];

        // Get Panelists
        $pnladj_query = "SELECT adjud.adjud_name AS adjud_name FROM draw_adjud AS round, adjudicator AS adjud WHERE round.round_no=$round AND round.debate_id = $debate_id AND round.status = 'panelist' AND adjud.adjud_id = round.adjud_id ";
        $pnladj_result = mysql_query($pnladj_query);
		$panel="";
        while ($pnladj_row=mysql_fetch_assoc($pnladj_result))
		{
			if($panel=="")
			{
				$panel=$pnladj_row['adjud_name'];
			} else {
				$panel = $panel.", ".$pnladj_row['adjud_name'];
			}
		}
		if(strlen($panel)>114)
		{
			$panel=substr($panel,0,143);
		}

        
        // Get Speakers
        $ogspkr_query = "SELECT speaker_name FROM speaker WHERE team_id = $ogid ORDER BY speaker_name ";
        $ogspkr_result = mysql_query($ogspkr_query);
        $ogspkr_row = mysql_fetch_assoc($ogspkr_result);
        for ($i=0;$i<2;$i++)
        {     switch($i)
            {    case 0: $ogspkr1 = $ogspkr_row['speaker_name'];
                    break;
                case 1: $ogspkr2 = $ogspkr_row['speaker_name'];
                    break;
            }
            $ogspkr_row = mysql_fetch_assoc($ogspkr_result);
        }
        
        $oospkr_query = "SELECT speaker_name FROM speaker WHERE team_id = $ooid ORDER BY speaker_name ";
        $oospkr_result = mysql_query($oospkr_query);
        $oospkr_row = mysql_fetch_assoc($oospkr_result);
        for ($i=0;$i<2;$i++)
        {     switch($i)
            {    case 0: $oospkr1 = $oospkr_row['speaker_name'];
                    break;
                case 1: $oospkr2 = $oospkr_row['speaker_name'];
                    break;
            }
            $oospkr_row = mysql_fetch_assoc($oospkr_result);
        }
        
        $cgspkr_query = "SELECT speaker_name FROM speaker WHERE team_id = $cgid ORDER BY speaker_name ";
        $cgspkr_result = mysql_query($cgspkr_query);
        $cgspkr_row = mysql_fetch_assoc($cgspkr_result);
        for ($i=0;$i<2;$i++)
        {     switch($i)
            {    case 0: $cgspkr1 = $cgspkr_row['speaker_name'];
                    break;
                case 1: $cgspkr2 = $cgspkr_row['speaker_name'];
                    break;
            }
            $cgspkr_row = mysql_fetch_assoc($cgspkr_result);
        }

        $cospkr_query = "SELECT speaker_name FROM speaker WHERE team_id = $coid ORDER BY speaker_name ";
        $cospkr_result = mysql_query($cospkr_query);
        $cospkr_row = mysql_fetch_assoc($cospkr_result);
        for ($i=0;$i<2;$i++)
        {     switch($i)
            {    case 0: $cospkr1 = $cospkr_row['speaker_name'];
                    break;
                case 1: $cospkr2 = $cospkr_row['speaker_name'];
                    break;
            }
            $cospkr_row = mysql_fetch_assoc($cospkr_result);
        }
        @$panelist_2 = @$panelist_2 ? (", " . @$panelist_2) : "";
        @$panelist_3 = @$panelist_3 ? (", " . @$panelist_3) : "";

        $page = array(
            "chair" => $chair,
			"panel" => $panel,
			"round" => $round,
            "venue" => "$venue_name" . ($venue_location ? " at $venue_location" : ""),
            "motion" => $motion,
            "og" => $og,
            "oo" => $oo,
            "cg" => $cg,
            "co" => $co,
            "og1" => $ogspkr1,
            "og2" => $ogspkr2,
            "oo1" => $oospkr1,
            "oo2" => $oospkr2,
            "cg1" => $cgspkr1,
            "cg2" => $cgspkr2,
            "co1" => $cospkr1,
            "co2" => $cospkr2
            );
        $data[] = $page;
    }
    $result = array();
    $result["header"] = array("Chair", "Round", "Venue", "Motion", "Opening Government", "Opening Opposition", "Closing Government", "Closing Opposition", "Opening Government Speaker 1", "Opening Government Speaker 2", "Opening Opposition Speaker 1", "Opening Opposition Speaker 2", "Closing Government Speaker 1", "Closing Government Speaker 2", "Closing Opposition Speaker 1", "Closing Opposition Speaker 2");
    $result["data"] = $data;
    return $result;
}

function get_co_adjudicators_for_round($adjud_id, $round) {
    $result = array();
    $db_result = q("SELECT b.adjud_id FROM draw_adjud AS a, draw_adjud AS b WHERE a.round_no=$round AND b.round_no=$round AND  a.adjud_id = '$adjud_id' AND a.debate_id = b.debate_id AND NOT b.adjud_id = '$adjud_id'");
    while ($row = mysql_fetch_array($db_result))
        $result[] = $row[0];
    return $result;
}

function get_co_adjudicators($adjud_id) {
    $result = array();
    for ($i = 1; $i <= get_num_rounds(); $i++)
        $result = array_merge($result, get_co_adjudicators_for_round($adjud_id, $i));
    return $result;
}

function get_adjudicator_met_teams_for_round($adjud_id, $round) {
    $result = array();
    $db_result = q("SELECT draw.og, draw.oo, draw.cg, draw.co FROM draw_adjud AS a, draws AS draw WHERE a.round_no=$round AND draw.round_no=$round AND a.adjud_id = '$adjud_id' AND a.debate_id = draw.debate_id");
    while ($row = mysql_fetch_array($db_result)) {
        $result[] = $row[0];
        $result[] = $row[1];
        $result[] = $row[2];
        $result[] = $row[3];
    }
    return $result;
}

function get_adjudicator_met_teams($adjud_id) {
    $result = array();
    for ($i = 1; $i <= get_num_rounds(); $i++)
        $result = array_merge($result, get_adjudicator_met_teams_for_round($adjud_id, $i));
    return $result;
}

function team_name($team_id) {
    $db_result = q("SELECT university.univ_code, team.team_code FROM university, team WHERE team.team_id = '$team_id' AND team.univ_id = university.univ_id");
    $row = mysql_fetch_assoc($db_result);
    return $row['univ_code'] . " " . $row['team_code'];
}

function team_name_long($team_id) {
    $db_result = q("SELECT university.univ_code, team.team_code, university.univ_name FROM university, team WHERE team.team_id = '$team_id' AND team.univ_id = university.univ_id");
    $row = mysql_fetch_assoc($db_result);
    return "{$row['univ_code']} {$row['team_code']} ({$row['univ_name']})";
}

function team_name_long_table($team_id) {
    $db_result = q("SELECT university.univ_code, team.team_code, university.univ_name FROM university, team WHERE team.team_id = '$team_id' AND team.univ_id = university.univ_id");
    $row = mysql_fetch_assoc($db_result);
    return "{$row['univ_name']} {$row['team_code']}";
}

function team_code_long_table($team_id) {
    $db_result = q("SELECT university.univ_code, team.team_code, university.univ_name FROM university, team WHERE team.team_id = '$team_id' AND team.univ_id = university.univ_id");
    $row = mysql_fetch_assoc($db_result);
    return "{$row['univ_code']} {$row['team_code']}";
}

function venue_name($venue_id) {
	$db_result=q("SELECT venue_name FROM `venue` WHERE venue_id=$venue_id");
	$row = mysql_fetch_assoc($db_result);
	return $row['venue_name'];
}

function get_room_name_from_debate($debate_id, $round){
	$drawtable="draw_round_".$round;
	$query = "SELECT venue_name FROM venue INNER JOIN ".$drawtable." ON venue.venue_id = ".$drawtable.".venue_id WHERE debate_id = ".$debate_id;
	$return = mysql_fetch_assoc(mysql_query($query));
	return $return["venue_name"];
}

function adjudicator_name($adjudicator_id){
	$query="SELECT adjud_name FROM adjudicator WHERE adjudicator.adjud_id = $adjudicator_id";
	$result = mysql_query($query);
	$return = mysql_fetch_assoc($result);
	return $return["adjud_name"];
}

function get_university($univ_id) {
    return mysql_fetch_assoc(q("SELECT * FROM university WHERE univ_id = '$univ_id'"));
}

function delete_team($team_id) {
	//Check for whether debates have started
    $query="SELECT COUNT(round_no) FROM draws";
    $result=mysql_query($query);

    if (mysql_fetch_array($result)[0]!=0)
      $msg[]="Debates in progress. Cannot delete now.";
    else
      {    
    
        //Delete Stuff (From Both Speaker and Team
    
        $query1="DELETE FROM speaker WHERE team_id='$team_id'";
        $result1=mysql_query($query1);
    //Check for Error
        if (mysql_affected_rows()==0)
      $msg[]="There were problems deleting speakers: No such record.";
   
        $query2="DELETE FROM team WHERE team_id='$team_id'";
        $result2=mysql_query($query2);
        //Check for Error
        if (mysql_affected_rows()==0)
      $msg[]="There were problems deleting team: No such record.";
      }
	
}

function delete_adjud($adjud_id){
	//Check for whether debates have started
    $query="SELECT COUNT(round_no) FROM draws";
    $result=mysql_query($query);

    if (mysql_fetch_array($result)[0]!=0)
      $msg[]="Debates in progress. Cannot delete now.";
    else
      {
        //Delete Stuff
        $adjud_id=trim(@$_GET['adjud_id']);
        $query="DELETE FROM adjudicator WHERE adjud_id='$adjud_id'";
        $result=mysql_query($query);

        //Check for Error
        if (mysql_affected_rows()==0)
      $msg[]="There were problems deleting : No such record.";
      }
  
}

function convert_db_ssesl(){
	  //for backwards compatability with Tabbie versions <= 1.4.2
	  // add speaker_esl to speaker and import ESL data from teams
	    $result = mysql_query("SHOW COLUMNS FROM `speaker` LIKE 'speaker_esl'");
	    if (!mysql_num_rows($result))
			{
				mysql_query("ALTER TABLE  `speaker` ADD  `speaker_esl` CHAR( 3 ) NOT NULL DEFAULT  'N'");
				$query="SELECT speaker.speaker_id, team.esl, speaker.speaker_esl FROM  `speaker` INNER JOIN  `team` ON speaker.team_id = team.team_id";
				$result=mysql_query($query);
				while ($row = mysql_fetch_array($result)) 
				{
					if ($row[esl]=="Y") set_speaker_esl($row[speaker_id], "Y");
			    }
			}
		mysql_query("ALTER TABLE team CHANGE esl esl VARCHAR (3)");
		mysql_query("UPDATE team SET esl='ESL' WHERE esl = 'Y'");
}

function set_speaker_esl($speaker_id, $esl){
	mysql_query("UPDATE  `speaker` SET  `speaker_esl` =  '".$esl."' WHERE  `speaker`.`speaker_id` =".$speaker_id);
}

function makesafe($string){
	$string=trim($string);
	$string=str_replace("'", "’", $string);
	$string=str_replace(";", "⁏", $string);
	return $string;
}

function add_strike_judge_team($adjud_id, $team_id, $univ_id){
	$query="INSERT INTO strikes(adjud_id, team_id, univ_id) VALUES('$adjud_id','$team_id', '$univ_id')";
	mysql_query($query);
}

function add_strike_judge_univ($adjud_id, $univ_id){
	$query="INSERT INTO strikes(adjud_id, univ_id) VALUES('$adjud_id','$univ_id')";
	mysql_query($query);
}

function del_strike_judge_team($adjud_id, $team_id){
	//you'd better pass this function a valid strike or else: it's 1am.
	$query="SELECT * FROM strikes WHERE adjud_id='$adjud_id' AND team_id='$team_id'";
    $resultstrike=mysql_query($query);
    $row=mysql_fetch_assoc($resultstrike);
    $strike_id=$row['team_id'];
	$query="DELETE * FROM strikes where strike_id='$strike_id'";
}

function is_strike_judge_team($adjud_id, $team_id){
	//echo("is_strike_judge_team(".$adjud_id.",".$team_id.")");
	$query="SELECT * FROM strikes WHERE adjud_id='$adjud_id' AND team_id='$team_id'";
	$resultstrike=mysql_query($query);
	$rownum=mysql_num_rows($resultstrike);
	if ($rownum>0){
		//echo("struck by team");
		//echo("strike on university $team_id");
		return true;
	} else {
		if(is_strike_judge_univ($adjud_id, univ_id_from_team($team_id))){
			//echo("struck by univ");
			return true;
		} else {
			//echo("not struck");
			return false;
		}
	}
}

function univ_id_from_team($team_id){
	$query = "SELECT `univ_id` FROM `team` WHERE `team_id`='$team_id'";
	$result = mysql_query($query);
	$row = mysql_fetch_assoc($result);
	return $row['univ_id'];
}

function is_strike_judge_univ($adjud_id, $univ_id){
	$query="SELECT * FROM `strikes` WHERE `adjud_id`='$adjud_id' AND `univ_id`='$univ_id' AND `team_id` IS NULL";
	$resultstrike=mysql_query($query);
	$rownum=mysql_num_rows($resultstrike);
	if ($rownum>0){
		//echo("strike on university $univ_id");
		//echo $query;
		return true;
	} else {
		return false;
	}
}
function del_strike_id($strike_id){
	$query = "DELETE FROM `strikes` WHERE `strikes`.`strike_id` = $strike_id";
	mysql_query($query);
}
	
function get_team_id($univ_id, $team_code){
	$query="SELECT team_id FROM team WHERE univ_id='$univ_id' AND team_code='$team_code'";
	mysql_query($query);
	$resultteam=mysql_query($query);
    $row=mysql_fetch_assoc($resultteam);
    $team_id=$row['team_id'];
	return $team_id;
}

function strikes_to_conflict_list($adjud_id){
	$query="SELECT * FROM strikes WHERE adjud_id='$adjud_id'";
	$conflict_list="";
	$result=mysql_query($query);
	while($row=mysql_fetch_assoc($result)){
		$conflict_list=$conflict_list.team_name($row['team_id']).",";
	}
	return $conflict_list;
}

function mysql_to_xml($query, $baseelement){
	$result=mysql_query($query);
	$dom = new DomDocument('1.0', 'utf8'); 
	$top = $dom->createElement('collection');
	$top = $dom->appendChild($top);
	while($row = mysql_fetch_assoc($result)) {
		$element = $dom->createElement($baseelement);
		$element = $top->appendchild($element);
		foreach ($row as $key => $value) {
			$child = $dom->createElement($key, $value);
			$child = $element->appendChild($child);
		}
	}
	$xml=$dom->saveXML();
	return $xml;
}

function is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid){
	if ( (is_strike_judge_team($adjud_id, $ogid))|| (is_strike_judge_team($adjud_id, $ooid)) || (is_strike_judge_team($adjud_id, $cgid)) || (is_strike_judge_team($adjud_id, $coid)) ){
		return true;
	} else {
		return false;
	}
	
}

function print_conflicts($adjud_id=0, $negative="<b>None</b>"){
	$strikelist="";
	$strikequery="SELECT u.univ_code, t.team_code FROM strikes as s INNER JOIN university AS u on s.univ_id = u.univ_id LEFT JOIN team AS t on s.team_id = t.team_id WHERE s.adjud_id = $adjud_id";
	$strikeresult=mysql_query($strikequery);
	//echo mysql_error();
	while($strike=mysql_fetch_assoc($strikeresult)){
		$strikelist .= $strike['univ_code']." ".$strike['team_code'].", ";
	}
	//remove trailing comma
	if($strikelist){
		$strikelist=substr($strikelist,0,strlen($strikelist)-2);
	} else {
		$strikelist = $negative;
	}
	return $strikelist;
}

function finalise_temporary_draw($nextround)
{
	$query = "SELECT DISTINCT COUNT(*) AS numadjud ";
    $query .= "FROM temp_adjud_round_$nextround ";
    $query .= "WHERE STATUS = 'chair'";

    $resultnumadjud=mysql_query($query);
    $rownumadjud=mysql_fetch_assoc($resultnumadjud);
    $adjudcount=$rownumadjud['numadjud']; //count chair adjudicators

      
    $query = "SELECT COUNT(*) AS numdebates ";
    $query .= "FROM temp_draw_round_$nextround ";

    $resultnumdebates=mysql_query($query);
    $rownumdebates=mysql_fetch_assoc($resultnumdebates);
    $debatecount=$rownumdebates['numdebates'];

    if ($adjudcount!=$debatecount) //No. of debates and chair adjudicators dont match
      {
        $msg[]="ERROR! There are debates with no Chair Adjudicator Allocated";
		return 0;
      }
	 $query="SELECT `adjud_id`, `og`, `oo`, `cg`, `co` FROM `temp_adjud_round_$nextround` INNER JOIN `temp_draw_round_$nextround` ON `temp_adjud_round_$nextround`.`debate_id`=`temp_draw_round_$nextround`.`debate_id`";
	 $result=mysql_query($query);
	 if(!($result=mysql_query($query))){
		 $msg[]="ERROR - strike query failed to execute";
		return 0;
	 } else {
		while($row=mysql_fetch_assoc($result)){
			$ogid=$row['og'];
			$ooid=$row['oo'];
			$cgid=$row['cg'];
			$coid=$row['co'];
			$query="SELECT `univ_id` FROM `team` WHERE `team_id` = '$ogid' OR `team_id` = '$ooid' OR `team_id` = '$cgid' OR `team_id` = '$coid'";
			$univ_id_result=mysql_query($query);
			if(mysql_num_rows($univ_id_result)!=4){
				$msg[]="ERROR - more than four teams for id (!)";
				$msg[]="You appear to have a corrupted database. Consider restoring from a backup and check previous rounds' data carefully.";
				return 0;
			}
			$univ_ids=array();
			while($univ_id_row=mysql_fetch_assoc($univ_id_result)){
				$univ_ids[]=$univ_id_row['univ_id'];
			}
			$query="SELECT * FROM `strikes` WHERE `adjud_id` = '".$row['adjud_id']."' AND ( (`team_id` = '$ogid' OR `team_id` = '$ooid' OR `team_id` = '$cgid' OR `team_id` = '$coid' ) OR ((`univ_id` = '".$univ_ids[0]."' OR `univ_id` = '".$univ_ids[1]."' OR `univ_id` = '".$univ_ids[2]."' OR `univ_id` = '".$univ_ids[3]."') AND `team_id` IS NULL) )";
			echo("<br/>");
			if(!($strikeresult=mysql_query($query))){
			 	$msg[]="ERROR - strike query failed to execute";
				return 0;
		 	} else if(mysql_num_rows($strikeresult)>0){
				return 0;
			 $msg[]="ERROR - strike in draw";
			 $msg[]="Adjudicator ".$row["adjud_id"]." is conflicted from their debate. Either clear the conflict or reallocate the adjudicator to proceed.";

			}
		}
	}
	return 1;
}

?>
