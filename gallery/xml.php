<?php
$file = 'list.xml';
$stack = array();

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

$xml_parser = xml_parser_create();
xml_set_element_handler($xml_parser, "startTag", "endTag");
xml_set_character_data_handler($xml_parser, "cdata");

$data = xml_parse($xml_parser,file_get_contents($file));
if(!$data) {
   die(sprintf("XML error: %s at line %d",
xml_error_string(xml_get_error_code($xml_parser)),
xml_get_current_line_number($xml_parser)));
}

xml_parser_free($xml_parser);

print("<pre>\n");
print_r($stack);
print("</pre>\n");
?>
