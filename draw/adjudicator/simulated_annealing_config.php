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

//Energy calculation:

function get_scoring_factors_from_db() {
  $params = array();
  $db_res = mysql_query("select param_name,param_value from configure_adjud_draw");
  while ($row = mysql_fetch_assoc($db_res)) {
    $params[$row['param_name']]=$row['param_value'];
  }
  return $params;
}

function store_scoring_factors_to_db($params) {
    foreach ($params as $param => $pvalue) {
      mysql_query("update configure_adjud_draw set param_value='$pvalue' where param_name='$param'");
  }
}

$scoring_factors = get_scoring_factors_from_db();
//store_scoring_factors_to_db($scoring_factors);

/*
university_conflict: Penalty for each team-adjudicator from that team's uni.
High values: Uni- conflicts will occur less
2Low values: (Down to 0): Uni conflicts matter less

chair_not_perfect: Penalty for each chair of less quality than 100. Total penalty = penalty * (100 - real value)
High values: The best adjudicators will all be chairs
Low values: Having the best people in chair is not as important

"panel_steepness": Value between 0 and 1. Reflecting the relation between panel strength and debate strength.
High values: Up to 1: Debate strength strictly relates to panel strength.
Low values: All debates are considered equal.
Further remarks: Slowly increasing this value during the tournament is expected to have a positive effect on the tournament.

panel_strength_not_perfect: Penalty for distance to this 'ideal average'.
High values: Emphasis on getting panels on the 'right strenght'
Low values: Not so much emphasis on getting panels on the 'right strenght'

adjudicator_met_adjudicator: Penalty for adjudicator meeting each other again. This penalty is multiplied by the 
times these two already were in one panel together.
High values: Adjudicators are not put in panels with previous co-panellists
*/


?>
