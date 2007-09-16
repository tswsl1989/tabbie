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

$title = "Tabbie - BP Debating Tab Software";
$dir_hack = "run/";
require("run/view/header.php");
$local = ($_SERVER["SERVER_NAME"] != "tabbie.sourceforge.net");
?>

<div id="mainmenu">
    <h2 class="hide">Main Menu</h2>
    <ul>
    <li><a href="index.php">Home</a></li>
    <li><a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Download</a></li>
    <li><a href="installation.php">Installation Guide</a></li>
    <li><a href="history.php">History</a></li>
    <li><a href="run/">Run<? if (!$local) echo " Online Demo"; ?></a></li>
    </ul>
</div>

<h3>All News</h3>
<p>
<b>Sat 21th July 2007</b><br>
Tabbie 1.1 is released. This release contains a number of features from the wishlist that followed out of the IDC tournament. This version has not yet been tested on a live tournament, if you want this, take version 1.0. New features are:
<ul>
<li>Added Overview Page / Dashboard / Wizard.</li>
<li>Added motions input module.</li>
<li>Added an integrated one-click backup module (for linux/unix systems).</li>
</ul>
</p>
<p>
<b>Thu 19th July 2007</b><br>
Tabbie 1.0 is released. The tournament at the The Interdisciplinary Center, Herzliya was a success. Some things we found very easy using Tabbie were:
<ul>
<li>Putting in and changing teams, adjudicators and debates.</li>
<li>Calulating and manipulating the draw.</li>
<li>Displaying the draw, printing adjudication sheets for the rooms.</li>
</ul>
Things that have been promoted to the wish list are:
<ul>
<li>An overview page or wizard - to provide clarity in stressful times.</li>
<li>Integrated Backup (we used <a href="http://www.phpmybackuppro.net/">phpMyBackupPro</a>)</li>
<li>Team Overview - in case of doubt you should be able to see all of a teams debates.</li>
<li>Tournament Overview for after the tournament: HTML/CSS files of the final results, all rounds and relevant other data.</li>
</ul>
Because running Tabbie went so smoothly it is now officially version 1.0!
</p>

<p>
<b>Fri 6th July 2007</b><br>
Version 1.0-RC1 is out. If Tabbie runs well in its second tournament coming week we'll take the RC (Release Candidate) off and make the official 1.0 release. New in this version:
<ul>
<li>A completely new Draw Algorithm, which fixes a number of issues with the NTU draw. Also - interesting for those out there who think they can do better - the algorithm is fairly easily to plug in or out. All Algorithm's results are automatically verified for correctness against the WUDC rules, and Tabbie displays how well it's doing in getting everyone fairly distributed over debating positions.</li>
<li>Adjudicator sheets (with names, motions etc.) can now be generated with one click - as a PDF that matches you input screen. No more hassling with mail merge!</li>
<li>The Installation has been made more robust and a <a href="installation.php">guide</a> is provided.</li>
</ul>
</p>

<?php require("run/view/footer.php"); ?>
