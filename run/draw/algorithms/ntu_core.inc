<?php

function funny_conversion_for_ntu_code_backwards($teams, $teams_containing_points) {
    $debates = array();
    for ($i = 0; $i < count($teams) / 4; $i++) {
        $debate = array();
        for ($j = 0; $j < 4; $j++) {
            $team = array("team_id" => $teams[$i * 4 + $j][0]);
            foreach ($teams_containing_points as $team2)
                if ($team2["team_id"] == $team["team_id"]) {
                    $team["points"] = $team2["points"];
                    break;
                }
            $debate[] = $team;
        }
        $debates[] = $debate;
    }
    return $debates;
}

function draw_ntu_core($teams, $nextround) {
    //Do Random Draw for first round
    if ($nextround==1)
    {
        $query="SELECT team_id, univ_id  FROM team WHERE active='Y'";
        $result=q($query);

        //Load into array
        $index=0;
       while($row=mysql_fetch_assoc($result))
       {
           $teamarray[$index][0]=$row['team_id']; //note the lack of init of teamarray which apparently works.
           $teamarray[$index][1]="NOT-USED";
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
    {    //Do Power Matching Draw for other rounds
$query="SELECT team_id, univ_id  FROM team WHERE active='Y'";
$result=mysql_query($query);

//Load into array
$index=0;
while($row=mysql_fetch_assoc($result))
{
    $teamarray[$index][0]=$row['team_id'];
    $teamarray[$index][1]="NOT-USED";
    $teamcode = $teamarray[$index][0];
    $teams[$index][0] =  $teamarray[$index][0];
    
    $points = 0;
    for ($i=1; $i<$nextround; $i++)
    {    $pointsquery = "SELECT first FROM result_round_$i WHERE first = $teamcode ";
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
    $index++;
}


/* ====================================================================== */
/* To sort the array containing the containing team names and points      */
/* ====================================================================== */

$top_value = 0;
foreach($teams as $aa) {
    if ($aa[1] > $top_value) {
        $top_value = $aa[1];
    }
}


$team_count = 0;
$while_count = $top_value;

while ($while_count >= 0) {
    foreach($teams as $bb) {
        if ($bb[1] == $while_count) {
            $sorted_array[] = array ("name" => $bb[0],
                                                 "score" => $bb[1]);
            $team_count++;
        }
    }
    $while_count--;
}


/* ====================================================================== */
/* To randomise the teams on same points                                  */
/* ====================================================================== */

$random_count = 0;
$number_of_teams = count($sorted_array);
$prev_team_score = $sorted_array[0]["score"];
$random_count_start = 0;
$temp_array = array();

for ($i=0; $i<$number_of_teams; $i++) {
    if ($sorted_array[$i]["score"] == $prev_team_score) {
    }
    else {
        $temp = $i - 1;

        for ($j=$random_count_start; $j < $i; $j++) {
            $random = rand($random_count_start,$temp);
            $temp_variable = $sorted_array[$j]["name"];
            $sorted_array[$j]["name"] = $sorted_array[$random]["name"];
            $sorted_array[$random]["name"] = $temp_variable;
        }
        $prev_team_score = $sorted_array[$i]["score"];
        $random_count_start = $i;
    }
}

for ($j=$random_count_start; $j < $number_of_teams; $j++) {
    $random = rand($random_count_start,$number_of_teams-1);
    $temp_variable = $sorted_array[$j]["name"];
    $sorted_array[$j]["name"] = $sorted_array[$random]["name"];
    $sorted_array[$random]["name"] = $temp_variable;
}


/* ====================================================================== */
/* To rearrange teams within a debate randomly                            */
/* ====================================================================== */
/*
for ($x=0;$x<count($sorted_array);$x=$x+4)
{    
    for ($i=0;$i<4;$i++)
    {    $random = rand($x,$x+3);
        $xi = $x + $i;
        $temp_variable = $sorted_array[$xi]["name"];
        $temp_variable1 = $sorted_array[$xi]["score"];
        $sorted_array[$xi]["name"] = $sorted_array[$random]["name"];
        $sorted_array[$xi]["score"] = $sorted_array[$random]["score"];
        $sorted_array[$random]["name"] = $temp_variable;
        $sorted_array[$random]["score"] = $temp_variable1;
    }
}    
*/


/* ====================================================================== */
/* To rearrange teams according to the previous positions                 */
/* ====================================================================== */

function cmp ($a, $b) {
    if ($a["times"] == $b["times"]) 
            return 0;
    return ($a["times"] < $b["times"]) ? -1 : 1;
}
$teamarray="";

for ($x=0;$x<count($sorted_array);$x=$x+4)
{
    for ($i=0;$i<4;$i++)
    {
        $xi = $x + $i;
        $ii = $i * 4;

        $og = 0;
        $oo = 0;
        $cg = 0;
        $co = 0;
        
        $team_id = $sorted_array[$xi]["name"]; 
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
        
        $positional_array[$ii] = array("team" => $sorted_array[$xi]["name"],
                                   "pos" => 'og',
                                   "times" => $og);
                                   
        $positional_array[$ii+1] = array("team" => $sorted_array[$xi]["name"],
                                   "pos" => 'oo',
                                   "times" => $oo);

        $positional_array[$ii+2] = array("team" => $sorted_array[$xi]["name"],
                                   "pos" => 'cg',
                                   "times" => $cg);

        $positional_array[$ii+3] = array("team" => $sorted_array[$xi]["name"],
                                   "pos" => 'co',
                                   "times" => $co);
    }
    
    // Sorting the array into ascending order
    usort($positional_array, "cmp");

/*
    print "Teams rearramged within each debate (ascending order): <BR>";
    foreach($positional_array as $cc) {
        foreach($cc as $kk=>$dd) {
            print "... $kk=>$dd";
        }
        print "<BR>";
    }
*/

    // Randomising teams on same count
    $random_count = 0;
    $number = count($positional_array);
    $prev_count = $positional_array[0]["times"];
    $random_count_start = 0;
    $temp_array = array();

    for ($i=0; $i<$number; $i++) {
        if ($positional_array[$i]["times"] == $prev_count) {
        }
        else {
            $temp = $i - 1;
    
            for ($j=$random_count_start; $j < $i; $j++) {
                $random = rand($random_count_start,$temp);
                $temp_variable1 = $positional_array[$j]["team"];
                $temp_variable2 = $positional_array[$j]["pos"];
                $positional_array[$j]["team"] = $positional_array[$random]["team"];
                $positional_array[$j]["pos"] = $positional_array[$random]["pos"];
                $positional_array[$random]["team"] = $temp_variable1;
                $positional_array[$random]["pos"] = $temp_variable2;
            }
            $prev_count = $positional_array[$i]["times"];
            $random_count_start = $i;
        }
    }

/*
    print "Teams rearramged within each debate (randomising after asc): <BR>";
    foreach($positional_array as $cc) {
        foreach($cc as $kk=>$dd) {
            print "... $kk=>$dd";
        }
        print "<BR>";
    }
*/

    $assign_count=0;
    while ($assign_count < 4)
    {
        $pos_team_id = $positional_array[0]["team"];
        $pos_pos = $positional_array[0]["pos"];
//        echo "$pos_team_id -> $pos_pos <br/><br/>";
        
        switch($pos_pos)
        {
            case "og":
                $temp_array[0]=$pos_team_id;
                break;
            case "oo":
                $temp_array[1]=$pos_team_id;
                break;
            case "cg":
                $temp_array[2]=$pos_team_id;
                break;
            case "co":
                $temp_array[3]=$pos_team_id;
                break;
        }
        
        $temp_index=0;
        for ($j=0;$j<count($positional_array);$j++)
        {
            if (($positional_array[$j]["team"] <> $pos_team_id) AND ($positional_array[$j]["pos"] <> $pos_pos))
            {
                $temp_array2[$temp_index] = $positional_array[$j];
                $temp_index++;
            }
        }
    
        $positional_array = "";
        $positional_array = $temp_array2;
        $temp_array2 = "";
    
    $assign_count++;
    }
    
    for ($j=0;$j<4;$j++)
        $teamarray[$x+$j][0] = $temp_array[$j];
}
    }
    return funny_conversion_for_ntu_code_backwards($teamarray, $teams);

}
?>