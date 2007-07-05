<?php require("templates/header.inc");
$local = ($_SERVER["SERVER_NAME"] != "tabbie.sourceforge.net");
?>

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
<li>It's old and tested software: it was originally created for and used in the NTU Worlds 2004 with great success.</li>
<li>It uses the official <a href="doc/Worlds_Tab_Rules_-_DRAFT.doc">WUDC rules</a> to calculate its draw.</li>
<li>It is web based. You can use multiple computers for inputting data on large tournaments.</li>
<li>It is Free Software. Download is free, and its source code is freely inspectable. You can modify or add anything you like.</li>
<li>It is alive. As of July 2007 changes are being made to make it work even better.</li>
</ul>
</div>

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

<?php require("templates/footer.inc"); ?>