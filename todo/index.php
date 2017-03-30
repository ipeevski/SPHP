<?php
/**
 * Created on Mar 3, 2006
 *
 * @author Ivan Peevski <cyberhorse@users.sourceforge.net>
 * @version 0.2
 */

include_once 'config.php';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Deal with session login information
if ($action == 'login' && $_POST['password'] == $password) {
	$_SESSION['password'] = $_POST['password'];
} elseif ($action == 'logout') {
	unset($_SESSION['password']);
}

// Kick out users if they are not logged in.
if (!empty($password) && !isset($_SESSION['password'])) {
	include 'template/login.php';
	exit;
}

if (isset($_POST['user'])) {
	$_SESSION['last_user'] = $_POST['user'];
}

$users = db_column('SELECT distinct(user) FROM todo');
if (!empty($users)) {
	$uid = array_search('', $users);
	if ($uid !== false) {
		$users[$uid] = 'All';
	}
}
$week = date('Y-m-d', strtotime('+1 weeks'));

if (!empty($_SESSION['last_user'])) {
	$hours = db_list("SELECT user, sum(duration) AS work FROM todo WHERE completed >= 0 AND completed < 100 AND priority >= 0 AND date <= '$week' AND duration < 2000 AND user = '{$_SESSION['last_user']}'");

	$tasks_count = db_list("SELECT user, count(*) AS tasks FROM todo WHERE completed >= 0 AND completed < 100 AND priority >= 0 AND user = '{$_SESSION['last_user']}'");

	$xp = db_list("SELECT user, sum(FLOOR(completed / 100 * duration * (priority + 1))) AS xp, FLOOR(avg(UNIX_TIMESTAMP(timestamp - DATE_ADD(date, INTERVAL 1 DAY)))/60/4) AS delay FROM todo WHERE completed > 0 AND date > '' AND user = '{$_SESSION['last_user']}'");
} else {
	$hours = db_list("SELECT user, sum(duration) AS work FROM todo WHERE completed >= 0 AND completed < 100 AND priority >= 0 AND date <= '$week' AND duration < 2000 AND user <> '' GROUP BY user ORDER BY user");

	$tasks_count = db_list("SELECT user, count(*) AS tasks FROM todo WHERE completed >= 0 AND completed < 100 AND priority >= 0 AND user <> '' GROUP BY user ORDER BY user");

	$xp = db_list("SELECT user, sum(FLOOR(completed / 100 * duration * (priority + 1))) AS xp, FLOOR(avg(UNIX_TIMESTAMP(timestamp - DATE_ADD(date, INTERVAL 1 DAY)))/60/4) AS delay FROM todo WHERE completed > 0 AND date > '' AND user <> '' GROUP BY user ORDER BY user");
}

$_POST['completed'] = isset($_POST['completed']) ? $_POST['completed'] : '0';
$_POST['bug'] = isset($_POST['bug']) ? $_POST['bug'] : '0';

