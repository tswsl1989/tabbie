  <div>
    <h2 class="hide">Teamstandings Submenu</h2>
    <form action=standing.php?moduletype=position method="POST">
       <label for="round">Round: </label>
        <select id="round" name="round">
        <?
            $query="SHOW TABLES LIKE 'result_round%'";
        $result=mysql_query($query);
        $numresults=mysql_num_rows($result);
        if (!$round)
            $round=$numdraws;
        for ($i=1;$i<=$numdraws;$i++)
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

