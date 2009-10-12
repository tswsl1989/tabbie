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
 * end license */

require("ntu_bridge.php");
require("view/header.php");
require_once("includes/backend.php");
?>

<?php 
$nextround=@$_GET['nextround'];
$query = "SELECT A.adjud_id, A.adjud_name, A.ranking ";
$query .= "FROM adjudicator A ";
$query .= "LEFT JOIN temp_adjud_round_$nextround T ON A.adjud_id = T.adjud_id ";
$query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC, adjud_id ASC";        
$result=mysql_query($query);
echo mysql_error();
if (mysql_num_rows($result)!=0)
  {
    echo "<table><tr><th>Name</th><th>Ranking</th><th>Conflicts</th></tr>\n";
     	while($row=mysql_fetch_assoc($result))    
     	{
       		echo "<tr><td>{$row['adjud_name']}</td>";
       		echo "<td>{$row['ranking']}</td>\n";
			$adjud_id=$row['adjud_id'];
			echo "<td>".print_conflicts($adjud_id)."</td></tr>";
		}
		echo "</table>";
  } else {
	echo "<h3>No Adjudicators Found</h3>";
}
  
?>

<h3><a href="">Refresh</a></h3>
</div>

<?php
require('view/footer.php'); 
?>
