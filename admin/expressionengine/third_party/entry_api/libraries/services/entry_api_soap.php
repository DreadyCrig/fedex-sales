<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Soap service class
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons//add-ons/entry-api
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'entry_api/config.php';

//require_once PATH_THIRD.'entry_api/libraries/services/entry_api_service.php';

class Entry_api_soap
{	
	/*
	*	The username
	*/
	public $username;
	
	/*
	*	the password
	*/
	public $password;
	
	/*
	*	The postdata
	*/
	public $post_data;
	
	/*
	*	the channel
	*/
	public $post_data_channel;
	
	/*
	*	Soap server
	*/
	private $server;

	
	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load the Classes
		//$this->EE =& get_instance();
		//ee()->load->library('Entry_api_server');
		ee()->load->library('entry_api_lib');	

		//load the nusoap server
		require_once(PATH_THIRD .'entry_api/libraries/services/nusoap/nusoap.php');
		
		//set the type
		//ee()->entry_api_server_helper->type = 'soap';	
		
		/** ---------------------------------
		/**  Create a soap service
		/** ---------------------------------*/
		$this->server = new soap_server();
		$this->server->setDebugLevel(100);
		
		/** ---------------------------------
		/**  Initialize WSDL support
		/** ---------------------------------*/
		$this->server->configureWSDL('EntryApi', 'urn:EntryApi', ee()->functions->create_url(ee()->entry_api_settings->item('url_trigger').'/soap'));
		$namespace = "http://localhost/html/nusoap/index.php";

		// set our namespace
		$this->server->wsdl->schemaTargetNamespace = $namespace;
		
		/** ---------------------------------
		/**  create AssocativeArray support
		/** ---------------------------------*/		
		$this->server->wsdl->addComplexType(
			'Associative',
			'complexType',
			'struct',
			'all',
			'',
			array(
				'key' => array('name'=>'key','type'=>'xsd:string'),
				'value' => array('name'=>'value','type'=>'xsd:string')
			)
		);
		
		$this->server->wsdl->addComplexType(
			'AssociativeArray',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(
				array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:Associative[]')
			),
			'tns:Associative'
		);

