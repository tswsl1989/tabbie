<?
/******************************************************************************
 File    :   manualdraw.inc

 Author  :   Deepak Jois

 Purpose :   Calculate the draw for the current round taking into account all
 the possible rules mentioned in the Tab rules draft.
           
******************************************************************************/
require_once("includes/display.inc");

$action=trim(@$_GET['action']); //Check action
if ($action=="") $action="display";

$debate_id=trim(@$_GET['debate_id']);
$title="Draw : Round ".$nextround;

$query="SHOW TABLES LIKE 'temp%round_$nextround'";
$result=mysql_query($query);

if ((mysql_num_rows($result))!=2) //both or one of the tables don't exist
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
     $venue_id_edit=@$_POST["venueselect"];
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
     //Check for no chief adjudicators and issue warning only
     $chief=0;
     $query = "SELECT A.adjud_id, A.adjud_name ";
     $query .= "FROM adjudicator A ";
     $query .= "LEFT JOIN temp_adjud_round_$nextround T ON A.adjud_id = T.adjud_id ";
     $query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 
        
     $result=mysql_query($query);

     while($row=mysql_fetch_assoc($result))
       {
         $adjud_id=$row['adjud_id'];
            
         if (@$_POST["radio_$adjud_id"]=="chair") 
           {
         if ($chief==0)
           $chief=1;
         else
           {
             $msg[]="ERROR! Attempt to assign two chair adjudicators";
             $validate=0;
             $action="edit";
           }
           }
       }

     //If all okay do the necessary addition

     if ($validate==1)
       {
            
         $query = "SELECT A.adjud_id, A.adjud_name ";
         $query .= "FROM adjudicator A ";
         $query .= "LEFT JOIN temp_adjud_round_$nextround T ON A.adjud_id = T.adjud_id ";
         $query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 
         $result=mysql_query($query);

         while ($row=mysql_fetch_assoc($result))
           {
         $adjud_id=$row['adjud_id'];

         if (@$_POST["radio_$adjud_id"]!="none")
           {
             $status=@$_POST["radio_$adjud_id"];
             $validate=1;
             if ($status=="chair")
               {
             //check for duplicate chief
             $query="SELECT adjud_id FROM temp_adjud_round_$nextround WHERE debate_id='$debate_id' AND status='chair'";
             $chiefresult=mysql_query($query);
             if (mysql_num_rows($chiefresult)>0)
               {
                 $rowchief=mysql_fetch_assoc($chiefresult);
                 if ((@$_POST["check_{$rowchief['adjud_id']}"])!="on")
                   {
                 $validate=0;
                 $msg[]="ERROR! Attempt to assign more than one Chair Adjudicator";
                 $action="edit";
                   }
               }
               }

             if ($validate==1)
               {
             $query="INSERT INTO temp_adjud_round_$nextround VALUES('$debate_id','$adjud_id','$status')";
             mysql_query($query);
               }
           }
           }

         //Delete
         $query="SELECT  A.adjud_id AS adjud_id,adjud_name,status FROM adjudicator AS A,temp_adjud_round_$nextround AS T WHERE A.adjud_id=T.adjud_id AND T.debate_id=$debate_id ORDER BY status";

         $result=mysql_query($query);

         while($row=mysql_fetch_assoc($result))
           {
         $adjud_id=$row['adjud_id'];

         if (@$_POST["check_$adjud_id"]=="on")
           {
             $query="DELETE FROM temp_adjud_round_$nextround WHERE adjud_id='$adjud_id'";
             mysql_query($query);
           }
           }

            
       }
    

     if ($action=="doedit") 
       {
         //set action to display
         $action="display";
         $lastmodified=$debate_id;

         //check allocation status and issue messages accordingly
         $query = "SELECT v.venue_name, COUNT( a.adjud_id ) AS num_panel FROM venue v, temp_draw_round_$nextround d, temp_adjud_round_$nextround a WHERE v.venue_id = d.venue_id AND d.debate_id = a.debate_id AND a.status = 'panelist' GROUP BY v.venue_id HAVING num_panel < 2 ";
         $result=mysql_query($query);
         while($rownumpanel=mysql_fetch_assoc($result))
           {
         $venue=$rownumpanel['venue_name'];
         $msg[]="$venue has only one panelist";
           }
         
         $query = "SELECT ";
        
       }
    
       }

     if ($action=="finalise")
       {
     //check for all conditions satisfied
     $validate=1;
     $query = "SELECT DISTINCT COUNT(*) AS numadjud ";
     $query .= "FROM temp_adjud_round_$nextround ";
     $query .= "WHERE STATUS = 'chair'";

     $resultnumadjud=mysql_query($query);
     $rownumadjud=mysql_fetch_assoc($resultnumadjud);
     $adjudcount=$rownumadjud['numadjud']; //count chief adjudicators

       
     $query = "SELECT COUNT(*) AS numdebates ";
     $query .= "FROM temp_draw_round_$nextround ";

     $resultnumdebates=mysql_query($query);
     $rownumdebates=mysql_fetch_assoc($resultnumdebates);
     $debatecount=$rownumdebates['numdebates'];

     if ($adjudcount!=$debatecount) //No. of debates and chief adjudicators dont match
       {
         $validate=0;
         $msg[]="ERROR! There are debates with no Chair Adjudicator Allocated";
       }
       
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

         $query = "CREATE TABLE adjud_round_$nextround ( `debate_id` MEDIUMINT NOT NULL ,";
         $query .= " `adjud_id` MEDIUMINT NOT NULL ,";
         $query .= " `status` ENUM( 'chair', 'panelist', 'trainee' ) NOT NULL );";
         $result=mysql_query($query);

         //Insert Values
         $query="INSERT INTO draw_round_$nextround SELECT * FROM temp_draw_round_$nextround";
         mysql_query($query);
                        
         $query="INSERT INTO adjud_round_$nextround SELECT * FROM temp_adjud_round_$nextround";
         mysql_query($query);
            
         //Delete Temporary Tables
         $query="DROP TABLE temp_draw_round_$nextround";
         mysql_query($query);
         $query="DROP TABLE temp_adjud_round_$nextround";
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

include("header.inc");

echo "<div id=\"content\">";
echo "<h2>$title</h2>";

displayMessagesUL(@$msg);

if ($exist)
  {    
    if ($action=="edit")
      {
        //Display Debate Details
        $query="SELECT  v.venue_id,venue_name,t1.team_code as ogtc,u1.univ_code as oguc,t2.team_code as ootc,u2.univ_code as oouc,t3.team_code as cgtc,u3.univ_code as cguc,t4.team_code as cotc,u4.univ_code as couc, t1.team_id AS ogid, t2.team_id AS ooid, t3.team_id AS cgid, t4.team_id AS coid  ";
        $query.="FROM temp_draw_round_$nextround d, team t1,team t2, team t3, team t4, university u1, university u2, university u3, university u4, venue v ";
        $query.="WHERE d.venue_id=v.venue_id AND d.og=t1.team_id AND t1.univ_id=u1.univ_id ";
        $query.= "AND d.oo=t2.team_id AND t2.univ_id=u2.univ_id AND d.cg=t3.team_id AND t3.univ_id=u3.univ_id ";
        $query.= "AND d.co=t4.team_id AND t4.univ_id=u4.univ_id AND d.debate_id='$debate_id'";
        
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
    $venue_id=$row['venue_id'];
    $venue_name=$row['venue_name'];
    
    $ogpoints=0;
    for ($i=1; $i<$nextround; $i++)
    {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ogid']}' ";
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
    {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ooid']}' ";
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
    {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['cgid']}' ";
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
    {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['coid']}' ";
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
    echo "<h3>Opening Government : ".$row['oguc']." ".$row['ogtc']." (".$ogpoints.")</h3>";
    echo "<h3>Opening Opposition : ".$row['oouc']." ".$row['ootc']." (".$oopoints.")</h3>";
    echo "<h3>Closing Government : ".$row['cguc']." ".$row['cgtc']." (".$cgpoints.")</h3>";
    echo "<h3>Opening Government : ".$row['couc']." ".$row['cotc']." (".$copoints.")</h3>";
        echo "</div>\n";
            
        //For the purpose of testing of conflicts
        $oguc=$row['oguc'];
        $oouc=$row['oouc'];
        $cguc=$row['cguc'];
        $couc=$row['couc'];
        
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
        echo "<h3>Allocated Adjudicators</h3>\n";
        
        //read all adjudicators from present round along with their status
        $query="SELECT  A.adjud_id AS adjud_id,adjud_name,status,A.ranking AS ranking,A.conflicts FROM adjudicator AS A,temp_adjud_round_$nextround AS T WHERE A.adjud_id=T.adjud_id AND T.debate_id=$debate_id ORDER BY status";
        $result=mysql_query($query);

        if (mysql_num_rows($result)!=0)
      {
            echo "<table>\n";
            echo "<th>Remove(Y/N)</th><th>Adjudicator</th><th>Status</th><th>Ranking</th><th>Conflicts</th>\n";
            while($row=mysql_fetch_assoc($result))    
          {
                echo "<tr>";
        echo "<td><input type=\"checkbox\" name=\"check_{$row['adjud_id']}\"/></td>\n";

        //check for conflict and print name accordingly
        $conflicts=preg_split("/,/",$row['conflicts'],-1, PREG_SPLIT_NO_EMPTY);

            for ($x=0;$x<count($conflicts);$x++)
              $conflicts[$x]=trim($conflicts[$x]);
        

        if (((in_array($oguc,$conflicts))||
             (in_array($oouc,$conflicts))||
             (in_array($cguc,$conflicts))||
             (in_array($couc,$conflicts))))//conflicts occuring
          {
            //Red Color
            $starttag="<span style=\"color:red\">";
            $endtag="</span>";
          }
        else
          {
            //Set it to empty
            $starttag="";
            $endtag="";
          }
                   
        echo "<td>$starttag{$row['adjud_name']}$endtag</td>";
                    
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['ranking']}</td>";
        echo ($row['conflicts'])?"<td>{$row['conflicts']}</td>":"<td><b>NONE</b></td>";
                    
                echo "</tr>";    
          }
            echo "</table>\n";
      }
        else
      {
            echo "<p>No Adjudicators Assigned</p>";
      }
        echo "</div>";

        echo "<div>";
    echo "<h3>Adjudicator Pool</h3>";
    $query = "SELECT A.adjud_id, A.adjud_name, A.ranking, A.conflicts ";
    $query .= "FROM adjudicator A ";
    $query .= "LEFT JOIN temp_adjud_round_$nextround T ON A.adjud_id = T.adjud_id ";
    $query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 
            
    $result=mysql_query($query);
    if (mysql_num_rows($result)!=0)
      {
        echo "<table>\n";
        echo "<tr><th>Chair</th><th>Panelist</th><th>Trainee</th><th>None</th><th>Name</th><th>Ranking</th><th>Conflicts</th></tr>\n";
        while($row=mysql_fetch_assoc($result))    
          {
        echo "<tr>\n";
        echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"chair\"/></td>\n";
        echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"panelist\"/></td>\n";
        echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"trainee\"/></td>\n";
        echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"none\" checked=\"checked\"/></td>\n";

        //check for conflict and print name accordingly
        $conflicts=preg_split("/,/",$row['conflicts'],-1, PREG_SPLIT_NO_EMPTY);
        for ($x=0;$x<count($conflicts);$x++)
          $conflicts[$x]=trim($conflicts[$x]);


        if (((in_array($oguc,$conflicts))||
             (in_array($oouc,$conflicts))||
             (in_array($cguc,$conflicts))||
             (in_array($couc,$conflicts))))//conflicts occuring
          {
            //Red Color
            $starttag="<span style=\"color:red\">";
            $endtag="</span>";
          }
        else
          {
            //Set it to empty
            $starttag="";
            $endtag="";
                          
          }
                           

        echo "<td>$starttag{$row['adjud_name']}$endtag</td>\n";
                        
        echo "<td>{$row['ranking']}</td>\n";
        echo ($row['conflicts'])?"<td>{$row['conflicts']}</td>":"<td><b>NONE</b></td>";
        echo "</tr>\n";
          }
        echo "</table>\n";    
               
      }
            
    else
      {
        echo "<p>No more Adjudicators left!</p>\n";
      }
                
            
        echo "</div>\n";
        echo "<br/><input type=\"submit\" value=\"Modify Allocation\"/>";
        echo " <input type=\"button\" value=\"Cancel\" onClick=\"location.replace('draw.php?moduletype=manualdraw')\"/>";
        echo "</form>\n";
        
       
      }

    if ($action=="display")
      {
        echo "<h3><a href=\"freeadjud.php?nextround=$nextround\" target=\"_new\">List of Free Adjudicators</a></h3>";
    //Display the table of calculated draw
        $query = 'SELECT A1.debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ';
        $query .= "FROM temp_draw_round_$nextround AS A1, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue ";
        $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND A1.venue_id=venue.venue_id "; 
        $query .= "ORDER BY venue_name";
        

        $result=mysql_query($query);
    
        if ($result)
      {
            echo "<table id=\"manualdraw\">\n";
        echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Total Points</th><th>Chair</th><th>Panelists</th><th>Trainee</th></tr>\n";

            while($row=mysql_fetch_assoc($result))
          {
                
        echo "<tr>\n";
        $highlightlastmodified=((@$lastmodified)&&($row['debate_id']==$lastmodified));
                   $ogpoints=0;
                   for ($i=1; $i<$nextround; $i++)
          {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ogid']}' ";
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
          {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['ooid']}' ";
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
          {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['cgid']}' ";
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
          {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = '{$row['coid']}' ";
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
        //this whole range-query thing seems to be a way to highlight funny results in the draw
        //but - these should be checked automatically, not like this.
        //in other words: throw it out
        $rangequery = "SELECT * FROM highlight WHERE type = 'draw' ";
        $rangeresult = mysql_query($rangequery);
        $rangerow = mysql_fetch_assoc($rangeresult);
        if ($rangerow)
        {    $lowerlimit = $rangerow['lowerlimit'];
            $upperlimit = $rangerow['upperlimit'];
        }
        else
        {    $lowerlimit = '0';
            $upperlimit = '100';
        }
        if (($totalpoints >= $lowerlimit) AND ($totalpoints <= $upperlimit))
            $highlight = 1;
        else
            $highlight = 0;

        $text = ($highlight==1 ? "<td style=\"color:blue\">" : "<td>");
        $text = (($highlightlastmodified) ? "<td style=\"color:green\">" : "<td>");
        echo "$text"."{$row['venue_name']}</td>\n";
        echo "$text"."{$row['ogtc']} {$row['ogt']} <br/> ($ogpoints) </td>\n";
        echo "$text"."{$row['ootc']} {$row['oot']} <br/> ($oopoints) </td>\n";
        echo "$text"."{$row['cgtc']} {$row['cgt']} <br/> ($cgpoints) </td>\n";
                echo "$text"."{$row['cotc']} {$row['cot']} <br/> ($copoints) </td>\n";
                echo "$text"."$totalpoints </td>\n";                
                
                //For the purpose of finding conflicts
                $oguc=$row['ogtc'];
                $oouc=$row['ootc'];
                $cguc=$row['cgtc'];
                $couc=$row['cotc'];

                //Find Chief Adjudicatmr
                $query="SELECT A.adjud_name AS adjud_name, A.conflicts AS conflicts FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='chair' AND T.debate_id='{$row['debate_id']}'";
                $resultadjud=mysql_query($query);

                if (mysql_num_rows($resultadjud)==0)
        echo "$text"."<b>NONE</b></td>";
                else
          {
                   
                    $rowadjud=mysql_fetch_assoc($resultadjud);
                    //check for conflict and print name accordingly
            $conflicts=preg_split("/,/",$rowadjud['conflicts'],-1, PREG_SPLIT_NO_EMPTY);
            for ($x=0;$x<count($conflicts);$x++)
              $conflicts[$x]=trim($conflicts[$x]);


            if (((in_array($oguc,$conflicts))||
             (in_array($oouc,$conflicts))||
             (in_array($cguc,$conflicts))||
             (in_array($couc,$conflicts))))//conflicts occuring
              {
            //Red Color
            $starttag="<span style=\"color:red\">";
            $endtag="</span>";
              }
            else
              {
            //Set it to empty
            $starttag="";
            $endtag="";
              }
                    echo "$text"."$starttag{$rowadjud['adjud_name']}$endtag</td>";
          }

                //Find Panelists
                $query="SELECT A.adjud_name AS adjud_name, A.conflicts AS conflicts FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='panelist' AND T.debate_id='{$row['debate_id']}'";
                $resultadjud=mysql_query($query);

                if (mysql_num_rows($resultadjud)==0)
          echo "$text"."<b>NONE</b></td>";
                else
          {
                    echo "$text"."<ul>\n";
                    while($rowadjud=mysql_fetch_assoc($resultadjud))
              {
                        //check for conflict and print name accordingly
                        $conflicts=preg_split("/,/",$rowadjud['conflicts'],-1, PREG_SPLIT_NO_EMPTY);
            for ($x=0;$x<count($conflicts);$x++)
              $conflicts[$x]=trim($conflicts[$x]);


                        if (((in_array($oguc,$conflicts))||
                 (in_array($oouc,$conflicts))||
                 (in_array($cguc,$conflicts))||
                 (in_array($couc,$conflicts))))//conflicts occuring
              {
                //Red Color
                $starttag="<span style=\"color:red\">";
                $endtag="</span>";
              }
            else
              {
                //Set it to empty
                $starttag="";
                $endtag="";
              }
                       
                        echo "<li>$starttag{$rowadjud['adjud_name']}$endtag</li>\n";
              }
                    echo "</ul></td>\n";
          }

                //Find Trainees
                $query="SELECT A.adjud_name AS adjud_name, A.conflicts AS conflicts FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='trainee' AND T.debate_id='{$row['debate_id']}'";
                $resultadjud=mysql_query($query);

                if (mysql_num_rows($resultadjud)==0)
          echo "$text"."<b>NONE</b></td>";
                else
          {
                    echo "$text"."<ul>\n";
                    while($rowadjud=mysql_fetch_assoc($resultadjud))
              {
                        //check for conflict and print name accordingly
                        $conflicts=preg_split("/,/",$rowadjud['conflicts'],-1, PREG_SPLIT_NO_EMPTY);
            for ($x=0;$x<count($conflicts);$x++)
              $conflicts[$x]=trim($conflicts[$x]);


            for ($x=0;$x<count($conflicts);$x++)
              $conflicts[$x]=trim($conflicts[$x]);

                        if (((in_array($oguc,$conflicts))||
                 (in_array($oouc,$conflicts))||
                 (in_array($cguc,$conflicts))||
                 (in_array($couc,$conflicts))))//conflicts occuring
              {
                //Red Color
                $starttag="<span style=\"color:red\">";
                $endtag="</span>";
              }
            else
              {
                //Set it to empty
                $starttag="";
                $endtag="";
              }

                        echo "<li>$starttag{$rowadjud['adjud_name']}$endtag</li>\n";

              }
                    echo "</ul></td>\n";
          }
                               
                
                echo "<td class=\"editdel\"><a href=\"draw.php?moduletype=manualdraw&amp;action=edit&amp;debate_id={$row['debate_id']}\">Edit</a></td>";
                
        echo "</tr>\n";
        
          }

        echo "</table>\n";
    
      }

        echo "<a href=\"draw.php?moduletype=manualdraw&amp;action=finalise\" onClick=\"return confirm('The draw cannot be modified once finalised. Do you wish to continue?');\"><h3>Finalise Draw</h3></a>";
      }

  }

?>
</div>
<?php include('customize/footer.inc'); ?>
</body>
</html>
