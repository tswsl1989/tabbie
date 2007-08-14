<?
require_once("includes/backend.php");

function get_active_adjudicators() {
    $query = "SELECT adjud_id, conflicts FROM adjudicator WHERE active='Y' ORDER BY ranking DESC";
    $db_result = mysql_query($query);
    
    $result = array();
    while ($row = mysql_fetch_assoc($result)) {
        $newrow = array();
        $newrow['adjud_id'] = $row['adjud_id'];
        $newrow['conflicts'] = preg_split("/,/", $row['conflicts'], -1, PREG_SPLIT_NO_EMPTY);
    }
}

function create_temp_adjudicator_table($round) {
    $tablename = "temp_adjud_round_$round";
    mysql_query("DROP TABLE $tablename");

    $query = "CREATE TABLE `$tablename` ( `debate_id` MEDIUMINT NOT NULL ,";
    $query .= " `adjud_id` MEDIUMINT NOT NULL ,";
    $query .= " `status` ENUM( 'chair', 'panelist', 'trainee' ) NOT NULL );";
    $db_result = mysql_query($query);
    if (!$result)
        return mysql_error();
}

//Open Debates Table
$query = "SELECT debate_id, U1.univ_code AS ogcode, U2.univ_code AS oocode, U3.univ_code AS cgcode, U4.univ_code AS cocode ";
$query .= "FROM temp_draw_round_$nextround AS D, university AS U1, university AS U2, university AS U3, university AS U4, team AS T1, team AS T2, team AS T3, team AS T4 ";
$query .= "WHERE D.og = T1.team_id AND D.oo = T2.team_id AND D.cg = T3.team_id AND D.co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id "; 

?>