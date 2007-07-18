<?
$query="SELECT COUNT(*) AS num FROM venue";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numvenue=$row['num'];

$query="SELECT COUNT(*) AS num FROM venue WHERE active='Y'";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numvenueactive=$row['num'];

$query="SELECT COUNT(*) AS num FROM university";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numuniv=$row['num'];

$query="SELECT COUNT(*) AS num FROM team";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numteam=$row['num'];

$query="SELECT COUNT(*) AS num FROM team WHERE active='Y'";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numteamactive=$row['num'];

$query="SELECT COUNT(*) AS num FROM adjudicator";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numadjud=$row['num'];

$query="SELECT COUNT(*) AS num FROM adjudicator WHERE active='Y'";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numadjudactive=$row['num'];


?>

<p>Welcome to the Input Module. Please choose the option above according to what you want to edit.</p>

<h2>Summary</h2>
<ul class="summary">
    <li><span class="flt">No. of Venues</span>: <?echo "<b>$numvenue</b> ($numvenueactive)"?></li>
    <li><span class="flt">No. of Universities</span>: <?echo "<b>$numuniv</b>"?></li>
    <li><span class="flt">No. of Teams</span>: <?echo "<b>$numteam</b> ($numteamactive)"?></li>
    <li><span class="flt">No. of Adjudicators</span>: <?echo "<b>$numadjud</b> ($numadjudactive)"?></li>
</ul>

<p class="msg">* The numbers in brackets indicate how many entries are active</p>