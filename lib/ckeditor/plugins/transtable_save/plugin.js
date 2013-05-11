CKEDITOR.plugins.add('transtable_save', {
	
	init: function(editor){
		var pluginName = 'transtable_save';

		editor.addCommand( pluginName,{
			exec : function( editor ){
				transtable.save_translation(editor.name);
			},
		
			canUndo : true
		});

		if ( editor.ui.addButton ) {
			editor.ui.addButton('transtable_save', {
				label: 'Save',
				command: pluginName
			});
		}
	}
});
