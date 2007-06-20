<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>Input Module - <? echo $title;?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
  <h1 id="main">Worlds Universities Debating Championships</h1>
  <div id="mainmenu">
    <h2 class="hide">Main Menu</h2>
    <ul>
      <li><a href="input.php" class="activemain">Input</a></li>
      <li><a href="draw.php">Draw</a></li>
      <li><a href="result.php">Results</a></li>
      <li><a href="standing.php" >Standings</a></li>
      <li><a href="print.php" >Print</a></li>
      <li><a href="setup.php">Setup</a></li>
    </ul>
  </div>

  <div id="submenu">
   <h2 class="hide">Input Submenu</h2>
    <ul>
      <li><a href="input.php?moduletype=main" <?echo ($moduletype=="main")?"class=\"activemain\"":""?>>main</a></li>
      <li><a href="input.php?moduletype=venue" <?echo ($moduletype=="venue")?"class=\"activemain\"":""?>>venue</a></li>
      <li><a href="input.php?moduletype=univ" <?echo ($moduletype=="univ")?"class=\"activemain\"":""?>>university</a></li>
      <li><a href="input.php?moduletype=adjud" <?echo ($moduletype=="adjud")?"class=\"activemain\"":""?>>adjudicator</a></li>
      <li><a href="input.php?moduletype=team" <?echo ($moduletype=="team")?"class=\"activemain\"":""?>>team</a></li>
    </ul>
  </div>

