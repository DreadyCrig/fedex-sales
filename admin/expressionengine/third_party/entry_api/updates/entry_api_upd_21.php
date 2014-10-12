<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update file for the update to 21
 * This version add a new settings
 * - no_inlog_channel (for those channels you wont have to login to call the methods search() and read())
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2012 Reinos.nl Internet Media
 */
 
include(PATH_THIRD.'entry_api/config.php');
 
class Entry_api_upd_21
{
	private $EE;
	private $version = '2.1';
	
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
		$sql[] = "INSERT INTO `".ee()->db->dbprefix."entry_api_settings` (`site_id`, `var`, `value`) VALUES (1, 'no_inlog_channel', '')";

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
}