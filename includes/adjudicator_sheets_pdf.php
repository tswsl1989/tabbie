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

require_once('config/settings.php');
date_default_timezone_set(@date_default_timezone_get());

require('includes/fpdf/fpdf.php');
require('includes/fpdf/tfpdf.php');

function text_convert($t) {
	if (extension_loaded('mbstring')) {
		# If mbstring is loaded then we can use UTF-8 text as is
		return $t;
	} else {
		# And if all else fails, use utf8_decode and accept that
		# some things are going to be replaced with ?
		return utf8_decode($t);
	}
}

function speaker(&$pdf, $r, $speaker) {
    $pdf->Cell(95, 10, text_convert($r[$speaker]), "L");
    $pdf->Cell(25, 10, "", "LRTB");
}

function four_speakers(&$pdf, $r, $team1, $team2) {
    speaker($pdf, $r, $team1 . "1");
    $pdf->Cell(10, 10);
    speaker($pdf, $r, $team2 . "1");
    $pdf->Ln();
    speaker($pdf, $r, $team1 . "2");
    $pdf->Cell(10, 10);
    speaker($pdf, $r, $team2. "2");
    $pdf->Ln();
    $pdf->Cell(95, 10, "Total:", "LB");
    $pdf->Cell(25, 10, "", "LRTB");
    $pdf->Cell(10, 10);
    $pdf->Cell(95, 10, "Total:", "LB");
    $pdf->Cell(25, 10, "", "LRTB");
    $pdf->Ln();
}

function two_teams(&$pdf, $r, $teams) {
    $pdf->Cell(85, 10, text_convert($teams[0]["name"]) . ": " . text_convert($r[$teams[0]["short"]]), "LT");
    $pdf->Cell(20, 10, "Rank: ", "T");
    $pdf->Cell(15, 10, "", "LRTB");
    $pdf->Cell(10, 10);
    $pdf->Cell(85, 10, text_convert($teams[1]["name"]) . ": " .  text_convert($r[$teams[1]["short"]]), "LT");
    $pdf->Cell(20, 10, "Rank: ", "T");
    $pdf->Cell(15, 10, "", "LRTB");
    $pdf->Ln();
    $pdf->Cell(120, 10, "", "LR");
    $pdf->Cell(10, 10);
    $pdf->Cell(120, 10, "", "LR");
    $pdf->Ln();
    four_speakers($pdf, $r, text_convert($teams[0]["short"]), text_convert($teams[1]["short"]));
}

function adjudicator_sheets_pdf($filename, $data) {
    global $local_image;
    if (extension_loaded('mbstring')) {
	# Multibyte support available, so use UTF-8 support
	$pdf = new tFPDF("L"); #Landscape
	$pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
	$pdf->AddFont('DejaVu','B','DejaVuSansCondensed-Bold.ttf',true);
	$fontName='DejaVu';
    } else {
	$pdf = new FPDF("L");
	$fontName = "Helvetica";
    }
    foreach ($data as $r) {
        $pdf->AddPage();
        $pdf->SetLeftMargin(25);
        $pdf->SetLineWidth(1.0);
	$pdf->SetFont($fontName,'B', 10, true);

        if (file_exists($local_image))  {
            $pdf->Image($local_image,240,10);
        }
        $pdf->Cell(220, 8, "Venue: " . text_convert($r['venue']));
        $pdf->Ln();
        $pdf->Cell(20, 8, "Round: " . text_convert($r['round']));
        $pdf->Ln();
        $pdf->Cell(200, 8, "Chair: " . text_convert($r['chair']));
        $pdf->Ln();
        $pdf->Cell(200, 8, "Panel: " . text_convert($r['panel']));
        $pdf->Ln();
        $pdf->MultiCell(250, 8, "Motion: " . text_convert($r['motion']));
        $pdf->Ln();
        two_teams($pdf, $r, array(
            array("name" => "Opening Gov.", "short" => "og"),
            array("name" => "Opening Opp.", "short" => "oo")));
        $pdf->Ln();
        two_teams($pdf, $r, array(
            array("name" => "Closing Gov.", "short" => "cg"),
            array("name" => "Closing Opp.", "short" => "co")));
        $pdf->SetFont($fontName,'B', 10, true);
        $pdf->Cell(220, 8, "The best team gets Rank 1. Higher rank requires higher total team score (no equal scores).");
	$pdf->Ln();
	$pdf->Cell(220, 8, "Failure to comply with this instruction will affect your judge ranking.");
    }
    
    $pdf->Output($filename, "I");
}

?>
