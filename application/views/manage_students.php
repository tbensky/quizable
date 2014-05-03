<?php
//$class_hash is defined


echo anchor("welcome/main_menu","Return",Array('id' => 'return_link'));
echo "<p/>";

if (!empty($msg))
	echo "<div id=\"message\">$msg</div>";


echo "<h1>Manage Students</h1>";

echo form_open("welcome/manage_students/$class_hash");

echo $this->Classes->get_students_with_checkbox($class_hash);

echo "<p/>";
echo "<span id=\"confirm_delete_student\"><input type=checkbox name=\"confirm_delete_student\" value=\"confirm\"> Click to verify. Selected students and their data will be deleted.</span>";
echo "<p/>";
echo "<input type=submit>";
echo form_close();
?>