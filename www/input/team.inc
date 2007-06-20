<?
/******************************************************************************
 File    :   team.inc

 Author  :   Deepak Jois

 Purpose :   Handle Teams/Speakers Add/Edit/Delete. Called from Input Main
           
******************************************************************************/
require("includes/display.inc");

//Get POST values and validate/convert them

$univ_id=trim(@$_POST['univ_id']);
$team_code=trim(@$_POST['team_code']);
$esl=strtoupper(trim(@$_POST['esl']));
$active=strtoupper(trim(@$_POST['active']));
$composite=strtoupper(trim(@$_POST['composite']));
$speaker1=trim(@$_POST['speaker1']);
$speaker2=trim(@$_POST['speaker2']);
$actionhidden=trim(@$_POST['actionhidden']); //Hidden form variable to indicate action


if (($actionhidden=="add")||($actionhidden=="edit")) //do validation
  {
    $validate=1;
    //Check if they are empty and set the validate flag accordingly

    if (!$univ_id) $msg[]="University ID Missing.";
    if (!$team_code) $msg[]="Team Code Missing.";
    if (!$speaker1) $msg[]="Speaker 1 Missing.";
    if (!$speaker2) $msg[]="Speaker 2 Missing.";
    
    if ((!$esl=='Y') && (!$esl=='N')) 
      {
        $msg[]="ESL Status not set properly.";
        $validate=0;
      }

    if ((!$active=='Y') && (!$active=='N')) 
      {
        $msg[]="Active Status not set properly.";
        $validate=0;
      }
   
    if ((!$composite=='Y') && (!$composite=='N')) 
      {
        $msg[]="Composite Status not set properly.";
        $validate=0;
      }

    if (strcasecmp($speaker1, $speaker2)==0)
      {
        $msg[]="Speaker names cannot be equal.";
        $validate=0;
      }

    if (!ereg("^[A-Z]$",$team_code)) //Not a single alphabetic value
      {
        $msg[]="Team Code should be a single letter.";
        $validate=0;
      }

    if ((!$univ_id) || (!$team_code) || (!$speaker1) ||(!$speaker2)) $validate=0;
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
    
        //Delete Stuff (From Both Speaker and Team)
        $team_id=trim(@$_GET['team_id']);
    
        $query1="DELETE FROM speaker WHERE team_id='$team_id'";
        $result1=mysql_query($query1);
	//Check for Error
        if (mysql_affected_rows()==0)
	  $msg[]="There were problems deleting speakers: No such record.";
   
        $query2="DELETE FROM team WHERE team_id='$team_id'";
        $result2=mysql_query($query2);
        //Check for Error
        if (mysql_affected_rows()==0)
	  $msg[]="There were problems deleting team: No such record.";
      }
   
    //Change Mode to Display
    $action="display";    
  }

