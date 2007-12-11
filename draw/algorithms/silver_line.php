<?php /* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 * 
 *     This file is part of Tabbie
 * 
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * end license */

require_once("includes/draw.php");
require_once("includes/draw_badness.php");

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

function ciaran_bob_shuffle(&$teams) {
    //there are probably nicer ways to achieve this, but they would take more time to program :-)
    for ($i = 0; $i < 20000; $i++) {
        $team_a = $teams[array_rand($teams)];
        $team_b = $teams[array_rand($teams)];
        if (is_swappable($team_a, $team_b)) {
            swap_two_teams($teams, $team_a, $team_b);
        }
    }
}

function draw_silver_line($teams, $seed=0) {
    srand($seed);
    shuffle($teams);
    usort($teams, "cmp_teams_on_points");
    $teams = array_reverse($teams);
    $teams = attributed_teams_from_debates(debates_from_teams($teams));
    ciaran_bob_shuffle($teams);

    $previous_solution = 0;
    while (teams_badness($teams) > 0) {
        if ($previous_solution == teams_badness($teams))
            break;
        $previous_solution = teams_badness($teams);
        foreach ($teams as $team) 
            if (team_badness($team) > 0)
                if (find_best_swap_for($teams, $team))
                    break;
    }
    return debates_from_teams($teams);
}

?>
