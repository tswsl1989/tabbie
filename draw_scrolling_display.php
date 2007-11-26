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
require_once("includes/backend.php");
$roundno=@$_GET['roundno'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Draw : Round <?= $roundno ?></title>    
    <link rel="stylesheet" href="view/scrolling/css/jScrollPane.css" type="text/css" charset="utf-8"/>
    <link rel="stylesheet" href="view/scrolling/css/scrollpage.css" type="text/css" charset="utf-8"/>
    <script type="text/javascript" charset="utf-8" src="view/scrolling/javascripts/jquery.js"></script>
    <script type="text/javascript" charset="utf-8" src="view/scrolling/javascripts/jquery.dimensions.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="view/scrolling/javascripts/jScrollPane.min.js"></script>    
    <script type="text/javascript" charset="utf-8" src="view/scrolling/javascripts/jquery.timer.js"></script>        
    <script type="text/javascript" charset="utf-8" src="view/scrolling/javascripts/scroller.js"></script>    
</head>

<body>
    <h2>Draw : Round <?= $roundno ?></h2>
    <div id="scrolldisplay" rel="10"> <!-- Start of scrolldisplay -->
        <table>
            <tbody>
                <tr>
                    <th>
                        Team Name
                    </th>
               </tr>     
<?php

$db_result = mysql_query(
    "SELECT T.team_id, univ_code, team_code, univ_name, S1.speaker_name " .
    "AS speaker1, S2.speaker_name AS speaker2, esl, active, composite " .
    "FROM university AS U, team AS T, speaker AS S1, speaker AS S2 " .
    "WHERE T.univ_id=U.univ_id AND S1.team_id=T.team_id AND " . 
    "S2.team_id=T.team_id AND S1.speaker_id<S2.speaker_id  " .
    "ORDER BY univ_code, team_code ");

    
    while ($row = mysql_fetch_assoc($db_result)) {
        print "<tr><td>{$row['univ_code']} {$row['team_code']}</td>";
        $query = "SELECT debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location ";
        $query .= "FROM draw_round_$roundno, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue ";
        $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND draw_round_$roundno.venue_id=venue.venue_id AND (og = {$row['team_id']} OR oo = {$row['team_id']} OR cg = {$row['team_id']} OR co = {$row['team_id']})"; 
        $result=mysql_query($query);
        $row_debate=mysql_fetch_assoc($result);
                 $debate_id = $row_debate['debate_id'];
                 $adj_query = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
                 $adj_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
                 $adj_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND status = 'chair' ";
                 $adj_result=mysql_query($adj_query);

                 $adj_row=mysql_fetch_assoc($adj_result);

                 echo "<td>{$row_debate['venue_name']}</td>\n";
                 echo "<td>{$row_debate['ogtc']} {$row_debate['ogt']}</td>\n";
                 echo "<td>{$row_debate['ootc']} {$row_debate['oot']}</td>\n";
                 echo "<td>{$row_debate['cgtc']} {$row_debate['cgt']}</td>\n";
                 echo "<td>{$row_debate['cotc']} {$row_debate['cot']}</td>\n";
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
?>                    
                    

        </table>
    
    </div> <!-- End of scrolldisplay -->
<body>
</html>    