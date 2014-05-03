<?php

class Pr extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function assign_pr_questions($owner_hash,$class_hash,$problems,$owner_get_assignments)
	{
		if ($owner_get_assignments == 'yes')
			$q = $this->db->query("select user_hash from enroll where class_hash=" . $this->db->escape($class_hash));
		else $q = $this->db->query("select user_hash from enroll where class_hash=" . $this->db->escape($class_hash) . " and user_hash != " . $this->db->escape($owner_hash));

		$users = Array();
		foreach($q->result_array() as $x)
			array_push($users,$x['user_hash']);

		shuffle($problems);
		shuffle($users);
		
		$pairing = Array();
		if (count($problems) <= count($users))
			{
				$i = 0;
				foreach($users as $user_hash)
				{
					$pairing[$user_hash] = $problems[$i];
					$i++;
					if ($i >= count($problems))
						$i = 0;
				}
			}
		else
			{
				$i = 0;
				foreach($problems as $prob)
				{
					$user_hash = $users[$i];
					$pairing['$user_hash'] = $prob;
					$i++;
					if ($i >= count($users))
						$i = 0;
				}
			}
			
		$pr_hashes = "";
		$c = 1;
		$prob_hash_text = Array();
		foreach($pairing as $user => $prob)
			{
				$pr_hash = md5(time() . $user . $prob);
				$question_hash = md5(time() . $c . $prob);
				$this->db->query("insert into pr_question values(NULL," .
							$this->db->escape($pr_hash) . "," .
							$this->db->escape($user) . "," .
							$this->db->escape($question_hash) . "," .
							$this->db->escape($class_hash) . "," .
							"0,0)");
				$pr_hashes .= "'" . $pr_hash . "',";
				$prob_hash_text[$question_hash] = $prob;
				$c++;
			}
		return(Array('pr_hashes' => rtrim($pr_hashes,","),'prob_hashes' => $prob_hash_text));
	}
	
	function create_question($user_hash,$class_hash,$data,$deadline_nice,$deadline_ts)
    {
    	$probs = explode("<![CDATA[",$data['probs']);
    	$probs = array_filter($probs);
    	
    	$ret = $this->assign_pr_questions($user_hash,$class_hash,$probs,$data['owner_get_assignments']);
    
    	$pr_hashes = $ret['pr_hashes'];
    	$prob_hashes = $ret['prob_hashes'];
		$q = $this->db->query("select * from pr_question where pr_hash in ($pr_hashes)");
		$question_hash_list = "";
		foreach($q->result_array() as $row)
    		{
				$question_hash = $row['question_hash'];
				$question_hash_list .= $question_hash . ",";
				$raw_text = "pr//" . $prob_hashes[$question_hash] . "//#end//" . 
										$data['points_for_up_vote'] . "//" .
										$data['points_for_down_vote'] . "//" .
										$data['points_for_participating'] . "//" .
										$data['time_between_votes'] . "//" .
										$data['owner_get_assignments'];
										
										
				$this->db->query("insert into question values(NULL," .
							$this->db->escape($question_hash) . "," .
							$this->db->escape($user_hash) . "," .
							$this->db->escape($class_hash) . "," .
							$this->db->escape($data['type']) . "," .
							$this->db->escape($raw_text) . "," .
							$this->db->escape($prob_hashes[$question_hash]) . "," .
							$this->db->escape($data['correct']) . "," .
							$this->db->escape($deadline_nice) . "," .
							$this->db->escape($deadline_ts) . "," .
							$this->db->escape($data['attempts']) . "," .
							$this->db->escape($data['points']) . "," .
							$this->db->escape($data['ded_per_attempt']) . "," .
							$this->db->escape($data['show_answer_when_expired']) . "," .
							$data['margin'] . "," .
							$this->db->escape($data['abs_answer']) . "," .
							$this->db->escape($data['units']) . "," . 
							$this->db->escape($data['attach_bg']) . ")");
				
			}
			
		$question_hash_list = rtrim($question_hash_list,",");
		$this->db->query("insert into pr_config values(NULL," .
					$this->db->escape($user_hash) . "," .
					$this->db->escape($class_hash) . "," .
					$this->db->escape($question_hash_list) . "," .
					$this->db->escape($data['points_for_up_vote']) . "," .
					$this->db->escape($data['points_for_down_vote']) . "," .
					$this->db->escape($data['points_for_participating']) . "," .
					$this->db->escape($data['owner_get_assignments']) . "," .
					$this->db->escape($data['time_between_votes']) . ")");
		
    	return(Array('ok' => true));
    }
	
	function get_question_title($question_hash)
	{
		$q = $this->db->query("select * from pr_config where question_hash=" . $this->db->escape($question_hash));
		$row = $q->row_array();
		return($row['title']);	
	}
	
	function question_list($user_hash,$question_hash)
	{
		$ret = "<div id=\"pr_problems\">";
		
		$ret .= "<b>" . $this->get_question_title($question_hash) . "</b>";
		$ret .= "<br/>";
		
		$q = $this->db->query("select * from pr_question where question_hash=" . $this->db->escape($question_hash));
		
		$to_you = "";
		$to_others = "";
		foreach($q->result_array() as $row)
		{
			if ($row['issued_to_hash'] == $user_hash)
				$to_you .= anchor("welcome/issue/$question_hash." . $row['pr_hash'],$row['qtext']) . "<br/>";
			else $to_others .= anchor("welcome/issue/$question_hash." . $row['pr_hash'],$row['qtext']) . "<br/>";
		}
		
		$ret .= "Assigned to you:<br/>";
		$ret .= $to_you;
		$ret .= "<p/>";
		
		$ret .= "For you to review:<br/>";
		$ret .= $to_others;
		
		$ret .= "</div>";
		return($ret);
	}
	
	function assigned_to_user($user_hash,$question_hash)
	{
		$q = $this->db->query("select * from pr_question where question_hash=" . $this->db->escape($question_hash) . 
										" and issued_to_hash=" . $this->db->escape($user_hash));
		return($q->num_rows() == 1);
	}
	
	function get_stats($question_hash)
	{
		$q = $this->db->query("select * from pr_question where question_hash=" . $this->db->escape($question_hash));
		$row = $q->row_array();
		return(Array("up" => $row['up_votes'],"down" => $row['down_votes']));
	}
	
	function answer_there($question_hash)
	{
		$sql = "select * from pr_question where question_hash=" . $this->db->escape($question_hash);
		$q = $this->db->query($sql);
		$row = $q->row_array();
		$issued_to_hash = $row['issued_to_hash'];
		$q = $this->db->query("select * from answer where question_hash=" . $this->db->escape($question_hash) . " and student_hash=" . $this->db->escape($issued_to_hash));
		return($q->num_rows() == 1);
	}
	
	function issued_to($question_hash)
	{
		$sql = "select * from pr_question where question_hash=" . $this->db->escape($question_hash);
		$q = $this->db->query($sql);
		$row = $q->row_array();
		return($row['issued_to_hash']);
	}
	
	function get_answer_as_link($question_hash)
	{
		$q = $this->db->query("select * from answer where question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows() == 0)
			return("");
		$row = $q->row_array();
		return("<a href=\"" . prep_url($row['answer']) . "\" target=\"_blank\" onclick=\"return update_view();\">" . $row['answer'] . "</a>");
	}
	
	function handle_vote($user_hash,$question_hash,$answer)
	{
		if ($this->assigned_to_user($user_hash,$question_hash))
			return;
		$q = $this->db->query("select * from pr_config where locate('$question_hash',question_hash_list) != 0");
		if ($q->num_rows() == 0)
			return;
		$row = $q->row_array();		
		$this->add_to_vote($answer,$user_hash,$question_hash,$row['points_for_up_vote']);
		$this->give_participation_points($user_hash,$question_hash,$row['points_for_participating']);
	}
	
	function add_to_vote($updown,$user_hash,$question_hash,$points)
	{
		if ($updown == "up")
			$this->db->query("update pr_question set up_votes=up_votes+$points where question_hash=" . $this->db->escape($question_hash));
		else 
			{
				$this->db->query("update pr_question set down_votes=down_votes+$points where question_hash=" . $this->db->escape($question_hash));
				$points = -$points;
			}
		$issued_to_hash = $this->issued_to($question_hash);
		$sql = "update answer set points=points+$points where question_hash=" . $this->db->escape($question_hash) . " and student_hash=" . $this->db->escape($issued_to_hash);
		$this->db->query($sql);
		$this->db->query("delete from vote_time where user_hash=" . $this->db->escape($user_hash));
		$this->db->query("insert into vote_time values(NULL," . $this->db->escape($user_hash) . ",unix_timestamp())");
	}
	
	function give_participation_points($user_hash,$question_hash,$points)
	{
		$sql = "update answer set points=$points where question_hash=" . $this->db->escape($question_hash) . " and student_hash=" . $this->db->escape($user_hash);
		$this->db->query($sql);
	}
	
	function lodged_vote($user_hash,$question_hash)
	{
		$q = $this->db->query("select * from answer where student_hash=" . $this->db->escape($user_hash) . " and question_hash=" . $this->db->escape($question_hash) . " and answer in ('up','down')");
		return($q->num_rows());
	}
	
	function get_max_student_points($class_hash)
	{
		$q = $this->db->query("select answer.student_hash,sum(answer.points) as points from question inner join answer on question.question_hash=answer.question_hash where question.type='pr' group by student_hash order by points desc");
		if ($q->num_rows() == 1)
			{
				$row = $q->row_array();
				return($row['points']);
			}
		return('n/a');
	}
	
	function get_grade_pr_probs($student_hash,$class_hash)
	{
		$possible = $this->get_max_student_points($class_hash);
				
		$q = $this->db->query("select sum(answer.points) as points from question inner join answer on question.question_hash=answer.question_hash where question.type = 'pr' and answer.student_hash='$student_hash' and answer.class_hash='$class_hash'");
		$row = $q->row_array();
		$earned = round($row['points'],1);
		if ($possible != 0)
    		$per = round($earned / $possible * 100.0,1);
    	else $per = "n/a";
    	return(Array('earned' => $earned,'possible' => $possible,'per' => $per));
		
	}
	
	function ignore_expire($question_hash,$user_hash)
	{
		//first see if the quesiton is peer reviewed at all
		$q = $this->db->query("select * from pr_question where question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows() == 0) // if not, we cannot ignore the expiration date.
			return(false);
		//now, see if the pr question was issued to the specific user
		$q = $this->db->query("select * from pr_question where question_hash=" . $this->db->escape($question_hash) . 
										" and issued_to_hash=" . $this->db->escape($user_hash));
		return($q->num_rows() == 0);
	}
	
	function get_time_between_votes($question_hash)
	{
		$q = $this->db->query("select * from pr_config where locate('$question_hash',question_hash_list) != 0");
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['time_between_votes']);
	}
	
	function ok_to_vote($user_hash,$question_hash)
	{
		if ($this->Question->verify_question_owner($user_hash,$question_hash))
			return(Array('ok' => true));
		$q = $this->db->query("select * from vote_time where user_hash=" . $this->db->escape($user_hash));
		if ($q->num_rows() == 0)
			return(Array('ok' => true));
		$row = $q->row_array();
		$ret = $this->get_time_between_votes($question_hash);
		if ($ret === false)
			return(Array('ok' => true));
		$dt = abs($row['ts'] - time())/60;
		if ($dt >= $ret)
			return(Array('ok' => true));
		return(Array('ok' => false,'wait' => $ret-$dt));
	}
}

?>