// Edit a task:
if ($action == 'search') {
	$sql = 'SELECT * FROM todo WHERE ' .
			" user like '%{$_POST['user']}%'" .
			" AND task like '%{$_POST['task']}%'" .
			" AND notes like '%{$_POST['notes']}%'" .
			' AND duration >=  ' . $_POST['duration'] .
			' AND completed >= 0' . // Ignore deleted tasks
			" AND (date >= '{$_POST['date']}' OR completed < 100)" .
			' AND priority >=  ' . $_POST['priority'] .
			' AND bug >=  ' . $_POST['bug'] .
			" AND recur like '%{$_POST['recur']}%'" .
			' AND completed >=  ' . $_POST['completed'] .
			' ORDER BY user, FLOOR(completed/100), date DESC, task';
	$tasks = db_list($sql);
	$last_user = $_POST['user'];
} elseif ($action == 'today') {
	if (!empty($_POST['complete'])) {
		$today = date('Y-m-d');
		foreach($_POST['complete'] as $task) {
			db_exec("UPDATE todo SET `date` = '$today' WHERE id = " . $task);
			msg($task, 'Task scheduled for that day');
		}
	}
	$sql = 'SELECT * FROM todo WHERE ' .
			" user like '%{$_POST['user']}%'" .
			" AND LEFT(date, 10) = '" . date('Y-m-d') . "'" .
			' AND completed >= 0' . // Ignore deleted tasks
			' ORDER BY user, FLOOR(completed/100), priority DESC, date DESC, task';
	$tasks = db_list($sql);
} elseif ($action == 'complete') {
	$task = $_POST['id'];
	// Fetch the existing task if it wasn't completed so a recurring task is generated
	$row = db_row('SELECT date, recur, user FROM todo WHERE completed < 100 AND id = ' . $task);
	// Set date if there wasn't one
	db_exec('UPDATE todo SET `date` = now() WHERE id = ' . $task);
	// Complete the task
	db_exec('UPDATE todo SET completed = 100 WHERE id = ' . $task);
	msg($task, 'Task Completed');
	
	// Insert recurring tasks
	if (!empty($row['recur'])) {
		db_exec("INSERT INTO todo(user, task, notes, duration, priority, recur) (SELECT user, task, notes, duration, priority, recur FROM todo WHERE id = $task LIMIT 1)");
		$new_id = db_lastid();
		if ($row['recur'] == '1 days' && date('l') == 'Friday') {
			$row['recur'] = '3 days';
		}
		$new_date = date('Y-m-d H:i:s', strtotime('+'.$row['recur'], strtotime($row['date'])));
		db_exec("UPDATE todo SET date='$new_date' WHERE id = $new_id");
	}
	
	$_POST['user'] = $row['user'];
} elseif ($action == 'edit') {
	list($selected_task) = db_list('SELECT * FROM todo WHERE id = ' . $_POST['id']);
	if (substr($selected_task['date'], 0, 10) == '0000-00-00') {
		$selected_task['date'] = '';
	}
	$_POST['user'] = $selected_task['user'];
// Save a task:
} elseif (!empty($action)) {
	if ($action == 'save') {
		$last_user = $_POST['user'];
		$date = $_POST['date'];
		if (!empty($date) && !empty($_POST['time'])) {
			$date .= ' ' . $_POST['time'] . ':00';
		}
		$log = '';
		if (!empty($_POST['id'])) {
			$task = $_POST['id'];
			$row = db_row('SELECT date, recur, completed FROM todo WHERE completed < 100 AND id = ' . $task);
			if ((empty($date) or $date == '0000-00-00') && $row['completed'] < 100 && $_POST['completed'] == 100) {
				$date = date('Y-m-d H:i:s');
			}
			db_exec('DELETE FROM todo WHERE id = ' . $_POST['id']);
			$log = 'Updated task';
		} elseif (empty($date) && $_POST['completed'] == 100) {
			$date = date('Y-m-d H:i:s');
			$log = 'Added task';
		} else {
			$log = 'Added task';
		}
		if (!empty($date)) {
			db_exec('INSERT INTO todo(user, task, notes, duration, `date`, priority, bug, completed, recur) VALUES(' . "'{$_POST['user']}', '" . addslashes($_POST['task']) . "', '" . addslashes($_POST['notes']) . "', {$_POST['duration']}, '$date', {$_POST['priority']}, {$_POST['bug']}, {$_POST['completed']}, '{$_POST['recur']}'" . ')');
		} else {
			db_exec('INSERT INTO todo(user, task, notes, duration, priority, bug, completed, recur) VALUES(' . "'{$_POST['user']}', '" . addslashes($_POST['task']) . "', '" . addslashes($_POST['notes']) . "', {$_POST['duration']}, {$_POST['priority']}, {$_POST['bug']}, {$_POST['completed']}, '{$_POST['recur']}'" . ')');
		}
		
		// TODO: Make sync to google calendar if configured: http://framework.zend.com/manual/en/zend.gdata.calendar.html
		$task = db_lastid();
		// Record history
		msg($task, $log);
		if (empty($_POST['id']) && $_POST['completed'] == 100) {
			$row = array('date' => $date, 'recur' => $_POST['recur']);
		}

		if (isset($row) && !empty($row['recur']) && $_POST['completed'] == 100) {
			db_exec("INSERT INTO todo(user, task, notes, duration, priority, recur) (SELECT user, task, notes, duration, priority, recur FROM todo WHERE id = $task)");
			$new_id = db_lastid();
			$new_date = date('Y-m-d H:i:s', strtotime('+'.$row['recur'], strtotime($row['date'])));
			db_exec("UPDATE todo SET date='$new_date' WHERE id = $new_id");
		}
	// Set a task completed:
	} elseif ($action == 'delete') {
		// Hide. To list:
		// SELECT * FROM todo WHERE date='0000-00-00 00:00:00' AND completed=100
		if (!empty($_POST['id'])) {
			db_exec("UPDATE todo SET completed = -1 WHERE id = " . $_POST['id']);
			msg($_POST['id'], 'Task deleted');
		}
		$row = db_row('SELECT user FROM todo WHERE id = ' . $_POST['id']);
		$last_user = $row['user'];
	} elseif ($action == 'update') {
		if (!empty($_POST['complete'])) {
			foreach($_POST['complete'] as $task) {
				// mark task date to now if it hasn't been marked in the past
				$row = db_row('SELECT date, recur, user FROM todo WHERE completed < 100 AND id = ' . $task);
				db_exec('UPDATE todo SET `date` = now() WHERE completed < 100 AND (date = \'0000-00-00 00:00:00\' OR date IS NULL) AND id = ' . $task);
				db_exec('UPDATE todo SET completed = 100 WHERE id = ' . $task);
				msg($task, 'Task Completed');

				if ($row) {
					$last_user = $row['user'];
				}

				// Insert recurring tasks
				if ($row && !empty($row['recur'])) {
					db_exec("INSERT INTO todo(user, task, notes, duration, priority, recur) (SELECT user, task, notes, duration, priority, recur FROM todo WHERE id = $task LIMIT 1)");
					$new_id = db_lastid();
					if ($row['recur'] == '1 days' && date('l') == 'Friday') {
						$row['recur'] = '3 days';
					}
					$new_date = date('Y-m-d H:i:s', strtotime('+'.$row['recur'], strtotime($row['date'])));
					db_exec("UPDATE todo SET date='$new_date' WHERE id = $new_id");
				}
			}
		}
	} elseif ($action == 'log') {
		$task = $_POST['id'];
		
		if ($_POST['perc'] == 100) {
			$row = db_row('SELECT date, recur, user FROM todo WHERE completed < 100 AND id = ' . $task);
			db_exec('UPDATE todo SET `date` = now() WHERE completed < 100 AND (date = \'0000-00-00 00:00:00\' OR date IS NULL) AND id = ' . $task);
		
			if ($row) {
				$last_user = $row['user'];
			}
		
			// Insert recurring tasks
			if ($row && !empty($row['recur'])) {
				db_exec("INSERT INTO todo(user, task, notes, duration, priority, recur) (SELECT user, task, notes, duration, priority, recur FROM todo WHERE id = $task LIMIT 1)");
				$new_id = db_lastid();
				if ($row['recur'] == '1 days' && date('l') == 'Friday') {
					$row['recur'] = '3 days';
				}
				$new_date = date('Y-m-d H:i:s', strtotime('+'.$row['recur'], strtotime($row['date'])));
				db_exec("UPDATE todo SET date='$new_date' WHERE id = $new_id");
			}
		}
		
		db_exec("UPDATE todo SET completed = {$_POST['perc']} WHERE id = $task");
		msg($task, $_POST['note'], $_POST['time']);
	} elseif ($action == 'msg') {
		$task = $_POST['id'];
		msg($task, $_POST['msg']);
	}

	if (isset($last_user)) {
		$_SESSION['last_user'] = $last_user;
	}
	
	// After performing an action, reload page to clear pending POSTs
	// (in case of reloading the page)
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
} elseif (!empty($_POST['user'])) {
	// If filtering by user
	header('Location: '.$_SERVER['PHP_SELF']);
	exit;
}
	

