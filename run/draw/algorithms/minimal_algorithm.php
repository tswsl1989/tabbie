<?php

//typical input value:
// $teams = array(
//     array("team_id" => "0", "points" => 0, "positions" => array("og" => 1, "oo" => 1, "cg" => 1, "co" => 0)),
// );

function debates_from_teams($teams) {
    //assert that array contains modulo 4 == 0 items
    $debates = array();
    for ($i = 0; $i < count($teams) / 4; $i++) {
        $debate = array();
        for ($j = 0; $j < 4; $j++) {
            $debate[] = $teams[$i * 4 + $j];
        }
        $debates[] = $debate;
    }
    return $debates;
}

function just_ids_from_debates($debates) {
    $result_debates = array();
    foreach ($debates as $debate) {
        $result_debate = array();
        foreach ($debate as $team) {
            $result_debate[] = $team["team_id"];
        }
        $result_debates[] = $result_debate;
    }
    return $result_debates;
}

function cmp_teams_on_points($team_a, $team_b) {
    return $team_a["points"] - $team_b["points"];
}

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