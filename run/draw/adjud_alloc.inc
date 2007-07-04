<?


//Table of group allocation strategy
$adjud_strat[1]= array("uc"=>1,"up1"=>3,"up2"=>5,"lc"=>2,"lp1"=>4,"lp2"=>6);
$adjud_strat[2]= array("uc"=>1,"up1"=>3,"up2"=>5,"lc"=>2,"lp1"=>4,"lp2"=>6);
$adjud_strat[3]= array("uc"=>1,"up1"=>3,"up2"=>5,"lc"=>2,"lp1"=>4,"lp2"=>6);
$adjud_strat[4]= array("uc"=>1,"up1"=>2,"up2"=>5,"lc"=>3,"lp1"=>4,"lp2"=>6);
$adjud_strat[5]= array("uc"=>1,"up1"=>2,"up2"=>5,"lc"=>3,"lp1"=>4,"lp2"=>6);
$adjud_strat[6]= array("uc"=>1,"up1"=>2,"up2"=>5,"lc"=>3,"lp1"=>4,"lp2"=>6);
$adjud_strat[7]= array("uc"=>1,"up1"=>2,"up2"=>3,"lc"=>4,"lp1"=>5,"lp2"=>6);
$adjud_strat[8]= array("uc"=>1,"up1"=>2,"up2"=>3,"lc"=>4,"lp1"=>5,"lp2"=>6);
$adjud_strat[9]= array("uc"=>1,"up1"=>2,"up2"=>3,"lc"=>4,"lp1"=>5,"lp2"=>6);

//Find no of debates
$query="SELECT COUNT(*) AS numdebates FROM temp_draw_round_$nextround";
$result=mysql_query($query);
$row=mysql_fetch_assoc($result);
$numdebates=$row['numdebates'];

//Load and order adjudicators by ranking
$query="SELECT adjud_id,conflicts FROM adjudicator WHERE active='Y' AND ranking>0 ORDER BY ranking DESC";
$result=mysql_query($query);

$index=0;
while ($row=mysql_fetch_assoc($result))
{
    //Load adjudicators and their conflicts
    $adjudicator[$index][0]=$row['adjud_id'];
    $adjudicator[$index][1]=$row['conflicts'];
    $adjudicator[$index][2]='N';//to indicate whether he is already assgined. Defaults to No.

    $index++;
}

//Create Temp Adjudicator Table
$tablename="temp_adjud_round_$nextround";
$query="DROP TABLE $tablename";
$result=mysql_query($query);

$query = "CREATE TABLE `$tablename` ( `debate_id` MEDIUMINT NOT NULL ,";
$query .= " `adjud_id` MEDIUMINT NOT NULL ,";
$query .= " `status` ENUM( 'chair', 'panelist', 'trainee' ) NOT NULL );";
$query .= '';
$result=mysql_query($query);
if (!$result)
    $msg[]=mysql_error();

//Shuffle
for($x=0;$x<6;$x++)
{
  $low=$x*$numdebates/2;
  $high=$low+($numdebates/2)-1;
  
  for($y=intval($low);$y<=$high;$y++)
  {
      $randnum=rand(intval($low),intval($high));
      $temp0=$adjudicator[$y][0];
      $temp1=$adjudicator[$y][1];
      $adjudicator[$y][0]=$adjudicator[$randnum][0];
      $adjudicator[$y][1]=$adjudicator[$randnum][1];
      $adjudicator[$randnum][0]=$temp0;
      $adjudicator[$randnum][1]=$temp1;
  }
}
//Allocate

//Open Debates Table
$query = "SELECT debate_id, U1.univ_code AS ogcode, U2.univ_code AS oocode, U3.univ_code AS cgcode, U4.univ_code AS cocode ";
$query .= "FROM temp_draw_round_$nextround AS D, university AS U1, university AS U2, university AS U3, university AS U4, team AS T1, team AS T2, team AS T3, team AS T4 ";
$query .= "WHERE D.og = T1.team_id AND D.oo = T2.team_id AND D.cg = T3.team_id AND D.co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id "; 
         
