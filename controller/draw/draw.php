<?php
/* begin license *
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

//THIS FILE IS NOT FUNCTIONAL OR USED AT THIS TIME
set_include_path(get_include_path() . PATH_SEPARATOR . "../../");
require_once("includes/backend.php");

//Information from the client
$debate_id = htmlspecialchars(trim($_POST['debate_id']));
$adjud_id = htmlspecialchars(trim($_POST['adjud_id']));
$action  = htmlspecialchars(trim($_POST['action']));


//Get current round no.
$round = get_num_rounds()+1;

//Check temporary tables exist

if(!has_temp_draw()){
	//Error condition: client requested non-existent team.
	header('HTTP/1.1 412 Precondition Failed');
	echo('No temporary draw to provide.');
}

//Check valid action
if(!($action=="CHECKSTRIKE")){
	//Must supply a valid ACTION
	header('HTTP/1.1 400 Bad Request');
	echo('Specify valid ACTION value');
	$action="";
}

//Check for strikes
$query = "SELECT og, oo, cg, co FROM temp_draw_round_$round WHERE `debate_id` = '$debate_id'";
if($result=mysql_query($query)){
	$row=mysql_fetch_assoc($result);
	$ogid=$row['og'];
	$ooid=$row['oo'];
	$cgid=$row['cg'];
	$coid=$row['co'];
	if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
		echo("<?xml version='1.0' encoding='utf8'?><collection><strike><adjud_id>$adjud_id</adjud_id></strike></collection>");
	} 
} else {
	header('HTTP/1.1 400 Bad Request');
	echo('Specify valid DEBATE_ID value');
}
?>