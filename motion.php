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
			<h1>Motion for Round <?= $roundno ?></h1>
		</div>
<?php
	// Get the motion
	$min = $roundno - 1;
	$motion_query = "SELECT motion, info_slide, info ";
	$motion_query .= "FROM motions ";
	$motion_query .= "WHERE round_no=$roundno";
	$motion_result = mysql_query($motion_query);
	$row = mysql_fetch_array( $motion_result );
	if( $row['info_slide'] == "Y" )
	{
echo <<< END
		<div class='slide'>
			<h1>Info Slide</h1>
			<p>{$row['info']}</p>
		</div>\n
END;
	}
echo <<< END
		<div class='slide'>
			<h1>Motion</h1>
			<p class="motion">{$row['motion']}</p>
		</div>\n
END;
?>
	</div>	
</body>
</html>
