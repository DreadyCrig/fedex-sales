<?php
class Lamplighter_library {

	protected $lamplighter_site_id, $api_key;

	public function __construct() {
		$this->EE =& get_instance();
		$this->prefix 	= $this->EE->db->dbprefix;
		$this->backup_path = PATH_THIRD.'lamplighter/backups/';
	}

	public function register_action_id() {
		$this->_get_license_info();
		$action_id  = $this->EE->cp->fetch_action_id('Lamplighter', 'api_request');
		$api_url 	= 'https://lamplighter.io/api/register/' . $this->lamplighter_site_id;
		$data	 	= array(
						'action_id' => $action_id,
						'api_key'	=> $this->api_key
						);
		$return = json_decode($this->_curl_request($api_url, $data));
		return isset($return->status) && $return->status == 'success'? 1 : 0;
	}

	public function unregister_action_id() {
		$this->_get_license_info();
		$api_url 	= 'https://lamplighter.io/api/unregister/' . $this->lamplighter_site_id;
		$data	 	= array(
						'api_key'	=> $this->api_key
						);
		$this->_curl_request($api_url, $data);
	}

	public function has_api_key() {
		$licensing = "
			SELECT
				`lamplighter_site_id`,
				`key`
			FROM
				`".$this->prefix."lamplighter_license`
			WHERE
				`site_id` = ".$this->EE->config->item('site_id')."
			ORDER BY
				`id` DESC
			LIMIT 1
		";
		$licensing = $this->EE->db->query($licensing);
		return $licensing->num_rows() ? $licensing->row('lamplighter_site_id').':'.$licensing->row('key') : 0;
	}

	protected function _get_license_info() {
		$licensing = "
			SELECT
				`lamplighter_site_id`,
				`key`
			FROM
				`".$this->prefix."lamplighter_license`
			WHERE
				`site_id` = ".$this->EE->config->item('site_id')."
			ORDER BY
				`id` DESC
			LIMIT 1
		";
		$licensing = $this->EE->db->query($licensing);
		$this->lamplighter_site_id 	= $licensing->row('lamplighter_site_id');
		$this->api_key	= $licensing->row('key');
		return TRUE;
	}

	/*
		Process an API Request sent from lamplighter
	*/
	public function api_request() {
		$this->_get_license_info();

		// Our array of valid endpoints
		$valid_endpoints = array(
								'addons',
								'backup'
							);

		$api = $this->EE->input->get('api');


		// Error catching!
		if (empty($api)) {
			return array('status' => 'error',
						'message' => 'Invalid API Request');
		}
		if (!in_array($api, $valid_endpoints)) {
			return array('status' => 'error',
						'message' => 'Invalid Endpoint');
		}

		// This seems to be a valid request, make a call to the API function.
		$api = '_api_'.$api;
		return $this->$api();
	}

	protected function _api_backup() {
		$database_query = $this->EE->db->query("SELECT DATABASE()");
		$database 		= $database_query->row('DATABASE()');
		$table_query 	= $this->EE->db->query("SHOW TABLES");
		$table_array 	= $table_query->result_array();
		$filename		= 'backup_' . date('Y_m_d_H_i_s').'.sql';

		$backup 		= fopen($this->backup_path . $filename, 'w');

		foreach ($table_array AS $table) {
			// Get current table name.
			$current_table = $table['Tables_in_'.$database];

			// Drop table
			$backup_table = 'DROP TABLE IF EXISTS '.$current_table.'; ';

			// Create a new Table
			$create_query = $this->EE->db->query("SHOW CREATE TABLE ".$current_table);
			$backup_table .= $create_query->row('Create Table').'; ';

			// Get our rows
			$select_query = $this->EE->db->query("SELECT * FROM ".$current_table);
			if ($select_query->num_rows()) {
				$backup_table .= 'INSERT INTO `'.$current_table.'` VALUES ';
				foreach ($select_query->result_array() AS $row) {
					$backup_table .= '(';
					foreach ($row AS $field => $value) {
						$backup_table .= "'".mysql_real_escape_string($value)."', ";
					}
					$backup_table = rtrim($backup_table, ', ');
					$backup_table .= '), ';
				}
				$backup_table = rtrim($backup_table, ', ').'; ';
			}
			fwrite($backup, $backup_table);
		}

		fclose($backup);

		$curl = $this->_curl_request('https://lamplighter.io/api/backup/'.$this->lamplighter_site_id,
			array(
				'rand' => rand(),
				'backup' => '@' . $this->backup_path . $filename .';filename='.$filename
			), 1);

		$return = json_decode($curl);
		return $return;
	}

