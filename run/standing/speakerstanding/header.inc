  <div>
    <h2 class="hide">Speakerstandings Submenu</h2>
    <form action=standing.php?moduletype=speakerstanding method="POST">
        <label for="list">List Type: </label>
        <select id="list" name="list">
            <option value="all" <?echo ($list=="all")?"selected":"" ?>>All</option>
            <option value="esl" <?echo ($list=="esl")?"selected":"" ?>>ESL</option>
        </select> <br/><br/>
        
        <label for="round">Round: </label>
        <select id="round" name="round">
        <?
            $query="SHOW TABLES LIKE 'result_round%'";
        $result=mysql_query($query);
        $numresults=mysql_num_rows($result);
        if (!$round)
            $round=$numresults;
        for ($i=1;$i<=$numresults;$i++)
        {    $text="<option value=\"".$i."\" ";
            if ($i==$round)
                $text.="selected";
            $text.=">Round: ".$i."</option>";
            echo "$text";
        }
    ?>
    </select> <br/><br/>
    
    <input type="submit" value="Change"/>
     </form>
   </div>