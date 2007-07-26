<?
//Check Database
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numdraws=mysql_num_rows($result);

$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

switch($list)
{
    case "draw":
                $title.= " (Draw)";
                        break;
    case "teamcode":    $title.= " (Team Code)";
                break;
    case "main":
                break;
    default:
                        $list="main";
}

//Load respective module
include("display/$list.php");
?>
