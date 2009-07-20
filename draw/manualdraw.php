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
require_once("includes/db_tools.php");
require_once("includes/http.php");

$action=trim(@$_GET['action']); //Check action
if ($action=="") {
	redirect("draw.php?moduletype=currentdraw");
}

$debate_id=trim(@$_GET['debate_id']);
$title="Draw : Round ".$nextround;

$query="SHOW TABLES LIKE 'temp%round_$nextround'";
$result=mysql_query($query);

if ((mysql_num_rows($result))!=2) //both or one of the tables don't exist
  {
    $exist=0;
    $msg[]="Missing tables in database!!";
    $msg[]="Please calculate the draw first!!";
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

	 $query="SELECT `adjud_id`, `og`, `oo`, `cg`, `co` FROM `temp_adjud_round_$nextround` INNER JOIN `temp_draw_round_$nextround` ON `temp_adjud_round_$nextround`.`debate_id`=`temp_draw_round_$nextround`.`debate_id`";
	 $result=mysql_query($query);
	 if(!($result=mysql_query($query))){
		 $validate=0;
		 $msg[]="ERROR - strike query failed to execute";
	 } else {
		while($row=mysql_fetch_assoc($result)){
			$ogid=$row['og'];
			$ooid=$row['oo'];
			$cgid=$row['cg'];
			$coid=$row['co'];
			$query="SELECT `univ_id` FROM `team` WHERE `team_id` = '$ogid' OR `team_id` = '$ooid' OR `team_id` = '$cgid' OR `team_id` = '$coid'";
			$univ_id_result=mysql_query($query);
			if(mysql_num_rows($univ_id_result)!=4){
				$validate=0;
				$msg[]="ERROR - more than four teams for id (!)";
				$msg[]="You appear to have a corrupted database. Consider restoring from a backup and check previous rounds' data carefully.";
			}
			$univ_ids=array();
			while($univ_id_row=mysql_fetch_assoc($univ_id_result)){
				$univ_ids[]=$univ_id_row['univ_id'];
			}
			$query="SELECT * FROM `strikes` WHERE `adjud_id` = '".$row['adjud_id']."' AND ( (`team_id` = '$ogid' OR `team_id` = '$ooid' OR `team_id` = '$cgid' OR `team_id` = '$coid' ) OR ((`univ_id` = '".$univ_ids[0]."' OR `univ_id` = '".$univ_ids[1]."' OR `univ_id` = '".$univ_ids[2]."' OR `univ_id` = '".$univ_ids[3]."') AND `team_id` IS NULL) )";
			echo("<br/>");
			if(!($strikeresult=mysql_query($query))){
			 $validate=0;
			 $msg[]="ERROR - strike query failed to execute";
		 	} else if(mysql_num_rows($strikeresult)>0){
			 $validate=0;
			 $msg[]="ERROR - strike in draw";
			 $msg[]="Adjudicator ".$row["adjud_id"]." is conflicted from their debate. Either clear the conflict or reallocate the adjudicator to proceed.";
			
			}
		}
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
                        
         $query="INSERT INTO adjud_round_$nextround SELECT `debate_id`, `adjud_id`, `status` FROM temp_adjud_round_$nextround";
         mysql_query($query);

		 //Make non-chair trainees, trainees.
		 $query="UPDATE `adjud_round_$nextround` INNER JOIN `adjudicator` ON `adjud_round_$nextround`.`adjud_id` = `adjudicator`.`adjud_id` SET `adjud_round_$nextround`.`status` = 'trainee' WHERE `adjudicator`.`status` = 'trainee' AND `adjud_round_$nextround`.`status` != 'chair'";
         mysql_query($query);
   
         //Delete Temporary Tables
         $query="DROP TABLE temp_draw_round_$nextround";
         mysql_query($query);
         $query="DROP TABLE temp_adjud_round_$nextround";
         mysql_query($query);
		 $query="DROP TABLE draw_lock_round_$nextround";
		 mysql_query($query);
        
         //Redirect
         
         redirect("draw.php?moduletype=round&action=showdraw&roundno=$nextround");
            
       }
	

     $action="display";
       }
	/*
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
    */
   }

echo "<h2>Draw failed to validate</h2>";

displayMessagesUL(@$msg);

