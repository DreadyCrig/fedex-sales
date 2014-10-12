<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MOD file
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @link        http://reinos.nl/add-ons//add-ons/entry-api
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */
 
// ------------------------------------------------------------------------

/**
 * @deprecated in v1.1.1
 * Entry_api is now working on full url instead of the ACT uri.
 */	

/**
 * Include the config file
 */
require_once PATH_THIRD.'entry_api/config.php';
 
class Entry_api {
	
	private $xmlrpc;
	
	private $soap;
	
	private $EE;
	
	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//load the Classes
		//$this->EE =& get_instance();
		
		//load entry_api helper
		ee()->load->library('entry_api_lib');

	}

	// ----------------------------------------------------------------

	/**
	 * Prepare channels to show
	 * 
	 * @return 	DB object
	 */
	public function ajax_cp()
	{
		
		switch($_GET['function'])
		{
			case 'get_channels' : echo json_encode(ee()->entry_api_model->get_channels_for_member($_GET['member_id']));
		}

		exit;
	}
}


/* End of file mod.entry_api.php */
/* Location: /system/expressionengine/third_party/entry_api/mod.entry_api.php */