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

require("includes/display.php");
require_once("includes/backend.php");

//Get POST values and validate/convert them

$actionhidden="";
$rankorder=false;
$univ_id=$adjud_name=$ranking=$active=$status="";
$conflicts="";

if(array_key_exists("univ_id", @$_POST)) $univ_id=trim(@$_POST['univ_id']);
if(array_key_exists("adjud_name", @$_POST)) $adjud_name=makesafe(@$_POST['adjud_name']);
if(array_key_exists("ranking", @$_POST)) $ranking=trim(@$_POST['ranking']);
if(array_key_exists("active", @$_POST)) $active=strtoupper(trim(@$_POST['active']));
if(array_key_exists("actionhidden", @$_POST)) $actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action
if(array_key_exists("rankorder", @$_GET)) $rankorder=trim(@$_GET['rankorder']);
if(array_key_exists("status", @$_POST)) $status=trim(@$_POST['status']);


if (($actionhidden=="add")||($actionhidden=="edit")) { //do validation
	$validate=1;
	//Check if they are empty and set the validate flag accordingly

	if (!$univ_id) $msg[]="University ID Missing.";
	if (!$adjud_name) $msg[]="Adjudicator Name Missing.";

	if ((!$active=='Y') && (!$active=='N'))  {
		$msg[]="Active Status not set properly.";
		$validate=0;
	}

	if ((0 < intval($ranking)) && (intval($ranking) >= 101)) {
		//Not an integer value or out of range
		$msg[]="Ranking not an integer or out of range: ".intval($ranking);
		$validate=0;
	}

	if ((!$univ_id) || (!$adjud_name)) $validate=0;
}

if ($action=="delete") {
	$adjud_id=trim(@$_GET['adjud_id']);
	$msg = delete_adjud($adjud_id);
	$action="display";
}

if ($actionhidden=="add") {
	//Check Validation
	if ($validate==1) {
		//Add Stuff to Database
		$query = "INSERT INTO adjudicator(univ_id, adjud_name, ranking, active, status) VALUES(?, ?, ?, ?, ?)";
		$result=qp($query, array($univ_id, $adjud_name, intval($ranking), $active, $status));

		if (!$result) { //Error
			$msg[]="There was some problem adding adjudicator: ". $DBConn->ErrorMsg(); //Display Msg
			$action="add";
		} else {
			//If Okay Change Mode to Display (but be careful if there's an error adding a strike)
			$msg[]="Record Successfully Added";

			//Get the id of the judge we just added. Hacky but would require a pathological error to break
			$query = "SELECT adjud_id FROM  adjudicator WHERE univ_id=? AND adjud_name=? AND ranking=? AND active=?";
			$result=qp($query, array($univ_id, $adjud_name, $ranking, $active));
			$row=$result->FetchRow();
			$adjud_id=$row['adjud_id'];
			//Strike them from their own institution.
			add_strike_judge_univ($adjud_id,$univ_id);
			if(!is_strike_judge_univ($adjud_id, $univ_id)){
				$msg[]="Failed to conflict judge against their own institution.";
			}
		}
		$action="display";
	} else {
		//Back to Add Mode
		$action="add";
	}
}


if ($actionhidden=="edit") {

	$adjud_id=trim(@$_POST['adjud_id']);
	//Check Validation
	if ($validate==1) {
		//Edit Stuff in Database
		$a_record['adjud_id'] = $adjud_id;
		$a_record['univ_id'] = $univ_id;
		$a_record['adjud_name'] = $adjud_name;
		$a_record['ranking'] = $ranking;
		$a_record['active'] = $active;
		$a_record['status'] = $status;
		$result=$DBConn->Replace("adjudicator", $a_record, "adjud_id", true);

		//If not okay raise error else change mode to display
		if (!$result) {
			//Raise Error
			$msg[]="There were some problems editing adjudicator ".$adjud_id.": ".$DBConn->ErrorMsg();
			$action="edit";
		} else {
			//All okay
			$msg[]="Record edited successfully.";
			$action="display";
		}
	} else {
		//Back to Edit Mode
		$action="edit";
	}
}

