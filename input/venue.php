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

//Get POST values and validate/convert them

$actionhidden="";
$active="";
$venue_name="";
$venue_location="";

if(array_key_exists("venue_name", @$_POST)) $venue_name=trim(@$_POST['venue_name']);
if(array_key_exists("venue_location", @$_POST)) $venue_location=trim(@$_POST['venue_location']);
if(array_key_exists("active", @$_POST)) $active=strtoupper(trim(@$_POST['active']));
if(array_key_exists("actionhidden", @$_POST)) $actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action

if (($actionhidden=="add")||($actionhidden=="edit")) //do validation
  {
    $validate=1;
    //Check if they are empty and set the validate flag accordingly

    if (!$venue_name) $msg[]="Venue Name Missing.";
    if (!$venue_location) $msg[]="Venue Location Missing.";
    if ((!$active=='Y') && (!$active=='N')) 
      {
        $msg[]="Active Status not set properly.";
        $validate=0;
      }    

    if ((!$venue_name) || (!$venue_location)) $validate=0;
  }

if ($action=="delete")
  {
    //Check for whether debates have started
    $query="SHOW  TABLES  LIKE  '%_round_%'";
    $result=mysql_query($query);

    if (mysql_num_rows($result)!=0)
      $msg[]="Debates in progress. Cannot delete now.";
    else
      {    
    
        //Delete Stuff
        $venue_id=trim(@$_GET['venue_id']);
        $query="DELETE FROM venue WHERE venue_id='$venue_id'";
        $result=mysql_query($query);
    
        //Check for Error
        if (mysql_affected_rows()==0)
      $msg[]="There were problems deleting : No such record.";
      }
    
    //Change Mode to Display
    $action="display";    
  }

if ($actionhidden=="add")
  {
    //Check Validation
    if ($validate==1)
      {        
        //Add Stuff to Database
        
        $query = "INSERT INTO venue(venue_name, venue_location,active) ";
        $query.= " VALUES('$venue_name', '$venue_location', '$active')";
        $result=mysql_query($query);

        if (!$result) //Error
      {
            $msg[]="There was some problem adding : ". mysql_error(); //Display Msg
            $action="add";
      }

        else
      {
            //If Okay Change Mode to Display
            $msg[]="Record Successfully Added";
            $action="display";
      }
      }
    else
      {
        //Back to Add Mode
        $action="add";
      }
  }


if ($actionhidden=="edit")
  {
    
    $venue_id=trim(@$_POST['venue_id']);
    //Check Validation
    if ($validate==1)
      {
        //Edit Stuff in Database
        $query = "UPDATE venue ";
        $query.= "SET venue_name='$venue_name', venue_location='$venue_location', active='$active' ";
        $query.= "WHERE venue_id='$venue_id'";
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
    //Check for Venue ID. Issue Error and switch to display if missing or not found
    if ($actionhidden!="edit")
      {
        $venue_id=trim(@$_GET['venue_id']); //Read in venue_id from querystring
        

        //Extract values from database
        $query="SELECT * FROM venue WHERE venue_id='$venue_id'";
        $result=mysql_query($query);
        if (mysql_num_rows($result)==0)
      {
            $msg[]="There were some problems editing : Record Not Found ";
            $action="display";
      }
        else
      {
            $row=mysql_fetch_assoc($result);
            $venue_name=$row['venue_name'];
            $venue_location=$row['venue_location'];
            $active=$row['active'];
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

echo "<h2>$title</h2>\n"; //title

if(isset($msg)) displayMessagesUL(@$msg);

   
//Check for Display
if ($action=="display")
  {
    //Display Data in Tabular Format
    $result=mysql_query("SELECT * FROM venue ORDER BY venue_name");
    $active_result = mysql_query("SELECT * FROM venue WHERE ACTIVE = 'Y' ");

    if (mysql_num_rows($result)==0)
      {
    //Print Empty Message    
    echo "<h3>No Venues Found.</h3>";
    echo "<h3><a href=\"input.php?moduletype=venue&amp;action=add\">Add New</a></h3>";
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
    <h3>Total No. of Venues : <?echo mysql_num_rows($result)?> (<?echo mysql_num_rows($active_result)?>)</h3>

    <?echo "<h3><a href=\"input.php?moduletype=venue&amp;action=add\">Add New</a></h3>";?>                
      <table>
         <tr><th>Name</th><th>Location</th><th>Active(Y/N)</th></tr>
         <? while($row=mysql_fetch_assoc($result)) { ?>

      <tr <?if ($row['active']=='N') echo "style=\"color:red\"";?>>
        <td><?echo $row['venue_name'];?></td>
            <td><?echo $row['venue_location'];?></td>
        <td><?echo $row['active'];?></td>
        <td class="editdel"><a href="input.php?moduletype=venue&amp;action=edit&amp;venue_id=<?echo $row['venue_id'];?>">Edit</a></td>
    <?
    if ($showdelete)
      {
    ?>
        <td class="editdel"><a href="input.php?moduletype=venue&amp;action=delete&amp;venue_id=<?echo $row['venue_id'];?>" onClick="return confirm('Are you sure?');">Delete</a></td>

       <?} //Do Not Remove ?>
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
            
     <form action="input.php?moduletype=venue" method="POST">
       <input type="hidden" name="actionhidden" value="<?echo $action;?>"/>
       <input type="hidden" name="venue_id" value="<?echo $venue_id;?>"/>

       <label for="venue_name">Venue Name</label>
       <input type="text" id="venue_name" name="venue_name" value="<?echo $venue_name;?>"/><br/><br/>

       <label for="venue_location">Venue Location</label>
                 <input type="text" id="venue_location" name="venue_location" value="<?echo $venue_location;?>"/><br/><br/>

                 <label for="active">Active</label>
                     <select id="active" name="active">
                     <option value="Y" <?echo ($active=="Y")?"selected":""?>>Yes</option>
                     <option value="N" <?echo ($active=="N")?"selected":""?>>No</option>
                     </select> <br/><br/>

                     <input type="submit" value="<?echo ($action=="edit")?"Edit Venue":"Add Venue" ;?>"/>
                     <input type="button" value="Cancel" onClick="location.replace('input.php?moduletype=venue')"/>
                     </form>
            
                     <?
            
                     }
?>
