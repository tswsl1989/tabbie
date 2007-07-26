  <div>
    <h2 class="hide">Tab Print Modules</h2>
    <form action=print.php?moduletype=tab method="POST">
       <label for="list">List: </label>
        <select id="list" name="list">
        <option value="main" <?echo ($list=="main")?"selected":"" ?>>Summary</option>
        <option value="venue" <?echo ($list=="venue")?"selected":"" ?>>Allocated Venues</option>
    </select> <br/><br/>
    
    <input type="submit" value="Change"/>
     </form>
   </div>

