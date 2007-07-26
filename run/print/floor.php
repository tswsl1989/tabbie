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
    case "teams":
                        $title.=" (Team list)";
                        break;
    case "adj":
                $title.=" (Adjudicator list)";
                break;
    case "draw":
                $title.=" (Draw)";
                break;
    case "main":
                break;
    default:
                        $list="main";
}

//Load respective module
include("floor/$list.php");
?>
