<?php
include_once ('lib/jpgraph/jpgraph.php');
//

// Some data
session_start();
$pl = null;
$graph = null;
$id = $_GET['gid'];
$type = $_SESSION[$id]['type'];
//$type = 'ch';
$graph_title = $_SESSION[$id]['graph_title'];
$graph_array = $_SESSION[$id]['graph_array'];
$graph_titles = array();
$graph_data = array();
foreach($graph_array as $key => $value)
{
	$graph_titles[] = $key;
	$graph_data[] = $value;
}

// Create the Graph. 

if ($type == 'chart')
{
	include ('lib/jpgraph/jpgraph_bar.php');
	$graph = new Graph(800,200,'auto');
	$graph->SetScale("textlin");
	$graph->xaxis->title->Set("Date");
	$graph->yaxis->title->Set("Money");
}
else
{
	include ('lib/jpgraph/jpgraph_pie.php');
	include ('lib/jpgraph/jpgraph_pie3d.php');
	$graph = new PieGraph(400,200,'auto');
}

$graph->SetShadow();


// Set A title for the plot
$graph->title->Set($graph_title);
$graph->title->SetFont(FF_FONT1,FS_BOLD);


// Create
if ($type == 'chart')
{
	$bar = new BarPlot($graph_data);
	//$months=$gDateLocale->GetShortMonth();
	foreach($graph_titles as $k => $gt)
	{
		//$gt = intval($gt) - 1;
		//$graph_titles[$k] = $months[$gt];
		$bar->SetLegend($gt);
	}
		
	$graph->xaxis->SetTickLabels($graph_titles);
}
else
{
	$bar = new PiePlot3D($graph_data);
	$bar->SetLegends($graph_titles);
}

//$targ=array("pie3d_csimex1.php?v=1","pie3d_csimex1.php?v=2","pie3d_csimex1.php?v=3",
//			"pie3d_csimex1.php?v=4","pie3d_csimex1.php?v=5","pie3d_csimex1.php?v=6");
//$alts=array("val=%d","val=%d","val=%d","val=%d","val=%d","val=%d");
//$p1->SetCSIMTargets($targ,$alts);

if ($type == 'chart')
{
	$bar = new BarPlot($graph_data);
	//$bar->
	$bar->value->Show(true);
	$bar->value->SetFormat("%d $");
}
else
{
// Use absolute label
	$bar->SetLabelType(1);
	$bar->value->SetFormat("%d $");

// Move the pie slightly to the left
	$bar->SetCenter(0.4,0.5);
}

// Send back the HTML page which will call this script again
// to retrieve the image.

//$graph->StrokeCSIM('graph.php');
//$graph->Stroke();

$graph->Add($bar);
$graph->Stroke();
?>