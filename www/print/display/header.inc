  <div>
    <h2 class="hide">Display Modules</h2>
    <form action=print.php?moduletype=display method="POST">
   	<label for="list">List: </label>
    	<select id="list" name="list">
		<option value="main" <?echo ($list=="main")?"selected":"" ?>>Summary</option>
		<option value="draw" <?echo ($list=="draw")?"selected":"" ?>>Draw</option>
	</select> <br/><br/>
	
	<input type="submit" value="Change"/>
     </form>
   </div>

