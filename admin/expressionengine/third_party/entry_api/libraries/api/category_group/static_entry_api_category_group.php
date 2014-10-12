<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Static Category groupe API
 *
 * @package		Entry API
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'entry_api/config.php';

class Static_entry_api_category_group
{
	// ----------------------------------------------------------------------
	
	/**
	 * Insert a entry in the database
	 *
	 * @param none
	 * @return void
	 */
	static function create_category_group($data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/category_group/entry_api_category_group');	

		//post the data to the service
		$return_data = ee()->entry_api_category_group->create_category_group($data);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * read a entry
	 *
	 * @param none
	 * @return void
	 */
	static function read_category_group($data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/category_group/entry_api_category_group');	

		//post the data to the service
		$return_data = ee()->entry_api_category_group->read_category_group($data);

		//var_dump($return_data);exit;
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{	
				$return_data['data'] = entry_api_format_soap_data($return_data['data'], 'entry_list');
			}
		}		
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update a entry in the database
	 *
	 * @param none
	 * @return void
	 */
	static function update_category_group($data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/category_group/entry_api_category_group');	
		
		//post the data to the service
		$return_data = ee()->entry_api_category_group->update_category_group($data);

		//format the array, because we cannot do nested arrays
		if($type != 'rest'  && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	 
	
	// ----------------------------------------------------------------------
	
	/**
	 * Delete a entry
	 *
	 * @param none
	 * @return void
	 */
	static function delete_category_group($data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/category_group/entry_api_category_group');		

		//post the data to the service
		$return_data = ee()->entry_api_category_group->delete_category_group($data);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_format_data($return_data['data'], $type);
		}
		
		//return result
		return $return_data;
	}	
}