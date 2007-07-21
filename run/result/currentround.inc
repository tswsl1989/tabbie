<?
require("includes/display.inc");

$action=trim(@$_GET['action']); //Check action
$validate=1;


//Check for No of Draws against No. of results entered
if ($nextresult!=$numrounds)
{
    $msg[]="Current Round Draw hasnt been created. Cannot input results";
    $validate=0;
}

else
{


    if (($action=="doedit")||($action=="process")||($action=="edit"))
    {
        //Validate debate_id
        $debate_id=@$_GET['debate_id'];

        $query="SELECT * FROM temp_result_round_$nextresult WHERE debate_id='$debate_id'";
        $result=mysql_query($query);
        if (mysql_num_rows($result)==0)
        {
            $validate=0;
            $msg[]="Invalid Debate ID. No such debate in database.";
            $action="display";
        }
        else
        {
            $query="SELECT S.speaker_id, S.speaker_name ";
            $query.="FROM temp_speaker_round_$nextresult TS, speaker S, draw_round_$nextresult D  ";
            $query.="WHERE TS.speaker_id=S.speaker_id AND TS.debate_id='$debate_id' AND D.og=S.team_id "; //Opening Govt
            $query.="ORDER BY S.speaker_name";
            $result=mysql_query($query);
            //Get first speaker details
            $row=mysql_fetch_assoc($result);
            $speaker1_og_id=$row['speaker_id'];
            $speaker1_og_name=$row['speaker_name'];
            $speaker1_og_score=@$_POST["speaker_$speaker1_og_id"];
            //Get second speaker
            $row=mysql_fetch_assoc($result);
            $speaker2_og_id=$row['speaker_id'];
            $speaker2_og_name=$row['speaker_name'];
            $speaker2_og_score=@$_POST["speaker_$speaker2_og_id"];

            
            $query="SELECT S.speaker_id, S.speaker_name ";
            $query.="FROM temp_speaker_round_$nextresult TS, speaker S, draw_round_$nextresult D  ";
            $query.="WHERE TS.speaker_id=S.speaker_id AND TS.debate_id='$debate_id' AND D.oo=S.team_id ";//Opening Opp
            $query.="ORDER BY S.speaker_name";
            $result=mysql_query($query);
            //Get first speaker details
            $row=mysql_fetch_assoc($result);
            $speaker1_oo_id=$row['speaker_id'];
            $speaker1_oo_name=$row['speaker_name'];
            $speaker1_oo_score=@$_POST["speaker_$speaker1_oo_id"];
            //Get second speaker
            $row=mysql_fetch_assoc($result);
            $speaker2_oo_id=$row['speaker_id'];
            $speaker2_oo_name=$row['speaker_name'];
            $speaker2_oo_score=@$_POST["speaker_$speaker2_oo_id"];



            $query="SELECT S.speaker_id, S.speaker_name ";
            $query.="FROM temp_speaker_round_$nextresult TS, speaker S, draw_round_$nextresult D  ";
            $query.="WHERE TS.speaker_id=S.speaker_id AND TS.debate_id='$debate_id' AND D.cg=S.team_id "; //Closing Govt
            $query.="ORDER BY S.speaker_name";
            $result=mysql_query($query);
            //Get first speaker details
            $row=mysql_fetch_assoc($result);
            $speaker1_cg_id=$row['speaker_id'];
            $speaker1_cg_name=$row['speaker_name'];
            $speaker1_cg_score=@$_POST["speaker_$speaker1_cg_id"];
            //Get second speaker
            $row=mysql_fetch_assoc($result);
            $speaker2_cg_id=$row['speaker_id'];
            $speaker2_cg_name=$row['speaker_name'];
            $speaker2_cg_score=@$_POST["speaker_$speaker2_cg_id"];
           
            $query="SELECT S.speaker_id, S.speaker_name ";
            $query.="FROM temp_speaker_round_$nextresult TS, speaker S, draw_round_$nextresult D  ";
            $query.="WHERE TS.speaker_id=S.speaker_id AND TS.debate_id='$debate_id' AND D.co=S.team_id "; //Closing Opp
            $query.="ORDER BY S.speaker_name";
            $result=mysql_query($query);
            //Get first speaker details
            $row=mysql_fetch_assoc($result);
            $speaker1_co_id=$row['speaker_id'];
            $speaker1_co_name=$row['speaker_name'];
            $speaker1_co_score=@$_POST["speaker_$speaker1_co_id"];
            //Get second speaker
            $row=mysql_fetch_assoc($result);
            $speaker2_co_id=$row['speaker_id'];
            $speaker2_co_name=$row['speaker_name'];
            $speaker2_co_score=@$_POST["speaker_$speaker2_co_id"];
            

            //Get Team Names & Venue
            $query = "SELECT T.debate_id AS debate_id, V.venue_name AS venue_name, `first` , `second` , third, fourth, og, oo, cg, co, T1.team_code AS team_og_code, T1.team_id AS team_og_id, U1.univ_code AS univ_og_code, T2.team_code AS team_oo_code, T2.team_id AS team_oo_id, U2.univ_code AS univ_oo_code, T3.team_code AS team_cg_code, T3.team_id AS team_cg_id, U3.univ_code AS univ_cg_code, T4.team_code AS team_co_code, T4.team_id AS team_co_id, U4.univ_code AS univ_co_code ";
            
            $query .= "FROM temp_result_round_$nextresult T, draw_round_$nextresult D, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4, venue V ";
            
            $query .= "WHERE T.debate_id = '$debate_id' AND T.debate_id = D.debate_id AND T1.team_id = D.og AND T2.team_id = D.oo AND T3.team_id = D.cg AND T4.team_id = D.co AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND D.venue_id=V.venue_id "; 
            $result=mysql_query($query);
            $rowresults=mysql_fetch_assoc($result);
            
            $team_og_id= $rowresults['team_og_id'];
            $team_oo_id= $rowresults['team_oo_id'];
            $team_cg_id= $rowresults['team_cg_id'];
            $team_co_id= $rowresults['team_co_id'];
            $team_og_name=$rowresults['univ_og_code'].' '.$rowresults['team_og_code'];
        $team_oo_name=$rowresults['univ_oo_code'].' '.$rowresults['team_oo_code'];
        $team_cg_name=$rowresults['univ_cg_code'].' '.$rowresults['team_cg_code'];
        $team_co_name=$rowresults['univ_co_code'].' '.$rowresults['team_co_code'];
        $venue=$rowresults['venue_name'];


            //Validate the values (numeric Between 1 and 100)
            
          
        }
    }

    if ($action=="edit")
    {
        unset($msg);
        //Load in values of points from table
        $query="SELECT points FROM temp_speaker_round_$nextresult WHERE speaker_id='$speaker1_og_id'";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        $speaker1_og_score=$row['points'];
        if ($speaker1_og_score == 0)
        {    $speaker1_og_score=@$speaker1_og_post_score;
        }
        
        $query="SELECT points FROM temp_speaker_round_$nextresult WHERE speaker_id='$speaker2_og_id'";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        $speaker2_og_score=$row['points'];
        if ($speaker2_og_score == 0)
    {    $speaker2_og_score=@$speaker2_og_post_score;
    }

        $query="SELECT points FROM temp_speaker_round_$nextresult WHERE speaker_id='$speaker1_oo_id'";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        $speaker1_oo_score=$row['points'];
        if ($speaker1_oo_score == 0)
    {    $speaker1_oo_score=@$speaker1_oo_post_score;
    }
        
        $query="SELECT points FROM temp_speaker_round_$nextresult WHERE speaker_id='$speaker2_oo_id'";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        $speaker2_oo_score=$row['points'];
        if ($speaker2_oo_score == 0)
    {    $speaker2_oo_score=@$speaker2_oo_post_score;
    }

        $query="SELECT points FROM temp_speaker_round_$nextresult WHERE speaker_id='$speaker1_cg_id'";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        $speaker1_cg_score=$row['points'];
        if ($speaker1_cg_score == 0)
    {    $speaker1_cg_score=@$speaker1_cg_post_score;
    }

        $query="SELECT points FROM temp_speaker_round_$nextresult WHERE speaker_id='$speaker2_cg_id'";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        $speaker2_cg_score=$row['points'];
        if ($speaker2_cg_score == 0)
    {    $speaker2_cg_score=@$speaker2_cg_post_score;
    }

        $query="SELECT points FROM temp_speaker_round_$nextresult WHERE speaker_id='$speaker1_co_id'";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        $speaker1_co_score=$row['points'];
        if ($speaker1_co_score == 0)
    {    $speaker1_co_score=@$speaker1_co_post_score;
    }

        $query="SELECT points FROM temp_speaker_round_$nextresult WHERE speaker_id='$speaker2_co_id'";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        $speaker2_co_score=$row['points'];
        if ($speaker2_co_score == 0)
    {    $speaker2_co_score=@$speaker2_co_post_score;
    }

    }
    
    
    if ($action=="doedit")
    {
    $team_og_score = $speaker1_og_score + $speaker2_og_score;
    $team_oo_score = $speaker1_oo_score + $speaker2_oo_score;
    $team_cg_score = $speaker1_cg_score + $speaker2_cg_score;
    $team_co_score = $speaker1_co_score + $speaker2_co_score;
        
        $result_score_array = array($team_og_score,$team_oo_score,$team_cg_score,$team_co_score);
    $result_teamname_array = array($team_og_name,$team_oo_name,$team_cg_name,$team_co_name);
    $result_teamid_array = array($team_og_id,$team_oo_id,$team_cg_id,$team_co_id);

    array_multisort ($result_score_array, SORT_DESC,
                    $result_teamname_array,
                    $result_teamid_array);
      
        $query ="UPDATE `temp_result_round_$nextresult` SET ";
    $query.="first = '$result_teamid_array[0]', ";
    $query.="second = '$result_teamid_array[1]', ";
    $query.="third = '$result_teamid_array[2]', ";
    $query.="fourth = '$result_teamid_array[3]' ";
    $query.="WHERE debate_id = '$debate_id' ";
    $result=mysql_query($query);
    if (!$result) //Get Error Message
      {    $msg[]="Error creating results table.".mysql_error();
        $action="display";
    }
    
    $temp_speaker_points_array = array($speaker1_og_score,$speaker2_og_score,$speaker1_oo_score,$speaker2_oo_score,$speaker1_cg_score,$speaker2_cg_score,$speaker1_co_score,$speaker2_co_score);
    $temp_speaker_id_array = array($speaker1_og_id,$speaker2_og_id,$speaker1_oo_id,$speaker2_oo_id,$speaker1_cg_id,$speaker2_cg_id,$speaker1_co_id,$speaker2_co_id);
            
    for ($i=0;$i<8;$i++)
    {    $query ="UPDATE `temp_speaker_round_$nextresult` SET ";
        $query.="points = '$temp_speaker_points_array[$i]' ";
        $query.="WHERE debate_id = '$debate_id' AND speaker_id = '$temp_speaker_id_array[$i]' ";
        $result=mysql_query($query);
        if (!$result) //Get Error Message
          {    $msg[]="Error creating results table.".mysql_error();
            $action="display";
        }
    }
    }

    if ($action=="process")
    {
        $team_og_score = $speaker1_og_score + $speaker2_og_score;
    $team_oo_score = $speaker1_oo_score + $speaker2_oo_score;
    $team_cg_score = $speaker1_cg_score + $speaker2_cg_score;
    $team_co_score = $speaker1_co_score + $speaker2_co_score;

        $score_min = 1;
        $score_max = 100;
        if (($speaker1_og_score < $score_min) OR ($speaker1_og_score > $score_max) OR
            ($speaker1_oo_score < $score_min) OR ($speaker1_oo_score > $score_max) OR
            ($speaker1_cg_score < $score_min) OR ($speaker1_cg_score > $score_max) OR
            ($speaker1_co_score < $score_min) OR ($speaker1_co_score > $score_max) OR
        ($speaker2_og_score < $score_min) OR ($speaker2_og_score > $score_max) OR
            ($speaker2_oo_score < $score_min) OR ($speaker2_oo_score > $score_max) OR
            ($speaker2_cg_score < $score_min) OR ($speaker2_cg_score > $score_max) OR
            ($speaker2_co_score < $score_min) OR ($speaker2_co_score > $score_max)  )
        {    $validate = 0;
             $msg[]="Invalid score entered for one or more debaters.";
        }
        
        if (($team_og_score == $team_oo_score) OR ($team_og_score == $team_cg_score) OR ($team_og_score == $team_co_score)
            OR ($team_oo_score == $team_cg_score) OR ($team_oo_score == $team_co_score)
            OR ($team_cg_score == $team_co_score)    )
        {    $validate = 0;
             $msg[]="Two or more teams with same total scores";
        }
        
       if ($validate==1)
        {
            //Process the results and ask for confirmation
            
        $result_score_array = array($team_og_score,$team_oo_score,$team_cg_score,$team_co_score);
        $result_teamname_array = array($team_og_name,$team_oo_name,$team_cg_name,$team_co_name);
        $result_teamid_array = array($team_og_id,$team_oo_id,$team_cg_id,$team_co_id);
                       
        $speaker1_og_post_score = @$_POST["speaker_$speaker1_og_id"];
        $speaker2_og_post_score = @$_POST["speaker_$speaker2_og_id"];
        $speaker1_oo_post_score = @$_POST["speaker_$speaker1_oo_id"];
        $speaker2_oo_post_score = @$_POST["speaker_$speaker2_oo_id"];
        $speaker1_cg_post_score = @$_POST["speaker_$speaker1_cg_id"];
        $speaker2_cg_post_score = @$_POST["speaker_$speaker2_cg_id"];
        $speaker1_co_post_score = @$_POST["speaker_$speaker1_co_id"];
        $speaker2_co_post_score = @$_POST["speaker_$speaker2_co_id"];

        }
        else
        {
            $action="edit"; //Go back to edit mode
        }
        
    }

    if ($action=="create")
    {
        unset($msg);
        //Create temporary table
        $query ="CREATE TABLE `temp_result_round_$nextresult` ( ";
        $query.="`debate_id` mediumint(9) NOT NULL default '0',";
        $query.="`first` mediumint(9) NOT NULL default '0',";
        $query.="`second` mediumint(9) NOT NULL default '0',";
        $query.="`third` mediumint(9) NOT NULL default '0',";
        $query.="`fourth` mediumint(9) NOT NULL default '0',";
        $query.="PRIMARY KEY  (`debate_id`))";

        $result=mysql_query($query);

        if (!$result) //Get Error Message
        {
      $msg[]="Error creating results table.".mysql_error();
          $action="display";
        }
        else
        {
            //Get debate IDs of present round
            $query="SELECT debate_id FROM draw_round_$nextresult";
            $resultdebates=mysql_query($query);
            while($rowdebates=mysql_fetch_assoc($resultdebates))
            {
                $result_debate_id=$rowdebates['debate_id'];
                //Add a corresponding entry into created table
                $query="INSERT INTO temp_result_round_$nextresult(debate_id) VALUES('$result_debate_id')";
                $result=mysql_query($query);
            }

            //create speaker table
            $query ="CREATE TABLE `temp_speaker_round_$nextresult` ( ";
            $query.="`speaker_id` mediumint(9) NOT NULL default '0',"; 
            $query.="`debate_id` mediumint(9) NOT NULL default '0',";
            $query.="`points` smallint(9) NOT NULL default '0', ";
            $query.="PRIMARY KEY (`speaker_id`))";

            $result=mysql_query($query);
            
            if(!$result)
            {
          $msg[]="Error creating speaker points table.".mysql_error();
          $action="display";
            }
            else
            {
               $query = "SELECT speaker_id, debate_id ";
               $query .= "FROM speaker S, draw_round_$nextresult D ";
               $query .= "WHERE S.team_id = D.og OR S.team_id = D.oo OR S.team_id = D.cg OR S.team_id = D.co ";
               $speakerresult=mysql_query($query);

                while($rowspeaker=mysql_fetch_assoc($speakerresult))
                {
                    $result_speaker_id=$rowspeaker['speaker_id'];
                    $result_debate_id=$rowspeaker['debate_id'];

                    $query="INSERT INTO temp_speaker_round_$nextresult(speaker_id, debate_id) VALUES('$result_speaker_id','$result_debate_id')";
                    $result=mysql_query($query);
                    
                }
            }
        }
        $result="display";
    }

    if ($action=="finalize")
    {
        //Finalize the results after some checks
        
        $query = "SELECT  COUNT(*)  FROM  `temp_speaker_round_$nextresult` ";
        $query .= "WHERE points = 0 and debate_id like ('$nextresult%') ";
        $result=mysql_query($query);
        $row=mysql_fetch_assoc($result);
        
        foreach ($row as $aa)
        {    if ($aa > 0)
            {    $validate = 0;
                $msg[]="1 or more speakers without any score entered. ";
            }
        }
        
        if ($validate == 1)
        {
            //Create and add values to results table
            
            $query ="CREATE TABLE `result_round_$nextresult` ( ";
        $query.="`debate_id` mediumint(9) NOT NULL default '0',";
        $query.="`first` mediumint(9) NOT NULL default '0',";
        $query.="`second` mediumint(9) NOT NULL default '0',";
        $query.="`third` mediumint(9) NOT NULL default '0',";
        $query.="`fourth` mediumint(9) NOT NULL default '0',";
        $query.="PRIMARY KEY  (`debate_id`))";
        $result=mysql_query($query);
        
        if (!$result) //Get Error Message
          {    
            $msg[]="Error creating results table.".mysql_error();
            $action="display";
          }
            else
        {    $query="INSERT INTO result_round_$nextresult SELECT * FROM temp_result_round_$nextresult";
                    mysql_query($query);
    
            //create speaker table
                $query ="CREATE TABLE `speaker_round_$nextresult` ( ";
                $query.="`speaker_id` mediumint(9) NOT NULL default '0',"; 
                $query.="`debate_id` mediumint(9) NOT NULL default '0',";
                $query.="`points` smallint(9) NOT NULL default '0', ";
                $query.="PRIMARY KEY (`speaker_id`))";
            $result=mysql_query($query);
                    
                if(!$result)
              {    $msg[]="Error creating speaker points table ".mysql_error();
                        $action="display";
                }
                else
                {    $query="INSERT INTO speaker_round_$nextresult SELECT * FROM temp_speaker_round_$nextresult";
                        mysql_query($query);
                        
                        $query="DROP TABLE temp_result_round_$nextresult";
                mysql_query($query);
                $query="DROP TABLE temp_speaker_round_$nextresult";
                mysql_query($query);
                
                //Redirect
               require_once("includes/http.php");
               redirect("result.php?moduletype=round&action=showresult&roundno=$nextresult");
                }
            $result="display";
            }
    }
    else
    {
        $action="display"; //Go back to display mode
    }

    }


}

