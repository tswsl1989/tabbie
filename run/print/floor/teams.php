<?

    $text.=" <tr><th>Team Name</th><th>Venue</th><th>Venue Location</th><th>&nbsp;</th> \n";

    $venuequery="SELECT v.venue_id AS venue_id, v.venue_location AS venue_location, v.venue_name AS venue_name, t.team_id AS team_id, t.team_code AS team_code, u.univ_code AS univ_code ";
    $venuequery.="FROM team AS t, university AS u, draw_round_$roundno AS d, venue AS v ";
    $venuequery.="WHERE d.venue_id=v.venue_id AND (t.team_id=d.og OR t.team_id=d.oo OR t.team_id=d.cg OR t.team_id=d.co) AND t.univ_id=u.univ_id ";
    $venuequery.="ORDER BY univ_code, team_code ";
    $venueresult=mysql_query($venuequery);

    while ($venuerow=mysql_fetch_assoc($venueresult))
    {    $venue_location = $venuerow['venue_location'];
        $teamname = $venuerow['univ_code']." ".$venuerow['team_code'];
        $venuename = $venuerow['venue_name']    ;

        $text=" <td>$teamname</td><td>$venuename</td><td>$venue_location</td>\n";
    }
