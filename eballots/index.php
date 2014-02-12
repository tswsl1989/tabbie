<?php
set_include_path(".." . PATH_SEPARATOR . get_include_path());
require_once("../includes/backend.php");


$title = "eBallots - ".$local_name;
page_header($title);


if (get_setting("eballots_enabled") != 1) {
	echo "<div class=\"alert alert-danger\">eBallots are currently disabled</div>";
	page_footer();
	return;
} else if (has_temp_result()==false) {
	echo "<div class=\"alert alert-danger\">
		<p>Temporary tables for this round have not yet been created.<br />
		Send paper ballot back if completed or try again shortly</p>
		</div>";
	page_footer();
	return;
}

if (!isset($_POST['stage'])||!is_numeric($_POST['stage'])) {
	if (isset($_GET['stage']) && $_GET['stage'] = 2) {
		$stage = 2;
		$_POST['debate'] = $_GET['debate'];
		$_POST['ballot_code'] = $_GET['ballot_code'];
	} else {
		$stage = 0;
	}
} else {
	$stage = $_POST['stage'];
}

$positions = array("og","oo","cg","co");
$pnames = array("og" => "Opening Government",
	"oo" => "Opening Opposition",
	"cg" => "Closing Government",
	"co" => "Closing Opposition");
$hl = array(
	"og"  => "",
	"og1" => "",
	"og2" => "",
	"oo" => "",
	"oo1" => "",
	"oo2" => "",
	"cg" => "",
	"cg1" => "",
	"cg2" => "",
	"co" => "",
	"co1" => "",
	"co2" => "");

$validated = 1;
switch($stage) {
case 4:
	//We want the same validation as before, even if the UI doesn't allow changes to be made
case 3:
	// For validation failures. set class and flags
	$hlupper = get_setting("highlight_upperlimit");
	$hllower = get_setting("highlight_lowerlimit");
	foreach ($positions as $pos) {
		if (!isset($_POST[$pos."1"]) || !is_numeric($_POST[$pos."1"])
		       	|| (int)$_POST[$pos."1"] < 0 || (int)$_POST[$pos."1"] > 100) {
			$msg[] = "Invalid score for speaker 1 in ".$pnames[$pos];
			$validated = 0;
			$hl[$pos."1"] = "text-danger";
		} elseif ($_POST[$pos."1"] > $hlupper || $_POST[$pos."1"] < $hllower) {
			$msg[] = "Check score for speaker 1 in ".$pnames[$pos];
			$hl[$pos."1"] = "text-info";
			$hl[$pos] = "bg-info";
		}

		if (!isset($_POST[$pos."2"]) || !is_numeric($_POST[$pos."2"])
		       	|| (int)$_POST[$pos."2"] < 0 || (int)$_POST[$pos."2"] > 100) {
			$msg[] = "Invalid score for speaker 2 in ".$pnames[$pos];
			$hl[$pos."2"] = "text-danger";
			$validated = 0;
		} elseif ($_POST[$pos."2"] > $hlupper || $_POST[$pos."2"] < $hllower) {
			$msg[] = "Check score for speaker 2 in ".$pnames[$pos];
			$hl[$pos."2"] = "text-info";
			$hl[$pos] = "bg-info";
		}
		$tscore[$pos] = (int)$_POST[$pos."1"] + (int)$_POST[$pos."2"];
	}
	arsort($tscore);
	$tpos = array();
	for ($i=1;$i<=4;$i++) {
		$r = each($tscore);
		$tpos[$r[0]] = $i;
	}
	if (count(array_unique($tscore)) != 4) {
		$msg[] = "One or more teams on equal scores";
		$js = array_diff_key($tscore, array_unique($tscore));
		$tk = array_keys($tscore, array_values($js)[0]);
		foreach ($tk as $pos) {
			$hl[$pos] = "bg-danger text-danger";
		}
		$validated = 0;
	}

case 2:
	if (!is_numeric($_POST['debate']) || !is_numeric("0x".$_POST['ballot_code'])) {
		$msg[] = "Input parameters are invalid";
		$stage = 0;
		break;
	}

	$rs = qp("SELECT round_no, debate_id, auth_code FROM eballot_rooms WHERE debate_id = ?", array($_POST['debate']));
	if (!$rs || $rs->RecordCount() != 1) {
		$msg[] = "Unable to retrieve debate details from database";
		$msg[] = "Submit paper ballot to the tab room";
		$stage = 0;
		break;
	}
	$er = $rs->FetchRow();
	if ($_POST['ballot_code'] != $er['auth_code']) {
		$msg[] = "Ballot Code invalid";
		$stage = 0;
		break;
	}
	$debate = $er['debate_id'];
	$ac =$er['auth_code'];
	break;
}

