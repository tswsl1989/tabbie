<?
/******************************************************************************
File    :   position.php

Author  :   AK

Purpose :   This file displays the number of times each team has debated in each position

******************************************************************************/

include("header.inc");

include("config/dbconnection.php"); //Database Connection

$round=trim(@$_POST['round']);

//Check Database
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numdraws=mysql_num_rows($result);

$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

if (!$round)
	$round=$numdraws;

switch($action)
{
    case "display":
                        break;
    default:
                        $action="display";
}



//Load respective module
include("position/$action.inc");
?>
