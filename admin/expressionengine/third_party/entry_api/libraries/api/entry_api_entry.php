<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API - Entry file
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

class Entry_api_entry extends Entry_api_base_api
{	
	/*
	*	the custom fields
	*/
	public $fields;

	/*
	*	the channel data 
	*/
	public $channel = array();

	/*
	*	Type of service
	*/
	public $type;

	/*
	*	Type of service
	*/
	public $api_type = 'entry';
	
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
		/** load the api`s
		/** ---------------------------------------*/
		ee()->load->library('api');
		ee()->api->instantiate('channel_entries');
		ee()->api->instantiate('channel_fields');	
	}
	
	// ----------------------------------------------------------------

	/**
	 * Create a entry
	 * 
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function create_entry($auth = array(), $post_data = array())
	{
		/** ---------------------------------------
		/**  Run some default checks
		/**  if the site id is given then switch to that site, otherwise use site_id = 1
		/** ---------------------------------------*/
		$site_id = isset($post_data['site_id']) ? $post_data['site_id'] : 1;
		$default_checks = $this->default_checks($auth, 'create_entry', $site_id);
		if( ! $default_checks['succes'])
		{
			return $default_checks['message'];
		}

		/* -------------------------------------------
		/* 'entry_api_create_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Entry_api_helper::add_hook('create_entry_start', $post_data);
		/** ---------------------------------------*/

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['title']) || $post_data['title'] == '') {
			$data_errors[] = 'title';
		}
		if(!isset($post_data['channel_name']) || $post_data['channel_name'] == '') {
			$data_errors[] = 'channel_name';
		}
		if(!isset($post_data['site_id']) || $post_data['site_id'] == '') {
			$data_errors[] = 'site_id';
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
		/**  Parse Out Channel Information and check if the use is auth for the channel
		/** ---------------------------------------*/
		$channel_check = $this->_parse_channel($post_data['channel_name'], false);
		if( ! $channel_check['succes'] )
		{
			return $channel_check['message'];
		}

		/** ---------------------------------------
		/**  Check if the site_id are a match
		/** ---------------------------------------*/
		if($post_data['site_id'] != $this->channel['site_id'])
		{
			return $this->service_error['error_site_id'];
		}

		/** ---------------------------------------
		/**  Check the other fields witch are required
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if($val['field_required'] == 'y')
				{
					if(!isset($post_data[$val['field_name']]) || $post_data[$val['field_name']] == '') {
						$data_errors[] = $val['field_name'];
					}
				}
			}
		}		
		
		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			$this->service_error['error_field_validate']['message'] .= ' '.implode(', ',$data_errors);
			return $this->service_error['error_field_validate'];
		}
				
		/** ---------------------------------------
		/**  validate fields by the fieldtype parser
		/** ---------------------------------------*/
		$validate_errors = array();
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//validate the data
					$validate_field = (bool) ee()->entry_api_fieldtype->validate($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, true, 0);
					
					if($validate_field == false)
					{
						$validate_errors[] = $val['field_name'].' : '.ee()->entry_api_fieldtype->validate_error;
					}
				}
			}
		}

		/** ---------------------------------------
		/**  Return the errors from the validate functions
		/** ---------------------------------------*/
		if(!empty($validate_errors) || count($validate_errors) > 0)
		{
			//generate error
			$this->service_error['error_field_validate']['message'] .= ' '.implode(', ',$validate_errors);
			return $this->service_error['error_field_validate'];
		}


		/** ---------------------------------------
		/**  default Entry data
		/** ---------------------------------------*/
		$entry_date = time();
		$entry_data = array(
			'channel_id'			=> $this->channel['channel_id'],
			'author_id'				=> ee()->session->userdata('member_id'),
			'title'					=> $post_data['title'],
			'ip_address'			=> ee()->input->ip_address(),
			'entry_date'			=> $entry_date,
			'edit_date'				=> gmdate("YmdHis", $entry_date),
			'year'					=> gmdate('Y', $entry_date),
			'month'					=> gmdate('m', $entry_date),
			'day'					=> gmdate('d', $entry_date),
			'status'				=> isset($post_data['status']) ? $post_data['status'] : $this->channel['deft_status'],
			'allow_comments'		=> $this->channel['deft_comments'],
			'ping_servers'			=> array(),
			'versioning_enabled'	=> $this->channel['enable_versioning'],
			'sticky'				=> isset($post_data['sticky']) ? $post_data['sticky'] : 'n',
			'allow_comments'		=> isset($post_data['allow_comments']) ? $post_data['allow_comments'] : $this->channel['deft_comments'],
		);

		//** ---------------------------------------
		/**  Fill out the other fields
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//set the data
					$entry_data['field_ft_'.$val['field_id']]  = $val['field_fmt'];	
					$entry_data['field_id_'.$val['field_id']]  = ee()->entry_api_fieldtype->save($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, true);
				}	
			}
		}	
		
		/** ---------------------------------------
		/**  set the channel setting 
		/** ---------------------------------------*/
		ee()->api_channel_fields->setup_entry_settings($this->channel['channel_id'], $entry_data);
		
		/** ---------------------------------
		/**  add the entry data
		/** ---------------------------------*/		
		if ( ! ee()->api_channel_entries->save_entry($entry_data, $this->channel['channel_id']))
		{
			//return een fout bericht met de errors
			$errors = ee()->api_channel_entries->get_errors();

			//generate error
			$this->service_error['error_api']['message'] = array($errors, 'array');
			return $this->service_error['error_api'];
		}

		/** ---------------------------------------
		/** Okay, now lets add a new category 
		/** ---------------------------------------*/
		if(isset($post_data['category']))
		{
			//$cat_ids = explode('|', $post_data['category']);
			ee()->entry_api_category_model->update_category((array) $post_data['category'], ee()->api_channel_entries->entry_id);
		}

		/** ---------------------------------------
		/**  Post save callback
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//validate the data
					ee()->entry_api_fieldtype->post_save($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, $entry_data, ee()->api_channel_entries->entry_id);
				}
			}
		}

		/* -------------------------------------------
		/* 'entry_api_create_entry_end' hook.
		/*  - Added: 2.2
		*/
		Entry_api_helper::add_hook('create_entry_end', ee()->api_channel_entries->entry_id);
		/** ---------------------------------------*/
	
		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_create']['id'] = ee()->api_channel_entries->entry_id;
		return $this->service_error['succes_create'];
	}

	// ----------------------------------------------------------------

	/**
	 * Read a entry
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function read_entry($auth = array(), $post_data = array())
	{
		/** ---------------------------------------
		/**  Run some default checks
		/**  Give the method name to check if we got free access
		/** if the site id is given then switch to that site, otherwise use site_id = 1
		/** ---------------------------------------*/
		$site_id = isset($post_data['site_id']) ? $post_data['site_id'] : 1;
		$default_checks = $this->default_checks($auth, 'read_entry', $site_id);
		if( ! $default_checks['succes'])
		{
			return $default_checks['message'];
		}

		/* -------------------------------------------
		/* 'entry_api_read_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Entry_api_helper::add_hook('read_entry_start', $post_data);

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  entry_id is always required for a select
		/** ---------------------------------------*/
		if(!isset($post_data['entry_id']) || $post_data['entry_id'] == '') {
			$data_errors[] = 'entry_id';
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
		/**  get the entry data and check if the entry exists
		/** ---------------------------------------*/
		$entry_data = $this->get_entry($post_data['entry_id']);
		
		//check the data
		if ( empty($entry_data))
		{
			//generate error
			return $this->service_error['error_no_entry'];
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information and check if the user is auth for the channel
		/**  Give the method name to check if we got free access
		/** ---------------------------------------*/
		$channel_check = $this->_parse_channel($post_data['entry_id'], true, 'read_entry');
		if( ! $channel_check['succes'])
		{
			return $channel_check['message'];
		}
		
		$post_data['entry_id'] = isset($post_data['entry_id']) ? $post_data['entry_id'] : '';
		//$post_data['url_title'] = isset($post_data['url_title']) ? $post_data['url_title'] : '';
		
		/** ---------------------------------------
		/**  check if the given channel_id match the channel_id of the entry
		/**  By free access is the $this->channel = true to avoid this check
		/** ---------------------------------------*/
		if($this->channel != true && $entry_data['channel_id'] != $this->channel['channel_id'])
		{
			//generate error
			return $this->service_error['error_channel_match'];
		}

		/** ---------------------------------------
		/**  Process the data per field
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($entry_data[$val['field_name']])) 
				{
					$entry_data[$val['field_name']] = ee()->entry_api_fieldtype->pre_process($entry_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, 'read_entry', $post_data['entry_id']);
				}
			}
		}

		/** ---------------------------------------
		/** Get the categories
		/** ---------------------------------------*/
		$entry_data['categories'] = (ee()->entry_api_category_model->get_entry_categories(array($entry_data['entry_id'])));
		
		/** ---------------------------------------
		/** set the data correct
		/** ---------------------------------------*/
		$return_entry_data = $this->_format_read_result($entry_data);
	
		/* -------------------------------------------
		/* 'entry_api_read_entry_end' hook.
		/*  - Added: 2.2
		*/	
		$return_entry_data = Entry_api_helper::add_hook('read_entry_end', $return_entry_data);
		// -------------------------------------------

		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'][0] = $return_entry_data;
		$this->service_error['succes_read']['id'] = $entry_data['entry_id'];
		return $this->service_error['succes_read'];
	}
	
	// ----------------------------------------------------------------

	/**
	 * build a entry data array for a new entry
	 *
	 * @return 	void
	 */
	public function update_entry($auth = array(), $post_data = array())
	{
		/** ---------------------------------------
		/**  Run some default checks
		/**  if the site id is given then switch to that site, otherwise use site_id = 1
		/** ---------------------------------------*/
		$site_id = isset($post_data['site_id']) ? $post_data['site_id'] : 1;
		$default_checks = $this->default_checks($auth, 'update_entry', $site_id);
		if( ! $default_checks['succes'])
		{
			return $default_checks['message'];
		}

		/* -------------------------------------------
		/* 'entry_api_update_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Entry_api_helper::add_hook('update_entry_start', $post_data);

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  entry_id is always required for a select
		/** ---------------------------------------*/
		if(!isset($post_data['entry_id']) || $post_data['entry_id'] == '') {
			$data_errors[] = 'entry_id';
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
		/**  get the entry data and check if the entry exists
		/** ---------------------------------------*/
		$entry_data = $this->get_entry($post_data['entry_id']);
				
		//check the data
		if ( empty($entry_data))
		{
			//generate error
			return $this->service_error['error_no_entry'];
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information and check if the use is auth for the channel
		/** ---------------------------------------*/
		$channel_check = $this->_parse_channel($post_data['entry_id']);
		if( ! $channel_check['succes'])
		{
			return $channel_check['message'];
		}
		
		/** ---------------------------------------
		/**  Check the other fields witch are required
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if($val['field_required'] == 'y')
				{
					if(!isset($post_data[$val['field_name']]) || $post_data[$val['field_name']] == '') {
						$data_errors[] = $val['field_name'];
					}
				}
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
		/**  check if the given channel_id match the channel_id of the entry
		/** ---------------------------------------*/
		if($entry_data['channel_id'] != $this->channel['channel_id'])
		{
			//generate error
			return $this->service_error['error_channel_match'];
		}

		/** ---------------------------------------
		/**  validate fields by the fieldtype parser
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			$validate_errors = array();

			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//validate the data
					$validate_field = (bool) ee()->entry_api_fieldtype->validate($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, false, $post_data['entry_id']);
					
					if($validate_field == false)
					{
						$validate_errors[] = $val['field_name'].' : '.ee()->entry_api_fieldtype->validate_error;
					}
				}
			}
		}

		/** ---------------------------------------
		/**  Return the errors from the validate functions
		/** ---------------------------------------*/
		if(!empty($validate_errors) || count($validate_errors) > 0)
		{
			//generate error
			$this->service_error['error_field_validate']['message'] .= ' '.implode(', ',$validate_errors);
			return $this->service_error['error_field_validate'];
		}
		
		/** ---------------------------------------
		/**  default data
		/** ---------------------------------------*/
		$entry_data['title'] = isset($post_data['title']) ? $post_data['title'] : $entry_data['title'] ;
		$entry_data['status'] = isset($post_data['status']) ? $post_data['status'] : $entry_data['status'] ;
		$entry_data['edit_date'] = time();
		$entry_data['sticky'] = isset($post_data['sticky']) ? $post_data['sticky'] : $entry_data['sticky'] ;
		$entry_data['allow_comments'] = isset($post_data['allow_comments']) ? $post_data['allow_comments'] : $entry_data['allow_comments'] ;
		
		//** ---------------------------------------
		/**  Fill out the other custom fields
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//set the data
					$entry_data['field_ft_'.$val['field_id']]  = $val['field_fmt'];	
					$entry_data['field_id_'.$val['field_id']]  = ee()->entry_api_fieldtype->save($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, false, $entry_data['entry_id']);
				}	
			}
		}	

		/** ---------------------------------------
		/**  set the channel setting 
		/** ---------------------------------------*/
		ee()->api_channel_fields->setup_entry_settings($this->channel['channel_id'], $entry_data);
		
		/** ---------------------------------------
		/**  update entry
		/** ---------------------------------------*/
		$r = ee()->api_channel_entries->save_entry($entry_data, null, $entry_data['entry_id']);
		
		//Any errors?
		if ( ! $r)
		{
			//return een fout bericht met de errors
			$errors = implode(', ', ee()->api_channel_entries->get_errors());

			//generate error
			$this->service_error['error_api']['message'] = $errors;
			return $this->service_error['error_api'];
		}

		/** ---------------------------------------
		/** Okay, now lets add a new category 
		/** ---------------------------------------*/
		if(isset($post_data['category']))
		{
			//$cat_ids = explode('|', $post_data['category']);
			ee()->entry_api_category_model->update_category((array) $post_data['category'], ee()->api_channel_entries->entry_id);
		}

		/** ---------------------------------------
		/**  Post save callback
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{

			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					//validate the data
					ee()->entry_api_fieldtype->post_save($post_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, $entry_data, ee()->api_channel_entries->entry_id);
				}
			}
		}

		/* -------------------------------------------
		/* 'entry_api_update_entry_end' hook.
		/*  - Added: 2.2
		*/
		Entry_api_helper::add_hook('update_entry_end', $entry_data);
		// -------------------------------------------
	
		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_update']['id'] = $entry_data['entry_id'];
		return $this->service_error['succes_update'];
	}
	
	// ----------------------------------------------------------------

	/**
	 * build a entry data array for a new entry
	 *
	 * @return 	void
	 */
	public function delete_entry($auth = array(), $post_data = array())
	{
		/** ---------------------------------------
		/**  Run some default checks
		/**  if the site id is given then switch to that site, otherwise use site_id = 1
		/** ---------------------------------------*/
		$site_id = isset($post_data['site_id']) ? $post_data['site_id'] : 1;
		$default_checks = $this->default_checks($auth, 'delete_entry', $site_id);
		if( ! $default_checks['succes'])
		{
			return $default_checks['message'];
		}

		/* -------------------------------------------
		/* 'entry_api_delete_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Entry_api_helper::add_hook('delete_entry_start', $post_data);

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  entry_id is always required for a select
		/** ---------------------------------------*/
		if(!isset($post_data['entry_id']) || $post_data['entry_id'] == '') {
			$data_errors[] = 'entry_id';
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
		/**  get the entry data and check if the entry exists
		/** ---------------------------------------*/
		$entry_data = $this->get_entry($post_data['entry_id']);
		
		//check the data
		if ( empty($entry_data))
		{
			//generate error
			return $this->service_error['error_no_entry'];
		}

		/** ---------------------------------------
		/**  Parse Out Channel Information and check if the use is auth for the channel
		/** ---------------------------------------*/
		$channel_check = $this->_parse_channel($post_data['entry_id']);
		if( ! $channel_check['succes'])
		{
			return $channel_check['message'];
		}

		$post_data['entry_id'] = isset($post_data['entry_id']) ? $post_data['entry_id'] : '';
		//$post_data['url_title'] = isset($post_data['url_title']) ? $post_data['url_title'] : '';
		
		/** ---------------------------------------
		/**  check if the given channel_id match the channel_id of the entry
		/** ---------------------------------------*/
		if($entry_data['channel_id'] != $this->channel['channel_id'])
		{
			//generate error
			return $this->service_error['error_channel_match'];
		}
		
		/** ---------------------------------------
		/**  Call the fieldtype delete function per field
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($entry_data[$val['field_name']])) 
				{
					ee()->entry_api_fieldtype->delete($entry_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, $entry_data['entry_id']);
				}
			}
		}
		
		/** ---------------------------------------
		/**  delete entry
		/** ---------------------------------------*/
		$r = ee()->api_channel_entries->delete_entry($entry_data['entry_id']);
		
		//Any errors?
		if ( ! $r)
		{
			$errors = implode(', ', ee()->api_channel_entries->get_errors());
			if(!empty($errors)) 
			{
				//generate error
				$this->service_error['error_api']['message'] = $errors;
				return $this->service_error['error_api'];

			} else {
				//generate error
				return $this->service_error['error_delete'];
			}
		}

		/** ---------------------------------------
		/**  Call the fieldtype post_delete function per field
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($entry_data[$val['field_name']])) 
				{
					ee()->entry_api_fieldtype->post_delete($entry_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, $entry_data['entry_id']);
				}
			}
		}

		/* -------------------------------------------
		/* 'entry_api_delete_entry_end' hook.
		/*  - Added: 2.2
		*/
		Entry_api_helper::add_hook('delete_entry_end', $entry_data['entry_id']);
		// -------------------------------------------
	
		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_delete']['id'] = $entry_data['entry_id'];
		return $this->service_error['succes_delete'];
	}

	// ----------------------------------------------------------------

	/**
	 * Search a entry
	 * @param  string $auth
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function search_entry($auth = array(), $post_data = array())
	{
		
		/** ---------------------------------------
		/**  Run some default checks
		/**  if the site id is given then switch to that site, otherwise use site_id = 1
		/** ---------------------------------------*/
		$site_id = isset($post_data['site_id']) ? $post_data['site_id'] : 1;
		$default_checks = $this->default_checks($auth, 'search_entry', $site_id);
		if( ! $default_checks['succes'])
		{
			return $default_checks['message'];
		}

		/* -------------------------------------------
		/* 'entry_api_search_entry_start' hook.
		/*  - Added: 3.2.1
		*/
		$post_data = Entry_api_helper::add_hook('search_entry_start', $post_data);

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		
		/** ---------------------------------------
		/**  Search the entry entry
		/** ---------------------------------------*/
		$search_result = $this->_search_entry($post_data, ee()->session->userdata('username'));

		/** ---------------------------------------
		/**  Get the fields
		/** ---------------------------------------*/
		$this->fields = $this->_get_fieldtypes();


		if(!$search_result)
		{
			/** ---------------------------------------
			/** return response
			/** ---------------------------------------*/
			return $this->service_error['error_no_entry'];
		}
		else
		{
			$return_entry_data = array();

			foreach($search_result as $entry_id)
			{
				
				/** ---------------------------------------
				/**  get the entry data and check if the entry exists
				/** ---------------------------------------*/
				$entry_data = $this->get_entry($entry_id);

				/** ---------------------------------------
				/** Get the categories
				/** ---------------------------------------*/
				$entry_data['categories'] = (ee()->entry_api_category_model->get_entry_categories(array($entry_data['entry_id'])));
		
				/** ---------------------------------------
				/**  Process the data per field
				/** ---------------------------------------*/
				if(!empty($this->fields))
				{
					foreach($this->fields as $key=>$val)
					{
						if(isset($entry_data[$val['field_name']])) 
						{
							$entry_data[$val['field_name']] = ee()->entry_api_fieldtype->pre_process($entry_data[$val['field_name']], $val['field_type'], $val['field_name'], $val, $this->channel, 'search_entry', $entry_id);
						}
					}
				}
				/** ---------------------------------------
				/** set the data correct
				/** ---------------------------------------*/
				$entry_data = $this->_format_read_result($entry_data);

				/* -------------------------------------------
				/* 'entry_api_search_entry_end' hook.
				/*  - Added: 3.2
				*/
				$entry_data = Entry_api_helper::add_hook('search_entry_per_entry', $entry_data);
				// -------------------------------------------
				
				//assign the data to the array
				$return_entry_data[] = $entry_data; 
				
			};

			/* -------------------------------------------
			/* 'entry_api_search_entry_end' hook.
			/*  - Added: 2.2
			*/
			$return_entry_data = Entry_api_helper::add_hook('search_entry_end', $return_entry_data);
			// -------------------------------------------

			/** ---------------------------------------
			/** Lets collect all the entry_ids so we can return
			/** ---------------------------------------*/
			$entry_ids = array();
			foreach($return_entry_data as $row)
			{
				$entry_ids[] = $row['entry_id'];
			}

			/** ---------------------------------------
			/** return response
			/** ---------------------------------------*/
			$this->service_error['succes_read']['entries'] = $return_entry_data;
			$this->service_error['succes_read']['id'] = implode('|', $entry_ids);
			return $this->service_error['succes_read'];
		}		
	}
	

	// ----------------------------------------------------------------
	// PRIVATE FUNCTIONS
	// ----------------------------------------------------------------
	
	/**
	 * Parses out received channel parameters
	 *
	 * @access	public
	 * @param	int
	 * @return	void
	 */
	private function _parse_channel($entry_channel_id = '', $entry_based = true, $method = '')
	{
		//get the channel data
		ee()->db->select('*')->from('channels');
		//select based on entry_id
		if($entry_based)
		{
			ee()->db->where('channel_titles.entry_id', $entry_channel_id);
			ee()->db->join('channel_titles', 'channels.channel_id = channel_titles.channel_id', 'left');
		}
		//based on channelname
		else
		{
			ee()->db->where('channel_name', $entry_channel_id);
		}
		
		$query = ee()->db->get();

		//no result?
		if ($query->num_rows() == 0)
		{	
			return array(
				'succes' => false,
				'message' => $this->service_error['error_no_channel']
			);
		}

		//channel data array
		$this->channel = array();

		//check if the channel id is assigned to the user
		//Only do this if there is no free access
		if($method == '' || ee()->entry_api_lib->has_free_access($method, ee()->session->userdata('username')) == 0)
		{	
			foreach ($query->result_array() as $key=>$row)
			{
				if ( ! array_key_exists($row['channel_id'], ee()->session->userdata('assigned_channels')) && ee()->session->userdata('group_id') != '1')
				{
					//no rights to the channel
					return array(
						'succes' => false,
						'message' => $this->service_error['error_channel']
					);
				}
				
				//assign to var
				$this->channel = $row;
			}
		}
		else if($method != '')
		{
			$this->channel = true;
		}

		//Find Fields
		// ee()->db->select('f.*')
		// 		->from('channel_fields f, channels c')
		// 		->where('c.field_group = f.group_id')
		// 		->where('c.channel_id', $this->channel['channel_id'])
		// 		->order_by('f.field_order');
		// $query = ee()->db->get();
		
		// //save the fields
		// $this->fields = array();
		// if ($query->num_rows() != 0)
		// {	
		// 	foreach($query->result_array() as $row)
		// 	{
		// 		$this->fields[$row['field_id']] = $row;
		// 	}
		// }
		
		if(empty($this->channel))
		{
			//no rights to the channel
			return array(
				'succes' => false,
				'message' => $this->service_error['error_channel']
			);
		}

		$this->fields = $this->_get_fieldtypes();
		
		//everything is ok
		return array('succes'=>true);
	}

	// ----------------------------------------------------------------
	
	/**
	 * Search an entry based on the given values
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	private function _get_fieldtypes()
	{
		$channel_id = isset($this->channel['channel_id']) ? $this->channel['channel_id'] : null ;
		$channel_fields = ee()->channel_data->get_channel_fields($channel_id)->result_array();
		$fields = ee()->channel_data->utility->reindex($channel_fields, 'field_name');	
		return $fields;
	}

	// ----------------------------------------------------------------
	
	/**
	 * Search an entry based on the given values
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	private function _search_entry($values, $username = '')
	{
		$field_sql = '';
		$sql_conditions = '';
		$operator 	=  'AND';
		$operators = array();
		$i = 0;
		$offset = 0;
		$limit = 10;

		//get the limit and offset from the values
		if(isset($values['limit']))
		{
			$limit = $values['limit'];
			unset($values['limit']);
		}
		if(isset($values['offset']))
		{
			$offset = $values['offset'];
			unset($values['offset']);
		}
		
		// words to ignore in search terms
		include(APPPATH.'config/stopwords'.EXT);	
		$this->_ignore = $ignore;

		$this->_fetch_custom_channel_fields();

		foreach($values as $field_name=>$terms)
		{	
			//get the operator
           	if(preg_match('/\[OR\]/', $terms, $match))
           	{
           		$operator = 'OR';
           		$operators[] = 'OR';
           		$terms = trim(str_replace('[OR]', '', $terms));
           	} 
           	else
           	{
           		$operator = 'AND';
           		$operators[] = 'AND';
           		$terms = trim(str_replace('[AND]', '', $terms));
           	}

	 		// search channel custom fields
			if (isset($this->fields_name[$field_name]))
			{
				$field_sql = 'wd.field_id_'.$this->fields_name[$field_name];
			}

			// search channel titles
			else if ($field_name =="title") 
			{
				$field_sql = 'wt.title';
			}

			// search channel titles
			else if ($field_name =="url_title") 
			{
				$field_sql = 'wt.url_title';
			}

			// search channel names
			else if ($field_name =="channel") 
			{
				$field_sql = 'wl.channel_name';
			}

			// can't search this field because it doesn't exist
			else
			{
				$field_sql = '';
			}

			if ($field_sql !== '' && $terms !== '' )
			{
				
				if (strncmp($terms, '=', 1) ==  0)
				{
					/** ---------------------------------------
					/**  Exact Match e.g.: search:body="=pickle"
					/** ---------------------------------------*/
					
					$terms = substr($terms, 1);
					
					// special handling for IS_EMPTY
					if (strpos($terms, 'IS_EMPTY') !== FALSE)
					{
						$terms = str_replace('IS_EMPTY', '', $terms);
						$terms = $this->_sanitize_search_terms($terms, TRUE);
						
						$add_search = ee()->functions->sql_andor_string($terms, $field_sql);
						
						// remove the first AND output by ee()->functions->sql_andor_string() so we can parenthesize this clause
						$add_search = substr($add_search, 3);
            	
						$conj = ($add_search != '' && strncmp($terms, 'not ', 4) != 0) ? 'OR' : 'AND';
            	
						if (strncmp($terms, 'not ', 4) == 0)
						{
							$sql_conditions .= $operator.' ('.$add_search.' '.$conj.' '.$field_sql.' != "") ';
						}
						else
						{
							$sql_conditions .= $operator.' ('.$add_search.' '.$conj.' '.$field_sql.' = "") ';
						}
					}
					else
					{
						$condition = ee()->functions->sql_andor_string($terms, $field_sql).' ';	
						// replace leading AND/OR with desired operator
						$condition =  preg_replace('/^AND|OR/', $operator, $condition,1);
						$sql_conditions.=$condition;					
					}
				}
				else
				{
					/** ---------------------------------------
					/**  "Contains" e.g.: search:body="pickle"
					/** ---------------------------------------*/
					
					if (strncmp($terms, 'not ', 4) == 0)
					{
						$terms = substr($terms, 4);
						$like = 'NOT LIKE';
					}
					else
					{
						$like = 'LIKE';
					}
					
					if (strpos($terms, '&&') !== FALSE)
					{
						$terms = explode('&&', $terms);
						$andor = (strncmp($like, 'NOT', 3) == 0) ? 'OR' : 'AND';
					}
					else
					{
						$terms = explode('|', $terms);
						$andor = (strncmp($like, 'NOT', 3) == 0) ? 'AND' : 'OR';
					}

					$sql_conditions .= ''.(isset($operators[$i-1]) ? $operators[$i-1] : '' ).' (';
					
					foreach ($terms as $term)
					{
						if ($term == 'IS_EMPTY')
						{
							$sql_conditions .= ' '.$field_sql.' '.$like.' "" '.$andor;
						}
						elseif (strpos($term, '\W') !== FALSE) // full word only, no partial matches
						{
							$not = ($like == 'LIKE') ? ' ' : ' NOT ';
							$term = $this->_sanitize_search_terms($term, TRUE);
							$term = '([[:<:]]|^)'.addslashes(preg_quote(str_replace('\W', '', $term))).'([[:>:]]|$)';
							$sql_conditions .= ' '.$field_sql.$not.'REGEXP "'.ee()->db->escape_str($term).'" '.$andor;
						}
						else
						{
							$term = $this->_sanitize_search_terms($term);
							$sql_conditions .= ' '.$field_sql.' '.$like.' "%'.ee()->db->escape_like_str($term).'%" '.$andor;								
						}
					}
					$sql_conditions = substr($sql_conditions, 0, -strlen($andor)).') ';
				}
			} 
			$i++;
		}

		// check that we actually have some conditions to match
		if ($sql_conditions == '')
		{
			// no valid fields to search	
			$this->return_data = ee()->TMPL->no_results();
			return; // end the process here
		}

		// let's build the query
		$sql = "SELECT distinct(wt.entry_id)
		FROM ".ee()->db->dbprefix."channel_titles AS wt
		LEFT JOIN ".ee()->db->dbprefix."channel_data AS wd
			ON wt.entry_id = wd.entry_id
		LEFT JOIN ".ee()->db->dbprefix."channels AS wl
			ON wt.channel_id = wl.channel_id
		";

		//the channels where the user may search
		//do not check when te user has free access
		$channel_id_query = '';
		if(ee()->entry_api_lib->has_free_access('search_entry', $username) == 0)
		{	
			$channel_ids = implode(',',array_keys(ee()->session->userdata('assigned_channels')));
			$channel_id_query = "wl.channel_id IN(".$channel_ids.") AND";
		}

		// limit search to current site and channels
		$sql .= "WHERE (".$channel_id_query." wt.site_id = ".ee()->config->item('site_id')." )"."\n";
		
		// add search conditions
		$sql = $sql.'AND ('.$sql_conditions.')';

		//add limits
		$sql = $sql.'LIMIT '.$offset.', '.$limit;

		//short fix for http://devot-ee.com/add-ons/support/entry-api/viewthread/10684
		//@todo, need to be a better fix
		$sql = str_replace(' (AND ', ' ( ', $sql);

		$results = ee()->db->query($sql);
		
		//return print_r($results, true);
		// run the query
		if (!$results || $results->num_rows() == 0)
        {
			// no results
            return false;
        }  
   		else
		{
        	// loop through found entries     
	  		$found_ids = array();
	        foreach($results->result_array() as $row)
	        { 
				$found_ids[] = $row['entry_id'];
			}

			return $found_ids;
		}
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Get entry based on entry_id
	 *
	 * @access	public
	 * @param	parameter list
	 * @return	void
	 */
	function get_entry($entry_id = 0)
	{	
		//get the entry
		$entry_data_query = ee()->channel_data->get_entry($entry_id, array('select' => array('*')));

		if(!$entry_data_query || $entry_data_query->num_rows() == 0)
		{
			return array();
		}

		return $entry_data_query->row_array();	
	}

	/** 
	* Fetches custom channel fields from page flash cache. 
	* If not cached, runs query and caches result.
	* @access private
	* @return boolean
	*/
    private function _fetch_custom_channel_fields()
    {

		ee()->db->select('field_id, field_type, field_name, site_id');
		ee()->db->from('channel_fields');
		ee()->db->where('field_type !=', 'date');
		ee()->db->where('field_type !=', 'rel'); 
		            
		$query = ee()->db->get();

		if ($query->num_rows() > 0)
		{
			foreach ($query->result_array() as $row)
			{
				// assign standard custom fields
				$this->fields_name[$row['field_name']] = $row['field_id'];
				$this->fields[] = $row;
			}
			//ee()->session->cache['channel']['custom_channel_fields'] = $this->fields;
			return true;
		}
		else
		{
			return false;
		}       
    }

    // ----------------------------------------------------------------

    /** 
	 * Sanitize earch terms
	 * 
	 * @access private
	 * @param string $keywords
	 * @param boolean $exact_keyword
	 * @return boolean
	 */
	private function _sanitize_search_terms($keywords, $exact_keyword = false)
	{
		$this->min_length = 3;

		/** ----------------------------------------
		/**  Strip extraneous junk from keywords
		/** ----------------------------------------*/
		if ($keywords != "")		
		{
			// Load the search helper so we can filter the keywords
			ee()->load->helper('search');

			$keywords = sanitize_search_terms($keywords);
			
			/** ----------------------------------------
			/**  Is the search term long enough?
			/** ----------------------------------------*/
	
			if (strlen($keywords) < $this->min_length)
			{
				$text = ee()->lang->line('search_min_length');
				
				$text = str_replace("%x", $this->min_length, $text);
							
				return ee()->output->show_user_error('general', array($text));
			}

			// Load the text helper
			ee()->load->helper('text');

			$keywords = (ee()->config->item('auto_convert_high_ascii') == 'y') ? ascii_to_entities($keywords) : $keywords;
			
			
			/** ----------------------------------------
			/**  Remove "ignored" words
			/** ----------------------------------------*/
		
			if (!$exact_keyword)
			{		
				$parts = explode('"', $keywords);
				
				$keywords = '';
				
				foreach($parts as $num => $part)
				{
					// The odd breaks contain quoted strings.
					if ($num % 2 == 0)
					{
						foreach ($this->_ignore as $badword)
						{    
							$part = preg_replace("/\b".preg_quote($badword, '/')."\b/i","", $part);
						}
					}
					$keywords .= ($num != 0) ? '"'.$part : $part;
				}
		
				if (trim($keywords) == '')
				{
					return ee()->output->show_user_error('general', array(ee()->lang->line('search_no_stopwords')));
				}
			}
		}
		
		// finally, double spaces
		$keywords = str_replace("  ", " ", $keywords);
			
		return $keywords;
	}
	
	// ----------------------------------------------------------------
	
	private function _load_channel_settings($channel_id, $type = '')
	{
		//ee()->entry_api_lib->get_member_based_on_username($this->userdata->member('username'));
		return '';

		//get the channels
		ee()->db->select('entry_api_channel_settings.type, channels.deft_status, channels.deft_comments, entry_api_channel_settings.entry_status, entry_api_channel_settings.active, entry_api_channel_settings.data, channels.channel_id, channels.channel_name, channels.channel_title');
		ee()->db->from('channels');
		ee()->db->join('entry_api_channel_settings', 'channels.channel_id = entry_api_channel_settings.channel_id', 'left');
		
		//build where query
		$where = array();
		$where['channels.channel_id'] = $channel_id;
		
		//the type
		if(!empty($type))
		{
			$where['entry_api_channel_settings.type'] = $type;
		}
		
		ee()->db->where($where);
		$query = ee()->db->get();

		$channel = array();
						
		//format a array
		if ($query->num_rows() > 0)
		{	
			$channel = $query->row();
			$channel->entry_status = $channel->entry_status != '' ? $channel->entry_status : $channel->deft_status ;
			return $channel;
		}
		return '';
	}	

	// ----------------------------------------------------------------

	//format an result for a get 
	private function _format_read_result($result)
	{
		if(!empty($result))
		{
			foreach($result as $key=>$val)
			{
				if(substr($key, 0, 9) == 'field_ft_' || substr($key, 0, 9) == 'field_id_')
				{
					unset($result[$key]);
				}
			}
		}
		return $result;
	}
}

/* End of file entry_api_entry.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/api/entry_api_entry.php */