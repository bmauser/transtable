CKEDITOR.plugins.add('transtable_cleanhtml', {
	
	init: function(editor){
		var pluginName = 'transtable_cleanhtml';

		editor.addCommand( pluginName,{
			exec : function( editor ){
				
				var data = editor.getData();
				data = transtable.strip_tags(data);
				editor.setData(data);
			},
		
			canUndo : true
		});

		if ( editor.ui.addButton ) {
			editor.ui.addButton('transtable_cleanhtml', {
				label: 'Clean up HTML (leave only basic tags)',
				command: pluginName
			});
		}
	}
});
