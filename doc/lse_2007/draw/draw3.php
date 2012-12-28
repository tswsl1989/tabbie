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

/* ====================================================================== */
/* To input test data into the array in any sequence                      */
/* ====================================================================== */




	$tablename2="powerdraw_master";
    	$query = "DROP TABLE `$tablename2`";
    	$result=mysql_query($query);

	$query = "CREATE TABLE $tablename2 (";
	$query .= "team_id MEDIUMINT(9) NOT NULL default '0' PRIMARY KEY,";
	$query .= "team_points MEDIUMINT(9) default '0',";
	$query .= "team_speaks MEDIUMINT(9) default '0'), ENGINE=InnoDB";
	$result=mysql_query($query);
    		if (!$result){
        		$msg[]=mysql_error();}

	$query ="INSERT INTO $tablename2 (team_id)";
	$query.="SELECT team_id from team";
	$result=mysql_query($query);

/*
	$query="SELECT team_id FROM team WHERE active='Y'";
    	$result=mysql_query($query);
	
	        $index=0;

       while($row=mysql_fetch_assoc($result))
       {
           $team[$index][0]=$row['team_id'];
	   $team_id=$team[$index][0];
	   $query="INSERT INTO $tablename2(team_id) VALUES('$team_id')";
	   $result1=mysql_query($query);           
	   $index++;
       }

*/

$query="SELECT team_id  FROM team WHERE active='Y'";
$result=mysql_query($query);

//Load into array
$index=0;
while($row=mysql_fetch_assoc($result))
{
	$teamsarray[$index][0]=$row['team_id'];
	$teamcode = $teamsarray[$index][0];
	$teams[$index][0] =  $teamsarray[$index][0];
	
	$points = 0;
	for ($i=1; $i<$nextround; $i++)
	{	$pointsquery = "SELECT first FROM result_round_$i WHERE first = $teamcode ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$points = $points + 3;
			
		$pointsquery = "SELECT second FROM result_round_$i WHERE second = $teamcode ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$points = $points + 2;
			
		$pointsquery = "SELECT third FROM result_round_$i WHERE third = $teamcode ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$points = $points + 1;
			
		$pointsquery = "SELECT fourth FROM result_round_$i WHERE fourth = $teamcode ";
		$pointsresult=mysql_query($pointsquery);
		$pointsrow=mysql_fetch_assoc($pointsresult);
		if ($pointsrow)
			$points = $points + 0;
	}
	$teams[$index][1] = $points; 
	
	$query ="UPDATE powerdraw_master SET ";
	$query.="team_points = $points ";
	$query.="WHERE team_id = $teamcode";	
	$result1=mysql_query($query);
	if (!$result1) //Get Error Message
	  	{	$msg[]="Error updating test_points table.".mysql_error();
		
		}

	
	$x=$nextround-1;
	$query ="UPDATE powerdraw_master SET ";
	$query.="team_speaks = (select team_speaks_round_$x from speaks where team_id = $teamcode) where team_id =  $teamcode";
	$result2=mysql_query($query);
	if (!$result2) //Get Error Message
	  	{	$msg[]="Error updating test_speaks table.".mysql_error();
		
		}
	$index++;
}


/* ====================================================================== */
/* To sort the array containing the containing team names and points      */
/* ====================================================================== */

$index=0;
$query ="SELECT * from powerdraw_master order by team_points desc, team_speaks desc";
$result=mysql_query($query);
$count=mysql_num_rows($result);
while($row=mysql_fetch_assoc($result)) {
	    $sorted_array[$index] = array("index" => $index,
	                        "teamid" => $row['team_id'],
	                        "og" => 0,
	                        "oo" => 0,
	                        "cg" => 0,
	                        "co" => 0);
				$index++;   
}



/*
$index=0;

$sorted_array[$index]["teamid"] = $row['team_id'];
$sorted_array[$index]["og"] = '';
$sorted_array[$index]["oo"] = '';
$sorted_array[$index]["cg"] = '';
$sorted_array[$index]["co"] = '';
*/




