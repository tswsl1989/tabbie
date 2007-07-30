<?php
$ntu_controller = "print";
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
<? /*
<li><a href="....recreate...">Printable version of the draw</a></li>
*/ ?>
<li><a href="rest.php?result_type=html&amp;function=get_adjudicators_venues&amp;param=<?= $round ?>&amp;title=Adjudicators%20locations%20for%20round%20<?= $round ?>">List of adjudicators, and their venues</a></li>
<li><a href="rest.php?result_type=html&amp;function=get_teams_venues&amp;param=<?= $round ?>&amp;title=Team%20locations%20for%20round%20<?= $round ?>">
List of teams, and their venues</a>
</ul>

<? /*
<h3>Tab Room (keep in the room)</h3>
<ul>
<li><a href="adjlist.php">Full list of adjudicators</a></li>
<li><a href="freeadj.php">List of unassigned adjudicators</a></li>
<li><a href="teamadjcount.php">Adjudicators per team overview</a></li>
</ul>
*/ ?>
<p>

<?php
require('view/footer.php'); 
?>