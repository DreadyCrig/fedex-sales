<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update file for the update to 1.1.1
 * This version add a new extension so we can use the url to create nice links
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2012 Reinos.nl Internet Media
 */
 
include(PATH_THIRD.'entry_api/config.php');
 
class Entry_api_upd_111
{
	private $EE;
	private $version = '1.1.1';
	
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
		$sql[] = "INSERT INTO `exp_extensions` (`class`, `method`, `hook`, `settings`, `priority`, `version`, `enabled`) VALUES ('Entry_api_ext', 'sessions_end', 'sessions_end', '', 10, '1.1.1', 'y')";
		
		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
}