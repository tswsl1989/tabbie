<?
/******************************************************************************
File    :   debatelist.inc

Author  :   AK

Purpose :   This file prints the debate list for the CA/DCAs for use before manual draw

******************************************************************************/


include("includes/dbconnection.php"); //Database Connection

$action=@$_GET['action'];

$roundno = $numdraws + 1;

$check_query="SHOW TABLES LIKE 'temp_draw_round_$roundno'";
$check_result=mysql_query($check_query);

if (!($check_count=mysql_num_rows($check_result)))
{   
    $msg[]="Calculate new draw before using this print module. <br/>This module is to be used before manual draw.";
    $msg[]="<br/>";
    $action = "error";
}

include("header.inc");

switch($action)
{
    case "display":     break;
    case "error":       $title.=" - Error";
                        break;
    default:
                        $action="display";
                        break;
}

$title.=" (Round: $roundno)";
echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

if ($action == "error")
{
    //Display Messages
    for($x=0;$x<count($msg);$x++)
        echo "<p class=\"err\">".$msg[$x]."</p>\n";
}
else
{
if ($action == "display")
{

    //Open the text file
    $filename = "print\outputs\chief_adjudicator_debate_list_$roundno.html";
    $fp = fopen($filename, "w");
    
    $text="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"> \n<html> \n <head> ";
    $text.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/> \n";
    $text.=" </head> \n <body> \n";
    $text.=" <h2>$title</h2><br/>\n\n";
    fputs($fp,$text);
    
    $query = 'SELECT A1.debate_id AS debate_id, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ';
    $query .= "FROM temp_draw_round_$roundno AS A1, team T1, team T2, team T3, team T4 ";
    $query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id "; 
    
    $result = mysql_query($query);
    
    if ($result)
    {    $team_array="";
        while ($row=mysql_fetch_assoc($result))
        {    $debate_id=$row['debate_id'];
            $ogpoints=0;
                       for ($i=1; $i<$roundno; $i++)
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
                       for ($i=1; $i<$roundno; $i++)
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
                    for ($i=1; $i<$roundno; $i++)
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
                       for ($i=1; $i<$roundno; $i++)
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
            $team_array[]=array("id" => $debate_id,
                        "points" => $totalpoints );
        }
    }
        
    function cmp ($a, $b) {
        if ($a["points"] == $b["points"]) 
                return 0;
        return ($a["points"] < $b["points"]) ? 1 : -1;
    }
    
    usort($team_array, "cmp");
    $count = count($team_array);
    
    /*
    foreach($team_array as $cc) {
        foreach($cc as $kk=>$dd) {
            print "... $kk=>$dd";
        }
        print "<br\>";
    }
    */
    
    
    if ($count)
    {    $text=" <table name=\"debatelist\" border=\"1\" width=\"100%\"> \n";
        $text.=" <tr><th valign=\"top\">Venue</th><th valign=\"top\">OG</th><th valign=\"top\">OO</th><th valign=\"top\">CG</th><th valign=\"top\">CO</th><th valign=\"top\">Adjudicator</th><th valign=\"top\">Status</th><th valign=\"top\">Conflicts</th></tr> \n";
        fputs($fp,$text);

        for ($j=0;$j<$count;$j++)
        {    $debate_id=$team_array[$j]["id"];
            $query = 'SELECT A1.debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ';
            $query .= "FROM temp_draw_round_$roundno AS A1, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue ";
            $query .= "WHERE A1.debate_id=$debate_id AND T1.team_id=A1.og AND T2.team_id=A1.oo AND T3.team_id=A1.cg AND T4.team_id=A1.co AND U1.univ_id=T1.univ_id AND U2.univ_id=T2.univ_id AND U3.univ_id=T3.univ_id AND U4.univ_id=T4.univ_id AND venue.venue_id=A1.venue_id "; 
            
            $result=mysql_query($query);
            $row=mysql_fetch_assoc($result);
            
            $venue_name = $row['venue_name'];
            $og_code = $row['ogtc']." ".$row['ogt'];
            $oo_code = $row['ootc']." ".$row['oot'];
            $cg_code = $row['cgtc']." ".$row['cgt'];
            $co_code = $row['cotc']." ".$row['cot'];

            $ogpoints=0;
            for ($i=1; $i<$roundno; $i++)
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
            for ($i=1; $i<$roundno; $i++)
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
            for ($i=1; $i<$roundno; $i++)
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
                       for ($i=1; $i<$roundno; $i++)
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
    
            //Find Chair
            $chairquery="SELECT A.adjud_name AS adjud_name, A.conflicts AS conflicts, A.ranking AS ranking FROM temp_adjud_round_$roundno AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='chair' AND T.debate_id='$debate_id' ";
            $resultadjud=mysql_query($chairquery);
            if (mysql_num_rows($resultadjud)==0)
            {    $chair="None Assigned";
                $chairconflicts="None";
                 $chairstatus="Chair";
            }
            else
            {    $rowadjud=mysql_fetch_assoc($resultadjud);
                 $chair="{$rowadjud['adjud_name']}"." ("."{$rowadjud['ranking']}".")";
                 $chairstatus="Chair";
                 if ($rowadjud['conflicts'])
                     $chairconflicts="{$rowadjud['conflicts']}";
                 else
                     $chairconflicts="---";
            }
            
            //Find Panelists
            $panelquery="SELECT A.adjud_name AS adjud_name, A.conflicts AS conflicts, A.ranking AS ranking FROM temp_adjud_round_$roundno AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='panelist' AND T.debate_id='$debate_id' ORDER by ranking";
            $resultadjud=mysql_query($panelquery);
            $panelist="";
            $panelistconflicts="";
            $paneliststatus="";
            if (mysql_num_rows($resultadjud)==0)
            {    $panelist.="<br/>None Assigned";
                $panelistconflicts.="<br/>None";
                $paneliststatus.="<br/>Panelist";
            }
            else
            {    while($rowadjud=mysql_fetch_assoc($resultadjud))
                {    $panelist.="<br/>{$rowadjud['adjud_name']}"." ("."{$rowadjud['ranking']}".")";
                    $paneliststatus.="<br/>Panelist";
                    if ($rowadjud['conflicts'])
                        $panelistconflicts.="<br/>{$rowadjud['conflicts']}";
                    else
                        $panelistconflicts.="<br/>---";
                }
            }

            //Find Trainees
            $panelquery="SELECT A.adjud_name AS adjud_name, A.conflicts AS conflicts, A.ranking AS ranking FROM temp_adjud_round_$roundno AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='trainee' AND T.debate_id='$debate_id' ORDER by A.ranking DESC";
            $resultadjud=mysql_query($panelquery);
            $trainee="";
            $traineeconflicts="";
            $traineestatus="";
            if (mysql_num_rows($resultadjud)>0)
            {    while($rowadjud=mysql_fetch_assoc($resultadjud))
                {    $trainee.="<br/>{$rowadjud['adjud_name']}"." ("."{$rowadjud['ranking']}".")";
                    $traineestatus.="<br/>Trainee";
                    if ($rowadjud['conflicts'])
                        $traineeconflicts.="<br/>{$rowadjud['conflicts']}";
                    else
                        $traineeconflicts.="<br/>---";
                }
            }

            $text=" <tr><td valign=\"top\">$venue_name</td><td valign=\"top\">$og_code<br/>($ogpoints)</td><td valign=\"top\">$oo_code<br/>($oopoints)</td><td valign=\"top\">$cg_code<br/>($cgpoints)</td><td valign=\"top\">$co_code<br/>($copoints)</td><td valign=\"top\"><b>$chair</b> $panelist $trainee</td><td valign=\"top\"><b>$chairstatus</b> $paneliststatus $traineestatus</td><td valign=\"top\"><b>$chairconflicts</b> $panelistconflicts $traineeconflicts</td></tr> \n";
            fputs($fp,$text);
        }
        $text=" </table> \n";
        fputs($fp,$text);
    }


    $text=" </body>\n</html>\n";
    fputs($fp,$text);
    fclose($fp);
    echo "<h3>File created successfully!</h3> ";
    echo "<h3><a href=\"$filename\">Debate List for Round $roundno</a></h3> ";
}
}

?>
</div>
</body>
</html>
