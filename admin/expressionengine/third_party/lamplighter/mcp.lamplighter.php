<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Lamplighter Module Control Panel File
 *
 * @package		ExpressionEngine
 * @subpackage	Addons
 * @category	Module
 * @author		Masuga Design
 * @link
 */


class Lamplighter_mcp {

	public $return_data;

	private $_base_url;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();
		$this->_base_url = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=lamplighter';
		$this->_base_uri = '?D=cp&C=addons_modules&M=show_module_cp&module=lamplighter';
		$this->EE->load->add_package_path( PATH_THIRD.'lamplighter/' );
		$this->EE->load->library('lamplighter_library');

		if ($this->EE->lamplighter_library->has_api_key()) {
			$this->EE->cp->set_right_nav(array(
				'module_home'	=> $this->_base_url,
				'send_data' => $this->_base_url.'&method=refresh&api=addons',
			));
		} else {
			$this->EE->cp->set_right_nav(array(
				'module_home'	=> $this->_base_url,
			));

		}
	}

	// ----------------------------------------------------------------

	/**
	 * Index Function
	 *
	 * @return 	void
	 */
	public function index()
	{
		$this->EE->view->cp_page_title = lang('lamplighter_module_name');
		return $this->EE->load->view('mcp_index',
			array(
				'api_key' => $this->EE->lamplighter_library->has_api_key(),
				'base_url' => $this->_base_url,
				'curl_enabled' => $this->_curl_enabled(),
				'cp' => $this->EE->cp
			), TRUE);
	}

	public function refresh()
	{
		$this->EE->lamplighter_library->api_request();
		return $this->EE->functions->redirect($this->_base_url);
	}

	public function save_key()
	{
		$key = $this->EE->input->post('api_key');
		$key = explode(':', $key);
		if (isset($key[1])) {
			$lamplighter_site_id = $key[0];
			$api_key = $key[1];
			$this->EE->db->insert('lamplighter_license', array(
				'key' => $api_key,
				'lamplighter_site_id' => $lamplighter_site_id,
				'site_id' => $this->EE->config->item('site_id')
			));
			$registered = $this->EE->lamplighter_library->register_action_id();

		}
		if (!$registered) {
			$this->EE->session->set_flashdata('message_failure', 'The add-on was unable to successfully register with Lamplighter.  Please check your API Key.');
			$this->remove_key();
		}
		return $this->EE->functions->redirect($this->_base_url);
	}

	public function remove_key()
	{
		$key = $this->EE->lamplighter_library->has_api_key();
		$key = explode(':', $key);
		if (isset($key[1])) {
			$lamplighter_site_id = $key[0];
			$api_key = $key[1];
			$this->EE->lamplighter_library->unregister_action_id();
			$this->EE->db->delete(
				'lamplighter_license',
				array(
					'lamplighter_site_id'=>$lamplighter_site_id,
					'key'=>$api_key,
				)
			);
		}
		return $this->EE->functions->redirect($this->_base_url);
	}

	public function _curl_enabled() {
    	return function_exists('curl_version');
	}

}
/* End of file mcp.lamplighter.php */
/* Location: /system/expressionengine/third_party/lamplighter/mcp.lamplighter.php */