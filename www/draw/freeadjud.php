<?include("../includes/dbconnection.php");?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
    <title>List of Free Adjudicators</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="../style.css">
</head>

<body>
<div id="content">
<h2>List of Free Adjudicators</h2>
<?php
$nextround=@$_GET['nextround'];
$query = "SELECT A.adjud_id, A.adjud_name, A.ranking, A.conflicts ";
$query .= "FROM adjudicator A ";
$query .= "LEFT JOIN temp_adjud_round_$nextround T ON A.adjud_id = T.adjud_id ";
$query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 
        
$result=mysql_query($query);
echo mysql_error();
if (mysql_num_rows($result)!=0)
  {
    echo "<table><tr><th>Name</th><th>Ranking</th><th>Conflicts</th></tr>\n";
       while($row=mysql_fetch_assoc($result))    
	 {
	   echo "<tr><td>{$row['adjud_name']}</td>";
	   echo "<td>{$row['ranking']}</td>\n";
	   echo ($row['conflicts'])?"<td>{$row['conflicts']}</td>":"<td><b>None</b></td>";
	 }
       echo "</table>";
  }
 else
  echo "<h3>No Adjudicators Found</h3>";
?>

<h3><a href="">Refresh</a></h3>
</div>
</body>
</html>
