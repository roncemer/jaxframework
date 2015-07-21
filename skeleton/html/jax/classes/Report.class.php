<?php
// THIS FILE IS PART OF THE phpreportgen PACKAGE.  DO NOT EDIT.
// THIS FILE GETS RE-WRITTEN EACH TIME THE UPSTREAM PACKAGE IS UPDATED.
// ANY MANUAL EDITS WILL BE LOST.

// Report.class.php
// Copyright (c) 2011-2015 Ronald B. Cemer
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

require_once dirname(__FILE__).'/fpdf/fpdf.php';
require_once dirname(__FILE__).'/PHPExcel/Classes/PHPExcel.php';

class ReportColumn {
	public $name;
	public $heading;
	public $align;
	public $format;
	public $decimalPlaces;
	public $useThousandsSeparator;
	public $suppressNegative;
	public $suppressZero;
	public $suppressPositive;
	public $relativeWidth;
	public $outputTotalsAtLevels;
	public $valueCalcCallback;
	public $formatCallback;

	// Construct a ReportColumn instance.
	// Parameters:
	// $params: An assocative array of parameter names to their values.
	//
	// Recognized paramters:
	// name: The mnemonic field name for the column, used to retrieve data from the data object.
	// heading: The column heading which is outputted at the top of the column.
	// align: The column alignment; one of 'left', 'center' or 'right'.
	//     Optional.  Defaults to 'left'.
	// format: The output format.  One of 'string', 'number'.
	//     Optional.  Defaults to 'string'.
	// decimalPlaces: For output formats of type 'number', this is the number of decimal places.
	//     Optional.  Defaults to 0.
	// useThousandsSeparator: For output formats of type 'number', this allows the caller to turn
	//     on or off the thousands separator (typically a comma [,] character).
	//     Optional.  Defaults to true.
	// suppressNegative: true to suppress negative values; false to not (only used for numeric
	//     format columns).
	// suppressZero: true to suppress zero values; false to not (only used for numeric
	//     format columns).
	// suppressPositive: true to suppress positive values; false to not (only used for numeric
	//     format columns).
	// relativeWidth: A relative width (relative to the other columns).  This is only used in
	//     PDF format.
	//     Optional.  Defaults to 1.0.
	// outputTotalsAtLevels: An array of strings containing report level names at which
	//     to print totals for this column.  This can also be null, false or an empty array.
	//     Optional.  Defaults to null.
	// valueCalcCallback: A function callback in the form used for call_user_func().  If this
	//     is set to a valid function callback, the function will be called each time a value
	//     calculation is needed.  This includes when a report row is being output, and when
	//     totals are being output at any level.  The following parameters are passed to the
	//     callback function:
	//         $row: An object containing the row or totals data.
	//         $report: The Report instance.
	//         $column: The ReportColumn instance.
	//         $totalsLevel: The ReportLevel instance for the current totals level being output,
	//             or null if we are currently outputting a regular report row.
	//     The callback function must return the calculated value.
	//     NOTE: The parameters to the callback function are NOT passed by reference, according
	//     to the PHP documentation.  If this is true, this means that changes made to these
	//     objects within the callback function will NOT affect the original objects.
	// formatCallback: A function callback in the form used for call_user_func().  If this
	//     is set to a valid function callback, the function will be called each time a value
	//     is output, in order to do custom formatting for the value just before it is output.
	//     This includes when a report row is being output, and when totals are being output at
	//     any level.  The following parameters are passed to the
	//     callback function:
	//         $row: An object containing the row or totals data.
	//         $report: The Report instance.
	//         $column: The ReportColumn instance.
	//         $totalsLevel: The ReportLevel instance for the current totals level being output,
	//             or null if we are currently outputting a regular report row.
	//         $value: The unformatted value.
	//     The callback function must return the formatted value, in the format that is desired
	//     for final output.
	//     NOTE: The parameters to the callback function are NOT passed by reference, according
	//     to the PHP documentation.  If this is true, this means that changes made to these
	//     objects within the callback function will NOT affect the original objects.
	public function ReportColumn($params) {
		$this->name = isset($params['name']) ? (string)$params['name'] : '';
		$this->heading = isset($params['heading']) ? (string)$params['heading'] : '';
		$this->align = isset($params['align']) ? (string)$params['align'] : 'left';
		if (($this->align != 'left') && ($this->align != 'center') && ($this->align != 'right')) {
			$this->align = 'left';
		}
		$this->format = isset($params['format']) ? (string)$params['format'] : 'string';
		if ( ($this->format != 'string') && ($this->format != 'number') ) {
			$this->format = 'string';
		}
		$this->decimalPlaces = isset($params['decimalPlaces']) ? (int)$params['decimalPlaces'] : 0;
		if ($this->decimalPlaces < 0) $this->decimalPlaces = 0;
		$this->useThousandsSeparator = isset($params['useThousandsSeparator']) ?
			(boolean)$params['useThousandsSeparator'] : true;
		$this->suppressNegative = isset($params['suppressNegative']) ?
			(boolean)$params['suppressNegative'] : false;
		$this->suppressZero = isset($params['suppressZero']) ?
			(boolean)$params['suppressZero'] : false;
		$this->suppressPositive = isset($params['suppressPositive']) ?
			(boolean)$params['suppressPositive'] : false;
		$this->relativeWidth = isset($params['relativeWidth']) ? (double)$params['relativeWidth'] : 1.0;
		$this->outputTotalsAtLevels = isset($params['outputTotalsAtLevels']) ?
			$params['outputTotalsAtLevels'] : array();
		if (!is_array($this->outputTotalsAtLevels)) {
			$this->outputTotalsAtLevels = trim($this->outputTotalsAtLevels);
			$this->outputTotalsAtLevels = ($this->outputTotalsAtLevels != '') ?
				explode(',', $this->outputTotalsAtLevels) : array();
		}
		$this->valueCalcCallback = isset($params['valueCalcCallback']) ?
			$params['valueCalcCallback'] : null;
		$this->formatCallback = isset($params['formatCallback']) ?
			$params['formatCallback'] : null;
	}
}

class ReportCustomColumn {
	public $align;
	public $columnSpan;

	// Construct a ReportCustomColumn instance.
	// Parameters:
	// $params: An assocative array of parameter names to their values.
	//
	// Recognized paramters:
	// align: The column alignment; one of 'left', 'center' or 'right'.
	//     Optional.  Defaults to 'left'.
	// columnSpan: The number of actual columns to occupy within the report grid.
	//     Optional.  Defaults to 1.
	public function ReportCustomColumn($params) {
		$this->align = isset($params['align']) ? (string)$params['align'] : 'left';
		if (($this->align != 'left') && ($this->align != 'center') && ($this->align != 'right')) {
			$this->align = 'left';
		}
		$this->columnSpan = isset($params['columnSpan']) ? (int)$params['columnSpan'] : 0;
		if ($this->columnSpan < 1) $this->columnSpan = 1;
	}
}

class ReportLevel {
	public $name;
	public $uniqueIdColumnNames;
	public $totalsDescription;
	public $totalsDescriptionLeftColumnName;
	public $totalsDescriptionColumnSpan;
	public $totalsDescriptionAlign;
	public $reOutputHeadingAfterEachLevelTotal;

