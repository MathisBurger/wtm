<?php

namespace App\Generator;

use TCPDF;

/**
 * The report pdf wrapper
 */
class ReportPdf extends TCPDF
{
    private string $period = '';

    public function Header() {
        $image_file = 'pdf_logo.png';
        $this->Image($image_file, 10, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        $this->SetFont('helvetica', 'B', 25);
        $this->Cell(0, 15, 'Monatsbericht (' . $this->period . ')', 0, false, 'C', 0, '', 0, false, 'M', 'M');
    }

    /**
     * Handles the creation of the pdf
     *
     * @param string $html The html that is written
     * @param string $period The time period
     * @return void
     */
    public function handleCreation(string $html, string $period): void
    {
        $this->period = $period;
        $this->SetCreator(PDF_CREATOR);
        $this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
        $this->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', 30));
        $this->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);
        $this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $this->SetFont('dejavusans', '', 10);
        $this->AddPage();
        $this->writeHTML($html, true, false, true, false, '');
    }
}