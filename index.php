<?php
/**
 * Transtable
 *
 * http://code.google.com/p/transtable/
 *
 * Copyright (c) 2013 Bojan Mauser
 *
 * Released under the MIT license
 * http://www.opensource.org/licenses/mit-license.php
 *
 * @author Bojan Mauser <bmauser@gmail.com>
 * @version $Id: index.php 47 2013-04-17 16:35:29Z bmauser@gmail.com $
 */


include 'config.php';


/**
 * Find the action to do from the GET parameter.
 */
if(isset($_GET['transtable_action']) && $_GET['transtable_action'])
	$action = $_GET['transtable_action'];
else
	$action = 'index';


/**
 * Controller part
 */
try{
	// main page
	if($action == 'index'){
		
		$transtable = new transtable();
		
		if(isset($_GET['transtable_folder']) && $_GET['transtable_folder'])
			$folder = $_GET['transtable_folder'];
		else
			$folder = '/';
		
		// get all translations from folder
		$translations = $transtable->get_all_translations($folder);
		
		// template engine object
		$dully = new Psa_Dully(dirname(__FILE__) . '/templates');
		
		if($translations){
			$dully->assign('translate', $transtable);
			$dully->assign('data', $translations);
			$dully->assign('folder', $folder);
			$dully->assign('page_title', $TTCFG['php_array_files']['page_title']);
			$dully->assign('enable_html_editor', $TTCFG['php_array_files']['enable_html_editor']);
			$dully->assign('enable_edit_index', $TTCFG['php_array_files']['enable_edit_index']);
			$dully->assign('enable_delete_translation', $TTCFG['php_array_files']['enable_delete_translation']);
			$dully->assign('enable_add_translation', $TTCFG['php_array_files']['enable_add_translation']);
			$dully->assign('include_css_files', @$TTCFG['include_css']);
			$dully->assign('version', '0.1');
		}
		else
			$dully->assign('no_translations', 1);
		
		// page content
		$dully->assign('page_content', $dully->fetch('translation_table.tpl'));
		
		// display page
		echo $dully->fetch('main.tpl');
	}
	
	// save translation
	else if($action == 'savetranslation'){
		$transtable = new transtable();
		echo $transtable->save_translation($_POST['file_name'], $_POST['folder'], $_POST['index'], $_POST['translation']);
	}
	
	// save index
	else if($action == 'saveindex'){
		$transtable = new transtable();
		echo $transtable->rename_index($_POST['old_index'], $_POST['new_index'], $_POST['folder']);
	}
	
	// delete index (row)
	else if($action == 'deleteindex'){
		$transtable = new transtable();
		echo $transtable->delete_index($_POST['index'], $_POST['folder']);
		$dully->assign('include_css_files', @$TTCFG['include_css']);
	}
}
catch(Exception $e){
	
	// send 500 header
	if(!headers_sent())
		header('HTTP/1.1 500 Exception');
	
	echo $e->getMessage();
}






/**
 * Transtable class.
 */
