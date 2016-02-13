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
set_include_path(get_include_path() . PATH_SEPARATOR . "../../");
require_once("includes/backend.php");

//Information from the client
$adjud_id = trim($_POST['adjud_id']);
$action = trim($_POST['action']);

if(!($adjud_id && $action)){
	//Error condition: client requested non-existent team.
	header('HTTP/1.1 403 Forbidden');
	echo('Incomplete request ('.$adjud_id.', '.$action.')');
	die();
}

switch($action) {
	case 'ACTIVETOGGLE':
		toggle_adjudicator($adjud_id);
		print_adjudicator_xml($adjud_id);
		break;
	case 'BIG_INCREMENT':
		adjust_adjudicator_rank($adjud_id, +10);
		print_adjudicator_xml($adjud_id);
		break;
	case 'BIG_DECREMENT':
		adjust_adjudicator_rank($adjud_id, -10);
		print_adjudicator_xml($adjud_id);
		break;
	case 'LITTLE_INCREMENT':
		adjust_adjudicator_rank($adjud_id, +5);
		print_adjudicator_xml($adjud_id);
		break;
	case 'LITTLE_DECREMENT':
		adjust_adjudicator_rank($adjud_id, -5);
		print_adjudicator_xml($adjud_id);
		break;
	default:
		header('HTTP/1.1 403 Forbidden');
		echo 'Action not valid ('.$action.')';
		break;
}



function toggle_adjudicator($adjud_id) {
	global $DBCONN;
	$query="SELECT `adjud_id`, `active` FROM `adjudicator` WHERE `adjud_id` =?";
	$result=qp($query, array($adjud_id));
	if($result->RecordCount()!=1){
		//Adjud_id was not unique: risk working on the wrong adjudicator
		header('HTTP/1.1 403 Forbidden');
		echo('Adjud_id did not specify a unique adjudicator ($adjud_id)');
		die();
	}

	$adjudicator=$result->FetchRow();

	if($adjudicator['active']=="Y"){
		$active="N";
	}
	if($adjudicator['active']=="N"){
		$active="Y";
	}

	//$active needs to be a valid value before we put it into the DB!
	if (!(($active == "Y") || ($active == "N"))) {
		header("HTTP/1.1 403 Forbidden");
		echo "Active state invalid";
		die();
	}

	$query = "UPDATE `adjudicator` SET `active` = ? WHERE `adjud_id` = ?";
	$result=qp($query, array($active, $adjud_id));
	if (!$result) {
		echo $DBCONN->ErrorMsg();
	}
	return $result;
}

function adjust_adjudicator_rank($adjud_id, $qty) {
	global $DBCONN;
	$query="SELECT `adjud_id`, `ranking` FROM `adjudicator` WHERE `adjud_id` =?";
	$result=qp($query, array($adjud_id));
	if($result->RecordCount()!=1){
		//Adjud_id was not unique: risk working on the wrong adjudicator
		header('HTTP/1.1 403 Forbidden');
		echo('Adjud_id did not specify a unique adjudicator ($adjud_id)');
		die();
	}

	$adjudicator=$result->FetchRow();

	$ranking = $adjudicator['ranking'] + $qty;

	//$active needs to be a valid value before we put it into the DB!
	if ($ranking < 1  || $ranking > 100) {
		header("HTTP/1.1 403 Forbidden");
		echo "Invalid rank result";
		die();
	}

	$query = "UPDATE `adjudicator` SET `ranking` = ? WHERE `adjud_id` = ?";
	$result=qp($query, array($ranking, $adjud_id));
	if (!$result) {
		echo $DBCONN->ErrorMsg();
	}
	return $result;
}

function print_adjudicator_xml($adjud_id) {
	echo(mysql_to_xml("SELECT `adjud_id`, `ranking`, `active` FROM `adjudicator` WHERE `adjud_id`='$adjud_id'","adjudicator"));
}
?>
