<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * UPD file
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
 
class Entry_api_upd {
	
	public $version = ENTRY_API_VERSION;
	
	private $EE;
	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		//create a instance of the EE object
		//$this->EE =& get_instance();
		
		//load the classes
		ee()->load->dbforge();
		
		//require the settings
		require PATH_THIRD.'entry_api/settings.php';
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Installation Method
	 *
	 * @return 	boolean 	TRUE
	 */
	public function install()
	{	
		if (strnatcmp(phpversion(),'5.3') <= 0) 
		{ 
			show_error('Entry Api require PHP 5.3 or higher.', 500, 'Oeps!');
			return FALSE;
		}

		//set the module data
		$mod_data = array(
			'module_name'			=> ENTRY_API_CLASS,
			'module_version'		=> ENTRY_API_VERSION,
			'has_cp_backend'		=> "y",
			'has_publish_fields'	=> 'n'
		);
	
		//insert the module
		ee()->db->insert('modules', $mod_data);
		
		//create some actions for the ajax in the control panel
		$this->_register_action('ajax_cp');

		//install the extension
		$this->_register_hook('sessions_start', 'sessions_start');
		//$this->_register_hook('publisher_set_language', 'publisher_set_language');
		
		//create the Login backup tables
		$this->_create_entry_api_tables();

		//load the helper
		ee()->load->library('entry_api_lib');
		
		//insert the settings data
		ee()->entry_api_settings->first_import_settings();	
		
		return TRUE;
	}

	// ----------------------------------------------------------------
	
	/**
	 * Uninstall
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function uninstall()
	{
		//delete the module
		ee()->db->where('module_name', ENTRY_API_CLASS);
		ee()->db->delete('modules');

		//remove databases
		ee()->dbforge->drop_table('entry_api_services_settings');
		ee()->dbforge->drop_table('entry_api_settings');
		ee()->dbforge->drop_table('entry_api_keys');
		ee()->dbforge->drop_table('entry_api_logs');
		
		//remove actions
		ee()->db->where('class', ENTRY_API_CLASS);
		ee()->db->delete('actions');
		
		//remove the extension
		ee()->db->where('class', ENTRY_API_CLASS.'_ext');
		ee()->db->delete('extensions');
		
		return TRUE;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Module Updater
	 *
	 * @return 	boolean 	TRUE
	 */	
	public function update($current = '')
	{
		//nothing to update
		if ($current == '' OR $current == $this->version)
			return FALSE;
		
		//loop through the updates and install them.
		if(!empty($this->updates))
		{
			foreach ($this->updates as $version)
			{
				//$current = str_replace('.', '', $current);
				//$version = str_replace('.', '', $version);
				
				if ($current < $version)
				{
					$this->_init_update($version);
				}
			}
		}
			
		return true;
	}
		
	// ----------------------------------------------------------------
	
