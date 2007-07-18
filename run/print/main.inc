<?
$query="SELECT COUNT(*) AS num FROM team";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numteam=$row['num'];

$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numdraws=mysql_num_rows($result);

$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

?>


<div id="content">

    <p>Welcome to the Print Module. Please choose from the options above.</p>

    <h2>Summary</h2>
    <ul class="summary">
     <li><span class="flt">No. of Teams</span>: <?echo "$numteam"?></li>
    </ul>
    
    <? if ($numdraws = $numresults)
    { $msg[]="Results for the last draw have been entered already.";
      $msg[]="New draw has not been created.";
    }


//Display Messages
if (count(@$msg)>0)
{
  echo "<ul class=\"err\">\n";
  for($x=0;$x<count($msg);$x++)
    echo "<li>".$msg[$x]."</li>\n";

  echo "</ul>";

}
?> 