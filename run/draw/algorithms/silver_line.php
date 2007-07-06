<?
require_once("includes/draw.php");
require_once("includes/draw_badness.php");

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

function attributed_teams_from_debates(&$debates) {
    $result = array();
    $index = 0;
    foreach ($debates as $debate) {
        $current_position = 0;
        $best_team = $debate[0];
        $debate_level = $best_team["points"];
        foreach ($debate as $team) {
            $attributed_team = $team;
            $attributed_team["current_position"] = $current_position;
            $attributed_team["debate_level"] = $debate_level;
            $attributed_team["index"] = $index;
            $result[] = $attributed_team;
            $current_position++;
            $index++;
        }
    }
    return $result;
}

function is_swappable($team_a, $team_b) {
    $result = 
        ($team_a["team_id"] != $team_b["team_id"]) &&
        (($team_a["points"] == $team_b["points"]) ||
        ($team_a["debate_level"] == $team_b["debate_level"]));
    return $result;
}

function swap_two_teams(&$teams, &$team_a, &$team_b) {
    $current_position_a = $team_a["current_position"];
    $debate_level_a = $team_a["debate_level"];
    $index_a = $team_a["index"];
    
    $team_a["current_position"] = $team_b["current_position"];
    $team_a["debate_level"] = $team_b["debate_level"];
    $team_a["index"] = $team_b["index"];
    
    $team_b["current_position"] = $current_position_a;
    $team_b["debate_level"] = $debate_level_a;
    $team_b["index"] = $index_a;
    
    $teams[$team_a["index"]] = $team_a;
    $teams[$team_b["index"]] = $team_b;
}

function find_best_swap_for(&$teams, &$team_a) {
    $best_effect = 0;
    $best_team_b = false;
    foreach ($teams as $team_b) { //this loop especially can be limited
        if (is_swappable($team_a, $team_b)) {
            $current = team_badness($team_a) + team_badness($team_b);
            $future = team_badness($team_a, $team_b["current_position"]) + team_badness($team_b, $team_a["current_position"]);
/*            if ($future == 0) {
                swap_two_teams($teams, $team_a, $team_b);
                return;
            }*/
            $net_effect = $future - $current;
            if ($net_effect < $best_effect) {
                $best_effect = $net_effect;
                $best_team_b = $team_b;
            }
        }
    }
    if ($best_team_b) {
        swap_two_teams($teams, $team_a, $best_team_b);
        return true;
    }
    return false;
}

function calculate_draw($teams) {
    srand(0);
    shuffle($teams);
    usort($teams, "cmp_teams_on_points");
    $teams = array_reverse($teams);
    $teams = attributed_teams_from_debates(debates_from_teams($teams));
    $previous_solution = 0;
    while (teams_badness($teams) > 0) {
        if ($previous_solution == teams_badness($teams))
            break;
        $previous_solution = teams_badness($teams);
        foreach ($teams as $team) //<= maybe here the problem?
            if (team_badness($team) > 0)
                if (find_best_swap_for($teams, $team))
                    break;
    }
    return debates_from_teams($teams);
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
                    print "problemen $points \n";
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
    return 
        validate_debates_in_brackets($teams, $debates) &&
        validate_all_teams_in_exactly_one_debate($teams, $debates);
}

?>