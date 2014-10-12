<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API - Members
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

include_once PATH_THIRD .'entry_api/libraries/api/entry_api_base_api.php';

class Entry_api_member extends Entry_api_base_api
{	
	/*
	*	the custom fields
	*/
	public $fields;

	/*
	*	Type of service
	*/
	public $type;

	/*
	*	Type of service
	*/
	public $api_type = 'members';
	
	/**
	 * Constructor
	 */
	public function __construct($params = array())
	{	
		//construct the parent
		parent::__construct();
		
		// load the stats class because this is not loaded because of the use of the extension
		ee()->load->library('stats'); 
		
		/** ---------------------------------------
		/** load the models
		/** ---------------------------------------*/
		ee()->load->model('member_model');
		
		//set the default data
		$this->default_data();
	}
	
	// ----------------------------------------------------------------

	/**
	 * Create a new member
	 * 
	 * @param  array $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function create_member($auth = array(), $post_data = array())
	{
		/** ---------------------------------------
		/**  Run some default checks
		/** ---------------------------------------*/
		$default_checks = $this->default_checks($auth);
		if( ! $default_checks['succes'])
		{
			return $default_checks['message'];
		}
		
		/** ---------------------------------------
		/**  can we add a new channel, do we have the right for it
		/** ---------------------------------------*/
		if(ee()->session->userdata('can_admin_members') != 'y')
		{
			return $this->service_error['error_member_no_right'];
		}
		
		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['username']) || $post_data['username'] == '') 
		{
			$data_errors[] = 'username';
		}
		if(!isset($post_data['password']) || $post_data['password'] == '') 
		{
			$data_errors[] = 'password';
		}
		if(!isset($post_data['password_confirm']) || $post_data['password_confirm'] == '') 
		{
			$data_errors[] = 'password_confirm';
		}
		if(!isset($post_data['email']) || $post_data['email'] == '') 
		{
			$data_errors[] = 'email';
		}
		if(!isset($post_data['group_id']) || $post_data['group_id'] == '') 
		{
			$data_errors[] = 'group_id';
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
		/**  Check username
		/** ---------------------------------------*/
		if($this->check_member('username', $post_data['username']) > 0)
		{
			return $this->service_error['error_member_username'];
		}
		
		/** ---------------------------------------
		/**  Check email
		/** ---------------------------------------*/
		if($this->check_member('email', $post_data['email']) > 0)
		{
			return $this->service_error['error_member_email'];
		}
		
		/** ---------------------------------------
		/** password mismatch
		/** ---------------------------------------*/
		if($post_data['password'] != $post_data['password_confirm'])
		{
			return $this->service_error['error_member_password_mismatch'];
		}
		
		/** ---------------------------------------
		/** Create the member
		/** ---------------------------------------*/
		$member_id = $this->_register_member($post_data);
		
		/* -------------------------------------------
		/* 'entry_api_create_member_end' hook.
		/*  - Added: 3.0
		*/
		if (ee()->extensions->active_hook('entry_api_create_member_end') === TRUE)
		{
			ee()->extensions->call('entry_api_create_member_end', $member_id);
		}
		// -------------------------------------------

		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_create']['id'] = $member_id;
		return $this->service_error['succes_create'];
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Read a channel
	 * 
	 * @param  array $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function read_member($auth = array(), $post_data = array())
	{
		/** ---------------------------------------
		/**  Run some default checks
		/** ---------------------------------------*/
		$default_checks = $this->default_checks($auth);
		if( ! $default_checks['succes'])
		{
			return $default_checks['message'];
		}
		
		/** ---------------------------------------
		/**  can we add a new channel, do we have the right for it
		/** ---------------------------------------*/
		if(ee()->session->userdata('can_admin_members') != 'y')
		{
			return $this->service_error['error_member_no_right'];
		}
		
		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  Title is for a insert always required
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
			ee()->extensions->call('entry_api_create_read_end', $member_data);
		}
		// -------------------------------------------

		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'][0] = $member_data;
		return $this->service_error['succes_read'];	
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Update a channel
	 * 
	 * @param  array $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function update_member($auth = array(), $post_data = array())
	{
		/** ---------------------------------------
		/**  Run some default checks
		/** ---------------------------------------*/
		$default_checks = $this->default_checks($auth);
		if( ! $default_checks['succes'])
		{
			return $default_checks['message'];
		}
		
		/** ---------------------------------------
		/**  can we add a new channel, do we have the right for it
		/** ---------------------------------------*/
		if(ee()->session->userdata('can_admin_members') != 'y')
		{
			return $this->service_error['error_member_no_right'];
		}
		
		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['member_id']) || $post_data['member_id'] == '') 
		{
			$data_errors[] = 'member_id';
		}
		if((isset($post_data['password']) && $post_data['password'] != '') || (isset($post_data['password_confirm']) && $post_data['password_confirm'] != '')) 
		{
			if(!isset($post_data['password']) || $post_data['password'] == '') 
			{
				$data_errors[] = 'password';
			}
			
			if(!isset($post_data['password_confirm']) || $post_data['password_confirm'] == '') 
			{
				$data_errors[] = 'password_confirm';
			}
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
		/**  member NOT exists, show error
		/** ---------------------------------------*/
		if($this->check_member('member_id', $post_data['member_id']) == 0)
		{
			return $this->service_error['error_no_member'];
		}
		
		/** ---------------------------------------
		/**  Check username
		/** ---------------------------------------*/
		if(isset($post_data['username']) && $post_data['username'] != '') 
		{
			if($this->check_member('username', $post_data['username'], $post_data['member_id']) > 0)
			{
				return $this->service_error['error_member_username'];
			}
		}
		
		/** ---------------------------------------
		/**  Check email
		/** ---------------------------------------*/
		if(isset($post_data['email']) && $post_data['email'] != '') 
		{
			if($this->check_member('email', $post_data['email'], $post_data['member_id']) > 0)
			{
				return $this->service_error['error_member_email'];
			}
		}
		
		/** ---------------------------------------
		/** password mismatch
		/** ---------------------------------------*/
		if((isset($post_data['password']) && $post_data['password'] != '') || (isset($post_data['password_confirm']) && $post_data['password_confirm'] != '')) 
		{
			if($post_data['password'] != $post_data['password_confirm'])
			{
				return $this->service_error['error_member_password_mismatch'];
			}
		}
		
	
		//set een default array and merge this with the postdata
		//we dont want to include unnessery stuff
		$post_data = $this->filter_memberdata($post_data);
		
		/** ---------------------------------------
		/** Update the member
		/** ---------------------------------------*/
		$check = $this->_update_member($post_data['member_id'], $post_data);
		
		/* -------------------------------------------
		/* 'entry_api_create_member_end' hook.
		/*  - Added: 3.0
		*/
		if (ee()->extensions->active_hook('entry_api_create_member_end') === TRUE)
		{
			ee()->extensions->call('entry_api_create_member_end', $member_id);
		}
		// -------------------------------------------

		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_create']['id'] = $member_id;
		return $this->service_error['succes_create'];

	}
	
	// ----------------------------------------------------------------
	
	/**
	 * delete a channel
	 * 
	 * @param  array $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function delete_member($auth = array(), $post_data = array())
	{
		
		
	}
	
	// ----------------------------------------------------------------
	
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
	 *	Rip from the controllers/cp/members.php 
	 *
	 * Register Member 
	 *
	 * Create a member profile
	 *
	 * @return	mixed
	 */
	public function _register_member($member_data)
	{
		ee()->load->helper('security');

		$data = array();

		// -------------------------------------------
		// 'cp_members_member_create_start' hook.
		//  - Take over member creation when done through the CP
		//  - Added 1.4.2
		//
			ee()->extensions->call('cp_members_member_create_start');
			//if (ee()->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// If the screen name field is empty, we'll assign is
		// from the username field.

		$data['screen_name'] = ($member_data['screen_name']) ? $member_data['screen_name'] : $member_data['username'];

		// Get the password information from Auth
		ee()->load->library('auth');
		$hashed_password = ee()->auth->hash_password($member_data['password']);

		// Assign the query data
		$data['username'] 	= $member_data['username'];
		$data['password']	= $hashed_password['password'];
		$data['salt']		= $hashed_password['salt'];
		$data['unique_id']	= random_string('encrypt');
		$data['crypt_key']	= ee()->functions->random('encrypt', 16);
		$data['email']		= $member_data['email'];
		$data['ip_address']	= ee()->input->ip_address();
		$data['join_date']	= ee()->localize->now;
		$data['language'] 	= ee()->config->item('deft_lang');
		$data['timezone'] 	= ee()->config->item('default_site_timezone');
		$data['time_format'] = ee()->config->item('time_format') ? ee()->config->item('time_format') : 'us';

		// Was a member group ID submitted?

		$data['group_id'] = ( ! $member_data['group_id']) ? 2 : $member_data['group_id'];

		$base_fields = array('bday_y', 'bday_m', 'bday_d', 'url', 'location',
			'occupation', 'interests', 'aol_im', 'icq', 'yahoo_im', 'msn_im', 'bio');

		foreach ($base_fields as $val)
		{
			$data[$val] = ($member_data[$val] === FALSE) ? '' : $member_data[$val];
		}

		if (is_numeric($data['bday_d']) && is_numeric($data['bday_m']))
		{
			ee()->load->helper('date');
			$year = ($data['bday_y'] != '') ? $data['bday_y'] : date('Y');
			$mdays = days_in_month($data['bday_m'], $year);

			if ($data['bday_d'] > $mdays)
			{
				$data['bday_d'] = $mdays;
			}
		}

		// Clear out invalid values for strict mode
		foreach (array('bday_y', 'bday_m', 'bday_d') as $val)
		{
			if ($data[$val] == '')
			{
				unset($data[$val]);
			}
		}

		if ($data['url'] == 'http://')
		{
			$data['url'] = '';
		}

		// Extended profile fields
		$cust_fields = FALSE;
		// $query = ee()->member_model->get_all_member_fields(array(array('m_field_cp_reg' => 'y')), FALSE);
// 
		// if ($query->num_rows() > 0)
		// {
			// foreach ($query->result_array() as $row)
			// {
				// if ($member_data['m_field_id_'.$row['m_field_id']] !== FALSE)
				// {
					// $cust_fields['m_field_id_'.$row['m_field_id']] = $member_data['m_field_id_'.$row['m_field_id']];
				// }
			// }
		// }

		$member_id = ee()->member_model->create_member($data, $cust_fields);

		// Write log file

		$message = lang('new_member_added');
		ee()->logger->log_action($message.NBS.NBS.stripslashes($data['username']));

		// -------------------------------------------
		// 'cp_members_member_create' hook.
		//  - Additional processing when a member is created through the CP
		//
			ee()->extensions->call('cp_members_member_create', $member_id, $data);
			// /if (ee()->extensions->end_script === TRUE) return;
		//
		// -------------------------------------------

		// Update Stats
		ee()->stats->update_member_stats();

		return $member_id;
	}
	
	// --------------------------------------------------------------------

	/**
	 * Rip from EE
	 * 
	 * Update member profile
	 */
	function update_profile($post_data)
	{

		$id = $post_data['member_id'];

		unset($post_data['id']);

		$post_data['url'] = ($post_data['url'] == 'http://') ? '' : $post_data['url'];

		//def fields
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

		foreach ($fields as $val)
		{
			if (isset($post_data[$val]))
			{
				$data[$val] = $post_data[$val];
			}

			unset($post_data[$val]);
		}

		if (is_numeric($data['bday_d']) && is_numeric($data['bday_m']))
		{
			$this->load->helper('date');
			$year = ($data['bday_y'] != '') ? $data['bday_y'] : date('Y');
			$mdays = days_in_month($data['bday_m'], $year);

			if ($data['bday_d'] > $mdays)
			{
				$data['bday_d'] = $mdays;
			}
		}

		if (count($data) > 0)
		{
			ee()->member_model->update_member($id, $data);
		}

		if (count($post_data) > 0)
		{
			ee()->member_model->update_member_data($id, $post_data);
		}

		if ($data['location'] != "" OR $data['url'] != "")
		{
			if ($this->db->table_exists('comments'))
			{
				$d = array(
					'location'	=> $data['location'],
					'url'		=> $data['url']
				);

				$this->db->where('author_id', $this->id);
				$this->db->update('comments', $d);
			}
		}
	}
	
	// --------------------------------------------------------------------

	/**
	 * RIP from EE
	 *
	 * Validate either a user, or a Super Admin editing the user
	 * @param  array $validation_data Validation data to be sent to EE_Validate
	 * @return EE_Validate	Validation object returned from EE_Validate
	 */
	private function _validate_user($validation_data)
	{
		//	Validate submitted data
		if ( ! class_exists('EE_Validate'))
		{
			require APPPATH.'libraries/Validate.php';
		}

		$defaults = array(
			'member_id'		=> $this->id,
			'val_type'		=> 'update', // new or update
			'fetch_lang'	=> FALSE,
			'require_cpw'	=> TRUE,
			'enable_log'	=> TRUE,
		);

		$validation_data = array_merge($defaults, $validation_data);

		// Are we dealing with a Super Admin editing someone else's account?
		if ( ! $this->self_edit AND $this->session->userdata('group_id') == 1)
		{
			// Validate Super Admin's password
			$this->load->library('auth');
			$auth = $this->auth->authenticate_id(
				$this->session->userdata('member_id'),
				$this->input->post('current_password')
			);

			if ($auth === FALSE)
			{
				show_error(lang('invalid_password'));
			}

			// Make sure we don't verify the actual member's existing password
			$validation_data['require_cpw'] = FALSE;
		}

		return new EE_Validate($validation_data);
	}
	
	// --------------------------------------------------------------------
	
	/**
	 * default_data function.
	 * 
	 * @access public
	 * @return void
	 */
	function default_data()
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

/* End of file entry_api_entry.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/api/entry_api_entry.php */