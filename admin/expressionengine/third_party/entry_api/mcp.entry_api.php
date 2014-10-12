<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MCP class
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

class Entry_api_mcp {
	
	public $return_data;
	public $settings;
	
	public $api_url = '';
	
	private $_base_url;
	private $show_per_page = 25;

	/**
	 * Constructor
	 */
	public function __construct()
	{	
		//load the library`s
		ee()->load->library('table');
		ee()->load->library(ENTRY_API_MAP.'_lib');
		ee()->load->model(ENTRY_API_MAP.'_model');
		ee()->load->helper('form');	

 		//set the api_url
 		$this->api_url = 'http://'.$_SERVER['SERVER_NAME'];
 
		// get the settings
		//$this->settings = ee()->entry_api_lib->get_settings();
	   
	   //set the right nav
		$right_nav = array();
		$right_nav[lang('entry_api_overview')] = ee()->entry_api_settings->item('base_url');
		$right_nav[lang('entry_api_settings')] = ee()->entry_api_settings->item('base_url').AMP.'method=settings';
		$right_nav[lang('entry_api_status_check')] = ee()->entry_api_settings->item('base_url').AMP.'method=status_check';
		$right_nav[lang('entry_api_testing_tools')] = ee()->entry_api_settings->item('base_url').AMP.'method=testing_tools';
		$right_nav[lang('entry_api_logs')] = ee()->entry_api_settings->item('base_url').AMP.'method=logs';
		$right_nav[lang('entry_api_api_keys')] = ee()->entry_api_settings->item('base_url').AMP.'method=api_keys';
		$right_nav[lang('entry_api_documentation')] = ENTRY_API_DOCS;
		ee()->cp->set_right_nav($right_nav);

		ee()->cp->add_js_script('ui', 'accordion');
	    ee()->javascript->output('
	        $("#accordion").accordion({autoHeight: false,header: "h3"});
	    ');

	    ee()->javascript->compile();
		
		//require the default settings
		require PATH_THIRD.'entry_api/settings.php';
	}

	// ----------------------------------------------------------------

	/**
	 * public method check
	 *
	 * @return 	void
	 */
	public function test()
	{
		/*ee()->load->library('entry_api_public_methods');
		$result = ee()->entry_api_public_methods->method('create_entry', array(
			'site_id' => 1,
			'channel_name' => 'entry_api',
			'title' => 'test'
		));
		var_dump($result);*/
		exit;

	}
	
	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		// Set Breadcrumb and Page Title
		$this->_set_cp_var('cp_page_title', lang('entry_api_module_name'));
		$vars['cp_page_title'] = lang('entry_api_module_name');

		//set the default arrays
		$vars['members'] = $this->_prepare_members();

		//load the view
		return ee()->load->view('overview', $vars, TRUE);   
	}

	// ----------------------------------------------------------------

	/**
	 * Status check Function
	 *
	 * @return 	void
	 */
	public function status_check()
	{
		// Set Breadcrumb and Page Title
		$this->_set_cp_var('cp_page_title', lang('entry_api_module_name'));
		$vars['cp_page_title'] = lang('entry_api_module_name');

		//set the default arrays
		$vars['xmlrpc'] = extension_loaded('xmlrpc');
		$vars['soap'] = extension_loaded('soap');
		$vars['curl'] = extension_loaded('curl');

		//load the view
		return ee()->load->view('status_check', $vars, TRUE);   
	}
	
	
	// ----------------------------------------------------------------

	/**
	 * Create a new member
	 *
	 * @return 	void
	 */
	public function add_member()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			$this->_save_member('new');
			
