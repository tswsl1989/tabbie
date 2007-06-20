<?
/******************************************************************************
File    :   main.php

Author  :   AK

Purpose :   Displays Summary of Fllor Manager Print Modules.
           
******************************************************************************/

include("header.inc");

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

$query="SELECT COUNT(*) AS num FROM draw_round_$numdraws";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numdebates=$row['num'];


?>


<div id="content">

    <p>Welcome to the Tab Room Print Module. Round <?echo $numdraws?> is in progress. Please choose from the drop down box above.</p>

    <h2>Summary</h2>
    <ul class="summary">
     <li><span class="flt">No. of Teams</span>: <?echo "$numteam"?></li>
     <li><span class="flt">No. of Venues</span>: <?echo "$numdebates"?></li>
    </ul>
    

</div>
</body>

</html>
