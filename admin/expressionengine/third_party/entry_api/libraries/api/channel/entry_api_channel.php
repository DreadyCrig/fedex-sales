<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel API
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

class Entry_api_channel
{	
	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		// load the stats class because this is not loaded because of the use of the extension
		ee()->load->library('stats'); 
		
		/** ---------------------------------------
		/** load the api`s
		/** ---------------------------------------*/
		ee()->load->library('api');
		ee()->api->instantiate('channel_structure');
		ee()->api->instantiate('channel_fields');	

		//require the default settings
        require PATH_THIRD.'entry_api/settings.php';
	}

	// ----------------------------------------------------------------

	/**
	 * Create a new channel
	 * 
	 * @param  array $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function create_channel($post_data = array())
	{
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
	public function read_channel($post_data = array())
	{
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
		// -------------------------------------------
		
		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'][0] = $read_channel->row_array();
		$this->service_error['succes_read']['id'] = $post_data['channel_id'];
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
	public function update_channel($post_data = array())
	{	
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
	public function delete_channel($post_data = array())
	{

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
		// -------------------------------------------
		
		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_delete']['id'] = $post_data['channel_id'];
		return $this->service_error['succes_delete'];
	}

	// ----------------------------------------------------------------

	/**
	 * search a channel
	 *
	 * @param  array $auth
	 * @param  array  $post_data
	 * @return array
	 */
	public function search_channel($post_data = array())
	{
		/** ---------------------------------------
		/**  Default vars
		/** ---------------------------------------*/
		$limit = isset($post_data['limit']) ? $post_data['limit'] : 25;
		$sort = isset($post_data['sort']) ? $post_data['sort'] : 'DESC';
		$orderby = isset($post_data['orderby']) ? $post_data['orderby'] : 'channel_id';

		$channel_ids = array();

		/** ---------------------------------------
		/**  selecting the data
		/** ---------------------------------------*/
		ee()->db->select('*');
		ee()->db->from('channels');
		ee()->db->limit($limit);
		ee()->db->order_by($orderby, $sort);

		//get the filters
		foreach($post_data as $key=>$val)
		{
			if(ee()->db->field_exists($key, 'channels'))
			{
				ee()->db->like($key, $val);
			}
		}

		$result = ee()->db->get();

		/** ---------------------------------------
		/**  Check if there is an channel_id present
		/** ---------------------------------------*/
		if($result->num_rows == 0)
		{
			return $this->service_error['error_no_channel'];
		}

		/* -------------------------------------------
		/* 'entry_api_read_channel_end' hook.
		/*  - Added: 3.0
		*/
		Entry_api_helper::add_hook('search_channel_end', $result->result_array());
		// -------------------------------------------

		/** ---------------------------------------
		/**  We got luck, it works
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'] = $result->result_array();
		//$this->service_error['succes_read']['id'] = $post_data['channel_id'];
		return $this->service_error['succes_read'];
	}

	// ----------------------------------------------------------------

	/**
	 * search a channel
	 *
	 * @param  array $auth
	 * @param  array  $post_data
	 * @return array
	 */
	private function _search_channel($keyword = '')
	{
		ee()->db->select('channel_id');
		ee()->db->like('channel_title', $keyword);

	}
}