		$this->server->wsdl->addComplexType(
			'ObjectList',
			'complexType',
			'array',
			'',
			'SOAP-ENC:Array',
			array(),
			array(
				array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'tns:AssociativeArray[]')
			),
			'tns:AssociativeArray'
		);

		//get all the api settings
		$apis = ee()->entry_api_lib->load_apis();
		foreach($apis['apis'] as $api)
		{
			foreach($api->methods as $method)
			{

				$return_array = array();
				foreach($method->soap as $val)
				{
					$return_array[$val->name] = $val->type;
				}

				$this->register_method($method->method, $method->name, '', $return_array);

				//bad!!?? but it works
				$dynamic_method = 'function ' . $method->method . "(\$auth = array(), \$data = array()) 
				{
					ee()->load->helper('entry_api_helper');	
					ee()->load->library('entry_api_base_api');

					//load all the methods 
			        \$api = ee()->entry_api_base_api->api_type = ee()->entry_api_lib->search_api_method_class('{$method->method}');

			        \$class = 'static_entry_api_'.\$api;

			        if(!file_exists(PATH_THIRD.'entry_api/libraries/api/'.\$api.'/'.\$class.'.php'))
			        {
			           return new soap_fault('Client', '', 'API does not exist');
			        }

			         //load the api class
			        ee()->load->library('api/'.\$api.'/'.\$class);

			        //get the api settings
			        \$api_settings =  ee()->entry_api_lib->get_api(\$api);

			       	// check if method exists
			        if (!method_exists(ucfirst(\$class), '{$method->method}'))
			        {
			        	return new soap_fault('Client', '', 'Method does not exist');
			        }

			        \$error_auth = false;
			        \$return_data = array(
			            'message'           => '',
			            'code'              => 9999,
			            'code_http'         => 200,
			        );
						
					//set the site_id
					\$site_id = \$vars['data']['site_id'] = isset(\$vars['data']['site_id']) ? \$vars['data']['site_id'] : 1;

			        //if the api needs to be auth, do it here
			        if(isset(\$api_settings->auth) && (bool) \$api_settings->auth)
			        {
			            /** ---------------------------------------
			            /**  Run some default checks
			            /**  if the site id is given then switch to that site, otherwise use site_id = 1
			            /** ---------------------------------------*/
			            \$default_checks = ee()->entry_api_base_api->default_checks(\$auth, '{$method->method}', \$site_id);
			
			            if( ! \$default_checks['succes'])
			            { 
			                \$error_auth = true;
			                \$return_data = array_merge(\$return_data, \$default_checks['message']);
			                
			            }
			        }

		         	if(\$error_auth === false)
			        {
	                    //call the method
			            \$result = call_user_func(array(\$class, '{$method->method}'), \$data, 'soap');

			            //unset the response txt
			            unset(\$result['response']);     

			            //merge with default values
			            \$return_data = array_merge(\$return_data, \$result);
			        }

			        //add a log
			        ee()->entry_api_model->add_log(ee()->session->userdata('username'), \$return_data, '{$method->method}', 'soap', ee()->entry_api_base_api->servicedata);

			        //unset the http code
			        unset(\$return_data['code_http']);

			        //return result
			        return \$return_data;


				}";
				eval($dynamic_method);			

			}
		}

		// search_entry(array('api_key' => 'rein'), array('title' => 'test'));
		// exit;
		

				
		/** ---------------------------------
		/**  Register the method to expose
		/** ---------------------------------*/		
		/*$this->register_method('create_entry', 'Create a new entry', '', array('id' => 'xsd:string'));
		$this->register_method('read_entry', 'Read a entry', '', array('data' => 'tns:ObjectList', 'id' => 'xsd:string'));
		$this->register_method('update_entry', 'Update a entry', '', array('id' => 'xsd:string'));
		$this->register_method('delete_entry', 'Delete a entry', '', array('id' => 'xsd:string'));
		$this->register_method('search_entry', 'Search entries', '', array('data' => 'tns:ObjectList', 'id' => 'xsd:string'));
		
		//category
		$this->register_method('create_category', 'Create a new category', '', array('id' => 'xsd:int'));
		$this->register_method('read_category', 'Read a category', '', array('data' => 'tns:ObjectList'));
		$this->register_method('update_category', 'Update a category');
		$this->register_method('delete_category', 'Delete a category');

		//category group
		$this->register_method('create_category_group', 'Create a new category group', '', array('id' => 'xsd:int'));
		$this->register_method('read_category_group', 'Read a category group', '', array('data' => 'tns:ObjectList'));
		$this->register_method('update_category_group', 'Update a category group');
		$this->register_method('delete_category_group', 'Delete a category group');
		
		//Channels
		$this->register_method('create_channel', 'Create a Channel', '', array('id' => 'xsd:int'));
		$this->register_method('read_channel', 'Read a channel', '', array('data' => 'tns:ObjectList'));
		$this->register_method('update_channel', 'Update a channel');
		$this->register_method('delete_channel', 'Delete a channel');
		
		//Member
		$this->register_method('create_member', 'Create a member', '', array('id' => 'xsd:int'));
		$this->register_method('read_member', 'Read a member', '', array('data' => 'tns:ObjectList'));
		$this->register_method('update_member', 'Update a member');
		$this->register_method('delete_member', 'Delete a member');*/
			
		


		// /** ---------------------------------
		// /**  auth methods
		// /** ---------------------------------*/
		// function authenticate_username($auth)
		// {		
		// 	return Entry_api_service::authenticate_username($auth, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the add_entry function
		// /** ---------------------------------*/
		// function create_entry($auth, $data)
		// {		
		// 	return Entry_api_service::create_entry($auth, $data, 'soap');
		// }
		
		/** ---------------------------------
		/**  Create the select_entry function
		/** ---------------------------------*/
		// function read_entry($auth, $data)
		// {	
			
		// }
		
		// /** ---------------------------------
		// /**  Create the update_entry function
		// /** ---------------------------------*/
		// function update_entry($auth, $data)
		// {	
		// 	return Entry_api_service::update_entry($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the delete_entry function
		// /** ---------------------------------*/
		// function delete_entry($auth, $data)
		// {	
		// 	return Entry_api_service::delete_entry($auth, $data, 'soap');
		// }

		// /** ---------------------------------
		// /**  Search an entry
		// /** ---------------------------------*/
		// function search_entry($auth, $data)
		// {	
		// 	return Entry_api_service::search_entry($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the create_category function
		// /** ---------------------------------*/
		// function create_category($auth, $data)
		// {		
		// 	return Entry_api_service::create_category($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the read_category function
		// /** ---------------------------------*/
		// function read_category($auth, $data)
		// {	
		// 	return Entry_api_service::read_category($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the update_category function
		// /** ---------------------------------*/
		// function update_category($auth, $data)
		// {	
		// 	return Entry_api_service::update_category($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the delete_category function
		// /** ---------------------------------*/
		// function delete_category($auth, $data)
		// {	
		// 	return Entry_api_service::delete_category($auth, $data, 'soap');
		// }

		// /** ---------------------------------
		// /**  Create the create_category function
		// /** ---------------------------------*/
		// function create_category_group($auth, $data)
		// {		
		// 	return Entry_api_service::create_category_group($auth, $data, 'soap');
		// }
		
		// * ---------------------------------
		// /**  Create the read_category function
		// /** ---------------------------------
		// function read_category_group($auth, $data)
		// {	
		// 	return Entry_api_service::read_category_group($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the update_category function
		// /** ---------------------------------*/
		// function update_category_group($auth, $data)
		// {	
		// 	return Entry_api_service::update_category_group($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the delete_category function
		// /** ---------------------------------*/
		// function delete_category_group($auth, $data)
		// {	
		// 	return Entry_api_service::delete_category_group($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the create_channel function
		// /** ---------------------------------*/
		// function create_channel($auth, $data)
		// {	
		// 	return Entry_api_service::create_channel($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the read_channel function
		// /** ---------------------------------*/
		// function read_channel($auth, $data)
		// {	
		// 	return Entry_api_service::read_channel($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the update_channel function
		// /** ---------------------------------*/
		// function update_channel($auth, $data)
		// {	
		// 	return Entry_api_service::update_channel($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the delete_channel function
		// /** ---------------------------------*/
		// function delete_channel($auth, $data)
		// {	
		// 	return Entry_api_service::delete_channel($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the create_channel function
		// /** ---------------------------------*/
		// function create_member($auth, $data)
		// {	
		// 	return Entry_api_service::create_member($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the read_channel function
		// /** ---------------------------------*/
		// function read_member($auth, $data)
		// {	
		// 	return Entry_api_service::read_member($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the update_channel function
		// /** ---------------------------------*/
		// function update_member($auth, $data)
		// {	
		// 	return Entry_api_service::update_member($auth, $data, 'soap');
		// }
		
		// /** ---------------------------------
		// /**  Create the delete_channel function
		// /** ---------------------------------*/
		// function delete_member($auth, $data)
		// {	
		// 	return Entry_api_service::delete_member($auth, $data, 'soap');
		// }

		/** ---------------------------------
		/**  Use the request to (try to) invoke the service
		/** ---------------------------------*/
		$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
		$this->server->service(file_get_contents('php://input'));

		//log
		//file_put_contents(PATH_THIRD .'entry_api/test.html', $this->server->getDebug());
	
	}

	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Register new methods for the service
	 *
	 * @param none
	 * @return void
	 */
	private function register_method($method_name, $description, $input = array(), $output = array())
	{
		//input
		if(empty($input))
		{
			$input = array(
				'auth' => 'xsd:struct',
				'data' => 'xsd:struct'
			);
		}
		
		//input
		$output = array_merge(array(
			'code' => 'xsd:int',
			'message' => 'xsd:string'
		), $output);
		
	
		$this->server->register($method_name,
			$input, 						// input parameters      
			$output,  	 					// output parameters   
			'urn:EntryApi', 				// namespace
			'urn:EntryApi#'.$method_name, 	// soapaction
			'rpc', 							// style
			'encoded',					 	// use
			$description 					// documentation
		);
	}

}

/* End of file entry_api_xmlrpc.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/entry_api_xmlrpc.php */