/*
foreach($teams_1 as $bb) {
			$sorted_array[] = array ("teamid" => $bb[0],
						 "og" => 0,
	                        		 "oo" => 0,
	                        		 "cg" => 0,
	                        		 "co" => 0);
						 
						
			}

*/
/* ====================================================================== */
/* Counting no. of times each team has done previous table positions      */
/* ====================================================================== */



	
$index=0;

	foreach($sorted_array as $cc) 
	{
		$index = $cc["index"];
		$team_id = $cc["teamid"];
		$og = 0;
		$oo = 0;
		$cg = 0;
		$co = 0;

		for ($j=1;$j<$nextround;$j++)
		{
			// Count number of OG
			$posquery = "SELECT og FROM draw_round_$j WHERE og = '$team_id'";
			$posresult = mysql_query($posquery);
			$poscount = mysql_num_rows($posresult);
			if ($poscount > 0)
				$og++;
				
			// Count number of OO
			$posquery = "SELECT oo FROM draw_round_$j WHERE oo = '$team_id'";
			$posresult = mysql_query($posquery);
			$poscount = mysql_num_rows($posresult);
			if ($poscount > 0)
				$oo++;

			// Count number of CG
			$posquery = "SELECT cg FROM draw_round_$j WHERE cg = '$team_id'";
			$posresult = mysql_query($posquery);
			$poscount = mysql_num_rows($posresult);
			if ($poscount > 0)
				$cg++;

			// Count number of CO
			$posquery = "SELECT co FROM draw_round_$j WHERE co = '$team_id'";
			$posresult = mysql_query($posquery);
			$poscount = mysql_num_rows($posresult);
			if ($poscount > 0)
				$co++;
			
		}
		
		$sorted_array[$index]["og"] = $og;
		$sorted_array[$index]["oo"] = $oo;
		$sorted_array[$index]["cg"] = $cg;
		$sorted_array[$index]["co"] = $co;
		
	}
	



/* ====================================================================== */
/* the 24 combi test    					  	  */
/* ====================================================================== */


$x=0;


$validate=0;

$a=0;
$b=1;
$c=2;
$d=3;

$e=0;
$f=1;
$g=2;
$h=3;

