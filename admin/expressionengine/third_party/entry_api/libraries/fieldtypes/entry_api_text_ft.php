<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Text fieldtype file
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

class Entry_api_text_ft
{
	public $name = 'text';

	// ----------------------------------------------------------------

	/**
	 * Preps the data for saving
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @param  int $entry_id
	 * @return void            
	 */
	public function entry_api_save($data = null, $is_new = false, $entry_id = 0)
	{
		return $data;
	}

	// ----------------------------------------------------------------

	/**
	 * Validate the field
	 * 
	 * @param  mixed $data  
	 * @param  bool $is_new
	 * @return void            
	 */
	public function entry_api_validate($data = null, $is_new = false)
	{
		//max length
		if(strlen($data) > $this->field_data['field_maxl'])
		{
			$this->validate_error = 'Max length('.$this->field_data['field_maxl'].') exceeded';
			return false;
		}

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
}