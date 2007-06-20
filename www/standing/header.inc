<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>Standings Module - <? echo $title;?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="style.css">
</head>

<body>
  <h1 id="main">Worlds Universities Debating Championships</h1>
  <div id="mainmenu">
    <h2 class="hide">Main Menu</h2>
    <ul>
      <li><a href="input.php">Input</a></li>
      <li><a href="draw.php">Draw</a></li>
      <li><a href="result.php">Results</a></li>
      <li><a href="standing.php" class="activemain">Standings</a></li>
      <li><a href="print.php" >Print</a></li>
      <li><a href="setup.php">Setup</a></li>
    </ul>
  </div>

  <div id="submenu">
    <h2 class="hide">Standings Submenu</h2>
    <ul>
      <li><a href="standing.php?moduletype=main" <?echo ($moduletype=="main")?"class=\"activemain\"":""?>>main</a></li>
      <li><a href="standing.php?moduletype=teamstanding" <?echo ($moduletype=="teamstanding")?"class=\"activemain\"":""?>>teamstanding</a></li>
      <li><a href="standing.php?moduletype=speakerstanding" <?echo ($moduletype=="speakerstanding")?"class=\"activemain\"":""?>>speakerstanding</a></li>
      <li><a href="standing.php?moduletype=position" <?echo ($moduletype=="position")?"class=\"activemain\"":""?>>position</a></li>
      <li><a href="standing.php?moduletype=roundbreak" <?echo ($moduletype=="roundbreak")?"class=\"activemain\"":""?>>round points breakup</a></li>
    </ul>
  </div>