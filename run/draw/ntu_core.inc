<?php
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
        include("draw2.inc");
    }
?>