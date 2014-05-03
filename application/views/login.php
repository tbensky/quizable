<?php

if (empty($user_name))
	$user_name = "";


echo "<div id=\"wrapper\">";

if (!empty($msg))
	echo "<div id=\"form_error\">$msg</div>";


echo form_open("welcome/login");
echo "<h1>Already have an account?</h1>";
echo "<p/>";
echo "User name: ";
echo "<input id=login_input type=text name=username size=30 value=\"$user_name\">";
echo "<p/>";
echo "  Password: ";
echo "<input id=login_input type=password name=userpassword size=30>";
if (!empty($action))
	echo "<input type=hidden name=action value=\"$action\">";
echo "<p/>";
echo "<input type=submit value=\"Login\">  ";
echo anchor("welcome/","Cancel");

echo "<p/>";
echo "New here? ";
if (empty($action))
	echo anchor("welcome/create_account/home","Create a new account");
else echo anchor("welcome/create_account/start_new","Create a new account");

echo form_close();


?>


