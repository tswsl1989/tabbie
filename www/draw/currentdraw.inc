<?
/******************************************************************************
File    :   currentdraw.inc

Author  :   Deepak Jois

Purpose :   Calculate the draw for the current round taking into account all
            the possible rules mentioned in the Tab rules draft.
           
******************************************************************************/
require("includes/display.inc");

$action=trim(@$_GET['action']); //Check action
$title="Draw : Round ".$nextround;
//Check for number of teams and venues
$validate=1;

$query="SELECT COUNT(*) AS numteams FROM team WHERE active='Y'";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numteams=$row['numteams'];
//Check if number of teams is not zero and a multiple of 4
if (($numteams<4)||(($numteams%4)>0))
{
    $validate=0;
    $msg[]="Number of teams is not correct for the draw to take place.Make sure that the number of teams is a multiple of four.";
}

$query="SELECT COUNT(*) FROM venue WHERE active='Y'";
$result=mysql_query($query);
$row=mysql_fetch_row($result);
$numvenues=$row[0];

//Check if number of venues is equal to the number needed
if ($numvenues<($numteams/4))
{
    $validate=0;
    $msg[]="There are not enough venues to accomodate the teams.";
}

//Check if enough number of adjudicators
$query="SELECT COUNT(*) FROM adjudicator WHERE active='Y'";
$result=mysql_query($query);
$row=mysql_fetch_row($result);
$numadjud=$row[0];

//Check if there are enough adjudicators
if ($numadjud<($numteams/4))
{
    $validate=0;
    $msg[]="Number of Adjudicators is not enough. Please activate enough adjudicators.";
    $msg[]="No of Adjudicators : ". $numadjud;
}

//Get Number of  Rounds Completed
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numrounds=mysql_num_rows($result);

//Get Number of Rounds result entered for
$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

if ($numrounds <> $numresults)
{
    $validate=0;
    $msg[]="The results for the last round have not been entered.Please enter the results and then try again.";
}

if (($action=="draw")&&($validate==1))//Check Action : Draw
{

    //Load Teams from Database
    $query="SELECT team_id, univ_id FROM team WHERE active='Y'";
    $result=mysql_query($query);
    
    //Do Random Draw for first round
    if ($nextround==1)
    {
        $query="SELECT team_id, univ_id  FROM team WHERE active='Y'";
        $result=mysql_query($query);
        
        //Load into array
        $index=0;

       while($row=mysql_fetch_assoc($result))
       {
           $teamarray[$index][0]=$row['team_id'];
           $teamarray[$index][1]=$row['univ_id'];
           $index++;
       }

       //Do Random Draw
       for ($x=0; $x<count($teamarray);$x++)
       {
           $randnum=rand(0, count($teamarray)-1); //Calculate Random Number

           //Swap Numbers
           $temp0= $teamarray[$randnum][0]; 
           $temp1= $teamarray[$randnum][1];
           
           $teamarray[$randnum][0]=$teamarray[$x][0];
           $teamarray[$randnum][1]=$teamarray[$x][1];

           $teamarray[$x][0]=$temp0;
           $teamarray[$x][1]=$temp1;
       }
    }
    
    else 
    {	//Do Power Matching Draw for other rounds
    	include("draw2.inc");
    }
       
    //Store draw in temporary database
    
    //create table
    $tablename="temp_draw_round_$nextround";
    $query = "DROP TABLE `$tablename`";
    $result=mysql_query($query);
   
    $query= "CREATE TABLE $tablename (";
    $query.= "debate_id MEDIUMINT(9) NOT NULL ,";
    $query.= "og MEDIUMINT(9) NOT NULL ,";
    $query.= "oo MEDIUMINT(9) NOT NULL ,";
    $query.= "cg MEDIUMINT(9) NOT NULL ,";
    $query.= "co MEDIUMINT(9) NOT NULL ,";
    $query.= "venue_id MEDIUMINT(9) NOT NULL ,";
    $query.= "PRIMARY KEY (debate_id))";
    $result=mysql_query($query);
    if (!$result)
        $msg[]=mysql_error();

        $result1=mysql_query("SELECT venue_id FROM venue WHERE active='Y'");
        while ($row1=mysql_fetch_assoc($result1))
        {
            $venue[]=$row1['venue_id'];//Load Venues
        }

         //randomize order of venues
         for($x=0;$x<count($venue);$x++)
         {
             $randnum=rand(0,count($venue)-1);
             $temp=$venue[$randnum];
             $venue[$randnum]=$venue[$x];
             $venue[$x]=$temp;
         }
         
    $index=0;
    while ($index<count($teamarray))
    {
        //generate debate ID after multiplying current round with 100
        $debate_id=(100*$nextround)+($index/4);
        
        $og=$teamarray[$index++][0];
        $oo=$teamarray[$index++][0];
        $cg=$teamarray[$index++][0];
        $co=$teamarray[$index++][0];
        $venue_id=array_shift($venue);
        
        //insert into database
        $query="INSERT INTO $tablename(debate_id,og,oo,cg,co,venue_id) VALUES('$debate_id','$og','$oo','$cg','$co','$venue_id')";
        $result=mysql_query($query);
       
    }
    include("adjud_alloc.inc");
}


