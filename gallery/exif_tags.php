<?php
global $tags;
$tags = array (

// Exif IFD

33432 => array( 'Name' => "Copyright Information",
                'Type' => "String" ),

33434 => array( 'Name' => "Exposure Time",
                'Type' => "Numeric",
                'Units' => "seconds" ),

33437 => array( 'Name' => "Aperture F Number",
                'Type' => "Numeric" ),

33723 => array( 'Name' => "IPTC Records",
                'Type' => "IPTC" ),

34665 => array( 'Name' => "EXIF IFD",
                'Type' => "SubIFD",
                'Tags Name' => "EXIF" ),

34850 => array( 'Name' => "Exposure Program",
                'Type' => "Lookup",
                0 => "Not defined",
                1 => "Manual",
                2 => "Normal program",
                3 => "Aperture priority",
                4 => "Shutter priority",
                5 => "Creative program (biased toward depth of field)",
                6 => "Action program (biased toward fast shutter speed)",
                7 => "Portrait mode (for closeup photos with the background out of focus)",
                8 => "Landscape mode (for landscape photos with the background in focus)" ),

34852 => array( 'Name' => "Spectral Sensitivity",
                'Type' => "String" ),

34853 => array( 'Name' => "GPS Info IFD",
                'Type' => "SubIFD",
                'Tags Name' => "GPS" ),

34855 => array( 'Name' => "ISO Speed Ratings",
                'Type' => "Numeric" ),

34856 => array( 'Name' => "Opto-Electronic Conversion Function",
                'Type' => "Unknown" ),
								
36864 => array( 'Name' => "Exif Version",
                'Type' => "String" ),

36867 => array( 'Name' => "Date of Original",
                'Type' => "String",
                'Units' => "" ),

36868 => array( 'Name' => "Date Digitized",
                'Type' => "String",
                'Units' => "" ),

37121 => array( 'Name' => "Components Configuration",
                'Type' => "Special" ),

37122 => array( 'Name' => "Compressed Bits Per Pixel",
                'Type' => "Numeric",
                'Units' => "bits" ),

37377 => array( 'Name' => "APEX Shutter Speed",
                'Type' => "Numeric",
                'Units' => "Tv" ),

37378 => array( 'Name' => "APEX Aperture",
                'Type' => "Numeric",
                'Units' => "Av" ),

37379 => array( 'Name' => "APEX Brightness",
                'Type' => "Numeric",
                'Units' => "Bv" ),

37380 => array( 'Name' => "APEX Exposure Bias",
                'Type' => "Numeric",
                'Units' => "Ev" ),

37381 => array( 'Name' => "APEX Maximum Aperture",
                'Type' => "Numeric" ),

37382 => array( 'Name' => "Subject Distance",
                'Type' => "Numeric",
                'Units' => "metres" ),

37383 => array( 'Name' => "Metering Mode",
                'Type' => "Lookup",
                0 => "Unknown",
                1 => "Average",
                2 => "Center Weighted Average",
                3 => "Spot",
                4 => "Multi Spot",
                5 => "Pattern",
                6 => "Partial",
                255 => "Other" ),

37384 => array( 'Name' => "Light Source",
                'Type' => "Lookup",
                0 => "Unknown",
                1 => "Daylight",
                2 => "Fluorescent",
                3 => "Tungsten (incandescent light)",
                4 => "Flash",
                9 => "Fine weather",
                10 => "Cloudy weather",
                11 => "Shade",
                12 => "Daylight fluorescent (D 5700 – 7100K)",
                13 => "Day white fluorescent (N 4600 – 5400K)",
                14 => "Cool white fluorescent (W 3900 – 4500K)",
                15 => "White fluorescent (WW 3200 – 3700K)",
                17 => "Standard light A",
                18 => "Standard light B",
                19 => "Standard light C",
                20 => "D55",
                21 => "D65",
                22 => "D75",
                23 => "D50",
                24 => "ISO studio tungsten",
                255 => "Other" ),

37385 => array( 'Name' => "Flash",
                'Type' => "Lookup",
                0  => "Flash did not fire",
                1  => "Flash fired",
                5  => "Strobe return light not detected",
                7  => "Strobe return light detected",
                9  => "Flash fired, compulsory flash mode",
                13 => "Flash fired, compulsory flash mode, return light not detected",
                15 => "Flash fired, compulsory flash mode, return light detected",
                16 => "Flash did not fire, compulsory flash suppression mode",
                24 => "Flash did not fire, auto mode",
                25 => "Flash fired, auto mode",
                29 => "Flash fired, auto mode, return light not detected",
                31 => "Flash fired, auto mode, return light detected",
                32 => "No flash function",
                65 => "Flash fired, red-eye reduction mode",
                69 => "Flash fired, red-eye reduction mode, return light not detected",
                71 => "Flash fired, red-eye reduction mode, return light detected",
                73 => "Flash fired, compulsory flash mode, red-eye reduction mode",
                77 => "Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected",
                79 => "Flash fired, compulsory flash mode, red-eye reduction mode, return light detected",
                89 => "Flash fired, auto mode, red-eye reduction mode",
                93 => "Flash fired, auto mode, return light not detected, red-eye reduction mode",
                95 => "Flash fired, auto mode, return light detected, red-eye reduction mode" ),

37386 => array( 'Name' => "FocalLength",
                'Type' => "Numeric",
                'Units' => "mm" ),

37396 => array( 'Name' => "Subject Area",
                'Type' => "Numeric",
                'Units' => "( Two Values: x,y coordinates,  Three Values: x,y coordinates, diameter,  Four Values: center x,y coordinates, width, height)" ),

37500 => array( 'Name' => "Maker Note",
                'Type' => "Maker Note" ),

37510 => array( 'Name' => "User Comment",
                'Type' => "Character Coded String" ),

37520 => array( 'Name' => "Sub Second Time",
                'Type' => "String" ),

37521 => array( 'Name' => "Sub Second Time of Original",
                'Type' => "String" ),

37522 => array( 'Name' => "Sub Second Time when Digitized",
                'Type' => "String" ),

40960 => array( 'Name' => "FlashPix Version",
                'Type' => "String" ),

40961 => array( 'Name' => "Colour Space",
                'Type' => "Lookup",
                1 => "sRGB",
                0xFFFF => "Uncalibrated" ),

40962 => array( 'Name' => "Pixel X Dimension",
                'Type' => "Numeric",
                'Units'=> "pixels" ),

40963 => array( 'Name' => "Pixel Y Dimension",
                'Type' => "Numeric",
                'Units' => "pixels" ),

40964 => array( 'Name' => "Related Sound File",
                'Type' => "String" ),

40965 => array( 'Name' => "Interoperability IFD",
                'Type' => "SubIFD",
                'Tags Name' => "Interoperability" ),

42240 => array( 'Name' => "Gamma Compensation for Playback",
                'Type' => "Numeric" ),


41483 => array( 'Name' => "Flash Energy",
                'Type' => "Numeric",
                'Units' => "Beam Candle Power Seconds (BCPS)" ),

41484 => array( 'Name' => "Spatial Frequency Response",
                'Type' => "Unknown" ),

41486 => array( 'Name' => "Focal Plane X Resolution",
                'Type' => "Numeric",
                'Units' => "pixels per 'Focal Plane Resolution Unit'" ),

41487 => array( 'Name' => "Focal Plane Y Resolution",
                'Type' => "Numeric",
                'Units' => "pixels per 'Focal Plane Resolution Unit'" ),

41488 => array( 'Name' => "Focal Plane Resolution Unit",
                'Type' => "Lookup",
                2 => "Inches",
                3 => "Centimetres" ),

41492 => array( 'Name' => "Subject Location",
                'Type' => "Numeric",
                'Units' => "(x,y pixel coordinates of subject)" ),

41493 => array( 'Name' => "Exposure Index",
                'Type' => "Numeric" ),

41495 => array( 'Name' => "Sensing Method",
                'Type' => "Lookup",
                1 => "Not defined",
                2 => "One-chip colour area sensor",
                3 => "Two-chip colour area sensor",
                4 => "Three-chip colour area sensor",
                5 => "Colour sequential area sensor",
                7 => "Trilinear sensor",
                8 => "Colour sequential linear sensor" ),

41728 => array( 'Name' => "File Source",
                'Type' => "Lookup",
                3 => "Digital Still Camera" ),

41729 => array( 'Name' => "Scene Type",
                'Type' => "Lookup",
                1 => "A directly photographed image" ),

41730 => array( 'Name' => "Colour Filter Array Pattern",
                'Type' => "Special" ),

41985 => array( 'Name' => "Special Processing",
                'Type' => "Lookup",
                0 => "Normal process",
                1 => "Custom process" ),

41986 => array( 'Name' => "Exposure Mode",
                'Type' => "Lookup",
                0 => "Auto",
                1 => "Manual",
                2 => "Auto bracket" ),

41987 => array( 'Name' => "White Balance",
                'Type' => "Lookup",
                0 => "Auto",
                1 => "Manual" ),

41988 => array( 'Name' => "Digital Zoom Ratio",
                'Type' => "Numeric",
                'Units' => "" ),

41989 => array( 'Name' => "Equivalent Focal Length In 35mm Film",
                'Type' => "Numeric",
                'Units' => "mm" ),

41990 => array( 'Name' => "Scene Capture Type",
                'Type' => "Lookup",
                0 => "Standard",
                1 => "Landscape",
                2 => "Portrait",
                3 => "Night scene" ),

41991 => array( 'Name' => "Gain Control",
                'Type' => "Lookup",
                0 => "None",
                1 => "Low gain up",
                2 => "High gain up",
                3 => "Low gain down",
                4 => "High gain down" ),

41992 => array( 'Name' => "Contrast",
                'Type' => "Lookup",
                0 => "Normal",
                1 => "Soft",
                2 => "Hard" ),

41993 => array( 'Name' => "Saturation",
                'Type' => "Lookup",
                0 => "Normal",
                1 => "Low saturation",
                2 => "High saturation" ),

41994 => array( 'Name' => "Sharpness",
                'Type' => "Lookup",
                0 => "Normal",
                1 => "Soft",
                2 => "Hard" ),

41995 => array( 'Name' => "Device Setting Description",
                'Type' => "Unknown" ),

41996 => array( 'Name' => "Subject Distance Range",
                'Type' => "Lookup",
                0 => "Unknown",
                1 => "Macro",
                2 => "Close view",
                3 => "Distant view" ),

42016 => array( 'Name' => "Image Unique ID",
                'Type' => "String" ),


50341 => array( 'Name' => "Print Image Matching Info",
                'Type' => "PIM" ),

/*******************************************/
								
								
11  => array( 'Name' => "ACDComment",
			 				'Type' => "Numeric" ),
							
255 => array( 'Name' => "NewSubfileType",
			 				'Type' => "Numeric" ),
							
256 => array(   'Name'  => "Image Width",
                'Description' => "Width of image in pixels (number of columns)",
                'Type'  => "Numeric",
                'Units' => "pixels" ),

257 => array(   'Name'  =>  "Image Length",
                'Description' => "Height of image in pixels (number of rows)",
                'Type'  => "Numeric",
                'Units' => "pixels" ),

258 => array(   'Name'  => "Bits Per Sample",
                'Description' => "Number of bits recorded per sample (a sample is usually one colour (Red, Green or Blue) of one pixel)",
                'Type'  => "Numeric",
                'Units' => "bits" ),


259 => array(   'Name' => "Compression",
                'Description' => "Specifies what type of compression is used 1 = uncompressed, 6 = JPEG compression (thumbnails only), Other = reserved",
                'Type' => "Lookup",
                1 => "Uncompressed",
                6 => "Thumbnail compressed with JPEG compression" ),

262 => array(   'Name' =>  "Photometric Interpretation",
                'Description' => "Specifies Pixel Composition - 0 or 1 = monochrome, 2 = RGB, 3 = Palatte Colour, 4 = Transparency Mask, 6 = YCbCr",
                'Type' => "Lookup",
                2 => "RGB",
                6 => "YCbCr" ),

270 => array(   'Name' => "Image Description",
                'Type' => "String" ),

271 => array(   'Name' => "Manufacturer",
                'Type' => "String" ),

272 => array(   'Name' => "Model",
                'Type' => "String" ),

273 => array(   'Name' =>  "Strip Offsets",
                'Type' => "Numeric",
                'Units'=> "bytes offset" ),

274 => array(   'Name' =>  "Orientation",
                'Description' => "Specifies the orientation of the image.\n
                  1 = Row 0 top, column 0 left\n
                  2 = Row 0 top, column 0 right\n
                  3 = Row 0 bottom, column 0 right\n
                  4 = Row 0 bottom, column 0 left\n
                  5 = Row 0 left, column 0 top\n
                  6 = Row 0 right, column 0 top\n
                  7 = Row 0 right, column 0 bottom\n
                  8 = Row 0 left, column 0 bottom",
                'Type' => "Lookup",
                1 => "No Rotation, No Flip",
                2 => "No Rotation, Flipped Horizontally",
                3 => "Rotated 180 degrees, No Flip",
                4 => "No Rotation, Flipped Vertically",
                5 => "Flipped Horizontally, Rotated 90 degrees counter clockwise",
                6 => "No Flip, Rotated 90 degrees clockwise",
                7 => "Flipped Horizontally, Rotated 90 degrees clockwise",
                8 => "No Flip, Rotated 90 degrees counter clockwise" ),

277 => array(   'Name' =>  "Samples Per Pixel",
                'Description' => "Number of recorded samples (colours) per pixel - usually 1 for B&W, grayscale, and palette-colour, usually 3 for RGB and YCbCr",
                'Type' => "Numeric",
                'Units' => "Components (colours)" ),

278 => array(   'Name' =>  "Rows Per Strip",
                'Type' => "Numeric",
                'Units'=> "rows" ),

279 => array(   'Name' => "Strip Byte Counts",
                'Type' => "Numeric",
                'Units'=> "bytes" ),

282 => array(   'Name' =>  "X Resolution",
                'Description' => "Number of columns (pixels) per \'ResolutionUnit\'",
                'Type' => "Numeric",
                'Units'=> "pixels per Unit " ),

283 => array(   'Name' =>  "Y Resolution",
                'Description' => "Number of rows (pixels) per \'ResolutionUnit\'",
                'Type' => "Numeric",
                'Units'=> "pixels per Unit " ),

284 => array(   'Name' =>  "Planar Configuration",
                'Description' => "Specifies whether pixel components are recorded in chunky or planar format - 1 = Chunky, 2 = Planar",
                'Type' => "Lookup",
                1 => "Chunky Format",
                2 => "Planar Format" ),

296 => array(   'Name' =>  "Resolution Unit",
                'Description' => "Units for measuring XResolution and YResolution - 1 = No units, 2 = Inches, 3 = Centimetres",
                'Type' => "Lookup",
  							1 => "inches", 
  							2 => "inches", 
  							3 => "cm", 
  							4 => "mm", 
  							5 => "um" ),

301 => array(   'Name' => "Transfer Function",
                'Type' => "Numeric",
                'Units'=> "" ),

305 => array(   'Name' => "Software or Firmware",
                'Type' => "String" ),

306 => array(   'Name' => "Date and Time",
                'Type' => "Numeric",
                'Units'=> "" ),

315 => array(   'Name' => "Artist Name",
                'Type' => "String" ),

318 => array(   'Name' => "White Point Chromaticity",
                'Type' => "Numeric",
                'Units'=> ''),//"(x,y coordinates on a 1931 CIE xy chromaticity diagram)" ),

319 => array(   'Name' => "Primary Chromaticities",
                'Type' => "Numeric",
                'Units'=> ''),//"(Red x,y, Green x,y, Blue x,y coordinates on a 1931 CIE xy chromaticity diagram)" ),

530 => array(   'Name' =>  "YCbCr Sub-Sampling",
                'Description' => "Specifies ratio of chrominance to luminance components - [2, 1] = YCbCr4:2:2,  [2, 2] = YCbCr4:2:0",
                'Type' => "Special" ),

513 => array(   'Name' => "Exif Thumbnail (JPEG)",
                'Type' => "Special" ),

514 => array(   'Name' => "Exif Thumbnail Length (JPEG)",
                'Type' => "Numeric",
                'Units'=> "bytes" ),

529 => array(   'Name' => "YCbCr Coefficients",
                'Description' => "Transform Coefficients for transformation from RGB to YCbCr",
                'Type' => "Numeric",
                'Units'=> "(LumaRed, LumaGreen, LumaBlue [proportions of red, green, and blue in luminance])" ),

531 => array(   'Name' =>  "YCbCr Positioning",
                'Description' => "Specifies location of chrominance and luminance components - 1 = centered, 2 = co-sited",
                'Type' => "Lookup",
                1 => "Centre of Pixel Array",
                2 => "Datum Points" ),

532 => array(   'Name' => "Reference Black and White point",
                'Type' => "Numeric",
                'Units'=> '') //"(R or Y White Headroom, R or Y Black Footroom, G or Cb White Headroom, G or Cb Black Footroom, B or Cr White Headroom, B or Cr Black Footroom)" ),

);
?>