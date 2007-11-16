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

$ntu_controller = "index";

require("ntu_bridge.php");
require("view/header.php");
require("view/mainmenu.php");

require_once("includes/backend.php");
$round = get_num_rounds();
$next_round = $round + 1;

if (get_num_rounds() == 0 && !has_temp_draw()) {
    $state = "before_tournament";
    $p_state = "The tournament has not started yet - i.e., there has not been a draw for the first round yet.";
} elseif (get_num_rounds() == get_num_completed_rounds() && !has_temp_draw() && !has_temp_result()) {
    $state = "before_draw";
    $p_state = "All results for round $round have been inputted and finalized, and the draw for round $next_round has not been made.";
} elseif (get_num_rounds() == get_num_completed_rounds() && has_temp_draw() && !has_temp_result()) {
    $state = "during_draw";
    $p_state = "You are manually adjusting the adjudicators for round $next_round.";
} elseif (get_num_rounds() > get_num_completed_rounds() && !has_temp_draw() && !has_temp_result()) {
    $state = "during_round";
    $p_state = "The draw for round $round has been created, but inputting results for this round has not started yet.";
} elseif (get_num_rounds() > get_num_completed_rounds() && !has_temp_draw() && has_temp_result()) {
    $state = "during_results";
    $p_state = "You are inputting the results for round $round.";
}
?>

<h2>Overview</h2>
<p>
<?= $p_state ?> 
</p>

<? if ($state == "before_tournament") { ?>

<h3>Before the tournament &amp; during registration</h3>
<ul>
<li><a href="input.php?moduletype=venue">Input venue (room) names and locations</a></li>
<li><a href="input.php?moduletype=univ">Input participating universities or institutions</a></li>
<li><a href="input.php?moduletype=adjud">Input participating adjudicators</a></li>
<li><a href="input.php?moduletype=team">Input participating teams</a></li>
<li><a href="input.php?moduletype=motion">Input motions for the regular rounds</a></li>
<li><a href="backup.php">Make a backup (and save it on another computer)</a></li>
</ul>
<h3>Before the first regular round</h3>
<ul>

<? } if (in_array($state, array("before_draw", "during_draw", "during_round"))) { ?>

<h3>Before each regular round</h3>
<ul>

<? } if ($state == "before_draw") { ?>

<li><a href="input.php?moduletype=venue">Update venues according to availability</a></li>
<li><a href="input.php?moduletype=team">Update team status according to presence</a></li>
<li><a href="input.php?moduletype=adjud">Update adjudicator status according to presence</a></li>

<? } if ($state == "before_tournament" || $state == "before_draw") { ?>

<li><a href="input.php?moduletype=adjud_params">Adapt the parameters for adjudicator allocation</a></li>
<li><a href="draw.php?moduletype=currentdraw&amp;action=draw">Request the automated draw</a></li>

<? } if ($state == "during_draw") { ?>

<li><a href="draw.php?moduletype=manualdraw">Manually adjust adjudicators and rooms</a></li>
<li><a href="draw.php?moduletype=manualdraw&amp;action=finalise">Finalize the draw</a></li>

<? } if ($state == "during_round") { ?>

<li><a href="rest.php?result_type=pdf&amp;function=adjudicator_sheets&amp;param=<?= $round ?>">Print the adjudicator sheets with room-specific info</a> (and have them distributed)</li>
<li><a href="draw.php">Display the draw</a></li>
<li>Display the motion</li>
<li><a href="backup.php">Make a backup (and save it on another computer)</a></li>

<? } if (in_array($state, array("before_draw", "before_tournament", "during_draw", "during_round"))) { ?>

</ul>

<? } if (in_array($state, array("during_round", "during_results", "before_draw"))) { ?>

<h3>After each regular round</h3>
<ul>

<? } if ($state == "during_round") { ?>

<li><a href="result.php?moduletype=currentround&amp;action=create">Start Inputting results</a></li>

<? } if ($state == "during_results") { ?>

<li><a href="result.php?moduletype=currentround">Continue Inputting results</a></li>
<li><a href="result.php?moduletype=currentround&amp;action=finalize">Finalize results</a></li>

<? } if ($state == "before_draw") { ?>

<li><a href="backup.php">Make a backup (and save it on another computer)</a></li>

<? } if (in_array($state, array("during_round", "during_results", "before_draw"))) { ?>

</ul>

<? } if ($state == "before_draw") { ?>

<h3>Before the break</h3>
<ul>
<li><a href="standing.php?moduletype=teamstanding">Determine the breaking teams</a></li>
<li>Automatically create the fold (not implemented yet)</li>
</ul>

<h3>Before the grand final</h3>
<ul>
<li><a href="standing.php?moduletype=speakerstanding">Determine the best speakers</a></li>
</ul>

<h3>After the tournament</h3>
<ul>
<li>Present the results as a website (not implemented yet)</li>
</ul>

<? } ?>

</p>
<?php
require('view/footer.php'); 
?>