for($x=0;$x<count($sorted_array);$x=$x+4)
{

$validate=0;
$total_times_1 = $sorted_array[$a]["og"] + $sorted_array[$b]["oo"] + $sorted_array[$c]["cg"] + $sorted_array[$d]["co"];
$total_times_2 = $sorted_array[$a]["og"] + $sorted_array[$b]["oo"] + $sorted_array[$c]["co"] + $sorted_array[$d]["cg"];
$total_times_3 = $sorted_array[$a]["og"] + $sorted_array[$b]["cg"] + $sorted_array[$c]["oo"] + $sorted_array[$d]["co"];
$total_times_4 = $sorted_array[$a]["og"] + $sorted_array[$b]["co"] + $sorted_array[$c]["oo"] + $sorted_array[$d]["cg"];
$total_times_5 = $sorted_array[$a]["og"] + $sorted_array[$b]["co"] + $sorted_array[$c]["cg"] + $sorted_array[$d]["oo"];
$total_times_6 = $sorted_array[$a]["og"] + $sorted_array[$b]["cg"] + $sorted_array[$c]["co"] + $sorted_array[$d]["oo"];
$total_times_7 = $sorted_array[$a]["oo"] + $sorted_array[$b]["og"] + $sorted_array[$c]["cg"] + $sorted_array[$d]["co"];
$total_times_8 = $sorted_array[$a]["oo"] + $sorted_array[$b]["og"] + $sorted_array[$c]["co"] + $sorted_array[$d]["cg"];
$total_times_9 = $sorted_array[$a]["cg"] + $sorted_array[$b]["og"] + $sorted_array[$c]["oo"] + $sorted_array[$d]["co"];
$total_times_10 = $sorted_array[$a]["co"] + $sorted_array[$b]["og"] + $sorted_array[$c]["oo"] + $sorted_array[$d]["cg"];
$total_times_11 = $sorted_array[$a]["co"] + $sorted_array[$b]["og"] + $sorted_array[$c]["cg"] + $sorted_array[$d]["oo"];
$total_times_12 = $sorted_array[$a]["cg"] + $sorted_array[$b]["og"] + $sorted_array[$c]["co"] + $sorted_array[$d]["oo"];
$total_times_13 = $sorted_array[$a]["oo"] + $sorted_array[$b]["cg"] + $sorted_array[$c]["og"] + $sorted_array[$d]["co"];
$total_times_14 = $sorted_array[$a]["oo"] + $sorted_array[$b]["co"] + $sorted_array[$c]["og"] + $sorted_array[$d]["cg"];
$total_times_15 = $sorted_array[$a]["cg"] + $sorted_array[$b]["oo"] + $sorted_array[$c]["og"] + $sorted_array[$d]["co"];
$total_times_16 = $sorted_array[$a]["co"] + $sorted_array[$b]["oo"] + $sorted_array[$c]["og"] + $sorted_array[$d]["cg"];
$total_times_17 = $sorted_array[$a]["cg"] + $sorted_array[$b]["co"] + $sorted_array[$c]["og"] + $sorted_array[$d]["oo"];
$total_times_18 = $sorted_array[$a]["co"] + $sorted_array[$b]["cg"] + $sorted_array[$c]["og"] + $sorted_array[$d]["oo"];
$total_times_19 = $sorted_array[$a]["oo"] + $sorted_array[$b]["cg"] + $sorted_array[$c]["co"] + $sorted_array[$d]["og"];
$total_times_20 = $sorted_array[$a]["oo"] + $sorted_array[$b]["co"] + $sorted_array[$c]["cg"] + $sorted_array[$d]["og"];
$total_times_21 = $sorted_array[$a]["cg"] + $sorted_array[$b]["oo"] + $sorted_array[$c]["co"] + $sorted_array[$d]["og"];
$total_times_22 = $sorted_array[$a]["co"] + $sorted_array[$b]["oo"] + $sorted_array[$c]["cg"] + $sorted_array[$d]["og"];
$total_times_23 = $sorted_array[$a]["cg"] + $sorted_array[$b]["co"] + $sorted_array[$c]["oo"] + $sorted_array[$d]["og"];
$total_times_24 = $sorted_array[$a]["co"] + $sorted_array[$b]["cg"] + $sorted_array[$c]["oo"] + $sorted_array[$d]["og"];




switch($total_times_1):


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 1; 
		break; } 

	



default:	     

switch($total_times_2):

	case 0: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 1; 
		break; }

			
	


default:

switch($total_times_3):

	case 0: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 1; 
		break; } 
			


default:

switch($total_times_4):


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_5):


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_6):


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 1; 
		break; } 



default:

switch($total_times_7):


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 1; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 1; 
		break; } 

			



default:

switch($total_times_9): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_10): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 1; 
		break; } 

			



default:

switch($total_times_11): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 1; 
		break; } 

			



default:

switch($total_times_12): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 1; 
		break; } 




default:

switch($total_times_13): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_14): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 1; 
		break; } 

			



default:

switch($total_times_15): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_16): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_17): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 1; 
		break; } 

			



default:

switch($total_times_18): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 1; 
		break; } 




default:


switch($total_times_19): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_20): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_21): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_22): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 1; 
		break; } 

			


default:

switch($total_times_23): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 1; 
		break; } 

			




switch($total_times_24): 


	case 0: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 1; 
		break; } 
break;


endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;


switch($validate):

	case 1: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;


	

	default:	
		
switch($total_times_1):


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 2; 
		break; } 

	



