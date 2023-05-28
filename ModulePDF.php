<?php
require_once 'vendors/FPDF/FPDF.php';

class ModulePDF extends FPDF
{
	private const MULTI_CELL_HEIGHT = 4;

	private string $fileName;
	private bool $smallerFontSize;

	public function __construct(string $fileName, stdClass $moduleDetails, string $language, bool $smallerFontSize)
	{
		$this->fileName = $fileName;
		$this->smallerFontSize = $smallerFontSize;

		parent::__construct();

		$this->AddFont('Roboto', '', 'RobotoCondensed-Regular.php');
		$this->AddFont('Roboto', 'B', 'RobotoCondensed-Bold.php');
		$this->AddFont('Roboto', 'I', 'RobotoCondensed-Italic.php');
		$this->AddFont('Roboto', 'BI', 'RobotoCondensed-BoldItalic.php');

		$this->AddPage();
		$this->SetAutoPageBreak(true, 10);

		$logos = [];
		if (trim($moduleDetails->logo1_pdfFileName) !== '') {
			$logos[] = 'logo/' . $moduleDetails->logo1_pdfFileName;
		} else if (trim($moduleDetails->logo1_dateiname) !== '') {
			$logos[] = 'logo/' . $moduleDetails->logo1_dateiname;
		}
		if (trim($moduleDetails->logo2_pdfFileName) !== '') {
			$logos[] = 'logo/' . $moduleDetails->logo2_pdfFileName;
		} else if (trim($moduleDetails->logo2_dateiname) !== '') {
			$logos[] = 'logo/' . $moduleDetails->logo2_dateiname;
		}
		if (trim($moduleDetails->logo3_pdfFileName) !== '') {
			$logos[] = 'logo/' . $moduleDetails->logo3_pdfFileName;
		} else if (trim($moduleDetails->logo3_dateiname) !== '') {
			$logos[] = 'logo/' . $moduleDetails->logo3_dateiname;
		}

		$rightPagePadding = 11;
		$logoWidth = 38;

		$pageWidth = $this->GetPageWidth();

		$i = 0;
		foreach ($logos as $logoPath) {
			$logoPadding = 4 * $i;
			$i++;
			$this->Image($logoPath, $pageWidth - $rightPagePadding - ($i * $logoWidth) - $logoPadding, 15, $logoWidth);
		}

		$this->SetFont('Roboto', '', 14);
		$this->SetXY(10, 10);
		$this->MultiCell(40, ModulePDF::MULTI_CELL_HEIGHT, $moduleDetails->nummer, 0, 'L');

		$this->SetFont('Roboto', '', 14);
		$this->SetXY(10, 18);
		$this->MultiCell(110, ModulePDF::MULTI_CELL_HEIGHT + 2, $moduleDetails->titel, 0, 'L');

		$newY = ($this->GetY() > 32) ? $this->GetY()+2 : 32;

		$this->SetXY(11, $newY);

		switch ($moduleDetails->zuteilung) {
			case 'basel':
				$this->SetFillColor(213, 0, 13);
				break;
			case 'bern':
				$this->SetFillColor(254, 202, 0);
				break;
			case 'zuerich':
				$this->SetFillColor(0, 113, 180);
				break;
		}
		$this->Cell($this->GetPageWidth() - 20, 1, '', 10, 0, '', true);

		$this->SetXY(10, $this->GetY() + 4);

		if (trim($moduleDetails->ziel) !== '') {
			$this->addContentArea($this->mph_setLanguage('Ziel', $language), $moduleDetails->ziel);
		}

		if (trim($moduleDetails->inhalte) !== '') {
			$this->addContentArea($this->mph_setLanguage('Inhalte', $language), $moduleDetails->inhalte);
		}

		if (trim($moduleDetails->methoden) !== '') {
			$this->addContentArea($this->mph_setLanguage('Methoden', $language), $moduleDetails->methoden);
		}

		if ($moduleDetails->nachweisID == 6) {
			$sLN = $moduleDetails->nachweis_info;
		} else {
			$sLN = $moduleDetails->nachweis;
			if (trim($moduleDetails->nachweis_info) != '') {
				$sLN .= '<br>' . $moduleDetails->nachweis_info;
			}
		}
		$this->addContentArea($this->mph_setLanguage('LNInfo', $language), $sLN);

		$stime = ($language == 'en') ? 'hour' : 'Stunde';
		$plr = ($language == 'en') ? 's' : 'n';
		$x = '';

		if ($moduleDetails->vorbereitung == 0) {
			$x .= $this->mph_setLanguage("KeineVorbereitung", $language);
		} else {
			$x .= $moduleDetails->vorbereitung . " " . (($moduleDetails->vorbereitung == 1) ? $stime : $stime . $plr) . " " . $this->mph_setLanguage('Vorbereitung', $language);
		}
		$x .= ', ';

		if ($moduleDetails->nachbearbeitung == 0) {
			$x .= $this->mph_setLanguage("KeineNachbereitung", $language);
		} else {
			$x .= $moduleDetails->nachbearbeitung . " " . (($moduleDetails->nachbearbeitung == 1) ? $stime : $stime . $plr) . " " . $this->mph_setLanguage("Nachbereitung", $language);
		}

		if (trim($moduleDetails->vorbereitung_info) != "") {
			$x .= '<br>' . $moduleDetails->vorbereitung_info;
		}
		$this->addContentArea($this->mph_setLanguage('Vornach', $language), $x);

		$ectsValue = ((int)$moduleDetails->ECTS_punkte === 1) ? $this->mph_setLanguage('ECTS_Kreditpunkt', $language) : $this->mph_setLanguage('ECTS_Kreditpunkte', $language);
		$this->addContentArea($this->mph_setLanguage('ECTS_Kreditpunkte', $language), $moduleDetails->ECTS_punkte . ' ' . $ectsValue);

		if (trim($moduleDetails->publikum) !== '') {
			$this->addContentArea($this->mph_setLanguage('Zielpublikum', $language), $moduleDetails->publikum);
		}

		if (trim($moduleDetails->vorkenntnisse) !== '') {
			$this->addContentArea($this->mph_setLanguage('Vorkenntnisse', $language), $moduleDetails->vorkenntnisse);
		}

		$org = $moduleDetails->hv_name;

		if (trim($moduleDetails->mv_name) !== '') {
			$org .= '<br>' . $moduleDetails->mv_name;
		}
		if (trim($org) !== '') {
			$this->addContentArea($this->mph_setLanguage('Veranstalter', $language), $org);
		}

		$lArr = [];

		$leitung1Arr = [];
		if (trim($moduleDetails->leitung1_titel) != '') {
			$leitung1Arr[] = $moduleDetails->leitung1_titel;
		}
		if (trim($moduleDetails->leitung1_vorname) != '') {
			$leitung1Arr[] = $moduleDetails->leitung1_vorname . ' ' . $moduleDetails->leitung1_nachname;
		}
		if (trim($moduleDetails->leitung1_zusatz) != '') {
			$leitung1Arr[] = $moduleDetails->leitung1_zusatz;
		}
		if (count($leitung1Arr) != 0) {
			$lArr[1] = implode(" ", $leitung1Arr);
		}
		if ($moduleDetails->lv1_name != '') {
			$lArr[1] .= ", {$moduleDetails->lv1_name }";
		}

		$leitung2Arr = [];
		if (trim($moduleDetails->leitung2_titel) != '') {
			$leitung2Arr[] = $moduleDetails->leitung2_titel;
		}
		if (trim($moduleDetails->leitung2_vorname) != '') {
			$leitung2Arr[] = $moduleDetails->leitung2_vorname . ' ' . $moduleDetails->leitung2_nachname;
		}
		if (trim($moduleDetails->leitung2_zusatz) != '') {
			$leitung2Arr[] = "({$moduleDetails->leitung2_zusatz})";
		}
		if (count($leitung2Arr) != 0) {
			$lArr[2] = implode(" ", $leitung2Arr);
		}
		if ($moduleDetails->lv2_name != '') {
			$lArr[2] .= ", {$moduleDetails->lv2_name }";
		}

		$leitung = implode('<br>', $lArr);

		$coordinationTitle = ($moduleDetails->jahr >= 2021) ? $this->mph_setLanguage('Leitung2021', $language) : $this->mph_setLanguage('Leitung', $language);
		$this->addContentArea($coordinationTitle, $leitung);

		if (trim($moduleDetails->referenten) !== '') {
			$tutorsTitle = ($moduleDetails->jahr >= 2021) ? $this->mph_setLanguage('ReferentInnen2021', $language) : $this->mph_setLanguage('ReferentInnen', $language);
			$this->addContentArea($tutorsTitle, $moduleDetails->referenten);
		}

		$this->addContentArea($this->mph_setLanguage('Daten', $language), $moduleDetails->daten);
		$this->addContentArea($this->mph_setLanguage('Ort', $language), $moduleDetails->ort);
		$this->addContentArea($this->mph_setLanguage('Kosten', $language), 'CHF ' . number_format($moduleDetails->kosten, 0, "", "'") . '.- ' . $moduleDetails->kosten_info);
		if ($language === 'en') {
			$deadline = trim((trim($moduleDetails->deadline) !== '') ? strftime('%e %B %Y', (new DateTime($moduleDetails->deadline))->getTimestamp()) : '');
		} else {
			$deadline = trim((trim($moduleDetails->deadline) !== '') ? strftime('%e. %B %Y', (new DateTime($moduleDetails->deadline))->getTimestamp()) : '');
		}
		$this->addContentArea($this->mph_setLanguage('Anmeldeschluss', $language), $deadline);
		if (trim($moduleDetails->spezielles) !== '') {
			$this->addContentArea($this->mph_setLanguage('Spezielles', $language), $moduleDetails->spezielles, true);
		}
	}

