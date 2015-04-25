<?php /* begin license *
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

require_once("includes/display.php");
require_once("includes/backend.php");


//Convert pre-1.4.2 databases to the new format
convert_db_ssesl();

//Get POST values and validate/convert them
//rls="Restricted Language Status"

$actionhidden="";
$univ_id=$team_code=$active=$composite=$speaker1=$speaker2=$speaker1esl=$speaker2esl=$esl=$speaker1novice=$speaker2novice=$novice="";
$recordedesl=$recordednovice="";

if(array_key_exists("univ_id", @$_POST)) $univ_id=trim(@$_POST['univ_id']);
if(array_key_exists("team_code", @$_POST)) $team_code=makesafe(@$_POST['team_code']);
if(array_key_exists("active", @$_POST)) $active=strtoupper(trim(@$_POST['active']));
if(array_key_exists("composite", @$_POST)) $composite=strtoupper(trim(@$_POST['composite']));
if(array_key_exists("speaker1", @$_POST)) $speaker1=makesafe(trim(@$_POST['speaker1']));
if(array_key_exists("speaker2", @$_POST)) $speaker2=makesafe(trim(@$_POST['speaker2']));
if(array_key_exists("speaker1esl", @$_POST)) $speaker1esl=trim(@$_POST['speaker1esl']);
if(array_key_exists("speaker2esl", @$_POST)) $speaker2esl=trim(@$_POST['speaker2esl']);
if(array_key_exists("speaker1novice", @$_POST)) $speaker1novice=trim(@$_POST['speaker1novice']);
if(array_key_exists("speaker2novice", @$_POST)) $speaker2novice=trim(@$_POST['speaker2novice']);
if(array_key_exists("esl", @$_POST)) $recordedesl=strtoupper(trim(@$_POST['esl']));
if(array_key_exists("novice", @$_POST)) $recordednovice=strtoupper(trim(@$_POST['novice']));
if(array_key_exists("actionhidden", @$_POST)) $actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action


if (($actionhidden=="add")||($actionhidden=="edit")) { //do validation
    $validate=1;
    //Check if they are empty and set the validate flag accordingly

    if (!$univ_id) $msg[]="University ID Missing.";
    if (!$team_code) $msg[]="Team Code Missing.";
    if (!$speaker1) $msg[]="Speaker 1 Missing.";
    if (!$speaker2) $msg[]="Speaker 2 Missing.";


	//Change Team ESL status if justified
	if(($speaker1esl=="EFL")&&($speaker2esl=="EFL")) $esl="EFL"; else  $esl="N";
	if(($speaker1esl=="ESL")&&($speaker2esl=="EFL")) $esl="ESL"; else  $esl="N";
	if(($speaker1esl=="EFL")&&($speaker2esl=="ESL")) $esl="ESL"; else  $esl="N";
	if(($speaker1esl=="ESL")&&($speaker2esl=="ESL")) $esl="ESL"; else  $esl="N";

	if(($speaker1esl=="N")||($speaker2esl=="N")) {
		$esl="N";
	} else {
		if (($speaker1esl=="ESL")||($speaker2esl=="ESL")){
			$esl="ESL";
		} else {
			$esl="EFL";
		}
	}
	if (($speaker1novice=="Y") && ($speaker2novice=="Y")) {
		$novice="Y";
	} else if (($speaker1novice=="Y")||($speaker2novice=="Y")) {
		$novice="P-A";
	} else {
		$novice="N";
	}

	if($esl!=$recordedesl) {
		$messagestr="Team restrictive language status automatically set to: ";
		if($esl=="ESL") $messagestr.="ESL";
		if($esl=="EFL") $messagestr.="EFL";
		if($esl=="N") $messagestr.="MBO (Main Break Only)";
		$msg[]=$messagestr;
	}

	if ($novice != $recordednovice) {
		$messagestr="Team novice status automatically set to: ";
		if ($novice == "Y") {
			$messagestr.="Novice";
		} else if ($novice == "P-A") {
			$messagestr.="Pro-Am";
		} else if ($novice == "N") {
			$messagestr.="MBO (Main Break Only)";
		} else {
			$messagestr.="An invalid state ($novice) - ERROR!";
		}
		$msg[]=$messagestr;
	}

	if ((!$active=='Y') && (!$active=='N')) {
		$msg[]="Active Status not set properly.";
		$validate=0;
	}

	if ((!$composite=='Y') && (!$composite=='N')) {
		$msg[]="Composite Status not set properly.";
		$validate=0;
	}

	if (strcasecmp($speaker1, $speaker2)==0) {
		$msg[]="Speaker names cannot be equal.";
		$validate=0;
	}

	if ((!$univ_id) || (!$team_code) || (!$speaker1) ||(!$speaker2)) $validate=0;
}

if ($action=="delete") {
	$msg=delete_team(@$_GET['team_id']);
	$action="display";
}

if ($actionhidden=="add") {
	//Check Validation
	if ($validate==1) {
		//Insert Team First
		$query1 = "INSERT INTO team(univ_id, team_code, esl, novice, active, composite) VALUES(?, ?, ?, ?, ?, ?)";
		$result1=qp($query1, array($univ_id, $team_code, $esl, $novice, $active, $composite));

        if ($result1) {
		$queryteam="SELECT team_id FROM team WHERE univ_id=? AND team_code=?";
		$resultteam=qp($queryteam, array($univ_id, $team_code));

		if ($resultteam) {
			$row=$resultteam->FetchRow();
			$team_id=$row['team_id'];
			$query2 = "INSERT INTO speaker(team_id, speaker_name, speaker_esl, speaker_novice) VALUES (?, ?, ?, ?), (?, ?, ?, ?)";
			$result2 = qp($query2, array($team_id, $speaker1, $speaker1esl, $speaker1novice, $team_id, $speaker2, $speaker2esl, $speaker2novice));

			if (!$result2) {
				//Error. Go to display
				unset($msg);
				$msg[]="Serious Error : Cannot Insert Speakers. ".$DBConn->ErrorMsg();
				$action="display";
			} else {
				$msg[]="Added record successfully";
			}
		} else {
			//Error Finding Team. Go to display
			unset($msg);
			$msg[]="Serious Error : Cannot Find Team.".$DBConn->ErrorMsg();
			$action="display";
		}
	} else {
            //Error Adding Team. Show error
            $msg[]="Error during insert : ".$DBConn->ErrorMsg();
            $action="add";
	}

   } else {
	//Back to Add Mode
	$action="add";
   }
}


if ($actionhidden=="edit") {
	$team_id=trim(@$_POST['team_id']);
	$speaker1id=trim(@$_POST['speaker1id']);
	$speaker2id=trim(@$_POST['speaker2id']);
	//Check Validation
	if ($validate==1) {
		$team_record["univ_id"] = $univ_id;
		$team_record["team_code"] = $team_code;
		$team_record["esl"] = $esl;
		$team_record["novice"] = $novice;
		$team_record["active"] = $active;
		$team_record["composite"] = $composite;
		$team_record["team_id"] = $team_id;
		$result1=$DBConn->Replace("team", $team_record, "team_id", true);
		if (!$result1) {
		      $msg[]="Problems editing Team : ".$DBConn->ErrorMsg();
		}

		//Edit Speaker 1
		$speaker1_record["speaker_id"] = $speaker1id;
		$speaker1_record["speaker_name"] = $speaker1;
		$speaker1_record["speaker_esl"] = $speaker1esl;
		$speaker1_record["speaker_novice"] = $speaker1novice;

		$result2=$DBConn->Replace("speaker", $speaker1_record, "speaker_id", true);
		if (!$result2) {
			$msg[]="Problems editing Speaker 1 : ".$DBConn->ErrorMsg();
		}

		//Edit Speaker 2
		$speaker2_record["speaker_id"] = $speaker2id;
		$speaker2_record["speaker_name"] = $speaker2;
		$speaker2_record["speaker_esl"] = $speaker2esl;
		$speaker2_record["speaker_novice"] = $speaker2novice;

		$result3=$DBConn->Replace("speaker", $speaker2_record, "speaker_id", true);
		if (!$result3) {
			$msg[]="Problems editing Speaker 2 : ".$DBConn->ErrorMsg();
		}

		if ((!$result1) || (!$result2) || (!$result3)) {
			$action="edit";
		} else {
			$msg[]="Record Edited Successfully.";
		}
	} else {
		//Back to Edit Mode
		$action="edit";
	}
}

if ($action=="edit") {
	//Check for Team ID. Issue Error and switch to display if missing or not found
	if ($actionhidden!="edit") {
		$team_id=trim(@$_GET['team_id']); //Get team_id from querystring

		//Extract values from database
		$result=qp("SELECT * FROM team WHERE team_id=?", array($team_id));
		if ($result->RecordCount()==0) {
			unset($msg); //remove possible validation msgs
			$msg[]="Problems accessing team : Record Not Found.";
			$action="display";
		} else {
			$row=$result->FetchRow();
			$univ_id=$row['univ_id'];
			$team_code=$row['team_code'];
			$esl=$row['esl'];
			$novice=$row['novice'];
			$active=$row['active'];
			$composite=$row['composite'];

			$result=qp("SELECT * FROM speaker WHERE team_id=? ORDER BY speaker_id ASC", array($team_id));
			if ($result->RecordCount()!=2) {
				unset($msg);//remove possible validation msgs
				$msg[]="Problems accessing speaker : Record Not Found.";
			}

			$row1=$result->FetchRow();
			$row2=$result->FetchRow();
			$speaker1id=$row1['speaker_id'];
			$speaker1=$row1['speaker_name'];
			$speaker1esl=$row1['speaker_esl'];
			$speaker1novice=$row1['speaker_novice'];
			$speaker2id=$row2['speaker_id'];
			$speaker2=$row2['speaker_name'];
			$speaker2esl=$row2['speaker_esl'];
			$speaker2novice=$row2['speaker_novice'];
		}
	}

}


switch($action) {
case "add":
	$title.=": Add";
	break;
case "edit":
	$title.=": Edit";
	break;
case "display":
	$title.=": Display";
	break;
case "delete":
	$title.=": Display";
	break;
default:
	$title=": Display";
	$action="display";
}

echo "<h2>$title</h2>\n"; //title

if(isset($msg)) {
	displayMessagesUL(@$msg);
}

//Check for Display
if ($action=="display") {
	//Display Data in Tabular Format
	$query = "SELECT T.team_id, univ_code, team_code, univ_name, S1.speaker_name AS speaker1, S2.speaker_name AS speaker2, S1.speaker_esl AS speaker1esl, S2.speaker_esl AS speaker2esl, esl, S1.speaker_novice as speaker1novice, S2.speaker_novice as speaker2novice, novice, active, composite ";
	$query.= "FROM university AS U, team AS T, speaker AS S1, speaker AS S2 ";
	$query.= "WHERE T.univ_id=U.univ_id AND S1.team_id=T.team_id AND S2.team_id=T.team_id AND S1.speaker_id < S2.speaker_id ";

	$active_query = $query . " AND T.ACTIVE = 'Y' ";
	$orderby= "ORDER BY univ_name, team_code ";
	$result=q($query.$orderby);
	$active_result=q($active_query.$orderby);

	if ($result->RecordCount()==0) {
		//Print Empty Message
		echo "<h3>No Teams Found.</h3>\n";
		echo "<h3><a href=\"input.php?moduletype=team&amp;action=add\">Add New</a></h3>";
	} else {
		//Check whether to display Delete Button
		$showdeleteresult=get_num_rounds();

		if ($showdeleteresult!=0) {
			$showdelete=0;
		} else {
			$showdelete=1;
		}

		//Print Table
		echo "<h3>Total No. of Teams : <span id=\"totalcount\">".$result->RecordCount()."</span> (<span id=\"activecount\">".$active_result->RecordCount()."</span>)</h3>\n";
		echo "<h3><a href=\"input.php?moduletype=team&amp;action=add\">Add New</a></h3>\n";
		echo <<<EndHeader
		<table>
		<tr>
			<th>Team</th>
			<th>University</th>
			<th>Speaker 1</th>
			<th>Speaker 2</th>
			<th>S1 RLS</th>
			<th>S2 RLS</th>
			<th>Team RLS</th>
			<th>S1 Novice?</th>
			<th>S2 Novice?</th>
			<th>Novice Team?</th>
			<th>Active(Y/N)</th>
			<th>Composite(Y/N)</th>
		</tr>
EndHeader;
		while($row=$result->FetchRow()) {
			echo "<tr ".($row['active']=='N' ? "class=\"inactive\"" : "") .">\n";
			echo "\t<td>".$row['univ_code']." ".$row['team_code']."</td>\n";
			echo "\t<td>".$row['univ_name']."</td>\n";
			echo "\t<td>".$row['speaker1']."</td>\n";
			echo "\t<td>".$row['speaker2']."</td>\n";
			echo "\t<td>".$row['speaker1esl']."</td>\n";
			echo "\t<td>".$row['speaker2esl']."</td>\n";
			echo "\t<td>".$row['esl']."</td>\n";
			echo "\t<td>".$row['speaker1novice']."</td>\n";
			echo "\t<td>".$row['speaker2novice']."</td>\n";
			echo "\t<td>".$row['novice']."</td>\n";
			echo "\t<td class='activetoggle' id='team".$row['team_id']."'>".$row['active']."</td>\n";
			echo "\t<td>".$row['composite']."</td>\n";
			echo "\t<td class=\"editdel\"><a href=\"input.php?moduletype=team&amp;action=edit&amp;team_id=".$row['team_id']."\">Edit</a></td>\n";
			if ($showdelete) {
			      echo "\t<td class=\"editdel\"><a href=\"input.php?moduletype=team&amp;action=delete&amp;team_id=".$row['team_id']."\" onClick=\"return confirm('Are you sure?');\">Delete</a></td>";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}

 } else { //Either Add or Edit
     //Display Form and Values
	echo "<form action=\"input.php?moduletype=team\" method=\"POST\">\n";
	echo "<input type=\"hidden\" name=\"actionhidden\" value=\"".$action."\"/>\n";
	if ($action == "edit") {
		echo "<input type=\"hidden\" name=\"team_id\" value=\"".$team_id."\"/>\n";
		echo "<input type=\"hidden\" name=\"speaker1id\" value=".$speaker1id."\"/>\n";
		echo "<input type=\"hidden\" name=\"speaker2id\" value=".$speaker2id."\"/>\n";
		echo "<input type=\"hidden\" name=\"esl\" value=".$esl."\"/>\n";
		echo "<input type=\"hidden\" name=\"novice\" value=".$novice."\"/>\n";
	}
	echo "<label for=\"univ_id\">University</label>\n";
	echo "<select id=\"univ_id\" name=\"univ_id\">\n";
	$result=q("SELECT univ_id,univ_code FROM university ORDER BY univ_code");
	while($row=$result->FetchRow()) {
		if ($row['univ_id']==$univ_id) {
			echo "<option selected value=\"{$row['univ_id']}\">{$row['univ_code']}</option>\n";
		} else {
			echo "<option value=\"{$row['univ_id']}\">{$row['univ_code']}</option>\n";
		}
	}
?>
       </select><br/><br/>
       <label for="team_code">Team Code</label>
       <input type="text" maxlength="50" id="team_code" name="team_code" value="<?= $team_code;?>"/><br/><br/>

	<br /><strong>Speaker Details</strong><br />
	<table>
	<tr><th>&nbsp</th><th>Name</th><th>ESL/EFL</th><th>Novice</th></tr>
	<tr>
		<td>1.</td>
		<td><input maxlength="100" type="text" id="speaker1" name="speaker1" value="<?= $speaker1;?>"/></td>
		<td><select id="speaker1esl" name="speaker1esl">
			<option value="N" <?= ($speaker1esl=="N")?"selected":""?>>No</option>
			<option value="ESL" <?= ($speaker1esl=="ESL")?"selected":""?>>ESL</option>
			<option value="EFL" <?= ($speaker1esl=="EFL")?"selected":""?>>EFL</option>
		</select></td>
		<td><select id="speaker1novice" name="speaker1novice">
			<option value="N" <?= ($speaker1novice=="N")?"selected":""?>>No</option>
			<option value="Y" <?= ($speaker1novice=="Y")?"selected":""?>>Yes</option>
		</select></td>
	</tr>
	<tr>
		<td>2.</td>
		<td><input maxlength="100" type="text" id="speaker2" name="speaker2" value="<?= $speaker2;?>"/></td>
		<td><select id="speaker2esl" name="speaker2esl">
			<option value="N" <?= ($speaker2esl=="N")?"selected":""?>>No</option>
			<option value="ESL" <?= ($speaker2esl=="ESL")?"selected":""?>>ESL</option>
			<option value="EFL" <?= ($speaker2esl=="EFL")?"selected":""?>>EFL</option>
		</select></td>
		<td><select id="speaker2novice" name="speaker2novice">
			<option value="N" <?= ($speaker2novice=="N")?"selected":""?>>No</option>
			<option value="Y" <?= ($speaker2novice=="Y")?"selected":""?>>Yes</option>
		</select></td>
	</tr>
	</table>

	<label for="active">Active</label>
	<select id="active" name="active">
		<option value="Y" <?= ($active=="Y")?"selected":""?>>Yes</option>
		<option value="N" <?= ($active=="N")?"selected":""?>>No</option>
	</select> <br/><br/>

	<label for="composite">Composite</label>
	<select id="composite" name="composite">
		<option value="N" <?= ($composite=="N")?"selected":""?>>No</option>
		<option value="Y" <?= ($composite=="Y")?"selected":""?>>Yes</option>
	</select> <br/><br/>


	<input type="submit" value="<?= ($action=="edit")?"Edit Team":"Add Team" ;?>"/>
	<input type="button" value="Cancel" onClick="location.replace('input.php?moduletype=team')"/>
	</form>

<?PHP

	}
?>