default:	     

switch($total_times_2):

	case 1: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 2; 
		break; }

			
	


default:

switch($total_times_3):

	case 1: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 2; 
		break; } 
			


default:

switch($total_times_4):


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_5):


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_6):


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 2; 
		break; } 



default:

switch($total_times_7):


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 2; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 2; 
		break; } 

			



default:

switch($total_times_9): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_10): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 2; 
		break; } 

			



default:

switch($total_times_11): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 2; 
		break; } 

			



default:

switch($total_times_12): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 2; 
		break; } 




default:

switch($total_times_13): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_14): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 2; 
		break; } 

			



default:

switch($total_times_15): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_16): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_17): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 2; 
		break; } 

			



default:

switch($total_times_18): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 2; 
		break; } 




default:


switch($total_times_19): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_20): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_21): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_22): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 2; 
		break; } 

			


default:

switch($total_times_23): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 2; 
		break; } 

			




switch($total_times_24): 


	case 1: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 2; 
		break; } 

break;

endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;




switch($validate):

	case 1: 		$validate = 1;
				break;

	case 2: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;


	

	default:	
		
switch($total_times_1):


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 3; 
		break; } 

	



default:	     

switch($total_times_2):

	case 2: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 3; 
		break; }

			
	


default:

switch($total_times_3):

	case 2: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 3; 
		break; } 
			


default:

switch($total_times_4):


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_5):


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_6):


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 3; 
		break; } 



default:

switch($total_times_7):


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 3; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 3; 
		break; } 

			



default:

switch($total_times_9): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_10): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 3; 
		break; } 

			



default:

switch($total_times_11): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 3; 
		break; } 

			



default:

switch($total_times_12): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 3; 
		break; } 




default:

switch($total_times_13): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_14): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 3; 
		break; } 

			



default:

switch($total_times_15): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_16): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_17): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 3; 
		break; } 

			



default:

switch($total_times_18): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 3; 
		break; } 




default:


switch($total_times_19): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_20): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_21): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_22): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 3; 
		break; } 

			


default:

switch($total_times_23): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 3; 
		break; } 

			




switch($total_times_24): 


	case 2: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 3; 
		break; } 
break;


endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;



switch($validate):
	
	case 1: 		$validate=1;
				break;
	
	case 2: 		
				$validate=2;
				break;	

	
	case 3: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;


	

	default:	

switch($total_times_1):


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 4; 
		break; } 

	



default:	     

switch($total_times_2):

	case 3: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 4; 
		break; }

			
	


default:

switch($total_times_3):

	case 3: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 4; 
		break; } 
			


default:

switch($total_times_4):


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_5):


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_6):


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 4; 
		break; } 



default:

switch($total_times_7):


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 4; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 4; 
		break; } 

			



default:

switch($total_times_9): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_10): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 4; 
		break; } 

			



default:

switch($total_times_11): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 4; 
		break; } 

			



default:

switch($total_times_12): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 4; 
		break; } 




default:

switch($total_times_13): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_14): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 4; 
		break; } 

			



default:

switch($total_times_15): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_16): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_17): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 4; 
		break; } 

			



default:

switch($total_times_18): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 4; 
		break; } 




default:


switch($total_times_19): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_20): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_21): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_22): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 4; 
		break; } 

			


default:

switch($total_times_23): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 4; 
		break; } 

			




switch($total_times_24): 


	case 3: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 4; 
		break; } 

break;

endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;



switch($validate):

	case 1: 		$validate=1;
				break;

	case 2: 		
				$validate=2;
				break;	

	
	case 3: 		
				$validate=3;
				break;

	case 4: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;

default:	

switch($total_times_1):


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 5; 
		break; } 

	



default:	     

switch($total_times_2):

	case 4: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 5; 
		break; }

			
	


default:

switch($total_times_3):

	case 4: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 5; 
		break; } 
			


default:

