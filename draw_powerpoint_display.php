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
require_once("includes/backend.php");
$roundno=@$_GET['roundno'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=utf-8">
    <title>Draw :: Round <?= $roundno ?></title>
    <meta name="generator" content="S5" />
	<meta name="version" content="S5 1.1" />
	<meta name="presdate" content="20050728" />
	<meta name="author" content="Eric A. Meyer" />
	<meta name="company" content="Complex Spiral Consulting" />
	<!-- configuration parameters -->
	<meta name="defaultView" content="slideshow" />
	<meta name="controlVis" content="hidden" />
	<!-- style sheet links -->
	<link rel="stylesheet" href="view/ui/default/slides.css" type="text/css" media="projection" id="slideProj" />
	<link rel="stylesheet" href="view/ui/default/outline.css" type="text/css" media="screen" id="outlineStyle" />
	<link rel="stylesheet" href="view/ui/default/print.css" type="text/css" media="print" id="slidePrint" />
	<link rel="stylesheet" href="view/ui/default/opera.css" type="text/css" media="projection" id="operaFix" />
	<!-- S5 JS -->
	<script src="view/ui/default/slides.js" type="text/javascript"></script>  
</head>

<body>
	<div class="layout">
		<div id="controls"><!-- DO NOT EDIT --></div>
		<div id="currentSlide"><!-- DO NOT EDIT --></div>
		<div id="header"></div>
		<div id="footer">
			<h1>Draw :: Round <?= $roundno ?></h1>
			<h2>Created with Tabbie, see <a href="http://smoothtournament.com">http://smoothtournament.com</a>.</h2>
		</div>
	</div>
	<div class="presentation">
		<div class="slide">
			<h1>Draw for Round <?= $roundno ?></h1>
			<div id="autoplay"><a href="javascript: auto();">Start Show</a></div>
		</div>
			
<?php
	// Get the individual debate details
	$venue_query = "SELECT draw.debate_id AS debate_id, draw.og AS ogid, draw.oo AS ooid, draw.cg AS cgid, draw.co AS coid, draw.venue_id AS venue_id, venue.venue_name AS venue_name, venue.venue_location AS venue_location, oguniv.univ_code AS og_univ_code, ogteam.team_code AS og_team_code, oouniv.univ_code AS oo_univ_code, ooteam.team_code AS oo_team_code, cguniv.univ_code AS cg_univ_code, cgteam.team_code AS cg_team_code, couniv.univ_code AS co_univ_code, coteam.team_code AS co_team_code ";
	$venue_query .= "FROM draw_round_$roundno AS draw, venue AS venue, university AS oguniv, team AS ogteam, university AS oouniv, team AS ooteam, university AS cguniv, team AS cgteam, university AS couniv, team AS coteam ";
	$venue_query .= "WHERE draw.venue_id = venue.venue_id AND ogteam.team_id = draw.og AND oguniv.univ_id = ogteam.univ_id AND ooteam.team_id = draw.oo AND oouniv.univ_id = ooteam.univ_id AND cgteam.team_id = draw.cg AND cguniv.univ_id = cgteam.univ_id AND coteam.team_id = draw.co AND couniv.univ_id = coteam.univ_id ";
	$venue_query .= "ORDER BY RAND() ";
	$venue_result = mysql_query($venue_query);
	while ($venue_row=mysql_fetch_assoc($venue_result))
	{
		$debate_id = $venue_row['debate_id'];
echo <<< END
		<div class='slide'>
			<h1>{$venue_row['venue_name']}</h1>
			<div id='left'>
				<div id='leftup'>
					<h2>Opening Government</h2>
					<p>{$venue_row['og_univ_code']} {$venue_row['og_team_code']}</p>
				</div>
				<div id='leftdown'>
					<h2>Closing Government</h2>
					<p>{$venue_row['cg_univ_code']} {$venue_row['cg_team_code']}</p>
				</div>
			</div>
			<div id='right'>
				<div id='rightup'>
					<h2>Opening Opposition</h2>
					<p>{$venue_row['oo_univ_code']} {$venue_row['oo_team_code']}</p>
				</div>
				<div id='rightdown'>
					<h2>Closing Opposition</h2>
					<p>{$venue_row['co_univ_code']} {$venue_row['co_team_code']}</p>
				</div>
			</div>\n
END;
		// Get Chair
		$chfadj_query = "SELECT adjud.adjud_name AS adjud_name FROM adjud_round_$roundno AS round, adjudicator AS adjud WHERE round.debate_id = $debate_id AND round.status = 'chair' AND adjud.adjud_id = round.adjud_id ";
		$chfadj_result = mysql_query($chfadj_query);
		$chfadj_row=mysql_fetch_assoc($chfadj_result);
echo <<< END
			<div id='bottom'>
				<h2>Adjudicators</h2>
				<p>{$chfadj_row['adjud_name']}
END;
		// Get Panelists
		$pnladj_query = "SELECT adjud.adjud_name AS adjud_name FROM adjud_round_$roundno AS round, adjudicator AS adjud WHERE round.debate_id = $debate_id AND round.status = 'panelist' AND adjud.adjud_id = round.adjud_id ";
		$pnladj_result = mysql_query($pnladj_query);
		while($pan_row=mysql_fetch_assoc($pnladj_result))
		{    
			echo ", {$pan_row['adjud_name']}";
		}
		echo "</p>\n";
echo <<< END
			</div>
		</div>\n
END;
	}
echo <<< END
		<div class='slide'>
			<p class="show"><a href='javascript: goTo( 0 );'>Show Draw Again</a></p>
			<p class="show"><a href='motion.php?roundno=$roundno'>Show Motion</a></p>
		</div>
END;
?>
	</div>
</body>
</html>  