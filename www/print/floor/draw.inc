<?
/******************************************************************************
File    :   draw.inc

Author  :   AK

Purpose :   This file prints the debate list with venue (for the floor managers)

******************************************************************************/


include("config/dbconnection.php"); //Database Connection

$action=@$_GET['action'];
$warning=@$_GET['warning'];

$roundno = $numdraws;

if ($numdraws = $numresults)
{   
    $warnmsg[]="Results for the last draw have been entered already.";
    $warnmsg[]="New draw has not been created.";
    $warnmsg[]="<BR>";
}

$warnmsg[]="Clicking the link below will create the file.";
if ($warning <> "done")
        $action = "warning";

include("header.inc");

switch($action)
{
    case "display":     break;
    case "warning":     $title.=" - Confirm";
                        break;
    default:
                        $action="display";
                        break;
}

echo "<div id=\"content\">";
echo "<h2>$title</h2>\n"; //title

if ($action == "warning")
{
	//Display Messages
	for($x=0;$x<count($warnmsg);$x++)
	    echo "<p class=\"err\">".$warnmsg[$x]."</p>\n";
	echo "<h3><a href=\"print.php?moduletype=floor&list=$list&action=display&warning=done\">Click to confirm</a></h3>";
}


if ($action == "display")
{
	$warning="null";
	
	//Open the text file
	$fp=fopen("print\outputs\Draw_$roundno.html","w");
	
	$text="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"> \n<html> \n <head> ";
	$text.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/> \n <style type=\"text/css\"> \n";
	$text.=" table \n { \n page-break-after : always;  \n } \n";
	$text.=" </style> \n </head> \n <body> \n";
	$text.=" <h2>$title</h2><br/>\n\n";
	fputs($fp,$text);

	$query="SELECT DISTINCT venue_location FROM venue WHERE active='Y' ORDER BY venue_location ";
	$result=mysql_query($query);
	while ($row=mysql_fetch_assoc($result))
	{	$venue_location = $row['venue_location'];
		$venuequery = "SELECT v.venue_name AS venue_name, A1.debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ";
		$venuequery .= "FROM venue AS v, draw_round_$roundno AS A1, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4 ";
		$venuequery .= "WHERE v.venue_location='$venue_location' AND T1.team_id=A1.og AND T2.team_id=A1.oo AND T3.team_id=A1.cg AND T4.team_id=A1.co AND U1.univ_id=T1.univ_id AND U2.univ_id=T2.univ_id AND U3.univ_id=T3.univ_id AND U4.univ_id=T4.univ_id AND v.venue_id=A1.venue_id "; 
		$venuequery .= "ORDER BY v.venue_name ";
		$venueresult=mysql_query($venuequery);
		if ($venueresult) 
		{
		if ($venuecount=mysql_num_rows($venueresult))
		{	$text=" <h3>$venue_location</h3>\n";		
			$text.=" <table name=\"debatelist".$venue_location."\" border=\"1\" width=\"100%\"> \n";
			$text.=" <tr><th>Venue</th><th>OG</th><th>OO</th><th>CG</th><th>CO</th><th>Adjudicators</th></tr> \n";
			fputs($fp,$text);
			while($venuerow=mysql_fetch_assoc($venueresult))
			{	$venue_name = $venuerow['venue_name'];
				$debate_id = $venuerow['debate_id'];
				$og_code = $venuerow['ogtc']." ".$venuerow['ogt'];
				$oo_code = $venuerow['ootc']." ".$venuerow['oot'];
				$cg_code = $venuerow['cgtc']." ".$venuerow['cgt'];
				$co_code = $venuerow['cotc']." ".$venuerow['cot'];
				
				//Find Chair
				$chairquery="SELECT A.adjud_name AS adjud_name FROM adjud_round_$roundno AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='chair' AND T.debate_id='$debate_id' ";
				$resultadjud=mysql_query($chairquery);
				$rowadjud=mysql_fetch_assoc($resultadjud);
				$chair="{$rowadjud['adjud_name']}";
							
				//Find Panelists
				$panelquery="SELECT A.adjud_name AS adjud_name FROM adjud_round_$roundno AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='panelist' AND T.debate_id='$debate_id' ORDER by ranking DESC";
				$resultadjud=mysql_query($panelquery);
				$panelist="";
				if (mysql_num_rows($resultadjud)>0)
				{	while($rowadjud=mysql_fetch_assoc($resultadjud))
					$panelist.="<br/>{$rowadjud['adjud_name']}";
				}
				
				//Find Trainees
				$panelquery="SELECT A.adjud_name AS adjud_name FROM adjud_round_$roundno AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='trainee' AND T.debate_id='$debate_id' ORDER by A.ranking DESC";
				$resultadjud=mysql_query($panelquery);
				$trainee="";
				if (mysql_num_rows($resultadjud)>0)
				{	while($rowadjud=mysql_fetch_assoc($resultadjud))
						$trainee.="<br/>{$rowadjud['adjud_name']}"." (Trainee)";
				}

				$text=" <tr><td valign=\"top\">$venue_name</td><td valign=\"top\">$og_code</td><td valign=\"top\">$oo_code</td><td valign=\"top\">$cg_code</td><td valign=\"top\">$co_code</td><td><b>$chair</b>$panelist $trainee</tr>\n";
				fputs($fp,$text);
			}
			$text=" </table> <br/>\n\n";
			fputs($fp,$text);
		}
		}
	}
	$text=" </body>\n</html>\n";
	fputs($fp,$text);
	fclose($fp);
	echo "File created successfully! <br/>";
	echo "<a href=\"print/Outputs/Draw_$roundno.html\">Floor Managers' Draw (Round $roundno)</a> ";
}

?>
</div>
</body>
</html>