	// Construct a ReportLevel instance.
	// Parameters:
	// $params: An assocative array of parameter names to their values.
	//
	// Recognized paramters:
	// name: The unique mnemonic name for the level; used to distinguish it from different
	//     levels.
	// uniqueIdColumnNames: An array of strings containing the column names which
	//     will all contain the same set of values for a group of rows at this level.
	// totalsDescription: The description which is prepended to the total lines at this level.
	//     Optional.  Defaults to empty.
	// totalsDescriptionLeftColumnName: The name of the leftmost column in which the totals
	//     description should be printed.
	//     This MUST be specified if a totals description is specified, or the totals description
	//     won't print.
	//     Optional.  Defaults to empty.
	// totalsDescriptionColumnSpan: The number of columns to use for the totals description,
	//     with the column referenced by $totalsDescriptionLeftColumnName being the leftmost
	//     column of the contiguous span of columns in which the totals description is printed.
	//     Optional.  Defaults to 1.
	// totalsDescriptionAlign: The alignment ('left', 'right', 'center') for the totals description.
	//     Optional.  Defaults to 'left'.
	// reOutputHeadingAfterEachLevelTotal: true to re-output the headings before outputting
	//     the next row following any totals which are output for this level.
	//     Optional.  Defaults to false.
	public function ReportLevel($params) {
		$this->name = isset($params['name']) ? (string)$params['name'] : '';
		$this->uniqueIdColumnNames = isset($params['uniqueIdColumnNames']) ?
			$params['uniqueIdColumnNames'] : array();
		$this->totalsDescription = isset($params['totalsDescription']) ?
			(string)$params['totalsDescription'] : '';
		$this->totalsDescriptionLeftColumnName = isset($params['totalsDescriptionLeftColumnName']) ?
			(string)$params['totalsDescriptionLeftColumnName'] : '';
		$this->totalsDescriptionColumnSpan = isset($params['totalsDescriptionColumnSpan']) ?
			(int)$params['totalsDescriptionColumnSpan'] : 0;
		if ($this->totalsDescriptionColumnSpan < 1) $this->totalsDescriptionColumnSpan = 1;

		$this->totalsDescriptionAlign = isset($params['totalsDescriptionAlign']) ?
			(string)$params['totalsDescriptionAlign'] : 'left';
		if (($this->totalsDescriptionAlign != 'left') &&
			($this->totalsDescriptionAlign != 'center') &&
			($this->totalsDescriptionAlign != 'right')) {
			$this->totalsDescriptionAlign = 'left';
		}
		$this->reOutputHeadingAfterEachLevelTotal =
			isset($params['reOutputHeadingAfterEachLevelTotal']) ?
			(boolean)$params['reOutputHeadingAfterEachLevelTotal'] : false;
	}
}

class Report {
	public $columns;
	public $levels;
	public $outputter;
	public $title;

	// Whether to always output unique identifier column values when outputting csv
	// or tsv format. Normally it would be desirable to always output all column
	// values in csv or tsv format, so this defaults to true.
	// Set this to false to enable suppressing of duplicate unique identifier column
	// values in csv or tsv output format.
	public $alwaysOutputUniqueIdColumnsInCSVOrTSVFormat = true;

	// Whether to always output unique identifier column values when outputting html
	// format. Normally it would be desirable to suppress duplicate unique identifier
	// column values in html format, so this defaults to false.
	// Set this to true to disable suppressing of duplicate unique identifier column
	// values in html output format.
	public $alwaysOutputUniqueIdColumnsInHTMLFormat = false;

	// Whether to always output unique identifier column values when outputting pdf
	// format. Normally it would be desirable to suppress duplicate unique identifier
	// column values in pdf format, so this defaults to false.
	// Set this to true to disable suppressing of duplicate unique identifier column
	// values in pdf output format.
	public $alwaysOutputUniqueIdColumnsInPDFFormat = false;

	// Whether to always output unique identifier column values when outputting xls
	// format. Normally it would be desirable to suppress duplicate unique identifier
	// column values in xls format, so this defaults to false.
	// Set this to true to disable suppressing of duplicate unique identifier column
	// values in xls output format.
	public $alwaysOutputUniqueIdColumnsInXLSFormat = false;

	// Whether to output totals when outputting csv or tsv format.
	// Normally it would not be desirable to include totals lines when outputting
	// in csv or tsv output format, so this defaults to false.
	// Set this to true to enable outputting of totals when outputting in csv or tsv
	// format.
	public $outputTotalsInCSVOrTSVFormat = false;

	protected $headingOutput = false;
	protected $htmlTableOpen = false;
	protected $htmlTableSection = null;
	protected $prevIdValues = array();
	protected $totals = array();

	public $oddRow = false;

	// When outputting HTML, these control the classes and attributes
	// of the various HTML tags which are output.
	public $htmlTableClass = 'reportTable';
	public $htmlTableBorder = '1';
	public $htmlTableCellSpacing = '0';
	public $htmlTableCellPadding = '2';
	public $htmlTHeadClass = '';
	public $htmlTHeadTRClass = '';
	public $htmlTHeadTHClass = '';
	public $htmlTHeadTHNoWrap = 'nowrap';
	public $htmlTHeadTHAlign = 'center';
	public $htmlTBodyClass = '';
	public $htmlTBodyTRClass = '';
	public $htmlTBodyTROddClass = 'odd';
	public $htmlTBodyTREvenClass = 'even';
	public $htmlTBodyTDClass = '';
	public $htmlTBodyTDNoWrap = 'nowrap';
	public $htmlTBodyTotalsTRClass = 'reportTotals';
	public $htmlTBodyTotalsTDClass = '';
	public $htmlTBodyTotalsTDNoWrap = 'nowrap';
	// If this is true, a complete HTML document will be output if the output
	// format is 'html'; otherwise, HTML which is suitable for inclusion within
	// a greater HTML document will be output.  Defaults to true.
	// Ignored if the output format is not 'html'.
	public $outputCompleteHTMLDocument = true;

	// When outputting PDF, these control the appearance.
	// General parameters:
	public $pdfColumnSpacing = 2;							// space between columns, in points
	// Title parameters:
	public $pdfTitleFontFamily = 'helvetica';
	public $pdfTitleFontStyle = 'B';
	public $pdfTitleFontSize = 12;
	public $pdfTitleTextRGB = '0.0,0.0,0.0';				// comma-separated r,g,b [0.0...1.0]
	// Page Number parameters:
	public $pdfPageNumFontFamily = 'helvetica';
	public $pdfPageNumFontStyle = 'B';
	public $pdfPageNumFontSize = 8;
	public $pdfPageNumTextRGB = '0.0,0.0,0.0';				// comma-separated r,g,b [0.0...1.0]
	public $pdfPageNumWidth = 108;							// 1.5 inch
	// Heading parameters:
	public $pdfHeadingBackgroundFill = true;				// fill background true/false
	public $pdfHeadingBackgroundRGB = '0.75,0.88,0.94';		// comma-separated r,g,b [0.0...1.0]
	public $pdfHeadingBorder = 'B';							// 0=none; 1=frame; or any combination
															// of L,R,T,B for left/right/top/bottom
	public $pdfHeadingBorderRGB = '0.5,0.5,0.5';			// comma-separated r,g,b [0.0...1.0]
	public $pdfHeadingFontFamily = 'helvetica';
	public $pdfHeadingFontStyle = 'B';
	public $pdfHeadingFontSize = 8;
	public $pdfHeadingTextRGB = '0.25,0.25,0.25';			// comma-separated r,g,b [0.0...1.0]
	// Detail parameters:
	public $pdfDetailFontFamily = 'helvetica';
	public $pdfDetailFontStyle = '';
	public $pdfDetailFontSize = 8;
	public $pdfDetailTextRGB = '0.25,0.25,0.25';			// comma-separated r,g,b [0.0...1.0]
	public $pdfDetailBackgroundOddRGB = '0.886,0.894,1.0';
	public $pdfDetailBackgroundEvenRGB = '1.0,1.0,1.0';
	// Totals parameters:
	public $pdfTotalsFontFamily = 'helvetica';
	public $pdfTotalsFontStyle = 'B';
	public $pdfTotalsFontSize = 8;
	public $pdfTotalsTextRGB = '0.25,0.25,0.25';			// comma-separated r,g,b [0.0...1.0]

	// Construct a Report instance.
	// Parameters:
	// $columns: An array of ReportColumn instances which describe the columns.
	// $levels: An array of ReportLevel instances which describe the levels.  The first element
	//     is always the top level (the level at which grand totals would be printed).
	// $outputter: A ReportOutputter instance which will handle the output functionality.
	// $title: The title of the report.  Optional.  Only applies to certain output types.
	public function Report(&$columns, &$levels, &$outputter, $title = '') {
		$this->columns = &$columns;
		$this->levels = &$levels;
		$this->outputter = &$outputter;
		$this->title = $title;
	}

	public function softReset() {
		$this->headingOutput = false;
		$this->htmlTableOpen = false;
		$this->htmlTableSection = null;
		$this->prevIdValues = array();
		$this->totals = array();
		$this->oddRow = false;
	}

	// Reset totals; get ready to start the report again.
	// This function does not change the output format.
	// This function resets the output stream and empties the output.
	public function reset() {
		$this->softReset();

		$this->outputter->reset();
	}

	public function forceNewHeadings() {
		$this->headingOutput = false;
	}

	public function findLevelIdxByName($levelName) {
		for ($i = 0, $n = count($this->levels); $i < $n; $i++) {
			if ($this->levels[$i]->name == $levelName) {
				return $i;
			}
		}
		return -1;
	}

