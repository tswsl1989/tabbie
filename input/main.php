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

require_once("includes/backend.php");

$query="SELECT COUNT(*) AS num FROM venue";
$result=q($query);
$row=$result->FetchRow();
$numvenue=$row['num'];

$query="SELECT COUNT(*) AS num FROM venue WHERE active='Y'";
$result=q($query);
$row=$result->FetchRow();
$numvenueactive=$row['num'];

$query="SELECT COUNT(*) AS num FROM university";
$result=q($query);
$row=$result->FetchRow();
$numuniv=$row['num'];

$query="SELECT COUNT(*) AS num FROM team";
$result=q($query);
$row=$result->FetchRow();
$numteam=$row['num'];

$query="SELECT COUNT(*) AS num FROM team WHERE active='Y'";
$result=q($query);
$row=$result->FetchRow();
$numteamactive=$row['num'];

$query="SELECT COUNT(*) AS num FROM adjudicator";
$result=q($query);
$row=$result->FetchRow();
$numadjud=$row['num'];

$query="SELECT COUNT(*) AS num FROM adjudicator WHERE active='Y'";
$result=q($query);
$row=$result->FetchRow();
$numadjudactive=$row['num'];


?>

<p>Welcome to the Input Module. Please choose the option above according to what you want to edit.</p>

<h2>Summary</h2>
<ul class="summary">
    <li><span class="flt">No. of Venues</span>: <?php echo "<b>$numvenue</b> ($numvenueactive)"?></li>
    <li><span class="flt">No. of Universities</span>: <?php echo "<b>$numuniv</b>"?></li>
    <li><span class="flt">No. of Teams</span>: <?php echo "<b>$numteam</b> ($numteamactive)"?></li>
    <li><span class="flt">No. of Adjudicators</span>: <?php echo "<b>$numadjud</b> ($numadjudactive)"?></li>
</ul>

<p class="msg">* The numbers in brackets indicate how many entries are active</p>
