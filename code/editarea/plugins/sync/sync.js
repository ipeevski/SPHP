/**
 * Plugin designed for test prupose. It add a button (that manage an alert) and a select (that allow to insert tags) in the toolbar.
 * This plugin also disable the "f" key in the editarea, and load a CSS and a JS file
 */  
var EditArea_sync = {
	/**
	 * Get called once this file is loaded (editArea still not initialized)
	 *
	 * @return nothing	 
	 */	 	 	
	init: function(){	
		//	alert("test init: "+ this._someInternalFunction(2, 3));
	}
	
	,onkeydown: function(e){
		var str= String.fromCharCode(e.keyCode);
		// desactivate the "f" character

		if (!AltPressed(e) && !CtrlPressed(e) && !ShiftPressed(e)) {
			if (!this.timer) {
				this.timer = setTimeout("EditArea_sync.execCommand('sync', parent.editAreaLoader.getCurrentFile())", 10000);
			}
		}
		
		return true;
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
			case "sync":
				//alert('syncing file ' + parent.editAreaLoader.getCurrentFile('code').id);
				//parent.lastfile = parent.editAreaLoader.getCurrentFile('code');
				parent.ajax('ajax/filetime.php?file='+parent.editAreaLoader.getCurrentFile('code').id, 'sync');
			
				this.timer = 0;

				return false;
		}
		// Pass to next handler in chain
		return true;
	}
};

// Adds the plugin class to the list of available EditArea plugins
editArea.add_plugin("sync", EditArea_sync);