$resultdebates=mysql_query($query);
$debatecount=0;
while ($rowdebates=mysql_fetch_assoc($resultdebates))
{
    $debatecount++;  
    //Load variables from allocation strategy table
    if ($debatecount<($numdebates/2))
    {
        //use upper half values
        $chairptr=$adjud_strat[$nextround]["uc"];
        $pan1ptr=$adjud_strat[$nextround]["up1"];
        $pan2ptr=$adjud_strat[$nextround]["up2"];
    }
    else
    {
        //use lower half values
        $chairptr=$adjud_strat[$nextround]["lc"];
        $pan1ptr=$adjud_strat[$nextround]["lp1"];
        $pan2ptr=$adjud_strat[$nextround]["lp2"];
    }

    //Assign Chairs
    $low=($chairptr-1)*$numdebates/2;
    $high=$low+($numdebates/2)-1;
    
    $index=intval($low);
    while($index<=$high)
    {
        if ($adjudicator[$index][2]=='N')
        {
            $conflicts=preg_split("/,/",$adjudicator[$index][1],-1, PREG_SPLIT_NO_EMPTY);
            if (!((in_array($rowdebates['ogcode'],$conflicts))||
                (in_array($rowdebates['oocode'],$conflicts))||
                  (in_array($rowdebates['cgcode'],$conflicts))||
                  (in_array($rowdebates['cocode'],$conflicts))))//no conflicts
            {
                $debate_id=$rowdebates['debate_id'];
                $adjud_id=$adjudicator[$index][0];
                $query="INSERT INTO `temp_adjud_round_$nextround` VALUES('$debate_id','$adjud_id','chair')";
                $result=mysql_query($query);
                $adjudicator[$index][2]='Y';
                break;
            }
        }
    $index++;
    }
  
    //Assign Panelist 1
    $low=($pan1ptr-1)*$numdebates/2;
    $high=$low+($numdebates/2)-1;

    $index=intval($low);
    while($index<=min($high,(count($adjudicator)-1)))
    {
        if ($adjudicator[$index][2]=='N')
        {
            $conflicts=preg_split("/,/",$adjudicator[$index][1],-1, PREG_SPLIT_NO_EMPTY);
            if (!((in_array($rowdebates['ogcode'],$conflicts))||
                (in_array($rowdebates['oocode'],$conflicts))||
                  (in_array($rowdebates['cgcode'],$conflicts))||
                  (in_array($rowdebates['cocode'],$conflicts))))//no conflicts
            {
                $debate_id=$rowdebates['debate_id'];
                $adjud_id=$adjudicator[$index][0];
                $query="INSERT INTO `temp_adjud_round_$nextround` VALUES('$debate_id','$adjud_id','panelist')";
                $result=mysql_query($query);
                $adjudicator[$index][2]='Y';
                break;
            }
        }
    $index++;
    }
  
    //Assign Panelist 2
    $low=($pan2ptr-1)*$numdebates/2;
    $high=$low+($numdebates/2)-1;

    $index=intval($low);
    while($index<=min($high,(count($adjudicator)-1)))
    {
        if ($adjudicator[$index][2]=='N')
        {
            $conflicts=preg_split("/,/",$adjudicator[$index][1],-1, PREG_SPLIT_NO_EMPTY);
            if (!((in_array($rowdebates['ogcode'],$conflicts))||
                (in_array($rowdebates['oocode'],$conflicts))||
                  (in_array($rowdebates['cgcode'],$conflicts))||
                  (in_array($rowdebates['cocode'],$conflicts))))//no conflicts
            {
                $debate_id=$rowdebates['debate_id'];
                $adjud_id=$adjudicator[$index][0];
                $query="INSERT INTO `temp_adjud_round_$nextround` VALUES('$debate_id','$adjud_id','panelist')";
                $result=mysql_query($query);
                $adjudicator[$index][2]='Y';
                break;
            }
        }
    $index++;
    }
}

?>