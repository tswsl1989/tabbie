<?
$header = array("Team Name", "Venue", "Venue Location")

$query = 
    $venuequery="SELECT v.venue_id AS venue_id, v.venue_location AS venue_location, v.venue_name AS venue_name, t.team_id AS team_id, t.team_code AS team_code, u.univ_code AS univ_code ";
    $venuequery.="FROM team AS t, university AS u, draw_round_$roundno AS d, venue AS v ";
    $venuequery.="WHERE d.venue_id=v.venue_id AND (t.team_id=d.og OR t.team_id=d.oo OR t.team_id=d.cg OR t.team_id=d.co) AND t.univ_id=u.univ_id ";
    $venuequery.="ORDER BY univ_code, team_code ";
    $venueresult=mysql_query($venuequery);

$data = array();
while ($row = mysql_fetch_assoc($result)) {
    $data[] = array($row['univ_code'] . " " . $row['team_code'], $row["venue_name"], $row["venue_location"]);
}
