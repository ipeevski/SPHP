<?php
/*  Exif reader v 1.2
    By Richard James Kendall 
    Free to use, please acknowledge me

		Modified by cyberhorse
 
    
    To use, just include this file (with require, include) and call
    exif(filename);
    An array called $exif_data will be populated with the exif tags and folders from the image.
*/ 

include('exif_tags.php');

// holds the formatted data read from the EXIF data area
$exif_data = array();

// gets one byte from the file at handle $fp and converts it to a number
function fgetord($fp) 
{
	return ord(fgetc($fp));
}

// takes $data and pads it from the left so strlen($data) == $shouldbe
function pad($data, $shouldbe, $put) 
{
	if (strlen($data) == $shouldbe)
		return $data;
	else 
	{
		$padding = "";
		for ($i = strlen($data);$i < $shouldbe;$i++)
			$padding .= $put;
			
		return $padding . $data;
	}
}

// converts a number from intel (little endian) to motorola (big endian format)
function ii2mm($intel) 
{
	$mm = "";
	for ($i = 0;$i <= strlen($intel);$i+=2)
		$mm .= substr($intel, (strlen($intel) - $i), 2);
	
	return $mm;
}

// gets a number from the EXIF data and converts if to the correct representation
function getnumber($data, $start, $length, $align) 
{
	$a = bin2hex(substr($data, $start, $length));
	if (!$align)
		$a = ii2mm($a);
	
	return hexdec($a);
}

// gets a rational number (num, denom) from the EXIF data and produces a decimal
function getrational($data, $align, $type) 
{
	$a = bin2hex($data);
	if (!$align) 
		$a = ii2mm($a);
	
	if ($align == 1) {
		$n = hexdec(substr($a, 0, 8));
		$d = hexdec(substr($a, 8, 8));
	} else {
		$d = hexdec(substr($a, 0, 8));
		$n = hexdec(substr($a, 8, 8));
	}
	if ($type == "S" && $n > 2147483647) 
		$n = $n - 4294967296;
	
	if ($n == 0) 
		return 0;
	
	if ($d != 0) 
		return ($n / $d);
	
	return $n . "/" . $d;
}

// opens the JPEG file and attempts to find the EXIF data
function exif($file) {
	global $dir;

	$fp = fopen($dir.'/'.$file, "rb");
	$a = fgetord($fp);
	if ($a != 255 || fgetord($fp) != 216) 
		return false;
	
	$ef = false;
	while (!feof($fp)) 
	{
		$section_length = 0;
		$section_marker = 0;
		$lh = 0;
		$ll = 0;
		for ($i = 0;$i < 7;$i++) 
		{
			$section_marker = fgetord($fp);
			if ($section_marker != 255) 
				break;
			
			if ($i >= 6) 
				return false;
		}
		if ($section_marker == 255) {
			return false;
		}
		$lh = fgetord($fp);
		$ll = fgetord($fp);
		$section_length = ($lh << 8) | $ll;
		$data = chr($lh) . chr($ll);
		$t_data = fread($fp, $section_length - 2);
		$data .= $t_data;
		if ($section_marker == 225)
    	return extractEXIFData(substr($data, 2), $section_length);
	}
	fclose($fp);
}

// reads the EXIF header and if it is intact it calls readEXIFDir to get the data
function extractEXIFData($data, $length) 
{
	if (substr($data, 0, 4) == "Exif") 
	{
		if (substr($data, 6, 2) == "II") 
			$align = 0;
		else 
		{
			if (substr($data, 6, 2) == "MM") 
				$align = 1;
			else
				return false;
		}
		$a = getnumber($data, 8, 2, $align);
		if ($a != 0x2a)
			return false;

		$first_offset = getnumber($data, 10, 4, $align);
		if ($first_offset < 8 || $first_offset > 16) 
			return false;
		
		readEXIFDir(substr($data, 14), 8, $length - 6, $align);
		return true;
	} 
	else 
		return false;
}

// takes a tag id along with the format, data and length of the data and deals with it appropriatly
function dealwithtag($tag, $format, $data, $length, $align) 
{
	global $exif_data, $exif_tags;

	$format_type = array("", "BYTE", "STRING", "USHORT", "ULONG", "URATIONAL", "SBYTE", "UNDEFINED", "SSHORT", "SLONG", "SRATIONAL", "SINGLE", "DOUBLE");
	$w = false;
	$val = "";
	
	switch ($format_type[$format]) 
	{
		case "STRING":
			$val = trim(substr($data, 0, $length));
			$w = true;
			break;
		case "ULONG":
		case "SLONG":
			$val = getnumber($data, 0, 4, $align);
			$w = true;
			break;
		case "UNDEFINED":
		case "USHORT":
		case "SSHORT":
			$val = getnumber($data, 0, 2, $align);
			$w = true;
			break;
		case "URATIONAL":
			$val = getrational(substr($data, 0, 8), $align, "U");
			$w = true;
			break;
		case "SRATIONAL":
			$val = getrational(substr($data, 0, 8), $align, "S");
			$w = true;
			break;

		default: $val = $format_type[$format];
			break;
	}
	
	if ($exif_tags[$tag]['Type'] == 'Lookup' && isset($exif_tags[$tag][$val]))
		$val = $exif_tags[$tag][$val];
	else if ($exif_tags[$tag]['Type'] == 'Numeric' && isset($exif_tags[$tag]['Units']))
		$val .= ' '.$exif_tags[$tag]['Units'];

	if ($w)
		if ($exif_tags[$tag]['Name'])
			$exif_data[$exif_tags[$tag]['Name']] = $val;
		else 
			$exif_data[$tag.'_unknown']=$val;
}

// reads the tags from and EXIF IFD and if correct deals with the data
function readEXIFDir($data, $offset_base, $exif_length, $align) 
{
	$format_length = array(0, 1, 1, 2, 4, 8, 1, 1, 2, 4, 8, 4, 8);
	$value_ptr = 0;
	$sofar = 2;
	$data_in = "";
	$number_dir_entries = getnumber($data, 0, 2, $align);
	for ($i = 0; $i < $number_dir_entries; $i++) 
	{
		$sofar += 12;
		$dir_entry = substr($data, 2 + 12 * $i);
		$tag = getnumber($dir_entry, 0, 2, $align);
		$format = getnumber($dir_entry, 2, 2, $align);
		$components = getnumber($dir_entry, 4, 4, $align);
		if (($format - 1) >= 12) 
			return false;

		$byte_count = $components * $format_length[$format];
		if ($byte_count > 4) 
		{
			$offset_val = (getnumber($dir_entry, 8, 4, $align)) - $offset_base;
			if (($offset_val + $byte_count) > $exif_length) 
				return false;
			$data_in = substr($data, $offset_val);
		} 
		else 
			$data_in = substr($dir_entry, 8);
		
		if ($tag == 0x8769) 
		{
			$tmp = (getnumber($data_in, 0, 4, $align)) - 8;
			readEXIFDir(substr($data, $tmp), $tmp + 8 , $exif_length, $align);
		} 
		else 
			dealwithtag($tag, $format, $data_in, $byte_count, $align);
	}
}
?>
