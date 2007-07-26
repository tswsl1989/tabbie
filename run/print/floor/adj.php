<?
include("includes/dbconnection.php");

$header = array("Adjudicator Name", "Venue", "Venue Location")

$query = "SELECT v.*, a.* FROM adjudicator AS a, draw_round_$roundno AS d, " .
            "venue AS v, adjud_round_$roundno AS adjud  " .
            "WHERE d.venue_id=v.venue_id AND adjud.debate_id = d.debate_id AND " .
            "a.adjud_id = adjud.adjud_id ORDER BY adjud_name";

$result = mysql_query($query);
$data = array();

while ($row =mysql_fetch_assoc($result)) {
    $data[] = array($row["adjud_name"], $row["venue_name"], $row["venue_location"]);