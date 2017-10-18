<?php
// THIS FILE IS PART OF THE phpchartgen PACKAGE.  DO NOT EDIT.
// THIS FILE GETS RE-WRITTEN EACH TIME THE UPSTREAM PACKAGE IS UPDATED.
// ANY MANUAL EDITS WILL BE LOST.

// Chart.class.php
// Copyright (c) 2011 engenic - Vern Baker
// All rights reserved.
// This software is released under the BSD license.
// Please see the accompanying LICENSE.txt for details.

class ReportFont
{
	public $textColor	= '000000'; 			// text-color.
	public $textFont	= './font/arial.ttf';	// Font file name;
	public $fontSize	= 10;					// Size of font
	public $fontAngle	= 0;					// Angle of font
	
	// Create an instance of the Font
	public function __construct($params) {
		$this->textColor 	= isset($params['textColor']) ? (string)$params['textColor']	: $this->textColor;
		$this->textFont		= isset($params['textFont']) ? (string)$params['textFont'] 		: $this->textFont;
		$this->fontSize 	= isset($params['fontSize']) ? (integer)$params['fontSize'] 	: $this->fontSize;
		$this->fontAngle 	= isset($params['fontAngle']) ? (integer)$params['fontAngle']	: $this->fontAngle;
	}
} // ReportFont


// Define the image
class ReportImage
{
	public $imgFile			= '';
	public $displayTrue 	= true;
	public $imgWidth		= 599;
	public $imgHeight		= 299;
	public $shadowDrop		= 12;
	public $shadowDark		= true;
	public $borderColor		= 'CCD6E0';
	public $outlineColor	= 'EFEFEF';
	public $gridlineColor	= 'DDDDDD';
	public $baselineColor	= '000000';
	public $barColor		= '5CA4FF';
	public $backgroundColor	= 'FFFFFF';

	public $img;
	
	// Create an instance of the Image
	public function __construct($params) 
	{
	
		$this->imgFile			= isset($params['imgFile']) 		? (string)$params['imgFile'] : 			$this->$imgFile;
		$this->displayTrue		= isset($params['displayTrue']) 	? (boolean)$params['displayTrue'] : 	$this->displayTrue;
		$this->imgWidth 		= isset($params['imgWidth']) 		? (integer)$params['imgWidth'] : 		$this->imgWidth;
		$this->imgHeight 		= isset($params['imgHeight']) 		? (integer)$params['imgHeight'] : 		$this->imgHeight;
		$this->shadowDrop 		= isset($params['shadowDrop']) 		? (integer)$params['shadowDrop'] : 		$this->shadowDrop;
		$this->shadowDark 		= isset($params['shadowDark']) 		? (boolean)$params['shadowDark'] : 		$this->shadowDark;
		$this->borderColor 		= isset($params['borderColor']) 	? (string)$params['borderColor'] : 		$this->borderColor;
		$this->outlineColor 	= isset($params['outlineColor']) 	? (string)$params['outlineColor'] : 	$this->outlineColor;
		$this->gridlineColor 	= isset($params['gridlineColor']) 	? (string)$params['gridlineColor'] : 	$this->gridlineColor;
		$this->baselineColor 	= isset($params['baselineColor']) 	? (string)$params['baselineColor'] : 	$this->baselineColor;
		$this->barColor 		= isset($params['barColor']) 		? (string)$params['barColor'] : 		$this->barColor;
		$this->backgroundColor 	= isset($params['backgroundColor']) ? (string)$params['backgroundColor'] : 	$this->backgroundColor;
		
		// Create the report image				
		$this->img = ImageCreateTrueColor($this->imgWidth, $this->imgHeight);
		// -- Put the border on it
		ImageFill($this->img, 0, 0, chartColorHex($this->img, $this->borderColor));
	}

	public function finish() 
	{
		ImageDestroy($this->$img);
	}

}  // ReportImage


class ReportChartOutputter
{
	public static $OUTPUT_CHART_FORMATS;

	// OBJECTS passed in
	// Image details 
	public	$chartImage;
	// Font details 
	public	$fontText;
	public	$fontLabel;
	public	$fontGroup;

	
	// Other properties
	public $outputStream = false;
	public $output = '';
	
	
	// FIND COLORS with these resources:
	// -- http://www.colorcombos.com/combotester.html
	// -- http://kuler.adobe.com
	
