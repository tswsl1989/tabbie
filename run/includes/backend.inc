<?
require_once("includes/dbconnection.php");
require_once("includes/db_tools.inc");

function get_num_rounds() {
    $result = q("SHOW TABLES LIKE 'draw_round%'");
    return mysql_num_rows($result);
}

function get_num_completed_rounds() {
    $result = q("SHOW TABLES LIKE 'result_round%'");
    return mysql_num_rows($result);
}

function __team_on_place($round_nr, $team_id, $place) {
    $query = "SELECT $place FROM result_round_$round_nr WHERE $place = $team_id";
    return (mysql_num_rows(q($query)));
}

function __team_on_position($round_nr, $team_id, $position) {
    $query = "SELECT $position FROM draw_round_$round_nr WHERE $position = '$team_id'";
    return (mysql_num_rows(q($query)));
}

function get_teams_positions_points($nr_of_rounds) {
    $db_result = q("SELECT team_id FROM team WHERE active='Y'");
    $teams = array();
    
    while ($team = mysql_fetch_assoc($db_result)) {
        $team_id = $team['team_id'];
        $points = 0;
        $POSITIONS = array('og', 'oo', 'cg', 'co');
        $positions = array();
        foreach ($POSITIONS as $POSITION)
            $positions[$POSITION] = 0;
        for ($i = 1; $i <=  $nr_of_rounds; $i++) {
            $points += __team_on_place($i, $team_id, "first") ? 3 : 0;
            $points += __team_on_place($i, $team_id, "second") ? 2 : 0;
            $points += __team_on_place($i, $team_id, "third") ? 1 : 0;
            //$points += something($i, $team_id, "fourth") ? 0 : 0; silly
            foreach ($POSITIONS as $POSITION) {
                $positions[$POSITION] += __team_on_position($i, $team_id, $POSITION) ? 1 : 0;
            }
        }
        $team['points'] = $points;
        $team['positions'] = $positions;
        $teams[] = $team;
    }
    return $teams;
}

function print_teams_css($teams) {
    echo "team_id\tpoints\tog\too\tcg\tco\n";
    foreach ($teams as $team) {
        echo $team["team_id"] . "\t" .
            $team["points"] . "\t" .
            $team["og"] . "\t" .
            $team["oo"] . "\t" .
            $team["cg"] . "\t" .
            $team["co"] . "\n";
    }
}

?>
