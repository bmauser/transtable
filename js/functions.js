$(document).ready(function () {
	
	transtable.init_table();
	
	transtable.show_hide_load();
	
	$(document).ajaxError(function(event, request, settings){
	
		if(request.error().status == '520' || request.responseText){
			alert(request.responseText);
		}
		//else
		//	alert("Error requesting page: " + settings.url);
	})
});


/**
 * transtable object - namespace
 */
transtable = {}


/**
 * Last edited cell
 */
transtable.last_edit = null;


/**
 * CKeditor settings
 */
transtable.CKeditor_config = {
	customConfig : '',
	language : 'en',
	toolbar : 'Transtable',
	toolbar_Transtable :
		[
		 	['transtable_save'],
			['Cut','Copy','Paste','PasteText','PasteFromWord'],
			['Undo','Redo','-','SelectAll','RemoveFormat'],
			['Link','Unlink'],
			['Format'],
			'/',
			['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
			['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
			['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
			['Sourcedialog', 'SpecialChar', 'transtable_cleanhtml']
		],
	enterMode : CKEDITOR.ENTER_BR,
	shiftEnterMode: CKEDITOR.ENTER_P,
	disableAutoInline: true,
	format_tags : 'h1;h2;h3;h4',
	on: {
		blur: function(event){
			transtable.save_translation($(event.editor.element.$).attr('id'));
	        },
	        
	        paste: function(event){
	        	event.data.dataValue = transtable.strip_tags(event.data.dataValue);
	        },
	        
	        instanceReady: function(event){
	        	$(event.editor.element.$).attr('title','');
	        },
	},
	extraPlugins: 'sourcedialog,transtable_cleanhtml,transtable_save',
	removePlugins: 'sourcearea'
};


/**
 * Do not auto initialize all HTML editors
 */
CKEDITOR.disableAutoInline = true;


/**
 * Initialize translation table
 */
transtable.init_table = function(){
	
	$('#transtable_folders, #transtable_sign').on('click', 'a', function (e){
		if(transtable.last_edit)
			transtable.save_translation(transtable.last_edit);
	})

	$('#transtable_table').on('mouseover', '.transtable_edit_div', function(e){
		//if($('#transtable_enable_html_editor').val() == 1)
		transtable.init_html_editor($(e.currentTarget).attr('id'));
	});
	
	$('#transtable_table').on('click', '.transtable_edit_div', function(e){
		transtable.edit_translation($(e.currentTarget).attr('id'));
	});

	
	if($('#transtable_enable_edit_index').val() == '1'){
		$('#transtable_table').on('dblclick', '.transtable_index_cell', function(e){		
			transtable.edit_index(e.currentTarget);
		});
	}
	
	if($('#transtable_enable_add_translation').val() == '1'){
		$('#transtable_add_index').on('click', function(e){		
			transtable.add_index();
		});
	}
	
	if($('#transtable_enable_delete_translation').val() == '1'){
		$('#transtable_table').on('click', '.transtable_del_link', function(e){		
			e.preventDefault();
			transtable.delete_index(transtable.get_translation_id(e.target));
		});
	}
	
	$('#transtable_show_files input').on('click', function(e){	
		transtable.show_hide_column($(e.target).attr('data-transtable-column-index'));
	});
}


/**
 * Edits translation
 */
transtable.edit_translation = function(edit_div_id){
	
	transtable.last_edit = edit_div_id;

	//if($('#transtable_enable_html_editor').val() != 1){
	//	transtable.cancel_content[edit_div_id] = $('#' + edit_div_id).html();	
	//}
}


/**
 * Inits HTML editor
 */
transtable.init_html_editor = function(edit_div_id){
	if(!CKEDITOR.instances[edit_div_id])
		CKEDITOR.inline(edit_div_id, transtable.CKeditor_config);
}


/**
 * Edits index
 */
transtable.edit_index = function(td_element){

	var cell = $(td_element);
	var translation_id = transtable.get_translation_id(td_element);
	
	// if already editing
	if($('#transtable_index_input' + translation_id).length)
		return;
	
	var old_index = $('#transtable_index' + translation_id).val();
	var input_div = $('#transtable_index_div' + translation_id);
	
	var input = $('<input class="transtable_index_input" id="transtable_index_input' + translation_id + '" type="text" value="' + old_index + '" />');
	var save_button = $('<button class="transtable_btt transtable_btt_orange transtable_btt_small" type="button">Save</button>');
	var cancel_button = $('<button class="transtable_btt transtable_btt_orange transtable_btt_small" type="button">Cancel</button>');
	
	save_button.on('click', function(e){		
		transtable.rename_index(transtable.get_translation_id(e.target));
	});
	
	cancel_button.on('click', function(e){
		transtable.cancel_edit_index(transtable.get_translation_id(e.target));
	});
	
	input_div.html('');
	input_div.append(input, save_button, cancel_button);
	input.focus();
}


/**
 * Cancels editing translation
 */
/*
transtable.cancel_edit_translation = function(translation_id, translation){
	
	if(translation)
		transtable.cancel_content[translation_id] = translation;
	
	if(CKEDITOR.instances['transtable_translation' + translation_id])
		CKEDITOR.instances['transtable_translation' + translation_id].destroy();
	
	$('#transtable_translation' + translation_id).html(transtable.cancel_content[translation_id]);
	delete transtable.cancel_content[translation_id];
}
*/


/**
 * Cancels editing index
 */
transtable.cancel_edit_index = function(translation_id, new_index){
	
	var input = $('#transtable_index' + translation_id);
	
	if(new_index)
		input.val(new_index);
	
	$('#transtable_index_div' + translation_id).html(input.val());
}


/**
 * Saves translation
 */
transtable.save_translation = function(edit_div_id){
	
	var edit_div = $('#' + edit_div_id);
	var cell = edit_div.parent();
	var translation_id = transtable.get_translation_id(cell);
	
	var column = cell[0].cellIndex;
	//var row = cell[0].parentNode.rowIndex;
	
	var file_name = $.trim($('#transtable_file_name' + column).val());
	var index = $.trim($('#transtable_index' + translation_id).val());
	
	// if ck editor is enabled
	if(CKEDITOR.instances[edit_div_id]){
		var translation = $.trim(CKEDITOR.instances[edit_div_id].getData());
	}
	else
		translation = $.trim(edit_div.html());
	
	$.ajax({
		type: 'POST',
		url: '?transtable_action=savetranslation',
		data: {file_name:file_name, index:index, translation:translation, folder:$('#transtable_open_folder').val()},
		success: function(reponse){
			if(reponse == 1){
				var file_name_no_ext = file_name.substr(0, file_name.lastIndexOf('.')) || file_name;
				transtable.show_notice('Saved: ' + index + ' [' + file_name_no_ext + ']');				
				transtable.last_edit = null;
			}
		}
	});
}


/**
 * Shows save indicator
 */
transtable.show_notice = function(message){
	
	var notice_div = $('<div class="transtable_notice">' + message + '</div>');
	$("body").append(notice_div);
	$(notice_div).fadeIn('slow', function() {
		$(notice_div).fadeOut(900, function() {
			$(notice_div).remove();
		});
	});
}


/**
 * Renames index
 */
transtable.rename_index = function(translation_id){
	
	var old_index = $.trim($('#transtable_index' + translation_id).val());
	var new_index = $.trim($('#transtable_index_input' + translation_id).val());
	var index_unique = 1;
	
	if(old_index != new_index){
		
		// check that new index is unique
		$('[id^="transtable_index_div"]').each(function(){
			if($.trim($(this).html()) == new_index){
				index_unique = 0;
				return false;
			}
		});
		
		if(index_unique){
			$.ajax({
				type: 'POST',
				url: '?transtable_action=saveindex',
				data: {old_index:old_index, new_index:new_index, folder:$('#transtable_open_folder').val()},
				success: function(e){
					transtable.cancel_edit_index(translation_id, new_index);
				}
			});
		}
		else{
			alert("Translation with index '" + new_index + "' already exists");
			return;
		}
	}
	else
		transtable.cancel_edit_index(translation_id);
}


/**
 * Deletes index
 */
transtable.delete_index = function(translation_id){
	
	var index = $.trim($('#transtable_index' + translation_id).val());
	
	if(confirm('Are you shure you want to delete all translations in the row?')){
		$.ajax({
			type: 'POST',
			url: '?transtable_action=deleteindex',
			data: {index:index, folder:$('#transtable_open_folder').val()},
			success: function(reponse){
				if(reponse == 1){
					var row = $('#transtable_row' + translation_id);
					row.hide('slow', function(){
						row.remove();
					});					
				}
			}
		});
	}
}


/**
 * Returns translation id
 */
transtable.get_translation_id = function(element_in_row){
	return $(element_in_row).closest('tr').attr('data-transtable-translation-id');
}


/**
 * Adds a new table row
 */
transtable.add_index = function(){
	
	// last table row
	var last_row = $('#transtable_table tr').last();
	var template = last_row.html();
	var old_translation_id = last_row.attr('data-transtable-translation-id');
	var new_translation_id = Math.random().toString(36).substring(2, 12);
	
	var patt = new RegExp(old_translation_id, 'g');
	
	template = 
		'<tr class="transtable_hidden" id="transtable_row' + new_translation_id + '" data-transtable-translation-id="' + new_translation_id + '">' + 
		template.replace(patt, new_translation_id) + 
		'</tr>';
	
	// insert row after last row
	last_row.after(template);
	
	// clean old values
	$('#transtable_index_div' + new_translation_id).html(new_translation_id);
	$('#transtable_index' + new_translation_id).val(new_translation_id);
	$('#transtable_index_div' + new_translation_id).html(new_translation_id);
	$('#transtable_row' + new_translation_id + ' .transtable_edit_div').html('');
	
	// show new row
	$('#transtable_row' + new_translation_id).show('slow');
}


/**
 * Shows or hides table column
 */
transtable.show_hide_column = function(column_nubmer, on_off){
	
	var column = $('#transtable_table td:nth-child(' + column_nubmer + '), #transtable_table th:nth-child(' + column_nubmer + ')');
	
	if(typeof on_off === 'undefined'){
		if(column.first().is(':visible'))
			column.hide();
		else
			column.show();
	}
	else{
		if(!on_off)
			column.hide();
		else
			column.show();
	}
	
	transtable.show_hide_save();
}


/**
 * Saves hidden/visible columns
 */
transtable.show_hide_save = function(){
	
	if(!localStorage['transtable_columns_state'])
		var data = {};
	else
		data = JSON.parse(localStorage['transtable_columns_state']);

	$('#transtable_show_files input').each(function(){
		var checked = $(this).prop('checked') ? 1 : 0;
		data[$(this).attr('id')] = checked;
	});
	
	localStorage['transtable_columns_state'] = JSON.stringify(data);
}


/**
 * Loads hidden/visible state of columns
 */
transtable.show_hide_load = function(){
	
	if(!localStorage['transtable_columns_state'])
		return;

	var data = JSON.parse(localStorage['transtable_columns_state']);

	if(!data)
		return;
	
	// number of columns
	if($('#transtable_enable_delete_translation').length)
		var column_number_start = 3;
	else
		var column_number_start = 2;
	
	$('#transtable_show_files input').each(function(){
		var chkbox = $(this);
		var id = chkbox.attr('id');
		
		if(typeof data[id] != 'undefined'){
			if(data[id] == 1)
				chkbox.prop('checked', true);
			else if(data[id] == 0){
				chkbox.prop('checked', false);
				transtable.show_hide_column(chkbox.attr('data-transtable-column-index'), 0);
			}
		}
	});
}


/**
 * From: http://phpjs.org/functions/strip_tags
 */
transtable.strip_tags = function(input) {
	
	allowed = ['<blockquote>', '<p>', '<br>', '<strong>', '<em>', '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>', '<ol>', '<ul>', '<li>', '<img>', '<a>', '<i>', '<b>']
	
	var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
	commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
		return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	});
}

