<?php

class Classes extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function class_code_exists($code)
    {
    	$q = $this->db->query("select * from class where code=" . $this->db->escape($code));
    	return($q->num_rows() != 0);
    }
    
    function get_user_and_class_hash_from_class_code($code)
    {
    	$q = $this->db->query("select * from class where code=" . $this->db->escape($code));
    	if ($q->num_rows() == 0)
    		return(false);
    	$row = $q->row_array();
    	return(Array('user_hash' => $row['user_hash'],'class_hash' => $row['class_hash']));
    }
    
    function get_user_and_class_hash_from_question_hash($question_hash)
    {
    	$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
    	if ($q->num_rows() == 0)
    		return(false);
    	$row = $q->row_array();
    	return(Array('user_hash' => $row['user_hash'],'class_hash' => $row['class_hash']));
    }

	function create_class($user_hash,$title,$code)
	{
		$class_hash = md5($title . time() . $code);
		$this->db->query("insert into class values(NULL," .
								$this->db->escape($class_hash) . "," .
								$this->db->escape($user_hash) . "," .
								$this->db->escape($title) . "," .
								"''" . "," .
								$this->db->escape($code) . "," . 
								"'open',unix_timestamp(),now())");
	}

	function dump_classes_owned($user_hash)
	{
		$q = $this->db->query("select * from class where user_hash=" . $this->db->escape($user_hash) . " order by create_time desc");
		//echo "<div id=\"info_box_full\">";
		echo anchor("welcome/create_class","Create a new class",Array('id' => 'create_link'));
		echo "<ul>";
		foreach($q->result_array() as $row)
		{
			echo "<li> ";
			echo anchor("welcome/class_menu/" . $row['class_hash'],$row['code']);
			echo": " . $row['title'];
			echo "<br/>";
			echo "<div id=\"class_links\">";
			echo anchor("welcome/student_report/" . $row['class_hash'] . "/last","Grade report");
			echo " | ";
			echo anchor("welcome/reset_password/" . $row['class_hash'],"Reset password");
			echo " | ";
			echo anchor("welcome/manage_students/" . $row['class_hash'],"Manage students");
			echo "</div>";
			
			if ($this->Classes->get_class_status($row['class_hash']) != 'open')
				echo "<span id=\"class_closed\">Closed for maintenance.  Will open shortly.</span>";
			
			echo "<p/>";	
		}
	echo "</ul>";
	//echo "</div>";
	}
	
	function get_unexpired_count($class_hash)
	{
		$q = $this->db->query("select count(*) as count from question where deadline_ts - unix_timestamp() > 0 and class_hash=" . $this->db->escape($class_hash));
		$row = $q->row_array();
		return($row['count']);
	}
	
	function dump_classes_joined($user_hash)
	{
		$q = $this->db->query("select * from enroll inner join class on enroll.class_hash=class.class_hash where enroll.user_hash=" . $this->db->escape($user_hash) . " order by create_time desc;");
		//echo "<div id=\"info_box_full\">";
		echo anchor("welcome/join","Join a class",Array('id' => 'create_link'));
		echo "<ul>";
		foreach($q->result_array() as $row)
		{
			echo "<li> ";
			$status = $this->Classes->get_class_status($row['class_hash']);
			if ($status == 'open' || $this->Classes->verify_class_owner($user_hash,$row['class_hash']))
				{
					echo anchor("welcome/question_menu/" . $row['class_hash'],$row['code']);
					echo": " . $row['title'];
					if ($status != 'open')
						echo "<br/><span id=\"class_closed\">Your teacher has closed this class for maintenance.  It will open shortly.</span> ";
				}
			else 
				{
					echo $row['code'] . ": " . $row['title'] . "<br/><span id=\"class_closed\">Your teacher has closed this class for maintenance.  It will open shortly.</span> ";
				}
			echo "<br/>";
			echo "<span id=\"status1\">";
			$gr = $this->Answer->get_grade($user_hash,$row['class_hash']);
			echo "(Your grade: " . $gr['per'] . "%.)  ";
			echo "</span>";
			/*
			echo $this->Classes->get_unexpired_count($row['class_hash']);
			echo " questions to do!";
			*/
			echo "<p/>";
		}
	echo "</ul>";
	//echo "</div>";
	}
	
	function verify_class_owner($user_hash,$class_hash)
	{
		$q = $this->db->query("select * from class where user_hash=" .
								$this->db->escape($user_hash) . " and class_hash=" .
								$this->db->escape($class_hash));
		return($q->num_rows() == 1);
	}
	
	function verify_class_joined($user_hash,$class_hash)
	{
		$q = $this->db->query("select * from enroll where user_hash=" .
								$this->db->escape($user_hash) . " and class_hash=" .
								$this->db->escape($class_hash));
		return($q->num_rows() == 1);
	}
	
	function get_class_desc($class_hash)
	{
		$q = $this->db->query("select * from class where class_hash=" . $this->db->escape($class_hash));
		$row = $q->row_array();
		return($row['code'] . ": " . $row['title']);
	}
	
	function get_class_hash_from_code($code)
	{
		$q = $this->db->query("select * from class where code=" . $this->db->escape($code));
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['class_hash']);
	}
	
	function get_class_code_from_class_hash($class_hash)
	{
		$q = $this->db->query("select * from class where class_hash=" . $this->db->escape($class_hash));
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['code']);
	}
	
	function get_class_code_from_question_hash($question_hash)
	{
		$q = $this->db->query("select * from question inner join class on question.class_hash=class.class_hash where question.question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['code']);
	}
	
	function enroll($user_hash,$class_hash,$last,$first)
	{
		$this->db->query("insert into enroll values(NULL," . 
							$this->db->escape($user_hash) . "," .
							$this->db->escape($class_hash) . "," . 
							$this->db->escape($last) . "," .
							$this->db->escape($first) . ")");
	}
	
	function already_enrolled($user_hash,$class_hash)
	{
		$q = $this->db->query("select * from enroll where user_hash = ".
									$this->db->escape($user_hash) . " and class_hash=" .
									$this->db->escape($class_hash));
		return($q->num_rows() == 1);
	}
	
	function get_class_status($class_hash)
	{
		$q = $this->db->query("select * from class where class_hash=" . $this->db->escape($class_hash));
		$row = $q->row_array();
		return($row['status']);
	}
	
	function set_class_status($class_hash,$status)
	{
		$q = $this->db->query("update class set status=" . $this->db->escape($status) . " where class_hash=" . $this->db->escape($class_hash));
	}
	
	function get_students($class_hash)
	{
		$q = $this->db->query("select enroll.user_hash,user.user_name from enroll inner join user on enroll.user_hash=user.user_hash where enroll.class_hash=" .
						$this->db->escape($class_hash));
		return($q->result_array());
	}
	
	function get_students_into_select($class_hash)
	{	
		$q = $this->db->query("select enroll.user_hash,user.user_name,enroll.last,enroll.first from enroll inner join user on enroll.user_hash=user.user_hash where enroll.class_hash=" . $this->db->escape($class_hash) . " order by last asc");
		$ret = "";
		foreach($q->result_array() as $row)
		{
			$ret .= "<option value=\"" . $row['user_hash'] . "\">";
			$ret .= $row['last'] . ", " . $row['first'] . " (" . $row['user_name'] . ")";
			$ret .= "</option>";
		}
			
		return($ret);
	}
	
	function get_students_with_checkbox($class_hash)
	{	
		$q = $this->db->query("select enroll.user_hash,user.user_name,enroll.last,enroll.first from enroll inner join user on enroll.user_hash=user.user_hash where enroll.class_hash=" . $this->db->escape($class_hash) . " order by last asc");
		$ret = "";
		foreach($q->result_array() as $row)
		{
			$ret .= "<span id=\"student_name\"><input name=\"student_array[]\" type=\"checkbox\" value=\"" . $row['user_hash'] . "\"/>";
			$ret .= $row['last'] . ", " . $row['first'] . " (" . $row['user_name'] . ")</span>";
			$ret .= "<br/>";
		}
			
		return($ret);
	}
	
	
		
}    
    
?>
