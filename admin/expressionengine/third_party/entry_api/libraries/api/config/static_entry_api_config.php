<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Static Test API
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

class Static_entry_api_config
{	

	//-------------------------------------------------------------------------

	/**
     * read_config test method
    */
	public static function read_config($post_data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/config/entry_api_config');	

		//post the data to the service
		$return_data = ee()->entry_api_config->read_config($post_data, $type);

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

	//-------------------------------------------------------------------------

	/**
     * read_config test method
    */
	public static function update_config($post_data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/config/entry_api_config');	

		//post the data to the service
		$return_data = ee()->entry_api_config->update_config($post_data, $type);

		//format the array, because we cannot do nested arrays
		if($type != 'rest'  && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
}