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
$debate_id = htmlspecialchars(trim($_POST['debate_id']));
$action = htmlspecialchars(trim($_POST['action']));

if(!has_temp_result()){
	//Error condition: client requested non-existent team.
	header('HTTP/1.1 412 Precondition Failed');
	echo('No temporary results to provide.');
}

//Check valid action

if(!($action=="LIST")){
	//Must supply a valid ACTION
	header('HTTP/1.1 400 Bad Request');
	echo('Specify valid ACTION value');
	$action="";
}

//Get current round no.
$round = get_num_rounds();

$masterquery="SELECT * FROM `temp_result_round_$round`";

switch ($action) {
	case "LIST":
		if($debate_id){
			$masterquery.="WHERE `debate_id`='$debate_id'";
		}
		$masterquery.=" ORDER BY debate_id ASC";
		echo(mysql_to_xml("$masterquery", "debate"));
		break;
}