echo "<p>From here you can either:</p>";
echo "<h3><a href=\"draw.php?moduletype=currentdraw&amp;action=draw_adjudicators_again\">Give the computer another shot at allocating the adjudicators (Using the current state to generate a better result).</a></h3>";
echo '<p>or</p>';
echo '<h3><a href="draw.php?moduletype=dragdraw">Manually adjust adjudicators and rooms</a></h3>';

/*
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
    
	$ogpoints = points_for_team($row['ogid'], $numdraws);
    $oopoints = points_for_team($row['ooid'], $numdraws);
    $cgpoints = points_for_team($row['cgid'], $numdraws);
    $copoints = points_for_team($row['coid'], $numdraws);
        
    $totalpoints = $ogpoints + $oopoints + $cgpoints + $copoints;

    
        echo "<div id=\"debatedetails\">\n";
    echo "<h3>Opening Government : ".$row['oguc']." ".$row['ogtc']." (".$ogpoints.")</h3>";
    echo "<h3>Opening Opposition : ".$row['oouc']." ".$row['ootc']." (".$oopoints.")</h3>";
    echo "<h3>Closing Government : ".$row['cguc']." ".$row['cgtc']." (".$cgpoints.")</h3>";
    echo "<h3>Opening Government : ".$row['couc']." ".$row['cotc']." (".$copoints.")</h3>";
        echo "</div>\n";
            
        //For the purpose of testing of conflicts
        $ogid=$row['ogid'];
        $ooid=$row['ooid'];
        $cgid=$row['cgid'];
        $coid=$row['coid'];
        
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
        $query="SELECT  A.adjud_id AS adjud_id,adjud_name,status,A.ranking AS ranking FROM adjudicator AS A,temp_adjud_round_$nextround AS T WHERE A.adjud_id=T.adjud_id AND T.debate_id=$debate_id ORDER BY status";
        $result=mysql_query($query);

        if (mysql_num_rows($result)!=0)
      {
            echo "<table>\n";
            echo "<th>Remove(Y/N)</th><th>Adjudicator</th><th>Status</th><th>Ranking</th><th>Conflicts</th>\n";
            while($row=mysql_fetch_assoc($result)) {
				$adjud_id=$row['adjud_id'];
				echo "<tr>";
        		echo "<td><input type=\"checkbox\" name=\"check_{$row['adjud_id']}\"/></td>\n";
				//check for conflict and print name accordingly
		if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
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
                   
        echo "<td>$starttag{$row['adjud_name']} ({$row['ranking']})$endtag</td>";
                    
        echo "<td>{$row['status']}</td>";
        echo "<td>{$row['ranking']}</td>";
        echo "<td>".print_conflicts($adjud_id)."</td></tr>";
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
    $query = "SELECT A.adjud_id, A.adjud_name, A.ranking ";
    $query .= "FROM adjudicator A ";
    $query .= "LEFT JOIN temp_adjud_round_$nextround T ON A.adjud_id = T.adjud_id ";
    $query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 
            
    $result=mysql_query($query);
echo $query;
    if (mysql_num_rows($result)!=0)
      {
        echo "<table>\n";
        echo "<tr><th>Chair</th><th>Panelist</th><th>Trainee</th><th>None</th><th>Name</th><th>Ranking</th><th>Conflicts</th></tr>\n";
        while($row=mysql_fetch_assoc($result))    
          {
		$adjud_id=$row['adjud_id'];
        echo "<tr>\n";
        echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"chair\"/></td>\n";
        echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"panelist\"/></td>\n";
        echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"trainee\"/></td>\n";
        echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"none\" checked=\"checked\"/></td>\n";

        //check for conflict and print name accordingly
		if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
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
                           

        echo "<td>$starttag{$row['adjud_name']} ({$row['ranking']})$endtag</td>\n";
                        
        echo "<td>{$row['ranking']}</td>\n";
        echo "<td>".print_conflicts($adjud_id)."</td></tr>";
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
        

        $result=mysql_query($query);
    
        if ($result)
      {
            echo "<table id=\"manualdraw\">\n";
        echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Average Points</th><th>Chair</th><th>Panelists</th><th>Trainee</th></tr>\n";
			//Nasty hack to make it display in tab order with average points; fix! - GWJR
			while($row=mysql_fetch_array($result)){
				$row['ogpoints'] = points_for_team($row['ogid'], $numdraws);
			    $row['oopoints'] = points_for_team($row['ooid'], $numdraws);
			    $row['cgpoints'] = points_for_team($row['cgid'], $numdraws);
			    $row['copoints'] = points_for_team($row['coid'], $numdraws);
				$totalpoints = (int)$row['ogpoints'] + (int)$row['oopoints'] + (int)$row['cgpoints'] + (int)$row['copoints'];
				$averagepoints = $totalpoints/4.0;
				$row['averagepoints']=$averagepoints;
				$rowarray[]=$row;
			}
			
			//Assume it's in tab order (!)
			
            foreach($rowarray as $row)
          {
                
        echo "<tr>\n";
        $highlightlastmodified=((@$lastmodified)&&($row['debate_id']==$lastmodified));

        $text = ($highlight==1 ? "<td style=\"color:blue\">" : "<td>");
        $text = (($highlightlastmodified) ? "<td style=\"color:green\">" : "<td>");
        echo "$text"."{$row['venue_name']}</td>\n";
        echo "$text"."{$row['ogtc']} {$row['ogt']} <br/> ("."{$row['ogpoints']}".") </td>\n";
        echo "$text"."{$row['ootc']} {$row['oot']} <br/> ("."{$row['oopoints']}".") </td>\n";
        echo "$text"."{$row['cgtc']} {$row['cgt']} <br/> ("."{$row['cgpoints']}".") </td>\n";
        echo "$text"."{$row['cotc']} {$row['cot']} <br/> ("."{$row['copoints']}".") </td>\n";
        echo "$text"."{$row['averagepoints']} </td>\n";                
                
                //For the purpose of finding conflicts
                $ogid=$row['ogid'];
                $ooid=$row['ooid'];
                $cgid=$row['cgid'];
                $coid=$row['coid'];

                //Find Chief Adjudicator ?chair, surely?
                $query="SELECT A.adjud_name AS adjud_name, A.adjud_id As adjud_id, A.ranking FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='chair' AND T.debate_id='{$row['debate_id']}'";
                $resultadjud=mysql_query($query);

                if (mysql_num_rows($resultadjud)==0) {
       				echo "$text"."<b>NONE</b></td>";
				} else {
					$rowadjud=mysql_fetch_assoc($resultadjud);
					$adjud_id=$rowadjud['$adjud_id'];
                    //check for conflict and print name accordingly
					if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
            			//Red Color
            			$starttag="<span style=\"color:red\">";
            			$endtag="</span>";
              		} else {
            			//Set it to empty
            			$starttag="";
            			$endtag="";
              		}
				echo "$text"."$starttag{$rowadjud['adjud_name']} ({$rowadjud['ranking']})$endtag</td>";
				}

                //Find Panelists
                $query="SELECT A.adjud_name AS adjud_name, A.adjud_id As adjud_id, A.ranking FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='panelist' AND T.debate_id='{$row['debate_id']}'";
                $resultadjud=mysql_query($query);

                if (mysql_num_rows($resultadjud)==0) {
          			echo "$text"."<b>NONE</b></td>";
                } else {
                    echo "$text"."<ul>\n";
                    while($rowadjud=mysql_fetch_assoc($resultadjud))
              		{
                        //check for conflict and print name accordingly
						$adjud_id=$rowadjud['$adjud_id'];
						if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
	            			//Red Color
	            			$starttag="<span style=\"color:red\">";
	            			$endtag="</span>";
	              		} else {
	            			//Set it to empty
	            			$starttag="";
	            			$endtag="";
	              		}
						echo "<li>$starttag{$rowadjud['adjud_name']} ({$rowadjud['ranking']})$endtag</li>\n";
					}
                    echo "</ul></td>\n";
				}

                //Find Trainees
                $query="SELECT A.adjud_name AS adjud_name, A.adjud_id AS adjud_id, A.ranking FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='trainee' AND T.debate_id='{$row['debate_id']}'";
                $resultadjud=mysql_query($query);
                if (mysql_num_rows($resultadjud)==0){
					echo "$text"."<b>NONE</b></td>";
				} else {
                    echo "$text"."<ul>\n";
                    while($rowadjud=mysql_fetch_assoc($resultadjud))
              		{
                        //check for conflict and print name accordingly
						$adjud_id=$rowadjud['$adjud_id'];
						if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
	            			//Red Color
	            			$starttag="<span style=\"color:red\">";
	            			$endtag="</span>";
	              		} else {
	            			//Set it to empty
	            			$starttag="";
	            			$endtag="";
	              		}
						echo "<li>$starttag{$rowadjud['adjud_name']} ({$rowadjud['ranking']})$endtag</li>\n";
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
*/

?>
