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

assert_options(ASSERT_BAIL, 1);

//Information from the client
$univ_id = htmlspecialchars(trim($_REQUEST['univ_id']));

if(!($univ_id)){
	//Error condition: client requested non-existent team.
	header('HTTP/1.1 403 Forbidden');
	echo('Incomplete request ('.$univ_id.')');
	die();
}
echo(mysql_to_xml("SELECT `team_id`, `team_code` FROM `team` WHERE `univ_id` ='$univ_id'","team"));
?>