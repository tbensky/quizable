<?php
//$class_hash is defined

if (!empty($msg))
	{
		echo "<div id=\"message\">$msg</div>";
		echo "<p/>";
	}
	
if (empty($done))
	$done = false;
if ($done === true)
	{
		echo "Your password has been reset.  Login as usual (with your new password) to use Quizable.  ";
		echo anchor("welcome/","Return");
		return;
	}

if (!empty($user_name))
{
	echo form_open("welcome/do_reset_password");

	echo "Type your temporary password: ";
	echo "<input type=password size=30 name=\"tpw\"/>";
	echo "<p/>";
	
	echo "Type your new password: ";
	echo "<input type=password size=30 name=\"new_pw\"/>";
	echo "<p/>";
	
	echo "Verify your new password: ";
	echo "<input type=password size=30 name=\"verify_new_pw\"/>";
	echo "<p/>";
	
	echo "<input type=hidden name=\"user_name\" value=\"$user_name\">";
	echo "<br/>";
	echo "<input type=submit>";
	echo form_close();
	return;
}


echo "<h1>Reset Student Password</h1>";


if (!empty($pw))
	{	
		echo "<p/>";
		echo "Tell the student their temporary password is: <b>$pw</b>";
		echo "<p/>";
	}
echo "Click on a student's name to reset their password or ";
echo anchor("welcome/main_menu/","Return");
echo " to return.";

echo "<h2>Students</h2>";

$s = $this->Classes->get_students($class_hash);

foreach($s as $student)
{
	echo anchor("welcome/reset_password_incoming/$class_hash/" . $student['user_hash'],$student['user_name']);
	echo "<br/>";
}

echo anchor("welcome/main_menu/","Return");

?>