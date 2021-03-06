<?php

/**
 * Environment Declaration
 * 
 * This switch statement sets our environment. The environment is used primarily
 * in our custom config file setup. It is also used, however, in the front-end
 * index.php file and the back-end admin.php file to set the debug mode
 * 
 * @package    Focus Lab Master Config
 * @version    1.1.1
 * @author     Focus Lab, LLC <dev@focuslabllc.com>
 */

if ( ! defined('ENV'))
{
	switch (strtolower($_SERVER['HTTP_HOST'])) {
		case 'venderesganar.com' :
			define('ENV', 'prod');
			define('ENV_FULL', 'Production');
			define('ENV_DEBUG', FALSE);
		break;

		case 'www.venderesganar.com' :
			define('ENV', 'prod');
			define('ENV_FULL', 'Production');
			define('ENV_DEBUG', FALSE);
		break;
		
		case 'fedex.gracreatives.com' :
			define('ENV', 'stage');
			define('ENV_FULL', 'Staging');
			define('ENV_DEBUG', FALSE);
		break;

		default :
			define('ENV', 'local');
			define('ENV_FULL', 'Local');
			define('ENV_DEBUG', TRUE);
		break;
	}
}

/* End of file config.env.php */
/* Location: ./env/config.env.php */