if ($actionhidden=="add")
  {
    //Check Validation
    if ($validate==1)
      {        
        //Insert Team First
        $query1 = "INSERT INTO team(univ_id, team_code, esl, active, composite) ";
        $query1.= "VALUES('$univ_id','$team_code','$esl','$active','$composite')";
        $result1=mysql_query($query1);

        if ($result1)
	  {
	    $queryteam="SELECT team_id FROM team WHERE univ_id='$univ_id' AND team_code='$team_code'";
	    $resultteam=mysql_query($queryteam);

	    if ($resultteam) 
	      {
		$row=mysql_fetch_assoc($resultteam);
		$team_id=$row['team_id'];
		$query2 = "INSERT INTO speaker(team_id, speaker_name) ";
		$query2.= "VALUES('$team_id','$speaker1'),('$team_id','$speaker2')";
		$result2=mysql_query($query2);

		if (!$result2)
		  {
		    //Error. Go to display
		    unset($msg);
		    $msg[]="Serious Error : Cannot Insert Speakers. ".mysql_error();
		    $action="display";
		  }
		else
		  {
		    $msg[]="Added record successfully";
		  }
	      }
	    else
	      {
		//Error Finding Team. Go to display
		unset($msg);
		$msg[]="Serious Error : Cannot Find Team.".mysql_error();
		$action="display";
	      }
	  }

        else
	  {
            //Error Adding Team. Show error
            $msg[]="Error during insert : ".mysql_error();
            $action="add";
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
    
    $team_id=trim(@$_POST['team_id']);
    $speaker1id=trim(@$_POST['speaker1id']);
    $speaker2id=trim(@$_POST['speaker2id']);
    //Check Validation
    if ($validate==1)
      {
        
        //Edit Team
        $query1 = "UPDATE team ";
        $query1.= "SET univ_id='$univ_id', team_code='$team_code', esl='$esl', active='$active', composite='$composite'";
        $query1.= "WHERE team_id='$team_id'";
        $result1=mysql_query($query1);
        if (!$result1) 
	  $msg[]="Problems editing Team : ".mysql_error();
        
        //Edit Speaker 1
        $query2 = "UPDATE speaker ";
        $query2.= "SET speaker_name='$speaker1'";
        $query2.= "WHERE speaker_id='$speaker1id'";
        $result2=mysql_query($query2);
        if (!$result2)
	  $msg[]="Problems editing Speaker 1 : ".mysql_error();

        //Edit Speaker 2
        $query3 = "UPDATE speaker ";
        $query3.= "SET speaker_name='$speaker2'";
        $query3.= "WHERE speaker_id='$speaker2id'";
        $result3=mysql_query($query3);
        if (!$result3)
	  $msg[]="Problems editing Speaker 3 : ".mysql_error();

        if ((!$result1) || (!$result2) || (!$result3))
	  {    
            $action="edit";
	  }
	else
	  {
	    $msg[]="Record Edited Successfully.";
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
    //Check for Team ID. Issue Error and switch to display if missing or not found
    if ($actionhidden!="edit")
      {
        $team_id=trim(@$_GET['team_id']); //Get team_id from querystring

        //Extract values from database
        $result=mysql_query("SELECT * FROM team WHERE team_id='$team_id'");
        if (mysql_num_rows($result)==0)
	  {
            unset($msg); //remove possible validation msgs
            $msg[]="Problems accessing team : Record Not Found.";
            $action="display";
            
	  }

        else
	  {
            $row=mysql_fetch_assoc($result);
            $univ_id=$row['univ_id'];
            $team_code=$row['team_code'];
            $esl=$row['esl'];
            $active=$row['active'];
            $composite=$row['composite'];

            $result=mysql_query("SELECT * FROM speaker WHERE team_id='$team_id'");
            if (mysql_num_rows($result)!=2)
	      {
                unset($msg);//remove possible validation msgs
                $msg[]="Problems accessing speaker : Record Not Found.";
	      }

            $row1=mysql_fetch_assoc($result);
            $row2=mysql_fetch_assoc($result);
            $speaker1id=$row1['speaker_id'];
            $speaker1=$row1['speaker_name'];
            $speaker2id=$row2['speaker_id'];
            $speaker2=$row2['speaker_name'];

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

displayMessages(@$msg);
   
//Check for Display
if ($action=="display")
  {
    //Display Data in Tabular Format
    $query = "SELECT T.team_id, univ_code, team_code, univ_name, S1.speaker_name AS speaker1, S2.speaker_name AS speaker2, esl, active, composite ";
    $query.= "FROM university AS U, team AS T, speaker AS S1, speaker AS S2 ";
    $query.= "WHERE T.univ_id=U.univ_id AND S1.team_id=T.team_id AND S2.team_id=T.team_id AND S1.speaker_id<S2.speaker_id ";

    $active_query = $query . " AND T.ACTIVE = 'Y' ";
    $query.= "ORDER BY univ_code, team_code ";
            
    $result=mysql_query($query);
    $active_result=mysql_query($active_query);

    if (mysql_num_rows($result)==0)
      {
	//Print Empty Message    
	echo "<h3>No Teams Found.</h3>\n";
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
	    <h3>Total No. of Teams : <?echo mysql_num_rows($result)?> (<?echo mysql_num_rows($active_result)?> )</h3>          
	  <? echo "<h3><a href=\"input.php?moduletype=team&amp;action=add\">Add New</a></h3>";?>      
	      <table>
	      <tr><th>Team</th><th>University</th><th>Speaker 1</th><th>Speaker 2</th><th>ESL(Y/N)</th><th>Active(Y/N)</th><th>Composite(Y/N)</th></tr>
	      <? while($row=mysql_fetch_assoc($result)) { ?>

	  <tr <?if ($row['active']=='N') echo "style=\"color:red\""?>>
	    <td><?echo $row['univ_code']." ".$row['team_code'];?></td>
	     <td><?echo $row['univ_name'];?></td>
	     <td><?echo $row['speaker1'] ?></td>
	     <td><?echo $row['speaker2'] ?></td>
	     <td><?echo $row['esl'];?></td>
	      <td><?echo $row['active'];?></td>
	     <td><?echo $row['composite']?></td>
	      <td class="editdel"><a href="input.php?moduletype=team&amp;action=edit&amp;team_id=<?echo $row['team_id'];?>">Edit</a></td>
	     <?

		  if ($showdelete)
		       {
			 ?>
              <td class="editdel"><a href="input.php?moduletype=team&amp;action=delete&amp;team_id=<?echo $row['team_id'];?>" onClick="return confirm('Are you sure?');">Delete</a></td>
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
            
     <form action="input.php?moduletype=team" method="POST">
       <input type="hidden" name="actionhidden" value="<?echo $action;?>"/>
       <input type="hidden" name="team_id" value="<?echo $team_id;?>"/>
       <input type="hidden" name="speaker1id" value="<?echo $speaker1id;?>"/>
       <input type="hidden" name="speaker2id" value="<?echo $speaker2id;?>"/>

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
                
	   <label for="team_code">Team Code</label>
	   <input type="text" id="team_code" name="team_code" value="<?echo $team_code;?>"/><br/><br/>

	   <label for="speaker1">Speaker 1</label>
				    <input type="text" id="speaker1" name="speaker1" value="<?echo $speaker1;?>"/><br/><br/>
               
				    <label for="speaker2">Speaker 2</label>
				   <input type="text" id="speaker2" name="speaker2" value="<?echo $speaker2;?>"/><br/><br/>

				   <label for="active">Active</label>
							    <select id="active" name="active">
							    <option value="Y" <?echo ($active=="Y")?"selected":""?>>Yes</option>
							    <option value="N" <?echo ($active=="N")?"selected":""?>>No</option>
							    </select> <br/><br/>

							    <label for="esl">ESL</label>
							 <select id="esl" name="esl">
							 <option value="N" <?echo ($esl=="N")?"selected":""?>>No</option>
							 <option value="Y" <?echo ($esl=="Y")?"selected":""?>>Yes</option>
							 </select> <br/><br/>
                
							 <label for="composite">Composite</label>
									       <select id="composite" name="composite">
									       <option value="N" <?echo ($composite=="N")?"selected":""?>>No</option>
									       <option value="Y" <?echo ($composite=="Y")?"selected":""?>>Yes</option>
									       </select> <br/><br/>

               
									       <input type="submit" value="<?echo ($action=="edit")?"Edit Team":"Add Team" ;?>"/>
									       <input type="button" value="Cancel" onClick="location.replace('input.php?moduletype=team')"/>
									       </form>
            
									       <?
            
									       }
?>

</div>
</body>

</html>
