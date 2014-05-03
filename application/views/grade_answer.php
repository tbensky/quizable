<?php
//$question_hash, $user_hash, and $answer are defined
$class_hash = $this->Question->get_class_hash_from_question_hash($question_hash);


if ($this->Question->expired($question_hash))
	{
		echo "<div id=\"expired_message\">Sorry, this question has expired.</div>";
		echo "  " . anchor("welcome/question_menu/$class_hash","Return");
		return;
	}

if ($this->Answer->all_tries_used($user_hash,$question_hash))
	{
		echo "<div id=\"expired_message\">Sorry, you have used all of your allowed attempts for this problem.</div>";
		echo "  " . anchor("welcome/question_menu/$class_hash","Return");
		return;
	}
	


$this->Answer->new_answer($user_hash,$question_hash,$class_hash,$answer,'yes',1.0);
?>