<?
/******************************************************************************
File    :   venue.inc

Author  :   AK

Purpose :   This file prints the list of allocated venues (for the tab room)

******************************************************************************/

include("config/dbconnection.php"); //Database Connection

//Open the text file

$query="SHOW TABLES LIKE 'draw_round%'";
$result=mysql_query($query);
$roundno=mysql_num_rows($result);

$fp=fopen("print\DisplayFiles\Draw_$roundno.html","w");

$text="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"> \n<html> \n <head> ";
$text.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/> \n ";
$text.=" </head> \n <body> \n";
$text.=" <div id=\"datacontainer\" style=\"position:absolute;left:0;top:10;width:100%\"> \n";
$text.=" <table style=\"width:100%;\" border=\"1\"> \n";
fputs($fp,$text);

$color["SS"]="#FFFFFF";
$color["SS B1L"]="lightgreen";
$color["SS B1R"]="#2F8FFF";
$color["SS L1L"]="#FFFF5F";
$color["SS L1R"]="#2F8FFF";
$color["S4 B2A"]="#FF3F3F";
$color["S4 B2B"]="#FF3F3F";

$venue_query = "SELECT draw.debate_id AS debate_id, draw.venue_id AS venue_id, venue.venue_name AS venue_name, venue.venue_location AS venue_location, oguniv.univ_code AS og_univ_code, ogteam.team_code AS og_team_code, oouniv.univ_code AS oo_univ_code, ooteam.team_code AS oo_team_code, cguniv.univ_code AS cg_univ_code, cgteam.team_code AS cg_team_code, couniv.univ_code AS co_univ_code, coteam.team_code AS co_team_code ";
$venue_query .= "FROM draw_round_$roundno AS draw, venue AS venue, university AS oguniv, team AS ogteam, university AS oouniv, team AS ooteam, university AS cguniv, team AS cgteam, university AS couniv, team AS coteam ";
$venue_query .= "WHERE draw.venue_id = venue.venue_id AND ogteam.team_id = draw.og AND oguniv.univ_id = ogteam.univ_id AND ooteam.team_id = draw.oo AND oouniv.univ_id = ooteam.univ_id AND cgteam.team_id = draw.cg AND cguniv.univ_id = cgteam.univ_id AND coteam.team_id = draw.co AND couniv.univ_id = coteam.univ_id ";
$venue_query .= "ORDER BY venue_name ";

$venue_result = mysql_query($venue_query);

while ($venue_row=mysql_fetch_assoc($venue_result))
{	$venue_location = $venue_row['venue_location'];
	$venue_name = $venue_row['venue_name'];
	$debate_id = $venue_row['debate_id'];
	$og = $venue_row['og_univ_code'].' '.$venue_row['og_team_code'];
	$oo = $venue_row['oo_univ_code'].' '.$venue_row['oo_team_code'];
	$cg = $venue_row['cg_univ_code'].' '.$venue_row['cg_team_code'];
	$co = $venue_row['co_univ_code'].' '.$venue_row['co_team_code'];

	$colour=$color[$venue_location];

	$text= " <tr><td width=\"110\" style=\"background-color:$colour;\"><font size=\"5\"><b>$venue_name</b></font></td><td width=\"142\" style=\"background-color:$colour;\">$og</td><td width=\"142\" style=\"background-color:$colour;\">$oo</td><td width=\"142\" style=\"background-color:$colour;\">$cg</td><td width=\"142\" style=\"background-color:$colour;\">$co</td>\n ";

	// Get Chief Adjudicator
	$chfadj_query = "SELECT adjud.adjud_name AS adjud_name FROM adjud_round_$roundno AS round, adjudicator AS adjud WHERE round.debate_id = $debate_id AND round.status = 'chair' AND adjud.adjud_id = round.adjud_id ";
	$chfadj_result = mysql_query($chfadj_query);
	$chfadj_row=mysql_fetch_assoc($chfadj_result);
	$chair = $chfadj_row['adjud_name'];
	$text.=" <td width=\"240\" style=\"background-color:$colour;\"><font size=\"5\"><b>* $chair</b>";

	// Get Panelists
	$pnladj_query = "SELECT adjud.adjud_name AS adjud_name FROM adjud_round_$roundno AS round, adjudicator AS adjud WHERE round.debate_id = $debate_id AND round.status = 'panelist' AND adjud.adjud_id = round.adjud_id ";
	$pnladj_result = mysql_query($pnladj_query);
	while ($pnladj_row=mysql_fetch_assoc($pnladj_result))
	{	$panelist = $pnladj_row['adjud_name'];
		$text.=" <br/>* $panelist";
	}

	// Get Trainees
	$trnadj_query = "SELECT adjud.adjud_name AS adjud_name FROM adjud_round_$roundno AS round, adjudicator AS adjud WHERE round.debate_id = $debate_id AND round.status = 'trainee' AND adjud.adjud_id = round.adjud_id ";
	$trnadj_result = mysql_query($trnadj_query);
	while ($trnadj_row=mysql_fetch_assoc($trnadj_result))
	{	$trainee = $trnadj_row['adjud_name'];
		$text.=" <br/>* $trainee (Trainee)";
	}

	$text.=" </font></td></tr>\n";
	fputs($fp,$text);
}

