<?php

class Settings extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		if(!$this->session->userdata('login') || $this->session->userdata('login') == FALSE)
		{
			redirect($this->config->base_url() . 'index.php/user/login/');
		}
	}

	public function Index()
	{
		#Generate header
		$this->lang->load('commons');
		$data['common_lang_set'] = $this->lang->line('common_lang_set');
		$data['common_title'] = $this->lang->line('common_title');
		$this->load->view('header',$data);
		
		#generate navigation bar
		$data['common_index_page'] = $this->lang->line('common_index_page');
		$data['common_node_manager'] = $this->lang->line('common_node_manager');
		$data['common_node_monitor'] = $this->lang->line('common_node_monitor');
		$data['common_install'] = $this->lang->line('common_install');
		$data['common_host_settings'] = $this->lang->line('common_host_settings');
		$data['common_node_operate'] = $this->lang->line('common_node_operate');
		$data['common_user_admin'] = $this->lang->line('common_user_admin');
		$data['common_log_out'] = $this->lang->line('common_log_out');
		$this->load->view('nav_bar', $data);
		
		$this->load->view('div_fluid');
		$this->load->view('div_row_fluid');
		
		$this->load->view('ehm_hosts_settings_nav', $data);
		
		$data['common_hostname'] = $this->lang->line('common_hostname');
		$data['common_ip_addr'] = $this->lang->line('common_ip_addr');
		$data['common_node_role'] = $this->lang->line('common_node_role');
		$data['common_create_time'] = $this->lang->line('common_create_time');
		$data['common_action'] = $this->lang->line('common_action');
		
		#generate settings lists
		$this->load->model('ehm_settings_model','sets');
		$this->load->model('ehm_hosts_model', 'hosts');
		$data['result_general'] = $this->sets->get_general_settings_list();
		$data['all_hosts'] = $this->hosts->get_all_hosts();
		
		$data['common_submit'] = $this->lang->line('common_submit');
		$data['common_global_setting_tips'] = $this->lang->line('common_global_setting_tips');
		$data['common_node_setting_tips'] = $this->lang->line('common_node_setting_tips');
		
		$this->load->model('ehm_settings_model','sets');
		$data['result_divide'] = $this->sets->get_divide_settings_list();
		$data['hosts_divide'] = $this->hosts->get_divide_setted_host();
		
		$this->load->view('ehm_hosts_settings_list', $data);
		
		$this->load->view('view_etc_hosts_modal',$data);
		$this->load->view('add_general_settings_modal',$data);
		//$this->load->view('add_divide_settings_modal',$data);
		$this->load->view('push_etc_hosts_modal', $data);
		$this->load->view('push_general_settings_modal', $data);
		//$this->load->view('push_divide_settings_modal', $data);
		
		$this->load->view('div_end');
		$this->load->view('div_end');
		
		#generaet footer
		$this->load->view('footer', $data);
	}
	
	public function UpdateGeneralSettings()
	{
		$set_id = $this->input->post('set_id');
		$filename = $this->input->post('filename');
		$content = $this->input->post('content');
		$ip = "0";
		
		$this->load->model('ehm_settings_model', 'sets');
		$this->sets->update_settings($set_id, $filename, $content, $ip);
		
		redirect($this->config->base_url() . 'index.php/settings/index/');
	}
	
	public function DeleteSettings()
	{
		$set_id = $this->input->post('set_id');
		$this->load->model('ehm_settings_model', 'sets');
		$this->sets->delete_settings($set_id);
		
		redirect($this->config->base_url() . 'index.php/settings/index/');
	}
	
	public function UpdateDivideSettings()
	{
		$set_id = $this->input->post('set_id');
		$filename = $this->input->post('filename');
		$content = $this->input->post('content');
		$ip = $this->input->post('ip');
		
		$this->load->model('ehm_settings_model', 'sets');
		$this->sets->update_settings($set_id, $filename, $content, $ip);
		
		redirect($this->config->base_url() . 'index.php/settings/index/');
	}
	
	public function ViewHosts()
	{
		$this->load->model('ehm_settings_model', 'sets');
		$str = $this->sets->get_etc_hosts_list();
		$str = str_replace("\n", '<br />', $str);
		echo  $str;
	}
	
	public function ViewSettings()
	{
		$this->load->model('ehm_settings_model', 'sets');
		$set_id = $this->uri->segment(3,0);
		
		$result = $this->sets->get_settings_by_id($set_id);
		$data['result'] = $result;
	}
	
	public function AddSettings()
	{
		$filename = $this->input->post('filename');
		$content = $this->input->post('content');
		$ip = $this->input->post('ip');
		
		$this->load->model('ehm_settings_model', 'sets');
		$this->sets->insert_settings($filename, $content, $ip);
		
		redirect($this->config->base_url() . 'index.php/settings/index/');
	}
	
	public function PushEtcHost()
	{
		$host_id = $this->uri->segment(3,0);
		$this->load->model('ehm_hosts_model', 'hosts');
		$result = $this->hosts->get_host_by_host_id($host_id);
		$ip = $result->ip;
		
		$this->load->model('ehm_settings_model', 'sets');
		$str = $this->sets->get_etc_hosts_list();
		
		$this->load->model('ehm_installation_model', 'install');
		echo $this->install->push_setting_files($ip, '/etc/hosts', $str); #full path of /etc/hosts
	}
	
	public function PushGeneralSettings()
	{
		$host_id = $this->uri->segment(3,0);
		$set_id = $this->uri->segment(4,0);
		$this->load->model('ehm_hosts_model', 'hosts');
		$result = $this->hosts->get_host_by_host_id($host_id);
		$ip = $result->ip;
		
		$this->load->model('ehm_settings_model', 'sets');
		$result = $this->sets->get_settings_by_id($set_id);
		$set_id = $result->set_id;
		$filename = $result->filename;
		$content = $result->content;
		
		$this->load->model('ehm_installation_model', 'install');
		echo $this->install->push_setting_files($ip, $this->config->item('conf_folder') . $filename, $content);#full path of hadoop setting file
	}
	
	public function PushDivideSettings()
	{
		$host_id = $this->uri->segment(3,0);
		$set_id = $this->uri->segment(4,0);
		$this->load->model('ehm_hosts_model', 'hosts');
		$result = $this->hosts->get_host_by_host_id($host_id);
		$ip = $result->ip;
		
		$this->load->model('ehm_settings_model', 'sets');
		$result = $this->sets->get_settings_by_id($set_id);
		$set_id = $result->set_id;
		$filename = $result->filename;
		$content = $result->content;
		
		$this->load->model('ehm_installation_model', 'install');
		echo $this->install->push_setting_files($ip, $this->config->item('conf_folder') . $filename, $content);
	}

}

?>