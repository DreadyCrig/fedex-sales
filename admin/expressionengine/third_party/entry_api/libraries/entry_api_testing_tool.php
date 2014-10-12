<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Testing tools
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

class Entry_api_testing_tool
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		ini_set( 'soap.wsdl_cache_enabled', 0);
		//return $this->init();
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function init()
	{
		//set return var
		$return = '';

		//what type we have to serve
		if(isset($_POST['type']) && isset($_POST['method']) && isset($_POST['path']))
		{
			switch($_POST['type'])
			{
				case 'soap': $return = $this->soap($_POST['method']); break;
				case 'xmlrpc': $return = $this->xmlrpc($_POST['method']); break;
				case 'rest': $return = $this->rest($_POST['method']); break;
				case 'custom': $return = $this->custom($_POST['method']); break;
			}
		}

		return $return;	
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function soap($type = '')
	{
		$reponse = '';

		$client = new SoapClient(ee()->input->post('path'), array('trace' => 1));

		$extra_array = isset($_POST['extra']) && $_POST['extra'] != '' ? eval("return ".$_POST['extra']) : array() ;

		try
		{
			switch($type)
			{
				//Entry
				case 'create_entry' : 
					$reponse = $client->create_entry(array(
						'session_id' => ee()->session->userdata('session_id')
					), array_merge(array(
						'site_id' => ee()->config->item('site_id'),
						'channel_name' => ee()->input->post('channel_name'),
						'status' => ee()->input->post('status'),
						'title' => ee()->input->post('title'),
						'sticky' => ee()->input->post('sticky'),
						'allow_comments' => ee()->input->post('allow_comments'),
						'category' => ee()->input->post('category'),
						'file_field' => array(
							'filedata' => base64_encode(@file_get_contents($_FILES['file_field']['tmp_name'])),
							'filename' => $_FILES['file_field']['name']
						),
					), (array)$extra_array));
				break;
				case 'read_entry' : 
					$reponse = $client->read_entry(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'entry_id' => ee()->input->post('entry_id'),
					));
				break;
				case 'update_entry' : 
					$reponse = $client->update_entry(array(
						'session_id' => ee()->session->userdata('session_id')
					), array_merge(array(
						'entry_id' => ee()->input->post('entry_id'),
						'status' => ee()->input->post('status'),
						'title' => ee()->input->post('title'),
						'sticky' => ee()->input->post('sticky'),
						'allow_comments' => ee()->input->post('allow_comments'),
						'category' => ee()->input->post('category'),
						'file_field' => array(
							'filedata' => base64_encode(@file_get_contents($_FILES['file_field']['tmp_name'])),
							'filename' => $_FILES['file_field']['name']
						),
					), (array)$extra_array));
				break;
				case 'delete_entry' : 
					$reponse = $client->delete_entry(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'entry_id' => ee()->input->post('entry_id'),
					));
				break; 
				case 'search_entry' : 
					$reponse = $client->search_entry(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'title' => ee()->input->post('title'),
						'limit' => ee()->input->post('limit'),
						'channel' => ee()->input->post('channel'),
					));
				break; 

				//Category group
				case 'create_category_group' : 
					$reponse = $client->create_category_group(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'group_name' => ee()->input->post('group_name'),
						'exclude_group' => ee()->input->post('exclude_group'),
						'field_html_formatting' => ee()->input->post('field_html_formatting'),
						'can_edit_categories' => ee()->input->post('can_edit_categories'),
						'can_delete_categories' => ee()->input->post('can_delete_categories'),
					));
				break;
				case 'read_category_group' : 
					$reponse = $client->read_category_group(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'group_id' => ee()->input->post('group_id'),
					));
				break;
				case 'update_category_group' : 
					$reponse = $client->update_category_group(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'group_id' => ee()->input->post('group_id'),
						'group_name' => ee()->input->post('group_name'),
						'exclude_group' => ee()->input->post('exclude_group'),
						'field_html_formatting' => ee()->input->post('field_html_formatting'),
						'can_edit_categories' => ee()->input->post('can_edit_categories'),
						'can_delete_categories' => ee()->input->post('can_delete_categories'),
					));
				break;
				case 'delete_category_group' : 
					$reponse = $client->delete_category_group(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'group_id' => ee()->input->post('group_id'),
					));
				break;

				//Category
				case 'create_category' : 
					$reponse = $client->create_category(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'group_id' => ee()->input->post('group_id'),
						'parent_id' => ee()->input->post('parent_id'),
						'cat_name' => ee()->input->post('cat_name'),
						'cat_description' => ee()->input->post('cat_description'),
						'custom_field' => ee()->input->post('custom_field'),
					));
				break;
				case 'read_category' : 
					$reponse = $client->read_category(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'cat_id' => ee()->input->post('cat_id'),
					));
				break;
				case 'update_category' : 
					$reponse = $client->update_category(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'cat_id' => ee()->input->post('cat_id'),
						'group_id' => ee()->input->post('group_id'),
						'parent_id' => ee()->input->post('parent_id'),
						'cat_name' => ee()->input->post('cat_name'),
						'cat_url_title' => ee()->input->post('cat_url_title'),
						'cat_order' => ee()->input->post('cat_order'),
						'cat_description' => ee()->input->post('cat_description'),
						'custom_field' => ee()->input->post('custom_field'),
					));
				break;
				case 'delete_category' : 
					$reponse = $client->delete_category(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'cat_id' => ee()->input->post('cat_id'),
					));
				break;
				
				//Channel
				case 'create_channel' : 
					$reponse = $client->create_channel(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'site_id'   => ee()->input->post('site_id'),
						'channel_title'   => ee()->input->post('channel_title'),
						'channel_name'   => ee()->input->post('channel_name'),
						'url_title_prefix'   => ee()->input->post('url_title_prefix'),
						'comment_expiration'   => ee()->input->post('comment_expiration'),
						//'dupe_id'   => ee()->input->post('dupe_id'),
						'status_group'   => ee()->input->post('status_group'),
						'field_group'   => ee()->input->post('field_group'),
						'channel_url'   => ee()->input->post('channel_url'),
						'channel_lang'   => ee()->input->post('channel_lang'),
						'group_order'   => ee()->input->post('group_order'),
					));
				break;
				case 'read_channel' : 
					$reponse = $client->read_channel(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'channel_id' => ee()->input->post('channel_id'),
					));
				break;
				case 'update_channel' : 
					$reponse = $client->update_channel(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'channel_id' => ee()->input->post('channel_id'),
						'site_id'   => ee()->input->post('site_id'),
						'channel_title'   => ee()->input->post('channel_title'),
						'channel_name'   => ee()->input->post('channel_name'),
						'url_title_prefix'   => ee()->input->post('url_title_prefix'),
						'comment_expiration'   => ee()->input->post('comment_expiration'),
						//'dupe_id'   => ee()->input->post('dupe_id'),
						'status_group'   => ee()->input->post('status_group'),
						'field_group'   => ee()->input->post('field_group'),
						'channel_url'   => ee()->input->post('channel_url'),
						'channel_lang'   => ee()->input->post('channel_lang'),
						'group_order'   => ee()->input->post('group_order'),
					));
				break;
				case 'delete_channel' : 
					$reponse = $client->delete_channel(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'channel_id' => ee()->input->post('channel_id'),
					));
				break;
				
				//Member
				case 'create_member' : 
					$reponse = $client->create_member(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'username'   => ee()->input->post('username'),
						'password'   => ee()->input->post('password'),
						'password_confirm'   => ee()->input->post('password'),
						'screen_name'   => ee()->input->post('screen_name'),
						'email'   => ee()->input->post('email'),
						'group_id'   => ee()->input->post('group_id'),
						'bday_y'   => ee()->input->post('bday_y'),
						'bday_m'   => ee()->input->post('bday_m'),
						'bday_d'   => ee()->input->post('bday_d'),
						'url'   => ee()->input->post('url'),
						'location'   => ee()->input->post('location'),
						'occupation'   => ee()->input->post('occupation'),
						'interests'   => ee()->input->post('interests'),
						'aol_im'   => ee()->input->post('aol_im'),
						'icq'   => ee()->input->post('icq'),
						'yahoo_im'   => ee()->input->post('yahoo_im'),
						'msn_im'   => ee()->input->post('msn_im'),
						'bio'   => ee()->input->post('bio'),
					));
				break;
				case 'read_member' : 
					$reponse = $client->read_member(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'member_id' => ee()->input->post('member_id'),
					));
				break;
				case 'update_member' : 
					$reponse = $client->update_member(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'member_id'   => ee()->input->post('member_id'),
						'username'   => ee()->input->post('username'),
						'password'   => ee()->input->post('password'),
						'password_confirm'   => ee()->input->post('password'),
						'screen_name'   => ee()->input->post('screen_name'),
						'email'   => ee()->input->post('email'),
						'group_id'   => ee()->input->post('group_id'),
						'bday_y'   => ee()->input->post('bday_y'),
						'bday_m'   => ee()->input->post('bday_m'),
						'bday_d'   => ee()->input->post('bday_d'),
						'url'   => ee()->input->post('url'),
						'location'   => ee()->input->post('location'),
						'occupation'   => ee()->input->post('occupation'),
						'interests'   => ee()->input->post('interests'),
						'aol_im'   => ee()->input->post('aol_im'),
						'icq'   => ee()->input->post('icq'),
						'yahoo_im'   => ee()->input->post('yahoo_im'),
						'msn_im'   => ee()->input->post('msn_im'),
						'bio'   => ee()->input->post('bio'),

					));
				break;
				case 'delete_member' : 
					$reponse = $client->delete_member(array(
						'session_id' => ee()->session->userdata('session_id')
					), array(
						'member_id' => ee()->input->post('member_id'),
					));
				break;
			}
		}
		catch (Exception $e)
		{
			return array(
				//$e,
				$client->__getLastRequestHeaders(),
				$client->__getLastRequest(),
				$client->__getLastResponseHeaders(),
				$client->__getLastResponse()
			);
		}	

		return $reponse;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function xmlrpc($type = '')
	{

		/*
		* 	alternatief
		*/

		include("xmlrpc/xmlrpc.inc"); 

		$c = new xmlrpc_client(ee()->input->post('path'));
		//$c->debug = true; 
		
		$extra_array = isset($_POST['extra']) && $_POST['extra'] != '' ? eval("return ".$_POST['extra']) : array() ;


		switch($type)
		{
			//entry
			case 'create_entry' : 
					
				$x = new xmlrpcmsg("create_entry", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array_merge(array(
						'site_id' => ee()->config->item('site_id'),
						'channel_name' => ee()->input->post('channel_name'),
						'site_id' => ee()->entry_api_settings->item('site_id'),
						'status' => ee()->input->post('status'),
						'title' => ee()->input->post('title'),
						'sticky' => ee()->input->post('sticky'),
						'allow_comments' => ee()->input->post('allow_comments'),
						'category' => ee()->input->post('category'),
						'file_field' => array(
							'filedata' => base64_encode(@file_get_contents($_FILES['file_field']['tmp_name'])),
							'filename' => $_FILES['file_field']['name']
						),
					), (array)$extra_array)),
				));
			break;
			case 'read_entry' : 
			
				$x = new xmlrpcmsg("read_entry", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'entry_id' => ee()->input->post('entry_id'),
					)),
				));
			break;
			case 'update_entry' : 
				$x = new xmlrpcmsg("update_entry", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array_merge(array(
						'entry_id' => ee()->input->post('entry_id'),
						'status' => ee()->input->post('status'),
						'title' => ee()->input->post('title'),
						'sticky' => ee()->input->post('sticky'),
						'allow_comments' => ee()->input->post('allow_comments'),
						'category' => ee()->input->post('category'),
						'file_field' => array(
							'filedata' => base64_encode(@file_get_contents($_FILES['file_field']['tmp_name'])),
							'filename' => $_FILES['file_field']['name']
						),
					), (array)$extra_array)),
				));
			break;
			case 'delete_entry' : 
				$x = new xmlrpcmsg("delete_entry", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'entry_id' => ee()->input->post('entry_id'),
					)),
				));
			break; 
			case 'search_entry' : 
				$x = new xmlrpcmsg("search_entry", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'title' => ee()->input->post('title'),
						'limit' => ee()->input->post('limit'),
						'channel' => ee()->input->post('channel'),
					)),
				));
			break; 

			//Cateroy group
			case 'create_category_group' : 
				$x = new xmlrpcmsg("create_category_group", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'group_name' => ee()->input->post('group_name'),
						'exclude_group' => ee()->input->post('exclude_group'),
						'field_html_formatting' => ee()->input->post('field_html_formatting'),
						'can_edit_categories' => ee()->input->post('can_edit_categories'),
						'can_delete_categories' => ee()->input->post('can_delete_categories'),
					)),
				));
			break;
			case 'read_category_group' : 
				$x = new xmlrpcmsg("read_category_group", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'group_id' => ee()->input->post('group_id'),
					)),
				));
			break;
			case 'update_category_group' : 
				$x = new xmlrpcmsg("update_category_group", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'cat_id' => ee()->input->post('cat_id'),
						'group_id' => ee()->input->post('group_id'),
						'group_name' => ee()->input->post('group_name'),
						'exclude_group' => ee()->input->post('exclude_group'),
						'field_html_formatting' => ee()->input->post('field_html_formatting'),
						'can_edit_categories' => ee()->input->post('can_edit_categories'),
						'can_delete_categories' => ee()->input->post('can_delete_categories'),
					)),
				));
			break;
			case 'delete_category_group' : 
				$x = new xmlrpcmsg("delete_category_group", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'group_id' => ee()->input->post('group_id'),
					)),
				));
			break;

			//Cateroy
			case 'create_category' : 
				$x = new xmlrpcmsg("create_category", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'group_id' => ee()->input->post('group_id'),
						'parent_id' => ee()->input->post('parent_id'),
						'cat_name' => ee()->input->post('cat_name'),
						'cat_description' => ee()->input->post('cat_description'),
						'custom_field' => ee()->input->post('custom_field'),
					)),
				));
			break;
			case 'read_category' : 
				$x = new xmlrpcmsg("read_category", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'cat_id' => ee()->input->post('cat_id'),
					)),
				));
			break;
			case 'update_category' : 
				$x = new xmlrpcmsg("update_category", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'cat_id' => ee()->input->post('cat_id'),
						'group_id' => ee()->input->post('group_id'),
						'parent_id' => ee()->input->post('parent_id'),
						'cat_name' => ee()->input->post('cat_name'),
						'cat_url_title' => ee()->input->post('cat_url_title'),
						'cat_order' => ee()->input->post('cat_order'),
						'cat_description' => ee()->input->post('cat_description'),
						'custom_field' => ee()->input->post('custom_field'),
					)),
				));
			break;
			case 'delete_category' : 
				$x = new xmlrpcmsg("delete_category", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'cat_id' => ee()->input->post('cat_id'),
					)),
				));
			break;
			
			//Channel
			case 'create_channel' : 
				$x = new xmlrpcmsg("create_channel", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'site_id'   => ee()->input->post('site_id'),
						'channel_title'   => ee()->input->post('channel_title'),
						'channel_name'   => ee()->input->post('channel_name'),
						'url_title_prefix'   => ee()->input->post('url_title_prefix'),
						'comment_expiration' => ee()->input->post('comment_expiration'),
						//'dupe_id'   => ee()->input->post('dupe_id'),
						'status_group'   => ee()->input->post('status_group'),
						'field_group'   => ee()->input->post('field_group'),
						'channel_url'   => ee()->input->post('channel_url'),
						'channel_lang'   => ee()->input->post('channel_lang'),
						'group_order'   => ee()->input->post('group_order'),
					)),
				));
			break;
			case 'read_channel' : 
				$x = new xmlrpcmsg("read_channel", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'channel_id' => ee()->input->post('channel_id'),
					)),
				));
			break;
			case 'update_channel' : 
				$x = new xmlrpcmsg("update_channel", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'channel_id' => ee()->input->post('channel_id'),
						'site_id'   => ee()->input->post('site_id'),
						'channel_title'   => ee()->input->post('channel_title'),
						'channel_name'   => ee()->input->post('channel_name'),
						'url_title_prefix'   => ee()->input->post('url_title_prefix'),
						'comment_expiration'   => ee()->input->post('comment_expiration'),
						//'dupe_id'   => ee()->input->post('dupe_id'),
						'status_group'   => ee()->input->post('status_group'),
						'field_group'   => ee()->input->post('field_group'),
						'channel_url'   => ee()->input->post('channel_url'),
						'channel_lang'   => ee()->input->post('channel_lang'),
						'group_order'   => ee()->input->post('group_order'),
					)),
				));
			break;
			case 'delete_channel' : 
				$x = new xmlrpcmsg("delete_channel", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'channel_id' => ee()->input->post('channel_id'),
					)),
				));
			break;

			//Comment
			case 'create_comment' : 
				$x = new xmlrpcmsg("create_comment", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'entry_id'   => ee()->input->post('entry_id'),
						'status'   => ee()->input->post('status'),
						'ip_address'   => ee()->input->post('ip_address'),
						'comment'   => ee()->input->post('comment'),
					)),
				));
			break;
			case 'read_comment' : 
				$x = new xmlrpcmsg("read_comment", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'entry_id' => ee()->input->post('entry_id'),
					)),
				));
			break;
			case 'update_comment' : 
				$x = new xmlrpcmsg("update_comment", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'entry_id'   => ee()->input->post('entry_id'),
						'status'   => ee()->input->post('status'),
						'ip_address'   => ee()->input->post('ip_address'),
						'comment'   => ee()->input->post('comment'),
					)),
				));
			break;
			case 'delete_comment' : 
				$x = new xmlrpcmsg("delete_comment", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'comment_id' => ee()->input->post('comment_id'),
					)),
				));
			break;
			
			//Member
			case 'create_member' : 
				$x = new xmlrpcmsg("create_member", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'username'   => ee()->input->post('username'),
						'password'   => ee()->input->post('password'),
						'password_confirm'   => ee()->input->post('password_confirm'),
						'screen_name'   => ee()->input->post('screen_name'),
						'email'   => ee()->input->post('email'),
						'group_id'   => ee()->input->post('group_id'),
						'bday_y'   => ee()->input->post('bday_y'),
						'bday_m'   => ee()->input->post('bday_m'),
						'bday_d'   => ee()->input->post('bday_d'),
						'url'   => ee()->input->post('url'),
						'location'   => ee()->input->post('location'),
						'occupation'   => ee()->input->post('occupation'),
						'interests'   => ee()->input->post('interests'),
						'aol_im'   => ee()->input->post('aol_im'),
						'icq'   => ee()->input->post('icq'),
						'yahoo_im'   => ee()->input->post('yahoo_im'),
						'msn_im'   => ee()->input->post('msn_im'),
						'bio'   => ee()->input->post('bio'),					)),
				));
			break;
			case 'read_member' : 
				$x = new xmlrpcmsg("read_member", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'member_id' => ee()->input->post('member_id'),
					)),
				));
			break;
			case 'update_member' : 
				$x = new xmlrpcmsg("update_member", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'member_id' => ee()->input->post('member_id'),
						'username'   => ee()->input->post('username'),
						'password'   => ee()->input->post('password'),
						'password_confirm'   => ee()->input->post('password_confirm'),
						'screen_name'   => ee()->input->post('screen_name'),
						'email'   => ee()->input->post('email'),
						'group_id'   => ee()->input->post('group_id'),
						'bday_y'   => ee()->input->post('bday_y'),
						'bday_m'   => ee()->input->post('bday_m'),
						'bday_d'   => ee()->input->post('bday_d'),
						'url'   => ee()->input->post('url'),
						'location'   => ee()->input->post('location'),
						'occupation'   => ee()->input->post('occupation'),
						'interests'   => ee()->input->post('interests'),
						'aol_im'   => ee()->input->post('aol_im'),
						'icq'   => ee()->input->post('icq'),
						'yahoo_im'   => ee()->input->post('yahoo_im'),
						'msn_im'   => ee()->input->post('msn_im'),
						'bio'   => ee()->input->post('bio'),
					)),
				));
			break;
			case 'delete_member' : 
				$x = new xmlrpcmsg("delete_member", array(
					php_xmlrpc_encode(array(
						'session_id' => ee()->session->userdata('session_id')
					)),
					php_xmlrpc_encode(array(
						'member_id' => ee()->input->post('member_id'),
					)),
				));
			break;


		}

		$c->return_type = 'phpvals';
		$r =$c->send($x);

		return $r;
		 
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function rest($type = '')
	{
		//include(PATH_THIRD.'entry_api/client_example/includes/rest_lib/rest_curl_client.php');

		ee()->load->library('curl');
		ee()->curl->option('FAILONERROR', false); 
		ee()->curl->create(ee()->input->post('path'));

		//http auth
		if(ee()->input->post('rest_http_auth') == "yes")
		{
			//echo 1;
			ee()->curl->http_login(ee()->input->post('username'), ee()->input->post('password'));
			$normal_auth = array();
		}
		else
		{
			$normal_auth = array(
				'auth' => array(
					'session_id' => ee()->session->userdata('session_id')
				)
			);
		}
		
		$reponse = '';
		
		$extra_array = isset($_POST['extra']) && $_POST['extra'] != '' ? eval("return ".$_POST['extra']) : array() ;

		switch($type)
		{
			//entry
			case 'create_entry' :
				$data = array_merge(array(
					'data' => array_merge(array(
						'site_id' => ee()->config->item('site_id'),
						'channel_name' => ee()->input->post('channel_name'),
						'status' => ee()->input->post('status'),
						'title' => ee()->input->post('title'),
						'sticky' => ee()->input->post('sticky'),
						'allow_comments' => ee()->input->post('allow_comments'),
						'category' => ee()->input->post('category'),
						'file_field' => array(
							'filedata' => base64_encode(@file_get_contents($_FILES['file_field']['tmp_name'])),
							'filename' => $_FILES['file_field']['name']
						),
					), (array)$extra_array)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'read_entry' : 
				$data = array_merge(array(
					'data' => array(
						'entry_id' => ee()->input->post('entry_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'update_entry' : 
				$data = array_merge(array(
					'data' => array_merge(array(
						'entry_id' => ee()->input->post('entry_id'),
						'status' => ee()->input->post('status'),
						'title' => ee()->input->post('title'),
						'sticky' => ee()->input->post('sticky'),
						'allow_comments' => ee()->input->post('allow_comments'),
						'category' => ee()->input->post('category'),
						'file_field' => array(
							'filedata' => base64_encode(@file_get_contents($_FILES['file_field']['tmp_name'])),
							'filename' => $_FILES['file_field']['name']
						),
					), (array)$extra_array)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'delete_entry' : 
				$data = array_merge(array(
					'data' => array(
						'entry_id' => ee()->input->post('entry_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break; 
			case 'search_entry' : 
				$data = array_merge(array(
					'data' => array(
						'title' => ee()->input->post('title'),
						'limit' => ee()->input->post('limit'),
						'channel' => ee()->input->post('channel'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break; 

			//Category group
			case 'create_category_group' :
				$data = array_merge(array(
					'data' => array(
						'group_name' => ee()->input->post('group_name'),
						'exclude_group' => ee()->input->post('exclude_group'),
						'field_html_formatting' => ee()->input->post('field_html_formatting'),
						'can_edit_categories' => ee()->input->post('can_edit_categories'),
						'can_delete_categories' => ee()->input->post('can_delete_categories'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'read_category_group' :
				$data = array_merge(array(
					'data' => array(
						'group_id' => ee()->input->post('group_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'update_category_group' :
				$data = array_merge(array(
					'data' => array(
						'group_id' => ee()->input->post('group_id'),
						'group_name' => ee()->input->post('group_name'),
						'exclude_group' => ee()->input->post('exclude_group'),
						'field_html_formatting' => ee()->input->post('field_html_formatting'),
						'can_edit_categories' => ee()->input->post('can_edit_categories'),
						'can_delete_categories' => ee()->input->post('can_delete_categories'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'delete_category_group' :
				$data = array_merge(array(
					'data' => array(
						'group_id' => ee()->input->post('group_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;

			//Category
			case 'create_category' :
				$data = array_merge(array(
					'data' => array(
						'group_id' => ee()->input->post('group_id'),
						'parent_id' => ee()->input->post('parent_id'),
						'cat_name' => ee()->input->post('cat_name'),
						'cat_description' => ee()->input->post('cat_description'),
						'custom_field' => ee()->input->post('custom_field'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'read_category' :
				$data = array_merge(array(
					'data' => array(
						'cat_id' => ee()->input->post('cat_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'update_category' :
				$data = array_merge(array(
					'data' => array(
						'group_id' => ee()->input->post('group_id'),
						'parent_id' => ee()->input->post('parent_id'),
						'cat_name' => ee()->input->post('cat_name'),
						'cat_url_title' => ee()->input->post('cat_url_title'),
						'cat_order' => ee()->input->post('cat_order'),
						'cat_description' => ee()->input->post('cat_description'),
						'custom_field' => ee()->input->post('custom_field'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'delete_category' :
				$data = array_merge(array(
					'data' => array(
						'cat_id' => ee()->input->post('cat_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			
			//channel
			case 'create_channel' :
				$data = array_merge(array(
					'data' => array(
						'site_id'   => ee()->input->post('site_id'),
						'channel_title'   => ee()->input->post('channel_title'),
						'channel_name'   => ee()->input->post('channel_name'),
						'url_title_prefix'   => ee()->input->post('url_title_prefix'),
						'comment_expiration'   => ee()->input->post('comment_expiration'),
						//'dupe_id'   => ee()->input->post('dupe_id'),
						'status_group'   => ee()->input->post('status_group'),
						'field_group'   => ee()->input->post('field_group'),
						'channel_url'   => ee()->input->post('channel_url'),
						'channel_lang'   => ee()->input->post('channel_lang'),
						'group_order'   => ee()->input->post('group_order'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'read_channel' :
				$data = array_merge(array(
					'data' => array(
						'channel_id' => ee()->input->post('channel_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'update_channel' :
				$data = array_merge(array(
					'data' => array(
						'channel_id' => ee()->input->post('channel_id'),
						'site_id'   => ee()->input->post('site_id'),
						'channel_title'   => ee()->input->post('channel_title'),
						'channel_name'   => ee()->input->post('channel_name'),
						'url_title_prefix'   => ee()->input->post('url_title_prefix'),
						'comment_expiration'   => ee()->input->post('comment_expiration'),
						//'dupe_id'   => ee()->input->post('dupe_id'),
						'status_group'   => ee()->input->post('status_group'),
						'field_group'   => ee()->input->post('field_group'),
						'channel_url'   => ee()->input->post('channel_url'),
						'channel_lang'   => ee()->input->post('channel_lang'),
						'group_order'   => ee()->input->post('group_order'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'delete_channel' :
				$data = array_merge(array(
					'data' => array(
						'channel_id' => ee()->input->post('channel_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;

			//Comment
			case 'create_comment' :
				$data = array_merge(array(
					'data' => array(
						'author_id'   => ee()->session->userdata('member_id'),
						'entry_id'   => ee()->input->post('entry_id'),
						'status'   => ee()->input->post('status'),
						'ip_address'   => ee()->input->post('ip_address'),
						'comment'   => ee()->input->post('comment'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'read_comment' :
				$data = array_merge(array(
					'data' => array(
						'entry_id' => ee()->input->post('entry_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'update_comment' :
				$data = array_merge(array(
					'data' => array(
						'comment_id'   => ee()->input->post('commentid'),
						'status'   => ee()->input->post('status'),
						'ip_address'   => ee()->input->post('ip_address'),
						'comment'   => ee()->input->post('comment'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'delete_comment' :
				$data = array_merge(array(
					'data' => array(
						'comment_id' => ee()->input->post('comment_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			
			//member
			case 'create_member' :
				$data = array_merge(array(
					'data' => array(
						'username'   => ee()->input->post('username'),
						'password'   => ee()->input->post('password'),
						'password_confirm'   => ee()->input->post('password_confirm'),
						'screen_name'   => ee()->input->post('screen_name'),
						'email'   => ee()->input->post('email'),
						'group_id'   => ee()->input->post('group_id'),
						'bday_y'   => ee()->input->post('bday_y'),
						'bday_m'   => ee()->input->post('bday_m'),
						'bday_d'   => ee()->input->post('bday_d'),
						'url'   => ee()->input->post('url'),
						'location'   => ee()->input->post('location'),
						'occupation'   => ee()->input->post('occupation'),
						'interests'   => ee()->input->post('interests'),
						'aol_im'   => ee()->input->post('aol_im'),
						'icq'   => ee()->input->post('icq'),
						'yahoo_im'   => ee()->input->post('yahoo_im'),
						'msn_im'   => ee()->input->post('msn_im'),
						'bio'   => ee()->input->post('bio'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'read_member' :
				$data = array_merge(array(
					'data' => array(
						'member_id' => ee()->input->post('member_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'update_member' :
				$data = array_merge(array(
					'data' => array(
						'member_id' => ee()->input->post('member_id'),
						'username'   => ee()->input->post('username'),
						'password'   => ee()->input->post('password'),
						'password_confirm'   => ee()->input->post('password_confirm'),
						'screen_name'   => ee()->input->post('screen_name'),
						'email'   => ee()->input->post('email'),
						'group_id'   => ee()->input->post('group_id'),
						'bday_y'   => ee()->input->post('bday_y'),
						'bday_m'   => ee()->input->post('bday_m'),
						'bday_d'   => ee()->input->post('bday_d'),
						'url'   => ee()->input->post('url'),
						'location'   => ee()->input->post('location'),
						'occupation'   => ee()->input->post('occupation'),
						'interests'   => ee()->input->post('interests'),
						'aol_im'   => ee()->input->post('aol_im'),
						'icq'   => ee()->input->post('icq'),
						'yahoo_im'   => ee()->input->post('yahoo_im'),
						'msn_im'   => ee()->input->post('msn_im'),
						'bio'   => ee()->input->post('bio'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			case 'delete_member' :
				$data = array_merge(array(
					'data' => array(
						'member_id' => ee()->input->post('member_id'),
					)
				), $normal_auth);

				ee()->curl->post(http_build_query($data));
			break;
			
		}

		//ee()->curl->option(CURLINFO_HEADER_OUT, true);
		$return = ee()->curl->execute();
		//print_r(ee()->curl->info);
		//ee()->curl->debug();exit;
		return $return;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * Insert the settings to the database
	 *
	 * @param none
	 * @return void
	 */
	public function custom($type = '')
	{
		ee()->load->library('entry_api_public_methods');
		
		$reponse = '';
		
		$extra_array = isset($_POST['extra']) && $_POST['extra'] != '' ? eval("return ".$_POST['extra']) : array() ;

		switch($type)
		{
			//Entry
			case 'create_entry' : 
				$reponse = ee()->entry_api_public_methods->create_entry(array(
					'session_id' => ee()->session->userdata('session_id')
				), array_merge(array(
					'site_id' => ee()->config->item('site_id'),
					'channel_name' => ee()->input->post('channel_name'),
					'status' => ee()->input->post('status'),
					'title' => ee()->input->post('title'),
					'sticky' => ee()->input->post('sticky'),
					'allow_comments' => ee()->input->post('allow_comments'),
					'category' => ee()->input->post('category'),
					'file_field' => array(
						'filedata' => base64_encode(@file_get_contents($_FILES['file_field']['tmp_name'])),
						'filename' => $_FILES['file_field']['name']
					),
				), (array)$extra_array));
			break;
			case 'read_entry' : 
				$reponse = ee()->entry_api_public_methods->read_entry(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'entry_id' => ee()->input->post('entry_id'),
				));
			break;
			case 'update_entry' : 
				$reponse = ee()->entry_api_public_methods->update_entry(array(
					'session_id' => ee()->session->userdata('session_id')
				), array_merge(array(
					'entry_id' => ee()->input->post('entry_id'),
					'status' => ee()->input->post('status'),
					'title' => ee()->input->post('title'),
					'sticky' => ee()->input->post('sticky'),
					'allow_comments' => ee()->input->post('allow_comments'),
					'category' => ee()->input->post('category'),
					'file_field' => array(
						'filedata' => base64_encode(@file_get_contents($_FILES['file_field']['tmp_name'])),
						'filename' => $_FILES['file_field']['name']
					),
				), (array)$extra_array));
			break;
			case 'delete_entry' : 
				$reponse = ee()->entry_api_public_methods->delete_entry(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'entry_id' => ee()->input->post('entry_id'),
				));
			break; 
			case 'search_entry' : 
				$reponse = ee()->entry_api_public_methods->search_entry(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'title' => ee()->input->post('title'),
					'limit' => ee()->input->post('limit'),
					'channel' => ee()->input->post('channel'),
				));
			break; 

			//Category group
			case 'create_category_group' : 
				$reponse = ee()->entry_api_public_methods->create_category_group(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'group_name' => ee()->input->post('group_name'),
					'exclude_group' => ee()->input->post('exclude_group'),
					'field_html_formatting' => ee()->input->post('field_html_formatting'),
					'can_edit_categories' => ee()->input->post('can_edit_categories'),
					'can_delete_categories' => ee()->input->post('can_delete_categories'),
				));
			break;
			case 'read_category_group' : 
				$reponse = ee()->entry_api_public_methods->read_category_group(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'group_id' => ee()->input->post('group_id'),
				));
			break;
			case 'update_category_group' : 
				$reponse = ee()->entry_api_public_methods->update_category_group(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'group_id' => ee()->input->post('group_id'),
					'group_name' => ee()->input->post('group_name'),
					'exclude_group' => ee()->input->post('exclude_group'),
					'field_html_formatting' => ee()->input->post('field_html_formatting'),
					'can_edit_categories' => ee()->input->post('can_edit_categories'),
					'can_delete_categories' => ee()->input->post('can_delete_categories'),
				));
			break;
			case 'delete_category_group' : 
				$reponse = ee()->entry_api_public_methods->delete_category_group(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'group_id' => ee()->input->post('group_id'),
				));
			break;

			//Category
			case 'create_category' : 
				$reponse = ee()->entry_api_public_methods->create_category(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'group_id' => ee()->input->post('group_id'),
					'parent_id' => ee()->input->post('parent_id'),
					'cat_name' => ee()->input->post('cat_name'),
					'cat_description' => ee()->input->post('cat_description'),
					'custom_field' => ee()->input->post('custom_field'),
				));
			break;
			case 'read_category' : 
				$reponse = ee()->entry_api_public_methods->read_category(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'cat_id' => ee()->input->post('cat_id'),
				));
			break;
			case 'update_category' : 
				$reponse = ee()->entry_api_public_methods->update_category(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'cat_id' => ee()->input->post('cat_id'),
					'group_id' => ee()->input->post('group_id'),
					'parent_id' => ee()->input->post('parent_id'),
					'cat_name' => ee()->input->post('cat_name'),
					'cat_url_title' => ee()->input->post('cat_url_title'),
					'cat_order' => ee()->input->post('cat_order'),
					'cat_description' => ee()->input->post('cat_description'),
					'custom_field' => ee()->input->post('custom_field'),
				));
			break;
			case 'delete_category' : 
				$reponse = ee()->entry_api_public_methods->delete_category(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'cat_id' => ee()->input->post('cat_id'),
				));
			break;
			
			//Channel
			case 'create_channel' : 
				$reponse = ee()->entry_api_public_methods->create_channel(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'site_id'   => ee()->input->post('site_id'),
					'channel_title'   => ee()->input->post('channel_title'),
					'channel_name'   => ee()->input->post('channel_name'),
					'url_title_prefix'   => ee()->input->post('url_title_prefix'),
					'comment_expiration'   => ee()->input->post('comment_expiration'),
					//'dupe_id'   => ee()->input->post('dupe_id'),
					'status_group'   => ee()->input->post('status_group'),
					'field_group'   => ee()->input->post('field_group'),
					'channel_url'   => ee()->input->post('channel_url'),
					'channel_lang'   => ee()->input->post('channel_lang'),
					'group_order'   => ee()->input->post('group_order'),
				));
			break;
			case 'read_channel' : 
				$reponse = ee()->entry_api_public_methods->read_channel(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'channel_id' => ee()->input->post('channel_id'),
				));
			break;
			case 'update_channel' : 
				$reponse = ee()->entry_api_public_methods->update_channel(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'channel_id' => ee()->input->post('channel_id'),
					'site_id'   => ee()->input->post('site_id'),
					'channel_title'   => ee()->input->post('channel_title'),
					'channel_name'   => ee()->input->post('channel_name'),
					'url_title_prefix'   => ee()->input->post('url_title_prefix'),
					'comment_expiration'   => ee()->input->post('comment_expiration'),
					//'dupe_id'   => ee()->input->post('dupe_id'),
					'status_group'   => ee()->input->post('status_group'),
					'field_group'   => ee()->input->post('field_group'),
					'channel_url'   => ee()->input->post('channel_url'),
					'channel_lang'   => ee()->input->post('channel_lang'),
					'group_order'   => ee()->input->post('group_order'),
				));
			break;
			case 'delete_channel' : 
				$reponse = ee()->entry_api_public_methods->delete_channel(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'channel_id' => ee()->input->post('channel_id'),
				));
			break;
			
			//Member
			case 'create_member' : 
				$reponse = ee()->entry_api_public_methods->create_member(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'username'   => ee()->input->post('username'),
					'password'   => ee()->input->post('password'),
					'password_confirm'   => ee()->input->post('password_confirm'),
					'screen_name'   => ee()->input->post('screen_name'),
					'email'   => ee()->input->post('email'),
					'group_id'   => ee()->input->post('group_id'),
					'bday_y'   => ee()->input->post('bday_y'),
					'bday_m'   => ee()->input->post('bday_m'),
					'bday_d'   => ee()->input->post('bday_d'),
					'url'   => ee()->input->post('url'),
					'location'   => ee()->input->post('location'),
					'occupation'   => ee()->input->post('occupation'),
					'interests'   => ee()->input->post('interests'),
					'aol_im'   => ee()->input->post('aol_im'),
					'icq'   => ee()->input->post('icq'),
					'yahoo_im'   => ee()->input->post('yahoo_im'),
					'msn_im'   => ee()->input->post('msn_im'),
					'bio'   => ee()->input->post('bio'),
				));
			break;
			case 'read_member' : 
				$reponse = ee()->entry_api_public_methods->read_member(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'member_id' => ee()->input->post('member_id'),
				));
			break;
			case 'update_member' : 
				$reponse = ee()->entry_api_public_methods->update_member(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'member_id'   => ee()->input->post('member_id'),
					'username'   => ee()->input->post('username'),
					'password'   => ee()->input->post('password'),
					'password_confirm'   => ee()->input->post('password_confirm'),
					'screen_name'   => ee()->input->post('screen_name'),
					'email'   => ee()->input->post('email'),
					'group_id'   => ee()->input->post('group_id'),
					'bday_y'   => ee()->input->post('bday_y'),
					'bday_m'   => ee()->input->post('bday_m'),
					'bday_d'   => ee()->input->post('bday_d'),
					'url'   => ee()->input->post('url'),
					'location'   => ee()->input->post('location'),
					'occupation'   => ee()->input->post('occupation'),
					'interests'   => ee()->input->post('interests'),
					'aol_im'   => ee()->input->post('aol_im'),
					'icq'   => ee()->input->post('icq'),
					'yahoo_im'   => ee()->input->post('yahoo_im'),
					'msn_im'   => ee()->input->post('msn_im'),
					'bio'   => ee()->input->post('bio'),

				));
			break;
			case 'delete_member' : 
				$reponse = ee()->entry_api_public_methods->delete_member(array(
					'session_id' => ee()->session->userdata('session_id')
				), array(
					'member_id' => ee()->input->post('member_id'),
				));
			break;
		}

		return $reponse;

	}

}