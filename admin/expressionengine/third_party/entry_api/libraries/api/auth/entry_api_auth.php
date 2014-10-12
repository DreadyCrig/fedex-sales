<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Auth API
 *
 * @package		Entry API
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

class Entry_api_auth
{
	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		//include_once PATH_THIRD.'entry_api/libraries/entry_api_base_api.php';
	}

	//-------------------------------------------------------------------------

	/**
     * authenticate_username
    */
	public function authenticate_username($data = array())
	{
		$base_api = new entry_api_base_api();
		$auth = $base_api->auth($data);	
		return $base_api->auth_data;
	}

	//-------------------------------------------------------------------------

	/**
     * authenticate_email
    */
	// public function authenticate_email($data = array())
	// {
	// 	$base_api = new entry_api_base_api();
	// 	$auth = $base_api->auth($data);	
	// 	return $base_api->auth_data;
	// }

	//-------------------------------------------------------------------------

	/**
     * authenticate_member_id
    */
	// public function authenticate_member_id($data = array())
	// {
	// 	$base_api = new entry_api_base_api();
	// 	$auth = $base_api->auth($data);	
	// 	return $base_api->auth_data;
	// }

	//-------------------------------------------------------------------------

	/**
     * authenticate_session_id
    */
	// public function authenticate_session_id($data = array())
	// {
	// 	$base_api = new entry_api_base_api();
	// 	$auth = $base_api->auth($data);	
	// 	var_dump($base_api->error_str);
	// 	//return $base_api->auth_data;
	// }

}

