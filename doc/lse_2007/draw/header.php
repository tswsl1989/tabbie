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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title><? echo $title;?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
  <h1 id="main">LSE Open 2007</h1>
  <div id="mainmenu">
    <h2 class="hide">Main Menu</h2>
    <ul>
      <li><a href="input.php">Input</a></li>
      <li><a href="draw.php" class="activemain">Draw</a></li>
      <li><a href="result.php">Results</a></li>
      <li><a href="standing.php" >Standings</a></li>
      <li><a href="wings.php" >Wing Judges</a></li>

    </ul>
  </div>
 <div id="submenu">
    <h2 class="hide">Results Submenu</h2>
    <ul>
      <?
	 for($x=0;$x<$numdraws;$x++) 
	   {
	     if ($roundno==($x+1))
	       $tag="class=\"activemain\"";
	     else
	       $tag="";

	     echo "<li><a href=\"draw.php?moduletype=round&amp;action=showdraw&amp;roundno=".($x+1)."\" $tag>Round ".($x+1) ."</a></li>\n";
	   }

        ?>
        <li><a href="draw.php?moduletype=currentdraw"<?echo ($moduletype=='currentdraw')?"class=\"activemain\"":""?>>Current Draw</a></li>
        <li><a href="draw.php?moduletype=manualdraw" <?echo ($moduletype=='manualdraw')?"class=\"activemain\"":""?>>Manual Draw</a></li>
    </ul>
</div>
