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


if (($actionhidden=="add")||($actionhidden=="edit")) //do validation
  {
    $validate=1;
    //Check if they are empty and set the validate flag accordingly

    if (!$univ_id) $msg[]="University ID Missing.";
    if (!$adjud_name) $msg[]="Adjudicator Name Missing.";
    
    if ((!$active=='Y') && (!$active=='N')) 
      {
        $msg[]="Active Status not set properly.";
        $validate=0;
      }

    if ((0 < intval($ranking)) && (intval($ranking) >= 101)) //Not an integer value or out of range
      {
        $msg[]="Ranking not an integer or out of range: ".intval($ranking);
        $validate=0;
      }

    if ((!$univ_id) || (!$adjud_name)) $validate=0;

    
  }

if ($action=="delete") {
    $adjud_id=trim(@$GET['adjud_id']);
    $msg = delete_adjud($adjud_id);
    $action="display";
}

if ($actionhidden=="add")
  {
    //Check Validation
    if ($validate==1)
      {
        //Add Stuff to Database
        
        $query = "INSERT INTO adjudicator(univ_id, adjud_name, ranking, active, status) ";
        $query.= " VALUES('$univ_id', '$adjud_name', '".intval($ranking)."',  '$active', '$status')";
        $result=mysql_query($query);

        if (!$result) //Error
      	{
            $msg[]="There was some problem adding : ". mysql_error(); //Display Msg
            $action="add";
      	} else {
            //If Okay Change Mode to Display (but be careful if there's an error adding a strike)
            $msg[]="Record Successfully Added";

			//Get the id of the judge we just added. Hacky but would require a pathological error to break
	        $query = "SELECT adjud_id FROM  adjudicator WHERE univ_id='$univ_id' AND adjud_name='$adjud_name' AND ranking='$ranking' AND active='$active'";
			$result=mysql_query($query);
			$row=mysql_fetch_assoc($result);
		    $adjud_id=$row['adjud_id'];
			//Strike them from their own institution.
			add_strike_judge_univ($adjud_id,$univ_id);
			if(!is_strike_judge_univ($adjud_id, $univ_id)){
				$msg[]="Failed to conflict judge against their own institution.";
			}
			
		}
        $action="display";
      }
    else
      {
        //Back to Add Mode
        $action="add";
      }
  }


if ($actionhidden=="edit")
  {
    
    $adjud_id=trim(@$_POST['adjud_id']);
    //Check Validation
    if ($validate==1)
      {
        //Edit Stuff in Database
        $query = "UPDATE adjudicator ";
        $query.= "SET univ_id='$univ_id', adjud_name='$adjud_name', ranking='$ranking', active='$active', status='$status' ";
        $query.= "WHERE adjud_id='$adjud_id'";
        $result=mysql_query($query);
        
        //If not okay raise error else change mode to display
        if (!$result)
      {
            //Raise Error
            $msg[]="There were some problems editing : ".mysql_error();
            $action="edit";
      }
        else
      {
            //All okay
            $msg[]="Record edited successfully.";
            $action="display";
      }
      }

    else
      {
        //Back to Edit Mode
        $action="edit";
      }
  }