$text=" </table></div> <br/>\n\n";
$text.=" <div>\n <script language=\"JavaScript1.2\">\n //<iframe> script by Dynamicdrive.com\n ";
$text.=" //Specify speed of scroll. Larger=faster \n";
$text.=" var scrollspeed=cache=2 \n";

$text.=" function initialize(){ \n";
$text.=" //marqueeheight=document.all? parent.document.all.datamain.height : parent.document.getElementById(\"datamain\").getAttribute(\"height\") \n";
$text.=" parent.document.getElementById(\"datamain\").getAttribute(\"height\")? teststring=\"true\": teststring=\"false\" \n";
$text.=" dataobj=document.all? document.all.datacontainer : document.getElementById(\"datacontainer\") \n";
$text.=" dataobj.style.top=5 \n";
$text.=" thelength=dataobj.offsetHeight \n";
$text.=" delay_time = 2 \n";
$text.=" movespeed = 75 \n";
$text.=" pause() } \n";

$text.=" function setSpeed(direction) { \n";
$text.=" if (direction == 0) \n";
$text.=" 	(scrollspeed == 0)?(scrollspeed=scrollspeed):(scrollspeed = scrollspeed - 1) \n";
$text.=" else { \n";
$text.=" 	if (direction == 1) \n";
$text.=" 		scrollspeed = scrollspeed + 1 \n";
$text.=" 	} \n";
$text.=" } \n";

$text.=" function reset() { \n";
$text.=" dataobj.style.top=5 \n";
$text.=" setTimeout(\"scrolltest()\",movespeed) \n";
$text.=" } \n";

$text.=" function pause() { \n";
$text.=" dataobj.style.top=5 \n";
$text.=" setTimeout(\"reset()\",delay_time*1000) \n";
$text.=" } \n";

$text.=" function bottompause() { \n";
$text.=" setTimeout(\"pause()\",delay_time*1000) \n";
$text.=" } \n";


$text.=" function scrolltest(){ \n";
$text.=" dataobj.style.top=parseInt(dataobj.style.top)-scrollspeed \n";
$text.=" //prompt(dataobj.style.top) \n";
$text.=" //prompt(thelength*(-1)+marqueeheight) \n";
$text.=" if (parseInt(dataobj.style.top)<(thelength*(-1)+520)) \n";
$text.=" 	setTimeout(\"bottompause()\",50) \n";
$text.=" else \n";
$text.=" 	setTimeout(\"scrolltest()\",movespeed) \n";
$text.=" } \n";

$text.=" window.onload=initialize \n";

$text.=" </script>\n </div>\n </body>\n </html>\n ";
fputs($fp,$text);
fclose($fp);
echo "<h3>File created successfully! </h3>";
?>
</div>
</body>
</html>