	private function addContentArea(string $title, string $text, bool $isLast = false): void
	{
		$fontSize = ($this->smallerFontSize) ? 9 : 10;

		$this->SetFont('Roboto', 'b', $fontSize);
		$currentY = $this->GetY();
		$currentPage = $this->PageNo();
		$this->MultiCell(35, ModulePDF::MULTI_CELL_HEIGHT, $title, 0, 'L');
		$minEndY = $this->GetY();
		$isOnSamePage = ($currentPage === $this->PageNo());
		if ($isOnSamePage) {
			$this->SetXY(45, $currentY);
		} else {
			$this->SetXY(45, 10);
		}
		$this->SetFont('Roboto', '', $fontSize);
		$this->writeDetailsParagraph($text);
		if ($minEndY > $this->GetY()) {
			$this->SetY($minEndY);
		}
		if (!$isLast) {
			$this->Cell($this->GetPageWidth() - 50, 4, '', 0, 1, 'L');
		}
	}

	private function writeDetailsParagraph(string $html): void
	{
		$html = str_replace("\n", ' ', $html);
		$a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

		$hasParagraphs = false;
		$isParagraphOpen = false;
		$isListElementOpen = false;
		$isIgnore = false;

		foreach ($a as $i => $e) {
			$this->SetX(45);
			if ($i % 2 === 0) {
				if (trim($e) === '' || $isIgnore) {
					continue;
				}
				if ($isListElementOpen) {
					$y = $this->GetY();
					$this->SetX(41);
					$this->MultiCell(4, 4, '- ', 0, 'L');
					$this->SetY($y);
					$this->SetX(45);
					$this->MultiCell($this->GetPageWidth() - 50, ModulePDF::MULTI_CELL_HEIGHT, html_entity_decode($e), 0, 'L');
				} else {
					$this->MultiCell($this->GetPageWidth() - 50, ModulePDF::MULTI_CELL_HEIGHT, html_entity_decode($e), 0, 'L');
				}

				continue;
			}

			$e = strtolower($e);
			if ($e === 'p') {
				$isParagraphOpen = true;
				if (!$hasParagraphs) {
					$hasParagraphs = true;
					continue;
				}

				$this->MultiCell($this->GetPageWidth() - 50, ModulePDF::MULTI_CELL_HEIGHT, '', 0, 'L');
				continue;
			}

			if ($e === 'br') {
				continue;
			}

			if ($e === '/p' && $isParagraphOpen) {
				$isParagraphOpen = false;
				continue;
			}

			if ($e === 'ul') {
				continue;
			}

			if ($e === '/ul') {
				continue;
			}

			if ($e === 'li') {
				$isListElementOpen = true;
				continue;
			}

			if ($e === '/li') {
				$isListElementOpen = false;
				continue;
			}

			if (in_array($e, ['!--[if gte mso 9]', '!--[if gte mso 10]'])) {
				$isIgnore = true;
				continue;
			}

			if ($e === '![endif]--') {
				$isIgnore = false;
				continue;
			}

			if ($isIgnore) {
				continue;
			}

			var_dump($e);
			exit;
		}
	}

