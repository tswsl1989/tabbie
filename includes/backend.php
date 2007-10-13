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
    $result = q("SHOW TABLES LIKE 'draw_round%'");
    return mysql_num_rows($result);
}

function has_temp_draw() {
    $result = q("SHOW TABLES LIKE 'temp_draw_round%'");
    return mysql_num_rows($result);
}

function get_num_completed_rounds() {
    $result = q("SHOW TABLES LIKE 'result_round%'");
    return mysql_num_rows($result);
}

function has_temp_result() {
    $result = q("SHOW TABLES LIKE 'temp_result_round%'");
    return mysql_num_rows($result);
}

function __team_on_ranking($round, $team_id, $ranking) {
    $query = "SELECT $ranking FROM result_round_$round WHERE $ranking = $team_id";
    return (mysql_num_rows(q($query)));
}

function __team_on_position($round, $team_id, $position) {
    $query = "SELECT $position FROM draw_round_$round WHERE $position = '$team_id'";
    return (mysql_num_rows(q($query)));
}

function points_for_ranking($ranking) {
    if ($ranking == "first") return 3;
    if ($ranking == "second") return 2;
    if ($ranking == "third") return 1;
    if ($ranking == "fourth") return 0;
    print "ERROR - points_for_ranking with ranking $ranking";
    return 999;
}

function points_for_team($team_id, $nr_of_rounds) {
    $RANKINGS = array("first", "second", "third", "fourth");
    $result = 0;
    for ($i = 1; $i <= $nr_of_rounds; $i++) {
        foreach ($RANKINGS as $RANKING) {
            if (__team_on_ranking($i, $team_id, $RANKING))
                $result += points_for_ranking($RANKING);
        }
    }
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
    $db_result = q("SELECT team_id FROM team WHERE active='Y'");
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
                @$current[$RANKING] += $team_on_ranking;
                if ($team_on_ranking) {
                    @$current["total"] += points_for_ranking($RANKING);
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
    
    $query = "SELECT v.*, a.* FROM adjudicator AS a, draw_round_$round AS d, " .
                "venue AS v, adjud_round_$round AS adjud  " .
                "WHERE d.venue_id=v.venue_id AND adjud.debate_id = d.debate_id AND " .
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
    $result["header"] = array("Team Name", "Venue", "Venue Location");
    
    $query = "SELECT v.venue_id AS venue_id, v.venue_location AS venue_location, v.venue_name AS venue_name, t.team_id AS team_id, t.team_code AS team_code, u.univ_code AS univ_code ";
        $query.="FROM team AS t, university AS u, draw_round_$round AS d, venue AS v ";
        $query.="WHERE d.venue_id=v.venue_id AND (t.team_id=d.og OR t.team_id=d.oo OR t.team_id=d.cg OR t.team_id=d.co) AND t.univ_id=u.univ_id ";
        $query.="ORDER BY univ_code, team_code ";

    $query_result = mysql_query($query);

    $data = array();
    while ($row = mysql_fetch_assoc($query_result)) {
        $data[] = array($row['univ_code'] . " " . $row['team_code'], $row["venue_name"], $row["venue_location"]);
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
    $venue_query .= "FROM draw_round_$round AS draw, venue AS venue, university AS oguniv, team AS ogteam, university AS oouniv, team AS ooteam, university AS cguniv, team AS cgteam, university AS couniv, team AS coteam ";
    $venue_query .= "WHERE draw.venue_id = venue.venue_id AND ogteam.team_id = draw.og AND oguniv.univ_id = ogteam.univ_id AND ooteam.team_id = draw.oo AND oouniv.univ_id = ooteam.univ_id AND cgteam.team_id = draw.cg AND cguniv.univ_id = cgteam.univ_id AND coteam.team_id = draw.co AND couniv.univ_id = coteam.univ_id ";
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
        $chfadj_query = "SELECT adjud.adjud_name AS adjud_name FROM adjud_round_$round AS round, adjudicator AS adjud WHERE round.debate_id = $debate_id AND round.status = 'chair' AND adjud.adjud_id = round.adjud_id ";
        $chfadj_result = mysql_query($chfadj_query);
        $chfadj_row=mysql_fetch_assoc($chfadj_result);
        $chair = $chfadj_row['adjud_name'];

        // Get Panelists
        $pnladj_query = "SELECT adjud.adjud_name AS adjud_name FROM adjud_round_$round AS round, adjudicator AS adjud WHERE round.debate_id = $debate_id AND round.status = 'panelist' AND adjud.adjud_id = round.adjud_id ";
        $pnladj_result = mysql_query($pnladj_query);
        $pnladj_row=mysql_fetch_assoc($pnladj_result);
        for($i=0;$i<3;$i++)
        {    switch($i)
            {    case 0: $panelist_1 = $pnladj_row['adjud_name'];
                    break;
                case 1: $panelist_2 = $pnladj_row['adjud_name'];
                    break;
                case 2: $panelist_3 = $pnladj_row['adjud_name'];
                    break;
            }
            $pnladj_row=mysql_fetch_assoc($pnladj_result);
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
        $page = array(
            "chair" => $chair,
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
    $db_result = q("SELECT b.adjud_id FROM adjud_round_$round AS a, adjud_round_$round AS b WHERE a.adjud_id = '$adjud_id' AND a.debate_id = b.debate_id AND NOT b.adjud_id = '$adjud_id'");
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
    $db_result = q("SELECT draw.og, draw.oo, draw.cg, draw.co FROM adjud_round_$round AS a, draw_round_$round AS draw WHERE a.adjud_id = '$adjud_id' AND a.debate_id = draw.debate_id");
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

function get_university($univ_id) {
    return mysql_fetch_assoc(q("SELECT * FROM university WHERE univ_id = '$univ_id'"));
}

?>
