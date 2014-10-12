<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * XMLRPC service class
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

// require_once PATH_THIRD.'entry_api/libraries/services/entry_api_service.php';

class Entry_api_xmlrpc 
{
	/*
	*	EE instance
	*/
	private $EE;
	
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

	
	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load the Classes
		//$this->EE =& get_instance();
		ee()->load->helper('security');  	
			
		/** ---------------------------------
		/**  Load the XML-RPC Files
		/** ---------------------------------*/
		ee()->load->library('xmlrpc');
		ee()->load->library('xmlrpcs');
		
		/* ---------------------------------
		/*  Specify Functions
		/* ---------------------------------*/
		$functions = array();

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

				$functions[$method->method] = array('function' => 'Entry_api_xmlrpc.call_method');

			}
		}
		// $functions = array(	
		// 	//Entry
		// 	'create_entry'	=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'read_entry'	=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'update_entry'	=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'delete_entry'	=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'search_entry'	=> array('function' => 'Entry_api_xmlrpc.call_method'),

		// 	//Category
		// 	'create_category'	=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'read_category'		=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'update_category'	=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'delete_category'	=> array('function' => 'Entry_api_xmlrpc.call_method'),

		// 	//Category Group
		// 	'create_category_group'		=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'read_category_group'		=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'update_category_group'		=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'delete_category_group'		=> array('function' => 'Entry_api_xmlrpc.call_method'),
			
		// 	//Channels
		// 	'create_channel'		=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'read_channel'			=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'update_channel'		=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'delete_channel'		=> array('function' => 'Entry_api_xmlrpc.call_method'),
			
		// 	//Member
		// 	'create_member'			=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'read_member'			=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'update_member'			=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'delete_member'			=> array('function' => 'Entry_api_xmlrpc.call_method'),

		// 	//comment
		// 	'create_comment'			=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'read_comment'				=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'update_comment'			=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// 	'delete_comment'			=> array('function' => 'Entry_api_xmlrpc.call_method'),
		// );
		
		/** ---------------------------------
		/**  Instantiate the Server Class
		/** ---------------------------------*/
		//ee()->xmlrpc->set_debug(TRUE);
		ee()->xmlrpcs->initialize(array('functions' => $functions, 'object' => $this, 'xss_clean' => FALSE));
		ee()->xmlrpcs->serve();
		die();

	}

	// ----------------------------------------------------------------------
	
	/**
	 * call the method
	 *
	 * @param none
	 * @return void
	 */
	public function call_method($request)
	{
		ee()->load->helper('entry_api_helper');	
		ee()->load->library('entry_api_base_api');

		//load all the methods 
        $api = ee()->entry_api_base_api->api_type = ee()->entry_api_lib->search_api_method_class($request->method_name);

        $class = 'static_entry_api_'.$api;

        if(!file_exists(PATH_THIRD.'entry_api/libraries/api/'.$api.'/'.$class.'.php'))
        {
        	return $this->response(array(
        		'code_http' => 400,
        		'code' => 400,
        		'message' => 'API does not exist'
        	));
           
        }

        // //load the api class
        ee()->load->library('api/'.$api.'/'.$class);

        //get the api settings
        $api_settings =  ee()->entry_api_lib->get_api($api);

        // check if method exists
        if (!method_exists(ucfirst($class), $request->method_name))
        {
            return $this->response(array(
        		'code_http' => 400,
        		'code' => 400,
        		'message' => 'Method does not exist'
        	));
        }

		//get the paramaters
		$parameters = $request->output_parameters();
		
		$error_auth = false;
        $return_data = array(
            'message'           => '',
            'code'              => 9999,
            'code_http'         => 200,
        );

        //set the site_id
        $site_id = $vars['data']['site_id'] = isset($vars['data']['site_id']) ? $vars['data']['site_id'] : 1;

        //if the api needs to be auth, do it here
        if(isset($api_settings->auth) && (bool) $api_settings->auth)
        {
            /** ---------------------------------------
            /**  Run some default checks
            /**  if the site id is given then switch to that site, otherwise use site_id = 1
            /** ---------------------------------------*/
            $default_checks = ee()->entry_api_base_api->default_checks($parameters[0], $request->method_name, $site_id);
     
            if( ! $default_checks['succes'])
            { 
                $error_auth = true;
                $return_data = array_merge($return_data, $default_checks['message']);
            }
        }

        if($error_auth === false)
        {
            //call the method
            $result = call_user_func(array($class, $request->method_name), $parameters[1], 'xmlrpc');

            //unset the response txt
            unset($result['response']);      

            //merge with default result
            $return_data = array_merge($return_data, $result);      
        }

        //add a log
        ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, $request->method_name, 'xmlrpc', ee()->entry_api_base_api->servicedata);
		
		//unset the http code
        if(isset($return_data['code_http']))
        {
            $http_code = $return_data['code_http'];
            unset($return_data['code_http']);
        }
        
        //return
        return $this->response($return_data, $http_code);
	}

	// ----------------------------------------------------------------------
	
	/**
	 * response
	 *
	 * @param none
	 * @return void
	 */
	public function response($result, $http_code = 200)
	{
		//good call?
		if($http_code == 200)
		{
			$response = array(
					array(
							'code'		=> array($result['code'],'int'),
							'message'	=> array($result['message'],'string')
						),
			'struct');

			//is there an id returnend by an create invoke
			if(isset($result['id']))
			{
				$response[0]['id'] = array($result['id'],'string');
			}
			
			//grab the data and assing it to the response array
			if(!empty($result['data'])) 
			{
				$values = array();
				//$values[0] =  array($result['data'][0], 'struct');
				
				$values = array();
				foreach($result['data'] as $key=>$entry)
				{
					$values[$key] = array($entry, 'struct');
				}

				$response[0]['data'] = array($values,'array');
			}

			//return data
			return ee()->xmlrpc->send_response($response);
		}
		//error?
		else
		{	
			return ee()->xmlrpc->send_error_message($result['code'], $result['message']);
		}
	}

	// ----------------------------------------------------------------------
	
}
/* End of file entry_api_xmlrpc.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/xmlrpc/entry_api_xmlrpc.php */