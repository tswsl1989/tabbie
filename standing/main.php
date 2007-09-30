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
