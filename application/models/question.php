<?php

class Question extends CI_Model {

    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
    }
    
    function compute_deadline($date,$time)
    {
    	$d = explode("/",$date);
    	$t = explode(":",$time);
    	$dl = mktime($t[0],$t[1],"0",$d[0],$d[1],$d[2]);
    	return($dl);
    }

	function get_dt_deadline($question_hash)
	{
		$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
    	$row = $q->row_array();
    	return($row['deadline_ts'] - time());
	}
    
    function parse_question($question)
    {
    	$part = explode("//",rtrim($question,"/"));
    	$part = array_map('trim', $part);
    	
    	if (count($part) == 0 || empty($part[0]))
    		return(Array('ok' => false,'error' => 'Please start your question with mc// num// or sa//.'));
		
		$type = $part[0];
		
		if (!isset($part[1]))
			return(Array('ok' => false,'error' => 'You didn\'t type what the question should be.'));
			
		$qtext = $part[1];
		
		switch($type)
			{
				case "mc":
						$i = 2;
						$answer = "";
						$choices = "";
						$correct = "";
						$margin = 0.0;
    					$abs_answer = "";
    					$units = "";
    					$attach_bg = "";
    	
						while(isset($part[$i]) && $i < count($part))
							{
								if ($part[$i] == '#end')
									{
										$i++;
										break;
									}
								$choices .= ltrim($part[$i],'*') . "<![CDATA[";
								if (substr($part[$i],0,1) == '*')
									$correct = ltrim($part[$i],'*');
								$i++;	
							}
							
						if (is_blank($correct))
							return(Array('ok' => false,'error' => 'You didn\'t supply a correct answer.'));
							
						if (!isset($part[$i]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points the question is worth.'));
						$points = $part[$i];
						
						if (!isset($part[$i+1]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many attempts can be made to answer this question.'));
						$attempts = $part[$i+1];
						
						if (!isset($part[$i+2]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points should be deducted per attempt.'));
						$ded_per_attempt = $part[$i+2];
						
						if (!isset($part[$i+3]))
							return(Array('ok' => false,'error' => 'You didn\'t say if the answer should be revealed when the question expires.'));
						$show_answer_when_expired = $part[$i+3];
						
						return(Array(
									'ok' => true,
									'type' => 'mc',
									'qtext' => $qtext,
									'choices' => $choices,
									'points' => $points,
									'attempts' => $attempts,
									'ded_per_attempt' => abs($ded_per_attempt),
									'correct' => $correct,
									'show_answer_when_expired' => $show_answer_when_expired,
									'margin' => $margin,
									'abs_answer' => $abs_answer,
									'units' => $units,
									'attach_bg' => $attach_bg
								));
				case "num":
						$i = 2;
						$answer = "";
						$choices = "";
						$correct = "";
						$margin = 0.0;
    					$abs_answer = "";
    					$units = "";
    					$attach_bg = "";
    	
						if (is_blank($part[$i]))
							return(Array('ok' => false,'error' => 'You didn\'t supply a correct answer.'));
						$correct = $part[$i];
						
						if (is_blank($part[$i+1]))
							return(Array('ok' => false,'error' => 'You didn\'t provide units in which the answer must be reported.'));
						$units = $part[$i+1];
						
						if (empty($part[$i+2]))
							return(Array('ok' => false,'error' => 'You didn\'t provide the margin of error (as a percent).'));
						$margin = $part[$i+2];
						
						if (empty($part[$i+3]))
							return(Array('ok' => false,'error' => 'You didn\'t say if only the absolute value of incoming answers should be graded.'));
						$abs_answer = $part[$i+3];
						
						if (is_blank($part[$i+4]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points the correct answer is worth.'));
						$points = $part[$i+4];
						
						if (is_blank($part[$i+5]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many attempts can be made to answer this question.'));
						$attempts = $part[$i+5];
						
						if (is_blank($part[$i+6]))
								return(Array('ok' => false,'error' => 'You didn\'t say how many points should be deducted per attempt.'));
						$ded_per_attempt = $part[$i+6];
						
						if (empty($part[$i+7]))
							return(Array('ok' => false,'error' => 'You didn\'t say if the answer should be revealed when the question expires.'));
						$show_answer_when_expired = $part[$i+7];
						
						return(Array(
									'ok' => true,
									'type' => 'num',
									'qtext' => $qtext,
									'choices' => $choices,
									'points' => $points,
									'attempts' => $attempts,
									'ded_per_attempt' => abs($ded_per_attempt),
									'correct' => $correct,
									'show_answer_when_expired' => $show_answer_when_expired,
									'margin' => $margin,
									'abs_answer' => $abs_answer,
									'units' => $units,
									'attach_bg' => $attach_bg
								));
						break;
						
					case "sa":
						$i = 2;
						$answer = "";
						$choices = "";
						$correct = "";
						$margin = 0.0;
    					$abs_answer = "";
    					$units = "";
    					$dead_per_attempt = "";
    					$correct = "";
    					$show_answer_when_expired = "no";
    					$attempts = 1;
    					$ded_per_attempt = 0;
    					$attach_bg = "";
    					
						if (!isset($part[$i]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points the correct answer is worth.'));
						$points = $part[$i];
						
						return(Array(
									'ok' => true,
									'type' => 'sa',
									'qtext' => $qtext,
									'choices' => $choices,
									'points' => $points,
									'attempts' => $attempts,
									'ded_per_attempt' => abs($ded_per_attempt),
									'correct' => $correct,
									'show_answer_when_expired' => $show_answer_when_expired,
									'margin' => $margin,
									'abs_answer' => $abs_answer,
									'units' => $units,
									'attach_bg' => $attach_bg
								));
						break;
						
					case "dr":
						$i = 2;
						$answer = "";
						$choices = "";
						$correct = "";
						$margin = 0.0;
    					$abs_answer = "";
    					$units = "";
    					$dead_per_attempt = "";
    					$correct = "";
    					$show_answer_when_expired = "no";
    					$attempts = 1;
    					$ded_per_attempt = 0;
    					$attach_bg = "";
    					
    					if (!isset($part[$i]))
							return(Array('ok' => false,'error' => 'You didn\'t say if the attachment (if any) should be used as the background image of the canvas.'));
						$attach_bg = $part[$i];
    					
						if (!isset($part[$i+1]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points the correct answer is worth.'));
						$points = $part[$i+1];
						
						return(Array(
									'ok' => true,
									'type' => 'dr',
									'qtext' => $qtext,
									'choices' => $choices,
									'points' => $points,
									'attempts' => $attempts,
									'ded_per_attempt' => abs($ded_per_attempt),
									'correct' => $correct,
									'show_answer_when_expired' => $show_answer_when_expired,
									'margin' => $margin,
									'abs_answer' => $abs_answer,
									'units' => $units,
									'attach_bg' => $attach_bg
								));
						break;
					case 'at':
						$i = 2;
						$answer = "";
						$choices = "";
						$correct = "";
						$margin = 0.0;
    					$abs_answer = "";
    					$units = "";
    					$dead_per_attempt = "";
    					$correct = "";
    					$show_answer_when_expired = "no";
    					$attempts = 1;
    					$ded_per_attempt = 0;
    					$attach_bg = "";
    					
						if (!isset($part[$i]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points the attachment will be worth.'));
						$points = $part[$i];
						
						return(Array(
									'ok' => true,
									'type' => 'at',
									'qtext' => $qtext,
									'choices' => $choices,
									'points' => $points,
									'attempts' => $attempts,
									'ded_per_attempt' => abs($ded_per_attempt),
									'correct' => $correct,
									'show_answer_when_expired' => $show_answer_when_expired,
									'margin' => $margin,
									'abs_answer' => $abs_answer,
									'units' => $units,
									'attach_bg' => $attach_bg
								));
						break;
					case 'pr':
						$answer = "";
						$choices = "";
						$correct = "";
						$margin = 0.0;
    					$abs_answer = "";
    					$units = "";
    					$dead_per_attempt = "";
    					$correct = "";
    					$show_answer_when_expired = "no";
    					$attempts = 1;
    					$ded_per_attempt = 0;
    					$attach_bg = "";
    					$points = 0;
    					
    					$i = 1;
    					$probs = "";
    					while(isset($part[$i]) && $i < count($part))
							{
								if ($part[$i] == '#end')
									{
										$i++;
										break;
									}
								$probs .= ltrim($part[$i],'*') . "<![CDATA[";
								if (substr($part[$i],0,1) == '*')
									$correct = ltrim($part[$i],'*');
								$i++;	
							}
    	
						if (!isset($part[$i]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points are earned for an up-vote.'));	
						$points_for_up_vote = $part[$i];
						if (!isset($part[$i+1]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points are earned for a down-vote.'));	
						$points_for_down_vote = $part[$i+1];
						if (!isset($part[$i+2]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points are earned for participating in the voting process.'));	
						$points_for_participating = $part[$i+2];
						if (!isset($part[$i+3]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many minutes must elapse between voting.'));	
						$time_between_votes = $part[$i+3];
						if (!isset($part[$i+4]))
							return(Array('ok' => false,'error' => 'You didn\'t say yes/no if the class owner should be assigned problems too.'));	
						$owner_get_assignments = $part[$i+4];
						return(Array(
										'ok' => true,
										'type' => 'pr',
										'qtext' => $qtext,
										'points_for_up_vote' => $points_for_up_vote,
										'points_for_down_vote' => $points_for_down_vote,
										'points_for_participating' => $points_for_participating,
										'time_between_votes' => $time_between_votes,
										'owner_get_assignments' => $owner_get_assignments,
										'probs' => $probs,
										'points' => $points,
										'attempts' => $attempts,
										'ded_per_attempt' => abs($ded_per_attempt),
										'correct' => $correct,
										'show_answer_when_expired' => $show_answer_when_expired,
										'margin' => $margin,
										'abs_answer' => $abs_answer,
										'units' => $units,
										'attach_bg' => $attach_bg
									));
						break;
					case 'pr_ask':
						$answer = "";
						$choices = "";
						$correct = "";
						$margin = 0.0;
    					$abs_answer = "";
    					$units = "";
    					$dead_per_attempt = "";
    					$correct = "";
    					$show_answer_when_expired = "no";
    					$attempts = 1;
    					$ded_per_attempt = 0;
    					$attach_bg = "";
    					$points = 0;
    					
						return(Array(
										'ok' => true,
										'type' => 'pr_ask',
										'qtext' => $qtext,
										'points_for_up_vote' => $points_for_up_vote,
										'points_for_down_vote' => $points_for_down_vote,
										'points_for_participating' => $points_for_participating,
										'time_between_votes' => $time_between_votes,
										'owner_get_assignments' => $owner_get_assignments,
										'probs' => $probs,
										'points' => $points,
										'attempts' => $attempts,
										'ded_per_attempt' => abs($ded_per_attempt),
										'correct' => $correct,
										'show_answer_when_expired' => $show_answer_when_expired,
										'margin' => $margin,
										'abs_answer' => $abs_answer,
										'units' => $units,
										'attach_bg' => $attach_bg
									));
						break;
						
					case "ln":
						$i = 2;
						$answer = "";
						$choices = "";
						$correct = "";
						$margin = 0.0;
    					$abs_answer = "";
    					$units = "";
    					$dead_per_attempt = "";
    					$correct = "";
    					$show_answer_when_expired = "no";
    					$attempts = 1;
    					$ded_per_attempt = 0;
    					$attach_bg = "";
    					
						if (!isset($part[$i]))
							return(Array('ok' => false,'error' => 'You didn\'t say how many points the link content should be graded against.'));
						$points = $part[$i];
						
						return(Array(
									'ok' => true,
									'type' => 'ln',
									'qtext' => $qtext,
									'choices' => $choices,
									'points' => $points,
									'attempts' => $attempts,
									'ded_per_attempt' => abs($ded_per_attempt),
									'correct' => $correct,
									'show_answer_when_expired' => $show_answer_when_expired,
									'margin' => $margin,
									'abs_answer' => $abs_answer,
									'units' => $units,
									'attach_bg' => $attach_bg
								));
						break;
				
			}
		return(Array('ok' => true));
		}
    	
    
    function create_question($user_hash,$class_hash,$question,$deadline_date,$deadline_time)
    {
    	$deadline_ts = $this->Question->compute_deadline($deadline_date,$deadline_time);
    	$deadline_nice = $deadline_date . " at " . $deadline_time;
    	$ret = $data = $this->Question->parse_question($question);
    	if ($ret['ok'] === false)
    		return(Array('ok' => false,'error' => $ret['error'],'question_hash' => ''));
    		
    	if ($data['type'] != 'pr')
    		{
				$question_hash = md5($user_hash . $class_hash . $question . time() . $deadline_ts);
				$this->db->query("insert into question values(NULL," .
							$this->db->escape($question_hash) . "," .
							$this->db->escape($user_hash) . "," .
							$this->db->escape($class_hash) . "," .
							$this->db->escape($data['type']) . "," .
							$this->db->escape($question) . "," .
							$this->db->escape($data['qtext']) . "," .
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
    	else 
    		{
    			$this->Pr->create_question($user_hash,$class_hash,$data,$deadline_nice,$deadline_ts);
    			$question_hash = "";
    		}
    	return(Array('ok' => true,'question_hash' => $question_hash));
    	
    }
    
    
    function generate_html($qdata,$question_hash,$mode)
    {
    	$data = $this->Question->parse_question($qdata);
    	if ($data['ok'] === false)
    		{
    			echo $data['error'];
    			return;
    		}
    		
    		
    	if ($mode == 'real')
    		{
    			if ($data['type'] == 'dr')
    				echo form_open("welcome/incoming_answer/$question_hash",Array('onsubmit' => 'grab_drawing();'));
    			else if ($data['type'] == 'at')
    				{
    					echo form_open_multipart("welcome/incoming_answer/$question_hash");
    				}
    			else echo form_open("welcome/incoming_answer/$question_hash");
    		}
    		
    	echo $this->Question->get_html_attachment_list($question_hash);
    	//$raw = $this->Question->get_raw_question_data($question_hash);
    	$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
    	switch($data['type'])
    		{
    			case 'mc':
							echo $data['qtext'];
							echo "<p/>";
							$choices = explode("<![CDATA[",$data['choices']);
							shuffle($choices);
							foreach ($choices as $choice)
								{
									if (!is_blank($choice))
										{
											$ans = urlencode($choice);
											if ($choice == $data['correct'] && $mode == 'test')
												{
													echo "<div id=\"answer_choice_correct\"><input type=radio name=answer value=\"$ans\"> $choice</div>";
												}
											else echo "<div id=\"answer_choice\"><input type=radio name=answer value=\"$ans\"> $choice</div>";
											echo "<br/>";
										}
								}
							echo "<p/>";
							break;
				case 'num':
							echo $data['qtext'];
							echo "<p/>";
							echo "<input id=\"form_input\" size=20 name=answer /> ";
							echo $data['units'];
							echo "<p/>";
							if ($mode == 'test')
								{
									echo "<div id=\"answer_choice_correct\">Answer is correct if: ";
									$low = $data['correct'] - $data['margin']/100.0 * $data['correct'];
									$high = $data['correct'] + $data['margin']/100.0 * $data['correct'];
									echo "$low $\le ";
									if ($data['abs_answer'] == 'yes')
										echo "|$ their-answer $|";
									else echo "$ their-answer $";
									echo "\le $high$";
									echo "</div>";
    							}
							break;
				case 'sa':
						echo $data['qtext'];
						echo "<p/>";
						if ($mode != 'waiting' && $mode != 'first_done')
							{
								echo "<textarea id=\"form_input\" cols=60 rows=5 name=answer /> ";
								echo "</textarea>";
								echo "<p/>";
							}
						break;
				case 'dr':
						echo $data['qtext'];
						echo "<p/>";
						echo "<input type=hidden name=answer id=answer />";
						echo $this->Drawing->html($question_hash);
						break;
				case 'at':
					echo $data['qtext'];
					echo "<p/>";
					if ($mode != 'waiting' && $mode != 'first_done')
						{
							echo "<input type=\"file\" name=\"userfile\"/> ";
							echo "<p/>";
						}
					break;
				case 'pr':
					echo $data['qtext'];
					echo "<br/>";
					$user_hash = $this->session->userdata('user_hash');
					if ($this->Pr->assigned_to_user($user_hash,$question_hash))
						{
							echo "<input id=\"form_input\" size=50 name=answer /> ";
							echo "<div id=\"note\">Paste a live, click-ready share link to your answer here.</div>";
						}
					else
						{
							echo "<span id=\"response\">Posted response: " . $this->Pr->get_answer_as_link($question_hash) . "</span>";
							echo "<br/>";
							$ret = $this->Pr->ok_to_vote($user_hash,$question_hash);
							if ($ret['ok'] === true)
								{
									echo "<div id=\"answer_choice\"><input type=radio name=answer value=\"up\">Up vote</div>";
									echo "<br/>";
									echo "<div id=\"answer_choice\"><input type=radio name=answer value=\"down\">Down vote</div>";
									echo "<p/>";
									echo "<div id=\"downvote_box\">";
									echo "If you are <b>down voting</b>, please (briefly) say why (reason will be posted anonymously): ";
									echo "<br/>";
									echo "<input id=downvote_why type=text name=downvote_why size=50>";
									echo "</div>";
								}
							else
								{
									$min = ceil($ret['wait']);
									echo "<div id=\"too_fast\">You are voting on answers too fast.  Slow down a bit.  You can vote again in about $min minutes.</div>";
									$mode = "too_fast";
								}
						}
					echo "<p/>";
					break;
				case 'ln':
						echo $data['qtext'];
						echo "<p/>";
						if ($mode != 'waiting' && $mode != 'first_done')
							{
								echo "Paste link here: ";
								echo "<input id=\"link_input\" size=70 name=answer /> ";
								echo "<p/>";
							}
						break;
							
						
			}
			
		switch($mode)
			{
				case 'real':
						echo "<input type=submit>";
						echo " | ";
						echo anchor("welcome/question_menu/$class_hash","Cancel",Array('id' => 'return_link'));
						echo form_close();
						break;
				case 'expired':
				case 'no_more_tries';
				case 'done':
				case 'waiting':
				case 'first_done':
				case 'too_fast':
						if ($mode == 'expired')
							{
								echo $this->Answer->get_answer_on_expired($question_hash);
								echo "<p/>";
							}
						echo anchor("welcome/question_menu/$class_hash","Return");
						break;
				case 'preview':
						echo anchor("welcome/class_menu/$class_hash","Return");
						break;									
			}					

		
			echo "<hr/>";
			if ($data['type'] != 'pr')
			{
				echo "<div id=\"rules\">";
				echo "Rules: ";
				echo "<ul>";
				echo "<li> This question is worth <b>" . $data['points'] . "</b> point(s).";
				echo "<li> You may make <b>" . $data['attempts'] . "</b> attempts to answer this question.";
				echo "<li> There is a <b>" . $data['ded_per_attempt'] . "</b> point deduction per attempt.";
				echo "</ul>"; 
				echo "<hr/>";
				echo "</div>";
				echo "<h2>Your previous answers</h2>";
			}
		else
			{
				echo "<div id=\"rules\">";
				echo "Rules: ";
				echo "<ul>";
				echo "<li> You are to submit some kind of Internet-ready \"share link\" that points to your work (Dropbox, Youtube, Google-docs, etc.)";
				echo "<li> You will earn " . $data['points_for_up_vote'] . " point(s) if someone up-votes your solution";
				echo "<li> You will lose " . $data['points_for_down_vote'] . " point(s) if someone down-votes your solution";
				echo "<li> When you up or down vote the work of others, at least " . $data['time_between_votes'] . " minutes must elapse between votes.";
				echo "</ul>"; 
				echo "<hr/>";
				echo "</div>";
			}
    		
    }
    
    function get_html($question_hash,$mode)
    {
    	$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
    	$row = $q->row_array();
    	$this->Question->generate_html($row['raw_input'],$question_hash,$mode);
    }    
    
    function dump_question_list_for_owner($class_hash)
	{
		$user_hash = $this->User->get_user_hash_from_class_hash($class_hash);
		$sort = $this->Sticky->get($user_hash,"question_sort");
			
		if (empty($sort))
			$sort = 'deadline';

		switch($sort)
			{
				case 'create_date':
							$sort_msg =  "Date created";
							$sql = "order by question_id desc";
							break;
				case 'deadline':
							$sort_msg = "Deadline";
							$sql = "order by deadline_ts desc";
							break;
			}
		
		//$q = $this->db->query("select * from question where class_hash=" . $this->db->escape($class_hash) . " order by deadline_ts desc");
		$q = $this->db->query("select * from question where class_hash=" . $this->db->escape($class_hash) . " $sql");

		echo "<div id=\"info_box_full\">";
		echo anchor("welcome/create_question/$class_hash","Write a question",Array('id' => 'create_link'));
		echo " | ";
		echo anchor("welcome/main_menu","Return",Array('id' => 'create_link'));
		echo " | ";
		echo "<a href=\"javascript:void(0);\" onclick=\"open_status_window();\" id=\"create_link\">Status</a>";
		echo " | ";
		echo anchor("welcome/dump_questions/$class_hash","Dump Questions",Array('id' => 'create_link'));
		echo " | ";
		echo "<span id=\"create_link\">";
		echo "Questions sort: ";
		if ($sort == 'create_date')
			{	
				echo anchor("welcome/owner_question_sort/$user_hash/$class_hash/create_date","Creation date",Array("id" => "selected"));
				echo " ";
				echo anchor("welcome/owner_question_sort/$user_hash/$class_hash/deadline","Deadline");
			}
		else
			{	
				echo anchor("welcome/owner_question_sort/$user_hash/$class_hash/create_date","Creation date");
				echo " ";
				echo anchor("welcome/owner_question_sort/$user_hash/$class_hash/deadline","Deadline",Array("id" => "selected"));
			}

		echo "</span>";
		echo "<p/>";
		
		echo "<div id=\"status_window\" title=\"Class status\">";
		echo "Change class status to: <p/>";
		echo anchor("welcome/set_class_status/$class_hash/open","Open",Array('id' => 'create_link'));
		echo " | ";
		echo anchor("welcome/set_class_status/$class_hash/closed","Closed",Array('id' => 'create_link'));
		echo "<p/>";
		echo "[<a href=\"javascript:void(0);\" onclick=\"close_status_window();\" id=\"create_link\">Cancel</a>]";
		echo "</div>";
		if ($this->Classes->get_class_status($class_hash) != 'open')
			{
				echo "<br/>";
				echo "<div id=\"class_closed\">Closed for maintenance.</div> ";
			}
		
		
		foreach($q->result_array() as $row)
		{
			echo "<span id=\"question_abbrev\">" . substr($row['qtext'],0,100) . "...</span>";
			$ans = $this->Answer->get_answer($row['question_hash']);
			if ($ans !== false)
				echo "<span id=\"answer_abbrev\">(Answer: " . substr($ans,0,20) . ")</span>";
			echo "<span id=\"note_no_indent\"> (Due: " . $row['deadline_nice'] . ")</span>";
			
			
			echo "<div id=\"question_nav\">[";
			echo anchor("welcome/preview/" . $row['question_hash'],"Preview",Array('target' => '_blank'));
			echo " | ";
			echo anchor("welcome/edit_question/" .  $row['question_hash'],"Edit");
			echo " | ";
			echo anchor("welcome/dump_answers/" .  $row['question_hash'],"See responses");
			
			
			$ret = $this->Answer->get_waiting_count($row['question_hash']);
			if ($ret > 0)
				{
					echo " | ";
					echo anchor("welcome/grade_waiting/" . $row['question_hash'],"$ret ungraded answers",Array("id" => "hl"));
				}
			else
				{
					$ai = $this->Answer->get_answer_count($row['question_hash']);
					echo " | <span id=\"correct_count\">" . $ai['correct'] . "</span>/<span id=\"incorrect_count\">" . $ai['incorrect'] . "</span>";
				}
			echo " | Delete: ";
			echo "<a href=\"javascript:void(0);\"  onclick=\"confirm_delete_answers('" . $row['question_hash'] . "');\">Answers</a>";
			echo " | ";
			echo "<a href=\"javascript:void(0);\"  onclick=\"confirm_delete('" . $row['question_hash'] . "');\">This question</a>";
			echo "]";
			echo "</div>";
		}
	echo "</div>";
	}
	
	function run_dump_question_list_for_student($user_hash,$class_hash)
	{
		$q = $this->db->query("select * from question where class_hash=" . $this->db->escape($class_hash) . " order by deadline_ts-unix_timestamp() asc");
		$open = "";
		$close = "";
		
		foreach($q->result_array() as $row)
		{
			$dt = round(($row['deadline_ts']-time())/3600,1);
			$info = "";
			$views = $this->Question->get_view_count($row['question_hash']);

			$hide = false;
			
			if ($row['type'] == 'pr')
				{
					$answer_there = $this->Pr->answer_there($row['question_hash']);
					$for_you = $this->Pr->assigned_to_user($user_hash,$row['question_hash']);
					$hide = !$for_you && !$answer_there;

					if (!$hide)
						{
					
							$stats = $this->Pr->get_stats($row['question_hash']);
							$info .= "<span id=\"vote_box\"><span id=\"up_vote\"><span id=\"marker\">&uarr;</span>" . $stats['up'] . "</span><span id=\"down_vote\"> <span id=\"marker\">&darr;</span>" . $stats['down'] . "</span></span>";
							$info .= "</span>";
						}
				}
			
			$attempt_count = $this->Answer->get_attempt_info($user_hash,$row['question_hash']);
			$go_link =  substr($row['qtext'],0,50) . "...";
			
			if ($row['type'] != 'pr')
				{
					$qh = $this->db->escape($row['question_hash']);
					$link = $this->db->escape(site_url("welcome/issue/" . $row['question_hash']));
					$info = "<a href=\"javascript:void(0);\" id=\"go_link\" onclick=\"return update_view($link,$qh);\">$go_link</a>" . "<span id=\"view_count\">($views views)</span>";
					//$info .=  anchor("javascript:void(0);",$go_link,Array('id' => 'go_link','onclick' => "return update_view($link,$qh);")) . "<span id=\"view_count\">($views views)</span>";
				}
			else
				{
					if ($for_you)
						{
							$info .=  anchor("welcome/issue/" . $row['question_hash'],$go_link,Array('id' => 'go_link')) .  "<span id=\"view_count\">($views views)</span>";;
							$info .= "<span id=\"to_you\">Assigned to you</span>";
						}
					else if (!$for_you && !$answer_there)
						{
							if (!$hide)
								{
									$info .= $go_link;
									$info .= "<span id=\"for_review\">Assigned to someone else</span>";
									$info .= "<span id=\"unanswered\">(Unanswered.)</span>";
								}
						}
					else if (!$for_you && $answer_there)
						{
							$info .=  anchor("welcome/issue/" . $row['question_hash'],$go_link,Array('id' => 'go_link')) . "<span id=\"view_count\">($views views)</span>";;
							if (!$this->Pr->lodged_vote($user_hash,$row['question_hash']))
								$info .= "<span id=\"answer_ready\">Review please</span>";
							else $info .= "<span id=\"answer_ready_reviewed\">Reviewed. Thank you.</span>";
						}
					else if (!$hide)
						$info .= $go_link . "<span id=\"unanswered\">(Unanswered.)</span>";
				}
				
			
				
			
			if (!$hide)
				{
					$info .= "<br/>";
					$info .= "  <span id=\"question_status\">" . $this->Answer->get_status_message($user_hash,$row['question_hash']) . "</span>  ";
				}
			
			
			if (!$this->Pr->ignore_expire($row['question_hash'],$user_hash))
				{
					if ($dt <= 0)
						$info .= "<span id=\"expired_question\">Expired on " . $row['deadline_nice'] . ".</span>";
					else 
						{
							if ($dt >= 1)
								$info .= "<span id=\"open_question\">Expires in $dt hours on " . $row['deadline_nice'] . ".</span>";
							else 
								{
									$dt = round(($row['deadline_ts']-time())/60,1);
									$info .= "<span id=\"open_question\">Expires in $dt minutes on " . $row['deadline_nice'] . ".</span>";
								}
						}
				}
			else $dt = 1; 
			
			if (!$hide)
				$info .= "<p/>";
			
		
			if ($dt < 0 || $attempt_count >= $row['max_attempts'] || $this->Answer->got_it_correct($user_hash,$row['question_hash']))
				$close .= $info;
			else $open .= $info;
		}
		return(Array('open' => $open,'close' => $close));
	}
	
	function dump_question_list_for_student($user_hash,$class_hash)
	{
		echo "<div id=\"info_box_full\">";
		echo anchor("welcome/main_menu","Return",Array('id' => 'create_link'));
		echo "<p/>";
		$ret = $this->Question->run_dump_question_list_for_student($user_hash,$class_hash,'open');
		echo "<h2>Open questions</h2>";
		if (empty($ret['open']))
			echo "None";
		else echo $ret['open'];
		
		echo "<h2>Finished questions</h2>";
		if (empty($ret['close']))
			echo "None";
		else echo $ret['close'];

		echo "</div>";
	
	}

	function verify_question_owner($user_hash,$question_hash)
	{
		$q = $this->db->query("select * from question where user_hash=" . $this->db->escape($user_hash) . " and question_hash=" .$this->db->escape($question_hash));
		return($q->num_rows() == 1);
	}
	
	function get_question_data($question_hash)
	{
		$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
		$row = $q->row_array();
		$a = explode(" at ",$row['deadline_nice']);
		return(Array('raw_input' => $row['raw_input'],'deadline_date' => $a[0],'deadline_time' => $a[1]));
	}
	
	function get_raw_question_data($question_hash)
	{
		$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
		$row = $q->row_array();
		return($row);
	}
	
	function update_question($question_hash,$question,$deadline_date,$deadline_time)
	{
    	$deadline_ts = $this->Question->compute_deadline($deadline_date,$deadline_time);
    	$deadline_nice = $deadline_date . " at " . $deadline_time;
    	$data = $this->Question->parse_question($question);
    	if ($data['ok'] === false)
    		{
    			$this->db->query("update question set raw_input=" . $this->db->escape($question) . " where question_hash=" . $this->db->escape($question_hash));
    			return(Array('ok' => false,'error' => $data['error']));
    		}
    	$raw = $this->Question->get_raw_question_data($question_hash);
    	//$this->db->query("delete from question where question_hash=" . $this->db->escape($question_hash));
    	$this->db->query("update question set " .
    				"type=" . $this->db->escape($data['type']) . "," .
    				"raw_input=" . $this->db->escape($question) . "," .
    				"qtext=" . $this->db->escape($data['qtext']) . "," .
    				"answer=" . $this->db->escape($data['correct']) . "," .
    				"deadline_nice=" . $this->db->escape($deadline_nice) . "," .
    				"deadline_ts=" . $this->db->escape($deadline_ts) . "," .
    				"max_attempts=" . $this->db->escape($data['attempts']) . "," .
    				"points=" . $this->db->escape($data['points']) . "," .
    				"deduct_per_attempt=" . $this->db->escape($data['ded_per_attempt']) . "," .
    				"show_answer_when_expired=" . $this->db->escape($data['show_answer_when_expired']) . "," .
    				"margin=" . $data['margin'] . "," .
    				"abs_answer=" . $this->db->escape($data['abs_answer']) . "," .
    				"units=" . $this->db->escape($data['units']) . "," .
    				"attach_bg=" . $this->db->escape($data['attach_bg']) . " where question_hash=" . $this->db->escape($question_hash) );
    	return(Array('ok' => true,'question_hash' => $question_hash));
    }
    
    function get_class_hash_from_question_hash($question_hash)
    {
    	$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
    	if ($q->num_rows() == 0)
    		return(false);
		$row = $q->row_array();
		return($row['class_hash']);
    }
    
    function get_owner_class_hash_from_question_hash($question_hash)
    {
    	$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
    	if ($q->num_rows() == 0)
    		return(false);
		$row = $q->row_array();
		return(Array('class_hash' => $row['class_hash'],'owner_hash' => $row['user_hash']));
    }
    
    function check_user_hash_with_question_hash($user_hash,$question_hash)
    {
    	$q = $this->db->query("select * from enroll inner join question on question.class_hash=enroll.class_hash where enroll.user_hash=" . $this->db->escape($user_hash) . " and question.question_hash=" . $this->db->escape($question_hash));
    	return($q->num_rows() == 1);
    }
    
    function get_max_attempts($question_hash)
	{
		$q = $this->db->query("select max_attempts from question where question_hash=" .  $this->db->escape($question_hash));
		$row = $q->row_array();
		return($row['max_attempts']);
	} 
	
	function get_question_numerics($question_hash)
	{
		$q = $this->db->query("select max_attempts,points,deduct_per_attempt from question where question_hash=" .  $this->db->escape($question_hash));
		$row = $q->row_array();
		return($row);
	}
	
	function expired($question_hash)
	{
		$q = $this->db->query("select deadline_ts from question where question_hash=" .  $this->db->escape($question_hash));
		$row = $q->row_array();
		$dt = $row['deadline_ts'] - time();
		return($dt < 0);
	}
	
	function create_attachment($user_hash,$class_hash,$question_hash,$upload_data)
	{
		$attach_hash = md5($user_hash . $question_hash . time() . $class_hash);
		$this->db->query("delete from attach where question_hash=" . $this->db->escape($question_hash));
		$sql = "insert into attach values(NULL," .
							$this->db->escape($attach_hash) . ", " .
							$this->db->escape($user_hash) . ", " .
							$this->db->escape($class_hash) . ", " .
							$this->db->escape($question_hash) . ", " .
							$this->db->escape($upload_data['file_name']) . ", " .
							$this->db->escape($upload_data['full_path']) . ", " .
							$this->db->escape($upload_data['file_ext']) . ", " .
							$this->db->escape($upload_data['file_type']) . ")";
		$this->db->query($sql);
	}
	
	function get_html_attachment_list($question_hash)
	{
		$q = $this->db->query("select * from attach where question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows() == 0)
			return;
		$raw = $this->Question->get_raw_question_data($question_hash);
		
		foreach($q->result_array() as $row)
			{
				switch(ltrim($row['file_ext'],"."))
					{
						case 'png':
						case 'gif':
						case 'bmp':
						case 'jpg':
						case 'jpeg':
								if ($raw['type'] != 'dr' || ($raw['type'] == 'dr' && $raw['attach_bg'] == 'no'))
									{
										$url = site_url("welcome/view_attachment/" . $row['attach_hash'] . $row['file_ext']);
										echo "<center><img src=\"$url\"></center>";
									}
								break;
						default:
								echo "<div id=\"attach_link\">[".anchor("welcome/view_attachment/" . $row['attach_hash'] . $row['file_ext'],"Attachment") . "]</div>";
								break;
					}
				echo "<br/>";
			}
	
	}
	
	function get_attachment_list($question_hash)
	{
		$q = $this->db->query("select * from attach where question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows() == 0)
			return("none");
		$ret = "";
		foreach($q->result_array() as $row)
			{
				echo anchor("welcome/view_attachment/" . $row['attach_hash'],$row['file_ext'] . " file");
				echo "(";
				echo anchor("welcome/delete_attachment/" . $row['attach_hash'] . "/" . $row['class_hash'] . "/$question_hash","delete");
				echo ")";
				echo "<br/>";
			}
		
	}
	
	function delete_attachment($attach_hash)
	{
		$this->db->query("delete from attach where attach_hash=" . $this->db->escape($attach_hash));
	}
	
	function get_type($question_hash)
	{
		$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
    	$row = $q->row_array();
    	return($row['type']);
    }	
    
    function delete_question($question_hash)
    {
    	$this->db->query("delete from comment where question_hash=" . $this->db->escape($question_hash));
    	$this->db->query("delete from answer where question_hash=" . $this->db->escape($question_hash));
    	$this->db->query("delete from attach where question_hash=" . $this->db->escape($question_hash));
    	$this->db->query("delete from question where question_hash=" . $this->db->escape($question_hash));
    }
    
     function delete_question_answers($question_hash)
    {
    	$this->db->query("delete from comment where question_hash=" . $this->db->escape($question_hash));
    	$this->db->query("delete from answer where question_hash=" . $this->db->escape($question_hash));
    }
    
    function dump_all_questions($class_hash)
    {
		$q = $this->db->query("select * from question where class_hash=" . $this->db->escape($class_hash));
		foreach($q->result_array() as $row)
		{
			$qtext = $row['raw_input'];
			if (substr($qtext,0,2) == "mc")
				{
					$a = explode("//",$qtext);
					echo $a[1];
					$i = 2;
					while($a[$i] != '#end')
						{
							echo "|" . ltrim($a[$i],'*');
							$i++;
						}
					echo "<br/>";
				}
		}
	}
	
	function get_view_count($question_hash)
	{
		$q = $this->db->query("select count(*) as count from view where question_hash = " . $this->db->escape($question_hash));
		$ret = $q->row_array();
		return($ret['count']);
	}
	
	function get_share_list($question_hash)
	{
		$sql = "select * from question inner join class on question.class_hash=class.class_hash and question.user_hash != class.user_hash where question_hash=" . $this->db->escape($question_hash);
		$share_list = "";
		$q = $this->db->query($sql);
    	foreach($q->result_array() as $row)
    		$share_list .= trim($row['code']) . ",";
    	return(rtrim($share_list,","));
    }
    
    function see_if_shared($question_hash)
    {
    	$q = $this->db->query("select * from share where locate(" . $this->db->escape($question_hash) . ",question_hash_list)");
    	if ($q->num_rows() == 0)
    		return(false);
    	$row = $q->result_array();
    	return($row);
    }

	
	//$current_class_hash is the class_hash of the user who is editing the question
    //$share_list is a comma delimited list of class codes
    //$upload_data is true if there's an attachment or false if nothing to attach
    //$question_hash is the newly created or edited question_hash, owned by the person doing the editing
    function share($current_class_hash,$question_hash,$question,$deadline_date,$deadline_time,$share_list,$upload_data)
    {
    	$share_array = explode(",",$share_list);
    
    	$ret = $this->Question->see_if_shared($question_hash);
    	if ($ret === false)
    		{	
    			$shared_question_hashes = Array();
    			array_push($shared_question_hashes,$question_hash);
				foreach($share_array as $share)
				{
					$share = trim($share);
					$info = $this->Classes->get_user_and_class_hash_from_class_code($share);
					$dest_user_hash = $info['user_hash'];
					$dest_class_hash = $info['class_hash'];
					$ret = $this->Question->create_question($dest_user_hash,$dest_class_hash,$question,$deadline_date,$deadline_time);
					array_push($shared_question_hashes,$ret['question_hash']);
				}	
				$this->db->query("insert into share values(NULL," . $this->db->escape(implode(",",$shared_question_hashes)) . ")");
			}
		else
			{
				foreach($ret as $result_array)
					{	
						//$new_shared_question_hash_list = Array($question_hash);
						$a = explode(",",$result_array['question_hash_list']);
						foreach($a as $shared_question_hash)
							{
								if ($shared_question_hash != $question_hash)
									{
										$info = $this->Classes->get_user_and_class_hash_from_question_hash($shared_question_hash);
										$dest_user_hash = $info['user_hash'];
										$dest_class_hash = $info['class_hash'];
										$ret = $this->Question->create_question($dest_user_hash,$dest_class_hash,$question,$deadline_date,$deadline_time);
										//array_push($new_shared_question_hash_list,$ret['question_hash']);
										$this->db->query("delete from question where question_hash=" . $this->db->escape($shared_question_hash));
										$this->db->query("update question set question_hash=" . $this->db->escape($shared_question_hash) . " where question_hash=" . $this->db->escape($ret['question_hash']));
										//$this->db->query("delete from share where share_id=" . $result_array['share_id']);
									}
							}
					}
				//$this->db->query("insert into share values(NULL," . $this->db->escape(implode(",",$new_shared_question_hash_list)) . ")");
			}
		}
    
	
	
	function get_grader_share_link($class_hash,$question_hash)
	{
		$hash = md5($class_hash . time() . $question_hash);
		$q = $this->db->query("select * from grader where class_hash=" . $this->db->escape($class_hash) . " and question_hash=" . $this->db->escape($question_hash));
		if ($q->num_rows())
			{
				$row = $q->row_array();
				return($row['grader_share_hash']);
			}
		$this->db->query("insert into grader values(NULL," . $this->db->escape($class_hash) . " ," . $this->db->escape($question_hash). " ," . $this->db->escape($hash) . ")");
		return($hash);
	}
	
	function get_question_hash_from_grader_share($grader_share)
	{
		$q = $this->db->query("select * from grader where grader_share_hash=" . $this->db->escape($grader_share));
		if ($q->num_rows() == 0)
			return(false);
		$row = $q->row_array();
		return($row['question_hash']);
	}
	
	function verify_grader_share_hash_vs_question_hash($grader_share_hash,$question_hash)
	{
		$q = $this->db->query("select * from grader where grader_share_hash=" . $this->db->escape($grader_share_hash) . " and question_hash=" . $this->db->escape($question_hash));
		return($q->num_rows() == 1);
	}
	
	function get_deadline($question_hash)
    {
    	$q = $this->db->query("select * from question where question_hash=" . $this->db->escape($question_hash));
    	$row = $q->row_array();
		$a = explode(" at ",$row['deadline_nice']);
    	return(Array('date' => $a[0],'time' => $a[1]));
    }    
    
    function get_share_code_list($question_hash)
    {
    	//see if the question_hash was written by someone else and shared with the current user
    	$q = $this->db->query("select * from share where locate(" . $this->db->escape($question_hash) . ",question_hash_list)");
    	if ($q->num_rows() == 0)
    		return(false);
    	
    	$list = "";
    	foreach($q->result_array() as $row)
    		{
    			$a = explode(",",$row['question_hash_list']);
    			foreach($a as $shared_question_hash)
    				$list .= $this->Classes->get_class_code_from_question_hash($shared_question_hash) . ", ";
    		}
    	
    	$list = rtrim($list,", ");
    	return($list);
    }
    
    //check is a question_hash is in a share list of a given question, and if so, return the original question_hash of the source question
    function get_orig_question_hash($question_hash)
    {
    	//see if the question_hash was written by someone else and shared with the current user
    	$q = $this->db->query("select * from share where share_question_hash=" . $this->db->escape($question_hash));
    	if ($q->num_rows() == 1) //yes..we found this question_hash as in the share field under a given orig_question
    		{
    			$row = $q->row_array();
    			$question_hash = $row['orig_question_hash'];
    		}
    	return($question_hash);
    }
    
    //true if $question_hash is the original question_hash from the original author of the question
     function is_this_original($question_hash)
    {
    	$q = $this->db->query("select * from share where share_question_hash=" . $this->db->escape($question_hash));
    	return(!$q->num_rows());
    }
    
    function delete_current_class_code($class_code,$share_list)
    {
    	$a = explode(",",$share_list);
    	$a = array_map('trim', $a);
    	$b = Array();
    	foreach($a as $share)
    		{
    			if ($share != $class_code)
    				array_push($b,$share);
    		}
    	$b = array_unique($b);
    	return(implode(",",$b));
    }
    	
    	

}   
?>
