<?php
include_once('config.php');
$type = $_GET['type'];
// Get the tasks to be exported from the last results page (stored in session)
$tasks = $_SESSION['tasks'];

$mimetypes = array(
	'txt' => 'text/plain',
	'html' => 'text/html',
	'xml' => 'application/xml',
	'csv' => 'application/vnd.ms-excel',
	'sql' => 'text/plain',
	'ics' => 'text/calendar',
	'rss' => 'application/rss+xml',
//'vcs' => 'text/x-vCalendar' 
);
$mimetype = $mimetypes[$type];

// Format task data in human readable form
$formatted_tasks = array();
foreach ($tasks as $task) {
	if ($task['date'] == '0000-00-00 00:00:00') {
		$t['date'] = "N/A";
	} else {
		$t['date'] = date('M d, Y', strtotime($task['date']));
	}
	$t['priority'] = $priorities[$task['priority']];
	$t['completed'] = $task['completed'];
	// Return duration in hours
	if ($task['duration'] == 0) {
		$t['duration'] = "Unknown"; 
	} else {
		$t['duration'] = ($task['duration'] / 60) . " hour(s)";
	}
	$t['recur'] = $task['recur'];
	$t['bug'] = ($task['bug'] ? 'yes' : 'no');
	
	$t['id'] = $task['id'];
	$t['task'] = $task['task'];
	$t['timestamp'] = $task['timestamp'];
	
	$formatted_tasks[] = $t;
}


