<?
require_once("includes/dbconnection.php");
require_once("includes/db_tools.inc");

function get_num_rounds() { //duplicate - factor out!
    $result = q("SHOW TABLES LIKE 'draw_round%'");
    return mysql_num_rows($result);
}

function team_on_place($round_nr, $team_id, $place) {
    $query = "SELECT $place FROM result_round_$round_nr WHERE $place = $team_id";
    return (mysql_num_rows(q($query)));
}

function team_on_position($round_nr, $team_id, $position) {
    $query = "SELECT $position FROM draw_round_$round_nr WHERE $position = '$team_id'";
    return (mysql_num_rows(q($query)));
}

function get_teams($nr_of_rounds) {
    $db_result = q("SELECT team_id, univ_id FROM team WHERE active='Y'");
    $teams = array();
    
    while ($team = mysql_fetch_assoc($db_result)) {
        $team_id = $team['team_id'];
        $points = 0;
        $positions = array('og', 'oo', 'cg', 'co');
        foreach ($positions as $position)
            $team[$position] = 0;
        for ($i = 1; $i <=  $nr_of_rounds; $i++) {
            $points += team_on_place($i, $team_id, "first") ? 3 : 0;
            $points += team_on_place($i, $team_id, "second") ? 2 : 0;
            $points += team_on_place($i, $team_id, "third") ? 1 : 0;
            //$points += something($i, $team_id, "fourth") ? 0 : 0; silly
            foreach ($positions as $position) {
                $team[$position] += team_on_position($i, $team_id, $position) ? 1 : 0;
            }
        }
        $team['points'] = $points;
        $teams[] = $team;
    }
    return $teams;
}

function print_teams_css($teams) {
    echo "team_id,points,og,oo,cg,co\n";
    foreach ($teams as $team) {
        echo $team["team_id"] . "," .
            $team["points"] . "," .
            $team["og"] . "," .
            $team["oo"] . "," .
            $team["cg"] . "," .
            $team["co"] . "\n";
    }
}

print_teams_css(get_teams(get_num_rounds()));
?>
