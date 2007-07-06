<?php

function team_badness(&$team, $position = -1) {
    $result = 0;
    $positions = $team["positions"];
    if ($position == -1)
        $position = $team["current_position"];
    $positions[position_to_s($position)] += 1;
    return badness($positions);
}

function teams_badness(&$teams) {
    $result = 0;
    foreach ($teams as $team) {
        $result += team_badness($team);
    }
    return $result;
}

function debates_badness(&$debates) {
    $result = 0;
    foreach ($debates as $debate) {
        $result += teams_badness($debate);
    }
    return $result;
}

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

function cmp_teams_on_points($team_a, $team_b) {
    return $team_a["points"] - $team_b["points"];
}

function teams_simplify_positions($teams) {
    $result = array();
    foreach ($teams as $team) {
        $team['positions'] = array_values($team['positions']);
        $result[] = $team;
    }
    return $result;
}

function position_to_s($i) {
    if ($i == 0) return "og";
    if ($i == 1) return "oo";
    if ($i == 2) return "cg";
    if ($i == 3) return "co";
    return "trouble";
}

function create_brackets($teams) {
    $result = array();
    usort($teams, "cmp_teams_on_points");
    $teams = array_reverse($teams);
    $current_max = $teams[0]["points"];
    $current_bracket = array();
    for ($i = 0; $i < count($teams) / 4; $i++) {
        $new_max = $teams[$i * 4]["points"];
        if ($current_max != $new_max) {
            $current_max = $new_max;
            $result[] = $current_bracket;
            $current_bracket = array();
        }
        for ($j = 0; $j < 4; $j++) {
            @$current_bracket[$teams[$i * 4 + $j]["points"]] += 1;
        }
    }
    $result[] = $current_bracket;
    return $result;
}

function validate_debates_in_brackets($teams, $debates) {
    $brackets = create_brackets($teams);
    $brackets = array_reverse($brackets);
    $current_bracket = array_pop($brackets);
    foreach ($debates as $debate) {
        if ($current_bracket == null)
           return false;
        foreach ($debate as $team) {
            @$current_bracket[$team["points"]] -= 1;
        }
        if (array_sum($current_bracket) == 0) {
            foreach ($current_bracket as $points)
                if ($points != 0) {
                    return false;
                }
            $current_bracket = array_pop($brackets);
        }
    }
    return true;
}

function validate_all_teams_in_exactly_one_debate($teams, $debates) {
    $t = array();
    $d = array();
    foreach ($debates as $debate)
        foreach ($debate as $team)
            $d[] += $team["team_id"];
    foreach ($teams as $team)
        $t[] += $team["team_id"];
    sort($d);
    sort($t);
    return $d == $t;
}

function validate_draw($teams, $debates) {
    $a = validate_debates_in_brackets($teams, $debates);
    $b = validate_all_teams_in_exactly_one_debate($teams, $debates);
    return $a && $b;
}

?>