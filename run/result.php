<?
require("ntu_bridge.php");
require_once("result/func.php"); //Helper Functions

//Get Number of  Rounds Completed
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numrounds=mysql_num_rows($result);

//Get Number of Rounds result entered for
$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

$nextresult=$numresults+1;




$ntu_controller = "result";
$ntu_default_module = "currentround";
$ntu_default_action = "";
$ntu_titles = array(
    "floor" => "Floor Managers",
    "chfadjud" => "Chief Adjudicator/DCAs",
    "tab" => "Tab Room",);

require("ntu_controller.php");

?>
