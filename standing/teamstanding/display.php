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

require_once("includes/teamstanding.php");

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

$title = "Team Standings";

switch($list)
{
    case "all"   :      break;
    case "esl"   :    $title.= " (ESL)";
                break;
    case "break" :    $title.= " (Breakable)";
                break;
    case "eslbreak" :   $title.= " (ESL - Breakable)";
                break;
	case "efl"   :    $title.= " (EFL)";
			                break;
	case "eflbreak" :   $title.= " (EFL - Breakable)";
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
    <h3><a href="standing.php?moduletype=teamstanding&list=<?echo $list?>&action=display&warning=done">Click to confirm</a></h3>
    <?
}


if ($action == "display")
{
   $team_array = team_standing_array($roundno, $list); 
    
    // Displaying the standings
    echo "<table>\n";
    echo "<tr><th>Ranking</th><th>Team Name</th>";
    for ($y=1;$y<=$roundno;$y++)
        echo "<th>Round $y</th>";
    echo "<th>Total Score</th><th>Speaker Points</th><th>WUDC art. 4.a.iii</th></tr>\n";
    $prev_ranking = "something";
    foreach ($team_array as $cc)
    {
        if ($cc['ranking'] != $prev_ranking) {
            $prev_ranking = $cc['ranking'];
            $display_ranking = $cc['ranking'];
        } else
            $display_ranking = "-";
        echo "<tr>\n";
            echo "<td>". $display_ranking ."</td>\n";
            echo "<td>"."<a href=\"team_overview.php?team_id={$cc['team_id']}\">".$cc["teamname"]."</a></td>\n";
            for ($y=1;$y<=$roundno;$y++)
                echo "<td>" . $cc["round_$y"] . "</td>";
            echo "<td>".$cc["score"]."</td>\n";
            echo "<td>".$cc["speaker"]."</td>\n";
            echo "<td>".$cc["rankings"]."</td>\n";
        echo "</tr>\n";
        
    }
    echo "</table>\n";
    

}    
    
?>
</div>
</body>
</html>
