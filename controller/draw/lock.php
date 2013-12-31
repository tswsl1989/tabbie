<?php
/*This file is not currently in use, but should provide workable locking semantics*/
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

function is_locked_by_client($field, $value, $client, $round){
	$expiry=time();
	$query="SELECT lock_id FROM draw_lock WHERE ?=? AND client=? AND expiry>?";
	$result=qp($query, array($field, $value, $client, $expiry));
	if ($result->RecordCount()>0){
		return true;
	} else {
		return false;
	}
}

function is_locked_by_other($field, $value, $client, $round){
	$expiry=time();
	$query="SELECT lock_id FROM draw_lock WHERE ?=? AND client!=? AND expiry>?";
	$result($query, array($field, $value, $client, $expiry));
	if ($result->RecordCount()>0){
		return true;
	} else {
		return false;
	}
}

function clean_locks(){
	$expiry=time();
	$query="DELETE FROM draw_lock WHERE expiry<?";
	qp($query, array($expiry));
}

//Information from the client
$action = htmlspecialchars(trim($_POST['action']));
$adjud_id = htmlspecialchars(trim($_POST['adjud_id']));
$debate_id = htmlspecialchars(trim($_POST['debate_id']));
$client = htmlspecialchars(trim($_POST['client']));

//Get current round no.
$round = get_num_rounds()+1;

//Check lock tables exist
$query="SHOW TABLES LIKE 'draw_lock'";
$result=q($query);
if($result->RecordCount()!=1){
	//This API is only functional while a temporary draw is open
	header('HTTP/1.1 412 Precondition Failed');
	echo('Unable to access lock tables.');
	$action=""; //Vacate ACTION to avoid operating down the line
}

//Check valid action
if(!($action=="LOCK"||$action="UNLOCK")){
	//Must supply a valid ACTION
	header('HTTP/1.1 400 Bad Request');
	echo('Specify valid ACTION value');
	$action="";
}

//Check that one of adjud_id and debate_id is actually set
if($adjud_id){
	$lockfield="adjud_id";
	$locktarget=$adjud_id;
} else if ($debate_id) {
	$lockfield="debate_id";
	$locktarget=$debate_id;
} else {
	header('HTTP/1.1 400 Bad Request');
	echo('Must specify one of ADJUD_ID or DEBATE_ID to LOCK');
	$action="";
}

//Clean locks
clean_locks();

switch ($action) {
	case "LOCK":
		//Lock will not renew an existing lock
		$expiry=time()+30;
		if(is_locked_by_other($lockfield, $locktarget, $client, $round)){
			header('HTTP/1.1 403 Forbidden');
			echo('Resource locked by another client');
			break;
		} else {
			$query="INSERT INTO draw_lock (? , expiry, client) VALUES (?, ?, ?);";
			qp($query, array($lockfield, $locktarget, $expiry, $client));
			$query="SELECT lock_id, ?, expiry, client FROM draw_lock WHERE ?=? AND expiry=? AND client=?";
			$rs=qp($query, array($lockfield, $lockfield, $locktarget, $expiry, $client));
			echo(recordset_to_xml($rs, "lock"));
			break;
		}
	case "UNLOCK":
		//Unlock will *not* fail if no lock is present
		if(is_locked_by_client($lockfield, $locktarget, $client, $round)){
			$query="DELETE FROM draw_lock WHERE ?=? AND client=?";
			qp($query, array($lockfield, $locktarget, $client);
			header('HTTP/1.1 200 OK');
			echo('Unlocked');
			break;
		} else {
			if(is_locked_by_other($lockfield, $locktarget, $client, $round)){
				header('HTTP/1.1 404 Forbidden');
				echo('Resource locked by another client');
				break;
			} else {
				//Resource is not locked by client and is not locked by any others, so return unlocked
				header('HTTP/1.1 200 OK');
				echo('Unlocked');
				break;
			}
		}		
}
?>