// By default only show task that have been finished in the last week or are upcoming in the next two weeks.
if ($action != 'search' && $action != 'today') {
	$last_week = date('Y-m-d', time() - 3600*24*7); // Last week
	$next_week = date('Y-m-d', time() + 3600*24*7); // Next week
	$two_weeks = date('Y-m-d', time() + 3600*24*7*2); // Next 2 weeks
	$next_month = date('Y-m-d', strtotime('+1 months')); // Next 2 weeks
	
	if (isset($_POST['user'])) {
		$_SESSION['last_user'] = $_POST['user'];
	}
	
	$order = 'date ASC, priority DESC, task';
	if (!empty($_GET['order'])) {
		$order = $_GET['order'].', '.$order;
	}
	$sql = "
SELECT * 
FROM todo 
WHERE 
	completed >= 0 AND ";
	
	if (!empty($_SESSION['last_user'])) {
		$sql .= " user = '{$_SESSION['last_user']}' AND ";
	}
	$sql .= " ((completed < 100 
	AND (date <= '$two_weeks' 
	OR (duration > 2000 AND date <= '$next_month'))) 
OR (completed = 100 AND date >= '$last_week')) 
ORDER BY user, FLOOR(completed/100), $order";
	$tasks = db_list($sql);
}

foreach ($tasks as $k => $task) {
	$task['history'] = db_list('SELECT * FROM todo_history WHERE id = '. $task['id'] . ' ORDER BY timestamp DESC');
	$task['history_minutes'] = db_result('SELECT SUM(time) FROM todo_history WHERE id = '. $task['id']);
	$task['history_minutes'] = ceil($task['history_minutes'] / 60);
	$tasks[$k] = $task;
}
// Store for export
$_SESSION['tasks'] = $tasks;

if (empty($order)) {
	$order = 'date';
}

// Get lists for projects and contexts
$sql = "
SELECT * 
FROM todo 
WHERE (task LIKE '%#%' OR task LIKE '%@%') 
AND completed >= 0
AND completed < 100";
if (!empty($_SESSION['last_user'])) {
	$sql .= " AND user = '{$_SESSION['last_user']}'";
}
$list = db_list($sql);

$projects = array();
$contexts = array();
foreach ($list as $row) {
	
	if (preg_match_all('/(^|[[:space:]])(?<tag>[#@][a-zA-Z0-9]+)/', $row['task'], $matches)) {
		foreach ($matches['tag'] as $tag) {
			if ($tag[0] == '#') {
				$projects[] = substr($tag, 1);
			} elseif ($tag[0] == '@') {
				$contexts[] = substr($tag, 1);
			}
		}
	}
	
	$projects = array_unique($projects);
	asort($projects);
	$contexts = array_unique($contexts);
	asort($contexts);
}
	
include 'template/list.php';
