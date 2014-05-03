<?php
//$class_hash and $question_hash are defined

$raw = $this->Question->get_raw_question_data($question_hash);

echo anchor("welcome/class_menu/$class_hash","Return",Array('id' => 'return_link'));
echo "<p/>";

echo "<h1>Add question response</h2>";

echo "<h2>Question</h2>";
echo $raw['qtext'] . "<br/>";
echo "<div id=\"note_larger\">";
if ($raw['type'] == 'mc' || $raw['type'] == 'num')
	echo "<b>Answer:</b> " . $raw['answer'] . " ";
echo "<b>Deadline:</b> " . $raw['deadline_nice'] . "<br/>";
echo "</div>";
echo "<h2>Select Student</h2>";

echo form_open("welcome/insert_answer_incoming/$class_hash/$question_hash");


echo "<select id=\"form_input\" name=\"student_hash\">";
$options = $this->Classes->get_students_into_select($class_hash);
echo $options;
echo "</select>";

echo "<h2>Answer</h2>";
echo "<input id=edit_field name=answer type=text size=30/>";
echo "<p/>";

echo "<h2>Correct</h2>";
echo "<select id=\"edit_field\" name=\"correct\">";
echo "<option value=\"yes\">yes</option>";
echo "<option value=\"no\">no</option>";
echo "<option value=\"waiting\">waiting (use for manual grading)</option>";
echo "</select>";
echo "<p/>";

echo "<h2>Points</h2>";
echo "<input id=edit_field name=points type=text size=5/>";
$qn = $this->Question->get_question_numerics($question_hash);
echo " (Max: " . $qn['points'] . ")";
echo "<p/>";

	
echo "<h2>Comment</h2>";
echo "<textarea id=edit_field name=comment rows=3 cols=30></textarea>";

echo "<p/>";

echo "<p/>";
echo "<input id=\"form_input\" type=submit>";
echo form_close();




?>