if ($action=="edit")
  {
    //Check for Adjud ID. Issue Error and switch to display if missing or not found
    if ($actionhidden!="edit")
      {
        $adjud_id=trim(@$_GET['adjud_id']); //Read in adjud_id from querystring
        

        //Extract values from database
        $query="SELECT * FROM adjudicator WHERE adjud_id='$adjud_id'";
        $result=mysql_query($query);
        if (mysql_num_rows($result)==0)
      {
            $msg[]="There were some problems editing : Record Not Found ";
            $action="display";
      }
        else
      {
            $row=mysql_fetch_assoc($result);
            $univ_id=$row['univ_id'];
            $adjud_name=$row['adjud_name'];
            $ranking=$row['ranking'];
            $active=$row['active'];
			$status=$row['status'];
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
	$query="SELECT * FROM adjudicator as A,university as U WHERE A.univ_id=U.univ_id";
	if($rankorder){
		$query.=" ORDER BY A.ranking DESC";
	} else{
		$query.=" ORDER BY A.adjud_name ASC";
	}
    $result=mysql_query($query);
    $active_result=mysql_query("SELECT * FROM adjudicator as A,university as U WHERE A.univ_id=U.univ_id AND A.ACTIVE = 'Y' ");

    if (mysql_num_rows($result)==0)
      {
    //Print Empty Message    
    echo "<h3>No Adjudicators Found.</h3>";
    echo "<h3><a href=\"input.php?moduletype=adjud&amp;action=add\">Add New</a></h3>";
      }
    else
      {
                
    //Check whether to display Delete Button
    $query="SHOW  TABLES  LIKE  '%_round_%'";
    $showdeleteresult=mysql_query($query);

    if (mysql_num_rows($showdeleteresult)!=0)
      $showdelete=0;
    else
      $showdelete=1;
                
    //Print Table
    ?>

      <h3>Total No. of Adjudicators : <span id="totalcount"><?echo mysql_num_rows($result)?></span> (<span id="activecount"><?echo mysql_num_rows($active_result)?></span>)</h3>

         <?echo "<h3><a href=\"input.php?moduletype=adjud&amp;action=add\">Add New</a></h3>";
		 if($rankorder){
			echo "<h3><a href=\"input.php?moduletype=adjud\">Order by Name</a></h3>";
		} else{
			echo "<h3><a href=\"input.php?moduletype=adjud&amp;rankorder=Y\">Order by Ranking</a></h3>";
		}?>
      <table>
         <tr><th>Name</th><th>University</th><th>Ranking</th><th>Active(Y/N)</th><th>Status</th><th>Conflicts</th></tr>
         <? while($row=mysql_fetch_assoc($result)) { ?>

      <tr <?if ($row['active']=='N') echo "class=\"inactive\"" ;?>>
        <td><?echo $row['adjud_name'];?></td>
    <td><?echo $row['univ_code'];?></td>
   <td><?echo $row['ranking'];?></td>
    <td class='activetoggle' id='adjud<?php echo $row['adjud_id']?>'><?echo $row['active'];?></td>
	<td><?if($row['status']!='normal') echo $row['status'];?></td>
   <td><?echo print_conflicts($row['adjud_id']);?></td>
    <td class="editdel"><a href="input.php?moduletype=adjud&amp;action=edit&amp;adjud_id=<?echo $row['adjud_id'];?>">Edit</a></td>

      <?

       if ($showdelete)
     {
   ?>
   <td class="editdel"><a href="input.php?moduletype=adjud&amp;action=delete&amp;adjud_id=<?echo $row['adjud_id'];?>" onClick="return confirm('Are you sure?');">Delete</a></td>

   <?} //Do Not Remove  ?> 
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
     <form action="input.php?moduletype=adjud" method="POST">
       <input type="hidden" name="actionhidden" value="<?echo $action;?>"/>
       <input type="hidden" name="adjud_id" id="adjud_id" value="<?echo $adjud_id;?>"/>

       <label for="adjud_name">Adjudicator Name</label>
       <input type="text" id="adjud_name" name="adjud_name" value="<?echo $adjud_name;?>"/><br/><br/>

       <label for="univ_id">University</label>
                 <select id="univ_id" name="univ_id">
                 <?
                 $query="SELECT univ_id,univ_code FROM university ORDER BY univ_code";
     $result=mysql_query($query);
     while($row=mysql_fetch_assoc($result))
       {
                            
     if ($row['univ_id']==$univ_id)
       echo "<option selected value=\"{$row['univ_id']}\">{$row['univ_code']}</option>\n";
     else
       echo "<option value=\"{$row['univ_id']}\">{$row['univ_code']}</option>\n";
       }
                            
     ?>
       </select><br/><br/>
<label for="ranking">Ranking</label>
                    <input type="text" id="ranking" name="ranking" value="<?echo $ranking;?>"/> Value: 0 - 100; Highly ranked adjudicators will be chairs of highly ranked debates.<br/><br/>

                    <label for="active">Active</label>
                  <select id="active" name="active">
                  <option value="Y" <?echo ($active=="Y")?"selected":""?>>Yes</option>
                  <option value="N" <?echo ($active=="N")?"selected":""?>>No</option>
                  </select> <br/><br/>

				<label for="status">Status</label>
				<select id="status" name="status">
				<option value="normal" <?echo ($status=="normal")?"selected":""?>>normal</option>
				<option value="trainee" <?echo ($status=="trainee")?"selected":""?>>trainee</option>
				<option value="watcher" <?echo ($status=="watcher")?"selected":""?>>watcher</option>
				<option value="watched" <?echo ($status=="watched")?"selected":""?>>watched</option>
				</select><br/><br/>

                  <input type="submit" value="<?echo ($action=="edit")?"Edit Adjudicator":"Add Adjudicator" ;?>"/>
                  <input type="button" value="Cancel" onClick="location.replace('input.php?moduletype=adjud')"/>
                  </form>

<?php
if($action=="edit"){
	//only do conflicts for existing judges or the AJAX will break.
?>
<form method=post action="">
				       <h4>Conflicts: Select the university and insert the team code. Leave blank to strike all teams from that university.</h4>
						<label for="add_univ_id">Add conflict:</label>
				               <select id="add_univ_id" name="univ_id">
				               <?
				               $query="SELECT univ_id,univ_name FROM university ORDER BY univ_name";
				   $result=mysql_query($query);
				   while($row=mysql_fetch_assoc($result))
				     {

				   if ($row['univ_id']==$univ_id)
				     echo "<option selected value=\"{$row['univ_id']}\">{$row['univ_name']}</option>\n";
				   else
				     echo "<option value=\"{$row['univ_id']}\">{$row['univ_name']}</option>\n";
				     }
                                   echo "</select>";     

				   ?><select id="add_team_code" name="team_code"></select><input type="button" value="Add conflict" id="addstrike"/></form>


				<h5>Current conflicts:</h5>
				<p class="failure"></p>
				<table id="striketable">
				</table>
				<noscript>
				<?PHP echo print_conflicts($adjud_id);?>            
				</noscript>
                  <?
            }
                  }
?>