switch($total_times_4):


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_5):


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_6):


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 5; 
		break; } 



default:

switch($total_times_7):


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 5; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 5; 
		break; } 

			



default:

switch($total_times_9): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_10): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 5; 
		break; } 

			



default:

switch($total_times_11): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 5; 
		break; } 

			



default:

switch($total_times_12): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 5; 
		break; } 




default:

switch($total_times_13): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_14): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 5; 
		break; } 

			



default:

switch($total_times_15): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_16): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_17): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 5; 
		break; } 

			



default:

switch($total_times_18): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 5; 
		break; } 




default:


switch($total_times_19): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_20): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_21): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_22): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 5; 
		break; } 

			


default:

switch($total_times_23): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 5; 
		break; } 

			




switch($total_times_24): 


	case 4: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 5; 
		break; } 

break;

endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;



switch($validate):

	case 1: 		$validate=1;
				break;

	case 2: 		
				$validate=2;
				break;	

	
	case 3: 		
				$validate=3;
				break;
	case 4: 		
				$validate=4;
				break;


	case 5: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;

default:	

switch($total_times_1):


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 6; 
		break; } 

	



default:	     

switch($total_times_2):

	case 5: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 6; 
		break; }

			
	


default:

switch($total_times_3):

	case 5: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 6; 
		break; } 
			


default:

switch($total_times_4):


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_5):


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_6):


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 6; 
		break; } 



default:

switch($total_times_7):


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 6; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 6; 
		break; } 

			



default:

switch($total_times_9): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_10): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 6; 
		break; } 

			



default:

switch($total_times_11): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 6; 
		break; } 

			



default:

switch($total_times_12): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 6; 
		break; } 




default:

switch($total_times_13): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_14): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 6; 
		break; } 

			



default:

switch($total_times_15): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_16): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_17): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 6; 
		break; } 

			



default:

switch($total_times_18): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 6; 
		break; } 




default:


switch($total_times_19): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_20): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_21): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_22): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 6; 
		break; } 

			


default:

switch($total_times_23): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 6; 
		break; } 

			




switch($total_times_24): 


	case 5: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 6; 
		break; } 

break;

endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;


switch($validate):

	case 1: 		$validate=1;
				break;

	case 2: 		
				$validate=2;
				break;	

	
	case 3: 		
				$validate=3;
				break;
	case 4: 		
				$validate=4;
				break;

	case 5: 		
				$validate=5;
				break;


	case 6: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;

default:	

switch($total_times_1):


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 7; 
		break; } 

	



default:	     

switch($total_times_2):

	case 6: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 7; 
		break; }

			
	


default:

switch($total_times_3):

	case 6: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 7; 
		break; } 
			


default:

switch($total_times_4):


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_5):


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_6):


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 7; 
		break; } 



default:

switch($total_times_7):


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 7; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 7; 
		break; } 

			



default:

switch($total_times_9): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_10): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 7; 
		break; } 

			



default:

switch($total_times_11): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 7; 
		break; } 

			



default:

switch($total_times_12): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 7; 
		break; } 




default:

switch($total_times_13): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_14): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 7; 
		break; } 

			



default:

switch($total_times_15): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_16): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_17): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 7; 
		break; } 

			



default:

switch($total_times_18): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 7; 
		break; } 




default:


switch($total_times_19): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_20): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_21): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_22): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 7; 
		break; } 

			


default:

switch($total_times_23): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 7; 
		break; } 

			




switch($total_times_24): 


	case 6: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 7; 
		break; } 

break;

endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;

switch($validate):

	case 1: 		$validate=1;
				break;

	case 2: 		
				$validate=2;
				break;	

	
	case 3: 		
				$validate=3;
				break;
	case 4: 		
				$validate=4;
				break;

	case 5: 		
				$validate=5;
				break;

	case 6: 		
				$validate=6;
				break;



	case 7: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;

default:	

switch($total_times_1):


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 8; 
		break; } 

	



