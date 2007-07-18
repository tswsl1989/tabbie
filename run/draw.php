<?
require("ntu_bridge.php");

$query = "SHOW TABLES LIKE 'draw_round%'";
$result = mysql_query($query);
$numdraws = mysql_num_rows($result);
$nextround = $numdraws + 1;

if ($numdraws <= 0) 
    $ntu_override_module = "currentdraw";

$ntu_controller = "draw";
$ntu_default_module = "round";
$ntu_default_action = "";
$ntu_titles = array();

require("ntu_controller.php");
?>
