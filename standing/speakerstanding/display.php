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

require_once('includes/backend.php');
$warning="";
//Upgrade DB for post 1.4.2
convert_db_ssesl();

if(array_key_exists("action", @$_GET)) $action=@$_GET['action'];
if(array_key_exists("warning", @$_GET)) $warning=@$_GET['warning'];

$list=false;
if(array_key_exists("list", @$_POST)) $list=@$_POST['list'];
if (!$list)
{    
	if(array_key_exists("list", @$_POST)){
		$list=trim(@$_GET['list']); //list : all, esl, break, eslbreak
	} else {
		$list="all"; //set to all if empty
	}
}

if (($numdraws <> $numresults) && !$round)
{   
    $warnmsg[]="Results for the ongoing draw (Round ".$numdraws.") has not been entered.";
    $warnmsg[]="This round will not be reflected in the standings.";
    $warnmsg[]="<BR>";
    if ($warning <> "done")
        $action = "warning";
}

$title = "Speaker Standings";

switch($list)
{
    case "all":
	break;
    case "esl":
	$title.= " (ESL)";
	break;
    case "efl":
	$title.= " (EFL)";
	break;
    default:
	$list = "all";
	break;
}
                
switch($action)
{
    case "display":     $title.=" - Round $roundno";
                        break;
    case "warning":     $title.=" - Confirm";
                        break;
    default:
                        $title.=" - Round $roundno";
                        $action="display";
                        break;
}

include("header.php");

echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

if ($action == "warning")
{
    //Display Messages
    for($x=0;$x<count($warnmsg);$x++)
        echo "<p class=\"err\">".$warnmsg[$x]."</p>\n";
    ?>
    <h3><a href="standing.php?moduletype=speakerstanding&list=<?echo $list?>&action=display&warning=done">Click to confirm</a></h3>
    <?
}


if ($action == "display")
{
    $warning="null";
    $query = "SELECT speaker.speaker_id, speaker.team_id, speaker_name FROM speaker AS speaker";

    if ($list=="esl")
        $query.=" WHERE speaker.speaker_esl != 'N'";

    if ($list=="efl")
        $query.=" WHERE speaker.speaker_esl = 'EFL'";

    $result = mysql_query($query);
    $speaker_count=mysql_num_rows($result);
    
    //echo "query => $query <BR>";
    //echo "$speaker_count <BR>";
    
    // Create array with all the team ids
    $index=0;
    $speaker_array = array();
    while ($row=mysql_fetch_assoc($result))
    {
        $speaker_array[$index] = array("index" => $index++,
                            "speakerid" => $row['speaker_id'],
                            "speakername" => $row['speaker_name'],
                            "teamid" => $row['team_id'],
                            "teamname" => ' ',
                            "points" => 0);    
    }
    
    
    // Fill up all the team names
    function fillUpTeamNames($speaker_array) {
        $result = array();
        foreach ($speaker_array as $speaker) {
            $teamid = $speaker["teamid"];
            $name_query = "SELECT team.team_id, univ.univ_code AS univ_code, team.team_code AS team_code ";
            $name_query .= "FROM university AS univ, team AS team ";
            $name_query .= "WHERE team.team_id=$teamid AND team.univ_id = univ.univ_id ";
            $name_result = mysql_query($name_query);
            $name_row = mysql_fetch_assoc($name_result);
            $teamname = $name_row['univ_code'].' '.$name_row['team_code'];
            $speaker["teamname"] = $teamname;
            $speaker["team_id"] = $name_row['team_id'];
            $result[] = $speaker;
        }
        return $result;
    }
    $speaker_array = fillUpTeamNames($speaker_array);
    
    // Run through the array and add the points
    foreach($speaker_array as $cc) 
    {
        $index = $cc["index"];
        $speaker_id = $cc["speakerid"];
        $points = 0;

        for ($x=1;$x<=$roundno;$x++)
        {
            $score_query = "SELECT points FROM speaker_results ";
            $score_query .= "WHERE round_no=$x AND speaker_id = '$speaker_id' ";
            $score_result = mysql_query($score_query);
            $score_row = mysql_fetch_assoc($score_result);
            $points += $score_row['points'];
            $speaker_array[$index]["round_$x"] = $score_row['points'];
        }
        $speaker_array[$index]["points"] = $points;
    }
    
    
    // Sorting the array
    function cmp ($a, $b) {
        return ($a["points"] > $b["points"]) ? -1 : 1;
    }
    usort($speaker_array, "cmp");
    
    
    // Displaying the standings
    echo "<table>\n";
    echo "<tr><th>Position</th><th>Speaker Name</th><th>Team Name</th>";
    for ($x=1;$x<=$roundno;$x++)
        echo "<th>Round $x</th>";
    echo "<th>Total Points</th></tr>\n";

    $prev_points = "something";

    for ($x=0;$x<count($speaker_array);$x++)
    {   
        $ranking = ($x+1);
        if ($speaker_array[$x]["points"] != $prev_points) {
            $prev_points = $speaker_array[$x]["points"];
            $display_ranking = ($x+1);
        } else
            $display_ranking = "-";
	    echo "<tr>\n";
            echo "<td>$display_ranking</td>\n";
            echo "<td>".$speaker_array[$x]["speakername"]."</td>\n";
            echo "<td>"."<a href=\"team_overview.php?team_id={$speaker_array[$x]['team_id']}\">".$speaker_array[$x]["teamname"]."</td>\n";
            for ($y=1;$y<=$roundno;$y++)
                echo "<td>" . $speaker_array[$x]["round_$y"] . "</td>";
            echo "<td>".$speaker_array[$x]["points"]."</td>\n";
        echo "</tr>\n";
        
    }
    echo "</table>\n";
    
    /*
    //Code for testing the present array content
    foreach($speaker_array as $cc) {
        echo "$cc -> ";
        foreach($cc as $k => $dd) {
            print ". . .$k => $dd";
        }
        print "<BR>";
    }
    */
    
    //echo "DONE PROCESSING! ";
}


?>
</div>
</body>
</html>
