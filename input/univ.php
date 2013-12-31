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
$univ_name=$univ_code="";

if(array_key_exists("univ_name", @$_POST)) $univ_name=makesafe(@$_POST['univ_name']);
if(array_key_exists("univ_code", @$_POST)) $univ_code=strtoupper(makesafe(@$_POST['univ_code']));
if(array_key_exists("actionhidden", @$_POST)) $actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action

if (($actionhidden=="add")||($actionhidden=="edit")) { //do validation
	$validate=1;
	//Check if they are empty and set the validate flag accordingly
	if (!$univ_name) $msg[]="University Name Missing.";
	if (!$univ_code) $msg[]="University Code Missing.";
	if ((!$univ_name) || (!$univ_code)) $validate=0;
}

if ($action=="delete") {
	//Check for whether debates have started
	if (get_num_rounds()!=0) {
		$msg[]="Debates in progress. Cannot delete now.";
	} else {    
				//Delete Stuff
				$univ_id=trim(@$_GET['univ_id']);
				$query="SELECT * FROM team WHERE univ_id=?";
				$db_result=qp($query, array($univ_id));
				while($row=$db_result->FetchRow()) {
					$team_id=$row["team_id"];
					$mtemp = $row['team_id']." (".$row['team_code'].")";
					$result=delete_team($team_id);
					if ($result) {
						$msg[] = "Deleted team ".$mtemp;
					} else {
						$msg[] = "Unable to delete team ".$mtemp;
					}
				}
				$query="DELETE FROM adjudicator WHERE univ_id=?";
				$db_result=qp($query, array($univ_id));
				if ($db_result) {
					$msg[] = "Successfully deleted adjudicators";
				} else {
					$msg[] = "Problem deleting adjudicators - ".$DBConn->ErrorMsg();
				}
				$query="DELETE FROM university WHERE univ_id=?";
				$db_result=qp($query, array($univ_id));
				//Check for Error
				if ($db_result) {
					$msg[] = "University $univ_id deleted";
				} else {
					$msg[]="There were problems deleting university $univ_id: ".$DBConn->ErrorMsg();
				}
		      }
			//Change Mode to Display
			$action="display";    
		}

if ($actionhidden=="add") {
	//Check Validation
	if ($validate==1) {
		//Add Stuff to Database
		$query = "INSERT INTO university(univ_name, univ_code) VALUES (?, ?)";
		$result = qp($query, array($univ_name, $univ_code));

		if (!$result) { //Error
			$msg[]="There was some problem adding a university: ".$DBConn->ErrorMsg(); //Display Msg
			$action="add";
		} else {
			//If Okay Change Mode to Display
			$msg[]="Record Successfully Added.";
			$action="display";
		}
	} else {
		//Back to Add Mode
		$action="add";
	}
}

if ($actionhidden=="edit") {
	$univ_id=trim(@$_POST['univ_id']);
	//Check Validation
	if ($validate==1) {
		//Edit Stuff in Database
		$uni_record['univ_id'] =  $univ_id;
		$uni_record['univ_name'] = $univ_name;
		$uni_record['univ_code'] = $univ_code;
		$result=$DBConn->Replace("university", $uni_record, "univ_id", true);

		//If not okay raise error else change mode to display
		if (!$result) {
			//Raise Error
			$msg[]="There were some problems editing university $univ_id: ".$DBConn->ErrorMsg();
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
	//Check for Univ ID. Issue Error and switch to display if missing or not found
	if ($actionhidden!="edit") {
		$univ_id=trim(@$_GET['univ_id']); //Read in venue_id from querystring

		//Extract values from database
		$query="SELECT * FROM university WHERE univ_id=?";
		$result=qp($query, array($univ_id));
		if ($result->RecordCount()==0) {
			$msg[]="There were some problems editing : Record Not Found.";
			$action="display";
		} else {
			$row=$result->FetchRow();
			$univ_name=$row['univ_name'];
			$univ_code=$row['univ_code'];
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

if(isset($msg)) displayMessagesUL(@$msg);
   
//Check for Display
if ($action=="display") {
	//Display Data in Tabular Format
	$result=q("SELECT * FROM university ORDER BY univ_name");

	if ($result->RecordCount()==0) {
		//Print Empty Message    
		echo "<h3>No Universities Found.</h3>";
		echo "<h3><a href=\"input.php?moduletype=univ&amp;action=add\">Add New</a></h3>";
	} else {
		//Check whether to display Delete Button
		if (get_num_rounds()!=0) {
			$showdelete=0;
		} else {
			$showdelete=1;
		}
		//Print Table
		echo "<h3>Total No. of Universities: ".$result->RecordCount()."</h3>\n";
		echo "<h3><a href=\"input.php?moduletype=univ&amp;action=add\">Add New</a></h3>\n";
		echo "<table>\n";
		echo "<tr><th>Name</th><th>Code</th></tr>\n";
		while($row=$result->FetchRow()) {
			echo "<tr>";
			echo "<td>".$row['univ_name']."</td>\n";
			echo "<td>".$row['univ_code']."</td>\n";
			echo "<td class=\"editdel\"><a href=\"input.php?moduletype=univ&amp;action=edit&amp;univ_id=".$row['univ_id']."\">Edit</a></td>\n";
			if ($showdelete) {
				echo "<td class=\"editdel\"><a href=\"input.php?moduletype=univ&amp;action=delete&amp;univ_id=".$row['univ_id']."\" onClick=\"return confirm('WARNING: This will delete all teams, adjudicators and speakers for this institution. Are you sure?');\">Delete</a></td>\n";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
} else { //Either Add or Edit
	echo "<form action=\"input.php?moduletype=univ\" method=\"POST\">\n";
	echo "<input type=\"hidden\" name=\"actionhidden\" value=\"".$action."\"/>\n";
	if (isset($univ_id)) {
		echo "<input type=\"hidden\" name=\"univ_id\" value=\"".$univ_id."\"/>\n";
	}
	echo "<label for=\"univ_name\">University Name</label>\n";
	echo "<input type=\"text\" id=\"univ_name\" name=\"univ_name\" value=\"".$univ_name."\"/><br/>\n";
	echo "<label for=\"univ_code\">University Code</label>\n";
	echo "<input type=\"text\" id=\"univ_code\" name=\"univ_code\" value=\"".$univ_code."\"/><br/>\n";
	echo "<input type=\"submit\" value=\"".($action=="edit" ? "Edit University" : "Add University")."\"/>\n";
	echo "<input type=\"button\" value=\"Cancel\" onClick=\"location.replace('input.php?moduletype=univ')\"/>\n";
	echo "</form>\n";
}
