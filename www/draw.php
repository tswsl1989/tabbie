<?
require_once("includes/dbconnection.php");

$moduletype = trim(@$_GET['moduletype']); //module type : round currentdraw

$query = "SHOW TABLES LIKE 'draw_round%'";
$result = mysql_query($query);
$numdraws = mysql_num_rows($result);
$nextround = $numdraws + 1;

if ($moduletype != "currentdraw" && $moduletype != "manualdraw")
    $moduletype="round"; 

require("draw/$moduletype.inc");
?>
