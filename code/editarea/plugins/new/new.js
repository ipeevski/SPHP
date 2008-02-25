/**
 * Plugin designed for test prupose. It add a button (that manage an alert) and a select (that allow to insert tags) in the toolbar.
 * This plugin also disable the "f" key in the editarea, and load a CSS and a JS file
 */  
var EditArea_new= {
	/**
	 * Get called once this file is loaded (editArea still not initialized)
	 *
	 * @return nothing	 
	 */	 	 	
	init: function(){	
		//	alert("test init: "+ this._someInternalFunction(2, 3));
	}
	/**
	 * Returns the HTML code for a specific control string or false if this plugin doesn't have that control.
	 * A control can be a button, select list or any other HTML item to present in the EditArea user interface.
	 * Language variables such as {$lang_somekey} will also be replaced with contents from
	 * the language packs.
	 * 
	 * @param {string} ctrl_name: the name of the control to add	  
	 * @return HTML code for a specific control or false.
	 * @type string	or boolean
	 */	
	,get_control_html: function(ctrl_name){
		switch(ctrl_name){
			case "new_button":
				// Control id, button img, command
				return parent.editAreaLoader.get_button_html('new_button', 'new.gif', 'doc_new', false, this.baseURL);
		}
		return false;
	}
	
	
	/**
	 * Executes a specific command, this function handles plugin commands.
	 *
	 * @param {string} cmd: the name of the command being executed
	 * @param {unknown} param: the parameter of the command	 
	 * @return true - pass to next handler in chain, false - stop chain execution
	 * @type boolean	
	 */
	,execCommand: function(cmd, param){
		// Handle commands
		switch(cmd){
			case "doc_new":
				var file_name = prompt('Enter new filename:', parent.full_path);
				var new_file= {'id': file_name, 'syntax': 'php', 'title': file_name};
				parent.editAreaLoader.openFile('code', new_file);
				parent.editAreaLoader.insertTags('code', "<?php\n	", "\n?>");
				return false;
		}
		// Pass to next handler in chain
		return true;
	}
};

// Adds the plugin class to the list of available EditArea plugins
editArea.add_plugin("new", EditArea_new);

