<?
require("includes/display.php");

//Get POST values and validate/convert them

$round_no=trim(@$_POST['round_no']);
$motion=trim(@$_POST['motion']);
$actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action

if (($actionhidden=="add")||($actionhidden=="edit")) //do validation
  {
    $validate=1;
    //Check if they are empty and set the validate flag accordingly

    if (!$round_no) $msg[]="Round Number Missing.";
    if (!$motion) $msg[]="Motion Missing.";
    if ((!$round_no) || (!$motion)) $validate=0;

    
  }

if ($action=="delete")
  { 
        //Delete Stuff
        $round_no=trim(@$_GET['round_no']);
        $query="DELETE FROM motions WHERE round_no='$round_no'";
        $result=mysql_query($query);

        //Check for Error
        if (mysql_affected_rows()==0)
      $msg[]="There were problems deleting : No such record.";
    
    //Change Mode to Display
    $action="display";    
  }

if ($actionhidden=="add")
  {
    //Check Validation
    if ($validate==1)
      {        
        //Add Stuff to Database
        
        $query = "INSERT INTO motions (round_no, motion) ";
        $query.= " VALUES('$round_no', '$motion')";
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
    
    $round_no=trim(@$_POST['round_no']);
    //Check Validation
    if ($validate==1)
      {
        //Edit Stuff in Database
        $query = "UPDATE motions ";
        $query.= "SET motion='$motion' ";
        $query.= "WHERE round_no='$round_no'";
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
   
    if ($actionhidden!="edit")
      {
        $round_no=trim(@$_GET['round_no']); 

        //Extract values from database
        $query="SELECT * FROM motions WHERE round_no='$round_no'";
        $result=mysql_query($query);
        if (mysql_num_rows($result)==0)
      {
            $msg[]="There were some problems editing : Record Not Found ";
            $action="display";
      }
        else
      {
            $row=mysql_fetch_assoc($result);
            $round_no=$row['round_no'];
            $motion=$row['motion'];
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

displayMessagesUL(@$msg);
   
//Check for Display
if ($action=="display")
  {
    //Display Data in Tabular Format
    $result=mysql_query("SELECT * FROM motions ORDER BY round_no");

    if (mysql_num_rows($result)==0)
      {
    //Print Empty Message    
    echo "<h3>No Motions Found.</h3>";
    echo "<h3><a href=\"input.php?moduletype=motion&amp;action=add\">Add New</a></h3>";
      }
    else
      {
                
    //Print Table
    ?>

      <h3>Total No. of Motions : <?echo mysql_num_rows($result)?></h3>

         <?echo "<h3><a href=\"input.php?moduletype=motion&amp;action=add\">Add New</a></h3>";?>
      <table>
         <tr><th>Round Number</th><th>Motion</th></tr>
         <? while($row=mysql_fetch_assoc($result)) { ?>

      <tr>
        <td><?echo $row['round_no'];?></td>
    <td><?echo $row['motion'];?></td>
    <td class="editdel"><a href="input.php?moduletype=motion&amp;action=edit&amp;round_no=<?echo $row['round_no'];?>">Edit</a></td>
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

                  <input type="submit" value="<?echo ($action=="edit")?"Edit Motion":"Add Motion" ;?>"/>
                  <input type="button" value="Cancel" onClick="location.replace('input.php?moduletype=motion')"/>
                  </form>
            
                  <?
            
                  }
?>
