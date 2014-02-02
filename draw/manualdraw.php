<?php /* begin license *
 * 
 *     Tabbie, Debating Tabbing Software
 *     Copyright Contributors
 * 
 *     This file is part of Tabbie
 * 
 *     Tabbie is free software; you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation; either version 2 of the License, or
 *     (at your option) any later version.
 * 
 *     Tabbie is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with Tabbie; if not, write to the Free Software
 *     Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * 
 * end license */
require_once("includes/display.php");
require_once("includes/backend.php");
require_once("includes/db_tools.php");
require_once("includes/http.php");
require_once("draw/adjudicator/simulated_annealing.php");
require_once("controller/draw/adjud.php");

$action="";
$lastmodified="";
$allow_override=0;
if(array_key_exists("action", @$_GET)) $action=trim(@$_GET['action']); //Check action
if ($action=="") {
	//redirect("draw.php?moduletype=currentdraw");
	$action="display";
}

if(array_key_exists("debate_id", @$_GET)) $debate_id=trim(@$_GET['debate_id']);
$title="Draw : Round ".$nextround;


if (!has_temp_draw()) {
	$exist=0;
	$msg[]="Missing tables in database!!";
	$msg[]="Please calculate the draw first!!";
} else { // continue with rest of operation
	$exist=1;

	if (($action=="edit")||($action=="doedit")) {
		//check for debate id. Display Message if invalid
		$query="SELECT * FROM temp_draw WHERE debate_id=?";
		$result=qp($query, array($debate_id));
		if ($result->RecordCount()==0) {
			$action="display";
		} else {
			$rowdebate=$result->FetchRow();
			$venue_id=$rowdebate['venue_id'];
		}
	}

	if ($action=="doedit") {
		//Check for venue edit
		$venue_id_edit=@$_POST["venueselect"];
		if (($venue_id_edit)&&($venue_id_edit!=$venue_id)) {
			//Check if venue valid
			$query= "SELECT V.venue_id, V.venue_name FROM venue V LEFT OUTER JOIN temp_draw T ON V.venue_id = T.venue_id WHERE V.active = 'Y' AND T.venue_id IS NULL AND V.venue_id=?";
			$result=qp($query, array($venue_id_edit));
			if ($result->RecordCount()==0) {
				$msg[]="Invalid Venue or Venue already taken.";
			} else {
				//Change if valid
				$query="UPDATE temp_draw SET venue_id=? WHERE debate_id=?";
				$result=qp($query, array($venue_id_edit, $debate_id));
				if (!$result) {
					$msg[]="Some error adding to database: ".$DBConn->ErrorMsg();
				} else {
					//Update message
					$msg[]="Venue Updated.";
				}
			}
		}

		$validate=1;
		//Check for no chief adjudicators and issue warning only
		$chief=0;
		$query = "SELECT A.adjud_id, A.adjud_name FROM adjudicator A LEFT JOIN temp_adjud T ON A.adjud_id = T.adjud_id ";
		$query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 

		$result=q($query);

		while($row=$result->FetchRow()) {
			$adjud_id=$row['adjud_id'];

			if (@$_POST["radio_$adjud_id"]=="chair") {
				if ($chief==0) {
					$chief=1;
				} else {
					$msg[]="ERROR! Attempt to assign two chair adjudicators";
					$validate=0;
					$action="edit";
				}
			}
		}

		//If all okay do the necessary addition
		if ($validate==1) {

			$query = "SELECT A.adjud_id, A.adjud_name FROM adjudicator A LEFT JOIN temp_adjud T ON A.adjud_id = T.adjud_id ";
			$query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 
			$result=q($query);

			while ($row=$result->FetchRow()) {
				$adjud_id=$row['adjud_id'];

				if (@$_POST["radio_$adjud_id"]!="none") {
					$status=@$_POST["radio_$adjud_id"];
					$validate=1;
					if ($status=="chair") {
						//check for duplicate chief
						$query="SELECT adjud_id FROM temp_adjud WHERE debate_id=? AND status='chair'";
						$chiefresult=qp($query, array($debate_id));
						if ($chiefresult->RecordCount()>0) {
							$rowchief=$chiefresult->FetchRow();
							if ((@$_POST["check_{$rowchief['adjud_id']}"])!="on") {
								$validate=0;
								$msg[]="ERROR! Attempt to assign more than one Chair Adjudicator";
								$action="edit";
							}
						}
					}

					if ($validate==1) {
						if(!add_adjudicator($adjud_id, $debate_id, $status, $nextround)){
							$msg[]="ERROR: Adjudicator failed to assign.";
							$action="edit";
						}
					}
				}
			}

			//Delete
			$query="SELECT A.adjud_id AS adjud_id,adjud_name,T.status FROM adjudicator AS A,temp_adjud AS T WHERE A.adjud_id=T.adjud_id AND T.debate_id=? ORDER BY status";

			$result=qp($query, array($debate_id));

			while($row=$result->FetchRow()) {
				$adjud_id=$row['adjud_id'];
				if(array_key_exists("check_$adjud_id",@$_POST)){
					if (@$_POST["check_$adjud_id"]=="on") {
						$query="DELETE FROM temp_adjud WHERE adjud_id=?";
						qp($query, array($adjud_id));
					}
				}
			}
		}

		if ($action=="doedit") {
			//set action to display
			$action="display";
			$lastmodified=$debate_id;

			//check allocation status and issue messages accordingly
			$query = "SELECT v.venue_name, COUNT( a.adjud_id ) AS num_panel FROM venue v, temp_draw d, temp_adjud a WHERE v.venue_id = d.venue_id AND d.debate_id = a.debate_id AND a.status = 'panelist' GROUP BY v.venue_id HAVING num_panel < 2 ";
			$result=q($query);
			while($rownumpanel=$result->FetchRow()) {
				$venue=$rownumpanel['venue_name'];
				$msg[]="$venue has only one panelist";
			}
		}
	}

	if ($action=="finalise") {
		//check for all conditions satisfied
		$validate=1;
		$query = "SELECT DISTINCT COUNT(*) AS numadjud FROM temp_adjud WHERE STATUS = 'chair'";
		$resultnumadjud=q($query);
		$rownumadjud=$resultnumadjud->FetchRow();
		$adjudcount=$rownumadjud['numadjud']; //count chief adjudicators

		$query = "SELECT COUNT(*) AS numdebates FROM temp_draw";
		$resultnumdebates=q($query);
		$rownumdebates=$resultnumdebates->FetchRow();
		$debatecount=$rownumdebates['numdebates'];

		if ($adjudcount!=$debatecount) { //No. of debates and chief adjudicators dont match
			$validate=0;
			$msg[]="ERROR! There are debates with no Chair Adjudicator Allocated ($adjudcount/$debatecount)";
		}

		$query="SELECT adjud_id, og, oo, cg, co FROM temp_adjud INNER JOIN temp_draw ON temp_adjud.debate_id=temp_draw.debate_id";
		$result=q($query);
		if(!($result)) {
			$validate=0;
			$msg[]="ERROR - debates query failed to execute";
		} else {
			while($row=$result->FetchRow()){
				$ogid=$row['og'];
				$ooid=$row['oo'];
				$cgid=$row['cg'];
				$coid=$row['co'];
				$query="SELECT univ_id FROM team WHERE team_id=? OR team_id=? OR team_id=? OR team_id=?";
				$univ_id_result=qp($query, array($ogid, $ooid, $cgid, $coid));
				if($univ_id_result->RecordCount()!=4){
					$validate=0;
					$msg[]="ERROR - more than four teams for id (!)";
					$msg[]="You appear to have a corrupted database. Consider restoring from a backup and check previous rounds' data carefully.";
				}
				$univ_ids=array();
				while($univ_id_row=$univ_id_result->FetchRow()){
					$univ_ids[]=$univ_id_row['univ_id'];
				}

				$query="SELECT * FROM strikes WHERE adjud_id=? AND ((team_id=? OR team_id=? OR team_id=? OR team_id=?) OR ((univ_id=? OR univ_id=? OR univ_id=? OR univ_id=?) AND team_id IS NULL))";
				if(!($strikeresult=qp($query, array($row['adjud_id'], $ogid, $ooid, $cgid, $coid, $univ_ids[0], $univ_ids[1], $univ_ids[2], $univ_ids[3])))) {
					$validate=0;
					$msg[]="ERROR - strike query failed to execute";
				} else if($strikeresult->RecordCount()>0) {
					$validate=0;
					$msg[]="ERROR - strike in draw";
					$msg[]="Adjudicator ".$row["adjud_id"]." is conflicted from their debate. Either clear the conflict or reallocate the adjudicator to proceed.";	
					$allow_override = 1;
					if(isset($_GET['override']) && $_GET['override']=='alpha'){
						$msg[]="Adjudicator clash override in effect";
						$validate=1;
					}
				}
			}
		}
		//Still within $action=="validate"
		if ($validate==1) {
			//create tables
			//Insert Values
			$query="INSERT INTO draws SELECT ? as 'round_no', debate_id, og, oo, cg, co, venue_id FROM temp_draw";
			qp($query, array($nextround));

			$query="INSERT INTO draw_adjud SELECT ? as 'round_no', debate_id, adjud_id, status FROM temp_adjud";
			qp($query, array($nextround));

			if ($scoring_factors['eballots_enabled'] == 1) {
				$rs=qp("SELECT * FROM draws WHERE round_no = ?", array($nextround));
				while ($r = $rs->FetchRow()) {
					$query="INSERT INTO eballot_rooms (round_no, debate_id, auth_code) VALUES (?, ?, ?)";
					$auth_code = hash("crc32b", implode("/", array($r['debate_id'], $nextround, date("c"))));
					if (!qp($query, array($nextround, $r['debate_id'], $auth_code))) {
						$msg[]="Failed to add eBallot details for debate ".@$r['debate_id']." - ".$DBConn->ErrorMsg();
					}
				}
			} else {
				die("eBallots disabled");
			}

			//Make non-chair trainees, trainees.
			$query="UPDATE draw_adjud INNER JOIN adjudicator ON draw_adjud.adjud_id = adjudicator.adjud_id SET draw_adjud.status = 'trainee' WHERE adjudicator.status = 'trainee' AND draw_adjud.status != 'chair' AND draw_adjud.round_no = ?";
			qp($query, array($nextround));

			//Delete Temporary Tables
			$query="DROP TABLE temp_draw";
			q($query);
			$query="DROP TABLE temp_adjud";
			q($query);
			$query="DROP TABLE IF EXISTS draw_lock";
			q($query);
			store_settings_to_db(array("round" => $nextround));
			//Redirect
			if (isset($msg)) {
				$action = "display";
				displayMessagesUL(@$msg);
			} else {
				redirect("draw.php?moduletype=round&action=showdraw&roundno=$nextround");
			}
		}
	}

	//set title
	switch($action) {
	case 'edit':
		$title="Manual Draw Round $nextround : Edit Adjudicator Allocation";
		break;
	case 'display':
		$title="Manual Draw Round $nextround : Display";
		break;
	default:
		$action="display";
		$title="Manual Draw Round $nextround : Display";
		break;
	}

}

