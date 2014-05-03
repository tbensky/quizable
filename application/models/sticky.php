<?php

class Sticky extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function get($user_hash,$name)
    {
    	$q = $this->db->query("select * from sticky where user_hash=" . $this->db->escape($user_hash) . " and name=" . $this->db->escape($name));
    	if ($q->num_rows() == 0)
    		return("");
    	$row = $q->row_array();
    	return($row['value']);
    }
    
     function set($user_hash,$name,$value)
   	 {
    	$this->db->query("delete from sticky where user_hash=" . $this->db->escape($user_hash) . " and name=" . $this->db->escape($name));
    	$this->db->query("insert into sticky values(NULL," . $this->db->escape($user_hash) . "," . $this->db->escape($name) . "," . $this->db->escape($value) . ")");
   	 }


}    
    
?>
