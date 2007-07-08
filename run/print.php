<?
$list=trim(@$_POST['list']);
if (!$list)
{    $list=trim(@$_GET['list']);
    if (!$list) $list="main"; //set to main if empty
}


$ntu_controller = "print";
$ntu_default_module = "main";
$ntu_default_action = "";
$ntu_titles = array(
    "floor" => "Floor Managers",
    "chfadjud" => "Chief Adjudicator/DCAs",
    "tab" => "Tab Room",);

require("ntu_bridge.php");
require("ntu_controller.php");

?>
