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
$round_no=$motion=$info="";
$info_slide="N";

if(array_key_exists("round_no", @$_POST)) $round_no=trim(@$_POST['round_no']);
if(array_key_exists("motion", @$_POST)) $motion=makesafe(@$_POST['motion']);
if(array_key_exists("info_slide", @$_POST)) $info_slide=trim(@$_POST['info_slide']);
if(array_key_exists("info", @$_POST)) $info=makesafe(@$_POST['info']);
if(array_key_exists("actionhidden", @$_POST)) $actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action

if (($actionhidden=="add")||($actionhidden=="edit")) //do validation
  {
    $validate=1;
	$info_check=true;
    //Check if they are empty and set the validate flag accordingly

    if (!$round_no) $msg[]="Round Number Missing.";
    if (!$motion) $msg[]="Motion Missing.";
	if ($info_slide=="Y" && (!$info))
	{
		$msg[]="Info Text Missing, But Set To Yes.";
		$info_check=false;
	}
	if ($info_slide=="N" && ($info))
	{
		$msg[]="Info Text Present, But Set To No.";
		$info_check=false;
	}
    if ((!$round_no) || (!$motion) || (!$info_check)) $validate=0;


  }

if ($action=="delete") {
	//Delete Stuff
	$round_no=trim(@$_GET['round_no']);
	$query="DELETE FROM motions WHERE round_no=?";
	try {
		$result=qp($query, array($round_no));
	} catch (Exception $e) {
		$msg[]="There were problems deleting: ".$e->getMessage();
	}
	//Check for Error
	if (!$result) {
		$msg[]="There were problems deleting : No such record.";
	}

	//Change Mode to Display
	$action="display";
}

if ($actionhidden=="add") {
	//Check Validation
	if ($validate==1) {
		//Add Stuff to Database
		$query = "INSERT INTO motions (round_no, motion, info_slide, info) VALUES(?, ?, ?, ?)";
		try {
			$result=qp($query, array($round_no, $motion, $info_slide, $info));
		} catch (Exception $e) {
			$msg[]="There was some problem adding: ".$e->getMessage(); //Display Msg
			$action="add";
			$validate=0;
		}
		if ($validate==1) {
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

	$round_no=trim(@$_POST['round_no']);
	//Check Validation
	if ($validate==1) {
		//Edit Stuff in Database
		$query = "UPDATE motions SET motion=?, info_slide=?, info=? WHERE round_no=?";
		$result = false;
		try {
			$result=qp($query, array($motion, $info_slide, $info, $round_no));
		} catch (Exception $e) {
			$msg[] = "An error was encountered: ".$e->getMessage();
			$result=false;
		}
		//If not okay raise error else change mode to display
		if (!$result) {
			//Raise Error
			$msg[]="There were some problems editing : ".get_db_error();
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

	if ($actionhidden!="edit") {
		$round_no=trim(@$_GET['round_no']);

		//Extract values from database
		$query="SELECT * FROM motions WHERE round_no=?";
		$result=false;
		try {
			$result=qp($query, array($round_no));
		} catch (Exception $e) {
			$msg[] = "An error was encountered: ".$e->getMessage();
			$result=false;
		}

		if (!$result) {
			$msg[]="There were some problems editing : Record Not Found ";
			$action="display";
		} else {
			$row=$result->FetchRow();
			$round_no=$row['round_no'];
			$motion=$row['motion'];
			$info_slide=$row['info_slide'];
			$info=$row['info'];
		}
	}

}


switch($action)
  {
  case "add" :
    $title.=": Add";
    break;
  case "edit" :
    $title.=": Edit";
    break;

  case "display" :
    $title.=": Display";
    break;

  case "delete"  :
    $title.=": Display";
    break;
  default :
    $title=": Display";
    $action="display";
  }


echo "<h2>$title</h2>\n"; //titlek

if(isset($msg)) displayMessagesUL(@$msg);

//Check for Display
if ($action=="display")
  {
    //Display Data in Tabular Format
    $result=q("SELECT * FROM motions ORDER BY round_no");

    if ($result->RecordCount()==0)
      {
    //Print Empty Message
    echo "<h3>No Motions Found.</h3>";
    echo "<h3><a href=\"input.php?moduletype=motion&amp;action=add\">Add New</a></h3>";
      }
    else
      {

    //Print Table
    ?>

      <h3>Total No. of Motions : <?echo $result->RecordCount()?></h3>

         <?echo "<h3><a href=\"input.php?moduletype=motion&amp;action=add\">Add New</a></h3>";?>
      <table>
         <tr><th>Round Number</th><th>Motion</th><th>Info Slide</th><th>Text</th></tr>
         <? while($row=$result->FetchRow()) { ?>

      <tr>
        <td><?echo $row['round_no'];?></td>
    <td><?echo $row['motion'];?></td>
	<td><?echo $row['info_slide'];?></td>
	<td><?echo $row['info'];?></td>
    <td class="editdel"><a href="input.php?moduletype=motion&amp;action=edit&amp;round_no=<?echo $row['round_no'];?>">Edit</a></td>
	<td class="editdel"><a href="input.php?moduletype=motion&amp;action=delete&amp;round_no=<?echo $row['round_no'];?>">Delete</a></td>
      </tr>
          <?} //Do Not Remove  ?>
    </table>

  <?
      }
  }

 else //Either Add or Edit
   {

     //Display Form and Values
     ?>
            
     <form action="input.php?moduletype=motion" method="POST">
       <input type="hidden" name="actionhidden" value="<?echo $action;?>"/>
       <? if ($action == "edit") { ?>
        <input type="hidden" name="round_no" value="<?echo $round_no;?>"/>
        <? } else { ?>
       <label for="round_no">Round Number</label>
        <input type="text" name="round_no" value="<?echo $round_no;?>"/><br><br>
        <? } ?>
       <label for="motion">Motion</label>
       <textarea rows="3" cols="60" id="motion" name="motion"><?= $motion ?></textarea><br/><br/>
	   <label for="info_slide">Info Slide</label>
	    <select id="info_slide" name="info_slide">
		 <option value="Y" <?echo ($info_slide=="Y")?"selected":""?>>Yes</option>
         <option value="N" <?echo (($info_slide=="N")||(!$info_slide))?"selected":""?>>No</option>
        </select><br/><br/>
	   <label for="info">Text</label>
	    <textarea type="text" rows="3" cols="60" id="info" name="info"><?= $info ?></textarea><br/><br/>

                  <input type="submit" value="<?echo ($action=="edit")?"Edit Motion":"Add Motion" ;?>"/>
                  <input type="button" value="Cancel" onClick="location.replace('input.php?moduletype=motion')"/>
                  </form>
            
                  <?
            
                  }
?>
