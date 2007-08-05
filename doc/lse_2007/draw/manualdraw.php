<?
/******************************************************************************
 File    :   manualdraw.inc

 Author  :   Deepak Jois

 Purpose :   Calculate the draw for the current round taking into account all
 the possible rules mentioned in the Tab rules draft.
           
******************************************************************************/

$action=trim($_GET['action']); //Check action
if ($action=="") $action="display";

$debate_id=trim($_GET['debate_id']);
$title="Draw : Round ".$nextround;

$query="SHOW TABLES LIKE 'temp%round_$nextround'";
$result=mysql_query($query);

if ((mysql_num_rows($result))<1) //the table don't exist
  {
    $exist=0;
    $msg[]="Missing tables in database!!";
    $msg[]="Please calcualate the draw first!!";
  }
 else // continue with rest of operation
   {
     $exist=1;

     if (($action=="edit")||($action=="doedit"))
       {
	 //check for debate id. Display Message if invalid
	 $query="SELECT * FROM temp_draw_round_$nextround WHERE debate_id='$debate_id'";
	 $result=mysql_query($query);
	 if (mysql_num_rows($result)==0)
	   $action="display";
	 else
	   {
	     $rowdebate=mysql_fetch_assoc($result);
	     $venue_id=$rowdebate['venue_id'];
	   }
	 
       }
    

     if ($action=="doedit")
       {

	 //Check for venue edit
	 $venue_id_edit=$_POST["venueselect"];
	 if (($venue_id_edit)&&($venue_id_edit!=$venue_id))
	   {
	     //Check if venue valid
	     $query= "SELECT V.venue_id, V.venue_name FROM venue V LEFT OUTER JOIN temp_draw_round_$nextround T ON V.venue_id = T.venue_id WHERE V.active = 'Y' AND T.venue_id IS NULL AND V.venue_id='$venue_id_edit'";
	     $result=mysql_query($query);
	     if (mysql_num_rows($result)==0)
	       {
		 $msg[]="Invalid Venue or Venue already taken.";
	       }
	     else
	       {
		 //Change if valid
		 $query="UPDATE temp_draw_round_$nextround SET venue_id='$venue_id_edit' WHERE debate_id='$debate_id'";
		 $result=mysql_query($query);
		 if (!$result)
		   $msg[]="Some error adding to database: ".mysql_error();
		 else
		   //Update message
		   $msg[]="Venue Updated.";
	       }
	     
	   }
	 
	 $validate=1;
	 	

	 if ($action=="doedit") 
	   {
	     //set action to display
	     $action="display";
	     $lastmodified=$debate_id;

	     //check allocation status and issue messages accordingly
	     $query = "SELECT v.venue_name AS num_panel FROM venue v, temp_draw_round_$nextround d, WHERE v.venue_id = d.venue_id AND d.debate_id = a.debate_id GROUP BY v.venue_id HAVING num_panel < 2 ";
	     $result=mysql_query($query);
	     while($rownumpanel=mysql_fetch_assoc($result))
	       {
		 $venue=$rownumpanel['venue_name'];
		 
	       }
	     
	     $query = "SELECT ";
	    
	   }
	
       }

     if ($action=="finalise")
       {
	 //check for all conditions satisfied
	 $validate=1;
	 
       
	 if ($validate==1)
	   {
	     //create tables
           
	     $query= "CREATE TABLE draw_round_$nextround (";
	     $query.= "debate_id MEDIUMINT(9) NOT NULL ,";
	     $query.= "og MEDIUMINT(9) NOT NULL ,";
	     $query.= "oo MEDIUMINT(9) NOT NULL ,";
	     $query.= "cg MEDIUMINT(9) NOT NULL ,";
	     $query.= "co MEDIUMINT(9) NOT NULL ,";
	     $query.= "venue_id MEDIUMINT(9) NOT NULL ,";
	     $query.= "PRIMARY KEY (debate_id))";
	     $result=mysql_query($query);

	    

	     //Insert Values
	     $query="INSERT INTO draw_round_$nextround SELECT * FROM temp_draw_round_$nextround";
	     mysql_query($query);
                        
	    
	     //Delete Temporary Tables
	     $query="DROP TABLE temp_draw_round_$nextround";
	     mysql_query($query);
	     
        
	     //Redirect
	     header("Location: draw.php?moduletype=round&action=showdraw&roundno=$nextround");
            
	   }
	 $action="display";
       }

     //set title
     switch($action)
       {
       case 'edit':
	 $title="Manual Draw Round $nextround : Edit Adjudicator Allocation";
	 break;

       case 'display':
	 $title="Manual Draw Round $nextround : Display";
	 break;

       default:
	 $action="display";
	 $title="Manual Draw Round $nextround : Display";
	 break;
            
       }
    
   }

