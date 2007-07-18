<div id="submenu">
    <h2 class="hide">Results Submenu</h2>
    <ul>
    <?
    for($x=0;$x<$numdraws;$x++) 
    {
        if (@$roundno==($x+1))
        $tag="class=\"activemain\"";
        else
        $tag="";

        echo "<li><a href=\"draw.php?moduletype=round&amp;action=showdraw&amp;roundno=".($x+1)."\" $tag>Round ".($x+1) ."</a></li>\n";
    }

        ?>
        <li><a href="draw.php?moduletype=currentdraw"<?echo ($moduletype=='currentdraw')?"class=\"activemain\"":""?>>Current Draw</a></li>
        <li><a href="draw.php?moduletype=manualdraw" <?echo ($moduletype=='manualdraw')?"class=\"activemain\"":""?>>Manual Draw</a></li>
    </ul>
</div>
