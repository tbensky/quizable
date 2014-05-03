<?php

echo "<div id=\"wrapper\">";


echo "<h1>Create a new account</h1>";

if (!empty($msg))
	{
		echo "<div id=\"message_red\">$msg</div>";
	}

if (!empty($action))
	echo form_open("welcome/create_account/$action");
else echo form_open("welcome/create_account/home");
$publickey = "6Leey-YSAAAAAAjfeZYjuuibd-Foc8Rfjl3kEfV-"; // you got this from the signup page

//echo validation_errors();

echo "<h2>1.  Create a user name</h2>";
echo "Your user name must be your valid email address.<p/>";
echo "<input id=account_input type=text name=email value=\"" . set_value('email') . "\" size=30>";
echo form_error('email');

echo "<p/>";

echo "<h2>2.  Create a good password</h2>";
echo "Your password must have at least 4 characters.<p/>";
echo "<input id=account_input type=password name=password size=30>";
echo form_error('password');

echo "<p/>";

echo "<h2>2.  Re-type your password</h2>";
echo "<input id=account_input type=password name=password_confirm size=30>";
echo form_error('password_confirm');

echo "<p/>";

echo "<h2>3.  Please prove that you are human</h2>";

if (!empty($captcha_error))
	echo "<div id=\"error_message\">$captcha_error</div><p/>";

echo recaptcha_get_html($publickey);
echo "<p/>";
if (!empty($action))
	echo "<input type=hidden name=action value=\"$action\">";
echo "<input type=submit value=\"Create account\">  ";
echo anchor("welcome/","Cancel");

echo form_close();

echo "</div>";

?>


