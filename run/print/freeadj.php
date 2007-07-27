<?
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