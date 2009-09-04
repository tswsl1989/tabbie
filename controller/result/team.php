<?php
set_include_path("../../");
require("includes/backend.php");

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