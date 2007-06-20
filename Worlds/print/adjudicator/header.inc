  <div>
    <h2 class="hide">Adjudicator Print Modules</h2>
    <form action=print.php?moduletype=adjudicator method="POST">
   	<label for="list">List: </label>
    	<select id="list" name="list">
		<option value="main" <?echo ($list=="main")?"selected":"" ?>>Summary</option>
		<option value="adjsheet" <?echo ($list=="adjsheet")?"selected":"" ?>>Personalised Adjudicator Sheets</option>
	</select> <br/><br/>
	
	<input type="submit" value="Change"/>
     </form>
   </div>