if (isset($msg)) {
	display_errors($msg);
}

switch($stage) {
case 4:
	store_to_db($debate);
	login_page();
	break;
case 3:
	score_check_page($debate, $ac, $tpos, $tscore, $hl, $validated);
	break;
case 2:
	score_input_page($debate, $ac);
	break;
case 1:
case 0:
	login_page();
	break;
}
page_footer();

//Functions below here

function display_errors($m) {
	echo "<div class=\"alert alert-danger\" id=\"warnings\">\n<ul>\n";
	foreach ($m as $message) {
		echo "<li>".$message."</li>";
	}
	echo "</ul>\n</div>";
	return;
}

function login_page() {
	$rs = qp("SELECT v.venue_name, d.debate_id FROM draws as d INNER JOIN venue as v ON v.venue_id=d.venue_id WHERE d.debate_id IN (SELECT debate_id FROM eballot_rooms WHERE round_no=?)", array(get_num_rounds()));
	$rooms = $rs->GetMenu("debate", "", true, false, "", "class=\"form-control\" required autofocus");
?>
    <form action="." method="POST" class="form-horizontal" role="form">
      <div class="form-group">
        <label for="debate">Room:</label>
        <?= $rooms ?>
      </div>
      <div class="form-group">
        <label for="ballot_code">Ballot Code:</label>
        <input type="text" name="ballot_code" id="ballot_code" class="form-control" placeholder="abcdef12" required>
      </div>
      <div class="form-group">
        <input type="submit" class="form-control btn-primary" value="Start" />
	<input type="hidden" name="stage" value="2" />
      </div>
    </form>

<?php
	return;
}

function score_input_page($debate, $ac) {
	global $positions;
	$round = get_num_rounds();
	$vname = get_room_name_from_debate($debate, $round);
	echo "<h3>Round ".$round." - ".$vname."</h3>";
	$rs = qp("SELECT og, oo, cg, co FROM draws WHERE debate_id = ?", array($debate));
	$team_ids = $rs->FetchRow();
	$rs = qp("SELECT SUM(speaker_score) as total FROM eballot_scores WHERE debate_id = ?", array($debate));
	$currscore = $rs->FetchRow();
        $currscore = $currscore['total'];
	if ($currscore > 0) {
		echo "<div class=\"alert alert-warning\"><p>An eBallot for this debate has already been submitted<br />Resubmitting will overwrite the saved scores</p></div>";
	}

	echo "<form action=\".\" method=\"POST\" class=\"form-horizontal\" role=\"form\">\n";
	echo "<input type=\"hidden\" name=\"stage\" value=\"3\">\n";
	echo "<input type=\"hidden\" name=\"ballot_code\" value=\"$ac\">\n";
	echo "<input type=\"hidden\" name=\"debate\" value=\"$debate\">\n";
	$tab=1;
	foreach ($positions as $pos) {
		if ($pos == "og" || $pos == "cg") {
			echo "<div class=\"row\">\n";
		}
		$speakers = get_speaker_names($team_ids[$pos]);
		$team_name = team_name($team_ids[$pos]);
		echo "<div class=\"col-xs-12 col-md-6\">\n";
		echo "\t<h4>".$team_name."</h4>";
	        echo "\t<div class=\"row\">\n";
		echo "\t\t<label class=\"col-xs-9\" for=\"".$pos."1\">".$speakers[0]."</label>\n";
		echo "\t\t<input class=\"col-xs-3\" min=\"50\" max=\"90\" tabindex=\"".$tab++."\" maxlength=\"2\" id=\"".$pos."1\" name=\"".$pos."1\" type=\"number\" required>\n";
		echo "\t</div>\n";
	        echo "\t<div class=\"row\">\n";
		echo "\t\t<label class=\"col-xs-9\" for=\"".$pos."2\">".$speakers[1]."</label>\n";
		echo "\t\t<input class=\"col-xs-3\" min=\"50\" max=\"90\" tabindex=\"".$tab++."\" maxlength=\"2\" id=\"".$pos."2\" name=\"".$pos."2\" type=\"number\" required>\n";
		echo "\t</div>\n";
		echo "</div>\n";
		echo "<div class=\"clearfix visible-xs visible-sm\"></div>\n";
		if ($pos=="oo" || $pos == "co") {
			echo "</div>";
		}
	}
	echo "<hr />";
	echo "<div class=\"row\"><label for=\"note\" class=\"col-xs-12 col-md-4\">Additional Information</label>\n";
	echo "<input type=\"text\" name=\"note\" id=\"note\" tabindex=\"".$tab++."\" placeholder=\"Any extra information for the tab room?\" class=\"col-xs-12 col-md-8\">\n";
	echo "</div>\n";
	echo "<hr />";
	echo "<div class=\"row\">";
	echo "<input type=\"submit\" value=\"Submit Score\" class=\"col-xs-5 col-md-2 btn btn-success\">";
	echo "<input type=\"button\" class=\"col-xs-5 col-xs-offset-2 col-md-2 col-md-offset-8 btn btn-default\" value=\"Back\" onClick=\"javascript:history.go(-1)\">";
	echo "</div></form>";
	return;
}

