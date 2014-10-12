<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * API - Category file
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

class Entry_api_category extends Entry_api_base_api
{	
	/*
	*	EE instance
	*/
	private $EE;
	
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
	public $api_type = 'category';
	
	/**
	 * Constructor
	 */
	public function __construct()
	{	
		parent::__construct();

		//get the instance
		//$this->EE =& get_instance();

		//load the url helper
		ee()->load->helper('url');	

		//get the category model
		ee()->load->model('category_model');			
	}
	
	// ----------------------------------------------------------------

	/**
	 * Create the category 
	 * 
	 * @param  string $auth  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function create_category($auth = array(),  $post_data = array())
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
		/**  Get the category information and check if this category is assinged to the user
		/** ---------------------------------------*/
		if( ! $this->_parse_category_group($post_data['group_id']))
		{
			//generate error
			return $this->service_error['error_category'];
		}

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		$length_errors = array();

		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['cat_name']) || $post_data['cat_name'] == '') {
			$data_errors[] = 'cat_name';
		}
		if(!isset($post_data['group_id']) || $post_data['group_id'] == '') {
			$data_errors[] = 'group_id';
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
	
		/** ---------------------------------------
		/**  default Entry data
		/** ---------------------------------------*/
		$category_array = array(
			'site_id'			=> $this->category_group->site_id,
			'group_id'			=> $post_data['group_id'],
			'parent_id'			=> isset($post_data['parent_id']) ? $post_data['parent_id'] : '',
			'cat_name'			=> $post_data['cat_name'],
			'cat_url_title' 	=> url_title( $post_data['cat_name'], ee()->config->item('word_separator'), TRUE ),
			'cat_description'	=> isset($post_data['cat_description']) ? $post_data['cat_description'] : '',
			'cat_image'			=> '',
			'cat_order'			=> $this->_get_latest_order_id_from_group($post_data['group_id']),
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
	 * Read a category 
	 * 
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function read_category($auth = array(), $post_data = array())
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
	 * Update a category
	 * 
	 * @param  string $auth
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function update_category($auth = array(), $post_data = array())
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
	 * Delete a category
	 * 
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function delete_category($auth = array(), $post_data = array())
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

	/**
	 * Search a category 
	 * 
	 * @param  string $username  
	 * @param  string $password  
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function search_category($auth = array(), $post_data = array())
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
		/**  Search the entry entry
		/** ---------------------------------------*/
		$search_result = $this->_search_category($post_data);

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
				/** set the data correct
				/** ---------------------------------------*/
				$return_entry_data[] = $this->_format_read_result($entry_data);
			};
		
			/** ---------------------------------------
			/** return response
			/** ---------------------------------------*/
			$this->service_error['succes_read']['entries'] = $return_entry_data;
			return $this->service_error['succes_read'];
		}		
	}
	

	// ----------------------------------------------------------------
	// PRIVATE FUNCTIONS 
	// ----------------------------------------------------------------
	

	/**
	 * Get a category by cat_id
	 * 
	 * @param  string $cat_id 
	 * @return object                     
	*/
	private function _get_category_by_id($cat_id = '')
	{
		ee()->db->select("*");
		ee()->db->from("categories c");

		//custom fields
		if(!empty($this->fields))
		{
			ee()->db->join('category_field_data f', 'c.cat_id = f.cat_id', 'left');
		}

		ee()->db->where("c.cat_id", $cat_id);
		$query = ee()->db->get();

		if($query->num_rows() > 0)
		{
			$result = $query->row_array();

			//set the custom fieds correct
			if(!empty($this->fields))
			{
				foreach($this->fields as $field)
				{
					$field_id_var = 'field_id_'.$field['field_id'];
					$field_ft_var = 'field_ft_'.$field['field_id'];

					$result[$field['field_name']] = $result[$field_id_var];
					unset($result[$field_id_var]);
					unset($result[$field_ft_var]);
				} 
			}

			return $result;
		}

		return '';
	}

	// ----------------------------------------------------------------

	/**
	 * Get all the categories from a group
	 * 
	 * @param  string $category_group_id 
	 * @param  string $order             
	 * @return [object]                    
	 */
	private function _get_categories_by_group_id($category_group_id = '', $order = 'ASC')
	{
		ee()->db->select("*");
		ee()->db->where("group_id", $category_group_id);
		ee()->db->order_by('cat_order', $order);
		$query = ee()->db->get("categories");

		$categories = array();

		if($query->num_rows() > 0)
		{
			foreach($query->result() as $row)
			{
				$categories[] = $row;
			}
		}

		return $categories;
	}

	// ----------------------------------------------------------------
	/**
	 * Get the latest order id from a category group 
	 * 
	 * @param  string $category_group_id 
	 * @return [int]                    
	 */
	private function _get_latest_order_id_from_group($category_group_id = '')
	{
		//get all categories
		$category = $this->_get_categories_by_group_id($category_group_id, 'desc');

		if(!empty($category))
		{
			return $category[0]->cat_order + 1;
		}	

		//empty? return empty value
		return 1;
	}
	
	// ----------------------------------------------------------------
	
	/**
	 * Get the category data assinged to a user
	 *
	 * @param	int
	 * @return	void
	 */
	private function _parse_category_group($category_group_id = '', $action = 'edit')
	{
		//get the channel data
		ee()->db->select('*')->from('category_groups')->where('group_id', $category_group_id);
		$query = ee()->db->get();

		if ($query->num_rows() == 0)
		{	
			//invalid category
			return false;
		}

		//category data array
		$this->category_group = array();

		//check if the user can edit this category
		foreach ($query->result() as $key=>$row)
		{
			if ( $action == 'edit' && @stripos($row->can_edit_categories, ee()->session->userdata('group_id')) === false && ee()->session->userdata('group_id') != '1')
			{
				//no rights to edit
				return false;
			}

			if ( $action == 'delete' && @stripos($row->can_delete_categories, ee()->session->userdata('group_id')) === false && ee()->session->userdata('group_id') != '1')
			{
				//no rights to delete
				return false;
			}
			
			//assign to var
			$this->category_group = $row;
		}

		//Find Fields
		ee()->db->select('f.*')
				->from('category_fields f, category_groups c')
				->where('c.group_id = f.group_id')
				->where('c.group_id', $this->category_group->group_id)
				->order_by('f.field_order');
		$query = ee()->db->get();
		
		//save the fields
		$this->fields = array();
		if ($query->num_rows() != 0)
		{	
			foreach($query->result_array() as $row)
			{
				$this->fields[$row['field_id']] = $row;
			}
		}

		return true;
	}

	// ----------------------------------------------------------------

	/**
	 * _insert_category
	 * @param  array  $values 
	 * @param  string $action 
	 * @return [type]         
	 */
	private function _insert_category($values = array(), $action = 'insert')
	{
		//prepare the custom fields
		$category_array_custom = array(); 
		if(!empty($this->fields))
		{
			foreach($this->fields as $field)
			{
				if(isset($values[$field['field_name']]))
				{
					$category_array_custom['field_id_'.$field['field_id']]  = $values[$field['field_name']];
					$category_array_custom['field_ft_'.$field['field_id']]  = $field['field_default_fmt'];	

					//unset from values array
					unset($values[$field['field_name']]);	
				}
			}
		}

		if($action == 'insert')
		{
			//insert the data
			if(ee()->db->insert('categories', $values))
			{
				//add the associated data
				$insert_data = array_merge(
					array(
						'cat_id' 	=> ee()->db->insert_id(),
						'site_id'	=> $values['site_id'],
						'group_id'	=> $values['group_id']
					),
					$category_array_custom
				);
				ee()->db->insert('category_field_data', $insert_data);

				//generate succes
				return $insert_data['cat_id'];
			}
			else
			{
				//generate succes
				return false;
			}		
		}
		else if($action == 'update')
		{
			//insert the data
			ee()->db->where('cat_id', $values['cat_id']);
			if(ee()->db->update('categories', $values))
			{
				//add the associated data
				$insert_data = array_merge(
					array(
						'cat_id' 	=> $values['cat_id'],
						'site_id'	=> $values['site_id'],
						'group_id'	=> $values['group_id']
					),
					$category_array_custom
				);
				ee()->db->where('cat_id', $values['cat_id']);
				ee()->db->update('category_field_data', $insert_data);

				//generate succes
				return true;
			}
			else
			{
				//generate succes
				return false;
			}	
		}
	}

	// ----------------------------------------------------------------

	/**
	 * _update_category
	 * @param  array  $values 
	 * @return [type]         
	 */
	private function _update_category($values = array())
	{
		return $this->_insert_category((array) $values, 'update');
	}
}
/* End of file entry_api_category.php */
/* Location: /system/expressionengine/third_party/entry_api/libraries/api/entry_api_category.php */