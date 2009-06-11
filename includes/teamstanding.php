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

function rankings_for_team_wudc_2004_article_4_a_iii($team_id, $round) {
    $rankings = array(0, 0, 0, 0);
    for ($roundno = 1; $roundno <= $round; $roundno++) {
        $ranking = 3 - (points_for_ranking(ranking_for_team_in_round($team_id, $roundno)));
        $rankings[$ranking] += 1;
    }
    return "{$rankings[0]}, {$rankings[1]}, {$rankings[2]}, {$rankings[3]}";
}

function cmp_teams_on_points_asdf (&$a, &$b) {
    if ($a["score"] == $b["score"]) {
        if ($a["speaker"] == $b["speaker"]) {
            if ($a["rankings"] == $b["rankings"])
                return 0;
            return ($a["rankings"] > $b["rankings"]) ? -1 : 1;
        }
        return ($a["speaker"] > $b["speaker"]) ? -1 : 1;
    }
    return ($a["score"] > $b["score"]) ? -1 : 1;
}

function __fillUpTeamnames($team_array) {
    $result = array();
    // Fill up all the team names
    foreach ($team_array as $cc)
    {
        $teamid = $cc["teamid"];
        $name_query = "SELECT univ.univ_code AS univ_code, team.team_code AS team_code ";
        $name_query .= "FROM university AS univ, team AS team ";
        $name_query .= "WHERE team.team_id=$teamid AND team.univ_id = univ.univ_id ";
        $name_result = mysql_query($name_query);
        $name_row = mysql_fetch_assoc($name_result);
        $teamname = $name_row['univ_code'].' '.$name_row['team_code'];
        $cc["teamname"] = $teamname;
        $result[] = $cc;
    }
    return $result;
}

function team_standing_array($roundno, $list="all") {
    $warning="null";
    $query = "SELECT team_id FROM team ";

    if ($list=="esl")
        $query.=" WHERE esl = 'ESL' OR esl = 'EFL'";
        
    if ($list=="break")
        $query.=" WHERE composite = 'N' ";
        
    if ($list=="eslbreak")
        $query.=" WHERE (esl = 'ESL' OR esl = 'EFL') and composite = 'N' ";

    if ($list=="efl")
        $query.=" WHERE esl = 'EFL'";

    if ($list=="eflbreak")
        $query.=" WHERE esl = 'EFL' and composite = 'N' ";

    $result = mysql_query($query);
    $team_count=mysql_num_rows($result);
            
    // Create array with all the team ids
    $index=0;
    $team_array = array();
    while ($row = mysql_fetch_assoc($result))
    {
        $team_array[$index] = array("index" => $index++,
                            "teamid" => $row['team_id'],
                            "team_id" => $row['team_id'], //bweghhh.... 
                            "teamname" => ' ',
                            "score" => 0,
                            "speaker" => 0);    
    }
    
    $team_array = __fillUpTeamnames($team_array);
    
    foreach($team_array as &$team)
        $team['rankings'] = rankings_for_team_wudc_2004_article_4_a_iii($team['teamid'], $roundno);

    // Run through the array and add the points
    foreach($team_array as $cc) 
    {
        $index = $cc["index"];
        $team_id = $cc["teamid"];
        $score = 0;
        $speaker = 0;
        for ($x=1;$x<=$roundno;$x++)
        {
            $team_array[$index]["round_$x"] = 0; //default;
            // Check for first
            $score_query = "SELECT first FROM result_round_$x WHERE first = '$team_id' ";
            $score_result = mysql_query($score_query);
            $score_count = mysql_num_rows($score_result);
            if ($score_count > 0) {
                $team_array[$index]["round_$x"] = 3;
            }
    
            // Check for second
            $score_query = "SELECT second FROM result_round_$x WHERE second = '$team_id' ";
            $score_result = mysql_query($score_query);
            $score_count = mysql_num_rows($score_result);
            if ($score_count > 0)
                $team_array[$index]["round_$x"] = 2;
    
            // Check for third
            $score_query = "SELECT third FROM result_round_$x WHERE third = '$team_id' ";
            $score_result = mysql_query($score_query);
            $score_count = mysql_num_rows($score_result);
            if ($score_count > 0)
                $team_array[$index]["round_$x"] = 1;
            

            $score +=  $team_array[$index]["round_$x"];
            // Speaker points
            $score_query = "SELECT points FROM speaker_round_$x AS round, speaker AS speaker ";
            $score_query .= "WHERE speaker.team_id = '$team_id' AND speaker.speaker_id = round.speaker_id ";
            $score_result = mysql_query($score_query);
            while ($score_row = mysql_fetch_assoc($score_result))
            {
                $speaker += $score_row['points'];
            }
        }
        $team_array[$index]["score"] = $score;
        $team_array[$index]["speaker"] = $speaker;
    }
    
    
    // Sorting the array
    usort($team_array, "cmp_teams_on_points_asdf");
    
    $ranking = 1;
    $prev_ranking = 1;
    $prev_concat = "something";
    foreach ($team_array as &$team) {
        $concat = "{$team['score']}; {$team['speaker']}; {$team['rankings']}";
        if ($concat != $prev_concat) {
            $prev_concat = $concat;
            $prev_ranking = $ranking;
        }
        $team['ranking'] = $prev_ranking;
        $ranking++;
    }

    
    return $team_array;

}

function st_nd_rd_th($nr) {
    if (($nr > 10) && ($nr < 20))
        return $nr . "th";
    $last = $nr % 10;
    if ($last == 1) return $nr . "st";
    if ($last == 2) return $nr . "nd";
    if ($last == 3) return $nr . "rd";
    return $nr . "th";
}

function team_standing_for_team(&$team_standing_array, $team_id) {
    foreach ($team_standing_array as &$team) {
        if ($team['team_id'] == $team_id) {
            $st_nd_rd_th = st_nd_rd_th($team['ranking']);
            return "$st_nd_rd_th ({$team['score']} points, {$team['speaker']} speaks)";
        }
    }
    return "Unknown";
}

?>