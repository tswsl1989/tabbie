<div id="submenu">
    <h2 class="hide">Results Submenu</h2>
    <ul>
    <?
        $roundno=@$_GET['roundno'];
    for($x=0;$x<$numresults;$x++) 
    {
        if ($roundno==($x+1))
        $tag="class=\"activemain\"";
        else
        $tag="";
        echo "<li><a href=\"result.php?moduletype=round&amp;action=display&amp;roundno=".($x+1)."\" $tag>Round ".($x+1)." </a></li>\n";
    }
    ?>
        <li><a href="result.php?moduletype=currentround&amp;action=display" <?echo ($roundno=="")?"class=\"activemain\"":""?>>Current Round</a></li>
    </ul>
</div>