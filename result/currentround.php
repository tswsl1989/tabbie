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
require_once("includes/settings.php");

if(array_key_exists("action", @$_GET)) $action=trim(@$_GET['action']); //Check action
$validate=1;


//Check for No of Draws against No. of results entered
if ($nextresult!=$numrounds) {
	$msg[]="Current Round Draw hasn't been created. Cannot input results";
	$validate=0;
} else { 
	if (($action=="doedit")||($action=="process")||($action=="edit")) {
		//Validate debate_id
		$debate_id=@$_GET['debate_id'];

		$query="SELECT * FROM temp_result WHERE debate_id=?";
		$result=qp($query, array($debate_id));
		if ($result->RecordCount()==0) {
			$validate=0;
			$msg[]="Invalid Debate ID. No such debate in database.";
			$action="display";
		} else {
			$speaker1_og_score=$speaker2_og_score=$speaker1_oo_score=$speaker2_oo_score=$speaker1_cg_score=$speaker2_cg_score=$speaker_1_cg_score=$speaker2_cg_score="";
			$query="SELECT S.speaker_id, S.speaker_name ";
			$query.="FROM temp_speaker_result TS, speaker S, draws D  ";
			$query.="WHERE D.round_no=? AND TS.speaker_id=S.speaker_id AND TS.debate_id=? AND D.og=S.team_id "; //Opening Govt
			$query.="ORDER BY S.speaker_name";
			$result=qp($query, array($nextresult, $debate_id));
			//Get first speaker details
			$row=$result->FetchRow();
			$speaker1_og_id=$row['speaker_id'];
			$speaker1_og_name=$row['speaker_name'];
			if(array_key_exists("speaker_$speaker1_og_id",@$_POST)) $speaker1_og_score=@$_POST["speaker_$speaker1_og_id"];
			//Get second speaker
			$row=$result->FetchRow();
			$speaker2_og_id=$row['speaker_id'];
			$speaker2_og_name=$row['speaker_name'];
			if(array_key_exists("speaker_$speaker2_og_id",@$_POST)) $speaker2_og_score=@$_POST["speaker_$speaker2_og_id"];

			$query="SELECT S.speaker_id, S.speaker_name ";
			$query.="FROM temp_speaker_result TS, speaker S, draws D  ";
			$query.="WHERE D.round_no=? AND TS.speaker_id=S.speaker_id AND TS.debate_id=? AND D.oo=S.team_id ";//Opening Opp
			$query.="ORDER BY S.speaker_name";
			$result=qp($query, array($nextresult, $debate_id));
			//Get first speaker details
			$row=$result->FetchRow();
			$speaker1_oo_id=$row['speaker_id'];
			$speaker1_oo_name=$row['speaker_name'];
			if(array_key_exists("speaker_$speaker1_oo_id",@$_POST)) $speaker1_oo_score=@$_POST["speaker_$speaker1_oo_id"];
			//Get second speaker
			$row=$result->FetchRow();
			$speaker2_oo_id=$row['speaker_id'];
			$speaker2_oo_name=$row['speaker_name'];
			if(array_key_exists("speaker_$speaker2_oo_id",@$_POST)) $speaker2_oo_score=@$_POST["speaker_$speaker2_oo_id"];

			$query="SELECT S.speaker_id, S.speaker_name ";
			$query.="FROM temp_speaker_result TS, speaker S, draws D  ";
			$query.="WHERE D.round_no = ? AND TS.speaker_id=S.speaker_id AND TS.debate_id=? AND D.cg=S.team_id "; //Closing Govt
			$query.="ORDER BY S.speaker_name";
			$result=qp($query,array($nextresult, $debate_id));
			//Get first speaker details
			$row=$result->FetchRow();
			$speaker1_cg_id=$row['speaker_id'];
			$speaker1_cg_name=$row['speaker_name'];
			if(array_key_exists("speaker_$speaker1_cg_id",@$_POST)) $speaker1_cg_score=@$_POST["speaker_$speaker1_cg_id"];
			//Get second speaker
			$row=$result->FetchRow();
			$speaker2_cg_id=$row['speaker_id'];
			$speaker2_cg_name=$row['speaker_name'];
			if(array_key_exists("speaker_$speaker2_cg_id",@$_POST)) $speaker2_cg_score=@$_POST["speaker_$speaker2_cg_id"];

			$query="SELECT S.speaker_id, S.speaker_name ";
			$query.="FROM temp_speaker_result TS, speaker S, draws D  ";
			$query.="WHERE D.round_no=? AND TS.speaker_id=S.speaker_id AND TS.debate_id=? AND D.co=S.team_id "; //Closing Opp
			$query.="ORDER BY S.speaker_name";
			$result=qp($query, array($nextresult, $debate_id));
			//Get first speaker details
			$row=$result->FetchRow();
			$speaker1_co_id=$row['speaker_id'];
			$speaker1_co_name=$row['speaker_name'];
			if(array_key_exists("speaker_$speaker1_co_id",@$_POST)) $speaker1_co_score=@$_POST["speaker_$speaker1_co_id"];
			//Get second speaker
			$row=$result->FetchRow();
			$speaker2_co_id=$row['speaker_id'];
			$speaker2_co_name=$row['speaker_name'];
			if(array_key_exists("speaker_$speaker2_co_id",@$_POST)) $speaker2_co_score=@$_POST["speaker_$speaker2_co_id"];

			//Get Team Names & Venue
			$query = "SELECT T.debate_id AS debate_id, V.venue_name AS venue_name, first, second, third, fourth, og, oo, cg, co, T1.team_code AS team_og_code, T1.team_id AS team_og_id, U1.univ_code AS univ_og_code, T2.team_code AS team_oo_code, T2.team_id AS team_oo_id, U2.univ_code AS univ_oo_code, T3.team_code AS team_cg_code, T3.team_id AS team_cg_id, U3.univ_code AS univ_cg_code, T4.team_code AS team_co_code, T4.team_id AS team_co_id, U4.univ_code AS univ_co_code ";
			$query .= "FROM temp_result T, draws D, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4, venue V ";
			$query .= "WHERE D.round_no=? AND T.debate_id = ? AND T.debate_id = D.debate_id AND T1.team_id = D.og AND T2.team_id = D.oo AND T3.team_id = D.cg AND T4.team_id = D.co AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND D.venue_id=V.venue_id "; 

			$result=qp($query, array($nextresult, $debate_id));
			$rowresults=$result->FetchRow();

			$team_og_id= $rowresults['team_og_id'];
			$team_oo_id= $rowresults['team_oo_id'];
			$team_cg_id= $rowresults['team_cg_id'];
			$team_co_id= $rowresults['team_co_id'];
			$team_og_name=$rowresults['univ_og_code'].' '.$rowresults['team_og_code'];
			$team_oo_name=$rowresults['univ_oo_code'].' '.$rowresults['team_oo_code'];
			$team_cg_name=$rowresults['univ_cg_code'].' '.$rowresults['team_cg_code'];
			$team_co_name=$rowresults['univ_co_code'].' '.$rowresults['team_co_code'];
			$venue=$rowresults['venue_name'];
		}
	}

	if ($action=="edit") {
		unset($msg);
		//Load the scores (taken from post above) to the post values)
		//in case that didn't happen
		$speaker1_og_post_score = "";
		$speaker2_og_post_score = "";
		$speaker1_oo_post_score = "";
		$speaker2_oo_post_score = "";
		$speaker1_cg_post_score = "";
		$speaker2_cg_post_score = "";
		$speaker1_co_post_score = "";
		$speaker2_co_post_score = "";

		if(isset($speaker1_og_score)) $speaker1_og_post_score=$speaker1_og_score;
		if(isset($speaker2_og_score)) $speaker2_og_post_score=$speaker2_og_score;
		if(isset($speaker1_oo_score)) $speaker1_oo_post_score=$speaker1_oo_score;
		if(isset($speaker2_oo_score)) $speaker2_oo_post_score=$speaker2_oo_score;
		if(isset($speaker1_cg_score)) $speaker1_cg_post_score=$speaker1_cg_score;
		if(isset($speaker2_cg_score)) $speaker2_cg_post_score=$speaker2_cg_score;
		if(isset($speaker1_co_score)) $speaker1_co_post_score=$speaker1_co_score;
		if(isset($speaker2_co_score)) $speaker2_co_post_score=$speaker2_co_score;

		//Load in values of points from table
		$query="SELECT points FROM temp_speaker_result WHERE speaker_id=?";
		$result=qp($query, array($speaker1_og_id));
		$row=$result->FetchRow();
		$speaker1_og_score=$row['points'];
		if ($speaker1_og_score == 0) {
			$speaker1_og_score=@$speaker1_og_post_score;
		}

		$query="SELECT points FROM temp_speaker_result WHERE speaker_id=?";
		$result=qp($query, array($speaker2_og_id));
		$row=$result->FetchRow();
		$speaker2_og_score=$row['points'];
		if ($speaker2_og_score == 0){
			$speaker2_og_score=@$speaker2_og_post_score;
		}

		$query="SELECT points FROM temp_speaker_result WHERE speaker_id=?";
		$result=qp($query, array($speaker1_oo_id));
		$row=$result->FetchRow();
		$speaker1_oo_score=$row['points'];
		if ($speaker1_oo_score == 0) {
			$speaker1_oo_score=@$speaker1_oo_post_score;
		}

		$query="SELECT points FROM temp_speaker_result WHERE speaker_id=?";
		$result=qp($query, array($speaker2_oo_id));
		$row=$result->FetchRow();
		$speaker2_oo_score=$row['points'];
		if ($speaker2_oo_score == 0){
			$speaker2_oo_score=@$speaker2_oo_post_score;
		}

		$query="SELECT points FROM temp_speaker_result WHERE speaker_id=?";
		$result=qp($query, array($speaker1_cg_id));
		$row=$result->FetchRow();
		$speaker1_cg_score=$row['points'];
		if ($speaker1_cg_score == 0) {
			$speaker1_cg_score=@$speaker1_cg_post_score;
		}

		$query="SELECT points FROM temp_speaker_result WHERE speaker_id=?";
		$result=qp($query, array($speaker2_cg_id));
		$row=$result->FetchRow();
		$speaker2_cg_score=$row['points'];
		if ($speaker2_cg_score == 0) {
			$speaker2_cg_score=@$speaker2_cg_post_score;
		}

		$query="SELECT points FROM temp_speaker_result WHERE speaker_id=?";
		$result=qp($query, array($speaker1_co_id));
		$row=$result->FetchRow();
		$speaker1_co_score=$row['points'];
		if ($speaker1_co_score == 0) {
			$speaker1_co_score=@$speaker1_co_post_score;
		}

		$query="SELECT points FROM temp_speaker_result WHERE speaker_id=?";
		$result=qp($query, array($speaker2_co_id));
		$row=$result->FetchRow();
		$speaker2_co_score=$row['points'];
		if ($speaker2_co_score == 0) {
			$speaker2_co_score=@$speaker2_co_post_score;
		}

		$team_og_score = $speaker1_og_score + $speaker2_og_score;
		$team_oo_score = $speaker1_oo_score + $speaker2_oo_score;
		$team_cg_score = $speaker1_cg_score + $speaker2_cg_score;
		$team_co_score = $speaker1_co_score + $speaker2_co_score;
	}

	if ($action=="doedit") {
		$team_og_score = $speaker1_og_score + $speaker2_og_score;
		$team_oo_score = $speaker1_oo_score + $speaker2_oo_score;
		$team_cg_score = $speaker1_cg_score + $speaker2_cg_score;
		$team_co_score = $speaker1_co_score + $speaker2_co_score;

		$result_score_array = array($team_og_score,$team_oo_score,$team_cg_score,$team_co_score);
		$result_teamname_array = array($team_og_name,$team_oo_name,$team_cg_name,$team_co_name);
		$result_teamid_array = array($team_og_id,$team_oo_id,$team_cg_id,$team_co_id);

		array_multisort ($result_score_array, SORT_DESC,
		$result_teamname_array,
		$result_teamid_array);

		$result_record['debate_id'] = $debate_id;
		$result_record['first'] = $result_teamid_array[0];
		$result_record['second'] = $result_teamid_array[1];
		$result_record['third'] = $result_teamid_array[2];
		$result_record['fourth'] = $result_teamid_array[3];
		$result=$DBConn->Replace("temp_result", $result_record, "debate_id", true);
		if (!$result) { //Get Error Message
			$msg[]="Error creating results table.".$DBConn->ErrorMsg();
			$action="display";
		}

		$temp_speaker_points_array = array($speaker1_og_score,$speaker2_og_score,$speaker1_oo_score,$speaker2_oo_score,$speaker1_cg_score,$speaker2_cg_score,$speaker1_co_score,$speaker2_co_score);
		$temp_speaker_id_array = array($speaker1_og_id,$speaker2_og_id,$speaker1_oo_id,$speaker2_oo_id,$speaker1_cg_id,$speaker2_cg_id,$speaker1_co_id,$speaker2_co_id);

		for ($i=0;$i<8;$i++) {
			$query ="UPDATE temp_speaker_result SET points = ? WHERE debate_id = ? AND speaker_id = ?";
			$result=qp($query, array($temp_speaker_points_array[$i], $debate_id, $temp_speaker_id_array[$i]));
			if (!$result) { //Get Error Message
				$msg[]="Error creating results table.".$DBConn->ErrorMsg();
				$action="display";
			}
		}
	}

	if ($action=="process") {
		$team_og_score = $speaker1_og_score + $speaker2_og_score;
		$team_oo_score = $speaker1_oo_score + $speaker2_oo_score;
		$team_cg_score = $speaker1_cg_score + $speaker2_cg_score;
		$team_co_score = $speaker1_co_score + $speaker2_co_score;

		$score_min = 1;
		$score_max = 100;
		if (
			($speaker1_og_score < $score_min) OR ($speaker1_og_score > $score_max) OR
			($speaker1_oo_score < $score_min) OR ($speaker1_oo_score > $score_max) OR
			($speaker1_cg_score < $score_min) OR ($speaker1_cg_score > $score_max) OR
			($speaker1_co_score < $score_min) OR ($speaker1_co_score > $score_max) OR
			($speaker2_og_score < $score_min) OR ($speaker2_og_score > $score_max) OR
			($speaker2_oo_score < $score_min) OR ($speaker2_oo_score > $score_max) OR
			($speaker2_cg_score < $score_min) OR ($speaker2_cg_score > $score_max) OR
			($speaker2_co_score < $score_min) OR ($speaker2_co_score > $score_max)  
		) {
			$validate = 0;
			$msg[]="Invalid score entered for one or more debaters.";
		}

		if (
			($team_og_score == $team_oo_score) OR ($team_og_score == $team_cg_score) OR
			($team_og_score == $team_co_score) OR ($team_oo_score == $team_cg_score) OR 
			($team_oo_score == $team_co_score) OR ($team_cg_score == $team_co_score)
		) {
			$validate = 0;
			$msg[]="Two or more teams with same total scores";
		}

		if ($validate==1) {
			//Process the results and ask for confirmation

			$result_score_array = array($team_og_score,$team_oo_score,$team_cg_score,$team_co_score);
			$result_teamname_array = array($team_og_name,$team_oo_name,$team_cg_name,$team_co_name);
			$result_teamid_array = array($team_og_id,$team_oo_id,$team_cg_id,$team_co_id);

			$speaker1_og_post_score = @$_POST["speaker_$speaker1_og_id"];
			$speaker2_og_post_score = @$_POST["speaker_$speaker2_og_id"];
			$speaker1_oo_post_score = @$_POST["speaker_$speaker1_oo_id"];
			$speaker2_oo_post_score = @$_POST["speaker_$speaker2_oo_id"];
			$speaker1_cg_post_score = @$_POST["speaker_$speaker1_cg_id"];
			$speaker2_cg_post_score = @$_POST["speaker_$speaker2_cg_id"];
			$speaker1_co_post_score = @$_POST["speaker_$speaker1_co_id"];
			$speaker2_co_post_score = @$_POST["speaker_$speaker2_co_id"];

		} else {
			$action="edit"; //Go back to edit mode
		}
		}

	if ($action=="create") {
		unset($msg);
		//Create temporary table
		$query ="CREATE TABLE `temp_result` ( ";
		$query.="`round_no` mediumint(9) NOT NULL default ?,";
		$query.="`debate_id` mediumint(9) NOT NULL default '0',";
		$query.="`first` mediumint(9) NOT NULL default '0',";
		$query.="`second` mediumint(9) NOT NULL default '0',";
		$query.="`third` mediumint(9) NOT NULL default '0',";
		$query.="`fourth` mediumint(9) NOT NULL default '0',";
		$query.="PRIMARY KEY  (`debate_id`))";

		$result=qp($query, array($nextresult));

		if (!$result) { //Get Error Message
			$msg[]="Error creating results table.".$DBConn->ErrorMsg();
			$action="display";
		} else {
			//Get debate IDs of present round
			$query="SELECT debate_id FROM draws WHERE round_no = ?";
			$resultdebates=qp($query, array($nextresult));
			while($rowdebates=$resultdebates->FetchRow()) {
				$result_debate_id=$rowdebates['debate_id'];
				//Add a corresponding entry into created table
				$query="INSERT INTO temp_result (debate_id) VALUES(?)";
				$result=qp($query, array($result_debate_id));
			}

			//create speaker table
			$query ="CREATE TABLE IF NOT EXISTS `temp_speaker_result` ( ";
			$query.="`round_no` mediumint(9) NOT NULL default ?,";
			$query.="`speaker_id` mediumint(9) NOT NULL default '0',"; 
			$query.="`debate_id` mediumint(9) NOT NULL default '0',";
			$query.="`points` smallint(9) NOT NULL default '0', ";
			$query.="PRIMARY KEY (`speaker_id`))";

			$result=qp($query, array($nextresult));

			if(!$result) {
				$msg[]="Error creating speaker points table.".$DBConn->ErrorMsg();
				$action="display";
			} else {
				$query = "SELECT speaker_id, debate_id ";
				$query .= "FROM speaker S, draws D ";
				$query .= "WHERE D.round_no = ? AND (S.team_id = D.og OR S.team_id = D.oo OR S.team_id = D.cg OR S.team_id = D.co) ";
				$speakerresult=qp($query, array($nextresult));

				while($rowspeaker=$speakerresult->FetchRow()) {
					$result_speaker_id=$rowspeaker['speaker_id'];
					$result_debate_id=$rowspeaker['debate_id'];

					$query="INSERT INTO temp_speaker_result(speaker_id, debate_id, round_no) VALUES(?, ?, ?)";
					$result=qp($query, array($result_speaker_id, $result_debate_id, $nextresult));
					if (get_setting("eballots_enabled")) {
						$query="INSERT INTO eballot_scores (round_no, debate_id, speaker_id) VALUES (?, ?, ?)";
						$result=qp($query, array($nextresult, $result_debate_id, $result_speaker_id));
					}
				}
			}
		}
		$result="display";
	}

	if ($action=="finalize") {
		//Finalize the results after some checks

		$query = "SELECT COUNT(*) FROM  temp_speaker_result WHERE points = 0 and round_no = ? ";
		$result=qp($query, array($nextround));
		$row=$result->FetchRow();

		foreach ($row as $aa) {
			if ($aa > 0) {
				$validate = 0;
				$msg[]="1 or more speakers without any score entered. ";
				$query="SELECT DISTINCT venue.venue_name AS venue FROM venue INNER JOIN draws ON venue.venue_id = draws.venue_id INNER JOIN temp_speaker_result ON draws.debate_id = temp_speaker_result.debate_id WHERE temp_speaker_result.points = 0 AND draws.round_no=?";
				$result=qp($query, array($nextresult));
				if (!$result) {
					print $DBConn->ErrorMsg();
				}
				while ($row=$result->FetchMsg()){
					$msg[]="Re-enter ballot for ".$row['venue'];
				}
			}
		}

		if ($validate == 1) {
			//Create and add values to results table

			if (!$result) { //Get Error Message
				$msg[]="Error creating results table.".$DBConn->ErrorMsg();
				$action="display";
			} else {
				$query="INSERT INTO results SELECT * FROM temp_result";
				$result=q($query);
				if (!$result) {
					print $DBConn->ErrorMsg();
				}

				if(!$result) {
					$msg[]="Error creating speaker points table ".$DBConn->ErrorMsg();
					$action="display";
				} else {
					$query="INSERT INTO speaker_results SELECT * FROM temp_speaker_result";
					$result=q($query);
					if (!$result) {
						print $DBConn->ErrorMsg();
					}
					$query="DROP TABLE temp_result";
					q($query);
					$query="DROP TABLE temp_speaker_result";
					q($query);

					//Redirect
					require_once("includes/http.php");
					redirect("result.php?moduletype=round&action=showresult&roundno=$nextresult");
				}
				$result="display";
			}
		} else {
			$action="display"; //Go back to display mode
		}
	}
}

