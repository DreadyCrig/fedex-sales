<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Entry api route
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

class Entry_api_route
{
	/*
	*	EE instance
	*/
	private $EE;
	
	/**
	 * Constructor
	 */
	public function __construct($type = '')
	{		
		//get the instance
		//$this->EE =& get_instance();
				
		switch($type)
		{
			//SOAP service
			case 'soap':
				include_once PATH_THIRD .'entry_api/libraries/services/entry_api_soap.php';
				$this->soap = new Entry_api_soap();
			break;
			
			//XML-RPC service
			case 'xmlrpc':
				include_once PATH_THIRD .'entry_api/libraries/services/entry_api_xmlrpc.php';
				$this->xmlrpc = new Entry_api_xmlrpc();
			break;
			
			//REST services
			case 'rest': 
				include_once PATH_THIRD .'entry_api/libraries/services/entry_api_rest.php';
				$this->rest = new Entry_api_rest();
			break;

			//Test the services
			case 'test': 
				include_once PATH_THIRD .'entry_api/tests/entry_api_test.php';
				$this->test = new Entry_api_test();
			break;
		}
		
	}
}

/* End of file entry_api_server_helper.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/entry_api_route.php */