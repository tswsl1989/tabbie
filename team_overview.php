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

$ntu_controller = "print"; #selected in menu
$title = "Team Overview";
require("view/header.php");
require("view/mainmenu.php");

require_once("includes/backend.php");
require_once("includes/teamstanding.php");

$team_id = @$_REQUEST['team_id'];

if (!$team_id) {

    print "<h2>Team Overview</h2><ul>";
    
    $db_result = mysql_query(
        "SELECT T.team_id, univ_code, team_code, univ_name, S1.speaker_name " .
        "AS speaker1, S2.speaker_name AS speaker2, esl, active, composite " .
        "FROM university AS U, team AS T, speaker AS S1, speaker AS S2 " .
        "WHERE T.univ_id=U.univ_id AND S1.team_id=T.team_id AND " . 
        "S2.team_id=T.team_id AND S1.speaker_id<S2.speaker_id  " .
        "ORDER BY univ_code, team_code ");

    while ($row = mysql_fetch_assoc($db_result)) {
        print "<li><a href=\"team_overview?team_id={$row['team_id']}\">{$row['univ_code']} {$row['team_code']} ({$row['speaker1']} and {$row['speaker2']})</a></li>";
    }
    print "</ul>";

} else {
    print "<h2>Team Overview for team " . team_name_long($team_id) . "</h2>";
    $db_result = mysql_query("SELECT S1.speaker_name AS speaker1, S2.speaker_name AS speaker2 FROM team AS T, speaker AS S1, speaker AS S2 WHERE T.team_id = '$team_id' AND S1.team_id=T.team_id AND S2.team_id=T.team_id AND S1.speaker_id<S2.speaker_id");
    $row = mysql_fetch_assoc($db_result);
    print "Speakers: {$row['speaker1']} and {$row['speaker2']}";
    $completed_rounds = get_num_completed_rounds();

    $final_standing = team_standing_array($completed_rounds);
    
    for ($round = 1; $round <= $completed_rounds; $round++) {
        $prev_round_standing = @$round_standing;
        $round_standing = team_standing_array($round);

        $motion = get_motion_for_round($round) ? get_motion_for_round($round) : "(Unknown)";
        print "<h2>Round $round</h2>";
        print "<h3>Motion: $motion</h3>";

        print "<h3>Teams:</h3>";
        $db_result = mysql_query(
            "SELECT debate_id AS debate_id, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location FROM draw_round_$round, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND draw_round_$round.venue_id=venue.venue_id AND (og = '$team_id' OR oo = '$team_id' OR cg = '$team_id' OR co = '$team_id')");
        $row = mysql_fetch_assoc($db_result);
        $debate_id = $row['debate_id'];
        print "<table><tr><th>Role</th><th>Team</th><th>Ranking</th><th>Points</th><th>Before Round $round</th><th>After Round $round</th><th>After Round $completed_rounds</th></tr>";
        
        foreach (array('og' => 'Opening Government', 'oo' => 'Opening Opposition', 'cg' => 'Closing Government', 'co' => 'Closing Opposition') as $short => $long) {
            $id = $row["{$short}id"];
            $name = $row["{$short}tc"] . " " . $row["{$short}t"];
            $ranking = ranking_for_team_in_round($id, $round);
            $points = points_for_ranking($ranking);
            $ranking = ucwords($ranking);
            if ($round == 1)
                $before = "1st (0 points, 0 speaks)";
            else 
                $before = team_standing_for_team($prev_round_standing, $id);
            $after = team_standing_for_team($round_standing, $id);
            $final = team_standing_for_team($final_standing, $id);

            print "<tr><td>$long</td><td><a href=\"team_overview?team_id=$id\">$name</a></td><td>$ranking</td><td>$points</td><td>$before</td><td>$after</td><td>$final</td></tr>";
        }

        print "</table>";
    
        print "<h3>Speakers:</h3>";

        $db_result = mysql_query("SELECT speaker_round_$round.points, speaker.speaker_name, university.univ_code, team.team_code FROM speaker_round_$round, speaker, team, university WHERE debate_id='$debate_id' AND speaker_round_$round.speaker_id = speaker.speaker_id AND speaker.team_id = team.team_id AND team.univ_id = university.univ_id ORDER BY speaker_name");
        
        print "<table><tr><th>Name</th><th>Team</th><th>Points</th></tr>";
        while ($row = mysql_fetch_assoc($db_result))
            print "<tr><td>{$row['speaker_name']}</td><td>{$row['univ_code']} {$row['team_code']}</td><td>{$row['points']}</td></tr>";
        print "</table>";

        print "<h3>Adjudicators:</h3>";

        $db_result = mysql_query("SELECT status, adjudicator.adjud_name, university.univ_name, university.univ_code FROM adjud_round_$round, adjudicator, university WHERE debate_id='$debate_id' AND adjud_round_$round.adjud_id = adjudicator.adjud_id AND adjudicator.univ_id = university.univ_id ORDER BY status");
        print mysql_error();
        
        print "<table><tr><th>Role</th><th>Name</th><th>University</th></tr>";
        while ($row = mysql_fetch_assoc($db_result)) {
            $role = ucwords($row['status']);
            print "<tr><td>$role</td><td>{$row['adjud_name']}</td><td>{$row['univ_name']} ({$row['univ_code']})</td></tr>";
        }
        print "</table>";

    }

}

require("view/footer.php");

?>