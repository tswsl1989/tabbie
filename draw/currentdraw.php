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

/*
Purpose :   Calculate the draw for the current round taking into account all
            the possible rules mentioned in the Tab rules draft.
*/
require_once("includes/display.php");
require_once("includes/db_tools.php");
require_once("includes/backend.php");
require_once("draw/adjudicator/simulated_annealing.php");

function cmp_debate_detail($one, $two) {
    $result = floatval($two[0]) - floatval($one[0]);
    return $result;
}

$action = @$_GET['action'];
$title="Draw : Round " . $nextround;

//Check for number of teams and venues
$validate = 1;

$numteams = count_rows("team", "active='Y'");
if ($numteams < 4 || ($numteams % 4) > 0) {
    $validate=0;
    $msg[] = "Number of teams is not correct for the draw to take place. Make sure that the number of teams is a multiple of four.";
}

$numvenues = count_rows("venue", "active='Y'");
if ($numvenues < ($numteams / 4)) {
    $validate = 0;
    $msg[] = "There are not enough venues to accomodate the teams.";
}

$numadjud = count_rows("adjudicator", "active='Y'");
if ($numadjud < ($numteams / 4)) {
    $validate = 0;
    $msg[] = "The are insufficient adjudicators to adjudicate the debates.";
    $msg[] = "Nr of Adjudicators : " . $numadjud;
}

$numrounds = get_num_rounds();
$numresults= get_num_completed_rounds();

if ($numrounds != $numresults) {
    $validate=0;
    $msg[] = "The results for the last round have not been entered. Please enter the results and then try again.";
}

$scoring_factors = get_scoring_factors_from_db();
if ($scoring_factors['lock']) {
    $validate=0;
    $msg[] = 'The automated draw has been locked - it seems that someone else is running the automated draw at the same time or you have hit reload too quickly. Try again by <a href="draw.php?moduletype=currentdraw">returning to the current draw page in a minute or so</a>. (Please realize that creating the draw for a tournament like worlds, with 400 teams and 300 adjudicators may take a while.) If you keep getting this message, <a href="input.php?moduletype=adjud_params">unset the lock parameter</a>.';
}

if ($validate) {
    store_scoring_factors_to_db(array("lock" => 1));
}

if (($action=="draw") && ($validate == 1)) {

    $teams = get_teams_positions_points(get_num_rounds());
    
    require_once("draw/algorithms/silver_line.php");
    require_once("draw/algorithms/ntu_core.php");

    //$debates = draw_ntu_core($teams, $nextround);
    $debates = draw_silver_line($teams, $nextround);

    if (! validate_draw($teams, $debates))
        $msg[] = "The Algortihm has not created a valid draw!!!";
    
    $score = debates_badness($debates); //only works for silver_line since others don't attribute enough data
    $msg[] = "The Debater Allocation Algortihm has created a draw with score $score, and 0 is the best possible score.";

    function funny_conversion_for_ntu_code($debates) {
        $result = array();
        foreach ($debates as $debate) {
            foreach ($debate as $team) {
                $result[] = array($team["team_id"], "NOT-USED");
            }
        }
        return $result;
    }
    $teamarray = funny_conversion_for_ntu_code($debates);

    //At this point $teamarray looks like this:
    // array(
    //     array("8", "NOT-USED"),
    //     array("4", "NOT-USED"),...

    //Store draw in temporary database

    $tablename = "temp_draw_round_$nextround";
    mysql_query("DROP TABLE `$tablename`"); //KvS notes that this query apparently fails (but expectedly) in round 0

    $query= "CREATE TABLE $tablename (
debate_id MEDIUMINT(9) NOT NULL ,
og MEDIUMINT(9) NOT NULL ,
oo MEDIUMINT(9) NOT NULL ,
cg MEDIUMINT(9) NOT NULL ,
co MEDIUMINT(9) NOT NULL ,
venue_id MEDIUMINT(9) NOT NULL ,
PRIMARY KEY (debate_id))";

    $result = q($query);
    
    $result1 = q("SELECT venue_id FROM venue WHERE active='Y'");
    while ($row1 = mysql_fetch_assoc($result1)) {
        $venue[] = $row1['venue_id'];
    }

    shuffle($venue);
    
    $index = 0;
    while ($index < count($teamarray))
    {
        //generate debate ID after multiplying current round with 10000
        $debate_id = (10000 * $nextround) + ($index/4);

        $og=$teamarray[$index++][0];
        $oo=$teamarray[$index++][0];
        $cg=$teamarray[$index++][0];
        $co=$teamarray[$index++][0];
        $venue_id=array_shift($venue);

        //insert into database
        q("INSERT INTO $tablename (debate_id, og, oo, cg, co, venue_id) VALUES('$debate_id', '$og', '$oo', '$cg', '$co', '$venue_id')");

    }
    $details = array();
    reallocate_simulated_annealing();
}

if (($action=="draw_adjudicators_again") && ($validate == 1)) {
    refine_simulated_annealing($msg);
}

if ($validate) {
    store_scoring_factors_to_db(array("lock" => 0));
}

if (has_temp_draw()) {
    display_sa_energy($msg, $details);
}

echo "<h2>$title</h2>\n"; //title

displayMessagesUL(@$msg);
         