class transtable{
	
	
	/**
	 * Configuration array
	 */
	public $config;
	
	
	/**
	 * Constructor
	 */
	public function __construct($config = null){
		
		// put config array to class scope
		if($config)
			$this->config = $config;
		else{
			global $TTCFG;
			$this->config = $TTCFG['php_array_files'];
		}

		$this->config['root_dir'] = realpath($this->config['root_dir']);
	}
	
	
	/**
	 * Returns an array with all folders (tabs) and corresponding translations.
	 * 
	 * @param string $for_folder path
	 * @return array
	 */
	public function get_all_translations($for_folder = null){
		
		$return = array();
		
		// file name pattern
		$file_name_pattern = '/^' . $this->config['file_name_pattern'] . '$/';
		
		// include foles from this folder
		if($for_folder)
			$for_folder = $this->check_path($for_folder);
		
		$root_path_len = strlen($this->config['root_dir']);
		
		$di = new RecursiveDirectoryIterator($this->config['root_dir']);
		foreach (new RecursiveIteratorIterator($di) as $path => $file) {
			
			// file name
			$file_name = $file->getFilename();
			
			// skip directories and file with names that doesn't match the patern
			if($file->isDir() or !preg_match($file_name_pattern, $file_name))
				continue;
				
			// folder relative path
			$folder = '/' . substr_replace(dirname($path), '', 0, $root_path_len+1);
			
			if(!$for_folder or $for_folder == $folder){
				
				// reset variable with translations
				${$this->config['var_name']} = array();
				
				// include the file
				include $path;
				
				$return[$folder]['translations'][$file_name] = ${$this->config['var_name']};
			}
			else
				$return[$folder]['translations'] = null;
			
			$return[$folder]['tab_name'] = basename($folder);
		}
		
		
		if($return){
			
			// sort translations by folder name
			ksort ($return);
			
			// find all indexes form each translation array
			// for each folder
			foreach ($return as $folder => $data) {
				// if there are any translations
				if($return[$folder]['translations']){
					
					// sort translations by file name
					ksort ($return[$folder]['translations']);
					
					// array that holds all indexes from all translation arrays (from each file in folder)
					$return[$folder]['all_indexes'] = array();
					
					// for each file
					foreach ($return[$folder]['translations'] as $file_name => $translations) {
						// for each translation in file
						foreach ($translations as $index => $translation) {
							
							if(is_array($translation)){
								
								foreach ($this->get_text_indexes($translation, $index) as $subindexes_text)
									$return[$folder]['all_indexes'][$subindexes_text] = null;
							}
							else{
								$return[$folder]['all_indexes'][$index] = null;
							}
						}
					}
				}
			}
		}
		
		return $return;
	}
	
	
	/**
	 * Recursive function which return text index from multi dimensional array.
	 * For example, for array with elements ['bla']['bla']['bla'] will return bla|bla|bla
	 * 
	 * @param array $translation_array 
	 * @param string $prefix_index
	 */
	protected function get_text_indexes($translation_array, $prefix_index){
		
		static $return = array();
		
		foreach ($translation_array as $index => $value){
	
			$text_index = $prefix_index . '|' . $index;
			
			if(is_array($value))
				$this->get_text_indexes($value, $text_index);
			else
				$return[] = $text_index;
		}
		
		return $return;
	}
	
	
	/**
	 * Returns php arrray index from txt index.
	 * For example:
	 * for bla will return ['bla']
	 * for bla|bla|bla will return ['bla']['bla']['bla']
	 * 
	 * @param string $txt_index
	 */
	public function get_php_index($txt_index){
		
		$txt_index = trim($txt_index);
		
		if(!$txt_index)
			throw new transtable_exception("Translation index cannot be empty");
		
		$txt_index = addslashes($txt_index);
		
		// for multi dimensional indexes like bla|bla|bla
		if(strpos($txt_index, $this->config['array_delimiter']) !== false)
			return "['" . str_replace($this->config['array_delimiter'], "']['", $txt_index) . "']";
		else
			return "['" . $txt_index . "']";
	}
	
	
	/**
	 * Saves translation to file.
	 * 
	 * @param string $file_path_relative relative path from $TTCFG['php_array_files']['root_dir']
	 * @param string $index text index
	 * @param string $translation translation value
	 * @throws transtable_exception
	 */
	public function save_translation($file_name, $folder, $index, $translation){
	
		$file_path_relative = $folder . '/' . $file_name;
		
		// full path to file
		$file_path_clean = $this->check_path($file_path_relative, 'return_absolute_path');
		
		${$this->config['var_name']} = array();
		
		// include the file
		include $file_path_clean;
		
		// set new value
		eval('${$this->config[\'var_name\']}' . $this->get_php_index($index) . ' = $translation;');
		
		// save file
		$this->write_translation_file($file_path_clean, $t);
		
		return 1;
	}
	
	
	/**
	 * Writes translation file to disk.
	 *
	 * @param string $file
	 * @param string $translations
	 * @throws transtable_exception
	 */
	protected function write_translation_file($file, $translations){
		
		$dully = new Psa_Dully(dirname(__FILE__) . '/templates');
		$dully->assign('t', $translations);
		$dully->assign('var_name', $this->config['var_name']);
		
		// php file content
		$file_content = $dully->fetch('translation_file.tpl');
		
		// normalize new lines
		str_replace(array("\r\n","\r"), "\n", $file_content);
		if($this->config['new_lines'] != "\n")
			$file_content = str_replace("\n", $this->config['new_lines'], $file_content);
		
		if(!file_put_contents($file, $file_content))
			throw new transtable_exception("Cannot write to file $file");
	}
	
	
	/**
	 * Check if path is in subdirectory of the $TTCFG['php_array_files']['root_dir'] and if exists.
	 * 
	 * @param string $file_path_relative
	 * @param bool $return_original_path
	 * @throws transtable_exception
	 * @return string
	 */
	protected function check_path($file_path_relative, $return_absolute_path = 0){
		
		// full path to file
		$file_path_clean = realpath($this->config['root_dir'] . '/' . $file_path_relative);
	
		if(!$file_path_clean)
			throw new transtable_exception("File {$this->config['root_dir']}/$file_path_relative doesn't exists");
	
		// check if file is subdir
		if(strcmp(substr($file_path_clean, 0, strlen($this->config['root_dir'])), $this->config['root_dir']) !== 0)
			throw new transtable_exception("File $file_path_clean is not in subdirecory of {$this->config['root_dir']}");
		
		if($return_absolute_path)
			return $file_path_clean;
		else
			return $file_path_relative;
	}