	// Properties of the graphic
	public	  $chartType		= 'pie';	// Provide a uniq file name here
	protected $displayTrue		= true;		// Output the graphic to the screen
	protected $values			= array();	// Values with labels (keys)
	protected $groupNames		= array();	// Names of groups
	protected $groupSize		= 0;
	protected $barWidth			= 30;		// Bar width
	protected $marginSize		= 12;		// Margin
	protected $yAxisMod			= 5;		// Mod display
	// Standard
	//protected $colors			= array('003366','CCD6E0','7F99B2','F7EFC6','C6BE8C','CC6600','990000','520000','BFBFC1','808080','AAAAAA'); // colors of the slices.
	// Fun colors
	//protected $colors			= array('F9FF9A','FFD48E','FFA28C','FF709C','FF6FEA','D076FF','8C70FF','809FFF','70D3FF','C7FF8E','FFC720','FF3DED'); // colors of the slices.
	// Strong colors
	//protected $colors			= array('FF0000','99FF00','B27D00','CC0099','FFCC00','006B6B','6B006B','0033CC','FF6600','008E00','00CC00','660099','FF9900','009999','FFFF00'); // colors of the slices.
	protected $colors			= array('EFD279','95CBE9','024769','AFD775','2C5700','DE9D7F','2B3E42','77BED2','D5E1DD','E2DC7C','F43E71'); // colors of the slices.
	protected $showLabel		= true;
	protected $showPercent		= true;
	protected $showText			= true;
	protected $showParts		= true;		
	protected $labelsOnPie		= true;
	protected $stripNonLetters	= false;
	protected $bulletForm		= 'square';
	protected $version			= '1.00';

	// $ReportChartOutputter: A ReportChartOutputter instance which will handle the output functionality.
	// Each of the passed objects are components needed to complete the chart
	public function __construct(&$chartImage, &$fontText, &$fontLabel, &$fontGroup) {
		$this->chartImage 	= &$chartImage;
		$this->fontText 	= &$fontText;
		$this->fontLabel 	= &$fontLabel;
		$this->fontGroup 	= &$fontGroup;
	}
	
