<?
/******************************************************************************
File    :   roundbreak.inc

Author  :   AK

Purpose :   This file handles the round breakup of points

******************************************************************************/


include("config/dbconnection.php"); //Database Connection

include("header.inc");

//Check Database
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numdraws=mysql_num_rows($result);

$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

$roundno=$numresults;

switch(@$action)
{
    case "display":
                        break;
    default:
                        $action="display";
}

//Load respective module
include("roundbreak/$action.inc");
?>