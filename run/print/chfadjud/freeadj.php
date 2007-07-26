<?
/******************************************************************************
File    :   freeadj.php

Author  :   AK

Purpose :   This file prints the free adjudicators for the CA/DCAs before manual draw

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

include("header.php");

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
    $filename = "print/outputs/chief_adjudicator_debate_list_$roundno.html";
    $fp = fopen($filename, "w");
    
    $text="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\"> \n<html> \n <head> ";
    $text.="<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/> \n";
    $text.=" </head> \n <body> \n";
    $text.=" <h2>$title</h2><br/>\n\n";
    fputs($fp,$text);
    
$query = "SELECT A.adjud_id, A.adjud_name, A.ranking, A.conflicts ";
$query .= "FROM adjudicator A ";
$query .= "LEFT JOIN temp_adjud_round_$roundno T ON A.adjud_id = T.adjud_id ";
$query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 

    $result = mysql_query($query);
    $count=0;
    if ($result)
    {    $text=" <table name=\"freeadj\" border=\"1\" width=\"100%\"> \n";
        $text.=" <tr><th>Adjudicator Name</th><th>Ranking</th><th>Conflicts</th><th>&nbsp;</th> \n";
        $text.=" <th>Adjudicator Name</th><th>Ranking</th><th>Conflicts</th></tr> \n";
        fputs($fp,$text);
        
        while ($row=mysql_fetch_assoc($result))
        {    $adjudname=$row['adjud_name'];
            $ranking=$row['ranking'];
            $conflicts=$row['conflicts'];
            if (($count%2)==0)
                $text=" <tr>\n";
            else
                $text=" ";
            $count++;            
            $text=" <td>$adjudname</td><td>$ranking</td><td>$conflicts</td> \n";
            if (($count%2)==0)
                $text.=" </tr>\n";
            else
                $text.=" <td>&nbsp;</td>";
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