include("header.inc");
echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

displayMessagesUL(@$msg);
         
//Display Draw if required
if (($validate==1))
{
    //Display the table of calculated draw
    $query = 'SELECT A1.debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ';
    $query .= "FROM temp_draw_round_$nextround AS A1, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue ";
    $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND A1.venue_id=venue.venue_id "; 
    $query .= "ORDER BY venue_name";

    $result=mysql_query($query);
    
    if ($result)
    {
        echo "<table>\n";
            echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Total Points</th><th>Chair</th><th>Panelists</th></tr>\n";

        while($row=mysql_fetch_assoc($result))
        {
            echo "<tr>\n";
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
                echo "<td>{$row['venue_name']}</td>\n";
		echo "<td>{$row['ogtc']} {$row['ogt']} <br/> ($ogpoints) </td>\n";
		echo "<td>{$row['ootc']} {$row['oot']} <br/> ($oopoints) </td>\n";
		echo "<td>{$row['cgtc']} {$row['cgt']} <br/> ($cgpoints) </td>\n";
                echo "<td>{$row['cotc']} {$row['cot']} <br/> ($copoints) </td>\n";
                echo "<td>$totalpoints </td>\n";                

                //Find Chief Adjudicator
                $query="SELECT A.adjud_name AS adjud_name FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='chair' AND T.debate_id='{$row['debate_id']}'";
                $resultadjud=mysql_query($query);

                if (mysql_num_rows($resultadjud)==0)
                    echo "<td><b>None Assigned</b></td>";
                else
                {
                    $rowadjud=mysql_fetch_assoc($resultadjud);
                    echo "<td>{$rowadjud['adjud_name']}</td>";
                }

                //Find Panelists
                $query="SELECT A.adjud_name AS adjud_name FROM temp_adjud_round_$nextround AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='panelist' AND T.debate_id='{$row['debate_id']}'";
                $resultadjud=mysql_query($query);

                if (mysql_num_rows($resultadjud)==0)
                    echo "<td><b>None Assigned</b></td>";
                else
                {
                    echo "<td><ul>\n";
                    while($rowadjud=mysql_fetch_assoc($resultadjud))
                    {
                        echo "<li>{$rowadjud['adjud_name']}</li>\n";
                    }
                    echo "</ul></td>\n";
                }

                
                
           echo "</tr>\n";
        
        }

        echo "</table>\n";
    
    }

    //Display Summary of Current Status
        
    //No of Teams
    echo "<h3>No. of Teams : $numteams</h3>";
    //No of Venues
    echo "<h3>No. of Venues : $numvenues</h3>";
    //No of Adjudicators
    echo "<h3>No. of Adjudicators : $numadjud</h3>";

    //Also display the pools in table format
}

//Display Button
    if ($validate==1)
    {    
      echo "<h3><a href=\"draw.php?moduletype=currentdraw&amp;action=draw\">Calculate Draw</a></h3>";
    }

?>
</div>
</body>
</html>
