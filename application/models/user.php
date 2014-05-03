<?php

class User extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }

	function create_new($user_name,$password)
	{	
		$user_hash = md5(time() . $password . "curiosity_grant_%%%" . strrev($user_name));
		$auth_hash = md5($user_name . "curiosity_grant_%%%" . $password);
		$this->db->query("insert into user values(NULL," . 
						$this->db->escape($user_name) . "," .
						$this->db->escape($user_hash) . "," .
						$this->db->escape($auth_hash) . ",0)");
		return($user_hash);
	}
	
	function change_password($user_name,$password)
	{
		$auth_hash = md5($user_name . "curiosity_grant_%%%" . $password);
		$this->db->query("update user set reset_limit=0,auth_hash=" . $this->db->escape($auth_hash) . " where user_name=" . $this->db->escape($user_name));
	}
	
	function reset_password($user_name)
	{
		$q = $this->db->query("select * from user where user_name=" . $this->db->escape($user_name));
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['reset_limit'] == 1);	
	}
	
	function verify_reset_password($user_name,$pw)
	{
		$q = $this->db->query("select * from user where user_name=" . $this->db->escape($user_name) . " and auth_hash=" . $this->db->escape($pw));
		if ($q->num_rows() == 0)
			return(false);
		return(true);
	}
	
	function validate($user_name,$password)
	{
		$auth_hash = md5($user_name . "curiosity_grant_%%%" . $password);
		$sql = "select * from user where user_name=" . 
						$this->db->escape($user_name) . " and auth_hash=" . 
						$this->db->escape($auth_hash);
		$q = $this->db->query($sql);
		if ($q->num_rows() == 1)
			{
				$row = $q->row_array();
				return($row['user_hash']);
			}
		return(false);
	}
	
	
	function is_admin($user_name)
	{
		$q = $this->db->query("select * from admin where user_name=" . $this->db->escape($user_name));
		return($q->num_rows() > 0);
	}
	
	function get_user_name($user_hash)
	{
		$q = $this->db->query("select * from enroll where user_hash=" . $this->db->escape($user_hash));
		if ($q->num_rows() == 0)
			return(Array('last' => '','first' => ''));
		$row = $q->row_array();
		return(Array('last' => $row['last'],'first' => $row['first']));
	}	
	
	function update_for_reset_password($student_hash)
	{
		$tp = uniqid();
		$this->db->query("update user set reset_limit=1,auth_hash='$tp' where user_hash=" . $this->db->escape($student_hash));	
		return($tp);
	}
	
	function update_fl($user_hash,$last,$first)
	{
		$this->db->query("update enroll set last=" . $this->db->escape($last) . ",first=" . 
													$this->db->escape($first) . " where user_hash=" .
													$this->db->escape($user_hash));
	}
	
	function is_enrolled($user_hash)
	{
		$q = $this->db->query("select * from enroll where user_hash=" . $this->db->escape($user_hash));
		return($q->num_rows() != 0);
	}

	function get_user_name_from_user_hash($user_hash)
	{
		$q = $this->db->query("select * from user where user_hash=" . $this->db->escape($user_hash));
		$row = $q->row_array();
		return($row['user_name']);
	}
	
	function get_user_hash_from_class_hash($class_hash)
	{
		$q = $this->db->query("select * from class where class_hash=" . $this->db->escape($class_hash));
		$row = $q->row_array();
		return($row['user_hash']);
	}
}    
    
?>
