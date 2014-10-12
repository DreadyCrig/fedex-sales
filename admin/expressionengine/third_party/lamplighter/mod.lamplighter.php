<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Lamplighter Module Front End File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Masuga Design
 * @link
 */

class Lamplighter {

	public $return_data;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
	}

	public function api_request()
	{
		header('Access-Control-Allow-Origin: *');
		$this->EE->load->add_package_path( PATH_THIRD.'lamplighter/' );
		$this->EE->load->library('lamplighter_library');
		echo json_encode($this->EE->lamplighter_library->api_request());
		exit;
	}

}
/* End of file mod.lamplighter.php */
/* Location: /system/expressionengine/third_party/lamplighter/mod.lamplighter.php */