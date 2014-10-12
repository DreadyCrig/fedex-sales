<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Public methods
 *
 * This file has one purpose, expose the methods on the CP without any services arround
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

class Entry_api_public_methods
{

	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		ee()->load->library('entry_api_lib');
		
		//load the helper
		ee()->load->helper('entry_api_helper'); 
        ee()->load->library('entry_api_base_api');
	}

    // --------------------------------------------------------------------
        
    /**
     * Dynamic calling
     */
    public function __call($name, $arguments)
    {
        return $this->method($name, $arguments);
    }

	// --------------------------------------------------------------------
        
    /**
     * Call Method
     */
    public function method($method = '', $data = array(), $inlog = array())
    {  

        //load all the methods 
        $api = ee()->entry_api_base_api->api_type = ee()->entry_api_lib->search_api_method_class($method);


        $class = 'static_entry_api_'.$api;

        if(!file_exists(PATH_THIRD.'entry_api/libraries/api/'.$api.'/'.$class.'.php'))
        {
            return array(
                'code_http' => 400,
                'code' => 400,
                'message' => 'API does not exist'
            ); 
        }

        // //load the api class
        ee()->load->library('api/'.$api.'/'.$class);

        //get the api settings
        $api_settings =  ee()->entry_api_lib->get_api($api);

        // check if method exists
        if (!method_exists(ucfirst($class), $method))
        {
            return array(
                'code_http' => 400,
                'code' => 400,
                'message' => 'Method does not exist'
            );
        }

        //get the paramaters
        $parameters = $data;
        
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
            $default_checks = ee()->entry_api_base_api->default_checks($parameters[0], $method, $site_id);
     
            if( ! $default_checks['succes'])
            { 
                $error_auth = true;
                $return_data = array_merge($return_data, $default_checks['message']);
            }
        }

        if($error_auth === false)
        {
            //call the method
            $result = call_user_func(array($class, $method), $parameters[1], 'xmlrpc');

            //unset the response txt
            unset($result['response']);      

            //merge with default result
            $return_data = array_merge($return_data, $result);      
        }

        //add a log
        ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, $method, 'xmlrpc', ee()->entry_api_base_api->servicedata);
        
        //unset the http code
        if(isset($return_data['code_http']))
        {
            $http_code = $return_data['code_http'];
            unset($return_data['code_http']);
        }
        
        //return
        return array($return_data, $http_code);
    }
	
}