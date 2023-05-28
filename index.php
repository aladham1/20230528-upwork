<?php
error_reporting(error_level: 0);
$fileName = '3370.24-de.pdf';
$moduleDetails = json_decode(json: file_get_contents(filename: 'data.json'));
$language = 'de';
require_once('ModulePDF.php');
$pdf = new ModulePDF(
	fileName: $fileName,
	moduleDetails: $moduleDetails,
	language: $language,
	smallerFontSize: false
);
if ($pdf->PageNo() > 1) {
	$pdf = new ModulePDF(
		fileName: $fileName,
		moduleDetails: $moduleDetails,
		language: $language,
		smallerFontSize: true
	);
}
$pdf->sendToBrowser();