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
require_once("draw/adjudicator/simulated_annealing_config.php");
$roundno=@$_GET['roundno'];
$slide=@$_GET['slide'];
$norefresh=@$_GET['norefresh'];
	$query="SELECT COUNT(*) FROM `draw_round_$roundno` WHERE 1";
	$result=mysql_query($query);
	$row=mysql_fetch_array($result);
	$maxrooms=$row["COUNT(*)"];
$refreshspeed=$scoring_factors["draw_table_speed"];

$intslide=intval($slide);
if($intslide > $maxrooms) $slide="premotion";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8"/>
	<?php
	if((0 < $intslide) && ($intslide <= $maxrooms) && (!$norefresh)) 
	{
		$newslide=$slide+1;
		echo("<meta http-equiv='refresh' content='".$refreshspeed.";draw_table_display2.php?roundno=$roundno&slide=$newslide'/>");
	}
	?>
	<link rel="stylesheet" href="view/table/css/tablepageold.css" type="text/css" charset="utf-8"/>
    <title>Draw : Round <?= $roundno ?></title>    
</head>

<body>
	<div class="canvas">
		<div class="header">
			Draw for Round <?= $roundno ?>
		</div>
		<?php
		if($slide == "0") {
			echo("<div class='notice'><a href='draw_table_display2.php?roundno=".$roundno."&slide=1'>Draw Round</a></div>");
		} elseif($slide=="premotion") {
			echo("<div class='notice'><a href='draw_table_display2.php?roundno=".$roundno."&slide=motion'>Motion</a></div>");
		} elseif($slide=="motion") {
			echo("<div class='notice'>" . get_motion_for_round($roundno) . "</div>");
		} else {
			?><div class='container'><?php
			$query = "SELECT * FROM `draw_round_$roundno` ORDER BY venue_id ASC";
			$db_result=mysql_query($query);
			for($i=1;$i<$intslide;$i++)
			  {
				$row=mysql_fetch_assoc($db_result);
			  }	
			$row = mysql_fetch_array($db_result);
			echo("<table class='roomtable'><tr>");
			echo("<td colspan='3'>".venue_name($row["venue_id"])."</td></tr>");
			echo("<tr><td></td><td>Proposition</td><td>Opposition</td></tr>");
			echo("<tr><td>1st</td><td>".team_code_long_table($row["og"])."</td>");
			echo("<td>".team_code_long_table($row["oo"])."</td></tr>");
			echo("<tr><td>2nd</td><td>".team_code_long_table($row["cg"])."</td>");
			echo("<td>".team_code_long_table($row["co"])."</td></tr>");
			
			$debate_id = $row['debate_id'];
			$db_result = mysql_query("SELECT * FROM `adjud_round_$roundno` WHERE `debate_id` = $debate_id AND `status` = CONVERT ( _utf8 'chair' USING latin1)");
			$row = mysql_fetch_array($db_result);
			echo("<tr><td colspan='3'>".adjudicator_name($row['adjud_id'])."</td></tr>");
			$db_result = mysql_query("SELECT * FROM `adjud_round_$roundno` WHERE `debate_id` = $debate_id AND `status` = CONVERT ( _utf8 'panelist' USING latin1)");
			if(mysql_num_rows($db_result)>0){
				echo ("<tr><td colspan='3'>");
				while($row=mysql_fetch_assoc($db_result)) {
					echo(adjudicator_name($row["adjud_id"]) . ", ");
				}
				echo ("</td></tr>");
			}
			$db_result = mysql_query("SELECT * FROM `adjud_round_$roundno` WHERE `debate_id` = $debate_id AND `status` = CONVERT ( _utf8 'trainee' USING latin1)");
			if(mysql_num_rows($db_result)>0){
				echo ("<tr><td colspan='3'>");
				while($row=mysql_fetch_assoc($db_result)) {
					echo(adjudicator_name($row["adjud_id"]) . ", ");
				}
				echo ("</td></tr>");
			}
		}

?>                        
    </table></div></div><div class="push"></div> <!-- End of scrolldisplay -->
	<div class="footer"><!-- BEGIN class footer-->
		Created with Tabbie, see <a href="http://smoothtournament.com">http://smoothtournament.com</a> and <a href="http://tabbie.wikidot.com">http://tabbie.wikidot.com</a>. Maintained by the Cambridge Union.
	</div><!-- END class footer-->
</body>
</html>