include("./lse_dev/draw/header.php");

echo "<div id=\"content\">";
echo "<h2>$title</h2>";

//Display Messages
if (count($msg)>0)
{
  echo "<ul class=\"err\">\n";
  for($x=0;$x<count($msg);$x++)
    echo "<li>".$msg[$x]."</li>\n";

  echo "</ul>";

}


if ($exist)
  {    


    if ($action=="display")
      {        
	//Display the table of calculated draw
        $query = 'SELECT A1.debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, venue_name, venue_location, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ';
        $query .= "FROM temp_draw_round_$nextround AS A1, team T1, team T2, team T3, team T4, venue ";
        $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND A1.venue_id=venue.venue_id "; 
        $query .= "ORDER BY venue_name";
        

        $result=mysql_query($query);
    	echo "$result" or die(mysql_error());


        if ($result)
	  {
            echo "<table id=\"manualdraw\">\n";
	    echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Total Points</th></tr>\n";
    
while($row=mysql_fetch_assoc($result))
	      {                
		echo "<tr>\n";
		$highlightlastmodified=(($lastmodified)&&($row['debate_id']==$lastmodified));
               	$ogpoints=0;
               	for ($i=1; $i<$nextround; $i++)
		  {	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ogid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $ogpoints = $ogpoints + 3;
				
		    $pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['ogid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $ogpoints = $ogpoints + 2;
				
		    $pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['ogid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $ogpoints = $ogpoints + 1;
		  }

              	$oopoints=0;
               	for ($i=1; $i<$nextround; $i++)
		  {	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ooid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $oopoints = $oopoints + 3;
		
		    $pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['ooid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $oopoints = $oopoints + 2;
				
		    $pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['ooid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $oopoints = $oopoints + 1;
		  }

                $cgpoints=0;
                for ($i=1; $i<$nextround; $i++)
		  {	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['cgid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $cgpoints = $cgpoints + 3;
				
		    $pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['cgid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $cgpoints = $cgpoints + 2;
		
		    $pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['cgid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $cgpoints = $cgpoints + 1;
		  }
               	$copoints=0;
               	for ($i=1; $i<$nextround; $i++)
		  {	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['coid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $copoints = $copoints + 3;
				
		    $pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['coid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $copoints = $copoints + 2;
				
		    $pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['coid']}' ";
		    $pointsresult=mysql_query($pointsquery);
		    $pointsrow=mysql_fetch_assoc($pointsresult);
		    if ($pointsrow)
		      $copoints = $copoints + 1;
		  }
	
		$totalpoints = $ogpoints + $oopoints + $cgpoints + $copoints;


			$highlight = 0;

		$text = ($highlight==1 ? "<td style=\"color:blue\">" : "<td>");
		$text = (($highlightlastmodified) ? "<td style=\"color:green\">" : "<td>");
		echo "$text"."{$row['venue_name']}</td>\n";
		echo "$text"."{$row['ogtc']} {$row['ogt']} <br/> ($ogpoints) </td>\n";
		echo "$text"."{$row['ootc']} {$row['oot']} <br/> ($oopoints) </td>\n";
		echo "$text"."{$row['cgtc']} {$row['cgt']} <br/> ($cgpoints) </td>\n";
                echo "$text"."{$row['cotc']} {$row['cot']} <br/> ($copoints) </td>\n";
                echo "$text"."$totalpoints </td>\n";                
                                            
                



                echo "<td class=\"editdel\"><a href=\"draw.php?moduletype=manualdraw&amp;action=edit&amp;debate_id={$row['debate_id']}\">Edit</a></td>";
                
		echo "</tr>\n";
        
	      }

	    echo "</table>\n";
    
	  }




        echo "<a href=\"draw.php?moduletype=manualdraw&amp;action=finalise\" onClick=\"return confirm('The draw cannot be modified once finalised. Do you wish to continue?');\"><h3>Finalise Draw</h3></a>";
      }

  }

    if ($action=="edit")
      {
        //Display Debate Details
        $query="SELECT  v.venue_id,venue_name,t1.team_code as ogtc, t2.team_code as ootc, t3.team_code as cgtc, t4.team_code as cotc, t1.team_id AS ogid, t2.team_id AS ooid, t3.team_id AS cgid, t4.team_id AS coid  ";
        $query.="FROM temp_draw_round_$nextround d, team t1,team t2, team t3, team t4, venue v ";
        $query.="WHERE d.venue_id=v.venue_id AND d.og=t1.team_id ";
        $query.= "AND d.oo=t2.team_id AND d.cg=t3.team_id ";
        $query.= "AND d.co=t4.team_id AND d.debate_id='$debate_id'";
        
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
	$venue_id=$row['venue_id'];
	$venue_name=$row['venue_name'];
	
	$ogpoints=0;
	for ($i=1; $i<$nextround; $i++)
	{	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ogid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$ogpoints = $ogpoints + 3;
		
		$pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['ogid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$ogpoints = $ogpoints + 2;
				
		$pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['ogid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$ogpoints = $ogpoints + 1;
	}
	$oopoints=0;
	for ($i=1; $i<$nextround; $i++)
	{	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ooid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$oopoints = $oopoints + 3;
				
		$pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['ooid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$oopoints = $oopoints + 2;
				
		$pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['ooid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$oopoints = $oopoints + 1;
	}
	$cgpoints=0;
	for ($i=1; $i<$nextround; $i++)
	{	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['cgid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$cgpoints = $cgpoints + 3;
				
		$pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['cgid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$cgpoints = $cgpoints + 2;
				
		$pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['cgid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$cgpoints = $cgpoints + 1;
	}
	$copoints=0;
	for ($i=1; $i<$nextround; $i++)
	{	$pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['coid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$copoints = $copoints + 3;
	
		$pointsquery = "SELECT second FROM result_round_$i WHERE second = '{$row['coid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$copoints = $copoints + 2;
				
		$pointsquery = "SELECT third FROM result_round_$i WHERE third = '{$row['coid']}' ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$copoints = $copoints + 1;
	}
		
	$totalpoints = $ogpoints + $oopoints + $cgpoints + $copoints;
	
        echo "<div id=\"debatedetails\">\n";
	echo "<h3>Opening Government : ".$row['ogtc']." (".$ogpoints.")</h3>";
	echo "<h3>Opening Opposition : ".$row['ootc']." (".$oopoints.")</h3>";
	echo "<h3>Closing Government : ".$row['cgtc']." (".$cgpoints.")</h3>";
	echo "<h3>Opening Government : ".$row['cotc']." (".$copoints.")</h3>";
        echo "</div>\n";
            
        //For the purpose of testing of conflicts

        
        echo "<form name=\"manualallocform\" method=\"POST\" action=\"draw.php?moduletype=manualdraw&amp;action=doedit&amp;debate_id=$debate_id\">\n";

        echo "<br/><input type=\"submit\" value=\"Modify Allocation\"/>";
        echo " <input type=\"button\" value=\"Cancel\" onClick=\"location.replace('draw.php?moduletype=manualdraw')\"/>";


	echo "<h3>Venue</h3>\n";


	echo "<select  id=\"venueselect\" name=\"venueselect\" style=\"margin-left:80px\">\n";

	//Display List of available venues
	echo "<option value=\"$venue_id\">$venue_name</option>\n";

	//Load Unoccupied Venues
	$query= "SELECT V.venue_id, V.venue_name FROM venue V LEFT OUTER JOIN temp_draw_round_$nextround T ON V.venue_id = T.venue_id WHERE V.active = 'Y' AND T.venue_id IS NULL";
	$resultvenue=mysql_query($query);
	
	while($rowvenue=mysql_fetch_assoc($resultvenue))
	  echo "<option value=\"{$rowvenue['venue_id']}\">{$rowvenue['venue_name']}</option>\n";
	
       	echo "</select>\n";
        echo "<div>\n";	
    

}
?>
</div>
</body>
</html>
