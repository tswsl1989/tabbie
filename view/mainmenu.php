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
 * end license */ ?>
<div id="mainmenu">
<h2 class="hide">Main Menu</h2>
<ul>
    <li><a href="index.php"<?= ($ntu_controller=="index")?" class=\"activemain\"":""?>>Overview</a></li>
    <li><a href="input.php"<?= ($ntu_controller=="input")?" class=\"activemain\"":""?>>Input</a></li>
    <li><a href="draw.php"<?= ($ntu_controller=="draw")?" class=\"activemain\"":""?>>Draw</a></li>
    <li><a href="result.php"<?= ($ntu_controller=="result")?" class=\"activemain\"":""?>>Results</a></li>
    <li><a href="standing.php"<?= ($ntu_controller=="standing")?" class=\"activemain\"":""?>>Standings</a></li>
    <li><a href="print.php"<?= ($ntu_controller=="print")?" class=\"activemain\"":""?>>Print</a></li>
    <li><a href="backup.php">Backup</a></li>
</ul>
</div>