function score_check_page($debate, $ac, $tpos, $tscore, $hl, $validated) {
	global $positions;
	$round = get_num_rounds();
	$vname = get_room_name_from_debate($debate, $round);
	echo "<h3>Round ".$round." - ".$vname."</h3>";

	$rs= qp("SELECT og, oo, cg, co FROM draws WHERE debate_id = ?", array($debate));
	$team_ids = $rs->FetchRow();
	
	echo "<form action=\".\" method=\"POST\" class=\"form-horizontal\" role=\"form\">\n";
	echo "<input type=\"hidden\" name=\"stage\" value=\"4\">\n";
	echo "<input type=\"hidden\" name=\"ballot_code\" value=\"$ac\">\n";
	echo "<input type=\"hidden\" name=\"debate\" value=\"$debate\">\n";
	echo "<input type=\"hidden\" name=\"round\" value=\"$round\">\n";
	foreach ($positions as $pos) {
		if ($pos == "og" || $pos == "cg") {
			echo "<div class=\"row\">\n";
		}

		$speakers = get_speaker_names($team_ids[$pos]);
		$team_name = team_name($team_ids[$pos]);
		echo "<div class=\"col-xs-12 col-md-6 ".$hl[$pos]."\">\n";
		echo "\t<h4>".addOrdinalNumberSuffix($tpos[$pos])." - ".$team_name." [".$tscore[$pos]." points]</h4>";
	        echo "\t<div class=\"row\">\n";
		echo "\t\t<label class=\"col-xs-9\">".$speakers[0]."</label>\n";
		echo "\t\t<input type=\"hidden\" id=\"".$pos."1\" name=\"".$pos."1\" value=\"".$_POST[$pos."1"]."\">\n";
		echo "\t\t<p class=\"col-xs-3 ".$hl[$pos."1"]."\">".$_POST[$pos."1"]."</p>\n";
		echo "\t</div>\n";
	        echo "\t<div class=\"row\">\n";
		echo "\t\t<label class=\"col-xs-9\">".$speakers[1]."</label>\n";
		echo "\t\t<input type=\"hidden\" id=\"".$pos."2\" name=\"".$pos."2\" value=\"".$_POST[$pos."2"]."\">\n";
		echo "\t\t<p class=\"col-xs-3 ".$hl[$pos."2"]."\">".$_POST[$pos."2"]."</p>\n";
		echo "\t</div>\n";
		echo "</div>\n";
		echo "<div class=\"clearfix visible-xs visible-sm\"></div>\n";
		if ($pos=="oo" || $pos == "co") {
			echo "</div>";
		}
	}
	echo "<hr />";
	echo "<div class=\"row\"><label for=\"note\" class=\"col-xs-12 col-md-4\">Additional Information</label>\n";
	echo "<input type=\"hidden\" name=\"note\" id=\"note\" value=\"".$_POST['note']."\">\n";
	echo "<p class=\"col-xs-12 col-md-8\">".$_POST['note']."</p>";
	echo "</div>\n";
	echo "<hr />";
	echo "<div class=\"row\">";
	if ($validated) {
		echo "<input type=\"submit\" value=\"Submit Score\" class=\"col-xs-5 col-md-2 btn btn-success\">";
	} else {
		echo "<input type=\"button\" value=\"Submit Score\" class=\"col-xs-5 col-md-2 btn btn-success disabled\">";
	}
		
	echo "<input type=\"button\" class=\"col-xs-5 col-xs-offset-2 col-md-2 col-md-offset-8 btn btn-default\" value=\"Back\" onClick=\"javascript:history.go(-1)\">";
	echo "</div></form>";
	return;

}

