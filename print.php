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

$ntu_controller = "print";
$moduletype="";
$title = "Print";

require("view/header.php");
require("view/mainmenu.php");

require_once("includes/backend.php");
$round = get_num_rounds();
?>

<h2>Print</h2>
<p>
Print module for round <?= $round ?>.
</p>
<h3>Rooms (distribute)</h3>
<ul>
<li><a href="rest.php?result_type=pdf&amp;function=adjudicator_sheets&amp;param=<?= $round ?>">Personalised adjudicator sheets</a></li>
</ul>

<h3>Runners and floormanagers (keep in hand)</h3>
<ul>

<li><a href="rest.php?result_type=html&amp;function=get_adjudicators_venues&amp;param=<?= $round ?>&amp;title=Adjudicators%20locations%20for%20round%20<?= $round ?>">List of adjudicators, and their venues</a></li>
<li><a href="rest.php?result_type=html&amp;function=get_teams_venues&amp;param=<?= $round ?>&amp;title=Team%20locations%20for%20round%20<?= $round ?>">
List of teams, and their venues</a>
</ul>

<p/>

<h3>Overhead projection</h3>
<ul>
    <li><a href="draw_scrolling_display.php?roundno=<?= $round ?>">Draw scrolling display with teams, positions and venues</a> or</li>
	<li><a href="draw_table_display.php?roundno=<?= $round ?>&slide=0">Draw slide display with teams, positions and venues</a> or</li>
	<li><a href="create_powerpoint.php?roundno=<?= $round ?>">Export to Microsoft Powerpoint 2007</a></li>
    <li><a href="rest.php?result_type=csv&amp;function=adjudicator_sheets&amp;param=<?= $round ?>">Export teams, positions and venues as ".csv" document</a></li>
</ul>


<h3>Analysis (Use for own pleasure or distribute)</h3>
<ul>
<li><a href="results_by_position.php">Motion Fairness</li>
<li><a href="team_overview.php">Team Overview</li>
</ul>

<?php
require('view/footer.php'); 
?>
