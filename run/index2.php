<?php
$ntu_controller = "index";

require("ntu_bridge.php");
require("view/header.php");
require("view/mainmenu.php");
?>

<h2>Overview</h2>
<p>
Use the menu above for direct access to all of Tabbie's functionality. 
</p>

<h3>Before the tournament &amp; during registration</h3>
<ul>
<li><a href="setup.php">Configure Tabbie</a></li>
<li><a href="input.php?moduletype=venue">Input venue (room) names and locations</a></li>
<li><a href="input.php?moduletype=univ">Input participating universities or institutions</a></li>
<li><a href="input.php?moduletype=adjud">Input participating adjudicators</a></li>
<li><a href="input.php?moduletype=team">Input participating teams</a></li>
<li><a href="input.php?moduletype=motion">Input motions for the regular rounds</a><li>
<li>Input motions (not implemented yet)</li>
<li>Make a backup (and save it on another computer)</li>
</ul>

<h3>Before each regular round</h3>
<ul>
<li><a href="input.php?moduletype=venue">Update venues according to availability</a></li>
<li><a href="input.php?moduletype=team">Update team status according to presence</a></li>
<li><a href="input.php?moduletype=adjud">Update adjudicator status according to presence</a></li>
<li><a href="draw.php?moduletype=currentdraw&amp;action=draw">Request the automated draw</a></li>
<li><a href="draw.php?moduletype=manualdraw">Manually adjust adjudicators and rooms</a></li>
<li><a href="draw.php?moduletype=manualdraw&amp;action=finalise">Finalize the draw</a></li>
<li><a href="print.php?moduletype=adjudicator&amp;list=adjsheet&amp;action=display&amp;warning=done">Print the adjudicator sheets with room-specific info</a> (and have them distributed)</li>
<li><a href="draw.php">Display the draw</a></li>
<li>Display the motion</li>
<li>Make a backup (and save it on another computer)</li>
</ul>

<h3>After each regular round</h3>
<ul>
<li><a href="result.php?moduletype=currentround&amp;action=create">Start Inputting results</a></li>
<li><a href="result.php?moduletype=currentround&amp;action=finalize">Finalize results</a></li>
<li>Make a backup (and save it on another computer)</li>
</ul>

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


</p>
<?php
require('view/footer.php'); 
?>