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

//Get POST values and validate/convert them

$actionhidden="";
$active="";
$venue_name="";
$venue_location="";

if(array_key_exists("venue_name", @$_POST)) $venue_name=makesafe(@$_POST['venue_name']);
if(array_key_exists("venue_location", @$_POST)) $venue_location=makesafe(@$_POST['venue_location']);
if(array_key_exists("active", @$_POST)) $active=strtoupper(trim(@$_POST['active']));
if(array_key_exists("actionhidden", @$_POST)) $actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action

if (($actionhidden=="add")||($actionhidden=="edit")) { //do validation
	$validate=1;
	//Check if they are empty and set the validate flag accordingly

	if (!$venue_name) $msg[]="Venue Name Missing.";
	if (!$venue_location) $msg[]="Venue Location Missing.";
	if ((!$active=='Y') && (!$active=='N')) {
		$msg[]="Active Status not set properly.";
		$validate=0;
	}

	if ((!$venue_name) || (!$venue_location)) $validate=0;
}

if ($action=="delete") {
	//Check for whether debates have started
	if (get_num_rounds()!=0) {
		$msg[]="Debates in progress. Cannot delete now.";
	} else {
		//Delete Stuff
		$venue_id=trim(@$_GET['venue_id']);
		$query="DELETE FROM venue WHERE venue_id=?";
		$result=$DBConn->Execute($query, array($venue_id));

		//Check for Error
		if (!$result) {
			$msg[]="There were problems deleting : No such record.";
			$msg[]=$DBConn->ErrorMsg();
		}
	}

	//Change Mode to Display
	$action="display";
}

if ($actionhidden=="add") {
	//Check Validation
	if ($validate==1) {
	//Add Stuff to Database

		$query = "INSERT INTO venue(venue_name, venue_location,active) VALUES(?, ?, ?)";
		$result = qp($query, array($venue_name, $venue_location, $active));
		if (!$result) {
			$msg[]="There was some problem adding venue: ".$DBConn->ErrorMsg(); //Display Msg
			$action="add";
		} else {
			//If Okay Change Mode to Display
			$msg[]="Record Successfully Added";
			$action="display";
		}
	} else {
		//Back to Add Mode
		$action="add";
	}
}


if ($actionhidden=="edit") {

	$venue_id=trim(@$_POST['venue_id']);
	//Check Validation
	if ($validate==1) {
		//Edit Stuff in Database
		$venue_record['venue_id'] = $venue_id;
		$venue_record['venue_name'] = $venue_name;
		$venue_record['venue_location'] = $venue_location;
		$venue_record['active'] = $active;
		$result = $DBConn->Replace("venue", $venue_record, "venue_id", true);

		//If not okay raise error else change mode to display
		if (!$result) {
			//Raise Error
			$msg[]="There were some problems editing : ".$DBConn->ErrorMsg();
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
	//Check for Venue ID. Issue Error and switch to display if missing or not found
	if ($actionhidden!="edit") {
		$venue_id=trim(@$_GET['venue_id']); //Read in venue_id from querystring
		//Extract values from database
		$query="SELECT * FROM venue WHERE venue_id=?";
		$result=qp($query, array($venue_id));
		if ($result->RecordCount()==0) {
			$msg[]="There were some problems editing venue $venue_id: Record Not Found ";
			$action="display";
		} else {
			$row=$result->FetchRow();
			$venue_name=$row['venue_name'];
			$venue_location=$row['venue_location'];
			$active=$row['active'];
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
	$result=q("SELECT * FROM venue ORDER BY venue_name");
	$active_result = q("SELECT * FROM venue WHERE ACTIVE = 'Y' ");

	if ($result->RecordCount()==0) {
		//Print Empty Message
		echo "<h3>No Venues Found.</h3>";
		echo "<h3><a href=\"input.php?moduletype=venue&amp;action=add\">Add New</a></h3>";
	} else {

		if (get_num_rounds()!=0) {
			$showdelete=0;
		} else {
			$showdelete=1;
		}

		//Print Table
		echo "<h3>Total No. of Venues: ".$result->RecordCount()." (".$active_result->RecordCount().")</h3>\n";
		echo "<h3><a href=\"input.php?moduletype=venue&amp;action=add\">Add New</a></h3>";
		echo <<<EndHeader
		<table>
		<tr>
			<th>Name</th>
			<th>Location</th>
			<th>Active(Y/N)</th>
		</tr>
EndHeader;
		while ($row=$result->FetchRow()) {
			echo "<tr ".($row['active']=='N' ? "style=\"color:red\"" : "").">\n";
			echo "\t<td>".$row['venue_name']."</td>\n";
			echo "\t<td>".$row['venue_location']."</td>\n";
			echo "\t<td class='activetoggle' id='venue".$row['venue_id']."'>".$row['active']."</td>\n";
			echo "\t<td class='editdel'><a href='input.php?moduletype=venue&amp;action=edit&amp;venue_id=".$row['venue_id']."'>Edit</a></td>\n";
			if ($showdelete) {
				echo "\t<td class='editdel'><a href='input.php?moduletype=venue&amp;action=delete&amp;venue_id=".$row['venue_id']." onClick=\"return confirm('Are you sure?')\">Delete</a></td>\n";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
} else { //Either Add or Edit
	//Display Form and Values
	echo "<form action=\"input.php?moduletype=venue\" method=\"POST\">\n";
	echo "<input type=\"hidden\" name=\"actionhidden\" value=\"".$action."\"/>\n";
	echo "<input type=\"hidden\" name=\"venue_id\" value=\"".$venue_id."\"/>\n";
	echo "<label for=\"venue_name\">Venue Name</label>\n";
	echo "<input type=\"text\" id=\"venue_name\" name=\"venue_name\" value=\"".$venue_name."\"/><br/>\n";
	echo "<label for=\"venue_location\">Venue Location</label>\n";
	echo "<input type=\"text\" id=\"venue_location\" name=\"venue_location\" value=\"".$venue_location."\"/><br/>\n";
	echo "<label for=\"active\">Active</label>\n";
	echo "<select id=\"active\" name=\"active\">\n";
	echo "\t<option value=\"Y\" ".($active=="Y" ? "selected" : "").">Yes</option>\n";
	echo "\t<option value=\"N\" ".($active=="N" ? "selected" : "").">No</option>\n";
	echo "</select><br/>\n";
	echo "<input type=\"submit\" value=\"".($action=="edit" ? "Edit Venue" : "Add Venue")."\"/>\n";
	echo "<input type=\"button\" value=\"Cancel\" onClick=\"location.replace('input.php?moduletype=venue')\"/>\n";
	echo "</form>\n";
}
?>
