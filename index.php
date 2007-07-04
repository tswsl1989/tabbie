<?php require("templates/header.inc"); ?>

<h3>What is Tabbie?</h3>
<div>
Tabbie helps you create the Tab or draw of British Parliamentary Debating Tournaments. It has a number of properties:
<ul>
<li>It's stable software: it was used in the NTU Worlds 2004 with great success.</li>
<li>It uses the official WUDC rules to calculate its draw.</li>
<li>It is web based. This means you can use multiple computers for inputting the data on large tournaments. In fact - you're looking at an installation right now.</li>
<li>It is Free Software. Download is free, and its source code is freely inspectable. You can modify or add anything you like.</li>
</ul>
</div>

<?php 
if ($_SERVER["SERVER_NAME"] == "tabbie.sourceforge.net") {
?>
<h3><a href="run/">Online Demo</a></h3>
<div>Why take our word for it if you can just dive in and <a href="run/">play with the real thing</a>? A full running system is available online (remember, Tabbie is web based).

Two words of caution: because this is a demo, some features have been disabled, specifically those that involve writing to files. Secondly, you are not neceserraly the only person using this demo at any given time, so some data may change mysteriously. Feel free to play around though!</div>

<?php } else { ?>

<h3><a href="run/">Run Tabbie</a></h3>
<div>
<a href="http://tabbie.sourceforge.net">Tabbie's website</a> online and the actual product are tighly integrated, which explains the similarities. Right now, however, you're looking at the real thing, running on the server <b><?= $_SERVER["SERVER_NAME"] ?></b>. Congratulations on your installation and have fun <a href="run/">actually running it</a>.
</div>

<?php } ?>


<h3><a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Download</a></h3>
<div>The latest version of Tabbie can be downloaded at <a href="http://sourceforge.net/project/showfiles.php?group_id=199347">Sourceforge</a>. Please 
realise that you will also need a webserver (php) and mysql to get this working. You may 
be interested in <a href="http://www.easyphp.org/">an easy package that combines mysql and 
apache</a>.</div>

<h3><a href="http://sourceforge.net/projects/tabbie/">Participate, Support</a></h3>
<div>
Participation in the <a href="http://sourceforge.net/projects/tabbie/">Tabbie Project</a> is possible on many levels. Feel free to report any bugs that you may find or request for a feature. 

We would welcome any participation in the <a href="http://sourceforge.net/projects/tabbie/">Tabbie Project</a>. This is also the source for any support questions you may have.

We strive to make Tabbie a platform for the many different diverging efforts that are being put into creating Tabbing Software and welcome help. We also try to reverse the trend of Tabbing Software being created for just one tournament and then being tossed aside. So if you feel you can contribute, do it here.
</div>

<h3><a href="history.php">History</a></h3>
<div>Tabbie was created for NTU Worlds 2004. It was subsequently found on the internet in 2007 and restored to working order - great thanks go out to Colm Flynn for preserving it. <a href="history.php">Read more...</a>
</div>

<?php require("templates/footer.inc"); ?>