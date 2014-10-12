<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Update file for the update to 2.2
 * Add a new field for the search fields
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2012 Reinos.nl Internet Media
 */
 
include(PATH_THIRD.'entry_api/config.php');
 
class Entry_api_upd_30
{
	private $EE;
	private $version = '3.0';
	
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
		
		//add free to the settings
		$sql[] = "ALTER TABLE `".ee()->db->dbprefix."entry_api_services_settings` ADD `membergroup_id` INT NOT NULL";
		$sql[] = "ALTER TABLE `".ee()->db->dbprefix."entry_api_services_settings` ADD `free_apis` TEXT NOT NULL";
		$sql[] = "ALTER TABLE `".ee()->db->dbprefix."entry_api_services_settings` ADD `api_keys` TEXT NOT NULL";

		//add logging table
		$sql[] = "
			CREATE TABLE `".ee()->db->dbprefix."entry_api_logs` (
			  `log_id` int(7) unsigned NOT NULL AUTO_INCREMENT,
			  `site_id` int(7) unsigned NOT NULL DEFAULT '0',
			  `username` varchar(255) NOT NULL DEFAULT '',
			  `service` varchar(255) NOT NULL DEFAULT '',
			  `ip` varchar(255) NOT NULL DEFAULT '',
			  `log_number` int(7) unsigned NOT NULL DEFAULT '0',
			  `msg` varchar(255) NOT NULL DEFAULT '',
			  `method` varchar(255) NOT NULL DEFAULT '',
			  `data` text NOT NULL,
			  PRIMARY KEY (`log_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";
		
		//ADD KEYS table
		$sql[] = "
			CREATE TABLE `".ee()->db->dbprefix."entry_api_keys` (
			  `api_key_id` int(7) unsigned NOT NULL AUTO_INCREMENT,
			  `site_id` int(7) unsigned NOT NULL DEFAULT '0',
			  `entry_api_id` int(7) unsigned NOT NULL DEFAULT '0',
			  `api_key` varchar(200) NOT NULL DEFAULT '',
			  PRIMARY KEY (`api_key_id`)
			) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
		";

		foreach ($sql as $query)
		{
			ee()->db->query($query);
		}
	}
}