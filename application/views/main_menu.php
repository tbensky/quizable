<?php

$user_name = $this->session->userdata('user_name');
$user_hash = $this->session->userdata('user_hash');

if (empty($user_name) || empty($user_hash))
	{
		echo "You must be logged on to use Quizable.  ";
		echo anchor("welcome/login","Logon");
		echo " | ";
		echo anchor("welcome/create_account/","Create an account");
		return;
	}
	

	
echo "<h1>Welcome $user_name</h1>";

if (!empty($msg))
	{
		echo "<div id=\"message\">$msg</div>";
		echo "<p/>";
	}
echo "<div id=\"main_menu\">";
echo "<div id=\"tabs\">";
echo "<ul>";
echo "<li><a href=\"#tabs-1\">Student</a>";
echo "<li><a href=\"#tabs-2\">Teacher</a>";
echo "</ul>";

echo "<div id=\"tabs-1\">";

$user_fl = $this->User->get_user_name($user_hash);
if ($this->User->is_enrolled($user_hash) && (empty($user_fl['last']) || empty($user_fl['first'])))
	{
		echo form_open("welcome/set_user_fl");
		echo "<h2>Please provide your last and first names:</h2>";
		echo "<div id=\"note\">(You didn't provide them when you joined this class, and your teacher needs to know who you are.)</div>";
		echo "<br/>";
		echo "Last: <input type=text size=20 name=last>";
		echo "<p/>";
		echo "First: <input type=text size=20 name=first>";
		echo "<p/>";
		echo "<input type=submit>";
		echo form_close();
	}
else 
	{
		echo "<h2>Classes you are taking</h2>";
		echo $this->Classes->dump_classes_joined($user_hash);
	}
echo "</div>";

echo "<div id=\"tabs-2\">";
echo "<h2>Classes you are teaching</h2>";
echo $this->Classes->dump_classes_owned($user_hash);
echo "</div>";
echo "</div>";



echo "</div>";




if ($this->User->is_admin($user_name))
	{
	}
?>

<script>
$('#tabs').tabs();
</script>