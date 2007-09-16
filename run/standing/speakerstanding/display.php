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

$action=@$_GET['action'];
$warning=@$_GET['warning'];

$list=trim(@$_POST['list']);
if (!$list)
{    $list=trim(@$_GET['list']); //list : all, esl, break, eslbreak
    if (!$list) $list="all"; //set to all if empty
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
    case "all"   :      break;
    case "esl"   :    $title.= " (ESL)";
                break;
    default      :    $list = "all";
                break;
}
                
switch($action)
{
    case "display":     $title.=" - Round $numresults";
                        break;
    case "warning":     $title.=" - Confirm";
                        break;
    default:
                        $title.=" - Round $numresults";
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
        $query.=", team AS team WHERE speaker.team_id = team.team_id and team.esl = 'Y' ";

    $result = mysql_query($query);
    $speaker_count=mysql_num_rows($result);
    
    //echo "query => $query <BR>";
    //echo "$speaker_count <BR>";
    
    // Create array with all the team ids
    $index=0;
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
            $name_query = "SELECT univ.univ_code AS univ_code, team.team_code AS team_code ";
            $name_query .= "FROM university AS univ, team AS team ";
            $name_query .= "WHERE team.team_id=$teamid AND team.univ_id = univ.univ_id ";
            $name_result = mysql_query($name_query);
            $name_row = mysql_fetch_assoc($name_result);
            $teamname = $name_row['univ_code'].' '.$name_row['team_code'];
            $speaker["teamname"] = $teamname;
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
        for ($x=1;$x<=$numresults;$x++)
        {
            $score_query = "SELECT points FROM speaker_round_$x ";
            $score_query .= "WHERE speaker_id = '$speaker_id' ";
            $score_result = mysql_query($score_query);
            $score_row = mysql_fetch_assoc($score_result);
                  $points += $score_row['points'];
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
    echo "<tr><th>Position</th><th>Speaker Name</th><th>Team Name</th><th>Points</th></tr>\n";
    for ($x=0;$x<count($speaker_array);$x++)
    {
        echo "<tr>\n";
            echo "<td>".($x+1)."</td>\n";
            echo "<td>".$speaker_array[$x]["speakername"]."</td>\n";
            echo "<td>".$speaker_array[$x]["teamname"]."</td>\n";
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
