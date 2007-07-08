<?
$round=trim(@$_POST['round']);

//Check Database
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numdraws=mysql_num_rows($result);

$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

if ($round && ($round<$numresults))
    $action="showround";
else
    $action="display";

switch($action)
{
    case "display":
                        break;
    case "showround":
                break;
    default:
                        $action="display";
}

//Load respective module
include("speakerstanding/$action.inc");
?>
