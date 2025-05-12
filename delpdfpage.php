<?php
require_once('common/commonfunctions.php');
require_once('config.php');
$tempDir = isset($cfg['db']['tempdir']) ? $cfg['db']['tempdir'] : "temp/";
$temppath = $cfg['db']['tempfilepath'];
$thefile = $temppath . basename($XVARS['PDF']);
if (!file_exists($thefile)) {
    echo 'pleaseWait("");showError("Unable to find PDF");';
    exit;
}

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;

require_once('pdf_php/fpdf.php');
require_once('pdf_php/autoload.php');
$pdf = new FPDI();
$pageCount = $pdf->setSourceFile($thefile);

//  Array of pages to skip -- modify this to fit your needs
$skipPages = [$XVARS['Page']];

//  Add all pages of source to new document
for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
    //  Skip undesired pages
    if (in_array($pageNo, $skipPages))
        continue;

    //  Add page to the document
    $templateID = $pdf->importPage($pageNo);
    $size = $pdf->getTemplateSize($templateID);
    $pdf->addPage($size['orientation'], [$size[0], $size[1]]);
    $pdf->useTemplate($templateID);
}
$pdf->Output('F', $thefile);
echo 'pleaseWait("");removePDFPage();';
