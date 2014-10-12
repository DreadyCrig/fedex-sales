<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API - Channel
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

class Entry_api_channel extends Entry_api_base_api
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
	public $api_type = 'auth';
	
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
		ee()->api->instantiate('channel_structure');
		ee()->api->instantiate('channel_fields');	
	}
	
	// ----------------------------------------------------------------

	/**
	 * Create a new channel
	 * 
	 * @param  array $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function create_channel($auth = array(), $post_data = array())
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
		if(ee()->session->userdata('can_admin_channels') != 'y')
		{
			return $this->service_error['error_channel_no_right'];
		}
		
		/** ---------------------------------------
		/**  try to create a new channel
		/** ---------------------------------------*/
		$create_channel = ee()->api_channel_structure->create_channel($post_data);
		if ($create_channel === FALSE)
		{
			$this->service_error['error_channel_create']['message'] .= implode(', ', ee()->api_channel_structure->errors);
			return $this->service_error['error_channel_create'];
		}
		
		/* -------------------------------------------
		/* 'entry_api_create_channel_end' hook.
		/*  - Added: 3.0
		*/
		Entry_api_helper::add_hook('create_channel_end', $create_channel);
		//if (ee()->extensions->active_hook('entry_api_create_channel_end') === TRUE)
		//{
		//	ee()->extensions->call('entry_api_create_channel_end', $create_channel);
		//}
		// -------------------------------------------

		
		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_create']['id'] = $create_channel;
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
	public function read_channel($auth = array(), $post_data = array())
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
		if(ee()->session->userdata('can_admin_channels') != 'y')
		{
			return $this->service_error['error_channel_no_right'];
		}
		
		/** ---------------------------------------
		/**  Check if there is an channel_id present
		/** ---------------------------------------*/
		if(!isset($post_data['channel_id']))
		{
			return $this->service_error['error_channel_id'];
		}
		
		/** ---------------------------------------
		/**  try to read a channel
		/** ---------------------------------------*/
		$read_channel = ee()->api_channel_structure->get_channel_info($post_data['channel_id']);
		if ($read_channel === FALSE)
		{
			$this->service_error['error_channel_delete']['message'] .= implode(', ', ee()->api_channel_structure->errors);
			return $this->service_error['error_channel_delete'];
		}
		
		/* -------------------------------------------
		/* 'entry_api_read_channel_end' hook.
		/*  - Added: 3.0
		*/
		Entry_api_helper::add_hook('read_channel_end', $read_channel->row_array());
		//if (ee()->extensions->active_hook('entry_api_read_channel_end') === TRUE)
		//{
		//	ee()->extensions->call('entry_api_read_channel_end', $read_channel->row());
		//}
		// -------------------------------------------
		
		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'][0] = $read_channel->row_array();
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
	public function update_channel($auth = array(), $post_data = array())
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
		if(ee()->session->userdata('can_admin_channels') != 'y')
		{
			return $this->service_error['error_channel_no_right'];
		}
		
		/** ---------------------------------------
		/**  Remove site_id, modify this will result in some weirdness
		/** ---------------------------------------*/
		if(isset($post_data['site_id']))
		{
			unset($post_data['site_id']);
		}
		
		/** ---------------------------------------
		/**  try to update a channel
		/** ---------------------------------------*/
		$update_channel = ee()->api_channel_structure->modify_channel($post_data);
		if ($update_channel === FALSE)
		{
			$this->service_error['error_channel_update']['message'] .= implode(', ', ee()->api_channel_structure->errors);
			return $this->service_error['error_channel_update'];
		}
		
		/* -------------------------------------------
		/* 'entry_api_update_channel_end' hook.
		/*  - Added: 3.0
		*/
		Entry_api_helper::add_hook('update_channel_end', $update_channel);
		//if (ee()->extensions->active_hook('entry_api_update_channel_end') === TRUE)
		//{
		//	ee()->extensions->call('entry_api_update_channel_end', $update_channel);
		//}
		// -------------------------------------------
		
		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_update']['id'] = $update_channel;
		return $this->service_error['succes_update'];

	}
	
	// ----------------------------------------------------------------
	
	/**
	 * delete a channel
	 * 
	 * @param  array $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function delete_channel($auth = array(), $post_data = array())
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
		if(ee()->session->userdata('can_admin_channels') != 'y')
		{
			return $this->service_error['error_channel_no_right'];
		}
		
		/** ---------------------------------------
		/**  Check if there is an channel_id present
		/** ---------------------------------------*/
		if(!isset($post_data['channel_id']))
		{
			return $this->service_error['error_channel_id'];
		}
		
		/** ---------------------------------------
		/**  try to delete a channel
		/** ---------------------------------------*/
		$delete_channel = ee()->api_channel_structure->delete_channel($post_data['channel_id']);
		if ($delete_channel === FALSE)
		{
			$this->service_error['error_channel_delete']['message'] .= implode(', ', ee()->api_channel_structure->errors);
			return $this->service_error['error_channel_delete'];
		}
		
		/* -------------------------------------------
		/* 'entry_api_delete_channel_end' hook.
		/*  - Added: 3.0
		*/
		Entry_api_helper::add_hook('delete_channel_end', $post_data['channel_id']);
		//if (ee()->extensions->active_hook('entry_api_delete_channel_end') === TRUE)
		//{
		//	ee()->extensions->call('entry_api_delete_channel_end', $post_data['channel_id']);
		//}
		// -------------------------------------------
		
		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		return $this->service_error['succes_delete'];
		
	}

}

/* End of file entry_api_entry.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/api/entry_api_entry.php */