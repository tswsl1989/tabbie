<?
/******************************************************************************
 File    :   adjud.inc

 Author  :   Deepak Jois

 Purpose :   Handle Adjudicator Add/Edit/Delete. Called from Input Main
           
******************************************************************************/
require("includes/display.inc");

//Get POST values and validate/convert them

$univ_id=trim(@$_POST['univ_id']);
$adjud_name=trim(@$_POST['adjud_name']);
$ranking=trim(@$_POST['ranking']);
$active=strtoupper(trim(@$_POST['active']));
$conflicts=strtoupper(trim(@$_POST['conflicts']));
$actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action

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

    if (!ereg("^[0-9]+$",$ranking)) //Not an integer value
      {
        $msg[]="Ranking not an integer value.";
        $validate=0;
      }

    $conflictarray=preg_split("/,/",$conflicts, -1, PREG_SPLIT_NO_EMPTY);

    while($temp=(trim(array_shift($conflictarray))))
      {
        $query="SELECT * FROM university WHERE univ_code='$temp'";
        $result=mysql_query($query);
        if (mysql_num_rows($result)==0)
	  {
            $msg[]="No such university as '$temp' exists. Please check.";
            $validate=0;
	  }
      }
            

    if ((!$univ_id) || (!$adjud_name)) $validate=0;

    
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
        $adjud_id=trim(@$_GET['adjud_id']);
        $query="DELETE FROM adjudicator WHERE adjud_id='$adjud_id'";
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
        
        $query = "INSERT INTO adjudicator(univ_id, adjud_name, ranking, active, conflicts) ";
        $query.= " VALUES('$univ_id', '$adjud_name', '$ranking',  '$active','$conflicts')";
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
    
    $adjud_id=trim(@$_POST['adjud_id']);
    //Check Validation
    if ($validate==1)
      {
        //Edit Stuff in Database
        $query = "UPDATE adjudicator ";
        $query.= "SET univ_id='$univ_id', adjud_name='$adjud_name', ranking='$ranking', active='$active', conflicts='$conflicts' ";
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
            $conflicts=$row['conflicts'];
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
                
echo "<div id=\"content\">\n";
echo "<h2>$title</h2>\n"; //titlek

displayMessagesUL(@$msg);
   
//Check for Display
if ($action=="display")
  {
    //Display Data in Tabular Format
    $result=mysql_query("SELECT * FROM adjudicator as A,university as U WHERE A.univ_id=U.univ_id ORDER BY adjud_name");
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

	  <h3>Total No. of Adjudicators : <?echo mysql_num_rows($result)?> (<?echo mysql_num_rows($active_result)?>)</h3>

	     <?echo "<h3><a href=\"input.php?moduletype=adjud&amp;action=add\">Add New</a></h3>";?>
	  <table>
	     <tr><th>Name</th><th>University</th><th>Ranking</th><th>Active(Y/N)</th><th>Conflicts</th></tr>
	     <? while($row=mysql_fetch_assoc($result)) { ?>

	  <tr <?if ($row['active']=='N') echo "style=\"color:red\"" ;?>>
	    <td><?echo $row['adjud_name'];?></td>
	<td><?echo $row['univ_code'];?></td>
   <td><?echo $row['ranking'];?></td>
    <td><?echo $row['active'];?></td>
   <td><?echo $row['conflicts'];?></td>
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
       <input type="hidden" name="adjud_id" value="<?echo $adjud_id;?>"/>

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

	   <label for="conflicts">Conflicts (separate by comma)</label>
	   <input type="text" id="conflicts" name="conflicts" value="<?echo $conflicts;?>"></input><br/><br/>

	   <label for="ranking">Ranking</label>
				    <input type="text" id="ranking" name="ranking" value="<?echo $ranking;?>"/><br/><br/>

				    <label for="active">Active</label>
				  <select id="active" name="active">
				  <option value="Y" <?echo ($active=="Y")?"selected":""?>>Yes</option>
				  <option value="N" <?echo ($active=="N")?"selected":""?>>No</option>
				  </select> <br/><br/>

				  <input type="submit" value="<?echo ($action=="edit")?"Edit Adjudicator":"Add Adjudicator" ;?>"/>
				  <input type="button" value="Cancel" onClick="location.replace('input.php?moduletype=adjud')"/>
				  </form>
            
				  <?
            
				  }
?>

</div>
</body>

</html>
