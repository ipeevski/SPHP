<?php
$dirs = array('./');
$dir = $_REQUEST['dir'] ? $_REQUEST['dir'] : $dirs[0];
$_SESSION['dir'] = stripslashes($_POST['dir']);
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Editor</title>
	<script type="text/javascript" src="dhtmlxTree/dhtmlxcommon.js"></script>	
	<script type="text/javascript" src="dhtmlxTree/dhtmlxtree.js"></script>
	<script type="text/javascript" src="ajax.js"></script>
	<script type="text/javascript">
		var full_path = '<?=$dir?>';
	</script>
	<link rel="stylesheet" href="dhtmlxTree/dhtmlxtree.css" type="text/css" />
	<link rel="stylesheet" href="main.css" type="text/css" />
</head>
<body>
<form action='' method='post'>
<table>
<tr>
	<td valign="top">
	<div class="dtree" style="width: 200px; scroll: auto">
		<h1>Files</h1>
		<form method="post">
		dir: 
		<select name="dir" onchange="this.form.submit();">
			<option value=""></option>
		<?php foreach ($dirs as $d) { ?>
			<option value="<?=$d?>" <?=($dir == $d ? 'selected="selected"':'')?>><?=$d?></option>
		<?php } ?>
		</select>
		</form>
	
		<div id="treeBox" style="border: 1px solid gray; min-height: 580px; overflow: auto"></div>
		<script type="text/javascript">  
		var tree = new dhtmlXTreeObject('treeBox',"100%","100%",'<?=$dir?>');
		tree.setImagePath("dhtmlxTree/imgs/csh_vista/"); 
		//tree.attachEvent("onClick",onNodeSelect)//set function object to call on node select
		tree.attachEvent("onDblClick",onNodeDblClick)//set function object to call on node select
		tree.enableHighlighting(1);
		tree.setXMLAutoLoading("ajax/files.php"); 
		tree.loadXML("ajax/files.php?id=<?=$dir?>");
		
		
		// Needs dhtmlXMenu extension - http://www.dhtmlx.com/docs/products/dhtmlxMenu/index.shtml
		/* init menu 
		aMenu=new dhtmlXContextMenuObject('120',0,"dhtmlxTree/codebase/sources/imgs/csh_vista/"); 
		aMenu.menu.loadXML("menu/_context.xml"); 
		aMenu.setContextMenuHandler(onMenuClick); 
		//init tree 
		
		tree.enableContextMenu(aMenu); 
		//link context menu to tree 
		function onMenuClick(id){ 
			alert("Menu item "+id+" was clicked");
		}
		*/
		function onNodeSelect(nodeId) {
			tree.selectItem(nodeId, false, false);
			tree.focusItem(nodeId);
		}
		
		function onNodeDblClick(nodeId){
			if (nodeId.substring(nodeId.length-1) != '\\') {
				open_codefile(nodeId);
				ajax('ajax/loadfile.php?file='+nodeId+'&escape=javascript', 'load_codefile');
				ajax('ajax/filetime.php?file='+nodeId, 'set_mtime');
			} else {
				tree.openItem(nodeId);
			}
			
			return 0;
		}
		
		</script>
		</div>
	</div>
	</td>
	<td valign="top">
		<h1>Editor</h1>
<?php 
$name = 'code';
include('editarea.php');
?>
	</td>
	<td valign="top">
		<h1>Chat</h1>
		Remote:<br />
		<textarea id="chat" name="chat" cols="40" rows="10" readonly="readonly"></textarea>
		<br />
		Local:<br />
		<textarea id="say" name="say" cols="40" rows="10" onchange="ajax('ajax/savefile.php?file=../.chat&content=' + encodeURIComponent(document.getElementById('chat').value), 'chat');"></textarea>
		<br />
		Messages: <br />
		<div id="msgs" style="border: 1px solid gray; width: 344px; min-height: 195px;">
		</div>
	</td>
</tr>
</table>
	</fieldset>
</form>


<script type="text/javascript">
	function monitor()
	{
		if (editAreaLoader.getCurrentFile('code') && editAreaLoader.getCurrentFile('code').id) {
			ajax('ajax/filetime.php?file='+editAreaLoader.getCurrentFile('code').id, 'sync');
		}
		ajax('ajax/loadfile.php?file=../.chat&escape=html', 'chat');
		setTimeout('monitor()', 1000);
	}
	
	function chat(text) 
	{
		document.getElementById('chat').value = text;
	}
	
	ajax('ajax/loadfile.php?file=../.chat&escape=html', 'chat');
	//monitor();
</script>
</body>
</html>