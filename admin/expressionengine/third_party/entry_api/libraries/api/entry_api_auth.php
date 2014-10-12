<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API - Auth
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'entry_api/config.php';

include_once PATH_THIRD .'entry_api/libraries/api/entry_api_base_api.php';

class Entry_api_auth extends Entry_api_base_api
{	
	/*
	*	EE instance
	*/
	private $EE;
	
	/*
	*	the custom fields
	*/
	public $fields;

	/*
	*	Type of service
	*/
	public $type;

	/*
	*	Type of service
	*/
	public $api_type = 'auth';
	
	/**
	 * Constructor
	 */
	public function __construct($params = array())
	{	
		//construct the parent
		parent::__construct();

	}
	
	// ----------------------------------------------------------------

	/**
	 * Auth based on username
	 * 
	 * @param  string $username  
	 * @param  string $password  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function authenticate_username($username = '', $password = '')
	{
		return $this->base_authenticate_username($username, $password);
	}

	// ----------------------------------------------------------------

	/**
	 * Auth based on username
	 * 
	 * @param  string $username  
	 * @param  string $password  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function authenticate_email($email = '', $password = '')
	{
		return $this->base_authenticate_email($email, $password);
	}
}

/* End of file entry_api_entry.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/api/entry_api_entry.php */