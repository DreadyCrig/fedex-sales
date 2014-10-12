<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Auth API
 *
 * @package		Entry API
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2014 Reinos.nl Internet Media
 */

/**
 * Include the config file
 */
require_once PATH_THIRD.'entry_api/config.php';

class Entry_api_member
{
	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		//include_once PATH_THIRD.'entry_api/libraries/entry_api_base_api.php';
		// load the stats class because this is not loaded because of the use of the extension
		ee()->load->library('stats'); 
		
		/** ---------------------------------------
		/** load the models
		/** ---------------------------------------*/
		ee()->load->model('member_model');
		
		//set the default data
		$this->_default_data();


		//require the default settings
        require PATH_THIRD.'entry_api/settings.php';
	}

	//-------------------------------------------------------------------------

	/**
     * create_member
    */
	public function create_member($post_data = array())
	{		
		/** ---------------------------------------
		/**  can we add a new member, do we have the right for it
		/** ---------------------------------------*/
		if(ee()->session->userdata('can_admin_members') != 'y')
		{
			return $this->service_error['error_member_no_right'];
		}

		/** ---------------------------------------
		/**  allow member registration
		/** ---------------------------------------*/
		if (ee()->config->item('allow_member_registration') == 'n')
		{
			return $this->service_error['error_member_registration'];
		}

		/** ---------------------------------------
		/**  Set the defaul globals
		/** ---------------------------------------*/
		$default = array(
			'username', 'password', 'password_confirm', 'email',
			'screen_name', 'url', 'location'
		);

		//assign them to a val
		foreach ($default as $val)
		{
			if ( ! isset($post_data[$val])) $post_data[$val] = '';
		}

		//screen name is the same as username if empty
		if ($post_data['screen_name'] == '')
		{
			$post_data['screen_name'] = $post_data['username'];
		}

		// Instantiate validation class
		if ( ! class_exists('EE_Validate'))
		{
			require APPPATH.'libraries/Validate.php';
		}

		/** ---------------------------------------
		/**  Start the validatiing
		/** ---------------------------------------*/
		$VAL = new EE_Validate(array(
			'member_id'			=> '',
			'val_type'			=> 'new', // new or update
			'fetch_lang' 		=> TRUE,
			'require_cpw' 		=> FALSE,
		 	'enable_log'		=> FALSE,
			'username'			=> trim_nbs($post_data['username']),
			'cur_username'		=> '',
			'screen_name'		=> trim_nbs($post_data['screen_name']),
			'cur_screen_name'	=> '',
			'password'			=> $post_data['password'],
		 	'password_confirm'	=> $post_data['password'],
		 	'cur_password'		=> '',
		 	'email'				=> trim($post_data['email']),
		 	'cur_email'			=> ''
		 ));

		/** ---------------------------------------
		/**  validate the username, screen_name, password and email
		/** ---------------------------------------*/
		$VAL->validate_username();
		$VAL->validate_screen_name();
		$VAL->validate_password();
		$VAL->validate_email();

		/** ---------------------------------------
		/**  Do we have any custom fields?
		/** ---------------------------------------*/
		$query = ee()->db->select('m_field_id, m_field_name, m_field_label, m_field_type, m_field_list_items, m_field_required')
							  ->where('m_field_reg', 'y')
							  ->get('member_fields');

		$cust_errors = array();
		$cust_fields = array();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				$field_name = 'm_field_id_'.$row['m_field_id'];

				// Assume we're going to save this data, unless it's empty to begin with
				$valid = isset($post_data[$field_name]) && $post_data[$field_name] != '';

				// Basic validations
				if ($row['m_field_required'] == 'y' && ! $valid)
				{
					$cust_errors[] = $row['m_field_label'];
				}
				elseif ($row['m_field_type'] == 'select' && $valid)
				{
					// Ensure their selection is actually a valid choice
					$options = explode("\n", $row['m_field_list_items']);

					if (! in_array(htmlentities($post_data[$field_name]), $options))
					{
						$valid = FALSE;
						$cust_errors[] =$row['m_field_label'];
					}
				}

				if ($valid)
				{
					$cust_fields[$field_name] = ee()->security->xss_clean($post_data[$field_name]);
				}
			}
		}

		if (isset($post_data['email_confirm']) && $post_data['email'] != $post_data['email_confirm'])
		{
			$cust_errors[] = lang('mbr_emails_not_match');
		}

		//merge error to one array
		$errors = array_merge($VAL->errors, $cust_errors);

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($errors) || count($errors) > 0)
		{
			//generate error
			$this->service_error['error_field_single']['message'] .= ' '.$errors[0];
			return $this->service_error['error_field_single'];
		}

		ee()->load->helper('security');
		ee()->load->helper('url'); 

		/** ---------------------------------------
		/**  Assign the base query data
		/** ---------------------------------------*/
		$data = array(
			'username'		=> trim_nbs($post_data['username']),
			'password'		=> sha1($post_data['password']),
			'ip_address'	=> ee()->input->ip_address(),
			'unique_id'		=> ee()->functions->random('encrypt'),
			'join_date'		=> ee()->localize->now,
			'email'			=> trim_nbs($post_data['email']),
			'screen_name'	=> trim_nbs($post_data['screen_name']),
			'url'			=> prep_url($post_data['url']),
			'location'		=> isset($post_data['location']) ? $post_data['location'] : '' ,

			// overridden below if used as optional fields
			'language'		=> (ee()->config->item('deft_lang')) ?
									ee()->config->item('deft_lang') : 'english',
			'date_format'	=> ee()->config->item('date_format') ?
					 				ee()->config->item('date_format') : '%n/%j/%y',
			'time_format'	=> ee()->config->item('time_format') ?
									ee()->config->item('time_format') : '12',
			'include_seconds' => ee()->config->item('include_seconds') ?
									ee()->config->item('include_seconds') : 'n',
			'timezone'		=> ee()->config->item('default_site_timezone')
		);

		/** ---------------------------------------
		/**  Set member group
		/** ---------------------------------------*/
		if(!isset($post_data['group_id']))
		{
			if (ee()->config->item('default_member_group') == '')
			{
				$data['group_id'] = 4;  // Pending
			}
			else
			{
				$data['group_id'] = ee()->config->item('default_member_group');
			}
		}
		else
		{
			$data['group_id'] = (int)$post_data['group_id'];
		}

		/** ---------------------------------------
		/**  Optional Fields
		/** ---------------------------------------*/
		$optional = array(
			'bio'				=> 'bio',
			'language'			=> 'deft_lang',
			'timezone'			=> 'server_timezone',
			'date_format'		=> 'date_format',
			'time_format'		=> 'time_format',
			'include_seconds'	=> 'include_seconds',
			'bday_y'			=> 'bday_y',
			'bday_m'   			=> 'bday_m',
			'bday_d'   			=> 'bday_d',
			'occupation'   		=> 'occupation',
			'interests'  	 	=> 'interests',
			'aol_im'   			=> 'aol_im',
			'icq'   			=> 'icq',
			'yahoo_im'   		=> 'yahoo_im',
			'msn_im'   			=> 'msn_im',
		);

		foreach($optional as $key => $value)
		{
			if (isset($post_data[$value]))
			{
				$data[$key] = $post_data[$value];
			}
		}

		/** ---------------------------------------
		/**  Insert basic member data
		/** ---------------------------------------*/
		ee()->db->query(ee()->db->insert_string('exp_members', $data));

		$member_id = ee()->db->insert_id();

		/** ---------------------------------------
		/**  Insert custom fields
		/** ---------------------------------------*/
		$cust_fields['member_id'] = $member_id;

		ee()->db->query(ee()->db->insert_string('exp_member_data', $cust_fields));


		// Create a record in the member homepage table
		// This is only necessary if the user gains CP access,
		// but we'll add the record anyway.

		ee()->db->query(ee()->db->insert_string('exp_member_homepage',
			array('member_id' => $member_id))
		);

		// Update
		ee()->stats->update_member_stats();

		/* -------------------------------------------
		/* 'entry_api_create_member_end' hook.
		/*  - Added: 3.5
		*/
		Entry_api_helper::add_hook('create_member_end', $member_id);
		/** ---------------------------------------*/

		/** ---------------------------------------
		/**  Return the result
		/** ---------------------------------------*/
		$this->service_error['succes_create']['id'] = $member_id;
		return $this->service_error['succes_create'];
	}

	//-------------------------------------------------------------------------

	/**
     * read_member
    */
	public function read_member($post_data = array())
	{
		/** ---------------------------------------
		/**  can we update member profiles, do we have the right for it
		/** ---------------------------------------*/
		if(ee()->session->userdata('can_view_profiles') != 'y')
		{
			return $this->service_error['error_member_no_right'];
		}
		
		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  member_id is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['member_id']) || $post_data['member_id'] == '') {
			$data_errors[] = 'member_id';
		}		

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			$this->service_error['error_field']['message'] .= ' '.implode(', ',$data_errors);
			return $this->service_error['error_field'];
		}
				
		/** ---------------------------------------
		/** Get the member
		/** ---------------------------------------*/
		$member_data = ee()->channel_data->get_member($post_data['member_id']);
		
		/** ---------------------------------------
		/** Any result
		/** ---------------------------------------*/
		if($member_data->num_rows == 0)
		{
			return $this->service_error['error_no_member'];
		}
		
		//set the data
		$member_data = $member_data->row_array();
		
		//filter data
		$member_data = $this->filter_memberdata($member_data);

		/* -------------------------------------------
		/* 'entry_api_read_member_end' hook.
		/*  - Added: 3.0
		*/
		if (ee()->extensions->active_hook('entry_api_read_member_end') === TRUE)
		{
			ee()->extensions->call('entry_api_read_member_end', $member_data);
		}
		// -------------------------------------------

		/* -------------------------------------------
		/* 'entry_api_read_member_end' hook.
		/*  - Added: 3.5
		*/
		Entry_api_helper::add_hook('read_member_end', $member_data);
		/** ---------------------------------------*/

		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'][0] = $member_data;
		$this->service_error['succes_read']['id'] = $member_data['member_id'];
		return $this->service_error['succes_read'];	
	}

	//-------------------------------------------------------------------------

	/**
     * update_member
    */
	public function update_member($post_data = array())
	{
		/** ---------------------------------------
		/**  Member_id is for a insert always required
		/** ---------------------------------------*/
		$data_errors = array();
		if(!isset($post_data['member_id']) || $post_data['member_id'] == '') {
			$data_errors[] = 'member_id';
		}	

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			$this->service_error['error_field']['message'] .= ' '.implode(', ',$data_errors);
			return $this->service_error['error_field'];
		}

		/** ---------------------------------------
		/**  Check if the member exists
		/** ---------------------------------------*/
		if(!$this->member_exists($post_data['member_id']))
		{
			//generate error
			//$this->service_error['error_field']['message'] .= ' '.implode(', ',$data_errors);
			return $this->service_error['error_no_member'];
		}

		ee()->load->model('member_model');

		// Are any required custom fields empty?
		ee()->db->select('m_field_id, m_field_label');
		ee()->db->where('m_field_required = "y"');
		$query = ee()->db->get('member_fields');

		 $errors = array();

		 if ($query->num_rows() > 0)
		 {
			foreach ($query->result_array() as $row)
			{
				if (isset($post_data['m_field_id_'.$row['m_field_id']]) AND $post_data['m_field_id_'.$row['m_field_id']] == '')
				{
					$errors[] = ee()->lang->line('mbr_custom_field_empty').'&nbsp;'.$row['m_field_label'];
				}
			}
		 }

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($errors) || count($errors) > 0)
		{
			//generate error
			$this->service_error['error_field_single']['message'] .= ' '.$errors[0];
			return $this->service_error['error_field_single'];
		}

		/** -------------------------------------
		/**  Build query
		/** -------------------------------------*/

		if (isset($post_data['url']) AND $post_data['url'] == 'http://')
		{
			$post_data['url'] = '';
		}

		$fields = array(
			'bday_y',
			'bday_m',
			'bday_d',
			'url',
			'location',
			'occupation',
			'interests',
			'aol_im',
			'icq',
			'yahoo_im',
			'msn_im',
			'bio'
		);

		$data = array();

		//get the memberdata
		$member_data = $this->get_member($post_data['member_id']);

		foreach ($fields as $val)
		{
			$data[$val] = (isset($post_data[$val])) ? ee()->security->xss_clean($post_data[$val]) : $member_data[$val];
			unset($post_data[$val]);
		}

		ee()->load->helper('url');
		$data['url'] = preg_replace('/[\'"]/is', '', $data['url']);
		$data['url'] = prep_url($data['url']);

		if (is_numeric($data['bday_d']) AND is_numeric($data['bday_m']))
		{
			ee()->load->helper('date');
			$year = ($data['bday_y'] != '') ? $data['bday_y'] : date('Y');
			$mdays = days_in_month($data['bday_m'], $year);

			if ($data['bday_d'] > $mdays)
			{
				$data['bday_d'] = $mdays;
			}
		}

		if (count($data) > 0)
		{
			ee()->member_model->update_member($post_data['member_id'], $data);
		}

		/** -------------------------------------
		/**  Update the custom fields
		/** -------------------------------------*/

		$m_data = array();

		if (count($post_data) > 0)
		{
			foreach ($post_data as $key => $val)
			{
				if (strncmp($key, 'm_field_id_', 11) == 0)
				{
					$m_data[$key] = ee()->security->xss_clean($val);
				}
			}

			if (count($m_data) > 0)
			{
				ee()->member_model->update_member_data($post_data['member_id'], $m_data);
			}
		}

		/** -------------------------------------
		/**  Update comments
		/** -------------------------------------*/

		if ($data['location'] != "" OR $data['url'] != "")
		{
			if (ee()->db->table_exists('comments'))
			{
				$d = array(
					'location'	=> $data['location'],
					'url'		=> $data['url']
				);

				ee()->db->where('author_id', $post_data['member_id']);
				ee()->db->update('comments', $d);
			}
	  	}

	  	/* -------------------------------------------
		/* 'entry_api_update_member_end' hook.
		/*  - Added: 3.5
		*/
		Entry_api_helper::add_hook('update_member_end', $post_data);
		/** ---------------------------------------*/

		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		//$this->service_error['succes_read']['data'][0] = $member_data;
		$this->service_error['succes_update']['id'] = $post_data['member_id'];
		return $this->service_error['succes_update'];	
		
	}

	//-------------------------------------------------------------------------

	/**
     * delete_member
    */
	public function delete_member($post_data = array())
	{

		/** ---------------------------------------
		/**  can we add a new channel, do we have the right for it
		/** ---------------------------------------*/
		if(ee()->session->userdata('can_admin_members') != 'y')
		{
			return $this->service_error['error_member_no_right'];
		}

		/** ---------------------------------------
		/**  can we add a new channel, do we have the right for it
		/** ---------------------------------------*/
		if(ee()->session->userdata('can_delete_members') != 'y')
		{
			return $this->service_error['error_member_no_right'];
		}

		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		$data_errors = array();
		if(!isset($post_data['member_id']) || $post_data['member_id'] == '') {
			$data_errors[] = 'member_id';
		}	

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			$this->service_error['error_field']['message'] .= ' '.implode(', ',$data_errors);
			return $this->service_error['error_field'];
		}

		/** ---------------------------------------
		/**  can we add a new channel, do we have the right for it
		/** ---------------------------------------*/
		if(ee()->session->userdata('member_id') == $post_data['member_id'])
		{
			return $this->service_error['error_not_delete_self'];
		}

		/** ---------------------------------------
		/**  check the member
		/** ---------------------------------------*/
		$member_data = $this->get_member($post_data['member_id']);
		if(empty($member_data))
		{
			return $this->service_error['error_no_member'];
		}

		/** ---------------------------------------
		/**  Never delete a super admin
		/** ---------------------------------------*/
		if($member_data['group_id'] == 1)
		{
			return $this->service_error['error_not_delete_super_admin'];
		}
		
		/** ---------------------------------------
		/**  Now lets delete the member an get the member_id that can take over the entries
		/** ---------------------------------------*/
		ee()->load->model('member_model');
		$heir = isset($post_data['member_id_takeover']) ? $post_data['member_id_takeover'] : NULL;
		ee()->member_model->delete_member($post_data['member_id'], $heir);

		// Update
		ee()->stats->update_member_stats();

		/* -------------------------------------------
		/* 'entry_api_delete_member_end' hook.
		/*  - Added: 3.5
		*/
		Entry_api_helper::add_hook('delete_member_end', $post_data['member_id']);
		/** ---------------------------------------*/

		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_delete']['id'] = $post_data['member_id'];
		return $this->service_error['succes_delete'];	
	}

	//-------------------------------------------------------------------------

	/**
     * change password
    */
	// public function change_username_password($post_data = array())
	// {

	// 	/** ---------------------------------------
	// 	/**  can we administrate members, do we have the right for it
	// 	/** ---------------------------------------*/
	// 	if(ee()->session->userdata('can_admin_members') != 'y')
	// 	{
	// 		return $this->service_error['error_member_no_right'];
	// 	}

	// 	/** ---------------------------------------
	// 	/**  Member_id is for a insert always required
	// 	/** ---------------------------------------*/
	// 	$data_errors = array();
	// 	if(!isset($post_data['member_id']) || $post_data['member_id'] == '') {
	// 		$data_errors[] = 'member_id';
	// 	}	

	// 	/** ---------------------------------------
	// 	/**  Return error when there are fields who are empty en shoulnd`t
	// 	/** ---------------------------------------*/
	// 	if(!empty($data_errors) || count($data_errors) > 0)
	// 	{
	// 		//generate error
	// 		$this->service_error['error_field']['message'] .= ' '.implode(', ',$data_errors);
	// 		return $this->service_error['error_field'];
	// 	}

	// 	/** ---------------------------------------
	// 	/**  check the member
	// 	/** ---------------------------------------*/
	// 	$member_data = $this->get_member($post_data['member_id']);
	// 	if(empty($member_data))
	// 	{
	// 		return $this->service_error['error_no_member'];
	// 	}

		



	// 	if ($this->config->item('allow_username_change') != 'y' &&
	// 		$this->session->userdata('group_id') != 1)
	// 	{
	// 		if ($_POST['current_password'] == '')
	// 		{
	// 			$this->functions->redirect(BASE.AMP.'C=myaccount'.AMP.'M=username_password'.AMP.'id='.$this->id);
	// 		}

	// 		$_POST['username'] = $_POST['current_username'];
	// 	}

	// 	// validate for unallowed blank values
	// 	if (empty($_POST))
	// 	{
	// 		show_error(lang('unauthorized_access'));
	// 	}

	// 	// If the screen name field is empty, we'll assign is from the username field.
	// 	if ($_POST['screen_name'] == '')
	// 	{
	// 		$_POST['screen_name'] = $_POST['username'];
	// 	}

	// 	// Fetch member data
	// 	$query = $this->member_model->get_member_data($this->id, array('username', 'screen_name'));

	// 	$this->VAL = $this->_validate_user(array(
	// 		'username'			=> $this->input->post('username'),
	// 		'cur_username'		=> $query->row('username'),
	// 		'screen_name'		=> $this->input->post('screen_name'),
	// 		'cur_screen_name'	=> $query->row('screen_name'),
	// 		'password'			=> $this->input->post('password'),
	// 		'password_confirm'	=> $this->input->post('password_confirm'),
	// 		'cur_password'		=> $this->input->post('current_password')
	// 	));

	// 	$this->VAL->validate_screen_name();

	// 	if ($this->config->item('allow_username_change') == 'y' OR
	// 		$this->session->userdata('group_id') == 1)
	// 	{
	// 		$this->VAL->validate_username();
	// 	}

	// 	if ($_POST['password'] != '')
	// 	{
	// 		$this->VAL->validate_password();
	// 	}

	// 	// Display errors if there are any
	// 	if (count($this->VAL->errors) > 0)
	// 	{
	// 		show_error($this->VAL->show_errors());
	// 	}

	// 	// Update "last post" forum info if needed
	// 	if ($query->row('screen_name') != $_POST['screen_name'] &&
	// 		$this->config->item('forum_is_installed') == "y")
	// 	{
	// 		$this->db->where('forum_last_post_author_id', $this->id);
	// 		$this->db->update(
	// 			'forums',
	// 			array('forum_last_post_author' => $this->input->post('screen_name'))
	// 		);

	// 		$this->db->where('mod_member_id', $this->id);
	// 		$this->db->update(
	// 			'forum_moderators',
	// 			array('mod_member_name' => $this->input->post('screen_name'))
	// 		);
	// 	}

	// 	// Assign the query data
	// 	$data['screen_name'] = $_POST['screen_name'];

	// 	if ($this->config->item('allow_username_change') == 'y' OR $this->session->userdata('group_id') == 1)
	// 	{
	// 		$data['username'] = $_POST['username'];
	// 	}

	// 	// Was a password submitted?
	// 	$pw_change = FALSE;

	// 	if ($_POST['password'] != '')
	// 	{
	// 		$this->load->library('auth');

	// 		$this->auth->update_password($this->id, $this->input->post('password'));

	// 		if ($this->self_edit)
	// 		{
	// 			$pw_change = TRUE;
	// 		}
	// 	}

	// 	$this->member_model->update_member($this->id, $data);

	// 	$this->cp->get_installed_modules();

	// 	if (isset($this->cp->installed_modules['comment']))
	// 	{
	// 		if ($query->row('screen_name') != $_POST['screen_name'])
	// 		{
	// 			$query = $this->member_model->get_member_data($this->id, array('screen_name'));

	// 			$screen_name = ($query->row('screen_name')	!= '') ? $query->row('screen_name')	 : '';

	// 			// Update comments with current member data
	// 			$data = array('name' => ($screen_name != '') ? $screen_name : $_POST['username']);

	// 			$this->db->where('author_id', $this->id);
	// 			$this->db->update('comments', $data);
	// 		}
	// 	}

	// 	// Write log file
	// 	$this->logger->log_action($this->VAL->log_msg);

	// 	$this->session->set_flashdata('message_success', lang('settings_updated'));
	// 	$this->functions->redirect(BASE.AMP.'C=myaccount'.AMP.'M=username_password'.AMP.'id='.$this->id);

	// 	/** ---------------------------------------
	// 	/**  We got luck, it works
	// 	/** ---------------------------------------*/
	// 	//$this->service_error['succes_read']['data'][0] = $member_data;
	// 	return $this->service_error['succes_delete'];	
	// }

	// ----------------------------------------------------------------
	// PRivate methods
	// ----------------------------------------------------------------


	// --------------------------------------------------------------------

	/**
	 * Check userdata settings        
	 */
	public function member_exists($member_id = 0)
	{
		ee()->db->where('member_id', $member_id);
		$query = ee()->db->get('members');
		
		return $query->num_rows();
	}
	
	// --------------------------------------------------------------------

	/**
	 * Check userdata settings        
	 */
	public function check_member($field, $data, $member_id = '')
	{
		//not looking for this member_id
		if($member_id != '')
		{
			ee()->db->where('member_id !=', $member_id);
		}
		
		ee()->db->where($field, $data);
		$query = ee()->db->get('members');
		
		return $query->num_rows();
	}

	// --------------------------------------------------------------------

	/**
	 * Check userdata settings        
	 */
	public function get_member($member_id = 0)
	{
		ee()->db->where('member_id', $member_id);
		$query = ee()->db->get('members');
		
		return $query->row_array();
	}

	// ----------------------------------------------------------------
	
	/**
	 * Only allow save memberdata          
	 */
	public function filter_memberdata($data, $delete = array())
	{
		$return = array();
		
		foreach($this->default as $val)
		{
			if(isset($data[$val]) && !in_array($val, $delete))
			{
				$return[$val] = $data[$val];
			}
		}
		
		return $return;
	}

	// --------------------------------------------------------------------
	
	/**
	 * default_data function.
	 * 
	 * @access public
	 * @return void
	 */
	private function _default_data()
	{
		$this->default = array(
			'member_id',
			'group_id',
			'username',
			'screen_name',
			'email',
			'url',
			'location',
			'occupation',
			'interests',
			'bday_d',
			'bday_m',
			'bday_y',
			'aol_im',
			'yahoo_im',
			'msn_im',
			'icq',
			'bio',
			'signature',
			'avatar_filename',
			'avatar_width',
			'avatar_height',
			'photo_filename',
			'photo_width',
			'photo_height',
			'sig_img_filename',
			'sig_img_width',
			'sig_img_height',
			'ignore_list',
			'private_messages',
			'accept_messages',
			'last_view_bulletins',
			'last_bulletin_date',
			'ip_address',
			'join_date',
			'last_visit',
			'last_activity',
			'total_entries',
			'total_comments',
			'total_forum_topics',
			'total_forum_posts',
			'last_entry_date',
			'last_comment_date',
			'last_forum_post_date',
			'last_email_date',
			'in_authorlist',
			'accept_admin_email',
			'accept_user_email',
			'notify_by_default',
			'notify_of_pm',
			'display_avatars',
			'display_signatures',
			'parse_smileys',
			'smart_notifications',
			'language',
			'timezone',
			'time_format',
			'cp_theme',
			'profile_theme',
			'forum_theme',
			'tracker',
			'template_size',
			'notepad',
			'notepad_size',
			'quick_links',
			'quick_tabs',
			'show_sidebar',
			'pmember_id',
			'rte_enabled',
			'rte_toolset_id'
		);	
	}

}

