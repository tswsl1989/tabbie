<?
/******************************************************************************
File    :   draw.php

Author  :   Deepak Jois

Purpose :   This file manages all the functions of the draw, including creation
            and display of the draw.

******************************************************************************/


include("config/dbconnection.php"); //Database Connection

$moduletype=trim($_GET['moduletype']); //module type : round currentdraw
if (!$moduletype) $moduletype="round"; //set to round if empty


//Check Database
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numdraws=mysql_num_rows($result);

//Calculate Next Round
$nextround=$numdraws+1;


switch($moduletype)
{
    case "round":
    case "currentdraw":
    case "manualdraw":
                        break;

    default:
                        $moduletype="round"; 
}

//Load respective module
include("draw/$moduletype.inc");
?>          
