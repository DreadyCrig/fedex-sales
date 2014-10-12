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

class Static_entry_api_test
{	

	//-------------------------------------------------------------------------

	/**
     * Simple test method
    */
	public static function test_method($post_data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/test/entry_api_test');	

		//post the data to the service
		$return_data = ee()->entry_api_test->test_method($post_data, $type);

		//unset the response txt
		unset($return_data['response']);

		//return result
		return $return_data;

	}
}