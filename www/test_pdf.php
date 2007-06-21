<?php
require('includes/fpdf/fpdf.php');
//TODO: Docname...

$data = array(
            array(
                "chair" => "The Chair Has A Long Name",
                "round" => "1",
                "venue" => "A Room at a location",
                "motion" => "THWB motions can be extremely long and phrased as to need many many many words  and confuse everyone",
                "og" => "DGJ - A",
                "oo" => "UU - B",
                "cg" => "XYZ - A",
                "co" => "AA - A",
                "og1" => "Klaas van Schelven",
                "og2" => "Avihu Tamir",
                "oo1" => "Anat Gelber",
                "oo2" => "Someone with an very long name",
                "cg1" => "Pietje Puk",
                "cg2" => "Blaatje Boetleje",
                "co1" => "John D.",
                "co2" => "Wu Meng Tan"
                ),
            array(
                "chair" => "Different Chair",
                "round" => "1",
                "venue" => "A Room at a location nr2",
                "motion" => "THWB in a short motion",
                "og" => "DGJ - A",
                "oo" => "UU - B",
                "cg" => "XYZ - A",
                "co" => "AA - A",
                "og1" => "Klaas van Schelven",
                "og2" => "Avihu Tamir",
                "oo1" => "Anat Gelber",
                "oo2" => "Someone with an very long name",
                "cg1" => "Pietje Puk",
                "cg2" => "Blaatje Boetleje",
                "co1" => "John D.",
                "co2" => "Wu Meng Tan"
                ));

function speaker($pdf, $r, $speaker) {
    $pdf->Cell(95, 10, $r[$speaker], "L");
    $pdf->Cell(25, 10, "", "LRTB");
}

function four_speakers($pdf, $r, $team1, $team2) {
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

function two_teams($pdf, $r, $teams) {
    $pdf->Cell(85, 10, $teams[0]["name"] . $r[$teams[0]["short"]], "LT");
    $pdf->Cell(20, 10, "Rank: ", "T");
    $pdf->Cell(15, 10, "", "LRTB");
    $pdf->Cell(10, 10);
    $pdf->Cell(85, 10, $teams[1]["name"] . ": " .  $r[$teams[1]["short"]], "LT");
    $pdf->Cell(20, 10, "Rank: ", "T");
    $pdf->Cell(15, 10, "", "LRTB");
    $pdf->Ln();
    $pdf->Cell(120, 10, "", "LR");
    $pdf->Cell(10, 10);
    $pdf->Cell(120, 10, "", "LR");
    $pdf->Ln();
    four_speakers($pdf, $r, $teams[0]["short"], $teams[1]["short"]);
}

function adjudicator_forms_pdf($filename, $data) {
    $pdf = new FPDF("L");
    foreach ($data as $r) {
        $pdf->AddPage();
        $pdf->SetLineWidth(1.0);
        $pdf->SetFont('Arial','B', 16);
        $pdf->Cell(220, 10, "Venue: " . $r['venue']);
        $pdf->Cell(20, 10, "Round: " . $r['round']);
        $pdf->Ln();
        $pdf->Cell(200, 10, "Chair: " . $r['chair']);
        $pdf->Ln();
        $pdf->MultiCell(250, 10, "Motion: " . $r['motion']);
        $pdf->Ln();
        two_teams($pdf, $r, array(
            array("name" => "Opening Gov.", "short" => "og"),
            array("name" => "Opening Opp.", "short" => "oo")));
        $pdf->Ln();
        two_teams($pdf, $r, array(
            array("name" => "Closing Gov.", "short" => "cg"),
            array("name" => "Closing Opp.", "short" => "co")));
        $pdf->SetFont('Arial','B', 10);
        $pdf->Ln();
        $pdf->Cell(250, 8, "The best team gets Rank 1. A higher rank has a higher total team score (no equal scores). Don't be stupid.");
    }
    
    $pdf->Output($filename, "I");
}

adjudicator_forms_pdf("test.pdf", $data);

?>