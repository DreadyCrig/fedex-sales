<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default fieldtype file, every fieldtype, except the one overridden, goes through this class
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons//add-ons/entry-api
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'entry_api/config.php';

class Entry_api_default_ft
{
	public $name = 'default';
	
	// ----------------------------------------------------------------

	/**
	 * Preps the data for saving
	 *
	 * Hint: you only have to format the data likes the publish page
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @param  int $entry_id
	 * @return mixed string            
	 */
	public function entry_api_save($data = null, $is_new = false, $entry_id = 0)
	{
		return $data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Handles any custom logic after an entry is saved.
	 * 
	 * @param  mixed $data   
	 * @param  array $inserted_data
	 * @param  int $entry_id
	 * @return void
	 */
	public function entry_api_post_save($data = null, $inserted_data = array(), $entry_id = 0) 
	{
		
	}

	// ----------------------------------------------------------------

	/**
	 * Validate the field
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @return bool            
	 */
	public function entry_api_validate($data = null, $is_new = false, $entry_id = 0)
	{
		//$this->validate_error = '';
		return true;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Preprocess the data to be returned
	 * 
	 * @param  mixed $data  
	 * @param  string $free_access
	 * @param  int $entry_id
	 * @return mixed string
	 */
	public function entry_api_pre_process($data = null, $free_access = false, $entry_id = 0) 
	{
		return $data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * delete field data, before the entry is deleted
	 *
	 * Hint: EE will mostly do everything for you, because the delete() function will trigger
	 * 
	 * @param  mixed $data   
	 * @param  int $entry_id
	 * @return void
	 */
	public function entry_api_delete($data = null, $entry_id = 0) 
	{
		
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * delete field data, after the entry is deleted
	 *
	 * Hint: EE will mostly do everything for you, because the delete() function will trigger
	 * 
	 * @param  mixed $data   
	 * @param  int $entry_id
	 * @return void
	 */
	public function entry_api_post_delete($data = null, $entry_id = 0) 
	{
		
	}
}