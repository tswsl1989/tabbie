  <div>
    <h2 class="hide">Teamstandings Submenu</h2>
    <form action=standing.php?moduletype=roundbreak method="POST">
        <label for="list">List Type: </label>
        <select id="list" name="list">
            <option value="all" <?echo ($list=="all")?"selected":"" ?>>All</option>
            <option value="esl" <?echo ($list=="esl")?"selected":"" ?>>ESL</option>
        </select> <br/><br/>
            
    <input type="submit" value="Change"/>
     </form>
   </div>