switch ($action)
{
    case "edit" : 
        $title="Results : Round $numrounds : Edit";
        break;
        
    case "process" :
        $title="Results : Round $numrounds : Summary";
        break;

    case "display":
    default :
    $action="display";    
    $title="Results : Round $numrounds : Display";
}

echo "<h2>$title</h2>\n"; //title

displayMessagesUL(@$msg);

if ($nextresult==$numrounds)
{
    if ($action=="display")
    {
        //try extracting results from the temporary result table
        $query = "SELECT T.debate_id AS debate_id, V.venue_name AS venue_name, `first` , `second` , third, fourth, og, oo, cg, co, T1.team_code AS team_og_code, U1.univ_code AS univ_og_code, T2.team_code AS team_oo_code, U2.univ_code AS univ_oo_code, T3.team_code AS team_cg_code, U3.univ_code AS univ_cg_code, T4.team_code AS team_co_code, U4.univ_code AS univ_co_code ";
        $query .= "FROM temp_result_round_$nextresult T, draw_round_$nextresult D, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4, venue V ";
        $query .= "WHERE T.debate_id = D.debate_id AND T1.team_id = D.og AND T2.team_id = D.oo AND T3.team_id = D.cg AND T4.team_id = D.co AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND D.venue_id=V.venue_id ";
        $query .= "ORDER BY venue_name";

        $resultresult=mysql_query($query);

        if($resultresult)
        {
            //Display result in table
            echo"<table>\n";
                echo "<th>Edit</th><th>Venue</th><th>Opening Govt.</th><th>Opening Opp.</th><th>Closing Govt.</th><th>Closing Opp.</th>\n";
                while($rowresults=mysql_fetch_assoc($resultresult))
                {
                    $results_debate_id=$rowresults['debate_id'];
                    
                    $first=$rowresults['first'];
                    $second=$rowresults['second'];
                    $third=$rowresults['third'];
                    $fourth=$rowresults['fourth'];

                    $og=$rowresults['og'];
                    $oo=$rowresults['oo'];
                    $cg=$rowresults['cg'];
                    $co=$rowresults['co'];

                    $team_og_name=$rowresults['univ_og_code'].' '.$rowresults['team_og_code'];
                    $team_oo_name=$rowresults['univ_oo_code'].' '.$rowresults['team_oo_code'];
                    $team_cg_name=$rowresults['univ_cg_code'].' '.$rowresults['team_cg_code'];
                    $team_co_name=$rowresults['univ_co_code'].' '.$rowresults['team_co_code'];

                    $venue=$rowresults['venue_name'];

                    echo "<tr>\n";
                    echo "<td class=\"editdel\"><a href=\"result.php?moduletype=currentround&amp;action=edit&amp;debate_id=$results_debate_id\">Edit</a></td>";
            echo "<td>$venue</td>\n";
                    echo "<td>$team_og_name (".returnposition($first,$second,$third,$fourth,$og).")</td>\n";
                    echo "<td>$team_oo_name (".returnposition($first,$second,$third,$fourth,$oo).")</td>\n";
                    echo "<td>$team_cg_name (".returnposition($first,$second,$third,$fourth,$cg).")</td>\n";
                    echo "<td>$team_co_name (".returnposition($first,$second,$third,$fourth,$co).")</td>\n";


                    echo"</tr>\n";
                }
                

            echo "</table>";
            echo "<a href=\"result.php?moduletype=currentround&amp;action=finalize\" onClick=\"return confirm('The results cannot be modified once finalised. Do you wish to continue?');\"><h3>Finalise Results</h3></a>";
        echo "<p class=\"msg\">* The numbers in the bracket indicate the standing</p>";
        }
        else
        {
            //Display message to create table and start inputting results.
            echo "<h3><a href=\"result.php?moduletype=currentround&amp;action=create\">Start Inputting Results</a></h3>";
        }
            
            
    }

    if($action=="edit")
    {

       ?>
            <div id="debatedetails">
                <h3>Debate Details</h3>
                <p>Venue : <? echo $venue ?></p>
            </div>
            <form name="resultsinputform" action="result.php?moduletype=currentround&amp;action=process&amp;debate_id=<?echo $debate_id?>" method="POST">
            <? 
    $tag="";
        if (((@$team_og_score == @$team_oo_score) OR (@$team_og_score == @$team_cg_score) OR (@$team_og_score == @$team_co_score)) AND $validate==0)
          $tag="class=\"error\"";
        ?>
    <div id="og" <?echo $tag;?>>

                <h3>OG: <? echo $team_og_name ?> </h3> 

                    <label for="<?echo "speaker_$speaker1_og_id";?>"><?echo $speaker1_og_name;?></label>
                    <input type="text" id="<?echo "speaker_$speaker1_og_id";?>" name="<?echo "speaker_$speaker1_og_id";?>" value="<?echo $speaker1_og_score;?>"/><br/><br/>
                    
                    <label for="<?echo "speaker_$speaker2_og_id";?>"><?echo $speaker2_og_name;?></label>
                    <input type="text" id="<?echo "speaker_$speaker2_og_id";?>" name="<?echo "speaker_$speaker2_og_id";?>" value="<?echo $speaker2_og_score;?>"/><br/><br/>
            </div>

            <? 
        $tag="";
        if (((@$team_oo_score == @$team_og_score) OR (@$team_oo_score == @$team_cg_score) OR (@$team_oo_score == @$team_co_score)) AND $validate==0)
          $tag="class=\"error\"";
            ?>
            
             <div id="oo" <?echo $tag;?>>
                 <h3>OO: <? echo $team_oo_name ?> </h3> 

                    <label for="<?echo "speaker_$speaker1_oo_id";?>"><?echo $speaker1_oo_name;?></label>
                    <input type="text" id="<?echo "speaker_$speaker1_oo_id";?>" name="<?echo "speaker_$speaker1_oo_id";?>" value="<?echo $speaker1_oo_score;?>"/><br/><br/>
                    
                    <label for="<?echo "speaker_$speaker2_oo_id";?>"><?echo $speaker2_oo_name;?></label>
                    <input type="text" id="<?echo "speaker_$speaker2_oo_id";?>" name="<?echo "speaker_$speaker2_oo_id";?>" value="<?echo $speaker2_oo_score;?>"/><br/><br/>
         <? if (@$same_validate==0)  {echo "</font>";} ?>
            </div>

            <? 
        $tag="";

        if (((@$team_cg_score == @$team_og_score) OR (@$team_cg_score == @$team_oo_score) OR (@$team_cg_score == @$team_co_score)) AND $validate==0)
          $tag="class=\"error\"";
            ?>

            
            <div id="cg" <?echo $tag;?>>

                <h3>CG: <? echo $team_cg_name ?> </h3> 

                     <label for="<?echo "speaker_$speaker1_cg_id";?>"><?echo $speaker1_cg_name;?></label>
                     <input type="text" id="<?echo "speaker_$speaker1_cg_id";?>" name="<?echo "speaker_$speaker1_cg_id";?>" value="<?echo $speaker1_cg_score;?>"/><br/><br/>
                    
                    <label for="<?echo "speaker_$speaker2_cg_id";?>"><?echo $speaker2_cg_name;?></label>
                    <input type="text" id="<?echo "speaker_$speaker2_cg_id";?>" name="<?echo "speaker_$speaker2_cg_id";?>" value="<?echo $speaker2_cg_score;?>"/><br/><br/>
            </div>

            <? 
        $tag="";
        if (((@$team_co_score == @$team_og_score) OR (@$team_co_score == @$team_oo_score) OR (@$team_co_score == @$team_cg_score)) AND $validate==0)
          $tag="class=\"error\"";
            ?>

            <div id="co" <?echo $tag;?>>

                <h3>CO: <? echo $team_co_name ?> </h3> 

                    <label for="<?echo "speaker_$speaker1_co_id";?>"><?echo $speaker1_co_name;?></label>
                    <input type="text" id="<?echo "speaker_$speaker1_co_id";?>" name="<?echo "speaker_$speaker1_co_id";?>" value="<?echo $speaker1_co_score;?>"/><br/><br/>
                    
                    <label for="<?echo "speaker_$speaker2_co_id";?>"><?echo $speaker2_co_name;?></label>
                    <input type="text" id="<?echo "speaker_$speaker2_co_id";?>" name="<?echo "speaker_$speaker2_co_id";?>" value="<?echo $speaker2_co_score;?>"/><br/><br/>
                
            </div>
           
        <div class="submit">
          <input type="submit" name="Submit" value="Submit">
          <input type="button" value="Cancel" onClick="location.replace('result.php?moduletype=currentround')"/>
        </div>

        </form>
              
        <?
    }
    
    if($action=="process")
    {    
      echo "<h3>Debate Details</h3>\n";
      echo "<p>Venue : $venue</p>\n";
        $rangequery = "SELECT * FROM highlight WHERE type = 'result' ";
        $rangeresult = mysql_query($rangequery);
        $rangerow = mysql_fetch_assoc($rangeresult);
        if ($rangerow)
        {    $lower_limit = $rangerow['lowerlimit'];
            $upper_limit = $rangerow['upperlimit'];
        }
        else
        {    $lower_limit = '0';
            $upper_limit = '100';
        }

      //sort scores to find position
      $scores_arr['og']=$team_og_score;
      $scores_arr['oo']=$team_oo_score;
      $scores_arr['cg']=$team_cg_score;
      $scores_arr['co']=$team_co_score;
      arsort($scores_arr);

      
      echo "<div id=\"og\">\n";
      echo "<h2 class=\"pos\">".(array_search('og',array_keys($scores_arr))+1)."</h2>\n";
      echo "<h3>OG: $team_og_name</h3>\n";
      echo "<h3>($team_og_score)</h3>\n";
      if (($speaker1_og_score < $lower_limit) OR ($speaker1_og_score > $upper_limit))
    echo "<p class=\"outrange\">$speaker1_og_name ($speaker1_og_score)</p>\n";
      else
    echo "<p>$speaker1_og_name ($speaker1_og_score)</p>\n";

      if (($speaker2_og_score < $lower_limit) OR ($speaker2_og_score > $upper_limit))
    echo "<p class=\"outrange\">$speaker2_og_name ($speaker2_og_score)</p>\n";
      else
    echo "<p>$speaker2_og_name ($speaker2_og_score)</p>\n";
      echo "</div>\n";
      
      echo "<div id=\"oo\">\n";      
      echo "<h2 class=\"pos\">".(array_search('oo',array_keys($scores_arr))+1)."</h2>\n";
      echo "<h3>OO: $team_oo_name</h3>\n";
      echo "<h3>($team_oo_score)</h3>\n";
      if (($speaker1_oo_score < $lower_limit) OR ($speaker1_oo_score > $upper_limit))
    echo "<p class=\"outrange\">$speaker1_oo_name ($speaker1_oo_score)</p>\n";
      else
    echo "<p>$speaker1_oo_name ($speaker1_oo_score)</p>";
      if (($speaker2_oo_score < $lower_limit) OR ($speaker2_oo_score > $upper_limit))
    echo "<p class=\"outrange\">$speaker2_oo_name ($speaker2_oo_score)</p>\n";
      else
    echo "<p>$speaker2_oo_name ($speaker2_oo_score)</p>\n";
      
      echo "</div>\n";

      echo "<div id=\"cg\">\n";
      echo "<h2 class=\"pos\">".(array_search('cg',array_keys($scores_arr))+1)."</h2>\n";
      echo "<h3>CG: $team_cg_name</h3>\n";
      echo "<h3> ($team_cg_score)</h3>";
      if (($speaker1_cg_score < $lower_limit) OR ($speaker1_cg_score > $upper_limit))
    echo "<p class=\"outrange\">$speaker1_cg_name ($speaker1_cg_score)</p>\n";
      else
    echo "<p>$speaker1_cg_name ($speaker1_cg_score)</p>\n";
      if (($speaker2_cg_score < $lower_limit) OR ($speaker2_cg_score > $upper_limit))
    echo "<p class=\"outrange\">$speaker2_cg_name ($speaker2_cg_score)</p>\n";
      else
    echo "<p>$speaker2_cg_name ($speaker2_cg_score)</p>\n";

      echo "</div>\n";
      
      echo "<div id=\"co\">\n";
      echo "<h2 class=\"pos\">".(array_search('co',array_keys($scores_arr))+1)."</h2>\n";
      echo "<h3>CO: $team_co_name</h3>\n";
      echo "<h3>($team_co_score)</h3>";
      if (($speaker1_co_score < $lower_limit) OR ($speaker1_co_score > $upper_limit))
    echo "<p class=\"outrange\">$speaker1_co_name ($speaker1_co_score)</p>\n";
      else
    echo "<p>$speaker1_co_name ($speaker1_co_score)</p>\n";
      if (($speaker2_co_score < $lower_limit) OR ($speaker2_co_score > $upper_limit))
    echo "<p class=\"outrange\">$speaker2_co_name ($speaker2_co_score)</p>\n";
      else
    echo "<p>$speaker2_co_name ($speaker2_co_score)</p>\n";

      echo "</div>\n";

      echo "<div class=\"submit\">\n";

      echo "<p class=\"msg\">* Numbers in red are supposed to indicate possible out of range values</p>\n";
      
      
      array_multisort ($result_score_array, SORT_DESC,
               $result_teamname_array,
               $result_teamid_array);
      


       ?>
            <form name="resultsconfirmform" action="result.php?moduletype=currentround&amp;action=doedit&amp;debate_id=<?echo $debate_id?>" method="POST">
            <input type="hidden" id="<?echo "speaker_$speaker1_og_id";?>" name="<?echo "speaker_$speaker1_og_id";?>" value="<?echo $speaker1_og_score;?>"/>
            <input type="hidden" id="<?echo "speaker_$speaker2_og_id";?>" name="<?echo "speaker_$speaker2_og_id";?>" value="<?echo $speaker2_og_score;?>"/>
            
            <input type="hidden" id="<?echo "speaker_$speaker1_oo_id";?>" name="<?echo "speaker_$speaker1_oo_id";?>" value="<?echo $speaker1_oo_score;?>"/>
            <input type="hidden" id="<?echo "speaker_$speaker2_oo_id";?>" name="<?echo "speaker_$speaker2_oo_id";?>" value="<?echo $speaker2_oo_score;?>"/>
            
            <input type="hidden" id="<?echo "speaker_$speaker1_cg_id";?>" name="<?echo "speaker_$speaker1_cg_id";?>" value="<?echo $speaker1_cg_score;?>"/>
            <input type="hidden" id="<?echo "speaker_$speaker2_cg_id";?>" name="<?echo "speaker_$speaker2_cg_id";?>" value="<?echo $speaker2_cg_score;?>"/>
            
            <input type="hidden" id="<?echo "speaker_$speaker1_co_id";?>" name="<?echo "speaker_$speaker1_co_id";?>" value="<?echo $speaker1_co_score;?>"/>
            <input type="hidden" id="<?echo "speaker_$speaker2_co_id";?>" name="<?echo "speaker_$speaker2_co_id";?>" value="<?echo $speaker2_co_score;?>"/>
                
            <input type="submit" name="Confirm" value="Confirm">

            <input type="button" name="Back" onClick="history.go(-1)" value="Back">

               </form>
           </div>
              
        <?
    
    }
}

?>