switch ($action) {
case "edit":
	$title="Results : Round $numrounds : Edit";
	break;
        
case "process":
	$title="Results : Round $numrounds : Summary";
	break;
case "display":
default:
	$action="display";    
	$title="Results : Round $numrounds : Display";
}

//HERE STARTS THE DISPLAY LOGIC
echo "<h2>$title</h2>\n"; //title

if(isset($msg)) displayMessagesUL(@$msg);

if ($nextresult==$numrounds) {
	if ($action=="display") {
		//try extracting results from the temporary result table
		$query = "SELECT T.debate_id AS debate_id, V.venue_name AS venue_name, first, second, third, fourth, og, oo, cg, co, T1.team_code AS team_og_code, U1.univ_code AS univ_og_code, T2.team_code AS team_oo_code, U2.univ_code AS univ_oo_code, T3.team_code AS team_cg_code, U3.univ_code AS univ_cg_code, T4.team_code AS team_co_code, U4.univ_code AS univ_co_code, A.adjud_name AS chair_name ";
		$query .= "FROM temp_result T, draws D, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4, venue V, adjudicator A, draw_adjud J ";
		$query .= "WHERE D.round_no = ? AND J.round_no = ? AND T.debate_id = D.debate_id AND T1.team_id = D.og AND T2.team_id = D.oo AND T3.team_id = D.cg AND T4.team_id = D.co AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND D.venue_id=V.venue_id AND A.adjud_id = J.adjud_id AND J.debate_id = D.debate_id AND J.status='chair' ";
		$query .= "ORDER BY venue_name";

		try {
			$resultresult=qp($query, array($nextresult, $nextresult));
		} catch (Exception $e) {
			$resultresult = false;
		}

		if($resultresult) {
			//Display result in table
			echo"<table>\n";
			echo "<tr><th>Edit</th><th>Venue</th><th>Opening Govt.</th><th>Opening Opp.</th><th>Closing Govt.</th><th>Closing Opp.</th><th>Chair Judge</th><th>eBallot entered?</th></tr>\n";
			while($rowresults=$resultresult->FetchRow()) {
				$eballrs = qp("SELECT MAX(speaker_score) as MSS FROM eballot_scores WHERE round_no=? AND debate_id=?", array($nextresult, $rowresults["debate_id"]));
				$eball = $eballrs->FetchRow();
				$results_debate_id=$rowresults['debate_id'];
				
				$first=$rowresults['first'];
				$second=$rowresults['second'];
				$third=$rowresults['third'];
				$fourth=$rowresults['fourth'];

				$og=$rowresults['og'];
				$oo=$rowresults['oo'];
				$cg=$rowresults['cg'];
				$co=$rowresults['co'];

				$team_og_name=$rowresults['univ_og_code'].' '.$rowresults['team_og_code'];
				$team_oo_name=$rowresults['univ_oo_code'].' '.$rowresults['team_oo_code'];
				$team_cg_name=$rowresults['univ_cg_code'].' '.$rowresults['team_cg_code'];
				$team_co_name=$rowresults['univ_co_code'].' '.$rowresults['team_co_code'];

				$venue=$rowresults['venue_name'];
				$chair=$rowresults['chair_name'];

				$og_pos=returnPositionString(returnposition($first,$second,$third,$fourth,$og));
				$oo_pos=returnPositionString(returnposition($first,$second,$third,$fourth,$oo));
				$cg_pos=returnPositionString(returnposition($first,$second,$third,$fourth,$cg));
				$co_pos=returnPositionString(returnposition($first,$second,$third,$fourth,$co));

				$rowclass = "res_r";
				if ($eball && $eball["MSS"] > 0) {
					$ebcell="<td>Y</td>\n";
					$rowclass = "res_b";
				} else {
					$ebcell="<td>N</td>\n";
				}
				$rowclass=($og_pos=="<b>none</b>" && $oo_pos=="<b>none</b>" && $cg_pos=="<b>none</b>" && $co_pos=="<b>none</b>") ? $rowclass : "res_g";

				echo "<tr class='$rowclass'>\n";
				echo "<td class=\"editdel\"><a href=\"result.php?moduletype=currentround&amp;action=edit&amp;debate_id=$results_debate_id\">Edit</a></td>";
				echo "<td>$venue</td>\n";
				echo "<td>$team_og_name (".$og_pos.")</td>\n";
				echo "<td>$team_oo_name (".$oo_pos.")</td>\n";
				echo "<td>$team_cg_name (".$cg_pos.")</td>\n";
				echo "<td>$team_co_name (".$co_pos.")</td>\n";
				echo "<td>$chair</td>";
				echo $ebcell;
				echo"</tr>\n";
			}
			echo "</table>";
			echo "<a href=\"result.php?moduletype=currentround&amp;action=finalize\" onClick=\"return confirm('The results cannot be modified once finalised. Do you wish to continue?');\"><h3>Finalise Results</h3></a>";
			echo "<p class=\"msg\">* The numbers in the bracket indicate the standing</p>";
		} else {
			//Display message to create table and start inputting results.
			echo "<h3><a href=\"result.php?moduletype=currentround&amp;action=create\">Start Inputting Results</a></h3>";
		}
	}

	if($action=="edit") {
	//LOGIC FOR DISPLAYING THE EDIT FORM.
?>
	<div id="debatedetails">
		<h3>Debate Details</h3>
		<p>Venue : <?=  $venue ?></p>
	</div>
	<form name="resultsinputform" action="result.php?moduletype=currentround&amp;action=process&amp;debate_id=<?= $debate_id?>" method="POST">
<?php
	$tag="";
	if (((@$team_og_score == @$team_oo_score) OR (@$team_og_score == @$team_cg_score) OR (@$team_og_score == @$team_co_score)) AND $validate==0)
		$tag="class=\"error\"";
?>
	<div id="og" <?= $tag;?>>
		<h3>OG: <?=  $team_og_name ?> </h3>
		<label for="<?= "speaker_$speaker1_og_id";?>"><?= $speaker1_og_name;?></label>
		<input type="text" id="<?= "speaker_$speaker1_og_id";?>" name="<?= "speaker_$speaker1_og_id";?>" value="<?= $speaker1_og_score;?>"/><br/><br/>
		<label for="<?= "speaker_$speaker2_og_id";?>"><?= $speaker2_og_name;?></label>
		<input type="text" id="<?= "speaker_$speaker2_og_id";?>" name="<?= "speaker_$speaker2_og_id";?>" value="<?= $speaker2_og_score;?>"/><br/><br/>
	</div>
<?php
	$tag="";
	if (((@$team_oo_score == @$team_og_score) OR (@$team_oo_score == @$team_cg_score) OR (@$team_oo_score == @$team_co_score)) AND $validate==0)
	  $tag="class=\"error\"";
?>
		
	<div id="oo" <?= $tag;?>>
		<h3>OO: <?=  $team_oo_name ?> </h3> 

		<label for="<?= "speaker_$speaker1_oo_id";?>"><?= $speaker1_oo_name;?></label>
		<input type="text" id="<?= "speaker_$speaker1_oo_id";?>" name="<?= "speaker_$speaker1_oo_id";?>" value="<?= $speaker1_oo_score;?>"/><br/><br/>
		<label for="<?= "speaker_$speaker2_oo_id";?>"><?= $speaker2_oo_name;?></label>
		<input type="text" id="<?= "speaker_$speaker2_oo_id";?>" name="<?= "speaker_$speaker2_oo_id";?>" value="<?= $speaker2_oo_score;?>"/><br/><br/>
	</div>

<?php
	$tag="";

	if (((@$team_cg_score == @$team_og_score) OR (@$team_cg_score == @$team_oo_score) OR (@$team_cg_score == @$team_co_score)) AND $validate==0)
		$tag="class=\"error\"";
?>


	<div id="cg" <?= $tag;?>>
		<h3>CG: <?=  $team_cg_name ?> </h3> 
		<label for="<?= "speaker_$speaker1_cg_id";?>"><?= $speaker1_cg_name;?></label>
		<input type="text" id="<?= "speaker_$speaker1_cg_id";?>" name="<?= "speaker_$speaker1_cg_id";?>" value="<?= $speaker1_cg_score;?>"/><br/><br/>
		<label for="<?= "speaker_$speaker2_cg_id";?>"><?= $speaker2_cg_name;?></label>
		<input type="text" id="<?= "speaker_$speaker2_cg_id";?>" name="<?= "speaker_$speaker2_cg_id";?>" value="<?= $speaker2_cg_score;?>"/><br/><br/>
	</div>

<?php
	$tag="";
	if (((@$team_co_score == @$team_og_score) OR (@$team_co_score == @$team_oo_score) OR (@$team_co_score == @$team_cg_score)) AND $validate==0)
		$tag="class=\"error\"";
?>

	<div id="co" <?= $tag;?>>
		<h3>CO: <?=  $team_co_name ?> </h3> 
		<label for="<?= "speaker_$speaker1_co_id";?>"><?= $speaker1_co_name;?></label>
		<input type="text" id="<?= "speaker_$speaker1_co_id";?>" name="<?= "speaker_$speaker1_co_id";?>" value="<?= $speaker1_co_score;?>"/><br/><br/>
		<label for="<?= "speaker_$speaker2_co_id";?>"><?= $speaker2_co_name;?></label>
		<input type="text" id="<?= "speaker_$speaker2_co_id";?>" name="<?= "speaker_$speaker2_co_id";?>" value="<?= $speaker2_co_score;?>"/><br/><br/>
	</div>
	<div class="submit">
		<input type="submit" name="Submit" value="Submit">
		<input type="button" value="Cancel" onClick="location.replace('result.php?moduletype=currentround')"/>
	</div>

	</form>

<?php
	}

	if($action=="process") {
		echo "<h3>Debate Details</h3>\n";
		echo "<p>Venue : $venue</p>\n";
		$lower_limit = get_setting('highlight_lowerlimit');
		$upper_limit = get_setting('highlight_upperlimit');

	//sort scores to find position
	$scores_arr['og']=$team_og_score;
	$scores_arr['oo']=$team_oo_score;
	$scores_arr['cg']=$team_cg_score;
	$scores_arr['co']=$team_co_score;
	arsort($scores_arr);


	echo "<div id=\"og\">\n";
	echo "<h2 class=\"pos\">".returnPositionString(array_search('og',array_keys($scores_arr))+1)."</h2>\n";
	echo "<h3>OG: $team_og_name</h3>\n";
	echo "<h3>($team_og_score)</h3>\n";
	if (($speaker1_og_score < $lower_limit) OR ($speaker1_og_score > $upper_limit)) {
		echo "<p class=\"outrange\">$speaker1_og_name ($speaker1_og_score)</p>\n";
	} else {
		echo "<p>$speaker1_og_name ($speaker1_og_score)</p>\n";
	}

	if (($speaker2_og_score < $lower_limit) OR ($speaker2_og_score > $upper_limit)) {
		echo "<p class=\"outrange\">$speaker2_og_name ($speaker2_og_score)</p>\n";
	} else {
		echo "<p>$speaker2_og_name ($speaker2_og_score)</p>\n";
	}
	echo "</div>\n";

	echo "<div id=\"oo\">\n";      
	echo "<h2 class=\"pos\">".returnPositionString(array_search('oo',array_keys($scores_arr))+1)."</h2>\n";
	echo "<h3>OO: $team_oo_name</h3>\n";
	echo "<h3>($team_oo_score)</h3>\n";
	if (($speaker1_oo_score < $lower_limit) OR ($speaker1_oo_score > $upper_limit)) {
		echo "<p class=\"outrange\">$speaker1_oo_name ($speaker1_oo_score)</p>\n";
	} else { 
		echo "<p>$speaker1_oo_name ($speaker1_oo_score)</p>";
	}
	if (($speaker2_oo_score < $lower_limit) OR ($speaker2_oo_score > $upper_limit)) {
		echo "<p class=\"outrange\">$speaker2_oo_name ($speaker2_oo_score)</p>\n";
	} else {
		echo "<p>$speaker2_oo_name ($speaker2_oo_score)</p>\n";
	}

	echo "</div>\n";

	echo "<div id=\"cg\">\n";
	echo "<h2 class=\"pos\">".returnPositionString(array_search('cg',array_keys($scores_arr))+1)."</h2>\n";
	echo "<h3>CG: $team_cg_name</h3>\n";
	echo "<h3> ($team_cg_score)</h3>";
	if (($speaker1_cg_score < $lower_limit) OR ($speaker1_cg_score > $upper_limit)) {
		echo "<p class=\"outrange\">$speaker1_cg_name ($speaker1_cg_score)</p>\n";
	} else {
		echo "<p>$speaker1_cg_name ($speaker1_cg_score)</p>\n";
	}
	if (($speaker2_cg_score < $lower_limit) OR ($speaker2_cg_score > $upper_limit)) {
		echo "<p class=\"outrange\">$speaker2_cg_name ($speaker2_cg_score)</p>\n";
	} else {
		echo "<p>$speaker2_cg_name ($speaker2_cg_score)</p>\n";
	}
	echo "</div>\n";

	echo "<div id=\"co\">\n";
	echo "<h2 class=\"pos\">".returnPositionString(array_search('co',array_keys($scores_arr))+1)."</h2>\n";
	echo "<h3>CO: $team_co_name</h3>\n";
	echo "<h3>($team_co_score)</h3>";
	if (($speaker1_co_score < $lower_limit) OR ($speaker1_co_score > $upper_limit)) {
		echo "<p class=\"outrange\">$speaker1_co_name ($speaker1_co_score)</p>\n";
	} else {
		echo "<p>$speaker1_co_name ($speaker1_co_score)</p>\n";
	}
	if (($speaker2_co_score < $lower_limit) OR ($speaker2_co_score > $upper_limit)) {
		echo "<p class=\"outrange\">$speaker2_co_name ($speaker2_co_score)</p>\n";
	} else {
		echo "<p>$speaker2_co_name ($speaker2_co_score)</p>\n";
	}

	echo "</div>\n";
	echo "<div class=\"submit\">\n";
	echo "<p class=\"msg\">* Numbers in red are supposed to indicate possible out of range values</p>\n";

	array_multisort ($result_score_array, SORT_DESC,
		$result_teamname_array,
		$result_teamid_array);
?>
	<form name="resultsconfirmform" action="result.php?moduletype=currentround&amp;action=doedit&amp;debate_id=<?= $debate_id?>" method="POST">
		<input type="hidden" id="<?= "speaker_$speaker1_og_id";?>" name="<?= "speaker_$speaker1_og_id";?>" value="<?= $speaker1_og_score;?>"/>
		<input type="hidden" id="<?= "speaker_$speaker2_og_id";?>" name="<?= "speaker_$speaker2_og_id";?>" value="<?= $speaker2_og_score;?>"/>
		
		<input type="hidden" id="<?= "speaker_$speaker1_oo_id";?>" name="<?= "speaker_$speaker1_oo_id";?>" value="<?= $speaker1_oo_score;?>"/>
		<input type="hidden" id="<?= "speaker_$speaker2_oo_id";?>" name="<?= "speaker_$speaker2_oo_id";?>" value="<?= $speaker2_oo_score;?>"/>
		
		<input type="hidden" id="<?= "speaker_$speaker1_cg_id";?>" name="<?= "speaker_$speaker1_cg_id";?>" value="<?= $speaker1_cg_score;?>"/>
		<input type="hidden" id="<?= "speaker_$speaker2_cg_id";?>" name="<?= "speaker_$speaker2_cg_id";?>" value="<?= $speaker2_cg_score;?>"/>
		
		<input type="hidden" id="<?= "speaker_$speaker1_co_id";?>" name="<?= "speaker_$speaker1_co_id";?>" value="<?= $speaker1_co_score;?>"/>
		<input type="hidden" id="<?= "speaker_$speaker2_co_id";?>" name="<?= "speaker_$speaker2_co_id";?>" value="<?= $speaker2_co_score;?>"/>
		
		<input type="submit" name="Confirm" value="Confirm">
		<input type="button" name="Back" onClick="history.go(-1)" value="Back">
	</form>
	</div>
<?php
	}
}
?>
