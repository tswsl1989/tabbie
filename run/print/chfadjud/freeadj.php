<?
$query = "SELECT A.adjud_id, A.adjud_name, A.ranking, A.conflicts ";
$query .= "FROM adjudicator A ";
$query .= "LEFT JOIN temp_adjud_round_$roundno T ON A.adjud_id = T.adjud_id ";
$query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 

$result = mysql_query($query);
if ($result)
    { 
        $text.=" <tr><th>Adjudicator Name</th><th>Ranking</th><th>Conflicts</th>"

        while ($row=mysql_fetch_assoc($result))
        {    $adjudname=$row['adjud_name'];
            $ranking=$row['ranking'];
            $conflicts=$row['conflicts'];
            $text=" <td>$adjudname</td><td>$ranking</td><td>$conflicts</td> \n";
