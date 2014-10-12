<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Adman API
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

class Entry_api_adman
{	
	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		ee()->load->add_package_path(PATH_THIRD . 'adman/');
        ee()->lang->loadfile('adman');
		

		//require the default settings
        require PATH_THIRD.'entry_api/settings.php';
	}

	//-------------------------------------------------------------------------

	/**
	 * Show an ad
	 * 
	 * @param  string $auth 
	 * @param  array  $post_data 
	 * @return array            
	 */
	public function show_adman($post_data = array())
	{
		//defaults
		$post_data = array_merge(array(
			'limit' => 10,
			'group' => '',
			'order' => 'asc'
		), $post_data);
		
		/* -------------------------------------------
		/* 'entry_api_show_adman_start' hook.
		/*  - Added: 3.5.0
		*/
		$post_data = Entry_api_helper::add_hook('show_adman_start', $post_data);
		/** ---------------------------------------*/

		/** ---------------------------------------
		/**  Validate data
		/** ---------------------------------------*/
		$data_errors = array();

		/** ---------------------------------------
		/**  Set the site_id is empty
		/** ---------------------------------------*/
		if(!isset($post_data['site_id']) || $post_data['site_id'] == '') {
			$post_data['site_id'] = 1;
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

		$return = $this->_show($post_data['limit'], $post_data['group'], $post_data['order']);

		/* -------------------------------------------
		/* 'entry_api_show_adman_end' hook.
		/*  - Added: 3.5.0
		*/
		Entry_api_helper::add_hook('show_adman_end', $return);
		/** ---------------------------------------*/

		//no result?
		if($return === false)
		{
			//generate error
			return $this->service_error['error_no_adman'];
		}
	
		/** ---------------------------------------
		/** return response
		/** ---------------------------------------*/
		$this->service_error['succes_read']['data'] = $return;
		return $this->service_error['succes_read'];
	}

	//-------------------------------------------------------------------------

	//Code copy from the mod.adman.php
	private function _show($limit = 10, $group = '', $order = 'asc')
	{

		ee()->load->model('adman_core');

		$settings = ee()->adman_core->_get_settings();

		$group = explode("|",$group);

		ee()->db->select("a.*,b.*,c.*");
		ee()->db->from("adman_groups a");
		ee()->db->join("adman_adgroups b","a.group_id = b.group_id");
		ee()->db->join("adman_ads c","c.ad_id = b.ad_id");
		foreach($group as $item)
		{
			ee()->db->or_where("a.group_url",$item);
		}
		ee()->db->where("now() between c.ad_start and c.ad_end");

		if ($order=="RANDOM")
		{
			ee()->db->order_by("RAND()");
		}

		if ($order=="SORT_ORDER")
		{
			ee()->db->order_by("b.sort_order","asc");
		}


		if ($limit!="")
		{
			ee()->db->limit($limit);
		}

		$q = ee()->db->get();

		$rows = $q->result_array();

		if (empty($rows)) { 
			return false;
		}

		$i = 0;

		foreach($rows as $item)
		{

			$max_click = $item['ad_max_click'];
			$max_imp = $item['ad_max_impression'];

			if ($max_click != 0)
			{
				ee()->db->select("sum(click) as clickcount")
					->from("adman_stats")
					->where("ad_id",$item['ad_id']);
					
				$q = ee()->db->get();
				$row = $q->row();

				$clickcount = $row->clickcount;

				if ($max_click == $clickcount)
				{
					unset($rows[$i]);
				}
			}
			elseif ($max_imp != 0)
			{
				ee()->db->select("sum(impression) as imp_count")
						->from("adman_stats")
						->where("ad_id",$item['ad_id']);
						
				$q = ee()->db->get();
				$row = $q->row();

				$imp_count = $row->imp_count;

				if ($max_imp == $imp_count)
				{
					unset($rows[$i]);
				}
			}

			$i++;		
		}

		$data = array();

		foreach($rows as $row)
		{
			$row['total_results'] = count($rows);

			$row['ad_url'] = ee()->functions->fetch_site_index(0,0).QUERY_MARKER.'ACT='.$this->get_aid('Adman_mcp', 'log_click').AMP.'ad_id='.$row['ad_id'];

			$ext = explode(".",$row['ad_image']);
				
			if (isset($ext[1]))
			{
				if ($ext[1]=="swf")
				{
					$row['is_flash'] = 1;
				}
				else
				{
					$row['is_flash'] = 0;
				}
			}

			if ((!is_null($row['ad_image'])) && ($row['ad_image'] !=""))
			{
				$row['ad_image'] = $settings['adman_url'].$row['ad_image'];
			}

			$data[] = $row;

			$this->_log_imp($row['ad_id']);
		}

		return $data;
	}

	//-------------------------------------------------------------------------

	//Code copy from the mod.adman.php
	public function _switch_explode($match)
	{
		$options = explode('|', $match[2]);
		$option = $this->switchcount % count($options);
		$this->switchcount++;
		return $options[$option];
	}

	//-------------------------------------------------------------------------
  	
  	//Code copy from the mod.adman.php
  	function _log_imp($ad_id)
  	{
  		
  		if ( $this->_bot_check($_SERVER['HTTP_USER_AGENT']) ) { return; }
  		
  		$chk = ee()->db->query("select * from exp_adman_stats where ad_id = ".$ad_id." and dte_mn = month(current_date()) and dte_yr = year(current_date())")->result_array();
  		
  		if (empty($chk))
  		{
	  		//Insert a new record
	  		$data = array(
	  			'dte_mn' => date('n'),
	  			'dte_yr' => date('Y'),
	  			'ad_id'	 => $ad_id,
	  			'impression' => 1,
	  			'click' => 0
	  		);
	  		
	  		ee()->db->insert('adman_stats',$data);
  		}
  		else
  		{
	  		//Update the existing record
	  		ee()->db->query("update exp_adman_stats set impression=impression+1 where ad_id = ".$ad_id." and dte_mn = month(current_date()) and dte_yr = year(current_date())");
  		}
  		
  	}

  	//-------------------------------------------------------------------------
  	
  	//Code copy from the mod.adman.php
  	function _bot_check($user_agent){
	  	$botagents = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby'; 
		
		$agent_array = explode("|",$botagents);
		
		foreach ($agent_array as $agent)
		{
			if ( strpos($agent, $user_agent) === true )
			{
				return true;
			}
		}       
  	}
  	
  	//-------------------------------------------------------------------------
  	
  	//Code copy from the mod.adman.php
  	function _get_settings(){
		$this->EE =& get_instance();
		
		ee()->db->select("settings");
		ee()->db->from("extensions");
		ee()->db->where("class","Adman_ext");
		
		$q = ee()->db->get();
		$row = $q->row();
		
		return strip_slashes(unserialize($row->settings));
	}
  	
  	//-------------------------------------------------------------------------
  	
  	//Code copy from the mod.adman.php
  	function get_aid($class,$method){
		ee()->db->select('action_id')
				->from('actions')
				->where('class',$class)
				->where('method',$method);
		$query = ee()->db->get();
		if($query->num_rows() > 0){
		   $row = $query->row(); 
		   return $row->action_id;
		}
		return 0;
	}

}

