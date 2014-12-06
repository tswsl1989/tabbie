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
require_once("ntu_bridge.php");
require_once("includes/backend.php");
require_once("config/settings.php");
require_once("includes/teamstanding.php");

$ntu_controller = "print"; #selected in menu
$moduletype="";
$zipFile = new ZipArchive();

$tmpDir = sys_get_temp_dir();
$zipLoc = tempnam($tmpDir, "tab");

if (!$zipFile->open($zipLoc, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
	echo "<h2>Unable to open temporary zipfile</h2>";
	die();
}

$dest_dir="teamsummaries/";
$zipFile->AddEmptyDir($dest_dir);
$zipFile->addFile("view/index.css", $dest_dir."index.css");
if (file_exists("view/custom.css")) {
	$zipFile->addFile("view/custom.css", $dest_dir."custom.css");
} else {
	$zipFile->addFromString($dest_dir."custom.css", "/** No custom styles **/");
}

$db_all_teams = $DBConn->GetAssoc("SELECT T.*, U.* FROM team as T INNER JOIN university as U ON T.univ_id = U.univ_id");

$completed_rounds = get_num_completed_rounds();
$final_standing = team_standing_array($completed_rounds);
$team_cache=array();
foreach ($db_all_teams as $team_id => $thingy) {
	$round_standing = 1; //Before round 1, defines variable

	$team_name =  team_name_long($team_id);
	$sres = qp("SELECT speaker_name FROM speaker WHERE team_id=? ORDER BY speaker_id ASC", array($team_id));
	$snam = $sres->FetchRow();
	$speakers = $snam['speaker_name'];
	$snam = $sres->FetchRow();
	$speakers .= " and ".$snam['speaker_name'];
	$contents=pageHeader("Team Overview for $team_name ($speakers)");

	$contents.="<h2>Team Overview for team ".$team_name."</h2>";
	$contents.="<h2>Speakers: $speakers</h2>";

	for ($round = 1; $round <= $completed_rounds; $round++) {
		$prev_round_standing = @$round_standing;
		$round_standing = team_standing_array($round);

		$motion = get_motion_for_round($round) ? get_motion_for_round($round) : "(Unknown)";
		$contents.="<h2>Round $round</h2>\n";
		$contents.="<h3>Motion: $motion</h3>\n";

		$db_result = qp("SELECT debate_id AS debate_id, T1.team_id AS ogid, T2.team_id AS ooid, T3.team_id AS cgid, T4.team_id AS coid, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, venue_name, venue_location FROM draws D, team T1, team T2, team T3, team T4, venue WHERE D.round_no=? AND og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND D.venue_id=venue.venue_id AND (og=? OR oo=? OR cg=? OR co=?)", array($round, $team_id, $team_id, $team_id, $team_id));
		if ($db_result->RecordCount() == 0) {
			$contents.="<h3>Team did not participate in this round</h3>";
		} else {
			$row = $db_result->FetchRow();
			$debate_id = $row['debate_id'];
			$contents.="<h3>Venue: {$row['venue_name']} ({$row['venue_location']})</h3>";

			$contents.="<h3>Teams:</h3>";
			$contents.="<table><tr><th>Role</th><th>Team</th><th>Ranking</th><th>Points</th><th>Before Round $round</th><th>After Round $round</th><th>After Round $completed_rounds</th></tr>";

			foreach (array('og' => 'Opening Government', 'oo' => 'Opening Opposition', 'cg' => 'Closing Government', 'co' => 'Closing Opposition') as $short => $long) {
				$id = $row["{$short}id"];
				$name = team_name_long($id);
				$ranking = ranking_for_team_in_round($id, $round);
				$points = points_for_ranking($ranking);
				$ranking = ucwords($ranking);
				if ($round == 1) {
					$before = "1st (0 points, 0 speaks)";
				} else {
					$before = team_standing_for_team($prev_round_standing, $id);
				}
				$after = team_standing_for_team($round_standing, $id);
				$final = team_standing_for_team($final_standing, $id);

				$contents.="<tr><td>$long</td><td><a href=\"teamID$id.html\">$name</a></td><td>$ranking</td><td>$points</td><td>$before</td><td>$after</td><td>$final</td></tr>";
			}
			$contents.="</table>";
			$contents.="<h3>Speakers:</h3>";

			$db_result = qp("SELECT SR.points, S.speaker_name, U.univ_code, T.team_code FROM speaker_results SR, speaker S, team T, university U WHERE SR.round_no=? AND SR.debate_id=? AND SR.speaker_id = S.speaker_id AND S.team_id = T.team_id AND T.univ_id = U.univ_id ORDER BY speaker_name", array($round, $debate_id));

			$contents.="<table><tr><th>Name</th><th>Team</th><th>Points</th></tr>";
			while ($row = $db_result->FetchRow()) {
				$contents.="<tr><td>{$row['speaker_name']}</td><td>{$row['univ_code']} {$row['team_code']}</td><td>{$row['points']}</td></tr>";
			}
			$contents.="</table>";

			$contents.="<h3>Adjudicators:</h3>";

			$db_result = qp("SELECT DA.status, adjudicator.adjud_name, university.univ_name, university.univ_code FROM draw_adjud DA, adjudicator, university WHERE DA.round_no = ? AND DA.debate_id=? AND DA.adjud_id = adjudicator.adjud_id AND adjudicator.univ_id = university.univ_id ORDER BY status", array($round, $debate_id));

			$contents.="<table><tr><th>Role</th><th>Name</th><th>University</th></tr>";
			while ($row = $db_result->FetchRow()) {
				$role = ucwords($row['status']);
				$contents.="<tr><td>$role</td><td>{$row['adjud_name']}</td><td>{$row['univ_name']} ({$row['univ_code']})</td></tr>";
			}
			$contents.="</table></body></html>";
		}
	}
	$zipFile->addFromString($dest_dir."teamID".$team_id.".html", $contents);
}

//
//Now make the Speaker Standing:
//

$roundno = get_num_rounds();

$query = "SELECT speaker.speaker_id, speaker.team_id, speaker_name FROM speaker AS speaker";
$result = q($query);

// Create array with all the team ids
$index=0;
$speaker_array = array();
while ($row=$result->FetchRow()) {
	$speaker_array[$index] = array("index" => $index++,
		"speakerid" => $row['speaker_id'],
		"speakername" => $row['speaker_name'],
		"teamid" => $row['team_id'],
		"teamname" => ' ',
		"points" => 0);
}


// Fill up all the team names
$speaker_array = fillUpTeamNames($speaker_array);

// Run through the array and add the points
foreach($speaker_array as $cc) {
	$index = $cc["index"];
	$speaker_id = $cc["speakerid"];
	$points = 0;
	for ($x=1;$x<=$roundno;$x++) {
		$score_query = "SELECT points FROM speaker_results WHERE round_no=? AND speaker_id=?";
		$score_result = qp($score_query, array($x, $speaker_id));
		$score_row = $score_result->FetchRow();
		$points += $score_row['points'];
		$speaker_array[$index]["round_$x"] = $score_row['points'];
	}
	$speaker_array[$index]["points"] = $points;
}


// Sorting the array
usort($speaker_array, "cmp");

$contents = pageHeader("Speaker Standings");

$contents.="<h2>Speaker Tab For ".$local_name."</h2>";

$contents.="<table>\n";
$contents.="<tr><th>Position</th><th>Speaker Name</th><th>Team Name</th>";
for ($x=1;$x<=$roundno;$x++) {
	$contents.="<th>Round $x</th>";
}
$contents.="<th>Total Points</th></tr>\n";

$prev_points = "something";
$salength = count($speaker_array);
for ($x=0;$x<$salength;$x++) {
	$ranking = ($x+1);
	if ($speaker_array[$x]["points"] != $prev_points) {
		$prev_points = $speaker_array[$x]["points"];
		$display_ranking = ($x+1);
	} else {
		$display_ranking = "-";
	}
	$contents.="<tr>\n";
	$contents.="<td>$display_ranking</td>\n";
	$contents.="<td>".$speaker_array[$x]["speakername"]."</td>\n";
	$contents.="<td>"."<a href=\"teamID{$speaker_array[$x]['team_id']}.html\">".$speaker_array[$x]["teamname"]."</a></td>\n";
	for ($y=1;$y<=$roundno;$y++) {
		$contents.="<td>" . $speaker_array[$x]["round_$y"] . "</td>";
	}
	$contents.="<td>".$speaker_array[$x]["points"]."</td>\n";
	$contents.="</tr>\n";

}
$contents.="</table>\n</body></html>";
$zipFile->addFromString($dest_dir."speakertab.html", $contents);
//And Now the Team Tab
$team_array = team_standing_array($roundno);

$contents = pageHeader("Team Standings for ".$local_name);
$contents.="<h2>Team Tab For ".$local_name."</h2>";
$contents.="<table>\n";
$contents.="<tr><th>Ranking</th><th>Team Name</th>";
for ($y=1;$y<=$roundno;$y++) {
	$contents.="<th>Round $y</th>";
}
$contents.="<th>Total Score</th><th>Speaker Points</th><th>WUDC art. 4.a.iii</th></tr>\n";
$prev_ranking = "something";
foreach ($team_array as $cc) {
	if ($cc['ranking'] != $prev_ranking) {
		$prev_ranking = $cc['ranking'];
		$display_ranking = $cc['ranking'];
	} else {
		$display_ranking = "-";
	}
	$contents.="<tr>\n";
	$contents.="<td>". $display_ranking ."</td>\n";
	$contents.="<td>"."<a href=\"teamID{$cc['team_id']}.html\">".$cc["teamname"]."</a></td>\n";
	for ($y=1;$y<=$roundno;$y++) {
		$contents.="<td>" . $cc["round_$y"] . "</td>";
	}
	$contents.="<td>".$cc["score"]."</td>\n";
	$contents.="<td>".$cc["speaker"]."</td>\n";
	$contents.="<td>".$cc["rankings"]."</td>\n";
	$contents.="</tr>\n";

}
$contents.="</table>\n</body>\n</html>";
$zipFile->addFromString($dest_dir."teamtab.html", $contents);

$contents = pageHeader("Interactive tab for ".$local_name);

$contents.="<h1 style='text-indent: 0px'>Tab Website For $local_name</h1>";
$contents.="<a href=\"teamtab.html\">Team Tab</a><br />";
$contents.="<a href=\"speakertab.html\">Speaker Tab</a>'";

$zipFile->addFromString($dest_dir."index.html", $contents);
$zipFile->setArchiveComment("Tabbie HTML output for ".$local_name);
$zipFile->close();

date_default_timezone_set(@date_default_timezone_get());
header("Content-Type: application/zip");
header('Content-Disposition: attachment; filename="tabexport-'.date("Ymd-His").'.zip"');
header('Content-Description: File Transfer');
readfile($zipLoc);

function cmp ($a, $b) {
	return ($a["points"] > $b["points"]) ? -1 : 1;
}

function getDirectoryList ($directory) {
	$results = array();
	// create a handler for the directory
	$handler = opendir($directory);

	// open directory and walk through the filenames
	while ($file = readdir($handler)) {
		// if file isn't this directory or its parent, add it to the results
		if ($file != "." && $file != "..") {
			$results[] = $file;
		}
	}

	// tidy up: close the handler
	closedir($handler);
	return $results;
}

function pageHeader($t) {
	return <<<EoH
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>$t</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="./index.css" />
		<link rel="stylesheet" href="./custom.css" />
	</head>
	<body>
EoH;
}
function fillUpTeamNames($speaker_array) {
	$result = array();
	foreach ($speaker_array as $speaker) {
		$teamid = $speaker["teamid"];
		$name_query = "SELECT team.team_id, univ.univ_code AS univ_code, team.team_code AS team_code ";
		$name_query .= "FROM university AS univ, team AS team WHERE team.team_id=? AND team.univ_id = univ.univ_id ";
		$name_result = qp($name_query, array($teamid));
		$name_row = $name_result->FetchRow();
		$teamname = $name_row['univ_code'].' '.$name_row['team_code'];
		$speaker["teamname"] = $teamname;
		$speaker["team_id"] = $name_row['team_id'];
		$result[] = $speaker;
	}
	return $result;
}

?>
