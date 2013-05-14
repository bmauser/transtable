<!-- no translations -->
<?php if(isset($no_translations)){ ?>
Cannot find any translations. Please check path to translation files in config.php.
<?php 
return;
} 
?>

<div id="transtable_sign">
	<a href="http://code.google.com/p/transtable/" target="_blank">Transtable v<?php echo $version; ?></a>
</div>

<!-- navigation (all folders with translations) -->
<?php if(count($data) > 1 ){ ?>
<div id="transtable_folders">
	<?php foreach ($data as $_tab_name => $_null){ ?>
	<a class="transtable_btt <?php if($folder == $_tab_name){ ?>transtable_btt_blue<?php }else{ ?>transtable_btt_white<?php } ?> transtable_btt_rounded transtable_btt_small" href="<?php if($folder == $_tab_name){ ?>#<?php }else{ ?>?transtable_folder=<?php echo urlencode($_tab_name) ?><?php } ?>"><?php echo htmlspecialchars($_tab_name); ?></a>
	<?php } ?>
</div>
<?php } ?>


<?php 
	if($enable_delete_translation)
		$_column_start_index = 2;
	else
		$_column_start_index = 1;
?>


<!-- column chooser -->
<?php if(count($data[$folder]['translations']) > 1 ){ ?>
<div id="transtable_show_files">
	<span>
		<label>
			<input type="checkbox" checked="checked" id="transtable_showindexrow" data-transtable-column-index="<?php echo $_column_start_index ?>"/>
			index
		</label>
	</span>
<?php 
$_column_index = $_column_start_index+1;
foreach ($data[$folder]['translations'] as $_file_name => $_translations){ 
?>
	<span>
		<label>
			<input type="checkbox" checked="checked" id="transtable_<?php echo md5($_file_name); ?>" data-transtable-column-index="<?php echo $_column_index ?>"/>
			<?php echo transtable_strip_extension($_file_name) ?>
		</label>
	</span>
<?php 
	$_column_index++;
}
?>
</div>
<?php } ?>



<!-- table with translations -->
<table width="100%" border="0" cellpadding="2" cellspacing="5" id="transtable_table">
	
	<?php 
	$_row_index = 0;
	// for all translation indexes
	foreach ($data[$folder]['all_indexes'] as $_index => $_null){ 
	?>
	
	
	<?php if($_row_index == 0){ ?>
	<!-- first row with file names -->
	<tr>
		<?php if($enable_delete_translation){ ?>
		<th>&nbsp;</th>
		<?php } ?>

		<th class="transtable_header_cell">index</th>
		<?php 
		$_column_index = $_column_start_index;
		foreach ($data[$folder]['translations'] as $_file_name => $_translations){ 
		?>
		<th class="transtable_header_cell">
			<?php echo transtable_strip_extension($_file_name); ?>
			<input id="transtable_file_name<?php echo $_column_index ?>" type="hidden" value="<?php echo htmlspecialchars($_file_name) ?>" />
		</th>
		<?php
			$_column_index++;
		} 
		?>
	</tr>
	<?php 
		$_row_index++;
	} 
	?>
	
	<!-- translation row -->
	<?php 
		$_translation_id = 't' . md5($_file_name . rand() . microtime());
	?>
	<tr id="transtable_row<?php echo $_translation_id ?>" data-transtable-translation-id="<?php echo $_translation_id ?>">
		
		<?php if($enable_delete_translation){ ?>
		<td class="transtable_del_cell"><a href="#" class="transtable_del_link" title="Delete translation">&times;</a></td>
		<?php } ?>
		
		<td class="transtable_index_cell">
			<div id="transtable_index_div<?php echo $_translation_id ?>">
				<?php echo htmlspecialchars($_index) ?>
			</div>
			<input id="transtable_index<?php echo $_translation_id ?>" type="hidden" value="<?php echo htmlspecialchars($_index) ?>" />
		</td>
		<?php 
		foreach ($data[$folder]['translations'] as $_file_name => $_translations){ 
		?>
		<td valign="top" class="transtable_translation_cell">
			<div contenteditable="true" class="transtable_edit_div" id="transtable_translation<?php echo $_translation_id . md5($_file_name) ?>">
				<?php @eval('echo ($_translations' . $translate->get_php_index($_index) . ');'); ?>
			</div>
		</td>
		<?php } ?>
	</tr>
	<?php 
		$_row_index++;
	} 
	?>
	
</table>
<?php if($enable_add_translation){ ?>
<button class="transtable_btt transtable_btt_orange transtable_btt_medium" type="button" id="transtable_add_index">Add a new translation</button>
<?php } ?>

<div class="transtable_help">
	<ul>
		<li>Click on the translation to edit.</li>
		<?php if($enable_edit_index){ ?>
		<li>Double click on the index to edit.</li>
		<?php } ?>
		<?php if($dont_write_file){ ?>
		<li>NOTE: saving is disabled so when you refresh the page all changes will be lost.</li>
		<?php } ?>
	</ul>
</div>

<input id="transtable_open_folder" type="hidden" value="<?php echo htmlspecialchars($folder) ?>" />
<input id="transtable_enable_html_editor" type="hidden" value="<?php echo $enable_html_editor?1:0 ?>" />
<input id="transtable_enable_delete_translation" type="hidden" value="<?php echo $enable_delete_translation?1:0 ?>" />
<input id="transtable_enable_edit_index" type="hidden" value="<?php echo $enable_edit_index?1:0 ?>" />
<input id="transtable_enable_add_translation" type="hidden" value="<?php echo $enable_add_translation?1:0 ?>" />

