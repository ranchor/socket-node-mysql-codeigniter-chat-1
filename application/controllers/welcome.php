<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	 
	function __construct()
    {
        // Call the Model constructor
        parent::__construct();
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->model('welcome_mdl');
    } 
	public function index()
	{
		$this->load->view('welcome_message');
	}
	public function saveUserInfo()
	{
		$this->form_validation->set_rules('username', 'Username', 'trim|required|xss_clean');
		if ($this->form_validation->run()) {
			$user_id=$this->welcome_mdl->saveUser($this->form_validation->set_value('username'));
			redirect('/welcome/chatRoom/'.$user_id.'');
		}
		else
		{
			redirect('');
		}
		
	}
	public function chatRoom($user_id)
	{
		if($user_id)
		{
			$result=$this->welcome_mdl->getUser($user_id);
			$data['user_id']=$result['user_id'];
			$data['username']=$result['username'];
			$this->load->view('chatpage',$data);
		}
		else
		{
			redirect('');
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */