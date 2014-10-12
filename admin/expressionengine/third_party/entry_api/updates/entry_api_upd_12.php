<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update file for the update to 1.2
 * This version add a new settings
 * Change the sessions_end to sessions_start
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2012 Reinos.nl Internet Media
 */
 
include(PATH_THIRD.'entry_api/config.php');
 
class Entry_api_upd_12
{
	private $EE;
	private $version = '1.2';
	
	/**
	 * Construct method
	 *
	 * @return      boolean         TRUE
	 */
	public function __construct()
	{
		//get a intance of the EE object
		$this->EE = get_instance();
		
		//load the classes
		ee()->load->dbforge();
	}
	
	/**
	 * Run the update
	 *
	 * @return      boolean         TRUE
	 */
	public function run_update()
	{
		$sql = array();
		
		//add new extension
		$sql[] = "INSERT INTO `exp_entry_api_settings` (`site_id`, `var`, `value`) VALUES (1, 'debug', false)";
		$sql[] = "UPDATE  `exp_extensions` SET `method` = 'sessions_start', `hook` = 'sessions_start', `version` = '1.2' WHERE `class` = 'Entry_api_ext' AND `hook` = 'sessions_end'";

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
}