default:	     

switch($total_times_2):

	case 7: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 8; 
		break; }

			
	


default:

switch($total_times_3):

	case 7: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 8; 
		break; } 
			


default:

switch($total_times_4):


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_5):


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_6):


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 8; 
		break; } 



default:

switch($total_times_7):


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 8; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 8; 
		break; } 

			



default:

switch($total_times_9): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_10): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 8; 
		break; } 

			



default:

switch($total_times_11): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 8; 
		break; } 

			



default:

switch($total_times_12): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 8; 
		break; } 




default:

switch($total_times_13): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_14): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 8; 
		break; } 

			



default:

switch($total_times_15): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_16): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_17): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 8; 
		break; } 

			



default:

switch($total_times_18): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 8; 
		break; } 




default:


switch($total_times_19): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_20): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_21): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_22): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 8; 
		break; } 

			


default:

switch($total_times_23): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 8; 
		break; } 

			




switch($total_times_24): 


	case 7: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 8; 
		break; } 

break;

endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;

switch($validate):

	case 1: 		$validate=1;
				break;

	case 2: 		
				$validate=2;
				break;	

	
	case 3: 		
				$validate=3;
				break;
	case 4: 		
				$validate=4;
				break;

	case 5: 		
				$validate=5;
				break;

	case 6: 		
				$validate=6;
				break;

	case 7: 		
				$validate=7;
				break;


	case 8: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;

default:	

switch($total_times_1):


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 9; 
		break; } 

	



default:	     

switch($total_times_2):

	case 8: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 9; 
		break; }

			
	


default:

switch($total_times_3):

	case 8: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 9; 
		break; } 
			


default:

switch($total_times_4):


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_5):


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_6):


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$a]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 9; 
		break; } 



default:

switch($total_times_7):


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 9; 
		break; } 

			
	

default:
		
switch($total_times_8): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 9; 
		break; } 

			



default:

switch($total_times_9): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_10): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 9; 
		break; } 

			



default:

switch($total_times_11): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 9; 
		break; } 

			



default:

switch($total_times_12): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$b]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 9; 
		break; } 




default:

switch($total_times_13): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_14): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 9; 
		break; } 

			



default:

switch($total_times_15): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$d]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_16): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$d]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_17): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 9; 
		break; } 

			



default:

switch($total_times_18): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$c]["teamid"];
		$teamarray[$f][0] = $sorted_array[$d]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 9; 
		break; } 




default:


switch($total_times_19): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_20): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$a]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_21): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$c]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_22): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$b]["teamid"];
		$teamarray[$g][0] = $sorted_array[$c]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 9; 
		break; } 

			


default:

switch($total_times_23): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$a]["teamid"];
		$teamarray[$h][0] = $sorted_array[$b]["teamid"];
		$validate = 9; 
		break; } 

			




switch($total_times_24): 


	case 8: { 
		$teamarray[$e][0] = $sorted_array[$d]["teamid"];
		$teamarray[$f][0] = $sorted_array[$c]["teamid"];
		$teamarray[$g][0] = $sorted_array[$b]["teamid"];
		$teamarray[$h][0] = $sorted_array[$a]["teamid"];
		$validate = 9; 
		break; } 

break;

endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;
endswitch;

switch($validate):

	case 1: 		$validate=1;
				break;

	case 2: 		
				$validate=2;
				break;	

	
	case 3: 		
				$validate=3;
				break;
	case 4: 		
				$validate=4;
				break;

	case 5: 		
				$validate=5;
				break;

	case 6: 		
				$validate=6;
				break;

	case 8: 		
				$validate=7;
				break;

	case 8: 		
				$validate=8;
				break;

	case 9: 		$a=$a+4;
				$b=$b+4;
				$c=$c+4;
				$d=$d+4;

				$e=$e+4;
				$f=$f+4;
				$g=$g+4;
				$h=$h+4;
				break;

endswitch;



}


?>
