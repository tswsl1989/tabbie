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

require('includes/fpdf/fpdf.php');

function speaker(&$pdf, $r, $speaker) {
    $pdf->Cell(95, 10, utf8_decode($r[$speaker]), "L");
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
    $pdf->Cell(85, 10, utf8_decode($teams[0]["name"]) . ": " . utf8_decode($r[$teams[0]["short"]]), "LT");
    $pdf->Cell(20, 10, "Rank: ", "T");
    $pdf->Cell(15, 10, "", "LRTB");
    $pdf->Cell(10, 10);
    $pdf->Cell(85, 10, utf8_decode($teams[1]["name"]) . ": " .  utf8_decode($r[$teams[1]["short"]]), "LT");
    $pdf->Cell(20, 10, "Rank: ", "T");
    $pdf->Cell(15, 10, "", "LRTB");
    $pdf->Ln();
    $pdf->Cell(120, 10, "", "LR");
    $pdf->Cell(10, 10);
    $pdf->Cell(120, 10, "", "LR");
    $pdf->Ln();
    four_speakers($pdf, $r, utf8_decode($teams[0]["short"]), utf8_decode($teams[1]["short"]));
}

function adjudicator_sheets_pdf($filename, $data) {
    $pdf = new FPDF("L"); #Landscape
    foreach ($data as $r) {
        $pdf->AddPage();
        $pdf->SetLeftMargin(25);
        $pdf->SetLineWidth(1.0);
        $pdf->SetFont('Arial','B', 11);
        $pdf->Cell(220, 8, "Venue: " . utf8_decode($r['venue']));
        $pdf->Cell(20, 8, "Round: " . utf8_decode($r['round']));
        $pdf->Ln();
        $pdf->Cell(200, 8, "Chair: " . utf8_decode($r['chair']));
        $pdf->Ln();
        $pdf->Cell(200, 8, "Panel: " . utf8_decode($r['panel']));
        $pdf->Ln();
        $pdf->MultiCell(250, 8, "Motion: " . utf8_decode($r['motion']));
        $pdf->Ln();
        two_teams($pdf, $r, array(
            array("name" => "Opening Gov.", "short" => "og"),
            array("name" => "Opening Opp.", "short" => "oo")));
        $pdf->Ln();
        two_teams($pdf, $r, array(
            array("name" => "Closing Gov.", "short" => "cg"),
            array("name" => "Closing Opp.", "short" => "co")));
        $pdf->SetFont('Arial','B', 10);
        $pdf->Cell(250, 8, "The best team gets Rank 1. A better rank has a higher total team score (no equal scores). Learn to do arithmetic or face the bin.");
        $pdf->Ln();
        $pdf->Cell(250, 8, "Created with Tabbie. See http://www.smoothtournament.com and http://tabbie.wikidot.com");
    }
    
    $pdf->Output($filename, "I");
}

?>