//Display Draw if required
if (($validate==1)) {

    //Display the table of calculated draw
    $query = 'SELECT A1.debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ';
    $query .= "FROM temp_draw_round_$nextround AS A1, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue ";
    $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND A1.venue_id=venue.venue_id "; 
    $query .= "ORDER BY venue_name";

    $result=mysql_query($query); //KvS notes that this query apparently fails (but expectedly) in round 0
    
    if ($result) {
        echo "<p>From here you can either:</p>";
        echo "<h3><a href=\"draw.php?moduletype=currentdraw&amp;action=draw_adjudicators_again\">Give the computer another shot at allocating the adjudicators (Using the current state to generate a better result).</a></h3>";
        echo '<p>or</p>';
        echo '<h3><a href="draw.php?moduletype=manualdraw">Manually adjust adjudicators and rooms</a></h3>';
        echo '<p>or</p>';
        echo '<h3><a href="draw.php?moduletype=manualdraw&amp;action=finalise">Finalize the draw</a></h3>';

        $table_data = array();
        while($row = mysql_fetch_assoc($result)) {
            foreach (array("venue_name", "ogtc", "ogt", "ootc", "oot", "cgtc", "cgt", "cotc", "cot", "debate_id") as $copy)
                $row2[$copy] = $row[$copy];
            $row2['ogpoints'] = points_for_team($row['ogid'], $numdraws);
            $row2['oopoints'] = points_for_team($row['ooid'], $numdraws);
            $row2['cgpoints'] = points_for_team($row['cgid'], $numdraws);
            $row2['copoints'] = points_for_team($row['coid'], $numdraws);
            $row2['points'] = $row2['ogpoints'] + $row2['oopoints'] + $row2['cgpoints'] + $row2['copoints'];

            //Find Chief Adjudicator
            $query="SELECT A.adjud_name AS adjud_name, A.ranking FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='chair' AND T.debate_id='{$row['debate_id']}'";
            $resultadjud=q($query);

            if (mysql_num_rows($resultadjud) > 0) {
                $rowadjud = mysql_fetch_assoc($resultadjud);
                $row2['chair'] = "{$rowadjud['adjud_name']} ({$rowadjud['ranking']})";
            }

            //Find Panelists
            $query="SELECT A.adjud_name AS adjud_name, A.ranking FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='panelist' AND T.debate_id='{$row['debate_id']}'";
            $resultadjud=q($query);

            if (mysql_num_rows($resultadjud) > 0) {
                $row2['panel'] = array();
                while($rowadjud=mysql_fetch_assoc($resultadjud)) {
                    $row2['panel'][] = "{$rowadjud['adjud_name']} ({$rowadjud['ranking']})";
                }
             }
            $table_data[] = $row2;
        }

        usort($table_data, "cmp_debate_desc");

        echo "<table>\n";
            echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Avg. Points</th><th>Chair</th><th>Panelists</th><th>Adj. Allocation Score</th></tr>\n";

        foreach ($table_data as $row) {
            echo "<tr>\n";

            echo "<td>{$row['venue_name']}</td>\n";
            echo "<td>{$row['ogtc']} {$row['ogt']} <br/> ({$row['ogpoints']}) </td>\n";
            echo "<td>{$row['ootc']} {$row['oot']} <br/> ({$row['oopoints']}) </td>\n";
            echo "<td>{$row['cgtc']} {$row['cgt']} <br/> ({$row['cgpoints']}) </td>\n";
            echo "<td>{$row['cotc']} {$row['cot']} <br/> ({$row['copoints']}) </td>\n";
            
            $avg_points = sprintf("%01.2f", $row['points'] / 4);
            echo "<td>$avg_points</td>\n";

            if (! @$row['chair'])
                echo "<td><b>None Assigned</b></td>";
            else
                echo "<td>{$row['chair']}</td>";

            if (! @$row['panel'])
                echo "<td><b>None Assigned</b></td>";
            else {
                echo "<td><ul>\n";
                foreach ($row['panel'] as $panellist)
                {
                    echo "<li>$panellist</li>\n";
                }
                echo "</ul></td>\n";
            }
            echo "<td>";
            if (@$details[$row['debate_id']]) {
                echo "<table>";
                usort ($details[$row['debate_id']], "cmp_debate_detail");
                $total = 0;
                foreach ($details[$row['debate_id']] as $detail) {
                    if ($detail[0] > 0) {
                        $penalty = format_dec($detail[0]);
                        echo "<tr><td width=\"10\">$penalty</td><td width=\"300\">$detail[1]</td></tr>";
                        $total += $detail[0];
                    }
                }
                $total = format_dec($total);
                echo "<tr><td width=\"10\">$total</td><td width=\"300\">Total</td></tr>";
                echo "</table>";
            }
            echo "</td>";

        echo "</tr>\n";
        
        }

        echo "</table>\n";
    
    }

    //Display Summary of Current Status
    echo "<h3>No. of Teams : $numteams</h3>";
    echo "<h3>No. of Venues : $numvenues</h3>";
    echo "<h3>No. of Adjudicators : $numadjud</h3>";

}

if ($validate == 1) {
    echo "<h3><a href=\"draw.php?moduletype=currentdraw&amp;action=draw\">Automatically Calculate Draw (Starting from nothing)</a></h3>";
    
    if (has_temp_draw()) {
        echo "<h3><a href=\"draw.php?moduletype=currentdraw&amp;action=draw_adjudicators_again\">Give the computer another shot at allocating the adjudicators (Using the current state to generate a better result).</a></h3>";
    }
}
?>