	public function generateChart($params)
	{
		$this->chartType		= isset($params['chartType']) 		? (string)$params['chartType'] 			: $this->chartType		;
		$this->displayTrue		= isset($params['displayTrue']) 	? (boolean)$params['displayTrue'] 		: $this->displayTrue	;
		$this->values			= isset($params['values']) 			? 			$params['values'] 			: $this->values			;
		$this->groupNames		= isset($params['groupNames'])		? 			$params['groupNames'] 		: $this->groupNames		;
		$this->groupSize		= isset($params['groupSize']) 		? (integer)$params['groupSize']			: $this->groupSize		;
		$this->barWidth			= isset($params['barWidth']) 		? (integer)$params['barWidth']			: $this->barWidth		;
		$this->marginSize		= isset($params['marginSize']) 		? (integer)$params['marginSize']		: $this->marginSize		;
		$this->yAxisMod			= isset($params['yAxisMod']) 		? (integer)$params['yAxisMod'] 			: $this->yAxisMod		;
		$this->colors			= isset($params['colors']) 			? 			$params['colors']			: $this->colors			;
		$this->showLabel		= isset($params['showLabel']) 		? (boolean)$params['showLabel']			: $this->showLabel		;
		$this->showPercent		= isset($params['showPercent']) 	? (boolean)$params['showPercent']		: $this->showPercent	;
		$this->showText			= isset($params['showText']) 		? (boolean)$params['showText']			: $this->showText		;
		$this->showParts		= isset($params['showParts']) 		? (boolean)$params['showParts']			: $this->showParts		;
		$this->labelsOnPie		= isset($params['labelsOnPie']) 	? (boolean)$params['labelsOnPie']		: $this->labelsOnPie	;
		$this->stripNonLetters	= isset($params['stripNonLetters']) ? (boolean)$params['stripNonLetters']	: $this->stripNonLetters	;
		$this->bulletForm		= isset($params['bulletForm']) 		? (string)$params['bulletForm']			: $this->bulletForm		;
		$this->version			= isset($params['version']) 		? (string)$params['version']			: $this->version		;
		
		// Get text length details about the label - $data
		for ($i = 0; $i < count($this->values); $i++) 
		{
			// Get each value set for the list
			list($key,$value)=each($this->values); 
			
			if ($value/array_sum($this->values) < 0.1) $number[$i] = ' '.number_format(($value/array_sum($this->values))*100,1,'.','.').'%';
			else $number[$i] = number_format(($value/array_sum($this->values))*100,1,'.','.').'%';
			if (strlen($value) > $text_length) $text_length = strlen($key);
		}

		/*
		// Details of the display for labels
		$antal_label = count($this->values);
		$xtra = (5+15*$antal_label)-($height+ceil($shadow_drop));
		if ($xtra > 0) $xtra_height = (5+15*$antal_label)-($height+ceil($shadow_drop));
		// +ceil($shadow_drop)+$xtra_height
		*/
			

		
		// Determine the colours that will be used
		if ($this->colors)
		{
			foreach ($this->colors as $colorkode) 
			{
				$fill_color[] 	= chartColorHex($this->chartImage->img, $colorkode);
				$shadow_color[] = chartColorHexshadow($this->chartImage->img, $colorkode, $this->chartImage->shadowDark);
			}
		}
		else
		{
			{
				// If no colours were passed, go with the default
				$fill_color[] 	= chartColorHex($this->chartImage->img, $this->chartImage->barColor);
				$shadow_color[] = chartColorHexshadow($this->chartImage->img, $this->chartImage->barColor, $this->chartImage->shadowDark);		
			}
		
		}
 
		// Space for the legend 
		$mSpaceSize = 5;

		// Loop through, and get size details about the text to be written
		$max_text_height   = 0; // Will be used to describe the box as well
		$max_label_width   = 0;
		$max_percent_width = 0;	
		
		
		if ($this->chartType == 'pie')
		{
			////////////////////////
			// Pie Chart
			////////////////////////
			
			reset($this->values);
			for ($i = 0; $i < count($this->values); $i++) 
			{
				// Get each value set for the list
				list($key,$value)=each($this->values); 

				
				$label_output = '';
				if ($this->showText) $label_output = $label_output.$key.' ';
				if ($this->showParts) $label_output = $label_output.$value;
				
				// Display off the page somewhere, and then capture
				$text_details = imagettftext($this->chartImage->img, 
											$this->fontLabel->fontSize, 
											0, // angle
											-10000, -10000, 
											chartColorHex($this->chartImage->img, $this->fontLabel->textColor), 
											$this->fontLabel->textFont, 
											$label_output);

				// Height of Key
				$mHeight = $text_details[1] - $text_details[7];
				if ($mHeight > $max_text_height)
				{
					$max_text_height = $mHeight;
				}
				// Width of Key
				$mWidth = $text_details[2] - $text_details[0]; // Right side - left side
				if ($mWidth > $max_label_width)
				{
					$max_label_width = $mWidth;
				}

				
				// Only if we show percent
				if ($this->showPercent)
				{
					// Details about the percentage display		
					$text_details = imagettftext($this->chartImage->img, 
												$this->fontText->fontSize, 
												0, 
												-10000,-10000, 
												chartColorHex($this->chartImage->img, 
															$this->fontText->textColor), 
												$this->fontText->textFont, 
												$number[$i]);
												
					// Width of Key
					$mWidth = $text_details[2] - $text_details[0];  // Right side - left side
					if ($mWidth > $max_percent_width)
					{
						$max_percent_width = $mWidth;
					}
					// Save the width
					$number_width[$i] = $mWidth;		
				}
				
			} // for 
			
			// Space for the legend 
			$mSpaceSize = 5;
			// -- How much space is the legend going to take
			$legend_width = 10 +  					// margin
							$max_text_height + 		// box size
							$mSpaceSize +			// Space between that
							$max_percent_width +	// Percent description
							$mSpaceSize +			// Space between that
							$max_label_width;		// Description
			
			
			// Determine where to start drawing
			$width = $this->chartImage->imgWidth - $legend_width;
			
			// Outline (where the text is)
			imagefilledrectangle($this->chartImage->img, 1, 1, 
										$this->chartImage->imgWidth-2, 
										$this->chartImage->imgHeight-2, 
										chartColorHex($this->chartImage->img, $this->chartImage->outlineColor));
			
			// Background 
			imagefilledrectangle($this->chartImage->img, 1, 1,	
										$width,		
										$this->chartImage->imgHeight-2, 
										chartColorHex($this->chartImage->img, $this->chartImage->backgroundColor));

			// Label and percent text drawing
			$label_place = 5; // Y
			reset($this->values);
			for ($i = 0; $i < count($this->values); $i++) 
			{
				// Get each value set for the list
				list($key,$value)=each($this->values); 

				if ($this->bulletForm == 'round' && $this->showLabel  && $value > 0)
				{
					imagefilledellipse($this->chartImage->img,	
										$width+$max_text_height/2+$mSpaceSize/2, 
										$label_place+$max_text_height/2, 
										$max_text_height, 
										$max_text_height, 
										chartColorHex($this->chartImage->img, $this->colors[$i % count($this->colors)]));
										
					imageellipse($this->chartImage->img,			
										$width+$max_text_height/2+$mSpaceSize/2, 
										$label_place+$max_text_height/2, 
										$max_text_height, 
										$max_text_height, 
										chartColorHex($this->chartImage->img, $this->fontText->textColor));
				}
				else if ($this->bulletForm == 'square' && $this->showLabel && $value > 0)
				{	
					imagefilledrectangle($this->chartImage->img,	
										$width+$mSpaceSize, 
										$label_place, 
										$width+$max_text_height+$mSpaceSize-2, 
										$label_place+$max_text_height-2, 
										chartColorHex($this->chartImage->img, $this->colors[$i % count($this->colors)]));
										
					imagerectangle($this->chartImage->img,		
										$width+$mSpaceSize, 
										$label_place, 
										$width+$max_text_height+$mSpaceSize-2, 
										$label_place+$max_text_height-2, 
										chartColorHex($this->chartImage->img, $this->fontText->textColor));
				}

				if ($value > 0)
				{
					$mPlaceX = $width + $max_text_height + $mSpaceSize + 2;  // Bump along with a little space
				
					// Put each part down as needed
					if ($this->showPercent)
					{
						$mPlaceX = $mPlaceX + $max_percent_width - $number_width[$i];
						imagettftext($this->chartImage->img, 
									$this->fontText->fontSize, 
									0, 
									$mPlaceX, 
									$label_place+10, 
									chartColorHex($this->chartImage->img, $this->fontText->textColor), 
									$this->fontText->textFont, $number[$i]);	
					}
					
					$label_output = '';
					if ($this->showText) $label_output = $label_output.$key.' ';
					if ($this->showParts) $label_output = $label_output.$value;

					
					// Draw out the font for the label
					$mPlaceX = $width + $max_text_height + $mSpaceSize + $max_percent_width + $mSpaceSize;
					imagettftext($this->chartImage->img, $this->fontLabel->fontSize, 0, $mPlaceX, $label_place+10, 
									chartColorHex($this->chartImage->img, $this->fontLabel->textColor), $this->fontLabel->textFont, $label_output);		


					// Bump down to the next line based on the font height
					$label_place = $label_place + ($max_text_height) + 5;  // 15;
				}
			}
			
			$centerX = round($width/2);
			$centerY = round(($this->chartImage->imgHeight - $this->chartImage->shadowDrop)/2);
			$diameterX = $width-4;
			$diameterY = $this->chartImage->imgHeight - $this->chartImage->shadowDrop -4;

			reset($this->values);

			$data_sum = array_sum($this->values);
			$start = 270;
			$counter = 0;
			$value_counter= 0;
			
			
			// Determine the slices
			for ($i = 0; $i < count($this->values); $i++) 
			{
				// Get each value set for the list
				list($key,$value)=each($this->values); 

				$counter += $value;
				$end = ceil(($counter/$data_sum)*360) + 270;
				$slice[] = array($start, $end, $shadow_color[$value_counter % count($shadow_color)], $fill_color[$value_counter % count($fill_color)]);
				$start = $end;
				$value_counter++;
			}



			// Draw the shadow
			for ($i=$centerY+$this->chartImage->shadowDrop; $i>$centerY; $i--) 
			{
				// How many pixels deep?
				for ($j = 0; $j < count($slice); $j++)
				{
					if ($slice[$j][0] != $slice[$j][1])
					{
						ImageFilledArc($this->chartImage->img, $centerX, $i, $diameterX, $diameterY, $slice[$j][0], $slice[$j][1], $slice[$j][2], IMG_ARC_PIE);
					}
				}
			}	

			// Draw each slice
			for ($j = 0; $j < count($slice); $j++)
			{
				if ($slice[$j][0] != $slice[$j][1]) 
				{
					ImageFilledArc($this->chartImage->img, $centerX, $centerY, $diameterX, $diameterY, $slice[$j][0], $slice[$j][1], $slice[$j][3], IMG_ARC_PIE);
				}
			}

			
		}
		else
		{
			////////////////////////
			// Bar Chart
			////////////////////////
			
			// Max value is required to adjust the scale
			$max_value		= max($this->values);

			// Find the size of graph by substracting the size of borders
			$graph_width	= $this->chartImage->imgWidth 	- $this->marginSize * 2;
			$graph_height	= $this->chartImage->imgHeight	- $this->marginSize * 2; 
		 
			$total_bars=count($this->values);
			$gap= ($graph_width- $total_bars * $this->barWidth ) / ($total_bars +1);
			
			
			// Determine the size of the on the X Axis Labels
			reset($this->values);
			for ($i = 0; $i < count($this->values); $i++) 
			{
				// Get each value set for the list
				list($key,$this->value)=each($this->values); 

				
				// Display off the page somewhere, and then capture
				$text_details = imagettftext($this->chartImage->img, 
											$this->fontLabel->fontSize, 
											$this->fontLabel->fontAngle, 
											-10000, -10000, 
											chartColorHex($this->chartImage->img, $this->fontLabel->textColor), 
											$this->fontLabel->textFont, $key);
				//echo ('<br />');
				//print_r($text_details);
				
				// Height of Key
				// -- This is odd, because the location is on the corner points of the text ... NOT the rectangle in which the text prints
				// -- So, we need to find the worst circumstance
				$mHeight1 = abs($text_details[1] - $text_details[5]); // bottom left Y - top right Y
				$mHeight2 = abs($text_details[3] - $text_details[7]); // bottom right Y - top left Y
				$mHeight = max($mHeight1, $mHeight2);
				
				if ($mHeight > $max_text_height)
				{
					$max_text_height = $mHeight;
				}
				//echo ($max_text_height.'-'.$mHeight1.'-'.$mHeight2.'<br />');
				
				// Width of Key
				$mWidth = $text_details[2] - $text_details[0]; // Right side - left side
				if ($mWidth > $max_label_width)
				{
					$max_label_width = $mWidth;
				}
			}
			
			// Get the max value for the Y axis (also used for group height definition)
			$text_details = imagettftext($this->chartImage->img, 
										$this->fontText->fontSize, 
										0, // angle
										-10000, -10000, 
										chartColorHex($this->chartImage->img, $this->fontText->textColor), 
										$this->fontText->textFont, $max_value);
										
			$max_value_width = $text_details[2] - $text_details[0]; // Right side - left side

			// Get the text thickness
			$text_details = imagettftext($this->chartImage->img, 
										$this->fontText->fontSize,  
										0,  // angle
										-10000, -10000, 
										chartColorHex($this->chartImage->img, $this->fontText->textColor), 
										$this->fontText->textFont, 'Z');
										
			$text_thickness = abs($text_details[1] - $text_details[7]);
			
			
			// Have GroupName lables been passed in?
			$group_height = 0;
			$group_text_thickness = 0;
			if (($this->groupSize > 0) && ($this->groupSize))
			{
				// Get the group text thicknesss
				$text_details = imagettftext($this->chartImage->img, 
										$this->fontGroup->fontSize,  
										0, // angle
										-10000,-10000, 
										chartColorHex($this->chartImage->img, $this->fontGroup->textColor), 
										$this->fontGroup->textFont, 'Z');
										
				$group_text_thickness = abs($text_details[1] - $text_details[7]);
				
				// The group labels will go just below the bars
				$group_height = $group_text_thickness; 
			}
			
			
			// === Draw the entire works ===
			
			// Determine the ratio for the bars (@@@ use / 5 or 10 instead)
			$ratio= ($graph_height - $max_text_height - $group_height)/$max_value;
		 
			
			// Outline (where the text is)
			imagefilledrectangle($this->chartImage->img, 1, 1, 
										$this->chartImage->imgWidth-2, 
										$this->chartImage->imgHeight-2, 
										chartColorHex($this->chartImage->img, $this->chartImage->outlineColor));
			
			// A line for the bottom of the bar graph to give it a base
			imagefilledrectangle($this->chartImage->img, 	
										$max_value_width+$mSpaceSize+$this->marginSize-1,	
										$this->marginSize,	
										$this->chartImage->imgWidth-1-$this->marginSize,	
										$this->chartImage->imgHeight-0-$this->marginSize-$max_text_height-$group_height, 
										chartColorHex($this->chartImage->img, $this->chartImage->baselineColor));

			// Background 
			imagefilledrectangle($this->chartImage->img,	
										$max_value_width+$mSpaceSize+$this->marginSize,		
										$this->marginSize,	
										$this->chartImage->imgWidth-1-$this->marginSize,	
										$this->chartImage->imgHeight-1-$this->marginSize-$max_text_height-$group_height, 
										chartColorHex($this->chartImage->img, $this->chartImage->backgroundColor));


			// Graph Grid Lines by mod
			for($i=0;$i<=max($this->values);$i++)
			{
				$yLine = $this->marginSize -$max_text_height + $graph_height - intval($i * $ratio);	// Top of bars
				
				if (($i % $this->yAxisMod ) == 0)
				{
					// Make sure we dont take out the bottom line
					if ($i > 0)
					{
						imageline($this->chartImage->img,	
										$this->marginSize+$mSpaceSize+$max_value_width,
										$yLine - $group_height,
										$this->chartImage->imgWidth-$this->marginSize,
										$yLine - $group_height, 
										chartColorHex($this->chartImage->img, $this->chartImage->gridlineColor));
					}
					
					// Y axis number
					imagettftext($this->chartImage->img,  
									$this->fontText->fontSize, 
									0, 
									5, 
									$yLine - $group_height, 
									chartColorHex($this->chartImage->img,  $this->fontText->textColor), 
									$this->fontText->textFont, $i);		
				}
			}
			
		 
			// Draw the bars on the chart
			// -- include values at top of bar
			reset($this->values);

			// Determine the group gap amount to add and subtract ( if groups )
			$mGroupGap = 0;
			if (($this->groupSize > 0) && ($this->groupNames))
			{
				$mGroupGap = round($gap / $this->groupSize);
				
				// Get the keys as well
				$mGroupKeys = array_keys($this->groupNames);		
			}
			// Group counter for lables
			$mGroupCount = 0;

			
			for($i=0;$i< $total_bars; $i++)
			{ 
				// Extract key and value pair from the current pointer position
				list($key,$value) = each($this->values); 
				
				
				// Bunch the bars up together for groups
				if ($mGroupGap > 0)
				{
					$mGroupAdjust = $mGroupAdjust - $mGroupGap;
					if (($i % $this->groupSize) == 0)
					{
						$mGroupAdjust = 0;
						
						// Issolate the keys from the array
						$mGroupName = $mGroupKeys[$mGroupCount];
						
						// Display the group label here
						$mGroupX =  $this->marginSize + $max_value_width + ($gap /2) + $i * ($gap+$this->barWidth);
						
						// Print group label
						imagettftext($this->chartImage->img, 
										$this->fontGroup->fontSize, 
										0, 
										$mGroupX, 
										$this->chartImage->imgHeight - $max_text_height - $mSpaceSize, 
										chartColorHex($this->chartImage->img, $this->fontGroup->textColor), 
										$this->fontGroup->textFont, $mGroupName);
						// Next group
						$mGroupCount++;
					}
				}

				
				// Determine the position of the bar 
				$x1= $this->marginSize + $max_value_width + ($gap /2) + $i * ($gap+$this->barWidth) + $mGroupAdjust;
				$x2= $x1 + $this->barWidth; 
				
				// Verticals		
				$y1= $this->marginSize + $graph_height - intval($value * $ratio);	// Top of bars
				$y2= $this->chartImage->imgHeight - $this->marginSize;								// Bottom
				
						
				// X axis Bar labels (( Can include angles ))
				// -- Adjust position of of text depending on the angle
				// -- @@@ Need to use sin / cos for clean results (( but even then, its hard to visualize whats going on with the angle part )
				$mOffset = $text_thickness - 2;
				// Note: 0 is flat and pointing right
				if ( $this->fontLabel->fontAngle >= 270)  // points down
				{
					// Angled down, start at top
					$mOffset = $max_text_height - $mSpaceSize;
				}
				else if ( $this->fontLabel->fontAngle >= 180)
				{
					// Angled up, start at bottom
					$mOffset = $max_text_height;		
				}
				else if ( $this->fontLabel->fontAngle >= 25)
				{
					// Angled up, start at bottom
					$mOffset = $text_thickness;		
				}
				
				// Remove non-letters from the key
				// -- Note: This is used in order to force array keys by using numbers
				$mKeyLabel = $key;
				if ($this->stripNonLetters)
				{
					$allowed = "/[^a-z\\040\\.\\-]/i";
					$mKeyLabel = preg_replace($allowed,"",$mKeyLabel);
					
				}
				
				
				// X Axis Labels
				imagettftext($this->chartImage->img, 
								$this->fontLabel->fontSize, 
								$this->fontLabel->fontAngle, 
								$x1+3, 
								$this->chartImage->imgHeight-$mOffset+$group_height-3, 
								chartColorHex($this->chartImage->img, $this->fontLabel->textColor), 
								$this->fontLabel->textFont, $mKeyLabel);		

				
				// Bar shadow
				for($mLoop=0;$mLoop< $this->chartImage->shadowDrop; $mLoop++)
				{ 
					// y2-1 will give room for the base line
					imagefilledrectangle($this->chartImage->img,
												$x1,
												$y1+$mLoop-$max_text_height-$group_height,
												$x2+$mLoop,
												$y2-$max_text_height - 1-$group_height, 	
													$shadow_color[$i % count($shadow_color)]);
				}
				// Bar 
				imagefilledrectangle($this->chartImage->img,	
											$x1,
											$y1-$max_text_height-$group_height,
											$x2,
											$y2-$max_text_height- 1-$group_height, 	
												$fill_color[$i % count($fill_color)]);
												
				// Value of bar (printed at top)
				// -- Print this last over all other graphics
				$mYPos = $y1-$max_text_height-4-$group_height;
				if ($mYPos-1 < $text_thickness) $mYPos = $text_thickness+1;
				imagettftext($this->chartImage->img, 
									$this->fontText->fontSize, 0, $x1+3, $mYPos, 
									chartColorHex($this->chartImage->img, $this->fontText->textColor), 
									$this->fontText->textFont, $value);		
			}
		
		} // if [pie or bar]

		
		// Generate an image file to display
		$mTempFile = $this->chartImage->imgFile;
		if ($mTempFile == '')
		{
			$mTempFile = $this->chartType.'_chart_'.uniqid().'.png';
		}
		imagepng($this->chartImage->img, $mTempFile);
		
		// Display it?
		if ($this->displayTrue)
		{
			echo ('<img height="'.$this->chartImage->imgHeight.'" width="'.$this->chartImage->imgWidth.'" alt="Dynamically generated image" src="'.$mTempFile.'"> ');
		}
		
	} // generateChart

	
	public function setOutputType($outputType) 
	{
		if (($outputType != 'pie') &&
			($outputType != 'bar')) {
			$outputType = 'pie';
		}
		$this->chartType = $outputType;
	}

