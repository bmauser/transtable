<?php
/**
 * Configuration.
 */

// absolute filesystem path to folder with translation files
$TTCFG['php_array_files']['root_dir'] = '/somefolder/translations'; // without / at the end

// translation file name pattern
$TTCFG['php_array_files']['file_name_pattern'] = '..\.php';

// the name of the array that holds translations
$TTCFG['php_array_files']['var_name'] = 't';

// delimiter for translations from multi dimensional arrays
$TTCFG['php_array_files']['array_delimiter'] = '|';

// page title
$TTCFG['php_array_files']['page_title'] = 'Transtable';

// enable html editor
$TTCFG['php_array_files']['enable_html_editor'] = 1;

// enable editing indexes (first column)
$TTCFG['php_array_files']['enable_edit_index'] = 1;

// enable deleting translations
$TTCFG['php_array_files']['enable_delete_translation'] = 1;

// enable add a new translations
$TTCFG['php_array_files']['enable_add_translation'] = 1;

// generated translation file newlines
$TTCFG['php_array_files']['new_lines'] = PHP_EOL;

// CSS files to include.
//$TTCFG['include_css'][] = '';


/**
 * Configuration file that will override settings in this file.
 */
$config_override_path = dirname(__FILE__) . '/config_override.php';
if(file_exists($config_override_path))
	include_once $config_override_path;

