<?
/******************************************************************************
File    :   teams.inc

Author  :   AK

Purpose :   This file prints the team list with venue (for the floor managers)

******************************************************************************/


include("includes/dbconnection.php"); //Database Connection

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
    $filename = "print/outputs/floor_teams_$roundno.html";
    $fp = fopen($filename, "w");
    
    $text="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"> \n<html> \n <head> ";
    $text.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/> \n <style type=\"text/css\"> \n";
    $text.=" table \n { \n page-break-after : always;  \n } \n";
    $text.=" </style> \n </head> \n <body> \n";
    $text.=" <h2>$title</h2><br/>\n\n";
    
    $text.=" <h3>$venue_location</h3>\n";        
    $text.=" <table name=\"teamlist\" border=\"1\" width=\"100%\"> \n";
    $text.=" <tr><th>Team Name</th><th>Venue</th><th>Venue Location</th><th>&nbsp;</th> \n";
    $text.=" <th>Team Name</th><th>Venue</th><th>Venue Location</th></tr>\n";
    fputs($fp,$text);

    $venuequery="SELECT v.venue_id AS venue_id, v.venue_location AS venue_location, v.venue_name AS venue_name, t.team_id AS team_id, t.team_code AS team_code, u.univ_code AS univ_code ";
    $venuequery.="FROM team AS t, university AS u, draw_round_$roundno AS d, venue AS v ";
    $venuequery.="WHERE d.venue_id=v.venue_id AND (t.team_id=d.og OR t.team_id=d.oo OR t.team_id=d.cg OR t.team_id=d.co) AND t.univ_id=u.univ_id ";
    $venuequery.="ORDER BY univ_code, team_code ";
    $venueresult=mysql_query($venuequery);
    $count=0;
    while ($venuerow=mysql_fetch_assoc($venueresult))
    {    $venue_location = $venuerow['venue_location'];
        $teamname = $venuerow['univ_code']." ".$venuerow['team_code'];
        $venuename = $venuerow['venue_name']    ;
        if (($count%2)==0)
            $text=" <tr>\n";
        else
            $text=" ";
        $count++;
        $text=" <td>$teamname</td><td>$venuename</td><td>$venue_location</td>\n";
        if (($count%2)==0)
            $text.=" </tr>\n";
        else
            $text.=" <td>&nbsp;</td>";
        fputs($fp,$text);
    }
    $text=" </table> <br/>\n\n";
    $text.=" </body>\n</html>\n";
    fputs($fp,$text);
    fclose($fp);
    echo "<h3>File created successfully! </h3>";
    echo "<h3><a href=\"$filename\">Floor Managers' Team List (Round $roundno)</a></h3> ";
}

?>
</div>
</body>
</html>
