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

// KVS - not a priority - isn't this way to much data for the DCA's to fully understand?

function present($adjud_id,$adj_array)
{
  for($x=0;$x<count($adj_array);$x++)
      if ($adj_array[$x][0]==$adjud_id) return $x;
  return -1;
  
 }
include("includes/dbconnection.php"); //Database Connection

$action=@$_GET['action'];

$roundno = $numdraws;

if (!$roundno)
{   
    $msg[]="No draws have been finalised as yet.";
    $msg[]="This module is used only after the tournament has started.";
    $msg[]="<br/>";
    $action = "error";
}

include("header.php");

switch($action)
{
    case "display":     break;
    case "error":       $title.=" - Error";
                        break;
    default:
                        $action="display";
                        break;
}

$title.=" (Round: $roundno)";
echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

if ($action == "error")
{
    //Display Messages
    for($x=0;$x<count($msg);$x++)
        echo "<p class=\"err\">".$msg[$x]."</p>\n";
}
else
{
if ($action == "display")
{

    //Open the text file
    $filename = "print/outputs/TeamAdjList_$roundno.html";
    $fp = fopen($filename, "w");
    
    $text="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"> \n<html> \n <head> ";
    $text.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/> \n";
    $text.=" </head> \n <body> \n";
    $text.=" <h2>$title</h2><br/>\n\n";
    $text.=" <table name=\"teamadj\" border=\"1\" width=\"100%\"> \n";
    $text.=" <tr><th>Team</th><th>Adjudicator</th><th>Count</th></tr> \n";
    fputs($fp,$text);
    
    $teamquery = "SELECT t.team_id AS team_id, t.team_code AS team_code, u.univ_name AS univ_name FROM team AS t, university AS u WHERE u.univ_id=t.univ_id ORDER BY univ_name, team_code ";
    $teamresult = mysql_query($teamquery);
    while ($teamrow=mysql_fetch_assoc($teamresult))
    {    $team_name = $teamrow['univ_name'].' '.$teamrow['team_code'];
        $team_id = $teamrow['team_id'];
        $text="<tr><td>$team_name</td><td>";
        unset($adj_array);
        $index=0;
        for ($i=1;$i<=$roundno;$i++)
        {    $query="SELECT a.adjud_id AS adjud_id FROM draw_round_$i AS d, adjud_round_$i AS a ";
            $query.="WHERE (d.og='$team_id' OR d.oo='$team_id' OR d.cg='$team_id' OR d.co='$team_id') AND a.debate_id=d.debate_id ";
            $result=mysql_query($query);
            if ($result)
            {    while ($row=mysql_fetch_assoc($result))
                {    $adjud_id=$row['adjud_id'];
                    if ((present($adjud_id,@$adj_array))==-1)
                    
                    {    $adj_array[$index][0]=$adjud_id;
                        $adj_array[$index][1]=1;
                        $index++;
                    }
                    
                    else
                    {
                          $adj_array[present($adjud_id,$adj_array)][1]+=1;
                    }
                    
                }
            }
        }
        $adjud_text="";
        $count_text="";
        for ($y=0; $y<count(@$adj_array); $y++)
        {    $query="SELECT a.adjud_name AS adjud_name FROM adjudicator AS a WHERE a.adjud_id='{$adj_array[$y][0]}' ";
            $result=mysql_query($query);
            $row=mysql_fetch_assoc($result);
            $adjud_name=$row['adjud_name'];
            $adjud_text.="$adjud_name<br/>";
            $count_text.=$adj_array[$y][1]."<br/>";
        }
        $text.=$adjud_text."</td><td>".$count_text."</td></tr>";
        fputs($fp,$text);
    }

    $text=" <table>\n</body>\n</html>\n";
    fputs($fp,$text);
    fclose($fp);
    echo "<h3>File created successfully!</h3> ";
    echo "<h3><a href=\"$filename\">Team-Adjudicator Count for Round $roundno</a></h3> ";
}
}

?>
</div>
</body>
</html>
