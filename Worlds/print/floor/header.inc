  <div>
    <h2 class="hide">Floor Managers Print Modules</h2>
    <form action=print.php?moduletype=floor method="POST">
   	<label for="list">List: </label>
    	<select id="list" name="list">
		<option value="main" <?echo ($list=="main")?"selected":"" ?>>Summary</option>
		<option value="teams" <?echo ($list=="teams")?"selected":"" ?>>Team List</option>
		<option value="adj" <?echo ($list=="adj")?"selected":"" ?>>Adjudicator List</option>
		<option value="draw" <?echo ($list=="draw")?"selected":"" ?>>Draw</option>
	</select> <br/><br/>
	
	<input type="submit" value="Change"/>
     </form>
   </div>

