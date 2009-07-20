<?php
//THIS FILE IS NOT FUNCTIONAL OR USED AT THIS TIME
set_include_path("../../");
require("includes/backend.php");

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