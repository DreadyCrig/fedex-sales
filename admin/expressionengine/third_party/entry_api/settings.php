<?php
/**
 * the settings for the module
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

//updates
$this->updates = array(
	'2.1',
	'2.2',
	'3.0',
	'3.4.1',
	'3.5'
);

//service methods
$this->services = array(
	'soap' => 'SOAP',
	'xmlrpc' => 'XML-RPC',
	'rest' => 'REST',
	'custom' => 'Custom'
);

//service api
// $this->apis = array(
// 	'entry' => 'Entry',
// 	'category' => 'Category',
// 	'category_group' => 'Category Group',
// 	'channel' => 'Channel',
// 	//'member_group' => 'Member Group',
// 	//'member' => 'Member',
// 	//'settings' => 'Settings',
// 	//'field' => 'Field'
// );

//enabled disables
$this->service_active = array(
	'1' => 'Active',
	'0' => 'Inactive',
);

//enabled disables
$this->service_logging = array(
	'2' => 'All messages',
	'1' => 'Success calls only',
	'0' => 'Nothing',
);

//Debug
$this->service_debug = array(
	'1' => 'Yes',
	'0' => 'No',
);

//Free apis
// $this->free_apis = array(
// 	'read_entry' => 'Read Entry',
// 	'search_entry' => 'Search_entry',
// 	//'read_category' => 'Read Category',
// 	//'read_category_group' => 'Read Category Group',
// );

//Default Post
$this->default_post = array(
	'license_key'   				=> '',
	'report_date' 					=> time(),
	'report_stats' 					=> true,
	'debug'   						=> true,
	'no_inlog_channels' 			=> '',
	'tmp_dir'						=> PATH_THIRD.'entry_api/_tmp',
	'ip_blacklist'					=> '',
	//'ip_whitelist'				=> '',
	'free_apis'						=> serialize(array('')),
	'rest_auth' 					=> 'none',
	'url_trigger'					=> 'entry_api',
	'super_admin_key'				=> '',
	'rest_output_header'			=> ''
);	

// $this->rest_auths = array(
// 	'none',
// 	'basic',
// 	'digest',
// 	'rest_ip_whitelist_enabled'
// );

//set a secret auth token
//$this->secret_auth_token = md5(ee()->config->item('site_url'));

//overrides
$this->overide_settings = array(
	//'gmaps_icon_dir' => '[theme_dir]images/icons/',
	//'gmaps_icon_url' => '[theme_url]images/icons/',
);

// Backwards-compatibility with pre-2.6 Localize class
$this->format_date_fn = (version_compare(APP_VER, '2.6', '>=')) ? 'format_date' : 'decode_date';

//mcp veld header
$this->logs_table_headers = array(
	ENTRY_API_MAP.'_log_id' => array('data' => lang(ENTRY_API_MAP.'_log_id'), 'style' => 'width:10%;'),
	ENTRY_API_MAP.'_time' => array('time' => lang(ENTRY_API_MAP.'_time'), 'style' => 'width:40%;'),
	ENTRY_API_MAP.'_username' => array('data' => lang(ENTRY_API_MAP.'_username'), 'style' => 'width:40%;'),
	ENTRY_API_MAP.'_ip' => array('data' => lang(ENTRY_API_MAP.'_ip'), 'style' => 'width:40%;'),
	ENTRY_API_MAP.'_service' => array('data' => lang(ENTRY_API_MAP.'_service'), 'style' => 'width:40%;'),
	ENTRY_API_MAP.'_method' => array('data' => lang(ENTRY_API_MAP.'_method'), 'style' => 'width:40%;'),
	ENTRY_API_MAP.'_log_number' => array('data' => lang(ENTRY_API_MAP.'_log_number'), 'style' => 'width:40%;'),
	ENTRY_API_MAP.'_msg' => array('data' => lang(ENTRY_API_MAP.'_msg'), 'style' => 'width:40%;'),
);

//mcp veld header
$this->api_keys_table_headers = array(
	//ENTRY_API_MAP.'_api_key_id' => array('data' => lang(ENTRY_API_MAP.'_api_key_id'), 'style' => 'width:10%;'),
	ENTRY_API_MAP.'_api_key' => array('data' => lang(ENTRY_API_MAP.'_api_key'), 'style' => 'width:80%;'),
	ENTRY_API_MAP.'_edit' => array('data' => lang(ENTRY_API_MAP.'_edit'), 'style' => 'width:10%;'),
);

$this->fieldtype_settings = array(
	array(
		'label' => lang('license'),
		'name' => 'license',
		'type' => 't', // s=select, m=multiselect t=text
		//'options' => array('No', 'Yes'),
		'def_value' => '',
		'global' => true, //show on the global settings page
	),

);

//the service errors
$this->service_error = array(
	//success
	'succes_create' => array(
		'response' 			=> 'ok',	
		'message'			=> 'Created successfully',
		'code'				=> 200,
		'code_http'			=> 200,
	),

	'succes_read' => array(
		'response' 			=> 'ok',
		'message'			=> 'Successfully readed',
		'code'				=> 200,
		'code_http'			=> 200,
	),

	'succes_update' => array(
		'response' 			=> 'ok',
		'message'			=> 'Successfully updated',
		'code'				=> 200,
		'code_http'			=> 200,
	),

	'succes_delete' => array(
		'response' 			=> 'ok',
		'message'			=> 'Successfully deleted',
		'code'				=> 200,
		'code_http'			=> 200,
	),

	'succes_auth' => array(
		'response' 			=> 'ok',	
		'message'			=> 'Auth success',
		'code'				=> 200,
		'code_http'			=> 200,
	),

	//-------------------------------------------------------------
	
	//errors API/Services
	'error_access' => array(
		'message'			=> 'You are not authorized to use this service',
		'code'				=> 5201,
		'code_http'			=> 503,
	),
	
	'error_inactive' => array(
		'message'			=> 'Service is not running',
		'code'				=> 5202,
		'code_http'			=> 503,
	),
	
	'error_api' => array(
		'code'				=> 5203, //general api error
		'code_http'			=> 503,
	),

	'error_api_type' => array(
		'message'			=> 'This API is not active for this services',
		'code'				=> 5204,
		'code_http'			=> 503,
	),

	'error_api_ip' => array(
		'message'			=> 'This IP ('.$_SERVER['REMOTE_ADDR'].') has no access',
		'code'				=> 5205,
		'code_http'			=> 503,
	),
	'error_auth' => array(
		'response' 			=> 'ok',	
		'message'			=> 'Auth error',
		'code'				=> 5206,
		'code_http'			=> 503,
	),
	'error_license' => array(
		'message'			=> 'Oeps! The '.ENTRY_API_NAME.' has an incorrect License. Grab a license from devote:ee and fill the license in the CP',
		'code'				=> 5207,
		'code_http'			=> 503,
	),


	//-------------------------------------------------------------

	//Default errors
	'error_field_single' => array(
		'message'			=> 'Following error occur: ',
		'code'				=> 5300,
		'code_http'			=> 500,
	),
	'error_field' => array(
		'message'			=> 'The following fields are not filled in:',
		'code'				=> 5301,
		'code_http'			=> 500,
	),

	'error_no_entry' => array(
		'message'			=> 'No entry found',
		'code'				=> 5302,
		'code_http'			=> 404,
	),

	'error_delete' => array(
		'message'			=> 'Entry could not be removed',
		'code'				=> 5303,
		'code_http'			=> 500,
	),

	'error_no_channel' => array(
		'message'			=> 'Given channel does not exist',
		'code'				=> 5304,
		'code_http'			=> 500,
	),
	
	'error_channel' => array(
		'message'			=> 'You are not authorized to use this channel',
		'code'				=> 5305,
		'code_http'			=> 500,
	),

	'error_category' => array(
		'message'			=> 'You are not authorized to use this category',
		'code'				=> 5306,
		'code_http'			=> 500,
	),
	
	'error_channel_match' => array(
		'message'			=> 'Specified entry does not appear in the specified channel',
		'code'				=> 5307,
		'code_http'			=> 500,
	),
	
	'error_duplicated_category' => array(
		'message'			=> 'There is already a category with this name',
		'code'				=> 5308,
		'code_http'			=> 500,
	),

	'error_create_category' => array(
		'message'			=> 'Can`t create category',
		'code'				=> 5309,
		'code_http'			=> 503,
	),

	'error_field_length' => array(
		'message'			=> 'The following fields are to long:',
		'code'				=> 5310,
		'code_http'			=> 500,
	),

	'error_no_category' => array(
		'message'			=> 'No category found',
		'code'				=> 5311,
		'code_http'			=> 404,
	),

	'error_field_validate' => array(
		'message'			=> 'The following fields have errors:',
		'code'				=> 5312,
		'code_http'			=> 500,
	),

	'error_site_id' => array(
		'message'			=> 'The site_id for this channel is wrong',
		'code'				=> 5313,
		'code_http'			=> 500,
	),
	
	'error_channel_no_right' => array(
		'message'			=> 'You have no right to administrate channel',
		'code'				=> 5314,
		'code_http'			=> 500,
	),
	
	'error_channel_create' => array(
		'message'			=> 'Following errors occurs creating a new channel : ',
		'code'				=> 5315,
		'code_http'			=> 500,
	),
	
	'error_channel_update' => array(
		'message'			=> 'Following errors occurs updating the channel : ',
		'code'				=> 5316,
		'code_http'			=> 500,
	),
	
	'error_channel_id' => array(
		'message'			=> 'Channel_id is missing',
		'code'				=> 5317,
		'code_http'			=> 500,
	),
	
	'error_channel_delete' => array(
		'message'			=> 'Following errors occurs deleting the channel : ',
		'code'				=> 5318,
		'code_http'			=> 500,
	),
	
	'error_member_no_right' => array(
		'message'			=> 'You have no right to administrate members',
		'code'				=> 5319,
		'code_http'			=> 500,
	),
	
	'error_member_create' => array(
		'message'			=> 'Following errors occurs creating a new member : ',
		'code'				=> 5320,
		'code_http'			=> 500,
	),
	
	'error_member_update' => array(
		'message'			=> 'Following errors occurs updating this member : ',
		'code'				=> 5321,
		'code_http'			=> 500,
	),
	
	'error_member_id' => array(
		'message'			=> 'Member_id is missing',
		'code'				=> 5322,
		'code_http'			=> 500,
	),
	
	'error_member_delete' => array(
		'message'			=> 'Following errors occurs deleting the member : ',
		'code'				=> 5323,
		'code_http'			=> 500,
	),
	'error_member_username' => array(
		'message'			=> 'Username already exists',
		'code'				=> 5324,
		'code_http'			=> 500,
	),
	'error_member_password' => array(
		'message'			=> 'Password is missing',
		'code'				=> 5325,
		'code_http'			=> 500,
	),
	'error_member_password_confirm' => array(
		'message'			=> 'Password COnfirm is missing',
		'code'				=> 5326,
		'code_http'			=> 500,
	),
	'error_member_email' => array(
		'message'			=> 'Email already exists',
		'code'				=> 5327,
		'code_http'			=> 500,
	),
	'error_member_password_mismatch' => array(
		'message'			=> 'Password mismatch',
		'code'				=> 5328,
		'code_http'			=> 500,
	),
	'error_no_member' => array(
		'message'			=> 'No member found',
		'code'				=> 5329,
		'code_http'			=> 404,
	),
	'error_comment_disabled' => array(
		'message'			=> 'Comments for this entry are disabled',
		'code'				=> 5330,
		'code_http'			=> 404,
	),

	'error_member_registration' => array(
		'message'			=> 'Member Registration has been disabled',
		'code'				=> 5331,
		'code_http'			=> 500,
	),
	
	'error_no_member' => array(
		'message'			=> 'No member found',
		'code'				=> 5332,
		'code_http'			=> 404,
	),
	'error_not_delete_self' => array(
		'message'			=> 'Cannot delete yourself',
		'code'				=> 5333,
		'code_http'			=> 404,
	),
	'error_not_delete_super_admin' => array(
		'message'			=> 'Cannot delete a superadmin',
		'code'				=> 5334,
		'code_http'			=> 404,
	),

	'error_no_channel' => array(
		'message'			=> 'No channel found',
		'code'				=> 5335,
		'code_http'			=> 404,
	),

	'error_no_adman' => array(
		'message'			=> 'No Ad found',
		'code'				=> 53036,
		'code_http'			=> 404,
	),

	//-------------------------------------------------------------
	
	//file errors
	'error_file' => array(
		'code'				=> 5401, //general file error
		'code_http'			=> 500,
	),
	
	'error_no_file_data' => array(
		'message'			=> 'no file data set',
		'code'				=> 5402,
		'code_http'			=> 500,
	),
	
	'error_no_file_data_given' => array(
		'message'			=> 'No filedata is given',
		'code'				=> 5403,
		'code_http'			=> 500,
	),
	
	'error_no_filename_given' => array(
		'message'			=> 'No filename is given',
		'code'				=> 5404,
		'code_http'			=> 500,
	),

	'error_no_dir_id_given' => array(
		'message'			=> 'No upload dir_id is given',
		'code'				=> 5405,
		'code_http'			=> 500,
	),
	
	'error_cannot_save_tmp_file' => array(
		'message'			=> 'cannot save the file to a tmp file on: ',
		'code'				=> 5406,
		'code_http'			=> 500,
	),
	
);

$this->content_types = array(
	'json' => 'application/json',
	'xml' => 'text/xml',
	'array' => 'php/array',
	'default' => 'text/html',
);

/* End of file settings.php */
/* Location: /system/expressionengine/third_party/entry_api/settings.php */