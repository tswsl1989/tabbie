<?php
/* begin license *
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



error_reporting(E_ALL);

/** Set the include path for PHPPowerPoint's subclasses (!) */
set_include_path(get_include_path() . PATH_SEPARATOR . "includes/phppowerpoint/");
require_once("includes/backend.php");
//require_once("includes/phppowerpoint/PHPPowerpoint.php");
require_once("includes/phppowerpoint/PHPPowerPoint/IOFactory.php");


/* SOAS Colours
$SOASYellow = new PHPPowerPoint_Style_Color( "FFFCFF7E" );
$SOASGreen = new PHPPowerPoint_Style_Color( "FFC58A68" );
$SOASOchre = new PHPPowerPoint_Style_Color( "FF974A1C" );
$SOASBrown  = new PHPPowerPoint_Style_Color( "FF364F39" );
*/


function add_text_element($slide, $text, $width, $height, $xoffset, $yoffset, $fontsize, $italic=false){
	$shape = $slide->createRichTextShape();
	$shape->setHeight($height);
	$shape->setWidth($width);
	$shape->setOffsetX($xoffset);
	$shape->setOffsetY($yoffset);
	$shape->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER);
	$shape->getAlignment()->setVertical( PHPPowerPoint_Style_Alignment::VERTICAL_CENTER);
	$textRun = $shape->createTextRun($text);
	$textRun->getFont()->setBold(true);
	$textRun->getFont()->setName("Georgia");
	$textRun->getFont()->setSize($fontsize);
	if($italic){
		$textRun->getFont()->setItalic(true);
	}
}

function add_design_element($slide, $path, $width, $height, $xoffset, $yoffset){
	$shape = $slide->createDrawingShape();
    $shape->setName('Graphic');
    $shape->setDescription('Graphic');
    $shape->setPath($path);
    $shape->setWidth($width);
    $shape->setHeight($height);
    $shape->setOffsetX($xoffset);
    $shape->setOffsetY($yoffset);
}

function two_slide_room_block($slide, $row_debate, $roundno, $offset){
	add_text_element($slide, $row_debate['venue_name'], 380, 90, 10+$offset, 50, 30, true);
	
	add_text_element($slide, $row_debate["ogtc"] . " " . $row_debate["ogt"], 380, 100,  10+$offset, 140, 30);
	add_text_element($slide, $row_debate["ootc"] . " " . $row_debate["oot"], 380, 100,  10+$offset, 240, 30);
	add_text_element($slide, $row_debate["cgtc"] . " " . $row_debate["cgt"], 380, 100,  10+$offset, 340, 30);
	add_text_element($slide, $row_debate["cotc"] . " " . $row_debate["cot"], 380, 100,  10+$offset, 440, 30);
	
	$judgelist = "";
	
	//Find the chair adjudicator
	$debate_id = $row_debate['debate_id'];
    $adj_query  = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
    $adj_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
    $adj_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND AR.status = 'chair' ";
	$adj_result=mysql_query($adj_query);
	$adj_row=mysql_fetch_assoc($adj_result);
	
	$judgelist.=$adj_row["adjud_name"]." (c), ";
	
	//Find the panellists
	$pan_query = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
 	$pan_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
 	$pan_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND AR.status = 'panelist' ";
 	$pan_result = mysql_query($pan_query);
	if(mysql_num_rows($pan_result) > 0){
		while($pan_row=mysql_fetch_assoc($pan_result))
        {    
          $judgelist .= $pan_row['adjud_name'] . ", ";
        }
	}

	//And the trainees		
	$trainee_query = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
 	$trainee_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
 	$trainee_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND AR.status = 'trainee' ";
 	$trainee_result=mysql_query($trainee_query);

 	$num_trainee=mysql_num_rows($trainee_result);
 	if (@$numtrainee > 0){
		while($trainee_row=mysql_fetch_assoc($trainee_result))
		{    
       		$judgelist .= $trainee_row['adjud_name'] . " (t), ";
		}
	}

	//Place panellists and trainees
	$judgelist=substr($judgelist,0,strlen($judgelist)-2); //removes the trailing commas (!)
	add_text_element($slide, $judgelist, 380, 120,  10+$offset, 540, 22, true);
}

