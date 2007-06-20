<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title><? echo $title;?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
  <h1 id="main">Worlds Universities Debating Championships</h1>
  <div id="mainmenu">
    <h2 class="hide">Main Menu</h2>
    <ul>
      <li><a href="input.php">Input</a></li>
      <li><a href="draw.php" class="activemain">Draw</a></li>
      <li><a href="result.php">Results</a></li>
      <li><a href="standing.php" >Standings</a></li>
      <li><a href="print.php" >Print</a></li>
      <li><a href="setup.php">Setup</a></li>
    </ul>
  </div>
 <div id="submenu">
    <h2 class="hide">Results Submenu</h2>
    <ul>
      <?
	 for($x=0;$x<$numdraws;$x++) 
	   {
	     if (@$roundno==($x+1))
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