	/**
	 * Renames the index in the translation array
	 * 
	 * @param string $old_index
	 * @param string $new_index
	 * @param string $folder
	 */
	public function rename_index($old_text_index, $new_text_index, $folder){
	
		$this->require_edit_index_permission();
		
		$folder = $this->check_path($folder);
		
		$old_text_index = trim($old_text_index);
		$new_text_index = trim($new_text_index);
		
		if(!$old_text_index or !$new_text_index)
			throw new transtable_exception("Error. Old or new index name not set.");
		
		$translations = $this->get_all_translations($folder);
		
		// for each translation file in folder
		foreach ($translations as $folder => $data) {
			
			if($folder == $folder){
				foreach ($data['translations'] as $file_name => $translations) {
						
					// full path to file with translations
					$file_path = $this->check_path($folder . $file_name, 'return_absolute_path');
					
					// replace index
					$translations = $this->replace_array_key($translations, $old_text_index, $new_text_index);
					
					// write file
					$this->write_translation_file($file_path, $translations);
				}
				
				break;
			}
		}
	}
	
	
	/**
	 * Deletes index.
	 * 
	 * @param string $text_index
	 * @param string $folder
	 * @throws transtable_exception
	 * @return bool
	 */
	public function delete_index($text_index, $folder){
		
		$this->require_delete_translation_permission();
		
		$folder = $this->check_path($folder);
		
		$text_index = trim($text_index);
		
		if(!$text_index)
			throw new transtable_exception("Error. Old or new index name not set.");
		
		$translations = $this->get_all_translations($folder);
		
		// for each translation file in folder
		foreach ($translations as $folder => $data) {
				
			if($folder == $folder){
				foreach ($data['translations'] as $file_name => $translations) {
		
					// full path to file with translations
					$file_path = $this->check_path($folder . $file_name, 'return_absolute_path');
						
					// delete index
					eval('unset($translations' . $this->get_php_index($text_index) . ');');
						
					// write file
					$this->write_translation_file($file_path, $translations);
				}
				
				break;
			}
		}
		
		return 1;
	}
	
	
	/**
	 * Checks if editing indexes is enabled.
	 * 
	 * @throws transtable_exception
	 */
	protected function require_edit_index_permission(){
	
		if(!$this->config['enable_edit_index'])
			throw new transtable_exception("Editing indexes not enabled.");
	}
	
	
	/**
	 * Checks if deleting translations is enabled.
	 * 
	 * @throws transtable_exception
	 */
	protected function require_delete_translation_permission(){
	
		if(!$this->config['enable_delete_translation'])
			throw new transtable_exception("Deleting translations is not enabled.");
	}
	
	
	/**
	 * Changes a key in an array while maintaining the order if possible.
	 * 
	 * @param array $array
	 * @param string $old_text_index
	 * @param string $new_text_index
	 * @return array
	 */
	protected function replace_array_key($array, $old_text_index, $new_text_index){
		
		// if indexes are same
		if($old_text_index == $new_text_index)
			return $array;
		
		// if new index is subindex
		//if()
			
		// if new index already exists
		
		
		// if old key is array
		if(strpos($old_text_index, $this->config['array_delimiter']) !== false){
			$all_old_indexes = explode($this->config['array_delimiter'], $old_text_index);
			$count_all_old_indexes = count($all_old_indexes);
			$old_text_index_temp = $old_text_index;
			
			$old_php_index = $this->get_php_index($old_text_index);
			$new_php_index = $this->get_php_index($new_text_index);
			
			
			// translation text
			eval('$translation = $array' . $old_php_index . ';');
			// delete old value
			eval('unset($array' . $old_php_index . ');');
			// set value with new index
			eval('$array' . $new_php_index . ' = $translation;');
			
			for ($i = 1; $i < $count_all_old_indexes; $i++) {
				// bla|bla|bla => bla|bla
				$old_text_index_temp = substr($old_text_index_temp, 0, strrpos($old_text_index_temp, $this->config['array_delimiter']));
				// bla|bla => ['bla']['bla']
				$old_php_index_temp = $this->get_php_index($old_text_index_temp);
				
				eval('$tval = $array' . $old_php_index_temp . ';');
				
				// if $tval is empty array unset it
				if(!$tval && is_array($tval))
					eval('unset($array' . $old_php_index_temp . ');');
				else
					break;
			}
		}
		// if new key is array
		else if(strpos($new_text_index, $this->config['array_delimiter']) !== false){
			
			$new_php_index = $this->get_php_index($new_text_index);
			
			// translation text
			$translation = $array[$old_text_index];
			// delete old value
			unset($array[$old_text_index]);
			// set value with new index
			eval('$array' . $new_php_index . ' = $translation;');
		}
		else{
			$keys = array_keys($array);
			$index = array_search($old_text_index, $keys);
		
			if ($index !== false) {
				$keys[$index] = $new_text_index;
				$array = array_combine($keys, $array);
			}
		}
		
		return $array;
	}
}