$roundno=@$_GET['roundno'];
if(!$roundno){$roundno=get_num_rounds();};
if(array_key_exists("generate", @$_POST)) $generate = true;

if(isset($generate)){
	//Mux & output the powerpoint file
	header("Content-disposition: attachment; filename=tabbie-$roundno.pptx");
	header("Content-type: .pptx,application/vnd.openxmlformats-officedocument.presentationm1.presentation");	
	
	
	/* Some code for use with GD, if we ever get that far
	//Handle the first slide
	switch($_FILES['firstslide']['type']){
		case "image/jpg":
		case "image/jpeg":
		case "image/pjpeg":
		$firstslidegd = imagecreatefromjpeg($_FILES['firstslide']['tmp_name']);
		break;
		case "image/gif":
		$firstslidegd = imagecreatefromgif($_FILES['firstslide']['tmp_name']);
		break;
		case "image/png":
		$firstslidegd = imagecreatefrompng($_FILES['firstslide']['tmp_name']);
		break;
	}
	
	//And the second
	switch($_FILES['otherslides']['type']){
		case "image/jpg":
		case "image/jpeg":
		case "image/pjpeg":
		$otherslidesgd = imagecreatefromjpeg($_FILES['otherslides']['tmp_name']);
		break;
		case "image/gif":
		$otherslidesgd = imagecreatefromgif($_FILES['otherslides']['tmp_name']);
		break;
		case "image/png":
		$otherslidesgd = imagecreatefrompng($_FILES['otherslides']['tmp_name']);
		break;
	}
	*/
	
	$firstslide = $_FILES['firstslide']['tmp_name'];
	$otherslides = $_FILES['otherslides']['tmp_name'];
	$roomsperslide = $_POST['roomsperslide'];
	
	$presentation = new PHPPowerPoint();
	$presentation->getProperties()->setCreator("Tabbie");

	// First slide
	$currentSlide = $presentation->getActiveSlide();
	
	// Add background image
	if($firstslide != "") add_design_element($currentSlide, $firstslide, 950, 720, 0, 0);
	
	//Add text with the round
	add_text_element($currentSlide, "Round $roundno", 700, 300, 325, 520, 65);
	
	//Put in the room data (!)
	$query = "SELECT debate_id AS debate_id, T1.team_code AS ogt, T2.team_code AS oot, T3.team_code AS cgt, T4.team_code AS cot, U1.univ_code AS ogtc, U2.univ_code AS ootc, U3.univ_code AS cgtc, U4.univ_code AS cotc, venue_name, venue_location ";
	$query .= "FROM draw_round_$roundno, team T1, team T2, team T3, team T4, university U1, university U2, university U3, university U4,venue ";
	$query .= "WHERE og = T1.team_id AND oo = T2.team_id AND cg = T3.team_id AND co = T4.team_id AND T1.univ_id = U1.univ_id AND T2.univ_id = U2.univ_id AND T3.univ_id = U3.univ_id AND T4.univ_id = U4.univ_id AND draw_round_$roundno.venue_id=venue.venue_id ORDER BY venue_name ASC"; 
	$result=mysql_query($query);
	echo(mysql_error());
	if ($roomsperslide == 1){
		while ($row_debate=mysql_fetch_assoc($result))
		{
			//Create a new slide
			$slide=$presentation->createSlide();

			//Background image
			if($otherslides != "") add_design_element($slide, $otherslides, 950, 720, 0, 0);

			//Venue name
			add_text_element($slide, $row_debate['venue_name'], 940, 90, 10, 20, 44);

			//Government & Opposition Labels
			add_text_element($slide, "Government", 460, 50,  10, 110, 18);
			add_text_element($slide, "Opposition", 460, 50, 490, 110, 18);

			//Teams
			add_text_element($slide, $row_debate["ogtc"] . " " . $row_debate["ogt"], 460, 190,  10, 160, 44);
			add_text_element($slide, $row_debate["ootc"] . " " . $row_debate["oot"], 460, 190, 490, 160, 44);
			add_text_element($slide, $row_debate["cgtc"] . " " . $row_debate["cgt"], 460, 190,  10, 350, 44);
			add_text_element($slide, $row_debate["cotc"] . " " . $row_debate["cot"], 460, 190, 490, 350, 44);

			//Find the chair adjudicator
			$debate_id = $row_debate['debate_id'];
	        $adj_query  = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
	        $adj_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
	        $adj_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND AR.status = 'chair' ";
			$adj_result=mysql_query($adj_query);
			$adj_row=mysql_fetch_assoc($adj_result);

			add_text_element($slide, $adj_row["adjud_name"], 940, 55,  10, 550, 30);

			//Find the panellists
			$panellistlist = "";

			$pan_query = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
	     	$pan_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
	     	$pan_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND AR.status = 'panelist' ";
	     	$pan_result = mysql_query($pan_query);
			if(mysql_num_rows($pan_result) > 0){
				while($pan_row=mysql_fetch_assoc($pan_result))
		        {    
		          $panellistlist .= $pan_row['adjud_name'] . ", ";
		        }
			}

			//And the trainees		
			$trainee_query = "SELECT AR.adjud_id as adjud_id, Ad.adjud_name as adjud_name ";
	     	$trainee_query .= "FROM adjud_round_$roundno AR, adjudicator Ad ";
	     	$trainee_query .= "WHERE debate_id = $debate_id AND AR.adjud_id = Ad.adjud_id AND AR.status = 'trainee' ";
	     	$trainee_result=mysql_query($trainee_query);

	     	$num_trainee=mysql_num_rows($trainee_result);
	     	if (@$numtrainee > 0){
				while($trainee_row=mysql_fetch_assoc($trainee_result))
				{    
		       		$panellistlist .= $trainee_row['adjud_name'] . " (t), ";
				}
			}

			//Place panellists and trainees
			add_text_element($slide, $panellistlist, 940, 85,  10, 605, 30);
		}
	} else if ($roomsperslide == 2){
		/* 2 rooms per slide
		
		Total width of the slide is 950.
		
		Left gutter 10px.
		Label block 150px (160px)
		Label gutter 10px (170px)
		Left block 380px (550px)
		Middle gutter 10px (560px)
		Right block 380px (940px)
		Right gutter 10px (950px)
		
		*/
		$slidecount = 0;
		while ($row_debate=mysql_fetch_assoc($result))
		{
			$slidecount++;
			if(($slidecount % 2) == 1){
				//Slidecount is odd - left-hand side (verso)
				//Therefore create a new slide
				$slide=$presentation->createSlide();
				
				//width, height, xoffset, yoffset, fontsize
				
				//Background image
				if($otherslides != "") add_design_element($slide, $otherslides, 950, 720, 0, 0);
				
				//Labels
				add_text_element($slide, "Venue:", 150, 90, 10, 50, 22, true);
				
				add_text_element($slide, "Opening Government:", 155, 100,  10, 140, 14, true);
				add_text_element($slide, "Opening Opposition:", 155, 100,  10, 240, 14, true);
				add_text_element($slide, "Closing Government:", 155, 100,  10, 340, 14, true);
				add_text_element($slide, "Closing Opposition:", 155, 100,  10, 440, 14, true);
				
				add_text_element($slide, "Judges:", 150, 120,  10, 540, 16, true);
				
				two_slide_room_block($slide, $row_debate, $roundno, 170);
				
			}
			if(($slidecount % 2) == 0){
				two_slide_room_block($slide, $row_debate, $roundno, 560);
			}
			
		}
		
		
	}
	
	
	// Slide saying "The Motion" (a safeguard against accidentally revealing it!)
	$currentSlide = $presentation->createSlide();
	if($otherslides != "") add_design_element($currentSlide, $firstslide, 960, 720, 0, 0);
	add_text_element($currentSlide, "The Motion", 700, 300, 325, 520, 65);
	
	// Slide with the motion on it
	$currentSlide = $presentation->createSlide();
	if($otherslides != "") add_design_element($currentSlide, $otherslides, 960, 720, 0, 0);
	add_text_element($currentSlide, get_motion_for_round($roundno), 930, 720, 10, 10, 80);
	
	//Write out the file
	$objWriter = PHPPowerPoint_IOFactory::createWriter($presentation, 'PowerPoint2007');
	$objWriter->save("php://output");
	
	
} else {
	//Display the starting form
	$ntu_controller = "print";
	$moduletype = "";
	$title="Export to Microsoft PowerPoint 2007";
	
	require("view/header.php");
	require("view/mainmenu.php");
	?>
	<h2>Generate a PowerPoint 2007 File</h2>
	<p>This feature is under development.</p>
	<p>At present you should supply two image files.
	<ul><li>The first will be used for the first slide, (containing the text "Round <?php echo($roundno);?>"), and for the penultimate slide reading "The Motion".</li>
	<li>The second will be used as the background for all other slides, including the slide containing the motion text.</li></ul></p>
	<p>The image files should be:
	<ul><li>At least 960px by 720px, <b>and</b></li>
	<li>Under 2MB in size, <b>and</b></li>
	<li>GIFs, JPEGs or PNGs</li></ul></p>
	<p>No transitions are programmed into the output file: you should either advance the slides manually, or add transitions by hand in PowerPoint.</p>
	<p>
	<form enctype="multipart/form-data" action="create_powerpoint.php?roundno=<?php echo($roundno); ?>" method="POST">
	<input type="hidden" name="MAX_FILE_SIZE" value="2000000">
	<input type="hidden" name="generate" value="true">
	File for first slide: <input name="firstslide" type="file" /><br/>
	File for other slides: <input name="otherslides" type="file" /><br/>
	Rooms per slide: <select name="roomsperslide"><option value="1" selected="selected">1</option><option value="2">2</option></select>
	<input type="submit" value="Generate PowerPoint"/>
	</form>
	<?
}