	/*
	// From https://stackoverflow.com/questions/50176102/fpdf-writehtml-is-not-working/50176478
	private function WriteHTML(string $html)
	{
		// HTML parser
		$html = str_replace("\n", ' ', $html);
		$a = preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

		foreach ($a as $i => $e) {
			if ($i % 2 == 0) {
				$this->Write(5, $e);
			} else {
				// Tag
				if ($e[0] == '/') {
					$this->CloseTag(strtoupper(substr($e, 1)));
				} else {
					// Extract attributes
					$a2 = explode(' ', $e);
					$tag = strtoupper(array_shift($a2));
					$this->OpenTag($tag);
				}
			}
		}
	}

	private function OpenTag($tag)
	{
		if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
			$this->SetStyle($tag, true);
		}
		if ($tag == 'BR' || $tag == 'P') {
			$this->Ln(5);
		}
	}

	private function CloseTag($tag)
	{
		if ($tag == 'B' || $tag == 'I' || $tag == 'U') {
			$this->SetStyle($tag, false);
		}
	}

	private function SetStyle($tag, $enable)
	{
		// Modify style and select corresponding font
		$this->$tag += ($enable ? 1 : -1);
		$style = '';
		foreach (['B', 'I', 'U'] as $s) {
			if ($this->$s > 0) {
				$style .= $s;
			}
		}
		$this->SetFont('', $style);
	}
*/
	public function sendToBrowser(): void
	{
		$this->Output('I', $this->fileName);
	}

