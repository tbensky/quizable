<?php
//$question_hash and $class_hash are defined

$raw = $this->Question->get_raw_question_data($question_hash);

if (!empty($msg))
	echo "<div id=\"message\">$msg</div>";
	
echo "<h1>Answers for question</h1>";
echo anchor("welcome/class_menu/$class_hash","Return",Array('id' => 'return_link'));
echo "<p/>";
echo "<div id=\"info_box_full\">";
echo $raw['qtext'];
echo "<div id=\"note_no_indent\">Due: " . $raw['deadline_nice'] . "</div>";
if ($raw['type'] == 'num' || $raw['type'] == 'mc')
	{
		echo "<span id=\"note_larger\">";
		echo "Answer: " . $raw['answer'];
		echo "</span>";
	}
echo "<br/>";
echo anchor("welcome/add_response/$class_hash/$question_hash","Add response",Array("id" => "small_link"));
echo "<hr/>";
echo "<div id=\"answer_dump\">";
$this->Answer->dump_answers($question_hash);
echo "</div>";

echo "<p/>";
echo anchor("welcome/class_menu/$class_hash","Return",Array('id' => 'return_link'));


?>
