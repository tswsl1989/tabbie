<?
$query="SELECT COUNT(*) AS num FROM team";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numteam=$row['num'];

$query="SELECT COUNT(*) AS num FROM team WHERE esl='Y' ";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numeslteam=$row['num'];

$query="SELECT COUNT(*) AS num FROM speaker";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numspeaker=$row['num'];

$query="SELECT COUNT(*) AS num FROM speaker AS speaker, team AS team WHERE speaker.team_id = team.team_id and team.esl='Y' ";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numeslspeaker=$row['num'];

?>

<p>Welcome to the Maintenance Module. Please choose from the options above.</p>

<h2>Summary</h2>
<ul class="summary">
    <li><span class="flt">No. of Teams</span>: <?echo "$numteam"?></li>
    <li><span class="flt">No. of ESL Teams</span>: <?echo "$numeslteam"?></li>
    <li><span class="flt">No. of Speakers</span>: <?echo "$numspeaker"?></li>
    <li><span class="flt">No. of ESL Speakers</span>: <?echo "$numeslspeaker"?></li>
</ul>