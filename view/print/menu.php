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
    <li><a href="print.php?moduletype=main" <?echo ($moduletype=="main")?"class=\"activemain\"":""?>>main</a></li>
    <li><a href="print.php?moduletype=adjudicator" <?echo ($moduletype=="adjudicator")?"class=\"activemain\"":""?>>adjudicator</a></li>
    <li><a href="print.php?moduletype=floor" <?echo ($moduletype=="floor")?"class=\"activemain\"":""?>>floor managers</a></li>
    <li><a href="print.php?moduletype=chfadjud" <?echo ($moduletype=="chfadjud")?"class=\"activemain\"":""?>>CA/DCAs</a></li>
    <li><a href="print.php?moduletype=tab" <?echo ($moduletype=="tab")?"class=\"activemain\"":""?>>Tab Room</a></li>
    <li><a href="print.php?moduletype=display" <?echo ($moduletype=="display")?"class=\"activemain\"":""?>>Display</a></li>
</ul>
</div>