function store_to_db($debate) {
	global $positions;
	global $pnames;
	global $DBConn;
	$rs = qp("SELECT og, oo, cg, co FROM draws WHERE debate_id = ?", array($debate));
	$team_ids = $rs->FetchRow();
	foreach ($positions as $pos) {
		$s_rs = qp("SELECT speaker_id FROM speaker WHERE team_id = ? ORDER BY speaker_id ASC", array($team_ids[$pos]));
		$speaker = $s_rs->FetchRow();
		$esq['round_no'] = $_POST['round'];
		$esq['debate_id'] = $debate;
		$esq['speaker_id'] = $speaker['speaker_id'];
		$esq['speaker_score'] = $_POST[$pos."1"];
		$st1 = $DBConn->Replace("eballot_scores", $esq, array("round_no", "debate_id", "speaker_id"), false);
		if (!$st1) {
			$msg[] = "Failed to update scores for Speaker 1 in ".$pnames[$pos];
			$msg[] = $DBConn->ErrorMsg();
			break;
		}

		$speaker = $s_rs->FetchRow();
		$esq['speaker_id'] = $speaker['speaker_id'];
		$esq['speaker_score'] = $_POST[$pos."2"];
		$st2 = $DBConn->Replace("eballot_scores", $esq, array("round_no", "debate_id", "speaker_id"), false);
		if (!$st2) {
			$msg[] = "Failed to update scores for Speaker 2 in ".$pnames[$pos];
			$msg[] = $DBConn->ErrorMsg();
			break;
		}
	}
	if (isset($msg)) {
		$msg[] = "An error occurred updating the database";
		$msg[] = "Submit paper ballot to the tab room";
		display_errors($msg);
	} else {
		qp("UPDATE eballot_rooms SET updated=?, note=? WHERE debate_id = ?;", array($DBConn->BindTimeStamp(time()), $_POST['note'], $debate));
		echo "<div class=\"alert alert-success\" id=\"info\">\n<ul>\n";
		echo "<li>Ballot saved</li>\n<li>Paper ballots should still be submitted unless otherwise instructed</li>";
		echo "</ul></div>\n";
	}
	return;
}

function addOrdinalNumberSuffix($num) {
	if (!in_array(($num % 100),array(11,12,13))){
		switch ($num % 10) {
			// Handle 1st, 2nd, 3rd
			case 1:  return $num.'st';
			case 2:  return $num.'nd';
			case 3:  return $num.'rd';
		}
	}
	return $num.'th';
}

function page_header($title) {
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?></title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <div class="container">
<?PHP
  echo "<h1>$title</h1>";
  return;
}

function page_footer() {
	echo "</div>";
	if (file_exists("../config/version.txt")) {
		 $version_num = file_get_contents("../config/version.txt");
		 echo "<p class='text-center small'>Tabbie version: $version_num</p>";
	}
	echo <<<EoFooter
	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="https://code.jquery.com/jquery.js"></script>
	<script src="js/bootstrap.min.js"></script>
	</body>
</html>
EoFooter;
	return;
}