if ($type == 'txt') {
	$file = "Date           Priority  Progress  Duration       Recuring  Bug  Task\n";
	// Data returned in readable form (all columns are the same size)
	foreach ($formatted_tasks as $task) {
		$file .= str_pad($task['date'], 15);
		$file .= str_pad($task['priority'], 10);
		$file .= str_pad($task['completed'] . '%', 10);
		$file .= str_pad($task['duration'], 15);
		$file .= str_pad($task['recur'], 10);
		$file .= str_pad($task['bug'], 5);
		
		$file .= $task['task'] . "\n";
	}
} elseif ($type == 'rss') {
	$now = date("D, d M Y H:i:s T");
	$link = 'http://'.$_SERVER['SERVER_NAME'].'/'.dirname($_SERVER['PHP_SELF']);
	$file = '<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0">
<channel>
	<title>Tasks</title>
	<description>Tasks</description>
	<link>'.$link.'</link>
	<lastBuildDate>'.$now.'</lastBuildDate>
	<pubDate>'.$now.'</pubDate>
';
	// Data returned in readable form (all columns are the same size)
	foreach ($formatted_tasks as $task) {
		$desc = '';
		$desc .= 'Date: ' . $task['date'] . "\n";
		$desc .= 'Priority: ' . $task['priority'] . "\n";
		$desc .= 'Completed: ' . $task['completed'] . '%' . "\n";
		$desc .= 'Duration: ' . $task['duration'] . "\n";
		$desc .= 'Recurring: ' . $task['recur'] . "\n";
		$desc .= 'Bug: ' . $task['bug'];

		$ts = $now = date("D, d M Y H:i:s T", strtotime($task['timestamp']));
		$file .= '
	<item>
		<title>'.$task['task'].'</title>
		<description>'.$desc.'</description>
		<link>'.$link.'</link>
		<guid>'.$task['id'].'</guid>
		<pubDate>'.$ts.'</pubDate>
	</item>';
	}
	$file .= '
	</channel>
</rss>';
} elseif ($type == 'csv') {
	if (isset($_REQUEST['mode']) and $_REQUEST['mode'] == 'outlook') {
		$file = '"Subject","Start Date","Due Date","Reminder On/Off","Reminder Date","Reminder Time","Date Completed","% Complete","Total Work","Actual Work","Billing Information","Categories","Companies","Contacts","Mileage","Notes","Priority","Private","Role","Schedule+ Priority","Sensitivity","Status"'."\n";
		foreach ($tasks as $task) {
			$file .= '"'.str_replace('"', '""', $task['task']).'",';
			$file .= ','; // Leave start date empty
			if ($task['date'] != '0000-00-00 00:00:00') {
				$file .= '"'.date('m/d/Y', strtotime($task['date'])).'",';
			} else {
				$file .= ','; // Leave empty
			}
			$file .= '"False",,,,'; // reminder, reminder date, reminder time, date completed, 
			$file .= '"'. sprintf('%.3f', $task['completed'] / 100).'",';
			$file .= '"' . $task['duration'] . '",';
			$file .= '"' . $task['history_minutes'] . '",';
			$file .= ',,,,,';
			$file .= '"'. str_replace('"', '""', $task['notes']) . '",';
			if ($task['priority'] == 1) {
				$file .= '"Normal",';
			} elseif ($task['priority'] > 1) {
				$file .= '"High",';
			} elseif ($task['priority'] < 1) {
				$file .= '"Low",';
			}
			$file .= '"False","",,"Normal",';
			if ($task['priority'] == -1) {
				$file .= '"Waiting"';
			} elseif ($task['completed'] == 100) {
				$file .= '"Complete"';
			} elseif ($task['completed'] == 0) {
				$file .= '"Not Started"';
			} else {
				$file .= '"In Progress"';
			}
			$file .= "\n";
		}
	} else {
		$file = 'Date,Priority,Completed,Duration,Recur,Bug,Task'."\n";
		// Store the file in memory
		$csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
		foreach ($formatted_tasks as $task) {
			fputcsv($csv, $task);
		}
		rewind($csv);
		
		// put it all in a variable
		$file .= stream_get_contents($csv);
		fclose($csv);
	}
} elseif ($type == 'html') {
	$file = '<table>';
	$file .= '<tr><th>Date</th><th>Priority</th><th>Progress</th><th>Duration</th><th>Recurring</th><th>Bug</th><th>Task</th></tr>';
	foreach ($formatted_tasks as $task) {
		$file .= '<tr><td>'.implode('</td><td>', $task) . '</td></tr>';
	}
	$file .= '</table>';
} elseif ($type == 'sql') {
	$file = "INSERT INTO todo(user, task, notes, duration, date, priority, bug, completed, recur) \n VALUES";
	foreach ($tasks as $task) {
		$file .= '(';
		$file .= '\'' . addslashes($task['user']) . '\', ';
		$file .= '\'' . addslashes($task['task']) . '\', ';
		$file .= '\'' . addslashes($task['notes']) . '\', ';
		$file .= $task['duration'] . ', ';
		$file .= '\'' . addslashes($task['date']) . '\', ';
		$file .= $task['priority'] . ', ';
		$file .= $task['bug'] . ', ';
		$file .= $task['completed'] . ', ';
		$file .= '\'' . $task['recur'] . '\'';
		$file .= '),'."\n";
	}
	$file = substr($file, 0, -2) . ';';
} elseif ($type == 'xml') {
	$file = '<?xml version="1.0" encoding="ISO-8859-1"?>';
	$file .= "<data>\n";
	foreach ($formatted_tasks as $k => $task) {
		$file .= '<task_detail>'."\n";
		foreach ($task as $key => $value) {
			$file .= "\t<$key>$value</$key>\n";
		}
		$file .= "\t<note>" . $tasks[$k]['notes']."</note>\n";
		$file .= "</task_detail>\n";
	}
	$file .= '</data>';
} elseif ($type == 'ics') {
	$file = 'BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
PRODID:-//Gamaland//TODO List//EN';
	// For each task, use the VTODO specification
	foreach ($tasks as $task) {
		$file .= '
BEGIN:VTODO
UID:'.$task['id'].'
DTSTAMP:'.date('Ymd\THis', strtotime($task['timestamp'])).'Z
DUE:'.date('Ymd\THis', strtotime($task['date'])).'Z
STATUS:NEEDS-ACTION
SUMMARY:'.$task['task'].'
DESCRIPTION:'.$task['notes'].'
PRIORITY:'.$task['priority'].'
PERCENT:'.$task['completed'].'
ORGANIZER:'.$task['user'].'
END:VTODO';
// DUE and DURATION are mutually exclusive in the standard
//DURATION:PT'.$task['duration'].'M		
	}
	$file .= '
END:VCALENDAR';
}

// Set the content type and mark the file for download
header('Content-Length: ' . strlen($file));
header('Content-Type: ' . $mimetype);
if ($type != 'rss') {
	header('Content-Disposition: attachment; filename="list.'.$type.'"');
}


// Don't cache
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// Output the file
echo $file;