/**
 * Simple template engine class.
 * I called this class Dully to be opposite to {@link http://www.smarty.net Smarty}.
 * I needed some simple name and something like '<i>simple_template</i>' seemed too long.
 * It has few methods that are similar to those in Smarty template engine.
 *
 * All that Dully does is to put values from associative array into local namespace and
 * includes .php (template) file. There are no special template tags, just use PHP code
 * blocks to echo values. Templates are ordinary PHP files and you can write them
 * as any other .php script, but keep in mind that the point of template engine
 * should be to separate business logic from presentation.
 * So you should not put any logic (calculations, getting data from a database ...)
 * into templates. With {@link http://www.smarty.net Smarty} that is easier to achieve
 * and it has many advanced features so I suggest that you use Smarty as template engine.
 * But if you know what you are doing or need simple and fast template
 * engine or don't want to learn Smarty's tags you can use Dully.
 * Dully class doesn't include anything from PSA so you can use it as template engine in any PHP application.
 *
 * This class is inspired from {@link http://www.massassi.com/php/articles/template_engines/} and there are
 * some interesting thoughts about template engines.
 *
 * <b>Examples</b>
 *
 * <b>1)</b> Simple example with one template file:
 *
 * Template file <samp>template.tpl</samp>:
 * {@example documentation_examples/templates/template.tpl}
 *
 * .php file:
 * {@example documentation_examples/dully1.php}
 *
 * The printout of the above .php script will be:
 * <pre>
 * This is text from template file template.php
 * My car is Black.
 * Something else: bla bla bla
 * </pre>
 *
 * <b>Note:</b> you can write <kbd><?= $some_name ?></kbd> instead of <kbd><?php echo $some_name ?></kbd>.
 * Use of this shortcut requires <var>short_open_tag</var> PHP ini option to be on.
 *
 * <b>2)</b> You can put one rendered template into another:
 *
 * template file <samp>template1.tpl</samp>:
 * {@example documentation_examples/templates/template1.tpl}
 *
 * template file <samp>template2.tpl</samp>:
 * {@example documentation_examples/templates/template2.tpl}
 *
 * .php file:
 * {@example documentation_examples/dully2.php}
 *
 * The above example will output:
 * <pre>
 * This is text from template1.php
 * This is text from template2.php
 * bla bla bla
 * </pre>
 *
 * <b>3)</b> In this kind of templates it's nice to use alternative PHP syntax for control structures.
 * See details in PHP {@link http://www.php.net/manual/en/control-structures.alternative-syntax.php manual}.
 * <code>
 * <? if ($variable = 'abcd'): ?>
 * 	<h1>this is something</h1>
 * 	<p>
 * 		bla bla
 * 	</p>
 * <? endif ?>
 *
 * <? foreach ($array as $value): ?>
 * 	<div><?= $value['email']) ?></div>
 * 	<div><?= $value['phone_number']) ?></div>
 * <? endforeach ?>
 * </code>
 *
 *
 * @see Psa_Dully_View
 * @see Psa_Smarty
 */
