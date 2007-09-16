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

$title = "Tabbie History";
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
    <li><a href="history.php" class="activemain">History</a></li>
    <li><a href="run/">Run<? if (!$local) echo " Online Demo"; ?></a></li>
    </ul>
</div>

<h3>History</h3>
<div>The following message was found on <a href="http://flynn.debating.net/">Colm Flynn's 
site</a> and 
forms the basis for this project. Most of it is out of date by now - specifically, the 
current maintainers have not yet reached the original developers. If you want a 
working version of Tabbie, read the above. Colm's message begins here:</div>

<div>The organising committee of Singapore Worlds 2004 developed an excellent
system to run their tab.&nbsp; Deepak Jois and	Aditya Krishnan were the
developers.&nbsp;&nbsp; It is hosted on their website <a href="http://www.ntu.edu.sg/worlds" target="_blank">http://www.ntu.edu.sg/worlds</a>
but I have copied it here in case it disappears and that would be a big loss.</div>

<div>Here is a note they had on the website.</div>

<div>Tabbie v0.1 (NTU Worlds 2004 tab software)<br>
<br>
Both of us are busy with our final year, and hence we cannot find time to clean up the 
software and package it nicely and to write some help. We will get to that after we graduate 
in May. Till then, you can try following the instructions below. Please note that for 
operating this software in the current state, you need to have basic knowledge of 
Apache/PHP/MySQL. You may start by following the instructions at the websites of the various 
software.<br>
<br>
1. Go to http://www.phphome.net and install the latest version. You will have PHP, Apache, 
MySQL and phpMyAdmin installed on your computer, all in one package.<br>
<br>
2. After configuring them appropriately (You will find the instructions on the respective 
sites), please use phpMyAdmin and run the queries in the file worlds_database_Setup.sql<br>
<br>
3. After that, you may start the tab software by running the file worlds/index.php from your 
web browser.<br>
<br>
4. There is a very basic user guide in the docs folder.<br>

<br>
5. Any problems, we will be glad to help. Please include the full nature of the problem, and 
please make an effort on your part before contacting us (see 
http://www.catb.org/~esr/faqs/smart-questions.html for some tips)<br>
<br>
Deepak Jois<br>
Aditya Krishnan (AK)<br>
</div>

<?php require("run/view/footer.php"); ?>
