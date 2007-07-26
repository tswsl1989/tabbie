<?
require_once("includes/dbconnection.php");
require_once("includes/db_tools.php");

function validate_motion($motion) {
    $errors = array()
    if (!$motion["round_no")
        $errors[] = "Round Number Missing.";
    if (!$motion["motion"])
        $errors[]="Motion Missing.";
    return $errors;
}

function add_motion($motion) {
    

}

function edit_motion($motion) {

}

function delete_motion($motion) {
    $errors = array();
    $query = "DELETE FROM motions WHERE round_no='{$motion["round_no"]}'";
    $result = mysql_query($query);
    $error = mysql_error();
    if ($error)
        $errors[] = $error;

}

?>