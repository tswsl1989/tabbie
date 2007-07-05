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
    return
        ($team_a["team_id"] != $team_b["team_id"]) &&
        (($team_a["points"] == $team_b["points"]) ||
        ($team_a["debate_level"] == $team_b["debate_level"]));
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
        if (is_swappable(&$team_a, &$team_b)) {
            $current = team_badness($team_a) + team_badness($team_b);
            $future = team_badness($team_a, $team_b["current_position"]) + team_badness($team_b, $team_a["current_position"]);
/*            if ($future == 0) {
                swap_two_teams($teams, $team_a, $team_b);
                return;
            }*/
            $net_effect = $future - $current;
            if ($net_effect < $best_effect) {
                $best_effect = $net_effect;
                $best_team_b = &$team_b;
            }
        }
    }
    if ($best_team_b) {
        print "******************************\n";
        print $team_a["team_id"]. ", " .$best_team_b["team_id"] ."\n";
        var_dump($teams);
        swap_two_teams(&$teams, &$team_a, &$best_team_b);
        print "after\n";
        var_dump($teams);
    }
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
        foreach ($teams as $team)
            if (team_badness($team) > 0)
                find_best_swap_for(&$teams, &$team);
    }
    //print "FINAL SCORE: " . teams_badness($teams);
    return just_ids_from_debates(debates_from_teams($teams));
}

/*
def pullUpCount(teams):
    levelDicts = {}
    result = []
    teams = list(reversed(sorted(teams, cmpPoints)))
    while teams:
        level = teams[0].points
        if not level in levelDicts:
            levelDicts[level] = {}
        levelDict = levelDicts[level]
        for team in teams[:4]:
            if not team.points in levelDict:
                levelDict[team.points] = 0
            levelDict[team.points] += 1
        result.append(levelDict)
        teams = teams[4:]
    return result

def validate(teams, debates):
    teamsInDebates = []
    pullUpCounts = pullUpCount(teams)
    for i, debate in enumerate(debates):
        teamsInDebates.extend(debate.positions)
        for team in debate.positions:
            if not team.points in pullUpCounts[i]:
                return False
            pullUpCounts[i][team.points] -= 1
    return set(teamsInDebates) == set(teams)

def score(debates):
    return Solution(debates).badness()

*/
?>