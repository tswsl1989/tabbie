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
require_once("draw/adjudicator/simulated_annealing_config.php");
require("includes/display.php");

$submited=trim(@$_POST['save']);
if ($submited=="submitted") {
  foreach ($scoring_factors as $pname=>$pvalue) {
    $scoring_factors[$pname]=trim(@$_POST["param_".$pname]);
  }
  store_scoring_factors_to_db($scoring_factors);
}

echo "<h2>Adjust adjudicator allocation parameters</h2>\n"; //title
echo "<form action=\"input.php?moduletype=adjud_params\" method=POST><table border=1><tr><th>Parameter name</th><th>parameter value</th></tr>";
//iterate over params:
foreach ($scoring_factors as $pname => $pvalue) {
  printf("<tr><td>%s</td><td><input name=\"param_%s\" value=\"%01.1f\"></td></tr>\n",$pname,$pname,$pvalue);
}
printf("</table>");
//echo "<input type=\"hiddedn\" value=\"adjud_params\">";
echo "<button name=\"save\" type=\"submit\" value=\"submitted\">save</button>";

?>
<h3>What does this mean?</h3>
<p>
This way of adjudicator allocation (simulated annealing) is a rather new feature of Tabbie, but a very powerful one. In the near future we hope to add some fixed configurations for different kinds of tournaments and different stages in the tournament. For now, you'll have to fiddle with the parameters yourself. Remember, the algorithm tries to optimize for low scores. This means all the values here are penalty values - the higher you set them, the more the algorithm will try to avoid a specific condition to happen. Also, the parameters are not normalized - this means "1" in one parameter may mean something as powerful as "1000" in another.


<p>
university_conflict: Penalty for each meeting of a team and an adjudicator from that team's uni.<br/>
High values: Uni- conflicts will occur less<br/>
Low values: (Down to 0): Uni conflicts matter less
</p><p>
team_conflict: Penalty for each meeting of a team and an adjudicator that has a specificcally entered conflict with that team.<br/>
High values: Conflicts will occur less<br/>
Low values: (Down to 0): These onflicts matter less
</p><p>
chair_not_perfect: Penalty for each chair of less quality than 100. Total penalty = penalty * (100 - real value)<br/>
High values: The best adjudicators will all be chairs<br/>
Low values: Having the best people in chair is not as important
</p><p>
chair_not_ciaran_perfect: Total penalty = penalty * abs(100 - ciaran_desired_value)<br/>
High values: The best adjudicator will be chair in the best debate, the second best will be chair in the second best debate etc.<br/>
Low values: Having the best chairs in the best debates is not as important
</p><p>
"panel_steepness": Value between 0 and 1. Reflecting the relation between panel strength and debate strength.<br/>
High values: Up to 1: Debate strength strictly relates to panel strength.<br/>
Low values: All debates are considered equal.<br/>
Further remarks: Slowly increasing this value during the tournament is expected to have a positive effect on the tournament.
</p><p>
panel_strength_not_perfect: Penalty for distance to this 'ideal average'.<br/>
High values: Emphasis on getting panels on the 'right strenght'<br/>
Low values: Not so much emphasis on getting panels on the 'right strenght'<br/>
</p><p>
adjudicator_met_adjudicator: Penalty for adjudicator meeting each other again. This penalty is multiplied by the 
times these two already were in one panel together.<br/>
High values: Adjudicators are not put in panels with previous co-panellists<br/>
</p><p>
adjudicator_met_team: Penalty for adjudicator meeting a team they have adjudcicated before again. This penalty is multiplied by the times this has occurred before.<br/>
High values: Adjudicators are not to adjudicate the same teams again and again<br/>
</p><p>
lock: (Maybe a bit misplaced in this table, but anyway) If this lock is on a non-zero value the algorithms can not be run. This is to prevent two different computers from hitting the draw buttons at the same time and messing things up big time. If for some reason the lock is set to one, it can be removed here.<br/>
</p>
