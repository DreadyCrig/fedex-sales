<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Category groupe API
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

class Entry_api_category_group
{	
	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		//load the url helper
		ee()->load->helper('url');	

		//get the category model
		ee()->load->model('category_model');

		//require the default settings
        require PATH_THIRD.'entry_api/settings.php';
	}

	// ----------------------------------------------------------------

	/**
	 * Create
	 *
	 * @return 	void
	 */
	public function create_category_group($post_data = array())
	{
		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();

		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['group_name']) || $post_data['group_name'] == '') {
			$data_errors[] = 'group_name';
		}

		/** ---------------------------------------
		/**  Return error when there are fields who are empty en shoulnd`t
		/** ---------------------------------------*/
		if(!empty($data_errors) || count($data_errors) > 0)
		{
			//generate error
			$this->service_error['error_channel']['message'] .= ' '.implode(', ',$data_errors);
			return $this->service_error['error_channel'];
		}

		/** ---------------------------------------
		/**  default Entry data
		/** ---------------------------------------*/
		$category_group = array(
			'site_id'				=>	isset($post_data['site_id']) ? $post_data['site_id'] : 1,
			'group_name'			=> 	$post_data['group_name'],
			'field_html_formatting'	=>	isset($post_data['field_html_formatting']) ? $post_data['field_html_formatting'] : 'all',  	//possible values are: [all] / [none] / [safe]
			'exclude_group'			=> 	isset($post_data['exclude_group']) ? $post_data['exclude_group'] : 0, 						//Exclude from Channel or File Category Assignment? possible values are: [0](none) / [1](channel) / [2](file)
			'can_edit_categories'	=>	isset($post_data['can_edit_categories']) ? $post_data['can_edit_categories'] : '', 			//membergroups divided by a pipeline
			'can_delete_categories'	=>	isset($post_data['can_delete_categories']) ? $post_data['can_delete_categories'] : '', 		//membergroups divided by a pipeline
		);

		/** ---------------------------------
		/**  Does this category already exist?
		/** ---------------------------------*/	
		$category_group_check = $this->_get_category_group_by_name($category_group['group_name']);
		if( $category_group_check != '')
		{
			//generate error
			return $this->service_error['error_duplicated_category'];
		}

		/** ---------------------------------
		/**  Create the categroy group
		/** ---------------------------------*/	
		if(ee()->db->insert('category_groups', $category_group) )
		{
			/* -------------------------------------------
			/* 'entry_api_create_category_group_end' hook.
			/*  - Added: 2.2
			*/
			Entry_api_helper::add_hook('create_category_group_end', ee()->db->insert_id());
			// -------------------------------------------

			//generate succes
			$this->service_error['succes_create']['id'] = ee()->db->insert_id();
			return $this->service_error['succes_create'];
		}
		else
		{
			//generate error
			return $this->service_error['error_create_category_group'];
		}
	}
	
	// ----------------------------------------------------------------

	/**
	 * Read
	 *
	 * @return 	void
	 */
	public function read_category_group($post_data = array())
	{
		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();

		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['group_id']) || $post_data['group_id'] == '') {
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
		/**  default Entry data
		/** ---------------------------------------*/
		$category_group = array(
			'group_id'			=> 	$post_data['group_id'],
		);

		/** ---------------------------------------
		/**  Get the  category to get the group_id, also check if the category exists
		/** ---------------------------------------*/
		$category_group_id = $this->_get_category_group_by_id($post_data['group_id']);
		
		if(empty($category_group_id) || $category_group_id == '')
		{
			//generate error
			return $this->service_error['error_no_category'];
		}

		/** ---------------------------------------
		/**  Get the the sub categories
		/** ---------------------------------------*/
		if(isset($post_data['show_children']) && $post_data['show_children'] == 'yes')
		{
			$category_group_id['categories'] = $this->_get_categories_by_group_id($category_group_id['group_id']);
		}

		/** ---------------------------------------
		/**  Get the category information and check if this category is assinged to the user
		/** ---------------------------------------*/
		if( ! $this->_parse_category_group($category_group['group_id']))
		{
			//generate error
			return $this->service_error['error_category'];
		}

		/* -------------------------------------------
		/* 'entry_api_read_category_group_end' hook.
		/*  - Added: 2.2
		*/
		Entry_api_helper::add_hook('read_category_group_end', $category_group_id);
		// -------------------------------------------

		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'][0] = $category_group_id;
		$this->service_error['succes_read']['id'] = $post_data['group_id'];
		return $this->service_error['succes_read'];
	}
	
	
	// ----------------------------------------------------------------

	/**
	 * Update
	 *
	 * @return 	void
	 */
	public function update_category_group($post_data = array())
	{
		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();
		$length_errors = array();

		/** ---------------------------------------
		/**  Cat_id is required
		/** ---------------------------------------*/
		if(!isset($post_data['group_id']) || $post_data['group_id'] == '') {
			$data_errors[] = 'group_id';
		}

		/** ---------------------------------------
		/**  Get the  category to get the group_id, also check if the category exists
		/** ---------------------------------------*/
		$category_group_id = $this->_get_category_group_by_id($post_data['group_id']);
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

		$category_group_array = $category_group_id;

		/** ---------------------------------------
		/**  default Entry data
		/** ---------------------------------------*/
		$category_group_array['site_id'] = isset($post_data['site_id']) ? $post_data['site_id'] : $category_group_array['site_id'];
		$category_group_array['group_name'] = isset($post_data['group_name']) ? $post_data['group_name'] : $category_group_array['group_name'];
		$category_group_array['exclude_group'] = isset($post_data['exclude_group']) ? $post_data['exclude_group'] : $category_group_array['exclude_group'];
		$category_group_array['field_html_formatting'] = isset($post_data['field_html_formatting']) ? $post_data['field_html_formatting'] : $category_group_array['field_html_formatting'];
		$category_group_array['can_edit_categories'] = isset($post_data['can_edit_categories']) ? $post_data['can_edit_categories'] : $category_group_array['can_edit_categories'];
		$category_group_array['can_delete_categories'] = isset($post_data['can_delete_categories']) ? $post_data['can_delete_categories'] : $category_group_array['can_delete_categories'];

		/* -------------------------------------------
		/* 'entry_api_update_category_group_end' hook.
		/*  - Added: 2.2
		*/
		Entry_api_helper::add_hook('update_category_group_end', $category_group_array);
		// -------------------------------------------

		/** ---------------------------------
		/**  update the categroy
		/** ---------------------------------*/	
		ee()->db->where('group_id', $category_group_id['group_id']);
		if( ee()->db->update('category_groups', $category_group_array) )
		{
			//generate succes
			$this->service_error['succes_update']['id'] = $post_data['group_id'];
			return $this->service_error['succes_update'];
		}
		else
		{
			//generate succes
			$this->service_error['error_create_category']['id'] = $post_data['group_id'];
			return $this->service_error['error_create_category'];
		}	
	}

	// ----------------------------------------------------------------

	/**
	 * Delete
	 *
	 * @return 	void
	 */
	public function delete_category_group($post_data = array())
	{
		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();

		/** ---------------------------------------
		/**  Title is for a insert always required
		/** ---------------------------------------*/
		if(!isset($post_data['group_id']) || $post_data['group_id'] == '') {
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
		/**  Get the  category to get the group_id, also check if the category exists
		/** ---------------------------------------*/
		$category_group_id = $this->_get_category_group_by_id($post_data['group_id']);
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
		/* 'entry_api_delete_category_group_end' hook.
		/*  - Added: 2.2
		*/
		Entry_api_helper::add_hook('delete_category_group_end', $post_data['group_id']);
		// -------------------------------------------

		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		ee()->category_model->delete_category_group($post_data['group_id']);
		$this->service_error['succes_delete']['id'] = $post_data['group_id'];
		return $this->service_error['succes_delete'];
	}


	// ----------------------------------------------------------------
	// PRIVATE FUNCTIONS
	// ----------------------------------------------------------------

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

	/**
	 * [_get_category_group_by_id description]
	 * @param  string $category_id [description]
	 * @return [type]              [description]
	 */
	private function _get_category_group_by_id($category_id = '')
	{
		//$category_id = trim($category_id);
		ee()->db->select("*");
		ee()->db->where("group_id", $category_id);
		$query = ee()->db->get("category_groups");

		if($query->num_rows() > 0)
		{
			return $query->row_array();
		}

		return '';
	}

	/**
	 * [_get_category_group_by_name description]
	 * @param  string $category_name [description]
	 * @return [type]                [description]
	 */
	private function _get_category_group_by_name($category_name = '')
	{
		$category_name = trim($category_name);
		ee()->db->select("*");
		ee()->db->where("group_name", $category_name);
		$query = ee()->db->get("category_groups");

		if($query->num_rows() > 0)
		{
			return $query->row_array();
		}

		return '';
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

		return true;
	}

}

