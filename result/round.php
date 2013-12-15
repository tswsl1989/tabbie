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

require("includes/display.php");
require("includes/backend.php");

//Calculate Round Number (should have been already validated)
$action=@$_GET['action'];
$roundno=@$_GET['roundno'];

$validate=1;
if ( ($roundno=='')||!((intval($roundno)>0)&&(intval($roundno)<10)) )
{
    $roundno=$numdraws;
    if ($numdraws<=0) {
        require_once("includes/http.php");
        redirect("result.php?moduletype=currentround");
    }
    
}

                
switch($action)
{
    case "showresult":    $title="Results : Round $roundno";
                        break;
    default:
                        
                        $title="Results : Round $roundno";
                        $action="showresult";
                        break;
        

}

echo "<h2>$title</h2>\n"; //title

if(isset($msg)) displayMessagesP(@$msg);

//Display draw information

    $query = "SELECT R.debate_id AS debate_id, U1.univ_code AS oguniv, T1.team_code AS ogt, U2.univ_code AS oouniv, T2.team_code AS oot, U3.univ_code AS cguniv, T3.team_code AS cgt, U4.univ_code AS couniv, T4.team_code AS cot, D.venue_id AS venue_id, V.venue_name AS venue_name  ";
    $query .= "FROM results R, team T1, university U1, team T2, university U2, team T3, university U3, team T4, university U4, draws D, venue V  ";
    $query .= "WHERE D.round_no=? AND R.round_no=? AND R.debate_id BETWEEN ? AND ? AND R.FIRST = T1.team_id AND T1.univ_id = U1.univ_id AND R.SECOND = T2.team_id AND T2.univ_id = U2.univ_id AND R.THIRD = T3.team_id AND T3.univ_id = U3.univ_id AND R.FOURTH = T4.team_id AND T4.univ_id = U4.univ_id AND D.debate_id = R.debate_id  AND V.venue_id = D.venue_id ";
    $query .= "ORDER BY venue_name ";

    $result=qp($query,array($roundno, $roundno, $roundno*10000, ($roundno+1)*10000));

    echo "<table>\n";
    echo "<tr><th>Venue</th><th>First</th><th>Second</th><th>Third</th><th>Fourth</th></tr>\n";

    while($row=$result->FetchRow())
    {
        echo "<tr>\n";
            echo "<td>{$row['venue_name']} </td>\n";
            echo "<td>{$row['oguniv']} {$row['ogt']}</td>\n";
            echo "<td>{$row['oouniv']} {$row['oot']}</td>\n";
            echo "<td>{$row['cguniv']} {$row['cgt']}</td>\n";
            echo "<td>{$row['couniv']} {$row['cot']}</td>\n";
       echo "</tr>\n";
        
    }

    echo "</table>\n";
?>
