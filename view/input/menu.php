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
<div id="submenu">
<h2 class="hide">Input Submenu</h2>
    <ul>
    <li><a href="input.php?moduletype=main" <?= ($moduletype=="main")?"class=\"activemain\"":""?>>main</a></li>
    <li><a href="input.php?moduletype=venue" <?= ($moduletype=="venue")?"class=\"activemain\"":""?>>venue</a></li>
    <li><a href="input.php?moduletype=univ" <?= ($moduletype=="univ")?"class=\"activemain\"":""?>>university</a></li>
    <li><a href="input.php?moduletype=adjud" <?= ($moduletype=="adjud")?"class=\"activemain\"":""?>>adjudicator</a></li>
    <li><a href="input.php?moduletype=team" <?= ($moduletype=="team")?"class=\"activemain\"":""?>>team</a></li>
    <li><a href="input.php?moduletype=motion" <?= ($moduletype=="motion")?"class=\"activemain\"":""?>>motion</a></li>
    <li><a href="input.php?moduletype=adjud_params" <?= ($moduletype=="adjud_params")?"class=\"activemain\"":""?>>Settings</a></li>
    </ul>
</div>
