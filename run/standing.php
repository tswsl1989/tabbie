<?
/******************************************************************************
File    :   standing.php

Author  :   AK

Purpose :   Handles all required displays

******************************************************************************/

//Determine action and moduletype and branch accordingly

$moduletype=trim(@$_GET['moduletype']); //moduletype : main, teamstanding, speakerstanding, position
if (!$moduletype) $moduletype="main"; //set to main if empty

//Set document title according to module and list
switch($moduletype)
{
 case "teamstanding":
   $title="Team Standings";
   break;
 case "speakerstanding":
   $title="Speaker Standings";
   break;
 case "position":
   $title="Position Count";
   break;
 case "roundbreak":
   $title="Round Points Breakup";
   break;
 case "main":
   $title="Main";
   break;
 default:
   $title="Main";
   $moduletype="main";
   break;
}

//Make database connection
include("includes/dbconnection.php");

//Load respective module
include("standing/$moduletype.inc");
?>
