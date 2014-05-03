<?php
echo "<div id=\"intro_wrapper\">";
echo "</div>";
$create = anchor("welcome/create_account","Create an account");

echo form_open("welcome/login");
echo<<<EOT
<div id="intro_text">
<h1>Sign in</h1>
Username: <input type=text name=username size=30 id=form_input>
<p/>
Password: <input type=password name=userpassword size=20 id=form_input>
<p/>
<input type=submit value="Sign in">
<p/>
New User?  $create
</div>
EOT;

?>