if ($action=="edit") {
	//Check for Adjud ID. Issue Error and switch to display if missing or not found
	if ($actionhidden!="edit") {
		$adjud_id=trim(@$_GET['adjud_id']); //Read in adjud_id from querystring

		//Extract values from database
		$result=qp("SELECT * FROM adjudicator WHERE adjud_id=?", array($adjud_id));
		if ($result->RecordCount()==0) {
			$msg[]="There were some problems editing adjudicator $adjud_id: Record Not Found ";
			$action="display";
		} else {
			$row=$result->FetchRow();
			$univ_id=$row['univ_id'];
			$adjud_name=$row['adjud_name'];
			$ranking=$row['ranking'];
			$active=$row['active'];
			$status=$row['status'];
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


echo "<h2>$title</h2>\n"; //titlek

if(isset($msg)) displayMessagesUL(@$msg);

//Check for Display
if ($action=="display") {
//Display Data in Tabular Format
	$query="SELECT * FROM adjudicator as A,university as U WHERE A.univ_id=U.univ_id";
	if($rankorder){
		$query.=" ORDER BY A.ranking DESC";
	} else{
		$query.=" ORDER BY A.adjud_name ASC";
	}
	$result=q($query);
	$active_result=q("SELECT * FROM adjudicator as A,university as U WHERE A.univ_id=U.univ_id AND A.ACTIVE = 'Y' ");

	if ($result->RecordCount()==0) {
		//Print Empty Message    
		echo "<h3>No Adjudicators Found.</h3>";
		echo "<h3><a href=\"input.php?moduletype=adjud&amp;action=add\">Add New</a></h3>";
	} else {

		if (get_num_rounds()!=0) {
			$showdelete=0;
		} else {
			$showdelete=1;
		}

		//Print Table
		echo "<h3>Total No. of Adjudicators: <span id=\"totalcount\">".$result->RecordCount()."</span> (<span id=\"activecount\">".$active_result->RecordCount()."</span>)</h3>\n";
		echo "<h3><a href=\"input.php?moduletype=adjud&amp;action=add\">Add New</a></h3>";
		if($rankorder){
			echo "<h3><a href=\"input.php?moduletype=adjud\">Order by Name</a></h3>";
		} else{
			echo "<h3><a href=\"input.php?moduletype=adjud&amp;rankorder=Y\">Order by Ranking</a></h3>";
		}
		echo<<<EndHeader
		<table>
		<tr>
			<th>Name</th>
			<th>University</th>
			<th>Ranking</th>
			<th>Active(Y/N)</th>
			<th>Status</th>
			<th>Conflicts</th>
		</tr>
EndHeader;
		while($row=$result->FetchRow()) {
			echo "<tr ".($row['active']=='N' ? "class=\"inactive\"" : "").">\n";
			echo "\t<td>".$row['adjud_name']."</td>\n";
			echo "\t<td>".$row['univ_code']."</td>\n";
			echo "\t<td>".$row['ranking']."</td>\n";
			echo "\t<td class='activetoggle' id='adjud".$row['adjud_id']."'>".$row['active']."</td>\n";
			echo "\t<td>".($row['status']=='normal' ? "--" : $row['status'])."</td>\n";
			echo "\t<td>".print_conflicts($row['adjud_id'])."</td>\n";
			echo "<td class=\"editdel\"><a href=\"input.php?moduletype=adjud&amp;action=edit&amp;adjud_id=".$row['adjud_id']."\">Edit</a></td>\n";
			if ($showdelete) {
				echo "<td class=\"editdel\"><a href=\"input.php?moduletype=adjud&amp;action=delete&amp;adjud_id=".$row['adjud_id']."\" onClick=\"return confirm('Are you sure?');\">Delete</a></td>\n";
			}
			echo "</tr>";
		}
		echo "</table>";
	}
} else { //Either Add or Edit
	//Display Form and Values
	echo "<form action=\"input.php?moduletype=adjud\" method=\"POST\">\n";
	echo "<input type=\"hidden\" name=\"actionhidden\" value=\"".$action."\"/>\n";
	if (isset($adjud_id)) {
		echo "<input type=\"hidden\" name=\"adjud_id\" id=\"adjud_id\" value=\"".$adjud_id."\"/>\n";
	}

	echo "<label for=\"adjud_name\">Adjudicator Name</label>";
	echo "<input type=\"text\" id=\"adjud_name\" name=\"adjud_name\" value=\"".$adjud_name."\"/><br/>\n";

	echo "<label for=\"univ_id\">University</label>";
	echo "<select id=\"univ_id\" name=\"univ_id\">\n";
	$query="SELECT univ_id,univ_code FROM university ORDER BY univ_code";
	$result=q($query);
	while($row=$result->FetchRow()) {
		if ($row['univ_id']==$univ_id) {
			echo "<option selected value=\"{$row['univ_id']}\">{$row['univ_code']}</option>\n";
		} else {
			echo "<option value=\"{$row['univ_id']}\">{$row['univ_code']}</option>\n";
		}
	}
	echo "</select><br/>\n";
	echo "<label for=\"ranking\">Ranking</label>";
	echo "<input type=\"text\" id=\"ranking\" name=\"ranking\" value=\"".$ranking."\"/> Value: 0 - 100; Highly ranked adjudicators will be chairs of highly ranked debates.<br/>\n";

	echo "<label for=\"active\">Active</label>";
	echo "<select id=\"active\" name=\"active\">\n";
	echo "\t<option value=\"Y\" ".($active=="Y" ? "selected" : "" )."\">Yes</option>\n";
	echo "\t<option value=\"N\" ".($active=="N" ? "selected" : "" )."\">No</option>\n";
	echo "</select><br/>\n";

	echo "<label for=\"status\">Status</label>\n";
	echo "<select id=\"status\" name=\"status\">\n";
	echo "\t<option value=\"normal\" ".($status=="normal" ? "selected" : "").">normal</option>\n";
	echo "\t<option value=\"trainee\" ".($status=="trainee" ? "selected" : "").">trainee</option>\n";
	echo "\t<option value=\"watcher\" ".($status=="watcher" ? "selected" : "").">watcher</option>\n";
	echo "\t<option value=\"watched\" ".($status=="watched" ? "selected" : "").">watched</option>\n";
	echo "</select><br/>\n";

	echo "<input type=\"submit\" value=\"".($action=="edit" ? "Edit Adjudicator" : "Add Adjudicator")."\"/>\n";
	echo "<input type=\"button\" value=\"Cancel\" onClick=\"location.replace('input.php?moduletype=adjud')\"/>\n";
	echo "</form>\n";

	if($action=="edit"){
		//only do conflicts for existing judges or the AJAX will break.
		echo <<<FormHeader
		<form method=post action="">
		<h4>Conflicts: Select the university and team code.
		Choose "Institutional Strike" to strike all teams from that university.
		</h4>
		<label for="add_univ_id">Add conflict:</label>
		<select id="add_univ_id" name="univ_id">
FormHeader;
	$query="SELECT univ_id,univ_name FROM university ORDER BY univ_name";
	$result=q($query);
	while($row=$result->FetchRow()) {
		if ($row['univ_id']==$univ_id) {
			echo "<option selected value=\"{$row['univ_id']}\">{$row['univ_name']}</option>\n";
		} else {
			echo "<option value=\"{$row['univ_id']}\">{$row['univ_name']}</option>\n";
		}
	}
	echo "</select>";     
	echo <<<FormFooter
	<select id="add_team_code" name="team_code"></select>
	<input type="button" value="Add conflict" id="addstrike"/>
	</form>

	<h5>Current conflicts:</h5>
	<p class="failure"></p>
	<table id="striketable">
	</table>
FormFooter;
	echo "<noscript>".print_conflicts($adjud_id)."</noscript>";
	}
}
?>