/*





//	createGeneralSlide
//	Create a slide with the Tabbie bottom-strap

function setshapeColor(PHPPowerPoint_Shape $shape, PHPPowerPoint_Style_Color $color){
	$shape->getFill()->setStartColor($color);
	$shape->getFill()->setEndColor($color);
	$shape->getFill()->setFillType( PHPPowerPoint_Style_Fill::FILL_SOLID);
	return $shape;
}



function createGeneralSlide(PHPPowerPoint $presentation){
	$slide = $presentation->createSlide();
	$shape = $slide->createRichTextShape();
	$shape->setWidth(950);
	$shape->setHeight(40);
	$shape->setOffsetX(0);
	$shape->setOffsetY(720-40);
	$shape->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER);
	$shape->getAlignment()->setVertical( PHPPowerPoint_Style_Alignment::VERTICAL_CENTER);
	$textrun = $shape->createTextRun("Created with Tabbie. http://tabbie.wikidot.com. Maintained by Giles Robertson.".$shape->getFill());
	$textrun->getFont()->setSize(14);
	$textrun->getFont()->setName("Helvetica");
	$textrun->getFont()->setColor(new PHPPowerPoint_Style_Color( 'FF974A1C' ));
	
	return $slide;
}

$presentation = new PHPPowerPoint();
$presentation->getProperties()->setCreator("Tabbie");

// Remove the automatically generated first slide
$presentation->removeSlideByIndex(0);

$currentSlide = createGeneralSlide($presentation);

$shape = $currentSlide->createRichTextShape();
$shape->setHeight(300);
$shape->setWidth(600);
$shape->setOffsetX(170);
$shape->setOffsetY(180);
$shape->getAlignment()->setHorizontal( PHPPowerPoint_Style_Alignment::HORIZONTAL_CENTER);
$textRun = $shape->createTextRun("Round $roundno");
$textRun->getFont()->setBold(true);
$textRun->getFont()->setSize(80);

$objWriter = PHPPowerPoint_IOFactory::createWriter($presentation, 'PowerPoint2007');
$objWriter->save("php://output");
*/
?>