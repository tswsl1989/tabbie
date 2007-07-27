<?
require_once("includes/dbconnection.php");
require_once("includes/db_tools.php");

function get_num_rounds() {
    $result = q("SHOW TABLES LIKE 'draw_round%'");
    return mysql_num_rows($result);
}

function get_num_completed_rounds() {
    $result = q("SHOW TABLES LIKE 'result_round%'");
    return mysql_num_rows($result);
}

function __team_on_ranking($round_nr, $team_id, $ranking) {
    $query = "SELECT $ranking FROM result_round_$round_nr WHERE $ranking = $team_id";
    return (mysql_num_rows(q($query)));
}

function __team_on_position($round_nr, $team_id, $position) {
    $query = "SELECT $position FROM draw_round_$round_nr WHERE $position = '$team_id'";
    return (mysql_num_rows(q($query)));
}

function points_for_ranking($ranking) {
    if ($ranking == "first") return 3;
    if ($ranking == "second") return 2;
    if ($ranking == "third") return 1;
    if ($ranking == "fourth") return 0;
    return 999;
}

function get_teams_positions_points($nr_of_rounds) {
    $POSITIONS = array('og', 'oo', 'cg', 'co');
    $RANKINGS = array("first", "second", "third", "fourth");
    $db_result = q("SELECT team_id FROM team WHERE active='Y'");
    $teams = array();
    
    while ($team = mysql_fetch_assoc($db_result)) {
        $team_id = $team['team_id'];
        $points = 0;
        $positions = array();
        foreach ($POSITIONS as $POSITION)
            $positions[$POSITION] = 0;
        for ($i = 1; $i <=  $nr_of_rounds; $i++) {
            foreach ($RANKINGS as $RANKING) {
                if (__team_on_ranking($i, $team_id, $RANKING))
                    $points += points_for_ranking($RANKING);
            }
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

function results_by_position($round_nr) {
    $result = array();
    $POSITIONS = array('og', 'oo', 'cg', 'co');
    $RANKINGS = array("first", "second", "third", "fourth");
    foreach ($POSITIONS as $POSITION) {
        $result[$POSITION] = array();
        $current =& $result[$POSITION];
        $db_result = q("SELECT $POSITION FROM draw_round_$round_nr");
        while ($row = mysql_fetch_array($db_result)) {
            $team_id = $row[0];
            foreach ($RANKINGS as $RANKING) {
                $team_on_ranking = __team_on_ranking($round_nr, $team_id, $RANKING);
                @$current[$RANKING] += $team_on_ranking;
                if ($team_on_ranking) {
                    @$current["total"] += points_for_ranking($RANKING);
                }
            }
        }
    }
    return $result;
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

function get_adjudicators_venues($roundno) {
    $result["header"] = array("Adjudicator Name", "Venue", "Venue Location");
    
    $query = "SELECT v.*, a.* FROM adjudicator AS a, draw_round_$roundno AS d, " .
                "venue AS v, adjud_round_$roundno AS adjud  " .
                "WHERE d.venue_id=v.venue_id AND adjud.debate_id = d.debate_id AND " .
                "a.adjud_id = adjud.adjud_id ORDER BY adjud_name";
    
    $query_result = mysql_query($query);
    $data = array();
    
    while ($row =mysql_fetch_assoc($query_result)) {
        $data[] = array($row["adjud_name"], $row["venue_name"], $row["venue_location"]);
    }
    $result["data"] = $data;
    return $result;
}

?>
