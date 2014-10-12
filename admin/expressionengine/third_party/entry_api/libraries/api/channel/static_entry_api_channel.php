<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Static Channel API
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

class Static_entry_api_channel
{
	// ----------------------------------------------------------------------
	
	/**
	 * Insert a entry in the database
	 *
	 * @param none
	 * @return void
	 */
	static function create_channel($data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/channel/entry_api_channel');	

		//post the data to the service
		$return_data = ee()->entry_api_channel->create_channel($data);
		
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
	static function read_channel($data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/channel/entry_api_channel');	

		//post the data to the service
		$return_data = ee()->entry_api_channel->read_channel($data);

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
	static function update_channel($data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/channel/entry_api_channel');	
		
		//post the data to the service
		$return_data = ee()->entry_api_channel->update_channel($data);

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
	static function delete_channel($data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/channel/entry_api_channel');	

		//post the data to the service
		$return_data = ee()->entry_api_channel->delete_channel($data);
		
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
	 * Search a entry
	 *
	 * @param none
	 * @return void
	 */
	static function search_channel($data, $type = '')
	{
		//load the entry class
		ee()->load->library('api/channel/entry_api_channel');

		//post the data to the service
		$return_data = ee()->entry_api_channel->search_channel($data);

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
}