<?php
if (!empty($msg))
	echo "<div id=\"message\">$msg</div>";
else echo "An error occurred.  Cannot continue.  ";

echo "<p/>";

echo anchor("welcome/","Return");

?>