	private function mph_setLanguage($row, $lang = 'de')
	{
		$lang = ($lang == 'en') ? 1 : 0;

		$language = [
			'Ziel'               => ['Ziel', 'Goal'],
			'Inhalte'            => ['Inhalte', 'Contents'],
			'Methoden'           => ['Methoden', 'Methods'],
			'Veranstalter'       => ['Veranstalter', 'Organisation'],
			'Vorkenntnisse'      => ['Vorkenntnisse', 'Requirements'],
			'LNInfo'             => ['Leistungsnachweis', 'Exam'],
			'Zielpublikum'       => ['Zielpublikum', 'Target audience'],
			'Leitung'            => ['Leitung', 'Coordination of course'],
			'Leitung2021'        => ['Leitung', 'Module lead'],
			'ReferentInnen'      => ['Referentinnen und Referenten', 'Lecturers and tutors'],
			'ReferentInnen2021'  => ['ReferentInnen', 'Lecturers and tutors'],
			'Daten'              => ['Datum', 'Dates'],
			'Ort'                => ['Ort', 'Location'],
			'Kosten'             => ['Kosten', 'Fees'],
			'Anmeldeschluss'     => ['Anmeldeschluss', 'Registration deadline'],
			'ECTS_Kreditpunkt'   => ['ECTS-Punkt', 'ECTS Credit'],
			'ECTS_Kreditpunkte'  => ['ECTS-Punkte', 'ECTS Credits'],
			'Spezielles'         => ['Spezielles', 'Additional information'],
			'Vorbereitung'       => ['Vorbereitung', 'preparation'],
			'Nachbereitung'      => ['Nachbereitung', 'postprocessing'],
			'KeineVorbereitung'  => ['Keine Vorbereitung', ' No preparation'],
			'KeineNachbereitung' => ['keine Nachbereitung', ' no postprocessing'],
			'Vornach'            => ['Vor- und Nachbereitung', 'Preparation  and postprocessing'],
		];

		return $language[$row][$lang];
	}
}