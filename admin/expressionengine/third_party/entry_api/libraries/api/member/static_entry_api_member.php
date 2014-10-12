<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Static Auth API
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

class Static_entry_api_member
{	

	//-------------------------------------------------------------------------

	/**
     * create_member method
    */
	public static function create_member($data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/member/entry_api_member');	

		//post the data to the service
		$return_data = ee()->entry_api_member->create_member($data, $type);

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
     * create_member method
    */
	public static function read_member($data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/member/entry_api_member');	

		//post the data to the service
		$return_data = ee()->entry_api_member->read_member($data, $type);

		//Format soap
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

		//unset the response txt
		unset($return_data['response']);

		//return result
		return $return_data;
	}

	//-------------------------------------------------------------------------

	/**
     * update_member method
    */
	public static function update_member($data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/member/entry_api_member');	

		//post the data to the service
		$return_data = ee()->entry_api_member->update_member($data, $type);

		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_format_data($return_data['data'], $type);
		}

		//unset the response txt
		unset($return_data['response']);

		//return result
		return $return_data;
	}

	//-------------------------------------------------------------------------

	/**
     * delete_member method
    */
	public static function delete_member($data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/member/entry_api_member');	

		//post the data to the service
		$return_data = ee()->entry_api_member->delete_member($data, $type);

		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_format_data($return_data['data'], $type);
		}
		
		//unset the response txt
		unset($return_data['response']);

		//return result
		return $return_data;
	}
}