<?php /* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 * 
 *     This file is part of Tabbie
 * 
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * end license */

require("ntu_bridge.php");
require_once("result/func.php"); //Helper Functions
require_once("includes/backend.php");

//Get Number of  Rounds Completed
$numrounds=get_num_rounds();

//Get Number of Rounds result entered for
$numresults=get_num_completed_rounds();
$numresults=$numresults[0];

$nextresult=$numresults+1;

if(array_key_exists("roundno", @$_GET)){
	$roundno=@$_GET["roundno"];
} else {
	$roundno="";
}




$ntu_controller = "result";
$ntu_default_module = "currentround";
$ntu_default_action = "";
$ntu_titles = array(
    "floor" => "Floor Managers",
    "chfadjud" => "Chief Adjudicator/DCAs",
    "tab" => "Tab Room",);

require("ntu_controller.php");

?>
