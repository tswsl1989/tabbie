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
require_once("includes/settings.php");

function get_num_rounds() {
	return get_setting("round");
}

function has_temp_draw() {
	$tdresult=q("SHOW TABLES LIKE 'temp_draw'");
	$taresult=q("SHOW TABLES LIKE 'temp_adjud'");

	return ($tdresult->RecordCount()==1 && $taresult->RecordCount()==1);
}

function get_num_completed_rounds() {
    $result = q("SELECT MAX(round_no) as MRN FROM (SELECT d.round_no, COUNT(d.debate_id) as dCount, COUNT(r.debate_id) as rCount FROM draws AS d, results AS r WHERE d.round_no = r.round_no GROUP BY d.round_no) AS C");
    $count=$result->FetchRow();
    if ($count['MRN'] === NULL) {
	    return 0;
    } else {
	    return $count['MRN'];
    }
}

function has_temp_result() {
    $result = q("SHOW TABLES LIKE 'temp_result'");
    return $result->RecordCount();
}

function __team_on_ranking($round, $team_id, $ranking) {
	/*
	 * Needs to be a better way than this. Including $ranking in params gets it quoted,
	 * which breaks as the final criteria always returns false
	 */
	    $query="SELECT * FROM results WHERE round_no=? AND $ranking=?";
	    $params=array($round, $team_id);
	    $rs = qp($query, $params);
	    return $rs->RecordCount();
}