if($action=="finalise") {echo "<h2>Draw failed to validate</h2>";}

if(isset($msg)) displayMessagesUL(@$msg);
if($allow_override==1) {
	echo "<h3><a style=\"color: #f00\"href=\"draw.php?moduletype=manualdraw&amp;action=finalise&amp;override=alpha\">Click here to override confilcts and finalise draw</a></h3>";
}
echo "<p>From here you can either:</p>";
echo "<h3><a href=\"draw.php?moduletype=currentdraw&amp;action=draw_adjudicators_again\">Give the computer another shot at allocating the adjudicators (Using the current state to generate a better result).</a></h3>";
echo '<p>or</p>';
echo '<h3><a href="draw.php?moduletype=dragdraw">Manually adjust adjudicators and rooms</a></h3>';


if ($exist) {
    if ($action=="edit") {
	//Display Debate Details
        $query="SELECT  v.venue_id,venue_name,t1.team_code as ogtc,u1.univ_code as oguc,t2.team_code as ootc,u2.univ_code as oouc,t3.team_code as cgtc,u3.univ_code as cguc,t4.team_code as cotc,u4.univ_code as couc, t1.team_id AS ogid, t2.team_id AS ooid, t3.team_id AS cgid, t4.team_id AS coid  ";
        $query.="FROM temp_draw d, team t1,team t2, team t3, team t4, university u1, university u2, university u3, university u4, venue v WHERE d.venue_id=v.venue_id AND d.og=t1.team_id AND t1.univ_id=u1.univ_id ";
        $query.= "AND d.oo=t2.team_id AND t2.univ_id=u2.univ_id AND d.cg=t3.team_id AND t3.univ_id=u3.univ_id AND d.co=t4.team_id AND t4.univ_id=u4.univ_id AND d.debate_id=?";
        
        $result=qp($query, array($debate_id));
        $row=$result->FetchRow();
	$venue_id=$row['venue_id'];
	$venue_name=$row['venue_name'];
    
	$ogpoints = points_for_team($row['ogid'], $numdraws);
	$oopoints = points_for_team($row['ooid'], $numdraws);
	$cgpoints = points_for_team($row['cgid'], $numdraws);
	$copoints = points_for_team($row['coid'], $numdraws);
        
	$totalpoints = $ogpoints + $oopoints + $cgpoints + $copoints;

    
	echo "<div id=\"debatedetails\">\n";
	echo "<h3>Opening Government : ".$row['oguc']." ".$row['ogtc']." (".$ogpoints.")</h3>";
	echo "<h3>Opening Opposition : ".$row['oouc']." ".$row['ootc']." (".$oopoints.")</h3>";
	echo "<h3>Closing Government : ".$row['cguc']." ".$row['cgtc']." (".$cgpoints.")</h3>";
	echo "<h3>Opening Government : ".$row['couc']." ".$row['cotc']." (".$copoints.")</h3>";
	echo "</div>\n";
            
        //For the purpose of testing of conflicts
        $ogid=$row['ogid'];
        $ooid=$row['ooid'];
        $cgid=$row['cgid'];
        $coid=$row['coid'];
        
        echo "<form name=\"manualallocform\" method=\"POST\" action=\"draw.php?moduletype=manualdraw&amp;action=doedit&amp;debate_id=$debate_id\">\n";

        echo "<br/><input type=\"submit\" value=\"Modify Allocation\"/>";
        echo " <input type=\"button\" value=\"Cancel\" onClick=\"location.replace('draw.php?moduletype=manualdraw')\"/>";
	echo "<h3>Venue</h3>\n";
	echo "<select  id=\"venueselect\" name=\"venueselect\" style=\"margin-left:80px\">\n";
	//Display List of available venues
	echo "<option value=\"$venue_id\">$venue_name</option>\n";

	//Load Unoccupied Venues
	$query= "SELECT V.venue_id, V.venue_name FROM venue V LEFT OUTER JOIN temp_draw T ON V.venue_id = T.venue_id WHERE V.active = 'Y' AND T.venue_id IS NULL";
	$resultvenue=q($query);
    
	while($rowvenue=$resultvenue->FetchRow()) {
		echo "<option value=\"{$rowvenue['venue_id']}\">{$rowvenue['venue_name']}</option>\n";
	}
	echo "</select>\n";
	echo "<div>\n";    
	echo "<h3>Allocated Adjudicators</h3>\n";
        
        //read all adjudicators from present round along with their status
        $query="SELECT  A.adjud_id AS adjud_id,adjud_name, T.status AS status, A.ranking AS ranking FROM adjudicator AS A,temp_adjud AS T WHERE A.adjud_id=T.adjud_id AND T.debate_id=? ORDER BY status";
	$result=qp($query, array($debate_id));

        if ($result->RecordCount()!=0) {
		echo "<table>\n";
		echo "<th>Remove(Y/N)</th><th>Adjudicator</th><th>Status</th><th>Ranking</th><th>Conflicts</th>\n";
		while($row=$result->FetchRow()) {
			$adjud_id=$row['adjud_id'];
			echo "<tr>";
			echo "<td><input type=\"checkbox\" name=\"check_{$row['adjud_id']}\"/></td>\n";
			//check for conflict and print name accordingly
			if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
				//Red Color
				$starttag="<span style=\"color:red\">";
				$endtag="</span>";
			} else {
				//Set it to empty
				$starttag="";
				$endtag="";
			}

			echo "<td>$starttag{$row['adjud_name']} ({$row['ranking']})$endtag</td>";
			echo "<td>{$row['status']}</td>";
			echo "<td>{$row['ranking']}</td>";
			echo "<td>".print_conflicts($adjud_id)."</td></tr>";
		}
		echo "</table>\n";
	} else {
		echo "<p>No Adjudicators Assigned</p>";
	}
        echo "</div>";

        echo "<div>";
	echo "<h3>Adjudicator Pool</h3>";
	$query = "SELECT A.adjud_id, A.adjud_name, A.ranking ";
	$query .= "FROM adjudicator A LEFT JOIN temp_adjud T ON A.adjud_id = T.adjud_id ";
	$query .= "WHERE T.adjud_id IS NULL AND active='Y' ORDER BY ranking DESC"; 
	$result=q($query);
	if ($result->RecordCount()!=0) {
		echo "<table>\n";
		echo "<tr><th>Chair</th><th>Panelist</th><th>None</th><th>Name</th><th>Ranking</th><th>Conflicts</th></tr>\n";
		while($row=$result->FetchRow()) {
			$adjud_id=$row['adjud_id'];
			echo "<tr>\n";
			echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"chair\"/></td>\n";
			echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"panelist\"/></td>\n";
			//echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"trainee\"/></td>\n";
			echo "<td><input type=\"radio\" name=\"radio_{$row['adjud_id']}\" value=\"none\" checked=\"checked\"/></td>\n";

			//check for conflict and print name accordingly
			if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
				//Red Color
				$starttag="<span style=\"color:red\">";
				$endtag="</span>";
			} else {
				//Set it to empty
				$starttag="";
				$endtag="";
			}
			echo "<td>$starttag{$row['adjud_name']} ({$row['ranking']})$endtag</td>\n";
			echo "<td>{$row['ranking']}</td>\n";
			echo "<td>".print_conflicts($adjud_id)."</td></tr>";
			echo "</tr>\n";
		}
		echo "</table>\n";    
	} else {
		echo "<p>No more Adjudicators left!</p>\n";
	}
            
        echo "</div>\n";
        echo "<br/><input type=\"submit\" value=\"Modify Allocation\"/>";
        echo " <input type=\"button\" value=\"Cancel\" onClick=\"location.replace('draw.php?moduletype=manualdraw')\"/>";
        echo "</form>\n";
        
       
    }

    if ($action=="display") {
	echo "<h3><a href=\"freeadjud.php?nextround=$nextround\" target=\"_new\">List of Free Adjudicators</a></h3>";
	//Display the table of calculated draw
	$query = 'SELECT A1.debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid ';
	$query .= "FROM temp_draw AS A1, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue ";
	$query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND A1.venue_id=venue.venue_id "; 
	$result=q($query);

	if ($result) {
		echo "<table id=\"manualdraw\">\n";
		echo "<tr><th>Venue Name</th><th>Opening Govt</th><th>Opening Opp</th><th>Closing Govt</th><th>Closing Opp</th><th>Average Points</th><th>Chair</th><th>Panelists</th><th>Trainee</th></tr>\n";
		//Nasty hack to make it display in tab order with average points; fix! - GWJR
		while($row=$result->FetchRow()){
			$row['ogpoints'] = points_for_team($row['ogid'], $numdraws);
			$row['oopoints'] = points_for_team($row['ooid'], $numdraws);
			$row['cgpoints'] = points_for_team($row['cgid'], $numdraws);
			$row['copoints'] = points_for_team($row['coid'], $numdraws);
			$totalpoints = (int)$row['ogpoints'] + (int)$row['oopoints'] + (int)$row['cgpoints'] + (int)$row['copoints'];
			$averagepoints = $totalpoints/4.0;
			$row['averagepoints']=$averagepoints;
			$rowarray[]=$row;
		}

		//Assume it's in tab order (!)
		foreach($rowarray as $row) {
			echo "<tr>\n";
			$highlightlastmodified=((@$lastmodified)&&($row['debate_id']==$lastmodified));

			//$text = ($highlight==1 ? "<td style=\"color:blue\">" : "<td>");
			$text = (($highlightlastmodified) ? "<td style=\"color:green\">" : "<td>");
			echo "$text"."{$row['venue_name']}</td>\n";
			echo "$text"."{$row['ogtc']} {$row['ogt']} <br/> ("."{$row['ogpoints']}".") </td>\n";
			echo "$text"."{$row['ootc']} {$row['oot']} <br/> ("."{$row['oopoints']}".") </td>\n";
			echo "$text"."{$row['cgtc']} {$row['cgt']} <br/> ("."{$row['cgpoints']}".") </td>\n";
			echo "$text"."{$row['cotc']} {$row['cot']} <br/> ("."{$row['copoints']}".") </td>\n";
			echo "$text"."{$row['averagepoints']} </td>\n";                

			//For the purpose of finding conflicts
			$ogid=$row['ogid'];
			$ooid=$row['ooid'];
			$cgid=$row['cgid'];
			$coid=$row['coid'];

			//Find Chief Adjudicator ?chair, surely?
			$query="SELECT A.adjud_name AS adjud_name, A.adjud_id As adjud_id, A.ranking FROM temp_adjud AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='chair' AND T.debate_id=?";
			$resultadjud=qp($query, array($row['debate_id']));

			if ($resultadjud->RecordCount()==0) {
				echo "$text"."<b>NONE</b></td>";
			} else {
				$rowadjud=$resultadjud->FetchRow();
				$adjud_id=$rowadjud['adjud_id'];
				//check for conflict and print name accordingly
				if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
					//Red Color
					$starttag="<span style=\"color:red\">";
					$endtag="</span>";
				} else {
					//Set it to empty
					$starttag="";
					$endtag="";
				}
				echo "$text"."$starttag{$rowadjud['adjud_name']} ({$rowadjud['ranking']})$endtag</td>";
			}

			//Find Panelists
			$query="SELECT A.adjud_name AS adjud_name, A.adjud_id As adjud_id, A.ranking FROM temp_adjud AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND T.status='panelist' AND T.debate_id=? AND NOT A.status = 'trainee'";
			$resultadjud=qp($query, array($row['debate_id']));

			if ($resultadjud->RecordCount()==0) {
				echo "$text"."<b>NONE</b></td>";
			} else {
				echo "$text"."<ul>\n";
				while($rowadjud=$resultadjud->FetchRow()) {
					//check for conflict and print name accordingly
					$adjud_id=$rowadjud['adjud_id'];
					if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
						//Red Color
						$starttag="<span style=\"color:red\">";
						$endtag="</span>";
					} else {
						//Set it to empty
						$starttag="";
						$endtag="";
					}
					echo "<li>$starttag{$rowadjud['adjud_name']} ({$rowadjud['ranking']})$endtag</li>\n";
				}
				echo "</ul></td>\n";
			}

			//Find Trainees
			$query="SELECT A.adjud_name AS adjud_name, A.adjud_id AS adjud_id, A.ranking FROM temp_adjud AS T, adjudicator AS A WHERE A.adjud_id=T.adjud_id AND A.status='trainee' AND T.status ='panelist' AND T.debate_id=?";
			$resultadjud=qp($query, array($row['debate_id']));
			if ($resultadjud->RecordCount()==0){
				echo "$text"."<b>NONE</b></td>";
			} else {
				echo "$text"."<ul>\n";
				while($rowadjud=$resultadjud->FetchRow()) {
					//check for conflict and print name accordingly
					$adjud_id=$rowadjud['adjud_id'];
					if(is_four_id_conflict($adjud_id, $ogid, $ooid, $cgid, $coid)){
						//Red Color
						$starttag="<span style=\"color:red\">";
						$endtag="</span>";
					} else {
						//Set it to empty
						$starttag="";
						$endtag="";
					}
					echo "<li>$starttag{$rowadjud['adjud_name']} ({$rowadjud['ranking']})$endtag</li>\n";
				}
				echo "</ul></td>\n";
			}
			echo "<td class=\"editdel\"><a href=\"draw.php?moduletype=manualdraw&amp;action=edit&amp;debate_id={$row['debate_id']}\">Edit</a></td>";
			echo "</tr>\n";
		}
		echo "</table>\n";
	}
	echo "<a href=\"draw.php?moduletype=manualdraw&amp;action=finalise\" onClick=\"return confirm('The draw cannot be modified once finalised. Do you wish to continue?');\"><h3>Finalise Draw</h3></a>";
    }
}
?>
