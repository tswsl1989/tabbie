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
require("includes/backend.php");

//Information from the client
$adjud_id = htmlspecialchars(trim($_POST['adjud_id']));
$team_code = htmlspecialchars(trim($_POST['team_code']));
$univ_id = htmlspecialchars(trim($_POST['univ_id']));
$action = htmlspecialchars(trim($_POST['action']));
$strike_id = htmlspecialchars(trim($_POST['strike_id']));

if($team_code && $univ_id){
	$team_id=get_team_id($univ_id, $team_code);
	if(!$team_id){
		//Error condition: client requested non-existent team.
		header('HTTP/1.1 403 Forbidden');
		echo('Team does not exist.');
	}
}

//Add a strike
if($action == 'ADD'){
	if($team_id){
		if(is_strike_judge_team($adjud_id, $team_id)){
			//error condition: strike already in place
			header('HTTP/1.1 403 Forbidden');
			echo('Strike already in place.');
		} else {
			add_strike_judge_team($adjud_id, $team_id, $univ_id);
		}
	} else {
		if(is_strike_judge_univ($adjud_id, $univ_id)){
			//error condition: strike already in place
			header('HTTP/1.1 403 Forbidden');
			echo('Strike already in place.');
		} else {
			add_strike_judge_univ($adjud_id, $univ_id);
		}
	}
}

//Delete a strike
if($action == 'DELETE'){
	del_strike_id($strike_id);
}	

//List strikes
if($action == 'GET'){
	//default action anyway. see below
}

//In any case return the current strike list.
echo(mysql_to_xml("SELECT s.strike_id, s.adjud_id, u.univ_id, t.team_id, u.univ_name, t.team_code FROM `strikes` AS s INNER JOIN `university` AS U ON s.univ_id = u.univ_id LEFT JOIN team as t ON t.team_id = s.team_id WHERE s.adjud_id=$adjud_id ORDER BY s.strike_id ASC", "strike"));
?>