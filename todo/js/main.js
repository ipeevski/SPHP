var timer;
var time = 0;
var timed_task;
function start_time(div, task, progress) {
	if (time == 0) {
		clk_date = new Date();
		time = clk_date.getTime();
		timed_task = task;
		
		//div.children().remove();
		//div.html('');
		
		div.prepend('<div style="position: absolute">&nbsp;<span id="timer"></span></div>');
		show_date();
	} else if (task == timed_task) {
		//div.children().remove();
		$('#id').val(task);
		
		div.after('<div class="tasklog box">\
				<input type="hidden" name="time" value="'+Math.round((clk_date.getTime() - time) / 1000)+'" />\
				Done: <select name="perc">\
				<option value="0"' + (progress == 0 ? ' selected="selected"' : '') + '>0%</option>\
				<option value="20"' + (progress == 20 ? ' selected="selected"' : '') + '>20%</option>\
				<option value="40"' + (progress == 40 ? ' selected="selected"' : '') + '>40%</option>\
				<option value="60"' + (progress == 60 ? ' selected="selected"' : '') + '>60%</option>\
				<option value="80"' + (progress == 80 ? ' selected="selected"' : '') + '>80%</option>\
				<option value="100">100%</option>\
				</select><br />\
				Note: <br /><textarea name="note"></textarea><br />\
				<input type="submit" name="action" value="log" />\
				<a href="" onclick="$(\'.tasklog\').remove(); $(\'#timer\').remove(); return false">cancel</a>\
				</div>');

		time = 0;
		timed_task = 0;
		clearTimeout(timer);
	}
}

function show_date() {
	clk_date = new Date();

	var s = Math.round((clk_date.getTime() - time) / 1000);
	var m = Math.floor(s / 60);
	s = s % 60;
	if (s < 10) s = '0' + s;
	var h = Math.floor(m / 60);
	m = m % 60;
	if (m < 10) m = '0' + m;
	var d = Math.floor(h / 24);
	h = h % 24;
	if (h < 10) h = '0' + h;
	var str = h+':'+m+':'+s;
	if (d > 0) str = d + 'd ' + str;
	
	$('#timer').html(str);
	timer = setTimeout("show_date()", 1000);
}

$(document).ready(function() {
	$('#task').focus();

	$('tr.task').hover(function() {
		$(this).children().addClass('active');
		if (!$(this).hasClass('completed')) {
			$(this).find('.task_progress:first').prepend('<img src="images/icons/time.png" style="float: right" />');
		}
	}, function() {
		$(this).children().removeClass('active');
		$('.task_progress').find('img').remove();
	});

	$.ui.dialog.defaults.bgiframe = true;
	$('tr.task td').click(function() {
		$(this).find('.details').dialog({ 
			title: 'Task Details',
			modal: true,
			width: 600, 
			minHeight: 400
		});
	});
	
	$('.tabs').tabs();
	
	$('.date').click(function() { $(this).val('') });
	$('.date').change(function() { $('#time').show() });
	$('.date').datepicker({ 
		dateFormat: 'yy-mm-dd',
		showButtonPanel: true,
		showAnim: 'fadeIn',
		constrainInput: false
	});
});
