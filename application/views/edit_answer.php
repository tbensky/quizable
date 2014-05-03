<?php
//$answer_hash, $class_hash are defined
echo "<h1>Edit an Answer</h1>";

$row = $this->Answer->get_answer_data($answer_hash);
echo form_open("welcome/edit_answer_incoming/$class_hash/" . $row['question_hash'] . "/$answer_hash");
echo "<h2>Answer</h2>";
echo "<input id=edit_field name=answer type=text size=30 value=\"" . $row['answer'] . "\">";
echo "<p/>";

echo "<h2>Correct</h2>";
echo "<input id=edit_field edit_field name=correct type=text size=3 value=\"" . $row['correct'] . "\">";
echo "<div id=\"note\">This must be <b>yes</b> or <b>no</b></div>";
echo "<p/>";

echo "<h2>Points</h2>";
echo "<input id=edit_field name=points type=text size=5 value=\"" . $row['points'] . "\">";
$question_hash = $this->Answer->get_question_hash_from_answer_hash($answer_hash);
$qn = $this->Question->get_question_numerics($question_hash);
echo " (Max: " . $qn['points'] . ")";
echo "<p/>";


$comment = $this->Answer->get_grading_comment($row['answer_hash']);
if ($comment === false)
	$comment = "";
	
echo "<h2>Comment</h2>";
echo "<textarea id=edit_field name=comment rows=3 cols=30>$comment</textarea>";

echo "<p/>";
echo "<input type=submit>";
echo " | ";
echo anchor("welcome/dump_answers/" . $row['question_hash'],"Cancel");


echo form_close();
?>