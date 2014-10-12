<?php
/**
 * Default config
 *
 * @package		entry_api
 * @category	Modules
 * @author		Rein de Vries <info@reinos.nl>
 * @link		http://reinos.nl/add-ons/entry-api
 * @license  	http://reinos.nl/add-ons/commercial-license
 * @copyright 	Copyright (c) 2013 Reinos.nl Internet Media
 */

//contants
if ( ! defined('ENTRY_API_NAME'))
{
	define('ENTRY_API_NAME', 'Entry API');
	define('ENTRY_API_CLASS', 'Entry_api');
	define('ENTRY_API_MAP', 'entry_api');
	define('ENTRY_API_VERSION', '3.5');
	define('ENTRY_API_DESCRIPTION', 'Entry api service (SOAP/XMLRPC/REST) for select, insert, update and delete entries (and many more)');
	define('ENTRY_API_DOCS', 'http://reinos.nl/add-ons/entry-api');
	define('ENTRY_API_DEVOTEE', '');
	define('ENTRY_API_AUTHOR', 'Rein de Vries');
	define('ENTRY_API_DEBUG', false);
	define('ENTRY_API_STATS_URL', 'http://reinos.nl/index.php/module_stats_api/v1'); 	
}

//configs
$config['name'] = ENTRY_API_NAME;
$config['version'] = ENTRY_API_VERSION;

//load compat file
require_once(PATH_THIRD.ENTRY_API_MAP.'/compat.php');

/* End of file config.php */
/* Location: /system/expressionengine/third_party/entry_api/config.php */