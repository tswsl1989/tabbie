<?php

require_once("includes/draw.php");

function draw_minimal_algorithm($teams) {
    srand(0);
    shuffle($teams);
    usort($teams, "cmp_teams_on_points");
    $teams = array_reverse($teams);
    return debates_from_teams($teams);
}

?>