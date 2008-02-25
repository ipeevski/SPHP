<script type="text/javascript" 
src="editarea/edit_area_full.js"></script>
<script language="Javascript" type="text/javascript">
	
var file_num = 1;
var lastfile = '';
		
		// initialisation
		editAreaLoader.init({
			id: "<?=$name?>"	// id of the textarea to transform		
//			,font_size: "8"
			,font_family: "verdana, monospace"
			,start_highlight: true	// if start with highlight
			,allow_resize: "both"
			,allow_toggle: false
			,is_multi_files: true
			,language: "en"
			,syntax: "php"	// "css", "html", "js"
			,syntax_selection_allow: "css,html,js,php,xml"
			,toolbar: "new_button, save, |, search, go_to_line, fullscreen, |, undo, redo, |, select_font, |, syntax_selection, |, change_smooth_selection, highlight, reset_highlight, |, help" // load, 
			,plugins: "new, sync"
			,load_callback: "my_load"
			,save_callback: "filesave"
			,EA_load_callback: "editAreaLoaded"
		});
		

		
		// callback functions
		function filesave(id, content){
			filename = editAreaLoader.getCurrentFile(id).id;
			
			ajax('ajax/savefile.php?file='+filename+'&content='+encodeURIComponent(content), '');
			editAreaLoader.setFileEditedMode('<?=$name?>', filename, false);
			ajax('ajax/filetime.php?file='+filename, 'set_mtime');
		}
				
		function my_load(id){
			editAreaLoader.setValue(id, "The content is loaded from the load_callback function into EditArea");
		}
		
		function test_setSelectionRange(id){
			editAreaLoader.setSelectionRange(id, 100, 150);
		}
		
		function test_getSelectionRange(id){
			var sel =editAreaLoader.getSelectionRange(id);
			alert("start: "+sel["start"]+"\nend: "+sel["end"]); 
		}
		
		function test_setSelectedText(id){
			text= "[REPLACED SELECTION]"; 
			editAreaLoader.setSelectedText(id, text);
		}
		
		function test_getSelectedText(id){
			alert(editAreaLoader.getSelectedText(id)); 
		}
		
		function editAreaLoaded(id){
			//open_file1();
//			open_file2();
		}
		
		function open_file1()
		{
			var new_file= {id: "file1", text: "<a href=\"toto\">\n\tbouh\n</a>\n<!-- it's a comment -->", syntax: 'html'};
			editAreaLoader.openFile('<?=$name?>', new_file);
		}
		
		function close_file1()
		{
			editAreaLoader.closeFile('<?=$name?>', "file1");
		}
		
		function load_codefile(text){
			//alert('setting text: ' + text);
			editAreaLoader.setValue('<?=$name?>', text);
		}
		
		function set_mtime(text){
			//alert('setting text: ' + text);
			
			editAreaLoader.getCurrentFile('<?=$name?>').mtime = text;
		}
		
		function open_codefile(filename)
		{
			//fcontent = ajax(filename);
			ext = filename.substring(filename.indexOf('.') + 1);

			var new_file= {id: filename, title: filename.substring(full_path.length), text: '', syntax: ext};
			editAreaLoader.openFile('<?=$name?>', new_file);
			lastfile = new_file;
		}
		
		function sync(mtime)
		{
//			f = parent.editAreaLoader.getCurrentFile('<?=$name?>');
//			
//			if (f.mtime != mtime) {
//				alert('file changed!');
//				editAreaLoader.setValue('<?=$name?>', 'reloading ...');
//				
//				ajax('ajax/loadfile.php?file='+f.id+'&escape=javascript', 'load_codefile');
//				ajax('ajax/filetime.php?file='+f.id, 'set_mtime');
//			}
		}
</script>
<div id="editor">
<textarea id="<?=$name?>" style="height:600px; width:600px;" name="<?=$name?>"></textarea>

<p>Custom controls:<br />
	<!--input type='button' onclick='alert(editAreaLoader.getValue(<?=$name?>"));' value='get value' />
	<input type='button' onclick='editAreaLoader.setValue(<?=$name?>", "new_value");' value='set value' />
	<input type='button' onclick='test_getSelectionRange(<?=$name?>");' value='getSelectionRange' />
	<input type='button' onclick='test_setSelectionRange(<?=$name?>");' value='setSelectionRange' />
	<input type='button' onclick='test_getSelectedText(<?=$name?>");' value='getSelectedText' />
	<input type='button' onclick='test_setSelectedText(<?=$name?>");' value='setSelectedText' />
	<input type='button' onclick='editAreaLoader.insertTags(<?=$name?>", "[OPEN]", "[CLOSE]");' value='insertTags' />
	<input type='button' onclick='doc_new()' value='new file' /-->
	<input type='button' onclick='open_file1()' value='open file 1' />
	<input type='button' onclick='close_file1()' value='close file 1' />
</p>
</div>