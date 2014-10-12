<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API - Comment file
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

class Entry_api_comment extends Entry_api_base_api
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
	public $api_type = 'comment';
	
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		parent::__construct();

		//load the url helper
		ee()->load->helper('url');	

		//get the category model
		ee()->load->model('comment_model');			
	}
	
	// ----------------------------------------------------------------

	/**
	 * Create the comment 
	 * 
	 * @param  string $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function create_comment($auth = array(),  $post_data = array())
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
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		$length_errors = array();

		/** ---------------------------------------
		/**  Entry_id is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['entry_id']) || $post_data['entry_id'] == '') {
			$data_errors[] = 'entry_id';
		}
		if(!isset($post_data['comment']) || $post_data['comment'] == '') {
			$data_errors[] = 'comment';
		}

		/** ---------------------------------------
		/**  If author_id is not given, than those fields are required
		/** ---------------------------------------*/
		if(!isset($post_data['author_id'])) {
			if(!isset($post_data['name']) || $post_data['name'] == '') {
				$data_errors[] = 'name';
			}
			if(!isset($post_data['email']) || $post_data['email'] == '') {
				$data_errors[] = 'email';
			}
		}
		else if(isset($post_data['author_id']) && $post_data['author_id'] == '')
		{
			$data_errors[] = 'author_id';
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
		/**  Check if the Entry exists
		/** ---------------------------------------*/
		$entry = ee()->channel_data->get_channel_title($post_data['entry_id']);
		print_r($entry->row());return $this->service_error['error_no_entry'];
		if($entry->num_rows() == 0)
		{
			return $this->service_error['error_no_entry'];
		}

		/** ---------------------------------------
		/**  Check if the Entry accept comments
		/** ---------------------------------------*/
		$entry = $entry->row();
		if($entry->allow_comments != 'y')
		{
			return $this->service_error['error_comment_disabled'];
		}
	
		/** ---------------------------------------
		/**  default Entry data
		/** ---------------------------------------*/
		$category_array = array(
			'site_id'			=> $entry->site_id,
			'channel_id'		=> $entry->channel_id,
			'author_id'			=> isset($post_data['author_id']) ? $post_data['author_id'] : 0,
			'status'			=> $post_data['status'],
			'name' 				=> isset($post_data['author_id']) ? $post_data['author_id'] : 0,
			'email'				=> isset($post_data['author_id']) ? $post_data['author_id'] : 0,
			'url'				=> isset($post_data['author_id']) ? $post_data['author_id'] : 0,
			'location'			=> isset($post_data['author_id']) ? $post_data['author_id'] : 0,
			'ip_address'		=> '',
			'comment_date'		=> '',
			'edit_date'			=> '',
			'comment'			=> '',
		);	

		/** ---------------------------------------
		/**  Fill out the other fields
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					$category_array[$val['field_name']]  = $post_data[$val['field_name']];
				}
			}
		}	

		/** ---------------------------------
		/**  Does this category already exist?
		/** ---------------------------------*/	
		if( ee()->category_model->is_duplicate_category_name($category_array['cat_url_title'], '', $category_array['group_id']))
		{
			//generate error
			return $this->service_error['error_duplicated_category'];
		}

		/** ---------------------------------
		/**  Create the categroy
		/** ---------------------------------*/	
		if($insert_id = $this->_insert_category($category_array))
		{
			/* -------------------------------------------
			/* 'entry_api_create_category_end' hook.
			/*  - Added: 2.2
			*/
			Entry_api_helper::add_hook('create_category_end', $category_array);
			//if (ee()->extensions->active_hook('entry_api_create_category_end') === TRUE)
			//{
			//	ee()->extensions->call('entry_api_create_category_end', $category_array);
			//}
			// -------------------------------------------

			//generate succes
			$this->service_error['succes_create']['id'] = $insert_id;
			return $this->service_error['succes_create'];
		}
		else
		{
			//generate succes
			return $this->service_error['error_create_category'];
		}		
	}
	
	// ----------------------------------------------------------------

	/**
	 * Read a comment 
	 * 
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function read_comment($auth = array(), $post_data = array())
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
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();

		/** ---------------------------------------
		/**  cat_id is requierd
		/** ---------------------------------------*/
		if(!isset($post_data['cat_id']) || $post_data['cat_id'] == '') {
			$data_errors[] = 'cat_id';
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
		/**  Get the  category to get the group_id, also check if the category exists
		/** ---------------------------------------*/
		$category_group_id = $this->_get_category_by_id($post_data['cat_id']);
		if(empty($category_group_id) || $category_group_id == '')
		{
			//generate error
			return $this->service_error['error_no_category'];
		}

		/** ---------------------------------------
		/**  Get the category information and check if this category is assinged to the user
		/** ---------------------------------------*/
		if( ! $this->_parse_category_group($category_group_id['group_id']))
		{
			//generate error
			return $this->service_error['error_category'];
		}

		/* -------------------------------------------
		/* 'entry_api_read_category_end' hook.
		/*  - Added: 2.2
		*/
		Entry_api_helper::add_hook('read_category_end', $this->_get_category_by_id($post_data['cat_id']));
		//if (ee()->extensions->active_hook('entry_api_read_category_end') === TRUE)
		//{
		//	ee()->extensions->call('entry_api_read_category_end', $this->_get_category_by_id($post_data['cat_id']));
		//}
		// -------------------------------------------

		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'][0] = $this->_get_category_by_id($post_data['cat_id']);
		return $this->service_error['succes_read'];
	}
	
	
	// ----------------------------------------------------------------

	/**
	 * Update a comment
	 * 
	 * @param  string $auth
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function update_comment($auth = array(), $post_data = array())
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
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		$length_errors = array();

		/** ---------------------------------------
		/**  Cat_id is required
		/** ---------------------------------------*/
		if(!isset($post_data['cat_id']) || $post_data['cat_id'] == '') {
			$data_errors[] = 'cat_id';
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
		/**  Get the  category to get the group_id, also check if the category exists
		/** ---------------------------------------*/
		$category_group_id = $this->_get_category_by_id($post_data['cat_id']);
		if(empty($category_group_id) || $category_group_id == '')
		{
			//generate error
			return $this->service_error['error_no_category'];
		}

		/** ---------------------------------------
		/**  Get the category information and check if this category is assinged to the user
		/** ---------------------------------------*/
		if( ! $this->_parse_category_group($category_group_id['group_id']))
		{
			//generate error
			return $this->service_error['error_category'];
		}

		/** ---------------------------------------
		/**  Check the other fields witch are required
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				//is required
				if($val['field_required'] == 'y')
				{
					if(!isset($post_data[$val['field_name']]) || $post_data[$val['field_name']] == '') {
						$data_errors[] = $val['field_name'];
					}
				}

				//max length
				if(isset($post_data[$val['field_name']]) && $val['field_type'] == 'text' && strlen($post_data[$val['field_name']]) > $val['field_maxl'])
				{
					$length_errors[] = $val['field_name'];
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
		/**  Return error when the fields are to long
		/** ---------------------------------------*/
		if(!empty($length_errors) || count($length_errors) > 0)
		{
			//generate error
			$this->service_error['error_field_length']['message'] .= ' '.implode(', ',$length_errors);
			return $this->service_error['error_field_length'];
		}

		$category_array = $this->_get_category_by_id($post_data['cat_id']);

		/** ---------------------------------------
		/**  default Entry data
		/** ---------------------------------------*/
		$category_array['site_id'] = isset($post_data['site_id']) ? $post_data['site_id'] : $category_array['site_id'];
		$category_array['group_id'] = isset($post_data['group_id']) ? $post_data['group_id'] : $category_array['group_id'];
		$category_array['parent_id'] = isset($post_data['parent_id']) ? $post_data['parent_id'] : $category_array['parent_id'];
		$category_array['cat_name'] = isset($post_data['cat_name']) ? $post_data['cat_name'] : $category_array['cat_name'];
		$category_array['cat_url_title'] = isset($post_data['cat_url_title']) ? $post_data['cat_url_title'] : $category_array['cat_url_title'];
		$category_array['cat_description'] = isset($post_data['cat_description']) ? $post_data['cat_description'] : $category_array['cat_description'];
		//$category_array['cat_image'] = ''; // @todo
		$category_array['cat_order'] = isset($post_data['cat_order']) ? $post_data['cat_order'] : $category_array['cat_order'];

		/** ---------------------------------------
		/**  Fill out the other fields
		/** ---------------------------------------*/
		if(!empty($this->fields))
		{
			foreach($this->fields as $key=>$val)
			{
				if(isset($post_data[$val['field_name']])) 
				{
					$category_array[$val['field_name']]  = $post_data[$val['field_name']];
				}
			}
		}	

		/** ---------------------------------
		/**  Update the categroy
		/** ---------------------------------*/	
		 if($this->_update_category($category_array))
		 {
		 	/* -------------------------------------------
			/* 'entry_api_update_category_end' hook.
			/*  - Added: 2.2
			*/
			Entry_api_helper::add_hook('update_category_end', $category_array);
			//if (ee()->extensions->active_hook('entry_api_update_category_end') === TRUE)
			//{
			//	ee()->extensions->call('entry_api_update_category_end', $category_array);
			//}
			// -------------------------------------------

		 	//generate succes
			return $this->service_error['succes_update'];
		 }
		 else
		 {
		 	//generate succes
			return $this->service_error['error_create_category'];
		 }	
	}

	// ----------------------------------------------------------------

	/**
	 * Delete a comment
	 * 
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function delete_comment($auth = array(), $post_data = array())
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
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();

		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['cat_id']) || $post_data['cat_id'] == '') {
			$data_errors[] = 'cat_id';
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
		/**  Get the  category to get the group_id, also check if the category exists
		/** ---------------------------------------*/
		$category_group_id = $this->_get_category_by_id($post_data['cat_id']);
		if(empty($category_group_id) || $category_group_id == '')
		{
			//generate error
			return $this->service_error['error_no_category'];
		}

		/** ---------------------------------------
		/**  Get the category information and check if this category is assinged to the user
		/** ---------------------------------------*/
		if( ! $this->_parse_category_group($category_group_id['group_id']))
		{
			//generate error
			return $this->service_error['error_category'];
		}

		/* -------------------------------------------
		/* 'entry_api_delete_category_end' hook.
		/*  - Added: 2.2
		*/
		Entry_api_helper::add_hook('delete_category_end', $post_data['cat_id']);
		//if (ee()->extensions->active_hook('entry_api_delete_category_end') === TRUE)
		//{
		//	ee()->extensions->call('entry_api_delete_category_end', '');
		//}
		// -------------------------------------------

		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		ee()->category_model->delete_category($post_data['cat_id']);
		return $this->service_error['succes_delete'];
	}
	

	// ----------------------------------------------------------------
	// PRIVATE FUNCTIONS 
	// ----------------------------------------------------------------
	
}
/* End of file entry_api_comment.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/api/entry_api_comment.php */