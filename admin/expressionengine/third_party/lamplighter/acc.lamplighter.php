<?php if( ! defined('BASEPATH')) exit('No direct script access allowed');
// Include config for version info.
require_once PATH_THIRD.'/lamplighter/config.php';

/**
 * devot:ee Monitor Accessory
 *
 * @package     ExpressionEngine
 * @subpackage  Add-ons
 * @category    Accessories
 * @author      Visual Chefs, LLC
 * @copyright   Copyright (c) 2011-2013, Visual Chefs, LLC
 */
class Lamplighter_acc
{
	/**
	 * Accessory information
	 */
	public $name        = 'Lamplighter';
	public $id          = 'lamplighter';
	public $version     = LAMPLIGHTER_VERSION;
	public $description = 'Monitor your add-ons for updates.';
	public $sections    = array();

	/**
	 * CodeIgniter super object
	 *
	 * @var  CI_Controller
	 */
	protected $EE;

	/**
	 * URL to the theme files
	 *
	 * @var  string
	 */
	protected $theme_url;

	/**
	 * Constructor
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->EE->load->add_package_path( PATH_THIRD.'lamplighter/' );
		$this->EE->load->library('devotee_library');

		// Set theme URL
		$this->theme_url = defined('URL_THIRD_THEMES')
			? URL_THIRD_THEMES . '/lamplighter/'
			: $this->EE->config->item('theme_folder_url') . 'third_party/lamplighter/';

	}

	/**
	 * Install accessory
	 *
	 * @return  bool
	 */
	public function install()
	{
		$this->EE->load->dbforge();

		// Create the settings table
		$this->EE->dbforge->add_field('id int(10) unsigned NOT NULL AUTO_INCREMENT');
		$this->EE->dbforge->add_field('member_id int(10) unsigned NOT NULL');
		$this->EE->dbforge->add_field('package varchar(100) NOT NULL');
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->add_key('member_id');
		$this->EE->dbforge->create_table('lamplighter_hidden_addons', TRUE);

		return TRUE;
	}

	/**
	 * Update accessory
	 *
	 * @return  bool
	 */
	public function update()
	{
		if ( ! $this->EE->db->table_exists('lamplighter_hidden_addons') ) {
			$this->EE->load->dbforge();
			$this->EE->dbforge->add_field('id int(10) unsigned NOT NULL AUTO_INCREMENT');
			$this->EE->dbforge->add_field('member_id int(10) unsigned NOT NULL');
			$this->EE->dbforge->add_field('package varchar(100) NOT NULL');
			$this->EE->dbforge->add_key('id', TRUE);
			$this->EE->dbforge->add_key('member_id');
			$this->EE->dbforge->create_table('lamplighter_hidden_addons', TRUE);
		}

		return TRUE;
	}

	/**
	 * Uninstall accessory
	 *
	 * @return  bool
	 */
	public function uninstall()
	{
		$this->EE->load->dbforge();

		// Drop the settings table
		$this->EE->dbforge->drop_table('lamplighter_hidden_addons');

		return TRUE;
	}

	/**
	 * Set accessory sections
	 *
	 * @return  void
	 */
	public function set_sections()
	{
		$this->sections['Add-on Information'] = $this->_init();

		// Add theme assets to CP
		$this->EE->cp->add_to_foot('<link rel="stylesheet" href="' . $this->theme_url . 'styles/accessory.css?v=' . $this->version . '" />');
		$this->EE->cp->add_to_foot('<script type="text/javascript" src="' . $this->theme_url . 'scripts/accessory.js?v=' . $this->version . '"></script>');
	}

	/**
	 * Initial view of the accessory... allows us to load it through Ajax
	 *
	 * @return  string
	 */
	protected function _init()
	{
		$link = htmlspecialchars_decode(BASE . AMP . 'C=addons_accessories' . AMP . 'M=process_request' . AMP . 'accessory=lamplighter' . AMP . 'method=process_load');

		return $this->EE->load->view('acc_init', array(
			'link' => $link,
			'cp'   => $this->EE->cp,
		), TRUE);
	}

	/**
	 * AJAX method for loading the initial view
	 *
	 * @return  void
	 */
	public function process_load()
	{
		$this->process_refresh(FALSE);
	}

	/**
	 * AJAX method for clearing cache and reloading the add-ons list
	 *
	 * @param   bool  Whether to delete the cache
	 * @return  void
	 */
	public function process_refresh($delete_cache = TRUE, $display_hidden_addons = FALSE)
	{
		if(AJAX_REQUEST)
		{
			// Delete cache
			if($delete_cache)
			{
				$this->EE->functions->delete_directory(APPPATH . 'cache/lamplighter');
			}

			// Output HTML from the view
			echo $this->EE->devotee_library->get_addons($display_hidden_addons);
			exit;
		}
	}

	/**
	 * AJAX method for hiding certain add-ons from the accessory
	 *
	 * @return  void
	 */
	public function process_hide_addon()
	{
		if(AJAX_REQUEST)
		{
			// Add setting to database
			$this->EE->db->insert('lamplighter_hidden_addons', array(
				'member_id' => $this->EE->session->userdata('member_id'),
				'package'   => $this->EE->input->get('package')
			));

			// Refresh view
			$this->process_refresh(FALSE);
		}
	}

	/**
	 * AJAX method for including hidden add-ons in the list of installed
	 * add-ons.
	 *
	 * @return	void
	 */
	public function process_display_hidden_addons()
	{
		if(AJAX_REQUEST)
		{
			$this->process_refresh(FALSE, TRUE);
		}
	}

	/**
	 * AJAX method for removing an add-on from the lamplighter_hidden_addons
	 * table so that is shows up in the add-on list normally.
	 *
	 * @return  void
	 */
	public function process_unhide_addon()
	{
		if(AJAX_REQUEST)
		{
			// Fetch the add-on package name from the URL
			$package_name = $this->EE->input->get('package');

			// Delete hidden add-on setting for user
			$this->EE->db->where('package', $package_name)->where('member_id', $this->EE->session->userdata('member_id'))->delete('lamplighter_hidden_addons');

			// Refresh view
			$this->process_refresh(FALSE);
		}
	}

	/**
	 * This method fetches general site data to be used as
	 * debug information.
	 *
	 * @return void
	 */
	public function process_debug_info()
	{
		$this->EE->load->library('user_agent');

		$server_software = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'N/A';

		$vars = array(
			'ee_version'	     => $this->EE->config->item('app_version'),
			'php_version'	     => phpversion(),
			'db_driver'		     => $this->EE->db->dbdriver,
			'updates'		     => $this->EE->devotee_library->get_addons(TRUE, FALSE),
			'browser'		     => $this->EE->agent->browser().' '.$this->EE->agent->version(),
			'cookie_domain'	     => $this->EE->config->item('cookie_domain'),
			'cookie_path'        => $this->EE->config->item('cookie_path'),
			'user_session_type'  => $this->EE->config->item('user_session_type'),
			'admin_session_type' => $this->EE->config->item('admin_session_type'),
			'cp_cookie_domain'	 => $this->EE->config->item('cp_cookie_domain'),
			'cp_cookie_path'     => $this->EE->config->item('cp_cookie_path'),
			'cp_session_ttl'     => $this->EE->config->item('cp_session_ttl'),
			'server_software'    => $server_software
		);

		header('Content-Type : text/plain');
		exit( $this->EE->load->view('acc_debug_info', $vars, TRUE) );
	}
}
