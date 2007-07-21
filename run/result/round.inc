<?
require("includes/display.inc");

//Calculate Round Number (should have been already validated)
$action=@$_GET['action'];
$roundno=@$_GET['roundno'];

$validate=1;
if ((!$roundno)||(!ereg("^[1-9]$",$roundno)) )
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

displayMessagesP(@$msg);

//Display draw information

    $query = "SELECT R.debate_id AS debate_id, U1.univ_code AS oguniv, T1.team_code AS ogt, U2.univ_code AS oouniv, T2.team_code AS oot, U3.univ_code AS cguniv, T3.team_code AS cgt, U4.univ_code AS couniv, T4.team_code AS cot, D.venue_id AS venue_id, V.venue_name AS venue_name  ";
    $query .= "FROM result_round_$roundno R, team T1, university U1, team T2, university U2, team T3, university U3, team T4, university U4, draw_round_$roundno D, venue V  ";
    $query .= "WHERE R.debate_id LIKE ('$roundno%' ) AND R.FIRST = T1.team_id AND T1.univ_id = U1.univ_id AND R.SECOND = T2.team_id AND T2.univ_id = U2.univ_id AND R.THIRD = T3.team_id AND T3.univ_id = U3.univ_id AND R.FOURTH = T4.team_id AND T4.univ_id = U4.univ_id AND D.debate_id = R.debate_id  AND V.venue_id = D.venue_id ";
    $query .= "ORDER BY venue_name ";

    $result=mysql_query($query);

    echo mysql_error();
    echo "<table>\n";
    echo "<tr><th>Venue</th><th>First</th><th>Second</th><th>Third</th><th>Fourth</th></tr>\n";

    while($row=mysql_fetch_assoc($result))
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