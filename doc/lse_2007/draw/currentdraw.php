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

$action=trim($_GET['action']); //Check action
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


//Get Number of  Rounds Completed
$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$numrounds=mysql_num_rows($result);

//Get Number of Rounds result entered for
$query="SHOW TABLES LIKE 'result_round%'";
$result=mysql_query($query);
$numresults=mysql_num_rows($result);

if ($numrounds > $numresults)
{
    $validate=0;
    $msg[]="The results for the last round have not been entered.Please enter the results and then try again.";
}

if (($action=="draw")&&($validate==1))//Check Action : Draw
{

    //Load Teams from Database
    $query="SELECT team_id FROM team WHERE active='Y'";
    $result=mysql_query($query);
    
    //Do Random Draw for first round
    if ($nextround==1)
    {
        $query="SELECT team_id FROM team WHERE active='Y'";
        $result=mysql_query($query);
        
        //Load into array
        $index=0;

       while($row=mysql_fetch_assoc($result))
       {
           $teamarray[$index][0]=$row['team_id'];
           $index++;
       }

       //Do Random Draw
       for ($x=0; $x<count($teamarray);$x++)
       {
           $randnum=rand(0, count($teamarray)-1); //Calculate Random Number

           //Swap Numbers
           $temp0= $teamarray[$randnum][0]; 
           
           
           $teamarray[$randnum][0]=$teamarray[$x][0];
           

           $teamarray[$x][0]=$temp0;
           
       }
    }
    


    else 
    {	//Do Power Matching Draw for other rounds
    	include("./lse_dev/draw/draw3.php");
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
    $query.= "PRIMARY KEY (debate_id)), ENGINE=InnoDB;";
    $result=mysql_query($query);
    if (!$result)
        $msg[]=mysql_error();

        $result1=mysql_query("SELECT venue_id FROM venue WHERE active='Y'");
        while ($row1=mysql_fetch_assoc($result1))
        {
            $venue[]=$row1['venue_id'];//Load Venues
        }

	$tablename1="ppt_draw";
    	$query = "DROP TABLE `$tablename1`";
    	$result=mysql_query($query);

	$query = "CREATE TABLE $tablename1 (";
	$query .= "debate_id MEDIUMINT(9) NOT NULL PRIMARY KEY,";
	$query .= "venue_name varchar(50), ";
	$query .= "og varchar(50), ";
	$query .= "oo varchar(50), ";
	$query .= "cg varchar(50), ";
	$query .= "co varchar(50), ";
	$query .= "og_speaker1 varchar(50), ";
	$query .= "og_speaker2 varchar(50), ";
	$query .= "oo_speaker1 varchar(50), ";
	$query .= "oo_speaker2 varchar(50), ";
	$query .= "cg_speaker1 varchar(50), ";
	$query .= "cg_speaker2 varchar(50), ";
	$query .= "co_speaker1 varchar(50), ";
	$query .= "co_speaker2 varchar(50)), ";
	$query .= "ENGINE=InnoDB"


	$result=mysql_query($query);
    		if (!$result){
        		$msg[]=mysql_error();}
         
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



	$query="SELECT team_code from team where team_id = '$og'";
	$og1=mysql_query($query);
	$row1=mysql_fetch_assoc($og1);
	$a = $row1[team_code];
	$a = str_replace("'", "*", $a);

	$query="SELECT team_code from team where team_id = '$oo'";
	$oo1=mysql_query($query);
	$row2=mysql_fetch_assoc($oo1);
	$b = $row2[team_code];
	$b = str_replace("'", "*", $b); 

	$query="SELECT team_code from team where team_id = '$cg'";
	$cg1=mysql_query($query);
	$row3=mysql_fetch_assoc($cg1);
	$c = $row3[team_code];
	$c = str_replace("'", "*", $c); 


	$query="SELECT team_code from team where team_id = '$co'";
	$co1=mysql_query($query);
	$row4=mysql_fetch_assoc($co1);
	$d = $row4[team_code];
	$d = str_replace("'", "*", $d); 
	
	$query="SELECT venue_name from venue where venue_id = '$venue_id'";
	$venue1=mysql_query($query);
	$row5=mysql_fetch_assoc($venue1);
	$e = $row5[venue_name];
	$e = str_replace("'", "*", $e); 

	$count=0;

	$query="SELECT speaker_name from speaker where team_id = '$og' order by speaker_name";
	$og_speakers=mysql_query($query);
        while($row6=mysql_fetch_assoc($og_speakers))
        {
           $speakerarray[$count][0]=$row6['speaker_name'];
           $count++;
        }


	$f=$speakerarray[0][0];
	$g=$speakerarray[1][0];

	$query="SELECT speaker_name from speaker where team_id = '$oo' order by speaker_name";
	$oo_speakers=mysql_query($query);
        while($row7=mysql_fetch_assoc($oo_speakers))
        {
           $speakerarray[$count][0]=$row7['speaker_name'];
           $count++;
        }


	$h=$speakerarray[2][0];
	$i=$speakerarray[3][0];

	$query="SELECT speaker_name from speaker where team_id = '$cg' order by speaker_name";
	$cg_speakers=mysql_query($query);
        while($row8=mysql_fetch_assoc($cg_speakers))
        {
           $speakerarray[$count][0]=$row8['speaker_name'];
           $count++;
        }


	$j=$speakerarray[4][0];
	$k=$speakerarray[5][0];

	$query="SELECT speaker_name from speaker where team_id = '$co' order by speaker_name";
	$co_speakers=mysql_query($query);
        while($row9=mysql_fetch_assoc($co_speakers))
        {
           $speakerarray[$count][0]=$row9['speaker_name'];
           $count++;
        }


	$l=$speakerarray[6][0];
	$m=$speakerarray[7][0];

	$query="INSERT INTO $tablename1(debate_id, venue_name, og, oo, cg, co, og_speaker1, og_speaker2, oo_speaker1, oo_speaker2, cg_speaker1, cg_speaker2, co_speaker1, co_speaker2) VALUES ('$debate_id', '$e', '$a', '$b', '$c', '$d', '$f', '$g', '$h', '$i', '$j', '$k', '$l', '$m')";
        $result=mysql_query($query);
        echo "$result" or die(mysql_error());

		

	$row1= '';
	$row2= '';
	$row3= '';
	$row4= '';
	$row5= '';
	$row6= '';
	$row7= '';
	$row8= '';
	$row9= '';


    }
   
}


