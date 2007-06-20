<?
/******************************************************************************
File    :   print.php

Author  :   AK

Purpose :   Handles all required print modules

******************************************************************************/

//Determine action and moduletype and branch accordingly

$moduletype=trim(@$_GET['moduletype']); //moduletype : main, adjudicator, floor, chfadjud
if (!$moduletype) $moduletype="main"; //set to main if empty

$list=trim(@$_POST['list']);
if (!$list)
{	$list=trim(@$_GET['list']);
	if (!$list) $list="main"; //set to main if empty
}

//Set document title according to module

switch($moduletype)
{
   case "adjudicator":
                    @$title .= " Adjudicator";
                    break;
   case "floor":
   					@$title .= " Floor Managers";
   					break;
   case "chfadjud":
   					@$title .= " Chief Adjudicator/DCAs";
   					break;
   case "tab":
   					@$title .= " Tab Room";
   					break;
   case "display":
   					@$title .= " Display";
   					break;
   case "main":
                    @$title .= " Main Page";
                    break;
   default:
                    @$title .= " Main Page";
                    $moduletype="main";
                    break;
}

//Make database connection
include("includes/dbconnection.php");

//Load respective module
include("print/$moduletype.inc");
?>
