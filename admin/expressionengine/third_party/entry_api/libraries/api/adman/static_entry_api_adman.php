<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Static Entry API
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

class Static_entry_api_adman
{
	
	/**
	 * Get an ad
	 *
	 * @param none
	 * @return void
	 */
	static function show_adman($data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/adman/entry_api_adman');	

		//post the data to the service
		$return_data = ee()->entry_api_adman->show_adman($data);

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