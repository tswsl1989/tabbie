<?php

//typical input value:
// $teams = array(
//     array("team_id" => "0", "points" => 0, "positions" => array("og" => 1, "oo" => 1, "cg" => 1, "co" => 0)),
// );
require_once("includes/draw.php");

function calculate_draw($teams) {
    srand(0);
    shuffle($teams);
    usort($teams, "cmp_teams_on_points");
    $teams = array_reverse($teams);
    return just_ids_from_debates(debates_from_teams($teams));
}

//typical output value:

/*
$debates = array(
    array("1", "2", "3", "4"),
    array("5", "6", "7", "0"));
*/

?>