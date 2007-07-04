  <div>
    <h2 class="hide">CA/DCA Print Modules</h2>
    <form action=print.php?moduletype=chfadjud method="POST">
       <label for="list">List: </label>
        <select id="list" name="list">
        <option value="main" <?echo ($list=="main")?"selected":"" ?>>Summary</option>
        <option value="debatelist" <?echo ($list=="debatelist")?"selected":"" ?>>Debate List (Manual Draw)</option>
        <option value="freeadj" <?echo ($list=="freeadj")?"selected":"" ?>>Free Adjudicators (Manual Draw)</option>
        <option value="adjlist" <?echo ($list=="adjlist")?"selected":"" ?>>Adjudicator History</option>
        <option value="teamadjcount" <?echo ($list=="teamadjcount")?"selected":"" ?>>Team-Adjudicator Count</option>
    </select> <br/><br/>
    
    <input type="submit" value="Change"/>
     </form>
   </div>

