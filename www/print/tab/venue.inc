<?
/******************************************************************************
File    :   venue.inc

Author  :   AK

Purpose :   This file prints the list of allocated venues (for the tab room)

******************************************************************************/


include("includes/dbconnection.php"); //Database Connection

$action=@$_GET['action'];
$warning=@$_GET['warning'];

$roundno = $numdraws;

if ($numdraws == $numresults)
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
    echo "<h3><a href=\"print.php?moduletype=tab&list=$list&action=display&warning=done\">Click to confirm</a></h3>";
}


if ($action == "display")
{
    $warning="null";
    
    //Open the text file
    $filename = "print/outputs/tab_venue_$roundno.html";
    $fp = fopen($filename, "w");
    
    $text="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"> \n<html> \n <head> ";
    $text.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/> \n <style type=\"text/css\"> \n";
    $text.=" table \n { \n page-break-after : always;  \n } \n";
    $text.=" </style> \n </head> \n <body> \n";
    $text.=" <h2>$title</h2><br/>\n\n";

    $text.=" <table name=\"venuelist\" border=\"1\" width=\"100%\"> \n";
    $text.=" <tr><th>Venue</th><th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th>&nbsp;</th> ";
    $text.=" <th>Venue</th><th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th><th>&nbsp;</th> ";
    $text.=" <th>Venue</th><th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th></tr> \n";
    fputs($fp,$text);
    
    $venuequery="SELECT v.venue_name AS venue FROM venue AS v, draw_round_$roundno AS draw WHERE v.venue_id = draw.venue_id ORDER BY venue ";
    $venueresult=mysql_query($venuequery);
    $count=0;
    while ($venuerow=mysql_fetch_assoc($venueresult))
    {    $venue = $venuerow['venue'];
        if (($count%3)==0)
            $text=" <tr>\n";
        else
            $text=" ";
        $count++;
        $text.="  <td>$venue</td><td>&nbsp;</td>\n";
        if (($count%3)==0)
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
    echo "<h3><a href=\"$filename\">Tab Room Allocated Venue List (Round $roundno)</a></h3> ";
}

?>
</div>
</body>
</html>
