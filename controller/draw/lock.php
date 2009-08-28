<?php
/*This file is not currently in use, but should provide workable locking semantics*/
set_include_path("../../");
require("includes/backend.php");

function is_locked_by_client($field, $value, $client, $round){
	$expiry=time();
	$query="SELECT `lock_id` FROM draw_lock_round_$round WHERE `$field`='$value' AND `client`='$client' AND `expiry`>'$expiry';";
	$result=mysql_query($query);
	if (mysql_count_rows($result)>0){
		return true;
	} else {
		return false;
	}
}

function is_locked_by_other($field, $value, $client, $round){
	$expiry=time();
	$query="SELECT `lock_id` FROM draw_lock_round_$round WHERE `$field`='$value' AND `client`!='$client' AND `expiry`>'$expiry';";
	echo $query;
	$result=mysql_query($query);
	if (mysql_num_rows($result)>0){
		return true;
	} else {
		return false;
	}
}

function clean_locks(){
	$expiry=time();
	$query="DELETE FROM draw_lock_round_$round WHERE `expiry`<'$expiry';";
	mysql_query($query);
}

//Information from the client
$action = htmlspecialchars(trim($_POST['action']));
$adjud_id = htmlspecialchars(trim($_POST['adjud_id']));
$debate_id = htmlspecialchars(trim($_POST['debate_id']));
$client = htmlspecialchars(trim($_POST['client']));

//Get current round no.
$round = get_num_rounds()+1;

//Check lock tables exist
$query="SHOW TABLES LIKE 'draw_lock_round%'";
$result=mysql_query($query);
if(mysql_num_rows($result)!=1){
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
			$query="INSERT INTO draw_lock_round_$round (`$lockfield` , `expiry` , `client`) VALUES ( '$locktarget', '$expiry' , '$client');";
			mysql_query($query);
			$query="SELECT `lock_id`, `$lockfield`, `expiry`, `client` FROM draw_lock_round_$round WHERE `$lockfield`='$locktarget' AND `expiry`='$expiry' AND `client`='$client';";
			echo(mysql_to_xml("$query", "lock"));
			break;
		}
	case "UNLOCK":
		//Unlock will *not* fail if no lock is present
		if(is_locked_by_client($lockfield, $locktarget, $client, $round)){
			$query="DELETE FROM draw_lock_round_$round WHERE `$lockfield`='$locktarget' AND `client`='$client'";
			mysql_query($query);
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