			ee()->session->set_flashdata('message_success', lang('entry_api_success_add'));	
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api');
		}
	
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->entry_api_settings->item('base_url'), lang('entry_api_module_name'));
		$this->_set_cp_var('cp_page_title', lang('entry_api_add_member'));
		$vars['cp_page_title'] = lang('entry_api_add_member');

		//get membergroups
		$membergroups = ee()->entry_api_model->get_raw_membergroups();
		$selected_membergroups = ee()->entry_api_model->get_raw_selected_membergroups();
		$vars['membergroups'][0] = '--- choose a membergroup ---';
		if(!empty($membergroups))
		{
			foreach($membergroups as $membergoup)
			{
				if(!isset($selected_membergroups[$membergoup->group_id]))
				{
					$vars['membergroups'][$membergoup->group_id] = $membergoup->group_title;
				}
			}
		}

		//get the members
		$members = ee()->entry_api_model->get_raw_members();
		$selected_members = ee()->entry_api_model->get_raw_selected_members();
		$vars['members'][0] = '--- choose a member ---';
		if(!empty($members))
		{
			foreach($members as $member)
			{
				if(!isset($selected_members[$member->member_id]))
				{
					$vars['members'][$member->member_id] = $member->username;
				}
			}
		}

		//set the default arrays
		$vars['connection_services'] = $this->services; 
		$vars['active'] = $this->service_active ; 
		$vars['apis'] = ee()->entry_api_lib->get_api_names(); 
		//$vars['free_apis'] = ee()->entry_api_lib->get_api_free_names(); //$this->free_apis ; 
		$vars['logging'] = $this->service_logging ;
		$vars['debug'] = $this->service_debug ;
		$vars['ajax_url'] = ee()->entry_api_settings->item('site_url').'?ACT='.entry_api_helper::fetch_action_id('Entry_api', 'ajax_cp');
		$vars['urls']['xmlrpc'] = ee()->entry_api_settings->item('xmlrpc_url');
		$vars['urls']['soap'] = ee()->entry_api_settings->item('soap_url');
		$vars['urls']['rest'] = ee()->entry_api_settings->item('rest_url');
		$vars['urls']['custom'] = 'no url defined, mostly called from a file';
	
		//load the view
		return ee()->load->view('new_member', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * show channel Function
	 *
	 * @return 	void
	 */
	public function show_member()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			$this->_save_member('update');
		}
		
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->entry_api_settings->item('base_url'), lang('entry_api_module_name'));
		$this->_set_cp_var('cp_page_title', lang('entry_api_show_member'));
		$vars['cp_page_title'] = lang('entry_api_show_member');

		$vars['entry_api_user'] = $entry_api_user = ee()->entry_api_model->get_entry_api_user(ee()->input->get('entry_api_id'));

		//set the default arrays
		$vars['member'] = ee()->entry_api_model->get_member($entry_api_user->member_id);
		$vars['membergroup'] = ee()->entry_api_model->get_membergroup($entry_api_user->membergroup_id);
		$vars['connection_services'] = $this->services; 
		$vars['active'] = $this->service_active ; 
		$vars['hidden'] = array(
			'entry_api_id' => $entry_api_user->entry_api_id
		);
		$vars['apis'] = ee()->entry_api_lib->get_api_names();//$this->apis ; 
		//$vars['free_apis'] = ee()->entry_api_lib->get_api_free_names(); //$this->free_apis ; 
		$vars['logging'] = $this->service_logging ;
		//$vars['debug'] = $this->service_debug ; //@tmp disabled, not yet implemented
		$vars['urls']['xmlrpc'] = ee()->entry_api_settings->item('xmlrpc_url');
		$vars['urls']['soap'] = ee()->entry_api_settings->item('soap_url');
		$vars['urls']['rest'] = ee()->entry_api_settings->item('rest_url');
		$vars['urls']['custom'] = 'no url defined, mostly called from a file';

		//get the members
		$members = ee()->entry_api_model->get_raw_members();
		if(!empty($members))
		{
			foreach($members as $member)
			{
				$vars['members'][$member->member_id] = $member->username;
			}
		}

		//load the view
		return ee()->load->view('show_member', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	
	/**
	 * Delete Function
	 *
	 * @return 	void
	 */
	public function delete_member()
	{
		//restoren van een email
		if(ee()->input->post('confirm') == 'ok')
		{	
			//delete api keys
			ee()->db->where('entry_api_id', ee()->input->post('entry_api_id'));
			ee()->db->delete('entry_api_keys');
		
			//delete the db record
			ee()->db->delete('entry_api_services_settings', array(
				'entry_api_id' => ee()->input->post('entry_api_id')
			));
			
			ee()->session->set_flashdata('message_success', lang('entry_api_delete_succes'));	
			ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api');
		}
	
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->entry_api_settings->item('base_url'), lang('entry_api_module_name'));
		$this->_set_cp_var('cp_page_title', lang('entry_api_delete_member'));
		$vars['cp_page_title'] = lang('entry_api_delete_member');

		//vars
		$vars['entry_api_id'] = ee()->input->get('entry_api_id');
		//$vars['entry_api_user'] = ee()->entry_api_model->get_entry_api_user(ee()->input->get('entry_api_id'));

		//load the view
		return ee()->load->view('delete', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------

	/**
	 * show channel Function
	 *
	 * @return 	void
	 */
	public function _save_member($method = '')
	{
		if(isset($_POST))
		{
			//remove submit value
			unset($_POST['submit']);

			//empty both member and membergroup
			if($method == 'new')
			{
				if($_POST['member_id'] == 0 && $_POST['membergroup_id'] == 0)
				{
					ee()->session->set_flashdata(
						'message_failure',
						lang('entry_api_no_user_selected')
					);
					ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP.'method=add_member');
				}
			}

			//ee()->db->where('entry_api_id', $_POST['entry_api_id']);
			//ee()->db->where('services', $_POST['connection_services']);
			// if($method != 'new')
			// {
			// 	ee()->db->where('entry_api_id !=', $_GET['entry_api_id']); 
			// }
			//$check = ee()->db->get('entry_api_services_settings')->row();

			//is there already a record with the same data and is the method a insert?
			//than return a error message
			// if(!empty($check) )
			// {
			// 	//set a message
			// 	ee()->session->set_flashdata(
			// 		'message_failure',
			// 		lang('entry_api_error_duplicated_channel')
			// 	);
				
			// 	//redirect
			// 	ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api');	
			// }
			
			//build the array
			$data = array(
				'entry' => array(
					'def_entry'	=> ''
				)
			);

			//insert
			if($method == 'new')
			{				
				//insert data
				ee()->db->insert('entry_api_services_settings', array(
					'member_id' => $_POST['member_id'],
					'membergroup_id' => $_POST['membergroup_id'],
					'services' => !empty($_POST['connection_services']) ? implode('|',$_POST['connection_services']) : '',
					'active' => $_POST['active'],
					'logging' => $_POST['logging'],
					//'debug' => $_POST['debug'], //@tmp disabled, not yet implemented
					'apis' => !empty($_POST['api']) ? implode('|',$_POST['api']) : '',
					'free_apis' => !empty($_POST['free_api']) ? implode('|',$_POST['free_api']) : '',
					'data' => serialize($data),
					'api_keys' => $_POST['api_keys']
				)); 

				$insert_id = ee()->db->insert_id();
				
				//save the keys
				$keys = ee()->entry_api_model->save_keys($insert_id, explode("\n",$_POST['api_keys']));
				
				//update the keys
				ee()->db->where('entry_api_id', $insert_id);
				ee()->db->update('entry_api_services_settings', array(
					'api_keys' => implode("\n", $keys['keys']),
				));
			}
			
			//update
			else 
			{				
				ee()->db->where('entry_api_id', $_GET['entry_api_id']);
				ee()->db->update('entry_api_services_settings', array(
					'services' => !empty($_POST['connection_services']) ? implode('|',$_POST['connection_services']) : '',
					'active' => $_POST['active'],
					'logging' => $_POST['logging'],
					// 'debug' => $_POST['debug'], //@tmp disabled, not yet implemented
					'apis' => !empty($_POST['api']) ? implode('|',$_POST['api']) : '',
					'free_apis' => !empty($_POST['free_api']) ? implode('|',$_POST['free_api']) : '',
					'data' => serialize($data),
					'api_keys' => $_POST['api_keys'],
				)); 
				
				//save the keys
				$keys = ee()->entry_api_model->save_keys($_GET['entry_api_id'], explode("\n",$_POST['api_keys']));
				
				//update the keys
				ee()->db->where('entry_api_id', $_GET['entry_api_id']);
				ee()->db->update('entry_api_services_settings', array(
					'api_keys' => implode("\n", $keys['keys']),
				));
			}
		}

		//duplicated keys?
		$notification = array();
		if(!empty($keys['duplicated']))
		{
			$notification = array(
				'message_failure' => ee()->lang->line(ENTRY_API_MAP.'_duplicated_keys_error')	
			);
		}

		//set a message
		$notification['message_success'] = ee()->lang->line('preferences_updated');
		ee()->session->set_flashdata($notification);

		$entry_api_id = isset($_GET['entry_api_id']) ? $_GET['entry_api_id'] : $insert_id;
		
		//redirect
		ee()->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP.'method=show_member'.AMP.'entry_api_id='.$entry_api_id);			
	}

	// ----------------------------------------------------------------

	/**
	 * Prepare channels to show
	 *
	 * @return 	DB object
	 */
	private function _prepare_members()
	{
		//get the members and their data
		$members = ee()->entry_api_model->get_entry_api_users();
		
		//set the data
		if (!empty($members))
		{
			$vars = array();
			foreach ($members as $member)
			{
				$data['entry_api_id'] = $member->entry_api_id;
				$data['member_id'] = $member->member_id;
				$data['membergroup_id'] = $member->membergroup_id;
				$data['username'] = ee()->entry_api_model->get_username($member->member_id);;
				$data['group_title'] = ee()->entry_api_model->get_membergroup_title($member->membergroup_id);
				$data['services'] = !empty($member->services) ? $member->services : 'not set';
				$data['apis'] = !empty($member->apis) ? $member->apis : 'not set';
				$data['free_apis'] = !empty($member->free_apis) ? $member->free_apis : 'not set';
				$data['active'] = $member->active == 1 ? 'yes' : 'no';
				//$data['data'] = unserialize($member->data);

				$vars[] = $data;
			}
			
			//return the data
			return $vars;
		}
		
		//return empty array
		return array();
	}
		
	/**
	 * Documentation Function
	 *
	 * @return 	void
	 */
	public function documentation()
	{
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->entry_api_settings->item('base_url'), lang('entry_api_module_name'));
		$this->_set_cp_var('cp_page_title', lang('entry_api_documentation'));
		$vars['cp_page_title'] = lang('entry_api_documentation');

		//load the view
		return ee()->load->view('documentation', $vars, TRUE);   
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function logs()
	{
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->entry_api_settings->item('base_url'), lang('entry_api_module_name'));
		$this->_set_cp_var('cp_page_title', lang('entry_api_logs'));
		$vars['cp_page_title'] = lang('entry_api_logs');

		//set vars
		$vars['theme_url'] = ee()->entry_api_settings->item('theme_url');
		$vars['base_url_js'] = ee()->entry_api_settings->item('base_url_js');
		$vars['table_headers'] = $this->logs_table_headers;

		//load the view
		return ee()->load->view('logs', $vars, TRUE);  
	}
	
	// ----------------------------------------------------------------

	/**
	 * This method will be called by the table class to get the results
	 *
	 * @return 	void
	 */
	public function _logs_data($state)
	{
		$offset = $state['offset'];
		$order = $state['sort'];

		$results = ee()->entry_api_model->get_all_logs('', $this->show_per_page, $offset, $order);

		$rows = array();

		if(!empty($results))
		{
			foreach($results as $key=>$val)
			{
				//get the extra data
				$extra = @unserialize($val->data);
				$extra_id = isset($extra['id']) ? ' (ID:'.$extra['id'].')' : '' ;

				$rows[] = array(
					ENTRY_API_MAP.'_log_id' => $val->log_id,
					ENTRY_API_MAP.'_time' => $val->time != '' ? ee()->localize->format_date('%d-%m-%Y %g:%i:%s', $val->time, false) : '-',
					ENTRY_API_MAP.'_username' => $val->username,
					ENTRY_API_MAP.'_ip' => $val->ip,
					ENTRY_API_MAP.'_service' => $val->service,
					ENTRY_API_MAP.'_method' => $val->method,
					ENTRY_API_MAP.'_log_number' => $val->log_number,
					ENTRY_API_MAP.'_msg' => $val->msg.$extra_id,
				);
			}
		}
		//empty
		else
		{
			$rows[] = array(
				ENTRY_API_MAP.'_log_id' => '',
				ENTRY_API_MAP.'_time' => '',
				ENTRY_API_MAP.'_username' => '',
				ENTRY_API_MAP.'_ip' => '',
				ENTRY_API_MAP.'_service' => '',
				ENTRY_API_MAP.'_method' => '',
				ENTRY_API_MAP.'_log_number' => '',
				ENTRY_API_MAP.'_msg' => '',
			);
		}

		//return the data
		return array(
			'rows' => $rows,
			'pagination' => array(
				'per_page'   => $this->show_per_page,
				'total_rows' => ee()->entry_api_model->count_logs(),
			),
		);
	}

	// ----------------------------------------------------------------

	/**
	 * Overview Function
	 *
	 * @return 	void
	 */
	public function api_keys()
	{
		//set vars
		$vars['theme_url'] = ee()->entry_api_settings->item('theme_url');
		$vars['base_url_js'] = ee()->entry_api_settings->item('base_url_js');
		$vars['table_headers'] = $this->api_keys_table_headers;

		//load the view
		return ee()->load->view('api_keys', $vars, TRUE);  
	}
	
	// ----------------------------------------------------------------

	/**
	 * This method will be called by the table class to get the results
	 *
	 * @return 	void
	 */
	public function _api_keys_data($state)
	{
		$offset = $state['offset'];
		$order = $state['sort'];

		$results = ee()->entry_api_model->get_all_api_keys('', $this->show_per_page, $offset, $order);

		$rows = array();

		if(!empty($results))
		{
			foreach($results as $key=>$val)
			{
				$rows[] = array(
					//ENTRY_API_MAP.'_api_key_id' => $val->api_key_id,
					ENTRY_API_MAP.'_api_key' => $val->api_key,
					ENTRY_API_MAP.'_edit' => '<a href="'.BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=entry_api'.AMP.'method=show_member'.AMP.'entry_api_id='.$val->entry_api_id.'">Edit</a>',
				);
			}
		}
		//empty
		else
		{
			$rows[] = array(
				ENTRY_API_MAP.'_api_key_id' => '',
			);
		}

		//return the data
		return array(
			'rows' => $rows,
			'pagination' => array(
				'per_page'   => $this->show_per_page,
				'total_rows' => ee()->entry_api_model->count_api_keys(),
			),
		);
	}
	
	// ----------------------------------------------------------------

	/**
	 * Settings Function
	 *
	 * @return 	void
	 */
	public function settings()
	{
		//is there some data tot save?
		if(ee()->input->post('submit') != '')
		{
			ee()->entry_api_settings->save_post_settings();
		}
				
		// Set Breadcrumb and Page Title
		ee()->cp->set_breadcrumb(ee()->entry_api_settings->item('base_url'), lang('entry_api_module_name'));
		$this->_set_cp_var('cp_page_title', lang('entry_api_settings'));
		$vars['cp_page_title'] = lang('entry_api_settings');

		//default var array
		$vars = array();
		
		//license key
		$license_key = ee()->entry_api_settings->item('license_key');
		$report_stats = ee()->entry_api_settings->item('report_stats');
		$free_apis = ee()->entry_api_settings->item('free_apis');
		$ip_blacklist = ee()->entry_api_settings->item('ip_blacklist');
		$url_trigger = ee()->entry_api_settings->item('url_trigger', 'entry_api');
		$super_admin_key = ee()->entry_api_settings->item('super_admin_key');
		$rest_output_header = ee()->entry_api_settings->item('rest_output_header');

		//Debug
		$debug_yes = ee()->entry_api_settings->item('debug') ? true : false;
		$debug_no = !ee()->entry_api_settings->item('debug') ? true : false;

		//vars for the view and the form
		$vars['settings']['backup'] = array(
			'license_key'   => form_input('license_key', $license_key),	
			ENTRY_API_MAP.'_report_stats'  => array(form_dropdown('report_stats', array('1' => 'yes', '0' => 'no'), $report_stats), 'PHP & EE versions will be anonymously reported to help improve the product.'),
			ENTRY_API_MAP.'_free_apis'   => array(form_multiselect('free_apis[]', ee()->entry_api_lib->get_api_free_names(), $free_apis), 'the selected free api require <b>no</b> inlog.'),	
			ENTRY_API_MAP.'_ip_blacklist'   => array(form_input('ip_blacklist', $ip_blacklist), 'IP seperated by a pipline (|)'),	
			ENTRY_API_MAP.'_url_trigger'   => array(form_input('url_trigger', $url_trigger), 'Trigger segment_1 in de url'),	
			ENTRY_API_MAP.'_super_admin_key'   => array(form_input('super_admin_key', $super_admin_key), 'The super admin API key. With this key you can login as super admin. <br /><b style="color:red;">Be carefull with it, it provides full access to the API.</b>'),	
			ENTRY_API_MAP.'_rest_output_header'   => array(form_input('rest_output_header', $rest_output_header), 'Set the output header for the rest service, handy in some cases with "access control allow origin" issues. <br/><b>For example:</b> <i>Access-Control-Allow-Origin: *</i>'),	
			
			//ENTRY_API_MAP.'_ip_whitelist'   => form_input('ip_whitelist', $ip_whitelist),	
			'debug'   => 'Yes '.form_radio('debug', 1, $debug_yes).' No '.form_radio('debug', 0, $debug_no),	
			//'no_inlog_channels' => array(form_multiselect( 'no_inlog_channel[]', $no_inlog_channels, $no_inlog_channels_selected), 'Only apply on the search and read method'),
		);

		//load the view
		return ee()->load->view('settings', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Retrieve site path
	 */
	function get_site_path()
	{
		// extract path info
		$site_url_path = parse_url(ee()->functions->fetch_site_index(), PHP_URL_PATH);

		$path_parts = pathinfo($site_url_path);
		$site_path = $path_parts['dirname'];

		$site_path = str_replace("\\", "/", $site_path);

		return $site_path;
	}	

	// ----------------------------------------------------------------
	// Testing methods
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools()
	{
		$vars = array();

		//load the view
		return ee()->load->view('testing_tools/tools', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_entry_create()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}
		
		//get the channel names
		$channels = ee()->entry_api_model->get_channel_names();
		
		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/create_entry',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			//'rest_http_auth'   => form_checkbox('rest_http_auth', 'yes'),	
			'channel_name'   => form_dropdown('channel_name', $channels, ''),	
			'title'   => form_input('title', 'test'),	
			'status'   => form_input('status', 'Open'),	
			'sticky'   => form_input('sticky', 'y'),	
			'allow_comments'   => form_input('allow_comments', 'y'),
			'category'   => form_input('category', ''),
			'file_field'   => form_upload('file_field', ''),
			'extra'	=> form_textarea('extra', '')

		);

		//set the method
		$vars['method'] = 'create_entry';
		$vars['action_method'] = 'testing_tools_entry_create';
		
		//load the view
		return ee()->load->view('testing_tools/entry', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_entry_read()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/read_entry',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'entry_id'   => form_input('entry_id', ''),
		);

		//set the method
		$vars['method'] = 'read_entry';
		$vars['action_method'] = 'testing_tools_entry_read';
		
		//load the view
		return ee()->load->view('testing_tools/entry', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_entry_update()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}
		
		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/update_entry',
				'readonly' => 'readonly'
			)),	
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'entry_id'   => form_input('entry_id', ''),
			'title'   => form_input('title', 'test page modify'),	
			'status'   => form_input('status', 'Open'),	
			'sticky'   => form_input('sticky', 'y'),	
			'allow_comments'   => form_input('allow_comments', 'y'),
			'category'   => form_input('category', ''),
			'file_field'   => form_upload('file_field', ''),
			'extra'	=> form_textarea('extra', '')
			
		);

		//set the method
		$vars['method'] = 'update_entry';
		$vars['action_method'] = 'testing_tools_entry_update';
		
		//load the view
		return ee()->load->view('testing_tools/entry', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_entry_delete()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/delete_entry',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'entry_id'   => form_input('entry_id', ''),
		);

		//set the method
		$vars['method'] = 'delete_entry';
		$vars['action_method'] = 'testing_tools_entry_delete';
		
		//load the view
		return ee()->load->view('testing_tools/entry', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_entry_search()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//get the channel names
		$channels = ee()->entry_api_model->get_channel_names();

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/search_entry',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'channel'   => form_input('channel', ''),
			'title'   => form_input('title', ''),
			'limit'   => form_input('limit', '10'),
		);

		//set the method
		$vars['method'] = 'search_entry';
		$vars['action_method'] = 'testing_tools_entry_search';
		
		//load the view
		return ee()->load->view('testing_tools/entry', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_category_group_create()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//get the channel names
		$channels = ee()->entry_api_model->get_channel_names();

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/create_category_group',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'group_name'   => form_input('group_name', ''),	
			'exclude_group'   => form_dropdown('exclude_group', array('none', 'Channel Assignment', 'File Assignment'), ''),	
			'field_html_formatting'   => form_dropdown('field_html_formatting', array('none' => 'none', 'safe' => 'safe', 'all' => 'all'), ''),
			'can_edit_categories'   => form_input('can_edit_categories', 0),	
			'can_delete_categories'   => form_input('can_delete_categories', 0),
		);

		//set the method
		$vars['method'] = 'create_category_group';
		$vars['action_method'] = 'testing_tools_category_group_create';
		
		//load the view
		return ee()->load->view('testing_tools/category_group', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_category_group_read()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/read_category_group',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'group_id'   => form_input('group_id', ''),
		);

		//set the method
		$vars['method'] = 'read_category_group';
		$vars['action_method'] = 'testing_tools_category_group_read';
		
		//load the view
		return ee()->load->view('testing_tools/category_group', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_category_group_update()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/update_category_group',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'group_id'   => form_input('group_id', ''),
			'group_name'   => form_input('group_name', ''),	
			'exclude_group'   => form_dropdown('exclude_group', array('none', 'Channel Assignment', 'File Assignment'), ''),	
			'field_html_formatting'   => form_dropdown('field_html_formatting', array('none' => 'none', 'safe' => 'safe', 'all' => 'all'), ''),
			'can_edit_categories'   => form_input('can_edit_categories', 0),	
			'can_delete_categories'   => form_input('can_delete_categories', 0),
		);

		//set the method
		$vars['method'] = 'update_category_group';
		$vars['action_method'] = 'testing_tools_category_group_update';
		
		//load the view
		return ee()->load->view('testing_tools/category_group', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_category_group_delete()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/delete_category_group',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'group_id'   => form_input('group_id', ''),
		);

		//set the method
		$vars['method'] = 'delete_category_group';
		$vars['action_method'] = 'testing_tools_category_group_delete';
		
		//load the view
		return ee()->load->view('testing_tools/category_group', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_category_create()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//get the channel names
		$channels = ee()->entry_api_model->get_channel_names();

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/create_category',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'group_id'   => form_input('group_id', ''),	
			'parent_id'   => form_input('parent_id', ''),	
			'cat_name'   => form_input('cat_name', ''),	
			'cat_description'   => form_input('cat_description', ''),
			'custom_field'   => form_input('custom_field', ''),	
		);

		//set the method
		$vars['method'] = 'create_category';
		$vars['action_method'] = 'testing_tools_category_create';
		
		//load the view
		return ee()->load->view('testing_tools/category', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_category_read()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}
		
		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/read_category',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'cat_id'   => form_input('cat_id', ''),
		);

		//set the method
		$vars['method'] = 'read_category';
		$vars['action_method'] = 'testing_tools_category_read';
		
		//load the view
		return ee()->load->view('testing_tools/category', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_category_update()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}
		
		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/update_category',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'cat_id'   => form_input('cat_id', ''),	
			'group_id'   => form_input('group_id', ''),	
			'parent_id'   => form_input('parent_id', ''),	
			'cat_name'   => form_input('cat_name', ''),	
			'cat_url_title'   => form_input('cat_url_title', ''),
			'cat_order'   => form_input('cat_order', 1),
			'cat_description'   => form_input('cat_description', ''),
			'custom_field'   => form_input('custom_field', ''),	
		);

		//set the method
		$vars['method'] = 'update_category';
		$vars['action_method'] = 'testing_tools_category_update';
		
		//load the view
		return ee()->load->view('testing_tools/category', $vars, TRUE);   
	}

	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_category_delete()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/delete_category',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'cat_id'   => form_input('cat_id', ''),
		);

		//set the method
		$vars['method'] = 'delete_category';
		$vars['action_method'] = 'testing_tools_category_delete';
		
		//load the view
		return ee()->load->view('testing_tools/category', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_channel_create()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/create_channel',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'site_id'   => form_input('site_id', ee()->entry_api_settings->item('site_id')),
			'channel_title'   => form_input('channel_title', 'Test Channel'),
			'channel_name'   => form_input('channel_name', 'test_channel'),
			'url_title_prefix'   => form_input('url_title_prefix', ''),
			'comment_expiration'   => form_input('comment_expiration', ''),
			//'dupe_id'   => form_input('dupe_id', ''),
			'status_group'   => form_input('status_group', ''),
			'field_group'   => form_input('field_group', ''),
			'channel_url'   => form_input('channel_url', ''),
			'channel_lang'   => form_input('channel_lang', 'eng'),
			'group_order'   => form_input('group_order', ''),
		);

		//set the method
		$vars['method'] = 'create_channel';
		$vars['action_method'] = 'testing_tools_channel_create';
		
		//load the view
		return ee()->load->view('testing_tools/channel', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_channel_read()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/read_channel',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'channel_id'   => form_input('channel_id', ''),
		);

		//set the method
		$vars['method'] = 'read_channel';
		$vars['action_method'] = 'testing_tools_channel_read';
		
		//load the view
		return ee()->load->view('testing_tools/channel', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_channel_update()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/update_channel',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'channel_id'   => form_input('channel_id', ''),
			'channel_title'   => form_input('channel_title', 'Test Channel modify'),
			'channel_name'   => form_input('channel_name', 'test_channel_modify'),
			'url_title_prefix'   => form_input('url_title_prefix', ''),
			'comment_expiration'   => form_input('comment_expiration', ''),
			//'dupe_id'   => form_input('dupe_id', ''),
			'status_group'   => form_input('status_group', ''),
			'field_group'   => form_input('field_group', ''),
			'channel_url'   => form_input('channel_url', ''),
			'channel_lang'   => form_input('channel_lang', 'eng'),
			'group_order'   => form_input('group_order', ''),
		);

		//set the method
		$vars['method'] = 'update_channel';
		$vars['action_method'] = 'testing_tools_channel_update';
		
		//load the view
		return ee()->load->view('testing_tools/channel', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_channel_delete()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/delete_channel',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'channel_id'   => form_input('channel_id', ''),
		);

		//set the method
		$vars['method'] = 'delete_channel';
		$vars['action_method'] = 'testing_tools_channel_delete';
		
		//load the view
		return ee()->load->view('testing_tools/channel', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_comment_create()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/create_comment',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			//'site_id'   => form_input('site_id', ee()->entry_api_settings->item('site_id')),
			'entry_id'   => form_input('entry_id', ''),
			'status'   => form_input('status', 'open'),
			'ip_address'   => form_input('ip_address', ee()->input->ip_address()),
			'comment'   => form_textarea('comment', ''),
		);

		//set the method
		$vars['method'] = 'create_comment';
		$vars['action_method'] = 'testing_tools_comment_create';
		
		//load the view
		return ee()->load->view('testing_tools/comment', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_comment_read()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/read_comment',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			//'channel_id'   => form_input('channel_id', ''),
		);

		//set the method
		$vars['method'] = 'read_comment';
		$vars['action_method'] = 'testing_tools_comment_read';
		
		//load the view
		return ee()->load->view('testing_tools/comment', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_comment_update()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/update_comment',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
		);

		//set the method
		$vars['method'] = 'update_comment';
		$vars['action_method'] = 'testing_tools_comment_update';
		
		//load the view
		return ee()->load->view('testing_tools/comment', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_comment_delete()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/delete_comment',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'channel_id'   => form_input('channel_id', ''),
		);

		//set the method
		$vars['method'] = 'delete_comment';
		$vars['action_method'] = 'testing_tools_comment_delete';
		
		//load the view
		return ee()->load->view('testing_tools/comment', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_member_create()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/create_member',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'username'   => form_input('username', 'test_person'),
			'password'   => form_input('password', '123123'),
			'password_confirm'   => form_input('password_confirm', '123123'),
			'screen_name'   => form_input('screen_name', 'Test person'),
			'email'   => form_input('email', 'test@test.nl'),
			'group_id'   => form_input('group_id', '4'),
			'bday_y'   => form_input('bday_y', '2000'),
			'bday_m'   => form_input('bday_m', '12'),
			'bday_d'   => form_input('bday_d', '24'),
			'url'   => form_input('url', 'http://test.nl'),
			'location'   => form_input('location', 'holland'),
			'occupation'   => form_input('occupation', 'occupation'),
			'interests'   => form_input('interests', 'interests'),
			'aol_im'   => form_input('aol_im', 'aol_im'),
			'icq'   => form_input('icq', 'icq'),
			'yahoo_im'   => form_input('yahoo_im', 'yahoo_im'),
			'msn_im'   => form_input('msn_im', 'msn_im'),
			'bio'   => form_input('bio', 'bio'),
		);

		//set the method
		$vars['method'] = 'create_member';
		$vars['action_method'] = 'testing_tools_member_create';
		
		//load the view
		return ee()->load->view('testing_tools/member', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_member_read()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/read_member',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'member_id'   => form_input('member_id', ''),
		);

		//set the method
		$vars['method'] = 'read_member';
		$vars['action_method'] = 'testing_tools_member_read';
		
		//load the view
		return ee()->load->view('testing_tools/member', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_member_update()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/update_member',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'member_id'   => form_input('member_id', ''),
			//'username'   => form_input('username', 'test_person'),
			//'password'   => form_input('password', '123123'),
			//'password_confirm'   => form_input('password_confirm', '123123'),
			'screen_name'   => form_input('screen_name', 'Test person'),
			'email'   => form_input('email', 'test@test.nl'),
			'group_id'   => form_input('group_id', '4'),
			'bday_y'   => form_input('bday_y', '2000'),
			'bday_m'   => form_input('bday_m', '12'),
			'bday_d'   => form_input('bday_d', '24'),
			'url'   => form_input('url', 'http://test.nl'),
			'location'   => form_input('location', 'holland'),
			'occupation'   => form_input('occupation', 'occupation'),
			'interests'   => form_input('interests', 'interests'),
			'aol_im'   => form_input('aol_im', 'aol_im'),
			'icq'   => form_input('icq', 'icq'),
			'yahoo_im'   => form_input('yahoo_im', 'yahoo_im'),
			'msn_im'   => form_input('msn_im', 'msn_im'),
			'bio'   => form_input('bio', 'bio'),
		);

		//set the method
		$vars['method'] = 'update_member';
		$vars['action_method'] = 'testing_tools_member_update';
		
		//load the view
		return ee()->load->view('testing_tools/member', $vars, TRUE);   
	}
	
	// ----------------------------------------------------------------
	 
	/**
	 * Retrieve site path
	 */
	function testing_tools_member_delete()
	{
		$vars = array();

		//action
		$vars['response'] = '';
		if(isset($_POST['submit']))
		{
			ee()->load->library('entry_api_testing_tool');
			$vars['response'] = ee()->entry_api_testing_tool->init();
		}

		//set the data
		$vars['fields']['data'] = array(
			'path_xmlrpc'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/xmlrpc',
				'readonly' => 'readonly'
			)),
			'path_soap'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/soap?wsdl',
				'readonly' => 'readonly'
			)),
			'path_rest'   => form_input(array(
				'name' => 'path',
				'value' => $this->api_url.'/index.php/'.ee()->entry_api_settings->item('url_trigger').'/rest/delete_member',
				'readonly' => 'readonly'
			)),
			'path_custom'   => form_input(array(
				'name' => 'path',
				'value' => 'custom',
				'readonly' => 'readonly'
			)),
			'member_id'   => form_input('member_id', ''),
		);

		//set the method
		$vars['method'] = 'delete_member';
		$vars['action_method'] = 'testing_tools_member_delete';
		
		//load the view
		return ee()->load->view('testing_tools/member', $vars, TRUE);   
	}

	// --------------------------------------------------------------------

	/**
	 * Set cp var
	 *
	 * @access     private
	 * @param      string
	 * @param      string
	 * @return     void
	 */
	private function _set_cp_var($key, $val)
	{
		if (version_compare(APP_VER, '2.6.0', '<'))
		{
			ee()->cp->set_variable($key, $val);
		}
		else
		{
			ee()->view->$key = $val;
		}
	}


	
}
/* End of file mcp.entry_api.php */
/* Location: /system/expressionengine/third_party/entry_api/mcp.entry_api.php */