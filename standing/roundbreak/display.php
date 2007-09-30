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

$warning=@$_GET['warning'];

if (($numdraws <> $numresults) && !@$round)
{   
    $warnmsg[]="Results for the ongoing draw (Round ".$numdraws.") has not been entered.";
    $warnmsg[]="This round will not be reflected in the standings.";
    $warnmsg[]="<BR>";
    if ($warning <> "done")
        $action = "warning";
}

$list=trim(@$_POST['list']);
if (!$list)
{    $list=trim(@$_GET['list']); //list : all, esl, break, eslbreak
    if (!$list) $list="all"; //set to all if empty
}

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
    <h3><a href="standing.php?moduletype=roundbreak&action=display&warning=done">Click to confirm</a></h3>
    <?
}


if ($action == "display")
{
    $warning="null";
    

    echo "<table>\n";
    echo "<tr><th>Team Name</th>";
    for ($i=1;$i<=$roundno;$i++)
        echo "<th>Round $i</th>";
    
    echo "<th>Total</th></tr>\n";
    
    if ($list=="esl")
        $query = "SELECT team.team_id AS team_id, team.team_code AS team_code, univ.univ_name AS univ_name  FROM team AS team, university AS univ WHERE team.univ_id=univ.univ_id AND team.esl=\"Y\" ORDER BY univ.univ_name, team.team_code ";
    else if ($list=="all")
        $query = "SELECT team.team_id AS team_id, team.team_code AS team_code, univ.univ_name AS univ_name  FROM team AS team, university AS univ WHERE team.univ_id=univ.univ_id ORDER BY univ.univ_name, team.team_code ";

    $result = mysql_query($query);
    
    // echo "query => $query <BR>";
    // echo "$team_count <BR>";
            
    while ($row=mysql_fetch_assoc($result))
    {    $team_id=$row['team_id'];
        $team_name=$row['univ_name']." ".$row['team_code'];
        $total=0;
        echo "<tr><td>$team_name</td>";
        for ($x=1;$x<=$roundno;$x++)
        {
            $score=-1;
            
            // Check for first
            $score_query = "SELECT first FROM result_round_$x WHERE first = '$team_id' ";
            $score_result = mysql_query($score_query);
            $score_count = mysql_num_rows($score_result);
            if ($score_count > 0)
                $score = 3;
    
            // Check for second
            $score_query = "SELECT second FROM result_round_$x WHERE second = '$team_id' ";
            $score_result = mysql_query($score_query);
            $score_count = mysql_num_rows($score_result);
            if ($score_count > 0)
                $score = 2;
    
            // Check for third
            $score_query = "SELECT third FROM result_round_$x WHERE third = '$team_id' ";
            $score_result = mysql_query($score_query);
            $score_count = mysql_num_rows($score_result);
            if ($score_count > 0)
                $score = 1;
            
            // Check for fourth
            $score_query = "SELECT fourth FROM result_round_$x WHERE fourth = '$team_id' ";
            $score_result = mysql_query($score_query);
            $score_count = mysql_num_rows($score_result);
            if ($score_count > 0)
                $score = 0;
                
            if ($score==(-1))
                $score="-";
            else
                $total=$total+$score;
            echo "<td>$score</td>";
        }
        echo "<td>$total</td></tr>";
    }
    echo "</table>\n";
}    
    
?>
</div>
</body>
</html>