class Psa_Dully{

	/**
	 * Array that holds template values
	 *
	 * @var array
	 * @ignore
	 */
	protected $template_values = array();


	/**
	 * Folder with templates.
	 * Without '/' at the end.
	 *
	 * @var string
	*/
	public $template_dir;


	/**
	 * Sets {@link $template_dir} member value if passed as argument
	 *
	 * @param string $template_dir the directory with templates
	 */
	function __construct($template_dir = null){
		if($template_dir)
			$this->template_dir = $template_dir;
	}


	/**
	 * Assigns values to the templates.
	 * See examples in {@link Psa_Dully} class description.
	 *
	 * @param string $name variable name in template
	 * @param mixed $value variable value in template
	 * @see display()
	 * @see fetch()
	 */
	function assign($name, $value){
		$this->template_values[$name] = $value;
	}


	/**
	 * Returns the fetched template as string.
	 * See examples in {@link Psa_Dully} class description.
	 *
	 * @param string $template_filE template file name (path)
	 * @return string fetched (rendered) template
	 * @see display()
	 */
	function fetch($template_filE){

		// extract the template_values to local namespace
		extract($this->template_values);

		// start output buffering
		ob_start();

		// include the file
		include $this->template_dir . '/' . $template_filE;

		// get the contents and clean the buffer
		return ob_get_clean();
	}


	/**
	 * Outputs the fetched template.
	 * See examples in {@link Psa_Dully} class description.
	 *
	 * @param string $template_file template file name (path)
	 * @see fetch()
	 */
	function display($template_file){
		echo $this->fetch($template_file);
	}
}


/**
 * Exception transtable_exception
 */
class transtable_exception extends Exception{}


/**
 * Writes php code of translation files.
 * Used to write php translation files.
 * 
 * @param array $translations
 * @param string $var_name
 * @param string $arr_level
 */
function transtable_echo_translation_array($translations, $var_name, $arr_level = ''){

	foreach ($translations as $index => $translation){

		$arr_level1 = $arr_level . "['" . $index . "']";

		if(is_array($translation)){
			transtable_echo_translation_array($translation, $var_name, $arr_level1);
		}
		else
			echo '$' . $var_name . $arr_level1 . " = '" . transtable_addslashes($translation) . "';\n";
	}

}


/**
 * Adds slashes.
 * 
 * @param string $translation
 * @return string
 */
function transtable_addslashes($translation){
	return str_replace("'", '\\\'', $translation);
}


/**
 * Removes extension from file name.
 * 
 * @param string $file_name
 * @return string
 */
function transtable_strip_extension($file_name){
	return substr($file_name, 0, strrpos($file_name, '.'));
}
