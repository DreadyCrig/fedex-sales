<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Entry api Extension helper
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
require_once(PATH_THIRD.'entry_api/config.php');

/**
 * Include helper
 */
require_once(PATH_THIRD.'entry_api/libraries/entry_api_helper.php');

class Entry_api_lib
{
	private $default_settings;
	private $EE;

	public function __construct()
	{					
		//load model
		ee()->load->model(ENTRY_API_MAP.'_model');

		//load the channel data
		ee()->load->driver('channel_data');

		//load the settings
		ee()->load->library(ENTRY_API_MAP.'_settings');

		//load logger
		ee()->load->library('logger');

        //load helper
        ee()->load->helper('entry_api_helper');
		
		//require the default settings
		require PATH_THIRD.ENTRY_API_MAP.'/settings.php';
		
		// no time limit
		//set_time_limit(0);
			
		//check the tmp path
		ee()->load->helper('file');
		
		//create dir if not exists
		if(!is_dir(ee()->entry_api_settings->item('tmp_dir')) && ee()->entry_api_settings->item('tmp_dir') != '')
		{
			@mkdir(ee()->entry_api_settings->item('tmp_dir'), 0777, true);
		}
		//chmod to write mode
		@chmod(ee()->entry_api_settings->item('tmp_dir'), 0777);
		
		//set urls
		ee()->entry_api_settings->set_setting('xmlrpc_url', reduce_double_slashes(ee()->config->item('site_url').ee()->config->item('site_index').'/entry_api/xmlrpc'));
		ee()->entry_api_settings->set_setting('soap_url', reduce_double_slashes(ee()->config->item('site_url').ee()->config->item('site_index').'/entry_api/soap'));	
		ee()->entry_api_settings->set_setting('rest_url', reduce_double_slashes(ee()->config->item('site_url').ee()->config->item('site_index').'/entry_api/rest'));	
		
	}

	// --------------------------------------------------------------------
        
    /**
     * Has the user free access
     * User who exists has never free access
     * 0 = not free
     * 1 = no username, free access
     * 2 = inlog require, free access
     */
    public function has_free_access($method = '', $username = '')
    {
    	//user not exists, take the global settings
    	$user_exists = ee()->entry_api_model->user_exists($username);

    	if($username == '' || $user_exists == false)
		{
			if(in_array($method, ee()->entry_api_settings->item('free_apis')))
			{	
				return 1;
			}
			return 0;
		}
		else if($user_exists)
		{
			$member = ee()->entry_api_model->get_member_based_on_username($username);
			if(in_array($method, explode('|', $member->free_apis)))
			{	
				return 2;
			}
			return 0;
		}

		return 0;
    }

    // --------------------------------------------------------------------
        
    /**
     * Load the apis based on their dir name
     */
    public function load_apis()
    {
    	$apis = array();

    	ee()->load->helper('file');
    	ee()->load->helper('directory');

    	$path = PATH_THIRD.'entry_api/libraries/api';
		$dirs = directory_map($path);

		foreach ($dirs as $key=>$dir)
		{
			if(is_array($dir))
			{
				foreach($dir as $file)
				{
					 if($file == 'settings.json') 
					 {
					 	$json = file_get_contents($path.'/'.$key.'/settings.json');
					 	$json = json_decode($json);

                        //is enabled?
                        if(isset($json->enabled) && $json->enabled)
                        {
    					 	//set a quick array for the methods
    					 	$json->_methods = array();
    					 	foreach($json->methods as $method)
    					 	{
    					 		$json->_methods[$json->name] = $method->method;
    					 		$apis['_methods_class'][$method->method] = $json->name;
    					 	}

    					 	$apis['apis'][$json->name] = $json;
                        }
					 }
				}
			}		    
		}
        return $apis;
    }

    // --------------------------------------------------------------------
        
    /**
     * Search for the api method
     */
    public function search_api_method_class($method = '')
    {
    	$apis = $this->load_apis();
    	if(isset($apis['_methods_class'][$method]))
    	{
    		return $apis['_methods_class'][$method];
    	}
    }

    // --------------------------------------------------------------------
        
    /**
     * Load the apis based on their dir name
     */
    public function get_api_names()
    {
    	$apis = $this->load_apis();

    	$return = array();
    	foreach($apis['apis'] as $val)
    	{
            if($val->public == false)
            {
                $return[$val->name] = $val->label;
            }
    	}

    	return $return;
    }

    // --------------------------------------------------------------------
        
    /**
     * Load the apis based on their dir name
     */
    public function get_api_free_names()
    {
    	$apis = $this->load_apis();

    	$return = array();
    	foreach($apis['apis'] as $val)
    	{
    		foreach($val->methods as $method)
    		{
    			if(isset($method->free_api) && $method->free_api)
    			{
    				$return[$method->method] = $method->method;
    			}
    			
    		}
    		
    	}

    	return $return;
    }



    // --------------------------------------------------------------------
        
    /**
     * Load the apis based on their dir name
     */
    public function get_api($name = '')
    {
    	$apis = $this->load_apis();

    	if($name != '' && isset($apis['apis'][$name]))
    	{
    		return $apis['apis'][$name];
    	}
    }

	// --------------------------------------------------------------------
        
    /**
     * Hook - allows each method to check for relevant hooks
     */
    public function activate_hook($hook='', $data=array())
    {
        if ($hook AND ee()->extensions->active_hook(DEFAULT_MAP.'_'.$hook) === TRUE)
        {
                $data = ee()->extensions->call(DEFAULT_MAP.'_'.$hook, $data);
                if (ee()->extensions->end_script === TRUE) return;
        }
        
        return $data;
    }
	
		
	// ----------------------------------------------------------------------
	
} // END CLASS

/* End of file entry_api_lib.php  */
/* Location: ./system/expressionengine/third_party/entry_api/libraries/entry_api_lib.php */