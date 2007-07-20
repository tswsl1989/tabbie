<?php
$title = "Tabbie";
require("run/view/header.php");
$local = ($_SERVER["SERVER_NAME"] != "tabbie.sourceforge.net");
?>

<div id="mainmenu">
    <h2 class="hide">Main Menu</h2>
    <ul>
    <li><a href="index.php" class="activemain">Home</a></li>
    <li><a href="run/">Run<? if (!$local) echo " Online Demo"; ?></a></li>
    <li><a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Download</a></li>
    <li><a href="installation.php">Install</a></li>
    </ul>
</div>

<?php if ($local) { ?>

<h3><a href="run/">Run Tabbie</a></h3>
<div>
Welcome to Tabbie, running on <b><?= $_SERVER["SERVER_NAME"] ?></b>. If you just installed it - congratulations! Good luck <a href="run/">actually running it</a>. This page may look a lot like <a href="http://tabbie.sourceforge.net">Tabbie's website</a> which is accesible for anyone - rest assured because it's not. These sites are just very much integrated to make the online demo run smoothly and provide you with all the documentation on your local version.
</div>

<?php } ?>

<h3>What is Tabbie?</h3>
<div>
Tabbie helps you create the Tab or draw of British Parliamentary Debating Tournaments. It has a number of properties:
<ul>
<li>Official. It uses the official <a href="doc/Worlds_Tab_Rules_-_DRAFT.doc">WUDC rules</a> to calculate its draw.</li>
<li>Safe and stable - It's been tried in practice at a tournament as large as NTU Worlds 2004 with great success.</li>
<li>Scalable for large tournaments: It is web based, you can use multiple computers for inputting data.</li>
<li>Open and Free. Its source code is freely inspectable. You can modify or add anything you like. Download is free.</li>
<li>Alive. As of July 2007 changes are being made to make it work even better.</li>
</ul>
</div>

<h3>News</h3>
<p>
<b>Thu 19th July 2007</b><br>
The tournament at the The Interdisciplinary Center, Herzliya was a success. Some things we found very easy using Tabbie were:
<ul>
<li>Putting in and changing teams, adjudicators and debates.</li>
<li>Calulating and manipulating the draw.</li>
<li>Displaying the draw, printing adjudication sheets for the rooms.</li>
</ul>
Things that have been promoted to the wish list are:
<ul>
<li>Switching Rooms.</li>
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

<?php 
if (! $local) {
?>
<h3><a href="run/">Online Demo</a></h3>
<div>Why take our word for it if you can just dive in and <a href="run/">play with the real thing</a>? A full running system is available online. (remember, Tabbie is web based).

Two words of caution: because this is a demo, some features have been disabled, specifically those that involve writing to files. Secondly, you are not neceserraly the only person using this demo at any given time, so some data may change mysteriously. Feel free to play around though!</div>

<?php } ?>

<h3><a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Download</a></h3>
<div>The latest version of Tabbie can be downloaded at <a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Sourceforge</a>.
</div>

<h3><a href="installation.php">Install</a></h3>
<div>
You will also need a Web Server to run Tabbie since it's based on PHP. Use the <a href="installation.php">Installation Guide</a> to get you started.
</div>

<h3><a href="http://sourceforge.net/projects/tabbie/">Participate, Support</a></h3>
<div>
Participation in the <a href="http://sourceforge.net/projects/tabbie/">Tabbie Project</a> is possible on many levels. Feel free to report any bugs that you may find or request for a feature. 

We would welcome any participation in the <a href="http://sourceforge.net/projects/tabbie/">Tabbie Project</a>. This is also the source for any support questions you may have. Since the above page may be a bit intimidating (it is even for us) you can also just send a mail to <i>klaas aat vanschelven doot com</i>.

We strive to make Tabbie a platform for the many different diverging efforts that are being put into creating Tabbing Software and welcome help. We also try to reverse the trend of Tabbing Software being created for just one tournament and then being tossed aside. So if you feel you can contribute, do it here.
</div>

<h3><a href="history.php">History</a></h3>
<div>Tabbie was created for NTU Worlds 2004. It was subsequently found on the internet in 2007 and restored to working order - great thanks go out to Colm Flynn for preserving it. <a href="history.php">Read more...</a>
</div>

<?php require("run/view/footer.php"); ?>