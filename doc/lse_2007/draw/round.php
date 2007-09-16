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

//Calculate Round Number (should have been already validated)
$action=$_GET['action'];
$roundno=$_GET['roundno'];

$validate=1;
if (($roundno=='')||(!ereg("^[1-9]$",$roundno)) )
{

  $roundno=$numdraws;
    if ($numdraws<=0) 
        header("Location: draw.php?moduletype=currentdraw");
}
                
switch($action)
{
    case "showdraw":    $title="Draw : Round $roundno";
                        break;
    default:
                        
                        $title="Draw : Round $roundno";
                        $action="showdraw";
                        break;
}

include("./lse_dev/draw/header.php");

echo "<div id=\"content\">\n";
echo "<h2>$title</h2>\n"; //title


//Display Messages
for($x=0;$x<count($msg);$x++)
    echo "<p class=\"err\">".$msg[$x]."</p>\n";


//Display draw information

    $query = "SELECT debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, venue_name, venue_location, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ";
    $query .= "FROM draw_round_$roundno, team T1, team T2, team T3, team T4, venue ";
    $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND draw_round_$roundno.venue_id=venue.venue_id "; 
    $query .= "ORDER BY venue_name";
    $result=mysql_query($query);
    echo mysql_error();



    echo "<table>\n";
    echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Total Points</th></tr>\n";


while($row=mysql_fetch_assoc($result))

	      {		
               	$ogpoints=0;

		for ($i=1; $i<$roundno; $i++)
		  { $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ogid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $ogpoints = $ogpoints + 3;
				
		    $pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['ogid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $ogpoints = $ogpoints + 2;
				
		    $pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['ogid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $ogpoints = $ogpoints + 1;
		  }

              	$oopoints=0;
               	for ($i=1; $i<$roundno; $i++)
		  {	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ooid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $oopoints = $oopoints + 3;
		
		    $pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['ooid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $oopoints = $oopoints + 2;
				
		    $pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['ooid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $oopoints = $oopoints + 1;
		  }

                $cgpoints=0;
                for ($i=1; $i<$roundno; $i++)
		  {	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['cgid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $cgpoints = $cgpoints + 3;
				
		    $pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['cgid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $cgpoints = $cgpoints + 2;
		
		    $pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['cgid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $cgpoints = $cgpoints + 1;
		  }
               	$copoints=0;
               	for ($i=1; $i<$roundno; $i++)
		  {	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['coid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $copoints = $copoints + 3;
				
		    $pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['coid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $copoints = $copoints + 2;
				
		    $pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['coid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $copoints = $copoints + 1;
		  }

                
		
		  
	
		$totalpoints = $ogpoints + $oopoints + $cgpoints + $copoints;


              	echo "<td>{$row['venue_name']}</td>\n";
		echo "<td>{$row['ogtc']} {$row['ogt']} <br/> ($ogpoints) </td>\n";
		echo "<td>{$row['ootc']} {$row['oot']} <br/> ($oopoints) </td>\n";
		echo "<td>{$row['cgtc']} {$row['cgt']} <br/> ($cgpoints) </td>\n";
                echo "<td>{$row['cotc']} {$row['cot']} <br/> ($copoints) </td>\n";
                echo "<td>$totalpoints </td>\n";                

              
                
           echo "</tr>\n";
}
    
?>
</div>
</body>
</html>
