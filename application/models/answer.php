<?php

class Answer extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function new_answer($user_hash,$question_hash,$class_hash,$answer,$correct,$points)
    {
    	$answer_hash = md5(time(). "!!quizable!!" . $class_hash . $answer . $user_hash . $question_hash); 
    	$raw =  $this->Question->get_raw_question_data($question_hash);
    	$dt_deadline = $raw['deadline_ts'] - time();
    	switch($raw['type'])
    		{
    			case 'mc':
    						$status = 'graded';
    						break;
    			default: 
    						$status = 'graded';
    						break;
    		}
    	$this->db->query("insert into answer values(NULL," . 
    							$this->db->escape($answer_hash) . "," .
    							$this->db->escape($question_hash) . "," .
    							$this->db->escape($user_hash) . "," .
    							$this->db->escape($class_hash) . "," .
    							$this->db->escape($answer) . "," .
    							$this->db->escape($correct) . "," .
    							$points . "," .
    							$this->db->escape($status) . "," .
    							$this->db->escape($raw['type']) . "," .
    							$dt_deadline . "," .
    							"unix_timestamp(),now())");
    
    }
    
    function get_attempt_info($user_hash,$question_hash)
    {
    	$q = $this->db->query("select * from answer where student_hash=" . $this->db->escape($user_hash) . " and question_hash=" . $this->db->escape($question_hash));
    	return($q->num_rows());
    }
    
    function get_points_earned($user_hash,$question_hash)
    {
    	$q = $this->db->query("select sum(points) as points from answer where student_hash=" . $this->db->escape($user_hash) . " and question_hash=" . $this->db->escape($question_hash));
    	$row = $q->row_array();
    	if (empty($row['points']))
    		return("0");

    	return(round($row['points'],1));
    }
     function get_status_message($user_hash,$question_hash)
    {
    	$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
    	$num = $this->Question->get_question_numerics($question_hash);
		$more = $num['max_attempts'] - $attempt_count;
		$earned = $this->Answer->get_points_earned($user_hash,$question_hash);
		$status = $this->get_answer_correct_status($user_hash,$question_hash);
		$st = "";
		if ($status == "waiting")
			$st = "<span id=\"not_graded\">(Not graded yet.)</span>";
		return("Tries: $more left.  Earned: $earned of " . $num['points'] . " possible points $st.");
		
    }
    
    function all_tries_used($user_hash,$question_hash)
    {
    	$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
    	$num = $this->Question->get_question_numerics($question_hash);
		$more = $num['max_attempts'] - $attempt_count;
		return($more <= 0);
    }
    
    function get_grade($user_hash,$class_hash)
    {
    	$sql = "select sum(points) as points from answer where student_hash=" . $this->db->escape($user_hash) . " and class_hash=" .
    																$this->db->escape($class_hash);
    	$q = $this->db->query($sql);
    	$row = $q->row_array();
    	$earned = round($row['points'],1);
    	if (empty($earned))
    		$earned = "0";
    	
    	$q = $this->db->query("select sum(points) as points from question where class_hash=" . $this->db->escape($class_hash));
    	$row = $q->row_array();
    	$possible = round($row['points'],1);
    	if ($possible != 0)
    		$per = round($earned / $possible * 100.0,1);
    	else $per = "n/a";
    	return(Array('earned' => $earned,'possible' => $possible,'per' => $per));
    }
    
    function get_grade_non_pr_probs($student_hash,$class_hash)
	{
		$q = $this->db->query("select sum(points) as points from question where type <> 'pr' and class_hash='$class_hash'");
		$row = $q->row_array();
		$possible = round($row['points'],1);
				
		$q = $this->db->query("select sum(answer.points) as points from question inner join answer on question.question_hash=answer.question_hash where question.type <> 'pr' and answer.student_hash='$student_hash' and answer.class_hash='$class_hash'");
		$row = $q->row_array();
		$earned = round($row['points'],1);
		if ($possible != 0)
    		$per = round($earned / $possible * 100.0,1);
    	else $per = "n/a";
    	return(Array('earned' => $earned,'possible' => $possible,'per' => $per));
		
	}
	
	 function get_grade_non_pr_probs_past_deadline($student_hash,$class_hash)
	{
		$q = $this->db->query("select sum(points) as points from question where type <> 'pr' and class_hash='$class_hash' and deadline_ts < unix_timestamp()");
		$row = $q->row_array();
		$possible = round($row['points'],1);
				
		$q = $this->db->query("select sum(answer.points) as points from question inner join answer on question.question_hash=answer.question_hash where question.type <> 'pr' and answer.student_hash='$student_hash' and answer.class_hash='$class_hash' and question.deadline_ts < unix_timestamp()");
		$row = $q->row_array();
		$earned = round($row['points'],1);
		if ($possible != 0)
    		$per = round($earned / $possible * 100.0,1);
    	else $per = "n/a";
    	return(Array('earned' => $earned,'possible' => $possible,'per' => $per));
		
	}
    
    function get_max_points($class_hash)
    {
    	
    	$q = $this->db->query("select sum(points) as points from question where class_hash=" . $this->db->escape($class_hash));
    	$row = $q->row_array();
    	return(round($row['points'],1));
    }
    
    function is_correct($question_hash,$answer)
    {
        $quest = $this->Question->get_raw_question_data($question_hash);

        switch($quest['type'])
                {
                        case 'mc':
                                                if ($answer == $quest['answer'])
                                                        return(true);
                                                return(false);
                        case 'num':
                        						if (!is_numeric($answer))
                        							return(false);
                                                if ($quest['abs_answer'] == 'yes')
                                                        $answer = abs($answer);
                                                $low = $quest['answer'] - $quest['margin']/100.0 * $quest['answer'];
                                                $high = $quest['answer'] + $quest['margin']/100.0 * $quest['answer'];
                                                if ($high < $low)
                                                        list($high,$low) = Array($low,$high);
                                                if ($answer >= $low && $answer <= $high)
                                                        return(true);
                                                return(false);
                        case 'sa':
                                                return(true);

                }
        return(false);
    }


	function get_answer($question_hash)
    {
        $quest = $this->Question->get_raw_question_data($question_hash);

        switch($quest['type'])
                {
                		case 'num':
                					return($quest['answer'] . " " . $quest['units']);
                        case 'mc':
                        			return($quest['answer']);
                      	default:
                                	return(false);
                }
        return(false);
    }

    
    function get_points_worth($user_hash,$question_hash)
	{
		$num = $this->Question->get_question_numerics($question_hash);
		$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
		return($num['points'] - $attempt_count * $num['deduct_per_attempt']);
	}
	
	function got_it_correct($user_hash,$question_hash)
	{
		$q = $this->db->query("select * from answer where student_hash=" . $this->db->escape($user_hash) . " and question_hash=" . $this->db->escape($question_hash) .
								" and correct='yes'");
		return($q->num_rows() == 1);
	}
	
	function waiting_to_be_graded($user_hash,$question_hash)
	{
		$q = $this->db->query("select * from answer where student_hash=" . $this->db->escape($user_hash) . " and question_hash=" . $this->db->escape($question_hash) .
								" and correct='waiting'");
		return($q->num_rows() == 1);
	}
	
	function dump_answers($question_hash)
	{
		$sql = "select distinct answer_id,answer.question_hash,enroll.last,enroll.first,enroll.user_hash,answer,correct,points,ts_nice,answer.answer_hash,answer.class_hash,answer.type from answer inner join enroll on answer.student_hash=enroll.user_hash where question_hash=" . $this->db->escape($question_hash) . " order by enroll.last asc,ts asc";
		$q = $this->db->query($sql);
		echo "<center>";
		echo "<table width=\"100%\">";
		echo "<tr id=\"header_row\"><td width=15%>Last, First</td><td width=15%>Answer</td><td width=10%>Correct</td><td width=10%>Points</td><td width=15%>Submitted</td><td width=25%>Comment</td><td width=10%	>Actions</td>";
	
		foreach($q->result_array() as $row)
		{
			echo "<tr id=\"table_highlight\">";
			$name = "<a href=mailto:" . $this->User->get_user_name_from_user_hash($row['user_hash']) . ">" . $row['last'] . ", " . $row['first'];
			echo "<td valign=\"top\">" . $name . "</td>";
			$quest_numerics = $this->Question->get_question_numerics($row['question_hash']);

			if ($row['type'] != 'dr' && $row['type'] != 'at' && $row['type'] != 'ln')
				echo "<td id=\"answer_col\">" . $row['answer'] . "</td>";
			else if ($row['type'] == 'ln')
				{
					if (strlen($row['answer']) > 20)
						$link = substr(prep_url($row['answer']),0,20) . "...";
					else $link = prep_url($row['answer']);
					echo "<td valign=\"top\" id=\"answer_col\">" . "<a href=\"" .  $row['answer'] . "\" target=\"_blank\">" . $link . "</a></td>";
				}
			else if ($row['type'] == 'dr')
				{
					$ans = str_replace("[removed]","",$row['answer']);
					echo "<td>";
					echo "<img id=\"image_answer\" src=\"data:image/png;base64,$ans\">";
					echo "</td>";
				}
			else if ($row['type'] == 'at')
				{
					parse_str($row['answer'],$data);
					echo "<td>";
					echo anchor("welcome/view_answer_attachment/" . $row['answer_hash'] . $data['file_ext'],"Attachment");
					echo "</td>";
				}

			echo "<td valign=\"top\" >" . $row['correct'] . "</td>";
			echo "<td valign=\"top\" >" . $row['points'] . " (Max: " . $quest_numerics['points'] . ")</td>"; 
			echo "<td valign=\"top\" id=\"comment_dump\">" . $row['ts_nice'] . "</td>";
			$comment = $this->get_grading_comment($row['answer_hash']);
			if ($comment !== false)
				echo "<td valign=\"top\" id=\"comment_dump\">$comment</td>";
			else echo "<td></td>";
			echo "<td valign=\"top\" id=\"actions_col\">";
			echo anchor("welcome/edit_answer/" . $row['answer_hash'] . "/" . $row['class_hash'],"Edit");
			echo " | ";
			echo "Delet" . anchor("welcome/delete_answer/" . $row['answer_hash'] . "/$question_hash/" . $row['class_hash'],"e");
			echo "</tr>";
		}	
		echo "</table></center>";
	}
	
	function get_answer_data($answer_hash)
	{
		$q = $this->db->query("select * from answer where answer_hash=" . $this->db->escape($answer_hash));
		return($q->row_array());
	}
	
	function update($answer_hash,$answer,$points,$correct)
	{
		$sql = "update answer set answer=" . $this->db->escape($answer) . "," .
										"points=" . $this->db->escape($points) . "," . 
										"correct=" . $this->db->escape($correct) . 
										" where answer_hash=" . $this->db->escape($answer_hash);
		$this->db->query($sql);
	}
	

	
	function delete($answer_hash)
	{
		$this->db->query("delete from answer where answer_hash=" . $this->db->escape($answer_hash));
	}
	
	function get_points_earned_for_correct($user_hash,$question_hash)
	{
		$q = $this->db->query("select points from answer where correct='yes' and student_hash=" . $this->db->escape($user_hash) .  " and question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows() == 0)
			return(0);
		$row = $q->row_array();
		return($row['points']);
	}
	
	function get_submitted_answers($user_hash,$question_hash)
	{
		$ret = "<div id=\"previous_answers\"><ul>";
		$q = $this->db->query("select * from answer where student_hash=" . $this->db->escape($user_hash) . " and question_hash=" . $this->db->escape($question_hash));
		foreach($q->result_array() as $row)
			{
				$ret .= "<li>";
				if ($row['correct'] == 'waiting')
					$status = "Waiting to be graded.";
				else if ($row['correct'] == 'yes')
					$status = "Correct";
				else $status = "Incorrect";

				$ret .= "<b>Submitted:</b> " . $row['ts_nice'] .  ", <b>Status:</b> $status, ";
				$ret .= "<b>Points given:</b> " . $row['points'];
				$ret .=  ", <b>Answer: </b>";
				if ($row['type'] == 'sa')
					{
						$ret .= "<blockquote><i>"  . $row['answer'] . "</i>";
						$comment = $this->Answer->get_grading_comment($row['answer_hash']);
						if ($comment !== false)
							{
								$ret .= "<p/><b>From your teacher: </b><i>$comment</i>";
							}
						$ret .= "</blockquote>";
					} 	
				else if ($row['type'] == 'dr')
						{
							$ret .= "<p/>";
							$ans = str_replace("[removed]","",$row['answer']);
							$ret .= "<img id=\"image_answer\" width=\"800\" height=\"600\" src=\"data:image/png;base64,$ans\">";
							$ret .= "<p/>";
							$comment = $this->Answer->get_grading_comment($row['answer_hash']);
							if ($comment !== false)
								{
									$ret .= "<p/><b>From your teacher: </b><i>$comment</i>";
								}
						}
				else if ($row['type'] == 'at')
						{
							parse_str($row['answer'],$data);
							$ret .= anchor("welcome/view_answer_attachment/" . $row['answer_hash'] . $data['file_ext'],"Your upload");
							$comment = $this->Answer->get_grading_comment($row['answer_hash']);
							if ($comment !== false)
								{
									$ret .= "<p/><b>From your teacher: </b><i>$comment</i>";
								}
						}
				else if ($row['type'] == 'ln')
						{
							$ret .= "<a href=\"" . prep_url($row['answer']) . "\" target=\"_blank\" id=\"small_link\">" . $row['answer'] . "</a>";
							$comment = $this->Answer->get_grading_comment($row['answer_hash']);
							if ($comment !== false)
								{
									$ret .= "<p/><b>From your teacher: </b><i>$comment</i>";
								}

						}
				
				else $ret .= $row['answer'];
				$ret .= "<p/>";
			}
		$ret .= "</ul></div>";
		return($ret);
	}
	
	
	function get_waiting_count($question_hash)
	{
		$q = $this->db->query("select * from answer where correct='waiting' and question_hash=" . $this->db->escape($question_hash));
		return($q->num_rows());
	}
	
	function get_answer_count($question_hash)
	{
		$q = $this->db->query("select count(*) as count from answer where correct='no' and question_hash=" . $this->db->escape($question_hash) . " group by correct");
		if ($q->num_rows())
			{
				$row = $q->row_array();
				$incorrect = $row['count'];
			}
		else $incorrect = 0;
		$incorrect = $q->num_rows();
		$q = $this->db->query("select count(*) as count from answer where correct='yes' and question_hash=" . $this->db->escape($question_hash) . " group by correct");
		if ($q->num_rows())
			{
				$row = $q->row_array();
				$correct = $row['count'];
			}
		else $correct = 0;
		return(Array("correct" => $correct,"incorrect" => $incorrect));
	}
	
	function get_waiting_answers($question_hash)
	{
		$raw = $this->Question->get_raw_question_data($question_hash);

		$q = $this->db->query("select * from answer inner join enroll on answer.student_hash=enroll.user_hash where answer.correct='waiting' and answer.question_hash=" . $this->db->escape($question_hash) . " group by answer_hash");
		//echo "<h2>" . $q->num_rows() . " answers to grade</h2>";
		echo "<h2><span id=\"grade_count\">" . $q->num_rows() . "</span> answers to grade</h2>";
		foreach($q->result_array() as $row)
		{
			echo "<div id=\"answer_" . $row['answer_hash'] . "\">";
			$user_name = $this->User->get_user_name_from_user_hash($row['student_hash']);
			echo "<a href=\"mailto:$user_name\">" . $row['last'] . ", " . $row['first'] . "</a> answered: ";
		
			if ($raw['type'] == 'dr')
				{
					$ans = str_replace("[removed]","",$row['answer']);
					echo "<br/>";
					echo "<img id=\"image_answer\" src=\"data:image/png;base64,$ans\">";
				}
			else if ($raw['type'] == 'at')
				{
					parse_str($row['answer'],$data);
					echo anchor("welcome/view_answer_attachment/" . $row['answer_hash'] . $data['file_ext'],"Attachment");
				}
			else if ($raw['type'] == 'ln')
				{
					$url = prep_url($row['answer']);
					echo "<a href=\"$url\" id=\"link_input\" target=\"_blank\">$url</a>";
				}
			else
				{
					echo "<br/>";
					echo "<textarea id=\"answer_" . $row['answer_hash'] . "\" cols=60 rows=5>";
					echo $row['answer'];
					echo "</textarea>";
				}
			echo "<br/>";
			
			
			//echo "<input value=\"" . $raw['points'] . "\" type=text id=\"comment_" . $row['answer_hash'] . "\" size=50>";
			echo "<button onclick=\"increase('" . $row['answer_hash'] . "');\">+</button>";
			echo "<button onclick=\"decrease('" . $row['answer_hash'] . "');\">-</button>";
			echo "<button onclick=\"accept_input('" . $row['answer_hash'] . "');\">Submit</button>";
			echo "<br/>";
			echo "<div id=\"note_no_indent\">(Type comment here.  Format: numerical-grade,comment text)</div>";
			echo "<textarea id=\"comment_" . $row['answer_hash'] . "\" rows=3 cols=50>";
			echo $raw['points'];
			echo "</textarea>";
			echo "<br/>";
			echo "<hr/>";
			echo "</div>";
			
		}
	}
	
	function incoming_grade_comment($question_hash,$answer_hash,$comment)
	{
		$a = explode(",",$comment,2);
		if (isset($a[0]))
			{
				$this->db->query("update answer set correct='yes',points=" . $a[0] . " where question_hash=" . $this->db->escape($question_hash) . " and answer_hash=" . $this->db->escape($answer_hash));
			}
		if (isset($a[1]))
			$this->db->query("insert into grading_comment values(NULL," . $this->db->escape($answer_hash) . "," . $this->db->escape($a[1]) . ")");
	}
	
	function get_grading_comment($answer_hash)
	{	
		$q = $this->db->query("select * from grading_comment where answer_hash=" . $this->db->escape($answer_hash));
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['comment']);
	}
	
	
	function update_grading_comment($answer_hash,$comment)
	{	
		$this->db->query("delete from grading_comment where answer_hash=" . $this->db->escape($answer_hash));
		$this->db->query("insert into grading_comment values(NULL," . $this->db->escape($answer_hash) . "," . 
																		$this->db->escape($comment) . ")");
	}
	
	function get_answer_on_expired($question_hash)
	{
		$raw = $this->Question->get_raw_question_data($question_hash);
		if ($raw['show_answer_when_expired'] == 'no')
			return("");
		return("<div id=\"correct_answer\">The correct answer is: <b>" .$raw['answer'] . "</b></div>");
	}
	
	function get_question_hash_from_answer_hash($answer_hash)
	{
		$q = $this->db->query("select question_hash from answer where answer_hash=" . $this->db->escape($answer_hash));
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['question_hash']);
	}
	
	function get_answer_correct_status($user_hash,$question_hash)
	{
		$q = $this->db->query("select * from answer where student_hash=" . $this->db->escape($user_hash) . " and question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows() == 0)
			return("");
		$row = $q->row_array();
		return($row['correct']);
	}
}    
    
?>
