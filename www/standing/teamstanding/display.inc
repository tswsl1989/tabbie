<?
/******************************************************************************
File    :   display.inc

Author  :   AK

Purpose :   Display the team standings.
           
******************************************************************************/

$action=$_GET['action'];
$warning=$_GET['warning'];

$list=trim($_POST['list']);
if (!$list)
{	$list=trim($_GET['list']); //list : all, esl, break, eslbreak
	if (!$list) $list="all"; //set to all if empty
}

if (($numdraws <> $numresults) && !$round)
{   
    $warnmsg[]="Results for the ongoing draw (Round ".$numdraws.") has not been entered.";
    $warnmsg[]="This round will not be reflected in the standings.";
    $warnmsg[]="<BR>";
    if ($warning <> "done")
        $action = "warning";
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
    case "display":     $title.=" - Round $numresults";
                        break;
    case "warning":     $title.=" - Confirm";
                        break;
    default:
                        $title.=" - Round $numresults";
                        $action="display";
                        break;
}

include("header.inc");

echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

if ($action == "warning")
{
	//Display Messages
	for($x=0;$x<count($warnmsg);$x++)
	    echo "<p class=\"err\">".$warnmsg[$x]."</p>\n";
	?>
	<h3><a href="standing.php?moduletype=teamstanding&list=<?echo $list?>&action=display&warning=done">Click to confirm</a></h3>
	<?
}


if ($action == "display")
{
	$warning="null";
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
	while ($row = mysql_fetch_assoc($result))
	{
	    $team_array[$index] = array("index" => $index++,
	                        "teamid" => $row['team_id'],
	                        "teamname" => ' ',
	                        "score" => 0,
	                        "speaker" => 0);    
	}
	
	function fillUpTeamnames($team_array) {
		$result = array();
		// Fill up all the team names
		foreach ($team_array as $cc)
		{
			$teamid = $cc["teamid"];
			$name_query = "SELECT univ.univ_code AS univ_code, team.team_code AS team_code ";
			$name_query .= "FROM university AS univ, team AS team ";
			$name_query .= "WHERE team.team_id=$teamid AND team.univ_id = univ.univ_id ";
			$name_result = mysql_query($name_query);
			$name_row = mysql_fetch_assoc($name_result);
			$teamname = $name_row['univ_code'].' '.$name_row['team_code'];
			$cc["teamname"] = $teamname;
			$result[] = $cc;
		}
		return $result;
	}
	$team_array = fillUpTeamnames($team_array);
	
	// Run through the array and add the points
	foreach($team_array as $cc) 
	{
		$index = $cc["index"];
		$team_id = $cc["teamid"];
		$score = 0;
		$speaker = 0;
		for ($x=1;$x<=$numresults;$x++)
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
	echo "<tr><th>Position</th><th>Team Name</th><th>Score</th><th>Points</th></tr>\n";
	$x = 0;
	foreach ($team_array as $cc)
	{
		$x++;
	    echo "<tr>\n";
	    	echo "<td>".($x)."</td>\n";
	    	echo "<td>".$cc["teamname"]."</td>\n";
	    	echo "<td>".$cc["score"]."</td>\n";
	    	echo "<td>".$cc["speaker"]."</td>\n";
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
	
?>
</div>
</body>
</html>