	protected function outputHeading($forceNewPage = false) {
		switch ($this->outputter->outputFormat) {
		case 'xls':
			if ($this->outputter->workbook === null) $this->outputter->createWorkbook();
			break;
		case 'pdf':
			$newPage = false;
			if ($this->outputter->pdf === null) {
				$this->outputter->createPDF();
				$this->outputter->pdf->AddPage();
				$this->outputter->pageNumber++;
				$newPage = true;
			} else if ($forceNewPage) {
				$this->outputter->flushPDF();
				$this->outputter->pdf->AddPage();
				$this->outputter->pageNumber++;
				$newPage = true;
			} else {
				// Calculate height of headings.
				$this->outputter->pdf->SetFont(
					$this->pdfHeadingFontFamily,
					$this->pdfHeadingFontStyle,
					$this->pdfHeadingFontSize
				);
				$hs = $this->outputter->getPDFLineSpacing();
				// If we can't the headings on the current page, begin a new page.
				if (($this->outputter->pdf->y + $hs) >
					$this->outputter->pdf->PageBreakTrigger) {
					$this->outputter->flushPDF();
					$this->outputter->pdf->AddPage();
					$this->outputter->pageNumber++;
					$newPage = true;
				}
			}

			if ($newPage) {
				// Output title, if present.
				// Calculate larger line spacing of title and page number.
				$ls = 0;
				if ($this->title != '') {
					$this->outputter->pdf->SetFont(
						$this->pdfTitleFontFamily,
						$this->pdfTitleFontStyle,
						$this->pdfTitleFontSize
					);
					$ls = max($ls, $this->outputter->getPDFLineSpacing());
					$this->outputter->setPDFRGBTextColor($this->pdfTitleTextRGB);
					$spc = 4;
					$this->outputter->pdf->Cell(
						$this->outputter->getPDFPageWidth()-($this->pdfPageNumWidth+$spc),
						$this->outputter->getPDFLineSpacing(),
						$this->title,
						0,
						0,
						'L'
					);
					$this->outputter->pdf->x += $spc;
					unset($spc);
				} else {
					$this->outputter->pdf->x +=
						$this->outputter->getPDFPageWidth()-$this->pdfPageNumWidth;
				}

				// Output page number.
				// Calculate larger line spacing of title and page number.
				$this->outputter->pdf->SetFont(
					$this->pdfPageNumFontFamily,
					$this->pdfPageNumFontStyle,
					$this->pdfPageNumFontSize
				);
				$ls = max($ls, $this->outputter->getPDFLineSpacing());
				$this->outputter->setPDFRGBTextColor($this->pdfPageNumTextRGB);
				$this->outputter->pdf->Cell(
					$this->pdfPageNumWidth,
					$this->outputter->getPDFLineSpacing(),
					'Page '.$this->outputter->pageNumber,
					0,
					0,
					'R'
				);

				// Move down to the correct position for the column headings.
				$this->outputter->pdf->Ln($ls);
			}	// if ($newPage)

			// Prepare to output column headings.
			$this->outputter->pdf->SetFont(
				$this->pdfHeadingFontFamily,
				$this->pdfHeadingFontStyle,
				$this->pdfHeadingFontSize
			);

			$pdfTotalColSpacing = $this->pdfColumnSpacing*(count($this->columns)-1);

			$totalRelativeWidth = 0.0;
			foreach ($this->columns as &$col) $totalRelativeWidth += $col->relativeWidth;
			unset($col);	// Release reference to last element
			break;
		}

		$rowData = '';
		$sep = '';
		$colNum = -1;

		if ($this->outputter->outputFormat == 'html') {
			$this->ensureHTMLTableSection('thead');
			$rowData .=
				'<tr'.
				(($this->htmlTHeadTRClass != '') ? ' class="'.$this->htmlTHeadTRClass.'"' : '').
				'>';
		}

		foreach ($this->columns as &$col) {
			$colNum++;
			switch ($this->outputter->outputFormat) {
			case 'html':
				$rowData .=
					'<th'.
					(($this->htmlTHeadTHClass != '') ? ' class="'.$this->htmlTHeadTHClass.'"' : '').
					(($this->htmlTHeadTHNoWrap != '') ? ' nowrap="'.$this->htmlTHeadTHNoWrap.'"' : '').
					(($this->htmlTHeadTHAlign != '') ? ' align="'.$this->htmlTHeadTHAlign.'"' : '').
					'>'.htmlspecialchars($col->heading).'</th>';
				break;
			case 'csv':
				$rowData .= $sep.ReportOutputter::encodeCSV($col->heading);
				if ($sep == '') $sep = ',';
				break;
			case 'tsv':
				$rowData .= $sep.ReportOutputter::encodeTSV($col->heading);
				if ($sep == '') $sep = "\t";
				break;
			case 'xls':
				$cell = $this->outputter->worksheet->getCellByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
				$cell->setValueExplicit($col->heading, PHPExcel_Cell_DataType::TYPE_STRING);
				$style = $this->outputter->worksheet->getStyleByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
				$style->getAlignment()->setHorizontal('center');
				$style->getFont()->setBold(true);
				break;
			case 'pdf':
				$colWidth = (int)
					(((double)$col->relativeWidth/(double)$totalRelativeWidth) *
					 (double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
				if ($this->pdfHeadingBackgroundFill) {
					$savex = $this->outputter->pdf->x;
					$savey = $this->outputter->pdf->y;
					$this->outputter->setPDFRGBFillColor($this->pdfHeadingBackgroundRGB);
					$this->outputter->pdf->Cell(
						$colWidth,
						$this->outputter->getPDFLineSpacing(),
						'',
						0,
						0,
						'C',
						1
					);
					$this->outputter->pdf->x = $savex;
					$this->outputter->pdf->y = $savey;
				}
				$this->outputter->setPDFRGBDrawColor($this->pdfHeadingBorderRGB);
				$this->outputter->setPDFRGBTextColor($this->pdfHeadingTextRGB);
				$this->outputter->pdf->Cell(
					$colWidth,
					$this->outputter->getPDFLineSpacing(),
					$col->heading,
					$this->pdfHeadingBorder,
					0,
					'C'
				);
				$this->outputter->pdf->x += $this->pdfColumnSpacing;
				break;
			}
		}
		unset($col);		// Release reference to last element

		switch ($this->outputter->outputFormat) {
		case 'html':
			$rowData .= '</tr>';
			$this->outputter->outputText($rowData);
			break;
		case 'csv':
		case 'tsv':
			$rowData .= "\r\n";
			$this->outputter->outputText($rowData);
			break;
		case 'xls':
			$this->outputter->worksheetRowNum++;
			break;
		case 'pdf':
			$this->outputter->pdf->Ln();
			break;
		}

		$this->headingOutput = true;
	}

	public function outputRow(&$row) {
		$allowSuppression = true;
		$justOutputHeading = false;

		if ($this->outputter->outputFormat == 'pdf') {
			// Be sure the row will fit on this page; if not (or if the PDF document
			// has not been opened yet), go to next page and output heading.
			if ($this->outputter->pdf === null) {
				$this->outputHeading(true);
				$justOutputHeading = true;
				$allowSuppression = false;
			} else {
				$this->outputter->pdf->SetFont(
					$this->pdfDetailFontFamily,
					$this->pdfDetailFontStyle,
					$this->pdfDetailFontSize
				);
				if ((($this->outputter->pdf->y + $this->outputter->getPDFLineSpacing()) >
				 	$this->outputter->pdf->PageBreakTrigger)) {
					$this->outputHeading(true);
					$justOutputHeading = true;
					$allowSuppression = false;
				}
			}
		}

		// If we need a heading, output it now, but don't force a page break.
		if (!$this->headingOutput) {
			$this->outputHeading();
			$justOutputHeading = true;
			$allowSuppression = false;
		}

		$this->oddRow = !$this->oddRow;

		// Create a copy of $row into $crow, calculating any calculated fields.
		$crow = new stdClass();
		foreach ($this->columns as $col) {
			$colName = $col->name;
			// If this is a calculated column, call the function to calculate the value.
			// Otherwise, just get the value.
			if ($col->valueCalcCallback !== null) {
				$colval = call_user_func(
					$col->valueCalcCallback,
					$row,
					$this,
					$col,
					null
				);
			} else {
				$colval = isset($row->$colName) ? $row->$colName : '';
			}

			$crow->$colName = $colval;
		}

		$needAnotherHeading = false;

		switch ($this->outputter->outputFormat) {
		case 'csv':
		case 'tsv':
			if ($this->alwaysOutputUniqueIdColumnsInCSVOrTSVFormat) $allowSuppression = false;
			break;
		case 'html':
			if ($this->alwaysOutputUniqueIdColumnsInHTMLFormat) $allowSuppression = false;
			break;
		case 'pdf':
			if ($this->alwaysOutputUniqueIdColumnsInPDFFormat) $allowSuppression = false;
			break;
		case 'xls':
			if ($this->alwaysOutputUniqueIdColumnsInXLSFormat) $allowSuppression = false;
			break;
		}

		$colNamesToSuppress = array();
		$totalsPrintLevel = null;
		foreach ($this->levels as $level) {
			$cns = array();
			foreach ($this->columns as $col) {
				$colName = $col->name;
				if (in_array($colName, $level->uniqueIdColumnNames)) {
					if ((!isset($this->prevIdValues[$colName])) ||
						($crow->$colName != $this->prevIdValues[$colName])) {
						$totalsPrintLevel = &$level;
						$allowSuppression = false;
						if ((!$justOutputHeading) && ($level->reOutputHeadingAfterEachLevelTotal)) {
							$needAnotherHeading = true;
						}
						break;
					}
					$cns[] = $colName;
				}
			}
			if (!$allowSuppression) break;
			foreach ($cns as $colName) {
				if (!in_array($colName, $colNamesToSuppress)) {
					$colNamesToSuppress[] = $colName;
				}
			}
		}

		if ($totalsPrintLevel !== null) {
			for ($levelIdx = count($this->levels)-1; $levelIdx >= 0; $levelIdx--) {
				$this->outputAndResetTotals($levelIdx);
				if ($this->levels[$levelIdx] == $totalsPrintLevel) break;
			}
			if ($needAnotherHeading) {
				// If we need another heading based on heading re-output rules, output it now.
				// NOTE: This should NEVER occur if we just output a heading.
				$this->outputHeading();
				$justOutputHeading = true;
				$allowSuppression = false;
				$this->oddRow = true;
			}
		}

		if ($this->outputter->outputFormat == 'pdf') {
			$this->outputter->pdf->SetFont(
				$this->pdfDetailFontFamily,
				$this->pdfDetailFontStyle,
				$this->pdfDetailFontSize
			);
			$this->outputter->setPDFRGBFillColor(
				$this->oddRow ?
					$this->pdfDetailBackgroundOddRGB : $this->pdfDetailBackgroundEvenRGB
			);
			$this->outputter->setPDFRGBTextColor($this->pdfDetailTextRGB);

			$pdfTotalColSpacing = $this->pdfColumnSpacing*(count($this->columns)-1);

			$totalRelativeWidth = 0.0;
			foreach ($this->columns as &$col) $totalRelativeWidth += $col->relativeWidth;
			unset($col);	// Release reference to last element
		}

		$sep = '';
		$rowData = '';
		$colNum = -1;

		if ($this->outputter->outputFormat == 'html') {
			$this->ensureHTMLTableSection('tbody');
			$cls = trim(
				$this->htmlTBodyTRClass.' '.
				($this->oddRow ? $this->htmlTBodyTROddClass : $this->htmlTBodyTREvenClass)
			);
			$rowData .= '<tr'.(($cls != '') ? ' class="'.$cls.'"' : '').'>';
			unset($cls);
		}

		// Output data columns.
		foreach ($this->columns as &$col) {
			$colNum++;
			$colName = $col->name;

			$colval = $crow->$colName;

			if (!in_array($colName, $colNamesToSuppress)) {
				switch ($col->format) {
				case 'number':
					if ((($colval < 0) && ($col->suppressNegative)) ||
						(($colval == 0) && ($col->suppressZero)) ||
						(($colval > 0) && ($col->suppressPositive))) {
						$dispval = '';
					} else {
						if ($col->formatCallback !== null) {
							$dispval = call_user_func(
								$col->formatCallback,
								$row,
								$this,
								$col,
								null,
								$colval
							);
						} else {
							if (!$col->useThousandsSeparator) {
								$dispval = number_format((double)$colval, $col->decimalPlaces, '.', '');
							} else {
								$dispval = number_format((double)$colval, $col->decimalPlaces);
							}
						}
					}
					break;
				default:
					if ($col->formatCallback !== null) {
						$dispval = call_user_func(
							$col->formatCallback,
							$row,
							$this,
							$col,
							null,
							$colval
						);
					} else {
						$dispval = $colval;
					}
					break;
				}
			} else {
				$dispval = '';
			}

			switch ($this->outputter->outputFormat) {
			case 'html':
				$rowData .=
					'<td'.
					(($this->htmlTBodyTDClass != '') ? ' class="'.$this->htmlTBodyTDClass.'"' : '').
					(($this->htmlTBodyTDNoWrap != '') ? ' nowrap="'.$this->htmlTBodyTDNoWrap.'"' : '').
					' align="'.$col->align.'">'.
					((trim($dispval) != '') ? htmlspecialchars($dispval) : '&nbsp;').
					'</td>';
				break;
			case 'csv':
				$rowData .= $sep.ReportOutputter::encodeCSV($dispval);
				if ($sep == '') $sep = ',';
				break;
			case 'tsv':
				$rowData .= $sep.ReportOutputter::encodeTSV($dispval);
				if ($sep == '') $sep = "\t";
				break;
			case 'xls':
				$cell = $this->outputter->worksheet->getCellByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
				$cell->setValueExplicit($dispval, PHPExcel_Cell_DataType::TYPE_STRING);
				$style = $this->outputter->worksheet->getStyleByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
				$style->getAlignment()->setHorizontal($col->align);
				break;
			case 'pdf':
				$colWidth = (int)
					(((double)$col->relativeWidth/(double)$totalRelativeWidth) *
					 (double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
				$this->outputter->pdf->Cell(
					$colWidth,
					$this->outputter->getPDFLineSpacing(),
					$dispval,
					0,
					0,
					strtoupper($col->align[0]),
					1
				);
				$this->outputter->pdf->x += $this->pdfColumnSpacing;
				break;
			}

			foreach ($this->levels as $level) {
				if (in_array($colName, $level->uniqueIdColumnNames)) {
					$this->prevIdValues[$colName] = $colval;
					break;
				}
			}
		}
		unset($col);		// Release reference to last element

		switch ($this->outputter->outputFormat) {
		case 'html':
			$rowData .= '</tr>';
			$this->outputter->outputText($rowData);
			break;
		case 'csv':
		case 'tsv':
			$rowData .= "\r\n";
			$this->outputter->outputText($rowData);
			break;
		case 'xls':
			$this->outputter->worksheetRowNum++;
			break;
		case 'pdf':
			$this->outputter->pdf->Ln();
			break;
		}

		$this->accumulateTotals($crow);
	}

	// Output a custom row.  This can be useful for things like section headings.
	// Parameters:
	// $customColumns: An array of ReportCustomColumn instances.
	// $columnTexts: An array of pre-formatte strings to go into the custom columns.
	// $appearance: Controls the appearance of the text (mainly the font).  Must be
	// one of 'heading', 'detail' or 'totals'.  Optional.  Defaults to 'detail'.
	public function outputCustomRow(
		$customColumns,
		$columnTexts = array(),
		$appearance = 'detail') {

		switch ($this->outputter->outputFormat) {
		case 'html':
			switch ($appearance) {
			case 'heading':
				$tdTag = 'th';
				$htmlTRClass = $this->htmlTHeadTRClass;
				$htmlTDClass = $this->htmlTHeadTHClass;
				$htmlTDNoWrap = $this->htmlTHeadTHNoWrap;
				break;
			case 'detail':
			default:
				$tdTag = 'td';
				$htmlTRClass = trim($this->htmlTBodyTRClass.' '.$this->htmlTBodyTREvenClass);
				$htmlTDClass = $this->htmlTBodyTDClass;
				$htmlTDNoWrap = $this->htmlTBodyTDNoWrap;
				break;
			case 'totals':
				$tdTag = 'td';
				$htmlTRClass = $this->htmlTBodyTotalsTRClass;
				$htmlTDClass = $this->htmlTBodyTotalsTDClass;
				$htmlTDNoWrap = $this->htmlTBodyTotalsTDNoWrap;
				break;
			}
			break;
		case 'pdf':
			switch ($appearance) {
			case 'heading':
				$pdfFontFamily = $this->pdfHeadingFontFamily;
				$pdfFontStyle = $this->pdfHeadingFontStyle;
				$pdfFontSize = $this->pdfHeadingFontSize;
				$pdfTextRGB = $this->pdfHeadingTextRGB;
				$pdfBackgroundFill = $this->pdfHeadingBackgroundFill;
				$pdfBackgroundRGB = $this->pdfHeadingBackgroundRGB;
				$pdfBorder = $this->pdfHeadingBorder;
				$pdfBorderRGB = $this->pdfHeadingBorderRGB;
				break;
			case 'detail':
			default:
				$pdfFontFamily = $this->pdfDetailFontFamily;
				$pdfFontStyle = $this->pdfDetailFontStyle;
				$pdfFontSize = $this->pdfDetailFontSize;
				$pdfTextRGB = $this->pdfDetailTextRGB;
				$pdfBackgroundFill = false;
				$pdfBackgroundRGB = $this->pdfDetailBackgroundEvenRGB;
				$pdfBorder = 0;
				$pdfBorderRGB = null;
				break;
			case 'totals':
				$pdfFontFamily = $this->pdfTotalsFontFamily;
				$pdfFontStyle = $this->pdfTotalsFontStyle;
				$pdfFontSize = $this->pdfTotalsFontSize;
				$pdfTextRGB = $this->pdfTotalsTextRGB;
				$pdfBackgroundFill = false;
				$pdfBackgroundRGB = null;
				$pdfBorder = 0;
				$pdfBorderRGB = null;
				break;
			}
			break;
		}

		if ($this->outputter->outputFormat == 'pdf') {
			// Be sure the row will fit on this page; if not (or if the PDF document
			// has not been opened yet), go to next page and output heading.
			if ($this->outputter->pdf === null) {
				$this->outputHeading(true);
			} else {
				$this->outputter->pdf->SetFont(
					$pdfFontFamily,
					$pdfFontStyle,
					$pdfFontSize
				);
				if (($this->outputter->pdf->y + $this->outputter->getPDFLineSpacing()) >
					$this->outputter->pdf->PageBreakTrigger) {
					$this->outputHeading(true);
				}
			}
		}

		// If we need a heading, output it now, but don't force a page break.
		if (!$this->headingOutput) {
			$this->outputHeading();
		}

		$sep = '';
		$rowData = '';
		$colNum = -1;

		if ($this->outputter->outputFormat == 'html') {
			$this->ensureHTMLTableSection(($appearance == 'heading') ? 'thead' : 'tbody');
			$rowData .=
				'<tr'.(($htmlTRClass != '') ? ' class="'.$htmlTRClass.'"' : '').'>';
		}

		if ($this->outputter->outputFormat == 'pdf') {
			$this->outputter->pdf->SetFont($pdfFontFamily, $pdfFontStyle, $pdfFontSize);
			$this->outputter->setPDFRGBTextColor($this->pdfDetailTextRGB);

			$pdfTotalColSpacing = $this->pdfColumnSpacing*(count($this->columns)-1);

			$totalRelativeWidth = 0.0;
			foreach ($this->columns as &$col) $totalRelativeWidth += $col->relativeWidth;
			unset($col);	// Release reference to last element
		}

		// Output columns.
		for ($cci = 0, $ci = 0, $ncc = count($customColumns), $nc = count($this->columns);
			( ($cci < $ncc) && ($ci < $nc) );
			$cci++, $ci += $columnSpan) {

			$text = isset($columnTexts[$cci]) ? $columnTexts[$cci] : '';
			$align = $customColumns[$cci]->align;
			$columnSpan = $customColumns[$cci]->columnSpan;
			if (($ci+$columnSpan) > $nc) $columnSpan = $nc-$ci;
			if ($columnSpan <= 0) break;

			switch ($this->outputter->outputFormat) {
			case 'html':
				$rowData .=
					'<'.$tdTag.
					(($htmlTDClass != '') ? ' class="'.$htmlTDClass.'"' : '').
					(($htmlTDNoWrap != '') ? ' nowrap="'.$htmlTDNoWrap.'"' : '').
					' align="'.$align.'"'.
					(($columnSpan > 1) ? (' colspan="'.$columnSpan.'"') : '').
					'>'.
					((trim($text) != '') ? htmlspecialchars($text) : '&nbsp;').
					'</'.$tdTag.'>';
				break;
			case 'csv':
				$rowData .= $sep.ReportOutputter::encodeCSV($text);
				if ($sep == '') $sep = ',';
				for ($cs = 0; $cs < $columnSpan; $cs++) $rowData .= $sep;
				break;
			case 'tsv':
				$rowData .= $sep.ReportOutputter::encodeTSV($text);
				if ($sep == '') $sep = "\t";
				for ($cs = 0; $cs < $columnSpan; $cs++) $rowData .= $sep;
				break;
			case 'xls':
				$cell = $this->outputter->worksheet->getCellByColumnAndRow($ci, $this->outputter->worksheetRowNum);
				$cell->setValueExplicit($text, PHPExcel_Cell_DataType::TYPE_STRING);
				$style = $this->outputter->worksheet->getStyleByColumnAndRow($ci, $this->outputter->worksheetRowNum);
				$style->getAlignment()->setHorizontal($align);
				if (($appearance == 'heading') || ($appearance == 'totals')) {
					$style->getFont()->setBold(true);
				}
				if ($columnSpan > 0) {
					$this->outputter->worksheet->mergeCellsByColumnAndRow(
						$ci,
						$this->outputter->worksheetRowNum,
						$ci+($columnSpan-1),
						$this->outputter->worksheetRowNum
					);
				}
				break;
			case 'pdf':
				$colWidth = 0;
				for ($ci2 = $ci, $nci = $ci+$columnSpan; $ci2 < $nci; $ci2++) {
					$col2 = &$this->columns[$ci2];
					$colWidth += (int)
						(((double)$col2->relativeWidth/(double)$totalRelativeWidth) *
				 		(double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
					if ($ci2 > $ci) $colWidth += $this->pdfColumnSpacing;
				}
				unset($col2);		// Release reference to last element

				if ($pdfBackgroundFill) {
					$savex = $this->outputter->pdf->x;
					$savey = $this->outputter->pdf->y;
					$this->outputter->setPDFRGBFillColor($pdfBackgroundRGB);
					$this->outputter->pdf->Cell(
						$colWidth,
						$this->outputter->getPDFLineSpacing(),
						'',
						0,
						0,
						'C',
						1
					);
					$this->outputter->pdf->x = $savex;
					$this->outputter->pdf->y = $savey;
				}
				$this->outputter->setPDFRGBDrawColor($pdfBorderRGB);
				$this->outputter->setPDFRGBTextColor($pdfTextRGB);
				$this->outputter->pdf->Cell(
					$colWidth,
					$this->outputter->getPDFLineSpacing(),
					$text,
					$pdfBorder,
					0,
					strtoupper($align[0])
				);
				$this->outputter->pdf->x += $this->pdfColumnSpacing;
				break;
			}
		}

		switch ($this->outputter->outputFormat) {
		case 'html':
			$rowData .= '</tr>';
			$this->outputter->outputText($rowData);
			break;
		case 'csv':
		case 'tsv':
			$rowData .= "\r\n";
			$this->outputter->outputText($rowData);
			break;
		case 'xls':
			$this->outputter->worksheetRowNum++;
			break;
		case 'pdf':
			$this->outputter->pdf->Ln();
			break;
		}

		// Force the next detail row to be odd.
		$this->oddRow = true;
	}

	public function outputAndResetTotals($levelIdx) {
		if ((!$this->outputTotalsInCSVOrTSVFormat) &&
			(($this->outputter->outputFormat == 'csv') ||
			 ($this->outputter->outputFormat == 'tsv'))) {
			return;
		}

		$level = &$this->levels[$levelIdx];
		$levelName = $level->name;
		if (!isset($this->totals[$levelName])) return;
		$tot = $this->totals[$levelName];

		if ($this->outputter->outputFormat == 'pdf') {
			// Be sure the row will fit on this page; if not (or if the PDF document
			// has not been opened yet), go to next page and output heading.
			if ($this->outputter->pdf === null) {
				$this->outputHeading(true);
			} else {
				$this->outputter->pdf->SetFont(
					$this->pdfTotalsFontFamily,
					$this->pdfTotalsFontStyle,
					$this->pdfTotalsFontSize
				);
				if (($this->outputter->pdf->y + $this->outputter->getPDFLineSpacing()) >
					$this->outputter->pdf->PageBreakTrigger) {
					$this->outputHeading(true);
				}
			}
		}

		// If we need a heading, output it now, but don't force a page break.
		if (!$this->headingOutput) {
			$this->outputHeading();
		}

		// Create a copy of $tot into $crow, calculating any calculated fields.
		$crow = new stdClass();
		foreach ($this->columns as &$col) {
			$colName = $col->name;
			if (!in_array($levelName, $col->outputTotalsAtLevels)) continue;

			// If this is a calculated column, call the function to calculate the value.
			// Otherwise, just get the value.
			if ($col->valueCalcCallback !== null) {
				$colval = call_user_func(
					$col->valueCalcCallback,
					$tot,
					$this,
					$col,
					$level
				);
			} else {
				$colval = $tot->$colName;
			}

			$crow->$colName = $colval;
		}
		unset($col);		// Release reference to last element

		$sep = '';
		$rowData = '';
		$colNum = -1;

		if ($this->outputter->outputFormat == 'html') {
			$this->ensureHTMLTableSection('tbody');
			$rowData .=
				'<tr'.
				(($this->htmlTBodyTotalsTRClass != '') ?
					' class="'.$this->htmlTBodyTotalsTRClass.'"' : '').
				'>';
		}

		if ($this->outputter->outputFormat == 'pdf') {
			$this->outputter->pdf->SetFont(
				$this->pdfTotalsFontFamily,
				$this->pdfTotalsFontStyle,
				$this->pdfTotalsFontSize
			);
			$this->outputter->setPDFRGBTextColor($this->pdfTotalsTextRGB);

			$pdfTotalColSpacing = $this->pdfColumnSpacing*(count($this->columns)-1);

			$totalRelativeWidth = 0.0;
			foreach ($this->columns as &$col) $totalRelativeWidth += $col->relativeWidth;
			unset($col);	// Release reference to last element
		}

		// Output totals columns.
		for ($ci = 0, $nc = count($this->columns); $ci < $nc; $ci++) {
			$col = &$this->columns[$ci];
			$colNum++;
			$colName = $col->name;

			if ($colName == $level->totalsDescriptionLeftColumnName) {
				$nTotDescCols = $level->totalsDescriptionColumnSpan;
				switch ($this->outputter->outputFormat) {
				case 'html':
					$rowData .=
						'<td'.
						(($this->htmlTBodyTotalsTDClass != '') ? ' class="'.$this->htmlTBodyTotalsTDClass.'"' : '').
						(($this->htmlTBodyTotalsTDNoWrap != '') ? ' nowrap="'.$this->htmlTBodyTotalsTDNoWrap.'"' : '').
						' align="'.$level->totalsDescriptionAlign.'"'.
						(($nTotDescCols > 1) ? (' colspan="'.$nTotDescCols.'"') : '').
						'>'.htmlspecialchars($level->totalsDescription).':</td>';
					break;
				case 'csv':
					$rowData .= $sep.ReportOutputter::encodeCSV($level->totalsDescription.':');
					if ($sep == '') $sep = ',';
					for ($i = 1; $i < $nTotDescCols; $i++) $rowData .= $sep;
					break;
				case 'tsv':
					$rowData .= $sep.ReportOutputter::encodeTSV($level->totalsDescription.':');
					if ($sep == '') $sep = "\t";
					for ($i = 1; $i < $nTotDescCols; $i++) $rowData .= $sep;
					break;
				case 'xls':
					$cell = $this->outputter->worksheet->getCellByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
					$cell->setValueExplicit($level->totalsDescription.':', PHPExcel_Cell_DataType::TYPE_STRING);
					$style = $this->outputter->worksheet->getStyleByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
					$style->getAlignment()->setHorizontal($level->totalsDescriptionAlign);
					$style->getFont()->setBold(true);
					if ($nTotDescCols > 1) {
						$this->outputter->worksheet->mergeCellsByColumnAndRow(
							$colNum,
							$this->outputter->worksheetRowNum,
							$colNum+($nTotDescCols-1),
							$this->outputter->worksheetRowNum
						);
					}
					break;
				case 'pdf':
					$colWidth = 0;
					for ($ci2 = $ci, $nci = $ci+$nTotDescCols; $ci2 < $nci; $ci2++) {
						$col2 = &$this->columns[$ci2];
						$colWidth += (int)
							(((double)$col2->relativeWidth/(double)$totalRelativeWidth) *
					 		(double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
						if ($ci2 > $ci) $colWidth += $this->pdfColumnSpacing;
					}
					unset($col2);		// Release reference to last element
					$this->outputter->pdf->Cell(
						$colWidth,
						$this->outputter->getPDFLineSpacing(),
						$level->totalsDescription.':',
						0,
						0,
						strtoupper($level->totalsDescriptionAlign[0])
					);
					$this->outputter->pdf->x += $this->pdfColumnSpacing;
					break;
				}
				$ci += ($nTotDescCols-1);
				$colNum += ($nTotDescCols-1);
			} else {	// if ($colName == $level->totalsDescriptionLeftColumnName)
				$isTotCol = in_array($levelName, $col->outputTotalsAtLevels);
				if ($isTotCol) {
					$colval = $crow->$colName;
					switch ($col->format) {
					case 'number':
						if ((($colval < 0) && ($col->suppressNegative)) ||
							(($colval == 0) && ($col->suppressZero)) ||
							(($colval > 0) && ($col->suppressPositive))) {
							$dispval = '';
						} else {
							if ($col->formatCallback !== null) {
								$dispval = call_user_func(
									$col->formatCallback,
									$tot,
									$this,
									$col,
									$level,
									$colval
								);
							} else {
								if (!$col->useThousandsSeparator) {
									$dispval = number_format((double)$colval, $col->decimalPlaces, '.', '');
								} else {
									$dispval = number_format((double)$colval, $col->decimalPlaces);
								}
							}
						}
						break;
					default:
						if ($col->formatCallback !== null) {
							$dispval = call_user_func(
								$col->formatCallback,
								$tot,
								$this,
								$col,
								$level,
								$colval
							);
						} else {
							$dispval = $colval;
						}
						break;
					}
				} else {
					$dispval = '';
				}

				switch ($this->outputter->outputFormat) {
				case 'html':
					$rowData .=
						'<td'.
						(($this->htmlTBodyTotalsTDClass != '') ? ' class="'.$this->htmlTBodyTotalsTDClass.'"' : '').
						(($this->htmlTBodyTotalsTDNoWrap != '') ? ' nowrap="'.$this->htmlTBodyTotalsTDNoWrap.'"' : '').
						' align="'.$col->align.'">'.
						((trim($dispval) != '') ? htmlspecialchars($dispval) : '&nbsp;').
						'</td>';
					break;
				case 'csv':
					$rowData .= $sep.ReportOutputter::encodeCSV($dispval);
					if ($sep == '') $sep = ',';
					break;
				case 'tsv':
					$rowData .= $sep.ReportOutputter::encodeTSV($dispval);
					if ($sep == '') $sep = "\t";
					break;
				case 'xls':
					if (!$isTotCol) break;
					$cell = $this->outputter->worksheet->getCellByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
					$cell->setValueExplicit($dispval, PHPExcel_Cell_DataType::TYPE_STRING);
					$style = $this->outputter->worksheet->getStyleByColumnAndRow($colNum, $this->outputter->worksheetRowNum);
					$style->getAlignment()->setHorizontal($col->align);
					$style->getFont()->setBold(true);
					break;
				case 'pdf':
					$colWidth = (int)
						(((double)$col->relativeWidth/(double)$totalRelativeWidth) *
					 	(double)($this->outputter->getPDFPageWidth()-$pdfTotalColSpacing));
					$this->outputter->pdf->Cell(
						$colWidth,
						$this->outputter->getPDFLineSpacing(),
						$dispval,
						0,
						0,
						strtoupper($col->align[0])
					);
					$this->outputter->pdf->x += $this->pdfColumnSpacing;
					break;
				}
			}	// if ($colName == $level->totalsDescriptionLeftColumnName) ... else
		}
		unset($col);		// Release reference to last element

		switch ($this->outputter->outputFormat) {
		case 'html':
			$rowData .= '</tr>';
			$this->outputter->outputText($rowData);
			break;
		case 'csv':
		case 'tsv':
			$rowData .= "\r\n";
			$this->outputter->outputText($rowData);
			break;
		case 'xls':
			$this->outputter->worksheetRowNum++;
			break;
		case 'pdf':
			$this->outputter->pdf->Ln();
			break;
		}

		// Reset totals for this level by unsetting the object.
		unset($this->totals[$levelName]);

		// Force the next detail row to be odd.
		$this->oddRow = true;
	}

	public function resetTotalsForLevelIdx($levelIdx) {
		// Reset totals for this level by unsetting the object.
		unset($this->totals[$this->levels[$levelIdx]->name]);
	}

	public function resetTotalsForLevelName($levelName) {
		// Reset totals for this level by unsetting the object.
		unset($this->totals[$levelName]);
	}

	public function finish() {
		if (!$this->headingOutput) {
			// If we need a heading, output it now, but don't force a page break.
			$this->outputHeading();
		}

		// Output pending totals.
		for ($levelIdx = count($this->levels)-1; $levelIdx >= 0; $levelIdx--) {
			$this->outputAndResetTotals($levelIdx);
		}

		// Output the end of the report.
		if ($this->outputter->outputFormat == 'html') {
			$this->ensureHTMLTableClosed();
		}
	}

	protected function accumulateTotals($row) {
		if ((!$this->outputTotalsInCSVOrTSVFormat) &&
			(($this->outputter->outputFormat == 'csv') ||
			 ($this->outputter->outputFormat == 'tsv'))) {
			return;
		}

		foreach ($this->columns as $col) {
			$colName = $col->name;
			foreach ($col->outputTotalsAtLevels as $levelName) {
				if (!isset($this->totals[$levelName])) $this->totals[$levelName] = new stdClass();
				$this->totals[$levelName]->$colName = round(
					(isset($this->totals[$levelName]->$colName) ?
						$this->totals[$levelName]->$colName : 0.0) +
					(isset($row->$colName) ? $row->$colName : 0.0),
					$col->decimalPlaces
				);
			}
		}
	}

	protected function ensureHTMLTableOpen() {
		if (($this->outputter->outputFormat == 'html') && (!$this->htmlTableOpen)) {
			$this->htmlTableOpen = true;
			if (($this->outputter->outputFormat == 'html') &&
				($this->outputCompleteHTMLDocument)) {
				$this->outputter->outputText(
					'<html><head><title>'.htmlspecialchars($this->title).
					'</title></head><body>'
				);
			}
			$html = '<table';
			if ($this->htmlTableBorder != '') {
				$html .= ' border="'.$this->htmlTableBorder.'"';
			}
			if ($this->htmlTableCellSpacing != '') {
				$html .= ' cellspacing="'.$this->htmlTableCellSpacing.'"';
			}
			if ($this->htmlTableCellPadding != '') {
				$html .= ' cellpadding="'.$this->htmlTableCellPadding.'"';
			}
			if ($this->htmlTableClass != '') {
				$html .= ' class="'.$this->htmlTableClass.'"';
			}
			$html .= '>';
			$this->outputter->outputText($html);
		}
	}

	protected function ensureHTMLTableClosed() {
		if (($this->outputter->outputFormat == 'html') && ($this->htmlTableOpen)) {
			$this->ensureHTMLTableSection(null);
			$this->outputter->outputText('</table>');
			if (($this->outputter->outputFormat == 'html') &&
				($this->outputCompleteHTMLDocument)) {
				$this->outputter->outputText('</body></html>');
			}
			$this->htmlTableOpen = false;
		}
	}

	protected function ensureHTMLTableSection($section) {
		if ($this->outputter->outputFormat == 'html') {
			switch ($section) {
			case 'thead':
				$this->ensureHTMLTableOpen();
				if ($this->htmlTableSection != $section) {
					$this->ensureHTMLTableSection(null);
					$this->outputter->outputText(
						'<thead'.
						(($this->htmlTHeadClass != '') ? ' class="'.$this->htmlTHeadClass.'"' : '').
						'>'
					);
					$this->htmlTableSection = $section;
				}
				break;
			case 'tbody':
				$this->ensureHTMLTableOpen();
				if ($this->htmlTableSection != $section) {
					$this->ensureHTMLTableSection(null);
					$this->outputter->outputText(
						'<tbody'.
						(($this->htmlTBodyClass != '') ? ' class="'.$this->htmlTBodyClass.'"' : '').
						'>'
					);
					$this->htmlTableSection = $section;
				}
				break;
			default:
				if (($this->htmlTableOpen) &&
					($this->htmlTableSection !== null) &&
					($this->htmlTableSection != '')) {
					$this->outputter->outputText('</'.$this->htmlTableSection.'>');
				}
				$this->htmlTableSection = null;
				break;
			}
		}
	}
}

class ReportOutputter {
	// This array can be read externally.  It is an array of objects, each of which describes
	// an available output format and contains the following attributes:
	//     format: The output format.  This value should be passed to the setOutputFormat()
	//         function if this output format is desired.
	//     mimeType: The MIME type (Content-Type header) for this output format.
	//     extension: The file extension (filetype) for this output format, including the dot.
	//     description: The human-readable description for this output format.
	public static $OUTPUT_FORMATS;

	// This array can be read externally.  It is an array of objects, each of which describes
	// an available PDF page format and contains the following attributes:
	//     format: The page format.  This value should be assigned to the $pdfPageFormat
	//         attribute if this page format is desired.
	//     description: The human-readable description for this page format.
	public static $PDF_PAGE_FORMATS;

	// This array can be read externally.  It is an array of objects, each of which describes
	// an available PDF page orientation and contains the following attributes:
	//     orientation: The page orientation.  This value should be assigned to the
	//         $pdfPageOrientation attribute if this page orientation is desired.
	//     description: The human-readable description for this page orientation.
	public static $PDF_PAGE_ORIENTATIONS;

	private static $CSV_ENCODE_FROM = array('"', "\r", "\n");
	private static $CSV_ENCODE_TO = array('""', '', '');
	private static $TSV_ENCODE_FROM = array("\t", "\r", "\n");
	private static $TSV_ENCODE_TO = array('', '', '');

	protected $workbookFilename = null;
	protected $workbookUseTempFileForOutput = true;
	public $workbook = null;
	public $worksheet = null;
	public $worksheetRowNum = 1;

	protected $pdfFilename = null;
	protected $pdfUseTempFileForOutput = true;
	protected $pdfFilePointer = false;
	public $pdf = null;

	// Page format for PDF document.
	// Only used for PDF output format.
	// One of 'a3', 'a4', 'a5', 'letter', or 'legal'.
	// Must be equal to the 'format' attribute of one of the $PDF_PAGE_FORMATS entries.
	// Defaults to 'letter'.
	public $pdfPageFormat = 'letter';
	// Page orientation for PDF document.
	// Only used for PDF output format.
	// Must be equal to the 'orientation' attribute of one of the $PDF_PAGE_ORIENTATIONS entries.
	// Defaults to 'P'.
	public $pdfPageOrientation = 'P';

	public $pageNumber = 0;

	// The output format.  Must be equal to the 'format' attribute of
	// one of the output formats listed in ReportOutputter::$OUTPUT_FORMATS.
	public $outputFormat = 'html';
	// The output stream, if writing to a stream.
	public $outputStream = false;
	// The output data, if not writing to a stream.
	public $output = '';

	// Given an output format name, return the element in the $OUTPUT_FORMATS
	// array whose 'format' attribute matches the specified format name.
	// Returns null if an invalid format name was specified.
	public static function getOutputFormatForFormatName($formatName) {
		foreach (ReportOutputter::$OUTPUT_FORMATS as $of) {
			if ($of->format == $formatName) return $of;
		}
		return null;
	}

	// Set the output format.
	// This must be called before the first call to outputRow() within a report.
	// Do not change the output format when a report is in progress.  Instead, wait
	// until after finish() has been called.
	// Parameters:
	// $outputFormat: The format attribute of one of the ReportOutputter::$OUTPUT_FORMATS output formats.
	public function setOutputFormat($outputFormat) {
		if (ReportOutputter::getOutputFormatForFormatName($outputFormat) === null) {
			$outputFormat = ReportOutputter::$OUTPUT_FORMATS[0]->format;
		}
		$this->outputFormat = $outputFormat;
	}

	// Set the output stream, if outputting to a stream.
	// If outputting to a stream, this must be called before the first call to outputRow()
	// within a report.
	// Do not change the output stream when a report is in progress.  Instead, wait
	// until after finish() has been called.
	// The output stream is IGNORED when outputting XLS or PDF format.
	// Use setWorkbookFilename() or setPDFFilename() instead.
	// Parameters:
	// $outputStream: An output stream, as returned by fopen(), or false or null to simply
	//     accumulate the output into $this->output.
	public function setOutputStream($outputStream) {
		$this->outputStream = $outputStream;
	}

	// Set the workbook filename, for outputting XLS format to a file.
	// If outputting to a workbook, this must be called before the first call to outputRow()
	// within a report.
	// Do not change the output file when a report is in progress.  Instead, wait
	// until after finish() has been called.
	// The workbook file is ONLY USED when outputting XLS format.
	// If a non-empty workbook filename is set, the workbook will be output to the specified file
	// and the $output variable will NOT be populated with the raw XLS data.
	// Parameters:
	// $workbookFilename: The filename of the XLS file to which the output will be written.
	public function setWorkbookFilename($workbookFilename) {
		$this->workbookFilename = $workbookFilename;
		$this->workbookUseTempFileForOutput = ($workbookFilename == '') ? true : false;
	}

	// Set the PDF filename, for outputting PDF format to a file.
	// If outputting in PDF format, this must be called before the first call to outputRow()
	// within a report.
	// Do not change the output file when a report is in progress.  Instead, wait
	// until after finish() has been called.
	// The PDF file is ONLY USED when outputting PDF format.
	// If a non-empty PDF filename is set, the PDF document will be output to the specified file
	// and the $output variable will NOT be populated with the raw PDF data.
	// Parameters:
	// $pdfFilename: The filename of the PDF file to which the output will be written.
	public function setPDFFilename($pdfFilename) {
		$this->pdfFilename = $pdfFilename;
		$this->pdfUseTempFileForOutput = ($pdfFilename == '') ? true : false;
	}

	private static function parseRGBToArray($rgb) {
		$pieces = explode(',', $rgb);
		$n = count($pieces);
		for ($i = 0; $i < $n; $i++) $pieces[$i] = (double)$pieces[$i];
		while ($n < 3) {
			$pieces[$n] = 0.0;
			$n++;
		}
		if ($n > 3) $pieces = array_slice($pieces, 0, 3);
		return $pieces;
	}

	public function setPDFRGBFillColor($rgb) {
		$pieces = ReportOutputter::parseRGBToArray($rgb);
		$this->pdf->SetFillColor($pieces[0]*255.0, $pieces[1]*255.0, $pieces[2]*255.0);
	}

	public function setPDFRGBDrawColor($rgb) {
		$pieces = ReportOutputter::parseRGBToArray($rgb);
		$this->pdf->SetDrawColor($pieces[0]*255.0, $pieces[1]*255.0, $pieces[2]*255.0);
	}

	public function setPDFRGBTextColor($rgb) {
		$pieces = ReportOutputter::parseRGBToArray($rgb);
		$this->pdf->SetTextColor($pieces[0]*255.0, $pieces[1]*255.0, $pieces[2]*255.0);
	}

	public function getPDFPageWidth() {
		return $this->pdf->w - ($this->pdf->lMargin + $this->pdf->rMargin);
	}

	public function getPDFLineSpacing() {
		return (int)($this->pdf->FontSize*1.25);
	}

	public function reset() {
		$this->outputStream = false;
		$this->output = '';

		if (($this->workbookFilename !== null) && ($this->workbookUseTempFileForOutput)) {
			@unlink($this->workbookFilename);
		}
		$this->workbookFilename = null;
		$this->workbookUseTempFileForOutput = true;
		$this->workbook = null;
		$this->worksheet = null;
		$this->worksheetRowNum = 1;

		if (($this->pdfFilename !== null) && ($this->pdfUseTempFileForOutput)) {
			@unlink($this->pdfFilename);
		}
		$this->pdfFilename = null;
		$this->pdfUseTempFileForOutput = true;
		$this->pdf = null;

		$this->pageNumber = 0;
	}

	public function createWorkbook() {
		if (($this->workbook === null) && ($this->outputFormat == 'xls')) {
			if ($this->workbookUseTempFileForOutput) {
				$this->workbookFilename = tempnam('/tmp', 'rpt');
			}
			$this->workbook = new PHPExcel();
			$this->workbook->setActiveSheetIndex(0);
			$this->worksheet = $this->workbook->getActiveSheet();
		}
	}

	public function createPDF() {
		if ( ($this->pdf === null) && ($this->outputFormat == 'pdf') ) {
			if ($this->pdfUseTempFileForOutput) {
				$this->pdfFilename = tempnam('/tmp', 'rpt');
			}
			$this->pdf = new FPDF(
				$this->pdfPageOrientation,
				'pt',
				$this->pdfPageFormat
			);
			$this->pdf->AutoPageBreak = false;
		}
		$this->pdfFilePointer = fopen($this->pdfFilename, 'wb');
	}

	public function flushPDF() {
		if ($this->pdfFilePointer !== false) {
			if ($this->pdf !== null) {
				$data = $this->pdf->buffer;
				$this->pdf->buffer = '';
				if ($data != '') {
					fwrite($this->pdfFilePointer, $data, strlen($data));
				}
			}
		}
	}

	public function outputText($text) {
		if ($text != '') {
			if ($this->outputStream !== false) {
				fwrite($this->outputStream, $text);
			} else {
				$this->output .= $text;
			}
		}
	}

	public function finish() {
		switch ($this->outputFormat) {
		case 'xls':
			$xlswriter = new PHPExcel_Writer_Excel5($this->workbook);
			$xlswriter->save($this->workbookFilename);
			if ($this->workbookUseTempFileForOutput) {
				$this->output = file_get_contents($this->workbookFilename);
				@unlink($this->workbookFilename);
			}
			break;
		case 'pdf':
			if ($this->pdfFilePointer !== false) {
				if ($this->pdf !== null) $this->pdf->Close();
				$this->flushPDF();
				fclose($this->pdfFilePointer);
				$this->pdfFilePointer = false;
			}
			if ($this->pdfUseTempFileForOutput) {
				$this->output = file_get_contents($this->pdfFilename);
				@unlink($this->pdfFilename);
			}
			break;
		}
	}

	public static function encodeCSV($text) {
		$escapedText =
			'"'.
			str_replace(
				ReportOutputter::$CSV_ENCODE_FROM,
				ReportOutputter::$CSV_ENCODE_TO,
				$text
			).
			'"';
		return $escapedText;
	}

	public static function encodeTSV($text) {
		$escapedText = str_replace(
			ReportOutputter::$TSV_ENCODE_FROM,
			ReportOutputter::$TSV_ENCODE_TO,
			$text
		);
		return $escapedText;
	}
}
ReportOutputter::$OUTPUT_FORMATS = array(
	(object)array('format'=>'html', 'mimeType'=>'application/octet-stream', 'extension'=>'.html', 'description'=>'HTML'),
	(object)array('format'=>'csv', 'mimeType'=>'text/csv', 'extension'=>'.csv', 'description'=>'Comma-Separated Values'),
	(object)array('format'=>'tsv', 'mimeType'=>'text/tab-separated-values', 'extension'=>'.tsv', 'description'=>'Tab-Separated Values'),
	(object)array('format'=>'xls', 'mimeType'=>'application/vnd.ms-excel', 'extension'=>'.xls', 'description'=>'XLS Spreadsheet'),
	(object)array('format'=>'pdf', 'mimeType'=>'application/pdf', 'extension'=>'.pdf', 'description'=>'Portable Document Format (PDF)'),
);

ReportOutputter::$PDF_PAGE_FORMATS = array(
	(object)array('format'=>'a3', 'description'=>'A3'),
	(object)array('format'=>'a4', 'description'=>'A4'),
	(object)array('format'=>'a5', 'description'=>'A5'),
	(object)array('format'=>'letter', 'description'=>'Letter'),
	(object)array('format'=>'legal', 'description'=>'Legal'),
);

ReportOutputter::$PDF_PAGE_ORIENTATIONS = array(
	(object)array('orientation'=>'P', 'description'=>'Portrait'),
	(object)array('orientation'=>'L', 'description'=>'Landscape'),
);
