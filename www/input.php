<?
/******************************************************************************
File    :   input.php

Author  :   Deepak Jois

Purpose :   Handle all the functions of the input module including the input 
            of universities, venues, teams and adjudicators into the database.

******************************************************************************/

//Determine action and moduletype and branch accordingly

$moduletype=trim(@$_GET['moduletype']); //module type : adjud univ venue team main
if (!$moduletype) $moduletype="main"; //set to main if empty

$action=trim(@$_GET['action']); //action : add edit delete display
if (!$action) $action="display"; //set to display if empty

//Set document title according to module and action
switch($moduletype)
{
    case "adjud":
                    $title.=" Adjudicator ";
                    break;
    case "univ":
                    $title.=" University ";
                    break;
    case "venue":                   
                    $title.=" Venue ";
                    break;
   case "team":
                    $title.=" Team ";
                    break;

   case "main":
                    $title.=" Main ";
                    break;
   default:
                    $title=" Main ";
                    $moduletype="main";
                    break;
   
}
//Make database connection
include("config/dbconnection.php");

//Load respective module
include("input/$moduletype.inc");
?>