function __team_on_position($round, $team_id, $position) {
    $query = "SELECT * FROM draws WHERE round_no=? AND $position=?";
    $params = array($round, $team_id);
    $rs = qp($query, $params);
    return $rs->RecordCount();
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
    while ($team = $db_result->FetchRow()) {
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
        $db_result = q("SELECT $POSITION FROM draws WHERE round_no=$round");
        while ($row = $db_result->FetchRow()) {
            $team_id = $row[$POSITION];
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
    $query = "SELECT v.*, a.* FROM adjudicator AS a, draws AS d, " .
                "venue AS v, draw_adjud AS adjud  " .
                "WHERE d.round_no=? AND adjud.round_no=? AND d.venue_id=v.venue_id AND adjud.debate_id = d.debate_id AND " .
                "a.adjud_id = adjud.adjud_id ORDER BY adjud_name";

    $query_result = qp($query,array($round, $round));
    $data = array();

    while ($row = $query_result->FetchRow()) {
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
        $query.="WHERE d.round_no=? AND d.venue_id=v.venue_id AND (t.team_id=d.og OR t.team_id=d.oo OR t.team_id=d.cg OR t.team_id=d.co) AND t.univ_id=u.univ_id ";
        $query.="ORDER BY univ_code, team_code ";

    $query_result = qp($query, array($round));

    $data = array();
    while ($row = $query_result->FetchRow()) {
        // Find the Position
        $positions = array("og", "oo", "cg", "co");
        $pos_string = "";
        foreach($positions as $position) {
            $pos_string = $position;
	    $pos_query = "SELECT * FROM draws WHERE round_no=? AND $position=?";
	    $pos_params = array($round, $row['team_id']);
            $pos_query_result = qp($pos_query, $pos_params);
            if ($pos_query_result->RecordCount() > 0)
                break;
        }
        $data[] = array($row['univ_code'] . " " . $row['team_code'], $row["venue_name"], $row["venue_location"], strtoupper($pos_string));
    }
    $result["data"] = $data;
    return $result;
}

function get_motion_for_round($round) {
    $motion_query = "SELECT motion FROM motions WHERE round_no = ?";
    $motion_result = qp($motion_query, array($round));
    $motion_row = $motion_result->FetchRow();
    return $motion_row['motion'];
}

function adjudicator_sheets($round) {
    // Get the motion for the round
    $motion_query = "SELECT motion FROM motions WHERE round_no = ?";
    $motion_result = qp($motion_query, array($round));
    $motion_row=$motion_result->FetchRow();
    $motion = $motion_row['motion'];

    // Get the individual debate details
    $venue_query = "SELECT draw.debate_id AS debate_id, draw.og AS ogid, draw.oo AS ooid, draw.cg AS cgid, draw.co AS coid, draw.venue_id AS venue_id, venue.venue_name AS venue_name, venue.venue_location AS venue_location, oguniv.univ_code AS og_univ_code, ogteam.team_code AS og_team_code, oouniv.univ_code AS oo_univ_code, ooteam.team_code AS oo_team_code, cguniv.univ_code AS cg_univ_code, cgteam.team_code AS cg_team_code, couniv.univ_code AS co_univ_code, coteam.team_code AS co_team_code ";
    $venue_query .= "FROM draws AS draw, venue AS venue, university AS oguniv, team AS ogteam, university AS oouniv, team AS ooteam, university AS cguniv, team AS cgteam, university AS couniv, team AS coteam ";
    $venue_query .= "WHERE draw.round_no=? AND draw.venue_id = venue.venue_id AND ogteam.team_id = draw.og AND oguniv.univ_id = ogteam.univ_id AND ooteam.team_id = draw.oo AND oouniv.univ_id = ooteam.univ_id AND cgteam.team_id = draw.cg AND cguniv.univ_id = cgteam.univ_id AND coteam.team_id = draw.co AND couniv.univ_id = coteam.univ_id ";
    $venue_query .= "ORDER BY venue_location, venue_name ";

    $venue_result = qp($venue_query, array($round));

    $data = array();
    while ($venue_row=$venue_result->FetchRow()) {
        $venue_location = $venue_row['venue_location'];
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
	$debate_param = array($round, $debate_id);


        // Get Chair
        $chfadj_query = "SELECT adjud.adjud_name AS adjud_name FROM draw_adjud AS round, adjudicator AS adjud WHERE round.round_no=? AND round.debate_id=? AND round.status = 'chair' AND adjud.adjud_id = round.adjud_id ";
        $chfadj_result = qp($chfadj_query, $debate_param);
        $chfadj_row=$chfadj_result->FetchRow();
        $chair = $chfadj_row['adjud_name'];

        // Get Panelists
        $pnladj_query = "SELECT adjud.adjud_name AS adjud_name FROM draw_adjud AS round, adjudicator AS adjud WHERE round.round_no=? AND round.debate_id =? AND round.status = 'panelist' AND adjud.adjud_id = round.adjud_id ";
        $pnladj_result = qp($pnladj_query, $debate_param);
	$panel="";
        while ($pnladj_row=$pnladj_result->FetchRow()) {
		if($panel=="") {
			$panel=$pnladj_row['adjud_name'];
		} else {
			$panel = $panel.", ".$pnladj_row['adjud_name'];
		}
	}
	if(strlen($panel)>114) {
		$panel=substr($panel,0,143);
	}


        // Get Speakers
        list($ogspkr1, $ogspkr2) = get_speaker_names($ogid);
        list($oospkr1, $oospkr2) = get_speaker_names($ooid);
        list($cgspkr1, $cgspkr2) = get_speaker_names($cgid);
        list($cospkr1, $cospkr2) = get_speaker_names($coid);

	$page = array(
		"debate_id" => $debate_id,
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
	if (get_setting("eballots_enabled") == 1) {
		$page["auth_code"] = get_auth_code($debate_id);
	}
        $data[] = $page;
    }
    $result = array();
    $result["header"] = array("debate_id", "Chair", "Round", "Venue", "Motion", "Opening Government", "Opening Opposition", "Closing Government", "Closing Opposition", "Opening Government Speaker 1", "Opening Government Speaker 2", "Opening Opposition Speaker 1", "Opening Opposition Speaker 2", "Closing Government Speaker 1", "Closing Government Speaker 2", "Closing Opposition Speaker 1", "Closing Opposition Speaker 2");
    if (get_setting("eballots_enabled") == 1) {
	    $result["header"][] = "eBallot Authentication Code";
    }
    $result["data"] = $data;
    return $result;
}

function get_co_adjudicators_for_round($adjud_id, $round) {
    $result = array();
    $db_result = qp("SELECT b.adjud_id AS adjud_id FROM draw_adjud AS a, draw_adjud AS b WHERE a.round_no=? AND b.round_no=? AND  a.adjud_id =? AND a.debate_id = b.debate_id AND NOT b.adjud_id=?", array($round, $round, $adjud_id, $adjud_id));
    while ($row = $db_result->FetchRow())
        $result[] = $row['adjud_id'];
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
    $db_result = qp("SELECT draw.og, draw.oo, draw.cg, draw.co FROM draw_adjud AS a, draws AS draw WHERE a.round_no=? AND draw.round_no=? AND a.adjud_id=? AND a.debate_id = draw.debate_id", array($round, $round, $adjud_id));
    while ($row = $db_result->FetchRow()) {
        $result[] = $row['og'];
        $result[] = $row['oo'];
        $result[] = $row['cg'];
        $result[] = $row['co'];
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
    $db_result = qp("SELECT university.univ_code, team.team_code FROM university, team WHERE team.team_id=? AND team.univ_id = university.univ_id", array($team_id));
    $row = $db_result->FetchRow();
    return $row['univ_code'] . " " . $row['team_code'];
}

function team_name_long($team_id) {
    $db_result = qp("SELECT university.univ_code, team.team_code, university.univ_name FROM university, team WHERE team.team_id = ? AND team.univ_id = university.univ_id", array($team_id));
    $row = $db_result->FetchRow();
    return "{$row['univ_code']} {$row['team_code']} ({$row['univ_name']})";
}

function team_name_long_table($team_id) {
    $db_result = qp("SELECT university.univ_code, team.team_code, university.univ_name FROM university, team WHERE team.team_id = ? AND team.univ_id = university.univ_id", array($team_id));
    $row = $db_result->FetchRow();
    return "{$row['univ_name']} {$row['team_code']}";
}

function team_code_long_table($team_id) {
    $db_result = qp("SELECT university.univ_code, team.team_code, university.univ_name FROM university, team WHERE team.team_id = ? AND team.univ_id = university.univ_id", array($team_id));
    $row = $db_result->FetchRow();
    return "{$row['univ_code']} {$row['team_code']}";
}

function venue_name($venue_id) {
	$db_result=q("SELECT venue_name FROM venue WHERE venue_id=$venue_id");
	$row = $db_result->FetchRow();
	return $row['venue_name'];
}

function get_room_name_from_debate($debate_id, $round){
	$query = "SELECT venue_name FROM venue INNER JOIN draws ON venue.venue_id = draws.venue_id WHERE round_no=? AND debate_id = ?";
	$param = array($round, $debate_id);
	$res = qp($query, $param);
	return $res->Fields("venue_name");
}

function adjudicator_name($adjudicator_id){
	$res=qp("SELECT adjud_name FROM adjudicator WHERE adjudicator.adjud_id = ?",array($adjudicator_id));
	return $res->Fields("adjud_name");
}

function get_university($univ_id) {
    return q("SELECT * FROM university WHERE univ_id = '$univ_id'")->FetchRow();
}

function delete_team($team_id) {
    global $DBConn;
    //Check for whether debates have started
    $c = count_rows("draws");
    if ($c!=0) {
        $msg[]="Debates in progress. Cannot delete now.";
    } else {
        $result1=qp("DELETE FROM speaker WHERE team_id=?", array($team_id));
        //Check for Error
        if ($DBConn->Affected_Rows()==0) {
            $msg[]="There were problems deleting speakers: No such record.";
        }
        $result2=qp("DELETE FROM team WHERE team_id=?", array($team_id));
        //Check for Error
        if ($DBConn->Affected_Rows()==0) {
            $msg[]="There were problems deleting team: No such record.";
	}
    }
    return $msg;
}

function delete_adjud($adjud_id){
    $c = count_rows("draws");
    if ($c!=0) {
        $msg[]="Debates in progress. Cannot delete now.";
    } else {
        //Delete Stuff
        $adjud_id=trim(@$_GET['adjud_id']);
	$result=qp("DELETE FROM adjudicator WHERE adjud_id=?", array($adjud_id));

        //Check for Error
        if ($result->Affected_Rows()==0) {
            $msg[]="There were problems deleting : No such record.";
        }
    }
    return $msg;
}

function convert_db_ssesl(){
	  //for backwards compatability with Tabbie versions <= 1.4.2
	  // add speaker_esl to speaker and import ESL data from teams
	    $result = q("SHOW COLUMNS FROM speaker LIKE 'speaker_esl'");
	    if (!$result->RecordCount())
			{
				q("ALTER TABLE  speaker ADD  speaker_esl CHAR( 3 ) NOT NULL DEFAULT  'N'");
				$query="SELECT speaker.speaker_id, team.esl, speaker.speaker_esl FROM  speaker INNER JOIN  team ON speaker.team_id = team.team_id";
				$result=q($query);
				while ($row = $result->FetchRow()) {
					if ($row[esl]=="Y") set_speaker_esl($row[speaker_id], "Y");
			    }
			}
		q("ALTER TABLE team CHANGE esl esl VARCHAR (3)");
		q("UPDATE team SET esl='ESL' WHERE esl = 'Y'");
}

function set_speaker_esl($speaker_id, $esl){
	return q("UPDATE speaker SET  speaker_esl=? WHERE  speaker.speaker_id =?", array($esl,$speaker_id));
}

function makesafe($string){
	$string=trim($string);
	return $string;
}

function add_strike_judge_team($adjud_id, $team_id, $univ_id){
	return qp("INSERT INTO strikes (adjud_id, team_id, univ_id) VALUES(?, ?, ?)", array($adjud_id, $team_id, $univ_id));
}

function add_strike_judge_univ($adjud_id, $univ_id){
	return qp("INSERT INTO strikes (adjud_id, univ_id) VALUES(?, ?)", array($adjud_id, $univ_id));
}

function del_strike_judge_team($adjud_id, $team_id){
	//you'd better pass this function a valid strike or else: it's 1am.
	$r=qp("SELECT * FROM strikes WHERE adjud_id=? AND team_id=?", array($adjud_id, $team_id));
	$row=$r->FetchRow();
	$strike_id=$row['team_id'];
	$r=qp("DELETE * FROM strikes where strike_id=?", array($strike_id));
	return $r->Affected_Rows();
}

function is_strike_judge_team($adjud_id, $team_id){
	//echo("is_strike_judge_team(".$adjud_id.",".$team_id.")");
	$resultstrike=qp("SELECT * FROM strikes WHERE adjud_id=? AND team_id=?", array($adjud_id, $team_id));
	$rownum=$resultstrike->RecordCount();
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
	$result = qp("SELECT univ_id FROM team WHERE team_id=?", array($team_id));
	$row = $result->FetchRow();
	return $row['univ_id'];
}

function is_strike_judge_univ($adjud_id, $univ_id){
	$query=
	$resultstrike=qp("SELECT * FROM strikes WHERE adjud_id=? AND univ_id=? AND team_id IS NULL", array($adjud_id, $univ_id));
	$rownum=$resultstrike->RecordCount();
	if ($rownum>0){
		//echo("strike on university $univ_id");
		//echo $query;
		return true;
	} else {
		return false;
	}
}
function del_strike_id($strike_id){
	return qp("DELETE FROM strikes WHERE strikes.strike_id = ?" , array($strike_id));
}

function get_team_id($univ_id, $team_code){
	$resultteam=qp("SELECT team_id FROM team WHERE univ_id=? AND team_code=?", array($univ_id, $team_code));
	$row=$resultteam->FetchRow();
	$team_id=$row['team_id'];
	return $team_id;
}

function strikes_to_conflict_list($adjud_id){
	$query="SELECT * FROM strikes WHERE adjud_id=?";
	$param=array($adjud_id);
	$conflict_list="";
	$result=qp($query, $param);
	while($row=$result->FetchRow()){
		$conflict_list=$conflict_list.team_name($row['team_id']).",";
	}
	return $conflict_list;
}

function mysql_to_xml($query, $baseelement){
	$result=q($query);
	return recordset_to_xml($result, $baseelement);
}

function recordset_to_xml($result, $baseelement) {
	$dom = new DomDocument('1.0', 'utf8');
	$top = $dom->createElement('collection');
	$top = $dom->appendChild($top);
	while($row = $result->FetchRow()) {
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
	$strikequery="SELECT u.univ_code, t.team_code FROM strikes as s INNER JOIN university AS u on s.univ_id = u.univ_id LEFT JOIN team AS t on s.team_id = t.team_id WHERE s.adjud_id = ?";
	$strikeresult=qp($strikequery, array($adjud_id));
	while($strike=$strikeresult->FetchRow()){
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

function finalise_temporary_draw($nextround) {
    $query = "SELECT DISTINCT COUNT(*) AS numadjud ";
    $query .= "FROM temp_adjud";
    $query .= "WHERE STATUS = 'chair'";

    $resultnumadjud=q($query);
    $rownumadjud=$resultnumadjud->FetchRow();
    $adjudcount=$rownumadjud['numadjud']; //count chair adjudicators


    $query = "SELECT COUNT(*) AS numdebates ";
    $query .= "FROM temp_draw";

    $resultnumdebates=q($query);
    $rownumdebates=$resultnumdebates->FetchRow();
    $debatecount=$rownumdebates['numdebates'];

    if ($adjudcount!=$debatecount) //No. of debates and chair adjudicators dont match
      {
        $msg[]="ERROR! There are debates with no Chair Adjudicator Allocated";
		return 0;
      }
	 $query="SELECT adjud_id, og, oo, cg, co FROM temp_adjud INNER JOIN temp_draw ON temp_adjud.debate_id=temp_draw.debate_id";
	 if(!($result=q($query))){
		 $msg[]="ERROR - strike query failed to execute";
		return 0;
	 } else {
		while($row=$result->FetchRow()){
			$ogid=$row['og'];
			$ooid=$row['oo'];
			$cgid=$row['cg'];
			$coid=$row['co'];
			$query="SELECT univ_id FROM team WHERE team_id=? OR team_id=? OR team_id = ? OR team_id = ?";
			$univ_id_result=qp($query, array($ogid, $ooid, $cgid, $coid));
			if($univ_id_result->RecordCount()!=4){
				$msg[]="ERROR - more than four teams for id (!)";
				$msg[]="You appear to have a corrupted database. Consider restoring from a backup and check previous rounds' data carefully.";
				return 0;
			}
			$univ_ids=array();
			while($univ_id_row=$univ_id_result->FetchRow()){
				$univ_ids[]=$univ_id_row['univ_id'];
			}
			$query="SELECT * FROM strikes WHERE adjud_id=? AND ((team_id=? OR team_id=? OR team_id=? OR team_id=? ) OR ((univ_id=? OR univ_id=? OR univ_id=? OR univ_id=?) AND team_id IS NULL))";
			$param=array($row['adjud_id'], $ogid, $ooid, $cgid, $coid, $univ_ids[0], $univ_ids[1], $univ_ids[2], $univ_ids[3]);
			echo("<br/>");
			if(!($strikeresult=qp($query, $param))){
			 	$msg[]="ERROR - strike query failed to execute";
				return 0;
		 	} else if($strikeresult->RecordCount()>0){
				return 0;
				$msg[]="ERROR - strike in draw";
				$msg[]="Adjudicator ".$row["adjud_id"]." is conflicted from their debate. Either clear the conflict or reallocate the adjudicator to proceed.";
			}
		}
	}
	return 1;
}

function get_speaker_names($team_id) {
        $spkr_query = "SELECT speaker_name FROM speaker WHERE team_id = ? ORDER BY speaker_id ASC";
        $spkr_result = qp($spkr_query, array($team_id));
	$spkr_row = $spkr_result->GetArray();
	return array($spkr_row[0]['speaker_name'], $spkr_row[1]['speaker_name']);
}

function get_auth_code($debate_id) {
	$query = "SELECT auth_code FROM eballot_rooms WHERE debate_id = ?";
	$rs = qp($query, array($debate_id));
	$r = $rs->FetchRow();
	return $r['auth_code'];
}
?>
