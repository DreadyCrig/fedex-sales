<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Test API
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

class Entry_api_config
{
	//-------------------------------------------------------------------------

	/**
     * Constructor
    */
	public function __construct()
	{
		$this->init_configs();

		//require the default settings
        require PATH_THIRD.'entry_api/settings.php';
	}

	//-------------------------------------------------------------------------

	/**
     * read_config method
    */
	public function read_config($post_data = array())
	{

		/** ---------------------------------------
		/**  key is for a insert always required
		/** ---------------------------------------*/
		$data_errors = array();
		if(!isset($post_data['key']) || $post_data['key'] == '') {
			$data_errors[] = 'key';
		}

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

		/** ---------------------------------------
		/**  Check if the site_id exists
		/** ---------------------------------------*/
		if($post_data['site_id'] != ee()->config->item('site_id')) {
			//generate error
			$this->service_error['error_field_single']['message'] .= 'site_id does not exists';
			return $this->service_error['error_field_single'];
		}

		/** ---------------------------------------
		/**  Check if this config is readable
		/** ---------------------------------------*/
		if(!in_array($post_data['key'], $this->config_read)) {
			//generate error
			$this->service_error['error_field_single']['message'] .= $post_data['key'].' is a private config';
			return $this->service_error['error_field_single'];
		}

		$this->service_error['succes_read']['data'][0] = array($post_data['key'] => ee()->config->item($post_data['key']));
		return $this->service_error['succes_read'];
	}

	//-------------------------------------------------------------------------

	/**
     * read_config method
    */
	public function update_config($post_data = array())
	{

		/** ---------------------------------------
		/**  key is for a insert always required
		/** ---------------------------------------*/
		$data_errors = array();
		if(!isset($post_data['key']) || $post_data['key'] == '') {
			$data_errors[] = 'key';
		}
		if(!isset($post_data['value']) || $post_data['value'] == '') {
			$data_errors[] = 'value';
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
		/**  Set the site_id is empty
		/** ---------------------------------------*/
		if(!isset($post_data['site_id']) || $post_data['site_id'] == '') {
			$post_data['site_id'] = 1;
		}

		/** ---------------------------------------
		/**  Check if the site_id exists
		/** ---------------------------------------*/
		if($post_data['site_id'] != ee()->config->item('site_id')) {
			//generate error
			$this->service_error['error_field_single']['message'] .= 'site_id does not exists';
			return $this->service_error['error_field_single'];
		}

		/** ---------------------------------------
		/**  Check if this config is readable
		/** ---------------------------------------*/
		if(!in_array($post_data['key'], $this->config_update)) {
			//generate error
			$this->service_error['error_field_single']['message'] .= $post_data['key'].' is a private config and cannot be updated';
			return $this->service_error['error_field_single'];
		}

		//update
		$updated = ee()->config->update_site_prefs(array($post_data['key'] => $post_data['value']), array($post_data['site_id']));

		//$this->service_error['succes_update']['id'] = $entry_data['entry_id'];
		return $this->service_error['succes_update'];
	}

	//init the config 
	function init_configs()
	{
		$this->config_read = array(
			'is_site_on',
			'site_index',
			'site_url',
			'cp_url',
			'theme_folder_url',
			'theme_folder_path',
			'webmaster_email',
			'webmaster_name',
			'channel_nomenclature',
			'max_caches',
			'cache_driver',
			'captcha_url',
			'captcha_path',
			'captcha_font',
			'captcha_rand',
			'captcha_require_members',
			'enable_db_caching',
			'enable_sql_caching',
			'force_query_string',
			'show_profiler',
			'template_debugging',
			'include_seconds',
			'cookie_domain',
			'cookie_path',
			'website_session_type',
			'cp_session_type',
			'allow_username_change',
			'allow_multi_logins',
			'password_lockout',
			'password_lockout_interval',
			'require_ip_for_login',
			'require_ip_for_posting',
			'require_secure_passwords',
			'allow_dictionary_pw',
			'name_of_dictionary_file',
			'xss_clean_uploads',
			'redirect_method',
			'deft_lang',
			'xml_lang',
			'send_headers',
			'gzip_output',
			'log_referrers',
			'max_referrers',
			'default_site_timezone',
			'date_format',
			'time_format',
			'include_seconds',
			'mail_protocol',
			'smtp_server',
			'smtp_port',
			'smtp_username',
			'smtp_password',
			'email_debug',
			'email_charset',
			'email_batchmode',
			'email_batch_size',
			'mail_format',
			'word_wrap',
			'email_console_timelock',
			'log_email_console_msgs',
			'cp_theme',
			'email_module_captchas',
			'log_search_terms',
			'deny_duplicate_data',
			'redirect_submitted_links',
			'enable_censoring',
			'censored_words',
			'censor_replacement',
			'banned_ips',
			'banned_emails',
			'banned_usernames',
			'banned_screen_names',
			'ban_action',
			'ban_message',
			'ban_destination',
			'enable_emoticons',
			'emoticon_url',
			'recount_batch_total',
			'new_version_check',
			'enable_throttling',
			'banish_masked_ips',
			'max_page_loads',
			'time_interval',
			'lockout_time',
			'banishment_type',
			'banishment_url',
			'banishment_message',
			'enable_search_log',
			'max_logged_searches',
			'rte_enabled',
			'rte_default_toolset_id'
		);

		$this->config_update = array(
			'is_site_on',
			'site_index',
			'site_url',
			'cp_url',
			'theme_folder_url',
			'theme_folder_path',
			'webmaster_email',
			'webmaster_name',
			'channel_nomenclature',
			'max_caches',
			'cache_driver',
			'captcha_url',
			'captcha_path',
			'captcha_font',
			'captcha_rand',
			'captcha_require_members',
			'enable_db_caching',
			'enable_sql_caching',
			'force_query_string',
			'show_profiler',
			'template_debugging',
			'include_seconds',
			'cookie_domain',
			'cookie_path',
			'website_session_type',
			'cp_session_type',
			'allow_username_change',
			'allow_multi_logins',
			'password_lockout',
			'password_lockout_interval',
			'require_ip_for_login',
			'require_ip_for_posting',
			'require_secure_passwords',
			'allow_dictionary_pw',
			'name_of_dictionary_file',
			'xss_clean_uploads',
			'redirect_method',
			'deft_lang',
			'xml_lang',
			'send_headers',
			'gzip_output',
			'log_referrers',
			'max_referrers',
			'default_site_timezone',
			'date_format',
			'time_format',
			'include_seconds',
			'mail_protocol',
			'smtp_server',
			'smtp_port',
			'smtp_username',
			'smtp_password',
			'email_debug',
			'email_charset',
			'email_batchmode',
			'email_batch_size',
			'mail_format',
			'word_wrap',
			'email_console_timelock',
			'log_email_console_msgs',
			'cp_theme',
			'email_module_captchas',
			'log_search_terms',
			'deny_duplicate_data',
			'redirect_submitted_links',
			'enable_censoring',
			'censored_words',
			'censor_replacement',
			'banned_ips',
			'banned_emails',
			'banned_usernames',
			'banned_screen_names',
			'ban_action',
			'ban_message',
			'ban_destination',
			'enable_emoticons',
			'emoticon_url',
			'recount_batch_total',
			'new_version_check',
			'enable_throttling',
			'banish_masked_ips',
			'max_page_loads',
			'time_interval',
			'lockout_time',
			'banishment_type',
			'banishment_url',
			'banishment_message',
			'enable_search_log',
			'max_logged_searches',
			'rte_enabled',
			'rte_default_toolset_id'
		);
	}

}

