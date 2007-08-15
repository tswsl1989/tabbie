<?
//team-level conflicts should be a possibility
//but uni-level is a good starting point

require_once("includes/backend.php");

function get_active_adjudicators() {
    $query = "SELECT adjud_name, adjud_id, univ_id, conflicts, ranking FROM adjudicator WHERE active='Y' ORDER BY adjud_id";
    $db_result = mysql_query($query);
    
    $result = array();
    while ($row = mysql_fetch_assoc($db_result)) {
        $adjudicator = array();
        $adjudicator['adjud_name'] = $row['adjud_name'];
        $adjudicator['adjud_id'] = $row['adjud_id'];
        $adjudicator['ranking'] = $row['ranking'];
        $adjudicator['univ_conflicts'][] = $row['univ_id'];
        $conflicts = preg_split("/,/", $row['conflicts'], -1, PREG_SPLIT_NO_EMPTY);
        foreach ($conflicts as $conflict) {
            $univ_result = mysql_query("SELECT univ_id FROM university WHERE univ_code = '$conflict'");
            $univ = mysql_fetch_assoc($univ_result);
            //insert checks here...
            $adjudicator['univ_conflicts'][] = $univ['univ_id'];
        }
        $result[] = $adjudicator;
    }
    return $result;
}

function create_temp_adjudicator_table($round) {
    $tablename = "temp_adjud_round_$round";
    mysql_query("DROP TABLE $tablename");

    $query = "CREATE TABLE `$tablename` ( `debate_id` MEDIUMINT NOT NULL ,";
    $query .= " `adjud_id` MEDIUMINT NOT NULL ,";
    $query .= " `status` ENUM( 'chair', 'panelist', 'trainee' ) NOT NULL );";
    $db_result = mysql_query($query);
    if (!$db_result)
        return mysql_error();
}

function temp_debates_foobar($round) {
    $query =
        "SELECT debate_id, T1.univ_id AS og, T2.univ_id AS oo, " . 
        "T3.univ_id AS cg, T4.univ_id AS co " .
        
        "FROM temp_draw_round_$round AS D, team AS T1, team AS T2, team AS T3, team AS T4 " .
        
        "WHERE D.og = T1.team_id AND D.oo = T2.team_id AND D.cg = T3.team_id AND D.co = T4.team_id " .
        "ORDER BY debate_id";
    $db_result = mysql_query($query);
    print mysql_error();
    $result = array();
    while ($row = mysql_fetch_assoc($db_result)) {
        $new_row = array();
        $new_row['debate_id'] = $row['debate_id'];
        $new_row['universities'] = array($row['og'], $row['oo'], $row['cg'], $row['co']);
        $result[] = $new_row;
    }
    return $result;
}


?>