	public function finish() 
	{
		ImageDestroy($this->chartImage->img);
	}

} // ReportChartOutputter


function chartColorHex($img, $HexColorString) 
{
	$R = hexdec(substr($HexColorString, 0, 2));
	$G = hexdec(substr($HexColorString, 2, 2));
	$B = hexdec(substr($HexColorString, 4, 2));
	return ImageColorAllocate($img, $R, $G, $B);
}  // chartColorHex

function chartColorHexshadow($img, $HexColorString, $mork) 
{
	$R = hexdec(substr($HexColorString, 0, 2));
	$G = hexdec(substr($HexColorString, 2, 2));
	$B = hexdec(substr($HexColorString, 4, 2));

	if ($mork)
	{
		($R > 99) ? $R -= 100 : $R = 0;
		($G > 99) ? $G -= 100 : $G = 0;
		($B > 99) ? $B -= 100 : $B = 0;
	}
	else
	{
		($R < 220) ? $R += 35 : $R = 255;
		($G < 220) ? $G += 35 : $G = 255;
		($B < 220) ? $B += 35 : $B = 255;				
	}			
	
	return ImageColorAllocate($img, $R, $G, $B);
} // chartColorHexshadow


ReportChartOutputter::$OUTPUT_CHART_FORMATS = array(
	(object)array('format'=>'pie'),
	(object)array('format'=>'bar')
);

