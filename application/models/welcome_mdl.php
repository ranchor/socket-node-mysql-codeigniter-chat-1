<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
class Welcome_mdl extends CI_Model
{
	private $users_table_name	= 'users';
	function __construct()
	{
		parent::__construct();
		$ci =& get_instance();
		$this->users_table_name	= $this->users_table_name;
		
	}
	function saveUser($username)
	{
		$this->db->set('online',1);
		$this->db->set('username',$username);
		$this->db->insert('users');
		return $this->db->insert_id();
	}
	
	function getUser($user_id)
	{
		$this->db->where('user_id',$user_id);
		$query=$this->db->get('users');
		return $query->row_array();
	}
}