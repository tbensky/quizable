<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	 public function __construct()
       {
            parent::__construct();
            $this->load->model('User');
             $this->load->model('Classes');
               $this->load->model('Question');
             $this->load->model('Answer');
             $this->load->model('Drawing');
             $this->load->model('Sticky');
              $this->load->model('Pr');
               $this->load->model('Comments');
            $this->load->library('session');
            // Your own constructor code
       }
       
	public function index()
	{
		$this->load->view('header');
		$this->load->view('home');
		$this->load->view('footer');
	}
	
	public function main_menu()
	{
		$this->load->view('header');
		$this->load->view('main_menu');
		$this->load->view('footer');
	}
	
	public function login()
	{
		
		$user_name = $this->input->post('username');
		$user_password = $this->input->post('userpassword');
	
		if (empty($user_name) || empty($user_password))
			{
				$this->load->view('header');
				$this->load->view('login');
			}
		else
			{
				if ($this->User->reset_password($user_name))
					{
						$this->load->view('header');
						$this->load->view('reset_password',Array('user_name' => $user_name));
						$this->load->view('footer');
						return;
					
					}
				$user_hash = $this->User->validate($user_name,$user_password);
				if ($user_hash !== false)
					{
						$this->session->set_userdata(Array('user_hash' => $user_hash,'user_name' => $user_name));
						$this->main_menu();
					}
				else 
					{
						$this->load->view('header');
						$this->load->view('login',Array('msg' => 'Username and password not found. Your teacher can reset your password if you forgot it.','user_name' => $user_name));
					}
			}
		$this->load->view('footer');
	}
	
	
	public function create_account()
        {
                $this->load->helper(array('form', 'url'));
                $this->load->library('form_validation');


                $this->form_validation->set_rules('email', 'Username', 'trim|required|valid_email|is_unique[user.user_name]');
                $this->form_validation->set_rules('password', 'Password', 'trim|required|matches[password_confirm]|min_length[4]');
                $this->form_validation->set_rules('password_confirm', 'Password Re-type', 'required');
                $this->form_validation->set_error_delimiters('<div id="form_error">', '</div>');
                $this->form_validation->set_message('is_unique', 'That user name is already in use.');

                $privatekey = "6Leey-YSAAAAAABfGZ2AB743oXuEgF3oNzslDWVK";

                if ($this->form_validation->run() == FALSE)
                        {
                                $this->load->view('header');
                                $this->load->view('create_account');
                        }
                else
                        {
                                $resp = recaptcha_check_answer ($privatekey, $_SERVER["REMOTE_ADDR"], $_POST["recaptcha_challenge_field"], $_POST["recaptcha_response_field"]);
                                if ($this->form_validation->run() == TRUE && $resp->is_valid)
                                        {
                                                $user_name = $this->input->post('email');
                                                $password = $this->input->post('password');
                                                $action = $this->input->post('action');
                                                $user_hash = $this->User->create_new($user_name,$password);
                                                $this->session->set_userdata(Array('user_hash' => $user_hash,'user_name' => $user_name));
                                                $this->load->view('header');
                                                $this->load->view('main_menu',Array('msg' => ''));
                                        }
                                else
                                	{
                                		$this->load->view('header');
                                		$this->load->view('create_account',Array('msg' => 'You did the "prove you\'re human" puzzle wrong.'));
                                	}
                                }
                $this->load->view('footer');
        }

	
	public function logout()
	{
		$this->session->set_userdata(Array('user_hash' => '','user_name' => ''));
		$this->session->sess_destroy();
		$this->index();
	}
	
	public function start()
	{
		$this->load->view('header');
		$this->load->view('start');
		$this->load->view('footer');
	}	
	
	public function join()
	{
		$user_hash = $this->session->userdata('user_hash');
		if (empty($user_hash))
			$this->cant_continue("You must be logged on to join a class.");
		$this->load->view('header');
		$this->load->view('join',Array('user_hash' => $user_hash));
		$this->load->view('footer');
	}	
	
	public function create_class()
	{
		$this->load->view('header');
		$this->load->view('create_class');
		$this->load->view('footer');
	}	
	
	public function cant_continue($msg)
	{
		$this->load->view('header');
		$this->load->view('cant_continue',Array('msg' => $msg));
		$this->load->view('footer');
	}	
	
	
	public function class_menu($class_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		$this->load->view('header');
		$this->load->view('class_menu',Array('class_hash' => $class_hash));
		$this->load->view('footer');
	}	
	
	public function create_class_incoming()
	{
		$user_hash = $this->session->userdata('user_hash');
		if (empty($user_hash))
			{
				$this->cant_continue('You must be logged on to continue.');
				return;
			}
		$title = trim($this->input->post('title'));
		$code = trim($this->input->post('code'));
		
		$this->load->view('header');
		if (empty($title) || empty($code))
			$this->load->view('create_class',Array('msg' => 'Please fill out both the class title and code boxes'));
		else 
			{
				if ($this->Classes->class_code_exists($code))	
					{
						$this->load->view('create_class',
								Array('msg' => "Sorry, a class with code <b>$code</b> already exists.",
										'title' => $title,
										'code' => $code)
								);
					}
				else
					{
						$this->Classes->create_class($user_hash,$title,$code);
						$this->load->view('main_menu',Array('msg' => "Class $code created."));
					}
			}
		$this->load->view('footer');
	}
	
	public function create_question($class_hash)
	{
		$this->load->view('header');
		$this->load->view('create_question',Array('class_hash' => $class_hash));
		$this->load->view('footer');
	}	
	
	public function question_incoming($how,$class_or_question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		if ($how == 'new' && !$this->Classes->verify_class_owner($user_hash,$class_or_question_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		if ($how == 'edit' && !$this->Question->verify_question_owner($user_hash,$class_or_question_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this question.');
				return;
			}
			
		$this->load->view('header');
		$question = trim($this->input->post('question'));
		$share_list = trim($this->input->post('share_list'));
		$deadline_date = trim($this->input->post('deadline_date'));
		$deadline_time = trim($this->input->post('deadline_time'));
		
		$this->Sticky->set($user_hash,'deadline_date',$deadline_date);
		$this->Sticky->set($user_hash,'deadline_time',$deadline_time);
		$this->Sticky->set($user_hash,'share_list',$share_list);
		
		
		$config['upload_path'] = './uploads/';
		$config['allowed_types'] = 'gif|jpg|png|pdf|doc|jpeg|txt|dat|csv';

		$this->load->library('upload', $config);
		
		$upload = false;

		if ($this->upload->do_upload())
		{
			$data = $this->upload->data();
			$file_name = $data['file_name'];
			$file_type = $data['file_type'];
			$full_path = $data['full_path'];
			$file_ext = $data['file_ext'];
			$upload_data = Array('file_name' => $file_name,
									'file_type' => $file_type,
									'full_path' => $full_path,
									'file_ext' => $file_ext
								);
			$upload = true;
		}
		
		switch($how)
			{
				case 'new':
							$class_hash = $class_or_question_hash;
							$ret = $this->Question->create_question($user_hash,$class_hash,$question,$deadline_date,$deadline_time);
							if ($ret['ok'] !== false)
								{
									$this->load->view('class_menu',Array('class_hash' => $class_hash,'msg' => 'Question saved.'));
									if ($upload === true)
										$this->Question->create_attachment($user_hash,$class_hash,$ret['question_hash'],$upload_data);
									$question_hash = $ret['question_hash'];
									if ($upload === true)
										$this->Question->share($class_hash,$question_hash,$question,$deadline_date,$deadline_time,$share_list,$upload_data);
									else $this->Question->share($class_hash,$question_hash,$question,$deadline_date,$deadline_time,$share_list,false);
								}
							else $this->load->view('create_question',Array('class_hash' => $class_hash,'question_hash' => $ret['question_hash'],'msg' => $ret['error'],'question' => $question));
							break;
				case 'edit':
							$question_hash = $class_or_question_hash;
							$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
							$ret = $this->Question->update_question($question_hash,$question,$deadline_date,$deadline_time);
							if ($ret['ok'] !== false)
								{
									$this->load->view('class_menu',Array('class_hash' => $class_hash,'msg' => 'Question updated.'));
									if ($upload === true)
										$this->Question->create_attachment($user_hash,$class_hash,$ret['question_hash'],$upload_data);
									if ($upload === true)
										$this->Question->share($class_hash,$question_hash,$question,$deadline_date,$deadline_time,$share_list,$upload_data);
									else $this->Question->share($class_hash,$question_hash,$question,$deadline_date,$deadline_time,$share_list,false);
								}
							else $this->load->view('create_question',Array('class_hash' => $class_hash,'question_hash' => $question_hash,'msg' => $ret['error']));
							
							break;
			}
		$this->load->view('footer');
	}	
	
	public function simulate()
	{
		$qdata = trim($this->input->post('qdata'));
		//echo $qdata;
		$question_hash = trim($this->input->post('question_hash'));
		echo $this->Question->generate_html($qdata,$question_hash,'test');
	}
	
	public function incoming_join()
	{
		$user_hash = $this->session->userdata('user_hash');
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to join a class.");
				return;
			}
		$code = trim($this->input->post('class_code'));
		$last = trim($this->input->post('last'));
		$first = trim($this->input->post('first'));
		
		if (strlen($last) < 2 || strlen($first) < 2)
			{
				$this->load->view('header');
				$this->load->view('join',Array('msg' => 'Your first and last names are too short.','user_hash' => $user_hash));
				$this->load->view('footer');
				return;
			}
		
		$class_hash = $this->Classes->get_class_hash_from_code($code);
		if ($class_hash === false)
			{
				$this->load->view('header');
				$this->load->view('join',Array('msg' => 'That class code does not exist.','user_hash' => $user_hash));
				$this->load->view('footer');
				return;
			}
		if ($this->Classes->already_enrolled($user_hash,$class_hash))
			{
				$this->load->view('header');
				$this->load->view('join',Array('msg' => 'You already joined that class.','user_hash' => $user_hash));
				$this->load->view('footer');
				return;
			}
		$this->Classes->enroll($user_hash,$class_hash,$last,$first);
		$this->load->view('header');
		$this->load->view('main_menu',Array('msg' => "You are now joined into class: $code"));
		$this->load->view('footer');
	}	
	
	function question_menu($class_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to see the issued questions.");
				return;
			}
		$this->load->view('header');
		$this->load->view('question_menu',Array('class_hash' => $class_hash,'user_hash' => $user_hash));
		$this->load->view('footer');
	}
	
	function edit_question($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to edit this quesiton.");
				return;
			}
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
		if (!$this->Question->verify_question_owner($user_hash,$question_hash))
			{
				$this->load->view('header');
				$this->load->view('question_menu',Array('class_hash' => $class_hash,'user_hash' => $user_hash,'msg' => 'You are not the owner of that question'));
				$this->load->view('footer');
				return;
			}
		$this->load->view('header');
		$this->load->view('create_question',Array('class_hash' => $class_hash,'question_hash' => $question_hash));
		$this->load->view('footer');
	}
	
	public function issue($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to answer a question.");
				return;
			}
		
		if (!$this->Question->check_user_hash_with_question_hash($user_hash,$question_hash))
			{
				$this->load->view('header');
				$this->load->view('main_menu',Array('msg' => "That question is not for a class that you\'ve joined."));
				$this->load->view('footer');
				return;
			}
		$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
		$mode = "real";
		if ($this->Answer->got_it_correct($user_hash,$question_hash))
			$mode = "done";
		if ($this->Answer->waiting_to_be_graded($user_hash,$question_hash))
			$mode = "waiting";
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
		$this->load->view('header');
		$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'class_hash' => $class_hash,'mode' => $mode,'attempt_count' => $attempt_count));
		$this->load->view('footer');
	}	
	
	public function preview($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to preview a question.");
				return;
			}
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
		$this->load->view('header');
		$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'class_hash' => $class_hash,'mode' => 'preview'));
		$this->load->view('footer');
	}	
	
	public function incoming_answer($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to join a class.");
				return;
			}
		if (!$this->Question->check_user_hash_with_question_hash($user_hash,$question_hash))
			{
				$this->load->view('header');
				$this->load->view('main_menu',Array('msg' => "That question it not for a class that you\'ve joined."));
				$this->load->view('footer');
				return;
			}
		$type = $this->Question->get_type($question_hash);
		if ($type == 'at')
			{
				$config['upload_path'] = './uploads/';
				$config['allowed_types'] = 'gif|jpg|png|jpeg|bmp|doc|pdf|txt|dat|csv';

				$this->load->library('upload', $config);

				if ( ! $this->upload->do_upload("userfile"))
				{
					$error = trim(strip_tags($this->upload->display_errors()));
					$this->load->view('header');
					$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
					$msg = "Something went wrong with your attachment.<br/>Error code: $error";
					$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'msg' => $msg,'mode' => 'real','attempt_count' => $attempt_count));
					$this->load->view('footer');
					return;
				}
				else
				{
					$data =$this->upload->data();
					$file_name = $data['file_name'];
					$file_type = $data['file_type'];
					$full_path = $data['full_path'];
					$file_ext = $data['file_ext'];
					$upload_data = Array('file_name' => $file_name,
											'file_type' => $file_type,
											'full_path' => $full_path,
											'file_ext' => $file_ext
										);
					$answer = http_build_query($upload_data);
				}
		}
		if ($type != 'at')
			$answer = $this->input->post('answer');
		if ($type != 'dr' && $type != 'at')
			{
				$answer = trim($this->input->post('answer'));
				$in_ds = false;
				for($i=0;$i<strlen($answer);$i++)
						{
								if ($answer[$i] == '$')
										$in_ds = !$in_ds;
								if ($answer[$i] == '+' && !$in_ds)
										$answer[$i] = ' ';
						}

			}
		
		if ($type == "ln" && filter_var($answer, FILTER_VALIDATE_URL) === false)
			{
				$this->load->view('header');
				$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
				$uanswer = str_replace(" ","%20",$answer);
				$yes_per_20 = "";
				if (strstr($uanswer,"%20"))
					$yes_per_20 = "<p/><li><span id=\"yellow_error\">Note: You seem to have one or more %20's in your link. This is making the link invalid to Quizable.  To fix this, remove the spaces in the original name of the file you are trying to share and regenerate the share link. Your share link should not have any %20 sequences in it (and removing spaces in your original share file name will remove these).</span>";
				$err =<<<EOT
Your link of
<p/>
$uanswer
<p/>
is not a valid link format.  Here is perhaps why:
<ol>
<li> Your link simply isn't valid (Does it start with 'http://''? Does it have at least two periods (.) in it? Is it something that you can paste into a browser location bar and be taken to a web page? Trying testing it in your browser.
$yes_per_20
<p/>
<li> You have strange characters in the name of your file. Please remove anything like a $, #, !, *, +, commas, = signs, etc. from the name of the file you are trying to share.  Stick only with letters, numbers, dashes (-) and underbars (_).  When done, regenerate your share link.
</ol>
EOT;
				
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'msg' => $err ,'mode' => 'real','attempt_count' => $attempt_count));
				$this->load->view('footer');
				return;
			}
			
		if ($type == "num" && filter_var($answer,FILTER_VALIDATE_FLOAT) === false)
			{
				$this->load->view('header');
				$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'msg' => 'Your answer must be a number.  <ul><li> Do not include any units in your answer. <li> If you are trying to use scientific notation, then use "e" notation, meaning $6.02\times 10^{23}$ would be input as 6.02e23.</ul>','mode' => 'real','attempt_count' => $attempt_count));
				$this->load->view('footer');
				return;
			
			}
			
		
		if (is_numeric($answer) && empty($answer))
			$answer = "0.0";
		if (is_blank($answer))
			{
				$this->load->view('header');
				$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'msg' => 'Your forgot to supply an answer.','mode' => 'real','attempt_count' => $attempt_count));
				$this->load->view('footer');
				return;
			}
		if ($this->Question->expired($question_hash) && !$this->Pr->ignore_expire($question_hash,$user_hash))
			{
				$this->load->view('header');
				$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'msg' => 'Sorry, this question has expired.','mode' => 'real','attempt_count' => $attempt_count));
				$this->load->view('footer');
				return;
			}
		
		if ($this->Answer->all_tries_used($user_hash,$question_hash))
			{
				$this->load->view('header');
				$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'msg' => 'Sorry, you have used all of your allowed attempts.','mode' => 'real','attempt_count' => $attempt_count));
				$this->load->view('footer');
				return;
			}
			
		if ($this->Answer->got_it_correct($user_hash,$question_hash))
			{
				$this->load->view('header');
				$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'msg' => 'You already submitted a correct answser to this question.','mode' => 'real','attempt_count' => $attempt_count));
				$this->load->view('footer');
				return;
			}
		$this->load->view('header');
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
		$mode = "real";
		
	
		if ($type == 'sa' || $type == 'dr' || $type == 'at' || $type == 'ln')
			{
				$this->Answer->new_answer($user_hash,$question_hash,$class_hash,$answer,'waiting',0.0);	
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'points' => 0.0,'type' => $type,'class_hash' => $class_hash,'msg' => "Your answer has been received.  It will be graded by your instructor.",'mode' => 'first_done','attempt_count' =>1));
			}
		else if ($type == 'pr')
				{
					if ($answer == 'up' || $answer == 'down')
						{
							$downvote_why = $this->input->post("downvote_why");
							if (!empty($downvote_why))
								$this->Comments->post_downvote_comment($question_hash,$downvote_why);
						}
					$this->Answer->new_answer($user_hash,$question_hash,$class_hash,$answer,'yes',0.0);	
					$this->Pr->handle_vote($user_hash,$question_hash,$answer);
					$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'points' => 0.0,'type' => $type,'class_hash' => $class_hash,'msg' => "Your answer has been received.",'mode' => 'first_done','attempt_count' =>1));
				}
		else if ($this->Answer->is_correct($question_hash,$answer))
			{
				$points = $this->Answer->get_points_worth($user_hash,$question_hash);
				$this->Answer->new_answer($user_hash,$question_hash,$class_hash,$answer,'yes',$points);
				$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
				$points = $this->Answer->get_points_earned_for_correct($user_hash,$question_hash);
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'points' => $points,'class_hash' => $class_hash,'msg' => "Correct! <b>\"$answer\"</b> is the right answer!.",'mode' => 'first_done','attempt_count' => $attempt_count));
			}
		else  
			{
				$this->Answer->new_answer($user_hash,$question_hash,$class_hash,$answer,'no',0.0);
				$attempt_count = $this->Answer->get_attempt_info($user_hash,$question_hash);
				$this->load->view('issue_question',Array('user_hash' => $user_hash,'question_hash' => $question_hash,'msg_red' => "Sorry, <b>\"$answer\"</b> is not the correct answer.",'mode' => 'real','attempt_count' => $attempt_count));
			}
		$this->load->view('footer');
	}	
	
	function dump_answers($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to answer a question.");
				return;
			}
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		
		$this->load->view('header');
		$this->load->view('dump_answers',Array('question_hash' => $question_hash,'class_hash' => $class_hash));
		$this->load->view('footer');
	}
	
	function edit_answer($answer_hash,$class_hash)
	{	
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to answer a question.");
				return;
			}
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		
		$this->load->view('header');
		$this->load->view('edit_answer',Array('answer_hash' => $answer_hash,'class_hash' => $class_hash));
		$this->load->view('footer');
	}
	
	function delete_answer($answer_hash,$question_hash,$class_hash)
	{	
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to answer a question.");
				return;
			}
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
			
		$this->Answer->delete($answer_hash);
		
		$this->load->view('header');
		$this->load->view('dump_answers',Array('question_hash' => $question_hash,'class_hash' => $class_hash,'msg' => 'Answer deleted.'));
		$this->load->view('footer');
	}
	
	function edit_answer_incoming($class_hash,$question_hash,$answer_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to answer a question.");
				return;
			}
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
	
		$answer = trim($this->input->post('answer'));
		$points = trim($this->input->post('points'));
		$correct = trim($this->input->post('correct'));
		$comment = trim($this->input->post('comment'));
		
		$this->Answer->update($answer_hash,$answer,$points,$correct);
		$this->Answer->update_grading_comment($answer_hash,$comment);
		
		$this->load->view('header');
		$this->load->view('dump_answers',Array('question_hash' => $question_hash,'class_hash' => $class_hash,'msg' => 'Answer updated.'));
		$this->load->view('footer');
		
	}
	
	
	function insert_answer_incoming($class_hash,$question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to answer a question.");
				return;
			}
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
	
		$student_hash = trim($this->input->post('student_hash'));
		$answer = trim($this->input->post('answer'));
		$points = trim($this->input->post('points'));
		$correct = trim($this->input->post('correct'));
		$comment = trim($this->input->post('comment'));
		$status = 'graded';
		$answer_hash = md5(time() . $user_hash . $answer);
		$raw = $this->Question->get_raw_question_data($question_hash);
		$dt_deadline = $raw['deadline_ts'] - time();

		
		$sql = "insert into answer values(NULL," .
						$this->db->escape($answer_hash) . "," .
						$this->db->escape($question_hash) . "," .
						$this->db->escape($student_hash) . "," .
						$this->db->escape($class_hash) . "," .
						$this->db->escape($answer) . "," .
						$this->db->escape($correct) . "," .
						$this->db->escape($points) . "," .
						$this->db->escape($status) . "," .
						$this->db->escape($raw['type']) . "," .
						$dt_deadline . ",unix_timestamp(),now())";
		$this->db->query($sql);
						
		$this->Answer->update_grading_comment($answer_hash,$comment);
		
		$this->load->view('header');
		$this->load->view('dump_answers',Array('question_hash' => $question_hash,'class_hash' => $class_hash,'msg' => 'Answer inserted.'));
		$this->load->view('footer');
		
	}
	
	function view_attachment($attach_hash_with_extension)
	{
		$a = explode(".",$attach_hash_with_extension);
		$attach_hash = $a[0];
		
		$q = $this->db->query("select * from attach where attach_hash=" . $this->db->escape($attach_hash));
		$row = $q->row_array();
		$file_name = "file" . $row['file_ext'];
		header('Content-Type: application/octet-stream');
		 header('Content-Type: ' . $row['file_type']);
		header("Content-Transfer-Encoding: Binary"); 
		header("Content-disposition: attachment; filename=\"".$file_name."\""); 
		readfile($row['full_path']);
	}
	
	function view_answer_attachment($answer_hash_with_extension)
	{
		$a = explode(".",$answer_hash_with_extension);
		$answer_hash = $a[0];
		
		$q = $this->db->query("select * from answer where answer_hash=" . $this->db->escape($answer_hash));
		$data = $q->row_array();
		parse_str($data['answer'],$row);
		$file_name = "quizable_file" . $row['file_ext'];
		header('Content-Type: application/octet-stream');
		 header('Content-Type: ' . $row['file_type']);
		header("Content-Transfer-Encoding: Binary"); 
		header("Content-disposition: attachment; filename=\"".$file_name."\""); 
		readfile($row['full_path']);
	}
	
	function delete_attachment($attach_hash,$class_hash,$question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to delete an attachment.");
				return;
			}
			
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		$this->Question->delete_attachment($attach_hash);
		$this->load->view('header');
		$this->load->view('create_question',Array('class_hash' => $class_hash,'question_hash' => $question_hash,'msg' =>'Attachment deleted.'));
		$this->load->view('footer');
	
	}
	
	function student_report($class_hash,$sort_by)
	{
	
		$user_hash = $this->session->userdata('user_hash');

		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to run a student report.");
				return;
			}
	
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		$this->load->view('header');
		$this->load->view('student_report',Array('class_hash' => $class_hash,'user_hash' => $user_hash,'sort_by' => $sort_by));
		$this->load->view('footer');
			
	
	}
	
	function dropbox_student_report($user_hash,$class_hash)
	{
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		$this->load->view('student_report',Array('class_hash' => $class_hash,'user_hash' => $user_hash,'sort_by' => 'dropbox'));
	}
	
	function grade_waiting($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to grade answers.");
				return;
			}
		
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);	
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
			
		$this->load->view('header');
		$this->load->view('grade_waiting',Array('question_hash' => $question_hash));
		$this->load->view('footer');
	}
	
	function dump_waiting_answers($question_hash)
	{
		echo $this->Answer->get_waiting_answers($question_hash);
	}
	
	function incoming_comment($question_hash)
	{
		$comment = trim($this->input->post("comment"));
		$answer_hash = trim($this->input->post("answer_hash"));
		$grader_share_hash = trim($this->input->post("grader_share_hash"));
		
		if (!empty($grader_share_hash))
			{
				if ($this->Question->verify_grader_share_hash_vs_question_hash($grader_share_hash,$question_hash))
					$this->Answer->incoming_grade_comment($question_hash,$answer_hash,$comment);
				return;
			}
					
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				echo "no";
				return;
			}
			
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				echo "no";
				return;
			}
		
		$this->Answer->incoming_grade_comment($question_hash,$answer_hash,$comment);
	
	}
	
	function about()
	{
		$this->load->view('header');
		$this->load->view('about');
		$this->load->view('footer');
	}
	
	function delete_question($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to delete a question.");
				return;
			}
		
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);	
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
			
		
		$this->Question->delete_question($question_hash);
		$this->load->view('header');
		$this->load->view('class_menu',Array('class_hash' => $class_hash,'msg' => "Question deleted."));
		$this->load->view('footer');
	}
	
	function delete_question_answers($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to delete answers to a question.");
				return;
			}
		
		$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);	
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
			
		
		$this->Question->delete_question_answers($question_hash);
		$this->load->view('header');
		$this->load->view('class_menu',Array('class_hash' => $class_hash,'msg' => "Answers deleted."));
		$this->load->view('footer');
	}
	
	function set_class_status($class_hash,$status)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to delete a question.");
				return;
			}
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		$this->Classes->set_class_status($class_hash,$status);
		$this->class_menu($class_hash);
	}
	
	function reset_password($class_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to reset a password.");
				return;
			}
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
	
		$this->load->view('header');
		$this->load->view('reset_password',Array('class_hash' => $class_hash,'user_hash' => $user_hash));
		$this->load->view('footer');
	}
	
	function reset_password_incoming($class_hash,$student_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to reset a password.");
				return;
			}
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
			
		$pw = $this->User->update_for_reset_password($student_hash);
				
		$this->load->view('header');
		$this->load->view('reset_password',Array('class_hash' => $class_hash,'user_hash' => $user_hash,'pw' => $pw,'msg' => 'Student password has been reset.'));
		$this->load->view('footer');
	}
	
	function do_reset_password()
	{
		$user_name = trim($this->input->post('user_name'));
		$tpw = trim($this->input->post('tpw'));
		$new_pw = trim($this->input->post('new_pw'));
		$verify_new_pw = trim($this->input->post('verify_new_pw'));
	
		if ($new_pw != $verify_new_pw)
			{
				$this->load->view('header');
				$this->load->view('reset_password',Array('user_name' => $user_name,'msg' => "Your new password didn't match"));
				$this->load->view('footer');
				return;
			}
			
		if ($this->User->verify_reset_password($user_name,$tpw))
			{
				$this->User->change_password($user_name,$new_pw);
				$this->load->view('header');
				$this->load->view('reset_password',Array('done' => true));
				$this->load->view('footer');
				return;
			}
			
		$this->load->view('header');
		$this->load->view('reset_password',Array('user_name' => $user_name,'msg' => "Your temporary password was not correct."));
		$this->load->view('footer');
		return;
	}
	
	function set_user_fl()
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to set your names.");
				return;
			}
		$last = trim($this->input->post('last'));
		$first = trim($this->input->post('first'));
		
		$this->load->view('header');
		if (!empty($first) && !empty($last))
			{
				$this->User->update_fl($user_hash,$last,$first);
				$this->load->view('main_menu',Array('msg' => 'First and last name updated.'));
			}
		else $this->load->view('main_menu',Array('msg' => 'Please provide your first and last names.'));
		$this->load->view('footer');
	}
	
	function dump_questions($class_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to dump questions.");
				return;
			}
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		$this->load->view('header');
		$this->load->view('dump_questions',Array('class_hash' => $class_hash));
		$this->load->view('footer');
	
	}
	
	function dump_comments($question_hash)
	{
		echo $this->Comments->get_comments($question_hash);
	}
	
	function post_comment($user_hash,$question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		$comment = trim($this->input->post('comment'));
		$form_user_hash = trim($this->input->post('user_hash'));
		if ($user_hash != $form_user_hash)
			return;
		$user_name = $this->User->get_user_name($user_hash);
		$name = $user_name['last'] . ", " . $user_name['first'];
		$comment_hash = md5(time() . $comment . $user_hash . $question_hash);
		$sql = "insert into comment values(NULL," . $this->db->escape($comment_hash) . "," .
															$this->db->escape($question_hash) . "," .
															$this->db->escape($user_hash) . "," .
															$this->db->escape($name) . "," .
															$this->db->escape($comment) . "," .
															"now(),unix_timestamp())";
		$this->db->query($sql);
	}
	
	function update_view($question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		if (!empty($user_hash))
			$this->db->query("insert into view values(NULL," . $this->db->escape($question_hash) . "," . $this->db->escape($user_hash) . ",unix_timestamp())");
	}
	
	function grader($grader_share_hash)
	{
		$question_hash = $this->Question->get_question_hash_from_grader_share($grader_share_hash);
		if ($question_hash === false)
			{
				$this->cant_continue("Grader link invalid.");
				return;
			}
				
		$this->load->view('header');
		$this->load->view('grade_waiting',Array('question_hash' => $question_hash,'grader' => true));
		$this->load->view('footer');
	
	}
	
	function owner_question_sort($user_hash,$class_hash,$how)
	{
		$this->Sticky->set($user_hash,'question_sort',$how);
		$this->class_menu($class_hash);
	}
	
	function add_response($class_hash,$question_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to dump questions.");
				return;
			}
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
				
		$this->load->view('header');
		$this->load->view('add_response',Array('class_hash' => $class_hash,'question_hash' => $question_hash));
		$this->load->view('footer');
	}
	
	function manage_students($class_hash)
	{
		$user_hash = $this->session->userdata('user_hash');
		
		if (empty($user_hash))
			{
				$this->cant_continue("You must be logged on to dump questions.");
				return;
			}
			
		if (!$this->Classes->verify_class_owner($user_hash,$class_hash))
			{
				$this->cant_continue('You do not appear to be the owner of this class.');
				return;
			}
		$student_array = $this->input->post("student_array");
		$confirm_delete_student = trim($this->input->post("confirm_delete_student"));
		
		$this->load->view('header');
		if (empty($confirm_delete_student) && !empty($student_array))
			{
				$this->load->view('manage_students',Array('class_hash' => $class_hash,'msg' => 'Confirm not selected; no action taken.'));	
			}
		else if (!empty($student_array) && $confirm_delete_student == 'confirm')
			{
				
				foreach($student_array as $student_hash)
					{
						$this->db->query("delete from enroll where user_hash=" . $this->db->escape($student_hash));
						$this->db->query("delete from answer where student_hash=" . $this->db->escape($student_hash));
					}
				$count = count($student_array);
				$this->load->view('manage_students',Array('class_hash' => $class_hash,'msg' => "$count student(s) deleted."));	
			}
		else $this->load->view('manage_students',Array('class_hash' => $class_hash));	
		$this->load->view('footer');
	}

	
	
}