	/**
	 * Add the tables for the module
	 *
	 * @return 	boolean 	TRUE
	 */	
	private function _create_entry_api_tables()
	{		
		// add entry_api setting table
		$fields = array(
				'entry_api_id'	=> array(
									'type'				=> 'int',
									'constraint'		=> 7,
									'unsigned'			=> TRUE,
									'null'				=> FALSE,
									'auto_increment'	=> TRUE,
								),
				'member_id'	=> array(
									'type'				=> 'int',
									'constraint'		=> 7,
									'unsigned'			=> TRUE,
									'null'				=> FALSE,
								),
				'membergroup_id'	=> array(
									'type'				=> 'int',
									'constraint'		=> 7,
									'unsigned'			=> TRUE,
									'null'				=> FALSE,
								),
				'services'  => array(
									'type' 				=> 'varchar',
									'constraint'		=> '255',
									'null'				=> FALSE,
									'default'			=> ''
								),
				'active'  => array(
									'type' 				=> 'int',
									'constraint'		=> '1',
									'null'				=> FALSE,
									'default'			=> 0
								),
				'logging'  => array(
									'type' 				=> 'int',
									'constraint'		=> '1',
									'null'				=> FALSE,
									'default'			=> 0
								),
				'debug'  => array(
									'type' 				=> 'int',
									'constraint'		=> '1',
									'null'				=> FALSE,
									'default'			=> 0
								),
				'apis'  => array(
									'type' 				=> 'varchar',
									'constraint'		=> '255',
									'null'				=> FALSE,
									'default'			=> ''
								),
				'free_apis'  => array(
									'type' 				=> 'varchar',
									'constraint'		=> '255',
									'null'				=> FALSE,
									'default'			=> ''
								),
				'search_fields'  => array(
									'type' 				=> 'text',
									'null'				=> TRUE,
								),
				'api_keys'  => array(
									'type' 				=> 'text',
									'null'				=> TRUE,
								),
				'data'  => array(
									'type' 				=> 'text',
									'null'				=> TRUE,
								),
		);
		
		//create the channel setting table
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('entry_api_id', TRUE);
		ee()->dbforge->create_table('entry_api_services_settings', TRUE);
	
		// add log tables
		$fields = array(
				'log_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'site_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'username'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'time'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '150',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'service'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'ip'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'log_number'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'method'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'msg'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'total_queries'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'queries'  => array(
									'type' 				=> 'text',
									'null'				=> FALSE,
								),
				'data'  => array(
									'type' 				=> 'text',
									'null'				=> FALSE,
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('log_id', TRUE);
		ee()->dbforge->create_table('entry_api_logs', TRUE);

		// add config tables
		$fields = array(
				'settings_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'site_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'var'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								),
				'value'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '255',
									'null'			=> FALSE,
									'default'			=> ''
								),
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('settings_id', TRUE);
		ee()->dbforge->create_table('entry_api_settings', TRUE);
		
		// add config tables
		$fields = array(
				'api_key_id'	=> array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'auto_increment'	=> TRUE
								),
				'site_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'entry_api_id'  => array(
									'type'			=> 'int',
									'constraint'		=> 7,
									'unsigned'		=> TRUE,
									'null'			=> FALSE,
									'default'			=> 0
								),
				'api_key'  => array(
									'type' 			=> 'varchar',
									'constraint'		=> '200',
									'null'			=> FALSE,
									'default'			=> ''
								)
		);
		
		//create the backup database
		ee()->dbforge->add_field($fields);
		ee()->dbforge->add_key('api_key_id', TRUE);
		ee()->dbforge->create_table('entry_api_keys', TRUE);
		
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Install a hook for the extension
	 *
	 * @return 	boolean 	TRUE
	 */		
	private function _register_hook($hook, $method = NULL, $priority = 10)
	{
		if (is_null($method))
		{
			$method = $hook;
		}

		if (ee()->db->where('class', ENTRY_API_CLASS.'_ext')
			->where('hook', $hook)
			->count_all_results('extensions') == 0)
		{
			ee()->db->insert('extensions', array(
				'class'		=> ENTRY_API_CLASS.'_ext',
				'method'	=> $method,
				'hook'		=> $hook,
				'settings'	=> '',
				'priority'	=> $priority,
				'version'	=> ENTRY_API_VERSION,
				'enabled'	=> 'y'
			));
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Create a action
	 *
	 * @return 	boolean 	TRUE
	 */	
	private function _register_action($method)
	{		
		if (ee()->db->where('class', ENTRY_API_CLASS)
			->where('method', $method)
			->count_all_results('actions') == 0)
		{
			ee()->db->insert('actions', array(
				'class' => ENTRY_API_CLASS,
				'method' => $method
			));
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Run a update from a file
	 *
	 * @return 	boolean 	TRUE
	 */	
	
	private function _init_update($version, $data = '')
	{
		// run the update file
		$class_name = 'Entry_api_upd_'.str_replace('.', '', $version);
		require_once(PATH_THIRD.'entry_api/updates/'.strtolower($class_name).'.php');
		$updater = new $class_name($data);
		return $updater->run_update();
	}
	
}
/* End of file upd.entry_api.php */
/* Location: /system/expressionengine/third_party/entry_api/upd.entry_api.php */