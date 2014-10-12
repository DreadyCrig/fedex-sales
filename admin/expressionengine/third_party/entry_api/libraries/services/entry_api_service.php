<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * The service class, here are all methods defined
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

class Entry_api_service
{
	// ----------------------------------------------------------------------
	
	/**
	 * Auth based on username
	 *
	 * @param none
	 * @return void
	 */
	static function authenticate_username($auth, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry_api_auth');	

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//set defaults
		$auth = array_merge(array(
			'username' => '',
			'password' => ''
		), $auth);

		//post the data to the service
		$return_data = ee()->entry_api_auth->authenticate_username($auth['username'], $auth['password']);

		//unset the response txt
		unset($return_data['response']);

		//return result
		return $return_data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert a entry in the database
	 *
	 * @param none
	 * @return void
	 */
	static function create_entry($auth, $data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry_api_entry');	

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_entry->create_entry($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_entry->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * read a entry
	 *
	 * @param none
	 * @return void
	 */
	static function read_entry($auth, $data, $type = '')
	{	

		//load the entry class
		ee()->load->library('api/entry_api_entry');	

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_entry->read_entry($auth, $data);

		//var_dump($return_data);exit;
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{	
				$return_data['data'] = self::_format_readed_data($return_data['data'], 'entry_list');
			}
		}

		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_entry->servicedata);

		//unset the response txt
		unset($return_data['response']);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update a entry in the database
	 *
	 * @param none
	 * @return void
	 */
	static function update_entry($auth, $data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry_api_entry');	

		//load the helper
		ee()->load->helper('entry_api_helper');
		
		//post the data to the service
		$return_data = ee()->entry_api_entry->update_entry($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_entry->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest'  && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	 
	
	// ----------------------------------------------------------------------
	
	/**
	 * Delete a entry
	 *
	 * @param none
	 * @return void
	 */
	static function delete_entry($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_entry');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_entry->delete_entry($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_entry->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}
		
		//return result
		return $return_data;
	}	

	// ----------------------------------------------------------------------
	
	/**
	 * search a entry
	 *
	 * @param none
	 * @return void
	 */
	static function search_entry($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_entry');	

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_entry->search_entry($auth, $data);
		
		//unset the response txt
		unset($return_data['response']);

		//rename the key
		if(isset($return_data['entries']))
		{
			//soap
			if($type == 'soap')
			{
				$return_data['data'] = self::_format_readed_data($return_data['entries'], 'entry_list');
			}

			//rest
			else
			{
				$return_data['data'] = $return_data['entries'];
			}
			
			unset($return_data['entries']);
		}

		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_entry->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	
	
	// ----------------------------------------------------------------------

	/**
	 * Insert a category in the database
	 *
	 * @param none
	 * @return void
	 */
	static function create_category($auth, $data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry_api_category');	

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_category->create_category($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_category->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * read a category
	 *
	 * @param none
	 * @return void
	 */
	static function read_category($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_category');	

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_category->read_category($auth, $data);
		
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{
				$return_data['data'] = self::_format_readed_data($return_data['data'], 'entry_list');
			}
		}

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_category->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update a category in the database
	 *
	 * @param none
	 * @return void
	 */
	static function update_category($auth, $data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry_api_category');	

		//load the helper
		ee()->load->helper('entry_api_helper');
		
		//post the data to the service
		$return_data = ee()->entry_api_category->update_category($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_category->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	 
	
	// ----------------------------------------------------------------------
	
	/**
	 * Delete a category
	 *
	 * @param none
	 * @return void
	 */
	static function delete_category($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_category');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_category->delete_category($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_category->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	

	// ----------------------------------------------------------------------

	/**
	 * Insert a category group
	 *
	 * @param none
	 * @return void
	 */
	static function create_category_group($auth, $data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry_api_category_group');	

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_category_group->create_category_group($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_category_group->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * read a category group
	 *
	 * @param none
	 * @return void
	 */
	static function read_category_group($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_category_group');	

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_category_group->read_category_group($auth, $data);
		
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{
				$return_data['data'] = self::_format_readed_data($return_data['data'], 'entry_list');
			}
		}

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_category_group->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update a category group
	 *
	 * @param none
	 * @return void
	 */
	static function update_category_group($auth, $data, $type = '')
	{		
		//load the entry class
		ee()->load->library('api/entry_api_category_group');	

		//load the helper
		ee()->load->helper('entry_api_helper');
		
		//post the data to the service
		$return_data = ee()->entry_api_category_group->update_category_group($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_category_group->servicedata);
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}	 
	
	// ----------------------------------------------------------------------
	
	/**
	 * Delete a category group
	 *
	 * @param none
	 * @return void
	 */
	static function delete_category_group($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_category_group');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_category_group->delete_category_group($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_category_group->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Create a channel
	 *
	 * @param none
	 * @return void
	 */
	static function create_channel($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_channel');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_channel->create_channel($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_channel->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Read a channel
	 *
	 * @param none
	 * @return void
	 */
	static function read_channel($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_channel');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_channel->read_channel($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{
				$return_data['data'] = self::_format_readed_data($return_data['data'], 'entry_list');
			}
		}
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_channel->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update a channel
	 *
	 * @param none
	 * @return void
	 */
	static function update_channel($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_channel');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_channel->update_channel($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_channel->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Delete a channel
	 *
	 * @param none
	 * @return void
	 */
	static function delete_channel($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_channel');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_channel->delete_channel($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_channel->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Create a channel
	 *
	 * @param none
	 * @return void
	 */
	static function create_comment($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_comment');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_comment->create_comment($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_comment->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Read a channel
	 *
	 * @param none
	 * @return void
	 */
	static function read_comment($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_comment');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_comment->read_comment($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{
				$return_data['data'] = self::_format_readed_data($return_data['data'], 'entry_list');
			}
		}
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_comment->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update a channel
	 *
	 * @param none
	 * @return void
	 */
	static function update_comment($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_comment');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_comment->update_comment($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_comment->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Delete a comment
	 *
	 * @param none
	 * @return void
	 */
	static function delete_comment($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_comment');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_comment->delete_comment($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_comment->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Create a channel
	 *
	 * @param none
	 * @return void
	 */
	static function create_member($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_member');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_member->create_member($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_member->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Read a channel
	 *
	 * @param none
	 * @return void
	 */
	static function read_member($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_member');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_member->read_member($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		if($type == 'soap')
		{
			if(isset($return_data['data']))
			{
				$return_data['data'] = self::_format_readed_data($return_data['data'], 'entry_list');
			}
		}
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_member->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Update a channel
	 *
	 * @param none
	 * @return void
	 */
	static function update_member($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_member');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_member->update_member($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_member->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Delete a channel
	 *
	 * @param none
	 * @return void
	 */
	static function delete_member($auth, $data, $type = '')
	{	
		//load the entry class
		ee()->load->library('api/entry_api_member');

		//load the helper
		ee()->load->helper('entry_api_helper');	

		//post the data to the service
		$return_data = ee()->entry_api_member->delete_member($auth, $data);

		//unset the response txt
		unset($return_data['response']);
		
		//log
		ee()->entry_api_model->add_log(ee()->session->userdata('username'), $return_data, __METHOD__, $type, ee()->entry_api_member->servicedata);	
		
		//format the array, because we cannot do nested arrays
		if($type != 'rest' && isset($return_data['data']))
		{
			$return_data['data'] = entry_api_service::format_data($return_data['data'], $type);
		}

		//return result
		return $return_data;
	}

	// ----------------------------------------------------------------------
	// PRIVATE FUNCTIONS
	// ----------------------------------------------------------------------	

	/**
	 * _format_readed_data function
	 *
	 * Posible types: list|entry
	 * 
	 * 
	 * @param  [type] $data 
	 * @return [type]       
	 */
	public static function format_data($data = array(), $type = '')
	{
		$return = array();

		if(!empty($data))
		{
			foreach($data as $key => $val)
			{
				if(!empty($val))
				{
					foreach($val as $k => $v)
					{
						if($type == 'soap')
						{
							foreach($v as $_k => $_v)
							{
								$return[$key][$k][$_k] = is_array($_v) ? json_encode($_v) : $_v;
							}
						}
						else
						{
							$return[$key][$k] = is_array($v) ? json_encode($v) : $v;
						}
						
					}
				}
			}
		}

		return $return;
	}

	// ----------------------------------------------------------------------

	/**
	 * _format_readed_data function
	 *
	 * Posible types: list|entry
	 * 
	 * 
	 * @param  [type] $data 
	 * @return [type]       
	 */
	private static function _format_readed_data($data, $type = 'entry')
	{
		//grab the data and assign it to a tmp var
		if(isset($data))
		{
			$data_ = $data;
			$data = array();
		}
		else
		{
			$data_ = array();
		}
		
		//create the structures
		if(!empty($data_))
		{
			$i = $ii = 0;
			foreach($data_ as $key=>$val)
			{	
				// one entry
				if($type == "entry")
				{
					//assign
					$data[$i] = array('key'=>$key, 'value'=>$val);	
				}

				//multiple entries
				else if($type == "entry_list")
				{
					if(!empty($val))
					{
						foreach($val as $k=>$v)
						{
							//assign
							$data[$i][$ii] = array('key'=>$k, 'value'=>$v);
							$ii++;
						}
					}
				}

				$i++;
			}
		}
		else
		{
			$data = array();
		}

		return (array) $data;
	}

	// ----------------------------------------------------------------------

}