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

$query = "SELECT A.adjud_id, A.adjud_name, A.ranking, A.conflicts ";
$query .= "FROM adjudicator A ";
$query .= "LEFT JOIN temp_adjud_round_$roundno T ON A.adjud_id = T.adjud_id ";
$query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 

$result = mysql_query($query);

$header = array("Adjudicator Name", "Ranking", "Conflicts");
$data = array();
while ($row = mysql_fetch_assoc($result)) {
    $data[] = array($row['adjud_name'], $row['ranking'], $row["conflicts"]);
}