include("./lse_dev/draw/header.php");
echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

?>

<p> This is where the draw is done. It is fully automated, just calculate the draw, go to "Manual Draw" to change venues if desired, and finalise the draw. <p>
<p> The 1st round is random and subsequent rounds are power drawn, distributed to ensure no team repeats table positions unless they absoluely have to.<p>
<p> Note: The allocation code can handle 5 rounds easily, but over that it may run into problems as teams begin repeating table positions with higher frequency as each additional round is conducted. <p>


<?

//Display Messages
if (count($msg)>0)
{
  echo "<ul class=\"err\">\n";
  for($x=0;$x<count($msg);$x++)
    echo "<li>".$msg[$x]."</li>\n";

  echo "</ul>";

}
         
//Display Draw if required
if (($validate==1))
{


    //Display the table of calculated draw
    $query = 'SELECT A1.debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, venue_name, venue_location, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ';
    $query .= "FROM temp_draw_round_$nextround AS A1, team T1, team T2, team T3, team T4, venue ";
    $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND A1.venue_id=venue.venue_id "; 
    $query .= "ORDER BY venue_name";

    $result=mysql_query($query);
    
    if ($result)
    {
        echo "<table>\n";
            echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Total Points</th></tr>\n";

        while($row=mysql_fetch_assoc($result))
        {
 
		
		$totalpoints = $ogpoints + $oopoints + $cgpoints + $copoints;
                echo "<td>{$row['venue_name']}</td>\n";
		echo "<td>{$row['ogid']}. {$row['ogt']} <br/> </td>\n";
		echo "<td>{$row['ooid']}. {$row['oot']} <br/> </td>\n";
		echo "<td>{$row['cgid']}. {$row['cgt']} <br/> </td>\n";
                echo "<td>{$row['coid']}. {$row['cot']} <br/> </td>\n";
                               

              
                
           echo "</tr>\n";
		
  



        }

        echo "</table>\n";
    
    }

?>


<?
    //Display Summary of Current Status
        //No of Teams
    echo "<h3>No. of Teams : $numteams</h3>";
    //No of Venues
    echo "<h3>No. of Venues : $numvenues</h3>";
   
    
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
