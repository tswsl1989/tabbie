<?
//Purpose :   Display the draws of the previous rounds in the tournament.

require("includes/display.inc");

//Calculate Round Number (should have been already validated)
$action=@$_GET['action'];
$roundno=@$_GET['roundno'];

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

include("header.inc");

echo "<div id=\"content\">\n";
echo "<h2>$title</h2>\n"; //title

displayMessagesP(@$msg);

//Display draw information

    $query = "SELECT debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location ";
    $query .= "FROM draw_round_$roundno, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue ";
    $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND draw_round_$roundno.venue_id=venue.venue_id "; 
    $query .= "ORDER BY venue_name";
    $result=mysql_query($query);
    echo mysql_error();

    echo "<table>\n";
    echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Chair</th><th>Panelists</th><th>Trainee</th></tr>\n";

    while($row=mysql_fetch_assoc($result))
    {
        echo "<tr>\n";
            $debate_id = $row['debate_id'];
            $adj_query = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
            $adj_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
            $adj_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND status = 'chair' ";
            $adj_result=mysql_query($adj_query);

            $adj_row=mysql_fetch_assoc($adj_result);
            
            echo "<td>{$row['venue_name']}</td>\n";
            echo "<td>{$row['ogtc']} {$row['ogt']}</td>\n";
            echo "<td>{$row['ootc']} {$row['oot']}</td>\n";
            echo "<td>{$row['cgtc']} {$row['cgt']}</td>\n";
            echo "<td>{$row['cotc']} {$row['cot']}</td>\n";
            echo "<td>{$adj_row['adjud_name']}</td>\n";
            
            echo "<td>";
            $pan_query = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
        $pan_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
        $pan_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND status = 'panelist' ";
        $pan_result=mysql_query($pan_query);
        echo mysql_error();

        $num_panelists=mysql_num_rows($pan_result);
        if (@$numpanelists > 0) echo "<ul>\n";
        while($pan_row=mysql_fetch_assoc($pan_result))
        {    
          echo "<li>{$pan_row['adjud_name']}</li>";
        }
        if (@$numpanelists > 0) echo "</ul>\n";
        echo "</td>\n";

            echo "<td>";
            $trainee_query = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
        $trainee_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
        $trainee_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND status = 'trainee' ";
        $trainee_result=mysql_query($trainee_query);
        echo mysql_error();

        $num_trainee=mysql_num_rows($trainee_result);
        if (@$numtrainee > 0) echo "<ul>\n";
        while($trainee_row=mysql_fetch_assoc($trainee_result))
        {    
          echo "<li>{$trainee_row['adjud_name']}</li>";
        }
        if (@$numtrainee > 0) echo "</ul>\n";
        echo "</td>\n";

       echo "</tr>\n";
        
    }

    echo "</table>\n";
?>
</div>
<?php include('customize/footer.inc'); ?>
</body>
</html>