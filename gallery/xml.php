<?php


function startTag($parser, $name, $attrs)
{
   global $stack;
   $tag=array("name"=>$name,"attrs"=>$attrs); 
   array_push($stack,$tag);
}

function cdata($parser, $cdata)
{
   global $stack,$i;
  
   if(trim($cdata))
   {   
       $stack[count($stack)-1]['cdata']=$cdata;   
   }
}

function endTag($parser, $name)
{
   global $stack; 
   $stack[count($stack)-2]['children'][] = $stack[count($stack)-1];
   array_pop($stack);
}

function xml_parser($file)
{
	global $stack;
	if (!is_readable($file))
		return false;
	$stack = array();
	$xml_parser = xml_parser_create();
	xml_set_element_handler($xml_parser, "startTag", "endTag");
	xml_set_character_data_handler($xml_parser, "cdata");

	$data = xml_parse($xml_parser,file_get_contents($file));
	if(!$data) {
	   echo sprintf("XML error: %s at line %d, file %s",
	xml_error_string(xml_get_error_code($xml_parser)),
	xml_get_current_line_number($xml_parser),
	$file);
	}

	xml_parser_free($xml_parser);

	return $stack;
}

function xml_fileinfo($file)
{
	global $do_exif;
	$fileinfo['name'] = $file['attrs']['NAME'];
	$fileinfo['size'] = $file['attrs']['SIZE'];
	$fileinfo['type'] = $file['attrs']['TYPE'];
	$fileinfo['date'] = $file['attrs']['DATE'];
  if ($fileinfo['type'] == 'images')
	{
		$fileinfo['h'] = $file['children'][0]['attrs']['H'];
		$fileinfo['w'] = $file['children'][0]['attrs']['W'];
		$fileinfo['bits'] = $file['children'][0]['attrs']['BITS'];
	}
	if ($do_exif && $fileinfo['type'] == 'images')
	{
		if (is_array($file['children'][1]['attrs']))
			foreach($file['children'][1]['attrs'] as $tagname=>$tag)
			{
				$tagname = str_replace('_', ' ', strtolower($tagname));
				$fileinfo['exif'][$tagname] = $tag;
			}
	}

	return $fileinfo;
}
?>
