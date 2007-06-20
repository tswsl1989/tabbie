<?
/******************************************************************************
File    :   adjlist.inc

Author  :   AK

Purpose :   This file prints the adjudicator history for the CA/DCAs

******************************************************************************/


include("includes/dbconnection.php"); //Database Connection

$action=@$_GET['action'];

$roundno = $numdraws;

if (!$roundno)
{   
    $msg[]="No draws have been finalised as yet.";
    $msg[]="This module is used only after the tournament has started.";
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
	$filename = "print/outputs/chf_adjud_adj_list_$roundno.html";
	$fp = fopen($filename, "w");
	
	$text="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"> \n<html> \n <head> ";
	$text.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/> \n";
	$text.=" </head> \n <body> \n";
	$text.=" <h2>$title</h2><br/>\n\n";
	fputs($fp,$text);
	
	$adjquery = "SELECT adjud_name, ranking, adjud_id, conflicts FROM adjudicator ORDER BY adjud_name ";
	$adjresult=mysql_query($adjquery);
	
	if ($adjresult)
	{	$text=" <table name=\"adjlist\" border=\"1\" width=\"100%\"> \n";
		$text.=" <tr><th>Name</th><th>Ranking</th><th>Last Status</th><th>Nbr of Chair</th><th>Nbr of Panelist</th><th>Nbr of Trainee</th><th>Conflicts</th></tr> \n";
		fputs($fp,$text);

		while ($adjrow=mysql_fetch_assoc($adjresult))
		{	$adjud_id=$adjrow['adjud_id'];
			$adjud_name=$adjrow['adjud_name'];
			$adjud_rank=$adjrow['ranking'];
			$adjud_conflicts=$adjrow['conflicts'];
			
			$prevquery="SELECT status FROM adjud_round_$roundno WHERE adjud_id='$adjud_id' ";
			$prevresult=mysql_query($prevquery);
			if($prevcount=mysql_num_rows($prevresult))
			{	$prevrow=mysql_fetch_assoc($prevresult);
				$prev_status=$prevrow['status'];
			}
			else
				$prev_status="<center><b> - </b></center>";
			
			// Get count of positions
			$chair_count=0;
			$panel_count=0;
			$trainee_count=0;
			for ($i=1; $i<=$roundno; $i++)
			{	
				// Count of Chair
				$chairquery="SELECT adjud_id FROM adjud_round_$i WHERE status='chair' AND adjud_id='$adjud_id' ";
				$chairresult=mysql_query($chairquery);
				if ($chaircount=mysql_num_rows($chairresult))
					$chair_count++;
					
				// Count of Panelist
				$panelquery="SELECT adjud_id FROM adjud_round_$i WHERE status='panelist' AND adjud_id='$adjud_id' ";
				$panelresult=mysql_query($panelquery);
				if ($panelcount=mysql_num_rows($panelresult))
					$panel_count++;
					
				// Count of Trainee
				$traineequery="SELECT adjud_id FROM adjud_round_$i WHERE status='trainee' AND adjud_id='$adjud_id' ";
				$traineeresult=mysql_query($traineequery);
				if ($traineecount=mysql_num_rows($traineeresult))
					$trainee_count++;
			}
			
			$text=" <tr><td>$adjud_name</td><td>$adjud_rank</td><td>$prev_status</td><td>$chair_count</td><td>$panel_count</td><td>$trainee_count</td><td>$adjud_conflicts</td></tr> \n";
			fputs($fp,$text);
		}
		$text=" </table>\n";
		fputs($fp,$text);
	}


	$text=" </body>\n</html>\n";
	fputs($fp,$text);
	fclose($fp);
	echo "<h3>File created successfully!</h3> ";
	echo "<h3><a href=\"$filename\">Adjudicator List for Round $roundno</a></h3> ";
}
}

?>
</div>
</body>
</html>
