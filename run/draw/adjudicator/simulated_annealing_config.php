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
$scoring_factors = array(
    "university_conflict" => 1000,
    "chair_not_perfect" => 1,
    "panel_steepness" => 0.5,
    "panel_strength_not_perfect" => 1,
    "adjudicator_met_adjudicator" => 50,
    "adjudicator_met_team" => 50
);

/*
university_conflict: Penalty for each team-adjudicator from that team's uni.
High values: Uni- conflicts will occur less
Low values: (Down to 0): Uni conflicts matter less

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