<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Test API
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

class Entry_api_template
{
	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		//require the default settings
        require PATH_THIRD.'entry_api/settings.php';
	}

	//-------------------------------------------------------------------------

	/**
     * Simple test method
    */
	public function create_template($post_data = array())
	{
		return array(
			'message' 		=> 'That is ok',
			'code'			=> 200,
			'code_http'		=> 200,
		);
	}

}

