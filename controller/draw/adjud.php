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

set_include_path("../../");
@include("includes/backend.php");

/* Procedure to make a judge a panelist from being chair:

1. Promote another judge to being chair in that room.
2. Target judge will automatically be demoted to panelist.
3. Make target judge chair in whichever room

This is not checked for in add_adjudicator (because add_adjudicator 
handles the demoting), but supplying a chair to ADD or CHAIR will
cause it to fail. This ensures you cannot have a debate without
a chair.*/

function is_panelist($adjud_id, $round){
		//RETURN 0 DOES NOT MEAN THEY ARE A CHAIR (!)
		$query="SELECT COUNT(adjud_id) FROM temp_adjud_round_$round WHERE `adjud_id`='$adjud_id' AND `status`='panelist';";
		if($count=mysql_fetch_assoc(mysql_query($query))){
			if($count['COUNT(adjud_id)']==1){
				return 1;
			} else {
				$query="SELECT COUNT(adjud_id) FROM temp_adjud_round_$round WHERE `adjud_id`='$adjud_id'";
				if($count=mysql_fetch_assoc(mysql_query($query))){
					if($count['COUNT(adjud_id)'==1]){
						//in the draw but not a panelist
						return 0;
					} else {
						//hacky return to handle out-of-draw judges
						return 1;
					}
					
				} else {
				//error case
				return 0;
				}
			}
		} else {
			//error case
			return 0;
		}
}

function add_adjudicator($adjud_id, $debate_id, $status, $round){
	
	$query="DELETE FROM temp_adjud_round_$round WHERE `adjud_id`='$adjud_id';";
	echo $query;
	if(mysql_query($query)){
		$query="INSERT INTO temp_adjud_round_$round (`debate_id`, `adjud_id`, `status`) VALUES ('$debate_id', '$adjud_id', '$status');";
		echo $query;
		if(mysql_query($query)){
			//return 1;
		} else {
			//return 0;
		}
		$query="SELECT debate_id FROM temp_adjud_round_$round WHERE `adjud_id`='$adjud_id';";
		$result=mysql_query($query);
		$row=mysql_fetch_assoc($result);
		echo $row['debate_id'];
		if($row['debate_id']==$debate_id){
			echo("Good change.");
		} else {
			echo("Bad change.");
		}
		return 1;
	} else {
		return 0;
	}
}

//Information from the client
$action = htmlspecialchars(trim($_POST['action']));
$adjud_id = htmlspecialchars(trim($_POST['adjud_id']));
$debate_id = htmlspecialchars(trim($_POST['debate_id']));
$client = htmlspecialchars(trim($_POST['client']));
$time = htmlspecialchars(trim($_POST['time']));
$free = htmlspecialchars(trim($_POST['free']));

//Get current round no.
$round = get_num_rounds()+1;

//Check temporary tables exist
if(!has_temp_draw()){
	//This API is only functional while a temporary draw is open
	header('HTTP/1.1 412 Precondition Failed');
	echo('No temporary draw open');
	$action="";
}



//Check valid action
if(!($action=="LIST"||$action=="ADD"||$action=="CHAIR")){
	//Must supply a valid ACTION
	//header('HTTP/1.1 400 Bad Request');
	//echo('Specify valid ACTION value');
	$action="";
}


$masterquery="SELECT debate_id, adjudicator.adjud_id AS id, adjudicator.ranking AS ranking, `temp_adjud_round_$round`.`status` AS status, adjudicator.adjud_name AS name, adjudicator.status AS trainee FROM `temp_adjud_round_$round` INNER JOIN adjudicator ON adjudicator.adjud_id = temp_adjud_round_$round.adjud_id ";

switch ($action) {
	case "LIST":
		if($time){
			$masterquery.="WHERE UNIX_TIMESTAMP(`time`) > '$time'";
		}
		if($adjud_id){
			$masterquery.="WHERE `temp_adjud_round_$round`.`adjud_id` = '$adjud_id'";
		}
		if($free){
			$masterquery = "SELECT A.adjud_id AS id, A.adjud_name AS name, A.ranking AS ranking, T.`status` AS status, A.`status` AS trainee, CONCAT('FREE','') AS debate_id ";
		    $masterquery .= "FROM adjudicator A ";
		    $masterquery .= "LEFT JOIN temp_adjud_round_$round T ON A.adjud_id = T.adjud_id ";
		    $masterquery .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC";
		} 
		echo(mysql_to_xml("$masterquery", "adjudicator"));
		break;
	case "ADD":
		if($adjud_id&&$debate_id){
			if(is_panelist($adjud_id, $round)){
				if(add_adjudicator($adjud_id, $debate_id, 'panelist', $round)){
					break;
				} else {
					header('HTTP/1.1 400 Bad Request');
					echo('Unable to add adjudicator');
					break;
				}
			} else {
				//Failed the is_panelist check
				header('HTTP/1.1 400 Bad Request');
				echo('Judge to be promoted must be panelist');
				break;
			}
		} else {
			//Malformed input
			header('HTTP/1.1 400 Bad Request');
			echo('Specify both ADJUD_ID and DEBATE_ID');
		}
	case "CHAIR":
		if($adjud_id&&$debate_id){
			if(is_panelist($adjud_id, $round)){
				$query="SELECT adjud_id FROM temp_adjud_round_$round WHERE `status`='chair' AND `debate_id`='$debate_id';";
				echo $query;
				if($result=mysql_query($query)){
					$success=true;
					while($adjudicator = mysql_fetch_assoc($result)){
						if(add_adjudicator($adjudicator['adjud_id'],$debate_id,"panelist",$round)){
							$success=$success&&true;
						} else {
							$success=false;
						}
					}
					if($success){
						if(add_adjudicator($adjud_id,$debate_id,"chair",$round)){
							break;
						} else{
							header('HTTP/1.1 400 Bad Request');
							echo('Failed to make requested ajudicator chair');
							break;
						}
					}else{
						header('HTTP/1.1 400 Bad Request');
						echo('Failed to move chairs to panelists');
						break;
					}
				} else {
					header('HTTP/1.1 400 Bad Request');
					echo('Could not find chairs(!)');
					break;
				}
			} else {
				//Failed the is_panelist check
				header('HTTP/1.1 400 Bad Request');
				echo('Judge to be promoted must be panelist');
				break;
			}
		} else {
			//Malformed input
			header('HTTP/1.1 400 Bad Request');
			echo('Specify both ADJUD_ID and DEBATE_ID');
			break;
		}	
}
?>