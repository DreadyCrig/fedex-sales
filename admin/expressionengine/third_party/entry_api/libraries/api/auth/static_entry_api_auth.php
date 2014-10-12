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

class Static_entry_api_auth
{	

	//-------------------------------------------------------------------------

	/**
     * authenticate_username method
    */
	public static function authenticate_username($data = array(), $type = '')
	{
		//load the entry class
		ee()->load->library('api/auth/entry_api_auth');	

		//post the data to the service
		$return_data = ee()->entry_api_auth->authenticate_username($data, $type);

		//unset the response txt
		unset($return_data['response']);

		//return result
		return $return_data;
	}

	//-------------------------------------------------------------------------

	/**
     * authenticate_username method
    */
	// public static function authenticate_email($data = array(), $type = '')
	// {
	// 	//load the entry class
	// 	ee()->load->library('api/auth/entry_api_auth');	

	// 	//post the data to the service
	// 	$return_data = ee()->entry_api_auth->authenticate_email($data, $type);

	// 	//unset the response txt
	// 	unset($return_data['response']);

	// 	//return result
	// 	return $return_data;
	// }

	//-------------------------------------------------------------------------

	/**
     * authenticate_username method
    */
	// public static function authenticate_member_id($data = array(), $type = '')
	// {
	// 	//load the entry class
	// 	ee()->load->library('api/auth/entry_api_auth');	

	// 	//post the data to the service
	// 	$return_data = ee()->entry_api_auth->authenticate_member_id($data, $type);

	// 	//unset the response txt
	// 	unset($return_data['response']);

	// 	//return result
	// 	return $return_data;
	// }

	//-------------------------------------------------------------------------

	/**
     * authenticate_username method
    */
	// public static function authenticate_session_id($data = array(), $type = '')
	// {
	// 	//load the entry class
	// 	ee()->load->library('api/auth/entry_api_auth');	

	// 	//post the data to the service
	// 	$return_data = ee()->entry_api_auth->authenticate_session_id($data, $type);

	// 	//unset the response txt
	// 	unset($return_data['response']);

	// 	//return result
	// 	return $return_data;
	// }
}