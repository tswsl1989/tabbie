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

$submitted="";
if(array_key_exists("save", @$_POST)) $submitted=trim(@$_POST['save']);
if ($submitted) {
  foreach ($scoring_factors as $pname=>$pvalue) {
    $scoring_factors[$pname]=trim(@$_POST["param_".$pname]);
  }
  store_scoring_factors_to_db($scoring_factors);
}

echo "<h2>Adjust adjudicator allocation parameters</h2>\n"; //title
echo "<form action=\"input.php?moduletype=adjud_params\" method=POST><table><tr><th>Parameter name</th><th>parameter value</th></tr>";
//iterate over params:
foreach ($scoring_factors as $pname => $pvalue) {
  printf("<tr><td>%s</td><td><input name=\"param_%s\" value=\"%01.1f\"></td></tr>\n",$pname,$pname,$pvalue);
}
printf("</table>");
//echo "<input type=\"hiddedn\" value=\"adjud_params\">";
echo "<button name=\"save\" type=\"submit\" value=\"submitted\">save</button>";
echo "</form>";

?>
<h3>What does this mean?</h3>
<p>
This way of adjudicator allocation (simulated annealing) is a rather new feature of Tabbie, but a very powerful one. In the near future we hope to add some fixed configurations for different kinds of tournaments and different stages in the tournament. For now, you'll have to fiddle with the parameters yourself. Remember, the algorithm tries to optimize for low scores. This means all the values here are penalty values - the higher you set them, the more the algorithm will try to avoid a specific condition to happen. Also, the parameters are not normalized - this means "1" in one parameter may mean something as powerful as "1000" in another.


<p>
<b>university_conflict</b>: Penalty for each meeting of a team and an adjudicator from that team's uni.<br/>
High values: Uni- conflicts will occur less<br/>
Low values: (Down to 0): Uni conflicts matter less
</p><p>
<b>team_conflict</b>: Penalty for each meeting of a team and an adjudicator that has a specificcally entered conflict with that team.<br/>
High values: Conflicts will occur less<br/>
Low values: (Down to 0): These onflicts matter less
</p><p>
<b>chair_not_perfect</b>: Penalty for each chair of less quality than 100. Total penalty = penalty * (100 - real value)<br/>
High values: The best adjudicators will all be chairs<br/>
Low values: Having the best people in chair is not as important
</p><p>
<b>chair_not_ciaran_perfect</b>: Quadratical difference with 'ciaran desired value'<br/>
High values: The best adjudicator will be chair in the best debate, the second best will be chair in the second best debate etc.<br/>
Low values: Having the best chairs in the best debates is not as important<br/>

"Ciaran Ideals" are implemented as brackets. This means that if you have a number of equally strong debates the "perfect chair" for that debate may be any of many values, like so:<br/>
<br/>
Debate points averages: 19, 19, 19, 18, 18, 17......<br/>
Adjudicators: 100, 95, 92, 90, 88, 84, 70<br/>
<br/>
A: (19) values between: 92 and 100 are ideal strength for chair<br/>
B: (19) values between: 92 and 100 are ideal strength for chair<br/>
C: (19) values between: 92 and 100 are ideal strength for chair<br/>
D: (18) values between: 88 and 90 are ideal strength for chair<br/>

</p><p>
<b>"panel_steepness"</b>: Value between 0 and 1. Reflecting the relation between panel strength and debate strength.<br/>
High values: Up to 1: Debate strength strictly relates to panel strength.<br/>
Low values: Down to 0: All debates are considered equal. (Note that with better chairs in the good debates this will actually give you worse pannellists in these debates to balance the chairs out)<br/>
<br/>
Panels are allocated a desired strength. This strength is based on the "panel steepness". With a steepness of 1, the desired strength is directly correlated to the strength of the debate, if it's 0 all debates are desired to be of equal strength.<br/>
<br/>
Say you have three debates:<br/>
2, 3, 5 points avg. total is 10<br/>
average nr. of points is 3.33<br/>
<br/>
the avg. of the adjudicator strength is 56.<br/>
now,
with steepness of 1, the desired averages are:<br/>
<br/>
2/3.33 * 56 = 33.6<br/>
3/3.33 * 56 = 50.4<br/>
5/3.33 * 56 = 84.0<br/>
<br/>
with a steepness of 0, the desired averages are<br/>
56, 56, 56<br/>
<br/>
How bad it is that a panel isn't on its desired strength is regulated with the parameter panel_strength_not_perfect<br/>
<br/>
Further remarks: Slowly increasing this value during the tournament is expected to have a positive effect on the tournament. I.e. you slowly move the best adjudicators to the best debates.
</p><p>
<b>panel_strength_not_perfect</b>: Penalty for distance to this 'ideal average'.<br/>
High values: Emphasis on getting panels on the 'right strenght'<br/>
Low values: Not so much emphasis on getting panels on the 'right strenght'<br/>
</p><p>
<b>panel_size_not_perfect</b>: Penalty for distance to the "perfect panel size".<br/>
The "perfect panel size" emphasizes that better debates deserve the bigger panels. Thus, say you're having two debates and five adjudicators, the best debates has a "perfect panel size" of 3, the other one of 2<br/>
</p><p>
<b>panel_size_out_of_bounds</b>: Penalty for distance the "acceptable panel size bounds".<br/>
These bounds do not emphasize that better debates deserve the bigger panels. Thus, say you're having two debates and five adjudicators, the bounds for both debates are panel sizes from 2 to 3.<br/>
</p><p>
<b>adjudicator_met_adjudicator</b>: Penalty for adjudicator meeting each other again. This penalty is multiplied by the 
times these two already were in one panel together.<br/>
High values: Adjudicators are not put in panels with previous co-panellists<br/>
</p><p>
<b>adjudicator_met_team</b>: Penalty for adjudicator meeting a team they have adjudcicated before again. This penalty is multiplied by the times this has occurred before.<br/>
High values: Adjudicators are not to adjudicate the same teams again and again<br/>
</p><p>
<b>trainee_in_chair</b>: Penalty for an adjudicator marked as a trainee being in the chair<br/>
</p><p>
<b>watcher_not_in_chair</b>: Penalty for an adjudicator marked as capable of 'watching' other adjudicators not being in the chair (irrespective of whether they are watching someone). Use this value sparingly, as the algorithm will underperform if too many chairs are pre-determined.<br/>
</p><p>
<b>watched_not_watched</b>: Penalty for an adjudicator marked to be watched by another, more experienced adjudicator not actually being watched. To make effective use of this parameter, mark several adjudicators as 'watchers'.<br/>
</p><p>
<b>lock</b>: (Maybe a bit misplaced in this table, but anyway) If this lock is on a non-zero value the algorithms can not be run. This is to prevent two different computers from hitting the draw buttons at the same time and messing things up big time. If for some reason the lock is set to one, it can be removed here.<br/>
</p>
<p>
<b>draw_table_speed</b>: (Certainly misplaced in this table, but anyway) The time in seconds between slides advancing on the table display.<br/>
</p>
