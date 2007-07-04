<?
/******************************************************************************
File    :   result.php

Author  :   Deepak Jois

Purpose :   This file manages all the functions related to the results
            of the draw, including creation and display of the draw.

******************************************************************************/

include("result/func.php"); //Helper Functions
include("includes/dbconnection.php"); //Database Connection

$moduletype=trim(@$_GET['moduletype']); //module type : round currentround
if (!$moduletype) $moduletype="currentround"; //set to round if empty


//Check Database

//Get Number of  Rounds Completed
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numrounds=mysql_num_rows($result);

//Get Number of Rounds result entered for
$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

$nextresult=$numresults+1;

switch($moduletype)
{
    case "round":
    case "currentround":
                 break;
    default:
                $moduletype="currentround";

}

//Load respective module
include("result/$moduletype.inc");
?>
