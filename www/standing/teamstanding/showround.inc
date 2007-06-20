<?
/******************************************************************************
File    :   showround.inc

Author  :   AK

Purpose :   Display the team standings at the end of a selected round
           
******************************************************************************/

$action=@$_GET['action'];
$roundno=$round;

$list=trim(@$_POST['list']);
if (!$list)
{	$list=trim(@$_GET['list']); //list : all, esl, break, eslbreak
	if (!$list) $list="all"; //set to all if empty
}

$validate=1;

if ($roundno > $numresults)
{   
    $msg[]="Invalid round selected.";
    $msg[]="<BR>";
    $validate=0;
}

$title = "Team Standings";

switch($list)
{
    case "all"   :      break;
    case "esl"   :	$title.= " (ESL)";
    			break;
    case "break" :	$title.= " (Breakable)";
    			break;
    case "eslbreak" :   $title.= " (ESL - Breakable)";
    			break;
    default      :	$list = "all";
    			break;
}
                
switch($action)
{
    case "showround":   $title.=" - Round $roundno";
                        break;
    default:
                        $title.=" - Round $roundno";
                        $action="showround";
                        break;
}

include("header.inc");

echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

if ($validate == 0)
{	for($x=0;$x<count($msg);$x++)
	echo "<p class=\"err\">".$msg[$x]."</p>\n";

}
else
{	if ($action == "showround")
	{
		$query = "SELECT team_id FROM team ";
	
		if ($list=="esl")
			$query.=" WHERE esl = 'Y' ";
			
		if ($list=="break")
			$query.=" WHERE composite = 'N' ";
			
		if ($list=="eslbreak")
			$query.=" WHERE esl = 'Y' and composite = 'N' ";
	
		$result = mysql_query($query);
		$team_count=mysql_num_rows($result);
		
		// echo "query => $query <BR>";
		// echo "$team_count <BR>";
				
		// Create array with all the team ids
		$index=0;
		while ($row=mysql_fetch_assoc($result))
		{
		    $team_array[$index] = array("index" => $index++,
		                        "teamid" => $row['team_id'],
		                        "teamname" => ' ',
		                        "score" => 0,
		                        "speaker" => 0);    
		}
		
		
		// Fill up all the team names
		$index=0;
		while ($index<count($team_array))
		{
		    $teamid = $team_array[$index]["teamid"];
		    $name_query = "SELECT univ.univ_code AS univ_code, team.team_code AS team_code ";
		    $name_query .= "FROM university AS univ, team AS team ";
		    $name_query .= "WHERE team.team_id=$teamid AND team.univ_id = univ.univ_id ";
		    $name_result = mysql_query($name_query);
		    $name_row = mysql_fetch_assoc($name_result);
		    $teamname = $name_row['univ_code'].' '.$name_row['team_code'];
		    $team_array[$index++]["teamname"] = $teamname;
		}
	
		
		// Run through the array and add the points
		foreach($team_array as $cc) 
		{
			$index = $cc["index"];
			$team_id = $cc["teamid"];
			$score = 0;
			$speaker = 0;
			for ($x=1;$x<=$roundno;$x++)
			{
				// Check for first
				$score_query = "SELECT first FROM result_round_$x WHERE first = '$team_id' ";
				$score_result = mysql_query($score_query);
				$score_count = mysql_num_rows($score_result);
				if ($score_count > 0)
					$score = $score + 3;
		
				// Check for second
				$score_query = "SELECT second FROM result_round_$x WHERE second = '$team_id' ";
				$score_result = mysql_query($score_query);
				$score_count = mysql_num_rows($score_result);
				if ($score_count > 0)
					$score = $score + 2;
		
				// Check for third
				$score_query = "SELECT third FROM result_round_$x WHERE third = '$team_id' ";
				$score_result = mysql_query($score_query);
				$score_count = mysql_num_rows($score_result);
				if ($score_count > 0)
					$score = $score + 1;
					
				// Speaker points
				$score_query = "SELECT points FROM speaker_round_$x AS round, speaker AS speaker ";
				$score_query .= "WHERE speaker.team_id = '$team_id' AND speaker.speaker_id = round.speaker_id ";
				$score_result = mysql_query($score_query);
				while ($score_row = mysql_fetch_assoc($score_result))
				{
					$speaker += $score_row['points'];
				}
			}
			$team_array[$index]["score"] = $score;
			$team_array[$index]["speaker"] = $speaker;
		}
		
		
		// Sorting the array
		function cmp ($a, $b) {
		    if ($a["score"] == $b["score"]) 
		    {
		        if ($a["speaker"] == $b["speaker"]) 
		        {
		            return 0;
		        }
		        return ($a["speaker"] > $b["speaker"]) ? -1 : 1;
		    }
		    return ($a["score"] > $b["score"]) ? -1 : 1;
		}
		usort($team_array, "cmp");
		
		
		// Displaying the standings
		echo "<table>\n";
		echo "<tr><th>Team Name</th><th>Score</th><th>Points</th></tr>\n";
		for ($x=0;$x<count($team_array);$x++)
		{
		    echo "<tr>\n";
		    	echo "<td>".$team_array[$x]["teamname"]."</td>\n";
		    	echo "<td>".$team_array[$x]["score"]."</td>\n";
		    	echo "<td>".$team_array[$x]["speaker"]."</td>\n";
		    echo "</tr>\n";
		    
		}
		echo "</table>\n";
		
		/*
		//Code for testing the present array content
		foreach($team_array as $cc) {
			echo "$cc -> ";
			foreach($cc as $k => $dd) {
				print ". . .$k => $dd";
			}
			print "<BR>";
		}
		*/
		
		
	//echo "DONE PROCESSING! ";
	}	
}
	
?>
</div>"
</body>
</html>
