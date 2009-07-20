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
require_once("includes/display.php");
require_once("includes/backend.php");
require_once("includes/teamstanding.php");
require_once("draw/adjudicator/simulated_annealing_config.php");

function ap_cmp(&$debate1, &$debate2){
	if($debate1==$debate2){
		return strnatcmp($debate1['venue'], $debate2['venue']);
	}
	return ($debate1['average_points'] < $debate2['average_points']) ? 1 : -1;
}

$lastround=get_num_rounds();
$round=$lastround+1;

$title="Draw : Round ".$round;
$query="SHOW TABLES LIKE 'temp%round_$round'";
$result=mysql_query($query);

echo "<h2>$title</h2>";
if ((mysql_num_rows($result))!=2) //both or one of the tables don't exist
  {
    $msg[]="Missing tables in database!!";
    $msg[]="Please calculate the draw first!!";
	displayMessagesUL(@$msg);
  } else {
?><h3><a href="draw.php?moduletype=manualdraw&amp;action=finalise">Finalize the draw</a></h3><?php
	$query="SELECT A1.debate_id AS debate_id, CONCAT(U1.univ_code, ' T', T1.team_code) AS og_name, CONCAT(U2.univ_code, ' T', T2.team_code) AS oo_name, CONCAT(U3.univ_code, ' T', T3.team_code) AS cg_name, CONCAT(U4.univ_code, ' T', T4.team_code) AS co_name, CONCAT(venue_name) AS venue, T1.team_id AS og_id, T2.team_id AS oo_id, T3.team_id AS cg_id, T4.team_id AS co_id FROM temp_draw_round_$round AS A1, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4, venue WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND A1.venue_id=venue.venue_id ORDER BY venue_name";
	$result=mysql_query($query);
	$team_array=team_standing_array($lastround);
	/*$params=get_scoring_factors_from_db();
	$main_breaking_teams=$params['main_breaking_teams'];
	$esl_breaking_teams=$params['esl_breaking_teams'];
	$efl_breaking_teams=$params['efl_breaking_teams'];
	$rounds_remaining=$params['number_of_rounds']-$lastround;*/
	//Strategy is to increment counters as we iterate through the array
	//We also need the array to be keyed by team_id. Iterating is a horrible kludge but I don't know a better solution ?!
	$main_break_counter=0;
	$esl_break_counter=0;
	$efl_break_counter=0;
	$new_team_array=array();
	foreach($team_array as $team){
		/*
		if($team['lowest_break']="EFL"){
			$main_break_counter++;
			$esl_break_counter++;
			$efl_break_counter++;
		} else if($team['lowest_break']="ESL"){
			$main_break_counter++;
			$esl_break_counter++;
		} else {
			$main_break_counter++;
		}
		if($main_break_counter==$main_breaking_teams){
			$main_break_lowest_points=max(($team['points']-($rounds_remaining*3)),0);
		}
		if($esl_break_counter==$main_breaking_teams){
			$esl_lowest_points=max(($team['points']-($rounds_remaining*3)),0);
		}
		if($main_break_counter==$main_breaking_teams){
			$efl_lowest_points=max(($team['points']-($rounds_remaining*3)),0);
		}*/
		$new_team_array[$team['teamid']]=$team;
	}
	$debate_array=array();
	while($row=mysql_fetch_assoc($result)){
		$debate_array[$row['debate_id']]=$row;
		$debate_array[$row['debate_id']]['average_points']=(($new_team_array[$row['og_id']]['score']+$new_team_array[$row['oo_id']]['score']+$new_team_array[$row['cg_id']]['score']+$new_team_array[$row['co_id']]['score'])/4);
		//$debate_array[$row['debate_id']]['live']=array();
		/*$debate_array[$row['debate_id']]['adjudicators']=array();
		if(
			( ($new_team_array[$row['og_id']]['lowest_break']=='EFL') && ($new_team_array[$row['og_id']]['score']>=$efl_lowest_points) ) ||
			( ($new_team_array[$row['oo_id']]['lowest_break']=='EFL') && ($new_team_array[$row['oo_id']]['score']>=$efl_lowest_points) ) ||
			( ($new_team_array[$row['cg_id']]['lowest_break']=='EFL') && ($new_team_array[$row['cg_id']]['score']>=$efl_lowest_points) ) ||
			( ($new_team_array[$row['co_id']]['lowest_break']=='EFL') && ($new_team_array[$row['co_id']]['score']>=$efl_lowest_points) ) 
			)
		{
			$debate_array[$row['debate_id']]['live']['EFL']=true;
		}
		if(
			//ESL breaking teams are those that are not N - ie are ESL and EFL
			( ($new_team_array[$row['og_id']]['lowest_break']!='N') && ($new_team_array[$row['og_id']]['score']>=$esl_lowest_points) ) ||
			( ($new_team_array[$row['oo_id']]['lowest_break']!='N') && ($new_team_array[$row['oo_id']]['score']>=$esl_lowest_points) ) ||
			( ($new_team_array[$row['cg_id']]['lowest_break']!='N') && ($new_team_array[$row['cg_id']]['score']>=$esl_lowest_points) ) ||
			( ($new_team_array[$row['co_id']]['lowest_break']!='N') && ($new_team_array[$row['co_id']]['score']>=$esl_lowest_points) ) 
			)
		{
			$debate_array[$row['debate_id']]['live']['ESL']=true;
		}
		if(
			( ($new_team_array[$row['og_id']]['score']>=$main_break_lowest_points) ) ||
			( ($new_team_array[$row['oo_id']]['score']>=$main_break_lowest_points) ) ||
			( ($new_team_array[$row['cg_id']]['score']>=$main_break_lowest_points) ) ||
			( ($new_team_array[$row['co_id']]['score']>=$main_break_lowest_points) ) 
			)
		{
			$debate_array[$row['debate_id']]['live']['MB']=true;
		}
		*/
		$adjud_query="SELECT adjudicator.adjud_id AS id, adjudicator.ranking AS ranking, `temp_adjud_round_$round`.`status` AS status, adjudicator.adjud_name AS name, adjudicator.status AS trainee FROM `temp_adjud_round_$round` INNER JOIN adjudicator ON adjudicator.adjud_id = temp_adjud_round_$round.adjud_id WHERE debate_id = '".$row['debate_id']."' ORDER BY `status` ASC";
		$adjudresult=mysql_query($adjud_query);
		echo mysql_error();
		while($adjud_row=mysql_fetch_assoc($adjudresult)){
			$adjud_result=array();
			$adjud_result['id']=$adjud_row['id'];
			$adjud_result['name']=$adjud_row['name'];
			$adjud_result['ranking']=$adjud_row['ranking'];
			$adjud_result['status']=$adjud_row['status'];
			$adjud_result['trainee']=$adjud_row['trainee'];
			if(is_four_id_conflict($adjud_row['id'], $row['og_id'], $row['oo_id'], $row['cg_id'], $row['co_id'])){
				$adjud_result['strike']=true;
			}else{
				$adjud_result['strike']=false;
			}
			$debate_array[$row['debate_id']]['adjudicators'][]=$adjud_result;
		}
	}
	uasort(&$debate_array,'ap_cmp'); //Sorts by average points
	
		
	?>
	<p><button class="resetbutton">Reset table</button><button class="rankingbutton">Show/hide rankings</button></p>
	<table class='bigtable'>
	<th>
		<th>Points</th>
		<th>OG</th>
		<th>OO</th>
		<th>CG</th>
		<th>CO</th>
		<th>Adjudicators</th>
	</th>
	<?php
	foreach($debate_array as $debate){
		?>
		<tr id='D<?= $debate['debate_id'] ?>'>
			<td class='venue'> <?= $debate['venue'] ?></td>
			<td class='points'>
			<?php
			echo($debate['average_points']);
			/*if($debate['live']['MB']){
				echo(" MB");
			}
			if($debate['live']['ESL']){
				echo(" ESL");
			}
			if($debate['live']['EFL']){
				echo(" EFL");
			}
			*/?>
			</td>
			<td class='team' id='<?= $debate[og_id]?>'><?= $debate[og_name]?></td>
			<td class='team' id='<?= $debate[oo_id]?>'><?= $debate[oo_name]?></td>
			<td class='team' id='<?= $debate[cg_id]?>'><?= $debate[cg_name]?></td>
			<td class='team' id='<?= $debate[co_id]?>'><?= $debate[co_name]?></td>
			<td><ul class='judgelist' id='J<?=$debate[debate_id]?>'>
			<?php
			/* WARNING: this code MUST be kept functionally equivalent to ajax/draw/dragdraw.js 
			as both files update the same part of the DOM */
			foreach($debate['adjudicators'] as $adjudicator){
				echo("<li id='A".$adjudicator['id']."' ");
				echo("class='adjudicator");
				if($adjudicator['status']=='chair'){echo(" chair");}
				if($adjudicator['trainee']=='trainee'){echo(" trainee");}
				if($adjudicator['strike']){echo(" strike");}
				echo("'>".$adjudicator['name']);
				echo(" <span class='ranking'>".$adjudicator['ranking']."</span></li>");
			}
			?>
			</ul></td>
		</tr>
	<?php
	} ?>
</table>
<h3>Free adjudicators</h3>
<table class="bigtable"><tr id='DFREE'><td><ul class='judgelist' id='JFREE'></ul></td></tr></table>
<!--<p>The 'points' column shows the average points of teams in that room, and the breaks for which that room is considered to be 'live'.</p>
<p>The Main Break (MB) is live for teams with at least <?=$main_break_lowest_points?> points.</p>
<p>The ESL break is live for teams with at least <?=$esl_lowest_points?> points.</p>
<p>The EFL break is live for teams with at least <?=$efl_lowest_points?> points.</p>
<p>A team is defined as 'live' for a break category when the current total of the team points for that team are not less than the result of deducting the product of the number of rounds remaining and three from the total of the team points for the team currently in the lowest breaking position in that break category. A room is defined as 'live' for a break category when it has at least one team that is 'live' for that break category within it. Adjudicators and tab directors are cautioned that there is no necessary correlation between a room being 'live' and the difficulty of adjudicating that room or the calibre of adjudicator required to ensure a reasonable result. The number of teams breaking in each break category, and the total number of rounds in the tournament, must be correctly set in the adjudication parameters screen.</p>-->
<?php } ?>