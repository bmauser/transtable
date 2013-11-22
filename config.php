<?php
/**
 * Transtable configuration.
 * See documentation on: http://bojanmauser.from.hr/transtable/documentation/ for more details.
 */


// Absolute filesystem path to folder with translation files (you can organize files in subfolders)
// <TRANSTABLE_ROOT> will be replaced with full system path to Transtable directory.
$TTCFG['php_array_files']['translations_root'] = '<TRANSTABLE_ROOT>/translations'; // without / at the end

// Translation file name pattern. Transtable will search for files matching this pattern.
$TTCFG['php_array_files']['file_name_pattern'] = '..\.php';

// The name of the PHP array that holds translations
$TTCFG['php_array_files']['var_name'] = 't';

// Page title
$TTCFG['php_array_files']['page_title'] = 'Transtable';

// Enable editing indexes (first column)
$TTCFG['php_array_files']['enable_edit_index'] = 1;

// Delimiter in translation index for multi dimensional arrays
$TTCFG['php_array_files']['array_delimiter'] = '|';

// Enable deleting translations
$TTCFG['php_array_files']['enable_delete_translation'] = 1;

// Enable add a new translations
$TTCFG['php_array_files']['enable_add_translation'] = 1;

// Generated translation file newlines
$TTCFG['php_array_files']['new_lines'] = PHP_EOL;

// CSS files to include.
//$TTCFG['include_css'][] = 'css/mystyles.css';
