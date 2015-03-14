<html>
<head>
	<title>TODO list</title>
	<script type="text/javascript" src="js/jquery.js"></script>
	<link href="http://fonts.googleapis.com/css?family=Cousine" rel="stylesheet" type="text/css">
	<link rel="stylesheet" type="text/css" href="jquery-ui.css" />
	<link rel="stylesheet" type="text/css" href="main.css" />
	<link rel="stylesheet" type="text/css" href="print.css" media="print" />
</head>
<body>
	<form method="post" name="form">
	<input type="hidden" name="id" id="id" value="<?php echo (isset($selected_task)?$selected_task['id']:'') ?>"/>
	<input type="hidden" name="action" id="action" />
	<input type="hidden" name="msg" id="msg" />
	<table width="90%" align="center" cellspacing="0">
	<tr class="noprint">
		<th><a class="order" href="?order=user">Person</a></th>
		<th width="100%"><a class="order" href="?order=task">Task</a></th>
		<th><a class="order" href="?order=date">Date</a></th>
		<th><a class="order" href="?order=recur">Recuring</a></th>
		<th><a class="order" href="?order=duration">Duration</a></th>
		<th><a class="order" href="?order=priority">Priority</a></th>
		<th><a class="order" href="?order=bug">Bug</a></th>
		<th><a class="order" href="?order=completed">Done</a></th>
	</tr>
	<tr class="noprint">
		<td>
			<select id="user" name="user" onchange="if (this.selectedIndex == this.options.length - 1) $('#add_user').show()">
				<option value=""></option>
	<?php foreach($users as $user) {
					if ($user == 'All') {
						$val = '';
					} else {
						$val = $user;
					}
	?>
				<option value="<?php echo $val ?>" <?php if ((isset($selected_task) and $selected_task['user'] == $val) or (isset($_SESSION['last_user']) and $_SESSION['last_user'] == $val))  echo 'selected' ?>><?php echo $user ?></option>
	<?php } ?>
				<option value="">Add User</option>
			</select>
			<div id="add_user" style="display: none">
				<input id="new_user" size="8" />
				<button onclick="$('#user').html($('#user').html() + '<option value='+$('#new_user').val()+'>'+$('#new_user').val()+'</option>'); return false;">add</button>
			</div>
		</td>
		<td nowrap="nowrap">
			<input id="task" name="task" size="65" value="<?php echo (isset($selected_task)?htmlentities($selected_task['task']):'') ?>"/>
			<!-- <textarea id="task" name="task" cols="50" rows="1"><?php echo (isset($selected_task)?$selected_task['task']:'') ?></textarea> -->
			<img src="images/icons/note.png" alt="notes" title="notes" onclick="$('#note').toggle()" />
		</td>
		<td nowrap="nowrap">
			<input type="text" name="date" size="8" value="<?php echo (isset($selected_task)?substr($selected_task['date'], 0, 10):date('Y-m-d')) ?>" class="date" />
			<span id="time" style="display: none">
			<select name="time">
				<option value=""></option>
	<?php for($i = 8; $i <= 18; $i++) { ?>
				<option class="odd" value="<?php echo sprintf('%02.0f:00', $i) ?>" <?php if (isset($selected_task) && substr($selected_task['date'], 11, 5) == sprintf('%02.0f:00', $i)) echo 'selected' ?>><?php echo sprintf('%02.0f:00', ($i > 12 ? $i - 12 : $i)) ?></option>
				<option class="even" value="<?php echo sprintf('%02.0f:30', $i) ?>" <?php if (isset($selected_task) && substr($selected_task['date'], 11, 5) == sprintf('%02.0f:30', $i)) echo 'selected' ?>><?php echo sprintf('%02.0f:30', ($i > 12 ? $i - 12 : $i)) ?></option>
	<?php } ?>
			</select>
			</span>
		</td>
		<td>
			<select name="recur">
	<?php foreach($recuring as $val => $recur) { ?>
				<option value="<?php echo $val ?>" <?php if (isset($selected_task) && $selected_task['recur'] == $val) echo 'selected' ?>><?php echo $recur ?></option>
	<?php } ?>
			</select>
		</td>
		<td>
			<select name="duration">
	<?php foreach($durations as $val => $dur) { ?>
				<option value="<?php echo $val ?>" <?php if (isset($selected_task) && $selected_task['duration'] == $val) echo 'selected' ?>><?php echo $dur ?></option>
	<?php } ?>
			</select>
		</td>
		<td>
			<select name="priority">
	<?php foreach($priorities as $val => $prio) { ?>
				<option value="<?php echo $val ?>" class="<?php echo $prio ?>" <?php if (isset($selected_task) and $selected_task['priority'] == $val) echo 'selected' ?>><?php echo $prio ?></option>
	<?php } ?>
			</select>
		</td>
		<td><input type="checkbox" name="bug" value="1" <?php if (isset($selected_task) and $selected_task['bug'] == 1) echo 'checked' ?>/></td>
		<td>
			<select name="completed">
				<option value="0"<?php if (isset($selected_task) and $selected_task['completed'] == 0) echo ' selected = "selected"' ?>>0%</option>
				<option value="20"<?php if (isset($selected_task) and $selected_task['completed'] == 20) echo ' selected = "selected"' ?>>20%</option>
				<option value="40"<?php if (isset($selected_task) and $selected_task['completed'] == 40) echo ' selected = "selected"' ?>>40%</option>
				<option value="60"<?php if (isset($selected_task) and $selected_task['completed'] == 60) echo ' selected = "selected"' ?>>60%</option>
				<option value="80"<?php if (isset($selected_task) and $selected_task['completed'] == 80) echo ' selected = "selected"' ?>>80%</option>
				<option value="100"<?php if (isset($selected_task) and $selected_task['completed'] == 100) echo ' selected = "selected"' ?>>100%</option>
			</select>
		</td>
	</tr>
	<tr id="note" <?php echo ((isset($selected_task) and !empty($selected_task['notes'])) ? '' : 'style="display: none"') ?>>
		<td colspan="9" valign="top">
			notes: <textarea name="notes" cols="100" rows="3"><?php echo (isset($selected_task)?$selected_task['notes']:'') ?></textarea>
		</td>
	</tr>
	<tr class="noprint">
		<td nowrap="nowrap">
			<input type="submit" name="action" value="save" /> &nbsp;
			<input type="submit" name="action" id="search" value="search" />
		</td>
		<td>&nbsp;</td>
		<td>
			<input type="submit" name="action" value="today" />
		</td>
		<td colspan="4" align="center">
			&nbsp;
		</td>
		<td>
			<input type="submit" name="action" value="delete" onclick="return confirm('Are you sure you want to delete this task?')" />
		</td>
	</tr>
	<tr class="noprint">
		<td colspan="5">Users:
		<?php if (!empty($users)) {
		foreach($users as $user) { ?>
			<a href="#" onclick="$('#user option[value=\'<?php echo ($user == 'All' ? '' : $user) ?>\']').attr('selected', 'selected'); document.form.submit(); return false;"><?php echo $user ?></a> &nbsp;
		<?php } } ?>
		</td>
		<td colspan="3">
			<a href="export.php?type=txt"><img src="images/icons/export_txt.png" title="Export to TXT" alt="Export to text" />
			<a href="export.php?type=csv"><img src="images/icons/export_excel.png" title="Export to CSV" alt="Export to CSV" />
			<a href="export.php?type=csv&mode=outlook"><img src="images/icons/export_outlook.png" title="Export to Outlook CSV" alt="Export to Outlook CSV" />
			<a href="export.php?type=ics"><img src="images/icons/export_ical.png" title="Export to iCal" alt="Export to iCal" />
			<a href="export.php?type=html"><img src="images/icons/export_html.png" title="Export to HTML" alt="Export to HTML" />
			<a href="export.php?type=xml"><img src="images/icons/export_xml.png" title="Export to XML" alt="Export to XML" />
			<a href="export.php?type=sql"><img src="images/icons/export_sql.png" title="Export to SQL" alt="Export to SQL" />
			<a href="export.php?type=rss"><img src="images/icons/export_rss.png" title="RSS Feed" alt="RSS Feed" />
		</td>
	</tr>

	<tr class="noprint">
		<td colspan="2" style="padding: 2px 0px">Projects:
		<?php foreach($projects as $tag) { ?>
			<a class="proj" href="#" onclick="$('#user option[value=\'<?php if (isset($_SESSION['last_user'])) { echo $_SESSION['last_user']; } ?>\']').attr('selected', 'selected'); $('#task').val('#<?php echo $tag ?>'); $('#search').click(); return false;">
				<img src="images/icons/proj.png" />
				<?php echo $tag ?>
			</a> 
		<?php } ?>
		</td>
		<td colspan="6" style="padding: 2px 0px">Contexts:
		<?php foreach($contexts as $tag) { ?>
			<a class="context" href="#" onclick="$('#user option[value=\'<?php if (isset($_SESSION['last_user'])) { echo $_SESSION['last_user']; } ?>\']').attr('selected', 'selected'); $('#task').val('@<?php echo $tag ?>'); $('#search').click(); return false;">
				<img src="images/icons/tag.png" />
				<?php echo $tag ?>
			</a> 
		<?php } ?>
		</td>
	</tr>


	<?php
	$cur_user = '_';
	foreach($tasks as $task) {
		if ($cur_user != $task['user'] and ($cur_user != 'All' or $task['user'] != '')) {
			$cur_user = $task['user'];
			$date_marker = false;
			if (empty($cur_user)) {
				$cur_user = 'All';
			}
			
			if (!empty($tasks_count)
					and isset($hours[0])
					and $hours[0]['user'] == $cur_user) {
				$row = array_shift($tasks_count);
				$user_tasks = $row['tasks'];
			} else {
				$user_tasks = 0;
			}

			if (!empty($hours) and $hours[0]['user'] == $cur_user) {
				$row = array_shift($hours);
				$work_hours = $row['work'];
				$work_hours = ceil($work_hours / 60);
				$percent = $work_hours * 10 / 4;
				if ($percent > 100) {
					$percent = 100;
				}
				$work_percent = sprintf('(%.2f%%)', $percent);

				$work_padding = $work_hours * 10;
				if ($work_padding > 400) {
					$work_padding = 400;
				}
			} else {
				$work_hours = false;
			}

			if (!empty($xp) and $xp[0]['user'] == $cur_user) {
				$row = array_shift($xp);
				$xp_points = $row['xp']; // - floor($row['delay'] / 10);
				if ($xp_points < 0) {
					$xp_points = 0;
				}
			} else {
				$xp_points = 0;
			}

			$xp_level = floor(pow($xp_points, 1/1.85) / 9);
			$xp_div = pow(9 * $xp_level, 1.85);
			$xp_run = $xp_points - $xp_div;
			$xp_div = pow(9 * ($xp_level + 1), 1.85) - $xp_div;
			$xp_remaining = ceil(pow(9 * ($xp_level + 1), 1.85)) - $xp_points;

			$xp_percent = floor(100 * $xp_run / $xp_div);
			if ($xp_percent > 100) {
				$xp_percent = 100;
			}
	?>
	<tr class="user user_page <?php echo($cur_user != 'All' ? 'newpage' : '')?>">
		<td colspan="9" class="summary">
			<h2><a name="<?php echo $cur_user?>"><?php echo ucwords($cur_user) ?></a></h2>
			<?php
			if ($work_hours !== false) {
				echo ' ' . $work_hours . ' hours ' . $work_percent;
				if ($xp_points !== false) {
					echo '<div class="points"><img src="images/icons/star.png" /> XP: '.$xp_points.'</div><br />';
					echo '<div class="progress ui-progressbar ui-widget ui-widget-content ui-corner-all" style="float: right">
							<div style="margin-left: 244px; width: 150px; position: absolute; text-align: right;">Next Level: '.$xp_remaining.'</div>';
					echo '<div style="width: '.$xp_percent.'%;" class="ui-progressbar-value ui-widget-header ui-corner-left"></div>';
					echo '</div> ';
				}
				echo '<div class="progress ui-progressbar ui-widget ui-widget-content ui-corner-all">
					<div style="width: '.$percent.'%;" class="ui-progressbar-value ui-widget-header ui-corner-left"></div>
					</div>
					<br />';
			}
			?>
		</td>
	</tr>
	<?php
			$type = '';
		}
		
		if ($type != intval($task['completed'] / 100)) {
			$type = intval($task['completed'] / 100);
			echo '<tr class="user"><td></td><td colspan="6"><em>'.($type == 1?'[Achievements]':'[Quests ('.$user_tasks.')]').'</em></td>
			<td class="noprint"><input type="submit" name="action" value="update" /></td></tr>';
		}
		
		if ($action != 'search' and 
				substr($order, 0, 4) == 'date' and 
				!$date_marker and 
				$task['completed'] == 0 and 
				$task['priority'] >= 0 and 
				$task['date'] >= date('Y-m-d')) {
			$date_marker = true;
			echo '<tr class="user neverprint ' . $priorities[$task['priority']] . '">
			<td class="normal">&nbsp;</td>
			<td class="normal" colspan="6">
				<strong>
				<img src="images/icons/arrow_up.png" alt="up" /> Late
				<hr />
				<img src="images/icons/arrow_down.png" alt="down" /> On Time
				</strong>
			</td></tr>';
		}
		
		
	///////////////// TASKS

	if ($task['date'] == '0000-00-00 00:00:00' or empty($task['date'])) {
		$task_date = '&nbsp;';
	} else {
		if (substr($task['date'], 10) == ' 00:00:00') {
			$task_date = date($date_format, strtotime(substr($task['date'], 0, 10)));
		} else {
			$task_date = date($date_format . ' g:ia', strtotime($task['date']));
		}
	}

	?>
	<tr class="task <?php echo ($task['completed'] >= 100?'completed':$priorities[$task['priority']]) ?> user <?php echo (($task['priority'] == -1 or $task['recur'] != '') ? 'noprint' : '') ?>">
		<td class="noprint">
		<?php
			$status = '';
			if (isset($_POST['id']) and $action == 'edit' and $_POST['id'] == $task['id']) {
				$status = 'editing';
			} elseif (!$task['completed']) {
				$status = '<div class="task_progress ui-progressbar ui-widget ui-widget-content ui-corner-all" title="Task Progress. Click to start timing current work." onclick="start_time($(this), '.$task['id'].', '.$task['completed'].')"></div>';
			} else {
				$status = '<div class="task_progress ui-progressbar ui-widget ui-widget-content ui-corner-all"'.($task['completed'] < 100 ? ' title="Task Progress. Click to start timing current work." onclick="start_time($(this), '.$task['id'].', '.$task['completed'].')"' : '').'>';
				$status .= '<div style="width: '.$task['completed'].'%;" class="ui-progressbar-value ui-widget-header ui-corner-left"></div>';
				$status .= '</div>';
			}
			echo $status;
		?>
		</td>
		<td valign="top">
			<?php
				if (preg_match_all("|(?<url>https?://[^ \n]+)|", $task['task'], $matches)) {
					foreach ($matches['url'] as $url) {
						$task['task'] = str_replace($url, '[<a href="'.$url.'" target="_blank">link</a>]', $task['task']);
					}
				}
				
				// Handle projects and contexts
				$task['projects'] = array();
				$task['contexts'] = array();
				if (preg_match_all('/(^|[[:space:]])(?<tag>[#@][a-zA-Z0-9]+)/', $task['task'], $matches)) {
					foreach ($matches['tag'] as $tag) {
						if ($tag[0] == '#') {
							$tag_type = 'proj';
							$tag_image = 'proj';
							$task['projects'][] = substr($tag, 1);
						} elseif ($tag[0] == '@') {
							$tag_type = 'context';
							$tag_image = 'tag';
							$task['contexts'][] = substr($tag, 1);
						}
						
						$task['task'] = preg_replace("|$tag|", '<a href="" class="'.$tag_type.'" onclick="$(\'#task\').val(\''.$tag.'\'); $(\'#search\').click(); return false;">
							<img src="images/icons/'.$tag_image.'.png" />
							'.substr($tag, 1).'
							</a>', $task['task'], 1);
					}
				}
			?>
			<?php echo $task['task'] ?>
			<?php if ($task['notes']) { ?>
				<img src="images/icons/note.png" alt="notes" title="Notes" />
			<?php } ?>
			
			
			
			
			<div class="box details">
			<span class="secondary">Title</span><br />
			<?php echo $task['task'] ?><br />
			<div class="clr"></div>
			<br />
			<div class="lst">
				<span class="secondary">Contexts</span><br />
				<?php 
				if (empty($task['contexts'])) {
					echo 'N/A';
				} else {
					foreach ($task['contexts'] as $tag) {
						echo '<a href="" class="context" onclick="$(\'#task\').val(\'@'.$tag.'\'); $(\'#search\').click(); return false;">
						<img src="images/icons/tag.png" />
						'.$tag . '
						</a><br />';
					}
				}
				?>
			</div>
			<div class="lst">
				<span class="secondary">Projects</span><br />
				<?php 
				if (empty($task['projects'])) {
					echo 'N/A';
				} else {
					foreach ($task['projects'] as $tag) {
						echo '<a href="" class="proj" onclick="$(\'#task\').val(\'#'.$tag.'\'); $(\'#search\').click(); return false;">
						<img src="images/icons/proj.png" />
						'.$tag . '
						</a><br />';
					}
				}
				?>
			</div>
			
			<span class="secondary">Priority</span><br />
			<span class="<?php echo $priorities[$task['priority']] ?>"><?php echo $priorities[$task['priority']] ?></span><br />
			
			<?php if ($task['date'] != '0000-00-00 00:00:00' and !empty($task['date'])) { ?>
				<span class="secondary">Date</span><br />
				<?php echo $task_date ?><br />
			<?php } ?>
			<span class="secondary">Progress</span><br />
			<div class="task_progress ui-progressbar ui-widget ui-widget-content ui-corner-all" style="margin: 0px">
				<div style="width: <?php echo $task['completed'] ?>%;" class="ui-progressbar-value ui-widget-header ui-corner-left">
					&nbsp;<?php echo $task['completed'] ?>%
				</div></div>
			
			<span class="secondary">Duration</span><br />
			worked <?php echo round($task['history_minutes'] / 60, 2) ?> of estimated <?php echo ($task['duration'] / 60) ?> hours<br />
			
			<br />
			<br class="clear" />
			<div class="tabs">
				<ul>
					<li><span class="secondary"><a href="#history<?php echo $task['id'] ?>">History</a></span></li>	
			<?php 
			if ($task['notes']) {
				if (preg_match_all("|(?<url>https?://[^ \n]+)|", $task['notes'], $matches)) {
					foreach ($matches['url'] as $url) {
						$task['notes'] = str_replace($url, '[<a href="'.$url.'" target="_blank">link</a>]', $task['notes']);
					}
				}
				echo '<li><span class="secondary"><a href="#notes'.$task['id'].'">Notes</a></span></li>';
			}
			?>
				</ul>
			
			
			<div id="history<?php echo $task['id'] ?>">
			<?php if ($task['history']): ?>
				<div class="box" style="height: 150px; overflow: auto">
				<?php
				$first_log = true;
				foreach ($task['history'] as $log) {
					echo '<div class="secondary">on '.date('M d, Y', strtotime($log['timestamp']));
					if (!empty($log['time'])) {
						echo ' - took ' . format_time($log['time']);
					}
					echo '</div><hr />';
					echo $log['msg'] . '<br />';
				}
				echo '</div>';
			?>
			<?php endif ?>
			<br /><input size="40" />
			<button onClick="$('#msg').val($(this).prev().val()); $('#id').val('<?php echo $task['id'] ?>'); $('#action').val('msg'); document.forms[0].submit(); return false;">Add</button>
			</div>
			<?php if ($task['notes']): ?>
				<div id="notes<?php echo $task['id'] ?>" class="box"><?php echo $task['notes'] ?></div>
			<?php endif ?>
			</div>
			</div>
			
			
		</td>
		<td width="140" nowrap="nowrap" valign="top">
		<?php echo $task_date ?>
		</td>
		<td class="noprint" valign="top">&nbsp;<?php echo $recuring[$task['recur']] ?></td>
		<td class="noprint" nowrap="nowrap" valign="top">&nbsp;<?php echo $durations[$task['duration']] ?></td>
		<td class="noprint" valign="top"><?php echo $priorities[$task['priority']] ?></td>
		<td class="noprint" valign="top"><?php echo $task['bug'] ? '<img src="images/icons/bug.png" />' : '&nbsp;' ?></td>
		<td class="noprint" nowrap="nowrap" valign="top">
			
			<img src="images/icons/edit.png" onClick="$('#id').val(<?php echo $task['id'] ?>); $('#action').val('edit'); document.forms[0].submit()" />
			<?php if ($task['completed'] < 100) { ?>
			<input type="checkbox" name="complete[]" value="<?php echo $task['id'] ?>" <?php if ($task['completed'] == 100) echo 'checked'; ?> />
			<img src="images/icons/delete.png" onClick="$('#id').val(<?php echo $task['id'] ?>); $('#action').val('delete'); document.forms[0].submit()" />
			<img src="images/icons/check.png" onClick="$('#id').val(<?php echo $task['id'] ?>); $('#action').val('complete'); document.forms[0].submit()" />
			<?php } ?>
		</td>
	</tr>
	<?php } ?>
	<tr class="noprint">
		<td colspan="9" align="right">
			<input type="submit" name="action" value="update" />
		</td>
	</tr>
	</table>
	<?php if (!empty($password)) { ?>
	<input type="button" name="action" value="logout" onClick="$('#action').val('logout'); this.form.submit()" />
	<?php } ?>
	</form>

	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" src="js/main.js"></script>

</body>
</html>