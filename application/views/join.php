<?php

echo "<h1>Join a class</h1>";
if (!empty($msg))
	echo "<div id=\"message\">$msg</div>";
	
$last = "";
$first = "";

$ret = $this->User->get_user_name($user_hash);
$last = $ret['last'];
$first = $ret['first'];
	
echo form_open("welcome/incoming_join");
echo "<h2>Class code</h2>";
echo "<input type=text name=class_code size=20 id=enroll_field>";
echo "<div id=\"note\">This is the 'class code' your teacher gave you when directing you to this site..</div>";

echo "<p/>";

echo "<h2>Your name</h2>";
echo "Last name: <input type=text name=last size=20 id=enroll_field value=\"$last\">";
echo "<br/>";
echo " First name: <input type=text name=first size=20 id=enroll_field value=\"$first\">";
echo "<div id=\"note\">Be proper and correct about these. It's how your teacher will identify you for your grade.</div>";

echo "<p/>";
echo "<input type=submit>";
echo " | ";
echo anchor("welcome/main_menu","Cancel",Array("id" => "return_link"));
echo form_close();
?>