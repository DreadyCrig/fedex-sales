<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Entry API Extension
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
 
class Entry_api_ext 
{	
	
	public $name			= ENTRY_API_NAME;
	public $description		= ENTRY_API_DESCRIPTION;
	public $version			= ENTRY_API_VERSION;
	public $settings 		= array();
	public $docs_url		= ENTRY_API_DOCS;
	public $settings_exist	= 'n';
	public $required_by 	= array('Entry API Module');
	
	private $EE;
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		//get the instance of the EE object
		//$this->EE =& get_instance();		
	}

	/**
	 * sessions_start
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function sessions_start($ee)
	{
		ee()->load->helper('entry_api_helper');

		//set the session to the var
		ee()->session = $ee;

		//just an page request?
		if (REQ == 'PAGE' && !empty($ee))
		{		
			//load the lib
			ee()->load->library('entry_api_lib');
	
			//get the trigger
			$url_trigger = ee()->entry_api_settings->item('url_trigger', 'entry_api');

			//is the first segment 'entry_api'
			$is_entry_api = ee()->uri->segment(1) == $url_trigger ? true : false;
			
			//is the request a page and is the first segment entry_api?
			//than we need to trigger te services
			if($is_entry_api)
			{
				//set agent if missing
				$_SERVER['HTTP_USER_AGENT'] = ee()->input->user_agent() == false ? '0' : ee()->input->user_agent();

				//debug?
				if(ee()->entry_api_settings->item('debug'))
				{
					//show better error reporting
					error_reporting(E_ALL);
					ini_set('display_errors', '1');

					//set the DB to save the queries
					ee()->db->save_queries = true;
				}
				     			
				//service request
				$service_request = ee()->uri->segment(2);
				
				//load the route class
				include_once PATH_THIRD .'entry_api/libraries/entry_api_route.php';
				
				//call the class
				$this->entry_api = new Entry_api_route($service_request);
					
				//stop the whole process because we will not show futher more 
				ee()->extensions->end_script = true;
				die();	
			}	
			
		}
		//print_r(ee()->session->userdata);	
	}

	/**
	 * sessions_start
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	// function publisher_set_language()
	// {
	// 	//echo 1;
	// 	//entry_api_helper::log_array(array('asdf'));	
	// }

	
	function debug_string_backtrace() 
	{ 
		ob_start(); 
		debug_print_backtrace(); 
		$trace = ob_get_contents(); 
		ob_end_clean(); 

		// Remove first item from backtrace as it's this function which 
		// is redundant. 
		$trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1); 

		// Renumber backtrace items. 
		$trace = preg_replace ('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace); 

		return $trace; 
    } 
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		//the module will install the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		//the module will disable the extension if needed
		return true;
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	function update_extension($current = '')
	{
		//the module will update the extension if needed
		return true;
	}	
	
	// ----------------------------------------------------------------------
}

/* End of file ext.entry_api.php */
/* Location: /system/expressionengine/third_party/entry_api/ext.entry_api.php */