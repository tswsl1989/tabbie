<?
/******************************************************************************
 File    :   univ.inc

 Author  :   Deepak Jois

 Purpose :   Handle Add/Edit/Delete University
           
******************************************************************************/
require("includes/display.inc");

//Get POST values and validate/convert them

$univ_name=trim(@$_POST['univ_name']);
$univ_code=strtoupper(trim(@$_POST['univ_code']));
$actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action

if (($actionhidden=="add")||($actionhidden=="edit")) //do validation
  {
    $validate=1;
    //Check if they are empty and set the validate flag accordingly

    if (!$univ_name) $msg[]="University Name Missing.";
    if (!$univ_code) $msg[]="University Code Missing.";

    if ((!$univ_name) || (!$univ_code)) $validate=0;
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
        $univ_id=trim(@$_GET['univ_id']);
        $query="DELETE FROM university WHERE univ_id='$univ_id'";
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
        
        $query = "INSERT INTO university(univ_name, univ_code) ";
        $query.= " VALUES('$univ_name', '$univ_code')";
        $result=mysql_query($query);

        if (!$result) //Error
	  {
            $msg[]="There was some problem adding : ". mysql_error(); //Display Msg
            $action="add";
	  }

        else
	  {
            //If Okay Change Mode to Display
            $msg[]="Record Successfully Added.";
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
    
    $univ_id=trim(@$_POST['univ_id']);
    //Check Validation
    if ($validate==1)
      {
        //Edit Stuff in Database
        $query = "UPDATE university ";
        $query.= "SET univ_name='$univ_name', univ_code='$univ_code' ";
        $query.= "WHERE univ_id='$univ_id'";
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
    //Check for Univ ID. Issue Error and switch to display if missing or not found
    if ($actionhidden!="edit")
      {
        $univ_id=trim(@$_GET['univ_id']); //Read in venue_id from querystring
        

        //Extract values from database
        $query="SELECT * FROM university WHERE univ_id='$univ_id'";
        $result=mysql_query($query);
        if (mysql_num_rows($result)==0)
	  {
            $msg[]="There were some problems editing : Record Not Found.";
            $action="display";
	  }
        else
	  {
            $row=mysql_fetch_assoc($result);
            $univ_name=$row['univ_name'];
            $univ_code=$row['univ_code'];
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



include("header.inc");

echo "<div id=\"content\">";
                
echo "<h2>$title</h2>\n"; //title

displayMessagesUL(@$msg);
   
//Check for Display
if ($action=="display")
  {
    //Display Data in Tabular Format
    $result=mysql_query("SELECT * FROM university ORDER BY univ_name");

    if (mysql_num_rows($result)==0)
      {
	//Print Empty Message    
	echo "<h3>No Universities Found.</h3>";
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
	    <h3>Total No. of Universities : <?echo mysql_num_rows($result)?></h3>
          <?echo "<h3><a href=\"input.php?moduletype=univ&amp;action=add\">Add New</a></h3>";?>      
	  <table>
	     <tr><th>Name</th><th>Code</th></tr>
	     <? while($row=mysql_fetch_assoc($result)) { ?>

	  <tr>
	    <td><?echo $row['univ_name'];?></td>
       <td><?echo $row['univ_code'];?></td>
	  <td class="editdel"><a href="input.php?moduletype=univ&amp;action=edit&amp;univ_id=<?echo $row['univ_id'];?>">Edit</a></td>
	  <?
	  if ($showdelete)
	    {
         ?>
	    <td class="editdel"><a href="input.php?moduletype=univ&amp;action=delete&amp;univ_id=<?echo $row['univ_id'];?>" onClick="return confirm('Are you sure?');">Delete</a></td>
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
            
     <form action="input.php?moduletype=univ" method="POST">
       <input type="hidden" name="actionhidden" value="<?echo $action;?>"/>
       <input type="hidden" name="univ_id" value="<?echo $univ_id;?>"/>

       <label for="univ_name">University Name</label>
       <input type="text" id="univ_name" name="univ_name" value="<?echo $univ_name;?>"/><br/><br/>

       <label for="univ_code">University Code</label>
				<input type="text" id="univ_code" name="univ_code" value="<?echo $univ_code;?>"/><br/><br/>

				<input type="submit" value="<?echo ($action=="edit")?"Edit University":"Add University" ;?>"/>
				<input type="button" value="Cancel" onClick="location.replace('input.php?moduletype=univ')"/>
				</form>
            
				<?
            
				}
?>
</div>
</body>

</html>
