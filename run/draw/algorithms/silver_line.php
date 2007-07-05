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

function debate_badness(&$debates) {
    $result = 0;
    $current_position = 0;
    foreach ($debates as $team) {
        $result += team_badness($team, $current_position);
        $current_position++;
    }
    return $result;
}

function debates_badnesses(&$debates) {
    $result = 0;
    foreach ($debates as $debate) {
        $result += debate_badness($debate);
    }
    return $result;
}

function attributed_teams_from_debates(&$debates) {
    $result = array();
    foreach ($debates as $debate) {
        $current_position = 0;
        $best_team = $debate[0];
        $debate_level = $best_team["points"];
        foreach ($debate as $team) {
            $attributed_team = $team;
            $attributed_team["current_position"] = $current_position;
            $attributed_team["debate_level"] = $debate_index;
            $current_position++;
            $result[] = $attributed_team;
        }
        $debate_index++;
    }
    return $result;
}
/*
    def teamsInPosition(self):
        result = []
        for debate in self.debates:
            for position, team in enumerate(debate.positions):
                result.append(PositionedTeam(team, position, debate))
        return result
*/
function is_swappable($team_a, $team_b) {
    return
        ($team_a["team_id"] != $team_b["team_id"]) &&
        (($team_a["points"] == $team_b["points"]) ||
        ($team_a["debate_level"] == $team_b["debate_level"]))
}

/*
def swapTwoTeams(teamInPositionA, teamInPositionB):
    debateA = teamInPositionA.debate
    debateB = teamInPositionB.debate
    positionA = teamInPositionA.position
    positionB = teamInPositionB.position
    debateA.positions[positionA] = teamInPositionB.team
    debateB.positions[positionB] = teamInPositionA.team
    teamInPositionA.position = positionB
    teamInPositionB.position = positionA
    teamInPositionA.debate = debateB
    teamInPositionB.debate = debateA

*/
function swap_two_teams(&$team_a, &$team_b) {
    print $team_a['team_id'] . " ". $team_b['team_id'] . "<br>";
}

function find_best_swap_for(&$teams, &$team_a) {
    $best_effect = 0;
    $best_team_b = false;
    foreach ($teams as $team_b) { //this loop especially can be limited
        if (is_swappable($team_a, $team_b)) {
            $current = team_badness($team_a) + team_badness($team_b);
            $future = team_badness($team_a, $team_b["current_position"]) + team_badness($team_b, $team_a["current_position"]);
            if ($future == 0) {
                swap_two_teams($team_a, $team_b);
                return;
            }
            $net_effect = $future - $current;
            if ($net_effect < $best_effect) {
                $best_effect = $net_effect;
                $best_team_b = $team_b;
            }
        }
    }
    if ($best_team_b) {
        swap_two_teams($team_a, $team_b);
    }
}

function calculate_draw($teams) {
    srand(0);
    shuffle($teams);
    usort($teams, "cmp_teams_on_points");
    $teams = array_reverse($teams);
    $solution = debates_from_teams($teams);
    $teams = attributed_teams_from_debates($solution);
    $previous_solution = 0;
    while (debates_badnesses($solution) > 0) {
        if ($previous_solution == debates_badnesses($solution))
            break;
        $previous_solution = debates_badnesses($solution);
        foreach ($teams as $team)
            if (team_badness($team) > 0)
                find_best_swap_for($teams, $team);
    }
    return just_ids_from_debates($solution);
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