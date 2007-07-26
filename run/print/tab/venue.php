<?
//what's the use of this? Venues with unknown locations??!

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
