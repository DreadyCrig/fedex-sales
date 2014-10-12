<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Default model
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

class Entry_api_model
{

	private $EE;

	public function __construct()
	{					
		//load other models
		ee()->load->model('entry_api_category_model');

		// Creat EE Instance
		//$this->EE =& get_instance();

		//load the helper
		//ee()->load->helper(DEFAULT_MAP.'_helper');
	}

	// --------------------------------------------------------------------

	/**
	 * Log item
	 *
	 * @access	public
	 * @return	array
	 */
	public function add_log($username = '', $data = array(), $method = '', $service = '', $servicedata)
	{
		$need_to_log = false;

		//global error?
		if(!isset($servicedata->logging) && ee()->entry_api_settings->item('debug') == 1)
		{
			$need_to_log = true;
		}

		//no global logging
		else if(!isset($servicedata->logging))
		{
			$need_to_log = false;
		}

		//log all
		else if($servicedata->logging == 2)
		{
			$need_to_log = true;
		}

		//only success
		else if($servicedata->logging == 1 && $data['code_http'] == 200)
		{
			$need_to_log = true;
		}

		//log
		if($need_to_log)
		{
			ee()->db->insert('entry_api_logs', array(
				'site_id' => ee()->config->item('site_id'),
				'time' => ee()->localize->now,
				'username' => $username,
				'service' => $service,
				'ip' => ee()->input->ip_address(),
				'log_number' => (isset($data['code']) ? $data['code'] : ''),
				'msg' => (isset($data['message']) ? $data['message'] : ''),
				'method' => $method,
				'total_queries' => count(ee()->db->queries),
				'queries' => serialize(ee()->db->queries),
				'data' => serialize($data)
			));	
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Cout all itemst
	 *
	 * @access	public
	 * @return	array
	 */
	public function count_logs()
	{
		$q = ee()->db->get(ENTRY_API_MAP.'_logs');
		return $q->num_rows();
	}

	// --------------------------------------------------------------------

	/**
	 * Get all aliases
	 *
	 * @access	public
	 * @return	void
	 */
	public function get_all_logs($log_id = '', $start = 0, $limit = false, $order = array())
	{
		$results = array();
		$q = '';

		//get all alias for an specific site_id
		if($log_id == '')
		{
			ee()->db->select('*');
			ee()->db->from(ENTRY_API_MAP.'_logs');
			ee()->db->where('site_id', ee()->config->item('site_id'));
		}

		//Fetch a list of entries in array
		else if(is_array($log_id) && !empty($log_id))
		{
			ee()->db->select('*');
			ee()->db->from(ENTRY_API_MAP.'_logs');
			ee()->db->where('site_id', ee()->config->item('site_id'));
			ee()->db->where_in('log_id', $log_id);
		}

		//fetch only the alias for an log_id
		else if(!is_array($log_id))
		{
			ee()->db->select('*');
			ee()->db->from(ENTRY_API_MAP.'_logs');
			ee()->db->where('site_id', ee()->config->item('site_id'));
			ee()->db->where('log_id', $log_id);
		}

		//do nothing
		else
		{
			return array();
		}

		//is there a start and limit
		if($limit !== false)
		{
			ee()->db->limit($start, $limit);
		}

		//do we need to order
		//given by the mcp table method http://ellislab.com/expressionengine/user-guide/development/usage/table.html
		if(!empty($order))
		{
			if(isset($order[ENTRY_API_MAP.'_log_id']))
			{
				ee()->db->order_by('log_id', $order[ENTRY_API_MAP.'_log_id']);	
			}
			if(isset($order[ENTRY_API_MAP.'_username']))
			{
				ee()->db->order_by('username', $order[ENTRY_API_MAP.'_username']);	
			}
			if(isset($order[ENTRY_API_MAP.'_method']))
			{
				ee()->db->order_by('method', $order[ENTRY_API_MAP.'_method']);	
			}
			if(isset($order[ENTRY_API_MAP.'_log_number']))
			{
				ee()->db->order_by('log_number', $order[ENTRY_API_MAP.'_log_number']);	
			}
			if(isset($order[ENTRY_API_MAP.'_service']))
			{
				ee()->db->order_by('service', $order[ENTRY_API_MAP.'_service']);	
			}
			if(isset($order[ENTRY_API_MAP.'_op']))
			{
				ee()->db->order_by('ip', $order[ENTRY_API_MAP.'_ip']);	
			}
		}
		else
		{
			ee()->db->order_by('log_id', 'desc');	
		}
		
		//get the result
		$q = ee()->db->get();

		//format result
		if($q != '' && $q->num_rows())
		{
			foreach($q->result() as $val)
			{
				$results[] = $val;
			}
		}

		return $results;
	}

	// --------------------------------------------------------------------

	/**
	 * Cout all itemst
	 *
	 * @access	public
	 * @return	array
	 */
	public function count_api_keys()
	{
		$q = ee()->db->get(ENTRY_API_MAP.'_keys');
		return $q->num_rows();
	}

	// --------------------------------------------------------------------

	/**
	 * Get all aliases
	 *
	 * @access	public
	 * @return	void
	 */
	public function get_all_api_keys($api_key_id = '', $start = 0, $limit = false, $order = array())
	{
		$results = array();
		$q = '';

		//get all alias for an specific site_id
		if($api_key_id == '')
		{
			ee()->db->select('*');
			ee()->db->from(ENTRY_API_MAP.'_keys');
			ee()->db->where('site_id', ee()->config->item('site_id'));
		}

		//Fetch a list of entries in array
		else if(is_array($api_key_id) && !empty($api_key_id))
		{
			ee()->db->select('*');
			ee()->db->from(ENTRY_API_MAP.'_keys');
			ee()->db->where('site_id', ee()->config->item('site_id'));
			ee()->db->where_in('api_key_id', $api_key_id);
		}

		//fetch only the alias for an log_id
		else if(!is_array($api_key_id))
		{
			ee()->db->select('*');
			ee()->db->from(ENTRY_API_MAP.'_keys');
			ee()->db->where('site_id', ee()->config->item('site_id'));
			ee()->db->where('api_key_id', $api_key_id);
		}

		//do nothing
		else
		{
			return array();
		}

		//is there a start and limit
		if($limit !== false)
		{
			ee()->db->limit($start, $limit);
		}

		//do we need to order
		//given by the mcp table method http://ellislab.com/expressionengine/user-guide/development/usage/table.html
		if(!empty($order))
		{
			if(isset($order[ENTRY_API_MAP.'_api_key_id']))
			{
				ee()->db->order_by('api_key_id', $order[ENTRY_API_MAP.'_api_key_id']);	
			}
			if(isset($order[ENTRY_API_MAP.'_api_key']))
			{
				ee()->db->order_by('api_key', $order[ENTRY_API_MAP.'_api_key']);	
			}
		}
		else
		{
			ee()->db->order_by('api_key_id', 'desc');	
		}
		
		//get the result
		$q = ee()->db->get();

		//format result
		if($q != '' && $q->num_rows())
		{
			foreach($q->result() as $val)
			{
				$results[] = $val;
			}
		}

		return $results;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * user exists
	 * 
	 * @param none
	 * @return void
	 */
	public function user_exists($username = '')
	{
		$user = $this->get_member_based_on_username($username);
		
		if(!isset($user->member_id))
		{	
			return false;
		}

		return $user->member_id == '' ? false : true;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the member data
	 * 
	 * @param none
	 * @return void
	 */
	public function get_channel_names()
	{
		ee()->db->select('channel_name');
		ee()->db->from('channels');
		$result = ee()->db->get();

		$return = array();

		if($result->num_rows() > 0)
		{
			foreach($result->result() as $row)
			{
				$return[$row->channel_name] = $row->channel_name;
			}
		}

		return $return;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the Raw member data
	 * 
	 * @param none
	 * @return void
	 */
	public function get_raw_members()
	{	
		//get the channels
		ee()->db->select('member_id, username');
		ee()->db->from('members');
		ee()->db->where_not_in('member_id', array(1));
		$query = ee()->db->get();

		$members = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$members[] = $val;
			}
		}
		return $members;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the Raw member data
	 * 
	 * @param none
	 * @return void
	 */
	public function get_raw_selected_members()
	{	
		//get the channels
		ee()->db->select('members.member_id, members.username');
		ee()->db->from('members');
		ee()->db->join('entry_api_services_settings', 'members.member_id = entry_api_services_settings.member_id', 'left');
		ee()->db->where('entry_api_services_settings.member_id !=', 'null');
		$query = ee()->db->get();

		$members = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$members[$val->member_id] = $val;
			}
		}
		return $members;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the Raw member data
	 * 
	 * @param none
	 * @return void
	 */
	public function get_raw_membergroups()
	{	
		//get the channels
		ee()->db->select('group_id, group_title');
		ee()->db->from('member_groups');
		ee()->db->where_not_in('group_title', array('Super Admins', 'Banned', 'Guests', 'Pending', 'Members'));
		$query = ee()->db->get();

		$membergroups = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$membergroups[] = $val;
			}
		}
		return $membergroups;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the Raw member data
	 * 
	 * @param none
	 * @return void
	 */
	public function get_raw_selected_membergroups()
	{	
		//get the channels
		ee()->db->select('member_groups.group_id, member_groups.group_title');
		ee()->db->from('member_groups');
		ee()->db->join('entry_api_services_settings', 'member_groups.group_id = entry_api_services_settings.membergroup_id', 'left');
		ee()->db->where('entry_api_services_settings.membergroup_id !=', 'null');
		$query = ee()->db->get();

		$member_groups = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$member_groups[$val->group_id] = $val;
			}
		}
		return $member_groups;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the channels
	 * 
	 * @param none
	 * @return void
	 */
	public function get_channels()
	{	
		//get the channels
		ee()->db->select('channel_id, channel_name');
		ee()->db->from('channels');
		$query = ee()->db->get();

		$channels = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$channels[] = $val;
			}
		}
		return $channels;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the member data
	 * 
	 * @param none
	 * @return void
	 */
	public function get_members()
	{	
		//get the channels
		ee()->db->select('members.group_id, members.member_id, members.username, members.screen_name, entry_api_services_settings.*');
		ee()->db->from('members');
		ee()->db->join('entry_api_services_settings', 'members.member_id = entry_api_services_settings.member_id', 'right');
		$query = ee()->db->get();

		$members = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$members[] = $val;
			}
		}
		return $members;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get channel data
	 * @deprecated
	 * @param none
	 * @return void
	 */
	public function get_member($member_id = 0)
	{	
		//get the channels
		ee()->db->select('*');
		ee()->db->from('members');
		ee()->db->where('member_id', $member_id);
		
		$query = ee()->db->get();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			$member = $query->row();
			/*$channel->entry_statuses = $this->get_statuses($channel->status_group);
			$channel->entry_status = $channel->entry_status != '' ? $channel->entry_status : $channel->deft_status ;*/
			return $member;
		}
		return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get channel data
	 * @deprecated
	 * @param none
	 * @return void
	 */
	public function get_membergroup($membergroup_id = 0)
	{	
		//get the channels
		ee()->db->select('*');
		ee()->db->from('member_groups');
		ee()->db->where('group_id', $membergroup_id);
		
		$query = ee()->db->get();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			$member = $query->row();
			/*$channel->entry_statuses = $this->get_statuses($channel->status_group);
			$channel->entry_status = $channel->entry_status != '' ? $channel->entry_status : $channel->deft_status ;*/
			return $member;
		}
		return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get channel data
	 * @deprecated
	 * @param none
	 * @return void
	 */
	public function get_entry_api_users()
	{	
		//get the channels
		ee()->db->select('*');
		ee()->db->from('entry_api_services_settings');
		
		$query = ee()->db->get();

		$members = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach($query->result() as $row)
			{
				$members[] = $row;
			}
		}
		return $members;
	}


	// ----------------------------------------------------------------------
	
	/**
	 * get channel data
	 * @deprecated
	 * @param none
	 * @return void
	 */
	public function get_entry_api_user($entry_api_id = '')
	{	
		//get the channels
		ee()->db->select('*');
		ee()->db->from('entry_api_services_settings');
		
		//build where query
		$where = array();
		$where['entry_api_id'] = $entry_api_id;
		
		ee()->db->where($where);
		$query = ee()->db->get();

		$member = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			$member = $query->row();
			/*$channel->entry_statuses = $this->get_statuses($channel->status_group);
			$channel->entry_status = $channel->entry_status != '' ? $channel->entry_status : $channel->deft_status ;*/
			return $member;
		}
		return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get channel data
	 * @deprecated
	 * @param none
	 * @return void
	 */
	public function get_member_based_on_username($username = '')
	{	
		//get the channels
		ee()->db->select('members.*, exp_entry_api_services_settings.*');
		ee()->db->from('members');
		ee()->db->join('exp_entry_api_services_settings', 'members.member_id = exp_entry_api_services_settings.member_id', 'left');
		
		//build where query
		$where = array();
		$where['members.username'] = $username;

		
		ee()->db->where($where);
		$query = ee()->db->get();

		$member = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			$member = $query->row();
			/*$channel->entry_statuses = $this->get_statuses($channel->status_group);
			$channel->entry_status = $channel->entry_status != '' ? $channel->entry_status : $channel->deft_status ;*/
			return $member;
		}
		return '';
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the Raw channels data
	 * @deprecated
	 * @param none
	 * @return void
	 */
	public function get_raw_channels()
	{	
		//get the channels
		ee()->db->select('channels.channel_id, channels.channel_name, channels.channel_title');
		ee()->db->from('channels');
		$query = ee()->db->get();

		$channels = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$channels[] = $val;
			}
		}
		return $channels;
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get the channels data
	 *
	 * @param none
	 * @return void
	 */
	public function get_statuses($group_id = '')
	{
		//get the channels
		ee()->db->select('status');
		ee()->db->from('statuses');
		ee()->db->where('group_id', $group_id);
		$query = ee()->db->get();

		$statuses = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$statuses[$val->status] = $val->status;
			}			
		}
		
		return $statuses;

		
	}

	// ----------------------------------------------------------------------
	
	/**
	 * get channel data
	 * @deprecated
	 * @param none
	 * @return void
	 */
	public function get_channel($entry_api_id = '')
	{	
		//get the channels
		ee()->db->select('entry_api_channel_settings.entry_api_id, entry_api_channel_settings.type, entry_api_channel_settings.logging, channels.status_group, channels.deft_status, entry_api_channel_settings.entry_status, entry_api_channel_settings.active, entry_api_channel_settings.data, channels.channel_id, channels.channel_name, channels.channel_title');
		ee()->db->from('channels');
		ee()->db->join('entry_api_channel_settings', 'channels.channel_id = entry_api_channel_settings.channel_id', 'left');
		
		//build where query
		$where = array();
		$where['entry_api_channel_settings.entry_api_id'] = $entry_api_id;

		
		ee()->db->where($where);
		$query = ee()->db->get();

		$channel = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			$channel = $query->row();
			$channel->entry_statuses = $this->get_statuses($channel->status_group);
			$channel->entry_status = $channel->entry_status != '' ? $channel->entry_status : $channel->deft_status ;
			return $channel;
		}
		return '';
	}

	// ----------------------------------------------------------------------
		
	/**
	 * 	get the channels based on the member
	 *
	 * 	@access public
	 *	@param string
	 * 	@param string
	 *	@return mixed
	 */
	public function get_channels_for_member($member_id)
	{
		//get the member
		ee()->db->select('group_id');
		ee()->db->from('members');
		$query = ee()->db->get();
		$result = $query->row();

		//is super admin
		if($result->group_id == 1) 
		{
			ee()->db->select('channels.channel_name, channels.channel_id');
			ee()->db->from('channels');
			$query = ee()->db->get();
		}

		//normal user
		else
		{
			ee()->db->select('channels.channel_name, channels.channel_id');
			ee()->db->where('channel_member_groups.group_id', $result->group_id);
			ee()->db->from('channel_member_groups');
			ee()->db->join('channels', 'channels.channel_id = channel_member_groups.channel_id', 'right');
			$query = ee()->db->get();
		}
		
		$channels = array();
		
		//format a array
		if ($query->num_rows() > 0)
		{	
			foreach ($query->result() as $val)
			{
			
				$channels[$val->channel_id] = $val->channel_name;
			}
		}
		return $channels;
	}

	// ----------------------------------------------------------------------
		
	/**
	 * 	Save keys
	 *
	 * 	@access public
	 *	@param string
	 *	@return mixed
	 */
	public function save_keys($id, $keys)
	{
		$keys = array_filter($keys);
		
		//return always true, except when there are duplicate keys
		$data = array('duplicated' => array(), 'keys' => array());
		
		if(!empty($keys) && is_array($keys))
		{
			//delete old ones
			ee()->db->where('entry_api_id', $id);
			ee()->db->delete('entry_api_keys');
			
			foreach($keys as $key)
			{
				//look if the key already exists
				ee()->db->where('api_key', $key);
				$check = ee()->db->get('entry_api_keys');
				
				if($check->num_rows > 0)
				{
					$data['duplicated'][] = $key;
					continue;
				}
				
				//insert this one
				ee()->db->insert('entry_api_keys', array(
					'api_key' => $key,
					'entry_api_id' => $id,
					'site_id' => ee()->config->item('site_id'),
				));
				
				//save
				$data['keys'][] = $key;
			}
		}
		
		return $data;
	}

	// ----------------------------------------------------------------------
		
	/**
	 * 	Search API key
	 *
	 * 	@access public
	 *	@param string
	 *	@return mixed
	 */
	public function search_api_key($api_key = '')
	{
		//empty, shame on you!
		if($api_key == '' || empty($api_key))
		{
			return false;
		}

		//get the member
		ee()->db->where('entry_api_keys.api_key', $api_key);
		ee()->db->from('entry_api_keys');
		ee()->db->join('entry_api_services_settings', 'entry_api_services_settings.entry_api_id = entry_api_keys.entry_api_id');
		ee()->db->limit(1);

		$query = ee()->db->get();
		
		$api_keys = array();

		if($query->num_rows() > 0)
		{
			return $query->row();
		}
		
		return false;
	}


	// ----------------------------------------------------------------------
		
	/**
	 * 	get username for member_id
	 *
	 * 	@access public
	 *	@param string
	 *	@return mixed
	 */
	public function get_username($member_id = 0)
	{
		//get the member
		ee()->db->select('username');
		ee()->db->from('members');
		ee()->db->where('member_id', $member_id);
		$query = ee()->db->get();
		if($query->num_rows() > 0)
		{
		 	return $query->row()->username;
		}
		return '';
	}

	// ----------------------------------------------------------------------
		
	/**
	 * 	get username for member_id
	 *
	 * 	@access public
	 *	@param string
	 *	@return mixed
	 */
	public function get_membergroup_title($membergroup_id = 0)
	{
		//get the member
		ee()->db->select('group_title');
		ee()->db->from('member_groups');
		ee()->db->where('group_id', $membergroup_id);
		$query = ee()->db->get();
		if($query->num_rows() > 0)
		{
		 	return $query->row()->group_title;
		}
		return '';
	}

} // END CLASS

/* End of file default_model.php  */
/* Location: ./system/expressionengine/third_party/default/models/default_model.php */