	/*
		Retrieve our PHP info
	*/
	protected function _php_info() {

        $tables = $this->EE->db->query('SHOW TABLE STATUS')->result_array();

        $dbSize = 0;
        foreach ($tables AS $table) {
            $dbSize += (integer) $table['Data_length'] + (integer) $table['Index_length'];
        }

		ob_start();
		phpinfo(-1);

		$pi = preg_replace(
		array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
		'#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
		"#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
		'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
		.'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
		'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
		'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
		"# +#", '#<tr>#', '#</tr>#'),
		array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
		'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
		"\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
		'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
		'<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
		'<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
		ob_get_clean());

		$sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
		unset($sections[0]);

		$pi = array();
		foreach($sections as $section){
			$n = substr($section, 0, strpos($section, '</h2>'));
			preg_match_all(
			'#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',
			 $section, $askapache, PREG_SET_ORDER);
			foreach($askapache as $m) {
					if (!isset($m[2]))
						continue;
			   	$pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
			}
		}
		$mysql_info = $this->EE->db->query('SELECT VERSION() AS version');

		$return = array();

		if (isset($pi['mysql'])) {
			$return['mysql'] = $pi['mysql'];
		} else if (isset($pi['mysqli'])) {
			$return['mysql'] = $pi['mysqli'];
		} else {
			$return['mysql'] = '';
		}

		$return['php'] 				= isset($pi['PHP Configuration']) ? $pi['PHP Configuration'] : '';
		$return['core'] 			= isset($pi['Core']) ? $pi['Core'] : '';
		$return['apache'] 			= isset($pi['apache2handler']) ? $pi['apache2handler'] : '';
		$return['server'] 			= isset($_SERVER["SERVER_SOFTWARE"]) ? $_SERVER["SERVER_SOFTWARE"] : '';
		$return['os_name'] 			= function_exists('php_uname') ? php_uname('s') : '';
		$return['os_version'] 			= function_exists('php_uname') ? php_uname('r') : '';
		$return['getrusage'] 		= function_exists('getrusage') ? getrusage() : '';
		$return['temp_dir'] = function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : '';
		$return['system_load'] 		= function_exists('sys_getloadavg') ? sys_getloadavg() : array();
		$return['mysql_version']	= $mysql_info->row('version');
		$return['apache_version'] 	= function_exists('apache_get_version') ? apache_get_version() : '';
		$return['php_version'] 		= phpversion();
        $return['db_size']          = $dbSize;
		return json_encode($return);

	}

	/*
		Request our add-ons
	*/
	protected function _api_addons() {
		$this->EE->load->add_package_path( PATH_THIRD.'lamplighter/' );
		$this->EE->load->library('devotee_library');
		$addons = json_encode($this->EE->devotee_library->get_addons(FALSE, FALSE, TRUE));
		$curl = $this->_curl_request('https://lamplighter.io/api/send/'.$this->lamplighter_site_id,
			array(
				'version' => APP_VER,
				'addons' => $addons,
				'phpinfo' => $this->_php_info(),
				'rand' => rand()));
		$return = json_decode($curl);
		if (isset($return->status) && $return->status == 'success') {
			return array(
						'status' => 'success',
						'message' => 'Request Completed'
					);
		} elseif (isset($return->status)) {
			return array(
						'status' => $return->status,
						'message' => $return->message
				);
		} else {
			return array(
						'status' => 'error',
						'message' => 'Bad Request'
				);

		}
	}

	/*
		POST a cURL request w/ (array) $data as POSTed fields.
	*/
	public function _curl_request($api_url, $data, $file = 0) {
		$data['api_key'] = $this->api_key;
		$ch = curl_init();
		$fields_string = '';
		foreach($data as $key => $value) {
			$fields_string .= $key .'='.urlencode($value).'&';
		}
		rtrim($fields_string, '&');
		curl_setopt($ch, CURLOPT_URL, $api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
		curl_setopt($ch, CURLOPT_POST, count($data));
		if ($file)
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		else
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		$curl = curl_exec($ch);
		return $curl;
	}

}