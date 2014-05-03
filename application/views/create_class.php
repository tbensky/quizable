<?php
echo "<h1>Create a class</h1>";
if (!empty($msg))
	{
		echo "<div id=\"message\">$msg</div>";
		echo "<p/>";
	}

if (empty($title))
	$title = '';
if (empty($code))
	$code = '';
echo form_open("welcome/create_class_incoming");
echo "<h2>Class title</h2>";
echo "<input type=text name=title id=form_input size=70 value=\"$title\">";
echo "<div id=\"note\">Make the class familiar to your students.  Include the course title, number, and your name.";
echo "<br/>";
echo "Example: Dr. Dan's ASTRO-101 course at 8am</div>";
echo "<p/>";

echo "<h2>Class code</h2>";
echo "<input type=text name=code id=form_input size=20 value=\"$code\">";
echo "<div id=\"note\">This is a codeword students will need to join your class.</div>";
echo "<p/>";
echo "<input type=submit>";
echo " | ";
echo anchor("welcome/main_menu","Cancel",Array("id" => "return_link"));
echo form_close();
?>