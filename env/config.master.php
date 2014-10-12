<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Focus Lab, LLC Master Config
 * 
 * This is the master config file for our ExpressionEngine sites
 * The settings will contain database credentials and numerous "config overrides"
 * used throughout the site. This file is used as first point of configuration
 * but there are environment-specific files as well. The idea is that the environment
 * config files contain config overrides that are specific to a single environment.
 * 
 * Some config settings are used in multiple (but not all) environments. You will
 * see the use of conditionals around the ENV constant in this file. This constant is
 * defined in ./config/config.env.php
 * 
 * All config files are stored in the ./config/ directory and this master file is "required"
 * in system/expressionengine/config/config.php and system/expressionengine/config/database.php
 * 
 * require $_SERVER['DOCUMENT_ROOT'] . '/../config/config.master.php';
 * 
 * This config setup is a combination of inspiration from Matt Weinberg and Leevi Graham
 * @link       http://eeinsider.com/articles/multi-server-setup-for-ee-2/
 * @link       http://ee-garage.com/nsm-config-bootstrap
 * 
 * @package    Focus Lab Master Config
 * @version    1.1.1
 * @author     Focus Lab, LLC <dev@focuslabllc.com>
 * @see        https://github.com/focuslabllc/ee-master-config
 */


// Require our environment declatation file if it hasn't
// already been loaded in index.php or admin.php
if ( ! defined('ENV'))
{
	require 'config.env.php'; 
}


// Setup our initial arrays
$env_db = $env_config = $env_global = $master_global = array();


/**
 * Database override magic
 * 
 * If this equates to TRUE then we're in the database.php file
 * We don't want these settings bothered with in our config.php file
 */
if (isset($db['expressionengine']))
{
	/**
	 * Load our environment-specific config file
	 * which contains our database credentials
	 * 
	 * @see config/config.local.php
	 * @see config/config.dev.php
	 * @see config/config.stage.php
	 * @see config/config.prod.php
	 */
	require 'config.' . ENV . '.php';
	
	// Dynamically set the cache path (Shouldn't this be done by default? Who moves the cache path?)
	$env_db['cachedir'] = APPPATH . 'cache/db_cache/';
	
	// Merge our database setting arrays
	$db['expressionengine'] = array_merge($db['expressionengine'], $env_db);
	
	// No need to have this variable accessible for the rest of the app
	unset($env_db);
}

// End if (isset($db['expressionengine'])) {}


/* End of file config.master.php */
/* Location: ./config/config.master.php */
